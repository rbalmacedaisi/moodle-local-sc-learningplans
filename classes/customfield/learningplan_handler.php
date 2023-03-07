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
 * Customfields handler
 *
 * @package    sc_learningplans
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sc_learningplans\customfield;

defined('MOODLE_INTERNAL') || die();
use core_customfield\api;
use core_customfield\field_controller;

class learningplan_handler extends \core_customfield\handler {
    
    /** 
     * @var course_handler
     */
    static protected $singleton;

    /**
     * @var \context
     */
    protected $parentcontext;

    /** @var int Field is displayed in the course listing, visible to everybody */
    const VISIBLETOALL = 2;
    /** @var int Field is displayed in the course listing but only for teachers */
    const VISIBLETOTEACHERS = 1;
    /** @var int Field is not displayed in the course listing */
    const NOTVISIBLE = 0;

    /**
     * Returns a singleton
     *
     * @param int $itemid
     * @return local_sc_learningplans\customfield\lp_career_handler
     */
    public static function create(int $itemid = 0) : \core_customfield\handler {
        if (static::$singleton === null) {
            self::$singleton = new static(0);
        }
        return self::$singleton;
    }
    
    public function set_parent_context(\context $context) {
        
        $this->parentcontext = \context_system::instance();
    }
    
    protected function get_parent_context() : \context {
        return \context_system::instance();
    }
    
    public function can_configure(): bool {
        return true; // TODO
    }

    /**
     * The current user can edit custom fields on the given record on this component.
     *
     * @param field_controller $field
     * @param int $instanceid
     * @return bool
     */
    public function can_edit(field_controller $field, $instanceid = null): bool {
        return true; // TODO
    }

    public function get_configuration_url(): \moodle_url {
        return new \moodle_url('/local/sc_learningplans/customfield.php');
    }

    public function can_view(field_controller $field, $instanceid = null): bool {
        return true; // TODO
    }

    public function get_instance_context(int $instanceid = 0): \context {
        return \context_system::instance();
    }

    public function get_configuration_context(): \context {
        return \context_system::instance();
    }
    
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'course_handler_header', get_string('customfieldsettings', 'local_sc_learningplans'));
        $mform->setExpanded('course_handler_header', true);

        // If field is locked.
        $mform->addElement('selectyesno', 'configdata[locked]', get_string('customfield_islocked', 'core_course'));
        $mform->addHelpButton('configdata[locked]', 'customfield_islocked', 'core_course');

        // Field data visibility.
        $visibilityoptions = [self::VISIBLETOALL => get_string('customfield_visibletoall', 'core_course'),
            self::VISIBLETOTEACHERS => get_string('customfield_visibletoteachers', 'core_course'),
            self::NOTVISIBLE => get_string('customfield_notvisible', 'core_course')];
        $mform->addElement('select', 'configdata[visibility]', get_string('customfield_visibility', 'core_course'),
            $visibilityoptions);
        $mform->addHelpButton('configdata[visibility]', 'customfield_visibility', 'core_course');
        
        // Field pattern regex.
        $mform->addElement('text', 'configdata[regex]', get_string('customfield_regexpattern', 'local_sc_learningplans'));
        // $mform->addHelpButton('configdata[regex]', 'customfield_regexpattern', 'local_sc_learningplans');
        
        // Field data type.
        $datatypeoptions = ['text' => get_string('datatypetext','local_sc_learningplans'),
            'number' =>get_string('datatypenumber','local_sc_learningplans'),
            'email' => get_string('datatypeemail','local_sc_learningplans')];
        $mform->addElement('select', 'configdata[datatype]', get_string('customfield_datatype', 'local_sc_learningplans'),$datatypeoptions);
        // $mform->addHelpButton('configdata[datatype]', 'customfield_datatype', 'local_sc_learningplans');
    }
    
    public function get_custom_fields_for_learning_plan($itemid = 0):array{
        global $DB;
        $learningplan = $DB->get_record('local_learning_plans', ['id' => $itemid]);
        // var_dump($learningplan->hasperiod);
        // die();
        $categoryarray = [];
        $customfields = $this->get_instance_data($itemid);
        foreach ($customfields as $customfield) {
            $field = $customfield->get_field();
            
            $category = $field->get_category()->get('name');
            if(!array_key_exists($category,$categoryarray)) {
                $categoryarray[$category]=[
                    'name'=>get_string($category, 'local_sc_learningplans'),
                    'fields'=>[]
                    ];
            }
            $formattedfield = [
                'shortname'=> $field->get('shortname'),
                'select' => $field->get('type') === 'select',
                'text' => $field->get('type') === 'text',
                'date' => $field->get('type') === 'date',
                'checkbox' => $field->get('type') === 'checkbox',
                'textarea' => $field->get('type') === 'textarea',
                'name' => get_string($field->get('shortname'), 'local_sc_learningplans'),
                'value' => empty($customfield->get_value())?'':$customfield->get_value(),
                'disabled' => $field->get('shortname')==='careerduration' && $learningplan->hasperiod === "1" ? 'disabled':''
            ];
            $configdata = $field->get('configdata');
            foreach (array_keys($configdata) as $key){
                if($key === 'required'){
                    $formattedfield[$key] = $configdata[$key]? 'required': ''; 
                    continue;
                }
                if($key === 'options'){
                    $customfieldoptions = [];
                    
                    foreach(explode("\n",$configdata[$key]) as $id => $option){
                        array_push($customfieldoptions, ['id'=>$id+1, 'value'=>$option, 'selected' => $customfield->get_value()===$id+1? 'selected': '']);
                    }
                    $formattedfield[$key] = $customfieldoptions;
                    continue;
                }
                $formattedfield[$key] = $configdata[$key];
            }
            array_push($categoryarray[$category]['fields'],$formattedfield);
        }
        return $categoryarray;
    }
    
}