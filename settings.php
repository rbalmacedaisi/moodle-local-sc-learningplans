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
 * Plugin administration pages are defined here.
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('courses', new admin_externalpage(
        'local_sc_learningplans',
        get_string('pluginname', 'local_sc_learningplans'),
        new moodle_url('/local/sc_learningplans/index.php')
    ));
    $ADMIN->add("courses", new admin_externalpage('learningplans_customfield', get_string('plugincustomfields', 'local_sc_learningplans'),new moodle_url('/local/sc_learningplans/customfield.php')));
    $ADMIN->add('reports', new admin_externalpage(
        'local_global_learning_plan_report_view',
        get_string('global_report', 'local_sc_learningplans'),
        new moodle_url('/local/sc_learningplans/global_report.php', []),
    ));
    
    $settingspage = new admin_settingpage('local_sc_learningplans_settings', get_string('pluginname', 'local_sc_learningplans'));
    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configtext(
            'local_sc_learningplans/periodnamesetting',
            get_string('periodnamesetting', 'local_sc_learningplans'),
            get_string('periodnamesetting_desc', 'local_sc_learningplans'),
            get_string('period', 'local_sc_learningplans'),
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_sc_learningplans/default_period_months',
            get_string('default_period_months', 'local_sc_learningplans'),
            get_string('default_period_months_desc', 'local_sc_learningplans'),
            4,
        ));

        $settingspage->add(new admin_setting_heading(
            'local_sc_learningplans/enroledheadinguser',
            get_string('enroledheadinguser', 'local_sc_learningplans'),
            ''
        ));
        $settingspage->add(new admin_setting_configcheckbox(
            'local_sc_learningplans/sendmailenrol',
            get_string('sendmailenrol', 'local_sc_learningplans'),
            get_string('sendmailenrol_desc', 'local_sc_learningplans'),
            false
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_sc_learningplans/emailsubjectenrol',
            get_string('emailsubjectenrol', 'local_sc_learningplans'),
            get_string('emailsubjectenrol_desc', 'local_sc_learningplans'),
            ''
        ));
        $settingspage->add(new admin_setting_confightmleditor(
            'local_sc_learningplans/templatemailenrol',
            get_string('templatemailenrol', 'local_sc_learningplans'),
            get_string('templatemailenrol_desc', 'local_sc_learningplans'),
            ''
        ));

        $settingspage->add(new admin_setting_heading(
            'local_sc_learningplans/updatelpheading',
            get_string('updatelpheading', 'local_sc_learningplans'),
            ''
        ));
        $settingspage->add(new admin_setting_configcheckbox(
            'local_sc_learningplans/sendupdatelp',
            get_string('sendupdatelp', 'local_sc_learningplans'),
            get_string('sendupdatelp_desc', 'local_sc_learningplans'),
            false
        ));
        $settingspage->add(new admin_setting_configtext(
            'local_sc_learningplans/emailsubjectupdatelp',
            get_string('emailsubjectupdatelp', 'local_sc_learningplans'),
            get_string('emailsubjectupdatelp_desc', 'local_sc_learningplans'),
            ''
        ));
        $settingspage->add(new admin_setting_confightmleditor(
            'local_sc_learningplans/templatemailupdatelp',
            get_string('templatemailupdatelp', 'local_sc_learningplans'),
            get_string('templatemailupdatelp_desc', 'local_sc_learningplans'),
            ''
        ));
    }
    $ADMIN->add('localplugins', $settingspage);
}
