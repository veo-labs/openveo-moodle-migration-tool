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
use context;
use context_coursecat;
use context_course;
use context_module;
use context_block;
use context_user;
use tool_openveo_migration\local\statuses;
use tool_openveo_migration\local\states;
use tool_openveo_migration\local\file_system;
use tool_openveo_migration\local\registered_video;
use tool_openveo_migration\local\contexts\course_video_context;
use tool_openveo_migration\local\contexts\module_video_context;
use tool_openveo_migration\local\contexts\category_video_context;
use tool_openveo_migration\local\contexts\block_video_context;
use tool_openveo_migration\local\contexts\user_video_context;

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
     * Gets the contexts where appears a Moodle video file.
     *
     * @param stored_file $video The original video
     * @return array The list of context ids
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_video_context_ids(stored_file $video) : array {
        $contextids = array();

        // Find where the video and its aliases are used.
        $contextids[] = $video->get_contextid();
        $aliases = $this->get_video_aliases($video);
        foreach ($aliases as $id => $alias) {
            $contextids[] = $alias->get_contextid();
        }

        return $contextids;
    }

    /**
     * Gets contexts for the registered video and its aliases.
     *
     * @param registered_video $video The registered video
     * @return array The list of video contexts
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_video_contexts(registered_video $video) : array {
        $contexts = array();
        $videofiles = $this->get_video_aliases($video->get_file());
        $videofiles[] = $video->get_file();
        $contextids = $video->get_contextids();

        foreach ($contextids as $contextid) {

            // Find video file reference corresponding to the context (either the original or an alias)
            $videofile = array_reduce($videofiles, function($carry, $item) use ($contextid) {
                if (!empty($carry) && $carry->get_contextid() === $contextid) {
                    return $carry;
                } else if ($item->get_contextid() === $contextid) {
                    return $item;
                }

                return null;
            });

            if (empty($videofile)) {
                continue;
            }

            $context = context::instance_by_id($contextid);
            $videocontext;

            if (empty($context)) {
                continue;
            }

            if ($context instanceof context_course &&
                $course = $this->database->get_record('course', array('id' => $context->instanceid))) {

                // Video is part of a course context
                $videocontext = new course_video_context($videofile, $context, $course);

            } else if ($context instanceof context_module &&
                $module = $this->database->get_record_sql("SELECT cm.*, md.name AS modulename
                        FROM {course_modules} cm
                        JOIN {modules} md ON md.id = cm.module
                        WHERE cm.id = ?", array($context->instanceid))) {
                if ($course = $this->database->get_record('course', array('id' => $module->course))) {

                    // Video is part of a module context
                    $videocontext = new module_video_context($videofile, $context, $course, $module->id , get_string('modulename', $module->modulename));

                }
            } else if ($context instanceof context_coursecat &&
               $category = $this->database->get_record('course_categories', array('id' => $context->instanceid))) {

               // Video is part of a category context
               $videocontext = new category_video_context($videofile, $context, $category);

            } else if ($context instanceof context_block &&
                $block = $this->database->get_record('block_instances', array('id' => $context->instanceid))) {

                // Video is part of a block context
                $course;

                if ($coursecontext = $context->get_course_context()) {

                    // Block is associated to a course
                    $course = $this->database->get_record('course', array('id' => $coursecontext->instanceid));

                }

                global $CFG;
                require_once("$CFG->dirroot/blocks/moodleblock.class.php");
                require_once("$CFG->dirroot/blocks/$block->blockname/block_$block->blockname.php");
                $blockname = "block_$block->blockname";
                if ($blockobject = new $blockname()) {
                    $block->name = $blockobject->title;
                    $videocontext = new block_video_context($videofile, $context, $block, $course);
                }
            } else if ($context instanceof context_user &&
                $user = $this->database->get_record('user', array('id' => $context->instanceid, 'deleted' => 0))) {

                // Video is part of a user context
                $videocontext = new user_video_context($videofile, $context, $user);

            } else {
                die;
                continue;
            }
            $contexts[] = $videocontext;
        }

        return $contexts;
    }

    /**
     * Gets video owner information.
     *
     * @param stored_file $video The original video
     * @return stdClass The video owner
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_video_owner(stored_file $video) : stdClass {
        return $this->database->get_record('user', ['id' => $video->get_userid()]);
    }

    /**
     * Gets a registered video by its migration status.
     *
     * The first video found in tool_openveo_migration table, with the expected status, will be returned.
     *
     * @param $status The migration status of the video
     * @return registered_video The registered video
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
        $owner = $this->get_video_owner($video);

        return new registered_video(
                $video,
                intval($registeredvideo->id),
                intval($registeredvideo->status),
                intval($registeredvideo->state),
                $registeredvideo->filename,
                explode(',', $registeredvideo->contextids),
                $registeredvideo->timecreated,
                $registeredvideo->mimetype,
                $owner
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
                WHERE f.component = ?
                AND f.filearea = ?
                AND f.mimetype $mimetypesexpression
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
     * Gets Moodle video files with migration information.
     *
     * Only original videos are returned, not aliases.
     * There are several statuses: ERROR, PLANNED, MIGRATING, MIGRATED, UNREGISTERED, BLOCKED and NOT_SUPPORTED.
     * ERROR, PLANNED, MIGRATING and MIGRATED will behave as expected returning the list of videos corresponding with the status.
     * NOT_SUPPORTED status is not supported by this method as requesting for videos in the list of supported fields (from
     * configuration) will result in a large database query. NOT_SUPPORTED videos will be part of results for a status set to
     * UNREGISTERED or null.
     * UNREGISTERED status corresponds to all videos not registered in tool_openveo_migration table which means it embraces both
     * UNREGISTERED and NOT_SUPPORTED videos.
     * BLOCKED status correponds to videos in status ERROR with a state different from NOT_INITIALIZED.
     * If status is not set, then it will return either NOT_SUPPORTED / UNREGISTERED videos and videos with a stable status.
     *
     * Returned videos may miss information depending on their status. For example a MIGRATED video won't have an associative Moodle
     * video file as it does not exist anymore. UNREGISTERED / NOT_SUPPORTED videos won't have any migration information.
     *
     * @param int $from A timestamp to get only videos created at this date or after
     * @param int $to A timestamp to get only videos created at this date or before
     * @param array $mimetypes The MIME types to get only videos of these types
     * @param int $status The migration status to get only videos in this status
     * @param int $limit The maximum number of videos to get
     * @param int $page The page to fetch in the paginated system
     * @return array An associative array containing the paginated results with a key "page" holding the current page, a key "limit"
     * holding the limit number of fetched videos, a key "pages" holding the total number of pages, a key "total" holding the total
     * number of videos and a key "results" holding the list of registered_video instances
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function get_videos(int $from = null, int $to = null, array $mimetypes = null, int $status = null, int $limit = 10,
                               int $page = 0) : array {
        $results = array();
        $limit = !empty($limit) ? $limit : 10;
        $page = max(0, $page);
        $offset = $page * $limit;
        list($mimetypesexpression, $mimetypesvalues) = $this->database->get_in_or_equal($mimetypes, SQL_PARAMS_QM, 'mimetype');
        $parameters = $mimetypesvalues;

        // Get Moodle video file information from files table and migration information from tool_openveo_migration table.
        // The trick here is that we want information from both files table and tool_openveo_migration table but sometimes
        // no relation exist between the two tables. It happens when a video is migrated, then it has an entry in
        // tool_openveo_migration table but no associated entry in files table. It also happens when a video is not registered yet
        // and thus has an entry in files table but not in tool_openveo_migration.
        // We need a full outer join to get entries from files and tool_openveo_migration tables even if not relation exists between
        // them.
        // The first SELECT request gets all videos present in files table with associated information from tool_openveo_migration if
        // any.
        // The second SELECT request gets all videos not present in files tables but in tool_openveo_migration (the MIGRATED videos).
        // The first SELECT uses the files table "id" column as the id and the second SELECT uses the tool_openveo_migration table
        // "id" column as the id because there is no corresponding entry in the files table, then the column "id" in files table is
        // null.
        // To avoid collisions between entries returned by the first SELECT and entries returned by the second SELECT, the second
        // SELECT id in prefixed by "tom-".
        // Also note that the second SELECT gets tool_openveo_migration columns "mimetype" and "timecreated" overriding equivalent
        // columns in files table because we filter all entries using these columns.
        $query = "FROM (
                SELECT f.id, f.contenthash, f.pathnamehash, f.contextid, f.component, f.filearea, f.itemid, f.filepath,
                        f.filename, f.userid, f.filesize, f.mimetype, f.status, f.source, f.author, f.license, f.timecreated,
                        f.timemodified, f.sortorder, f.referencefileid, tom.id as tommigrationid, tom.status as tomstatus,
                        tom.state as tomstate, tom.filename as tomfilename, tom.contextids as tomcontextids
                FROM {files} f
                LEFT JOIN {tool_openveo_migration} tom ON f.id = tom.filesid

                UNION ALL

                SELECT CONCAT('tom-', tom.id) as id, f.contenthash, f.pathnamehash, f.contextid, f.component, f.filearea,
                        f.itemid, f.filepath, f.filename, f.userid, f.filesize, tom.mimetype, f.status, f.source, f.author,
                        f.license, tom.timecreated, f.timemodified, f.sortorder, f.referencefileid, tom.id as tommigrationid,
                        tom.status as tomstatus, tom.state as tomstate, tom.filename as tomfilename, tom.contextids as tomcontextids
                FROM {tool_openveo_migration} tom
                LEFT JOIN {files} f ON f.id = tom.filesid
                WHERE f.id IS NULL
            ) as v
            WHERE v.referencefileid IS NULL
            AND v.mimetype $mimetypesexpression
            AND (v.filearea <> 'draft' OR v.filearea IS NULL)";

        if (!empty($from)) {
            $query .= ' AND v.timecreated >= ? ';
            $parameters[] = $from;
        }

        if (!empty($to)) {
            $query .= ' AND v.timecreated <= ? ';
            $parameters[] = $to;
        }

        if (isset($status)) {
            if ($status === statuses::UNREGISTERED) {
                $query .= ' AND v.tomstatus IS NULL ';
            } else if ($status === statuses::BLOCKED) {
                $query .= ' AND v.tomstatus = ? ';
                $parameters[] = statuses::ERROR;

                $query .= ' AND v.tomstate <> ? ';
                $parameters[] = states::NOT_INITIALIZED;
            } else if ($status === statuses::ERROR) {
                $query .= ' AND v.tomstatus = ? AND v.tomstate = ? ';
                $parameters[] = statuses::ERROR;
                $parameters[] = states::NOT_INITIALIZED;
            } else {
                $query .= ' AND v.tomstatus = ? ';
                $parameters[] = $status;
            }
        }

        $total = $this->database->count_records_sql("SELECT COUNT(*) $query", $parameters);
        $videos = $this->database->get_records_sql(
                "SELECT * $query",
                $parameters,
                $offset,
                $limit
        );

        if (sizeof($videos) !== 0) {
            foreach ($videos as $id => $video) {
                $contextids = array();
                $file = null;

                if (isset($video->contenthash) && !isset($video->tomcontextids)) {

                    // Video has a corresponding Moodle file but is not registered yet.
                    // Retrieve it.
                    $file = $this->filestorage->get_file_by_id($id);

                    // Find where the video and its aliases are used.
                    $contextids = $this->get_video_context_ids($file);

                } else if (isset($video->tomcontextids)) {

                    // Video has no corresponding Moodle file.
                    // Video has been migrated.
                    $contextids = explode(',', $video->tomcontextids);

                }

                $results[] = new registered_video($file, $video->tommigrationid, $video->tomstatus, $video->tomstate,
                                                  $video->tomfilename, $contextids, $video->timecreated, $video->mimetype);
            }
        }

        return array(
            'page' => $page,
            'limit' => $limit,
            'pages' => intval(ceil($total / $limit)),
            'total' => $total,
            'results' => $results
        );
    }

    /**
     * Plans a video for migration.
     *
     * It adds a new record to the tool_openveo_migration table with status "planned".
     *
     * @param stored_file $video The video to plan
     * @return registered_video The new registered video
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function plan_video(stored_file $video) : registered_video {
        $contextids = $this->get_video_context_ids($video);
        $owner = $this->get_video_owner($video);

        $record = new stdClass();
        $record->filesid = $video->get_id();
        $record->status = statuses::PLANNED;
        $record->state = states::NOT_INITIALIZED;
        $record->filename = $video->get_filename();
        $record->contextids = implode(',', $contextids);
        $record->timecreated = $video->get_timecreated();
        $record->mimetype = $video->get_mimetype();

        $id = $this->database->insert_record('tool_openveo_migration', $record);

        return new registered_video(
                $video,
                $id,
                $record->status,
                $record->state,
                $record->filename,
                $contextids,
                $record->timecreated,
                $record->mimetype,
                $owner
        );
    }

    /**
     * Plans videos for migration.
     *
     * It adds new records to the tool_openveo_migration table with status "planned".
     *
     * @param array $videos The Moodle video files to plan
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function plan_videos(array $videos) {
        $records = array();

        foreach ($videos as $video) {
            $record = new stdClass();
            $record->filesid = $video->get_id();
            $record->status = statuses::PLANNED;
            $record->state = states::NOT_INITIALIZED;
            $record->filename = $video->get_filename();
            $record->contextids = implode(',', $this->get_video_context_ids($video));
            $record->timecreated = $video->get_timecreated();
            $record->mimetype = $video->get_mimetype();
            $records[] = $record;
        }

        $this->database->insert_records('tool_openveo_migration', $records);
    }

    /**
     * Deregisters videos from migration.
     *
     * It removes records from tool_openveo_migration table.
     *
     * @param array $ids The registered video ids
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    public function deregister_videos(array $ids) {
        $this->database->delete_records_list('tool_openveo_migration', 'id', $ids);
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
