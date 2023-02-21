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
        $counts = $DB->get_records_sql('SELECT count(c.id) count, lp.*, lc.learningplanid FROM {local_learning_courses} lc
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
    return true;
}
