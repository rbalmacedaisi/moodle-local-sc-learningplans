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
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class report_lp_table extends table_sql {

    /**
     * Constructor
     */
    public function __construct($uniqueid, int $learningplanid) {
        parent::__construct($uniqueid);
        global $DB;
        $this->learningplanid = $learningplanid;

        // Define columns.
        $columns = [
            'fullname'                  => get_string('name', 'local_sc_learningplans'),
            'email'                     => get_string('email', 'local_sc_learningplans'),
            'lastcurrentcoursename'     => get_string('currentcourse', 'local_sc_learningplans'),
            'lastperiodname'            => get_string('currentperiod', 'local_sc_learningplans'),
            'lastcompletedcoursename'   => get_string('completedcourse', 'local_sc_learningplans'),
            'lpprogress'                => get_string('progress', 'local_sc_learningplans'),
        ];
        if($DB->get_record('user_info_field',['shortname'=>'base'])){
            $columns['data']=get_string('base', 'local_sc_learningplans');
        }

        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        $this->set_attribute('id', $this->uniqueid);
        $this->set_attribute('cellspacing', '0');

        $this->initialbars(true);
        $this->collapsible(false);

        $this->set_attribute('class', 'progressLearningPlanUsers'); // Add class to the table!

        // Initialize table SQL properties.
        $this->init_sql();
    }

    /**
     * Initializes table SQL properties
     *
     * @return void
     */
    protected function init_sql() {
        global $DB;

        $fields = 'r.*, u.firstname, u.lastname, u.email,
        u.firstnamephonetic,
        u.lastnamephonetic,
        u.middlename,
        u.alternatename';

        $from = '{local_learning_report} r
        JOIN {user} u ON (u.id = r.userid)';

        // Report search.
        $where = 'r.learningplanid = :learningplanid';

        $params = [
            'learningplanid' => $this->learningplanid
        ];
        
        // print_object($DB->get_records('user_info_field'));
        // die;
        if($baseField = $DB->get_record('user_info_field',['shortname'=>'base'])){
            $fields = $fields . ', cfd.data';
            $from = $from . ' JOIN {user_info_data} cfd ON (r.userid = cfd.userid)';
            $where = $where . ' AND cfd.fieldid = :fieldid';
            $params['fieldid']=$baseField->id;
        }
        
        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql('SELECT COUNT(1) FROM ' . $from . ' WHERE ' . $where, $params);
        
        // print_object($this);
        // die;
    }

    /**
     * Generate the display of the learning plan's progress date column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_name($values) {
        // Show readable date from timestamp.
        $progress = $values->firstname . ' ' . $values->lastname;
        return $progress;
    }

    /**
     * Generate the display of the learning plan's progress date column.
     * @param object $values the table row being output.
     * @return string HTML content to go inside the td.
     */
    public function col_lpprogress($values) {
        // Show readable date from timestamp.
        $progress = $values->lpprogress . '%';
        return $progress;
    }
}
