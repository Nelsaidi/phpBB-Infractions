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