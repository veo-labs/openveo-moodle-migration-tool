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
 * Defines the settings page for the plugin.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\output;

defined('MOODLE_INTERNAL') || die();

// Include OpenVeo REST PHP client autoloader.
require_once($CFG->dirroot . '/local/openveo_api/lib.php');

use Exception;
use renderable;
use templatable;
use renderer_base;
use stdClass;
use context_system;
use moodle_exception;
use core_form\filetypes_util;
use Openveo\Client\Client;
use Openveo\Exception\ClientException;
use local_openveo_api\event\connection_failed;
use tool_openveo_migration\event\getting_platforms_failed;

/**
 * Defines the settings page.
 *
 * The settings page holds a formular to configure how Moodle files should be migrated to the OpenVeo platform.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_page implements renderable, templatable {

    /**
     * The settings form.
     *
     * @var settings_form
     */
    protected $form;

    /**
     * The translated title of the page.
     *
     * @var string
     */
    protected $pagetitle;

    /**
     * The OpenVeo web service client.
     *
     * @var Openveo\Client\Client
     */
    protected $client;

    /**
     * Creates a settings_page holding migration settings.
     *
     * It also retrieve the list of configured video platforms from OpenVeo Publish.
     *
     * @param string $pagetitle The translated page title
     */
    public function __construct(string $pagetitle) {
        $this->pagetitle = $pagetitle;
        $url = get_config('local_openveo_api', 'webserviceurl');
        $clientid = get_config('local_openveo_api', 'webserviceclientid');
        $clientsecret = get_config('local_openveo_api', 'webserviceclientsecret');
        $certificatefilepath = get_config('local_openveo_api', 'webservicecertificatefilepath');
        $defaultvideotypestomigrate = get_config('tool_openveo_migration', 'videotypestomigrate');
        $defaultautomaticmigrationactivated = get_config('tool_openveo_migration', 'automaticmigrationactivated');
        $defaultdestinationplatform = get_config('tool_openveo_migration', 'destinationplatform');
        $defaultstatuspollingfrequency = get_config('tool_openveo_migration', 'statuspollingfrequency');
        $filetypesutil = new filetypes_util();

        // Create an OpenVeo web service client.
        try {
            $this->client = new Client($url, $clientid, $clientsecret, $certificatefilepath);
        } catch(ClientException $e) {
            throw new moodle_exception('errorlocalpluginnotconfigured', 'tool_openveo_migration');
        }

        // Build destinationplaform options.
        $platforms = $this->get_openveo_platforms();
        $destinationplatforms = array();

        if (sizeof($platforms) <= 0) {

            // No video platforms configured in OpenVeo Publish. Abort.

            throw new moodle_exception('errornovideoplatform', 'tool_openveo_migration');

        }

        foreach ($platforms as $platform) {
            $destinationplatforms[$platform] = get_string(
                    "settingsdestinationplatform$platform",
                    'tool_openveo_migration'
            );
        }

        // Create settings form.
        $defaults = array(
            'videotypestomigrate' => $defaultvideotypestomigrate,
            'automaticmigrationactivated' => $defaultautomaticmigrationactivated,
            'destinationplatform' => array(
                'value' => $defaultdestinationplatform,
                'options' => $destinationplatforms
            ),
            'statuspollingfrequency' => $defaultstatuspollingfrequency
        );
        $this->form = new settings_form(null, $defaults);

        // Handle form submission.
        $data = $this->form->get_data();
        if (isset($data)) {

            // Formular has been submitted and is validated.

            // Prepare data.
            $videotypestomigrate = $filetypesutil->normalize_file_types($data->videotypestomigrate);
            $videotypestomigrate = implode(' ', $videotypestomigrate);
            $statuspollingfrequency = !empty($data->statuspollingfrequency) ? $data->statuspollingfrequency : null;

            // Save configuration to database.
            set_config('videotypestomigrate', $videotypestomigrate, 'tool_openveo_migration');
            set_config('automaticmigrationactivated', $data->automaticmigrationactivated, 'tool_openveo_migration');
            set_config('destinationplatform', $data->destinationplatform, 'tool_openveo_migration');
            set_config('statuspollingfrequency', $statuspollingfrequency, 'tool_openveo_migration');

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
        $data = new stdClass();
        $data->title = $this->pagetitle;
        $data->form = $this->form->render();
        return $data;
    }

    /**
     * Interrogates OpenVeo Publish to get the list of configured video platforms.
     *
     * @return array The list of configured video platforms in OpenVeo Publish
     */
    private function get_openveo_platforms() : array {
        try {
            $response = $this->client->get('publish/platforms');

            if (isset($response->error)) {
                $event = getting_platforms_failed::create(array(
                    'context' => context_system::instance(),
                    'other' => array(
                        'code' => $response->error->code,
                        'module' => $response->error->module
                    )
                ));
                $event->trigger();
            }

            return isset($response->platforms) ? $response->platforms : array();
        } catch(Exception $e) {
            $event = connection_failed::create(array(
                'context' => context_system::instance(),
                'other' => array(
                    'message' => $e->getMessage()
                )
            ));
            $event->trigger();
            return array();
        }
    }

}
