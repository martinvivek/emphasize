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


namespace theme_emphasize;

use user_picture;
use moodle_url;
use blog_listing;
use context_system;
use course_in_list;
use context_course;
use core_completion\progress;
use stdClass;

include_once($CFG->dirroot.'/mod/forum/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

require_once($CFG->dirroot . "/lib/coursecatlib.php");
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . "/message/lib.php");


/**
 * General remui utility functions.
 *
 * Added to a class for the convenience of auto loading.
 *
 * @package   theme_remui
 * @copyright WisdmLabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility {

    /**
     * Update the User Profile details using ajax call.
     *
     * @param $fname, $lname, $emailid, $description, $city, $country
     * @return boolean, weather result are updated or not.
     */
    public static function save_user_profile_info($fname, $lname, $emailid, $description, $city, $country) {
        global $USER, $DB;
        $user = $DB->get_record('user', array('id' => $USER->id));
        $user->firstname = $fname;
        $user->lastname = $lname;
        $user->email = urldecode($emailid);
        $user->description = $description;
        $user->city = $city;
        $user->country = $country;
        //user_update_user($user);
        $result = $DB->update_record('user', $user, $bulk=false);
        return $result;
    }
}