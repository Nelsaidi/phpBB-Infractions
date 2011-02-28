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
	 * We need to centralise to things, a main issue function, and a wrapper for it which deals with permissions
	 * This will help because we will have multiple places infractions can be issued.
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
		
		if($infraction['type'] !== (0 OR 1))
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
		
		// === uSER
		// Option of username or user ID
		
		if(!isset($infraction['user_id'])
		{
			// Find the user id
			// Make username "clean", compare to user DB
			
			// **TODO**
			
			// ....
			$row = $db->sql_fetchrow($result);
			$username = $row['username']; // Take it out of the DB
		}
		else
		{
			// User ID supplied, find the username
			// Also counts as the check of both the infraction and username are added
			$sql = 'SELECT username FROM ' . PHPBB_USERS . ' WHERE user_id = ' . $infraction['user_id'];
			$result = $db->sql_query($sql);
			$username = $db->sql_fetchfield('username', $result);
			
			if($username === false)
			{
				throw new Exception('invalid user');
			}
			
			if(isset($infraction['username']) && $username != $infraction['username'])
			{
				throw new Exception('Mismatch of username');
			}
		}
		
		// Dealth with expiry, duration, username, userid
		
		// === PERSON ADDING BAN
		// Hmmm, we need to validate the person adding the ban right? We assume its been validated and is allowed, because most will use the $user?
		// But this is the underlying function, so all we check is the person exists?
		
		
		// === Reason
		// We assume its already clean (request var and what not, we will escape, however
		
		// Post
		if(isset($infraction['post_id']))
		{
			if(!is_numeric($infraction['post_id']))
			{
				throw new Exception('post id not int');
			}
			
			// Can potentially use this to quote the message, maybe a setting for it? Issue is mods editting the post first and then warning - maybe post title?
			// ATM just validation post exists and is by user.
			$sql = 'SELECT text, title, poster_id FROM ' . PHPBB_POSTS . ' WHERE post_id = ' . $infraction['post_id'];
			$
			
		
		// === Adding to DB
		
		// Update users table
		$sql = "UPDATE ..";
		
		
		// === Informing the user, send them a PM
		
		// Add to admin log AND MOD LOG
		/*
		// We add this to the mod log too for moderators to see that a specific user got warned.
		$sql = 'SELECT forum_id, topic_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id = ' . $post_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_USER_WARNING', $user_row['username']);
		*/
	}
	
	/**
	 * Gets the user state of the user, infractions wise, more like a short programatic version of all logs/recent, etc etc.
	 */
	 public function user_state($user_id)
	{
		
	}

}
