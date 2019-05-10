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
 * clear theme settings file.
 *
 * @package    theme_clear
 * @copyright  2018 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings = new theme_boost_admin_settingspage_tabs('themesettingclear', get_string('configtitle', 'theme_clear'));
    $page = new admin_settingpage('theme_clear_general', get_string('generalsettings', 'theme_boost'));

    // Background image setting.
    $name = 'theme_clear/settings';
    $title = get_string('settings', 'theme_clear');
    $description = get_string('settings_desc', 'theme_clear');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 'settings');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);
}
