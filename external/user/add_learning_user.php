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
 * External Lib - Add new learning user
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sc_learningplans\event\user_lpenrolment_created;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');

class add_learning_user_external extends external_api {

    public static function add_learning_user_parameters() {
        return new external_function_parameters(
            array(
                'learningplan'   => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'userid'   => new external_value(
                    PARAM_INT,
                    'ID of the user to add',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'roleid'  => new external_value(
                    PARAM_INT,
                    'ID of the role related to the suer',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'currentperiodid'  => new external_value(
                    PARAM_INT,
                    'Current period id',
                    VALUE_DEFAULT,
                    null
                ),
                'group'  => new external_value(
                    PARAM_TEXT,
                    'Group name',
                    VALUE_DEFAULT,
                    null,
                    NULL_ALLOWED
                ),

            )
        );
    }

    public static function add_learning_user($learningplan, $userid, $roleid, $currentperiodid, $group) {
        global $DB, $USER;
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplan]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        
        $tableusers = 'local_learning_users';

        $learninguserexist = $DB->get_record($tableusers, [
            'learningplanid' => $learningplan,
            'userid' => $userid
        ]);
        if ($learninguserexist) {
            throw new moodle_exception('learninguserexist', 'local_sc_learningplans');
        }
        $roles = $DB->get_records('role');
        $roleshortname = $roles[$roleid]->shortname;
        // Create new record related to user!
        $learninguserexist = new stdClass();
        $learninguserexist->learningplanid = $learningplan;
        $learninguserexist->userid = $userid;
        $learninguserexist->userroleid = $roleid;
        $learninguserexist->userrolename = $roleshortname;
        $learninguserexist->currentperiodid = $currentperiodid;
        $learninguserexist->groupname = $group;
        $learninguserexist->usermodified = $USER->id;
        $learninguserexist->timemodified = time();
        $learninguserexist->timecreated = time();
        $learninguserexist->id = $DB->insert_record($tableusers, $learninguserexist);
        
        // Enrol in first course and in all optional course.
        enrol_user_in_learningplan_courses($learningplan, $userid, $roleid, $group);
        // enrol_user_in_learningplan_courses(34, 3, 5, '');

        // Now, if the role is manager, enrol the user in role system to give capabilities!
        $context = context_system::instance();
        if ($roleshortname == 'manager') { // The role is manager. Add to this plugin custom manager role!
            $customrole = $DB->get_record('role', ['shortname' => 'scmanagerrole']);
            role_assign($customrole->id, $userid, $context->id);
        } else if ($roleshortname != 'student') { // If not student and not manager, add custom teacher role!
            $customrole = $DB->get_record('role', ['shortname' => 'scteachrole']);
            role_assign($customrole->id, $userid, $context->id);
        }

        send_email_user_enroled($learningplan, $userid, $roleid);

        $learningplanrecord->usercount++;
        $DB->update_record('local_learning_plans', $learningplanrecord);
        return [
            'id' => $learninguserexist->id,
        ];
    }

    public static function add_learning_user_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_TEXT,  'Record ID'),
            )
        );
    }
}
