<?php

/*
THE FRIKIN ISSUE IS THAT THIS IMPLIES THAT WE WILL ALWAYS DEAL WITH INFRACTIONS IN THIS WAY
OBVIOUSLY A MIS CONCEPTION
HOW TO MAKE IT SO IT PLAYS NICE WITH AMMENDING IT, PREFIL IT FROM DB, MAKE IT PLAY NICE WITH EXPIRE TIME AND DURATION??
ISSUES ARE WHEN AMMENDING WE HAVE TO GO AND REVERSE ACTIONS, REUPDATE DB, ETC ETC.
GRRRRRR
*/

/**
 * A (simple?) Infractions API
 * The standard method to create infractions
 * This is passed to the phpbb_infractions::save() function as an object
 *
 * Will validate for numbers only
 * N.B -> This is independ to actions, actions is an extension of this, 
 * It modifies the db to insert ban or new group info.
 */
class phpbb_infraction extends phpbb_infractions
{
	protected $data = array(); // Only directly accessible by phpbb_infractions	
	
	public function __construct();
	{
	}
	
	public function __set($variable, $value)
	{
		switch($variable)
		{
			case 'user_id':
			case 'post_id':
			case 'issuer_id':
			case 'points':
			case 'issue_time':
			case 'duration':
			case 'type':
				if(!is_numeric($value))
				{
					throw new Exception('not int');
				}
				
				$this->data[$variable] = $value;
			break;
			
			case 'reason':
				$this->data[$variable] = $value;
			break;
			
			default:
		}
	}
	
	public function __get($variable)
	{
		return $this->data[$variable];
	}
}
