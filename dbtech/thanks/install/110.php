<?php
// Altered Tables
if (self::$db_alter->fetch_table_info('dbtech_thanks_entry'))
{
	self::$db->hide_errors();
	self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "dbtech_thanks_entry` CHANGE `entrytype` `varname` VARCHAR( 50 ) NOT NULL");
	self::$db->query_write("
		UPDATE `" . TABLE_PREFIX . "dbtech_thanks_entry` 
		SET varname = 'likes'
		WHERE varname = 'like'
	");
	self::$db->query_write("
		UPDATE `" . TABLE_PREFIX . "dbtech_thanks_entry` 
		SET varname = 'dislikes'
		WHERE varname = 'dislike'
	");
	self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "dbtech_thanks_entry` ADD `receiveduserid` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0'");
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_thanks_entry DROP INDEX userid");
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_thanks_entry DROP INDEX contentid");
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_thanks_entry DROP INDEX dateline");
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_thanks_entry ADD UNIQUE KEY `unique_entry` (`varname`,`userid`,`contenttype`,`contentid`)");	
	self::$db->show_errors();
	self::report('Altered Table', 'dbtech_thanks_entry');
}

if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->drop_field('dbtech_thanks_enablethanks');
	self::$db_alter->drop_field('dbtech_thanks_enablelike');
	self::$db_alter->drop_field('dbtech_thanks_enabledislike');
	self::report('Altered Table', 'forum');
}

if (self::$db_alter->fetch_table_info('post'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_requiredbuttons_content',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_requiredbuttons_attach',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'post');
}

if (self::$db_alter->fetch_table_info('thread'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_requiredbuttons_content',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_requiredbuttons_attach',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	/*
	self::$db_alter->drop_field('dbtech_thanks_enablethanks');
	self::$db_alter->drop_field('dbtech_thanks_enablelike');
	self::$db_alter->drop_field('dbtech_thanks_enabledislike');
	*/
	self::report('Altered Table', 'thread');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_alertcount',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'user');
}

// New Tables
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_thanks_button`
	(
		`buttonid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`varname` VARCHAR( 50 ) NOT NULL ,
		`title` VARCHAR( 50 ) NOT NULL ,
		`description` MEDIUMTEXT NOT NULL ,
		`active` ENUM( '0', '1' ) NOT NULL DEFAULT '1',
		`actiontext` VARCHAR( 250 ) NOT NULL ,
		`listtext` VARCHAR( 250 ) NOT NULL ,
		`undotext` VARCHAR( 250 ) NOT NULL ,
		`reputation` INT( 10 ) NOT NULL DEFAULT '0',
		`permissions` MEDIUMTEXT NULL DEFAULT NULL,
		`bitfield` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (`buttonid`)
	) ENGINE=MyISAM;
");
self::report('Created Table', 'dbtech_thanks_button');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "dbtech_thanks_statistics`
	(
		`userid` INT( 10 ) UNSIGNED NOT NULL ,
		`thanks_given` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`thanks_received` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`likes_given` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`likes_received` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`dislikes_given` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`dislikes_received` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (`userid`)
	) ENGINE=MyISAM;
");
self::report('Created Table', 'dbtech_thanks_statistics');

// Populated Tables
self::$db->query_write("
	INSERT IGNORE INTO `" . TABLE_PREFIX . "dbtech_thanks_button` 
		(`buttonid`, `varname`, `title`, `description`, `actiontext`, `listtext`, `undotext`, `reputation`, `bitfield`) 
	VALUES (
		1, 'thanks', 'Thanks', '\"Thank\" for a post.', 'Thank for this post', 'thanked for this post', 'Unthank', 1, 1
	)
");
self::$db->query_write("
	INSERT IGNORE INTO `" . TABLE_PREFIX . "dbtech_thanks_button` 
		(`buttonid`, `varname`, `title`, `description`, `actiontext`, `listtext`, `undotext`, `reputation`, `bitfield`) 
	VALUES(
		2, 'likes', 'Likes', '\"Likes\" for a post.', 'Like this post', 'liked this post', 'Unlike', 1, 2
	)
");
self::$db->query_write("
	INSERT IGNORE INTO `" . TABLE_PREFIX . "dbtech_thanks_button` 
		(`buttonid`, `varname`, `title`, `description`, `actiontext`, `listtext`, `undotext`, `reputation`,  `bitfield`) 
	VALUES (
		3, 'dislikes', 'Dislikes', '\"Dislike\" a post.', 'Dislike this post', 'disliked this post', 'Undislike', -2, 4
	)
");
self::report('Populated Table', 'dbtech_thanks_button');


define('CP_REDIRECT', 'thanks.php?do=finalise&version=110');
define('DISABLE_PRODUCT_REDIRECT', true);