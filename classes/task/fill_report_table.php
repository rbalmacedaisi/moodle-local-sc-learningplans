<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Task to fill teacher report
 *
 * @package     local_sc_learningplans
 * @category    string
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sc_learningplans\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/libs/learningplanlib.php');

/**
 * An example of a scheduled task.
 */
class fill_report_table extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('report', 'local_sc_learningplans');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB, $CFG;
        require_once("$CFG->libdir/completionlib.php");
        $learningusers = $DB->get_records_sql(
            'SELECT
                    lu.id,
                    lu.userid,
                    lu.userrolename,
                    lu.waitingperiod,
                    lu.currentperiodid lastperiodid,
                    lperiod.name lastperiodname,
                    lu.learningplanid

                    FROM {local_learning_users} lu
                    LEFT JOIN {local_learning_periods} lperiod ON (lperiod.id = lu.currentperiodid)
                    ORDER BY lu.userid, lu.learningplanid',
            []
        );

        $learningplans = $DB->get_records('local_learning_plans');
        foreach ($learningplans as &$lp) {
            $learningplancourses = $DB->get_records_sql('SELECT lpc.*, c.fullname as coursename
                FROM {local_learning_courses} lpc
                JOIN {course} c ON (c.id = lpc.courseid)
                WHERE lpc.learningplanid = :learningplanid AND lpc.isrequired = 1
                ORDER BY lpc.periodid, lpc.position',
                [
                    'learningplanid' => $lp->id,
                ]);
            $lp->courses = $learningplancourses;
        }

        $currentreportdata = $DB->get_records_sql('SELECT
                                                        userid_lp,
                                                        id,
                                                        learningplanid,
                                                        userid,
                                                        countcurrentcourse,
                                                        lastcurrentcourse,
                                                        lastcurrentcoursename,
                                                        lastcompletedcourse,
                                                        lastcompletedcoursename,
                                                        lpprogress,
                                                        lastperiodid,
                                                        lastperiodname
                                                    FROM {local_learning_report}'
                                                );

        $allcourses = get_courses();
        foreach ($learningusers as $learninguser) {
            $userid = $learninguser->userid;
            $userlearningplanid = $learninguser->learningplanid;
            if (!isset($learningplans[$userlearningplanid])) {
                continue;
            }
            $learninguser->lastcompletedcourse =
            $learninguser->lastcompletedcoursename =
            $learninguser->lastcurrentcourse =
            $learninguser->lastcurrentcoursename = null;
            $learninguser->countcurrentcourse = 0;
            $coursesinlp = $learningplans[$userlearningplanid]->courses;
            $totalcourses = 0;

            $checkperiodchange = false;
            foreach ($coursesinlp as $courselp) {
                $courseid = $courselp->courseid;
                if (!isset($allcourses[$courseid])) {
                    continue;
                }
                $totalcourses++;
                $objcourse = $allcourses[$courseid];
                $cinfo = new \completion_info($objcourse);
                $iscomplete = $cinfo->is_course_complete($userid);
                if ($learninguser->waitingperiod == 1) {
                    // Waiting to move to the next period.
                    if ($learninguser->lastperiodid == $courselp->periodid) {
                        // The course is in the current lp.
                        $checkperiodchange = true;
                        $lastperiodcompletedcourse = $objcourse;
                    } else {
                        if ($checkperiodchange == true) {
                            if ($learninguser->lastcurrentcourse == null) {
                                // This is the first not completed courses, so is the current course.
                                $learninguser->lastcurrentcourse = $lastperiodcompletedcourse->id;
                                $learninguser->lastcurrentcoursename = $lastperiodcompletedcourse->fullname;
                            }
                        }
                    }
                }

                if ($iscomplete) {
                    // Set this courses as last completed course.
                    if ($learninguser->lastcurrentcourse == null) {
                        // Not current course yet, so count in progress and in last completed course.
                        $learninguser->lastcompletedcourse = $courseid;
                        $learninguser->lastcompletedcoursename = $objcourse->fullname;
                        $learninguser->countcurrentcourse++;
                    }

                } else {
                    if ($learninguser->lastcurrentcourse == null) {
                        // This si the first not completed courses, so is the current course.
                        $learninguser->lastcurrentcourse = $courseid;
                        $learninguser->lastcurrentcoursename = $objcourse->fullname;
                    }
                }
            }
            if ($learninguser->lastcompletedcourse == null) {
                $learninguser->lastcompletedcourse = $learninguser->lastcurrentcourse;
                $learninguser->lastcompletedcoursename = $learninguser->lastcurrentcoursename;
            }
            $learninguser->lpprogress = 0;
            if ($learninguser->countcurrentcourse > 0) {
                $learninguser->lpprogress = $learninguser->countcurrentcourse * 100 / $totalcourses;
            }
            $learninguser->lpprogress = round($learninguser->lpprogress, 2);

            $struseridlp = "$userid-$userlearningplanid";
            $learninguser->userid_lp = $struseridlp;

            if (isset($currentreportdata[$struseridlp])) {
                $recordobject = $currentreportdata[$struseridlp];

                $recordid = $recordobject->id;
                unset($recordobject->id);

                $recordobject = $learninguser;
                $recordobject->id = $recordid;
                $DB->update_record('local_learning_report', $recordobject, true);
            } else {
                $currentreportdata[$struseridlp] = $learninguser;
                $DB->insert_record('local_learning_report', $currentreportdata[$struseridlp], false, true);
            }
        }
        learning_plans_recount_users();
    }
}
