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
 * External Lib - Get Courses from LP (My OVerview Block).
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_course\external\course_summary_exporter;

require_once($CFG->dirroot . '/course/externallib.php');


class get_course_overview_external extends external_api {

    public static function get_course_overview_parameters() {
        return new external_function_parameters(
            array(
                'classification' => new external_value(PARAM_ALPHA, 'future, inprogress, or past'),
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'sort' => new external_value(PARAM_TEXT, 'Sort string', VALUE_DEFAULT, null),
                'customfieldname' => new external_value(
                    PARAM_ALPHANUMEXT,
                    'Used when classification = customfield',
                    VALUE_DEFAULT,
                    null
                ),
                'customfieldvalue' => new external_value(
                    PARAM_RAW,
                    'Used when classification = customfield',
                    VALUE_DEFAULT,
                    null
                ),
            )
        );
    }
    /**
     * Undocumented function
     *
     * @param  string $classification past, inprogress, or future
     * @param  int $limit Result set limit
     * @param  int $offset Offset the full course set before timeline classification is applied
     * @param  string $sort SQL sort string for results
     * @param  string $customfieldname
     * @param  string $customfieldvalue
     * @return array list of courses and warnings
     * @return void
     */
    public static function get_course_overview(
        string $classification,
        int $limit = 0,
        int $offset = 0,
        string $sort = null,
        string $customfieldname = null,
        string $customfieldvalue = null
    ) {
        global $DB, $USER;
        // Get all courses first (Courses taht user is enrolled).
        $corecourseexternal = core_course_external::get_enrolled_courses_by_timeline_classification(
            'all',
            $limit,
            $offset,
            $sort,
            $customfieldname,
            $customfieldvalue
        );
        $filteredcourses = $corecourseexternal['courses'];
        $nextoffset = $corecourseexternal['nextoffset'];
        // If have any course.
        if ($filteredcourses) {
            $allcoursesinlp = [];
            $userlearningplans = $DB->get_records('local_learning_users', ['userid' => $USER->id]);
            foreach ($userlearningplans as $ulp) {
                $coursesinlp = $DB->get_fieldset_select(
                    'local_learning_courses',
                    'courseid',
                    'learningplanid = :learningplanid',
                    ['learningplanid' => $ulp->learningplanid]
                );
                $coursesinlp = array_flip($coursesinlp);
                $allcoursesinlp = $allcoursesinlp + $coursesinlp;
            }
            // If found courses, do magic with filters.
            if ($classification == 'insidelp') {
                foreach ($filteredcourses as $k => $course) {
                    // If the course isn't exists as key in the var $allcoursesinlp, then unset from $filteredcourses.
                    // Show the courses inside any user learning plan.
                    if (!isset($allcoursesinlp[$course->id])) {
                        unset($filteredcourses[$k]);
                        $nextoffset--;
                    }
                }
            }
            if ($classification == 'outsidelp') {
                foreach ($filteredcourses as $k => $course) {
                    // If the course is exists as key in the var $allcoursesinlp, then unset from $filteredcourses.
                    // Show the courses outside any user learning plan.
                    if (isset($allcoursesinlp[$course->id])) {
                        unset($filteredcourses[$k]);
                        $nextoffset--;
                    }
                }
            }
        }
        return [
            'courses' => $filteredcourses,
            'nextoffset' => $nextoffset,
        ];
    }

    public static function get_course_overview_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_multiple_structure(course_summary_exporter::get_read_structure(), 'Course'),
                'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request')
            )
        );
    }
}
