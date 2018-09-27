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
use context_system;
use tool_openveo_migration\local\transitions\video_transition;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\event\creating_reference_failed;

/**
 * Defines transition to create new references in Moodle, pointing to the OpenVeo video.
 *
 * This transition creates the new Moodle references pointing to the OpenVeo video. It also creates a new reference for all Moodle
 * alisaes which were pointing to the original video.
 * Transition succeeds if new references have been successfully created for the original video and all its aliases.
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
     * @param registered_video $video The registered video to migrate
     * @param tool_openveo_migration\local\videos_provider $videosprovider The videos provider
     * @param int $openveorepositoryid The id of the OpenVeo repository instance to associate new references to
     */
    public function __construct(registered_video &$video, videos_provider $videosprovider, int $openveorepositoryid) {
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
        $newreferences = array();
        $openveoid = $this->originalvideo->get_openveo_id();
        $videofile = $this->originalvideo->get_file();

        if (!isset($openveoid)) {
            return false;
        }

        // Create a reference into Moodle pointing to the external OpenVeo video.
        $newreference = $this->create_video_reference(
                array(
                    'contextid' => $videofile->get_contextid(),
                    'component' => $videofile->get_component(),
                    'filearea' => $videofile->get_filearea(),
                    'itemid' => $videofile->get_itemid(),
                    'filepath' => $videofile->get_filepath(),
                    'filename' => $videofile->get_filename(),
                    'userid' => $videofile->get_userid(),
                    'mimetype' => $videofile->get_mimetype(),
                    'status' => $videofile->get_status(),
                    'author' => $videofile->get_author(),
                    'license' => $videofile->get_license(),
                    'timecreated' => $videofile->get_timecreated(),
                    'timemodified' => $videofile->get_timemodified(),
                    'sortorder' => $videofile->get_sortorder()
                ),
                $this->openveorepositoryid,
                $openveoid
        );
        if (!$newreference) {
            return false;
        }
        $newreferences[] = $newreference;
        $this->originalvideo->set_new_references($newreferences);

        $aliases = $this->originalvideo->get_aliases();
        if (isset($aliases)) {

            // Original video got aliases.

            // For each Moodle aliases pointing to the original video, create a new reference pointing to the new external OpenVeo
            // video.
            foreach ($aliases as $alias) {
                $newreference = $this->create_video_reference(
                        $alias,
                        $this->openveorepositoryid,
                        $openveoid
                );
                if (!isset($newreference)) {
                    return false;
                }
                $newreferences[] = $newreference;
                $this->originalvideo->set_new_references($newreferences);
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
            $this->send_creating_reference_failed($this->originalvideo->get_file()->get_id(), $e->getMessage());
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
