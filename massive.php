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
 * Plugin Page - Massive enrol users
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/externallib.php');
require_once("$CFG->dirroot/local/sc_learningplans/tables/table_manage_users_waiting_period.php");
require_once($CFG->dirroot . '/local/sc_learningplans/external/user/add_learning_user.php');

require_login();
require_admin();

$context = context_system::instance();
$PAGE->set_context($context);
$datafile = $_FILES['massivecsv'] ?? null;
if (is_null($datafile)) {
    $message = get_string('massive_nodata', 'local_sc_learningplans');
    \core\notification::info($message);
    redirect(new moodle_url('/local/sc_learningplans/index.php', ['csv' => 'no_data']));
}

$file = fopen($datafile['tmp_name'], "r");

$headerreaded = false;
$learningplans = [];
$rolestudentid = 5;
$rolestudent = $DB->get_record('role', ['shortname' => 'student']);
if ($rolestudent) {
    $rolestudentid = $rolestudent->id;
}
while (($linecsv = fgetcsv($file)) !== false) {
    if (!$headerreaded) {
        $headerreaded = true;
        continue;
    }
    if (count($linecsv) < 5) { // If not have the 7 datas, pass line.
        continue;
    }
    $email      = trim($linecsv[0]);
    $firstname  = trim($linecsv[1]);
    $lastname   = trim($linecsv[2]);
    $username   = core_user::clean_field(core_text::strtolower(trim($linecsv[3])), 'username'); // Is necessary to create the user.
    $password   = $username; // Check if the password policy is disable (If necessary).
    $idnumber   = $username;
    $lpname     = trim($linecsv[4]);
    $group      = $linecsv[5] ?? null;
    // Check the special chars and accents.
    $encode = mb_detect_encoding($lpname, ['UTF-8', 'ISO-8859-1'], true);
    $lpname = mb_convert_encoding($lpname, 'UTF-8', $encode);

    $user = $DB->get_record('user', ['username' => $username]);
    if (!$user) {
        // User not exist, create it.
        $usertocreate = [
            'confirmed'         => 1, // Is necessary, but not obligatory.
            'mnethostid'        => 1, // Is necessary for correct login, but not obligatory.
            'idnumber'          => $idnumber,
            'password'          => $password,
            'username'          => $username,
            'firstname'         => $firstname,
            'lastname'          => $lastname,
            'email'             => $email,
            'lang'              => 'en'
        ];
        $userid = user_create_user($usertocreate);
        $a = (object)[
            'username' => $username,
        ];
        if (!$userid) {
            $message = get_string('massive_usernamenotexist', 'local_sc_learningplans', $a);
            \core\notification::error($message);
            continue;
        }
        $message = get_string('massive_created_user', 'local_sc_learningplans', $a);
        \core\notification::info($message);
    } else {
        // Update the user.
        $user->firstname = $firstname;
        $user->lastname  = $lastname;
        $user->idnumber  = $idnumber;
        $user->password  = $password;
        $otheruseremail = $DB->get_record_sql(
            'SELECT * FROM {user} WHERE email = :email AND id <> :userid LIMIT 1',
            [
                'email' => $email,
                'userid' => $user->id
            ]
        );
        if (!$otheruseremail) {
            // If not exist other account with the email, then update it.
            $user->email  = $email;
        }
        user_update_user($user, true);
        // User exist, get the id.
        $userid = $user->id;
        $a = (object)[
            'username' => $username,
        ];
        $message = get_string('massive_update', 'local_sc_learningplans', $a);
        \core\notification::success($message);
    }
    if (!isset($learningplans[$lpname])) {
        // The LP not searched, so get sql record first.
        $learningplans[$lpname] = $DB->get_record('local_learning_plans', ['shortname' => $lpname]);
    }
    if (!$learningplans[$lpname]) {
        $a = (object)[
            'lpname' => $lpname,
        ];
        $message = get_string('massive_lpnotexist', 'local_sc_learningplans', $a);
        \core\notification::error($message);
        continue;
    }
    $learninguserexist = $DB->get_record('local_learning_users', [
        'learningplanid' => $learningplans[$lpname]->id,
        'userid' => $userid
    ]);
    if (!$learninguserexist) {
        add_learning_user_external::add_learning_user($learningplans[$lpname]->id, $userid, $rolestudentid, null, $group);
    }
    $a = (object)[
        'username' => $username,
        'lpname' => $lpname,
    ];
    $message = get_string('massive_succes', 'local_sc_learningplans', $a);
    \core\notification::success($message);
}

$message = get_string('massive_done', 'local_sc_learningplans');
redirect(new moodle_url('/local/sc_learningplans/index.php'), $message);
