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

/**
 * This function clears and undoes the effects of infractions
 *
 * @param int Optional - user ID to clear for
 */
function clear_expired_infractions($user_id = 0)
{
	global $auth, $db, $user, $template;
	global $config, $phpbb_root_path, $phpEx;
	
	$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE  . ' WHERE expire_time < ' . time() . ' AND void = 0 AND expire_time <> 0 ';
	
	if(is_numeric($user_id) && $user_id > 1)
	{
		$sql .= " AND user_id = $user_id ";
	}
	
	$result = $db->sql_query($sql);
	
	$infractions = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);
	
	if($config['infractions_deleted_keep_time'] > 0)
	{
		$void_time = time() - ($config['infractions_deleted_keep_time'] * 24 * 60 * 60);
		$sql = 'DELETE FROM ' . INFRACTIONS_TABLE . " WHERE (expire_time < $void_time AND expire_time <> 0) OR (deleted_time < $void_time AND deleted_time <> 0)";
		
		if(is_numeric($user_id)  && $user_id > 1)
		{
			$sql .= " AND user_id = $user_id ";
		}
		$db->sql_query($sql);
	}
	
	// No infractions
	if(sizeof($infractions) == 0)
	{
		return false;
	}
	
	// Note - bans are dealt by the default bans, here we deal with group moves, luckily they are stored in an array
	
	$infraction_sums = array();
	
	foreach($infractions as $infraction)
	{
		// TODO - Undo groups once groups are implemented
		// check if new groups -  iterate through infraction new groups , do group_user_del(4, 53);
		
		// Possibly a little bit more costly possibly more efficient, 
		if($infraction['infraction_points'] > 0)
		{
			$infraction_sums[$infraction['user_id']] += $infraction['infraction_points'];
		}
		
	}
	
	foreach($infraction_sums as $key => $value)
	{
		$sql = "UPDATE " . USERS_TABLE . " SET infraction_points = infraction_points - $value WHERE user_id = $key";
		$db->sql_query($sql);
	}
	
	if($config['infractions_delete_type'] == INFRACTION_DELETE_HARD)
	{
		// Delete it fully out of the DB
		$sql = 'DELETE FROM ' . INFRACTIONS_TABLE . ' WHERE expire_time < ' . time() . ' AND void = 0 ';
	}
	else
	{
		$sql = 'UPDATE ' . INFRACTIONS_TABLE . ' SET void = 1 WHERE expire_time < ' . time() . ' ';
	}

	if(is_numeric($user_id) && $user_id > 1)
	{
		$sql .= " AND user_id = $user_id ";
	}
	
	$db->sql_query($sql);
	
	return true;
	
}

