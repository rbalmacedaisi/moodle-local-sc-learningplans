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
require_once($CFG->dirroot . '/local/sc_learningplans/forms/createlp_form.php');

require_login();
$context = context_system::instance();

if (!has_any_capability(['local/sc_learningplans:manage'], $context)) {
    // If not have capability, then check if is admin!
    admin_externalpage_setup('local_sc_learningplans');
}

$PAGE->set_context($context);

$learningplanid = required_param('id', PARAM_INT);

$learningplan = $DB->get_record('local_learning_plans', array('id' => $learningplanid));
if (!$learningplan) {
    redirect(new moodle_url('/local/sc_learningplans/index.php'));
}

$PAGE->set_url(new moodle_url('/local/sc_learningplans/duplicate.php', ['id' => $learningplanid]));
$PAGE->set_title(get_string('duplicate_plan', 'local_sc_learningplans'));
$PAGE->set_heading(get_string('duplicate_plan', 'local_sc_learningplans'));

$PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
$PAGE->navbar->add(get_string('pluginname', 'local_sc_learningplans'), new moodle_url('/local/sc_learningplans/index.php'));
$PAGE->navbar->add(
    get_string('duplicate_plan', 'local_sc_learningplans'),
    new moodle_url('/local/sc_learningplans/duplicate.php', ['id' => $learningplanid])
);

// Prepare the filemanager with the selected image.
$formeditor = new createlp_form_editor();
$formiamge = new createlp_form_picker();
$draftitemid = file_get_submitted_draft_itemid('learningplan_image');
file_prepare_draft_area(
    $draftitemid,
    $context->id,
    'local_sc_learningplans',
    'learningplan_image',
    $learningplanid
);
$entry = new stdClass;
$entry->learningplan_image = $draftitemid;
$formiamge->set_data($entry);
$entry->desc_plan['text'] = $learningplan->description . ' ' . get_string('copy', 'local_sc_learningplans');
$formeditor->set_data($entry);
$maintemplatedata = [
    'formeditor' => $formeditor->render(),
    'formimage' => $formiamge->render(),
    'learningshortname' => $learningplan->shortname . ' ' . get_string('copy', 'local_sc_learningplans'),
    'learningplanname' => $learningplan->name,
    'learningplandescription' => $learningplan->description,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_sc_learningplans/duplicate_learningplan', $maintemplatedata);
$PAGE->requires->js_call_amd('local_sc_learningplans/duplicate_learningplan', 'init', ['learningid' => $learningplanid]);

echo $OUTPUT->footer();
