<?php
if (self::$db_alter->fetch_table_info('dbtech_thanks_entry'))
{
	self::$db_alter->add_index('unique_entry', array('varname', 'userid', 'contenttype', 'contentid'), '');
	self::report('Altered Table', 'dbtech_thanks_entry');
}