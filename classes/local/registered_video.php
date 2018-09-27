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
 * Defines a Moodle video file registered for migration.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local;

defined('MOODLE_INTERNAL') || die();

use stored_file;

/**
 * Defines a Moodle video file registered for migration.
 *
 * A registered video represents a Moodle video file in the migration process, from UNREGISTERED to MIGRATED.
 * Depending on its status a video may miss information. For example a MIGRATED video won't have an associative Moodle
 * video file as it does not exist anymore. UNREGISTERED / NOT_SUPPORTED videos won't have any migration information because they
 * haven't been registered yet.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class registered_video {

    /**
     * The Moodle file corresponding to the registered video.
     *
     * @var stored_file
     */
    protected $file;

    /**
     * The registration id.
     *
     * @var int
     */
    protected $id;

    /**
     * The video migration status.
     *
     * @var int
     */
    protected $status;

    /**
     * The video migration state.
     *
     * @var int
     */
    protected $state;

    /**
     * The migrated video file name.
     *
     * @var string
     */
    protected $filename;

    /**
     * The ids of contexts where video is used or was used.
     *
     * @var array
     */
    protected $contextids;

    /**
     * The UNIX timestamp corresponding to the video creation.
     *
     * @var int
     */
    protected $timecreated;

    /**
     * The migrated video MIME type.
     *
     * @var string
     */
    protected $mimetype;

    /**
     * The list of Moodle reference files, pointing to the OpenVeo video, as stored_file instances.
     *
     * @var array
     */
    protected $newreferences;

    /**
     * The id of the video on OpenVeo.
     *
     * @var string
     */
    protected $openveoid;

    /**
     * The list of video aliases.
     *
     * @var array
     */
    protected $aliases;

    /**
     * Creates a registered video.
     *
     * @param stored_file $file The Moodle video file
     * @param int $id The registration id
     * @param int $status The migration status
     * @param int $state The migration state
     * @param string $filename The name of the migrated video
     * @param array $contextids The ids of contexts where video is used or was used
     * @param int $timecreated The UNIX timestamp corresponding to migrated file creation
     * @param string $mimetype The MIME type of the migrated video
     */
    public function __construct(stored_file $file = null, int $id = null, int $status = null, int $state = null,
                                string $filename = null, array $contextids = null, int $timecreated = null,
                                string $mimetype = null) {
        $this->file = $file;
        $this->id = $id;
        $this->status = $status;
        $this->state = $state;
        $this->filename = $filename;
        $this->contextids = $contextids;
        $this->timecreated = $timecreated;
        $this->mimetype = $mimetype;
    }

    /**
     * Gets Moodle video file associated to the registered video.
     *
     * @return stored_file The Moodle file or null if no associated file
     */
    public function get_file() {
        return $this->file;
    }

    /**
     * Gets registration id.
     *
     * @return int The registration id
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Gets video migration status.
     *
     * @return int The migration status or null if not registered
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * Sets video migration status.
     *
     * @param int $status The new status
     */
    public function set_status(int $status) {
        $this->status = $status;
    }

    /**
     * Gets video migration state.
     *
     * @return int The migration state or null if not registered
     */
    public function get_state() {
        return $this->state;
    }

    /**
     * Sets video migration state.
     *
     * @param int $state The new state
     */
    public function set_state(int $state) {
        $this->state = $state;
    }

    /**
     * Gets the migrated file name.
     *
     * @return string The file name
     */
    public function get_filename() {
        return $this->filename;
    }

    /**
     * Gets ids of contexts where video is used or was used.
     *
     * @return array The list of context ids
     */
    public function get_contextids() {
        return isset($this->contextids) ? $this->contextids : array();
    }

    /**
     * Gets the migrated file creation time.
     *
     * @return int The file creation time
     */
    public function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * Gets the migrated file MIME type.
     *
     * @return string The file MIME type
     */
    public function get_mimetype() {
        return $this->mimetype;
    }

    /**
     * Gets Moodle video files pointing to the OpenVeo video.
     *
     * @return array The list of references
     */
    public function get_new_references() {
        return isset($this->newreferences) ? $this->newreferences : array();
    }

    /**
     * Sets Moodle video files which point to the OpenVeo video.
     *
     * @param array $newreferences The list of references
     */
    public function set_new_references(array $newreferences) {
        $this->newreferences = $newreferences;
    }

    /**
     * Gets the list of video aliases.
     *
     * @return array The list of aliases as stored_file instances
     */
    public function get_aliases() {
        return isset($this->aliases) ? $this->aliases : array();
    }

    /**
     * Sets video file aliases.
     *
     * @param array $aliases The list of aliases
     */
    public function set_aliases(array $aliases) {
        $this->aliases = $aliases;
    }

    /**
     * Gets video id on OpenVeo.
     *
     * @return string The OpenVeo id
     */
    public function get_openveo_id() {
        return $this->openveoid;
    }

    /**
     * Sets the video id on OpenVeo.
     *
     * @param string $openveoid The OpenVeo id
     */
    public function set_openveo_id(string $openveoid) {
        $this->openveoid = $openveoid;
    }

}
