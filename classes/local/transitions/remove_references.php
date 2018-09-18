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
 * Defines transition to remove newly created references.
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
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\event\removing_references_failed;

/**
 * Defines transition to remove references pointing to the OpenVeo video.
 *
 * Transition succeeds if references have been successfully removed.
 * Properties of a stored_file instance prefixed by "tom" are properties added by the OpenVeo Migration Tool. "tom" stands for
 * "Tool OpenVeo Migration".
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class remove_references extends video_transition {

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
        if (!isset($this->originalvideo->tomnewreferences) || sizeof($this->originalvideo->tomnewreferences) === 0) {
            return true;
        }

        // Remove references.
        try {
            foreach ($this->originalvideo->tomnewreferences as $reference) {
                $this->videosprovider->remove_video($reference);
            }
        } catch(Exception $e) {
            $this->send_removing_references_failed_event($this->originalvideo->get_id(), $e->getMessage());
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
        return 'Remove new references';
    }

    /**
     * Sends a "removing_references_failed" event.
     *
     * @param string $id The video id
     * @param string $message The error message
     */
    protected function send_removing_references_failed_event(int $id, string $message) {
        $event = removing_references_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
