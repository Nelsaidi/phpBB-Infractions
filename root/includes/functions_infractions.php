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

/**
 * This function clears and undoes the effects of infractions
 *
 * @param int Optional - user ID to clear for
 */
function clear_expired_infractions($user_id = '')
{
	global $auth, $db, $user, $template;
	global $config, $phpbb_root_path, $phpEx;
	
	$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE  . ' WHERE expire_time < ' . time() . ' AND void = 0 AND expire_time <> 0 ';
	
	if(is_numeric($user_id))
	{
		$sql .= " AND user_id = $user_id ";
	}
	
	$result = $db->sql_query($sql);
	
	$infractions = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);
	
	if($config['infractions_deleted_keep_time'] > 0)
	{
		$void_time = time() - $config['infractions_deleted_keep_time'] * 24 * 60 * 60;
		$sql = 'DELTE FROM ' . INFRACTIONS_TABLE . ' WHERE expire_time < ' . $void_time . ' OR deleted_time < ' . $void_time;
		if(is_numeric($user_id))
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
	
	$users = array();
	
	foreach($infractions as $infraction)
	{
		// TODO - Undo groups once groups are implemented
		// check if new groups -  iterate through infraction new groups , do group_user_del(4, 53);
		
		// Possibly a little bit more costly possibly more efficient, 
		if($infraction['infraction_points'] > 0)
		{
			$user[$infraction['userid']] += $infraction['infraction_points'];
		}
		
	}
	
	foreach($user as $key => $value)
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

	if(is_numeric($user_id))
	{
		$sql .= " AND user_id = $user_id ";
	}
	
	$db->sql_query($sql);
	
	return true;
	
}

