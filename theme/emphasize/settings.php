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
 * @package   theme_emphasize
 * @copyright 2016 Ryan Wyllie
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_emphasize_admin_settingspage_tabs('themesettingemphasize', get_string('configtitle', 'theme_emphasize'));
    $page = new admin_settingpage('theme_emphasize_general', get_string('generalsettings', 'theme_emphasize'));

    // Preset.
    $name = 'theme_emphasize/preset';
    $title = get_string('preset', 'theme_emphasize');
    $description = get_string('preset_desc', 'theme_emphasize');
    $default = 'default.scss';

    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'theme_emphasize', 'preset', 0, 'itemid, filepath, filename', false);

    $choices = [];
    foreach ($files as $file) {
        $choices[$file->get_filename()] = $file->get_filename();
    }
    // These are the built in presets.
    $choices['default.scss'] = 'default.scss';
    $choices['plain.scss'] = 'plain.scss';

    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Preset files setting.
    $name = 'theme_emphasize/presetfiles';
    $title = get_string('presetfiles','theme_emphasize');
    $description = get_string('presetfiles_desc', 'theme_emphasize');

    $setting = new admin_setting_configstoredfile($name, $title, $description, 'preset', 0,
        array('maxfiles' => 20, 'accepted_types' => array('.scss')));
    $page->add($setting);

     // Profile Page banner image file setting.
        $name = 'theme_emphasize/profileimage';
        $title = get_string('profileimage', 'theme_emphasize');
        $description = get_string('profileimagedesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'profileimage');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
    // Course Page banner image file setting.
        $name = 'theme_emphasize/courseimage';
        $title = get_string('courseimage', 'theme_emphasize');
        $description = get_string('courseimagedesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'courseimage');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
    // Message Page banner image file setting.
        $name = 'theme_emphasize/messageimage';
        $title = get_string('messageimage', 'theme_emphasize');
        $description = get_string('messageimagedesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'messageimage');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    // Variable $body-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/brandcolor';
    $title = get_string('brandcolor', 'theme_emphasize');
    $description = get_string('brandcolor_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

    // Advanced settings.
    $page = new admin_settingpage('theme_emphasize_advanced', get_string('advancedsettings', 'theme_emphasize'));

    // Raw SCSS to include before the content.
    $setting = new admin_setting_scsscode('theme_emphasize/scsspre',
        get_string('rawscsspre', 'theme_emphasize'), get_string('rawscsspre_desc', 'theme_emphasize'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_emphasize/scss', get_string('rawscss', 'theme_emphasize'),
        get_string('rawscss_desc', 'theme_emphasize'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
    
    // New Settings added by Raghuvaran
    // Block's Header background color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/blockheaderbg';
    $title = get_string('blockheaderbg', 'theme_emphasize');
    $description = get_string('blockheaderbg_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Block's Header text color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/blocktextcolor';
    $title = get_string('blocktextcolor', 'theme_emphasize');
    $description = get_string('blocktext_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Button's Header background color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/buttonbgcolor';
    $title = get_string('buttonbgcolor', 'theme_emphasize');
    $description = get_string('buttonbg_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Button's Header text color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/btntextcolor';
    $title = get_string('btntextcolor', 'theme_emphasize');
    $description = get_string('btntext_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Button's Hover Header background color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/btnhoverbgcolor';
    $title = get_string('btnhoverbgcolor', 'theme_emphasize');
    $description = get_string('btnhover_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Button's Hover Header text color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/btnhovertextcolor';
    $title = get_string('btnhovertextcolor', 'theme_emphasize');
    $description = get_string('btnhovertext_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Custom SCSS to include after the content.
    $setting = new admin_setting_scsscode('theme_emphasize/btnradius', get_string('customscss', 'theme_emphasize'),
        get_string('btnradius_desc', 'theme_emphasize'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    // Footer background color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/footerbgcolor';
    $title = get_string('footerbgcolor', 'theme_emphasize');
    $description = get_string('footerbg_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Footer's text color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/footertextcolor';
    $title = get_string('footertextcolor', 'theme_emphasize');
    $description = get_string('footertext_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
    // Footer's Links text color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/footerlinkcolor';
    $title = get_string('footerlinkcolor', 'theme_emphasize');
    $description = get_string('footerlink_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
   
        
    // Slider Image1 banner image file setting.
        $name = 'theme_emphasize/sliderone';
        $title = get_string('sliderone', 'theme_emphasize');
        $description = get_string('slideronedesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderone');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/slideronetext';
        $title = get_string('slideronetext', 'theme_emphasize');
        $description = get_string('sldronetxtdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/slidertwo';
        $title = get_string('slidertwo', 'theme_emphasize');
        $description = get_string('slidertwodesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'slidertwo');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/slidertwotext';
        $title = get_string('slidertwotext', 'theme_emphasize');
        $description = get_string('sldrtwotxtdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/sliderthree';
        $title = get_string('sliderthree', 'theme_emphasize');
        $description = get_string('sliderthreedesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderthree');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/sliderthreetxt';
        $title = get_string('sliderthreetxt', 'theme_emphasize');
        $description = get_string('sldrthreetxtdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/sliderfourth';
        $title = get_string('sliderfourth', 'theme_emphasize');
        $description = get_string('sliderfourthdesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderfourth');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/sliderfourthtxt';
        $title = get_string('sliderfourthtxt', 'theme_emphasize');
        $description = get_string('sldrfourthtxtdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/sliderfifth';
        $title = get_string('sliderfifth', 'theme_emphasize');
        $description = get_string('sliderfifthdesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'sliderfifth');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/sliderfifthtxt';
        $title = get_string('sliderfifthtxt', 'theme_emphasize');
        $description = get_string('sldrfifthtxtdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //code added for Social links url
        //Marketing Spots
        //$name = 'theme_emphasize/sociallinks';
        //$title = get_string('sociallinks', 'theme_emphasize');
        //$description = get_string('sociallinksdesc', 'theme_emphasize');
        //$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
        //$setting->set_updatedcallback('theme_reset_all_caches');
        //$settings->add($setting);
        
        $name = 'theme_emphasize/fburl';
        $title = get_string('fburl', 'theme_emphasize');
        $description = get_string('fburldesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/twitter';
        $title = get_string('twitter', 'theme_emphasize');
        $description = get_string('twitterdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/linkedin';
        $title = get_string('linkedin', 'theme_emphasize');
        $description = get_string('linkedindesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/youtube';
        $title = get_string('youtube', 'theme_emphasize');
        $description = get_string('youtubedesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        $name = 'theme_emphasize/instagram';
        $title = get_string('instagram', 'theme_emphasize');
        $description = get_string('instagramdesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
}
