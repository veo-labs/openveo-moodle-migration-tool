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
 * Defines a specific file system to be able to get the file absolute paths.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filestorage/file_system.php');
require_once($CFG->libdir . '/filestorage/file_system_filedir.php');

use stored_file;
use file_system_filedir;

/**
 * Defines a specific file system to be able to use protected file_system functions.
 *
 * Some methods of the core file_system are protected and can't be used from outside. This file_system intends to expose some of
 * this methods.
 *
 * This won't work if Moodle does not use default file system implementation (file_system_filedir). This is the case if configuration
 * variable "alternative_file_system_class" says so.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_system extends file_system_filedir {

    /**
     * Gets the absolute path of a file and recover file from trash if necessary.
     *
     * @param stored_file $file The file to look for
     * @param bool $fetchifnotfound Whether to attempt to fetch from the trash path if not found
     * @return string The absolute path of the file
     */
    public function get_local_path(stored_file $file, $fetchifnotfound = false) {
        return $this->get_local_path_from_storedfile($file, $fetchifnotfound);
    }

}
