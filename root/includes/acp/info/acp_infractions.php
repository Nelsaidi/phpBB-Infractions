<?php

/**
* phpBB Infraction System
* 
* @package phpBB3
* @copyright (c) 2012 Nelsaidi
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package module_install
*/
class acp_infractions_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_infractions',
			'title'		=> 'ACP_INFRACTIONS',
			'version'	=> '1.0',
			'modes'		=> array(
				'general'		=> array('title' => 'ACP_INFRACTION_GENERAL', 'auth' => 'acl_a_infractions_manage', 'cat' => array('ACP_INFRACTIONS')),
				'templates'		=> array('title' => 'ACP_INFRACTION_TEMPLATES', 'auth' => 'acl_a_infractions_manage', 'cat' => array('ACP_INFRACTIONS')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>