<?php
$fetchtype = 'dbtech_thanks';
if ($fetchtype == 'dbtech_thanks')
{
	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($postid, 'post');
	
	// Prepare entry cache
	THANKS::processEntryCache();	
	
	// Lazy :p
	$fetchtype = 'post';
	
	// This post requires something we aint done
	unset($postbit_obj);
	
	if (intval($vbulletin->versionnumber) > 3 AND !class_exists('vB'))
	{
		// For some reason this is needed
		require_once(DIR . '/vb/vb.php');
	}
	
	$postbit_obj =& $postbit_factory->fetch_postbit('dbtech_thanks');
	$postbit_obj->highlight =& $replacewords;
	$postbit_obj->cachable = (!$post['pagetext_html'] AND $vbulletin->options['cachemaxage'] > 0 AND (TIMENOW - ($vbulletin->options['cachemaxage'] * 60 * 60 * 24)) <= $threadinfo['lastpost']);
}
?>