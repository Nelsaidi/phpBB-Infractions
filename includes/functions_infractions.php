<?php

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
		
		if($infraction['infraction_points'] > 0)
		{
			$sql = "UPDATE " . USERS_TABLE . " SET infraction_points = infraction_points - {$infraction['infraction_points']} WHERE user_id = {$infraction['user_id']}";
			$db->sql_query($sql);
		}
		
	}
	
	// TODO
	// Check config - time to keep infractions for in table for
	// Issue - now that they are variable, we have to do this each time?
	// Or does another script handle that? a cron job perhaps
	// Yep, that sounds best - we just set a flag
	
	$sql = "DELETE FROM " . INFRACTIONS_TABLE . " WHERE expire_time < " . time() . ' AND void = 0 ';
	$deleted = $db->sql_query($sql);
	
	return $deleted;
}