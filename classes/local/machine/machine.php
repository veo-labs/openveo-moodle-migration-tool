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
 * Defines a finite states machine.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_openveo_migration\local\machine;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines a simple implementation of a finite states machine.
 *
 * The states machine is capable of changing current state from initial state to the last state, respecting states order. Changing
 * state is made using a transition. A transition is represented by a start state, an end state and a transition instance. When
 * executed, the transition can indicate that the transition succeeded or failed. If transition failed the machine will execute
 * states in reverse order to roll back modifications. If no transitions are specified for the reverse order, nothing happens.
 *
 * @package tool_openveo_migration
 * @copyright 2018 Veo-labs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class machine {

    /**
     * The list of transitions. Each transition is an associative array with "from" the start state, "to" the end state and
     * "transition" an instance of tool_openveo_migration\local\transition.
     *
     * @var array
     */
    protected $transitions;

    /**
     * The actual state of the machine corresponding to one of the states defined in states.
     *
     * @var int
     */
    protected $state;

    /**
     * The ordered list of states, states will be executed in this order or in reverse order in case of a rollback.
     *
     * @var array
     */
    protected $states;

    /**
     * Indicates that rollback is activated, meaning the states will go in the reverse order.
     *
     * @var bool
     */
    protected $rollback;

    /**
     * Gets the transition from actual state to previous state.
     *
     * @param transition The previous transition or null if no previous transition
     */
    protected function get_previous_transition() {
        $previousstate = $this->get_previous_state();

        if (!isset($previousstate)) {
            return null;
        }

        foreach ($this->transitions as $transition) {
            if ($transition['from'] === $this->state && $transition['to'] === $previousstate) {
                return $transition['transition'];
            }
        }

        return null;
    }

    /**
     * Gets the transition from actual state to next state.
     *
     * @param transition The next transition or null if no next transition
     */
    protected function get_next_transition() {
        $nextstate = $this->get_next_state();

        if (!isset($nextstate)) {
            return null;
        }

        foreach ($this->transitions as $transition) {
            if ($transition['from'] === $this->state && $transition['to'] === $nextstate) {
                return $transition['transition'];
            }
        }

        return null;
    }

    /**
     * Gets the state before the actual state.
     *
     * @param int The previous state or false if no previous state
     */
    protected function get_previous_state() {
        $stateindex = array_search($this->state, $this->states);

        if ($stateindex - 1 < 0) {
            return false;
        }
        return $this->states[$stateindex - 1];
    }

    /**
     * Gets the state after the actual state.
     *
     * @param int The next state or false if no next state
     */
    protected function get_next_state() {
        $stateindex = array_search($this->state, $this->states);

        if ($stateindex + 1 >= sizeof($this->states)) {
            return false;
        }
        return $this->states[$stateindex + 1];
    }

    /**
     * Executes transitions from state to state until end state.
     *
     * Executing transitions starts at the actual state and executes states from either normal order or reverse order if a transition
     * failed. If a transition failed, this method will always return false.
     *
     * @param bool true if executing machine succeeded, false if something went wrong
     */
    public function execute() : bool {
        $transition = ($this->rollback) ? $this->get_previous_transition() : $this->get_next_transition();

        if (empty($transition)) {

            // No more transitions.
            // Return false if we were rolling back.
            return ($this->rollback) ? false : true;

        }


        // Execute transition.
        $this->handle_transition_started($transition->get_name());
        $transitionsucceeded = $transition->execute();

        if ($transitionsucceeded) {

            // Transition succeeed.

            $this->handle_transition_ended($transition->get_name());
            $oldstate = $this->state;

            // Set new state.
            $this->state = ($this->rollback) ? $this->get_previous_state() : $this->get_next_state();
            $this->handle_state_changed($oldstate, $this->state);

        } else {

            // Transition failed.

            $this->handle_transition_failed($transition->get_name());

            if ($this->rollback) {

                // Roll back failed. Just stop the machine.
                return false;

            }

            // Start rolling back.
            $this->rollback = true;
            $this->handle_abort();

        }

        // Execute machine again.
        return $this->execute();
    }

    /**
     * Handles a changing of state.
     *
     * @param int $oldstate The old state
     * @param int $newstate The new state
     */
    protected abstract function handle_state_changed(int $oldstate, int $newstate);

    /**
     * Handles a transition start.
     *
     * @param string $name The transition name
     */
    protected abstract function handle_transition_started(string $name);

    /**
     * Handles a transition end.
     *
     * @param string $name The transition name
     */
    protected abstract function handle_transition_ended(string $name);

    /**
     * Handles a transition fail.
     *
     * @param string $name The transition name
     */
    protected abstract function handle_transition_failed(string $name);

    /**
     * Handles a machine abort.
     *
     * A transition failed, rolling back is starting.
     */
    protected abstract function handle_abort();

}
