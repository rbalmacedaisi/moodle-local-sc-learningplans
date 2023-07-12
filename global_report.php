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
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once("$CFG->dirroot/local/sc_learningplans/tables/global_report_lp_table.php");

require_login();

$context = context_system::instance();
if (!has_any_capability(['local/sc_learningplans:teach'], $context)) {
    // If not have capability, then check if is admin.
    admin_externalpage_setup('local_sc_learningplans');
}

$download = optional_param('download', '', PARAM_ALPHA);

$context = context_system::instance();
$PAGE->set_context($context);
$pageurl = new moodle_url('/local/sc_learningplans/global_report.php');
$PAGE->set_url($pageurl);

$table = new global_report_lp_table('local_sc_learningplans_global_rep');

$table->is_downloading($download, 'report_learning_plan', get_string('report', 'local_sc_learningplans'));

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data. Print the page header!
    $PAGE->set_title(get_string('global_report', 'local_sc_learningplans'));
    $PAGE->set_heading(get_string('global_report', 'local_sc_learningplans'));
    echo $OUTPUT->header();
}

$table->define_baseurl($pageurl);

$table->out(20, true);  
die;


if (!$table->is_downloading()) {
    $PAGE->requires->js_call_amd('local_sc_learningplans/manage_learningplans', 'init');
    echo $OUTPUT->footer();
}
