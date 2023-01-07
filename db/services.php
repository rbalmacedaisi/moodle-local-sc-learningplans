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
 * Plugin Services - List of services
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    // Learning plan functions!
    'local_sc_learningplans_save_learning_plan' => array(
        'classname'     => 'save_learning_plan_external',
        'methodname'    => 'save_learning_plan',
        'classpath'     => 'local/sc_learningplans/external/learning/save_learning_plan.php',
        'description'   => 'Create new learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),

    'local_sc_learningplans_addperiod_learning_plan' => array(
        'classname'     => 'addperiod_learning_plan_external',
        'methodname'    => 'addperiod_learning_plan',
        'classpath'     => 'local/sc_learningplans/external/period/addperiod_learning_plan.php',
        'description'   => 'Add periods to learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),
    'local_sc_learningplans_delete_period_learning_plan' => array(
        'classname'     => 'delete_period_learning_plan_external',
        'methodname'    => 'delete_period_learning_plan',
        'classpath'     => 'local/sc_learningplans/external/period/delete_period_learning_plan.php',
        'description'   => 'Delete periods to Learning Plan',
        'type'          => 'write',
        'ajax'          => true,
    ),
    'local_sc_learningplans_edit_period_learning_plan' => array(
        'classname'     => 'edit_period_learning_plan_external',
        'methodname'    => 'edit_period_learning_plan',
        'classpath'     => 'local/sc_learningplans/external/period/edit_period_learning_plan.php',
        'description'   => 'Edit periods to Learning Plan',
        'type'          => 'write',
        'ajax'          => true,
    ),

    'local_sc_learningplans_update_required_learning_courses' => array(
        'classname'     => 'update_required_learning_courses_external',
        'methodname'    => 'update_required_learning_courses',
        'classpath'     => 'local/sc_learningplans/external/course/update_required_learning_courses.php',
        'description'   => 'Update courses order in the learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),
    'local_sc_learningplans_save_learning_course' => array(
        'classname'     => 'save_learning_course_external',
        'methodname'    => 'save_learning_course',
        'classpath'     => 'local/sc_learningplans/external/course/save_learning_course.php',
        'description'   => 'Save new (optional or required) course to learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),
    'local_sc_learningplans_delete_learning_course' => array(
        'classname'     => 'delete_learning_course_external',
        'methodname'    => 'delete_learning_course',
        'classpath'     => 'local/sc_learningplans/external/course/delete_learning_course.php',
        'description'   => 'Delete learning course (optional or required)',
        'type'          => 'write',
        'ajax'          => true,
    ),

    'local_sc_learningplans_edit_learning_plan' => array(
        'classname'     => 'edit_learning_plan_external',
        'methodname'    => 'edit_learning_plan',
        'classpath'     => 'local/sc_learningplans/external/learning/edit_learning_plan.php',
        'description'   => 'Edit learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),

    'local_sc_learningplans_add_learning_user' => array(
        'classname'     => 'add_learning_user_external',
        'methodname'    => 'add_learning_user',
        'classpath'     => 'local/sc_learningplans/external/user/add_learning_user.php',
        'description'   => 'Add user to learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),
    'local_sc_learningplans_delete_learning_user' => array(
        'classname'     => 'delete_learning_user_external',
        'methodname'    => 'delete_learning_user',
        'classpath'     => 'local/sc_learningplans/external/user/delete_learning_user.php',
        'description'   => 'Delete user from learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),

    'local_sc_learningplans_duplicate_learning_plan' => array(
        'classname'     => 'duplicate_learning_plan_external',
        'methodname'    => 'duplicate_learning_plan',
        'classpath'     => 'local/sc_learningplans/external/learning/duplicate_learning_plan.php',
        'description'   => 'Duplicate learning plan',
        'type'          => 'write',
        'ajax'          => true,
    ),
);

$services = array(
    'local_sc_learningplans_services' => array(
        'functions'             => array(
            'local_sc_learningplans_save_learning_plan',
        ),
        'requiredcapability'    => 'local/sc_learningplans:manage',
        'restrictedusers'       => 0,
        'enabled'               => 1,
    ),
);
