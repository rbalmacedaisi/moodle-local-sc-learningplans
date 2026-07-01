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
 * Scheduled task: credit_integrity_check
 *
 * Walks every (plan, course) that has student progress rows and re-syncs
 * gmk_course_progre.credits with the canonical local_learning_credits value.
 * Logs the number of rows scanned / updated / missing so admins can spot
 * plans whose credit definition is unset.
 *
 * @package    local_sc_learningplans
 * @copyright  2026 Grupo Makro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sc_learningplans\task;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/local/sc_learningplans/classes/local/credit_resolver.php');

use local_sc_learningplans\local\credit_resolver;

class credit_integrity_check extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('task_credit_integrity_check', 'local_sc_learningplans');
    }

    public function execute(): void {
        global $DB;

        $started = microtime(true);

        $result = credit_resolver::backfill_all();

        $duration = round(microtime(true) - $started, 3);

        $summary = sprintf(
            '[credit_integrity_check] scanned=%d updated=%d missing=%d duration=%.3fs',
            $result['scanned'],
            $result['updated'],
            $result['missing'],
            $duration
        );

        // Write to standard Moodle task log so admins see the result via
        // Site administration -> Reports -> Task logs.
        mtrace($summary);
        error_log($summary);
    }
}