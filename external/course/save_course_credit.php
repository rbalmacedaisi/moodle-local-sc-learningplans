<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External endpoint: save_course_credit
 *
 * Updates the canonical credit definition for a (plan, course) pair and
 * triggers a refresh of gmk_course_progre.credits for all affected students.
 *
 * @package    local_sc_learningplans
 * @copyright  2026 Grupo Makro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/classes/local/credit_resolver.php');

use local_sc_learningplans\local\credit_resolver;

class save_course_credit_external extends external_api {

    public static function save_course_credit_parameters() {
        return new external_function_parameters(
            array(
                'learningplanid' => new external_value(
                    PARAM_INT,
                    'ID of the learning plan',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'courseid' => new external_value(
                    PARAM_INT,
                    'ID of the course',
                    VALUE_REQUIRED,
                    null,
                    NULL_NOT_ALLOWED
                ),
                'credits' => new external_value(
                    PARAM_INT,
                    'Credit value to set. Pass 0 (or a negative number) to clear and fall back to the course custom field.',
                    VALUE_DEFAULT,
                    null
                ),
            )
        );
    }

    public static function save_course_credit($learningplanid, $courseid, $credits) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::save_course_credit_parameters(),
            array(
                'learningplanid' => $learningplanid,
                'courseid'       => $courseid,
                'credits'        => $credits,
            )
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/sc_learningplans:manage', $context);

        $learningplanid = (int)$params['learningplanid'];
        $courseid       = (int)$params['courseid'];
        $credits        = $params['credits'] === null ? null : (int)$params['credits'];

        if ($learningplanid <= 0 || $courseid <= 0) {
            throw new moodle_exception('invalidparameter', 'error');
        }

        if (!$DB->record_exists('local_learning_plans', ['id' => $learningplanid])) {
            throw new moodle_exception('lpnotexist', 'local_sc_learningplans');
        }
        if (!$DB->record_exists('course', ['id' => $courseid])) {
            throw new moodle_exception('coursenotexist', 'local_sc_learningplans');
        }

        // 0 / negative => clear the canonical entry (resolver will fall back to custom field).
        $valuetostore = ($credits === null || $credits <= 0) ? null : $credits;

        $result = credit_resolver::set_credit(
            $learningplanid,
            $courseid,
            $valuetostore,
            isset($USER->id) ? (int)$USER->id : null
        );

        return [
            'success'             => true,
            'changed'             => (bool)$result['changed'],
            'credits'             => (int)credit_resolver::resolve($learningplanid, $courseid),
            'affected_students'   => (int)$result['affected_students'],
            'snapshot_refreshed'  => (int)$result['snapshot_refreshed'],
        ];
    }

    public static function save_course_credit_returns() {
        return new external_single_structure(
            array(
                'success'            => new external_value(PARAM_BOOL, 'Whether the operation succeeded.'),
                'changed'            => new external_value(PARAM_BOOL, 'Whether the credit value actually changed.'),
                'credits'            => new external_value(PARAM_INT, 'Resolved credits after the write.'),
                'affected_students'  => new external_value(PARAM_INT, 'Distinct progress rows in scope.'),
                'snapshot_refreshed' => new external_value(PARAM_INT, 'Per-student snapshots refreshed.'),
            )
        );
    }
}