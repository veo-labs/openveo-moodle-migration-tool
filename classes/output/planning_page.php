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
 * Defines the planning page to plan videos for migration.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\output;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;
use DateTime;
use DateInterval;
use renderable;
use templatable;
use renderer_base;
use context;
use context_system;
use paging_bar;
use moodle_url;
use stored_file;
use core_form\filetypes_util;
use tool_openveo_migration\output\planning_search_form;
use tool_openveo_migration\output\planning_actions_form;
use tool_openveo_migration\local\statuses;
use tool_openveo_migration\local\states;
use tool_openveo_migration\local\videos_provider;
use tool_openveo_migration\local\utils;
use tool_openveo_migration\event\getting_videos_failed;
use tool_openveo_migration\event\getting_video_context_failed;
use tool_openveo_migration\event\planning_videos_failed;
use tool_openveo_migration\event\deregistering_videos_failed;

/**
 * Defines the planning page.
 *
 * The planning page displays the list of Moodle video files with the possibility to add them to the list of videos to migrate. If
 * automatic migration is deactivated then this page can be helpful to register videos for migration. Moodle video files can have
 * several migration statuses and dependent actions:
 * - "not registered" The Moodle video file is not registered for migration then it can be registered
 * - "registered" The Moodle video file is already registered for migration then it can be unregistered
 * - "migrating" The Moodle video file is being migrated then no action can be performed until it is in a stable state
 * - "migrated" The Moodle video file has been migrated and does not exist anymore on Moodle then it is only possible to remove it
 * - "error" The Moodle video file has encountered an error during migration then it can be registered again for a new attempt
 * - "blocked" When migration fails the Moodle video file rolls back to its initial state. However roll back can also fail leaving
 *   the Moodle video file in an unstable state which must be diagnosed by a human. It can be registered again for a new attempt but
 *   a human intervention is probably required.
 *
 * The planning page offers a search engine to filter the list of Moodle video files. Search results are paginated.
 *
 * Note that not supported Moodle video files are listed when status is not specified or set to "Unregistered" in search form, but
 * it is not possible to search only those videos. To get only not supported Moodle video files we'd need to get all Moodle video
 * files, from "files" table, which do not correspond to file fields listed in configuration, leading to a huge database request.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class planning_page implements renderable, templatable {

    /**
     * The translated title of the page.
     *
     * @var string
     */
    protected $pagetitle;

    /**
     * The page URL.
     *
     * @var moodle_url
     */
    protected $pageurl;

    /**
     * The actions form.
     *
     * @var planning_actions_form
     */
    protected $actionsform;

    /**
     * The search form.
     *
     * @var planning_search_form
     */
    protected $searchform;

    /**
     * The videos provider.
     *
     * @var tool_openveo_migration\local\videos_provider
     */
    protected $videosprovider;

    /**
     * An error message if something went wrong during page computing.
     *
     * @var string
     */
    protected $error;

    /**
     * An associative array containing the paginated results with a key "page" holding the current page, a key "limit"
     * holding the limit number of fetched videos, a key "total" holding the total number of videos and a key "results" holding the
     * list of registered_video instances.
     *
     * @var array
     */
    protected $results;

    /**
     * Cache for Moodle video files contexts.
     *
     * @var array
     */
    protected $contexts;

    /**
     * The list of file fields supported by the plugin. An associative array describing the Moodle file fields with
     * component/filearea as key and an associative array as values containing information about the field (component, filearea and
     * supportedmethods).
     *
     * @var array
     */
    protected $supportedfilefields;

    /**
     * Creates a planning_page.
     *
     * @param string $pagetitle The translated page title
     * @param moodle_url $pageurl The page URL
     * @param tool_openveo_migration\local\videos_provider $videosprovider The videos provider
     */
    public function __construct(string $pagetitle, moodle_url $pageurl, videos_provider $videosprovider) {
        $filetypesutil = new filetypes_util();
        $this->pagetitle = $pagetitle;
        $this->pageurl = $pageurl;
        $this->videosprovider = $videosprovider;
        $this->supportedfilefields = utils::get_moodle_file_fields();
        $defaultlimit = get_config('tool_openveo_migration', 'planningpagevideosnumber');

        // Get the list of MIME types to migrate from configuration.
        // Only MIME types defined in configuration are listed in the planning page.
        $videotypestomigrate = get_config('tool_openveo_migration', 'videotypestomigrate');
        $videotypestomigrate = $filetypesutil->normalize_file_types($videotypestomigrate);
        $videomimetypestomigrate = file_get_typegroup('type', $videotypestomigrate);

        // Compute default search date interval.
        // By default date interval begins last month and ends next month.
        $todaydate = new DateTime();
        $todaydate->setTime(0, 0);
        $todaydate->sub(new DateInterval('P1M'));
        $defaultfromdate = $todaydate->getTimestamp();
        $todaydate->add(new DateInterval('P2M'));
        $defaulttodate = $todaydate->getTimestamp();

        // Get search parameters from pagination bar or actions form.
        // Note that it is "fromtimestamp" instead of "from" and "totimestamp" instead of "to". This is due to Moodle
        // date_time_selector which transmits its value to the server as an array and not as a timestamp (timestamp is computed
        // later by get_data).
        // However "from" and "to" as timestamps are required by the pagination bar and the actions form to preserve research when
        // navigating. As "from" and "to" values submitted by the search form are not timestamps, they can't be validated by
        // PARAM_INT, then we need two different parameters "fromtimestamp" and "totimestamp".
        $page = optional_param('page', 0, PARAM_INT);
        $from = optional_param('fromtimestamp', $defaultfromdate, PARAM_INT);
        $to = optional_param('totimestamp', $defaulttodate, PARAM_INT);
        $limit = optional_param('limit', !empty($defaultlimit) ? $defaultlimit : 10, PARAM_INT);
        $type = optional_param('type', 'all', PARAM_RAW_TRIMMED);
        $status = optional_param('status', 'all', PARAM_RAW_TRIMMED);

        // Prepare search form.
        $this->searchform = new planning_search_form(null, array(
            'from' => $from,
            'to' => $to,
            'availabletypes' => $videomimetypestomigrate,
            'type' => $type,
            'status' => $status
        ));

        // Override navigation bar parameters by search form values if search form is submitted.
        $searchformdata = $this->searchform->get_data();
        if (isset($searchformdata)) {

            // Search form has been submitted and is validated.
            $from = $searchformdata->from;
            $to = $searchformdata->to;
            $type = $searchformdata->type;
            $status = $searchformdata->status;

        }

        // Validate search parameters.
        $type = isset($type) ? $type : 'all';
        $types = $type === 'all' ? $videomimetypestomigrate : array($type);
        $status = isset($status) ? $status : 'all';
        $status = $status !== 'all' ? intval($status) : $status;

        // Add search parameters to page URL.
        // This is required to preserve research when navigating using the pagination bar.
        $this->pageurl->params(array(
            'fromtimestamp' => $from,
            'totimestamp' => $to,
            'type' => $type,
            'status' => $status
        ));

        // Prepare actions form.
        // Set search parameters to the form to preserve navigation.
        $this->actionsform = new planning_actions_form(new moodle_url($pageurl, array(
            'fromtimestamp' => $from,
            'totimestamp' => $to,
            'type' => $type,
            'status' => $status,
            'page' => 0
        )));

        $actionsformdata = $this->actionsform->get_data();
        if (isset($actionsformdata)) {

            // Actions form has been submitted.
            // Retrieve selected file ids and action.
            $selectedfilesids = !empty($actionsformdata->selectedfiles) ? explode(',', $actionsformdata->selectedfiles) : null;
            $action = intval($actionsformdata->action);

            if (!empty($selectedfilesids) && !empty($action)) {
                switch ($action) {

                    // Register.
                    // Videos with id not prefixed by "tom-" (UNREGISTERED) are added to registered videos. Other videos (already
                    // REGISTERED) are updated to set their status to PLANNED and their state to NOT_INITIALIZED.
                    case 1:
                        try {
                            $notregisteredfiles = array();

                            foreach ($selectedfilesids as $selectedfileid) {
                                if (preg_match('/^tom-(.*)$/', $selectedfileid, $chunks)) {

                                    // Video is REGISTERED.
                                    // Reset status and state to respectively PLANNED and NOT_INITIALIZED.
                                    $data = new stdClass();
                                    $data->id = $chunks[1];
                                    $data->status = statuses::PLANNED;
                                    $data->state = states::NOT_INITIALIZED;
                                    $this->videosprovider->update_registered_video($data);

                                } else {

                                    // Video is UNREGISTERED.
                                    $notregisteredfiles[] = $this->videosprovider->get_video_by_id($selectedfileid);

                                }
                            }

                            // Plan UNREGISTERED videos.
                            if (!empty($notregisteredfiles)) {
                                $this->videosprovider->plan_videos($notregisteredfiles);
                            }
                        } catch (Exception $e) {
                            $this->send_planning_videos_failed_event(
                                    $e->getMessage(),
                                    $selectedfilesids
                            );
                            $this->error = get_string('errorplanningvideos', 'tool_openveo_migration');
                        }
                        break;

                    // Deregister / Remove.
                    // Remove is an alias to deregister.
                    // Only REGISTERED videos (with id prefixed by "tom-") can be deregistered.
                    case 2:
                    case 3:
                        $registeredfilesids = array();

                        foreach ($selectedfilesids as $selectedfileid) {
                            if (preg_match('/^tom-(.*)$/', $selectedfileid, $chunks)) {
                                $registeredfilesids[] = $chunks[1];
                            }
                        }

                        try {
                            if (!empty($registeredfilesids)) {
                                $this->videosprovider->deregister_videos($registeredfilesids);
                            }
                        } catch (Exception $e) {
                            $this->send_deregistering_videos_failed_event(
                                    $e->getMessage(),
                                    $selectedfilesids
                            );
                            $this->error = get_string('errorderegisteringvideos', 'tool_openveo_migration');
                        }
                        break;
                }

            }
        }

        if ($status === 'all') {
            $status = null;
        }

        try {

            // Find the list of videos corresponding to search criterias.
            $this->results = $this->videosprovider->get_videos($from, $to, $types, $status, $limit, $page);

        } catch(Exception $e) {
            $this->send_getting_videos_failed_event($e->getMessage(), $from, $to, $types, $status, $limit, $page);
            $this->error = get_string('errorgettingvideos', 'tool_openveo_migration');
        }

    }

    /**
     * Exports page data to be exposed to the template.
     *
     * @see templatable
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export
     * @return stdClass Data to expose to the template
     */
    public function export_for_template(renderer_base $output) : stdClass {
        $contextshelpicon = new \help_icon('planningtablecontexts', 'tool_openveo_migration');
        $datehelpicon = new \help_icon('planningtabledate', 'tool_openveo_migration');

        $data = new stdClass();
        $data->title = $this->pagetitle;
        $data->actionsform = $this->actionsform->render();
        $data->searchform = $this->searchform->render();
        $data->contexthelp = $contextshelpicon->export_for_template($output);
        $data->datehelp = $datehelpicon->export_for_template($output);
        $data->videosdisplayed = true;
        $data->videos = array();

        if (empty($this->results) && !empty($data->error)) {
            $data->videosdisplayed = false;
        } else {
            $data->totalresults = $this->results['total'];

            foreach ($this->results['results'] as $video) {
                $videofile = $video->get_file();
                $registrationid = $video->get_id();
                $videoid = (isset($videofile) && !isset($registrationid)) ? $videofile->get_id() : "tom-$registrationid";
                $videofilename = isset($videofile) ? $videofile->get_filename() : $video->get_filename();
                $videotimecreated = isset($videofile) ? $videofile->get_timecreated() : $video->get_timecreated();
                $videomimetype = isset($videofile) ? $videofile->get_mimetype() : $video->get_mimetype();

                // Gets video contexts names and URLs.
                try {
                    $contexts = $this->get_contexts($video->get_contextids());
                } catch(Exception $e) {
                    $this->send_getting_video_context_failed_event($e->getMessage(), $videoid);
                    $data->error = get_string('errorpreparingvideos', 'tool_openveo_migration');
                    $data->videosdisplayed = false;
                    break;
                }

                // Set video status and corresponding CSS class.
                $status = $video->get_status();
                $state = $video->get_state();
                $statusclass = 'statusinfo';
                if (isset($videofile) && !$this->is_video_supported($videofile)) {
                    $status = statuses::NOT_SUPPORTED;
                    $statusclass = 'statuswarning';
                } else if ($status === statuses::ERROR) {
                    $statusclass = 'statusserious';

                    if ($state !== states::NOT_INITIALIZED) {
                        $status = statuses::BLOCKED;
                        $statusclass = 'statuscritical';
                    }
                } else if (!isset($status)) {
                    $status = statuses::UNREGISTERED;
                }

                if ($status === statuses::MIGRATED) {
                    $statusclass = 'statusok';
                }

                $data->videos[] = array(
                    'id' => $videoid,
                    'filename' => $videofilename,
                    'contexts' => $contexts,
                    'timecreated' => $videotimecreated,
                    'type' => $videomimetype,
                    'status' => $status,
                    'statuslabel' => get_string("planningtablestatus$status", 'tool_openveo_migration'),
                    'statusclass' => $statusclass
                );
            }

            // Pagination bar.
            $data->paginationbar = $output->render(new paging_bar(
                    $this->results['total'],
                    $this->results['page'],
                    $this->results['limit'],
                    $this->pageurl
            ));
        }

        if (!empty($this->error)) {
            $data->errormessage = $output->notification($this->error, 'error');
        }

        return $data;
    }

    /**
     * Checks if the video is supported by OpenVeo Mirgation Tool.
     *
     * If video couple component/filearea is present in supported fields listed in configuration, then video is supported.
     *
     * @param stored_file $video The Moodle video file
     * @param bool true if supported, false otherwise
     */
    protected function is_video_supported(stored_file $video) : bool {
        return isset($video) && isset($this->supportedfilefields[$video->get_component() . '/' . $video->get_filearea()]);
    }

    /**
     * Gets contexts names and URLs from ids.
     *
     * @param array $contextids The list of context ids
     * @return array A list of associative arrays with key "url" holding the context URL and key "name" holding the context name
     * @throws dml_exception A DML specific exception is thrown for any errors
     */
    protected function get_contexts($contextids) {
        $contexts = array();

        foreach ($contextids as $contextid) {

            // Try to get context name and URL from cache.
            if (isset($this->contexts[$contextid])) {
                $contexts[] = $this->contexts[$contextid];
                break;
            }

            $context = context::instance_by_id($contextid);

            if (empty($context)) {
                break;
            }

            $this->contexts[$contextid] = array(
                'name' => $context->get_context_name(true),
                'url' => $context->get_url()
            );
            $contexts[] = $this->contexts[$contextid];
        }

        return $contexts;
    }

    /**
     * Sends a "getting_videos_failed" event.
     *
     * @param string $message The error message
     * @param int $from The "from" query parameter when fetching videos
     * @param int $to The "to" query parameter when fetching videos
     * @param array $types The "types" query parameter when fetching videos
     * @param int $status The "status" query parameter when fetching videos
     * @param int $limit The "limit" query parameter when fetching videos
     * @param int $page The "page" query parameter when fetching videos
     */
    protected function send_getting_videos_failed_event(string $message, int $from = null, int $to = null, array $types = null,
                                                              int $status = null, int $limit = null, int $page = null) {
        $event = getting_videos_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message,
                'from' => $from,
                'to' => $to,
                'types' => $types,
                'status' => isset($status) ? $status : 'all',
                'limit' => $limit,
                'page' => $page
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "getting_video_context_failed" event.
     *
     * @param string $message The error message
     * @param int $id The video id
     */
    protected function send_getting_video_context_failed_event(string $message, int $id) {
        $event = getting_video_context_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message,
                'id' => $id
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "planning_videos_failed" event.
     *
     * @param string $message The error message
     * @param array $ids The list of video ids
     */
    protected function send_planning_videos_failed_event(string $message, array $ids) {
        $event = planning_videos_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message,
                'ids' => $ids
            )
        ));
        $event->trigger();
    }

    /**
     * Sends a "deregistering_videos_failed" event.
     *
     * @param string $message The error message
     * @param array $ids The list of video ids
     */
    protected function send_deregistering_videos_failed_event(string $message, array $ids) {
        $event = deregistering_videos_failed::create(array(
            'context' => context_system::instance(),
            'other' => array(
                'message' => $message,
                'ids' => $ids
            )
        ));
        $event->trigger();
    }

}
