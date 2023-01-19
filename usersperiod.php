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
require_once("$CFG->dirroot/local/sc_learningplans/tables/table_manage_users_waiting_period.php");

require_login();
$context = context_system::instance();
if (!has_any_capability(['local/sc_learningplans:manage', 'local/sc_learningplans:teach'], $context)) {
    // If not have capability, then check if is admin.
    admin_externalpage_setup('local_sc_learningplans');
}

$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);

$learningplan = $DB->get_record('local_learning_plans', array('id' => $id));
if (!$learningplan) {
    redirect(new moodle_url('/local/sc_learningplans/index.php'));
}

if ($userid) {
    $usertoaprove = $DB->get_record('local_learning_users', array(
        'learningplanid' => $id,
        'userid' => $userid,
        'waitingperiod' => 1,
    ));
    if ($usertoaprove) {
        $usertoaprove->currentperiodid = $usertoaprove->nextperiodid;
        $usertoaprove->waitingperiod = null;
        $usertoaprove->nextperiodid = null;
        $DB->update_record('local_learning_users', $usertoaprove);
        // Enroll in the first course of new period.
        require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');
        enrol_user_in_learningplan_courses($id, $userid, $usertoaprove->userroleid, $usertoaprove->groupname);
    }
}


$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/sc_learningplans/usersperiod.php', ['id' => $id]));
if (is_siteadmin()) {
    $PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
    $PAGE->navbar->add(get_string('pluginname', 'local_sc_learningplans'), new moodle_url('/local/sc_learningplans/index.php'));
}
$PAGE->navbar->add(
    get_string('manageuser', 'local_sc_learningplans'),
    new moodle_url('/local/sc_learningplans/usersperiod.php', ['id' => $id])
);

$download = optional_param('download', '', PARAM_ALPHA);
$table = new table_manage_users_waiting_period('table_manage_users_waiting_period', $id);


$table->is_downloading($download, 'learningplans', get_string('pluginname', 'local_sc_learningplans'));
$templatedata = [];
if (!$table->is_downloading()) {
    $PAGE->set_title(get_string('manageuser', 'local_sc_learningplans'));
    $PAGE->set_heading(get_string('manageuser', 'local_sc_learningplans'));
    echo $OUTPUT->header();

    echo $OUTPUT->render_from_template('local_sc_learningplans/manage_users_approve', $templatedata);
}

$table->define_baseurl("$CFG->wwwroot/local/sc_learningplans/usersperiod.php");
$table->out(10, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}

