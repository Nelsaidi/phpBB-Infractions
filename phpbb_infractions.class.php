<?php
 
/**
 * The main class for phpBB Infractions
 */
class phpbb_infractions
{
	// Infraction types
	const WARNING = 0;
	const INFRACTION = 1;
	// const NOTE = 2;
	
	// Status levels for an infraction
	const ACTIVE = 0;
	const EXPIRED = 1;
	const REMOVED = 2; 
	
	// Tables, hardcoded for now
	const TABLE_INFRACTIONS = 'phpbb_infractions';
	const TABLE_TEMPLATES = 'phpbb_infractions_templates';
	// Other class will deal with actions
	
	public function __construct()
	{
		
	}
	
	
	/**
	 * Build an infraction/warning based on a template
	 * which the user create\zs in the ACP
	 * *we can* have additional options, such as template idea, but this is the absolute minimum needed for a infraction
	 * 
	 * @param $user_id 
	 * @param $template_id
	 * @return array
	 * @since 0.1
	 */
	public function build_from_template($user_id, $template_id)
	{
		global $db, $user;
		
		if(!is_numeric($template_id) OR !is_numeric($user_id))
		{
			return false;
		}
		
		$infraction = array();
		
		$infraction['issuer_id'] = $user->data['user_id']; // similar to how post system does it, saves permision checking etc.
		$infraction['user_id'] = $user_id; // Need to check this exists?
		
		$sql = 'SELECT text, type, points, duration FROM ' . PHPBB_INFRACTIONS::TABLE_TEMPLATES . " WHERE template_id = ' . $template_id";
		$result = $db->sql_query($sql);
		$row = $db->fetchrow($result);		
		$db->sql_freeresult($result);
		
		$infraction = array_merge($infraction, $row); // I'm lazy
				
		return $infraction;
		
	}
	
	/**
	 * 
	 */
	public function issue(array $infraction)
	{
		// All permision checks done outside of function, so no $user here, unlike build template which is a make easy function.
			
		global $db, $config;
				
		// Make sure the main stuff exists - Careful of what ! means, = zero and not only blank.
		if(!isset($infraction['issuer_id'], $infraction['user_id'], $infraction['text'], $infraction['type']))
		{
			throw new Exception('$infraction insufficient data')
		}
		
		if($infraction['type'] !== (0 OR 1 OR 2))
		{
			throw new Exception('Invalid type');
		}
		
		// Duration zero implies infinite duration, so,  if it doesnt exist then take the default duration time (default selected on entry screen)
		if(!isset($infraction['duration']))
		{
			$infraction['duration'] = (int) $config['infraction_default_time'];
		}
		
		// ==== EXPIRE
		if(!is_numeric($infraction['duration']))
		{
			throw new Exception();
		}
		
		$data['duration'] = (int) $infraction['duration'];
		$data['expire'] = time() + ($data['duration'] * 60); // Expire is in minutes
		
		if(!is_numeric($infraction['user_id']))
		{
			throw new Exception('user id not number');
		}
		
		$sql = 'SELECT username FROM ' . PHPBB_USERS . ' WHERE user_id = ' . $infraction['user_id'];
		$db->sql_query($sql);
		
	
		
	}
	
	/**
	 * Gets the user state of the user, infractions wise, more like a short programatic version of all logs/recent, etc etc.
	 */
	 public function user_state($user_id)
	{
		
	}

}
