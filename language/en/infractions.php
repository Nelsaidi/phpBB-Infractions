<?php
/** 
*
* hello_world [English]
*
* @package language
* @version $Id: v3_modules.xml 52 2007-12-09 19:45:45Z jelly_doughnut $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
                    
/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

    
$lang = array_merge($lang, array(
	'ACP_INFRACTIONS'			=> 'Infractions',
	'ACP_INFRACTION_TEMPLATES'	=> 'Templates',
	
	'MCP_INFRACTIONS'			=> 'Infractions',
	'MCP_INFRACTIONS_LIST'		=> 'List Infractions',
	'MCP_INFRACTIONS_ISSUE'		=> 'Issue Infraction',

	'L_INFRACTION_PM'			=> 'You have been issued an infraction',
	'L_INFRACTION_LOG'			=> 'Issued an infraction',
));

?>