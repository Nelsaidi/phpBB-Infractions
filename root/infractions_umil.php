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
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();


if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'Infractions';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'infractions_version';


// The language file which will be included when installing
$language_file = 'infractions';


/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	'1.0' => array(

		'permission_add' => array(
			array('m_infractions_issue', true),
			array('m_infractions', true),
			array('m_infractions_delete', true),
			array('a_infractions_manage', true),
		),

		'permission_set' => array(
			array('ROLE_MOD_STANDARD', 'm_infractions_issue'),
			array('ROLE_MOD_STANDARD', 'm_infractions'),
			
			array('ROLE_MOD_FULL', 'm_infractions_issue'),
			array('ROLE_MOD_FULL', 'm_infractions'),
			array('ROLE_MOD_FULL', 'm_infractions_delete'),
			
			array('ROLE_ADMIN_FULL', 'a_infractions_manage'),
		),

		'table_add' => array(
			array('phpbb_infractions', array(
				'COLUMNS' => array(
					'infraction_id' 	=> array('INT:11', null, 'auto_increment'),
					'void'				=> array('BOOL', 0),
					'user_id' 			=> array('INT:11', 0),
					'post_id' 			=> array('INT:11', 0),
					'forum_id' 			=> array('INT:11', 0),
					'issuer_id' 		=> array('INT:11', 0),
					'infraction_points' => array('INT:11', 0),
					'issue_time' 		=> array('INT:11', 0),
					'expire_time' 		=> array('INT:11', 0),
					'deleted_time' 		=> array('INT:11', 0),
					'duration' 			=> array('INT:11', 0),
					'reason' 			=> array('TEXT', ''),
				),

				'PRIMARY_KEY'	=> 'infraction_id',

				'KEYS'		=> array(
					'expire_time' 	=> array('INDEX', 'expire_time'),
					'user_id'		=> array('INDEX', 'user_id'),
				),
			)),

			array('phpbb_infraction_templates', array(
				'COLUMNS' => array(
					'template_id'		=> array('INT:11', null, 'auto_increment'),
					'position' 			=> array('USINT', 0),
					'name' 				=> array('VCHAR', ''),
					'reason' 			=> array('TEXT', ''),
					'infraction_points' => array('INT:11', 0),
					'duration' 			=> array('VCHAR', ''),
				),

				'PRIMARY_KEY'	=> 'template_id',

				'KEYS'		=> array(
					'position' => array('INDEX', 'position'),
				),
			)),

		),

		'table_column_add' => array(
			array('phpbb_users', 'infraction_points', array('TINT:11', '0')),
		),

		'config_add' => array(
			array('infractions_installed', '1', 0),
			array('infractions_pm_sig', '', 0),
			array('infractions_hard_delete', '1', 0),
			array('infractions_deleted_keep_time', '0', 0),
		),

		'module_add' => array(
			array('mcp', '', 'MCP_INFRACTIONS'),
			
			array('ucp', '', 'UCP_YOUR_INFRACTIONS'),
			 
			array('acp', 'ACP_CAT_USERGROUP', 'ACP_INFRACTIONS'),
			 
			array('mcp', 'MCP_INFRACTIONS',
				array('module_basename'	=> 'infractions'),
			),
			
			array('acp', 'ACP_INFRACTIONS',
				array('module_basename'	=> 'infractions'),
			),
			
			array('ucp', 'UCP_YOUR_INFRACTIONS',
				array('module_basename'	=> 'infractions'),
			),
		),

		
	),
);

// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);