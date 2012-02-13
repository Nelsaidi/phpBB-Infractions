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

// Extend mcp to get the get_infraction method
class ucp_infractions extends mcp_infractions
{
	public $p_master;
	public $u_action;
	
	public function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		$action = request_var('action', '');

		// Just one mode.
		$this->tpl_name = 'ucp_infractions';	
		$this->page_title = 'INFRACTIONS';
		
		$infractions_list = $this->get_infractions(0, 0, 0, $user->data['user_id']);
			
		if(!$infractions_list)
		{
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		$template->assign_var('TOTAL_POINTS', sprintf($user->lang('INFRACTION_YOUR_TOTAL_A'), $user->data['infraction_points']));
		
		foreach($infractions_list as $infraction)
		{			
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'	=> $infraction['infraction_id'],
				
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				'EXPIRE_TIME'	 	=> $user->format_date($infraction['expire_time']),

				'REASON'			=> (!empty($infraction['topic_id'])) ? "<strong><a href=\"./viewtopic.php?t={$infraction['topic_id']}\">{$infraction['post_subject']}</a></strong><br/>{$infraction['reason']}" : $infraction['reason'];
				'POINTS_ISSUED'		=> $infraction['infraction_points'],

			));
		}
		
	}
	
}
