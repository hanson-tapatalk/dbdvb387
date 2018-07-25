<?php
if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_firstpostonly',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'forum');
}

if (self::$db_alter->fetch_table_info('dbtech_thanks_button'))
{
	self::$db_alter->add_field(array(
		'name'       => 'displayorder',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '10'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'minposts',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'clicksperday',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'postfont',
		'type'       => 'mediumtext',
		'null'       => true,	// True = NULL, false = NOT NULL
		'default'    => NULL
	));
	self::report('Altered Table', 'dbtech_thanks_button');
}

if (self::$db_alter->fetch_table_info('dbtech_thanks_entry'))
{
	self::$db_alter->drop_index('unique_entry');
	self::$db_alter->drop_index('contentid');
	self::$db_alter->add_index('entryid', 			array('userid', 'entryid'));
	self::$db_alter->add_index('entryid_2', 		array('receiveduserid', 'entryid'));
	self::$db_alter->add_index('userid', 			array('userid'));
	self::$db_alter->add_index('receiveduserid', 	array('receiveduserid'));
	self::report('Altered Table', 'dbtech_thanks_entry');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_settings2',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'user');
}

if (file_exists(DIR . '/includes/class_block.php'))
{
	require_once(DIR . '/includes/class_block.php');
	$blockmanager = vB_BlockManager::create(self::$vbulletin);
	$blockmanager->reloadBlockTypes(true);
	self::report('Rebuilt Data', 'Forum Blocks');
}