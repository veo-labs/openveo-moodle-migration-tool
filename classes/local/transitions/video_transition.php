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
 * Defines a base class for all video machine transitions.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\transitions;

defined('MOODLE_INTERNAL') || die();

use stored_file;
use context_system;
use tool_openveo_migration\local\machine\transition;
use tool_openveo_migration\event\connection_failed;

/**
 * Defines the common part of all video machine transitions.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class video_transition extends transition {

    /**
     * The original Moodle file video to migrate.
     *
     * @var stored_file
     */
    protected $originalvideo;

    /**
     * Builds transition.
     *
     * @param stored_file $video The Moodle video file to migrate
     */
    public function __construct(stored_file &$video) {
        $this->originalvideo =& $video;
    }

    /**
     * Sends a "connection_failed" event.
     *
     * @param string $message The error message
     */
    protected function send_connection_failed_event(string $message) {
        $event = connection_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
