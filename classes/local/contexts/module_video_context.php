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
 * Defines a Moodle video file inside a module context.
 *
 * @package tool_openveo_migration
 * @copyright 2019 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\contexts;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use context_module;
use stored_file;
use tool_openveo_migration\local\contexts\video_context;

/**
 * Defines a module context holding a Moodle video file.
 *
 * @package tool_openveo_migration
 * @copyright 2019 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class module_video_context extends video_context {

    /**
     * The module id associated to the Moodle context.
     *
     * @var string
     */
    protected $moduleid;

    /**
     * The module name associated to the Moodle context.
     *
     * @var string
     */
    protected $modulename;

    /**
     * The course associated to the Moodle context.
     *
     * @var stdClass
     */
    protected $course;

    /**
     * Builds a module context.
     *
     * @param stored_file $video The video that belongs to this context (either the original or an alias)
     * @param context_module $moodlecontext The Moodle context
     * @param stdClass $course The course the module belongs to
     * @param string $moduleid The id of the module associated to the Moodle context
     * @param string $modulename The name of the module associated to the Moodle context
     */
    public function __construct(stored_file &$video, context_module &$moodlecontext, stdClass &$course, string $moduleid, string $modulename) {
        parent::__construct($video, $moodlecontext);
        $this->moduleid = $moduleid;
        $this->modulename = $modulename;
        $this->course = $course;
        $this->type = 'module';
    }

    /**
     * Resolves a text regarding the context.
     *
     * Use this function to resolve a text by replacing its tokens by their values inside the context.
     * Module context has the following available tokens:
     * - %filename%: The Moodle video file name
     * - %moduleid%: The module id
     * - %modulename%: The module name
     * - %courseid%: The course id
     * - %courseidnumber%: The course id number
     * - %coursecategoryid%: The id of the category the course belongs to
     * - %coursefullname%: The course full name
     * - %courseshortname%: The course short name
     *
     * @param string text The text containing tokens to resolve
     * @return string The resolved text
     */
    public function resolve_text(string $text) : string {
        return str_replace(array(
            '%moduleid%',
            '%modulename%',
            '%courseid%',
            '%courseidnumber%',
            '%coursecategoryid%',
            '%coursefullname%',
            '%courseshortname%'
        ), array(
            $this->moduleid,
            $this->modulename,
            $this->course->id,
            $this->course->idnumber,
            $this->course->category,
            $this->course->fullname,
            $this->course->shortname
        ), parent::resolve_text($text));
    }

}
