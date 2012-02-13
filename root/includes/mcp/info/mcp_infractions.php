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

class mcp_infractions_info
{

    function module()
    {
	    return array(
			'filename'    => 'mcp_infractions',
			'title'	   => 'MCP_INFRACTIONS',
			'version'    => '1.0.0',
			'modes'	   => array(
				'view'        => array('title' => 'MCP_VIEW_INFRACTIONS', 'auth' => '', 'cat' => array('MCP_INFRACTIONS')),
				'issue'        => array('title' => 'MCP_ISSUE_INFRACTION', 'auth' => '', 'cat' => array('MCP_INFRACTIONS')),
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

	