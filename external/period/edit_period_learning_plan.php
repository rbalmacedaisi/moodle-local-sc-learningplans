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

class edit_period_learning_plan_external extends external_api {

    public static function edit_period_learning_plan_parameters() {
        return new external_function_parameters(
            array(
                'periodid'   => new external_value(
                    PARAM_INT,
                    'ID of the Period into learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'nameperiod'   => new external_value(
                    PARAM_RAW,
                    'Name of the period',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'vigency'  => new external_value(
                    PARAM_INT,
                    'ID of the role related to the suer',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function edit_period_learning_plan($periodid, $nameperiod, $vigency) {
        global $USER, $DB;

        $period = $DB->get_record('local_learning_periods', ['id' => $periodid]);
        if (!$period) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }

        $period->name = $nameperiod;
        $period->months = $vigency;
        $period->usermodified = $USER->id;
        $period->timemodified = time();
        $DB->update_record('local_learning_periods', $period);
        
        //Edit the career custom field based on the remaining periods
        $periods = $DB->get_records("local_learning_periods", ['learningplanid' => $period->learningplanid]);
        $careerduration = 0;
        foreach($periods as $p){
            $careerduration += intval($p->months);
        }
        
        $handler = local_sc_learningplans\customfield\learningplan_handler::create();
        $customfieldstobeupdated = new stdClass();
        $customfieldstobeupdated->id=$period->learningplanid;
        $customfieldstobeupdated->customfield_careerduration = $careerduration;
        $handler->instance_form_save($customfieldstobeupdated);
        //End Edit the career custom field based on the remaining periods

        return [
            'periodid' => $periodid
        ];
    }

    public static function edit_period_learning_plan_returns() {
        return new external_single_structure(
            array(
                'periodid' => new external_value(PARAM_INT, 'Period ID')
            )
        );
    }
}
