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
 * Hook callbacks for the Snap theme.
 *
 * @package   theme_snap
 * @author    Jonathan Garcia Gomez <jonathan.garcia@openlms.net>
 * @copyright Copyright (c) 2024 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace theme_snap;

class hook_callbacks {

    /**
     * Add alerts and redirects to the footer from event actions.
     *
     * @param \core\hook\output\before_footer_html_generation $hook
     * @return void
     * @throws \coding_exception
     */
    public static function before_footer_html_generation(\core\hook\output\before_footer_html_generation $hook): void {
        global $CFG, $PAGE;

        if ($PAGE->theme->name !== 'snap' || empty(get_config('theme_snap', 'advancedfeedsenable'))) {
            return;
        }

        $paths = [];
        $paths['theme_snap/snapce'] = [
            $CFG->wwwroot . '/pluginfile.php/' . $PAGE->context->id . '/theme_snap/vendorjs/snap-custom-elements/snap-ce'
        ];

        $PAGE->requires->js_call_amd('theme_snap/wcloader', 'init', [
            'componentPaths' => json_encode($paths)
        ]);
    }

    /**
     * Add custom fields to course edit form.
     *
     * @param \core_course\hook\after_form_definition $hook
     * @return void
     */
    public static function after_course_form_definition(\core_course\hook\after_form_definition $hook): void {
        $mform = $hook->mform;

        // Add a header for Snap theme settings.
        $mform->addElement('header', 'snap_course_settings', get_string('pluginname', 'theme_snap'));

        // Add the multi-language section names setting.
        $options = course_settings::get_multilang_options();
        $mform->addElement(
            'select',
            'snap_multilangsectionnames',
            get_string('multilangsectionnames', 'theme_snap'),
            $options
        );
        $mform->addHelpButton('snap_multilangsectionnames', 'multilangsectionnames', 'theme_snap');
        $mform->setDefault('snap_multilangsectionnames', course_settings::MULTILANG_AUTO);
    }

    /**
     * Set default values for custom course fields after data is loaded.
     *
     * @param \core_course\hook\after_form_definition_after_data $hook
     * @return void
     */
    public static function after_course_form_definition_after_data(
        \core_course\hook\after_form_definition_after_data $hook
    ): void {
        $mform = $hook->mform;
        $formwrapper = $hook->formwrapper;

        // Get the course data from the form.
        $course = $formwrapper->get_course();

        if (!empty($course->id)) {
            // Load existing setting value.
            $value = course_settings::get_multilang_section_names($course->id);
            $mform->setDefault('snap_multilangsectionnames', $value);
        }
    }

    /**
     * Save custom course settings when the form is submitted.
     *
     * @param \core_course\hook\after_form_submission $hook
     * @return void
     */
    public static function after_course_form_submission(\core_course\hook\after_form_submission $hook): void {
        $data = $hook->get_data();

        // Only save if the field was submitted.
        if (isset($data->snap_multilangsectionnames) && isset($data->id)) {
            course_settings::set_multilang_section_names($data->id, $data->snap_multilangsectionnames);
        }
    }
}
