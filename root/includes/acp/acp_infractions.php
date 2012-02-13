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
 
class acp_infractions
{
	public $p_master;
	public $u_action;

	public function main($id, $mode)
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		add_form_key('acp_infractions');
		$template->assign_var('U_ACTION', $this->u_action);
		
		switch($mode)
		{
			case 'general':
				$this->infraction_general();
				$this->tpl_name = 'acp_infraction_general';	
				$this->page_title = 'ACP_INFRACTION_GENERAL';
			break;
			case 'templates':
				$this->infraction_templates();
				$this->tpl_name = 'acp_infraction_templates';	
				$this->page_title = 'ACP_INFRACTION_TEMPLATES';
			break;
			
		}
	}
	
	public function infraction_general()
	{
		global $auth, $db, $user, $template;
		global $config, $phpbb_root_path, $phpEx;
		
		$action	= request_var('action', '');
		$submit = (isset($_POST['submit']) || isset($_POST['allow_quick_reply_enable'])) ? true : false;
		
		$form_key = 'acp_infractions';
		add_form_key($form_key);
		
		$display_vars = array(
					'title'	=> 'ACP_INFRACTION_GENERAL',
					'vars'	=> array(
						'legend1'						=> 'ACP_INFRACTION_GENERAL',
						'infractions_delete_type'		=> array('lang' => 'INFRACTION_DELETE_TYPE', 'validate' => 'bool',	'type' => 'radio:yes_no', 'explain' => true),		
						'infractions_deleted_keep_time'	=> array('lang' => 'INFRACTION_DELETE_KEEP_TIME', 'validate' => 'int',	'type' => 'text:40:40', 'explain' => false),						
						'infractions_pm_sig'			=> array('lang' => 'INFRACTION_PM_SIG', 'validate' => 'string',	'type' => 'textarea:4:40', 'explain' => true),
					),
				);

		// ---------
		
		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}
		
		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			if ($config_name == 'auth_method' || $config_name == 'feed_news_id' || $config_name == 'feed_exclude_id')
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($config_name == 'email_function_name')
			{
				$this->new_config['email_function_name'] = trim(str_replace(array('(', ')'), array('', ''), $this->new_config['email_function_name']));
				$this->new_config['email_function_name'] = (empty($this->new_config['email_function_name']) || !function_exists($this->new_config['email_function_name'])) ? 'mail' : $this->new_config['email_function_name'];
				$config_value = $this->new_config['email_function_name'];
			}

			if ($submit)
			{
				set_config($config_name, $config_value);

				if ($config_name == 'allow_quick_reply' && isset($_POST['allow_quick_reply_enable']))
				{
					enable_bitfield_column_flag(FORUMS_TABLE, 'forum_flags', log(FORUM_FLAG_QUICK_REPLY, 2));
				}
			}
		}
		
		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
		
		if ($submit)
		{
			add_log('admin', 'INFRACTION_LOG_UPDATED');
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
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
					
					if($duration == -1)
					{
						$duration = request_var('duration_custom', '');
						
						// Test duratio nis valid
						$test_date = strtotime('+' . $duration);
						if($duration === false)
						{
							trigger_error('INFRACTION_INVALID_DATE');
						}
						
					}
					
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
							$db->sql_escape($reason) . '", "'. 
							$db->sql_escape($duration) . "\", $infraction_points, $position ) ";
							
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
				
				if(!confirm_box(true))
				{
					$s_hidden_fields = build_hidden_fields(array(
						'submit'		=> true,
						'action' 		=> 'delete',
						'template_id' 	=> $template_id,

						)
					);

					//display mode
					confirm_box(false, 'INFRACTION_TEMPLATE_DELETE', $s_hidden_fields);
					return;
				}
				
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

