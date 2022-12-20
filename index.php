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
 * Plugin Page - Manage Learning Plans
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once("$CFG->libdir/tablelib.php");
require_once("$CFG->dirroot/local/sc_learningplans/tables/table_manage_learnings.php");

require_login();

$context = context_system::instance();
if (!has_any_capability(['local/sc_learningplans:manage', 'local/sc_learningplans:teach'], $context)) {
    // If not have capability, then check if is admin.
    admin_externalpage_setup('local_sc_learningplans');
}
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/sc_learningplans/index.php');

$download = optional_param('download', '', PARAM_ALPHA);
$table = new table_manage_learnings('local_learning_plans');

$table->is_downloading($download, 'learningplans', get_string('pluginname', 'local_sc_learningplans'));

if (!$table->is_downloading()) {
    $PAGE->set_title(get_string('pluginname', 'local_sc_learningplans'));
    $PAGE->set_heading(get_string('pluginname', 'local_sc_learningplans'));

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('local_sc_learningplans/manage_learningplans', []);
}

$table->define_baseurl("$CFG->wwwroot/local/sc_learningplans/index.php");
$table->out(10, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
