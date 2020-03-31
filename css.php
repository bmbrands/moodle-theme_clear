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
 * This file replaces /theme/styles.php for workplace theme
 *
 * The purpose of this override is to server different stylessheets for
 * different organisations (tenants) using one Moodle instance.
 *
 * The tenant id is retreived from the CSS file called by the user.
 * for example the file "theme/1548946762/workplace/css/all-3_1548750271.css"
 * will server the CSS for tenant with id = 3.
 *
 * @package   theme_clear
 * @copyright 2020 Bas Brands <bas@moodle.com>
 * @author    2020 Bas Brands <bas@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
// define('NO_DEBUG_DISPLAY', true);

define('ABORT_AFTER_CONFIG', true);
require('../../config.php');

require_once($CFG->dirroot.'/lib/csslib.php');

if ($slashargument = min_get_slash_argument()) {
    $slashargument = ltrim($slashargument, '/');
    list($type, $sheet) = explode('/', $slashargument, 2);


if ($type == 'fonts') {
    $fonts = "
    @font-face {
        font-family: 'FontAwesome';
        src: url('" . $CFG->wwwroot . "/lib/fonts/fontawesome-webfont.eot');
        src:
            url('" . $CFG->wwwroot . "/lib/fonts/fontawesome-webfont.eot') format('embedded-opentype'),
            url('" . $CFG->wwwroot . "/lib/fonts/fontawesome-webfont.woff2') format('woff2'),
            url('" . $CFG->wwwroot . "/lib/fonts/fontawesome-webfont.woff') format('woff'),
            url('" . $CFG->wwwroot . "/lib/fonts/fontawesome-webfont.ttf') format('truetype'),
            url('" . $CFG->wwwroot . "/lib/fonts/fontawesome-webfont.svg') format('svg');
        font-weight: normal;
        font-style: normal;
    }";
    css_send_uncached_css($fonts);
}}

if ($type == 'theme' && $sheet == 'default.css' && file_exists($CFG->dirroot . '/theme/clear/css/default.css')) {
    $themecss = $CFG->dirroot . '/theme/clear/css/default.css';
    $css = file_get_contents($themecss);
    css_send_uncached_css($css);
}
if ($type == 'theme' && $sheet == 'default.css.map' && file_exists($CFG->dirroot . '/theme/clear/css/default.css.map')) {
    $themecss = $CFG->dirroot . '/theme/clear/css/default.css.map';
    $css = file_get_contents($themecss);
    css_send_uncached_css($css);
}

define('ABORT_AFTER_CONFIG_CANCEL', true);
require("$CFG->dirroot/lib/setup.php");

if ($type == 'core') {
    $theme = theme_config::load('clear');
    $candidatesheet = "$CFG->localcachedir/theme/clear/css/all.css";
    if (file_exists($candidatesheet)) {
        css_send_cached_css($candidatesheet, 'all');
    } else {
        $theme = theme_config::load('clear');
        $csscontent = $theme->get_css_content();
        $theme->set_css_content_cache($csscontent);
        css_store_css($theme, $candidatesheet, $csscontent);
        css_send_cached_css($candidatesheet, 'all');
    }
}
