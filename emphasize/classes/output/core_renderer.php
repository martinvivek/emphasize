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

namespace theme_emphasize\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use paging_bar;
use context_course;
use pix_icon;
use course_in_list;
use coursecat_helper;
use user_picture;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_emphasize
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \core_renderer {

    /** @var custom_menu_item language The language menu if created */
    //use core_renderer_toolbox;
    protected $language = null;

    /**
     * Outputs the opening section of a box.
     *
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function box_start($classes = 'generalbox', $id = null, $attributes = array()) {
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        return parent::box_start($classes . ' p-y-1', $id, $attributes);
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE;

        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => 'row'));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::start_div('card');
        $html .= html_writer::start_div('card-block');
        // $html .= html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        // $html .= html_writer::start_div('pull-xs-left');
        // $html .= $this->context_header();
        // $html .= html_writer::end_div();
        $pageheadingbutton = $this->page_heading_button();
        if (empty($PAGE->layout_options['nonavbar'])) {
            $html .= html_writer::start_div('clearfix w-100 pull-xs-left', array('id' => 'page-navbar'));
            $html .= html_writer::tag('div', $this->navbar(), array('class' => 'breadcrumb-nav'));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right');
            $html .= html_writer::end_div();
        } else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right');
        }
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');
        return $html;
    }

    /**
     * The standard tags that should be included in the <head> tag
     * including a meta description for the front page
     *
     * @return string HTML fragment.
     */
    public function standard_head_html() {
        global $SITE, $PAGE;

        $output = parent::standard_head_html();
        if ($PAGE->pagelayout == 'frontpage') {
            $summary = s(strip_tags(format_text($SITE->summary, FORMAT_HTML)));
            if (!empty($summary)) {
                $output .= "<meta name=\"description\" content=\"$summary\" />\n";
            }
        }

        return $output;
    }

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        return $this->render_from_template('core/navbar', $this->page->navbar);
    }

    /**
     * We don't like these...
     *
     */
    public function edit_button(moodle_url $url) {
        return '';
    }

    /**
     * Override to inject the logo.
     *
     * @param array $headerinfo The header info.
     * @param int $headinglevel What level the 'h' tag will be.
     * @return string HTML for the header bar.
     */
    public function context_header($headerinfo = null, $headinglevel = 1) {
        global $SITE;

        if ($this->should_display_main_logo($headinglevel)) {
            $sitename = format_string($SITE->fullname, true, array('context' => context_course::instance(SITEID)));
            return html_writer::div(html_writer::empty_tag('img', [
                'src' => $this->get_logo_url(null, 150), 'alt' => $sitename]), 'logo');
        }

        return parent::context_header($headerinfo, $headinglevel);
    }

    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        return parent::get_compact_logo_url(null, 70);
    }

    /**
     * Whether we should display the main logo.
     *
     * @return bool
     */
    public function should_display_main_logo($headinglevel = 1) {
        global $PAGE;

        // Only render the logo if we're on the front page or login page and the we have a logo.
        $logo = $this->get_logo_url();
        if ($headinglevel == 1 && !empty($logo)) {
            if ($PAGE->pagelayout == 'frontpage' || $PAGE->pagelayout == 'login') {
                return true;
            }
        }

        return false;
    }
    /**
     * Whether we should display the logo in the navbar.
     *
     * We will when there are no main logos, and we have compact logo.
     *
     * @return bool
     */
    public function should_display_navbar_logo() {
        $logo = $this->get_compact_logo_url();
        return !empty($logo) && !$this->should_display_main_logo();
    }

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    /**
     * We want to show the custom menus as a list of links in the footer on small screens.
     * Just return the menu object exported so we can render it differently.
     */
    public function custom_menu_flat() {
        global $CFG;
        $custommenuitems = '';

        if (empty($custommenuitems) && !empty($CFG->custommenuitems)) {
            $custommenuitems = $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $custommenu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        return $custommenu->export_for_template($this);
    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/custom_menu_item', $context);
        }

        return $content;
    }

    /**
     * This code renders the navbar button to control the display of the custom menu
     * on smaller screens.
     *
     * Do not display the button if the menu is empty.
     *
     * @return string HTML fragment
     */
    public function navbar_button() {
        global $CFG;

        if (empty($CFG->custommenuitems) && $this->lang_menu() == '') {
            return '';
        }

        $iconbar = html_writer::tag('span', '', array('class' => 'icon-bar'));
        $button = html_writer::tag('a', $iconbar . "\n" . $iconbar. "\n" . $iconbar, array(
            'class'       => 'btn btn-navbar',
            'data-toggle' => 'collapse',
            'data-target' => '.nav-collapse'
        ));
        return $button;
    }

    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $data = $tabtree->export_for_template($this);
        return $this->render_from_template('core/tabtree', $data);
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * @param tabobject $tabobject
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tab) {
        throw new coding_exception('Tab objects should not be directly rendered.');
    }

    /**
     * Prints a nice side block with an optional header.
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid;
        $context->dockable = $bc->dockable;
        $context->id = $id;
        $context->hidden = $bc->collapsible == block_contents::HIDDEN;
        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->type = $bc->attributes['data-block'];
        $context->title = $bc->title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);
        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }

        return $this->render_from_template('core/block', $context);
    }

    /**
     * Returns the CSS classes to apply to the body tag.
     *
     * @since Moodle 2.5.1 2.6
     * @param array $additionalclasses Any additional classes to apply.
     * @return string
     */
    public function body_css_classes(array $additionalclasses = array()) {
        return $this->page->bodyclasses . ' ' . implode(' ', $additionalclasses);
    }

    /**
     * Renders preferences groups.
     *
     * @param  preferences_groups $renderable The renderable
     * @return string The output.
     */
    public function render_preferences_groups(preferences_groups $renderable) {
        return $this->render_from_template('core/preferences_groups', $renderable);
    }

    /**
     * Renders an action menu component.
     *
     * @param action_menu $menu
     * @return string HTML
     */
    public function render_action_menu(action_menu $menu) {

        // We don't want the class icon there!
        foreach ($menu->get_secondary_actions() as $action) {
            if ($action instanceof \action_menu_link && $action->has_class('icon')) {
                $action->attributes['class'] = preg_replace('/(^|\s+)icon(\s+|$)/i', '', $action->attributes['class']);
            }
        }

        if ($menu->is_empty()) {
            return '';
        }
        $context = $menu->export_for_template($this);

        return $this->render_from_template('core/action_menu', $context);
    }

    /**
     * Implementation of user image rendering.
     *
     * @param help_icon $helpicon A help icon instance
     * @return string HTML fragment
     */
    protected function render_help_icon(help_icon $helpicon) {
        $context = $helpicon->export_for_template($this);
        return $this->render_from_template('core/help_icon', $context);
    }

    /**
     * Renders a single button widget.
     *
     * This will return HTML to display a form containing a single button.
     *
     * @param single_button $button
     * @return string HTML fragment
     */
    protected function render_single_button(single_button $button) {
        return $this->render_from_template('core/single_button', $button->export_for_template($this));
    }

    /**
     * Renders a paging bar.
     *
     * @param paging_bar $pagingbar The object.
     * @return string HTML
     */
    protected function render_paging_bar(paging_bar $pagingbar) {
        // Any more than 10 is not usable and causes wierd wrapping of the pagination in this theme.
        $pagingbar->maxdisplay = 10;
        return $this->render_from_template('core/paging_bar', $pagingbar->export_for_template($this));
    }

    /**
     * Renders the login form.
     *
     * @param \core_auth\output\login $form The renderable.
     * @return string
     */
    public function render_login(\core_auth\output\login $form) {
        global $SITE;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]);

        return $this->render_from_template('core/login', $context);
    }

    /**
     * Render the login signup form into a nice template for the theme.
     *
     * @param mform $form
     * @return string
     */
    public function render_login_signup_form($form) {
        global $SITE;

        $context = $form->export_for_template($this);
        $url = $this->get_logo_url();
        if ($url) {
            $url = $url->out(false);
        }
        $context['logourl'] = $url;
        $context['sitename'] = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]);

        return $this->render_from_template('core/signup_form_layout', $context);
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the course administration, only on the course main page.
     *
     * @return string
     */
    public function context_header_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        $items = $this->page->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
                !empty($currentnode) &&
                ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($this->page->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE &&
                !$courseformat->has_view_page()) {

            $this->page->navigation->initialise();
            $activenode = $this->page->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                    $activenode->type == navigation_node::TYPE_RESOURCE)) {

                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
                !empty($currentnode) &&
                $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
                !empty($currentnode) &&
                ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }


        if ($showfrontpagemenu) {
            $settingsnode = $this->page->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $this->page->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $this->page->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $this->page->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $this->render($menu);
    }

    /**
     * This is an optional menu that can be added to a layout by a theme. It contains the
     * menu for the most specific thing from the settings block. E.g. Module administration.
     *
     * @return string
     */
    public function region_main_settings_menu() {
        $context = $this->page->context;
        $menu = new action_menu();

        if ($context->contextlevel == CONTEXT_MODULE) {

            $this->page->navigation->initialise();
            $node = $this->page->navigation->find_active_node();
            $buildmenu = false;
            // If the settings menu has been forced then show the menu.
            if ($this->page->is_settings_menu_forced()) {
                $buildmenu = true;
            } else if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                    $node->type == navigation_node::TYPE_RESOURCE)) {

                $items = $this->page->navbar->get_items();
                $navbarnode = end($items);
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($navbarnode && ($navbarnode->key === $node->key && $navbarnode->type == $node->type)) {
                    $buildmenu = true;
                }
            }
            if ($buildmenu) {
                // Get the course admin node from the settings navigation.
                $node = $this->page->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($this->page->pagetype === 'course-index-category') {
                $node = $this->page->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else {
            $items = $this->page->navbar->get_items();
            $navbarnode = end($items);

            if ($navbarnode && ($navbarnode->key === 'participants')) {
                $node = $this->page->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }

            }
        }
        return $this->render($menu);
    }
//function added for profile page image by Bunesh
    public function profileimage() {
        global $CFG;

        $profileimgurl = '';
         //$profileimage .='<div class="item">';
            $profileimgurl = $this->page->theme->setting_file_url('profileimage', 'profileimage');
            //$pimage5 = html_writer::empty_tag('img', array('src' => $profileimgurl, 'class' => 's5'));
            //$profileimage.= $pimage5;
        //$profileimage.='</div>';
        return $profileimgurl;
    }
    
    //function added for profile page image by Bunesh
    public function courseimage() {
        global $CFG;

        $courseimageurl = '';
            $courseimageurl = $this->page->theme->setting_file_url('courseimage', 'courseimage');
        
        return $courseimageurl;
    }
    
    //function added for profile page image by Bunesh
    public function messageimage() {
        global $CFG;

        $messageimageurl = '';
            $messageimageurl = $this->page->theme->setting_file_url('messageimage', 'messageimage');
        
        return $messageimageurl;
    }
    //function added for font for moodle by Raghuvaran
    public function html_head_fontfamily() {
        $fontselected = 'OpenSans';
        $setfont = $this->page->theme->settings->fontselect;
        if (!empty($setfont)) {
            $fontselected = $setfont;
            $fonturl = "<link href='https://fonts.googleapis.com/css?family=".$fontselected."|".$fontselected."' rel='stylesheet' type='text/css'>";
        } else {
            $fontselected = 'OpenSans';
            $fonturl = "<link href='https://fonts.googleapis.com/css?family=".$fontselected."|".$fontselected."' rel='stylesheet' type='text/css'>";
        }
        return $fonturl;
    }
    
    //function added for calling a css file for a selcted mustache files
    //author Raghuvaran
    public function html_head_bootstrapcss() {
        global $CFG;
        $cssselected = '';
        $cssselected .= "<link href='".$CFG->wwwroot."/theme/emphasize/scss/moodle/bootstrap.min.css' rel='stylesheet' type='text/css'>";;
        return $cssselected;
    }
    
    //function added for profile page image by Bunesh
    public function logginbackground() {
        global $CFG;
        $logginbgimgurl = '';
            $logginbgimgurl = $this->page->theme->setting_file_url('loginbg', 'loginbg');
            if(empty($logginbgimgurl)){
                $logginbgimgurl = $this->image_url('login_background','theme_emphasize');
            }
        return $logginbgimgurl;
    }
    
    
    // code for slider by Bunesh
    public function should_render_frontpage_slideshow() {
        // Only render the slideshow on the front page and login page
        if ($this->page->theme->settings->noofhelpslides > 0) {
            return true;
        }else{
            return false;
        }
    }
    
    
    //function written by Raghuvaran for Carousal slider on login page
    public function loginpage_slider() {
        global $CFG;
        $slider_height = $this->get_slider_hgt();
        $logocontainer = '';
        $text1 = $this->page->theme->settings->slider_text1;
        $text2 = $this->page->theme->settings->slider_text2;
        $text3 = $this->page->theme->settings->slider_text3;
        $text4 = $this->page->theme->settings->slider_text4;
        $text5 = $this->page->theme->settings->slider_text5;
        if(!empty($text1)){
            $logocontainertxt1 = '<div class="carousel-caption carousel_top">';
            $logocontainertxt1 .= '<h3>'.$text1.'</h3>';
            $logocontainertxt1 .= '</div>';                         
        }
        if(!empty($text2)){
            $logocontainertxt2 = '<div class="carousel-caption carousel_top">';
            $logocontainertxt2 .= '<h3>'.$text2.'</h3>';
            $logocontainertxt2 .= '</div>';                         
        }
        if(!empty($text3)){
            $logocontainertxt3 = '<div class="carousel-caption carousel_top">';
            $logocontainertxt3 .= '<h3>'.$text3.'</h3>';
            $logocontainertxt3 .= '</div>';                         
        }
        if(!empty($text4)){
            $logocontainertxt4 = '<div class="carousel-caption carousel_top">';
            $logocontainertxt4 .= '<h3>'.$text4.'</h3>';
            $logocontainertxt4 .= '</div>';                         
        }
        if(!empty($text5)){
            $logocontainertxt5 = '<div class="carousel-caption carousel_top">';
            $logocontainertxt5 .= '<h3>'.$text5.'</h3>';
            $logocontainertxt5 .= '</div>';                         
        }
        $logocontainer .= "<style> .carousel-inner > .item > img,.carousel-inner > .item > a > img {width: 100%;margin: auto;}
                           </style>";
    
        $logocontainer .= '<div id="myCarousel" class="carousel slide" data-ride="carousel">';
        $logocontainer .= '<!-- Indicators -->';
        $logocontainer .= '<ol class="carousel-indicators bottomCorousal">';
        if(!empty($this->page->theme->settings->slide_image1) && ($this->page->theme->settings->noofhelpslides >= 1)){
            $logocontainer .= '<li data-target="#myCarousel" data-slide-to="0" class="active"></li>';
        }
        if(!empty($this->page->theme->settings->slide_image2) && ($this->page->theme->settings->noofhelpslides >= 2)){
        $logocontainer .= '<li data-target="#myCarousel" data-slide-to="1"></li>';
        }
        if(!empty($this->page->theme->settings->slide_image3) && ($this->page->theme->settings->noofhelpslides >= 3)){
        $logocontainer .= '<li data-target="#myCarousel" data-slide-to="2"></li>';
        }
        if(!empty($this->page->theme->settings->slide_image4) && ($this->page->theme->settings->noofhelpslides >= 4)){
            $logocontainer .= '<li data-target="#myCarousel" data-slide-to="3"></li>';
        }
        if(!empty($this->page->theme->settings->slide_image5) && ($this->page->theme->settings->noofhelpslides >= 5)){
            $logocontainer .= '<li data-target="#myCarousel" data-slide-to="4"></li>';
        }
        $logocontainer .= '</ol>';
        $logocontainer .= '<div class="carousel-inner" role="listbox">';
            //$logocontainer .= '<div class="carousel-caption carousel_top">';
            //$logocontainer .= '<h3>Welcome to Illume - an interactive e-Learning Platform</h3>';
            //$logocontainer .= '<button class="show_login_button login_btn" onclick="ShowLogin();">Login to Learn</button>';/*login button*/
            //$logocontainer .= '</div>';
        if($this->should_render_frontpage_slideshow() == 1){
            if(!empty($this->page->theme->settings->slide_image1) &&
                ($this->page->theme->settings->noofhelpslides >= 1)){
                    $logocontainer .= '<div class="item active">';
                        $logocontainer .= $logocontainertxt1;
                        //$imageurl1= $CFG->wwwroot.'/theme/clean/pix/schedule.png';
                        $imageurl1 = $this->page->theme->setting_file_url('slide_image1', 'slide_image1');
                        $image1 = html_writer::empty_tag('img', array('src' => $imageurl1, 'class' => 's1 '.$slider_height));
                        $logocontainer .= $image1;
                    $logocontainer .='</div>';
                }
                                        if(!empty($this->page->theme->settings->slide_image2) && ($this->page->theme->settings->noofhelpslides >= 2)){
            $logocontainer .='<div class="item">';
                                            $logocontainer .= $logocontainertxt2;
                                            //$imageurl2= $CFG->wwwroot.'/theme/clean/pix/schedule.png';
                                            $imageurl2 = $this->page->theme->setting_file_url('slide_image2', 'slide_image2');
                                            $image2 = html_writer::empty_tag('img', array('src' => $imageurl2, 'class' => 's2 '.$slider_height));
                                            $logocontainer .= $image2;
             $logocontainer .='</div>';
                                        }
                                        if(!empty($this->page->theme->settings->slide_image3) && ($this->page->theme->settings->noofhelpslides >= 3)){
             $logocontainer .='<div class="item">';
                                            $logocontainer .= $logocontainertxt3;
                                            //$imageurl3= $CFG->wwwroot.'/theme/clean/pix/schedule.png';
                                            $imageurl3 = $this->page->theme->setting_file_url('slide_image3', 'slide_image3');
                                            $image3 = html_writer::empty_tag('img', array('src' => $imageurl3, 'class' => 's3 '.$slider_height));
                                            $logocontainer .= $image3;
             $logocontainer .='</div>';
                                        }
                                        if(!empty($this->page->theme->settings->slide_image4) && ($this->page->theme->settings->noofhelpslides >= 4)){
             $logocontainer .='<div class="item">';
                                            $logocontainer .= $logocontainertxt4;
                                            //$imageurl4= $CFG->wwwroot.'/theme/clean/pix/schedule.png';
                                            $imageurl4 = $this->page->theme->setting_file_url('slide_image4', 'slide_image4');
                                            $image4 = html_writer::empty_tag('img', array('src' => $imageurl4, 'class' => 's4 '.$slider_height));
                                            $logocontainer .= $image4;
            $logocontainer .='</div>';
                                        }
                                        if(!empty($this->page->theme->settings->slide_image5) && ($this->page->theme->settings->noofhelpslides >= 5)){
            $logocontainer .='<div class="item">';
                                            $logocontainer .= $logocontainertxt5;
                                            //$imageurl5= $CFG->wwwroot.'/theme/clean/pix/schedule.png';
                                            $imageurl5 = $this->page->theme->setting_file_url('slide_image5', 'slide_image5');
                                            $image5 = html_writer::empty_tag('img', array('src' => $imageurl5, 'class' => 's5 '.$slider_height));
                                            $logocontainer .= $image5;
            $logocontainer .='</div>';
                                        }
            $logocontainer .= '</div>';/*courousel -inner div closing*/
            
            
        }
        $logocontainer .= '</div>';/*myCarousel div closing*/
        return $logocontainer;
    }
    
    //Function added for logo displaying throught out the site
    public function logo() {
        global $CFG;
        $logo = '';
        $logopath = $this->page->theme->setting_file_url('logo', 'logo');
        if(!empty($logopath)) {
            $logo .= $logopath;
        }
        return $logopath;
    }
    //slider code ends here
    /**
     * Take a node in the nav tree and make an action menu out of it.
     * The links are injected in the action menu.
     *
     * @param action_menu $menu
     * @param navigation_node $node
     * @param boolean $indent
     * @param boolean $onlytopleafnodes
     * @return boolean nodesskipped - True if nodes were skipped in building the menu
     */
    private function build_action_menu_from_navigation(action_menu $menu,
                                                       navigation_node $node,
                                                       $indent = false,
                                                       $onlytopleafnodes = false) {
        $skipped = false;
        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {
            if ($menuitem->display) {
                if ($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if ($menuitem->action) {
                    if ($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if (!empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }
                } else {
                    if ($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);
                }
                if ($indent) {
                    $link->add_class('m-l-1');
                }
                if (!empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }
        }
        return $skipped;
    }
    
    /**
     *Function added by rizwana for footer social links
     * Returns a social links.
     *
     * @return social links.
     */
    public function social_icons() {
        global $PAGE;

        $hasfacebook    = (empty($PAGE->theme->settings->facebook)) ? false : $PAGE->theme->settings->facebook;
        $hastwitter     = (empty($PAGE->theme->settings->twitter)) ? false : $PAGE->theme->settings->twitter;
        $haslinkedin    = (empty($PAGE->theme->settings->linkedin)) ? false : $PAGE->theme->settings->linkedin;
        $hasyoutube     = (empty($PAGE->theme->settings->youtube)) ? false : $PAGE->theme->settings->youtube;
        $hasinstagram   = (empty($PAGE->theme->settings->instagram)) ? false : $PAGE->theme->settings->instagram;

        $socialcontext = [

            // If any of the above social networks are true, sets this to true.
            'hassocialnetworks' => ($hasfacebook || $hastwitter 
                 || $haslinkedin  || $hasyoutube ||  $hasinstagram
                 ) ? true : false,

            'socialicons' => array(
                    'facebook' => $hasfacebook,
                    'twitter'  => $hastwitter,
                    'linkedin' => $haslinkedin,
                    'youtube'    => $hasyoutube,
                    'instagram'  => $hasinstagram,
            )
        ];
        return $this->render_from_template('theme_emphasize/socialicons', $socialcontext);
    }
    /**
     * Secure login info.
     *
     * @return string
     */
    public function secure_login_info() {
        return $this->login_info(false);
    }

    /**
     * Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG,$DB;
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/lib/moodlelib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = array('class'=> 'usermenu nav-item dropdown user-menu login-menu pull-right');
        if (!$withlinks) {
            $usermenuclasses = array('class'=> ' nav-item dropdown user-menu login-menu withoutlinks');
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        //$login_dropdown = \theme_remui\toolbox::get_setting('navlogin_popup');
        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        $forgotpasswordurl = new moodle_url('/login/forgot_password.php');
        $loginurl_datatoggle = '';
        //if($login_dropdown) {
            $loginurl = '#';
            $loginurl_datatoggle = 'data-toggle="dropdown"';
        //}
        // sign in popup
        $signinformhtml = '<ul class="dropdown-menu w-350 p-15" role="menu">
                    <span class="pop_show"></span>
                    <form class="mb-0" action="'.get_login_url().'" method="post" id="login">
                       <div class="input-group form-group">
                      <span class="input-group-addon bg-gray"><i class="fa fa-user text-muted"></i>&nbsp;</span>
                      <input type="text" class="form-control" id="username" name="username" placeholder='.get_string('username', 'theme_emphasize').'>
                    </div>

                    <div class="input-group form-group">
                      <span class="input-group-addon bg-gray"><i class="fa fa-key text-muted"></i></span>
                      <input type="password" name="password" id="password" class="form-control" placeholder='.get_string('password','theme_emphasize').'>
                    </div>
                    <div class="form-group">
                      <div class="checkbox">
                        <label class="text-muted">
                        
                          <input type="checkbox" id="rememberusername" name="rememberusername" value="1" />
                          <label for="rememberusername">'.get_string('rememberusername', 'theme_emphasize').'</label>
                        </label>
                      </div>
                    </div>    
                     <li class="box-footer">
                        <div class="pull-left">
                            <a class="float-left" href="'.$forgotpasswordurl.'">'.get_string('forgotpassword', 'theme_emphasize').'</a>
                        </div>
                        <div class="pull-right">
                            <input type="submit" class="btn btn-default btn-flat" id="submit" name="submit" value="Login">
                        </div>
                    </li>
                </form>
                    </ul>';
        
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            //$returnstr = get_string('loggedinnot', 'moodle');
            $returnstr = '';
            if (!$loginpage) {
                $returnstr = '<a href="'.$loginurl.'" class="nav-link" '.$loginurl_datatoggle.' data-animation="scale-up">
                <i class="icon wb-user"></i>&nbsp;'.get_string('login').'</a>';

                //if($login_dropdown) {
                    $returnstr  .= $signinformhtml;
                //}
            }
            
            return html_writer::tag('div', $returnstr, $usermenuclasses);
        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            //$returnstr = get_string('loggedinasguest');
            $returnstr = '';
            if (!$loginpage && $withlinks) {
                $returnstr = '<a href="'.$loginurl.'" class="nav-link" '.$loginurl_datatoggle.' data-animation="scale-up">
                <i class="icon wb-user"></i>&nbsp;'.get_string('login').'</a>';

                //if($login_dropdown) {
                    $returnstr  .= $signinformhtml;
                //}
            }

            //return html_writer::tag('li', '<span class="text-white" style="line-height:66px;">'.get_string('loggedinasguest').'</span>', array('class' => 'nav-item'))
            return html_writer::tag('li', $returnstr, $usermenuclasses);
        }

        // Get some navigation opts.
        $opts = $this->theme_emphasize_user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider
       $divider = '<div class="dropdown-divider" role="presentation"></div>';
        //$usetextcontents = $DB->get_field('user','email',array('id'=>$USER->id));
        $usetextcontents = $DB->get_record('user',array('id'=>$USER->id));
        $usermenu = '';
        //$usermenu .='<div class="user_icon">';
        $usermenu .= '<a class="nav-link navbar-avatar" data-toggle="dropdown" href="#" aria-expanded="false" data-animation="scale-up" role="button">
            <span class="username">'.$usertextcontents.'</span>
            <span class="avatar avatar-online current">
            '.$opts->metadata['useravatar'].'
            <i></i>
            </span>
        </a>';
        $usermenu .= '<div class="dropdown-menu" role="menu">';
        $usermenu .= '<span class="pop_show"></span>';
        $usermenu .= '<div class="user_main">';
        $usermenu .= '<div class="user_icon">';
        $usermenu .= ' <span class="user_pop">
            '.$opts->metadata['useravatar'].'
            </span>';
        $usermenu .='</div>';
        $usermenu .='<div class="user_content">';
        $usermenu .='<span class="userid"> '.$usertextcontents.'</span>';
                if(!empty($usetextcontents->idnumber)){
                      $usermenu .='<span class="userid"> '.$usetextcontents->idnumber.'</span>';
                 }
        //$usermenu .='<span class="userid">User ID : '.$usetextcontents->idnumber.'</span>';
        $usermenu .='<span class="userid"> '.$usetextcontents->email.'</span>';
        $usermenu .='</div>';
        $usermenu .='</div>';
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $usermenu .= $divider;
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, $value->title, null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        // $al = new action_menu_link_secondary(
                        //     $value->url,
                        //     $pix,
                        //     $value->title,
                        //     array('class' => 'icon')
                        // );
                        // if (!empty($value->titleidentifier)) {
                        //     $al->attributes['data-title'] = $value->titleidentifier;
                        // }
                        // $am->add($al);
                        $icon = $this->pix_icon($pix->pix, '', 'moodle', $pix->attributes);
                        if ($value->title=='Profile' || $value->title=='Log out') {
                            $usermenu .= '<a class="dropdown-item dropdown_others link_button" href="'.$value->url.'" role="menuitem">'.$icon.$value->title.'</a>';
                        }else{
                            $usermenu .= '<a class="dropdown-item" href="'.$value->url.'" role="menuitem">'.$icon.$value->title.'</a>';
                        }
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $usermenu .= $divider;
                }
            }
        }
        $usermenu .= '</div>';

        return html_writer::tag('li', $usermenu, $usermenuclasses);
    }

    /**
      * written by rizwana for getting course summary files
      * Returns course summary content
      * @param  course object
      * @return course summary stored_file objects
      */
    public function get_course_summary_file($course){  
        global $DB, $CFG, $OUTPUT;
        if ($course instanceof stdClass) {
            require_once($CFG->libdir . '/coursecatlib.php');
            $course = new course_in_list($course);
            
            //code added by raghuvaran $course_summary = new coursecat_helper($course);(only this code had here, I added if and else statement on 1-9-17)
            if (!class_exists('coursecat_helper')) {
                // generate the class here
                 $course_summary = '';
            }else{
                $course_summary = new coursecat_helper($course);
            }
           
            
        }
        
            $summary_text = '';
        // display course summary
         if ($course->has_summary()) {
            $summary_text .= $course_summary->get_course_formatted_summary($course,
                    array('overflowdiv' => true, 'noclean' => true, 'para' => false));
         }
         else{
            $summary_text .= '<div class="alert alert-info text-center">No Description Provided</div>';
         }
         // set default course image
         $url = $this->image_url('courseimg','theme_emphasize');
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if($isimage)
                $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            }

        $content = '';
        $content .= html_writer::start_tag('div', array('class' => 'col-md-12 custom_course_top_section row-fluid'));
            $content .= html_writer::start_tag('div', array('class' => 'custom_course_image col-md-5 pull-left desktop-first-column'));
                $content .= html_writer::empty_tag('img', array('src' => $url, 'alt' => $course->fullname,'width' => '100%'));
            $content .= html_writer::end_tag('div');
        
            $content .= html_writer::start_tag('div', array('class' => 'custom_course_detail col-md-7 pull-left'));
                $content .= html_writer::tag('h3', $course->fullname, array('class'=>'col-md-12 custom_course_name pull-left row-fluid'));
                $content .= html_writer::tag('div', $summary_text, array('class'=>'pull-left row-fluid col-md-12'));
            $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        return $content;
    }
    /**
 * Get a list of essential user navigation items.
 *
 * @param stdclass $user user object.
 * @param moodle_page $page page object.
 * @param array $options associative array.
 *     options are:
 *     - avatarsize=35 (size of avatar image)
 * @return stdClass $returnobj navigation information object, where:
 *
 *      $returnobj->navitems    array    array of links where each link is a
 *                                       stdClass with fields url, title, and
 *                                       pix
 *      $returnobj->metadata    array    array of useful user metadata to be
 *                                       used when constructing navigation;
 *                                       fields include:
 *
 *          ROLE FIELDS
 *          asotherrole    bool    whether viewing as another role
 *          rolename       string  name of the role
 *
 *          USER FIELDS
 *          These fields are for the currently-logged in user, or for
 *          the user that the real user is currently logged in as.
 *
 *          userid         int        the id of the user in question
 *          userfullname   string     the user's full name
 *          userprofileurl moodle_url the url of the user's profile
 *          useravatar     string     a HTML fragment - the rendered
 *                                    user_picture for this user
 *          userloginfail  string     an error string denoting the number
 *                                    of login failures since last login
 *
 *          "REAL USER" FIELDS
 *          These fields are for when asotheruser is true, and
 *          correspond to the underlying "real user".
 *
 *          asotheruser        bool    whether viewing as another user
 *          realuserid         int        the id of the user in question
 *          realuserfullname   string     the user's full name
 *          realuserprofileurl moodle_url the url of the user's profile
 *          realuseravatar     string     a HTML fragment - the rendered
 *                                        user_picture for this user
 *
 *          MNET PROVIDER FIELDS
 *          asmnetuser            bool   whether viewing as a user from an
 *                                       MNet provider
 *          mnetidprovidername    string name of the MNet provider
 *          mnetidproviderwwwroot string URL of the MNet provider
 */
function theme_emphasize_user_get_user_navigation_info($user, $page, $options = array()) {
    global $OUTPUT, $DB, $SESSION, $CFG;

    $returnobject = new stdClass();
    $returnobject->navitems = array();
    $returnobject->metadata = array();

    $course = $page->course;

    // Query the environment.
    $context = context_course::instance($course->id);

    // Get basic user metadata.
    $returnobject->metadata['userid'] = $user->id;
    $returnobject->metadata['userfullname'] = fullname($user, true);
    $returnobject->metadata['userprofileurl'] = new moodle_url('/user/profile.php', array(
        'id' => $user->id
    ));

    $avataroptions = array('link' => false, 'visibletoscreenreaders' => false);
    if (!empty($options['avatarsize'])) {
        $avataroptions['size'] = $options['avatarsize'];
    }
    $returnobject->metadata['useravatar'] = $OUTPUT->user_picture (
        $user, $avataroptions
    );
    // Build a list of items for a regular user.

    // Query MNet status.
    if ($returnobject->metadata['asmnetuser'] = is_mnet_remote_user($user)) {
        $mnetidprovider = $DB->get_record('mnet_host', array('id' => $user->mnethostid));
        $returnobject->metadata['mnetidprovidername'] = $mnetidprovider->name;
        $returnobject->metadata['mnetidproviderwwwroot'] = $mnetidprovider->wwwroot;
    }

    // Did the user just log in?
    if (isset($SESSION->justloggedin)) {
        // Don't unset this flag as login_info still needs it.
        if (!empty($CFG->displayloginfailures)) {
            // Don't reset the count either, as login_info() still needs it too.
            if ($count = user_count_login_failures($user, false)) {

                // Get login failures string.
                $a = new stdClass();
                $a->attempts = html_writer::tag('span', $count, array('class' => 'value'));
                $returnobject->metadata['userloginfail'] =
                    get_string('failedloginattempts', '', $a);

            }
        }
    }

    // Links: Dashboard.
    $myhome = new stdClass();
    $myhome->itemtype = 'link';
    $myhome->url = new moodle_url('/my/');
    $myhome->title = get_string('mymoodle', 'admin');
    $myhome->titleidentifier = 'mymoodle,admin';
    $myhome->pix = "i/dashboard";
    $returnobject->navitems[] = $myhome;


    if (is_role_switched($course->id)) {
        if ($role = $DB->get_record('role', array('id' => $user->access['rsw'][$context->path]))) {
            // Build role-return link instead of logout link.
            $rolereturn = new stdClass();
            $rolereturn->itemtype = 'link';
            $rolereturn->url = new moodle_url('/course/switchrole.php', array(
                'id' => $course->id,
                'sesskey' => sesskey(),
                'switchrole' => 0,
                'returnurl' => $page->url->out_as_local_url(false)
            ));
            $rolereturn->pix = "a/logout";
            $rolereturn->title = get_string('switchrolereturn');
            $rolereturn->titleidentifier = 'switchrolereturn,moodle';
            $returnobject->navitems[] = $rolereturn;

            $returnobject->metadata['asotherrole'] = true;
            $returnobject->metadata['rolename'] = role_get_name($role, $context);

        }
    } else {
        // Build switch role link.
        $roles = get_switchable_roles($context);
        if (is_array($roles) && (count($roles) > 0)) {
            $switchrole = new stdClass();
            $switchrole->itemtype = 'link';
            $switchrole->url = new moodle_url('/course/switchrole.php', array(
                'id' => $course->id,
                'switchrole' => -1,
                'returnurl' => $page->url->out_as_local_url(false)
            ));
            $switchrole->pix = "i/switchrole";
            $switchrole->title = get_string('switchroleto');
            $switchrole->titleidentifier = 'switchroleto,moodle';
            $returnobject->navitems[] = $switchrole;
        }
    }

    // Before we add the last items (usually a logout + switch role link), add any
    // custom-defined items.
    $customitems = user_convert_text_to_menu_items($CFG->customusermenuitems, $page);
    foreach ($customitems as $item) {
        $returnobject->navitems[] = $item;
    }

    // Links: My Profile.
    $myprofile = new stdClass();
    $myprofile->itemtype = 'link';
    $myprofile->url = new moodle_url('/user/profile.php', array('id' => $user->id));
    $myprofile->title = get_string('profile');
    $myprofile->titleidentifier = 'profile,moodle';
    $myprofile->pix = "i/user";
    $returnobject->navitems[] = $myprofile;

    $returnobject->metadata['asotherrole'] = false;
    
    if ($returnobject->metadata['asotheruser'] = \core\session\manager::is_loggedinas()) {
        $realuser = \core\session\manager::get_realuser();

        // Save values for the real user, as $user will be full of data for the
        // user the user is disguised as.
        $returnobject->metadata['realuserid'] = $realuser->id;
        $returnobject->metadata['realuserfullname'] = fullname($realuser, true);
        $returnobject->metadata['realuserprofileurl'] = new moodle_url('/user/profile.php', array(
            'id' => $realuser->id
        ));
        $returnobject->metadata['realuseravatar'] = $OUTPUT->user_picture($realuser, $avataroptions);

        // Build a user-revert link.
        $userrevert = new stdClass();
        $userrevert->itemtype = 'link';
        $userrevert->url = new moodle_url('/course/loginas.php', array(
            'id' => $course->id,
            'sesskey' => sesskey()
        ));
        $userrevert->pix = "a/logout";
        $userrevert->title = get_string('logout');
        $userrevert->titleidentifier = 'logout,moodle';
        $returnobject->navitems[] = $userrevert;

    } else {

        // Build a logout link.
        $logout = new stdClass();
        $logout->itemtype = 'link';
        $logout->url = new moodle_url('/login/logout.php', array('sesskey' => sesskey()));
        $logout->pix = "a/logout";
        $logout->title = get_string('logout');
        $logout->titleidentifier = 'logout,moodle';
        $returnobject->navitems[] = $logout;
    }

    return $returnobject;
}
    
    //function added for Marketing Spots by Raghuvaram
    public function marketing_spots() {
        $spots = '';
        if (!empty($this->page->theme->settings->marketingspots)) {
        
        $spots .= ' <div style ="background-color: rgb(241, 241, 241);" class="marketing_spots_container text-center row">
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="div-section">';
                                $url1 = $this->page->theme->setting_file_url('firstmarketingspot_icon', 'firstmarketingspot_icon');
                                $first_header = $this->page->theme->settings->first_ms_header;
                                $first_content = $this->page->theme->settings->first_ms_Content;
                                $spots .= html_writer::empty_tag('img',array('class'=>'marketing_icon', 'src'=>$url1, 'alt'=>$first_header, 'title'=>$first_header));
                                $spots .= '<div class="div-quote">';
                                            $spots .= $first_header;
                                            $spots .='<div class="ms_content_container">';
                                                $spots .= $first_content;
                                            $spots .='</div>
                                            </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12 sec_spot">
                            <div class="div-section">';
                                $url2 = $this->page->theme->setting_file_url('secmarketingspot_icon', 'secmarketingspot_icon');
                                $sec_header = $this->page->theme->settings->sec_ms_header;
                                $sec_ms_Content = $this->page->theme->settings->sec_ms_Content;
                                
                                $spots .= html_writer::empty_tag('img', array('class'=>'marketing_icon','src'=>$url2, 'alt'=>$sec_header ,'title'=>$sec_header ));
                                $spots .='<div class="div-quote">';
                                                $spots .= $sec_header;
                                            $spots .='<div class="ms_content_container">';
                                                $spots .= $sec_ms_Content;
                                            $spots .='</div>
                                        </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12 third_spot">
                            <div class="div-section">';
                                $url3 = $this->page->theme->setting_file_url('third_ms_icon', 'third_ms_icon');
                                $third_header = $this->page->theme->settings->third_ms_header;
                                $third_marketing_content = $this->page->theme->settings->third_ms_Content;
                                
                                $spots .= html_writer::empty_tag('img', array('class'=>'marketing_icon','src' => $url3, 'alt' => $third_header, 'title' => $third_header));
                                $spots .='<div class="div-quote">';
                                          $spots .= $third_header;
                                       $spots .='<div class="ms_content_container">';
                                          $third_ms_Content = $third_marketing_content;
                                          $spots .= $third_ms_Content;
                                       $spots .='</div>
                                    </div>
                            </div>
                        </div>
                    </div>';
        }            
        return $spots;                
    }

    // get user profile pic link
    public static function get_user_picture($userobject , $imgsize = 100) {
        global $USER, $PAGE;
        if (!$userobject) {
            $userobject = $USER;
        }

        $userimg = new user_picture($userobject);
        $userimg->size = $imgsize;
        return  $userimg->get_url($PAGE);
    }
    
    // function added by Raghuvaran on 21_AUG-17
    //for gettings the slider images height dynamic
    function get_slider_hgt() {
        global $CFG;
        $maxheight = '400px';
        $setting = get_config('theme_emphasize', 'sliderhgt');
        //$choices = array(0 => '100px', 1 => '200px', 2 => '300px', 3 => '400px', 4 =>'500px', 5 =>'600px');
        switch($setting){
            case 0:
                $height = 'one';
                break;
            case 1:
                $height = 'two';
                break;
            case 2:
                $height = 'three';
                break;
            case 3:
                $height = 'four';
                break;
            case 4:
                $height = 'five';
                break;
            case 5:
                $height = 'six';
                break;
            default:
                $height = 'four';
                break;
        }
        return $height;
    }
    
    function get_footnote() {
        $footnote='';
        $footnotepath = $this->page->theme->settings->footnote;
        if(!empty($footnotepath)){
            $footnote .= $footnotepath;
        }
        return $footnote;
    }
}
