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
 * Plugin strings are defined here.
 *
 * @package   local_sc_learningplans
 * @category  string
 * @copyright 2022 Solutto <>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Learning Plans';

$string['sc_learningplans:manage'] = 'Manager - learning plans in local sc_learningplans';
$string['sc_learningplans:teach'] = 'Teacher - learning plans in local sc_learningplans';

$string['id']           = 'ID';
$string['shortname']    = 'Short name';
$string['name']         = 'Name';
$string['coursecount']  = 'Courses';
$string['usercount']    = 'Users';
$string['periodcount']  = 'Periods';
$string['created_at']   = 'Created at';
$string['updated_at']   = 'Updated at';
$string['actions']      = 'Actions';

$string['manage_users']         = 'Manage users';
$string['manage_courses']       = 'Manage courses';
$string['delete_learningplan']  = 'Delete Learning Plan';
$string['edit_plan']            = 'Edit Learning plan';
$string['duplicate_plan']       = 'Duplicate Learning plan';
$string['report']               = "Learning Plan's Report";
$string['requirement_title']    = 'Add new requirement';
$string['manage_periods']       = 'Manage Periods';
$string['title_current_users']  = 'List users';

$string['new_learning_plan'] = 'New learning plan';
$string['plan_name'] = 'Learning Plan Name';
$string['addingperiods'] = 'You want to add Periods';
$string['select_periods'] = 'Select the number of periods';
$string['btnaddperiods'] = 'Add Periods';
$string['title_add_course'] = 'Add new course';
$string['select_course'] = 'Select Course';
$string['btn_add_course'] = 'Add';
$string['btn_addopt_course'] = 'Add Optional';
$string['title_current_course'] = 'Current courses';
$string['title_optional_course'] = 'Optional courses';
$string['delete_current_course'] = 'Delete course';
$string['title_add_users'] = 'Manage users';
$string['add_user_lp'] = 'Add new users in Learning Plan';
$string['select_user'] = 'Select User';
$string['select_role'] = 'Select Rol';
$string['btn_add_user'] = 'Add users';
$string['title_desc_plan'] = 'Plan description';
$string['plan_image'] = 'Plan Image';
$string['add_plan'] = 'Add learning plan';

$string['bulk_users'] = 'Bulk upload users in csv file';

$string['periodname']   = 'Period name';
$string['period']       = 'Period';
$string['typeperiod']   = 'Select the registration type of Period ';
$string['manual']       = 'Manual';
$string['auto']         = 'Automatic';

$string['periodnamesetting'] = 'Period name';
$string['periodnamesetting_desc'] = 'Select a default period name';
$string['default_period_months'] = 'Period duration';
$string['default_period_months_desc'] = 'Default period duration in months';

$string['periodmonths'] = 'Duration (Months)';

$string['student']          = 'Student';
$string['teacher']          = 'Teacher';
$string['editingteacher']   = 'Editing teacher';
$string['manager']          = 'Manager';
$string['coursecreator']    = 'coursecreator';
$string['guest']            = 'guest';
$string['user']             = 'user';
$string['frontpage']        = 'frontpage';
$string['scteachrole']      = 'scteachrole';
$string['scmanagerrole']    = 'scmanagerrole';

$string['addnewperiods'] = 'Add new Periods';
$string['name_period'] = 'Assign name Period';
$string['close_modal'] = 'close';

$string['titleconfirm'] = 'Confirm delete';
$string['msgconfirm_period'] = 'Are you sure you want to delete this Period?<br/>Once you accept you will not be able to undo the changes and all data related to the period will be deleted.';
$string['yesconfirm'] = 'Delete';
$string['msgconfirm_course'] = 'Are you sure you want to delete this Course?<br/>Once you accept you will not be able to undo the changes and all data related to the course will be deleted.';

$string['edit_period'] = 'Edit periods';

$string['managecourses'] = 'Manage courses';
$string['lpname'] = 'Plan de aprendizaje {$a->name} ';
$string['available_courses'] = 'Available Courses';
$string['courses_required'] = 'Required Courses';
$string['courses_optional'] = 'Optionals Courses';
$string['add_courses_required'] = 'Add Required Courses';
$string['add_courses_optional'] = 'Add Optionals Courses';
$string['btn_save_coursepos'] = 'Save Courses Position';
$string['add_courses_period'] = 'Add new courses';
$string['select_credits'] = 'Select number credits';
$string['select_one_period'] = 'Select Period';

$string['titleconfirmmove'] = 'Move optional course to required';
$string['msgconfirm_mmove'] = 'Are you sure to move the course {$a->cname} to required list?';
$string['yesmmoveconfirm'] = 'Move';

$string['save'] = 'Save changes';

$string['manageuser'] = 'Learning plan - Manage user';
$string['id_user'] = 'Id';
$string['name_user'] = 'Name';
$string['email_user'] = 'Email';
$string['roles_user'] = 'Roles';
$string['action_user'] = 'Actions';
$string['bulk_users'] = 'Bulk upload users in csv file';
$string['search_users'] = 'Search users';
$string['assign_users'] = 'Assign Users';
$string['assign_rol'] = 'Assign Rol';

$string['msgconfirm_user'] = 'Are you sure you want to delete this User?<br/>Once you accept you will not be able to undo the changes.<br/><label for="checkRemoveCourses"><input type="checkbox" id="checkRemoveCourses" name="checkRemoveCourses"/> &nbsp Unenrol from courses</label>';

$string['msgconfirm'] = 'Are you sure you want to remove this learning plan and its related content?';

$string['period_enrol'] = 'Period Actually Enrol';
$string['copy'] = 'Copy';
$string['duplicate_courses'] = 'Duplicate the courses of the learning plan (Using the same courses)';
$string['copy_courses'] = 'Create a copy of the courses of the learning plan';
$string['duplicate_users'] = 'Duplicate the enrolments';

$string['plan_requirements'] = 'Requirements';

$string['pending_user'] = 'Users waiting';
$string['nextperiodname'] = 'Next period';
$string['enrolnextperiod'] = 'Enrol';

$string['massive_lpnotexist'] = 'The learning plan {$a->lpname} not exist.';
$string['massive_usernamenotexist'] = 'The user {$a->username} not exist, and can\'t create it.';
$string['massive_succes'] = 'User {$a->username} enroled to learning plan {$a->lpname}.';
$string['massive_created_user'] = 'User {$a->username} created.';
$string['massive_nodata'] = 'Need upload data';
$string['massive_done'] = 'Upload completed';
$string['massive_update'] = 'The user {$a->username} are updated.';

$string['report'] = "Learning Plan's Report";
$string['email'] = 'Email';
$string['currentcourse'] = 'Current Course Count';
$string['completedcourse'] = 'Last Completed Course';
$string['progress'] = 'Progress';
$string['currentperiod'] = 'Current Period';

$string['assign_group'] = 'Assign Group';
$string['select_group'] = 'Select group';
$string['cancel'] = 'Cancel';

$string['search_user_btn'] = 'Search';
$string['search_user'] = 'Search user:&nbsp;';

$string['alert_not_course'] = 'There are no courses assigned yet';
