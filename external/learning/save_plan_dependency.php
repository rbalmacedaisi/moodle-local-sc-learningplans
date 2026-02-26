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
 * External Lib - Save learning plan dependency
 *
 * @package     local_sc_learningplans
 * @copyright   2026 Antigravity
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class save_plan_dependency_external extends external_api {

    public static function save_plan_dependency_parameters() {
        return new external_function_parameters(
            array(
                'learningplanid' => new external_value(PARAM_INT, 'ID of the parent learning plan'),
                'dependentplanid' => new external_value(PARAM_INT, 'ID of the dependent learning plan'),
            )
        );
    }

    public static function save_plan_dependency($learningplanid, $dependentplanid) {
        global $DB, $USER;

        if ($learningplanid == $dependentplanid) {
            throw new moodle_exception('cannotlinktoself', 'local_sc_learningplans');
        }

        $exists = $DB->record_exists('local_learningplan_deps', [
            'learningplanid' => $learningplanid,
            'dependentplanid' => $dependentplanid
        ]);

        if ($exists) {
            return ['status' => 'exists'];
        }

        $record = new stdClass();
        $record->learningplanid = $learningplanid;
        $record->dependentplanid = $dependentplanid;
        $record->usermodified = $USER->id;
        $record->timecreated = time();
        $record->timemodified = time();

        $DB->insert_record('local_learningplan_deps', $record);

        return ['status' => 'success'];
    }

    public static function save_plan_dependency_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
            )
        );
    }
}
