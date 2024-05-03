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
require_once($CFG->dirroot . '/local/sc_learningplans/external/period/addperiod_learning_plan.php');
require_once($CFG->dirroot . '/local/sc_learningplans/external/user/add_learning_user.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

class duplicate_learning_plan_external extends external_api {

    public static function duplicate_learning_plan_parameters() {
        return new external_function_parameters(
            array(
                'learningid'    => new external_value(PARAM_INT, 'ID of the learning plan'),
                'learningshortname' => new external_value(PARAM_TEXT, 'Unique shortname'),
                'learningname'  => new external_value(PARAM_TEXT, 'Name of the learning plan'),
                'courses'       => new external_value(PARAM_BOOL, 'If duplicate the courses (Same courses)'),
                'copycourses'   => new external_value(PARAM_BOOL, 'If duplicate the courses (Create new courses)'),
                'users'         => new external_value(PARAM_BOOL, 'If duplicate the users'),
                'fileimage'     => new external_value(PARAM_INT, 'Image itemid provide by filemanager form element'),
                'description'   => new external_value(PARAM_RAW, 'Description of the learning plan'),
            )
        );
    }

    public static function duplicate_learning_plan(
        $learningid,
        $learningshortname,
        $learningname,
        $courses,
        $copycourses,
        $users,
        $fileimage,
        $description
    ) {
        global $DB, $USER, $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);
        $oldlearningid = $learningid;
        // Check if LP Exist.
        $learningplantoduplicate = $DB->get_record('local_learning_plans', ['id' => $oldlearningid]);
        if (!$learningplantoduplicate) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        // Check if other LP Have the received learning shortname.
        $checklpshortname = $DB->get_records_sql(
            'SELECT *
            FROM {local_learning_plans}
            WHERE shortname = :shortname',
            [
                'shortname' => $learningshortname,
            ]
        );
        if ($checklpshortname) {
            throw new moodle_exception('otherlpsameshortname', 'local_sc_learningplans');
        }
        // Duplicate the LP.
        $newlearningplan = $learningplantoduplicate;
        unset($newlearningplan->id); // Necessary to do the insert_record.
        $newlearningplan->shortname = $learningshortname;
        $newlearningplan->name = $learningname;
        $newlearningplan->description = $description;
        $newlearningplan->coursecount = 0;
        $newlearningplan->usercount = 0;
        $newlearningplan->periodcount = 0;
        $newlearningplan->usermodified = $USER->id;
        $newlearningplan->timecreated = time();
        $newlearningplan->timemodified = time();
        $newlearningplanid = $DB->insert_record('local_learning_plans', $newlearningplan);

        // Duplicate the courses (If exist, with periods).
        $withperiods = $newlearningplan->hasperiod;
        if ($courses || $copycourses) {
            $countcourses = 0;
            $lpcourses = $DB->get_records('local_learning_courses', ['learningplanid' => $oldlearningid]);
            if ($withperiods) {
                // Get the periods and make an array.
                $periodcourses = [];
                $lpperiods = $DB->get_records('local_learning_periods', ['learningplanid' => $oldlearningid]);
                foreach ($lpperiods as $period) {
                    $periodid = $period->id;
                    $periodcourses[$periodid] = $period;
                    $periodcourses[$periodid]->courses = [];
                }
                foreach ($lpcourses as $course) {
                    $periodid = $course->periodid;
                    $periodcourses[$periodid]->courses[] = $course;
                }
                // Create the new periods with their courses.
                foreach ($periodcourses as $periodid => $data) {
                    $datanewperiod = addperiod_learning_plan_external::addperiod_learning_plan(
                        $newlearningplanid,
                        $data->name,
                        $data->months,
                        $data->hassubperiods
                    );
                    $newperiodid = $datanewperiod['id'];
                    foreach ($data->courses as $datacourse) {
                        if ($copycourses) {
                            $datacourse->courseid = self::create_new_course_from_other($datacourse->courseid);
                        }
                        $countcourses++;
                        save_learning_course_external::save_learning_course(
                            $newlearningplanid,
                            $newperiodid,
                            null,
                            $datacourse->courseid,
                            $datacourse->isrequired,
                            $datacourse->credits,
                            $datacourse->position
                        );
                    }
                }
            } else {
                foreach ($lpcourses as $datacourse) {
                    if ($copycourses) {
                        $datacourse->courseid = self::create_new_course_from_other($datacourse->courseid);
                    }
                    $countcourses++;
                    save_learning_course_external::save_learning_course(
                        $newlearningplanid,
                        null,
                        null,
                        $datacourse->courseid,
                        $datacourse->isrequired,
                        $datacourse->credits,
                        $datacourse->position
                    );
                }
            }
        }
        // Duplicate users.
        if ($users) {
            $users = $DB->get_records('local_learning_users', ['learningplanid' => $oldlearningid]);
            foreach ($users as $u) {
                add_learning_user_external::add_learning_user($newlearningplanid, $u->userid, $u->userroleid, $u->currentperiodid, $u->groupname);
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
                $newlearningplanid,
                array('subdirs' => 0, 'maxfiles' => 1)
            );
        }
        
        
        //Duplicate the custom fields of the learning plan----------------------------
        $handler = local_sc_learningplans\customfield\learningplan_handler::create();
        $learningplan_customfields = $handler->get_instance_data($oldlearningid);
        if(!empty($learningplan_customfields)) {
            $customfieldstobeduplicated = new stdClass();
            $customfieldstobeduplicated->id=$newlearningplanid;
            
            foreach ($learningplan_customfields as $customfield) {
                if (empty($customfield->get_value())) {
                    continue;
                }
                $customfieldstobeduplicated->{'customfield_'.$customfield->get_field()->get('shortname')} = $customfield->get_value();
            }
            $handler->instance_form_save($customfieldstobeduplicated);
        }
        //End custom fields duplicate------------------------------
        
        return [
            'learningplanid' => $newlearningplanid
        ];
    }

    public static function duplicate_learning_plan_returns() {
        return new external_single_structure(
            array(
                'learningplanid' => new external_value(PARAM_INT, 'Learning Plan ID')
            )
        );
    }

    public static function create_new_course_from_other($courseid) {
        global $USER;
        $course = get_course($courseid);
        $suffix = 'copy ' . time();
        // Create the course backup.
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $courseid,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT, // MODE_IMPORT to avoid 'Include user completion information'.
            $USER->id
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        // Do restore to new course with default settings.
        $newcourseid = restore_dbops::create_new_course(
            $course->fullname . $suffix,
            $course->shortname . $suffix,
            $course->category
        );
        $results        = $bc->get_results();
        $backupbasepath = $bc->get_plan()->get_basepath();
        $file = $results['backup_destination'] ?? null; // May be empty if file already moved to target location.
        if ($file && !file_exists($backupbasepath . '/moodle_backup.xml')) {
            // If the backup not exist, create it.
            $fp = get_file_packer('application/vnd.moodle.backup');
            $files = $file->extract_to_pathname($fp, $backupbasepath);
        }
        // Restore the backup.
        $rc = new restore_controller(
            $backupid,
            $newcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_SAMESITE,
            $USER->id,
            backup::TARGET_NEW_COURSE
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy(); // Delete the restore.
        $bc->destroy(); // Delete the backup.
        if ($file) {
            $file->delete();
        }
        return $newcourseid;
    }
}
