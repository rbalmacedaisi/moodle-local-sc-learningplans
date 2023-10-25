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
 * External Lib - Add new learning period
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class addperiod_learning_plan_external extends external_api {

    public static function addperiod_learning_plan_parameters() {
        return new external_function_parameters(
            array(
                'learningplanid'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'name'   => new external_value(
                    PARAM_TEXT,
                    'Name of period',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'vigency'  => new external_value(
                    PARAM_INT,
                    'ID of the role related to the user',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'hassubperiods' => new external_value(PARAM_BOOL, 'Boolean indicating if the period has sub periods'),
                'subperiods' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'Sub period name'),
                            'position' => new external_value(PARAM_INT, 'subperiod position in the period')
                        )
                    )
                )
            )
        );
    }

    public static function addperiod_learning_plan($learningplanid, $name, $vigency,$hassubperiods,$subperiods) {
        global $DB, $USER;
        
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplanid]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
    
        $tableperiod = 'local_learning_periods';

        $createperiod = new stdClass();
        $createperiod->learningplanid = $learningplanid;
        $createperiod->name = $name;
        $createperiod->months = $vigency;
        $createperiod->hassubperiods = $hassubperiods;
        $createperiod->usermodified = $USER->id;
        $createperiod->timecreated = time();
        $createperiod->timemodified = time();
        $createperiod->id = $DB->insert_record($tableperiod, $createperiod);
        
        //If the period has subperiods, create the subperiods
        if($hassubperiods){
            $subperiods = [['name'=>'BIMESTRE 1','position'=>0],['name'=>'BIMESTRE 2','position'=>1]];
            foreach($subperiods as $subperiod){
                $createsubperiod = new stdClass();
                $createsubperiod->learningplanid = $learningplanid;
                $createsubperiod->name = $subperiod['name'];
                $createsubperiod->periodid = $createperiod->id;
                $createsubperiod->position = $subperiod['position'];
                $createsubperiod->usermodified = $USER->id;
                $createsubperiod->timecreated = time();
                $createsubperiod->timemodified = time();
                $createsubperiod->id = $DB->insert_record('local_learning_subperiods', $createsubperiod);
            }
        }

        $learningplanrecord->periodcount += 1;
        $learningplanrecord->timemodified = time();
        $DB->update_record('local_learning_plans', $learningplanrecord);
        
        //Edit the career custom field based on the remaining periods
        $periods = $DB->get_records("local_learning_periods", ['learningplanid' => $learningplanid]);
        $careerduration = 0;
        foreach($periods as $p){
            $careerduration += intval($p->months);
        }
        
        $handler = local_sc_learningplans\customfield\learningplan_handler::create();
        $customfieldstobeupdated = new stdClass();
        $customfieldstobeupdated->id=$learningplanid;
        $customfieldstobeupdated->customfield_careerduration = $careerduration;
        $handler->instance_form_save($customfieldstobeupdated);
        //Edit the career custom field based on the remaining periods
        
        return [
            'id' => $createperiod->id,
        ];
    }

    public static function addperiod_learning_plan_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_TEXT,  'Record ID'),
            )
        );
    }
}
