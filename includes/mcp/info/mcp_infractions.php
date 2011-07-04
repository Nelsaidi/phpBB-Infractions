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
				'list'        => array('title' => 'MCP_INFRACTIONS_LIST', 'auth' => '', 'cat' => array('MCP_INFRACTIONS')),
				'issue'        => array('title' => 'MCP_INFRACTIONS_ISSUE', 'auth' => '', 'cat' => array('MCP_INFRACTIONS')),
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

	