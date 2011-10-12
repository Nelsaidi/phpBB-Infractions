<?php

/**
* phpBB Infraction System
* 
* @package phpBB3
* @copyright (c) 2011 Nelsaidi
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

 
class mcp_infractions
{
	public $p_master;
	public $u_action;
	
	public function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		global $infractions;
		
		$action = request_var('action', '');

		add_form_key('mcp_infractions');
		
		$template->assign_vars(array(
			'S_IN_INFRACTIONS'		=> 1,
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
			
		));

		switch($mode)
		{
			case 'issue':
				$this->issue_infraction();
				$this->tpl_name = 'infractions_issue';	
				$this->page_title = 'Issue Infraction';
			break;

			case 'view':
			
				if($action == 'delete')
				{
					$this->delete_infraction();					
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
	 * This function is responsible for displaying the form for issuing an infraction
	 * And then processing the infraction and issuing it
	 */
	public function issue_infraction()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		global $infractions;
		
		// Check if the user can issue an infraction
		if(!$auth->acl_get('m_infractions_issue'))
		{
			trigger_error('NOT_AUTHORISED');
		}
		
		$username = request_var('username', '');
		
		$user_id = request_var('user_id', 0);
		$post_id = request_var('post_id', 0);
		$type = request_var('type', 0);
		
		if($user_id == 0 && $post_id == 0 && $username == '')
		{
			$template->assign_var('S_INFRACTIONS_NO_USER' , 1);			
			return;
		}
		
		if($user_id == ANONYMOUS)
		{
			trigger_error('Cannot issue a warning to this user');
		}
		
		// Get the user ID of the selected user, and redirect to a URL with the id appended
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
			trigger_error('Selected user does not exist.');
		}
		
	
		if($user->data['user_id'] == $user_row['user_id'])
		{
			trigger_error('You cannot issue an infraction to your self.');
		}	
		

		// Check if the form has been submitted, if not, display the form to issue an infraction
		if(!isset($_POST['issue_infraction']))
		{
			$template->assign_vars(array(
				'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=infractions&amp;mode=issue&amp;user_id=' . $user_id . '&amp;post_id=' . $post_id),
				'INFRACTION_USER_ID'	=> $user_row['user_id'],
				'INFRACTION_TYPE'		=> $type,
			));
			
			// Get user information such as avatar and rank
			if (!function_exists('get_user_avatar'))
			{
				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
			}
			$rank_title = $rank_img = '';
			$avatar_img = get_user_avatar($user_row['user_avatar'], $user_row['user_avatar_type'], $user_row['user_avatar_width'], $user_row['user_avatar_height']);

			$template->assign_vars(array(
				// 'U_POST_ACTION'	=> $this->u_action,

				'RANK_TITLE'		=> $rank_title,
				'JOINED'			=> $user->format_date($user_row['user_regdate']),
				'POSTS'			=> $user_row['user_posts'],
				'INFRACTION_POINTS'		=> $user_row['infraction_points'] ,

				'USERNAME'		=> $user_row['username'],
				'USER_PROFILE'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

				'AVATAR_IMG'		=> $avatar_img,
				'RANK_IMG'		=> $rank_img,
			));

			// Is the infraction for a post?
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
			
			// Load infraction templates to be put in the form
			$sql = 'SELECT * FROM ' . INFRACTION_TEMPLATES_TABLE . ' ORDER BY position ASC';
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
		
		/** We are issuing an infraction **/
		
		// Populate infraction details with already known stuff
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
		
		$infraction_template = request_var('infraction_template', 0);
		
		// Load data from template if selected
		if($infraction_template != 0)
		{
			$sql = 'SELECT * FROM ' . INFRACTION_TEMPLATES_TABLE . " WHERE template_id = $infraction_template";
			$result = $db->sql_query($sql);
			$template_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if(sizeof($template_row) == 0)
			{
				trigger_error('invalid template selected');
			}

			$infraction = array_merge($infraction, array(
				'infraction_points'		=> $template_row['infraction_points'],
				'duration'				=> $template_row['duration'],
				'reason'				=> $template_row['reason']
			));
		
		}
		else
		{
		
			$infraction = array_merge($infraction, array(
				'infraction_points'		=> request_var('points', 0),
				'duration'				=> request_var('duration', ''),
				'reason'				=> request_var('reason', ''),
			));
		}
		
		// Validate infraction details
		if($infraction['infraction_points'] < 0)
		{
			trigger_error('');
		}
		
		if($infraction['duration'] == 0)
		{
			// Permanent 
			$infraction['expire_time'] = 0;
			$infraction['duration'] = 0; // For typcast reasons
		}
		else
		{
			$infraction['expire_time'] = strtotime('+' . $infraction['duration']);
			if($infraction['expire_time'] < time())
			{
				trigger_error('Invalid Date');
			}
			else
			{
				$infraction['duration'] = $infraction['expire_time'] - time();
			}
		}

		// Calculate expire from duration, 0 = non expiring
		$infraction['expire_time'] = ($infraction['duration'] == 0) ? 0 : time() + $infraction['duration'] * 60;
		
		if($infraction['duration'] == 0)
		{
			// Permanent infraction
			$infraction['expire_time'] = 0;
		}
		else
		{
			$infraction['expire_time'] = $infraction['duration'] * 60 + time(); // (final) Duration is given in minutes
		}
		
		$sql = 'INSERT INTO ' . INFRACTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $infraction);
		$db->sql_query($sql);

		// Update infraction_points in users table
		if($infraction['infraction_points'] > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET infraction_points = infraction_points + {$infraction['infraction_points']} WHERE user_id = {$user_row['user_id']}";
			$db->sql_query($sql);
		}
		
		// TODO Actions!!
		
		include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		include($phpbb_root_path . 'language/' . basename($user_row['user_lang']) . "/infractions.$phpEx");

		$message_parser = new parse_message();

		$message_parser->message = sprintf($lang['INFRACTION_PM_BODY'], $user_row['username'], $infraction['reason'], $infraction['infraction_points'], $infraction['infraction_points'] + $user_row['infraction_points']);
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

		submit_pm('post', $lang['INFRACTION_PM_SUBJECT'], $pm_data, false);
		add_log('mod', 0, 0, 'Infraction issued');	
		
		// TODO RUN HOOK: infraction_issued !!

		// User chose to edit post, redirect
		if(request_var('edit_post', 0) == 1)
		{
			redirect(append_sid("{$phpbb_root_path}posting.php", "mode=edit&f={$infraction['forum_id']}&p={$infraction['post_id']}"));
		}
		
		// Redirect to infractions page for instantness
		redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=infractions"));
		exit;
	}
 
	/**
	 * This function deals with the deletion and thus reversal of infractions,
	 * If infraction id is NOT supplied, it will get it from the URI
	 * And then redirect
	 *
	 * Optimisations - modify the users table only once if multiple deletes?
	 */
	public function delete_infraction($infraction_id = false)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;

		
		// Loaded via the URI
		if($infraction_id === false)
		{
			if(!$auth->acl_get('m_infractions_delete'))
			{
				trigger_error('NOT_AUTHORISED');
			}
			
			$infraction_id = request_var('infraction_id', 0);
		}
		
		if($infraction_id == 0 OR !is_numeric($infraction_id))
		{
			trigger_error('Invalid infraction ID');
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
		
		// $delete_mode = request_var('delete_mode', 'delete');
		$delete_mode = 'delete';
		
		if($delete_mode == 'delete')
		{
			// Delete it fully out of the DB
			$removal_sql = 'DELETE FROM ' . INFRACTIONS_TABLE . " WHERE infraction_id = $infraction_id";
		}
		else if($delete_mode == 'void')
		{
			// Just void it, still display it - cron job will purge
			// $removal_sql = 'UPDATE ' . INFRACTIONS_TABLE . " SET void = 1 WHERE infraction_id = $infraction_id";
		}
		else
		{
			trigger_error('Unexepected delete method');
		}
		
		$db->sql_query($removal_sql);
		unset($removal_sql);
		
		// Infraction now doesnt exist, lets reverse its actions

		$user_id = $infraction['user_id']; // Lets not trust the DB too
		$infraction_points = $infraction['infraction_points']; 

		// Remove added points from the user
		if($infraction_points > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET infraction_points = infraction_points - {$infraction_points} WHERE user_id = {$user_id}";
			$db->sql_query($sql);
		}
		
		// TODO RUN HOOK: infraction_deleted
		
		if($infraction_id === false)
		{
			add_log('mod', 0, 0, 'Infraction deleted');		
			redirect(append_sid($this->u_action));
		}
		
		return true;
	}
	
	/**
	 * Infractions index
	 * So recent infractions
	 */
	public function view_infractions()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// Do pagination
		
		clear_expired_infractions();
		$infractions_list = $this->get_infractions();
		
		if(!$infractions_list)
		{
			// Something templatey about no infractions
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		foreach($infractions_list as $infraction)
		{			
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'	=> $infraction['infraction_id'],
				'POST_ID'			=> $infraction['post_id'],
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				
				'EXPIRE_TIME'	 	=> $user->format_date($infraction['expire_time']),
				
				'USERNAME'			=> $infraction['username'],
				'USER_PROFILE'		=> get_username_string('full', $infraction['user_id'], $infraction['username'], $infraction['user_colour']),
				
				'USER_ID'			=> $infraction['user_id'],
				'REASON'			=> $infraction['reason'],
				'POINTS_ISSUED'		=> $infraction['infraction_points'],
				'TOTAL_POINTS'		=> $infraction['total_points'],
				'ACTIONS'			=> '',
				
				'DELETE_LINK'		=> ($auth->acl_get('m_infractions_delete') ? append_sid($this->u_action . '&action=delete&infraction_id=' . $infraction['infraction_id']) : ''),
			));
		}
		
	
		
	}
	
	/**
	 * View infractions for a user
	 * To show user details, more detail about infractions
	 */
	public function view_infractions_user()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		$user_id = request_var('user_id', 0);
		clear_expired_infractions($user_id);
		$start = request_var('start', 0);
		
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
			'POSTS'				=> $user_row['user_posts'],
			'INFRACTION_POINTS'	=> $user_row['infraction_points'] ,

			'USERNAME'			=> $user_row['username'],
			'USER_PROFILE'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $rank_img,
	
		));

		// TODO : Pagination, so, get limit and offset?
		
		// Get infractions
		$infractions_list = $this->get_infractions(25, $start, 0, $user_id);

		if(!$infractions_list)
		{
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		foreach($infractions_list as $infraction)
		{
	
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'		=> $infraction['infraction_id'],
				'POST_ID'			=> $infraction['post_id'],
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				'EXPIRE_TIME'	 	=> $user->format_date($infraction['expire_time']),
				
				'USERNAME'			=> $infraction['username'],
				'USER_PROFILE'		=> get_username_string('full', $infraction['user_id'], $infraction['username'], $infraction['user_colour']),
				
				'USER_ID'			=> $infraction['user_id'],
				'REASON'			=> $infraction['reason'],
				'POINTS_ISSUED'		=> $infraction['infraction_points'],
				'TOTAL_POINTS'		=> $infraction['total_points'],
				'ACTIONS'			=> '',
				
				'DELETE_LINK'		=> ($auth->acl_get('m_infractions_delete') ? append_sid($this->u_action . '&action=delete&infraction_id=' . $infraction['infraction_id'] . '&user_id=' . $user_id . '&start=' . $start) : ''),
				// TODO actions
			));
		}
		
		// Pagination
		$total_infractions = $this->last_get_infraction_total();	
		$pagination_url = append_sid($phpbb_root_path . 'mcp.' . $phpEx, array('i' => 'infractions', 'mode' => 'view', 'user_id' => $user_id));

		
		$template->assign_vars(array(
			'PAGINATION'      		  => generate_pagination($pagination_url, $total_infractions, 25, $start),
			'PAGE_NUMBER'    		  => on_page($total_infractions, 25, $start),
			'TOTAL_INFRACTIONS'       => $total_infractions . ' Infractions',
		));		
	}
	
	/**
	 * Load the data for a post, checking that the user has read permissions for it too
	 * @param int post id
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
	public function get_infractions($limit = 25, $offset = 0, $start_date = 0,  $user_id = false, $forum_id = false,  $show_void = false)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// Records + offset for pagination - which i should learn how to dodododo
		$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE . ' WHERE ';

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
			
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'		=> 'i.user_id = u.user_id',
				),
			),
			
			'WHERE'	=> array(),
			
			'ORDER_BY'	=> 'issue_time DESC',
		);
		
		if($show_void === false)
		{
			$sql_array['WHERE'][] = ' void = 0 ';
		}
		
		if(is_numeric($user_id) && $user_id > 0)
		{
			$sql_array['WHERE'][] = " i.user_id =  $user_id ";
		}
		
		if(is_numeric($forum_id) && $forum_id > 0)
		{
			$sql_array['WHERE'][] = " i.forum_id =  $forum_id ";
		}
		
		// Build our WHERE part as needed
		$sql_array['WHERE'] = implode($sql_array['WHERE'], 'AND');
		
		// Store the array so we can select count total to use for pagination
		$this->last_sql_array = $sql_array;
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $limit, $offset);
		
		$infractions = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		
		$row_count = sizeof($infractions); 
		$this->last_get_infraction_count = $row_count; // Total rows returned
		
		// If we got less rows than our limit, then this is our total rows
		if($row_count < $limit)
		{
			$this->last_get_infraction_total = $row_count; 
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
	 *
	 * Not used yet, need to figure it out
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