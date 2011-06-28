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
	
}
