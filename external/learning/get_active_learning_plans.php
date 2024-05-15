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
 * External Lib - Save new learning plan
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto <nicolas.castillo@soluttoconsulting.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/sc_learningplans/libs/learningplanlib.php');
require_once("$CFG->libdir/externallib.php");

class get_active_learning_plans_external extends external_api {

    public static function get_active_learning_plans_parameters() {
        return new external_function_parameters([
            'instructorId' => new external_value(PARAM_INT, 'Id of the instructor',  VALUE_DEFAULT, null, NULL_ALLOWED)
            ]);
    }

    public static function get_active_learning_plans($instructorId = null) {
        global $DB;
        // Check if LP Exist.
        try{
            $learningplans = [];
            if($instructorId){
                $instructorLearningPlansRegistered = $DB->get_records('local_learning_users',array('userid'=>$instructorId,'userroleid'=>4));
                foreach($instructorLearningPlansRegistered as $instructorLearningPlanRegistered){
                    $learningplans[] = $DB->get_record('local_learning_plans',array('id'=>$instructorLearningPlanRegistered->learningplanid));
                }
            }else {
                $learningplans = $DB->get_records('local_learning_plans');
            }
            
            
            $careername_options = [];
            
            $availablecareers = [];
            foreach($learningplans as $learningplan){
    
                //Get the learning plan custom fields
                $handler = local_sc_learningplans\customfield\learningplan_handler::create();
                $learningplan_customfields = $handler->get_custom_fields_for_learning_plan($learningplan->id);
                $learningPlanCareerCustomFields = $learningplan_customfields['Informacion_carrera']['fields'];
                
                $learningplanName = $learningplan->name;
                $career_name = null;
                $career_formattedinfo = [];
                
                foreach($learningPlanCareerCustomFields as $careerfield){
                    if($careerfield['shortname'] === 'careername'){
                        $selected_careername_id = $careerfield['value'];
                        if(empty($careername_options)){
                            $options = $careerfield['options'];
                            foreach($options as $option){
                                $careername_options[$option['id']]=$option['value'];
                            }
                        }
     
                        $career_name = array_key_exists($selected_careername_id,$careername_options)?$careername_options[$selected_careername_id]:'unset' ;
                        continue;
                    }
                    $career_formattedinfo[$careerfield['shortname']] = $careerfield['value']!==''? $careerfield['value']:'unset';
                }
                
                $career_formattedinfo['careername'] = $career_name;
                $career_formattedinfo['lpid'] = $learningplan->id;
                $career_formattedinfo['timecreated'] = $learningplan->timecreated;
                if(!array_key_exists($learningplanName,$availablecareers)){
                    $availablecareers[$learningplanName]= $career_formattedinfo;
                    continue;
                }
                $already_added_lp_timecreated = $availablecareers[$learningplanName]['timecreated'];
                if($already_added_lp_timecreated > $career_formattedinfo['timecreated']){
                    continue;
                }
                $availablecareers[$learningplanName]= $career_formattedinfo;
                
            }
            return [
                'availablecareers' => json_encode($availablecareers)
            ];
        }catch (Exception $e){
            return ['status' => -1,'message'=>$e->getMessage()];
        }
        
    }

    public static function get_active_learning_plans_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, '1 if success, -1 otherwise',VALUE_DEFAULT,1),
                'availablecareers' => new external_value(PARAM_RAW, 'Json with the available careers',VALUE_DEFAULT,null),
                'message' => new external_value(PARAM_TEXT, 'Error message if error, ok otherwise',VALUE_DEFAULT,'ok')
            )
        );
    }
}