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
 * Defines transition to wait for a video to be treated by OpenVeo.
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
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\event\getting_openveo_video_failed;
use tool_openveo_migration\event\waiting_for_openveo_video_failed;
use Openveo\Client\Client;

/**
 * Defines transition to wait for a video until it has been fully treated by OpenVeo.
 *
 * Transition succeeds if the status of the video on OpenVeo, is either "ready" or "published". Any other status will make the
 * transitions fails.
 * Properties of a stored_file instance prefixed by "tom" are properties added by the OpenVeo Migration Tool. "tom" stands for
 * "Tool OpenVeo Migration".
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wait_for_openveo_video extends video_transition {

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
     * @param stored_file $video The Moodle video file to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     * @param int $statuspollingfrequency The frequency of polling requests when waiting for OpenVeo to treat the uploaded video
     * (in seconds)
     */
    public function __construct(stored_file &$video, Client $client, int $statuspollingfrequency = 10) {
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
        if (!isset($this->originalvideo->tomopenveoid)) {
            return false;
        }

        $openveovideostate = $this->wait_for_openveo_video($this->originalvideo->tomopenveoid);

        if (!isset($openveovideostate) || ($openveovideostate !== 11 && $openveovideostate !== 12)) {

            // Video state on OpenVeo couldn't be retrieved or is different from "ready" or "published". Abort.
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
        return 'Wait for OpenVeo video';
    }

    /**
     * Waits until OpenVeo video has a stable state (ready, published or error).
     *
     * @param string $id OpenVeo video id
     * @return int The OpenVeo video state or null if something went wrong
     */
    protected function wait_for_openveo_video($id) {
        try {
            $response = $this->client->get("/publish/videos/$id");

            if (isset($response->error)) {
                $this->send_getting_openveo_video_failed_event($id, $response->error->code, $response->error->module);
                return false;
            } else if (isset($response->entity) &&
                        ($response->entity->state === 0 || // ERROR
                         $response->entity->state === 6    // WAITING FOR UPLOAD
                      )) {
                $this->send_waiting_for_openveo_video_failed_event($id, $response->entity->state);
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

            return isset($response->entity) ? $response->entity->state : null;
        } catch(Exception $e) {
            $this->send_connection_failed_event($e->getMessage());
            return null;
        }
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

    /**
     * Sends a "waiting_for_openveo_video_failed" event.
     *
     * @param string $id The OpenVeo video id
     * @param int $state The OpenVeo video state
     */
    protected function send_waiting_for_openveo_video_failed_event(string $id, int $state) {
        $event = waiting_for_openveo_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'state' => $state
            )
        ));
        $event->trigger();
    }

}
