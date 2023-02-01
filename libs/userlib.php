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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');

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
function enrol_user_in_first_uncomplete_course($courses, $userid, $roleid, $learningplanrecord, $learninguserrecord, $groupname) {
    global $CFG, $DB;
    require_once("$CFG->libdir/completionlib.php");
    $enrolplugin = enrol_get_plugin('manual');
    $allcourses = get_courses();

    $prevloopperiodid = null;
    $usercurrentperiod = $learninguserrecord->currentperiodid;
    $hasperiods = $learningplanrecord->hasperiod;
    $enroltype = $learningplanrecord->enroltype; // 1: Manual, 2: Automatic.
    $ismanual = false;
    if ($enroltype == 1) {
        // Toca poner al usuario en espera llegado el caso.
        $ismanual = true;
    }
    $changeperiod = false;
    $checkchangeperiod = false;
    $learninguserrecord->waitingperiod = null;
    $learninguserrecord->nextperiodid = null;
    foreach ($courses as $course) {
        $courseperiod = $course->periodid; // If null, not matter.
        if ($hasperiods && $ismanual) {
            // Have periods and is manual enrolment.
            if ($usercurrentperiod == null) {
                // El usuario no tiene asignado el periodo actual, se lo asignamos al periodo del ciclo.
                $usercurrentperiod = $learninguserrecord->currentperiodid = $courseperiod;
            }
            if ($prevloopperiodid == null) {
                // Si la variable no tiene asignado el periodo del ciclo anterior, es porq ue es el primer ciclo.
                $prevloopperiodid = $courseperiod;
            }
            if ($courseperiod == $usercurrentperiod) {
                // Ya que el periodo del ciclo actual es el mismo que el periodo actual del usuario, necesitamos verificar
                // si el periodo en los siguientes ciclos va a cambiar.
                $checkchangeperiod = true;
                $prevloopperiodid = $usercurrentperiod;
            }
            if ($checkchangeperiod) {
                // Nos toca verificar si se presenta un cambio de periodo.
                if ($prevloopperiodid != $courseperiod) {
                    // Si la variable que viene guardando el periodo del ciclo anterior no es igual al periodo del ciclo actual
                    // entonces asignamos un cambio de periodo y ponemos al usuario en espera
                    // y el id del siguiente periodo, que en este caso es el periodo del ciclo actual
                    // debemos terminar el ciclo ya que es un nuevo periodo y toca enrolar al usuario manualmente.
                    $changeperiod = true;
                    $learninguserrecord->waitingperiod = 1;
                    $learninguserrecord->nextperiodid = $courseperiod;
                    // Cuando se enrole manualmente, el currenperiodid cambiara al nexteperiodid
                    // y se verificara el cambio de periodo solo cuando el coureseperiodid sea igual al user current periodid.
                    break;
                }
            }
            $prevloopperiodid = $courseperiod;
        }
        $courseid = $course->courseid;
        $learninguserrecord->currentperiodid = $courseperiod; // If null, only mean that the lp not have periods.
        // Enrol in the course.
        enrol_user($enrolplugin, $userid, $courseid, $roleid, $groupname);
        // Check if the course is completed.
        $objcourse = $allcourses[$courseid];
        $cinfo = new completion_info($objcourse);
        $iscomplete = $cinfo->is_course_complete($userid);
        if (!$iscomplete) {
            // If not complete, break the loop.
            break;
        }
    }
    $learninguserrecord->id = (int)$learninguserrecord->id;
    $learninguserrecord->timemodified = time();
    $DB->update_record('local_learning_users', $learninguserrecord);
}

/**
 * Enroll user in all $courses. Each item need to have courseid key
 *
 * @param array $courses
 * @param int $userid
 * @param int $roleid
 * @return void
 */
function enrol_user_in_all_courses($courses, $userid, $roleid, $groupname) {
    $enrolplugin = enrol_get_plugin('manual');
    foreach ($courses as $course) {
        $courseid = $course->courseid;
        enrol_user($enrolplugin, $userid, $courseid, $roleid, $groupname);
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
function enrol_user($enrolplugin, $userid, $courseid, $roleid, $groupname) {
    $instance = get_manual_enrol($courseid);
    if ($instance) {
        $enrolplugin->enrol_user($instance, $userid, $roleid);
        if ($groupname) {
            $group = groups_get_group_by_name($courseid, $groupname);
            if ($group) {
                groups_add_member($group, $userid);
            }
        }
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

/**
 * Logic to enroll user in courses learningplan.
 *
 * @param int $learningplanid
 * @param int $userid
 * @param int $roleid
 * @return void
 */
function enrol_user_in_learningplan_courses($learningplanid, $userid, $roleid, $groupname) {
    global $DB;
    // Get optional courses.
    $optionalcourses = $DB->get_records('local_learning_courses', ['learningplanid' => $learningplanid, 'isrequired' => 0]);
    enrol_user_in_all_courses($optionalcourses, $userid, $roleid, $groupname);

    $requiredcourses = $DB->get_records_sql('SELECT lpc.*, c.fullname FROM {local_learning_courses} lpc
        JOIN {course} c ON (c.id = lpc.courseid)
        WHERE lpc.learningplanid = :learningplanid AND lpc.isrequired = :isrequired
        ORDER BY periodid, position',
    [
        'learningplanid' => $learningplanid,
        'isrequired' => 1
    ]);

    $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplanid]);
    $learninguserrecord = $DB->get_record('local_learning_users', [
        'learningplanid' => $learningplanid,
        'userid' => $userid
    ]);
    if ($roleid != 5) {
        // Isn't student, enroll in all required courses.
        enrol_user_in_all_courses($requiredcourses, $userid, $roleid, $groupname);
    } else {
        enrol_user_in_first_uncomplete_course($requiredcourses, $userid, $roleid, $learningplanrecord,
        $learninguserrecord,
        $groupname);
    }
}
