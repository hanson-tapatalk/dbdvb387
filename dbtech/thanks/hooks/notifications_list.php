<?php
if (
	$vbulletin->options['dbtech_thanks_notifications'] AND 
	$vbulletin->options['dbtech_thanks_enableprofile'] AND 
	!($vbulletin->userinfo['dbtech_thanks_settings2'] & 1)
)
{
	$notifications['dbtech_thanks_alertcount'] = array(
		'phrase' => $vbphrase['dbtech_thanks_new_post_thanks_alerts'],
		'link'   => 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=profilenotif',
		'order'  => 110
	);
}
?>