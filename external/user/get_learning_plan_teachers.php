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

class get_learning_plan_teachers_external extends external_api {

    public static function get_learning_plan_teachers_parameters() {
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
                 'courseId'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan course',
                    VALUE_OPTIONAL,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function get_learning_plan_teachers($learningPlanId,$periodId=null,$courseId=null) {
        global $USER, $DB;
        
        // $coreCourseId = $DB->get_record("local_learning_courses",['id' => $courseId])->courseid;
        // $courseGroups = $DB->get_records("groups", ['courseid'=>$coreCourseId]);
        
        // $teachers = [];
        // foreach($courseGroups as $group){
        //     $groupTeachers = $DB->get_records("local_learning_users", ['learningplanid' => $learningPlanId, 'userroleid' => 4 , 'groupname' => $group->name]);
        //     foreach($groupTeachers as $teacher){
        //         $coreUser = $DB->get_record('user',['id'=>$teacher->userid]);
        //         $teacher->fullname = $coreUser->firstname.' '.$coreUser->lastname;
        //         $teacher->email = $coreUser->email;
        //         array_push($teachers, $teacher);
        //     }
        // }
        
        $teachers = [];
        $groupTeachers = $DB->get_records("local_learning_users", ['learningplanid' => $learningPlanId, 'userroleid' => 4]);
        foreach($groupTeachers as $teacher){
            $coreUser = $DB->get_record('user',['id'=>$teacher->userid]);
            $teacher->fullname = $coreUser->firstname.' '.$coreUser->lastname;
            $teacher->email = $coreUser->email;
            array_push($teachers, $teacher);
        }
        
        return [
            'teachers' => json_encode(array_values($teachers))
        ];
    }

    public static function get_learning_plan_teachers_returns() {
        return new external_single_structure(
            array(
                'teachers' => new external_value(PARAM_RAW, 'Learning plan teachers')
            )
        );
    }
}
