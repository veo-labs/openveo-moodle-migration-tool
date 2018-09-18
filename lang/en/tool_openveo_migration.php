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
 * Defines english translations.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin name in administration
$string['pluginname'] = 'OpenVeo Migration Tool';

// Privacy (GDPR)
$string['privacy:metadata'] = 'The plugin OpenVeo Migration Tool does not store any personal data.';

// Settings page
$string['settingstitle'] = 'OpenVeo Migration Tool settings';
$string['settingsdescription'] = '<p>Configure how Moodle videos will be migrated to OpenVeo. Only videos of type defined here will be migrated, make sure OpenVeo can handle these types of videos first. With the OpenVeo Migration Tool it is possible to select the videos you want to migrate using the migration page but it is also possible to automatically migrate videos, without having to select them (if all present and future videos need to be migrated), using option "Automatic migration". You can stop migration at any time by stopping corresponding scheduled task.</p>';
$string['settingsvideotypestomigratelabel'] = 'Video types to migrate';
$string['settingsvideotypestomigrate'] = 'Video types to migrate';
$string['settingsvideotypestomigrate_help'] = 'The list of video types to migrate to OpenVeo. Make sure OpenVeo Publish accepts the video types defined here.';
$string['settingsautomaticmigrationactivatedlabel'] = 'Automatic migration';
$string['settingsautomaticmigrationactivatedcheckboxlabel'] = 'Automatically migrate videos';
$string['settingsautomaticmigrationactivated'] = 'Automatic migration';
$string['settingsautomaticmigrationactivated_help'] = 'Activating automatic migration will automatically migrate all Moodle videos (present and future) to OpenVeo without having to select them.';
$string['settingsdestinationplatformlabel'] = 'Destinaton platform';
$string['settingsdestinationplatform'] = 'Destinaton platform';
$string['settingsdestinationplatform_help'] = 'Choose the destination platform for videos to migrate. OpenVeo can store the migrated videos on different platforms.';
$string['settingsdestinationplatformlocal'] = 'OpenVeo';
$string['settingsdestinationplatformvimeo'] = 'Vimeo';
$string['settingsdestinationplatformyoutube'] = 'Youtube';
$string['settingsdestinationplatformtls'] = 'TLS';
$string['settingsdestinationplatformwowza'] = 'Wowza';
$string['settingsstatuspollingfrequencylabel'] = 'Status polling frequency (in seconds)';
$string['settingsstatuspollingfrequency'] = 'Status polling frequency (in seconds)';
$string['settingsstatuspollingfrequency_help'] = 'When migrating a Moodle video to OpenVeo, OpenVeo Migration Tool frequently asks OpenVeo about the video status until video has been completely treated. Default polling frequency is 10.';
$string['settingsstatuspollingfrequencyformaterror'] = 'Invalid frequency (e.g. 10).';
$string['settingsfilefieldslabel'] = 'File fields';
$string['settingsfilefields'] = 'File fields';
$string['settingsfilefields_help'] = 'The list of fields of type "editor" and "filemanager" used to upload files. If a reference to an OpenVeo video is added from a field not defined in here, OpenVeo Migration Tool won\'t migrate it. Each line represents a field with three columns: the component holding the field (component), the file area (filearea) and the supported methods (supportedmethods). Columns are separated by pipes. More information available on <a href="https://github.com/veo-labs/openveo-moodle-migration-tool" target="_blank">plugin\'s page</a>. Not that the order of lines is important as it determines the priority of automatic migration. Videos corresponding to the first field (first line) will be migrated before videos corresponding to the second field (second line) and son on.';
$string['settingssubmitlabel'] = 'Save changes';

// Errors
$string['errorlocalpluginnotconfigured'] = 'Local plugin "OpenVeo API" is not configured.';
$string['errornovideoplatform'] = 'No video platform configured in OpenVeo Publish.';
$string['errormigrationwrongconfiguration'] = 'Migration needs at least one type of videos and a destination platform.';
$string['errornorepositoryopenveo'] = 'No repository OpenVeo found.';

// Events
$string['eventgettingplatformsfailed'] = 'Getting video platforms failed';
$string['eventvideomigrationstarted'] = 'Video migration started';
$string['eventvideomigrationended'] = 'Video migration finished';
$string['eventvideomigrationfailed'] = 'Video migration failed';
$string['eventvideotransitionstarted'] = 'Video transition started';
$string['eventvideotransitionended'] = 'Video transition finished';
$string['eventvideotransitionfailed'] = 'Video transition failed';
$string['eventgettingregisteredvideofailed'] = 'Getting planned video failed';
$string['eventgettingvideofailed'] = 'Getting video failed';
$string['eventplanningvideofailed'] = 'Planning video failed';
$string['eventupdatingvideomigrationstatusfailed'] = 'Updating video migration status failed';
$string['eventsendingvideofailed'] = 'Sending video failed';
$string['eventwaitingforopenveovideofailed'] = 'Waiting for OpenVeo video failed';
$string['eventremovingopenveovideofailed'] = 'Removing OpenVeo video failed';
$string['eventgettingopenveovideofailed'] = 'Getting OpenVeo video failed';
$string['eventpublishingopenveovideofailed'] = 'Publishing OpenVeo video failed';
$string['eventcreatingreferencefailed'] = 'Creating video reference failed';
$string['eventverifyingvideofailed'] = 'Video verification failed';
$string['eventremovingreferencesfailed'] = 'Removing video references failed';
$string['eventremovingoriginalfailed'] = 'Removing original video failed';
$string['eventremovingoriginalaliasesfailed'] = 'Removing original video aliases failed';
$string['eventremovingdraftfilesfailed'] = 'Removing video draft files failed';
$string['eventconnectingtoopenveofailed'] = 'Connecting to OpenVeo failed';
$string['eventrestoringoriginalfailed'] = 'Restoring original video failed';
$string['eventrestoringoriginalaliasesfailed'] = 'Restoring original video aliases failed';
$string['eventupdatingregisteredvideoidfailed'] = 'Updating registered video id failed';

// Tasks
$string['taskmigratename'] = 'Migrate Moodle videos to OpenVeo';
