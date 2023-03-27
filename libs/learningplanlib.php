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
 * Local Lib - Common function for learning plan
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto < G>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');

function send_email_lp_updated($learningplanid) {
    $config = get_config('local_sc_learningplans');
    if ($config->sendupdatelp) {
        global $DB, $PAGE;
        // Get all users with role 'student' in learningplanid.
        $users = $DB->get_records_sql(
            'SELECT
                u.*,
                lpu.userroleid,
                r.shortname as roleshortname
            FROM {local_learning_users} lpu
            JOIN {role} r ON (r.id = lpu.userroleid AND r.shortname = \'student\')
            JOIN {user} u ON (u.id = lpu.userid)
            WHERE lpu.learningplanid = :learningplanid',
            [
                'learningplanid' => $learningplanid
            ]
        );
        $subject = $config->emailsubjectupdatelp;
        list($lpname, $firstcoursename, $firsturlcourse) = get_replace_lpdata($learningplanid);
        foreach ($users as $user) {
            $templatehtml   = $config->templatemailupdatelp;
            $fullusername = fullname($user);
            $templatehtml = replace_template_html_email(
                $templatehtml,
                $fullusername,
                $lpname,
                $firstcoursename,
                $firsturlcourse
            );
            try {
                $context = context_system::instance();
                $PAGE->set_context($context); // Need context to correct use of email_to_user function.
                email_to_user($user, core_user::get_noreply_user(), $subject, strip_tags($templatehtml), $templatehtml);
            } catch (\Throwable $th) {
                // Some error, trycatch to not stop the execution.
                mtrace($th->getMessage());
            }
        }
    }
}

/**
 * Count users from LP and save it.
 */
function learning_plans_recount_users() {
    global $DB;
    $counts = $DB->get_records_sql('SELECT lp.*, lu.learningplanid, count(u.id) count 
        FROM {local_learning_plans} lp
        LEFT JOIN {local_learning_users} lu ON (lp.id = lu.learningplanid)
        LEFT JOIN {user} u ON (u.id = lu.userid AND u.deleted = 0)
        GROUP BY lu.learningplanid');

    foreach ($counts as &$count) {
        $count->usercount = $count->count;
        unset($count->learningplanid);
        unset($count->count);
        $DB->update_record('local_learning_plans', $count);
    }
}