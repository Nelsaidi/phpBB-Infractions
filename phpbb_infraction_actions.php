<?php
// @since 0.3
class phpbb_infraction_actions
{
	
	private $actions = array(); // cache
	
	/*
	object dependancy crap,
	for simplicity an array, essentially,
	*/
	public function __construct(array $user_state)
	{
		
	}
	
	/**
	 * User state, an array containing the new infraction number, etc etc
	 * make sure the most "recent" (for highest infractions) of valid actions, unique.
	 * 
	 * returns array of actions
	 */
	public function get_actions()
	
	/**
	 * iterate this function to perform each action from above function
	 */
	public function perform_action($action)
}
