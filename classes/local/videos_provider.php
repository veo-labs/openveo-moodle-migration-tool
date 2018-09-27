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
 * Defines the videos provider.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use stored_file;
use file_storage;
use moodle_database;
use tool_openveo_migration\local\statuses;
use tool_openveo_migration\local\states;
use tool_openveo_migration\local\file_system;
use tool_openveo_migration\local\registered_video;

/**
 * Defines a provider to manage Moodle video files and registered videos.
 *
 * Registered videos refer to videos added to the tool_openveo_migration table and videos refer to Moodle videos.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class videos_provider {

    /**
     * The database instance.
     *
     * @var moodle_database
     */
    protected $database;

    /**
     * Moodle file storage.
     *
     * @var file_storage
     */
    protected $filestorage;

    /**
     * Extended Moodle file system to manipulate video files.
     *
     * @var tool_openveo_migration\local\file_system
     */
    protected $filesystem;

    /**
     * Creates a videos_provider.
     *
     * @param moodle_database $database The database instance
     * @param file_storage $filestorage The file storage instance
     * @param file_system $filesystem The file system instance
     */
    public function __construct(moodle_database $database, file_storage $filestorage, file_system $filesystem) {
        $this->database = $database;
        $this->filestorage = $filestorage;
        $this->filesystem = $filesystem;
    }

    /**
     * Gets a registered video by its migration status.
     *
     * The first video found in tool_openveo_migration table, with the expected status, will be returned.
     *
     * @param $status The migration status of the video
     * @return stored_file The video with the following additional properties or null if not found
     *     - **tommigrationid** The migration id
     *     - **tomstatus** The migration status
     *     - **tomstate** The migration state
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_registered_video_by_status(string $status) {

        // Fetch one video from tool_openveo_migration table.
        $videos = $this->database->get_records('tool_openveo_migration', array('status' => $status), '', '*', 0, 1);

        if (sizeof($videos) === 0) {
            return null;
        }

        // Get all information about the associated Moodle file.
        $registeredvideo = current($videos);
        $video = $this->filestorage->get_file_by_id($registeredvideo->filesid);

        return new registered_video(
                $video,
                intval($registeredvideo->id),
                intval($registeredvideo->status),
                intval($registeredvideo->state)
        );

    }

    /**
     * Gets a not registered video, from Moodle files, by component and filearea.
     *
     * Only videos are returned, not aliases. Only video not already present in tool_openveo_migration table are returned.
     * The first video found will be returned.
     * Two requests are performed to let file_storage manipulate the "files" table as much as possible.
     *
     * @param string $component The component the video belongs to
     * @param string $filearea The filearea the video is contained in
     * @param array $mimetypes The list of accepted MIME types
     * @return stored_file The video or null if not found
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_video(string $component, string $filearea, array $mimetypes = null) {
        list($mimetypesexpression, $mimetypesvalues) = $this->database->get_in_or_equal($mimetypes, SQL_PARAMS_QM, 'mimetype');
        $parameters = array_merge(array($component, $filearea), $mimetypesvalues);

        $query = "SELECT f.id
                FROM {files} f
                LEFT JOIN {tool_openveo_migration} tom ON f.id = tom.filesid
                WHERE component = ?
                AND filearea = ?
                AND mimetype $mimetypesexpression
                AND f.referencefileid IS NULL
                AND tom.filesid IS NULL";

        // get_records_sql is used here instead of get_record_sql because we know that there may be more than one record and we don't
        // want a debugging message to be logged.
        $videos = $this->database->get_records_sql($query, $parameters, 0, 1);

        if (sizeof($videos) === 0) {
            return null;
        } else {
            return $this->filestorage->get_file_by_id(current($videos)->id);
        }
    }

    /**
     * Plans a video for migration.
     *
     * It adds a new record to the tool_openveo_migration table with status "planned".
     *
     * @param stored_file $video The video to plan
     * @return int The migration id
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function plan_video(stored_file $video) : int {
        $record = new stdClass();
        $record->filesid = $video->get_id();
        $record->status = statuses::PLANNED;
        $record->state = states::NOT_INITIALIZED;
        return $this->database->insert_record('tool_openveo_migration', $record);
    }

    /**
     * Updates a registered video migration status.
     *
     * @param registered_video $video The registered video
     * @param int $status The new migration status
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function update_video_migration_status(registered_video &$video, int $status) {
        $data = new stdClass();
        $data->id = $video->get_id();
        $data->status = $status;
        $video->set_status($status);
        $this->database->update_record('tool_openveo_migration', $data);
    }

    /**
     * Updates a registered video migration state.
     *
     * @param registered_video $video The registered video
     * @param int $state The new migration state
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function update_video_migration_state(registered_video &$video, int $state) {
        $data = new stdClass();
        $data->id = $video->get_id();
        $data->state = $state;
        $video->set_state($state);
        $this->database->update_record('tool_openveo_migration', $data);
    }

    /**
     * Updates a registered video.
     *
     * @param stdClass $data The properties to update (should contain the migration id in "id" property)
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function update_registered_video(stdClass $data) {
        $this->database->update_record('tool_openveo_migration', $data);
    }

    /**
     * Creates a new Moodle file referencing an OpenVeo video.
     *
     * @param array $videorecord The Moodle file record as an associative array
     * @param int $repositoryid The id of the OpenVeo Respository instance the reference will be associated to
     * @param string $openveovideoid The id of the video on OpenVeo
     * @return stored_file The new reference
     * @throws moodle_exception A Moodle exception if something went wrong
     */
    public function create_video_reference(array $videorecord, int $repositoryid, string $openveovideoid) : stored_file {
        return $this->filestorage->create_file_from_reference($videorecord, $repositoryid, $openveovideoid);
    }

    /**
     * Gets the list of Moodle aliases pointing to a Moodle video file.
     *
     * @param stored_file $video The original video file
     * @return array The list of aliases
     * @throws moodle_exception A Moodle exception if something went wrong
     */
    public function get_video_aliases(stored_file $video) : array {
        return $this->filestorage->get_references_by_storedfile($video);
    }

    /**
     * Gets a Moodle video file by id.
     *
     * @param int $id The video id
     * @return stored_file The video or false if not found
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_video_by_id(int $id) {
        return $this->filestorage->get_file_by_id($id);
    }

    /**
     * Removes a Moodle video file.
     *
     * @param stored_file $video The video file to remove
     * @throws Exception Either a dml_exception or moodle_exception if something went wrong
     */
    public function remove_video(stored_file $video) {
        $video->delete();
    }

    /**
     * Restores video from trash directory.
     *
     * Video is copied from trash directory. Video in trash directory stays untouched and will be removed by Moodle schedule tasks.
     *
     * @param stored_file $video The video file to restore
     * @return stored_file The restored video
     * @throws Exception Either a dml_exception or moodle_exception if something went wrong
     */
    public function restore_video(stored_file $video) {
        $filerecord = array(
            'contextid' => $video->get_contextid(),
            'component' => $video->get_component(),
            'filearea' => $video->get_filearea(),
            'itemid' => $video->get_itemid(),
            'sortorder' => $video->get_sortorder(),
            'filepath' => $video->get_filepath(),
            'filename' => $video->get_filename(),
            'timecreated' => $video->get_timecreated(),
            'timemodified' => $video->get_timemodified(),
            'mimetype' => $video->get_mimetype(),
            'userid' => $video->get_userid(),
            'source' => $video->get_source(),
            'author' => $video->get_author(),
            'license' => $video->get_license(),
            'status' => $video->get_status()
        );
        return $this->filestorage->create_file_from_pathname($filerecord, $this->filesystem->get_trash_file_path($video));
    }

    /**
     * Packs a Moodle video file as a source for references.
     *
     * @param stored_file $video The video file to use as the source
     * @return string The video reference encoded in base64
     */
    public function pack_video_reference(stored_file $video) {
        $data = new stdClass();
        $data->contextid = $video->get_contextid();
        $data->component = $video->get_component();
        $data->itemid = $video->get_itemid();
        $data->filearea = $video->get_filearea();
        $data->filepath = $video->get_filepath();
        $data->filename = $video->get_filename();
        return $this->filestorage::pack_reference($data);
    }

    /**
     * Removes all draft files corresponding to a video.
     *
     * It means all draft files with the same content hash as the video. It includes draft files and draft folders.
     *
     * @param stored_file $video The video file corresponding to the draft files to remove
     * @throws Exception Either a dml_exception or moodle_exception if something went wrong
     */
    public function remove_video_draft_files(stored_file $video) {
        $query = "SELECT *
                FROM {files}
                WHERE component = 'user'
                AND filearea = 'draft'
                AND contenthash = ?";
        $draftfiles = $this->database->get_recordset_sql($query, array($video->get_contenthash()));

        foreach ($draftfiles as $draftfile) {
            $this->filestorage->get_file_instance($draftfile)->delete();
        }

        $draftfiles->close();
    }

}
