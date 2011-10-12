<?php
/**
 *
 * @author Nelsaidi 
 * @version $Id$
 * @copyright (c) 2011 Nelsaidi
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	'1.0.1'	=> array(
		
		'table_column_update' => array(
			array('phpbb_infraction_templates', 'duration', array('VARCHAR', 255)),
		),
			
		'module_remove' => array(
			array('mcp', '', 'MCP_WARN'),
		),	
	)
	
	'1.0.0' => array(

		'permission_add' => array(
			array('m_infractions_issue', 1),
			array('m_infractions', 1),
			array('m_infractions_delete', 1),
		),

		'permission_set' => array(
			array('ROLE_MOD_STANDARD', 'm_infractions_issue'),
			array('ROLE_MOD_STANDARD', 'm_infractions'),
			
			array('ROLE_MOD_FULL', 'm_infractions_issue'),
			array('ROLE_MOD_FULL', 'm_infractions'),
			array('ROLE_MOD_FULL', 'm_infractions_delete'),
		),

		'table_add' => array(
			array('phpbb_infractions', array(
				'COLUMNS' => array(
					'infraction_id' 	=> array('INT:11', null, 'auto_increment'),
					// 'type' 			=> array('BOOL', 0),
					'void'			=> array('BOOL', 0),
					'user_id' 		=> array('INT:11', 0),
					'post_id' 		=> array('INT:11', 0),
					'forum_id' 		=> array('INT:11', 0),
					'issuer_id' 		=> array('INT:11', 0),
					'infraction_points' => array('INT:11', 0),
					'issue_time' 		=> array('INT:11', 0),
					'expire_time' 		=> array('INT:11', 0),
					'duration' 		=> array('INT:11', 0),
					'reason' 			=> array('TEXT', ''),
					
					// In next version
					// 'banned' 			=> array('BOOL', 0),
					// 'ban_id' 			=> array('INT:11', 0),
					// 'ban_duration' 	=> array('INT:11', 0),
					// 'groups'			=> array('TEXT', ''),
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
					'position' 		=> array('USINT', 0),
					'name' 			=> array('VCHAR', ''),
					'reason' 			=> array('TEXT', ''),
					'infraction_points' => array('INT:11', 0),
					'duration' 		=> array('INT:11', 0),
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
		),

		'module_add' => array(
			array('mcp', '', 'MCP_INFRACTIONS'),
			 
			array('acp', 'ACP_CAT_USERGROUP', 'ACP_INFRACTIONS'),
			 
			array('mcp', 'MCP_INFRACTIONS',
				array('module_basename'	=> 'infractions'),
			),
			
			array('acp', 'ACP_INFRACTIONS',
				array('module_basename'	=> 'infractions'),
			),
		),

	),
);

// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);