<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 2)
	{
		// Disabled integration
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($id, 'dbgallery_image');
	
	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);
?>