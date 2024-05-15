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
 * External Lib - Get learning plan periods
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class get_learning_plan_periods_external extends external_api {

    public static function get_learning_plan_periods_parameters() {
        return new external_function_parameters(
            array(
                'learningPlanId'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function get_learning_plan_periods($learningPlanId) {
        global $DB;
        
        try{
            // Check if plan has periods.
            $periods = $DB->get_records("local_learning_periods", ['learningplanid' => $learningPlanId]);
            
            return [
                'periods' => json_encode(array_values($periods))
            ];
        }catch(Exception $e){
            return ['status'=>-1,'message'=>$e->getMessage()];
        }
        
    }

    public static function get_learning_plan_periods_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, '1 if success, -1 otherwise',VALUE_DEFAULT,1),
                'periods' => new external_value(PARAM_RAW, 'Periods information',VALUE_DEFAULT,null),
                'message' => new external_value(PARAM_TEXT, 'Periods information',VALUE_DEFAULT,'ok'),
            )
        );
    }
}
