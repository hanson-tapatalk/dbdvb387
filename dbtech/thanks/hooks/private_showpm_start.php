<?php
THANKS::$created['statistics'] = $db->query_first_slave("
	SELECT statistics.*
	FROM " . TABLE_PREFIX . "pm AS pm
	LEFT JOIN " . TABLE_PREFIX . "pmtext AS pmtext ON(pmtext.pmtextid = pm.pmtextid)
	LEFT JOIN " . TABLE_PREFIX . "dbtech_thanks_statistics AS statistics ON(statistics.userid = pmtext.fromuserid)
	WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.pmid=" . $vbulletin->GPC['pmid']
);
?>