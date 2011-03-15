<?php

/** 
*
* install script to set up permission options in the db for foo mod
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/

// initialize the page
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);


// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();


// Setup $auth_admin class so we can add tabulated survey permission options
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
$auth_admin = new auth_admin();

// Add foo permissions as local permissions
// (you could instead make them global permissions by making the obvious changes below)
$auth_admin->acl_add_option(array(
    'local'        => array('m_infractions_issue'),
    'global'    => array()
));

$sql = "CREATE TABLE `euk`.`phpbb_infractions` (
`infraction_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`infraction_type` INT( 11 ) NOT NULL ,
`infraction_status` INT( 1 ) NOT NULL ,
`user_id` INT( 11 ) NOT NULL ,
`post_id` INT( 11 ) NOT NULL ,
`issuer_id` INT( 11 ) NOT NULL ,
`points` INT( 11 ) NOT NULL ,
`duration` INT( 11 ) NOT NULL ,
`issue_time` INT( 11 ) NOT NULL ,
`expire_time` INT( 11 ) NOT NULL ,
`reason` TEXT NOT NULL ,
`banned` INT( 1 ) NOT NULL ,
`ban_id` INT( 11 ) NOT NULL ,
`ban_duration` INT( 11 ) NOT NULL ,
`ban_reason` INT( 11 ) NOT NULL ,
`new_groups` TEXT NOT NULL ,
INDEX ( `infraction_status` , `user_id` , `post_id` , `expire_time` )
) ENGINE = MYISAM ;"

$sql = "ALTER TABLE `phpbb_infractions` CHANGE `infraction_id` `infraction_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
CHANGE `type` `type` INT( 11 ) NOT NULL ,
CHANGE `status` `status` INT( 1 ) NOT NULL DEFAULT '0',
CHANGE `user_id` `user_id` INT( 11 ) NOT NULL ,
CHANGE `post_id` `post_id` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `issuer_id` `issuer_id` INT( 11 ) NOT NULL ,
CHANGE `points` `points` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `duration` `duration` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `issue_time` `issue_time` INT( 11 ) NOT NULL ,
CHANGE `expire_time` `expire_time` INT( 11 ) NOT NULL ,
CHANGE `reason` `reason` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `banned` `banned` INT( 1 ) NOT NULL DEFAULT '0',
CHANGE `ban_id` `ban_id` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `ban_duration` `ban_duration` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `ban_reason` `ban_reason` TEXT NOT NULL DEFAULT '',
CHANGE `new_groups` `new_groups` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''"



