<?php

class phpbb_infraction_actions
{
	/**
	 * User state, an array containing the new infraction number, etc etc
	 * make sure the most "recent" (for highest infractions) of valid actions, unique.
	 * 
	 * returns array of actions
	 */
	public function get_actions(array $user_state)
	
	/**
	 * iterate this function to perform each action from above function
	 */
	public function perform_action($action, $user_state)
}
