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
 * Local Lib - Common function for users
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto < G>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get allowed roles for this plugin
 *
 * @return array
 */
function sc_learningplan_get_roles() {
    global $DB;
    $roles = $DB->get_records('role');
    foreach ($roles as $key => &$role) {
        if (
            $role->shortname != 'manager' &&
            $role->shortname != 'editingteacher' &&
            $role->shortname != 'teacher' &&
            $role->shortname != 'student'
        ) {
            unset($roles[$key]);
            continue;
        }
        $role->strname = get_string($role->shortname, 'local_sc_learningplans');
    }
    return $roles;
}

/**
 * Enroll user in the first course uncompleted. Each item need to have courseid key
 *
 * @param array $courses
 * @param int $userid
 * @param int $roleid
 * @return void
 */
function enrol_user_in_first_uncomplete_course($courses, $userid, $roleid) {
    $enrolplugin = enrol_get_plugin('manual');
    $allcourses = get_courses();
    // The prev courses of the first course, not exist, so the prev course is completed
    // and the user can be enroled in the first course if is uncompleted.
    $prevcourseiscompleted = true;
    foreach ($courses as $course) {
        $courseid = $course->courseid;
        if ($prevcourseiscompleted) {
            enrol_user($enrolplugin, $userid, $courseid, $roleid);
        }
        $objcourse = $allcourses[$courseid];
        $cinfo = new completion_info($objcourse);
        $iscomplete = $cinfo->is_course_complete($userid);
        if (!$iscomplete) {
            break;
        }
    }

}

/**
 * Enroll user in all $courses. Each item need to have courseid key
 *
 * @param array $courses
 * @param int $userid
 * @param int $roleid
 * @return void
 */
function enrol_user_in_all_courses($courses, $userid, $roleid) {
    $enrolplugin = enrol_get_plugin('manual');
    foreach ($courses as $course) {
        $courseid = $course->courseid;
        enrol_user($enrolplugin, $userid, $courseid, $roleid);
    }
}

/**
 * Enrol user in course.
 *
 * @param enrol_plugin $enrolplugin (Use method enrol_get_plugin)
 * @param int $userid
 * @param int $courseid
 * @param int $roleid
 * @return void
 */
function enrol_user($enrolplugin, $userid, $courseid, $roleid) {
    $instance = get_manual_enrol($courseid);
    if ($instance) {
        $enrolplugin->enrol_user($instance, $userid, $roleid);
    }
}
/**
 * Enrol user in course.
 *
 * @param enrol_plugin $enrolplugin (Use method enrol_get_plugin)
 * @param int $userid
 * @param int $courseid
 * @param int $roleid
 * @return void
 */
function unenrol_user($enrolplugin, $userid, $courseid) {
    $instance = get_manual_enrol($courseid);
    if ($instance) {
        $enrolplugin->unenrol_user($instance, $userid);
    }
}

/**
 * Get instance of manual enrol
 *
 * @param int $courseid
 * @return stdClass instance
 */
function get_manual_enrol($courseid) {
    $instances = enrol_get_instances($courseid, true);
    foreach ($instances as $instance) {
        if ($instance->enrol = 'manual') {
            return $instance;
        }
    }
    return false;
}
