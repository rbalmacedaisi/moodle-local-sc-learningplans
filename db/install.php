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
 * This file is executed right after the install.xml
 *
 * @package     local_sc_learningplans
 * @category    string
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_sc_learningplans_install() {
    global $DB;
    $existingroles = $DB->get_record('role', ['shortname' => 'scteachrole']);
    // Install the roles system.
    if (!$existingroles) {
        $scteachrole = create_role('SC Learning Plan Teacher Role', 'scteachrole', '', 'teacher');

        // Now is the correct moment to install capabilities - after creation of legacy roles, but before assigning of roles!
        update_capabilities('local_sc_learningplans');
        // Set up the context levels where you can assign each role!
        set_role_contextlevels($scteachrole, [CONTEXT_SYSTEM]);
    }
}
