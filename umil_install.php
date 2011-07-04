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
$mod_name = 'phpBB Infractions';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'infractions_version';


// The language file which will be included when installing
$language_file = 'mods/info_mcp_infractions';


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
	'0.1.0-dev' => array(

		'permission_add' => array(
			array('m_infractions_issue', 1),
		),

		'table_add' => array(
			array(phpbb_infractions, array(
				'COLUMNS' => array(
					'infraction_id' => array('TINT:0', 0, 'auto_increment'),
					'type' => array('TINT:0', 0),
					'status' => array('TINT:0', 0),
					'user_id' => array('TINT:0', 0),
					'post_id' => array('TINT:0', 0),
					'issuer_id' => array('TINT:0', 0),
					'points' => array('TINT:0', 0),
					'issue_time' => array('TINT:0', 0),
					'expire_time' => array('TINT:0', 0),
					'banned' => array('TINT:0', 0),
					'ban_id' => array('TINT:0', 0),
					'ban_duration' => array('TINT:0', 0),
					'groups' => array('STEXT', ''),
					'reason' => array('STEXT', ''),
				),

				'PRIMARY_KEY'	=> array('infraction_id', ''),

				'KEYS'		=> array(
					'infraction_id' => array('PRIMARY', array('infraction_id')),
					'user_id' => array('INDEX', array('user_id')),
					'post_id' => array('INDEX', array('post_id')),
					'expire_time' => array('INDEX', array('expire_time')),
					'' => array('INDEX', array('')),
				),
			)),

		),

		'table_column_add' => array(
			array('USERS_TABLE', 'infractions', array('TINT:0', '0')),
		),

		'module_add' => array(
			array('MCP_INFRACTIONS', 'MCP_INFRACTIONS',
				array('module_basename'	=> 'MCP_INFRACTIONS'),
			),
		),

	),
);

// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);