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
 * External administration page to plan videos for migration.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use tool_openveo_migration\output\planning_page;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\file_system;

// Initialize the planning page (layout, context, URL, page title, site name), verify that user is logged in and has enough
// permission and set planning page as active in the admin tree.
admin_externalpage_setup('tool_openveo_migration_planning');

$videosprovider = new videos_provider($DB, get_file_storage(), new file_system());
$page = new planning_page(get_string('planningtitle', 'tool_openveo_migration'), $PAGE->url, $videosprovider);

// Render page.
$renderer = $PAGE->get_renderer('tool_openveo_migration');
echo $renderer->header();
echo $renderer->render($page);
echo $renderer->footer();
