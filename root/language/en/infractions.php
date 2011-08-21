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
	'MCP_INFRACTIONS_VIEW'		=> 'View Infractions',
	'MCP_INFRACTIONS_ISSUE'		=> 'Issue Infraction',

	'INFRACTION_PM_BODY'		=> "Dear %s \nYou have been issued an infraction with the following details:\n\nReason: %s\nInfraction Points: %s\n\nYour total infraction points is now %s",
	'INFRACTION_PM_SUBJECT'		=> 'You have been issued an infraction ',
	
	'INFRACTION_LOG'			=> 'Issued an infraction',
	
	'ISSUE_INFRACTION'			=> 'Issue Infraction',
	'INFRACTION_POINTS'			=> 'Infraction Points',
	
	'acl_m_infractions'   		 => array('lang' => 'Can view infractions', 'cat' => 'misc'),
	'acl_m_infractions_issue'    => array('lang' => 'Can issue infractions', 'cat' => 'misc'),
	
));

?>