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

	'INFRACTION_PM_BODY'		=> 'You have been issued %d infraction points for %s',
	'INFRACTION_PM_SUBJECT'		=> 'You have been issued an infraction ',
	
	'INFRACTION_LOG'			=> 'Issued an infraction',
	
	'L_ISSUE_INFRACTION'		=> 'Issue Infraction',
	'L_INFRACTION_POINTS'		=> 'Infraction Points',
	
	
));

?>