<?php
do
{
	if (defined('VB_API') AND VB_API === true)
	{
		// We're in the mobile app
		break;
	}

	if ($this->registry->options['dbtech_thanks_disabledintegration'] & 16)
	{
		// Disabled integration
		break;
	}
	
	if (!$this->registry->userinfo['userid'] AND $this->registry->options['dbtech_thanks_hideguests'])
	{
		// Guests can't see this
		break;
	}
	
	if ($this->registry->userinfo['dbtech_thanks_excluded'])
	{
		// We're excluded
		break;
	}
	
	if ($response['dbtech_thanks_excluded'])
	{
		// User is excluded
		break;
	}
	
	if (!THANKS::$processed)
	{
		// Haven't processed anything
		break;
	}
	
	if ($response['userid'] == $this->registry->userinfo['userid'] AND !THANKS::$entrycache['data'][$message['vmid']])
	{
		// Can't click own posts
		break;
	}
	
	$post = array_merge($message, array('postid' => $message['vmid'], 'dbtech_thanks_disabledbuttons' => 0));
	$message['dbtech_thanks_disabledbuttons'] = 0;
	
	// Refresh AJAX post data
	$excluded = THANKS::doButtonExclusive($post);
	
	$show['thanks_nouserinfo'] = true;
	
	// Extract the variables from the display processer
	list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, $post, $message, 'visitormessage');
	
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
	if ($this->registry->options['dbtech_thanks_displayextrainfo'])
	{				
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			if (!$button['active'])
			{
				// Skip this button
				continue;
			}
			
			// Store buttons by varname
			$extrainfo[] = intval(THANKS::$entrycache['count'][$message['vmid']][$button['varname']]) . ' ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}
	}
	
	$templater = vB_Template::create('dbtech_thanks_postbit_entries_visitormessage');
		$templater->register('post', 		$post);
		$templater->register('show', 		$show);
		$templater->register('entries', 	$entries);
		$templater->register('actions', 	$actions);
		$templater->register('extrainfo', 	implode(', ', $extrainfo));
	$message['message'] .= $templater->render();
}
while (false);
?>