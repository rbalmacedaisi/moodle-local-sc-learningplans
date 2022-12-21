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
 * Local Lib - Common function for users
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto < G>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get allowed roles for this plugin
 *
 * @return array
 */
function sc_learningplan_get_roles() {
    global $DB;
    $roles = $DB->get_records('role');
    foreach ($roles as $key => &$role) {
        if (
            $role->shortname != 'manager' &&
            $role->shortname != 'editingteacher' &&
            $role->shortname != 'teacher' &&
            $role->shortname != 'student'
        ) {
            unset($roles[$key]);
            continue;
        }
        $role->strname = get_string($role->shortname, 'local_sc_learningplans');
    }
    return $roles;
}
