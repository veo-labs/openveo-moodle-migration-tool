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
 * Defines the settings form to configure migration.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\output;

defined('MOODLE_INTERNAL') || die();

use moodleform;

/**
 * Defines the Moodle settings form to configure migration.
 *
 * Settings form configures how migration should operate.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_form extends moodleform {

    /**
     * Builds the formular.
     *
     * Formular is an HTML_QuickForm instance from Pear library {@link https://pear.php.net} while added elements
     * are instances of HTML_QuickForm_element. You might want to refer to Pear documentation if Moodle
     * documentation on forms is not enough.
     */
    public function definition() {

        // Settings page description.
        $this->_form->addElement('html', get_string('settingsdescription', 'tool_openveo_migration'));

        // Types of videos to migrate.
        // Client side validation is not working for filetypes fields...
        // Groups "html_video" and "web_video" have to be added as without it Moodle validation does not work...
        $this->_form->addElement(
                'filetypes',
                'videotypestomigrate',
                get_string('settingsvideotypestomigratelabel', 'tool_openveo_migration'),
                array('onlytypes' => ['video', 'html_video', 'web_video'])
        );
        $this->_form->setType('videotypestomigrate', PARAM_RAW_TRIMMED);
        $this->_form->addHelpButton('videotypestomigrate', 'settingsvideotypestomigrate', 'tool_openveo_migration');
        $this->_form->addRule('videotypestomigrate', null, 'required', null, 'server');
        if (!empty($this->_customdata['videotypestomigrate'])) {
            $this->_form->setDefault('videotypestomigrate', $this->_customdata['videotypestomigrate']);
        }

        // Automatic migration.
        $this->_form->addElement(
                'checkbox',
                'automaticmigrationactivated',
                get_string('settingsautomaticmigrationactivatedlabel', 'tool_openveo_migration'),
                get_string('settingsautomaticmigrationactivatedcheckboxlabel', 'tool_openveo_migration')
        );
        $this->_form->addHelpButton(
                'automaticmigrationactivated',
                'settingsautomaticmigrationactivated',
                'tool_openveo_migration'
        );
        if (!empty($this->_customdata['automaticmigrationactivated'])) {
            $this->_form->setDefault('automaticmigrationactivated', $this->_customdata['automaticmigrationactivated']);
        }

        // Destination platform.
        array_unshift($this->_customdata['destinationplatform']['options'], get_string(
                "settingsdestinationplatformchoose",
                'tool_openveo_migration'
        ));
        $this->_form->addElement(
                'select',
                'destinationplatform',
                get_string('settingsdestinationplatformlabel', 'tool_openveo_migration'),
                $this->_customdata['destinationplatform']['options']
        );
        $this->_form->addHelpButton(
                'destinationplatform',
                'settingsdestinationplatform',
                'tool_openveo_migration'
        );
        if (!empty($this->_customdata['destinationplatform']['value'])) {
            $this->_form->setDefault('destinationplatform', $this->_customdata['destinationplatform']['value']);
        }

        // Polling status frequency.
        // PARAM_RAW is used instead of PARAM_INT because PARAM_INT does not allow field with no value, it keeps setting the
        // value to 0. Consequently field validation is made using a regular expression. It validates that the value corresponds
        // to a positive number greater than 0.
        $this->_form->addElement(
                'text',
                'statuspollingfrequency',
                get_string('settingsstatuspollingfrequencylabel', 'tool_openveo_migration'),
                array('size' => 2)
        );
        $this->_form->setType('statuspollingfrequency', PARAM_RAW);
        $this->_form->addHelpButton(
                'statuspollingfrequency',
                'settingsstatuspollingfrequency',
                'tool_openveo_migration'
        );
        $this->_form->addRule(
                'statuspollingfrequency',
                get_string('settingsstatuspollingfrequencyformaterror', 'tool_openveo_migration'),
                'regex',
                '/^[1-9]{1}[0-9]*$/',
                'client'
        );
        if (!empty($this->_customdata['statuspollingfrequency'])) {
            $this->_form->setDefault('statuspollingfrequency', $this->_customdata['statuspollingfrequency']);
        }

        // Number of videos to display in planning page.
        // PARAM_RAW is used instead of PARAM_INT because PARAM_INT does not allow field with no value, it keeps setting the
        // value to 0. Consequently field validation is made using a regular expression. It validates that the value corresponds
        // to a positive number greater than 0.
        $this->_form->addElement(
                'text',
                'planningpagevideosnumber',
                get_string('settingsplanningpagevideosnumberlabel', 'tool_openveo_migration'),
                array('size' => 2)
        );
        $this->_form->setType('planningpagevideosnumber', PARAM_RAW);
        $this->_form->addHelpButton(
                'planningpagevideosnumber',
                'settingsplanningpagevideosnumber',
                'tool_openveo_migration'
        );
        $this->_form->addRule(
                'planningpagevideosnumber',
                get_string('settingsplanningpagevideosnumberformaterror', 'tool_openveo_migration'),
                'regex',
                '/^[1-9]{1}[0-9]*$/',
                'client'
        );
        if (!empty($this->_customdata['planningpagevideosnumber'])) {
            $this->_form->setDefault('planningpagevideosnumber', $this->_customdata['planningpagevideosnumber']);
        }

        // File fields.
        $this->_form->addElement(
                'textarea',
                'filefields',
                get_string('settingsfilefieldslabel', 'tool_openveo_migration'),
                'rows="20" cols="80" style="white-space: nowrap;"'
        );
        $this->_form->setType('filefields', PARAM_RAW_TRIMMED);
        $this->_form->addHelpButton(
                'filefields',
                'settingsfilefields',
                'tool_openveo_migration'
        );
        if (!empty($this->_customdata['filefields'])) {
            $this->_form->setDefault('filefields', $this->_customdata['filefields']);
        }

        $this->add_action_buttons(false, get_string('settingssubmitlabel', 'tool_openveo_migration'));
    }

}
