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
 * Defines a transition to make sure video still exists.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\transitions;

defined('MOODLE_INTERNAL') || die();

use Exception;
use stored_file;
use context_system;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\event\verifying_video_failed;

/**
 * Defines a transition to verify that the video still exists.
 *
 * Transition succeeds if the video still exists.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class verify_video extends video_transition {

    /**
     * The videos provider.
     *
     * @var tool_openveo_migration\local\videos_provider
     */
    protected $videosprovider;

    /**
     * Builds transition.
     *
     * @param stored_file $video The Moodle video file to migrate
     * @param tool_openveo_migration\local\videos_provider $videosprovider The videos provider
     */
    public function __construct(stored_file &$video, videos_provider $videosprovider) {
        parent::__construct($video);
        $this->videosprovider = $videosprovider;
    }

    /**
     * Executes transition.
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        try {
            $video = $this->videosprovider->get_video_by_id($this->originalvideo->get_id());
        } catch(Exception $e) {
            $this->send_verifying_video_failed_event($this->originalvideo->get_id(), $e->getMessage());
            return false;
        }

        if (empty($video)) {
            $this->send_verifying_video_failed_event($this->originalvideo->get_id(), 'Video not found');
            return false;
        }

        return true;
    }

    /**
     * Gets non-localised transition name.
     *
     * @return string The transition name
     */
    public function get_name() : string {
        return 'Verify video';
    }

    /**
     * Sends a "verifying_video_failed" event.
     *
     * @param int $id The video id
     * @param string $message The error message
     */
    protected function send_verifying_video_failed_event(int $id, string $message) {
        $event = verifying_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
