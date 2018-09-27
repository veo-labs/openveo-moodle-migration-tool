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
/*
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This module manages the planning page.
 *
 * The planning page displays a list of Moodle video files with the possibility to select them (using individual checkboxes or
 * global checkbox) and a form to apply an action of the selected videos. Checkboxes to select Moodle video files are not part of
 * the action form and then won't be sent with form submission. The planning manager updates the actions form depending on selected
 * files. The list of selected files are added to the "selectedfiles" hidden field of the actions form and the list of actions is
 * updated regarding selected files. If one of the selected files does not support an action, the action is disabled. All selected
 * files must support the action for it to be enabled.
 *
 * This module does not expose anything and must be loaded only once per page.
 *
 * @module tool_openveo_migration/planningmanager
 */
define(['jquery'], function($) {
    var allCheckboxJqueryElement;
    var videosCheckboxesJqueryElement;
    var actionJqueryElement;
    var submitJqueryElement;
    var selectedFilesJqueryElement;


    /**
     * Enables / disables submit button.
     *
     * Submit button is disabled if:
     * - Selected action is "Choose..."
     * - Selected action is disabled
     * - No video selected
     */
    function updateSubmitButton() {

        // Count selected videos.
        var selectedVideosNumber = 0;
        videosCheckboxesJqueryElement.each(function(index, videoCheckboxElement) {
            if ($(videoCheckboxElement).prop('checked')) {
                selectedVideosNumber++;
            }
        });

        var selectedAction = actionJqueryElement.val();
        var actionDisabled = actionJqueryElement.find('option[value=' + selectedAction + ']').prop('disabled') !== false;
        var chooseActionSelected = selectedAction === '0';

        submitJqueryElement.prop('disabled', actionDisabled || !selectedVideosNumber || chooseActionSelected);
    }

    /**
     * Updates the action form regarding selected videos.
     *
     * Available actions must be possible on all selected videos. If at least one of the selected video doesn't support an action,
     * the action is disabled. If there is no enabled actions left, then the submit button is disabled.
     * The list of selected videos are added to the "selectedfiles" hidden field.
     */
    function updateActionForm() {
        var selectedVideoIds = [];
        var actionsStatuses = {
            '1': true, // Register.
            '2': true, // Unregister.
            '3': true // Remove.
        };

        // Find selected videos and filter actions.
        videosCheckboxesJqueryElement.each(function(index, videoCheckboxElement) {
            var videoCheckboxJqueryElement = $(videoCheckboxElement);

            if (!videoCheckboxJqueryElement.prop('checked')) {
                return;
            }

            // Find video information.
            var videoJqueryElement = videoCheckboxJqueryElement.parents('tr');
            var videoId = videoJqueryElement.attr('data-id');
            var videoStatus = parseInt(videoJqueryElement.attr('data-status'));

            // Add video to selected videos.
            selectedVideoIds.push(videoId);

            switch (videoStatus) {

                // Error.
                // Disable "Unregister" actions.
                case 0:
                    actionsStatuses['2'] = false;
                    break;

                // Registered.
                // Disable "Register" actions.
                case 1:
                    actionsStatuses['1'] = false;
                    break;

                // Migrating.
                // Disable all actions.
                case 2:
                    actionsStatuses['1'] = actionsStatuses['2'] = actionsStatuses['3'] = false;
                    break;

                // Migrated.
                // Disable "Register" and "Unregister" actions.
                case 3:
                    actionsStatuses['1'] = actionsStatuses['2'] = false;
                    break;

                // Unregistered.
                // Disable "Unregister" and "Remove" actions.
                case 4:
                    actionsStatuses['2'] = actionsStatuses['3'] = false;
                    break;

                // Blocked.
                // Disable "Unregister" actions.
                case 5:
                    actionsStatuses['2'] = false;
                    break;

                // Not supported.
                // Disable all actions.
                case 6:
                    actionsStatuses['1'] = actionsStatuses['2'] = actionsStatuses['3'] = false;
                    break;

            }
        });

        // Enable / disable actions.
        for (var id in actionsStatuses) {
            actionJqueryElement.find('option[value=' + id + ']').attr('disabled', !actionsStatuses[id]);
        }

        // Enable / disable the submit button.
        updateSubmitButton();

        // Set selectedfiles field value.
        selectedFilesJqueryElement.val(selectedVideoIds.join(','));

    }

    /**
     * Checks / unchecks all videos and actualizes action form.
     */
    function toggleAll() {
        var shouldBeChecked = allCheckboxJqueryElement.prop('checked');

        videosCheckboxesJqueryElement.each(function(index, element) {
            $(element).prop('checked', shouldBeChecked);
        });

        updateActionForm();
    }

    /**
     * Initializes interactions between the video checkboxes and the action form.
     *
     * It retrieves HTML element references often used in this file and set event listeners on checkboxes to be able to update the
     * action form.
     */
    function init() {

        // Find selectedfiles field.
        selectedFilesJqueryElement = $('input[name="selectedfiles"]');

        // Find submit button.
        submitJqueryElement = $('input[name="actionssubmit"]');

        // Find actions combobox and set change event listener.
        actionJqueryElement = $('select[name="action"]');
        actionJqueryElement.change(updateSubmitButton);

        // Find checkboxes and set change event listeners.
        allCheckboxJqueryElement = $('.tool-openveo-migration-planning-page thead input');
        videosCheckboxesJqueryElement = $('.tool-openveo-migration-planning-page tbody input');
        allCheckboxJqueryElement.change(toggleAll);
        videosCheckboxesJqueryElement.change(updateActionForm);

        updateActionForm();

    }

    init();
    return {};
});
