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
    $choices['defaultcustom.scss'] = 'defaultcustom.scss';
    $choices['plaincustom.scss'] = 'plaincustom.scss';

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

    // Variable $body-color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/brandcolor';
    $title = get_string('brandcolor', 'theme_emphasize');
    $description = get_string('brandcolor_desc', 'theme_emphasize');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Tables Header background color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/tblhdcolor';
    $title = get_string('tblhdcolor', 'theme_emphasize');
    $description = get_string('tblhd_desc', 'theme_emphasize');
    $default = '#03A9F4';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    // tables Header text color.
    // We use an empty default value because the default colour should come from the preset.
    $name = 'theme_emphasize/tbltextcolor';
    $title = get_string('tbltextcolor', 'theme_emphasize');
    $description = get_string('tbltext_desc', 'theme_emphasize');
    $default = '#fff';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    // Logo setting added bu Raghuvaran
    $name = 'theme_emphasize/logo';
    $title = get_string('logo', 'theme_emphasize');
    $description = get_string('logodesc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/logoheight';
    $title = get_string('logoheight', 'theme_emphasize');
    $description = get_string('logoheight_desc', 'theme_emphasize');
    $default = '42px';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/logowidth';
    $title = get_string('logowidth', 'theme_emphasize');
    $description = get_string('logowidth_desc', 'theme_emphasize');
    $default = '130px';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Code added for Marketings Spots by Raghuvaran
    //code added for Marketing Spots to be displayed on login page
    //Number of Marketing Spots.
    $name = 'theme_emphasize/marketingspots';
    $title = get_string('marketingspots', 'theme_emphasize');
    $description = get_string('marketingspotsdesc', 'theme_emphasize');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //First Marketing Spots Icon to be displayed on login page
    $name = 'theme_emphasize/firstmarketingspot_icon';
    $title = get_string('firstmarketingspot_icon', 'theme_emphasize');
    $description = get_string('firstmarketingspot_icon_desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'firstmarketingspot_icon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //First Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/first_ms_header';
    $title = get_string('first_ms_header', 'theme_emphasize');
    $description = get_string('first_ms_header_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    //First Marketing Spots HTML Content to be displayed on login page
    $name = 'theme_emphasize/first_ms_Content';
    $title = get_string('first_ms_Content', 'theme_emphasize');
    $description = get_string('first_ms_Content_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Icon to be displayed on login page
    $name = 'theme_emphasize/secmarketingspot_icon';
    $title = get_string('secmarketingspot_icon', 'theme_emphasize');
    $description = get_string('secmarketingspot_icon_desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'secmarketingspot_icon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/sec_ms_header';
    $title = get_string('sec_ms_header', 'theme_emphasize');
    $description = get_string('sec_ms_header_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    //Second Marketing Spots HTML Content to be displayed on login page
    $name = 'theme_emphasize/sec_ms_Content';
    $title = get_string('sec_ms_Content', 'theme_emphasize');
    $description = get_string('sec_ms_Content_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Third Marketing Spots Icon to be displayed on login page
    $name = 'theme_emphasize/third_ms_icon';
    $title = get_string('third_ms_icon', 'theme_emphasize');
    $description = get_string('third_ms_icon_desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'third_ms_icon');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/third_ms_header';
    $title = get_string('third_ms_header', 'theme_emphasize');
    $description = get_string('third_ms_header_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    //Second Marketing Spots HTML Content to be displayed on login page
    $name = 'theme_emphasize/third_ms_Content';
    $title = get_string('third_ms_Content', 'theme_emphasize');
    $description = get_string('third_ms_Content_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    // Must add the page after definiting all the settings!
    $settings->add($page);
    $page = new admin_settingpage('theme_emphasize_imagesettings',  get_string('imagesettings', 'theme_emphasize'));
    
    //loggin background image by Bunesh
        $name = 'theme_emphasize/loginbg';
        $title = get_string('loginbg', 'theme_emphasize');
        $description = get_string('loginbgdesc', 'theme_emphasize');
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbg');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
    // Slider Image1 banner image file setting.
       //code by bunesh
    $name = 'theme_emphasize/noofhelpslides';
    $title = get_string('noofhelpslides', 'theme_emphasize');
    $description = get_string('noofhelpslidesdesc', 'theme_emphasize');
    $choices = array(0 => 'No slide show', 1 => '1', 2 => '2', 3 => '3', 4 =>'4', 5 =>'5');
    $default = 5;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    //$setting = new admin_setting_configselect($name, $title, $description, array('No slide show', '1', '2', '3', '4', '5'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    $name = 'theme_emphasize/slide_image1';
    $title = get_string('slide_image1', 'theme_emphasize');
    $description = get_string('slide_image1desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide_image1');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/slider_text1';
    $title = get_string('slider_text1', 'theme_emphasize');
    $description = get_string('slider_text1_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //slider2 file setting.
    $name = 'theme_emphasize/slide_image2';
    $title = get_string('slide_image2', 'theme_emphasize');
    $description = get_string('slide_image2desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide_image2');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/slider_text2';
    $title = get_string('slider_text2', 'theme_emphasize');
    $description = get_string('slider_text2_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    $name = 'theme_emphasize/slide_image3';
    $title = get_string('slide_image3', 'theme_emphasize');
    $description = get_string('slide_image3desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide_image3');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/slider_text3';
    $title = get_string('slider_text3', 'theme_emphasize');
    $description = get_string('slider_text3_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //slider2 file setting.
    $name = 'theme_emphasize/slide_image4';
    $title = get_string('slide_image4', 'theme_emphasize');
    $description = get_string('slide_image4desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide_image4');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/slider_text4';
    $title = get_string('slider_text4', 'theme_emphasize');
    $description = get_string('slider_text4_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    $name = 'theme_emphasize/slide_image5';
    $title = get_string('slide_image5', 'theme_emphasize');
    $description = get_string('slide_image5desc', 'theme_emphasize');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide_image5');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    //Second Marketing Spots Header to be displayed on login page
    $name = 'theme_emphasize/slider_text5';
    $title = get_string('slider_text5', 'theme_emphasize');
    $description = get_string('slider_text5_desc', 'theme_emphasize');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    
    // code added by Raghuvaran for choices for selecting slider height
        $name = 'theme_emphasize/sliderhgt';
        $title = get_string('sliderhgt', 'theme_emphasize');
        $description = get_string('sliderhgtdesc', 'theme_emphasize');
        $choices = array(0 => '100px', 1 => '200px', 2 => '300px', 3 => '400px', 4 =>'500px', 5 =>'600px');
        $default = 3;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        //$setting = new admin_setting_configselect($name, $title, $description, array('No slide show', '1', '2', '3', '4', '5'));
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    // Must add the page after definiting all the settings!
    //$settings->add($page);
    
        $page->add(new admin_setting_heading(
        'theme_emphasize_pagebanner',
        get_string('pagebanner', 'theme_emphasize'),
        format_text(get_string('pagebannerdesc', 'theme_emphasize'), FORMAT_MARKDOWN))); 

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

    $settings->add($page);

    $page = new admin_settingpage('theme_emphasize_colorsetting',  get_string('colorsetting', 'theme_emphasize'));
    
        $name = 'theme_emphasize/blockheaderbg';
        $title = get_string('blockheaderbg', 'theme_emphasize');
        $description = get_string('blockheaderbg_desc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#03a9f4');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        
        // Block's Header text color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/blocktextcolor';
        $title = get_string('blocktextcolor', 'theme_emphasize');
        $description = get_string('blocktext_desc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#fff');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        //links color added by rizwana
        $name = 'theme_emphasize/linkcolor';
        $title = get_string('linkcolor', 'theme_emphasize');
        $description = get_string('linkcolordesc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#0072bc');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

         //links hover added by rizwana
        $name = 'theme_emphasize/linkhovercolor';
        $title = get_string('linkhovercolor', 'theme_emphasize');
        $description = get_string('linkhovercolordesc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#333');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        // Button's Header background color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/buttonbgcolor';
        $title = get_string('buttonbgcolor', 'theme_emphasize');
        $description = get_string('buttonbg_desc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#5bc0de');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        // Button's Header text color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/btntextcolor';
        $title = get_string('btntextcolor', 'theme_emphasize');
        $description = get_string('btntext_desc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#fff');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        
        // Button's Hover Header background color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/btnhoverbgcolor';
        $title = get_string('btnhoverbgcolor', 'theme_emphasize');
        $description = get_string('btnhover_desc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#31b0d5');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        
        // Button's Hover Header text color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/btnhovertextcolor';
        $title = get_string('btnhovertextcolor', 'theme_emphasize');
        $description = get_string('btnhovertext_desc', 'theme_emphasize');
        $setting = new admin_setting_configcolourpicker($name, $title, $description, '#fff');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //Custom SCSS to change the border color of each button.
        //author : K.Raghuvaran
        $name = 'theme_emphasize/btnbordercolor';
        $title = get_string('btnbordercolor', 'theme_emphasize');
        $description = get_string('btnbordercolor_desc', 'theme_emphasize');
        $default = '#269abc';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //Custom SCSS to change the Left side bar bg color of each button.
        //author : K.Raghuvaran
        $name = 'theme_emphasize/sidebarbgcolor';
        $title = get_string('sidebarbgcolor', 'theme_emphasize');
        $description = get_string('sidebarbgcolor_desc', 'theme_emphasize');
        $default = '#555658';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //Custom SCSS to change the Header bg color of each button.
        //author : K.Raghuvaran
        $name = 'theme_emphasize/headerbgcolor';
        $title = get_string('headerbgcolor', 'theme_emphasize');
        $description = get_string('headerbgcolor_desc', 'theme_emphasize');
        $default = '#F6F6F6';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //Custom SCSS to change the Body bg color of each button.
        //author : K.Raghuvaran
        $name = 'theme_emphasize/bodybgcolor';
        $title = get_string('bodybgcolor', 'theme_emphasize');
        $description = get_string('bodybgcolor_desc', 'theme_emphasize');
        $default = '#F6F6F6';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
    $settings->add($page);
    
    // Footer settings.
    $page = new admin_settingpage('theme_emphasize_footersetting',  get_string('footersetting', 'theme_emphasize'));
    
    // Social media settings
    $page->add(new admin_setting_heading(
        'theme_emphasize_socialmedia',
        get_string('socialmedia', 'theme_emphasize'),
        format_text(get_string('socialmediadesc', 'theme_emphasize'), FORMAT_MARKDOWN)));


        // $name = 'theme_emphasize/sociallinks';
        // $title = get_string('sociallinks', 'theme_emphasize');
        // $description = get_string('sociallinksdesc', 'theme_emphasize');
        // $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
        // $setting->set_updatedcallback('theme_reset_all_caches');
        // $page->add($setting);
        
        $name = 'theme_emphasize/facebook';
        $title = get_string('facebook', 'theme_emphasize');
        $description = get_string('facebookdesc', 'theme_emphasize');
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

        // Footer background color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/footerbgcolor';
        $title = get_string('footerbgcolor', 'theme_emphasize');
        $description = get_string('footerbg_desc', 'theme_emphasize');
        $default = '#555658';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        
        // Footer's text color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/footertextcolor';
        $title = get_string('footertextcolor', 'theme_emphasize');
        $description = get_string('footertext_desc', 'theme_emphasize');
        $default = '#fff';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // Footer's Links text color.
        // We use an empty default value because the default colour should come from the preset.
        $name = 'theme_emphasize/footerlinkcolor';
        $title = get_string('footerlinkcolor', 'theme_emphasize');
        $description = get_string('footerlink_desc', 'theme_emphasize');
        $default = '#00acdf';
        $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        // Footnote setting.
        $name = 'theme_emphasize/footnote';
        $title = get_string('footnote', 'theme_emphasize');
        $description = get_string('footnotedesc', 'theme_emphasize');
        $default = '';
        $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

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

        $name = 'theme_emphasize/fontselect';
        $title = get_string('fontselect', 'theme_emphasize');
        $description = get_string('fontselectdesc', 'theme_emphasize');
        $default = 'Open Sans';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //Custom SCSS to change the All Buttons Border radius.
        //author : K.Raghuvaran
        $name = 'theme_emphasize/btnsradius';
        $title = get_string('btnsradius', 'theme_emphasize');
        $description = get_string('btnsradius_desc', 'theme_emphasize');
        $default = '0px 0px 0px 0px';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
        
        //Custom SCSS to change the Block's Header radius.
        //author : K.Raghuvaran
        $name = 'theme_emphasize/blkradius';
        $title = get_string('blkradius', 'theme_emphasize');
        $description = get_string('blkradius_desc', 'theme_emphasize');
        $default = '8px 8px 0px 0px';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

    $settings->add($page);
}
