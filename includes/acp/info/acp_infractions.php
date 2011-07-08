<?php

/**
* @package module_install
*/
class acp_board_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_infraction',
			'title'		=> 'ACP_INFRACTIONS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'templates'		=> array('title' => 'ACP_INFRACTION_TEMPLATES', 'auth' => '', 'cat' => array('ACP_INFRACTIONS')),
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

?>