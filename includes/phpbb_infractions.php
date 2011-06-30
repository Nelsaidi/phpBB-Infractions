<?php

class phpbb_infractions
{
	
	/**
	 * Load the data for a post, checking that the user has read permissions for it too
	 * @return mixed - array success, else string error
	 */
	public function get_post_for_infraction($id)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// Check if the user has already been warned for this post
		// TODO
			
		if(!is_numeric($id))
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
		
		return $post_row
	}
	
	/**
	 * Clears expired infractions
	 * @param $user mixed - single or array or blank for all
	 * @return bool success
	 */
	public function clear_expired_infractions($user_id = '')
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE  . ' WHERE expire_date < ' . time() . ' AND void = 0 ';
		
		if(is_numeric($user_id))
		{
			$sql .= "AND user_id = $user_id ";
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
			
			if($infraction['points'] > 0)
			{
				$sql = "UPDATE " . USERS_TABLE . " SET infraction_points = infraction_points - {$infraction['points']} WHERE user_id = {$infraction['user_id']}";
				$db->sql_query($sql);
			}
			
		}
		
		// TODO
		// Check config - time to keep infractions for in table for
		// Issue - now that they are variable, we have to do this each time?
		// Or does another script handle that? a cron job perhaps
		// Yep, that sounds best - we just set a flag
		
		$sql = "DELETE FROM " . USERS_TABLE . " WHERE expire_date < " . time() . ' AND void = 0 '
		$deleted = $db->sql_query($sql);
		
		return $deleted;
	}

	/** 
	 * Get the infractions
	 * Underlying function
	 *
	 * @param $user_id User ID to select for - false for non specific (so all)
	 * @param $forum_id - forum id to sele
	 * @return array infractions demanded
	 */
	public function get_infractions($limit = 25, $offset = 0, $start_date = 0,  $user_id = false, $forum_id = false,  $active_only = true)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		// Records + offset for pagination - which i should learn how to dodododo
		$sql = 'SELECT * FROM ' . INFRACTIONS_TABLE . ' WHERE ';
		
		// TODO - set the right select from options
		// TODO - Maybe have an order by var?
		
		// TODO - Choose only required feelms, TBC later
		$sql_array = array(
			'SELECT'		=> 'i.*, p.post_subject, u.username, u.infractions',
			
			'FROM'		=> array(
				INFRACTIONS_TABLE	=> 'i',
			),
			
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(POSTS_TABLE => 'p'),
					'ON'		=> 'i.post_id = p.post_id'
				),
				// NOTE : Need to link to users too - impact on performance now?
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'		=> 'i.user_id = u.user_id',
				),
			),
			
			'WHERE'	=> 'void <> ' . $active_only . ' ' ,
			
			'ORDER_BY'	=> 'issue_time DESC',
		);
		
		// TODO
		// Check relation ship works
		
		// TODO - Imnplement forum_id in schema
		
		if(is_numeric($user_id) && $user_id > 0)
		{
			$sql_array['WHERE'] .= " AND user_id =  $user_id ";
		}
		
		if(is_numeric($forum_id) && $forum_id > 0)
		{
			$sql_array['WHERE'] .= " AND forum_id =  $forum_id ";
		}
		
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $limit, $offset);
		
		$infractions = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		
		// used for pagination
		$this->last_get_infraction_sql = $sql;
		
		$row_count = sizeof($infractions)
		$this->last_get_infraction_count = $row_count;
		
		if($row_count < $limit)
		{
			$this->last_get_infraction_total = $row_count; // Our total rows!
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
		
		$sql = $this->last_get_infraction_sql;
		$sql['WHERE'] = 'count(i.infraction_id) AS total_infractions';
		$result = $db->sql_query($sql);
		$total_infractions = $db->sql_fetchfield('total_infractions');
		$db->sql_freeresult($result);
		
		return $total_infractions;
	}
	

	
}
