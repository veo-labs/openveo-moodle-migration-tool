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
 * Defines videos migration states.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the list of states which could be applied to a video during its migration.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface states {

    /**
     * Video has not been treated yet.
     *
     * @var int
     */
    const NOT_INITIALIZED = 1;

    /**
     * Video status has been set to migrating.
     *
     * @var int
     */
    const INITIALIZED = 2;

    /**
     * Video has been sent to OpenVeo.
     *
     * @var int
     */
    const SENT = 3;

    /**
     * Video has been treated by OpenVeo.
     *
     * @var int
     */
    const TREATED = 4;

    /**
     * The sent video on OpenVeo has been published.
     *
     * @var int
     */
    const PUBLISHED = 5;

    /**
     * New Moodle references pointing to the new OpenVeo video have been created.
     *
     * @var int
     */
    const NEW_REFERENCES_CREATED = 6;

    /**
     * The original Moodle video file is still there.
     *
     * @var int
     */
    const VERIFIED = 7;

    /**
     * Original Moodle file has been removed.
     *
     * @var int
     */
    const ORIGINAL_REMOVED = 8;

    /**
     * Original Moodle aliases have been removed.
     *
     * @var int
     */
    const ORIGINAL_ALIASES_REMOVED = 9;

    /**
     * Original Moodle draft files corresponding to original video file have been removed.
     *
     * @var int
     */
    const ORIGINAL_DRAFT_FILES_REMOVED = 10;

    /**
     * Moodle file has been migrated.
     *
     * @var int
     */
    const MIGRATED = 11;

}
