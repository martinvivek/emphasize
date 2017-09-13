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
 * A two column layout for the emphasize theme.
 *
 * @package   theme_emphasize
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

$is_loggedin = isloggedin();
$is_loggedin = empty($is_loggedin) ? false : true;

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
//echo "hii";
// print_object($regionmainsettingsmenu);
// echo "hioi";
// exit;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'isloggedin' => $is_loggedin,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

//$templatecontext['flatnavigation'] = $PAGE->flatnav;

// $pagetype = $this->page->pagetype;
// $layout = $this->page->pagelayout;
$templatecontext['flatnavigation'] = flatnav_icon_support($PAGE->flatnav);
// if ($this->page->pagelayout == 'standard' && $this->page->pagetype == 'message-index') {
// echo $OUTPUT->render_from_template('theme_emphasize/message', $templatecontext);
// }elseif ($this->page->pagelayout == 'coursecategory' && $this->page->pagetype == 'course-index-category') {
    //echo "hii";exit;
echo $OUTPUT->render_from_template('theme_emphasize/course_image', $templatecontext);
// } else {
//     //echo "bye";exit;
// echo $OUTPUT->render_from_template('theme_emphasize/columns2', $templatecontext);
// }


