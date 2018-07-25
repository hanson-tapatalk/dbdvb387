<?php
if ($vbulletin->GPC['ajax'] OR $vbulletin->GPC['quickeditnoajax'])
{
	// We're not doing anything while ajax is on
}
else
{
	if (!THANKS::$isPro)
	{
		// Lite-only shit
		$parentlist = explode(',', $foruminfo['parentlist']);
		if ($parentlist[0] == -1)
		{
			// This forum
			$noticeforum = $foruminfo['forumid'];		
		}
		else
		{
			$key = (count($parentlist) - 2);
			$noticeforum = $parentlist["$key"];
		}
	}
	else
	{
		// This forum
		$noticeforum = $foruminfo['forumid'];
	}
	
	// Grab our two button types
	$vbulletin->input->clean_array_gpc('p', array(
		'dbtech_thanks_disabledbuttons' => TYPE_ARRAY,
		'dbtech_thanks_requiredbuttons' => TYPE_ARRAY,
	));
	
	$vbulletin->GPC['dbtech_thanks_postinfo'] = array();
	foreach (THANKS::$cache['button'] as $buttonid => $button)
	{
		if (!$button['active'])
		{
			// Inactive button
			continue;
		}
		
		if ((int)$vbulletin->forumcache["$noticeforum"]['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
		{
			// Button was disabled for this forum
			continue;
		}
		
		if (THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'candisableclick') AND THANKS::$isPro)
		{
			$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_disabledbuttons'] += ($vbulletin->GPC['dbtech_thanks_disabledbuttons']["$buttonid"] ? $button['bitfield'] : 0);
		}
		
		if (THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'canreqclick'))
		{
			foreach (array('content', 'attach') as $type)
			{
				if ($vbulletin->GPC['dbtech_thanks_disabledbuttons']["$buttonid"])
				{
					// We obviously can't require a button that's been disabled
					continue 2;
				}
				
				// Set the new postinfo
				$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_requiredbuttons_' . $type] += ($vbulletin->GPC['dbtech_thanks_requiredbuttons']["$type"]["$buttonid"] ? $button['bitfield'] : 0);
			}
		}
	}
	
	// Set the data menger
	$dataman->set('dbtech_thanks_disabledbuttons', 			$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_disabledbuttons']);
	$dataman->set('dbtech_thanks_requiredbuttons_attach', 	$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_requiredbuttons_attach']);	
	$dataman->set('dbtech_thanks_requiredbuttons_content', 	$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_requiredbuttons_content']);	
}
?>