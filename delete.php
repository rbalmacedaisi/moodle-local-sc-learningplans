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
 * Plugin Page - Delete Learning Plans
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/external/user/delete_learning_user.php');
require_once($CFG->dirroot . '/local/sc_learningplans/external/course/delete_learning_course.php');
require_once($CFG->dirroot . '/local/sc_learningplans/external/period/delete_period_learning_plan.php');

require_login();

$context = context_system::instance();
if (!has_any_capability(['local/sc_learningplans:manage'], $context)) {
    admin_externalpage_setup('local_sc_learningplans');
}

$id = required_param('id', PARAM_INT);
$back = required_param('b', PARAM_RAW);
var_dump($id);
$learningplan = $DB->get_record('local_learning_plans', array('id' => $id));
if (!$learningplan) {
    redirect(new moodle_url('/local/sc_learningplans/index.php'));
}

// Unenroll all users from lp courses.

$users = $DB->get_records('local_learning_users', ['learningplanid' => $id]);
foreach ($users as $user) {
    $userid = $user->userid;
    delete_learning_user_external::delete_learning_user($id, $userid, true);
}

// Delete all courses from lp.
$courses = $DB->get_records('local_learning_courses', ['learningplanid' => $id]);
foreach ($courses as $course) {
    $recordid = $course->id;
    $isrequired = $course->isrequired;
    delete_learning_course_external::delete_learning_course($id, $recordid, $isrequired);
}

// Delete periods.
$periods = $DB->get_records('local_learning_periods', ['learningplanid' => $id]);
foreach ($periods as $period) {
    $recordid = $period->id;
    delete_period_learning_plan_external::delete_period_learning_plan($id, $recordid);
}

// Delete lp.
$DB->delete_records('local_learning_plans', ['id' => $id]);

redirect($back);
