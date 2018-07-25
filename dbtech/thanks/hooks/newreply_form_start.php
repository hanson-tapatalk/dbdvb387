<?php
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

$vbulletin->GPC['dbtech_thanks_in_posting'] = false;
foreach (THANKS::$cache['button'] as $button)
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
	
	// We're doing something fancy
	$vbulletin->GPC['dbtech_thanks_in_posting'] = true;
}

if ($postinfo)
{
	// Store this
	$vbulletin->GPC['dbtech_thanks_postinfo'] = $postinfo;
}
?>