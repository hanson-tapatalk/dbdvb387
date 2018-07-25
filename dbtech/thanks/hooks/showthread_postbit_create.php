<?php
global $vbulletin;

if (isset($threadinfo['forumid']))
{
	// Ensure this is set
	$forumid = $threadinfo['forumid'];
}
else
{
	// Ensure this is set
	$forumid = $thread['forumid'];
}

$noticeforuminfo = $vbulletin->forumcache[$forumid];
if (!THANKS::$isPro)
{
	// Lite-only shit
	$parentlist = explode(',', $noticeforuminfo['parentlist']);
	if ($parentlist[0] == -1)
	{
		// This forum
		$noticeforum = $noticeforuminfo['forumid'];		
	}
	else
	{
		$key = (count($parentlist) - 2);
		$noticeforum = $parentlist[$key];
	}
}
else
{
	// This forum
	$noticeforum = $forumid;
}

$threshold = $vbulletin->forumcache[$noticeforum]['dbtech_thanks_hide_threshold'];

// Do replacements
$formula = preg_replace("/\[(\w+)\]/sU", 'count(THANKS::$entrycache[\'data\'][' . $post['postid'] . '][$1])', $vbulletin->options['dbtech_thanks_postweight']);

if ($formula)
{
	// Eval the formula
	ob_start();
		eval('$weight = ' . $formula . ';');
	ob_end_clean();
}

if ($weight === NULL)
{
	// Just in case
	$weight = 0;
}

if ($threshold AND $weight >= $threshold)
{
	// This post has been downranked
	$fetchtype = 'dbtech_thanks_downranked';
}
?>