<?php
/**
 *
 * @author Nelsaidi () 
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
	'0.0.1' => array(

		'permission_add' => array(
			array('m_infractions_issue', 1),
		),

		'table_add' => array(
			array(phpbb_infractions, array(
				'COLUMNS' => array(
					'infraction_id' => array('TIMESTAMP', '', 'auto_increment'),
					'user_id' => array('TIMESTAMP', ''),
					'post_id' => array('TIMESTAMP', ''),
					'issuer_id' => array('TIMESTAMP', ''),
					'points' => array('UINT', ''),
					'duration' => array('UINT', ''),
					'issue_time' => array('TIMESTAMP', ''),
					'expire_time' => array('TIMESTAMP', ''),
					'reason' => array('TEXT', ''),
					'banned' => array('BOOL', ''),
					'ban_duration' => array('UINT', ''),
					'ban_roneas' => array('TEXT', ''),
				),

				'PRIMARY_KEY'	=> array('infraction_id', ''),

				'KEYS'		=> array(
					'infraction_id' => array('PRIMARY', array('infraction_id')),
					'user_id' => array('INDEX', array('user_id', 'expire_time')),
					'post_id' => array('INDEX', array('post_id')),
					'' => array('INDEX', array('')),
				),
			)),

		),

		'table_column_add' => array(
			array('TABLE_USERS', 'infraction_points', array('UINT', '0')),
		),

		'config_add' => array(
			array('infractions_config_var', '1', 0),
		),

		'module_add' => array(
			array('mcp_something_', 'mcp_somehing_else',
				array('module_basename'	=> 'mcp_dark_side'),
			),
		),

	),
);

// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);