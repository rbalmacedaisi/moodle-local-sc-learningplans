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
 * External Lib - Add course relations
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');

class add_course_relations_external extends external_api {

    public static function add_course_relations_parameters() {
        return new external_function_parameters(
            array(
                'recordid' => new external_value(
                    PARAM_INT,
                    'ID of the record to related',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'records' => new external_value(
                    PARAM_TEXT,
                    'ID of the records separated by comma',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
            )
        );
    }

    public static function add_course_relations($recordid, $records) {
        global $DB, $USER;
        $courserecord = $DB->get_record('local_learning_courses', ['id' => $recordid]);
        if (!$courserecord) {
            throw new moodle_exception('coursenotexist', 'local_sc_learningplans');
        }
        $learningplanid = $courserecord->learningplanid;
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplanid]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        $records = explode(',', $records);

        foreach ($records as $newrecord) {
            if($DB->get_record('local_learningplan_rel_cours',['origin_record_id'=>$recordid,'destination_record_id'=>$newrecord])){
                continue;
            }
            
            $newrecord = trim($newrecord);
            $insertrelation = new stdClass();
            $insertrelation->origin_record_id = $recordid;
            $insertrelation->destination_record_id = $newrecord;
            $insertrelation->usermodified = $USER->id;
            $insertrelation->timecreated = time();
            $insertrelation->timemodified = time();
            // Add relation X => Y.
            $DB->insert_record('local_learningplan_rel_cours', $insertrelation, true, true);

            $inverserelation = $insertrelation;
            $inverserelation->destination_record_id = $recordid;
            $inverserelation->origin_record_id = $newrecord;
            // Inverse the relation, Y => X.
            $DB->insert_record('local_learningplan_rel_cours', $inverserelation, true, true);
        }
        // Re enroll all lp users.
        $users = $DB->get_records_sql(
            'SELECT llu.* FROM {local_learning_users} llu
            JOIN {user} u ON (u.id = llu.userid)
            WHERE llu.learningplanid = :learningplanid', ['learningplanid' => $learningplanid]);
        foreach ($users as $user) {
            $userid = $user->userid;
            $roleid = $user->userroleid;
            enrol_user_in_learningplan_courses($learningplanid, $userid, $roleid, $user->groupname);
        }
        $learningplanrecord->timemodified = time();
        $DB->update_record('local_learning_plans', $learningplanrecord);

        return [
            'done' => true,
        ];
    }

    public static function add_course_relations_returns() {
        return new external_single_structure(
            array(
                'done' => new external_value(PARAM_BOOL, 'If done relation')
            )
        );
    }
}
