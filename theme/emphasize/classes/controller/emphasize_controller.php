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

namespace theme_emphasize\controller;
use theme_emphasize\renderables\remui_sidebar;
// use theme_remui\utility;
defined('MOODLE_INTERNAL') || die();

/**
 * Handles requests regarding all ajax operations.
 *
 * @package   theme_remui
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emphasize_controller extends controller_abstract {
    /**
     * Do any security checks needed for the passed action
     *
     * @param string $action
     */
    public function require_capability($action) {
        $action = $action;
    }

     /**
      * get the remui sidebar mustache content
      *
      * @return json encode array
      */
    public function get_emphasize_sidebar_action() {
        global $PAGE;
        
        $emphasize_sidebar = new emphasize_sidebar();
        echo $PAGE->get_renderer('core')->render_emphasize_sidebar($emphasize_sidebar);
    }

    // public function get_add_activity_course_list_action() {
    //     $courseid = required_param('courseid', PARAM_INT);
    //     return json_encode(theme_controller::get_courses_add_activity($courseid));
    //     // return json_encode(array('html' => theme_controller::get_courses_for_teacher()));
    // }

    public function get_userlist_action() {
        global $DB;
        $courseid = optional_param('courseid', 0, PARAM_INT);
        $sqlq = ("

        SELECT u.id, u.firstname, u.lastname

        FROM {course} c
        JOIN {context} ct ON c.id = ct.instanceid
        JOIN {role_assignments} ra ON ra.contextid = ct.id
        JOIN {user} u ON u.id = ra.userid
        JOIN {role} r ON r.id = ra.roleid
        WHERE c.id = ? AND r.id=5

    ");
        $userlist = $DB->get_records_sql($sqlq, array($courseid));

        return json_encode($userlist);
    }
    
    public function save_user_profile_settings_action() {
        global $USER, $DB;
        $fname = required_param('fname', PARAM_ALPHAEXT);
        $lname = required_param('lname', PARAM_ALPHAEXT);
        $emailid = required_param('emailid', PARAM_EMAIL);
        $description = required_param('description', PARAM_TEXT);
        $city = required_param('city', PARAM_TEXT);
        $country = required_param('country', PARAM_ALPHAEXT);
        // return "$fname $lname $emailid $description $city $country" ;
        return \theme_emphasize\utility::save_user_profile_info($fname, $lname, $emailid, $description, $city, $country);
    }
    
    public function save_user_roles_action() {
        global $USER, $DB;
        $role = optional_param('role','', PARAM_INT);
          $product = optional_param('product','', PARAM_INT);
        return \theme_emphasize\utility::save_role_profile_info($role, $product);
    }
    // public function set_contact_action() {
    //     $otheruserid = required_param('otheruserid', PARAM_INT);
    //     $type = required_param('type', PARAM_ALPHAEXT);
    //     $value = theme_controller::set_user_contact($otheruserid, $type);

    //     return json_encode($value);
    // }

    public function get_courses_by_category_action() {
        $categoryid = required_param('categoryid', PARAM_INT);
        return json_encode(\theme_emphasize\utility::get_courses_by_category($categoryid));
    }

    public function get_courses_for_quiz_action() {
        $courseid = required_param('courseid', PARAM_INT);
        return(json_encode(\theme_emphasize\utility::get_quiz_participation_data($courseid)));
    }


    public function set_setting_ajax_action() {
        $configname = required_param('configname', PARAM_RAW);
        $configvalue = required_param('configvalue', PARAM_RAW);

        set_config($configname, $configvalue, 'theme_emphasize');
    }

    public function get_data_for_messagearea_messages_ajax_action() {
        global $USER;
        $otheruserid = required_param('otheruserid', PARAM_INT);
        return json_encode(\theme_emphasize\utility::data_for_messagearea_messages($USER->id, $otheruserid, 0, 5, true));
    }

    public function send_quickmessage_ajax_action() {
        $contactid = optional_param('contactid', 0, PARAM_INT);
        $message = optional_param('message', '', PARAM_TEXT);
        return json_encode(\theme_emphasize\utility::quickmessage($contactid, $message));
    }
}
