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
 * Defines the planning form to register Moodle video files for migration.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\output;

defined('MOODLE_INTERNAL') || die();

use moodleform;

/**
 * Defines the Moodle planning actions form to set the list of Moodle video files to migrate.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class planning_actions_form extends moodleform {

    /**
     * Builds the formular.
     *
     * Formular is an HTML_QuickForm instance from Pear library {@link https://pear.php.net} while added elements
     * are instances of HTML_QuickForm_element. You might want to refer to Pear documentation if Moodle
     * documentation on forms is not enough.
     */
    public function definition() {
        $groupelements = array();

        // Videos.
        // The comma separated list of selected Moodle video files (ids).
        $this->_form->addElement('hidden', 'selectedfiles');
        $this->_form->setType('selectedfiles', PARAM_RAW_TRIMMED);

        // Actions.
        // The list of actions to perform on selected videos.
        $groupelements[] =& $this->_form->createElement(
                'select',
                'action',
                null,
                array(
                    0 => get_string('planningactionschooseaction', 'tool_openveo_migration'),
                    1 => get_string('planningactionsregisteraction', 'tool_openveo_migration'),
                    2 => get_string('planningactionsderegisteraction', 'tool_openveo_migration'),
                    3 => get_string('planningactionsremoveaction', 'tool_openveo_migration')
                )
        );

        // Submit button.
        $groupelements[] =& $this->_form->createElement(
                'submit',
                'actionssubmit',
                get_string('planningactionssubmitlabel', 'tool_openveo_migration')
        );

        $this->_form->addGroup(
                $groupelements,
                'actionsgroup',
                get_string('planningactionslabel', 'tool_openveo_migration'),
                ' ',
                false
        );
    }

}
