<?php
if (self::$db_alter->fetch_table_info('dbtech_thanks_recententry'))
{
	self::$db_alter->add_index('dateline', array('dateline'), '');
	self::report('Altered Table', 'dbtech_thanks_recententry');
}