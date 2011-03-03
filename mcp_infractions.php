<?php

/**
 * Main Infractions Module
 *
 * @todo Put it into classes, I have my reasons for why I done it like this, it'll be nicer ... eventually.
 * @author Nelsaidi
 */

// For sake of simplicity (for development)
define('INFRACTIONS_TABLE', 'phpbb_infractions');
define('INFRACTIION_TEMPLATES_TABLE', 'phpbb_infraction_templates');

define('INFRACTIONS_WARNING', 0);
define('INFRACTIONS_INFRACTION', 1);
// const NOTE = 2;

define('INFRACTIONS_ACTIVE', 0);
define('INFRACTIONS_EXPIRED', 1);
define('INFRACTIONS_REMOVED', 2);

 
class mcp_infractions
{
	public $p_master;
	public  $u_action;

	public function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;

		$action = request_var('action', array('' => ''));

		if (is_array($action))
		{
			list($action, ) = each($action);
		}

		add_form_key('mcp_infractions');
		
		switch($mode)
		{
			case 'issue_infraction':
				$this->issue_infraction();
				$this->tpl_name = 'issue_infraction';	
				$this->page_title = 'Issue Infraction';
			break;
				
		}
	}
	
	/**
	 * Issueing an infraction
	 * Posts and normal all in one, for simplicity
	 */
	public function issue_infraction()
	{
		global $auth, $user, $db, $config, $template
		
		// Check if the user can issue an infraction
		if(!$auth->acl_get('m_infractions_issue'))
		{
			trigger_error('NOT_AUTHORISED');
		}
		
		$user_id = request_var('user_id', 0);
		$post_id = request_var('post_id', 0);
		$type = request_var('type', 0);
		
		// Get post data
		if($post_id != 0)
		{
			// Check if the user has already been warned for this post
			// TODO
			
			$sql = "SELECT * FROM " . POSTS_TABLE . " WHERE post_id = $post_id";
			
			$result = $db->sql_query($sql); // Do we cache it for ~60 seconds, saves querying again but maybe another mod updates the post?
			
			$post_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if(sizeof($post_row) == 0)
			{
				trigger_error('post does not exist');
			}
			
			if($user_id == 0)
			{
				$user_id = $post_row['poster_id'];
			}
			
		}
		
		// Get user data
		$sql = 'SELECT * FROM ' . USERS_TABLE . " WHERE user_id = $user_id";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if(!isset$user_row['user_id')
		{
			trigger_error('user does not exist');
		}
		
		if($user->data['user_id'] == $user_row['user_id'])
		{
			trigger_error('deehuuude, you cant warn yourself');
		}			

		// Is someone being warned? If not, then just show them the view
		if(!isset($_POST['submit']))
		{
			$template->assign_vars(array(
				'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=infractions&amp;mode=&amp;u=' . $user_id),
				'INFRACTION_USER_ID'	=> $user_row['user_id'],
				'INFRACTION_TYPE'		=> $type,
			));
			
			// Get user info like avatar, infractions SHAMELESSLY STOLEN :D
			// Generate the appropriate user information for the user we are looking at
			if (!function_exists('get_user_avatar'))
			{
				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
			}

			$rank_title = $rank_img = '';
			$avatar_img = get_user_avatar($user_row['user_avatar'], $user_row['user_avatar_type'], $user_row['user_avatar_width'], $user_row['user_avatar_height']);

			// OK, they didn't submit a warning so lets build the page for them to do so
			$template->assign_vars(array(
				// 'U_POST_ACTION'	=> $this->u_action,

				'RANK_TITLE'		=> $rank_title,
				'JOINED'			=> $user->format_date($user_row['user_regdate']),
				'POSTS'			=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
				'WARNINGS'		=> ($user_row['user_warnings']) ? $user_row['user_warnings'] : 0,

				'USERNAME_FULL'	=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
				'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
				'USERNAME'		=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
				'U_PROFILE'		=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

				'AVATAR_IMG'		=> $avatar_img,
				'RANK_IMG'		=> $rank_img,
			));

			
			// Being warned for a post
			if(isset($post_data))
			{
				// Get the mssage and parse it, for display
				$message = censor_text($post_row['post_text']);
				
				if ($user_row['bbcode_bitfield'])
				{
					if(class_exists(bbcode))
					{
						include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
					}
					
					$bbcode = new bbcode($post_row['bbcode_bitfield']);
					$bbcode->bbcode_second_pass($message, $post_row['bbcode_uid'], $post_row['bbcode_bitfield']);
				}
				
				$message = bbcode_nl2br($message);
				$message = smiley_text($message);
		
				$template->assign_vars(array(
					'INFRACTION_POST'		=> true,
					'POST_TEXT'			=> $message,
				));
			}
			
			return true;
		}
		
		// Ok, we are creating an infraction
		
		// TODO form keys for security!!
		
		// Populate with already validated stuff
		$infraction = array(
			'user_id'		=> $user_id,
			'issuer_id'	=> $user['user_id'],
			'issue_time'	=> time()
		);
		
		// Assign a post ID if it exists
		if(isset($post_data))
		{
			$infraction['post_id'] = $post_data['post_id'];
		}
		
		// Load additional information
		$infraction_template = request_var('infraction_template', 0);
		
		if($infraction_template != 0)
		{
			// User has selected a template and not custom, load it
			$sql = 'SELECT * FROM ' . INFRACTIION_TEMPLATES_TABLE . " WHERE template_id = $infraction_template";
			$result = $db->sql_query($sql);
			$template_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if(sizeof($template_row) == 0)
			{
				trigger_error('invalid template selected');
			}
			
			// RHS already validated pre db insertion.
			$infraction = array_merge($infraction, array(
				'type'		=> $template_row['type'],
				'points'		=> $template_row['points'],
				'duration'	=> $template_row['duration'],
				'reason'		=> $template_row['reason']
			));
		
		}
		else
		{
			// User chose custom, validate correctness
		
			$infraction_type = request_var('type', 0);
			if($infraction_type > INFRACTIONS_INFRACTION)
			{
				trigger_error('invalid type');
			}
			
			$infraction_points = request_var('points', 0);
			// Negative infraction or zero points?
			if($infraction_points < 1)
			{
				if($infraction_type == INFRACTIONS_INFRACTION)
				{
					trigger_error('cannot issue infraction with zero points, try a warning');
				}
				else
				{
					trigger_error('dude, negative points??');
				}
			}

			// Make sure points are in range with maximum
			/*
			if($infraction_points > $config['infraction_max_points_issue'])
			{
				trigger_error('points too large');
			}
			*/
			
			$infraction_duration = request_var('duration', 0);
			
			$infraction_reason = request_var('reason', '');
			
			// Empty reason? Maybe config var?
			/*
			if(strlen($infraction_reason) == 0 && $config['infraction_empty_reason'] == false)
			{
				trigger_error('reason cannot be empty');
			}
			*/
			
			// Validated, merge
			$infraction = array_merge($infraction, array(
				'type'		=> $infraction_type,
				'points'		=> $infraction_points,
				'duration'	=> $infraction_duration,
				'reason'		=> $infraction_reason,
			));
		}
		
		// Custom syntax? Ie, One month = time() + one month, etc etc? or generic 30 days
		// NOTE
		// Calculate expire from duration, 0 = non expiring
		$infraction['expire_time'] = ($infraction['duration'] == 0) ? 0 : time() + $infraction['duration'] * 60;
		
		if($infraction['duration'] == 0)
		{
			$infraction['expire_time'] = 0;
		}
		else
		{
			$infraction['expire_time'] = $infraction['duration'] * 60 + time(); // Duration in minutes
		}
		
		// Add the thing
		// TODO : Move this into the class ma tings
		$sql = 'INSERT INTO ' . TABLE_INFRACTIONS . ' WHERE ' . $db->sql_build_array('SELECT', $infraction);
		$db->sql_query($sql);

		// Update users table
		$sql = 'UPDATE ' . TABLE_USERS . " SET infraction_points = infraction_points + {$infraction['points']} WHERE user_id = {$user_row['user_id']}";
		$sql->sql_query($sql);
		
		// Perform Actions
		
		// Notify the user, PM them, skip auth checks tbh, surely theyre a mod they can :/ ??
		// TODO
		
		// TODO RUN HOOK: infraction_issued !!
	}
	
	/**
	 * access via GET uri, maybe a are you sure you wanna do this too?
	 * Major issue, if the guy is banned, it needs to be unbanned, but what if he already had a ban before the autoaction ban?
	 * This gets complicated, we have to revert a ban thats made, and then check if they're eligible for a ban, if yes and its the same continue it.
	 * And if they are unbanned we should hook it to update this status?
	 */
	public function delete_infraction()
	{
		$infraction_id = request_var('infraction_id', 0);
		
		if($infraction_id == 0)
		{
			trigger_error('bad id');
		}
		
		// Get a copy of the infraction to allow for full reversal
		$sql = 'SELECT * FROM ' . TABLE_INFRACTIONS . " WHERE infraction_id = $infraction_id";
		$result = $db->sql_query($sql);
		$infraction = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
			
		// Generate the SQL statement for what we are doing to it (hide[or void] or delete)
		$delete_mode = request_var('delete_mode', '');
		if($delete_mode == 'void')
		{
			$removal_sql = 'UPDATE ' . TABLE_INFRACTIONS . ' SET status = ' . INFRACTION_REMOVED . " WHERE infraction_id = $infraction_id";
		}
		else if($delete_mode == 'remove')
		{
			// Out of DB
			$removal_sql = 'DELETE FROM ' . TABLE_INFRACTIONS . " WHERE infraction_id = $infraction_id";
		}
		else
		{
			trigger_error('unknown mode');
		}
		
		$db->sql_query($removal_sql);
		unset($removal_sql);
		
		// Infraction now doesnt exist, lets reverse its actions
		// Remove points from users table
		$user_id = (int) $infraction['user_id']; // Lets not trust the DB too
		$points = (int) $infraction['points']; 
		
		if($user_id == 0)
		{
			trigger_error('bad db, very bad');
		}
		
		if($points > 0)
		{
			$sql = 'UPDATE ' . TABLE_USERS . " SET points = points - {$points} WHERE user_id = {$user_id}";
			$db->sql_query($sql);
		}
		
		// Reverse any actions, or continue if they still apply
		
		// TODO RUN HOOK: infraction_deleted
	}
	
}

// EOF