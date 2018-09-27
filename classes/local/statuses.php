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
 * Defines planned video statuses.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the list of statuses which could be applied to a planned video.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface statuses {

    /**
     * Video migration failed.
     *
     * @var int
     */
    const ERROR = 0;

    /**
     * Video is planned for migration.
     *
     * @var int
     */
    const PLANNED = 1;

    /**
     * Video is being migrated.
     *
     * @var int
     */
    const MIGRATING = 2;

    /**
     * Video has been migrated.
     *
     * @var int
     */
    const MIGRATED = 3;

    /**
     * Video is not registered for migration.
     *
     * @var int
     */
    const UNREGISTERED = 4;

    /**
     * Video is blocked, both migration and rollback failed.
     *
     * @var int
     */
    const BLOCKED = 5;

    /**
     * Video is not supported and can't be migrated.
     *
     * @var int
     */
    const NOT_SUPPORTED = 6;

}
