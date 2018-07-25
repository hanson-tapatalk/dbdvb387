<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 4)
	{
		// Disabled integration
		break;
	}
	
	if ($bloginfo['userid'] == $vbulletin->userinfo['userid'])
	{
		// Can't click own posts
		break;
	}	
	
	// Extract the variables from the entry processer
	list($colorOptions, $thanksEntries) = THANKS::processEntries();
	
	// Begin list of JS phrases
	$jsphrases = array(
		'dbtech_thanks_must_wait_x_seconds'			=> $vbphrase['dbtech_thanks_must_wait_x_seconds'],
	);
	
	// Escape them
	THANKS::jsEscapeString($jsphrases);
	
	$escapedJsPhrases = '';
	foreach ($jsphrases as $varname => $value)
	{
		// Replace phrases with safe values
		$escapedJsPhrases .= "vbphrase['$varname'] = \"$value\"\n\t\t\t\t\t";
	}
	
	$footer .= THANKS::js($escapedJsPhrases . '
		var thanksOptions = ' . THANKS::encodeJSON(array(
			'threadId' 		=> $discussion['discussionid'],
			'vbversion' 	=> intval($vbulletin->versionnumber),
			'thanksEntries' => $thanksEntries,
			'contenttype' 	=> 'socialgroup',
			'floodTime' 	=> (int)$vbulletin->options['dbtech_thanks_floodcheck'],
		)) . ';
	', false, false);
	$footer .= '<script type="text/javascript" src="' . THANKS::jQueryPath() . '"></script>';
	$footer .= THANKS::js('', true, false);
}
while (false);
?>