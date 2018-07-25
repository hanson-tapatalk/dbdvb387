<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 16)
	{
		// Disabled integration
		break;
	}

	if (!$ids = THANKS::$db->fetchAllSingleKeyed('
		SELECT vmid 
		FROM $visitormessage 
		WHERE userid = :userId 
			AND state = ?
	', 'vmid', 'vmid', array(
		':userId' 	=> $vbulletin->GPC['userid'],
		'visible'
	)))
	{
		// We're done here
		THANKS::$processed = true;
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($ids, 'visitormessage');
	
	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);
?>