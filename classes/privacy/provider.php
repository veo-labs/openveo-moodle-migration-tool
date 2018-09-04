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
 * Defines a privacy provider to inform Moodle about the user data stored by this plugin (GDPR).
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\null_provider;

/**
 * Defines privacy subsystem for this plugin.
 *
 * This plugin does not store any information about users as specified by the General Data Protection Regulation
 * (GDPR).
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements null_provider {

    /**
     * Gets the language string identifier with the component's language file to explain why this plugin doesn't
     * store data.
     *
     * @return string The language string identifier
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }

}
