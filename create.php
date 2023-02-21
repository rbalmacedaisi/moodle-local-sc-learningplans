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
 * Plugin Page - Create Learning Plans
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/forms/createlp_form.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/courselib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_login();
$context = context_system::instance();
admin_externalpage_setup('local_sc_learningplans');
$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/local/sc_learningplans/create.php');
$PAGE->set_title(get_string('pluginname', 'local_sc_learningplans'));
$PAGE->set_heading(get_string('pluginname', 'local_sc_learningplans'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwidth');

$formimagepicker = new createlp_form_picker();
$formeditor = new createlp_form_editor();
$groups = get_groups_from_courses();
$optionsperiod = [
    ['id' => '1', 'value' => '1'],
    ['id' => '2', 'value' => '2'],
    ['id' => '3', 'value' => '3'],
    ['id' => '4', 'value' => '4'],
    ['id' => '5', 'value' => '5'],
    ['id' => '6', 'value' => '6'],
    ['id' => '7', 'value' => '7'],
    ['id' => '8', 'value' => '8'],
    ['id' => '9', 'value' => '9'],
    ['id' => '10', 'value' => '10'],
    ['id' => '11', 'value' => '11'],
    ['id' => '12', 'value' => '12'],
];

$allcourses = get_courses();
unset($allcourses[1]);

$allusers = $DB->get_records('user', ['suspended' => 0, 'deleted' => 0]);
unset($allusers[1]); // Guest user!

$roles = sc_learningplan_get_roles();

$config = get_config('local_sc_learningplans');

$userprofilefields = $DB->get_records('user_info_field');

$maintemplatedata = [
    'formimagpicker' => $formimagepicker->render(),
    'formeditor' => $formeditor->render(),
    'optionsperiod' => $optionsperiod,
    'courses' => array_values($allcourses),
    'allusers' => array_values($allusers),
    'roles' => array_values($roles),
    'groups' => array_values($groups),
    'userprofilefields' => array_values($userprofilefields),
    'cancelurl' => $CFG->wwwroot.'/local/sc_learningplans/index.php'

];
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_sc_learningplans/create_learningplan', $maintemplatedata);
$PAGE->requires->js_call_amd('local_sc_learningplans/create_learningplan', 'init', [
    'str_name_period_config' => $config->periodnamesetting ?? get_string('period', 'local_sc_learningplans'),
    'default_period_months' => $config->default_period_months ?? 4,
]);

echo $OUTPUT->footer();
