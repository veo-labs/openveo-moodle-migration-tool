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
 * Defines Moodle migration task.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\task;

defined('MOODLE_INTERNAL') || die();

// Include OpenVeo REST PHP client autoloader.
require_once($CFG->dirroot . '/local/openveo_api/lib.php');
require_once($CFG->dirroot . '/repository/lib.php');

use Generator;
use context_system;
use moodle_exception;
use repository;
use core\event\base;
use core\task\scheduled_task;
use core_form\filetypes_util;
use Openveo\Client\Client;
use Openveo\Exception\ClientException;
use tool_openveo_migration\local\statuses;
use tool_openveo_migration\local\states;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\file_system;
use tool_openveo_migration\local\video_machine;
use tool_openveo_migration\local\utils;
use tool_openveo_migration\event\getting_registered_video_failed;
use tool_openveo_migration\event\getting_video_failed;
use tool_openveo_migration\event\planning_video_failed;
use tool_openveo_migration\event\video_migration_started;
use tool_openveo_migration\event\video_migration_ended;
use tool_openveo_migration\event\video_migration_failed;

/**
 * Defines Moodle migrate task to migrate Moodle videos to OpenVeo.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class migrate extends scheduled_task {

    /**
     * A provider to find videos from Moodle database.
     *
     * @var videos_provider
     */
    protected $videosprovider;

    /**
     * Initializes migrate task.
     */
    public function __construct() {
        global $DB;
        $this->videosprovider = new videos_provider($DB, get_file_storage(), new file_system());
    }

    /**
     * Gets the translated name of the task.
     *
     * @return string The task name
     */
    public function get_name() {
        return get_string('taskmigratename', 'tool_openveo_migration');
    }

    /**
     * Executes the task.
     *
     * Only one video is migrated at a time. When migration of a video is finished (or has failed) migration of next video will
     * automatically start and so on.
     */
    public function execute() {
        $filetypesutil = new filetypes_util();
        $url = get_config('local_openveo_api', 'webserviceurl');
        $clientid = get_config('local_openveo_api', 'webserviceclientid');
        $clientsecret = get_config('local_openveo_api', 'webserviceclientsecret');
        $certificatefilepath = get_config('local_openveo_api', 'webservicecertificatefilepath');
        $videotypestomigrate = get_config('tool_openveo_migration', 'videotypestomigrate');
        $automaticmigrationactivated = get_config('tool_openveo_migration', 'automaticmigrationactivated');
        $destinationplatform = get_config('tool_openveo_migration', 'destinationplatform');
        $statuspollingfrequency = get_config('tool_openveo_migration', 'statuspollingfrequency');

        // Validate configuration. At least one type of videos and a destination platform are needed to start the migration.
        $videotypestomigrate = $filetypesutil->normalize_file_types($videotypestomigrate);
        if (sizeof($videotypestomigrate) === 0 || empty($destinationplatform)) {
            throw new moodle_exception('errormigrationwrongconfiguration', 'tool_openveo_migration');
        }

        // Create an OpenVeo web service client.
        try {
            $client = new Client($url, $clientid, $clientsecret, $certificatefilepath);
        } catch(ClientException $e) {
            throw new moodle_exception('errorlocalpluginnotconfigured', 'tool_openveo_migration');
        }

        // Build file fields from configuration.
        $filefields = utils::get_moodle_file_fields();

        // Retrieve all MIME types corresponding to videotypestomigrate setting.
        $acceptedmimetypes = file_get_typegroup('type', $videotypestomigrate);

        // Get the id of the OpenVeo repository instance.
        // As OpenVeo repository can have only one instance, the first one found will be the one.
        // We need an OpenVeo repository instance because references to migrated files will be associated to the OpenVeo repository.
        $openveorepositoryid = $this->get_openveo_repository_id();

        if (!isset($openveorepositoryid)) {
            throw new moodle_exception('errornorepositoryopenveo', 'tool_openveo_migration');
        }

        mtrace('Start migration');

        $filesystem = new file_system();
        $filestorage = get_file_storage();

        foreach ($this->generate_videos_to_migrate(
                $acceptedmimetypes,
                $filefields,
                $automaticmigrationactivated
        ) as $video) {

            // Got the video to migrate.

            $videoid = $video->get_file()->get_id();
            $this->send_video_migration_started_event($videoid, $video->get_file()->get_filename());

            // Migrate video using states machine.
            try {
                $machine = new video_machine(
                    $video,
                    $client,
                    $this->videosprovider,
                    $filestorage,
                    $filesystem,
                    $destinationplatform,
                    $statuspollingfrequency,
                    $filefields,
                    $openveorepositoryid
                );
                if (!$machine->execute()) {
                    $this->send_video_migration_failed_event($videoid, $video->get_filename());
                } else {
                    $this->send_video_migration_ended_event($videoid, $video->get_filename());
                }
            } catch(Exception $e) {
                $this->send_video_migration_failed_event($videoid, $video->get_filename());
            }

        }

        mtrace('Migration done');
    }

    /**
     * Logs event dispatched by the migration.
     *
     * Display the event message using mtrace.
     *
     * @param core\event\base $event The migration event
     */
    public static function log_migration_event(base $event) {
        mtrace($event->get_description());
    }

    /**
     * Creates a generator to generate videos to migrate.
     *
     * This is a generator to find the next Moodle video to migrate.
     * If automatic migration is activated video is taken in the tool_openveo_migration table first and then in Moodle files. If
     * automatic migration is deactivated video is taken only from tool_openveo_migration table.
     *
     * @param array $acceptedmimetypes The list of accepted MIME types
     * @param array $filefields An associative array describing the Moodle file fields with component/filearea as key and an
     * associative array as values containing information about the field (component, filearea, supportedmethods)
     * @param bool $automaticmigrationactivated true to automatically take videos from Moodle files, false to only take videos from
     * tool_openveo_migration table
     * @return Generator
     */
    protected function generate_videos_to_migrate(array $acceptedmimetypes, array $filefields,
                                                  bool $automaticmigrationactivated = false) : Generator {

        $hasvideo = true;
        while ($hasvideo) {

            // Get the first video from tool_openveo_migration table with status "planned".
            try {
                $video = $this->videosprovider->get_registered_video_by_status(statuses::PLANNED);
            } catch (Exception $e) {
                $this->send_getting_registered_video_failed_event($e->getMessage());
            }

            if (isset($video)) {
                yield $video;
            } else {
                $hasvideo = false;
            }
        }

        if (!$automaticmigrationactivated) {
            return null;
        }

        // No video manually planned and automatic migration is activated.
        // Look for Moodle files using file fields configuration.
        foreach ($filefields as $filefield) {

            $hasvideo = true;
            while ($hasvideo) {

                // Get the first video from Moodle files associated to the field and make sure video MIME type is part of accepted
                // MIME types.
                try {
                    $video = $this->videosprovider->get_video($filefield['component'], $filefield['filearea'], $acceptedmimetypes);
                } catch (Exception $e) {
                    $this->send_getting_video_failed_event(
                            $filefield['component'],
                            $filefield['filearea'],
                            $acceptedmimetypes,
                            $e->getMessage()
                    );

                    // Getting video failed.
                    // Jump to next field to avoid infinite loop. If video has not been migrated then next video fetched for this
                    // field will be the same. Error has to be diagnosed before retrying to get the video.

                    break;
                }

                if (empty($video)) {

                    // No more video associated to the field.
                    // Continue to next field.
                    $hasvideo = false;

                } else {

                    // Got a video associated to the field.
                    // Add video to planned videos.
                    // Add the migration id, the migration status and the migration state to the video.
                    try {
                        $video = $this->videosprovider->plan_video($video);
                    } catch (Exception $e) {
                        $this->send_planning_video_failed_event(
                                $video->get_id(),
                                $e->getMessage()
                        );

                        // Planning video failed.
                        // Jump to next field to avoid infinite loop. If video has not been migrated then next video fetched for this
                        // field will be the same. Error has to be diagnosed before retrying to plan the video.

                        break;
                    }

                    yield $video;

                }

            }

        }

    }

    /**
     * Gets OpenVeo Repository instance id.
     *
     * @return int The OpenVeo Repository instance id or null if not found
     */
    protected function get_openveo_repository_id() {
        foreach (repository::get_instances() as $repositoryid => $repository) {
            if ($repository->get_typename() === 'openveo') {
                return $repository->id;
            }
        }

        return null;
    }

    /**
     * Sends a "getting_registered_video_failed" event.
     *
     * @param string $message The error message
     */
    protected function send_getting_registered_video_failed_event(string $message) {
        $event = getting_registered_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "planning_video_failed" event.
     *
     * @param int $id The video id
     * @param string $message The error message
     */
    protected function send_planning_video_failed_event(int $id, string $message) {
        $event = planning_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'message' => $message
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "getting_video_failed" event.
     *
     * @param string $component The component holding the video
     * @param string $filearea The file area the video is contained in
     * @param array $mimetypes The list of searched MIME types
     * @param string $message The error message
     */
    protected function send_getting_video_failed_event(string $component, string $filearea, array $mimetypes, string $message) {
        $event = getting_video_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'component' => $component,
                'filearea' => $filearea,
                'mimetypes' => $mimetypes,
                'message' => $message
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "video_migration_started" event.
     *
     * @param int $id The video id
     * @param string $filename The video file name
     */
    protected function send_video_migration_started_event(int $id, string $filename) {
        $event = video_migration_started::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'filename' => $filename
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "video_migration_failed" event.
     *
     * @param int $id The video id
     * @param string $filename The video file name
     */
    protected function send_video_migration_failed_event(int $id, string $filename) {
        $event = video_migration_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'filename' => $filename
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "video_migration_ended" event.
     *
     * @param int $id The video id
     * @param string $filename The video file name
     */
    protected function send_video_migration_ended_event(int $id, string $filename) {
        $event = video_migration_ended::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'id' => $id,
                'filename' => $filename
            )
        ));
        $event->trigger();
    }

}
