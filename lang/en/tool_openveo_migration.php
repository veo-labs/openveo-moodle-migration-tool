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

// Plugin name in administration.
$string['pluginname'] = 'OpenVeo Migration Tool';

// Privacy (GDPR).
$string['privacy:metadata'] = 'The plugin OpenVeo Migration Tool does not store any personal data.';

// Settings page.
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
$string['settingsdestinationplatformchoose'] = 'Choose...';
$string['settingsdestinationplatformlocal'] = 'OpenVeo';
$string['settingsdestinationplatformvimeo'] = 'Vimeo';
$string['settingsdestinationplatformyoutube'] = 'Youtube';
$string['settingsdestinationplatformtls'] = 'TLS';
$string['settingsdestinationplatformwowza'] = 'Wowza';
$string['settingsdestinationgrouplabel'] = 'Destinaton group';
$string['settingsdestinationgroup'] = 'Destinaton group';
$string['settingsdestinationgroup_help'] = 'Choose an OpenVeo group to assign to migrated videos.';
$string['settingsdestinationgroupchoose'] = 'Choose...';
$string['settingsmigratedcoursevideonameformatlabel'] = 'OpenVeo video name format for videos in a course context';
$string['settingsmigratedcoursevideonameformat'] = 'OpenVeo video name format for course context videos';
$string['settingsmigratedcoursevideonameformat_help'] = 'The format to use as video names on OpenVeo for videos in a course context. Available tokens are: <ul><li><strong>%filename%</strong>: The video file name</li><li><strong>%courseid%</strong>: The course ID</li><li><strong>%courseidnumber%</strong>: The course ID number</li><li><strong>%coursecategoryid%</strong>: The course category id</li><li><strong>%coursefullname%</strong>: The course full name</li><li><strong>%courseshortname%</strong>: The course short name</li></ul> (default is "%courseid% - %filename%")';
$string['settingsmigratedmodulevideonameformatlabel'] = 'OpenVeo video name format for videos in an activity / resource context';
$string['settingsmigratedmodulevideonameformat'] = 'OpenVeo activity / resource video name format';
$string['settingsmigratedmodulevideonameformat_help'] = 'The format to use as video names on OpenVeo for videos in an activity or resource context. Available tokens are: <ul><li><strong>%filename%</strong>: The video file name</li><li><strong>%moduleid%</strong>: The activity / resource ID</li><li><strong>%modulename%</strong>: The activity / resource name</li><li><strong>%courseid%</strong>: The course ID</li><li><strong>%courseidnumber%</strong>: The course ID number</li><li><strong>%coursecategoryid%</strong>: The course category id</li><li><strong>%coursefullname%</strong>: The course full name</li><li><strong>%courseshortname%</strong>: The course short name</li></ul> (default is "%moduleid% - %filename%")';
$string['settingsmigratedcategoryvideonameformatlabel'] = 'OpenVeo video name format for videos in a category context';
$string['settingsmigratedcategoryvideonameformat'] = 'OpenVeo video name format for videos in a category context';
$string['settingsmigratedcategoryvideonameformat_help'] = 'The format to use as video names on OpenVeo for videos in a category context. Available tokens are: <ul><li><strong>%filename%</strong>: The video file name</li><li><strong>%categoryid%</strong>: The category ID</li><li><strong>%categoryidnumber%</strong>: The category ID number</li><li><strong>%categoryname%</strong>: The category name</li></ul> (default is "%categoryid% - %filename%")';
$string['settingsmigratedblockvideonameformatlabel'] = 'OpenVeo video name format for videos in a block context';
$string['settingsmigratedblockvideonameformat'] = 'OpenVeo video name format for videos in a block context';
$string['settingsmigratedblockvideonameformat_help'] = 'The format to use as video names on OpenVeo for videos in a block context. Available tokens are: <ul><li><strong>%filename%</strong>: The video file name</li><li><strong>%blockid%</strong>: The block ID</li><li><strong>%blockname%</strong>: The block name</li></ul>The following tokens are available only for blocks associated to a course:<ul><li><strong>%courseid%</strong>: The course ID</li><li><strong>%courseidnumber%</strong>: The course ID number</li><li><strong>%coursecategoryid%</strong>: The course category id</li><li><strong>%coursefullname%</strong>: The course full name</li><li><strong>%courseshortname%</strong>: The course short name</li></ul> (default is "%blockid% - %filename%")';
$string['settingsmigrateduservideonameformatlabel'] = 'OpenVeo video name format for videos in a user context';
$string['settingsmigrateduservideonameformat'] = 'OpenVeo user video name format for videos in a user context';
$string['settingsmigrateduservideonameformat_help'] = 'The format to use as video names on OpenVeo for videos in a user context. Available tokens are: <ul><li><strong>%filename%</strong>: The video file name</li><li><strong>%userid%</strong>: The user ID</li><li><strong>%username%</strong>: The user name</li><li><strong>%userfirstname%</strong>: The user first name</li><li><strong>%userlastname%</strong>: The user last name</li><li><strong>%useremail%</strong>: The user email</li></ul> (default is "%userid% - %filename%")';
$string['settingsstatuspollingfrequencylabel'] = 'Status polling frequency (in seconds)';
$string['settingsstatuspollingfrequency'] = 'Status polling frequency (in seconds)';
$string['settingsstatuspollingfrequency_help'] = 'When migrating a Moodle video to OpenVeo, OpenVeo Migration Tool frequently asks OpenVeo about the video status until video has been completely treated. Default polling frequency is 10.';
$string['settingsstatuspollingfrequencyformaterror'] = 'Invalid frequency (e.g. 10)';
$string['settingsplanningpagevideosnumberlabel'] = 'Planning page: max videos per page';
$string['settingsplanningpagevideosnumber'] = 'Planning page: max videos per page';
$string['settingsplanningpagevideosnumber_help'] = 'The number of videos to display per page of results in the planning page (default is 10).';
$string['settingsplanningpagevideosnumberformaterror'] = 'Invalid number of videos (e.g. 10)';
$string['settingsuploadcurltimeoutlabel'] = 'Upload timeout (in seconds)';
$string['settingsuploadcurltimeout'] = 'Upload timeout (in seconds)';
$string['settingsuploadcurltimeout_help'] = 'The number of seconds before stopping the upload of a video to OpenVeo (default is 3600).';
$string['settingsuploadcurltimeoutformaterror'] = 'Invalid timeout (e.g. 500)';
$string['settingsfilefieldslabel'] = 'File fields';
$string['settingsfilefields'] = 'File fields';
$string['settingsfilefields_help'] = 'The list of fields of type "editor" and "filemanager" used to upload files. If a reference to an OpenVeo video is added from a field not defined in here, OpenVeo Migration Tool won\'t migrate it. Each line represents a field with three columns: the component holding the field (component), the file area (filearea) and the supported methods (supportedmethods). Columns are separated by pipes. More information available on <a href="https://github.com/veo-labs/openveo-moodle-migration-tool" target="_blank">plugin\'s page</a>. Not that the order of lines is important as it determines the priority of automatic migration. Videos corresponding to the first field (first line) will be migrated before videos corresponding to the second field (second line) and son on.';
$string['settingssubmitlabel'] = 'Save changes';

// Planning page.
$string['planningtitle'] = 'OpenVeo Migration Tool planning';

// Planning page: search form.
$string['planningsearchgroup'] = 'Search';
$string['planningsearchfrom'] = 'From';
$string['planningsearchto'] = 'To';
$string['planningsearchtypeslabel'] = 'Type';
$string['planningsearchtypesall'] = 'All';
$string['planningsearchstatuslabel'] = 'Status';
$string['planningsearchstatusall'] = 'All';
$string['planningsearchstatus0'] = 'Error';
$string['planningsearchstatus1'] = 'Registered';
$string['planningsearchstatus2'] = 'Migrating';
$string['planningsearchstatus3'] = 'Migrated';
$string['planningsearchstatus4'] = 'Unregistered';
$string['planningsearchstatus5'] = 'Blocked';
$string['planningsearchsubmitlabel'] = 'Search';

// Planning page: action form.
$string['planningactionssubmitlabel'] = 'Apply';
$string['planningactionslabel'] = 'With selected videos...';
$string['planningactionschooseaction'] = 'Choose...';
$string['planningactionsregisteraction'] = 'Register';
$string['planningactionsderegisteraction'] = 'Deregister';
$string['planningactionsremoveaction'] = 'Remove';

// Planning page: table of results.
$string['planningtablecaption'] = 'Search results ({$a})';
$string['planningtablefilename'] = 'File name';
$string['planningtablecontexts'] = 'Contexts';
$string['planningtablecontexts_help'] = 'The list of contexts where the video file appears. Several contexts means that video has aliases.';
$string['planningtabledate'] = 'Date';
$string['planningtabledate_help'] = 'The date when the original video (not aliases) was added to Moodle.';
$string['planningtabletype'] = 'Type';
$string['planningtablestatus'] = 'Status';
$string['planningtablestatus0'] = 'Error';
$string['planningtablestatus1'] = 'Registered';
$string['planningtablestatus2'] = 'Migrating';
$string['planningtablestatus3'] = 'Migrated';
$string['planningtablestatus4'] = 'Unregistered';
$string['planningtablestatus5'] = 'Blocked';
$string['planningtablestatus6'] = 'Not supported';

// Errors.
$string['errorlocalpluginnotconfigured'] = 'Local plugin "OpenVeo API" is not configured.';
$string['errornovideoplatform'] = 'No video platform configured in OpenVeo Publish.';
$string['errormigrationwrongconfiguration'] = 'Migration needs at least one type of videos and a destination platform.';
$string['errornorepositoryopenveo'] = 'No repository OpenVeo found.';
$string['errorgettingvideos'] = 'Searching for videos failed (see logs for more details).';
$string['errorpreparingvideos'] = 'Searching for videos failed (see logs for more details).';
$string['errorplanningvideos'] = 'Adding videos for migration failed (see logs for more details).';
$string['errorderegisteringvideos'] = 'Deregistering videos from migration failed (see logs for more details).';

// Events.
$string['eventgettingplatformsfailed'] = 'Getting video platforms failed';
$string['eventgettinggroupsfailed'] = 'Getting groups failed';
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
$string['eventrestoringoriginalfailed'] = 'Restoring original video failed';
$string['eventrestoringoriginalaliasesfailed'] = 'Restoring original video aliases failed';
$string['eventupdatingregisteredvideoidfailed'] = 'Updating registered video id failed';
$string['eventgettingvideosfailed'] = 'Fetching Moodle video files failed';
$string['eventgettingvideocontextfailed'] = 'Getting video context failed';
$string['eventplanningvideosfailed'] = 'Planning videos failed';
$string['eventderegisteringvideosfailed'] = 'Deregistering videos failed';

// Tasks.
$string['taskmigratename'] = 'Migrate Moodle videos to OpenVeo';

// OpenVeo.
$string['openveooriginallabel'] = 'Original';
$string['openveoaliaslabel'] = 'Alias';
