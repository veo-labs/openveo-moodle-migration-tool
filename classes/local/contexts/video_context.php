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
 * Defines a Moodle context holding a video file.
 *
 * @package tool_openveo_migration
 * @copyright 2019 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\contexts;

defined('MOODLE_INTERNAL') || die();

use context;
use moodle_url;
use stored_file;

/**
 * Defines a Moodle context wrapper for a video.
 *
 * Each video file in Moodle belongs to a context. A video_context stores context information about a Moodle video file. Stored
 * information depends on the context type, each context type corresponds to a video_context sub class.
 *
 * @package tool_openveo_migration
 * @copyright 2019 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class video_context {

    /**
     * The video that belongs to the context (either the original or an alias).
     *
     * @var stored_file
     */
    protected $video;

    /**
     * The wrapped Moodle context.
     *
     * @var context
     */
    protected $moodlecontext;

    /**
     * The video_context type.
     *
     * @var string
     */
    public $type;

    /**
     * Creates a context.
     *
     * Use context sub classes to get an instance of video_context.
     *
     * @param stored_file $video The video that belongs to this context (either the original or an alias)
     * @param context $moodlecontext The Moodle context
     */
    public function __construct(stored_file &$video, context &$moodlecontext) {
        $this->video = $video;
        $this->moodlecontext = $moodlecontext;
        $this->type = 'default';
    }

    /**
     * Gets Moodle context URL.
     *
     * @return moodle_url The context URL
     */
    public function get_url() : moodle_url {
        return $this->moodlecontext->get_url();
    }

    /**
     * Resolves a text containing tokens.
     *
     * Use this function to resolve a text containing tokens. Each sub-class has its own tokens, refer to sub-class documentation
     * for the list of available tokens.
     *
     * @param string text The text containing tokens to resolve
     * @return string The resolved text
     */
    public function resolve_text(string $text) : string {
        return str_replace(array(
            '%filename%'
        ), array(
            $this->video->get_filename()
        ), $text);
    }

}
