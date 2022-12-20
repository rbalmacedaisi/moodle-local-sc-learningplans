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
    }
    $ADMIN->add('localplugins', $settingspage);
}
