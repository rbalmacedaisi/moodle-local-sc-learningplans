<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * External Lib - Save learning courses
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/learningplanlib.php');

class save_learning_course_external extends external_api {

    public static function save_learning_course_parameters() {
        return new external_function_parameters(
            array(
                'learningplan'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'periodid'   => new external_value(
                    PARAM_INT,
                    'ID of the period',
                    VALUE_DEFAULT,
                    null
                ),
                'subperiodid'   => new external_value(
                    PARAM_INT,
                    'ID of the subperiod',
                    VALUE_DEFAULT,
                    null
                ),
                'courseid'   => new external_value(
                    PARAM_TEXT,
                    'ID List of the course to add',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'required'   => new external_value(
                    PARAM_INT,
                    'If is required (1) or optional (0)',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'credits'   => new external_value(
                    PARAM_INT,
                    'credits of courses',
                    VALUE_DEFAULT,
                    null
                ),
                'position'   => new external_value(
                    PARAM_INT,
                    'credits of courses',
                    VALUE_DEFAULT,
                    null
                ),
            )
        );
    }

    public static function save_learning_course($learningplan, $periodid,$subperiodid, $courseid, $required, $credits, $position) {
        global $DB, $USER;

        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplan]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
      
        $tablecourses = 'local_learning_courses';
        $courselist = explode(',', $courseid);
        foreach ($courselist as $courseid) {
            // Check if course exist before in this learning plan.
            $existbefore = $DB->get_record($tablecourses, [
                'courseid' => $courseid,
                'learningplanid' => $learningplan,
            ]);
            if ($existbefore) {
                continue;
            }
            $courseposition = $position;
            if ($position == null && $required) {
                // No position set and is required course, so calculate it.
                $courseposition = 1;
                $prevcourses = $DB->get_records($tablecourses, [
                    'learningplanid' => $learningplan,
                    'isrequired' => $required,
                    'periodid' => $periodid,
                ]);
                if ($prevcourses) {
                    $courseposition = count($prevcourses) + 1;
                }
            }

            $learningplancourses = new stdClass();
            $learningplancourses->learningplanid = $learningplan;
            $learningplancourses->courseid = $courseid;
            $learningplancourses->isrequired = $required;
            $learningplancourses->position = $courseposition ?? 0;
            $learningplancourses->credits = $credits;
            $learningplancourses->periodid = $periodid;
            $learningplancourses->subperiodid = $subperiodid;
            $learningplancourses->usermodified = $USER->id;
            $learningplancourses->timecreated = time();
            $learningplancourses->timemodified = time();
            $learningplancourses->id = $DB->insert_record('local_learning_courses', $learningplancourses);
            if ($required) {
                // Increase course count if is required.
                $learningplanrecord->coursecount++;
            }
        }
        $users = $DB->get_records_sql(
            'SELECT llu.* FROM {local_learning_users} llu
            JOIN {user} u ON (u.id = llu.userid)
            WHERE llu.learningplanid = :learningplanid', ['learningplanid' => $learningplan]);
        foreach ($users as $user) {
            $userid = $user->userid;
            $roleid = $user->userroleid;
            enrol_user_in_learningplan_courses($learningplan, $userid, $roleid, $user->groupname);
        }
        $learningplanrecord->timemodified = time();
        $DB->update_record('local_learning_plans', $learningplanrecord);
        send_email_lp_updated($learningplan);
        return [
            'id' => $learningplancourses->id ?? 0,
        ];

    }

    public static function save_learning_course_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT,  'Record ID'),
            )
        );
    }
}
