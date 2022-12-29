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

require_login();

$context = context_system::instance();
if (!has_any_capability(['local/sc_learningplans:manage'], $context)) {
    // If not have capability, then check if is admin!
    admin_externalpage_setup('local_sc_learningplans');
}

$id = required_param('id', PARAM_INT);
global $DB;


$learningplan = $DB->get_record('local_learning_plans', array('id' => $id));
if (!$learningplan) {
    redirect(new moodle_url('/local/sc_learningplans/index.php'));
}

$allcourses = get_courses();
unset($allcourses[1]);

$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/sc_learningplans/courses.php', ['id' => $id]));

$a = (object)['name' => $learningplan->name];
$PAGE->set_title(get_string('managecourses', 'local_sc_learningplans', $a));
$PAGE->set_heading(get_string('managecourses', 'local_sc_learningplans', $a));
$PAGE->navbar->add(get_string('administrationsite'), new moodle_url('/admin/search.php'));
$PAGE->navbar->add(get_string('pluginname', 'local_sc_learningplans'), new moodle_url('/local/sc_learningplans/index.php'));
$PAGE->navbar->add(
    get_string('managecourses', 'local_sc_learningplans', $a),
    new moodle_url('/local/sc_learningplans/courses.php', ['id' => $id])
);

$hasperiod = false;
$listperiodcourses = [];
$listperiods = [];
$requiredcourses = [];
$optionalcourses = [];
$learningplancourses = $DB->get_records_sql('SELECT lpc.*, c.fullname as coursename
    FROM {local_learning_courses} lpc
    JOIN {course} c ON (c.id = lpc.courseid)
    WHERE lpc.learningplanid = :learningplanid
    ORDER BY lpc.periodid, lpc.isrequired, lpc.position',
    [
        'learningplanid' => $id,
    ]);
if ($learningplan->hasperiod == 1) {
    $hasperiod = true;
    $listperiods = $DB->get_records('local_learning_periods', array('learningplanid' => $id));
    if (!empty($learningplancourses)) {
        $listperiodcourses = [];
        foreach ($learningplancourses as $course) {
            if (!isset($listperiods[$course->periodid]) || !isset($allcourses[$course->courseid])) {
                continue;
            }
            $period = $listperiods[$course->periodid];
            if (!isset($listperiodcourses[$period->id])) {
                $listperiodcourses[$period->id]['idperiod'] = $period->id;
                $listperiodcourses[$period->id]['nameperiod'] = $period->name;
            }
            if ($course->isrequired == 1) {
                $listperiodcourses[$period->id]['coursesrequired'][] = $course;
            } else {
                $listperiodcourses[$period->id]['coursesoptional'][] = $course;
            }
            unset($allcourses[$course->courseid]);
        }
    }
} else {
    foreach ($learningplancourses as $course) {
        if (!isset($allcourses[$course->courseid])) {
            continue;
        }
        if ($course->isrequired == 1) {
            $requiredcourses[] = $course;
        } else {
            $optionalcourses[] = $course;
        }
        unset($allcourses[$course->courseid]);
    }
}

$credits = [
    ['id' => '1', 'value' => '1'],
    ['id' => '2', 'value' => '2'],
    ['id' => '3', 'value' => '3'],
    ['id' => '4', 'value' => '4'],
    ['id' => '5', 'value' => '5'],
    ['id' => '6', 'value' => '6'],
    ['id' => '7', 'value' => '7'],
    ['id' => '8', 'value' => '8'],
    ['id' => '9', 'value' => '9'],
    ['id' => '10', 'value' => '10']
];

$templatedata = [
    'learningplanid' => $id,
    'courses' => array_values($allcourses),
    'listperiods' => array_values($listperiods),
    'hasperiod' => $hasperiod,
    'credits' => $credits,
    'listcoursesperiod' => array_values($listperiodcourses),
    'currentcourses' => $requiredcourses,
    'optionalcourses' => $optionalcourses,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_sc_learningplans/manage_courses', $templatedata);
$PAGE->requires->js_call_amd('local_sc_learningplans/manage_courses', 'init', ['learningplanid' => $id]);
echo $OUTPUT->footer();
