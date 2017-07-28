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

    // get user profile pic link
    public static function get_user_picture($userobject = null, $imgsize = 100) {
        global $USER, $PAGE;
        if (!$userobject) {
            $userobject = $USER;
        }

        $userimg = new user_picture($userobject);
        $userimg->size = $imgsize;
        return  $userimg->get_url($PAGE);
    }

    // get user forum posts count
    public static function get_user_forum_post_count($userobject = null) {
        global $USER;
        if (!$userobject) {
            $userobject = $USER;
        }

        $courses = forum_get_courses_user_posted_in($userobject);
        $userpostcount = forum_get_posts_by_user($userobject, $courses)->totalcount;
        $userpostlink = new moodle_url('/mod/forum/user.php?id=' . $userobject->id);

        return $userpostcount;
    }

    // get user blog count
    public static function get_user_blog_post_count($userobject = null) {
        global $USER, $DB, $CFG;
        if (!$userobject) {
            $userobject = $USER;
        }

        if (!empty($CFG->enableblogs)) {
            include_once($CFG->dirroot .'/blog/locallib.php');
        }

        $blogobj = new blog_listing();
        if ($sqlarray = $blogobj->get_entry_fetch_sql(false, 'created DESC')) {
            $sqlarray['sql'] = "SELECT p.*, u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,
            u.firstname,u.lastname, u.email FROM {post} p, {user} u WHERE u.deleted = 0 AND p.userid = u.id AND
            (p.module = 'blog' OR p.module = 'blog_external') AND (p.userid = ?  OR p.publishstate = 'site' )
            AND u.id = ? ORDER BY created DESC";
            $sqlarray['params'] = array($USER->id, $userobject->id);
            $blogobj->entries = $DB->get_records_sql($sqlarray['sql'], $sqlarray['params']);
            $userblogcount = count($blogobj->entries);
            //$userbloglink = new moodle_url('/blog/index.php?userid=' . $otheruser->id);
        }

        return $userblogcount;
    }

    // get user blog count
    public static function get_user_contacts_count($userobject = null) {
        global $USER, $DB, $CFG;
        if (!$userobject) {
            $userobject = $USER;
        }

        $userblogcount = count($DB->get_records('message_contacts', array('userid'=>$userobject->id)));

        return $userblogcount;
    }

    // is user admin or manager
    public static function check_user_admin_cap($userobject = null) {
        global $USER;
        $has_capability = false;

        if (!$userobject) {
            $userobject = $USER;
        }
          $context = context_system::instance();

        if(is_siteadmin() || has_capability('block/myunit:viewblock', $context)) {
            $has_capability = true;
        }

        $roles = get_user_roles($context, $userobject->id, false);
        if (!$has_capability) {
            foreach ($roles as $role) {
                if ($role->roleid == 1 && $role->shortname == 'manager') {
                    $has_capability = true;
                    break;
                }
            }
        }

        return $has_capability;
    }

    /**
     * Return user's courses or all the courses
     *
     * Usually called to get usr's courese, or it could also be called to get all course.
     * This function will also be called whern search course is used.
     *
     * @param string $search course name to be search
     * @param int $category ids to be search of courses.
     * @param int $usercourses to return user's course which he/she enrolled into.
     * @param int $limitfrom course to be returned from these number onwards, like from course 5 .
     * @param int $limitto till this number course to be returned , like from course 10, then 5 course will be returned from 5 to 10.
     * @param int $showhidden include hidden courses in results.
     * @return array of course.
     */
    public static function get_courses(
        $totalcount = false,
        $search = null,
        $category = null,
        $limitfrom = 0,
        $limitto = 0) {

        global $DB, $CFG, $USER, $OUTPUT;
          $systemcontext = context_system::instance();
        $count = 0;
        $coursesarray = array();
        $where = '';
        require_once($CFG->libdir. '/coursecatlib.php');

        if (!empty($search)) {
                 $where .= " AND fullname like '%$search%' ";
        }
        if (!empty($category)) {
                $where .= " AND category ='$category' ";
        }

        // get courses
        $fields = array('c.id',
                    'c.category',
                    'c.fullname',
                    'c.startdate',
                    'c.enddate',
                    'c.visible',
                    );
        // return count of total courses by getting limited data
        // if required
        if(is_siteadmin()){
             $where .= "";
        }elseif(has_capability('local/assign_multiple_departments:manage',$systemcontext)){
             $costcenter = $DB->get_record_sql("SELECT id,costcenterid FROM {user} WHERE id =".$USER->id);
            // $cert = "SELECT * FROM {course}  WHERE  FIND_IN_SET(costcenter ,".$costcenter->costcenterid.")";
             $where .=" AND c.costcenterid =".$costcenter->costcenterid;
        }
        if($totalcount) {
            if(!self::check_user_admin_cap($USER)) {
                $where .= " AND visible = 1";
            }
              $where .= " ORDER BY id DESC";
            return count($DB->get_records_sql("SELECT c.id FROM {course} c where id != ? $where", array(1)));
        } else {
              $where .= " ORDER BY id DESC";
            $courses = $DB->get_records_sql("SELECT ".implode($fields, ',')." FROM {course} c where id != ? $where", array(1), $limitfrom, $limitto);
        }

        // prepare courses array
        $chelper = new \coursecat_helper();
        foreach ($courses as $k => $course) {
            $course_in_list = new course_in_list($course);
            $context = context_course::instance($course->id);

            // for hidden courses, require visibility check
            if (isset($course->visible) && $course->visible <= 0) {
                if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                    continue;
                }
            }
            $coursesarray[$count]["courseid"] = $course->id;
            $coursesarray[$count]["coursename"] = $course->fullname;
            $coursesarray[$count]["categoryname"] = $DB->get_record('course_categories',array('id'=>$course->category))->name;
            $coursesarray[$count]["visible"] = $course->visible;
            $coursecontext = context_course::instance($course->id);
            if(!is_siteadmin() && ! has_capability('local/assign_multiple_departments:manage',$systemcontext)){
                if(is_enrolled($coursecontext, $USER)){
                    $coursesarray[$count]["courseurl"] = $CFG->wwwroot."/course/view.php?id=".$course->id;
                }else{
                    $coursesarray[$count]["courseurl"] = $CFG->wwwroot."/local/costcenter/courseview.php?id=".$course->id;
                }
            }elseif(is_siteadmin() || has_capability('local/assign_multiple_departments:manage',$systemcontext)){

                $coursesarray[$count]["courseurl"] = $CFG->wwwroot."/course/view.php?id=".$course->id;
            }
            // $enrollid =  $DB->get_field('enrol','id',array('enrol'=>'manual','courseid'=> $course->id));
            // if($enrollid){
            //     $coursesarray[$count]["enrollusers"] = $CFG->wwwroot."/local/costcenter/courseenrol.php?id=".$course->id.'&enrolid='.$enrollid;
            // }else{
            //     $coursesarray[$count]["enrollusers"] = $CFG->wwwroot."/enrol/users.php?id=".$course->id;
            // }
            $enrollid =  $DB->get_record_sql("SELECT * FROM {enrol} WHERE courseid =".$course->id);
            if($enrollid){
                $coursesarray[$count]["enrollusers"] = $CFG->wwwroot."/local/costcenter/courseenrol.php?id=".$course->id;
            }else{
                $coursesarray[$count]["enrollusers"] = $CFG->wwwroot."/enrol/users.php?id=".$course->id;
            }
            $coursesarray[$count]["editcourse"] = $CFG->wwwroot."/course/edit.php?id=".$course->id;
            $coursesarray[$count]["grader"] = $CFG->wwwroot."/grade/report/grader/index.php?id=".$course->id;
            $coursesarray[$count]["activity"] = $CFG->wwwroot."/report/outline/index.php?id=".$course->id;
            $coursesummary = strip_tags($chelper->get_course_formatted_summary($course_in_list,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false)));
            $summarystring = strlen($coursesummary) > 100 ? substr($coursesummary, 0, 100)."..." : $coursesummary;
            $coursesarray[$count]["coursesummary"] = $summarystring;
            $coursesarray[$count]["coursestartdate"] = date('d M, Y', $course->startdate);

            // course instructors
            $instructors = $course_in_list->get_course_contacts();
            foreach ($instructors as $key => $instructor) {
                $coursesarray[$count]["instructors"][] = array(
                                                        'name' => $instructor['username'],
                                                        'url'  => $CFG->wwwroot.'/user/profile.php?id='.$key,
                                                        'picture' => self::get_user_picture($DB->get_record('user', array('id' => $key)))
                                                        );
                break;
            }

            // course image
            foreach ($course_in_list->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php",
                                          '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                                          $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                if ($isimage) {
                    break;
                }
            }
            if (!empty($courseimage)) {
                $coursesarray[$count]["courseimage"] = $courseimage;
            } else {
                $coursesarray[$count]["courseimage"] = $OUTPUT->image_url('placeholder', 'theme');
            }
            $courseimage = '';
            $count++;
        }

        return $coursesarray;
    }

    // used in corusecategory layout
    // render category selector
    public static function get_course_category_selector($category = '', $search = '', $pageurl) {
        global $DB;
        $categories = \coursecat::make_categories_list();

        $categoryhtml = "<form method='get' action='{$pageurl}'>";

        if($search != '') {
            $categoryhtml .= "<input type='hidden' name='search' value='{$search}'>";
        }

        $categoryhtml .= "<label for='categoryselect' class='d-none font-weight-400 blue-grey-600 font-size-14 pr-10'>".get_string('category', 'theme_remui')."</label> <select onchange='this.form.submit()' id='categoryselect' class='custom-select h-40 w-full' name='categoryid' id='category'>
                <option value=''>".get_string('allcategories', 'theme_remui')."</option>";

        foreach ($categories as $key => $coursecategory) {
              $parent = $DB->get_record_sql("SELECT id,name FROM {course_categories} WHERE id =".$key." AND parent = 0");
            if ( $category == $key) {
                $categoryhtml .= "<option selected value='{$key}'>{$coursecategory}</option>";
            } else {
                // $categoryhtml .= "<option value='{$key}'>{$coursecategory}</option>";
                 if(!empty($parent)){

                          $categoryhtml .= "<option value='{$key}'><h4>{$coursecategory}</h4></option>";
                 }else{
                             $categoryhtml .= "<option value='{$key}'>&nbsp;&nbsp;&nbsp;{$coursecategory}</option>";
                 }
            }
        }
        $categoryhtml .= "</select></form>";

        return $categoryhtml;
    }

    // get user courses along with their course progress
    public static function get_users_courses_with_progress($userobject,$planid = false, $type = false) {
        global $USER, $OUTPUT, $CFG, $DB;

        if (!$userobject) {
            $userobject = $USER;
        }

        require_once($CFG->dirroot.'/course/renderer.php');
        $chelper = new \coursecat_helper();
        if(!empty($planid) && $type=="certification"){
            $courses = $DB->get_records_sql("SELECT c.*,lc.sortorder,lc.id as lepid,lc.nextsetoperator as next
                    FROM {local_certificate_courses} lc
                    JOIN {course} c ON c.id = lc.courseid
                    WHERE lc.planid = ".$planid." ORDER BY lc.sortorder ASC");
        }elseif(!empty($planid)){
            $courses = $DB->get_records_sql("SELECT c.*,lc.sortorder,lc.id as lepid,lc.nextsetoperator as next
                    FROM {local_program_courses} lc
                    JOIN {course} c ON c.id = lc.courseid
                    WHERE lc.planid = ".$planid." ORDER BY lc.sortorder ASC");

        }else{
            $courses = enrol_get_users_courses($userobject->id, true, '*', 'visible DESC, fullname ASC, sortorder ASC');
        }
        foreach ($courses as $course) {

            // get course list instance
            if ($course instanceof stdClass) {
                require_once($CFG->libdir. '/coursecatlib.php');
                $courseobj = new \course_in_list($course);
            }

            $completion = new \completion_info($course);

            // First, let's make sure completion is enabled.
            if ($completion->is_enabled()) {
                $percentage = progress::get_course_progress_percentage($course, $userobject->id);

                if (!is_null($percentage)) {
                    $percentage = floor($percentage);
                }

                // add completion data in course object
                $course->completed = $completion->is_course_complete($userobject->id);
                $course->progress  = $percentage;
            }

            // update properties in object
            // if( $course->startdate ) {
            //     $course->startdate = date('d M, Y', $course->startdate);
            // }
            $course->link = $CFG->wwwroot."/course/view.php?id=".$course->id;

            // summary
            $course->summary = strip_tags($chelper->get_course_formatted_summary($courseobj,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false)));

            // update course image in object
            foreach ($courseobj->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php",
                                          '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                                          $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
                if ($isimage) {
                    break;
                }
            }
            if (empty($courseimage)) {
                $courseimage = $OUTPUT->image_url('placeholder', 'theme');
            }

            $course->courseimage = $courseimage;
            $courseimage = '';
        }
        return $courses;
    }

    /**
     * Returns list of courses of passed course category id.
     *
     * @param int $categoryid
     * @return array
     */
    public static function get_courses_by_category($categoryid) {
        global $DB;
        $query = "SELECT id, fullname, shortname from {course} where category = " . $categoryid;
        $courselist = $DB->get_records_sql($query);
        if ($courselist){
            foreach ($courselist as $course) {
                $context = context_course::instance($course->id);
                $query = "select count(u.id) as count from  {role_assignments} as a, {user} as u where contextid=" . $context->id . " and roleid=5 and a.userid=u.id;";
                $count = $DB->get_records_sql( $query );
                $count = key($count);
                $courselist[$course->id]->count = $count;
            }
            usort($courselist, function($variable1, $variable2) {
                return $variable2->count - $variable1->count;
            });
            $labels = $data = $background_color = $hoverBackground_color = array();
            $colors = array('#2196f3', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#ffeb3b', '#ff9800', '#f44336', '#9c27b0', '#673ab7', '#3f51b5');
            $others = $othersCount = 0;
            foreach ($courselist as $index => $course) {
                if ($index > 9) {
                    $others = 1;
                    $othersCount += $course->count;
                } else {
                    array_push($labels, $course->shortname);
                    array_push($data, $course->count);
                    array_push($background_color, $colors[$index]);
                    array_push($hoverBackground_color, $colors[$index]);
                }
            }
            if ($others > 0) {
                array_push($labels, get_string('others', 'theme_remui'));
                array_push($data, $othersCount);
                array_push($background_color, $colors[10]);
                array_push($hoverBackground_color, $colors[10]);
            }
            return array('labels' => $labels, 'data' => $data, 'background_color' => $background_color, 'hoverBackground_color' => $hoverBackground_color);
        } else {
            return null;
        }
    }

    // get user profile pic link
    public static function get_user_image_link($userid, $imgsize) {
        global $USER;
        if (!$userid) {
            $userid = $USER->id;
        }
        global $DB, $PAGE;
        $user = $DB->get_record('user', array('id' => $userid));
        $userimg = new user_picture($user);
        $userimg->size = $imgsize;
        return  $userimg->get_url($PAGE);
    }

    // Get the recently added users
    public static function get_recent_user() {
        global  $DB;
        $userdata = array();
        $limitfrom = 0;
        $limitto = 8;
        $users = $DB->get_records_sql('SELECT u.* FROM {user} u  WHERE u.deleted = 0 AND id != 1 ORDER BY timecreated desc', array(1), $limitfrom, $limitto);
        $count = 0;
        foreach ($users as $value) {
            $date = date('d/m/Y', $value->timecreated);
            if ($date == date('d/m/Y')) {
                     $date = get_string('today', 'theme_remui');
            } else if ($date == date('d/m/Y', time() - (24 * 60 * 60))) {
                 $date = get_string('yesterday', 'theme_remui');
            } else {
                $date = date('jS F Y', $value->timecreated);
            }
            $userdata[$count]['img'] = self::get_user_image_link($value->id, 100);
            $userdata[$count]['name'] = $value->firstname .' '.$value->lastname;
            $userdata[$count]['register_date'] = $date;
            $userdata[$count]['id'] = $value->id;
            $count++;
        }
        return $userdata;
    }

    // for quiz_stats block on dashboard
    public static function get_quiz_participation_data($courseid ,$limit = 8) {
        global $DB;
        $sqlq = ("SELECT COUNT(DISTINCT u.id)
            FROM {course} c
            JOIN {context} ct ON c.id = ct.instanceid
            JOIN {role_assignments} ra ON ra.contextid = ct.id
            JOIN {user} u ON u.id = ra.userid
            JOIN {role} r ON r.id = ra.roleid
            WHERE c.id = ?");
        $totalcount = $DB->get_records_sql($sqlq, array($courseid));
        $totalcount = key($totalcount);
        $sqlq = ("SELECT SUBSTRING(q.name, 1, 20) labels , COUNT(qa.userid) attempts
            FROM {quiz} q
            LEFT JOIN {quiz_attempts} qa ON q.id = qa.quiz
            WHERE q.course = ?
            GROUP BY q.name
            ORDER BY attempts DESC
            LIMIT $limit");
        $quizdata = $DB->get_records_sql($sqlq, array($courseid));
        $chartdata = array();
        $index = 0;
        $chartdata['datasets'][0]['label'] = get_string('totalusersattemptedquiz', 'theme_remui');
        $chartdata['datasets'][1]['label'] = get_string('totalusersnotattemptedquiz', 'theme_remui');
        $chartdata['datasets'][0]['backgroundColor'] = "rgba(75, 192, 192, 0.2)";
        $chartdata['datasets'][1]['backgroundColor'] = "rgba(255, 99, 132, 0.2)";
        $chartdata['datasets'][0]['borderColor'] = "rgba(75, 192, 192, 1)";
        $chartdata['datasets'][1]['borderColor'] = "rgba(255,99,132,1)";
        $chartdata['datasets'][0]['borderWidth'] = 1;
        $chartdata['datasets'][1]['borderWidth'] = 1;
        foreach ($quizdata as $key => $quiz) {
            $chartdata['labels'][$index] = $key;
            $chartdata['datasets'][0]['data'][$index] = intval($quiz->attempts);
            $chartdata['datasets'][1]['data'][$index] = intval($totalcount - $quiz->attempts);
            if ($chartdata['datasets'][1]['data'][$index] < 0) {
                $chartdata['datasets'][1]['data'][$index] = 0;
            }
            // $quizdata[$key]->noattempts = $totalcount - $quiz->attempts;
            $index++;
        }
        return $chartdata;
    }

    /*
     * get course summary image
     */
    public static function get_course_image($course_in_list, $islist = false) {

        global $CFG, $OUTPUT;
        if(!$islist) {
            $course_in_list = new course_in_list($course_in_list);
        }

        // course image
        foreach ($course_in_list->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php",
                                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                break;
            }
        }
        if (!empty($courseimage)) {
            return $courseimage;
        } else {
            return $OUTPUT->image_url('placeholder', 'theme');
        }
    }

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

   
    /**
     * Return the recent blog.
     *
     * This function helps in retrieving the recent blog.
     *
     * @param int $start how many blog should be skipped if specified 0 no recent blog will be skipped.
     * @param int $blogcount number of blog to be return.
     * @param string $filearea file area
     * @return array $blog returns array of blog data.
     */

    public static function get_recent_blogs($start = 0, $blogcount = 10) {
        Global $CFG;

        require_once($CFG->dirroot.'/blog/locallib.php');
        $bloglisting = new \blog_listing();

        $blogentries = $bloglisting->get_entries($start, $blogcount);

        foreach ($blogentries as $blogentry) {
            $blogsummary = strip_tags($blogentry->summary);
            $summarystring = strlen($blogsummary) > 150 ? substr($blogsummary, 0, 150)."..." : $blogsummary;
            $blogentry->summary = $summarystring;

            // created at
            $blogentry->createdat = date('d M, Y', $blogentry->created);

            // link
            $blogentry->link = $CFG->wwwroot.'/blog/index.php?entryid='.$blogentry->id;
        }
        return $blogentries;
    }

    // /**
    //  * This function is used to get the data for either slider or static at a time.
    //  *
    //  * @return array of sliding data
    //  */
    // public static function get_slider_data() {
    //     global $PAGE, $OUTPUT;

    //     $sliderdata = array();
    //     $sliderdata['isslider'] = false;
    //     $sliderdata['isimage']  = false;
    //     $sliderdata['isvideo']  = false;
    //     $sliderdata['slideinterval'] = false;

    //     if (\theme_emphasize\toolbox::get_setting('sliderautoplay') == '1') {
    //         $sliderdata['slideinterval'] =  \theme_emphasize\toolbox::get_setting('slideinterval');
    //     }

    //     $numberofslides =  \theme_emphasize\toolbox::get_setting('slidercount');

    //     // Get the content details either static or slider.
    //     $frontpagecontenttype =  \theme_emphasize\toolbox::get_setting('frontpageimagecontent');

    //     if ($frontpagecontenttype) { // Dynamic image slider.
    //         $sliderdata['isslider'] = true;
    //         if ($numberofslides >= 1) {
    //             for ($count = 1; $count <= $numberofslides; $count++) {
    //                 $sliderimageurl = \theme_remui\toolbox::setting_file_url('slideimage'.$count, 'slideimage'.$count);
    //                 if ($sliderimageurl == "" || $sliderimageurl == null) {
    //                     $sliderimageurl = \theme_remui\toolbox::image_url('slide', 'theme');
    //                 }
    //                 $sliderimagetext =  \theme_remui\toolbox::get_setting('slidertext'.$count);
    //                 $sliderimagelink =  \theme_remui\toolbox::get_setting('sliderurl'.$count);
    //                 $sliderbuttontext =  \theme_remui\toolbox::get_setting('sliderbuttontext'.$count);
    //                 if ($count == 1) {
    //                     $active = true;
    //                 } else {
    //                     $active = false;
    //                 }
    //                 $sliderdata['slides'][] = array(
    //                 'img' => $sliderimageurl,
    //                 'img_txt' => $sliderimagetext,
    //                 'btn_link' => $sliderimagelink,
    //                 'btn_txt' => $sliderbuttontext,
    //                 'active' => $active,
    //                 'count' => $count - 1);
    //             }
    //         }
    //     } else if (!$frontpagecontenttype) { // Static data.
    //         // Get the static front page settings
    //         $sliderdata['addtxt'] =  \theme_remui\toolbox::get_setting('addtext');
    //         $contenttype =  \theme_remui\toolbox::get_setting('contenttype');
    //         if (!$contenttype) {
    //             $sliderdata['isvideo'] = true;
    //             $sliderdata['video'] =  \theme_remui\toolbox::get_setting('video');
    //             $sliderdata['videoalignment'] =  \theme_remui\toolbox::get_setting('frontpagevideoalignment');
    //         } else if ($contenttype) {
    //             $sliderdata['isimage'] = true;
    //             $staticimage = \theme_remui\toolbox::setting_file_url('staticimage', 'staticimage');
    //             if ($staticimage == "" || $staticimage == null) {
    //                 $sliderdata['staticimage'] = \theme_remui\toolbox::image_url('slide', 'theme');
    //             } else {
    //                 $sliderdata['staticimage'] = $staticimage;
    //             }
    //         }
    //     }
    //     return $sliderdata;
    // }

    // /**
    //  * This function is used to get the data for testimonials in about us section.
    //  *
    //  * @return array of testimonial data
    //  */
    // public static function get_testimonial_data() {
    //     global $PAGE, $OUTPUT;

    //     // return if acout us is disabled
    //     if(!\theme_remui\toolbox::get_setting('enablefrontpageaboutus')) {
    //         return false;
    //     }

    //     $testimonial_data = array();
    //     $testimonialcount =  \theme_remui\toolbox::get_setting('testimonialcount');

    //     if ($testimonialcount >= 1) {

    //         for ($count = 1; $count <= $testimonialcount; $count++) {
    //             $testimonialimageurl = \theme_remui\toolbox::setting_file_url('testimonialimage'.$count, 'testimonialimage'.$count);

    //             $testimonialname =  \theme_remui\toolbox::get_setting('testimonialname'.$count);
    //             $testimonialdesignation =  \theme_remui\toolbox::get_setting('testimonialdesignation'.$count);
    //             $testimonialtext =  \theme_remui\toolbox::get_setting('testimonialtext'.$count);
    //             if ($count == 1) {
    //                 $active = true;
    //             } else {
    //                 $active = false;
    //             }
    //             $testimonial_data['testimonials'][] = array(
    //             'image' => @$testimonialimageurl,
    //             'name' => $testimonialname,
    //             'designation' => $testimonialdesignation,
    //             'text' => $testimonialtext,
    //             'active' => $active,
    //             'count' => $count - 1);
    //         }
    //     }

    //     return $testimonial_data;
    // }

    /**
     * This function is used to get the data for footer section.
     *
     * @return array of footer sections data
     */
    public static function get_footer_data($social = false) {
        $footer = array();
        $colcount = 0;
        for ($i=0; $i < 4; $i++) {
            if($i == 0) {
               $footer['social'] = array(
                    'facebook' => \theme_emphasize\toolbox::get_setting('fburl'),
                    'twitter'  => \theme_emphasize\toolbox::get_setting('twitter'),
                    'linkedin' => \theme_emphasize\toolbox::get_setting('linkedin'),
                    'gplus'    => \theme_emphasize\toolbox::get_setting('youtube'),
                    'youtube'  => \theme_emphasize\toolbox::get_setting('instagram'),
                    // 'instagram'=> \theme_emphasize\toolbox::get_setting('isntagramsetting'),
                    // 'pinterest'=> \theme_emphasize\toolbox::get_setting('pinterestsetting')
                );
               $footer['social'] = array_filter($footer['social']); // remove empty elements
               if(!empty($footer['social'])) {
                    $colcount++;
               }

            } else {
                // skip footer content if only social
                if($social) {
                    continue;
                }

                // $title = \theme_remui\toolbox::get_setting('footercolumn'.$i.'title');
                // $content = \theme_remui\toolbox::get_setting('footercolumn'.$i.'customhtml');
                // if(!empty($title) || !empty($content)) {
                //     $footer['sections'][] = array(
                //         'title' => $title,
                //         'content' => $content
                //     );
                //     $colcount++;
                // }
            }
        }

        // skip footer content if only social
        // if(!$social) {
        //     $footer['bottomtext'] = \theme_remui\toolbox::get_setting('footerbottomtext');
        //     $footer['bottomlink'] = \theme_remui\toolbox::get_setting('footerbottomlink');
        //     $footer['poweredby']  = \theme_remui\toolbox::get_setting('poweredbyedwiser');
        //     // to handle number of columns in footer row
        //     //$colcount = count($footer['social']) + count($footer['sections']);
        //     $classes = 'col-12 ';
        //     if($colcount == 4) {
        //         $classes .= "col-sm-6 col-lg-3";
        //     } else if($colcount == 3) {
        //         $classes .= "col-sm-6 col-lg-4";
        //     } else if($colcount == 2) {
        //         $classes .= "col-sm-6";
        //     }

        //     $footer['classes'] = $classes;
        // }
        //print_r($footer);
        return $footer;
    }

    /**
     * This function is used to get upcoming events.
     *
     * @return array of upcoming events
     */
    public static function get_events() {
        global $CFG;

        require_once($CFG->dirroot.'/calendar/lib.php');

        $filtercourse    = array();
        // Being displayed at site level. This will cause the filter to fall back to auto-detecting
        // the list of courses it will be grabbing events from.
        $filtercourse = calendar_get_default_courses();

        list($courses, $group, $user) = calendar_set_filters($filtercourse);

        $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
        if (isset($CFG->calendar_lookahead)) {
            $defaultlookahead = intval($CFG->calendar_lookahead);
        }
        $lookahead = get_user_preferences('calendar_lookahead', $defaultlookahead);
        // echo $lookahead;
        $defaultmaxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;
        if (isset($CFG->calendar_maxevents)) {
            $defaultmaxevents = intval($CFG->calendar_maxevents);
        }
        $maxevents = get_user_preferences('calendar_maxevents', $defaultmaxevents);
        // echo $maxevents;
        $events = calendar_get_upcoming($courses, $group, $user, $lookahead, $maxevents);
        return $events;
    }

    /**
     * The messagearea messages parameters.
     *
     * @return external_function_parameters
     * @since 3.2
     */
    public static function data_for_messagearea_messages_parameters() {
        return new external_function_parameters(
            array(
                'currentuserid' => new external_value(PARAM_INT, 'The current user\'s id'),
                'otheruserid' => new external_value(PARAM_INT, 'The other user\'s id'),
                'limitfrom' => new external_value(PARAM_INT, 'Limit from', VALUE_DEFAULT, 0),
                'limitnum' => new external_value(PARAM_INT, 'Limit number', VALUE_DEFAULT, 0),
                'newest' => new external_value(PARAM_BOOL, 'Newest first?', VALUE_DEFAULT, false),
                'timefrom' => new external_value(PARAM_INT,
                    'The timestamp from which the messages were created', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Get messagearea messages.
     *
     * @param int $currentuserid The current user's id
     * @param int $otheruserid The other user's id
     * @param int $limitfrom
     * @param int $limitnum
     * @param boolean $newest
     * @return stdClass
     * @throws moodle_exception
     * @since 3.2
     */
    public static function data_for_messagearea_messages($currentuserid, $otheruserid, $limitfrom = 0, $limitnum = 0, $newest = false, $timefrom = 0) {
        global $CFG, $PAGE, $USER;

        // Check if messaging is enabled.
        if (empty($CFG->messaging)) {
            throw new moodle_exception('disabled', 'message');
        }

        $systemcontext = context_system::instance();

        $params = array(
            'currentuserid' => $currentuserid,
            'otheruserid' => $otheruserid,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum,
            'newest' => $newest,
            'timefrom' => $timefrom,
        );

        // REQUIRED, but commented, because not working
        // \lib\externallib\external_api::validate_parameters(self::data_for_messagearea_messages_parameters(), $params);
        // \lib\externallib\external_api::validate_context($systemcontext);

        if (($USER->id != $currentuserid) && !has_capability('moodle/site:readallmessages', $systemcontext)) {
            throw new moodle_exception(get_string('you_do_not_have_permission_to_perform_this_action', 'theme_remui'));
        }

        if ($newest) {
            $sort = 'timecreated DESC';
        } else {
            $sort = 'timecreated ASC';
        }

        // We need to enforce a one second delay on messages to avoid race conditions of current
        // messages still being sent.
        //
        // There is a chance that we could request messages before the current time's
        // second has elapsed and while other messages are being sent in that same second. In which
        // case those messages will be lost.
        //
        // Instead we ignore the current time in the result set to ensure that second is allowed to finish.
        if (!empty($timefrom)) {
            $timeto = time() - 1;
        } else {
            $timeto = 0;
        }

        // No requesting messages from the current time, as stated above.
        if ($timefrom == time()) {
            $messages = [];
        } else {
            $messages = \core_message\api::get_messages($currentuserid, $otheruserid, $limitfrom,
                                                        $limitnum, $sort, $timefrom, $timeto);
        }

        $messages = new \core_message\output\messagearea\messages($currentuserid, $otheruserid, $messages);

        $renderer = $PAGE->get_renderer('core_message');
        return $messages->export_for_template($renderer);
    }

    // get activity navigation
    public static function get_activity_list() {
        global $COURSE, $PAGE;

        // return if no cm id
        if(!isset($PAGE->cm->id)) {
            return;
        }

        $modinfo = get_fast_modinfo($COURSE);
        $sections_data = $modinfo->sections;
        $excluded_mods = array('label');
        $count = 0; // to print section count in sidebar
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $sections = array();

        foreach($modinfo->get_section_info_all() as $mod => $value) {
            // return if sections does not have activities or section is hidden to current user
            if(!array_key_exists($mod, $modinfo->sections) || !$value->uservisible) {
              continue;
            }
            $section_name = $value->__get('name');
            // check if current section is being viewed
            $open_section = '';
            if(in_array($PAGE->cm->id, $sections_data[$mod])) {
              $open_section = 'open active';
            }

            // handle empty section heading
            if(empty($section_name) && $mod == 0) {
              $section_name = get_string('sectionnotitle', 'theme_remui');
            } elseif (empty($section_name)) {
              $section_name = get_string('sectiondefaulttitle', 'theme_remui').' '.($mod+1);
            }

            $sections[$count]['name'] = $section_name;
            $sections[$count]['open'] = $open_section;
            $sections[$count]['count'] = $count;

            // activities
            foreach($sections_data[$mod] as $activity_id) {
              $activity = $modinfo->get_cm($activity_id);
              $classes = '';
              $completioninfo = new \completion_info($COURSE);
              $activity_completion = $courserenderer->course_section_cm_completion($COURSE, $completioninfo, $activity, array());

              if(!in_array($activity->modname, $excluded_mods)) {
                  // check if current activity
                  $active = ' ';
                  if($PAGE->cm->id == $activity_id) {
                    $active = 'active ';
                  }

                  $completion = $completioninfo->is_enabled($activity);
                  if ($completion == COMPLETION_TRACKING_NONE) {
                      $classes = '';
                  } else {
                      $completiondata = $completioninfo->get_data($activity, true);
                      switch($completiondata->completionstate) {
                        case COMPLETION_INCOMPLETE:
                            $classes = 'incomplete';
                            break;
                        case COMPLETION_COMPLETE:
                            $classes = 'complete';
                            break;
                        case COMPLETION_COMPLETE_PASS:
                            $classes = 'complete';
                            break;
                        case COMPLETION_COMPLETE_FAIL:
                            $classes = 'fail';
                            break;
                      }
                  }

                  $sections[$count]['activity_list'][] = array(
                    'active' => $active,
                    'title'  => $courserenderer->course_section_cm_name_title($activity, array()),
                    'classes' => $classes
                  );
              }
            }
            $count++;
        }

        return $sections;
    }

    public static function quickmessage($contactid, $message) {

        global $USER, $DB;

        $otheruserid = $contactid;
        $otheruserobj = $DB->get_record('user', array('id' => $otheruserid));
        $messagebody = $message;
        if (!empty($message) && !empty($otheruserobj)) {
            message_post_message($USER, $otheruserobj, $messagebody, FORMAT_MOODLE);
            return 'success';
        } else {
            return 'failed';
        }
    }
}