<?php

namespace local_sc_learningplans\event;

use core\event\base;
// Define your custom event class
class learningplanuser_added extends base {

    protected function init() {
        $this->data['objecttable'] = 'local_learning_users';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        // return get_string('eventname', 'mod_yourmodule');
        return 'Usuario añadido al learning plan';
    }

    public function get_description() {
        return "Este evento es disparado cuando un usuario es añadido a un learning plan";
    }
}