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
 * Observer
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/completionlib.php");
require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/learningplanlib.php');

class local_sc_learningplans_observer {
    /**
     * Triggered when 'course_completed' event is triggered.
     *
     * @param \core\event\course_completed $event
     */
    public static function user_course_completed(\core\event\course_completed  $event) {
        global $DB;
        $eventdata = $event->get_data();
        $userid = $eventdata['relateduserid'];
        $courseid = $eventdata['courseid'];

        $otherlpwithsamecourse = $DB->get_records_sql(
            "SELECT
                lc.id,
                lc.learningplanid,
                lc.courseid,
                lu.userroleid,
                lu.groupname
            FROM {local_learning_users} lu
            JOIN {local_learning_plans} lp ON (lp.id = lu.learningplanid)
            JOIN {local_learning_courses} lc ON (lc.learningplanid = lu.learningplanid
                AND lc.courseid = :courseidcompleted AND lc.isrequired = 1)
            WHERE lu.userid = :userid
            ",
            [
                'userid' => $userid,
                'courseidcompleted' => $courseid,
            ]
        );
        foreach ($otherlpwithsamecourse as $otherlp) {
            $roleid = $otherlp->userroleid;
            $learningplanid = $otherlp->learningplanid;
            enrol_user_in_learningplan_courses($learningplanid, $userid, $roleid, $otherlp->groupname);
        }
    }

    /**
     * Triggered when 'user_deleted' event is triggered.
     *
     * @param \core\event\user_deleted $event
     */
    public static function user_deleted(\core\event\user_deleted  $event) {
        global $DB;
        $userid = $event->objectid;
        $DB->delete_records('local_learning_report', ['userid' => $userid]); // Delete from report.
        $DB->delete_records('local_learning_users', ['userid' => $userid]); // Delete from lp users.
        learning_plans_recount_users();
    }

}
