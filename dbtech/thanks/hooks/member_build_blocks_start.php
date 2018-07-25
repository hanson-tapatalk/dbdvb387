<?php
if ($vbulletin->options['dbtech_thanks_enableprofile'])
{
	// Fetch the profile blocks we need
	require_once(DIR . '/dbtech/thanks/includes/class_profileblock.php');

	$blocklist['thanks'] = array(
		'class' => 'APTL_Thanks',
		'title' => $vbphrase['dbtech_thanks_post_thanks_like'],
		'options' => array(
			'perpage' => $vbulletin->GPC['perpage'],
			'pagenumber' => $vbulletin->GPC['pagenumber']
		),
		'hook_location' => (intval($vbulletin->versionnumber) == 3 ? 'profile_left_last' : 'profile_tabs_last')
	);

	$show['vb4compat'] = version_compare($vbulletin->versionnumber, '4.0.8', '>=');
	$headinclude .= vB_Template::create('dbtech_thanks_member_css')->render();
}
?>