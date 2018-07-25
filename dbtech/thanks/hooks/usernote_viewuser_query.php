<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 8)
	{
		// Disabled integration
		break;
	}

	if (!$ids = THANKS::$db->fetchAllSingleKeyed('
		SELECT usernoteid
		FROM $usernote AS usernote
		WHERE usernote.userid = ?
		ORDER BY usernote.dateline
		LIMIT :limitStart, :limitEnd
	', 'usernoteid', 'usernoteid', array(
		$userinfo['userid'],
		':limitStart' => ($limitlower - 1),
		':limitEnd' => $vbulletin->GPC['perpage']
	)))
	{
		// We're done here
		THANKS::$processed = true;
		break;
	}
	
	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($ids, 'usernote');

	// Prepare entry cache
	THANKS::processEntryCache();	
		
	// Grab the statistics stuff
	require(DIR . '/dbtech/thanks/hooks/statistics.php');
}
while (false);
?>