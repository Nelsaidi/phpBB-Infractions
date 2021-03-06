<?php
/**
* phpBB Infraction System
* @copyright (c) 2012 Nelsaidi
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
				$this->tpl_name = 'mcp_infractions_issue';	
				$this->page_title = 'INFRACTION_ISSUE';
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
						trigger_error('INFRACTION_USER_NOT_EXIST');
					}
					
					redirect(append_sid("{$phpbb_root_path}mcp.$phpEx", "i=infractions&mode=view&user_id={$user_row['user_id']}"));
				}		
				
				if($user_id > 0)
				{
					$this->view_infractions_user();
					$this->tpl_name = 'mcp_infractions_user';
					$this->page_title = 'INFRACTIONS'; // append username to this
				}
				else
				{
					$this->view_infractions();
					$this->tpl_name = 'mcp_infractions_index';
					$this->page_title = 'INFRACTIONS';
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
			trigger_error('INFRACTION_ISSUE_GUEST');
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
				trigger_error('INFRACTION_USER_NOT_EXIST');
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
			
			$user_id = (int) $post_row['poster_id'];
		}
		
		$sql = 'SELECT * FROM ' . USERS_TABLE . " WHERE user_id = $user_id";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);	
		$db->sql_freeresult($result);
		
		if(!isset($user_row['user_id']))
		{
			trigger_error('INFRACTION_USER_NOT_EXIST');
		}
		
	
		if($user->data['user_id'] == $user_row['user_id'])
		{
			trigger_error('INFRACTION_ISSUE_YOURSELF');
		}	
		

		// Check if the form has been submitted, if not, display the form to issue an infraction
		if(!isset($_POST['submit']))
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
				'U_VIEW_INFRACTIONS'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=infractions&amp;mode=view&amp;user_id=' . $user_id),

				'RANK_TITLE'			=> $rank_title,
				'JOINED'				=> $user->format_date($user_row['user_regdate']),
				'POSTS'					=> $user_row['user_posts'],
				'INFRACTION_POINTS'		=> $user_row['infraction_points'] ,

				'USERNAME'				=> $user_row['username'],
				'USER_PROFILE'			=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

				'AVATAR_IMG'			=> $avatar_img,
				'RANK_IMG'				=> $rank_img,
			));

			// Is the infraction for a post?
			if(isset($post_row))
			{
				// Get the mssage and parse it, for display
				$message = censor_text($post_row['post_text']);
				
				if ($post_row['bbcode_bitfield'])
				{
					if(!class_exists('bbcode'))
					{
						include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
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
			'issuer_id'		=> $user->data['user_id'],
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
				trigger_error('INFRACTION_OOPS');
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
				'infraction_points'		=> request_var('infraction_points', 0),
				'duration'				=> request_var('duration', ''),
				'reason'				=> utf8_normalize_nfc(request_var('reason', '', true)),
			));
		}
		
		// Validate infraction details
		if($infraction['infraction_points'] < 0)
		{
			trigger_error('INFRACTION_NEGATIVE_POINTS');
		}
		
		// Load custom time
		if($infraction['duration'] == '-1')
		{
			$infraction['duration'] = request_var('duration_custom', '');

		}
		
		if($infraction['duration'] == '0')
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
				trigger_error('INFRACTION_INVALID_DATE');
			}
			else
			{
				$infraction['duration'] = $infraction['expire_time'] - time(); // Great, duration is good
			}
		}
		
		$sql = 'INSERT INTO ' . INFRACTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $infraction);
		$db->sql_query($sql);

		// Update infraction_points in users table
		if($infraction['infraction_points'] > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET infraction_points = infraction_points + {$infraction['infraction_points']} WHERE user_id = {$user_row['user_id']}";
			$db->sql_query($sql);
		}
			
		include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		include($phpbb_root_path . 'language/' . basename($user_row['user_lang']) . "/infractions.$phpEx");

		$message_parser = new parse_message();
		
		// Append post topic
		if(!empty($infraction['post_id']))
		{
			$infraction['reason'] = "[url=" . generate_board_url () . "/viewtopic.php?p={$infraction['post_id']}#p{$infraction['post_id']}][b]{$post_row['post_subject']}[/b][/url]\n{$infraction['reason']}";
		}
		
		
		$message_parser->message = sprintf($lang['INFRACTION_PM_BODY'], $user_row['username'], $infraction['reason'], $infraction['infraction_points'], $infraction['infraction_points'] + $user_row['infraction_points'],  sprintf($config['infractions_pm_sig'], $user->data['username']));
		
		$message_parser->parse(true, true, false, false, false, true, true);

		$pm_data = array(
			'from_user_id'			=> $user->data['user_id'],
			'from_user_ip'			=> $user->ip,
			'from_username'			=> $user->data['username'], // Why does it need this?
			'enable_sig'			=> false,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> true,
			'icon_id'				=> 0,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'address_list'			=> array('u' => array($user_row['user_id'] => 'to')),
		);
		
		submit_pm('post', $lang['INFRACTION_PM_SUBJECT'], $pm_data, false);
		add_log('mod', 0, 0, "Issued an infraction to {$user_row['username']}"); // Do this using languages is not possible?	
		
		// TODO RUN HOOK: infraction_issued !!

		// User chose to edit post, redirect
		if(request_var('edit_post', 0) == 1)
		{
			redirect(append_sid("{$phpbb_root_path}posting.php", "mode=edit&amp;f={$infraction['forum_id']}&amp;p={$infraction['post_id']}"));
		}
		
		// Redirec to topic after issuing an infraction
		if(isset($post_row))
		{
			redirect(append_sid("{$phpbb_root_path}viewtopic.php", "p={$infraction['post_id']}#p{$infraction['post_id']}"));
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
	 * 
	 * Needs to be seperated out once actions are implemented, and do the below.
	 * Optimisations - modify the users table only once if multiple deletes?
	 * A batch user table update perhaps, so store all point modifications in an array
	 */
	public function delete_infraction($infraction_id = false)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		if(!$auth->acl_get('m_infractions_delete'))
		{
			trigger_error('NOT_AUTHORISED');
		}
		
		if(!confirm_box(true))
		{
			$s_hidden_fields = build_hidden_fields(array(
				'submit'		=> true,
				'action' 		=> 'delete',
				'infraction_id' => request_var('infraction_id', 0),
				'user_id'		=> request_var('user_id', 0),
				'start'			=> request_var('start', 0),
				)
			);

			//display mode
			confirm_box(false, 'INFRACTION_DELETE', $s_hidden_fields);
			return;
		}
		
		$infraction_id = request_var('infraction_id', 0);
		
		
		if($infraction_id == 0 || !is_numeric($infraction_id))
		{
			trigger_error('INFRACTION_NOT_EXIST');
		}
		
		// Get a copy of the infraction to allow for full reversal
		$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE . " WHERE infraction_id = $infraction_id";
		$result = $db->sql_query($sql);
		$infraction = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		if(empty($infraction))
		{
			trigger_error('INFRACTION_NOT_EXIST');
		}

		if($infraction['void'] == 1)
		{
			trigger_error('INFRACTION_NOT_EXIST');
		}
		
		if($config['infractions_hard_delete'] == 1)
		{
			// Delete it fully out of the DB
			$removal_sql = 'DELETE FROM ' . INFRACTIONS_TABLE . " WHERE infraction_id = $infraction_id";
		}
		else
		{
			$removal_sql = 'UPDATE ' . INFRACTIONS_TABLE . ' SET void = 1, deleted_time = ' . time() . " WHERE infraction_id = $infraction_id";
		}

		$db->sql_query($removal_sql);
		unset($removal_sql);
		
		// Infraction now doesnt exist, lets reverse its actions

		$user_id = (int) $infraction['user_id']; // Lets not trust the DB too
		$infraction_points = (int) $infraction['infraction_points']; 

		// Remove added points from the user
		if($infraction_points > 0)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET infraction_points = infraction_points - {$infraction_points} WHERE user_id = {$user_id}";
			$db->sql_query($sql);
		}
		

		// Get the username for listing in log
		$sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		$username = $db->sql_fetchfield('username', 0, $result);
		$db->sql_freeresult($result);
		
		add_log('mod', 0, 0, "Deleted an infraction issued to {$username}");		
		
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
		
		clear_expired_infractions();
		
		$start = request_var('start', 0);
		
		$infractions_list = $this->get_infractions(25, $start, 0, 0, false, true);
		
		if(!$infractions_list)
		{
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		$total_infractions = $this->last_get_infraction_total();	
		$pagination_url = append_sid($phpbb_root_path . 'mcp.' . $phpEx, array('i' => 'infractions', 'mode' => 'view'));

		
		$template->assign_vars(array(
			'PAGINATION'      		  => generate_pagination($pagination_url, $total_infractions, 25, $start),
			'PAGE_NUMBER'    		  => on_page($total_infractions, 25, $start),
			'TOTAL_INFRACTIONS'       => $total_infractions . ' Infractions',
		));		
		
		foreach($infractions_list as $infraction)
		{			
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'	=> $infraction['infraction_id'],
				'POST_ID'			=> $infraction['post_id'],
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				
				'EXPIRE_TIME'	 	=> (($infraction['expire_time'] == 0) ? $user->lang['INFRACTION_NEVER'] : $user->format_date($infraction['expire_time'])),
				
				'USERNAME'			=> $infraction['username'],
				'USER_PROFILE'		=> get_username_string('full', $infraction['user_id'], $infraction['username'], $infraction['user_colour']),
				
				'USER_ID'			=> $infraction['user_id'],
				'REASON'			=> (!empty($infraction['topic_id']) ? "<strong><a href=\"./viewtopic.php?p={$infraction['post_id']}#p{$infraction['post_id']}\">{$infraction['post_subject']}</a></strong><br/>{$infraction['reason']}" : $infraction['reason']),
				'POINTS_ISSUED'		=> $infraction['infraction_points'],
				'TOTAL_POINTS'		=> $infraction['total_points'],
				'ACTIONS'			=> '',
				
				'DELETE_LINK'		=> (($auth->acl_get('m_infractions_delete') && $infraction['void'] == 0) ? append_sid($this->u_action . '&action=delete&infraction_id=' . $infraction['infraction_id']) : ''),
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
			'U_ISSUE_INFRACTION'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=infractions&amp;mode=issue&amp;user_id=' . $user_id),
			'U_VIEW_INFRACTIONS'	=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=infractions&amp;mode=view&amp;user_id=' . $user_id),

			'RANK_TITLE'		=> $rank_title,
			'JOINED'			=> $user->format_date($user_row['user_regdate']),
			'POSTS'				=> $user_row['user_posts'],
			'INFRACTION_POINTS'	=> $user_row['infraction_points'] ,

			'USERNAME'			=> $user_row['username'],
			'USER_PROFILE'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $rank_img,
	
		));

		
		// Get infractions
		$infractions_list = $this->get_infractions(25, $start, 0, $user_id, false, true);

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
				'EXPIRE_TIME'	 	=> (($infraction['expire_time'] == 0) ? $user->lang['INFRACTION_NEVER'] : $user->format_date($infraction['expire_time'])),
				
				'USERNAME'			=> $infraction['username'],
				'USER_PROFILE'		=> get_username_string('full', $infraction['user_id'], $infraction['username'], $infraction['user_colour']),
				
				'USER_ID'			=> $infraction['user_id'],
				'REASON'			=> (!empty($infraction['topic_id']) ? "<strong><a href=\"./viewtopic.php?p={$infraction['post_id']}#p{$infraction['post_id']}\">{$infraction['post_subject']}</a></strong><br/>{$infraction['reason']}" : $infraction['reason']),
				'POINTS_ISSUED'		=> $infraction['infraction_points'],
				'TOTAL_POINTS'		=> $infraction['total_points'],
				
				'VOID'				=> $infraction['void'],
				
				'DELETE_LINK'		=> (($auth->acl_get('m_infractions_delete') && $infraction['void'] == 0) ? append_sid($this->u_action . '&action=delete&infraction_id=' . $infraction['infraction_id'] . '&user_id=' . $user_id . '&start=' . $start) : ''),
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
		
		$sql_array = array(
			'SELECT'		=> 'i.*, p.post_subject, u.username, u.user_colour, u.infraction_points AS total_points, p.topic_id',
			
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