<?php

/**
* phpBB Infraction System
* 
* @package phpBB3
* @copyright (c) 2011 Nelsaidi
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
				// auth => 'a_infractions_manage'
				'general'		=> array('title' => 'ACP_INFRACTION_GENERAL', 'auth' => '', 'cat' => array('ACP_INFRACTIONS')),
				'templates'		=> array('title' => 'ACP_INFRACTION_TEMPLATES', 'auth' => '', 'cat' => array('ACP_INFRACTIONS')),

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