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

namespace theme_snap;

/**
 * Course settings manager for Snap theme.
 *
 * Manages course-level settings stored in the theme_snap_course_settings table.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_settings {

    /** @var string Option value for enabling multi-language section names. */
    public const MULTILANG_YES = 'yes';

    /** @var string Option value for disabling multi-language section names. */
    public const MULTILANG_NO = 'no';

    /** @var string Option value for automatic detection of multi-language section names. */
    public const MULTILANG_AUTO = 'auto';

    /**
     * Get the course settings for a specific course.
     *
     * @param int $courseid The course ID.
     * @return \stdClass|false The settings record or false if not found.
     */
    public static function get_settings(int $courseid) {
        global $DB;
        return $DB->get_record('theme_snap_course_settings', ['courseid' => $courseid]);
    }

    /**
     * Get the multi-language section names setting for a course.
     *
     * @param int $courseid The course ID.
     * @return string The setting value (yes, no, or auto).
     */
    public static function get_multilang_section_names(int $courseid): string {
        $settings = self::get_settings($courseid);
        if ($settings) {
            return $settings->multilangsectionnames;
        }
        return self::MULTILANG_AUTO;
    }

    /**
     * Set the multi-language section names setting for a course.
     *
     * @param int $courseid The course ID.
     * @param string $value The setting value (yes, no, or auto).
     * @return bool True on success.
     */
    public static function set_multilang_section_names(int $courseid, string $value): bool {
        global $DB;

        // Validate value.
        if (!in_array($value, [self::MULTILANG_YES, self::MULTILANG_NO, self::MULTILANG_AUTO])) {
            $value = self::MULTILANG_AUTO;
        }

        $settings = self::get_settings($courseid);
        $now = time();

        if ($settings) {
            $settings->multilangsectionnames = $value;
            $settings->timemodified = $now;
            return $DB->update_record('theme_snap_course_settings', $settings);
        } else {
            $record = new \stdClass();
            $record->courseid = $courseid;
            $record->multilangsectionnames = $value;
            $record->timemodified = $now;
            return (bool) $DB->insert_record('theme_snap_course_settings', $record);
        }
    }

    /**
     * Delete the course settings for a specific course.
     *
     * @param int $courseid The course ID.
     * @return bool True on success.
     */
    public static function delete_settings(int $courseid): bool {
        global $DB;
        return $DB->delete_records('theme_snap_course_settings', ['courseid' => $courseid]);
    }

    /**
     * Check if a text contains multi-language tags.
     *
     * @param string $text The text to check.
     * @return bool True if multi-language tags are present.
     */
    public static function contains_multilang_tags(string $text): bool {
        // Check for new syntax: <span lang="XX" class="multilang">
        if (preg_match('/<span[^>]+lang="[a-zA-Z0-9_-]+"[^>]+class="multilang"[^>]*>/i', $text)) {
            return true;
        }
        if (preg_match('/<span[^>]+class="multilang"[^>]+lang="[a-zA-Z0-9_-]+"[^>]*>/i', $text)) {
            return true;
        }
        // Check for old syntax: <lang lang="XX">
        if (preg_match('/<lang\s+lang="[a-zA-Z0-9_-]+"[^>]*>/i', $text)) {
            return true;
        }
        return false;
    }

    /**
     * Determine if multi-language filtering should be applied to section names for a course.
     *
     * @param int $courseid The course ID.
     * @param string|null $sectionname Optional section name to check for auto-detection.
     * @return bool True if multi-language filtering should be applied.
     */
    public static function should_apply_multilang(int $courseid, ?string $sectionname = null): bool {
        $setting = self::get_multilang_section_names($courseid);

        switch ($setting) {
            case self::MULTILANG_YES:
                return true;
            case self::MULTILANG_NO:
                return false;
            case self::MULTILANG_AUTO:
            default:
                // For auto, check if the section name contains multi-language tags.
                if ($sectionname !== null) {
                    return self::contains_multilang_tags($sectionname);
                }
                return false;
        }
    }

    /**
     * Get the available options for the multi-language section names setting.
     *
     * @return array Associative array of option value => label.
     */
    public static function get_multilang_options(): array {
        return [
            self::MULTILANG_AUTO => get_string('multilangsectionnames_auto', 'theme_snap'),
            self::MULTILANG_YES => get_string('multilangsectionnames_yes', 'theme_snap'),
            self::MULTILANG_NO => get_string('multilangsectionnames_no', 'theme_snap'),
        ];
    }
}
