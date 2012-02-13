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

class ucp_infractions_info
{

    function module()
    {
	    return array(
			'filename'    => 'ucp_infractions',
			'title'	   => 'UCP_YOUR_INFRACTIONS',
			'version'    => '1.0',
			'modes'	   => array(
				'view'        => array('title' => 'UCP_YOUR_INFRACTIONS', 'auth' => '', 'cat' => array('UCP_YOUR_INFRACTIONS')),
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

	