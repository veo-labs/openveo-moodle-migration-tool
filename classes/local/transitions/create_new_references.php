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
 * Defines transition to create new references.
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
use tool_openveo_migration\event\creating_reference_failed;

/**
 * Defines transition to create new references in Moodle, pointing to the OpenVeo video.
 *
 * This transition creates the new Moodle references pointing to the OpenVeo video. It also creates a new reference for all Moodle
 * alisaes which were pointing to the original video.
 * Transition succeeds if new references have been successfully created for the original video and all its aliases.
 * Properties of a stored_file instance prefixed by "tom" are properties added by the OpenVeo Migration Tool. "tom" stands for
 * "Tool OpenVeo Migration".
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_new_references extends video_transition {

    /**
     * The videos provider.
     *
     * @var tool_openveo_migration\local\videos_provider
     */
    protected $videosprovider;

    /**
     * The id of the OpenVeo Repository instance.
     *
     * @var int
     */
    protected $openveorepositoryid;

    /**
     * Builds transition.
     *
     * @param stored_file $video The Moodle video file to migrate
     * @param tool_openveo_migration\local\videos_provider $videosprovider The videos provider
     * @param int $openveorepositoryid The id of the OpenVeo repository instance to associate new references to
     */
    public function __construct(stored_file &$video, videos_provider $videosprovider, int $openveorepositoryid) {
        parent::__construct($video);
        $this->videosprovider = $videosprovider;
        $this->openveorepositoryid = $openveorepositoryid;
    }

    /**
     * Executes transition.
     *
     * Original references and new references are added to the original video object. It could be useful for other transitions.
     *
     * @return bool true if transition succeeded, false if something went wrong
     */
    public function execute() : bool {
        $this->originalvideo->tomnewreferences = array();

        if (!isset($this->originalvideo->tomopenveoid)) {
            return false;
        }

        // Create a reference into Moodle pointing to the external OpenVeo video.
        $newreference = $this->create_video_reference(
                array(
                    'contextid' => $this->originalvideo->get_contextid(),
                    'component' => $this->originalvideo->get_component(),
                    'filearea' => $this->originalvideo->get_filearea(),
                    'itemid' => $this->originalvideo->get_itemid(),
                    'filepath' => $this->originalvideo->get_filepath(),
                    'filename' => $this->originalvideo->get_filename(),
                    'userid' => $this->originalvideo->get_userid(),
                    'mimetype' => $this->originalvideo->get_mimetype(),
                    'status' => $this->originalvideo->get_status(),
                    'author' => $this->originalvideo->get_author(),
                    'license' => $this->originalvideo->get_license(),
                    'timecreated' => $this->originalvideo->get_timecreated(),
                    'timemodified' => $this->originalvideo->get_timemodified(),
                    'sortorder' => $this->originalvideo->get_sortorder()
                ),
                $this->openveorepositoryid,
                $this->originalvideo->tomopenveoid
        );
        if (!$newreference) {
            return false;
        }
        $this->originalvideo->tomnewreferences[] = $newreference;

        if (isset($this->originalvideo->tomaliases)) {

            // Original video got aliases.

            // For each Moodle aliases pointing to the original video, create a new reference pointing to the new external OpenVeo
            // video.
            foreach ($this->originalvideo->tomaliases as $alias) {
                $newreference = $this->create_video_reference(
                        $alias,
                        $this->openveorepositoryid,
                        $this->originalvideo->tomopenveoid
                );
                if (!isset($newreference)) {
                    return false;
                }
                $this->originalvideo->tomnewreferences[] = $newreference;
            }

        }

        return true;
    }

    /**
     * Gets non-localised transition name.
     *
     * @return string The transition name
     */
    public function get_name() : string {
        return 'Create new references';
    }

    /**
     * Creates a Moodle file reference pointing to an OpenVeo video.
     *
     * The new video reference within Moodle will be almost the same as the original file. Difference resides in the source which
     * contains the id of the video on OpenVeo.
     *
     * @param array $record The original record to use as the base of the new reference
     * @param int $repositoryid The id of the Moodle OpenVeo Repository instance the reference will be associated to
     * @param string $openveovideoid The id of the video in OpenVeo
     * @return stored_file The new reference or false if failed
     */
    protected function create_video_reference(array $record, int $repositoryid, string $openveovideoid) {
        try {
            $record['source'] = $openveovideoid;
            $videoreference = $this->videosprovider->create_video_reference($record, $repositoryid, $openveovideoid);
            return $videoreference;
        } catch(Exception $e) {
            $this->send_creating_reference_failed($originalvideo->get_id(), $e->getMessage());
            return false;
        }
    }

    /**
     * Sends a "creating_reference_failed" event.
     *
     * @param int $id The video id
     * @param string $message The error message
     */
    protected function send_creating_reference_failed(int $id, string $message) {
        $event = creating_reference_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

}
