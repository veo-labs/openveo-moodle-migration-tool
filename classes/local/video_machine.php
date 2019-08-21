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
 * Defines a states machine capable of migrating a Moodle video file to OpenVeo.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

// Include OpenVeo REST PHP client autoloader.
require_once($CFG->dirroot . '/local/openveo_api/lib.php');

use context_system;
use file_storage;
use tool_openveo_migration\local\machine\machine;
use tool_openveo_migration\local\machine\no_operation;
use tool_openveo_migration\local\states;
use tool_openveo_migration\local\statuses;
use tool_openveo_migration\local\transitions\set_migrating_status;
use tool_openveo_migration\local\transitions\send_video;
use tool_openveo_migration\local\transitions\wait_for_openveo_video;
use tool_openveo_migration\local\transitions\publish_openveo_video;
use tool_openveo_migration\local\transitions\create_new_references;
use tool_openveo_migration\local\transitions\verify_video;
use tool_openveo_migration\local\transitions\remove_original;
use tool_openveo_migration\local\transitions\remove_original_aliases;
use tool_openveo_migration\local\transitions\remove_draft_files;
use tool_openveo_migration\local\transitions\set_migrated_status;
use tool_openveo_migration\local\transitions\remove_references;
use tool_openveo_migration\local\transitions\remove_openveo_video;
use tool_openveo_migration\local\transitions\restore_original;
use tool_openveo_migration\local\transitions\restore_original_aliases;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\file_system;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\event\video_transition_started;
use tool_openveo_migration\event\video_transition_ended;
use tool_openveo_migration\event\video_transition_failed;
use Openveo\Client\Client;

/**
 * Defines a states machine capable of migrating a Moodle video file to OpenVeo.
 *
 * Video status and state evolve during the migration process.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_machine extends machine {

    /**
     * The registered video to migrate.
     *
     * @var registered_video
     */
    protected $originalvideo;

    /**
     * The videos provider.
     *
     * @var tool_openveo_migration\local\videos_provider
     */
    protected $videosprovider;

    /**
     * Creates a machine to migrate a Moodle video file to OpenVeo.
     *
     * @param registered_video $originalvideo The registered video to migrate
     * @param Openveo\Client\Client $client The OpenVeo web service client
     * @param tool_openveo_migration\local\videos_provider $videosprovider The videos provider
     * @param file_storage $filestorage The Moodle file storage instance
     * @param tool_openveo_migration\local\file_system $filesystem A file system capable of getting the absolute path of a Moodle
     * file (implementing get_local_path)
     * @param string $platform The videos platform to upload to (see OpenVeo Publish documentation)
     * @param array $nameformats The formats to use to name videos on OpenVeo depending on the context
     * @param int $statuspollingfrequency The frequency of polling requests when waiting for OpenVeo to treat the uploaded video
     * (in seconds).
     * @param array $filefields An associative array describing the Moodle file fields with component/filearea as key and an
     * associative array as values containing information about the field (component, filearea, supportedmethods)
     * @param int $openveorepositoryid The id of the OpenVeo Repository instance to associate the new video to
     * @param int $uploadcurltimeout The cURL upload timeout in seconds
     */
    public function __construct(registered_video &$originalvideo, Client $client, videos_provider $videosprovider,
                                file_storage $filestorage, file_system $filesystem, string $platform, array $nameformats,
                                int $statuspollingfrequency, array $filefields, int $openveorepositoryid, int $uploadcurltimeout) {
        $this->originalvideo = $originalvideo;
        $this->state = $originalvideo->get_state();
        $this->rollback = false;
        $this->videosprovider = $videosprovider;

        $this->transitions = array(

            // Transitions.
            array(
                'from' => states::NOT_INITIALIZED,
                'to' => states::INITIALIZED,
                'transition' => new set_migrating_status($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::INITIALIZED,
                'to' => states::SENT,
                'transition' => new send_video($this->originalvideo, $client, $filesystem, $platform, $nameformats,
                    $uploadcurltimeout, $this->videosprovider)
            ),
            array(
                'from' => states::SENT,
                'to' => states::TREATED,
                'transition' => new wait_for_openveo_video($this->originalvideo, $client, $statuspollingfrequency)
            ),
            array(
                'from' => states::TREATED,
                'to' => states::PUBLISHED,
                'transition' => new publish_openveo_video($this->originalvideo, $client)
            ),
            array(
                'from' => states::PUBLISHED,
                'to' => states::VERIFIED,
                'transition' => new verify_video($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::VERIFIED,
                'to' => states::ORIGINAL_ALIASES_REMOVED,
                'transition' => new remove_original_aliases($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::ORIGINAL_ALIASES_REMOVED,
                'to' => states::ORIGINAL_REMOVED,
                'transition' => new remove_original($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::ORIGINAL_REMOVED,
                'to' => states::ORIGINAL_DRAFT_FILES_REMOVED,
                'transition' => new remove_draft_files($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::ORIGINAL_DRAFT_FILES_REMOVED,
                'to' => states::NEW_REFERENCES_CREATED,
                'transition' => new create_new_references($this->originalvideo, $this->videosprovider, $openveorepositoryid)
            ),
            array(
                'from' => states::NEW_REFERENCES_CREATED,
                'to' => states::MIGRATED,
                'transition' => new set_migrated_status($this->originalvideo, $this->videosprovider)
            ),

            // Rollback transitions.
            // Once the video has been verified (VERIFIED state) rollback is no longer possible. The original Moodle file will
            // be removed.
            array(
                'from' => states::MIGRATED,
                'to' => states::NEW_REFERENCES_CREATED,
                'transition' => new no_operation()
            ),
            array(
                'from' => states::NEW_REFERENCES_CREATED,
                'to' => states::ORIGINAL_DRAFT_FILES_REMOVED,
                'transition' => new remove_references($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::ORIGINAL_DRAFT_FILES_REMOVED,
                'to' => states::ORIGINAL_REMOVED,
                'transition' => new no_operation()
            ),
            array(
                'from' => states::ORIGINAL_REMOVED,
                'to' => states::ORIGINAL_ALIASES_REMOVED,
                'transition' => new restore_original($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::ORIGINAL_ALIASES_REMOVED,
                'to' => states::VERIFIED,
                'transition' => new restore_original_aliases($this->originalvideo, $this->videosprovider)
            ),
            array(
                'from' => states::VERIFIED,
                'to' => states::PUBLISHED,
                'transition' => new no_operation()
            ),
            array(
                'from' => states::PUBLISHED,
                'to' => states::TREATED,
                'transition' => new no_operation()
            ),
            array(
                'from' => states::TREATED,
                'to' => states::SENT,
                'transition' => new no_operation()
            ),
            array(
                'from' => states::SENT,
                'to' => states::INITIALIZED,
                'transition' => new remove_openveo_video($this->originalvideo, $client)
            ),
            array(
                'from' => states::INITIALIZED,
                'to' => states::NOT_INITIALIZED,
                'transition' => new no_operation()
            )

        );

        $this->states = array(
            states::NOT_INITIALIZED,
            states::INITIALIZED,
            states::SENT,
            states::TREATED,
            states::PUBLISHED,
            states::VERIFIED,
            states::ORIGINAL_ALIASES_REMOVED,
            states::ORIGINAL_REMOVED,
            states::ORIGINAL_DRAFT_FILES_REMOVED,
            states::NEW_REFERENCES_CREATED,
            states::MIGRATED
        );
    }

    /**
     * Handles a changing of state.
     *
     * Machine state has changed, change the state of the planned video.
     *
     * @param int $oldstate The old state
     * @param int $newstate The new state
     */
    protected function handle_state_changed(int $oldstate, int $newstate) {
        $this->videosprovider->update_video_migration_state($this->originalvideo, $newstate);
    }

    /**
     * Handles a machine abort.
     *
     * Machine aborted, change the state of the planned video to error.
     */
    protected function handle_abort() {
        $this->videosprovider->update_video_migration_status($this->originalvideo, statuses::ERROR);
    }

    /**
     * Handles a transition start.
     *
     * Transition started, send an event about it.
     *
     * @param string $name The transition name
     */
    protected function handle_transition_started(string $name) {
        $this->send_video_transition_started_event($this->originalvideo->get_file()->get_id(), $name);
    }

    /**
     * Handles a transition end.
     *
     * Transition ended, send an event about it.
     *
     * @param string $name The transition name
     */
    protected function handle_transition_ended(string $name) {
        $this->send_video_transition_ended_event($this->originalvideo->get_file()->get_id(), $name);
    }

    /**
     * Handles a transition fail.
     *
     * Transition failed, send an event about it.
     *
     * @param string $name The transition name
     */
    protected function handle_transition_failed(string $name) {
        $this->send_video_transition_failed_event($this->originalvideo->get_file()->get_id(), $name);
    }

    /**
     * Sends a "video_transition_started" event.
     *
     * @param int $id The video id
     * @param string $name The name of the transition
     */
    protected function send_video_transition_started_event(int $id, string $name) {
        $event = video_transition_started::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'name' => $name
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "video_transition_ended" event.
     *
     * @param int $id The video id
     * @param string $name The name of the transition
     */
    protected function send_video_transition_ended_event(int $id, string $name) {
        $event = video_transition_ended::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'name' => $name
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "video_transition_failed" event.
     *
     * @param int $id The video id
     * @param string $name The name of the transition
     */
    protected function send_video_transition_failed_event(int $id, string $name) {
        $event = video_transition_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'name' => $name
            )
        ));
        $event->trigger();
    }

}
