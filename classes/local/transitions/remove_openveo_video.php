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
 * Defines transition to remove an OpenVeo video.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\transitions;

defined('MOODLE_INTERNAL') || die();

use Exception;
use context_system;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\event\removing_openveo_video_failed;
use tool_openveo_migration\event\getting_openveo_video_failed;
use Openveo\Client\Client;

/**
 * Defines a transition to remove an OpenVeo video.
 *
 * Transition succeeds if removing OpenVeo video succeeded.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class remove_openveo_video extends video_transition {

    /**
     * OpenVeo web service client.
     *
     * @var Openveo\Client\Client
     */
    protected $client;

    /**
     * Polling request frequency to check if the video is treated by OpenVeo.
     *
     * @var int
     */
    protected $statuspollingfrequency;

    /**
     * Builds transition.
     *
     * @param registered_video $video The registered video to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     * @param int $statuspollingfrequency The frequency of polling requests when waiting for OpenVeo to treat the uploaded video
     * (in seconds)
     */
    public function __construct(registered_video &$video, Client $client, int $statuspollingfrequency = 10) {
        parent::__construct($video);
        $this->client = $client;
        $this->statuspollingfrequency = (!empty($statuspollingfrequency)) ? $statuspollingfrequency : 10;
    }

    /**
     * Executes transition.
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        $openveoid = $this->originalvideo->get_openveo_id();
        if (!isset($openveoid)) {
            return false;
        }

        // Make sure OpenVeo video is in a stable state before trying to remove it.
        if (!$this->wait_for_openveo_video($openveoid)) {
            return false;
        }

        // Remove OpenVeo video.
        return $this->remove_openveo_video($openveoid);
    }

    /**
     * Gets non-localised transition name.
     *
     * @return string The transition name
     */
    public function get_name() : string {
        return 'Remove OpenVeo video';
    }

    /**
     * Waits until OpenVeo video has a stable state (ready, published waiting for upload or error).
     *
     * @param string $id OpenVeo video id
     * @return bool true if video is in a stable state, false if something went wrong
     */
    protected function wait_for_openveo_video($id) {
        try {
            $response = $this->client->get("/publish/videos/$id");

            if (isset($response->error)) {
                $this->send_getting_openveo_video_failed_event($id, $response->error->code, $response->error->module);
                return false;
            } else if (isset($response->entity) &&
                        $response->entity->state !== 0 && // ERROR
                        $response->entity->state !== 6 && // WAITING FOR UPLOAD
                        $response->entity->state !== 11 && // READY
                        $response->entity->state !== 12) { // PUBLISHED

                // OpenVeo video is not on a stable state.
                // Wait a little and launch test again.
                sleep($this->statuspollingfrequency);
                return $this->wait_for_openveo_video($id);

            }

            return isset($response->entity) ? true : false;
        } catch(Exception $e) {
            $this->send_connection_failed_event($e->getMessage());
            return false;
        }
    }

    /**
     * Removes an OpenVeo video.
     *
     * @param string $id OpenVeo video id
     * @return bool true if removing video failed, false otherwise
     */
    protected function remove_openveo_video(string $id) : bool {
        try {
            $response = $this->client->delete("/publish/videos/$id");

            if (isset($response->error)) {
                $this->send_removing_openveo_video_failed_event($id, $response->error->code, $response->error->module);
            }

            return isset($response->total) ? true : false;
        } catch(Exception $e) {
            $this->send_connection_failed_event($e->getMessage());
            return false;
        }
    }

    /**
     * Sends a "removing_openveo_video_failed" event.
     *
     * @param string $id The OpenVeo video id
     * @param int $code The error code
     * @param string $module The module responsible of the error
     */
    protected function send_removing_openveo_video_failed_event(string $id, int $code, string $module) {
        $event = removing_openveo_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'code' => $code,
                'module' => $module
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "getting_openveo_video_failed" event.
     *
     * @param string $id The OpenVeo video id
     * @param int $code The error code
     * @param string $module The module responsible of the error
     */
    protected function send_getting_openveo_video_failed_event(string $id, int $code, string $module) {
        $event = getting_openveo_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'code' => $code,
                'module' => $module
            )
        ));
        $event->trigger();
    }

}
