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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the planning search form to filter Moodle video files.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\output;

defined('MOODLE_INTERNAL') || die();

use moodleform;
use tool_openveo_migration\local\statuses;

/**
 * Defines the Moodle planning search form to filter Moodle video files.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class planning_search_form extends moodleform {

    /**
     * Builds the formular.
     *
     * Formular is an HTML_QuickForm instance from Pear library {@link https://pear.php.net} while added elements
     * are instances of HTML_QuickForm_element. You might want to refer to Pear documentation if Moodle
     * documentation on forms is not enough.
     */
    public function definition() {

        // Search fieldset.
        $this->_form->addElement('header', 'searchgroup', get_string('planningsearchgroup', 'tool_openveo_migration'));

        // From.
        $this->_form->addElement(
                'date_time_selector',
                'from',
                get_string('planningsearchfrom', 'tool_openveo_migration')
        );
        if (!empty($this->_customdata['from'])) {
            $this->_form->setDefault('from', $this->_customdata['from']);
        }

        // To.
        $this->_form->addElement(
                'date_time_selector',
                'to',
                get_string('planningsearchto', 'tool_openveo_migration')
        );
        if (!empty($this->_customdata['to'])) {
            $this->_form->setDefault('to', $this->_customdata['to']);
        }

        // Video types.
        // The list of possible video types.
        $availabletypesoptions = array('all' => get_string('planningsearchtypesall', 'tool_openveo_migration'));
        foreach ($this->_customdata['availabletypes'] as $availabletype) {
            $availabletypesoptions[$availabletype] = $availabletype;
        }
        $this->_form->addElement(
                'select',
                'type',
                get_string('planningsearchtypeslabel', 'tool_openveo_migration'),
                $availabletypesoptions
        );
        if (!empty($this->_customdata['type'])) {
            $this->_form->setDefault('type', $this->_customdata['type']);
        }

        // Status.
        // The list of possible video statuses.
        $statuses = array();
        $statuses['all'] = get_string('planningsearchstatusall', 'tool_openveo_migration');
        $statuses[statuses::UNREGISTERED] = get_string('planningsearchstatus' . statuses::UNREGISTERED, 'tool_openveo_migration');
        $statuses[statuses::PLANNED] = get_string('planningsearchstatus' . statuses::PLANNED, 'tool_openveo_migration');
        $statuses[statuses::MIGRATING] = get_string('planningsearchstatus' . statuses::MIGRATING, 'tool_openveo_migration');
        $statuses[statuses::MIGRATED] = get_string('planningsearchstatus' . statuses::MIGRATED, 'tool_openveo_migration');
        $statuses[statuses::ERROR] = get_string('planningsearchstatus' . statuses::ERROR, 'tool_openveo_migration');
        $statuses[statuses::BLOCKED] = get_string('planningsearchstatus' . statuses::BLOCKED, 'tool_openveo_migration');
        $this->_form->addElement(
                'select',
                'status',
                get_string('planningsearchstatuslabel', 'tool_openveo_migration'),
                $statuses
        );
        if (!empty($this->_customdata['status'])) {
            $this->_form->setDefault('status', $this->_customdata['status']);
        }

        // Submit button.
        $this->_form->addElement('submit', 'searchsubmit', get_string('planningsearchsubmitlabel', 'tool_openveo_migration'));

    }

}
