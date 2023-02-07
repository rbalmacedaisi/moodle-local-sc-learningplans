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

require_once($CFG->libdir . '/externallib.php');
require_once("$CFG->libdir/completionlib.php");

use core_completion\progress;

class get_learning_plans_external extends external_api {

    public static function get_learning_plans_parameters() {
        return new external_function_parameters(
            [
                'page'           => new external_value(PARAM_INT, 'Page of the list', VALUE_DEFAULT, 1),
                'resultsperpage' => new external_value(PARAM_INT, 'Records to show per page', VALUE_DEFAULT, 6),
            ]
        );
    }

    public static function get_learning_plans($page, $resultperpage) {
        global $DB, $USER, $CFG;

        if ($page > 0) {
            // Is required that the first page start with 0, not with 1 to get the correct values.
            $page--;
        }

        $limitfrom = $page * $resultperpage;
        $limitnum  = $resultperpage;

        $lptolimit = $DB->get_records_sql(
            'SELECT learningplanid FROM {local_learning_users}
                WHERE userid = :userid
                ORDER BY learningplanid',
                [
                    'userid' => $USER->id
                ],
            $limitfrom,
            $limitnum
        );
        $wherein = [];
        foreach ($lptolimit as $lpuser) {
            $wherein[] = $lpuser->learningplanid;
        }
        $whereinlearningplan = implode(',', $wherein);

        $userlearningplans = $DB->get_records_sql(
            "SELECT
                CONCAT(llu.id, llc.id),
                llu.id learninguserid,
                llu.learningplanid,
                llp.name learningname,
                llp.description,
                llu.userroleid,
                llu.userrolename,
                llc.courseid,
                c.fullname coursename,
                llc.isrequired,
                llp.hasperiod,
                llp.periodcount,
                llperiod.name periodname,
                llperiod.months periodmotnhs,
                llperiod.id periodrecordid
            FROM {local_learning_users} llu
            JOIN {local_learning_plans} llp ON (llp.id = llu.learningplanid)
            JOIN {local_learning_courses} llc ON (llc.learningplanid = llp.id)
            LEFT JOIN {local_learning_periods} llperiod ON (llc.periodid = llperiod.id)
            JOIN {course} c ON (c.id = llc.courseid)
            WHERE
                llu.learningplanid IN ($whereinlearningplan) AND
                llu.userid = :userid
            ORDER BY
                llp.id,
                llperiod.id,
                llc.isrequired,
                llc.position
            ",
            [
                'userid' => $USER->id,
            ]
        );
        $returnlearningplandata = [];
        $fs = get_file_storage();
        $context = context_system::instance();
        $coursecompletiondata = [];
        $courseslist = $DB->get_records('course');
        foreach ($userlearningplans as $userlpdata) {
            $learningplanid = $userlpdata->learningplanid;
            if (!isset($returnlearningplandata[$learningplanid])) {
                $files = $fs->get_area_files($context->id, 'local_sc_learningplans', 'learningplan_image', $learningplanid);
                $urlimg = '';
                foreach ($files as $file) {
                    $imageurl = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename(),
                        false
                    );
                    $urlimg = $imageurl->out(false);
                }
                $returnlearningplandata[$learningplanid] = [
                    'learningplanid' => $learningplanid,
                    'learningname' => $userlpdata->learningname,
                    'description' => $userlpdata->description,
                    'learningimage' => $urlimg,
                    'hasperiod' => $userlpdata->hasperiod == 1,
                    'periodcount' => $userlpdata->periodcount,
                    'isstudent' => $userlpdata->userrolename == 'student',
                    'rolename' => get_string( $userlpdata->userrolename, 'local_sc_learningplans'),
                    'requiredtotalcourses' => 0,
                    'requiredcoursescompleted' => 0,
                    'optionacoursecompleted' => 0,
                    'learningplanprogress' => 0,
                    'periodsdata' => [],
                ];
            }

            $courseid = $userlpdata->courseid;
            $course = $courseslist[$courseid];
            if (!isset($coursecompletiondata[$courseid])) {
                $coursecompletiondata[$courseid]['progress'] = progress::get_course_progress_percentage($course, $USER->id);
                $cinfo = new completion_info($course);
                $coursecompletiondata[$courseid]['completed'] = $cinfo->is_course_complete($USER->id);
            }

            $courseprogress = $coursecompletiondata[$courseid]['progress'];
            $coursecompleted = $coursecompletiondata[$courseid]['completed'];
            if ($userlpdata->isrequired == 1) {
                $returnlearningplandata[$learningplanid]['requiredtotalcourses']++;
            }
            $periodname = $returnlearningplandata[$learningplanid]['hasperiod'] ? $userlpdata->periodname : 'noperiod';
            $coursestringindex = $userlpdata->isrequired == 1 ? 'requiredcourses' : 'optionalcourses';
            if (!isset($returnlearningplandata[$learningplanid]['periodsdata'][$periodname])) {
                $returnlearningplandata[$learningplanid]['periodsdata'][$periodname] = [
                    'periodname' => $periodname,
                    'requiredcourses' => [],
                    'optionalcourses' => [],
                ];
            }
            $returnlearningplandata[$learningplanid]['periodsdata'][$periodname][$coursestringindex][$courseid] = [
                'fullname' => $userlpdata->coursename,
                'courseurl' => $CFG->wwwroot . '/course/view.php?id=' .$courseid,
                'realprogress' => $courseprogress ?? 0,
                'showprogress' => 0,
                'completed' => $coursecompleted ?? false,
                'waiting' => true, // For now, all courses are waiting.
                'active' => false, // For now, all courses aren't active .
                'current' => false, // For now, all courses aren't active .
            ];

        }
        // print_r($returnlearningplandata);die;
        // Do more.
        foreach ($returnlearningplandata as &$rlp) {
            foreach ($rlp['periodsdata'] as $periodname => &$perioddata) {
                foreach ($perioddata['requiredcourses'] as &$requiredcourse) {
                    $requiredcourse['waiting'] = false;
                    $requiredcourse['active'] = true;
                    $requiredcourse['showprogress'] = $requiredcourse['realprogress'];
                    if ($requiredcourse['completed'] == true) {
                        $rlp['requiredcoursescompleted'] ++;
                    } else {
                        $requiredcourse['current'] = true;
                        break;
                    }
                }
                foreach ($perioddata['optionalcourses'] as &$optionalcourse) {
                    $optionalcourse['waiting'] = false;
                    $optionalcourse['active'] = true;
                    $optionalcourse['showprogress'] = $optionalcourse['realprogress'];
                    $optionalcourse['current'] = true;
                    if ($optionalcourse['completed'] == true) {
                        $rlp['optionacoursecompleted'] ++;
                    }
                }
            }
            if ($rlp['requiredtotalcourses'] > 0) {
                $rlp['learningplanprogress'] = round($rlp['requiredcoursescompleted'] * 100 / $rlp['requiredtotalcourses'], 2);
            }
        }
        //print_r($returnlearningplandata);die;
        $totallp = $DB->get_records_sql(
            'SELECT learningplanid FROM {local_learning_users}
                WHERE userid = :userid
                ORDER BY learningplanid',
                [
                    'userid' => $USER->id
                ]
        );
        return [
            'learningplans' => $returnlearningplandata,
            'totallp' => count($totallp),
        ];
    }

    public static function get_learning_plans_returns() {
        $structurecourses = new external_multiple_structure(
            new external_single_structure(
                [
                    'fullname'      => new external_value(PARAM_TEXT, 'Fullname of course'),
                    'courseurl'     => new external_value(PARAM_RAW, 'Urlcourse'),
                    'realprogress'  => new external_value(PARAM_RAW, 'Real progress course'),
                    'showprogress'  => new external_value(PARAM_RAW, 'Progress to show'),
                    'completed'     => new external_value(PARAM_BOOL, 'Completed course'),
                    'waiting'       => new external_value(PARAM_BOOL, 'Si esta en espera o no'),
                    'active'        => new external_value(PARAM_BOOL, 'Si es un curso activo'),
                    'current'       => new external_value(PARAM_BOOL, 'Si es el curso actual que cursa'),
                ]
            ), 'Estructura de cursos', VALUE_DEFAULT
        );
        $structureperiod = new external_multiple_structure(
            new external_single_structure(
                [
                    'periodname'      => new external_value(PARAM_TEXT, 'Fullname of course'),
                    'requiredcourses'           => $structurecourses,
                    'optionalcourses'           => $structurecourses,
                ]
            ), 'Estructura de cursos', VALUE_DEFAULT
        );
        return new external_single_structure(
            array(
                'learningplans' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'learningplanid'            => new external_value(PARAM_INT, 'Learning Plan ID'),
                            'learningname'              => new external_value(PARAM_TEXT, 'Learning Plan Name'),
                            'description'               => new external_value(PARAM_RAW, 'Description'),
                            'learningimage'             => new external_value(PARAM_RAW, 'Image Learning Plan', VALUE_DEFAULT, ''),
                            'isstudent'                 => new external_value(PARAM_BOOL, 'Check if user is student'),
                            'hasperiod'                 => new external_value(PARAM_BOOL, 'If LP has periods'),
                            'periodcount'               => new external_value(PARAM_INT, 'Period count'),
                            'rolename'                  => new external_value(PARAM_TEXT, 'Rolename'),
                            'requiredtotalcourses'      => new external_value(PARAM_INT, 'Total required courses'),
                            'requiredcoursescompleted'  => new external_value(PARAM_INT, 'Total completed required courses'),
                            'learningplanprogress'      => new external_value(PARAM_RAW, 'Progress Learning Plan'),
                            'periodsdata'               => $structureperiod,
                            )
                        ),
                    ),
                    'totallp' => new external_value(PARAM_INT, 'Total learning plans')
                )
            );
    }
}
