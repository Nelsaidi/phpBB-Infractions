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
		

		
		if($post_id != 0)
		{
			// Check if the user has already been warned for this post
			// TODO
			
			$sql = $db->sql_build_query('SELECT', array(
				'SELECT'		=> 'u.*, p.forum_id, p.post_text, p.post_time',
				
				'FROM'		=> array(
					USERS_TABLE	=> 'u',
					POSTS_TABLE	=> 'p'
				),
				
				'LEFT JOIN'	=> array(
					array('ON' => 'u.user_id = p.poster_id'	)
				),
				
				'WHERE'		=> 'p.post_id = ' . $post_id
			);
			
			$result = $db->sql_query($sql, 60); // Do we cache it for ~60 seconds, saves querying again but maybe another mod updates the post?
			
			$post_data = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			if(empty($post_data))
			{
				trigger_error('post does not exist');
			}
		}
		

		// Is someone being warned? If not, then just show them the view
		if(!isset($_POST['submit']))
		{
			// Being warned for a post
			if(isset($post_data))
			{
				$template->assign_vars(array(
					'INFRACTION_POST'		=> true,
					'INFRACTION_USERNAME'	=> $post_data['username'],
					'INFRACTION_USER_ID'	=> $post_data['user_id'],
					'INFRACTION_POST_TEXT'	=> $post_data['post_text'],
				));
			}
			
						
			return true;
		}
		
	
	}
