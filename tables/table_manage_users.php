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
 * Plugin table - Show LP users
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class table_manage_users extends table_sql {

    /**
     * Constructor
     *
     * @param string $uniqueid
     * @param integer $learningplanid
     */
    public function __construct(string $uniqueid, int $learningplanid) {
        parent::__construct($uniqueid);

        $this->learningplanid = $learningplanid;

        // Define columns.
        $columns = [
            'id'            => get_string('id', 'local_sc_learningplans'),
            'fullname'          => get_string('name', 'local_sc_learningplans'),
            'email'         => get_string('email_user', 'local_sc_learningplans'),
            'roles_user'    => get_string('roles_user', 'local_sc_learningplans'),
            'action_user'   => get_string('action_user', 'local_sc_learningplans'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // Table configuration.
        $this->set_attribute('id', $this->uniqueid);
        $this->set_attribute('cellspacing', '0');

        $this->initialbars(true);
        $this->collapsible(false);

        // Initialize table SQL properties.
        $this->init_sql();
    }

    /**
     * Initializes table SQL properties
     *
     * @return void
     */
    protected function init_sql() {
        $fields = 'lu.*, u.firstname, u.lastname, u.email,
        r.shortname as rolename,
        u.firstnamephonetic,
        u.lastnamephonetic,
        u.middlename,
        u.alternatename';

        $from = '{local_learning_users} lu
        JOIN {user} u ON (u.id = lu.userid)
        JOIN {role} r ON (r.id = lu.userroleid)';

        // Report search.
        $where = 'lu.learningplanid = :learningplanid';
        $params = [
            'learningplanid' => $this->learningplanid
        ];

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql('SELECT COUNT(1) FROM ' . $from . ' WHERE ' . $where, $params);
    }

    /**
     * Generate the display of the roles_user column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_roles_user($values) {
        return get_string($values->rolename, 'local_sc_learningplans');
    }

    /**
     * Generate the display of the action_user column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_action_user($values) {
        $html = 'a';
        return $html;
    }
}
