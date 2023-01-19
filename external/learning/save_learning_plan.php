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
 * External Lib - Save new learning plan
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/sc_learningplans/external/course/save_learning_course.php');
require_once($CFG->dirroot . '/local/sc_learningplans/external/user/add_learning_user.php');
require_once($CFG->dirroot . '/local/sc_learningplans/external/period/addperiod_learning_plan.php');
require_once($CFG->libdir . '/filelib.php');

class save_learning_plan_external extends external_api {

    public static function save_learning_plan_parameters() {
        return new external_function_parameters(
            array(
                'learningshortid' => new external_value(PARAM_TEXT, 'Unique shortname'),
                'learningname' => new external_value(PARAM_TEXT, 'Name of the learning plan'),
                'periods'      => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_TEXT, 'Name of Periods'),
                            'months' => new external_value(PARAM_INT, 'Duration in months'),
                        ),
                    )
                ),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'Course ID'),
                            'required' => new external_value(PARAM_INT, 'If the course is required (1) or not (0)'),
                            'credits' => new external_value(PARAM_RAW, 'List credits of courses'),
                        ),
                    )
                ),
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'userid' => new external_value(PARAM_INT, 'User ID'),
                            'roleid' => new external_value(PARAM_INT, 'Role ID'),
                            'group'  => new external_value(PARAM_TEXT, 'Group name'),
                        ),
                    )
                ),
                'fileimage' => new external_value(PARAM_INT, 'Image itemid provide by filemanager form element'),
                'description' => new external_value(PARAM_RAW, 'Description of the learning plan'),
                'hasperiod'   => new external_value(PARAM_INT, 'Check if learning plan has periods'),
                'enroltype'  => new external_value(PARAM_INT, 'Type Enrolment if plan has periods'),
                'requirements'   => new external_value(PARAM_TEXT, 'User Profiles id'),
            )
        );
    }

    public static function save_learning_plan(
        $learningshortid,
        $learningname,
        $periods,
        $courses,
        $users,
        $fileimage,
        $description,
        $hasperiod,
        $enroltype,
        $requirements
        ) {
        global $DB, $USER;
        // Check if LP exist with the shortid.
        $learningplanexist = $DB->record_exists('local_learning_plans', ['shortname' => $learningshortid]);
        if ($learningplanexist) {
            throw new moodle_exception('errorlearningplanexist', 'local_sc_learningplans');
        }

        if ($hasperiod == 0) {
            $countperiod = 0;
        } else {
            $countperiod = count($periods);
        }
        $description = strip_tags($description);

        $newlearningplan = new stdClass();
        $newlearningplan->shortname     = $learningshortid;
        $newlearningplan->name          = $learningname;
        $newlearningplan->description   = $description;
        $newlearningplan->coursecount   = 0;
        $newlearningplan->usercount     = 0;
        $newlearningplan->hasperiod     = $hasperiod;
        $newlearningplan->periodcount   = $countperiod;
        $newlearningplan->enroltype     = $enroltype;
        $newlearningplan->requirements  = $requirements;
        $newlearningplan->usermodified  = $USER->id;
        $newlearningplan->timecreated = time();
        $newlearningplan->timemodified = time();
        $learningplanid = $DB->insert_record('local_learning_plans', $newlearningplan);
        $newlearningplan->id = $learningplanid;
        if ($hasperiod == 0) {
            // Not periods, so the request data have courses and users.
            $pos = 0;
            foreach ($courses as $course) {
                $courseid = $course['courseid'];
                $isrequired = $course['required'];
                $credits = $course['credits'];
                if ($isrequired) {
                    $pos++;
                    $position = $pos;
                    $newlearningplan->coursecount++;
                } else {
                    $position = 0;
                }
                save_learning_course_external::save_learning_course(
                    $learningplanid, null, $courseid, $isrequired, $credits, $position
                );
            }
            foreach ($users as $user) {
                $userid = $user['userid'];
                $roleid = $user['roleid'];
                $group  = $user['group'];
                $newlearningplan->usercount++;
                add_learning_user_external::add_learning_user($learningplanid, $userid, $roleid, null, $group);
            }
        } else {
            // Only add periods.
            foreach ($periods as $period) {
                $name = $period['name'];
                $months = $period['months'];
                addperiod_learning_plan_external::addperiod_learning_plan($learningplanid, $name, $months);
            }
        }

        if ($fileimage) {
            $itemid = $fileimage;
            $context = context_system::instance();
            file_save_draft_area_files(
                $itemid,
                $context->id,
                'local_sc_learningplans',
                'learningplan_image',
                $learningplanid,
                array('subdirs' => 0, 'maxfiles' => 1)
            );
        }
        $newlearningplan->updated_at = time();
        $DB->update_record('local_learning_plans', $newlearningplan);

        return [
            'learningplanid' => $learningplanid
        ];
    }

    public static function save_learning_plan_returns() {
        return new external_single_structure(
            array(
                'learningplanid' => new external_value(PARAM_INT, 'Learning Plan ID')
            )
        );
    }
}
