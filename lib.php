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
 * Clear theme callbacks.
 *
 * @package    theme_clear
 * @copyright  2018 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

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
function theme_clear_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && 
            ($filearea === 'loginbackgroundimage' || $filearea === 'themelogo' || $filearea === 'frontpageimage')) {
        $theme = theme_config::load('clear');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

function theme_clear_alter_css_urls(&$urls) {
    $urls = [];
    $theme = theme_config::load('clear');

    $plugincss = new moodle_url('/theme/clear/css.php');
    $plugincss->set_slashargument('/core/all.css');
    $urls[] = $plugincss;

    $themecss = new moodle_url('/theme/clear/css.php');
    $themecss->set_slashargument('/theme/default.css');
    $urls[] = $themecss;

    $fontcss = new moodle_url('/theme/clear/css.php');
    $fontcss->set_slashargument('/fonts/fonts.css');
    $urls[] = $fontcss;


}
