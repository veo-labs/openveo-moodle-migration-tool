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
 * Defines a new administration page to configure the migration.
 *
 * Settings page is an external page because we want to retrieve and verify the available platforms configured in
 * OpenVeo. Moodle default admin pages don't offer enough control to do it.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Ensure current user has "moodle/site:config" capability on system context.
if ($hassiteconfig) {

    // Create the settings page.
    // In an admin tool plugin $settings is not defined.
    $settingspage = new admin_externalpage(
            'tool_openveo_migration',
            get_string('settingstitle', 'tool_openveo_migration'),
            "$CFG->wwwroot/admin/tool/openveo_migration/openveo_migration_settings.php"
    );

    // Create the planning page.
    $migrationpage = new admin_externalpage(
            'tool_openveo_migration_planning',
            get_string('planningtitle', 'tool_openveo_migration'),
            "$CFG->wwwroot/admin/tool/openveo_migration/openveo_migration_planning.php"
    );

    // Add pages to the admin tree (admin_root class) in 'tools' category.
    $ADMIN->add('tools', $settingspage);
    $ADMIN->add('tools', $migrationpage);

}
