<?php
// Delete all ignored users from this user
$this->dbobject->query_write("
	DELETE FROM " . TABLE_PREFIX . "dbtech_thanks_entry
	WHERE userid = " . $this->existing['userid']
);
$this->dbobject->query_write("
	DELETE FROM " . TABLE_PREFIX . "dbtech_thanks_statistics
	WHERE userid = " . $this->existing['userid']
);
?>