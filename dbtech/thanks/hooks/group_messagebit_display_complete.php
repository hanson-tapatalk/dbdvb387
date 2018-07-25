<?php
do
{
	if ($this->registry->options['dbtech_thanks_disabledintegration'] & 4)
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
	
	if ($this->item['dbtech_thanks_excluded'])
	{
		// User is excluded
		break;
	}
	
	if (!THANKS::$processed)
	{
		// Haven't processed anything
		break;
	}
	
	if ($this->item['userid'] == $this->registry->userinfo['userid'] AND !THANKS::$entrycache['data'][$this->item['gmid']])
	{
		// Can't click own posts
		break;
	}
	
	$post = array_merge($this->item, array('postid' => $this->item['gmid'], 'dbtech_thanks_disabledbuttons' => 0));
	$this->item['dbtech_thanks_disabledbuttons'] = 0;
	
	// Refresh AJAX post data
	$excluded = THANKS::doButtonExclusive($post);
	
	// Extract the variables from the display processer
	list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, $post, $this->item, 'socialgroup');
	
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
			$extrainfo[] = intval(THANKS::$entrycache['count'][$this->item['gmid']][$button['varname']]) . ' ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}
	}
	
	$templater = vB_Template::create('dbtech_thanks_postbit_entries_socialgroup');
		$templater->register('post', 		$post);
		$templater->register('show', 		$show);
		$templater->register('entries', 	$entries);
		$templater->register('actions', 	$actions);
		$templater->register('extrainfo', 	implode(', ', $extrainfo));
	$this->item['message'] .= $templater->render();
}
while (false);
?>