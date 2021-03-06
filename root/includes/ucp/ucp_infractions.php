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

class ucp_infractions 
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
		
		// Load the MCP infractions class to get the get_infraction method
		include($phpbb_root_path . 'includes/mcp/mcp_infractions.' . $phpEx);
		$i = new mcp_infractions;
		$infractions_list = $i->get_infractions(0, 0, 0, $user->data['user_id']);
		unset($i);
			
		if(!$infractions_list)
		{
			$template->assign_var('S_INFRACTIONS_NONE', 1);
			return;
		}
		
		$template->assign_var('TOTAL_POINTS', sprintf($user->lang['INFRACTION_YOUR_TOTAL'], $user->data['infraction_points']));
		
		foreach($infractions_list as $infraction)
		{			
			$template->assign_block_vars('infraction', array(
				'INFRACTION_ID'	=> $infraction['infraction_id'],
				
				'ISSUE_TIME'	 	=> $user->format_date($infraction['issue_time']),
				'EXPIRE_TIME'	 	=> (($infraction['expire_time'] == 0) ? $user->lang['INFRACTION_NEVER'] : $user->format_date($infraction['expire_time'])),

				'REASON'			=> (!empty($infraction['topic_id']) ? "<strong><a href=\"./viewtopic.php?p={$infraction['post_id']}#p{$infraction['post_id']}\">{$infraction['post_subject']}</a></strong><br/>{$infraction['reason']}" : $infraction['reason']),
				'POINTS_ISSUED'		=> $infraction['infraction_points'],

			));
		}
		
	}
	
}
