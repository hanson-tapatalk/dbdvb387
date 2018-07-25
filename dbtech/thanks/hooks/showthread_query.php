<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 64)
	{
		// Disabled integration
		break;
	}

	if ($threadedmode != 0)
	{
		// $cache_postids
		$post_ids = preg_split('#\s*,\s*#si', $cache_postids, -1, PREG_SPLIT_NO_EMPTY);
	}
	else
	{
		if (intval($vbulletin->versionnumber) == 3)
		{
			// $ids
			$post_ids = preg_split('#\s*,\s*#si', $ids, -1, PREG_SPLIT_NO_EMPTY);
		}
		else
		{
			// $ids
			$post_ids = $ids;
		}
	}

	if (!$post_ids)
	{
		// We're done here
		THANKS::$processed = true;
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($post_ids, 'post');
	
	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);


/*
$cacheResult = THANKS_CACHE::read('showthread', 'thread.' . $thread['threadid'] . '.' . dechex(crc32($post_ids)));

if (!is_array($cacheResult))
{
}
else
{
	// Set the entry cache
	THANKS::$entrycache = $cacheResult;

	// Set processed
	THANKS::$processed = true;		
}
*/

// Grab the statistics stuff
require(DIR . '/dbtech/thanks/hooks/statistics.php');
?>