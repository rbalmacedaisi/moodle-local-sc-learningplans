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


require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('learningplans_customfield');

$output = $PAGE->get_renderer('core_customfield');

$handler = local_sc_learningplans\customfield\learningplan_handler::create();

// $customfields = $handler->get_fields();
// $categoryarray = [];
// foreach ($customfields as $field) {
//     $category = $field->get_category()->get('name');
//     if(!array_key_exists($category,$categoryarray)) {
//         $categoryarray[$category]=[];
//     }
//     $formattedfield = [
//         'shortname'=> $field->get('shortname'),
//         'type'=> $field->get('type'),
//         'name' => $field->get('name')
//     ];
//     $configdata = $field->get('configdata');
//     foreach (array_keys($configdata) as $key){
//         $formattedfield[$key] = $configdata[$key];
//     }
//     array_push($categoryarray[$category],$formattedfield);
// }
// var_dump($handler->get_custom_fields_for_learning_plan());
// die();

$outputpage = new \core_customfield\output\management($handler);



echo $output->header(),
$output->render($outputpage),
$output->footer();
