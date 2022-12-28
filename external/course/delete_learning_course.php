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
 * External Lib - Delete learning course
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class delete_learning_course_external extends external_api {

    public static function delete_learning_course_parameters() {
        return new external_function_parameters(
            array(
                'learningplan'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'courseid'   => new external_value(
                    PARAM_INT,
                    'ID of the course to delete',
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
            )
        );
    }

    public static function delete_learning_course($learningplan, $courseid, $required) {
        global $USER, $DB;
        $isdelete = false;
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplan]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        // Get the record of the DB!
        $learningcourse = $DB->get_record('local_learning_courses', [
            // Is the record id, not the course id.
            'id' => $courseid,
        ]);
        if ($learningcourse) {
            $DB->delete_records('local_learning_courses', ['id' => $learningcourse->id]);
            $isdelete = true;
            // Decrease the course count.
            $learningplanrecord->usermodified = $USER->id;
            $learningplanrecord->coursecount--;
            $learningplanrecord->updated_at = time();
            $DB->update_record('local_learning_plans', $learningplanrecord);
            if ($required) {
                // Calculate the new positions.
                $DB->execute("UPDATE {local_learning_courses}
                    SET position = position-1
                    WHERE learningplanid = :learningplanid
                    AND isrequired = :isrequired
                    AND position > :position
                    AND periodid = :periodid",
                    [
                        'learningplanid' => $learningplan,
                        'isrequired' => $required,
                        'position' => $learningcourse->position,
                        'periodid' => $learningcourse->periodid,
                    ]);
            }
        }
        return [
            'isdelete' => $isdelete,
        ];
    }

    public static function delete_learning_course_returns() {
        return new external_single_structure(
            array(
                'isdelete' => new external_value(PARAM_BOOL,  'If the course is delete succesfull'),
            )
        );
    }
}
