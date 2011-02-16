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
			case 'warn_user':
				$this->warn_user_view();
				$this->tpl_name = 'mcp_warn_front';			
			break;
				
			// case 'warn_post':
		}
	}
	
	/**
	 * The view method for warning a user
	 *
	 * @since 0.1
	 */
	public function warn_user_view()
	{
	
	
	}
