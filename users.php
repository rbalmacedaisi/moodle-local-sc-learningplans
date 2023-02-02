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
 * Plugin Page - Manage users in the learning plan
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/courselib.php');
require_once("$CFG->dirroot/local/sc_learningplans/tables/table_manage_users.php");

require_login();
$context = context_system::instance();
if (!has_any_capability(['local/sc_learningplans:manage', 'local/sc_learningplans:teach'], $context)) {
    // If not have capability, then check if is admin.
    admin_externalpage_setup('local_sc_learningplans');
}


$learningplanid = required_param('id', PARAM_INT);
$searchuser = optional_param('searchUser', null, PARAM_TEXT);

$learningplan = $DB->get_record('local_learning_plans', array('id' => $learningplanid));
if (!$learningplan) {
    redirect(new moodle_url('/local/sc_learningplans/index.php'));
}

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/sc_learningplans/users.php', ['id' => $learningplanid]));
if (is_siteadmin()) {
    $PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
    $PAGE->navbar->add(get_string('pluginname', 'local_sc_learningplans'), new moodle_url('/local/sc_learningplans/index.php'));
}
$PAGE->navbar->add(
    get_string('manageuser', 'local_sc_learningplans'),
    new moodle_url('/local/sc_learningplans/users.php', ['id' => $learningplanid])
);


$hasmanualenrol = $learningplan->enroltype == 1; // If enroltype == 1 is manual enrol.
$hasperiods = (bool) $learningplan->hasperiod;

$allusers = $DB->get_records('user', ['suspended' => 0, 'deleted' => 0]);
unset($allusers[1]); // Guest user!

$roles = sc_learningplan_get_roles();
$groups = get_groups_from_courses($learningplanid);

$learningusers = $DB->get_records_sql(
    "SELECT lu.*, u.firstname, u.lastname, u.email
        FROM {local_learning_users} lu
        JOIN {user} u ON (u.id = lu.userid)
        WHERE lu.learningplanid = :learningplanid",
    [
        'learningplanid' => $learningplanid
    ]
);

foreach ($learningusers as &$user) {
    $userid = $user->userid;
    if (isset($allusers[$userid])) {
        unset($allusers[$userid]);
        $roleshortname = $roles[$user->userroleid]->shortname;
        $user->rolename = get_String($roleshortname, 'local_sc_learningplans');
    }
}

$templatedata = [
    'allusers' => array_values($allusers),
    'roles' => array_values($roles),
    'learningusers' => $learningusers,
    'groups' => array_values($groups),
    'learningplanid' => $learningplanid,
    'hasperiods' => $hasperiods,
    'hasmanualenrol' => $hasmanualenrol,
    'searchUser' => $searchuser,
];

$download = optional_param('download', '', PARAM_ALPHA);
$table = new table_manage_users('local_learning_users', $learningplanid, $searchuser);

$table->is_downloading($download, 'learningplans', get_string('pluginname', 'local_sc_learningplans'));

if (!$table->is_downloading()) {
    $PAGE->set_title(get_string('manageuser', 'local_sc_learningplans'));
    $PAGE->set_heading(get_string('manageuser', 'local_sc_learningplans'));
    echo $OUTPUT->header();

    echo $OUTPUT->render_from_template('local_sc_learningplans/manage_users', $templatedata);
    $PAGE->requires->js_call_amd('local_sc_learningplans/manage_users', 'init', ['learningplanid' => $learningplanid]);
}

$table->define_baseurl("$CFG->wwwroot/local/sc_learningplans/users.php");
$table->out(10, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
