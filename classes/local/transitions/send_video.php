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
use context_system;
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\local\file_system;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\event\sending_video_failed;
use Openveo\Client\Client;
use Openveo\Exception\ClientException;

/**
 * Defines a transition to send the Moodle video file to OpenVeo.
 *
 * Transition succeeds if sending the video to OpenVeo succeeded.
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
     * The list of text templates to use to name OpenVeo videos.
     *
     * @var array
     */
    protected $nameformats;

    /**
     * The cURL upload timeout in seconds.
     *
     * @var string
     */
    protected $uploadcurltimeout;

    /**
     * The provider of videos.
     *
     * @var tool_openveo_migration\local\videos_provider
     */
    protected $videosprovider;

    /**
     * Builds transition.
     *
     * @param registered_video $video The registered video to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     * @param tool_openveo_migration\local\file_system $filesystem The file system instance
     * @param string $platform The videos platform to upload to (see OpenVeo Publish documentation)
     * @param array $nameformats The formats to use to name videos on OpenVeo depending on the videos contexts. It should be an
     * associative array with five keys: 'course', 'module', 'category', 'block' and 'user' with the desired format as values.
     * Formats may contains tokens which are available or not depending on the context:
     * - For course context: %filename%, %courseid%, %courseidnumber%, %coursecategoryid%, %coursefullname%, %courseshortname%
     * - For module context: %filename%, %moduleid%, %modulename%, %courseid%, %courseidnumber%, %coursecategoryid%,
     * %coursefullname%, %courseshortname%
     * - For category context: %filename%, %categoryid%, %categoryidnumber%, %categoryname%
     * - For block context: %filename%, %blockid%, %blockname%, %courseid%, %courseidnumber%, %coursecategoryid%, %coursefullname%,
     * %courseshortname%
     * - For user context: %filename%, %userid%, %username%, %userfirstname%, %userlastname%, %useremail%
     * Default formats are:
     * - For course context: %courseid% - %filename%
     * - For module context: %moduleid% - %filename%
     * - For category context: %categoryid% - %filename%
     * - For block context: %blockid% - %filename%
     * - For user context: %userid% - %filename%
     * @param int $uploadcurltimeout The cURL upload timeout in seconds (default to 3600)
     * @param videos_provider $videosprovider The videos provider
     */
    public function __construct(registered_video &$video, Client $client, file_system $filesystem, string $platform,
            array $nameformats, int $uploadcurltimeout, videos_provider $videosprovider) {
        parent::__construct($video);
        $this->client = $client;
        $this->filesystem = $filesystem;
        $this->platform = $platform;
        $this->nameformats = $nameformats;
        $this->uploadcurltimeout = !empty($uploadcurltimeout) ? $uploadcurltimeout : 3600;
        $this->nameformats['course'] = !empty($this->nameformats['course']) ? $this->nameformats['course'] : '%courseid% - %filename%';
        $this->nameformats['module'] = !empty($this->nameformats['module']) ? $this->nameformats['module'] : '%moduleid% - %filename%';
        $this->nameformats['category'] = !empty($this->nameformats['category']) ? $this->nameformats['category'] : '%categoryid% - %filename%';
        $this->nameformats['block'] = !empty($this->nameformats['block']) ? $this->nameformats['block'] : '%blockid% - %filename%';
        $this->nameformats['user'] = !empty($this->nameformats['user']) ? $this->nameformats['user'] : '%userid% - %filename%';
        $this->videosprovider = $videosprovider;
    }

    /**
     * Executes transition.
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        $openveovideoid = $this->send_video($this->filesystem->get_local_path($this->originalvideo->get_file()));

        if (!isset($openveovideoid)) {
            return false;
        }

        // Add the id of the video on OpenVeo to the video object. It could be useful to other transitions.
        $this->originalvideo->set_openveo_id($openveovideoid);
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
     * There is no bidirectional link between the Moodle reference(s) and the video on OpenVeo. If the Moodle reference
     * is removed or modified, the corresponding OpenVeo video won't be harmed. In the same way, if an OpenVeo video
     * is modified or removed, modifications won't be automatically reported to the Moodle reference.
     *
     * @param string $videopath The video absolute path on the file system
     * @return string The id of the video on OpenVeo or null if something went wrong
     */
    protected function send_video(string $videopath) {
        $videofile = $this->originalvideo->get_file();
        $videoowner = $this->originalvideo->get_owner();

        try {
            $videocontexts = $this->videosprovider->get_video_contexts($this->originalvideo);
            $response = $this->client->get("/users?email={$videoowner->email}");

            // Use first video_context to build the title, this is the original video file context
            $info = array(
                'title' => $videocontexts[0]->resolve_text($this->nameformats[$videocontexts[0]->type]),
                'description' => $this->build_video_description($videocontexts),
                'date' => $videofile->get_timecreated() * 1000,
                'platform' => $this->platform
            );

            if (sizeof($response->entities) > 0) {
                $info['user'] = $response->entities[0]->id;
            }

            $response = $this->client->post('/publish/videos', array(
                  'file' => curl_file_create($videopath),
                  'info' => json_encode($info)
                ),
                array(),
                array(
                    CURLOPT_TIMEOUT => $this->uploadcurltimeout
                )
            );

            return isset($response->id) ? $response->id : null;
        } catch(ClientException $e) {
            $this->send_sending_video_failed_event($videofile->get_id(), $e->getMessage());
        } catch(Exception $e) {
            $this->send_requesting_openveo_failed_event($e->getMessage());
        }
        return null;
    }

    /**
     * Builds video description using information from video contexts.
     *
     * @param array $videocontexts The video contexts for original video file and its aliases
     * @return string The HTML description of the video
     */
    protected function build_video_description(array $videocontexts) : string {
        $description = '';

        foreach ($videocontexts as $videocontext) {
            $linktext = $videocontext->resolve_text($this->nameformats[$videocontext->type]);
            $label = '';

            // First context is the context of the original video file
            if (empty($description)) {
                $label = get_string('openveooriginallabel', 'tool_openveo_migration');
            } else {
                $label = get_string('openveoaliaslabel', 'tool_openveo_migration');
            }

            $description .= "<li>$label: <a href='{$videocontext->get_url()}'>$linktext</a></li>";
        }

        return "<ul>$description</ul>";
    }

    /**
     * Sends a "sending_video_failed" event.
     *
     * @param int $id The id of the video on error
     * @param string $message The error message
     */
    protected function send_sending_video_failed_event(int $id, string $message) {
        $event = sending_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
