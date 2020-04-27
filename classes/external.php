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
 * Theme clear external API
 *
 * @package    theme_clear
 * @category   external
 * @copyright  2020 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_clear;

defined('MOODLE_INTERNAL') || die;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use coding_exception;

require_once($CFG->libdir . '/externallib.php');

/**
 * The arup boost external services.
 *
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * @return \external_function_parameters
     */
    public static function saveimage_parameters() {
        $parameters = [
            'params' => new \external_single_structure([
                'imagedata' => new \external_value(PARAM_TEXT, 'Image data', VALUE_REQUIRED),
                'imagefilename' => new \external_value(PARAM_TEXT, 'Image filename', VALUE_REQUIRED),
                'imageid' => new \external_value(PARAM_INT, 'Image Id', VALUE_OPTIONAL),
                'type' => new \external_value(PARAM_TEXT, 'Image type', VALUE_OPTIONAL),
                'cropped' => new \external_value(PARAM_INT, 'Cropped version', VALUE_OPTIONAL),
            ], 'Params wrapper - just here to accommodate optional values', VALUE_REQUIRED)
        ];
        return new \external_function_parameters($parameters);
    }

    /**
     * @param string $imagedata
     * @param string $imagefilename
     * @param int $imageid Related to the type contextid
     * @param string $type image type
     * @param int $cropped 1 if cropped version
     * @return array
     */
    public static function saveimage($params) {
        $params = self::validate_parameters(self::saveimage_parameters(), ['params' => $params])['params'];

        if (empty($params['imageid'])) {
            throw new coding_exception('Error - imageid must be provided');
        }

        if ($params['type'] === 'course') {
            $context = \context_course::instance($params['imageid']);
        } else {
            throw new coding_exception('Error - type must be course');
        }

        self::validate_context($context);

        $coverimage = self::processimage($context, $params['type'], $params['imagedata'],
            $params['imagefilename'], $params['cropped']);
        return $coverimage;
    }

    /**
     * @return \external_single_structure
     */
    public static function saveimage_returns() {
        $keys = [
            'success' => new \external_value(PARAM_BOOL, 'Was the cover image successfully changed', VALUE_REQUIRED),
            'fileurl' => new \external_value(PARAM_TEXT, 'New file', VALUE_REQUIRED)
        ];

        return new \external_single_structure($keys, 'coverimage');
    }

    /**
     * @param \context $context
     * @param string $type
     * @param string $data
     * @param string $filename
     * @return array
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public static function processimage(\context $context, $type, $data, $filename, $cropped) {

        global $CFG;

        $fs = get_file_storage();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;

        if (!in_array($ext, ['jpg', 'png', 'gif', 'svg', 'webp'])) {
            return ['success' => false, 'warning' => get_string('unsupportedcoverimagetype', 'theme_clear', $ext)];
        }

        $newfilename = $type . 'image.' . $ext;

        $binary = base64_decode($data);
        if (strlen($binary) > get_max_upload_file_size($CFG->maxbytes)) {
            throw new \moodle_exception('error:coverimageexceedsmaxbytes', 'theme_clear');
        }

        $filearea = $type;
        if ($cropped) {
            $filearea = $type . '_cropped';
        }

        if ($context->contextlevel === CONTEXT_COURSECAT && $type === 'catalogue') {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'theme_clear',
                'filearea' => $filearea,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $newfilename);

            // Remove everything from poster area for this context.
            if ($cropped) {
                $fs->delete_area_files($context->id, 'theme_clear', $filearea);
            } else {
                $fs->delete_area_files($context->id, 'theme_clear', $type);
                $fs->delete_area_files($context->id, 'theme_clear', $type. '_cropped');
            }
        } else if ($context->contextlevel === CONTEXT_COURSE && $type === 'course') {
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'theme_clear',
                'filearea' => $filearea,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $newfilename);

            // Remove everything from poster area for this context.
            if ($cropped) {
                $fs->delete_area_files($context->id, 'theme_clear', $filearea);
            } else {
                $fs->delete_area_files($context->id, 'theme_clear', $type);
                $fs->delete_area_files($context->id, 'theme_clear', $type. '_cropped');
            }
        } else {
            throw new coding_exception('Unsupported context level '.$context->contextlevel);
        }

        // Create new cover image file and process it.
        $storedfile = $fs->create_file_from_string($fileinfo, $binary);
        $success = $storedfile instanceof \stored_file;
        $fileurl = \moodle_url::make_pluginfile_url(
            $storedfile->get_contextid(),
            $storedfile->get_component(),
            $storedfile->get_filearea(),
            $storedfile->get_timemodified(), // Used as a cache buster.
            $storedfile->get_filepath(),
            $storedfile->get_filename()
        );
        return ['success' => $success, 'fileurl' => $fileurl->out()];
    }
}
