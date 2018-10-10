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

use context_system;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\local\machine\transition;
use tool_openveo_migration\event\requesting_openveo_failed;

/**
 * Defines the common part of all video machine transitions.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class video_transition extends transition {

    /**
     * The registered video to migrate.
     *
     * @var registered_video
     */
    protected $originalvideo;

    /**
     * Builds transition.
     *
     * @param registered_video $video The registered video to migrate
     */
    public function __construct(registered_video &$video) {
        $this->originalvideo =& $video;
    }

    /**
     * Sends a "requesting_openveo_failed" event.
     *
     * @param string $message The error message
     */
    protected function send_requesting_openveo_failed_event(string $message) {
        $event = requesting_openveo_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
