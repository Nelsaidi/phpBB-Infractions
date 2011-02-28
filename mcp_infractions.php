<?php

/**
 * The controller
 */

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

		$this->page_title = 'MCP_INFRACTION';

		add_form_key('mcp_warn');
		
		switch($mode)
		{
			case 'issue_infraction':
				$this->warn_user_view();
				$this->tpl_name = 'mcp_warn_front';			
			break;
				
			// case 'warn_post':
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
			
			$sql = "SELECT poster_id, post_id FROM " . POSTS_TABLE . " WHERE post_id = $post_id";
			
			$result = $db->sql_query($sql); // Do we cache it for ~60 seconds, saves querying again but maybe another mod updates the post?
			
			$post_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if(empty($post_data))
			{
				trigger_error('post does not exist');
			}
			
			if($user_id == 0)
			{
				$user_id = $post_data['poster_id'];
			}
			
		}
		
		// Get user data
		$sql = "SELECT * FROM " . USERS_TABLE . " WHERE user_id = $user_id";
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
			// Being warned for a post
			$template->assign_vars(array(
				'INFRACTION_USERNAME'	=> $user_row['username'],
				'INFRACTION_USER_ID'	=> $user_row['user_id'],
				'INFRACTION_TYPE'		=> $type,
			));
			
			if(isset($post_data))
			{
				$template->assign_vars(array(
					'INFRACTION_POST'		=> true,
					'INFRACTION_POST_TEXT'	=> $post_data['post_text'],
				));
			}
			
			return true;
		}
		
		// Ok, we are creating an infraction
		
		// Populate with already validated stuff
		$infraction = array(
			'user_id'		=> $user_id,
			'issuer_id'	=> $user['user_id'],
			'issue_time'	=> time()
		);
		
		if(isset($post_data))
		{
			$infraction['post_id'] = $post_data['post_id'];
		}
		
		// Get additional 
		
	
	}
