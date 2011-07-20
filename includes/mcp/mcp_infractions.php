<?php

/**
 * Main Infractions Module
 *
 * @todo Put it into classes, I have my reasons for why I done it like this, it'll be nicer ... eventually.
 * @author Nelsaidi
 */

// Move into constants.php
// TODO

// For sake of simplicity (for development)
// define('INFRACTIONS_TABLE', 'infractions');
define('INFRACTION_TEMPLATES_TABLE', 'phpbb_infraction_templates');

define('INFRACTIONS_WARNING', 0);
define('INFRACTIONS_INFRACTION', 1);
// const NOTE = 2;

define('INFRACTIONS_ACTIVE', 0);
define('INFRACTIONS_EXPIRED', 1);
define('INFRACTIONS_REMOVED', 2);

 
class mcp_infractions
{
	public $p_master;
	public $u_action;

	public function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		global $infractions;

		// Block Users
		if($user->data['user_id'] != 2)
		{
			trigger_error('Sorry dudes, still in development');
		}
		
		$user->add_lang('infractions');
		// Load our infractions class
		if(!class_exists('infractions'))
		{
			require($phpbb_root_path . 'includes/infractions.class.' . $phpEx);
			
		}
		$infractions = new infractions; 
		
		$action = request_var('action', '');

		add_form_key('mcp_infractions');
		
		$template->assign_vars(array(
			'S_IN_INFRACTIONS'		=> 1,
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
			
		));
		
		if($action == 'delete')
		{
			$this->delete_infraction();
		}
		
		switch($mode)
		{
			case 'issue':
				$this->issue_infraction();
				$this->tpl_name = 'infractions_issue';	
				$this->page_title = 'Issue Infraction';
			break;

			case 'view':
			default: 
			
				if($action == 'delete')
				{
					$this->delete_infraction();
					// Sort URL, we have offset, limit, user_id to sort out?
					// TODO
					// We need to redirect - remove the delete from the top? - Use AJAX? - Like fancy, click delete, confirm box, then it fades away and a yellow box fades in, epic
					// If we do AJAX we can have an AJAX variable??
					
				}
			
				$user_id = request_var('user_id', 0);
				$username = request_var('username', '');
				
				if($username != '')
				{
					$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE username_clean = "' . $db->sql_escape(utf8_clean_string($username)) . '"';
					$result = $db->sql_query($sql);
					$user_row = $db->sql_fetchrow($result);	
					$db->sql_freeresult($result);
					
					if(!isset($user_row['user_id']))
					{
						trigger_error('user does not exist');
					}
					
					redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=infractions&mode=view&user_id={$user_row['user_id']}"));
					exit;
				}		
				
				if($user_id > 0)
				{
					$this->view_infractions_user();
					$this->tpl_name = 'infractions_user';
					$this->page_title = 'Infractions user: '; // append username to this
				}
				else
				{
					$this->view_infractions();
					$this->tpl_name = 'infractions_index';
					$this->page_title = 'List Infractions';
				}
			
			break;
			
		}
	}
	
	/**
	 * Issueing an infraction
	 * Posts and normal all in one, for simplicity
	 */
	public function issue_infraction()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		global $infractions;
		
		// Check if the user can issue an infraction
		/*
		if(!$auth->acl_get('m_infractions_issue'))
		{
			trigger_error('NOT_AUTHORISED');
		}
		*/
		
		$username = request_var('username', '');
		
		$user_id = request_var('user_id', 0);
		$post_id = request_var('post_id', 0);
		$type = request_var('type', 0);
		
		if($user_id == 0 && $post_id == 0 && $username == '')
		{
			$template->assign_var('S_INFRACTIONS_NO_USER' , 1);			
			return;
		}
		
		// We want no usernames in URL
		if($username != '')
		{
			$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE username_clean = "' . $db->sql_escape(utf8_clean_string($username)) . '"';
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);	
			$db->sql_freeresult($result);
			
			if(!isset($user_row['user_id']))
			{
				trigger_error('user does not exist');
			}
			
			redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=infractions&mode=issue&user_id={$user_row['user_id']}"));
			exit;
		}
		
		// Get post data
		if($post_id != 0)
		{			
			$post_row = $this->get_post_for_infraction($post_id);
			if(!is_array($post_row))
			{
				trigger_error($post_row);
			}
			
			$user_id = $post_row['poster_id'];
		}
	
		

		$sql = 'SELECT * FROM ' . USERS_TABLE . " WHERE user_id = $user_id";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);	
		$db->sql_freeresult($result);
		
		if(!isset($user_row['user_id']))
		{
			trigger_error('user does not exist');
		}
		/*
		if($user->data['user_id'] == $user_row['user_id'])
		{
			trigger_error('deehuuude, you cant warn yourself');
		}	
		*/

		// 21-6 : This is a seperate method tbnh - also ,this code - reusable. so put it in a function
		// Is someone being warned? If not, then just show them the view
		if(!isset($_POST['issue_infraction']))
		{
			$template->assign_vars(array(
				'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=infractions&amp;mode=issue&amp;user_id=' . $user_id),
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
				'POSTS'			=> $user_row['user_posts'],
				'INFRACTIONS'		=> $user_row['infraction_points'] ,

				'USERNAME'		=> $user_row['username'],
				'USER_PROFILE'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

				'AVATAR_IMG'		=> $avatar_img,
				'RANK_IMG'		=> $rank_img,
			));

			
			// Being warned for a post
			if(isset($post_row))
			{
				// Get the mssage and parse it, for display
				$message = censor_text($post_row['post_text']);
				
				if ($post_row['bbcode_bitfield'])
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
			
			// Get Infraction Templates
			$sql = 'SELECT * FROM ' . INFRACTION_TEMPLATES_TABLE;
			$result = $db->sql_query($sql);
			
			while($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('infraction_templates', array(
					'NAME'		=> $row['name'],
					'TEMPLATE_ID'	=> $row['template_id'],
				));
			}
			$db->sql_freeresult($result);
			
			return true;
		}
		
		// Ok, we are creating an infraction
		
		// TODO form keys for security!!
		
		// Populate with already validated stuff
		$infraction = array(
			'user_id'		=> $user_id,
			'issuer_id'	=> $user->data['user_id'],
			'issue_time'	=> time(), 
		);
		
		// Assign a post ID if it exists
		if(isset($post_row))
		{
			$infraction['post_id'] = $post_row['post_id'];
			$infraction['forum_id'] = $post_row['forum_id'];
		}
		
		// Load additional information
		$infraction_template = request_var('infraction_template', 0);
		
		if($infraction_template != 0)
		{
			// COMING SOON
			
			// User has selected a template and not custom, load it
			$sql = 'SELECT * FROM ' . INFRACTION_TEMPLATES_TABLE . " WHERE template_id = $infraction_template";
			$result = $db->sql_query($sql);
			$template_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if(sizeof($template_row) == 0)
			{
				trigger_error('invalid template selected');
			}
			
			// RHS already validated pre db insertion.
			$infraction = array_merge($infraction, array(
				'type'				=> 0, // TODO
				'infraction_points'		=> $template_row['infraction_points'],
				'duration'			=> $template_row['duration'],
				'reason'				=> $template_row['reason']
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
			
			// Err, in times like these we should return to the form!!
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
			if($infraction_duration == -1)
			{
				$infraction_duration = request_var('duration_custom', 0);
			}
			
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
				'type'				=> $infraction_type,
				'infraction_points'		=> $infraction_points,
				'duration'			=> $infraction_duration,
				'reason'				=> $infraction_reason,
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
			$infraction['expire_time'] = $infraction['duration'] * 60 + time(); // Duration is in minutes
		}
		
		// Add the thing
		// TODO : Move this into the class ma tings
		$sql = 'INSERT INTO ' . INFRACTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $infraction);
		$db->sql_query($sql);

		// Update users table
		if($infraction['infraction_points'] > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET infraction_points = infraction_points + {$infraction['infraction_points']} WHERE user_id = {$user_row['user_id']}";
			$db->sql_query($sql);
		}
		// Perform Actions
		
		// Notify the user, PM them, skip auth checks tbh, surely theyre a mod they can :/ ??
		
		include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		include($phpbb_root_path . 'language/' . basename($user_row['user_lang']) . "/infractions.$phpEx");

		$message_parser = new parse_message();

		$message_parser->message = sprintf($lang['INFRACTION_PM_BODY'], $infraction['reason'], $infraction['infraction_points']);
		$message_parser->parse(true, true, true, false, false, true, true);

		$pm_data = array(
			'from_user_id'			=> $user->data['user_id'],
			'from_user_ip'			=> $user->ip,
			'from_username'		=> $user->data['username'],
			'enable_sig'			=> false,
			'enable_bbcode'		=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> false,
			'icon_id'				=> 0,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'address_list'			=> array('u' => array($user_row['user_id'] => 'to')),
		);

		// TODO - This needs to change with warnings/infractions??
		submit_pm('post', $lang['INFRACTION_PM_SUBJECT'], $pm_data, false);
		add_log('mod', 0, 0, 'Infraction issued');	
		
		// TODO RUN HOOK: infraction_issued !!
		
		// Redirect
		// A possible message that that the user was banned, etc - Flash cookies? (so, display a message on the following page with the sucess?)
		$redirect = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=infractions");
		
		// Instant - its better that way
		redirect($redirect);
		exit;
	}
	
	/**
	 * access via GET uri, maybe a are you sure you wanna do this too?
	 * Major issue, if the guy is banned, it needs to be unbanned, but what if he already had a ban before the autoaction ban?
	 * // TODO IMPORTANT how does the bans system deal with bans?? - like, multiple bans, is it possible to have 2?
	 * This gets complicated, we have to revert a ban thats made, and then check if they're eligible for a ban, if yes and its the same continue it.
	 * And if they are unbanned we should hook it to update this status?
	 */
	public function delete_infraction($infraction_id = false)
	{
		//TODO 
		// Let infraction id be an array, so, use request var array as default
		// check size, if alot then go through this function
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		if($infraction_id === false)
		{
			$infraction_id = request_var('infraction_id', 0);
		}
		
		if($infraction_id == 0 || !is_numeric($infraction_id))
		{
			trigger_error('bad id');
		}
		
		// Get a copy of the infraction to allow for full reversal
		$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE . " WHERE infraction_id = $infraction_id";
		$result = $db->sql_query($sql);
		$infraction = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if(sizeof($infraction) == 0)
		{
			trigger_error('Infraction does not exist');
		}
		// Generate the SQL statement for what we are doing to it (hide[or void] or delete)
		// 21-6: hide/void isnt the right word
		
		// TODO
		// Implement soft delete
		
		$delete_mode = request_var('delete_mode', 'remove');
		
		if($delete_mode == 'void')
		{
			$removal_sql = 'UPDATE ' . INFRACTIONS_TABLE . ' SET status = ' . INFRACTION_REMOVED . " WHERE infraction_id = $infraction_id";
		}
		else if($delete_mode == 'remove')
		{
			// Out of DB
			// And check the permisions here eenit
			
			/*
			if(!$auth->acl_get('m_infractions_delete'))
			{
				trigger_error('NOT_AUTHORISED');
			}
			*/
			
			// TODO
			$removal_sql = 'DELETE FROM ' . INFRACTIONS_TABLE . " WHERE infraction_id = $infraction_id";
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
		$infraction_points = (int) $infraction['infraction_points']; 
		
		if($user_id == 0)
		{
			trigger_error('bad db, very very bad'); // though impossible
		}
		
		if($infraction_points > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET infraction_points = infraction_points - {$infraction_points} WHERE user_id = {$user_id}";
			$db->sql_query($sql);
		}
		
		
		
		// Reverse any actions, or continue if they still apply
		
		// TODO RUN HOOK: infraction_deleted
		add_log('mod', 0, 0, 'Infraction deleted');		
		redirect(append_sid($this->u_action));
	}
	
	/**
	View infractions for a user, could be extended to view for a certain forum or topic
	Note - plural 
	Permisions are fine, post has an infraction any mod can view it?
	*/
	public function view_infractions()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
	
		// Do pagination
		// TODO
		
		// Config - per page, etc etc
		
		$infractions_list = $this->get_infractions();
		
		if(!$infractions_list)
		{
			// Something templatey about no infractions
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		foreach($infractions_list as $infraction)
		{
			// Set templates!
			
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'	=> $infraction['infraction_id'],
				'POST_ID'			=> $infraction['post_id'],
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				
				'USERNAME'		=> $infraction['username'],
				'USER_PROFILE'		=> get_username_string('full', $infraction['user_id'], $infraction['username'], $infraction['user_colour']),
				
				'USER_ID'			=> $infraction['user_id'],
				'REASON'			=> $infraction['reason'],
				'POINTS_ISSUED'	=> $infraction['infraction_points'],
				'TOTAL_POINTS'		=> $infraction['total_points'],
				'ACTIONS'			=> '',
				
				'DELETE_LINK'		=> $this->u_action . '&action=delete&infraction_id=' . $infraction['infraction_id'] ,
				
				// TODO actions
			));
		}
		
	
		
	}
	
	public function view_infractions_user()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		$user_id = request_var('user_id', 0);
		
		// Load avatars, colours, etc
		// Can we make use of the get_infraction function? - will cacheing for 1 second make the latter quciker?
		$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if (!function_exists('get_user_avatar'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}

		$rank_title = $rank_img = '';
		$avatar_img = get_user_avatar($user_row['user_avatar'], $user_row['user_avatar_type'], $user_row['user_avatar_width'], $user_row['user_avatar_height']);

		$template->assign_vars(array(

			'RANK_TITLE'		=> $rank_title,
			'JOINED'			=> $user->format_date($user_row['user_regdate']),
			'POSTS'			=> $user_row['user_posts'],
			'INFRACTIONS'		=> $user_row['infraction_points'] ,

			'USERNAME'		=> $user_row['username'],
			'USER_PROFILE'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'		=> $rank_img,
		));

		// TODO : Pagination, so, get limit and offset?
		
		// Get infractions
		$infractions_list = $this->get_infractions(25, 0, 0, $user_id);

		if(!$infractions_list)
		{
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		foreach($infractions_list as $infraction)
		{
	
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'	=> $infraction['infraction_id'],
				'POST_ID'			=> $infraction['post_id'],
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				
				'USERNAME'		=> $infraction['username'],
				'USER_PROFILE'		=> get_username_string('full', $infraction['user_id'], $infraction['username'], $infraction['user_colour']),
				
				'USER_ID'			=> $infraction['user_id'],
				'REASON'			=> $infraction['reason'],
				'POINTS_ISSUED'	=> $infraction['infraction_points'],
				'TOTAL_POINTS'		=> $infraction['total_points'],
				'ACTIONS'			=> '',
				
				// TODO actions
			));
		}
		
		// Do pagination
		$total_infractions = $this->last_get_infraction_total();	
	
		
		
		
		
	}
	
	/**
	 * Load the data for a post, checking that the user has read permissions for it too
	 * @param $id post id
	 * @return mixed - array success, else string error
	 */
	public function get_post_for_infraction($post_id)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// Check if the user has already been warned for this post
		// TODO
			
		if(!is_numeric($post_id))
		{
			return 'POST_NOT_EXIST';
		}
		
		$sql = "SELECT * FROM " . POSTS_TABLE . " WHERE post_id = $post_id";
		
		$result = $db->sql_query($sql); // Do we cache it for ~60 seconds, saves querying again but maybe another mod updates the post?
		
		$post_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if(sizeof($post_row) == 0)
		{
			return 'POST_NOT_EXIST';
		}
		
		// Check the user has issue warning rights too it 
		// TODO 
		// just read rights for now, let infractions be global?
		
		// Check if the user can read the post
		if(!$auth->acl_get('f_read', $post_row['forum_id']))
		{
			return 'NO_PERMISIONS';
		}
		
		// TODO - is there a better way to check permisions? - maybe first, since we already have the forum id from the infraction!
		
		
		return $post_row;
	}

	
	/** 
	 * Get the infractions
	 * Underlying function
	 *
	 * @param $user_id User ID to select for - false for non specific (so all)
	 * @param $forum_id - forum id to sele
	 * @return array infractions demanded
	 */
	public function get_infractions($limit = 25, $offset = 0, $start_date = 0,  $user_id = false, $forum_id = false,  $active_only = true)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// Records + offset for pagination - which i should learn how to dodododo
		$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE . ' WHERE ';
		
		// TODO - set the right select from options
		// TODO - Maybe have an order by var?
		
		// TODO - Choose only required feelms, TBC later
		
		// TODO TESTING - Run this query in PHP My admin to get the right syntax 
		$sql_array = array(
			'SELECT'		=> 'i.*, p.post_subject, u.username, u.user_colour, u.infraction_points AS total_points',
			
			'FROM'		=> array(
				INFRACTIONS_TABLE	=> 'i',
			),
			
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(POSTS_TABLE => 'p'),
					'ON'		=> 'i.post_id = p.post_id'
				),
				// NOTE : Need to link to users too - impact on performance now?
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'		=> 'i.user_id = u.user_id',
				),
			),
			
			'WHERE'	=> array(),
			
			'ORDER_BY'	=> 'issue_time DESC',
		);
		
		// TODO Do the active only
		// 'WHERE'	=> 'void <> ' . $active_only . ' ' ,
		
		
		// TODO
		// Check relation ship works
		
		// TODO - Imnplement forum_id in schema
		
		if(is_numeric($user_id) && $user_id > 0)
		{
			$sql_array['WHERE'][] = " i.user_id =  $user_id ";
		}
		
		if(is_numeric($forum_id) && $forum_id > 0)
		{
			$sql_array['WHERE'][] = " i.forum_id =  $forum_id ";
		}
		
		$sql_array['WHERE'] = implode($sql_array['WHERE'], 'AND');
		
		// used for pagination
		$this->last_sql_array = $sql_array;
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $limit, $offset);
		
		$infractions = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		
		$row_count = sizeof($infractions);
		$this->last_get_infraction_count = $row_count;
		
		if($row_count < $limit)
		{
			$this->last_get_infraction_total = $row_count; // Our total rows!
		}
		
		if($row_count == 0)
		{
			return false;
		}
		
		return $infractions;
	}
	
	/**
	 * A function to get total row count for last infraction view select
	 * NOTE this way its optional if pagination is required, - needs a better way though?
	 */
	public function last_get_infraction_total()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// NOTE - if the return was less than the limit, then that is our total rows - performance!!
		// Argh this is messing with my mind - its getting VERY messy, need to optimise approach
		
		if($this->last_get_infraction_count == 0)
		{
			return 0;
		}
		
		$sql_array = $this->last_sql_array;
		$sql_array['SELECT'] = 'count(i.infraction_id) AS total_infractions';
		$sql = $db->sql_build_query('SELECT', $sql_array);
		
		$result = $db->sql_query($sql);
		$total_infractions = $db->sql_fetchfield('total_infractions');
		$db->sql_freeresult($result);
		
		return $total_infractions;
	}

	
}

// EOF