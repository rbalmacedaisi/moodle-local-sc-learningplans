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
 * Local Lib - Common function for courses
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto < G>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Get all groyps in all courses from LP
 *
 * @param int $learningplanid
 * @return array
 */
function get_groups_from_courses($learningplanid = null) {
    $allcourses = get_courses();
    unset($allcourses[1]);
    $coursesgroups = [];
    if (!$learningplanid) {
        // Get all group from all courses.
        foreach ($allcourses as $courseid => $val) {
            $groups = get_groups_by_course($courseid);
            $coursesgroups = array_merge($coursesgroups, $groups);
        }
        return $coursesgroups;
    }
    global $DB;
    // Check if learning plan exist and not deleted!
    $learningplan = $DB->get_record('local_learning_plans', ['id' => $learningplanid]);
    if (!$learningplan) {
        return null;
    }
    $optionalcourses = $DB->get_records('local_learning_courses', ['learningplanid' => $learningplanid, 'isrequired' => 0]);
    $requiredcourses = $DB->get_records('local_learning_courses', ['learningplanid' => $learningplanid, 'isrequired' => 1]);

    if ($requiredcourses) {
        // Unset required course from $allcourses (Available courses) and set the related pos!
        foreach ($requiredcourses as $val) {
            $courseid = $val->courseid;
            if (isset($allcourses[$courseid])) {
                $groups = get_groups_by_course($courseid);
                $coursesgroups = array_merge($coursesgroups, $groups);
                unset($allcourses[$courseid]);
            }
        }
    }

    if ($optionalcourses) {
        // Unset optional course from $allcourses (Available courses)!
        foreach ($optionalcourses as $val) {
            $courseid = $val->courseid;
            if (isset($allcourses[$courseid])) {
                $groups = get_groups_by_course($courseid);
                $coursesgroups = array_merge($coursesgroups, $groups);
                unset($allcourses[$courseid]);
            }
        }
    }
    return $coursesgroups;
}

/**
 * Get array of groups from course
 *
 * @param int $courseid
 * @return array
 */
function get_groups_by_course($courseid) {
    $grouplist = [];
    $groups = groups_get_all_groups($courseid, 0, 0, 'g.*');

    foreach ($groups as $group) {
        $name = ($group->name);
        $grouplist[$name] = [
            'name'  => $name,
            'id'    => $group->id,
        ];
    }
    return $grouplist;
}

