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

class mcp_infractions_info
{

    function module()
    {
	    return array(
			'filename'    => 'mcp_infractions',
			'title'	   => 'MCP_INFRACTIONS',
			'version'    => '1.0.0',
			'modes'	   => array(
				'view'        => array('title' => 'MCP_VIEW_INFRACTIONS', 'auth' => 'acl_m_infractions', 'cat' => array('MCP_INFRACTIONS')),
				'issue'        => array('title' => 'MCP_ISSUE_INFRACTION', 'auth' => 'acl_m_infractions_issue', 'cat' => array('MCP_INFRACTIONS')),
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


//EOF

	