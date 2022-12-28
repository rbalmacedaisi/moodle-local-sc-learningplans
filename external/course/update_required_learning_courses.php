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
 * External Lib - Update the course positions
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sc_learningplans\event\learningplan_updated;

defined('MOODLE_INTERNAL') || die();

class update_required_learning_courses_external extends external_api {

    public static function update_required_learning_courses_parameters() {
        return new external_function_parameters(
            array(
                'learningplan' => new external_value(PARAM_INT, 'ID of the learning plan'),
                'courseorder' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                            'position' => new external_value(PARAM_INT, 'Position of the course'),
                        )
                    )
                ),
                'periodid' => new external_value(PARAM_INT, 'ID of the period', VALUE_OPTIONAL),
            )
        );
    }
    /**
     * Update the order of courses
     *
     * @param int $learningplan
     * @param array $courseorder
     * @return void
     */
    public static function update_required_learning_courses($learningplan, $courseorder, $periodid) {
        global $DB, $USER;
        $learningplanrecord = $DB->get_record('local_learning_plans', ['id' => $learningplan]);
        if (!$learningplanrecord) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        $returnorder = [];
        $periodwhere = '';
        if ($periodid) {
            $periodwhere = ' AND periodid = :periodid ';
        }
        foreach ($courseorder as $val) {
            $recordid = (int) $val['courseid'];
            $position = (int) $val['position'];
            $DB->execute("UPDATE {local_learning_courses}
                SET position = :position
                WHERE learningplanid = :learningplanid
                AND id = :recordid
                $periodwhere",
                [
                    'learningplanid' => $learningplan,
                    'position' => $position,
                    'recordid' => $recordid,
                    'periodid' => $periodid,
                ]);
            $returnorder[] = [
                'courseid' => $recordid,
                'position' => $position
            ];
        }

        $learningplanrecord->usermodified = $USER->id;
        $learningplanrecord->updated_at = time();
        $DB->update_record('local_learning_plans', $learningplanrecord);
        return [
            'isupdated' => true,
            'neworder' => $returnorder
        ];
    }

    public static function update_required_learning_courses_returns() {
        return new external_single_structure(
            array(
                'isupdated' => new external_value(PARAM_INT, 'If the record is updated'),
                'neworder'  => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'ID of the course'),
                            'position' => new external_value(PARAM_INT, 'Position of the course'),
                        )
                    )
                )
            )
        );
    }
}
