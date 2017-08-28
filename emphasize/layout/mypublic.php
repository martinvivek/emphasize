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
 * A two column layout for the Edwiser RemUI theme.
 *
 * @package   theme_emphasize
 * @copyright WisdmLabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->libdir . "/badgeslib.php");
//require_once($CFG->dirroot . "/local/costcenter/lib.php");      
global $USER, $DB,$PAGE;




// Get user's object from page url
$uid = optional_param('id', $USER->id, PARAM_INT);
$userobject = $DB->get_record('user', array('id' => $uid));
$systemcontext = context_system::instance();
//user_preference_allow_ajax_update('menubar_state', PARAM_ALPHA);
// check if sidebar is fold or unfold
// if (isloggedin()) {
//     //$menubar_state = get_user_preferences('menubar_state', 'unfold');
// } else {
//     //$menubar_state = 'unfold';
// }
$menubar_state = 'unfold';
$blockshtml = $OUTPUT->blocks('side-pre', array(), 'aside');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;

$extraclasses = [];
$extraclasses [] = 'page-profile site-menubar-'.$menubar_state.' site-menubar-fold-alt';

if($hasblocks) {
    $extraclasses [] = 'page-aside-fixed page-aside-right';
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$countries = get_string_manager()->get_list_of_countries();
// get the list of all country


if(!empty($userobject->country)) { // country field in user object is empty
    $tempArray[] = Array("keyName" => $userobject->country, "valName" => $countries[$userobject->country]);
    $tempArray[] = Array("keyName" => '', "valName" => 'Select a country...');
} else {
    $tempArray[] = Array("keyName" => '', "valName" => 'Select a country...');
}

foreach ($countries as $key => $value) {
     $tempArray[] = Array("keyName" => $key, "valName" => $value);
}

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    //'usercanmanage'             => \theme_remui\utility::check_user_admin_cap($userobject),
    'notcurrentuser'    => ($userobject->id != $USER->id)?true:false,
    'countries' => $tempArray,
    'userpicture' => $OUTPUT->get_user_picture($userobject, 35),
    //'imageone' => $OUTPUT->image_url('f1', 'theme'),
    //'footerdata' => \theme_emphasize\utility::get_footer_data() 
    //'fptextbox' => '<div class="fptextbox">'.format_text($PAGE->theme->settings->fptextbox).'</div>'
];

// prepare profile context

$hasinterests = false;
$hasbadges = false;
$onlypublic = true;
$aboutme = false;
$country = '';

$templatecontext['user'] = $userobject;
$templatecontext['user']->description  = strip_tags($userobject->description);

// about me tab data
$interests = \core_tag_tag::get_item_tags('core', 'user', $userobject->id);
foreach ($interests as $interest) {
    $hasinterests = true;
    $aboutme = true;
    $templatecontext['user']->interests[] = $interest;
}
$templatecontext['user']->hasinterests    = $hasinterests;

// badges
if($CFG->enablebadges) {
    $badges = badges_get_user_badges($userobject->id, 0, null, null, null, $onlypublic);
    if ($badges) {
        $hasbadges = true;
        $count = 0;
        foreach ($badges as $key => $badge) {
            $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
            $templatecontext['user']->badges[$count]['imageurl'] = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
            $templatecontext['user']->badges[$count]['name'] = $badge->name;
            $templatecontext['user']->badges[$count]['link'] = new moodle_url('/badges/badge.php?hash=' . $badge->uniquehash);
            $templatecontext['user']->badges[$count]['desc'] = $badge->description;
            $count++;
        }
    }
}
$templatecontext['user']->hasbadges = $hasbadges;


if(!empty($userobject->country)) {
    $country = get_string($userobject->country, 'countries');
}
$email = $userobject->email;
$templatecontext['user']->location  = $userobject->address.$userobject->city.$country;
$templatecontext['user']->instidept = $userobject->department.$userobject->institution;
// if(!empty($templatecontext['user']->location) || !empty($templatecontext['user']->instidept)) {
    $aboutme = true;
// }
$templatecontext['user']->aboutme = $aboutme;
$templatecontext['flatnavigation'] = flatnav_icon_support($PAGE->flatnav);
echo $OUTPUT->render_from_template('theme_emphasize/mypublic', $templatecontext);

$PAGE->requires->strings_for_js(array('enterfirstname', 'enterlastname', 'enteremailid', 'enterproperemailid', 'detailssavedsuccessfully', 'actioncouldnotbeperformed'), 'theme_emphasize');