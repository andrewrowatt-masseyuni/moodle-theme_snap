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
 * Backup plugin for theme_snap.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Backup plugin class for theme_snap course settings.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_theme_snap_plugin extends backup_theme_plugin {

    /**
     * Define the plugin structure for backup.
     *
     * @return backup_plugin_element
     */
    protected function define_course_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_theme_condition(), 'snap');

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Define the snap course settings element.
        $snapcoursesettings = new backup_nested_element('snap_course_settings', ['id'], [
            'multilangsectionnames',
            'timemodified',
        ]);

        // Add the snap course settings to the plugin wrapper.
        $pluginwrapper->add_child($snapcoursesettings);

        // Set source to populate the data.
        $snapcoursesettings->set_source_table('theme_snap_course_settings', [
            'courseid' => backup::VAR_COURSEID,
        ]);

        return $plugin;
    }
}
