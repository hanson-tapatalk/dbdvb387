<?php
do
{
	if (!$review_data['reviewid'])
	{
		// Disabled integration
		break;
	}
	
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 32)
	{
		// Disabled integration
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($review_data['reviewid'], 'dbreview_review');
	
	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);

do
{
	if (!$review_data['reviewid'])
	{
		// Disabled integration
		break;
	}
	
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 2)
	{
		// Disabled integration
		break;
	}
	
	if (!$vbulletin->userinfo['userid'] AND $vbulletin->options['dbtech_thanks_hideguests'])
	{
		// Guests can't see this
		break;
	}
	
	if ($vbulletin->userinfo['dbtech_thanks_excluded'])
	{
		// We're excluded
		break;
	}
	
	if ($review_userinfo['dbtech_thanks_excluded'])
	{
		// User is excluded
		break;
	}
	
	if (!THANKS::$processed)
	{
		// Haven't processed anything
		break;
	}
	
	if ($review_data['userid'] == $vbulletin->userinfo['userid'] AND !THANKS::$entrycache['data'][$review_data['reviewid']])
	{
		// Can't click own posts
		break;
	}
	
	$post = array_merge($review_userinfo, $review_data, array('postid' => $review_data['reviewid'], 'dbtech_thanks_disabledbuttons' => 0));
	$review_data['dbtech_thanks_disabledbuttons'] = 0;
	
	// Refresh AJAX post data
	$excluded = THANKS::doButtonExclusive($post);
	
	// Extract the variables from the display processer
	list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, $post, $review_data, 'dbreview_review');
	
	if ($actions)
	{
		$templater = vB_Template::create('dbtech_thanks_postbit_entries_actions');
			$templater->register('post', 	$post);		
			$templater->register('actions', $actions);
		$actions = $templater->render();
	}
	
	// Whether we're showing these areas
	$show['dbtech_thanks_area'] = ($actions OR $entries);
	
	$extrainfo = array();
	if ($vbulletin->options['dbtech_thanks_displayextrainfo'])
	{
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			if (!$button['active'])
			{
				// Skip this button
				continue;
			}
			
			// Store buttons by varname
			$extrainfo[] = intval(THANKS::$entrycache['count'][$review_data['reviewid']][$button['varname']]) . ' ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}
	}
	
	$templater = vB_Template::create('dbtech_thanks_postbit_entries_dbreview_review');
		$templater->register('post', 		$post);
		$templater->register('show', 		$show);
		$templater->register('entries', 	$entries);
		$templater->register('actions', 	$actions);
		$templater->register('extrainfo', 	implode(', ', $extrainfo));
	$template_hook['dbtech_review_after_more_images'] .= $templater->render();
	
	if ($review_data['userid'] == $vbulletin->userinfo['userid'])
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
			'threadId' 		=> $review_data['reviewid'],
			'vbversion' 	=> intval($vbulletin->versionnumber),
			'thanksEntries' => $thanksEntries,
			'contenttype' 	=> 'dbreview_review',
			'floodTime' 	=> (int)$vbulletin->options['dbtech_thanks_floodcheck'],
		)) . ';
	', false, false);
	$footer .= '<script type="text/javascript" src="' . THANKS::jQueryPath() . '"></script>';
	$footer .= THANKS::js('', true, false);
}
while (false);

?>