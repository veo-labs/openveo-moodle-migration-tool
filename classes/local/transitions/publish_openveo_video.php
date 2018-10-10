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
 * Defines transition to publish an OpenVeo video.
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
use tool_openveo_migration\event\publishing_openveo_video_failed;
use Openveo\Client\Client;
use Openveo\Exception\ClientException;

/**
 * Defines transition to publish an OpenVeo video.
 *
 * Transition succeeds if publishing the OpenVeo video succeeded.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class publish_openveo_video extends video_transition {

    /**
     * OpenVeo web service client.
     *
     * @var Openveo\Client\Client
     */
    protected $client;

    /**
     * Builds transition.
     *
     * @param registered_video $video The registered video to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     */
    public function __construct(registered_video &$video, Client $client) {
        parent::__construct($video);
        $this->client = $client;
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

        if (!$this->publish_openveo_video($openveoid)) {
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
        return 'Publish OpenVeo video';
    }

    /**
     * Publishes an OpenVeo video.
     *
     * @param string $id OpenVeo video id
     * @return bool true if publishing video succeeded, false otherwise
     */
    protected function publish_openveo_video(string $id) : bool {
        try {
            $response = $this->client->post("/publish/videos/$id/publish");
            return (isset($response->total)) ? true : false;
        } catch(ClientException $e) {
            $this->send_publishing_openveo_video_failed_event($id, $e->getMessage());
        } catch(Exception $e) {
            $this->send_requesting_openveo_failed_event($e->getMessage());
        }
        return false;
    }

    /**
     * Sends a "publishing_openveo_video_failed" event.
     *
     * @param string $id The OpenVeo video id
     * @param string $message The error message
     */
    protected function send_publishing_openveo_video_failed_event(string $id, string $message) {
        $event = publishing_openveo_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
