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
 * Theme functions.
 *
 * @package    theme_emphasize
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post process the CSS tree.
 *
 * @param string $tree The CSS tree.
 * @param theme_config $theme The theme config object.
 */
function theme_emphasize_css_tree_post_processor($tree, $theme) {
    $prefixer = new theme_emphasize\autoprefixer($tree);
    $prefixer->prefix();
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_emphasize_get_extra_scss($theme) {
    return !empty($theme->settings->scss) ? $theme->settings->scss : '';
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_emphasize_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/emphasize/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/emphasize/scss/preset/plain.scss');
    } else if ($filename == 'defaultcustom.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/emphasize/scss/preset/defaultcustom.scss');
    } else if ($filename == 'plaincustom.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/emphasize/scss/preset/plaincustom.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_emphasize', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/emphasize/scss/preset/default.scss');
    }

    return $scss;
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_emphasize_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['brand-primary'],
        'blockheaderbg' => ['blockheaderbg'],
        'blocktextcolor' => ['blocktextcolor'],
        'linkcolor' => ['linkcolor'],
        'linkhovercolor' => ['linkhovercolor'],
        'btnradius' => ['btnradius'],
        'btnbordercolor' => ['btnbordercolor'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }
    
    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}

/*
 * function added by Raghuvaran Regarding Icons for Left side toggle links in 20-7-2017
 * this function need to be call in the layout files where we are showing the toggle
 * we need to add the below line in flat_navigation.mustache file 
 * <i class="site-menu-icon {{{remuiicon}}}" aria-hidden="true"></i>
 * according to the key values passed to the anchor tag the icons will be displayed
*/
function flatnav_icon_support($flatnav) {
    // Getting strings for privatefiles & competencies, because their keys are numeric in $PAGE-flatnav
    $pf = get_string('privatefiles');
    $cmpt = get_string('competencies', 'core_competency');
    $flatnav_new = array();
    foreach ($flatnav as $key => $value)
    {
        $flatnav_new[$key] = $value;
        switch ($value->key) {
            case 'myhome' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-dashboard'; break;
            case 'home': $flatnav_new[$key]->nav_drwer_icon = 'fa fa-home'; break;
            case 'calendar' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-calendar'; break;
            case 'mycourses' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-book'; break;
            case 'sitesettings' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-settings'; break;
            case 'addblock' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-plus-circle'; break;
            case 'badgesview' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-bookmark'; break;
            case 'participants' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-users'; break;
            case 'grades' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-star'; break;
            case 'coursehome' : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-book'; break;
            default : $flatnav_new[$key]->nav_drwer_icon = 'fa'; break;
        }
        switch($value->text) {
            case $pf : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-copy'; break;
            case $cmpt : $flatnav_new[$key]->nav_drwer_icon = 'fa fa-check-circle'; break;
        }
    }
    return $flatnav_new;
}


/**
 *Function added by Raghuvaran for sliders, profile and message pages images
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_emphasize_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    static $theme;
    $course = $course;
    $cm = $cm;
    if (empty($theme)) {
        $theme = theme_config::load('emphasize');
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        if($filearea === 'help_slide1'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if($filearea === 'help_slide2'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if($filearea === 'help_slide3'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if($filearea === 'help_slide4'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if($filearea === 'help_slide5'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        //code added by Raghuvaran
        }  else if($filearea === 'profileimage'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }  else if($filearea === 'courseimage'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }  else if($filearea === 'messageimage'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if($filearea === 'sliderone'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        }else if($filearea === 'loginbg'){
            $theme = theme_config::load('emphasize');
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}
