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
use stored_file;
use context_system;
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\event\publishing_openveo_video_failed;
use Openveo\Client\Client;

/**
 * Defines transition to publish an OpenVeo video.
 *
 * Transition succeeds if publishing the OpenVeo video succeeded.
 * Properties of a stored_file instance prefixed by "tom" are properties added by the OpenVeo Migration Tool. "tom" stands for
 * "Tool OpenVeo Migration".
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
     * @param stored_file $video The Moodle video file to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     */
    public function __construct(stored_file &$video, Client $client) {
        parent::__construct($video);
        $this->client = $client;
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

        if (!$this->publish_openveo_video($this->originalvideo->tomopenveoid)) {
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

            if (isset($response->error)) {
                $this->send_publishing_openveo_video_failed_event($id, $response->error->code, $response->error->module);
            }

            return (isset($response->total)) ? true : false;
        } catch(Exception $e) {
            $this->send_connection_failed_event($e->getMessage());
            return false;
        }
    }

    /**
     * Sends a "publishing_openveo_video_failed" event.
     *
     * @param string $id The OpenVeo video id
     * @param int $code The error code
     * @param string $module The module responsible of the error
     */
    protected function send_publishing_openveo_video_failed_event(string $id, int $code, string $module) {
        $event = publishing_openveo_video_failed::create(array(
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
