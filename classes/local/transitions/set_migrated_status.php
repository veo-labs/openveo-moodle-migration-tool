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
 * Defines a transition setting the video as migrated.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\transitions;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;
use context_system;
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\statuses;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\event\updating_video_migration_status_failed;

/**
 * Defines a transition setting the video as migrated.
 *
 * It sets the video status to migrated and saves information about the migrated video to keep a trace of it.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_migrated_status extends video_transition {

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
     * @param videos_provider $videosprovider The videos provider
     */
    public function __construct(registered_video &$video, videos_provider $videosprovider) {
        parent::__construct($video);
        $this->videosprovider = $videosprovider;
    }

    /**
     * Executes transition.
     *
     * // TODO: Get the contexts of video aliases too
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        $videofile = $this->originalvideo->get_file();

        try {
            $data = new stdClass();
            $data->id = $this->originalvideo->get_id();
            $data->status = statuses::MIGRATED;
            $data->filename = $videofile->get_filename();
            $data->contextid = $videofile->get_contextid();
            $data->timecreated = $videofile->get_timecreated();
            $data->mimetype = $videofile->get_mimetype();
            $this->videosprovider->update_registered_video($data);
        } catch (Exception $e) {
            $this->send_updating_video_migration_status_failed_event(
                    $videofile->get_id(),
                    statuses::MIGRATED,
                    $e->getMessage()
            );
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
        return 'Set migrated status';
    }

    /**
     * Sends a "updating_planned_video_status_failed" event.
     *
     * @param int $id The video id
     * @param int $status The expected migration status
     * @param string $message The error message
     */
    protected function send_updating_video_migration_status_failed_event(int $id, int $status, string $message) {
        $event = updating_video_migration_status_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'status' => $status,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
