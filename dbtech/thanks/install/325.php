<?php
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_thanks_entrycache`
	(
		`varname` VARCHAR( 50 ) NOT NULL ,
		`contenttype` VARCHAR( 25 ) NOT NULL ,
		`contentid` INT( 10 ) UNSIGNED NOT NULL ,
		`data` MEDIUMBLOB,
		PRIMARY KEY (`varname`, `contenttype`, `contentid`)
	) ENGINE=" . self::$hightrafficengine . ";
");
self::report('Created Table', 'dbtech_thanks_entrycache');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_thanks_recententry`
	(
		`entryid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`varname` varchar(50) NOT NULL,
		`userid` int(10) unsigned NOT NULL DEFAULT '0',
		`contenttype` varchar(25) NOT NULL,
		`contentid` int(10) unsigned NOT NULL DEFAULT '0',
		`dateline` int(10) unsigned NOT NULL DEFAULT '0',
		`receiveduserid` int(10) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`entryid`)
	) ENGINE=" . self::$hightrafficengine . ";
");
self::report('Created Table', 'dbtech_thanks_recententry');

if (self::$db_alter->fetch_table_info('dbtech_thanks_entry'))
{
	self::$db_alter->add_index('content', array('contentid', 'contenttype'), '');
	self::report('Altered Table', 'dbtech_thanks_entry');
}

if (self::$db_alter->fetch_table_info('dbtech_thanks_entrycache'))
{
	self::$db_alter->add_index('content', array('contentid', 'contenttype'), '');
	self::report('Altered Table', 'dbtech_thanks_entrycache');
}

define('CP_REDIRECT', 'thanks.php?do=finalise&version=325');
define('DISABLE_PRODUCT_REDIRECT', true);