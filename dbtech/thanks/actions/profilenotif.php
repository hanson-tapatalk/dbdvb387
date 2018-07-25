<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

if (!$vbulletin->userinfo['userid'])
{
	// Git oot
	print_no_permission();
}

// Reset tag count
THANKS::$db->update('user', array('dbtech_thanks_alertcount' => 0), 'WHERE userid = ' . $vbulletin->db->sql_prepare($vbulletin->userinfo['userid']));

// Do ze redirect
exec_header_redirect('member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $vbulletin->userinfo['userid'] . '&tab=thanks');