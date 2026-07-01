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
 * Central credit resolver.
 *
 * Single source of truth for academic credits: local_learning_credits,
 * keyed by (learningplanid, courseid). This class is the only sanctioned
 * way to read or write credit definitions, and is responsible for keeping
 * the gmk_course_progre.credits cache column synchronized.
 *
 * @package    local_sc_learningplans
 * @copyright  2026 Grupo Makro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sc_learningplans\local;

defined('MOODLE_INTERNAL') || die();

class credit_resolver {

    /** @var string Canonical table name for credit definitions. */
    const TABLE = 'local_learning_credits';

    /** @var string Cache table where per-student snapshots are stored. */
    const SNAPSHOT_TABLE = 'gmk_course_progre';

    /**
     * Resolve the credits for a (plan, course) pair.
     *
     * Resolution order:
     *   1. local_learning_credits row (canonical, per plan-course)
     *   2. course custom field 'credits' (legacy fallback)
     *   3. 0 (no definition found)
     *
     * @param int $learningplanid Learning plan id (0 = no plan context).
     * @param int $courseid       Course id.
     * @return int Credits (>=0). 0 when nothing matches.
     */
    public static function resolve(int $learningplanid, int $courseid): int {
        global $DB;

        if ($courseid <= 0) {
            return 0;
        }

        // 1. Canonical source.
        if ($learningplanid > 0) {
            $record = $DB->get_record(self::TABLE, [
                'learningplanid' => $learningplanid,
                'courseid'       => $courseid,
            ], 'credits');
            if ($record && $record->credits !== null && (int)$record->credits > 0) {
                return (int)$record->credits;
            }
        }

        // 2. Legacy fallback: Moodle course custom field 'credits'.
        $customcredits = self::get_course_custom_field_credits($courseid);
        if ($customcredits > 0) {
            return $customcredits;
        }

        // 3. Last-resort: any gmk_course_progre row for this course (per-student snapshot).
        // This catches homologated and module enrollments where the (plan, course)
        // may not be in local_learning_credits but a student already has a credit value.
        if ($learningplanid > 0) {
            $max = $DB->get_field_sql(
                "SELECT MAX(credits) FROM {" . self::SNAPSHOT_TABLE . "}
                  WHERE learningplanid = :lpid AND courseid = :cid AND credits > 0",
                ['lpid' => $learningplanid, 'cid' => $courseid]
            );
            if ($max !== false && (int)$max > 0) {
                return (int)$max;
            }
        }

        return 0;
    }

    /**
     * Resolve credits for a specific student progress context. The lookup is the same
     * as resolve() but expressed in (user, plan, course) terms for call-site clarity.
     *
     * @param int $userid         Student id (informational; resolver does not need it).
     * @param int $learningplanid Learning plan id.
     * @param int $courseid       Course id.
     * @return int
     */
    public static function resolve_for_user(int $userid, int $learningplanid, int $courseid): int {
        return self::resolve($learningplanid, $courseid);
    }

    /**
     * Return every credit definition for a learning plan, as a map [courseid => credits].
     *
     * @param int $learningplanid
     * @return array<int,int>
     */
    public static function get_for_plan(int $learningplanid): array {
        global $DB;
        $map = [];
        if ($learningplanid <= 0) {
            return $map;
        }
        $rows = $DB->get_records(self::TABLE, ['learningplanid' => $learningplanid], '', 'courseid, credits');
        foreach ($rows as $r) {
            $map[(int)$r->courseid] = (int)$r->credits;
        }
        return $map;
    }

    /**
     * Insert or update the credit definition for a (plan, course) pair.
     *
     * After writing, refresh the gmk_course_progre.credits cache for all affected
     * students so reports, letters and the UI see the new value immediately.
     *
     * @param int      $learningplanid
     * @param int      $courseid
     * @param int|null $credits      Pass null to clear the explicit value (resolver
     *                               will fall back to custom field on next read).
     * @param int|null $userid       Moodle user id of who is making the change.
     * @return array{changed:bool,affected_students:int,snapshot_refreshed:int}
     */
    public static function set_credit(int $learningplanid, int $courseid, ?int $credits, ?int $userid = null): array {
        global $DB;

        $result = [
            'changed'            => false,
            'affected_students'  => 0,
            'snapshot_refreshed' => 0,
        ];

        if ($learningplanid <= 0 || $courseid <= 0) {
            return $result;
        }

        $now = time();
        $existing = $DB->get_record(self::TABLE, [
            'learningplanid' => $learningplanid,
            'courseid'       => $courseid,
        ]);

        if ($existing) {
            $newvalue = $credits === null ? null : (int)$credits;
            if ((int)($existing->credits ?? -1) === (int)($newvalue ?? -1)) {
                // No-op: value unchanged.
                $result['affected_students'] = self::count_affected_students($learningplanid, $courseid);
                return $result;
            }
            $existing->credits = $newvalue;
            $existing->timemodified = $now;
            $existing->usermodified = $userid;
            $DB->update_record(self::TABLE, $existing);
            $result['changed'] = true;
        } else {
            $record = new \stdClass();
            $record->learningplanid = $learningplanid;
            $record->courseid       = $courseid;
            $record->credits        = $credits === null ? null : (int)$credits;
            $record->timecreated    = $now;
            $record->timemodified   = $now;
            $record->usermodified   = $userid;
            $DB->insert_record(self::TABLE, $record);
            $result['changed'] = true;
        }

        $result['affected_students']  = self::count_affected_students($learningplanid, $courseid);
        $result['snapshot_refreshed'] = self::refresh_student_snapshots($learningplanid, $courseid);
        return $result;
    }

    /**
     * Recompute gmk_course_progre.credits for every progress row in a (plan, course).
     *
     * Returns the number of rows actually updated. Touches only active/enrolled rows
     * (status in 1..7) so historical terminal states are preserved as-is.
     *
     * @param int $learningplanid
     * @param int $courseid
     * @return int Rows updated.
     */
    public static function refresh_student_snapshots(int $learningplanid, int $courseid): int {
        global $DB;

        if ($learningplanid <= 0 || $courseid <= 0) {
            return 0;
        }

        $resolved = self::resolve($learningplanid, $courseid);
        $now = time();
        $rows = $DB->get_records(self::SNAPSHOT_TABLE, [
            'learningplanid' => $learningplanid,
            'courseid'       => $courseid,
        ]);
        $count = 0;
        foreach ($rows as $row) {
            $status = (int)$row->status;
            // Skip no-available (0) - we never set credits for unstarted placeholders.
            if ($status === 0) {
                continue;
            }
            if ((int)$row->credits !== $resolved) {
                $row->credits = $resolved;
                $row->timemodified = $now;
                $DB->update_record(self::SNAPSHOT_TABLE, $row);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Backfill every active (plan, course) progress row from the canonical store.
     * Used by the cron integrity check.
     *
     * @return array{scanned:int,updated:int,missing:int}
     */
    public static function backfill_all(): array {
        global $DB;

        $scanned = 0;
        $updated = 0;
        $missing = 0;

        $sql = "SELECT learningplanid, courseid
                  FROM {" . self::SNAPSHOT_TABLE . "}
                 WHERE learningplanid > 0 AND courseid > 0 AND status > 0
              GROUP BY learningplanid, courseid";
        $recordset = $DB->get_recordset_sql($sql);
        foreach ($recordset as $pair) {
            $scanned++;
            $resolved = self::resolve((int)$pair->learningplanid, (int)$pair->courseid);
            if ($resolved <= 0) {
                $missing++;
                continue;
            }
            $updated += self::refresh_student_snapshots((int)$pair->learningplanid, (int)$pair->courseid);
        }
        $recordset->close();

        return [
            'scanned'  => $scanned,
            'updated'  => $updated,
            'missing'  => $missing,
        ];
    }

    /**
     * Count how many distinct students have progress rows for this (plan, course).
     *
     * @param int $learningplanid
     * @param int $courseid
     * @return int
     */
    public static function count_affected_students(int $learningplanid, int $courseid): int {
        global $DB;
        $count = $DB->count_records(self::SNAPSHOT_TABLE, [
            'learningplanid' => $learningplanid,
            'courseid'       => $courseid,
        ]);
        return (int)$count;
    }

    /**
     * Read the legacy course custom field 'credits' (used as fallback).
     *
     * @param int $courseid
     * @return int
     */
    protected static function get_course_custom_field_credits(int $courseid): int {
        global $DB;

        $sql = "SELECT cd.value
                  FROM {customfield_data} cd
                  JOIN {customfield_field} cf ON cf.id = cd.fieldid
                 WHERE cf.shortname = 'credits' AND cd.instanceid = :cid
              ORDER BY cd.id DESC";
        $value = $DB->get_field_sql($sql, ['cid' => $courseid]);
        if ($value === false || $value === null || $value === '') {
            return 0;
        }
        $intvalue = (int)trim((string)$value);
        return $intvalue > 0 ? $intvalue : 0;
    }
}