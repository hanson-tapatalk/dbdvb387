<?php
if (!$this->condition)
{
	// New user
	$this->dbobject->query_write("
		INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_thanks_statistics
			(userid)
		VALUES (
			$userid
		)
	");
}
?>