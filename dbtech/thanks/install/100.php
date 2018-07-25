<?php
// Altered Tables
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanksadminperms',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'administrator');
}

if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db->hide_errors();	
	self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "forum` ADD `dbtech_thanks_enablethanks` ENUM( '0', '1' ) NOT NULL DEFAULT '1'");
	self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "forum` ADD `dbtech_thanks_enablelike` ENUM( '0', '1' ) NOT NULL DEFAULT '1'");
	self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "forum` ADD `dbtech_thanks_enabledislike` ENUM( '0', '1' ) NOT NULL DEFAULT '1'");
	self::$db->show_errors();
	self::report('Altered Table', 'forum');
}

if (self::$db_alter->fetch_table_info('post'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_cache',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	self::report('Altered Table', 'post');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_points',
		'type'       => 'int',
		'length'     => '10',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_settings',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'user');
}

if (self::$db_alter->fetch_table_info('usergroup'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thankspermissions',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'usergroup');
}

// New Tables
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_thanks_entry`
	(
		`entryid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`entrytype` enum('thanks','like','dislike') NOT NULL,
		`userid` int(10) unsigned NOT NULL DEFAULT '0',
		`contenttype` enum('post') NOT NULL,
		`contentid` int(10) unsigned NOT NULL DEFAULT '0',
		`dateline` int(10) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`entryid`),
		KEY `userid` (`userid`),
		KEY `contentid` (`contentid`),
		KEY `dateline` (`dateline`)
	) ENGINE=MyISAM;
");
self::report('Created Table', 'dbtech_thanks_entry');
?>