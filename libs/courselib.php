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
 * Local Lib - Common function for courses
 *
 * @package     local_sc_learningplans
 * @copyright   2022 Solutto < G>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/sc_learningplans/libs/userlib.php');

/**
 * Get all groyps in all courses from LP
 *
 * @param int $learningplanid
 * @return array
 */
function get_groups_from_courses($learningplanid = null) {
    $allcourses = get_courses();
    unset($allcourses[1]);
    $coursesgroups = [];
    if (!$learningplanid) {
        // Get all group from all courses.
        foreach ($allcourses as $courseid => $val) {
            $groups = get_groups_by_course($courseid);
            $coursesgroups = array_merge($coursesgroups, $groups);
        }
        return $coursesgroups;
    }
    global $DB;
    // Check if learning plan exist and not deleted!
    $learningplan = $DB->get_record('local_learning_plans', ['id' => $learningplanid]);
    if (!$learningplan) {
        return null;
    }
    $optionalcourses = $DB->get_records('local_learning_courses', ['learningplanid' => $learningplanid, 'isrequired' => 0]);
    $requiredcourses = $DB->get_records('local_learning_courses', ['learningplanid' => $learningplanid, 'isrequired' => 1]);

    if ($requiredcourses) {
        // Unset required course from $allcourses (Available courses) and set the related pos!
        foreach ($requiredcourses as $val) {
            $courseid = $val->courseid;
            if (isset($allcourses[$courseid])) {
                $groups = get_groups_by_course($courseid);
                $coursesgroups = array_merge($coursesgroups, $groups);
                unset($allcourses[$courseid]);
            }
        }
    }

    if ($optionalcourses) {
        // Unset optional course from $allcourses (Available courses)!
        foreach ($optionalcourses as $val) {
            $courseid = $val->courseid;
            if (isset($allcourses[$courseid])) {
                $groups = get_groups_by_course($courseid);
                $coursesgroups = array_merge($coursesgroups, $groups);
                unset($allcourses[$courseid]);
            }
        }
    }
    return $coursesgroups;
}

/**
 * Get array of groups from course
 *
 * @param int $courseid
 * @return array
 */
function get_groups_by_course($courseid) {
    $grouplist = [];
    $groups = groups_get_all_groups($courseid, 0, 0, 'g.*');

    foreach ($groups as $group) {
        $name = ($group->name);
        $grouplist[$name] = [
            'name'  => $name,
            'id'    => $group->id,
        ];
    }
    return $grouplist;
}

/**
 * Get the related courses from course recordid, not course id.
 */
function get_related_courses($recordid, $returnnames = false) {
    global $DB;
    $relations = $DB->get_records_sql(
        "SELECT
            lrl.*,
            coursedest.fullname as destination_coursename,
            coursedest.id as destination_courseid
        FROM {local_learningplan_rel_cours} lrl
        JOIN {local_learning_courses} lcdest ON (lcdest.id = lrl.destination_record_id)
        LEFT JOIN {course} coursedest ON (coursedest.id = lcdest.courseid)
        WHERE lrl.origin_record_id = :origin_record_id",
        [
            'origin_record_id' => $recordid
        ]
    );
    if ($returnnames) {
        $coursesnames = [];
        foreach ($relations as $rel) {
            $coursesnames[] = $rel->destination_coursename ?? 'N/A';
        }
        return implode(', ', $coursesnames);
    }
    return $relations;
}

/**
 * Check all related courses and save in $CFG->checkActiveRelatedCourses var.
 */
function mark_active_related_courses($recordid) {
    global $CFG;
    if (isset($CFG->checkActiveRelatedCourses[$recordid])) {
        return;
    }
    $CFG->checkActiveRelatedCourses[$recordid] = true;
    $relations = get_related_courses($recordid);
    foreach ($relations as $reldata) {
        mark_active_related_courses($reldata->destination_record_id);
    }
}
/*
* This function adds a relation record between a course added to a period in a learning plan
* and the courses added to the same period, the learning plan needs to have periods enabled
*/

function relate_course_with_current_period_courses($learningPlanCourseId){
    global $DB;
    
    $learningPlanCoursePeriodId = $DB->get_field('local_learning_courses','periodid',['id'=>$learningPlanCourseId],MUST_EXIST);
    //Get the courses in the same period
    $coursesInSamePeriod = $DB->get_records('local_learning_courses',['periodid'=>$learningPlanCoursePeriodId],'','id');
    unset($coursesInSamePeriod[$learningPlanCourseId]);
    if(empty($coursesInSamePeriod)){
        return false;
    }
    add_course_relations($learningPlanCourseId,array_column($coursesInSamePeriod, 'id'));
    return true;
}

/*
* This function adds a relation record between an lpCourse added to a leraning plan
* and the lpCourses defined in $lpCourseIdsToRelate
*/
function add_course_relations($lpCourseId,$lpCourseIdsToRelate){
    global $DB,$USER;
    
    try{
        $transaction = $DB->start_delegated_transaction();
        foreach ($lpCourseIdsToRelate as $relatedLpCourseId) {
            $relatedLpCourseId = trim($relatedLpCourseId);
            if($DB->record_exists('local_learningplan_rel_cours',['origin_record_id'=>$lpCourseId,'destination_record_id'=>$relatedLpCourseId])){
                continue;
            }
            
            $originDestinationRelation = new stdClass();
            $originDestinationRelation->origin_record_id = $lpCourseId;
            $originDestinationRelation->destination_record_id = $relatedLpCourseId;
            $originDestinationRelation->usermodified = $USER->id;
            $originDestinationRelation->timecreated = time();
            $originDestinationRelation->timemodified = time();
            
            // Add relation X => Y.
            $originDestinationRelationCreated = $DB->insert_record('local_learningplan_rel_cours', $originDestinationRelation, false, true);
    
            if(!$originDestinationRelationCreated){
                throw new moodle_exception('errorcreatingrelation', 'local_sc_learningplans');
            }
            
            $destinationOriginRelation = $originDestinationRelation;
            $destinationOriginRelation->origin_record_id = $relatedLpCourseId;
            $destinationOriginRelation->destination_record_id = $lpCourseId;
            
            // Inverse the relation, Y => X.
            $destinationOriginRelationCreated = $DB->insert_record('local_learningplan_rel_cours', $destinationOriginRelation, false, false);
            
            if(!$destinationOriginRelationCreated){
                throw new moodle_exception('errorcreatingrelation', 'local_sc_learningplans');
            }
        }    
        
        $learningPlanId = $DB->get_field('local_learning_courses','learningplanid',['id'=>$lpCourseId]);
        // Re enroll all lp users.
        $lpUsers = $DB->get_records_sql(
            'SELECT llu.* FROM {local_learning_users} llu
            JOIN {user} u ON (u.id = llu.userid)
            WHERE llu.learningplanid = :learningplanid', ['learningplanid' => $learningPlanId]);
        foreach ($lpUsers as $user) {
            $userId = $user->userid;
            $roleId = $user->userroleid;
            enrol_user_in_learningplan_courses($learningPlanId, $userId, $roleId, $user->groupname);
        }
        $transaction->allow_commit();
        
        return true;
    }catch(Exception $e){
        $transaction->rollback($e);
        throw $e;
    }
}