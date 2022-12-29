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
 * External Lib - Edit new learning plan
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sc_learningplans\event\learningplan_updated;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

class edit_learning_plan_external extends external_api {

    public static function edit_learning_plan_parameters() {
        return new external_function_parameters(
            array(
                'learningid' => new external_value(PARAM_INT, 'ID of the learning plan'),
                'learningshortname' => new external_value(PARAM_TEXT, 'Unique shortname'),
                'learningname' => new external_value(PARAM_TEXT, 'Name of the learning plan'),
                'fileimage' => new external_value(PARAM_INT, 'Image itemid provide by filemanager form element'),
                'description' => new external_value(PARAM_RAW, 'Description of the learning plan'),
            )
        );
    }

    public static function edit_learning_plan($learningid, $learningshortname, $learningname, $fileimage, $description) {
        global $DB;
        // Check if LP Exist.
        $learningplan = $DB->get_record('local_learning_plans', ['id' => $learningid]);
        if (!$learningplan) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        // Check if other LP Have the received learning shortid.
        $otherlearningshortid = $DB->get_records_sql(
            'SELECT *
            FROM {local_learning_plans}
            WHERE shortname = :shortname AND id <> :id',
            [
                'shortname' => $learningshortname,
                'id' => $learningid
            ]
        );
        if ($otherlearningshortid) {
            throw new moodle_exception('otherlpsameshortid', 'local_sc_learningplans');
        }
        if ($fileimage) {
            $itemid = $fileimage;
            $context = context_system::instance();
            file_save_draft_area_files(
                $itemid,
                $context->id,
                'local_sc_learningplans',
                'learningplan_image',
                $learningid,
                array('subdirs' => 0, 'maxfiles' => 1)
            );
        }
        $description = strip_tags($description);
        $learningplan->shortname = $learningshortname;
        $learningplan->name = $learningname;
        $learningplan->description = $description;
        $learningplan->timemodified = time();
        $DB->update_record('local_learning_plans', $learningplan);
        return [
            'learningplanid' => $learningid
        ];
    }

    public static function edit_learning_plan_returns() {
        return new external_single_structure(
            array(
                'learningplanid' => new external_value(PARAM_INT, 'Learning Plan ID')
            )
        );
    }
}
