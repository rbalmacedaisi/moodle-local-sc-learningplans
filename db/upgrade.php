<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is executed when upgrade
 *
 * @package     local_sc_learningplans
 * @category    string
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/libs/learningplanlib.php');

function xmldb_local_sc_learningplans_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2023011900) {
        // Define table local_learning_plans to be created.
        $table = new xmldb_table('local_learning_plans');

        // Adding fields to table local_learning_plans.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '64', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('coursecount', XMLDB_TYPE_INTEGER, '9', null, null, null, null);
        $table->add_field('usercount', XMLDB_TYPE_INTEGER, '9', null, null, null, null);
        $table->add_field('hasperiod', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
        $table->add_field('periodcount', XMLDB_TYPE_INTEGER, '9', null, null, null, '0');
        $table->add_field('enroltype', XMLDB_TYPE_CHAR, '9', null, null, null, null);
        $table->add_field('requirements', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table local_learning_plans.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_learning_plans.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_learning_courses to be created.
        $table = new xmldb_table('local_learning_courses');
        // Adding fields to table local_learning_courses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('learningplanid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('isrequired', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('position', XMLDB_TYPE_INTEGER, '9', null, null, null, null);
        $table->add_field('credits', XMLDB_TYPE_INTEGER, '9', null, null, null, null);
        $table->add_field('periodid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table local_learning_courses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_learning_courses.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_learning_users to be created.
        $table = new xmldb_table('local_learning_users');

        // Adding fields to table local_learning_users.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('learningplanid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('userroleid', XMLDB_TYPE_INTEGER, '9', null, null, null, null);
        $table->add_field('userrolename', XMLDB_TYPE_CHAR, '64', null, null, null, null);
        $table->add_field('currentperiodid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('waitingperiod', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('groupname', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('nextperiodid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table local_learning_users.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_learning_users.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_learning_periods to be created.
        $table = new xmldb_table('local_learning_periods');

        // Adding fields to table local_learning_periods.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('learningplanid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('months', XMLDB_TYPE_INTEGER, '9', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table local_learning_periods.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_learning_periods.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_learning_report to be created.
        $table = new xmldb_table('local_learning_report');

        // Adding fields to table local_learning_report.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid_lp', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_field('learningplanid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('countcurrentcourse', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('lastcurrentcourse', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lastcurrentcoursename', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('lastcompletedcourse', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lastcompletedcoursename', XMLDB_TYPE_CHAR, '128', null, null, null, null);
        $table->add_field('lpprogress', XMLDB_TYPE_CHAR, '6', null, null, null, null);
        $table->add_field('lastperiodid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('lastperiodname', XMLDB_TYPE_CHAR, '128', null, null, null, null);

        // Adding keys to table local_learning_report.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_learning_report.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023011900, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023022000) {
        $managerole = $DB->get_record('role', ['shortname' => 'scmanagerrole']);
        if ($managerole) {
            delete_role($managerole->id);
        }
        $techrole = $DB->get_record('role', ['shortname' => 'scteachrole']);
        if ($techrole) {
            $techrole->archetype = 'teacher';
            $DB->update_record('role', $techrole);
        }
        update_capabilities('local_sc_learningplans');
        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023022000, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023022001) {
        $counts = $DB->get_records_sql('SELECT lp.*, lc.learningplanid, count(c.id) count FROM {local_learning_courses} lc
        LEFT JOIN {course} c ON (c.id = lc.courseid)
        LEFT JOIN {local_learning_plans} lp ON (lp.id = lc.learningplanid)
        WHERE lc.isrequired = 1
        GROUP BY lc.learningplanid');
        foreach ($counts as &$count) {
            $count->coursecount = $count->count;
            unset($count->learningplanid);
            unset($count->count);
            $DB->update_record('local_learning_plans', $count);
        }
        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023022001, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023030200) {

        $learningdeleteduser = $DB->get_records_sql(
            "SELECT lu.*, u.firstname, u.lastname, u.email, u.deleted
                FROM {local_learning_users} lu
                JOIN {user} u ON (u.id = lu.userid AND u.deleted = 1)
            "
        );
        foreach ($learningdeleteduser as $value) {
            $DB->delete_records('local_learning_users', ['userid' => $value->userid]);
        }
        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023030200, 'local', 'sc_learningplans');
    }

    if ($oldversion < 2023032204) {

        $learningdeleteduser = $DB->get_records_sql(
            "SELECT lr.*, u.firstname, u.lastname, u.email, u.deleted
                FROM {local_learning_report} lr
                JOIN {user} u ON (u.id = lr.userid AND u.deleted = 1)
            "
        );
        foreach ($learningdeleteduser as $value) {
            $DB->delete_records('local_learning_report', ['userid' => $value->userid]);
        }
        learning_plans_recount_users();
        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023032204, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023032500) {

        // Define table local_learningplan_rel_cours to be created.
        $table = new xmldb_table('local_learningplan_rel_cours');

        // Adding fields to table local_learningplan_rel_cours.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('origin_record_id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('destination_record_id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_learningplan_rel_cours.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for local_learningplan_rel_cours.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023032500, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023101900) {

        // Define field hassubperiods to be added to local_learning_periods.
        $table = new xmldb_table('local_learning_periods');
        $field = new xmldb_field('hassubperiods', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field hassubperiods.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field subperiodid to be added to local_learning_courses.
        $table = new xmldb_table('local_learning_courses');
        $field = new xmldb_field('subperiodid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Conditionally launch add field subperiodid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define table local_learning_subperiods to be created.
        $table = new xmldb_table('local_learning_subperiods');

        // Adding fields to table local_learning_subperiods.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, 'subperiodo');
        $table->add_field('learningplanid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('periodid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('position', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_learning_subperiods.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for local_learning_subperiods.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Define field currentsubperiodid to be added to local_learning_users.
        $table = new xmldb_table('local_learning_users');
        $field = new xmldb_field('currentsubperiodid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Conditionally launch add field currentsubperiodid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023101900, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023112400) {

        // Define table local_learning_course_progre to be created.
        $table = new xmldb_table('local_learning_course_progre');

        // Adding fields to table local_learning_course_progre.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('periodid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('periodname', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, 'unnamed');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('coursename', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, 'unnamed');
        $table->add_field('progress', XMLDB_TYPE_NUMBER, '3, 2', null, null, null, '0.0');
        $table->add_field('grade', XMLDB_TYPE_NUMBER, '3, 2', null, XMLDB_NOTNULL, null, '0.0');
        $table->add_field('credits', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('prerequisites', XMLDB_TYPE_CHAR, '64', null, null, null, null);
        $table->add_field('tc', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('practicalhours', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('teoricalhours', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_learning_course_progre.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for local_learning_course_progre.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023112400, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023112401) {

        // Define field learningplanid to be added to local_learning_course_progre.
        $table = new xmldb_table('local_learning_course_progre');
        $field = new xmldb_field('learningplanid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');

        // Conditionally launch add field learningplanid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023112401, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023112402) {

        // Define field status to be added to local_learning_course_progre.
        $table = new xmldb_table('local_learning_course_progre');
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'learningplanid');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023112402, 'local', 'sc_learningplans');
    }
    if ($oldversion < 2023112700) {

        // Define table local_learning_course_progre to be dropped.
        $table = new xmldb_table('local_learning_course_progre');

        // Conditionally launch drop table for local_learning_course_progre.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Sc_learningplans savepoint reached.
        upgrade_plugin_savepoint(true, 2023112700, 'local', 'sc_learningplans');
    }




    return true;
}
