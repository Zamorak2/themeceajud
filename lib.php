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
 * Version details
 *
 * @package    theme
 * @subpackage ceajud
 * @copyright  2015 Léo Sanotos CEAJUD <leo.santos@cnj.jus.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_ceajud_process_css($css, $theme) {

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_ceajud_set_customcss($css, $customcss);

    // Define the default settings for the theme incase they've not been set.
    $defaults = array(
        '[[setting:fsize]]' => '90',
        '[[setting:linkcolor]]' => '#037fbf',
        '[[setting:linkhover]]' => '#037fbf',
        '[[setting:maincolor]]' => '#037fbf',
        '[[setting:backcolor]]' => '#FFFFFF',
        '[[setting:rendereroverlaycolour]]' => '#037fbf',
        '[[setting:rendereroverlayfontcolour]]' => '#FFFFFF',
        '[[setting:buttoncolour]]' => '#158cba',
        '[[setting:buttonhovercolour]]' => '#0084c2',
        '[[setting:dividingline]]' => '#1a557f',
        '[[setting:navbarborder]]' => '#037FBF',
        '[[setting:navbarhover]]' => '#00569D',
        '[[setting:breadcrumb]]' => '#b4bbbf',
        '[[setting:activebreadcrumb]]' => '#e8eaeb',
    );

    // Get all the defined settings for the theme and replace defaults.
    foreach ($theme->settings as $key => $val) {
        if (array_key_exists('[[setting:'.$key.']]', $defaults) && !empty($val)) {
            $defaults['[[setting:'.$key.']]'] = $val;
        }
    }
    // Replace the CSS with values from the $defaults array.
    $css = strtr($css, $defaults);
    if (empty($theme->settings->tilesshowallcontacts) || $theme->settings->tilesshowallcontacts == false) {
        $css = theme_ceajud_set_tilesshowallcontacts($css, false);
    } else {
        $css = theme_ceajud_set_tilesshowallcontacts($css, true);
    }
    return $css;
}


/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_ceajud_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

function theme_ceajud_set_tilesshowallcontacts($css, $display) {
    $tag = '[[setting:tilesshowallcontacts]]';
    if ($display) {
        $replacement = 'block';
    } else {
        $replacement = 'none';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


function theme_ceajud_initialise_zoom(moodle_page $page) {
    user_preference_allow_ajax_update('theme_ceajud_zoom', PARAM_TEXT);
    $page->requires->yui_module('moodle-theme_ceajud-zoom', 'M.theme_ceajud.zoom.init', array());
}

/**
 * Get the user preference for the zoom function.
 */
function theme_ceajud_get_zoom() {
    return get_user_preferences('theme_ceajud_zoom', '');
}

// Full width funcs.

function theme_ceajud_initialise_full(moodle_page $page) {
    user_preference_allow_ajax_update('theme_ceajud_full', PARAM_TEXT);
    $page->requires->yui_module('moodle-theme_ceajud-full', 'M.theme_ceajud.full.init', array());
}

/**
 * Get the user preference for the zoom function.
 */
function theme_ceajud_get_full() {
    return get_user_preferences('theme_ceajud_full', '');
}

function theme_ceajud_get_block_side() {
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('ceajud');
    }
    return get_user_preferences('theme_ceajud_block_side', $theme->settings->blockside);
}

function theme_ceajud_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::link($CFG->wwwroot, '', array('title' => get_string('home'), 'class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote">'.$page->theme->settings->footnote.'</div>';
    }

    return $return;
}

function theme_ceajud_get_setting($setting, $format = false) {
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('ceajud');
    }

    if (empty($theme->settings->$setting)) {
        return false;
    } else if (!$format) {
        return $theme->settings->$setting;
    } else if ($format === 'format_text') {
        return format_text($theme->settings->$setting, FORMAT_PLAIN);
    } else if ($format === 'format_html') {
        return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true));
    } else {
        return format_string($theme->settings->$setting);
    }
}

/**
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
function theme_ceajud_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('ceajud');
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        if ($filearea === 'logo') {
            return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
        } else if ($filearea === 'style') {
            theme_essential_serve_css($args[1]);
        } else if ($filearea === 'pagebackground') {
            return $theme->setting_file_serve('pagebackground', $args, $forcedownload, $options);
        } else if (preg_match("/p[1-9][0-9]/", $filearea) !== false) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if ((substr($filearea, 0, 9) === 'marketing') && (substr($filearea, 10, 5) === 'image')) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if ($filearea === 'iphoneicon') {
            return $theme->setting_file_serve('iphoneicon', $args, $forcedownload, $options);
        } else if ($filearea === 'iphoneretinaicon') {
            return $theme->setting_file_serve('iphoneretinaicon', $args, $forcedownload, $options);
        } else if ($filearea === 'ipadicon') {
            return $theme->setting_file_serve('ipadicon', $args, $forcedownload, $options);
        } else if ($filearea === 'ipadretinaicon') {
            return $theme->setting_file_serve('ipadretinaicon', $args, $forcedownload, $options);
        } else if ($filearea === 'fontfilettfheading') {
            return $theme->setting_file_serve('fontfilettfheading', $args, $forcedownload, $options);
        } else if ($filearea === 'fontfilettfbody') {
            return $theme->setting_file_serve('fontfilettfbody', $args, $forcedownload, $options);
        } else if ($filearea === 'ceajudmarkettingimages') {
            return $theme->setting_file_serve('ceajudmarkettingimages', $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

function theme_ceajud_get_course_activities() {
    GLOBAL $CFG, $PAGE, $OUTPUT;
    // A copy of block_activity_modules.
    $course = $PAGE->course;
    $content = new stdClass();
    $modinfo = get_fast_modinfo($course);
    $modfullnames = array();

    $archetypes = array();

    foreach ($modinfo->cms as $cm) {
        // Exclude activities which are not visible or have no link (=label).
        if (!$cm->uservisible or !$cm->has_view()) {
            continue;
        }
        if (array_key_exists($cm->modname, $modfullnames)) {
            continue;
        }
        if (!array_key_exists($cm->modname, $archetypes)) {
            $archetypes[$cm->modname] = plugin_supports('mod', $cm->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
        }
        if ($archetypes[$cm->modname] == MOD_ARCHETYPE_RESOURCE) {
            if (!array_key_exists('resources', $modfullnames)) {
                $modfullnames['resources'] = get_string('resources');
            }
        } else {
            $modfullnames[$cm->modname] = $cm->modplural;
        }
    }
    core_collator::asort($modfullnames);

    return $modfullnames;
}

function theme_ceajud_performance_output($param) {
    $html = html_writer::tag('span', get_string('loadtime', 'theme_ceajud').' '. round($param['realtime'], 2) . ' ' .
            get_string('seconds'), array('id' => 'load'));
    return $html;
}

function theme_ceajud_page_init(moodle_page $page) {
    global $CFG;
    $page->requires->jquery();
    error_log($CFG->version);
    if($CFG->version < 2015051100) {
      $page->requires->jquery_plugin('bootstrap', 'theme_ceajud');
    }

    $page->requires->jquery_plugin('flexslider', 'theme_ceajud');
    $page->requires->jquery_plugin('easing', 'theme_ceajud');
    $page->requires->jquery_plugin('ceajud', 'theme_ceajud');
}

function theme_ceajud_remove_site_fullname($heading) {
    global $SITE, $PAGE;
    if (strpos($PAGE->pagetype, 'course-view-') === 0) {
        return $heading;
    }

    $header = preg_replace("/^".$SITE->fullname."/", "", $heading);

    return $header;
}
