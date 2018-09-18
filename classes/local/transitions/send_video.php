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
 * Defines a transition to send a video to OpenVeo.
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
use tool_openveo_migration\local\file_system;
use tool_openveo_migration\event\sending_video_failed;
use Openveo\Client\Client;

/**
 * Defines a transition to send the Moodle video file to OpenVeo.
 *
 * Transition succeeds if sending the video to OpenVeo succeeded.
 * Properties of a stored_file instance prefixed by "tom" are properties added by the OpenVeo Migration Tool. "tom" stands for
 * "Tool OpenVeo Migration".
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_video extends video_transition {

    /**
     * OpenVeo web service client.
     *
     * @var Openveo\Client\Client
     */
    protected $client;

    /**
     * The file system.
     *
     * @var tool_openveo_migration\local\file_system
     */
    protected $filesystem;

    /**
     * The destination platform.
     *
     * @var string
     */
    protected $platform;

    /**
     * Builds transition.
     *
     * @param stored_file $video The Moodle video file to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     * @param tool_openveo_migration\local\file_system $filesystem The file system instance
     * @param string $platform The videos platform to upload to (see OpenVeo Publish documentation)
     */
    public function __construct(stored_file &$video, Client $client, file_system $filesystem, string $platform) {
        parent::__construct($video);
        $this->client = $client;
        $this->filesystem = $filesystem;
        $this->platform = $platform;
    }

    /**
     * Executes transition.
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        $openveovideoid = $this->send_video($this->filesystem->get_local_path($this->originalvideo));

        if (!isset($openveovideoid)) {
            return false;
        }

        // Add the id of the video on OpenVeo to the video object. It could be useful to other transitions.
        $this->originalvideo->tomopenveoid = $openveovideoid;
        return true;
    }

    /**
     * Gets non-localised transition name.
     *
     * @return string The transition name
     */
    public function get_name() : string {
        return 'Send video to OpenVeo';
    }

    /**
     * Sends video to OpenVeo platform.
     *
     * @param string $videopath The video absolute path on the file system
     * @return string The id of the video on OpenVeo or null if something went wrong
     */
    protected function send_video(string $videopath) {
        try {
            $response = $this->client->post('/publish/videos', array(
              'file' => curl_file_create($videopath),
              'info' => json_encode(array(
                'title' => $this->originalvideo->get_filename(),
                'date' => $this->originalvideo->get_timecreated() * 1000,
                'platform' => $this->platform
              ))
            ));

            if (isset($response->error)) {
                $this->send_sending_video_failed_event(
                        $this->originalvideo->get_id(),
                        $response->error->code,
                        $response->error->module
                );
            }

            return isset($response->id) ? $response->id : null;
        } catch(Exception $e) {
            $this->send_connection_failed_event($e->getMessage());
            return null;
        }
    }

    /**
     * Sends a "sending_video_failed" event.
     *
     * @param int $id The id of the video on error
     * @param string $code The error code
     * @param string $module The module responsible of the error
     */
    protected function send_sending_video_failed_event(int $id, int $code, string $module) {
        $event = sending_video_failed::create(array(
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
