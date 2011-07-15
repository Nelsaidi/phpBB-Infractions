<?php

/**
 *ACP infractions
 *
 * @todo Put it into classes, I have my reasons for why I done it like this, it'll be nicer ... eventually.
 * @author Nelsaidi
 */

// Move into constants.php
// TODO

// For sake of simplicity (for development)
// define('INFRACTIONS_TABLE', 'infractions');
define('INFRACTION_TEMPLATES_TABLE', 'phpbb_infraction_templates');

define('INFRACTIONS_WARNING', 0);
define('INFRACTIONS_INFRACTION', 1);
// const NOTE = 2;

define('INFRACTIONS_ACTIVE', 0);
define('INFRACTIONS_EXPIRED', 1);
define('INFRACTIONS_REMOVED', 2);
 
class acp_infractions
{
	public $p_master;
	public $u_action;

	public function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		global $infractions;

		// Block Users
		if($user->data['user_id'] != 2)
		{
			trigger_error('Sorry dudes, still in development');
		}
		
		
		// Load our infractions class
		if(!class_exists('infractions'))
		{
			require($phpbb_root_path . 'includes/infractions.class.' . $phpEx);
			
		}
		
		add_form_key('acp_infractions');
		
		switch($mode)
		{
			case 'templates':
				$this->infraction_templates();
				$this->tpl_name = 'acp_infraction_templates';	
				$this->page_title = 'Infraction Templates';
			break;
			
		}
	}
	
	public function infraction_templates()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		global $infractions;
		
		$action = request_var('action', '');		
		
		switch($action)
		{
			case 'add':
			case 'edit':
				if(isset($_POST['submit']))
				{
					// Check $action!
					$name = request_var('name', '');
					$reason = request_var('reason', '');
					$duration = request_var('duration', 0);
					$infraction_points = request_var('infraction_points', 0);
					
					// TODO or >  $config['infraction_points_max'] 
					if($infraction_points < 0)
					{
						trigger_error('bad infraction points');
					}
					
					
					if($action == 'add')
					{
						$sql = 'INSERT INTO ' . INFRACTION_TEMPLATES_TABLE . ' (name, reason, duration, infraction_points) VALUES ("' . 
							$db->sql_escape($name) . '", "'.
							$db->sql_escape($reason) . 
							"\", $duration, $infraction_points ) ";
					}
					else
					{
						$sql = '';
					}
					
					$db->sql_query($sql);
					
					redirect(adm_back_link($this->u_action));
				}
				
				if($action == 'edit')
				{
					// Preload data into form
					$template_id = request_var('template_id', 0);
				}
				
				$template->assign_var('S_TEMPLATE_FORM', 1);
				
			
				
			break;
			
			case 'delete':
				$template_id = request_var('template_id', 0);
				
			break;
			
			default:
				// Index
				$sql = 'SELECT * FROM ' . INFRACTION_TEMPLATES_TABLE;
				$result = $db->sql_query($sql);
				$infraction_templates = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				
				if(sizeof($infraction_templates) == 0)
				{
					$template->assign_var('S_NO_TEMPLATES', 1);
				}
				else
				{
					foreach($infraction_templates as $infraction_template)
					{
						$template->assign_block_vars('templates', array(
							'TEMPLATE_ID'			=>  $infraction_template['template_id'],
							'NAME'				=>  $infraction_template['name'],
							'REASON'				=>  $infraction_template['reason'],
							'INFRACION_POINTS'		=>  $infraction_template['infraction_points'],
							'DURATION'			=>  $infraction_template['duration'],
						));
					}
				}

		}
	
	}
	
}

