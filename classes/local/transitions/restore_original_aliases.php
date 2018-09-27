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
 * Defines a transition to restore original video aliases.
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
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\event\restoring_original_aliases_failed;

/**
 * Defines a transition to restore original Moodle video file aliases.
 *
 * Transition succeeds if restoring original video file aliases succeeded.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_original_aliases extends video_transition {

    /**
     * The videos provider.
     *
     * @var tool_openveo_migration\local\videos_provider
     */
    protected $videosprovider;

    /**
     * Builds transition.
     *
     * @param registered_video $video The registered video to migrate
     * @param tool_openveo_migration\local\videos_provider $videosprovider The videos provider
     */
    public function __construct(registered_video &$video, videos_provider $videosprovider) {
        parent::__construct($video);
        $this->videosprovider = $videosprovider;
    }

    /**
     * Executes transition.
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        $aliases = $this->originalvideo->get_aliases();
        $videofile = $this->originalvideo->get_file();

        if (!isset($aliases)) {
            return true;
        }

        try {
            $reference = $this->videosprovider->pack_video_reference($videofile);

            foreach ($aliases as $alias) {
                $this->videosprovider->create_video_reference(
                        $alias,
                        $alias['repositoryid'],
                        $reference
                );
            }
        } catch (Exception $e) {
            $this->send_restoring_original_aliases_failed_event($videofile->get_id(), $e->getMessage());
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
        return 'Restore original video aliases';
    }

    /**
     * Sends a "restoring_original_aliases_failed" event.
     *
     * @param int $id The video id
     * @param string $message The error message
     */
    protected function send_restoring_original_aliases_failed_event(int $id, string $message) {
        $event = restoring_original_aliases_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
