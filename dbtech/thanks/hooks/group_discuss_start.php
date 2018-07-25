<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 4)
	{
		// Disabled integration
		break;
	}

	if (!$ids = THANKS::$db->fetchAllSingleKeyed('
		SELECT gmid 
		FROM $groupmessage 
		WHERE discussionid = :threadId
	', 'gmid', 'gmid', array(
		':threadId' => $discussion['discussionid']
	)))
	{
		// We're done here
		THANKS::$processed = true;
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($ids, 'socialgroup');
	
	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);
?>