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
 * Defines a set of static functions to be used by the plugin.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines a set of static functions as helpers for the plugin.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class utils {

    /**
     * Gets Moodle file fields supported by the plugin.
     *
     * @return array An associative array describing the Moodle file fields with component/filearea as key and an associative array
     * as values containing information about the field (component, filearea and supportedmethods)
     */
    public static function get_moodle_file_fields() : array {
        $filefielddescriptions = array();
        $filefields = get_config('tool_openveo_migration', 'filefields');
        $filefields = preg_split('/\r\n|\r|\n/', $filefields);

        foreach ($filefields as $filefield) {
            $filefieldcolumns = explode('|', $filefield);

            if (isset($filefieldcolumns[0]) && isset($filefieldcolumns[1])) {
                $filefielddescriptions[$filefieldcolumns[0] . '/' . $filefieldcolumns[1]] = array(
                    'component' => $filefieldcolumns[0],
                    'filearea' => $filefieldcolumns[1],
                    'supportedmethods' => $filefieldcolumns[2]
                );
            }
        }

        return $filefielddescriptions;
    }

}
