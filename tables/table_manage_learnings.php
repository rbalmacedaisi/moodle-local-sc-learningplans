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
 * Plugin Page - Manage Learning Plans
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class table_manage_learnings extends table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        $headerscolumns = [
            get_string('id', 'local_sc_learningplans')          => 'id',
            get_string('shortname', 'local_sc_learningplans')   => 'shortname',
            get_string('name', 'local_sc_learningplans')        => 'name',
            get_string('coursecount', 'local_sc_learningplans') => 'coursecount',
            get_string('usercount', 'local_sc_learningplans')   => 'usercount',
            get_string('periodcount', 'local_sc_learningplans') => 'periodcount',
            get_string('created_at', 'local_sc_learningplans')  => 'created_at',
            get_string('updated_at', 'local_sc_learningplans')  => 'updated_at',
            get_string('actions', 'local_sc_learningplans')     => 'actions',
        ];
        // Define the list of columns to show.
        $this->sortable(false, 'id', SORT_DESC);
        $this->collapsible(false);
        $this->no_sorting('actions');
        
        $this->define_columns(array_values($headerscolumns));
        $this->define_headers(array_keys($headerscolumns));
        $this->set_attribute('class', 'learningPlansTable mt-2 shadow-sm rounded-lg bg-table ');
        $this->init_sql();
    }

    /**
     * Initializes table SQL properties
     *
     * @return void
     */
    protected function init_sql() {
        $fields = '*';

        $from = '{local_learning_plans} lp';

        // Report search.
        $where = '1 = 1';

        $params = [];

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql('SELECT COUNT(1) FROM ' . $from . ' WHERE ' . $where, $params);
    }




    /**
     * Generate the display of the course's creationg date column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_created_at($values) {
        // Show readable date from timestamp.
        $date = $values->timecreated;
        return userdate($date);
    }

    /**
     * Generate the display of the course's updating date column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_updated_at($values) {
        // Show readable date from timestamp.
        $date = $values->timemodified;
        return userdate($date);
    }

    /**
     * Generate the display of the action column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_actions($values) {

        $usericon       = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-user-plus fa-fw'));
        $courseicon     = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-book fa-fw'));
        $deleteicon     = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-trash fa-fw'));
        $editicon       = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-edit fa-fw'));
        $duplicateicon  = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-copy fa-fw'));
        $reporticon     = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-bar-chart fa-fw'));
        $period         = html_writer::tag('i', '', array('class' => 'lp_icon fa fa-calendar fa-fw'));

        $return = html_writer::link(
            new moodle_url('/local/sc_learningplans/users.php', ['id' => $values->id]),
            $usericon,
            array(
                'class' => 'mr-1',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => get_string(
                    'manage_users',
                    'local_sc_learningplans'
                )
            )
        );

        $return .= html_writer::link(
            new moodle_url('/local/sc_learningplans/courses.php', ['id' => $values->id]),
            $courseicon,
            array(
                'class' => 'mr-1',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => get_string('manage_courses', 'local_sc_learningplans')
            )
        );
        $return .= html_writer::link(
            new moodle_url('/local/sc_learningplans/edit.php', ['id' => $values->id]),
            $editicon,
            array(
                'class' => 'mr-1',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => get_string('edit_plan', 'local_sc_learningplans')
            )
        );
        $return .= html_writer::link(
            new moodle_url('/local/sc_learningplans/duplicate.php', ['id' => $values->id]),
            $duplicateicon,
            array(
                'class' => 'mr-1',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => get_string('duplicate_plan', 'local_sc_learningplans')
            )
        );
        $return .= html_writer::span(
            $deleteicon,
            'deleteLearningPlan',
            [
                'learning-plan-id' => $values->id,
                'class' => 'mr-1',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => get_string('delete_learningplan', 'local_sc_learningplans')
            ]
        );
        $return .= html_writer::link(
            new moodle_url('/local/sc_learningplans/report.php', ['id' => $values->id]),
            $reporticon,
            array(
                'class' => 'mr-1',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => get_string('report', 'local_sc_learningplans')
            )
        );
        if ($values->hasperiod != 0) {
            $return .= html_writer::link(
                new moodle_url('/local/sc_learningplans/period.php', ['id' => $values->id]),
                $period,
                array(
                    'class' => 'mr-1',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'bottom',
                    'title' => get_string('manage_periods', 'local_sc_learningplans')
                )
            );
        }
        return $return;
    }
}
