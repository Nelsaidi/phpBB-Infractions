<?php

class mcp_infractions_info
{

    function module()
    {
	    return array(
			'filename'    => 'mcp_infractions',
			'title'	   => 'MCP_INFRACTIONS',
			'version'    => '1.0.0',
			'modes'	   => array(
				'issue'		=> array('title' => 'Issue Infraction', 'auth' => 'm_infractions_issue', 'cat' => array('MCP_INFRACTIONS')),
				'list'		=> array('title' => 'List Infractions', 'auth' => 'm_infractions_issue', 'cat' => array('MCP_INFRACTIONS')),
				'remove'		=> array('title' => 'Remove Infraction', 'auth' => 'm_infractions_remove', 'cat' => array('MCP_INFRACTIONS')),
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

	