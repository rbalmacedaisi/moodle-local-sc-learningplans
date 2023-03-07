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
 * External Lib - Delete learning period
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class delete_period_learning_plan_external extends external_api {

    public static function delete_period_learning_plan_parameters() {
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
                    'ID of the period to delete',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function delete_period_learning_plan($learningplan, $periodid) {
        global $USER, $DB;

        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplan]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }

        $isdelete = null;
        $result = false;

        $isdelete = $DB->delete_records('local_learning_periods', ['id' => $periodid]);

        if ($isdelete) {
            $result = true;
            
            //Edit the career custom field based on the remaining periods
            $periods = $DB->get_records("local_learning_periods", ['learningplanid' => $learningplan]);
            $careerduration = 0;
            foreach($periods as $p){
                $careerduration += intval($p->months);
            }
            
            $handler = local_sc_learningplans\customfield\learningplan_handler::create();
            $customfieldstobeupdated = new stdClass();
            $customfieldstobeupdated->id=$learningplan;
            $customfieldstobeupdated->customfield_careerduration = $careerduration;
            $handler->instance_form_save($customfieldstobeupdated);
            //End Edit the career custom field based on the remaining periods
        }

        $learningplanrecord->periodcount -= 1;
        $learningplanrecord->timemodified = time();
        $DB->update_record('local_learning_plans', $learningplanrecord);
        
        return [
            'isdelete' => $result,
        ];
    }

    public static function delete_period_learning_plan_returns() {
        return new external_single_structure(
            array(
                'isdelete' => new external_value(PARAM_BOOL,  'If the course is delete succesfull'),
            )
        );
    }
}
