<?php
do
{
	// Extract the variables from the entry processer
	list($colorOptions, $thanksEntries) = THANKS::processEntries();
	
	if (count($colorOptions))
	{
		if (intval($vbulletin->versionnumber) == 3)
		{
			$headinclude .= '<style type="text/css">' . vB_Template::create('dbtech_thanks.css')->render() . '</style>';
		}
		else
		{
			// Sneak the CSS into the headinclude
			$templater = vB_Template::create('dbtech_thanks_css');
				$templater->register('versionnumber', THANKS::$versionnumber);
			$headinclude .= $templater->render();
		}
	}
	
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
			'threadId' 		=> $thread['threadid'],
			'vbversion' 	=> intval($vbulletin->versionnumber),
			'postCount' 	=> ($thread['replycount'] + 1),
			'thanksEntries' => $thanksEntries,
			'colorOptions' 	=> $colorOptions,
			'contenttype' 	=> 'post',
			'floodTime' 	=> (int)$vbulletin->options['dbtech_thanks_floodcheck'],
		)) . ';
	', false, false);
	$footer .= '<script type="text/javascript" src="' . THANKS::jQueryPath() . '"></script>';
	$footer .= THANKS::js('', true, false);
}
while (false);
?>