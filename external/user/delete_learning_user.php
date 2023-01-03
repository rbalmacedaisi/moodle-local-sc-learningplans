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
 * External Lib - Delete learning user
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');

class delete_learning_user_external extends external_api {

    public static function delete_learning_user_parameters() {
        return new external_function_parameters(
            array(
                'learningplan'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'userid'   => new external_value(
                    PARAM_INT,
                    'ID of the user to remove',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'unenrol'   => new external_value(
                    PARAM_BOOL,
                    'Remove the courses enrol',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function delete_learning_user($learningplan, $userid, $unenrol) {
        global $DB;
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplan]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        $learninguserexist = $DB->get_record('local_learning_users', [
            'learningplanid' => $learningplan,
            'userid' => $userid
        ]);
        $isdelete = null;
        if (!$learninguserexist) {
            $isdelete = false;
        } else {
            // Delete relation user - lp records.
            $isdelete = $DB->delete_records('local_learning_users', ['id' => $learninguserexist->id]);
            if ($unenrol) {
                // Check the courses from this lp.
                $optionalcourses = $DB->get_records_sql('SELECT c.id as course_id, lpc.*, c.fullname
                FROM {local_learning_courses} lpc
                    JOIN {course} c ON (c.id = lpc.courseid)
                    WHERE lpc.learningplanid = :learningplanid AND lpc.isrequired = :isrequired',
                    [
                            'learningplanid' => $learningplan,
                            'isrequired' => 0
                    ]);
                $requiredcourses = $DB->get_records_sql('SELECT c.id as course_id, lpc.*, c.fullname
                FROM {local_learning_courses} lpc
                    JOIN {course} c ON (c.id = lpc.courseid)
                    WHERE lpc.learningplanid = :learningplanid AND lpc.isrequired = :isrequired',
                    [
                        'learningplanid' => $learningplan,
                        'isrequired' => 1
                    ]);
                $coursesinotherslearningplans = $DB->get_records_sql(
                    'SELECT CONCAT(lpc.id, lpu.id), lpu.*, lpc.*
                    FROM {local_learning_courses} lpc
                    JOIN {local_learning_users} lpu ON (lpu.learningplanid = lpc.learningplanid)
                    WHERE lpu.learningplanid <> :learningplanid AND lpu.userid = :userid',
                    [
                        'userid' => $userid,
                        'learningplanid' => $learningplan,
                    ]
                );

                foreach ($coursesinotherslearningplans as $rcourse) {
                    $courseid = $rcourse->courseid;
                    // If required course exist in other LP, unset.
                    if (isset($requiredcourses[$courseid])) {
                        unset($requiredcourses[$courseid]);
                    }
                    // If optional course exist in other LP, unset.
                    if (isset($optionalcourses[$courseid])) {
                        unset($optionalcourses[$courseid]);
                    }
                    // If the required and optional courses are empty...
                    if (empty($optionalcourses) && empty($requiredcourses)) {
                        break; // Break the parent foreach.
                    }
                }
                // If the code enter to the next two condition, is because the courses in the array is not present in other user lp.
                $enrolplugin = enrol_get_plugin('manual');
                if ($requiredcourses) {
                    foreach ($requiredcourses as $courseid => $c) {
                        unenrol_user($enrolplugin, $userid, $courseid);
                    }
                }
                if ($optionalcourses) {
                    foreach ($optionalcourses as $courseid => $c) {
                        unenrol_user($enrolplugin, $userid, $courseid);
                    }
                }
            }
        }
        $learningplanrecord->usercount--;
        $learningplanrecord->timemodified = time();
        $DB->update_record('local_learning_plans', $learningplanrecord);
        return [
            'isdelete' => $isdelete,
        ];
    }

    public static function delete_learning_user_returns() {
        return new external_single_structure(
            array(
                'isdelete' => new external_value(PARAM_BOOL,  'If the record is deleted'),
            )
        );
    }
}
