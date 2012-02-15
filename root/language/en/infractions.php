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
	
	'UCP_YOUR_INFRACTIONS'			=> 'Your Infractions',
	
	'ISSUE_INFRACTION'				=> 'Issue Infraction',
	'VIEW_INFRACTION'				=> 'View Infractions',
	
	'MCP_ISSUE_INFRACTION'			=> 'Issue Infraction',
	'MCP_VIEW_INFRACTIONS'			=> 'View Infractions',
	
	'ACP_INFRACTION_TEMPLATES'		=> 'Templates',
	'ACP_INFRACTION_ADD_TEMPLATE'	=> 'Add Template',
	'ACP_INFRACTION_GENERAL'		=> 'General Settings',
	'ACP_INFRACTION_NO_TEMPLATES'	=> 'There are no templates, use the link above to add one',
	
	'INFRACTION_PM_SIG'				=> 'PM signature',
	'INFRACTION_PM_SIG_EXPLAIN' 	=> 'Signature of PM sent to users who recieve infractions, use %s to show the username of the user issuing',
	'INFRACTION_DELETE_KEEP_TIME'	=> 'Time to keep removed infractions (days)',
	'INFRACTION_DELETE_KEEP_TIME_EXPLAIN' => 'Applicable only when above is set to No, enter 0 to keep for ever',
	'INFRACTION_DELETE_TYPE'		=> 'Hard delete',
	'INFRACTION_DELETE_TYPE_EXPLAIN'=> 'If you want to keep infractions in the database for hisorical purposes select No',
	
	
	'INFRACTION_LOG_ISSUED'			=> 'Issued an infraction to %s',
	'INFRACTION_LOG_DELETED'		=> 'Deleted an infraction issued to %s',
	'INFRACTION_LOG_UPDATED'		=> 'Infraction settings changed',
	
	'INFRACTION_DELETE_CONFIRM'		=> 'Are you sure you want to remove this infraction?',
	
	'INFRACTION_TEMPLATE_NAME'		=> 'Template Name',
	'INFRACTION_NO_TEMPLATES'		=> 'No templates exist',
	'INFRACTION_TEMPLATE'			=> 'Template',
	'INFRACTION_CUSTOM'				=> 'Custom',
	
	'INFRACTION_REASON'				=> 'Reason',
	'INFRACTION_DURATION'			=> 'Duration',
	'INFRACTION_DURATION_CUSTOM'	=> 'Custom Duration',
	'INFRACTION_PERMANENT'			=> 'Permanent',
	'INFRACTION_POINTS'				=> 'Infraction Points',
	'INFRACTION_DELETE'				=> 'Remove',
	'INFRACTION_MOVE_UP'			=> 'Up',
	'INFRACTION_MOVE_DOWN'			=> 'Down',
	'INFRACTION_DAY'				=> 'Day',
	'INFRACTION_DAYS'				=> 'Days',
	'INFRACTION_WEEK'				=> 'Week',
	'INFRACTION_WEEKS'				=> 'Weeks',
	'INFRACTION_MONTH'				=> 'Month',
	'INFRACTION_MONTHS'				=> 'Months',
	'INFRACTION_REASON_DESC'		=> 'The reason presented to the user for this infraction',
	'INFRACTION_NOT_EXIST'			=> 'Infraction does not exist',
	'INFRACTION_POINTS_ISSUED'		=> 'Points Issued',
	'INFRACTION_NONE_CURRENT'		=> 'There are no current infractions',
	'INFRACTION_DATE'				=> 'Date',
	'INFRACTION_EXPIRE'				=> 'Expire Date',
	'INFRACTION_TOTAL_POINTS'		=> 'Total Points',
	'INFRACTION_NEVER'				=> 'Never',
	'INFRACTION_T_DELETE'			=> 'Delete',
	'INFRACTION_NO_REASON_NAME'		=> 'Reason or Name cannot be blank',
	'INFRACTION_EDIT'				=> 'Edit Infraction',
	
	'INFRACTION_TEMPLATE_DELETE'	=> 'Delete Template',
	'INFRACTION_TEMPLATE_DELETE_CONFIRM' => 'Are you sure you want to delete this template?',
	
	'INFRACTION_ENTER_USERNAME'		=> 'Please use this form to enter a username or use the Issue Infraction button on posts to issue for posts',
	
	'INFRACTION_OOPS'				=> 'Oops, something went wrong, go back and try again.',
	'INFRACTION_USER_NOT_EXIST'		=> 'The selected user does not exist',
	'INFRACTION_ISSUE_YOURSELF'		=> 'You cannot issue an infraction to yourself',
	'INFRACTIOS_ISSUE_GUEST'		=> 'You cannot issue an infraction to a guest user',
	
	'INFRACTION_NEGATIVE_POINTS'	=> 'You cant issue negative points, try removing infractions to reduce point count',
	'INFRACTION_INVALID_DATE'		=> 'Invalid date, the date must be after today and exist',
	
	// Note, week, days, hours, etc should be as such and english, they are parsed by PHP.
	'INFRACTION_DUR_CUSTOM_NOTE'	=> 'Enter custom duration, such as 1 week 2 days 4 hours 2 second',

	'INFRACTION_YOUR_TOTAL'			=> 'You have <strong>%d</strong> points in total',
	
	'INFRACTION_PM_SUBJECT'			=> 'You have recieved an infraction',
	// %1$s > Username, %2$s > Reason , %3$s > Infraction Points, %4$s >  Total Infraction Points, %5$s > Signature
	'INFRACTION_PM_BODY'			=>    "Dear %1\$s, \n" 
										. "You have been issued an infraction with the following details: \n"
										. "  \n"
										. "Reason: %2\$s \n"
										. "Infraction Points: %3\$s\n"
										. "  \n"
										. "Your total infraction points is now %4\$s  \n"
										. "%5\$s",

));
	

$lang = array_merge($lang, array(
	'acl_m_infractions'   		 => array('lang' => 'Can view infractions', 'cat' => 'misc'),
	'acl_m_infractions_issue'    => array('lang' => 'Can issue infractions', 'cat' => 'misc'),
	'acl_m_infractions_delete'    => array('lang' => 'Can delete infractions', 'cat' => 'misc'),
	
));

?>