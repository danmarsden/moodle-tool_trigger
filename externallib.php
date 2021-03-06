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
 * Admin tool trigger Web Service
 *
 * @package    tool_trigger
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

/**
 * Admin tool trigger Web Service
 *
 * @package    tool_trigger
 * @copyright  Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_trigger_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_all_eventlist_parameters() {
        return new external_function_parameters(
                array(
                    // If I had any parameters, they would be described here. But I don't have any, so this array is empty.
                )
            );
    }

    /**
     * Returns available events
     *
     */
    public static function get_all_eventlist() {
        global $USER;

        // Context validation.
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Capability checking.
        if (!has_capability('tool/trigger:manageworkflows', $context)) {
            throw new moodle_exception('cannot_access_api');
        }

        // Execute API call.
        $events = \tool_monitor\eventlist::get_all_eventlist(true);

        // Filter out events which cannot be triggered for some reason.
        $events = array_filter($events, function($classname) {
            return !$classname::is_deprecated();
        }, ARRAY_FILTER_USE_KEY);

        $eventlist = array();

        // Format response
        foreach ($events as $key => $event){
            $record = new \stdClass();
            $record->id = $key;
            $record->name = $event;

            $eventlist[] = $record;
        }

        return $eventlist;

    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_all_eventlist_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_TEXT, 'Event identifier'),
                        'name' => new external_value(PARAM_TEXT, 'Event Name'),
                        )
                    )
                );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function step_by_type_parameters() {
        return new external_function_parameters(
            array(
                'steptype' => new external_value(PARAM_ALPHA, 'The type of step to get.'),
            )
            );
    }

    /**
     * Returns all steps matching provided type.
     *
     */
    public static function step_by_type($steptype) {
        global $USER;

        // Context validation.
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // Capability checking.
        if (!has_capability('tool/trigger:manageworkflows', $context)) {
            throw new moodle_exception('cannot_access_api');
        }

        // Validate_parameters.
        $params = self::validate_parameters(self::step_by_type_parameters(),
                ['steptype' => $steptype]);

        // Execute API call.
        $wfmanager = new \tool_trigger\workflow_manager();
        $steps = $wfmanager->get_steps_by_type($params['steptype']);

        return $steps;

    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function step_by_type_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'class' => new external_value(PARAM_TEXT, 'Event identifier'),
                    'name' => new external_value(PARAM_TEXT, 'Event Name'),
                )
                )
            );
    }

    /**
     * Describes the parameters for validate_form webservice.
     * @return external_function_parameters
     */
    public static function validate_form_parameters() {
        return new external_function_parameters(
            array(
                'stepclass' => new external_value(PARAM_RAW, 'The step class being validated'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array')
            )
        );
    }

    /**
     * Validate the form.
     *
     * @param string stepclass The step class being validated
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @return int new group id.
     */
    public static function validate_form($stepclass, $jsonformdata) {
        global $USER;

        // Step class is passed - convert it to the form class for this step.
        $formclass = substr($stepclass, 0, (strlen($stepclass)-4)) . 'form';
        // Context validation.
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::validate_form_parameters(),
            ['stepclass' => $stepclass, 'jsonformdata' => $jsonformdata]);

        $data = array();
        if (!empty($params['jsonformdata'])) {
            $serialiseddata = json_decode($params['jsonformdata']);
            parse_str($serialiseddata, $data);
        }

        // The last param is the ajax submitted data.
        $mform = new $formclass(null, array(), 'post', '', null, true, $data);

        if (!$mform->is_validated()) {
            // Generate a warning.
            throw new moodle_exception('erroreditgroup', 'group');
        }
        return true;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function validate_form_returns() {
        return new external_value(PARAM_RAW, 'form errors');
    }

}