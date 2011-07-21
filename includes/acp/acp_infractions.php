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
		
		add_form_key('acp_infractions');
		$template->assign_var('U_ACTION', $this->u_action);
		
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
					$name = request_var('name', '');
					$reason = request_var('reason', '');
					$duration = request_var('duration', 0);
					$infraction_points = request_var('infraction_points', 0);
					
					if($infraction_points < 0)
					{
						trigger_error('bad infraction points');
					}
					
					if(empty($name) || empty($reason))
					{
						trigger_error('empty name or reason');
					}
					
					if($action == 'add')
					{
						// Determine position
						$sql = "SELECT MAX(position) as max_position FROM " . INFRACTION_TEMPLATES_TABLE;
						$db->sql_query($sql);
						$position = $db->sql_fetchfield('max_position') + 1;
							
						$sql = 'INSERT INTO ' . INFRACTION_TEMPLATES_TABLE . ' (name, reason, duration, infraction_points, position) VALUES ("' . 
							$db->sql_escape($name) . '", "'.
							$db->sql_escape($reason) . 
							"\", $duration, $infraction_points, $position ) ";
							
						$db->sql_query($sql);
					
					}
					else
					{
						$sql = '';
					}
					
					redirect($this->u_action);
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
				
				// Get position so we can rearrange list after delete
				$sql = "SELECT position FROM " . INFRACTION_TEMPLATES_TABLE . " WHERE template_id = $template_id";
				$result = $db->sql_query($sql);
				$current_position = (int) $db->sql_fetchfield('position');
				unset($sql);
				
				// Remove template from DB
				$sql = "DELETE FROM " . INFRACTION_TEMPLATES_TABLE . " WHERE template_id = $template_id";
				$db->sql_query($sql);
				
				// Rearrange list
				$sql = "UPDATE " . INFRACTION_TEMPLATES_TABLE . " SET position = position - 1 WHERE position > $current_position";
				$db->sql_query($sql);
				
				redirect($this->u_action);
				
				
			break;
			
			case 'moveup':
			case 'movedown':
				$template_id = request_var('template_id', 0);
				if($template_id == 0)
				{
					trigger_error('invalid template id');
				}
				
				$sql = "SELECT position FROM " . INFRACTION_TEMPLATES_TABLE . " WHERE template_id = $template_id";
				$result = $db->sql_query($sql);
				$current_position = (int) $db->sql_fetchfield('position');
				
				$sql = "SELECT MAX(position) as max_position FROM " . INFRACTION_TEMPLATES_TABLE;
				$db->sql_query($sql);
				$max_position = (int) $db->sql_fetchfield('max_position') ;

				unset($sql);

				if($action == 'moveup' && $current_position != 1)
				{
					// Decrease current by 1, add 1 to previous
					$sql = 'UPDATE ' . INFRACTION_TEMPLATES_TABLE . ' SET position = ' . $current_position . ' WHERE position = ' . ($current_position - 1);
					$db->sql_query($sql);
					
					$sql = 'UPDATE ' . INFRACTION_TEMPLATES_TABLE . ' SET position = ' . ($current_position - 1) . ' WHERE template_id = ' . $template_id;
					$db->sql_query($sql);
				
				}
				
				if($action == 'movedown' && $current_position != $max_position)
				{
					// Subtract 1 from previous
					$sql = 'UPDATE ' . INFRACTION_TEMPLATES_TABLE . ' SET position = ' . $current_position . ' WHERE position = ' . ($current_position + 1);
					$db->sql_query($sql);
					
					$sql = 'UPDATE ' . INFRACTION_TEMPLATES_TABLE . ' SET position = ' . ($current_position + 1) . ' WHERE template_id = ' . $template_id;
					$db->sql_query($sql);
				}
				
				

			// No break - continue on displaying the templates
			
			default:
				// Index
				$sql = 'SELECT * FROM ' . INFRACTION_TEMPLATES_TABLE . ' ORDER BY position ASC';
				$result = $db->sql_query($sql);
				$infraction_templates = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				
				$template->assign_var('TEMPLATE_ADD', append_sid($this->u_action, 'action=add'));
				
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
							'INFRACTION_POINTS'		=>  $infraction_template['infraction_points'],
							'DURATION'			=>  $infraction_template['duration'],
							
						));
					}
				}

		}
	
	}
	
}

