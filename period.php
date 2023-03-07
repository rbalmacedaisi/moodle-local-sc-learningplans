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

require_login();
$context = context_system::instance();
admin_externalpage_setup('local_sc_learningplans');

$PAGE->set_pagelayout('base');
$PAGE->set_context($context);

$learningplanid = required_param('id', PARAM_INT);

// Check if plan has periods.
$periods = $DB->get_records("local_learning_periods", ['learningplanid' => $learningplanid]);

$hasperiods = false;
if (!empty($periods)) {
    $hasperiods = true;
}


$PAGE->set_url(new moodle_url('/local/sc_learningplans/period.php', ['id' => $learningplanid]));
$PAGE->set_title(get_string('edit_plan', 'local_sc_learningplans'));
$PAGE->set_heading(get_string('edit_plan', 'local_sc_learningplans'));

if (is_siteadmin()) {
    $PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
    $PAGE->navbar->add(get_string('pluginname', 'local_sc_learningplans'), new moodle_url('/local/sc_learningplans/index.php'));
}
$PAGE->navbar->add(
    get_string('edit_period', 'local_sc_learningplans'),
    new moodle_url('/local/sc_learningplans/period.php', ['id' => $learningplanid])
);

$templatedata = [
    'hasperiods' => $hasperiods,
    'listperiods' => array_values($periods),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_sc_learningplans/edit_learning_periods', $templatedata);
$PAGE->requires->js_call_amd('local_sc_learningplans/edit_period', 'init', ['learningid' => $learningplanid]);

echo $OUTPUT->footer();
