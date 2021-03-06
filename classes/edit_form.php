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
 * Add / edit workflow form class.
 *
 * @package     tool_trigger
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_trigger;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Add / edit workflow form class.
 *
 * @package     tool_trigger
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_form extends \moodleform {

    /**
     * Build form for the general setting admin page for plugin.
     */
    public function definition() {

        $mform = $this->_form;
        $eventlist = $this->_customdata['eventlist'];
        $pluginlist = $this->_customdata['pluginlist'];

        // Workflow name.
        $mform->addElement('text', 'workflowname', get_string ('workflowname', 'tool_trigger'), 'size="50"');
        $mform->setType('workflowname', PARAM_ALPHAEXT);
        $mform->addRule('workflowname', get_string('required'), 'required');
        $mform->addHelpButton('workflowname', 'workflowname', 'tool_trigger');
        if (isset($this->_customdata['workflowname'])) {
            $mform->setDefault('workflowname', $this->_customdata['workflowname']);
        }

        // Workflow description.
        $editoroptions = array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => 0,
            'changeformat' => 0,
            'context' => \context_system::instance(),
            'noclean' => 0,
            'trusttext' => 0
        );
        $mform->addElement('editor', 'workflowdescription', get_string ('workflowdescription', 'tool_trigger'), $editoroptions);
        $mform->addHelpButton('workflowdescription', 'workflowdescription', 'tool_trigger');
        if (isset($this->_customdata['workflowdescription'])) {
            $mform->setDefault('workflowdescription', $this->_customdata['workflowdescription']);
        }

        $mform->addElement('select', 'areatomonitor', get_string('areatomonitor', 'tool_trigger'), $pluginlist);
        $mform->addHelpButton('areatomonitor', 'areatomonitor', 'tool_trigger');
        $mform->addRule('areatomonitor', get_string('required'), 'required');
        if (isset($this->_customdata['areatomonitor'])) {
            $mform->setDefault('areatomonitor', $this->_customdata['areatomonitor']);
        }

        $mform->addElement('select', 'eventtomonitor', get_string('eventtomonitor', 'tool_trigger'), $eventlist);
        $mform->addHelpButton('eventtomonitor', 'eventtomonitor', 'tool_trigger');
        $mform->addRule('eventtomonitor', get_string('required'), 'required');
        if (isset($this->_customdata['eventtomonitor'])) {
            $mform->setDefault('eventtomonitor', $this->_customdata['eventtomonitor']);
        }

        // Draft mode.
        $mform->addElement('advcheckbox',
            'draftmode',
            get_string ('draftmode', 'tool_trigger'),
            'Enable', array(), array(0, 1));
        $mform->setType('draftmode', PARAM_INT);
        $mform->addHelpButton('draftmode', 'draftmode', 'tool_trigger');
        if (isset($this->_customdata['draftmode'])) {
            $mform->setDefault('draftmode', $this->_customdata['draftmode']);
        } else {
            $mform->setDefault('draftmode', 0);
        }

        // Async mode.
        $mform->addElement('advcheckbox',
            'asyncmode',
            get_string ('asyncmode', 'tool_trigger'),
            'Enable', array(), array(0, 1));
        $mform->setType('asyncmode', PARAM_INT);
        $mform->addHelpButton('asyncmode', 'asyncmode', 'tool_trigger');
        if (isset($this->_customdata['asyncmode'])) {
            $mform->setDefault('asyncmode', $this->_customdata['asyncmode']);
        } else {
            $mform->setDefault('asyncmode', 1);
        }

        // Workflow active.
        $mform->addElement('advcheckbox',
            'workflowactive',
            get_string ('workflowactive', 'tool_trigger'),
            'Enable', array(), array(0, 1));
        $mform->setType('workflowactive', PARAM_INT);
        $mform->addHelpButton('workflowactive', 'workflowactive', 'tool_trigger');
        if (isset($this->_customdata['workflowactive'])) {
            $mform->setDefault('workflowactive', $this->_customdata['workflowactive']);
        } else {
            $mform->setDefault('workflowactive', 1);
        }

        //  Hidden text field for step JSON.
        $mform->addElement('hidden', 'stepjson');
        $mform->setType('stepjson', PARAM_RAW_TRIMMED);
        if (isset($this->_customdata['stepjson'])) {
            $mform->setDefault('stepjson', $this->_customdata['stepjson']);
        }

        // Workflow steps mini table.
        $mform->addElement('html', '<div id="steps-table"></div>');

        // Add processing step button.
        $mform->addElement('button', 'step_modal_button', get_string('step_modal_button', 'tool_trigger'));

        $this->add_action_buttons();
    }

}
