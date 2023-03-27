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
 * External Lib - Edit learning period
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class get_learning_plan_courses_external extends external_api {

    public static function get_learning_plan_courses_parameters() {
        return new external_function_parameters(
            array(
                'learningPlanId'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                 'periodId'   => new external_value(
                    PARAM_INT,
                    'ID of the period',
                    VALUE_OPTIONAL,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function get_learning_plan_courses($learningPlanId,$periodId=null) {
        global $USER, $DB;
        
        $parameters = ['learningplanid' => $learningPlanId];
        if($periodId){
            $parameters['periodid'] = $periodId;
        }
        $courses = $DB->get_records("local_learning_courses", $parameters);
        foreach($courses as $course){
            $coreCourse = $DB->get_record("course", ['id'=>$course->courseid]);
            $course->name = $coreCourse->fullname;
        }
        return [
            'courses' => json_encode(array_values($courses))
        ];
    }

    public static function get_learning_plan_courses_returns() {
        return new external_single_structure(
            array(
                'courses' => new external_value(PARAM_RAW, 'Learning plan courses')
            )
        );
    }
}
