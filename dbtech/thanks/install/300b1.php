<?php
if (self::$db_alter->fetch_table_info('blog'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'blog');
}

if (self::$db_alter->fetch_table_info('blog_text'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'blog_text');
}

if (self::$db_alter->fetch_table_info('dbtech_thanks_button'))
{
	self::$db_alter->add_field(array(
		'name'       => 'defaultbutton_attach',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'defaultbutton_content',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'disablenotifs',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'disableemails',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'dbtech_thanks_button');
}

if (self::$db_alter->fetch_table_info('dbtech_thanks_entry'))
{
	self::$db->query_write("ALTER TABLE `" . TABLE_PREFIX . "dbtech_thanks_entry` CHANGE `contenttype` `contenttype` VARCHAR( 25 ) NOT NULL");	
	self::$db_alter->add_index('userid_2', array('userid', 'contenttype', 'dateline'), '');
	self::$db_alter->add_index('entryid_3', array('varname', 'contenttype', 'entryid'), '');
	self::report('Altered Table', 'dbtech_thanks_entry');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_excluded',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'user');
}
?>