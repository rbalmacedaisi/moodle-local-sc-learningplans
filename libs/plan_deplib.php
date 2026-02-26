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
 * Dependency Library - Functions for handling learning plan dependencies
 *
 * @package     local_sc_learningplans
 * @copyright   2026 Antigravity
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/external/user/add_learning_user.php');

/**
 * Trigger auto-enrollment for dependent plans.
 *
 * @param int $learningplanid
 * @param int $userid
 * @param int $roleid
 * @param string|null $group
 */
function sc_learningplan_trigger_dependencies($learningplanid, $userid, $roleid, $group = null) {
    global $DB, $CFG;

    // Static variable to prevent circular dependency infinite loops.
    static $processed = [];
    $key = $userid . '_' . $learningplanid;
    if (isset($processed[$key])) {
        return;
    }
    $processed[$key] = true;

    $dependencies = $DB->get_records('local_learningplan_deps', ['learningplanid' => $learningplanid]);
    foreach ($dependencies as $dep) {
        $dependentid = $dep->dependentplanid;
        
        // Check if user is already in that plan.
        $exists = $DB->record_exists('local_learning_users', [
            'learningplanid' => $dependentid,
            'userid' => $userid
        ]);

        if (!$exists) {
            try {
                // Use the external API method to ensure all logic (enrolment, emails, etc.) is applied.
                add_learning_user_external::add_learning_user($dependentid, $userid, $roleid, null, $group);
                
                // Recursively check for dependencies of the dependent plan.
                sc_learningplan_trigger_dependencies($dependentid, $userid, $roleid, $group);
            } catch (Exception $e) {
                // Log error but don't stop the main process.
                mtrace("Error in auto-enrollment for LP $dependentid: " . $e->getMessage());
            }
        }
    }
}

/**
 * Get all linked plans for a learning plan.
 *
 * @param int $learningplanid
 * @return array
 */
function sc_learningplan_get_linked_plans($learningplanid) {
    global $DB;
    return $DB->get_records_sql(
        "SELECT d.id, d.dependentplanid, lp.name, lp.shortname
         FROM {local_learningplan_deps} d
         JOIN {local_learning_plans} lp ON lp.id = d.dependentplanid
         WHERE d.learningplanid = :lp",
        ['lp' => $learningplanid]
    );
}
