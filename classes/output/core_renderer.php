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
 * Overridden and custom renderers for this theme.
 *
 * @package    theme_clear
 * @copyright  2020 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_clear\output;

use \theme_boost\output\core_renderer as boost_core_renderer;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * Note: This class is required to avoid inheriting Boost's core_renderer,
 *       which removes the edit button required by Clear.
 *
 * @package    theme_clear
 * @copyright  2020 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends boost_core_renderer {

    /**
     * I have hijacked this function to inject the LiveReload script into the page footer.
     */
    public function home_link() {
        global $CFG;
        $homelink = parent::home_link();
        if (isset($CFG->cachejs)) {
        $homelink .= '<script>
          document.write(\'<script src="http://\' + (location.host || \'localhost\').split(\':\')[0] +
          \':35729/livereload.js?snipver=1"></\' + \'script>\')
          </script>';
        }
        return $homelink;
    }

    /**
     * Theme clear custom images
     *
     * @param string $type the image type
     * @param int $imageid the image imageid
     * @param string $originalimage the original image url
     * @param string $fallbackimage the default image url
     */
    public function imagehandler($type, $imageid, $originalimage, $fallbackimage = null) {
        global $PAGE, $OUTPUT, $USER;

        if ($type !== 'catalogue' && $type !== 'course') {
            return false;
        }

        $image = new stdClass();
        $image->imageid = $imageid;

        if ($type == 'course') {
            $image->contextid = \context_course::instance($imageid)->id;
        }
        if ($type == 'catalogue') {
            $image->contextid = \context_coursecat::instance($imageid)->id;
        }

        $image->type = $type;

        $croppedimage = $this->imagehandler_url($image->contextid, 'theme_clear', $type . '_cropped');
        $fullimage = $this->imagehandler_url($image->contextid, 'theme_clear', $type);

        $image->defaultimage = $fallbackimage;
        $image->originalimage = $originalimage;

        if ($croppedimage) {
            $image->image = $croppedimage;
            $image->originalimage = $fullimage;
        } else if ($fullimage) {
            $image->image = $fullimage;
            $image->originalimage = $fullimage;
        } else {
            $image->image = $originalimage;
        }

        if ($PAGE->user_allowed_editing() && isset($USER->editing) && $USER->editing == 1) {
            $image->allowcrop = true;
            $image->allowupload = true;
        }

        return $OUTPUT->render_from_template('theme_clear/imagehandler', $image);
    }

    /**
     * Get the image url for the imagehandler
     *
     * @param int $contextid The image contextid
     * @param string $component The image component
     * @param string $filearea The image filearea
     */
    public function imagehandler_url($contextid, $component, $filearea) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, $component, $filearea, 0, "itemid, filepath, filename", false);
        if (!$files) {
            return false;
        }
        if (count($files) > 1) {
            // Note this is a coding exception and not a moodle exception because there should never be more than one
            // file in this area, where as the course summary files area can in some circumstances have more than on file.
            throw new \coding_exception('Multiple files found in filearea (context '.$contextid.')');
        }
        $file = (end($files));

        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_timemodified(), // Used as a cache buster.
            $file->get_filepath(),
            $file->get_filename()
        );
    }
}
