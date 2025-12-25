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
 * Restore plugin for theme_snap.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class for theme_snap course settings.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_theme_snap_plugin extends restore_theme_plugin {

    /**
     * Define the plugin structure for restore.
     *
     * @return restore_path_element[]
     */
    protected function define_course_plugin_structure() {
        $paths = [];

        // Add path for snap course settings.
        $paths[] = new restore_path_element(
            'snap_course_settings',
            $this->get_pathfor('/snap_course_settings')
        );

        return $paths;
    }

    /**
     * Process the snap course settings data during restore.
     *
     * @param array $data The data from the backup file.
     */
    public function process_snap_course_settings($data) {
        global $DB;

        $data = (object) $data;
        $courseid = $this->task->get_courseid();

        // Check if settings already exist for this course.
        $existing = $DB->get_record('theme_snap_course_settings', ['courseid' => $courseid]);

        if ($existing) {
            // Update existing record.
            $existing->multilangsectionnames = $data->multilangsectionnames;
            $existing->timemodified = time();
            $DB->update_record('theme_snap_course_settings', $existing);
        } else {
            // Insert new record.
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->multilangsectionnames = $data->multilangsectionnames;
            $record->timemodified = time();
            $DB->insert_record('theme_snap_course_settings', $record);
        }
    }
}
