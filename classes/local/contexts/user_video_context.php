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
 * Defines a Moodle video file inside a user context.
 *
 * @package tool_openveo_migration
 * @copyright 2019 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\contexts;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use context_user;
use stored_file;
use tool_openveo_migration\local\contexts\video_context;

/**
 * Defines a user context holding a Moodle video file.
 *
 * @package tool_openveo_migration
 * @copyright 2019 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_video_context extends video_context {

    /**
     * The user associated to the Moodle context.
     *
     * @var stdClass
     */
    protected $user;

    /**
     * Builds a user context.
     *
     * @param stored_file $video The video that belongs to this context (either the original or an alias)
     * @param context_user $moodlecontext The Moodle context
     * @param stdClass $user The user associated to the Moodle context
     */
    public function __construct(stored_file &$video, context_user &$moodlecontext, stdClass &$user) {
        parent::__construct($video, $moodlecontext);
        $this->user = $user;
        $this->type = 'user';
    }

    /**
     * Resolves a text regarding the context.
     *
     * Use this function to resolve a text by replacing its tokens by their values inside the context.
     * User context has the following available tokens:
     * - %filename%: The Moodle video file name
     * - %userid%: The user id
     * - %username%: The user name
     * - %userfirstname%: The user first name
     * - %userlastname%: The user last name
     * - %useremail%: The user email
     *
     * @param string text The text containing tokens to resolve
     * @return string The resolved text
     */
    public function resolve_text(string $text) : string {
        return str_replace(array(
            '%userid%',
            '%username%',
            '%userfirstname%',
            '%userlastname%',
            '%useremail%'
        ), array(
            $this->user->id,
            $this->user->username,
            $this->user->firstname,
            $this->user->lastname,
            $this->user->email
        ), parent::resolve_text($text));
    }

}
