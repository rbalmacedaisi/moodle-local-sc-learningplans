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
 * External Lib - Get possible relation to specific course (record id)
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class get_possible_relations_external extends external_api {

    public static function get_possible_relations_parameters() {
        return new external_function_parameters(
            array(
                'recordid' => new external_value(
                    PARAM_INT,
                    'ID of the record that contain course id',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function get_possible_relations($recordid) {
        global $DB;

        $courserecord = $DB->get_record('local_learning_courses', ['id' => $recordid]);
        if (!$courserecord) {
            throw new moodle_exception('coursenotexist', 'local_sc_learningplans');
        }
        $learningplanid = $courserecord->learningplanid;
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplanid]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
         // Get all courses related to LP and periodid.
         // It doesn't matter if the periodid is null.
        $canberelatedcourses = $DB->get_records('local_learning_courses', [
            'learningplanid' => $learningplanid, 'periodid' => $courserecord->periodid
        ]);
        $allcoursesinlp = $DB->get_records('local_learning_courses', ['learningplanid' => $learningplanid]);
        unset($canberelatedcourses[$recordid]); // Unset $recordid, because can't be related to itself.

        $currentcourserelations = [];
        // Unset course that already are related.
        $alreadycourserelated = $DB->get_records('local_learningplan_rel_cours', ['origin_record_id' => $recordid]);
        foreach ($alreadycourserelated as $alreadycourse) {
            $currentcourserelations[] =
                $canberelatedcourses[$alreadycourse->destination_record_id] ??
                $allcoursesinlp[$alreadycourse->destination_record_id];
            unset($canberelatedcourses[$alreadycourse->destination_record_id]);
        }

        $allcourses = $DB->get_records('course');

        $courses = [];
        foreach ($canberelatedcourses as &$value) {
            $courses[] = [
                'recordid'   => $value->id,
                'courseid'   => $value->courseid,
                'coursename' => $allcourses[$value->courseid]->fullname ?? 'NA',
            ];
        }
        $currentrelations = [];
        foreach ($currentcourserelations as &$value) {
            $currentrelations[] = [
                'recordid'   => $value->id,
                'courseid'   => $value->courseid,
                'coursename' => $allcourses[$value->courseid]->fullname ?? "NA",
            ];
        }
        return [
            'courses' => $courses,
            'current' => $currentrelations,
        ];
    }

    public static function get_possible_relations_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'recordid'   => new external_value(PARAM_INT, 'ID of the record'),
                            'courseid'   => new external_value(PARAM_INT, 'ID of the course'),
                            'coursename' => new external_value(PARAM_TEXT, 'Course full name'),
                        )
                    )
                ),
                'current' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'recordid'   => new external_value(PARAM_INT, 'ID of the record'),
                            'courseid'   => new external_value(PARAM_INT, 'ID of the course'),
                            'coursename' => new external_value(PARAM_TEXT, 'Course full name'),
                        )
                    ),
                    '',
                    VALUE_DEFAULT,
                    []
                ),
            )
        );
    }
}
