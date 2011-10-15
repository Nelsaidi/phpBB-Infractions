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
	'INFRACTIONS'					=> 'Infractions',
	'ACP_INFRACTIONS'				=> 'Infractions',
	'MCP_INFRACTIONS'				=> 'Infractions',
	
	'ISSUE_INFRACTION'				=> 'Issue Infraction',
	'VIEW_INFRACTIONS'				=> 'View Infractions',
	
	'ACP_INFRACTION_TEMPLATES'		=> 'Templates',
	'ACP_INFRACTION_ADD_TEMPLATE'	=> 'Add Template',
	'ACP_INFRACTION_GENERAL'		=> 'General Settings',
	
	'INFRACTION_LOG_ISSUED'			=> 'Issued an infraction',
	
	'INFRACTION_TEMPLATE_NAME'		=> 'Template Name',
	'INFRACTION_NO_TEMPLATES'		=> 'No templates exist',
	
	'INFRACTION_REASON'				=> 'Reason',
	'INFRACTION_DURATION'			=> 'Duration',
	'INFRACTION_DURATION_CUSTOM'	=> 'Custom Duration',
	'INFRACTION_PERMANENT'			=> 'Permanent',
	'INFRACTION_POINTS'				=> 'Infraction Points',
	'INFRACTION_DELETE'
	'INFRACTION_MOVE_UP'
	'INFRACTION_MOVE_DOWN'
	'INFRACTION_DAY'				=> 'Day',
	'INFRACTION_DAYS'				=> 'Days',
	'INFRACTION_REASON_DESC'		=> 'The reason presented to the user for this infraction',
	'INFRACTION_NOT_EXIST'			=> 'Infraction does not exist',
	
	'INFRACTION_PM_SUBJECT'			=> 'You have recieved an infraction',
	// %1$s > Username, %2$s > Reason , %3$s > Infraction Points, %4$s >  Total Infraction Points, %5$s > Signature
	'INFRACTION_PM_BODY'			=>    'Dear %1$s, \n' 
										. 'You have been issued an infraction with the following details: \n'
										. '  \n'
										. 'Reason: %2$s \n'
										. 'Infraction Points: %3$s\n'
										. '  \n'
										. 'Your total infraction points is now %4$s  \n'
										. '%5$s',
);
	

$lang = array_merge($lang, array(
	'ACP_INFRACTIONS'			=> 'Infractions',
	
	
	'MCP_INFRACTIONS'			=> 'Infractions',
	'MCP_INFRACTIONS_VIEW'		=> 'View Infractions',
	'MCP_INFRACTIONS_ISSUE'		=> 'Issue Infraction',
	
	'VIEW_INFRACTIONS'		=> 'View Infractions',

	'INFRACTION_PM_BODY'		=> "Dear %s \nYou have been issued an infraction with the following details:\n\nReason: %s\nInfraction Points: %s\n\nYour total infraction points is now %s",
	'INFRACTION_PM_SUBJECT'		=> 'You have been issued an infraction ',
	
	'INFRACTION_LOG'			=> 'Issued an infraction',
	
	'ISSUE_INFRACTION'			=> 'Issue Infraction',
	'INFRACTION_POINTS'			=> 'Infraction Points',
	
	'acl_m_infractions'   		 => array('lang' => 'Can view infractions', 'cat' => 'misc'),
	'acl_m_infractions_issue'    => array('lang' => 'Can issue infractions', 'cat' => 'misc'),
	'acl_m_infractions_delete'    => array('lang' => 'Can delete infractions', 'cat' => 'misc'),
	
));

?>