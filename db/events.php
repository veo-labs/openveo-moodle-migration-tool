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
 * Defines plugin event observers.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => 'tool_openveo_migration\event\video_migration_started',
        'callback' => 'tool_openveo_migration\task\migrate::log_migration_event'
    ),

    array(
        'eventname' => 'tool_openveo_migration\event\video_migration_ended',
        'callback' => 'tool_openveo_migration\task\migrate::log_migration_event'
    ),

    array(
        'eventname' => 'tool_openveo_migration\event\video_migration_failed',
        'callback' => 'tool_openveo_migration\task\migrate::log_migration_event'
    ),

    array(
        'eventname' => 'tool_openveo_migration\event\video_transition_started',
        'callback' => 'tool_openveo_migration\task\migrate::log_migration_event'
    ),

    array(
        'eventname' => 'tool_openveo_migration\event\video_transition_ended',
        'callback' => 'tool_openveo_migration\task\migrate::log_migration_event'
    ),

    array(
        'eventname' => 'tool_openveo_migration\event\video_transition_failed',
        'callback' => 'tool_openveo_migration\task\migrate::log_migration_event'
    )

);
