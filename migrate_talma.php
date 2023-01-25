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
 * Plugin Page - Migrate from talma DB
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once("$CFG->dirroot/local/sc_learningplans/external/learning/save_learning_plan.php");
require_once("$CFG->dirroot/local/sc_learningplans/external/course/save_learning_course.php");
require_once($CFG->dirroot . '/local/sc_learningplans/external/user/add_learning_user.php');

require_login();
admin_externalpage_setup('local_sc_learningplans');

$learningplans = $DB->get_records('local_sc_learningplans', ['deleted' => 0]);

$migratedlp = [];
foreach ($learningplans as $lp) {
    try {
        mtrace("Se intenta agregar el LP $lp->name con shortid $lp->shortid", '</br>');
            $migratedlp[$lp->id] = save_learning_plan_external::save_learning_plan(
                $lp->shortid,
            $lp->name,
            [],
            [],
            [],
            null,
            $lp->description,
            0,
            0,
            ''
        );
        mtrace("::Se agrega el LP $lp->name con un nuevo id " . $migratedlp[$lp->id]['learningplanid'], '</br>');
    } catch (\Throwable $th) {
        mtrace("No se pudo agregar el LP $lp->name con shortid $lp->shortid: " . $th->getMessage(), '</br>');
    }
}
mtrace('', '<br/>');

$learningcourses = $DB->get_records('local_learningplans_courses');

foreach ($learningcourses as $lcourse) {
    $oldlpid = $lcourse->learningplanid;
    if (!isset($migratedlp[$oldlpid])) {
        continue;
    }
    $newlpid = $migratedlp[$oldlpid]['learningplanid'];
    $coursesinlp = explode('|', $lcourse->courses);
    $required = $lcourse->required;
    foreach ($coursesinlp as $courseid) {
        mtrace("Se agrega el curso $courseid en el LP $newlpid ", '</br>');
        save_learning_course_external::save_learning_course($newlpid, null, $courseid, $required, -1, null);
    }
}
mtrace('', '<br/>');

$learningusers = $DB->get_records('local_sc_learningplans_users');

foreach ($learningusers as $lusers) {
    $oldlpid = $lusers->learningplanid;
    if (!isset($migratedlp[$oldlpid])) {
        continue;
    }
    $newlpid = $migratedlp[$oldlpid]['learningplanid'];
    mtrace("[$lusers->id]Se agrega el usuario $lusers->userid en el LP $newlpid con oldid $oldlpid", '</br>');
    add_learning_user_external::add_learning_user($newlpid, $lusers->userid, $lusers->userroleid, null, $lusers->groupname);
}

die('Fin');
