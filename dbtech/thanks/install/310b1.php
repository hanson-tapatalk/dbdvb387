<?php
if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_hide_threshold',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '10'
	));	
	self::report('Altered Table', 'forum');
}

if (self::$db_alter->fetch_table_info('dbtech_thanks_button'))
{
	self::$db_alter->add_field(array(
		'name'       => 'image',
		'type'       => 'varchar',
		'length'     => '100',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));	
	self::$db_alter->add_field(array(
		'name'       => 'image_unclick',
		'type'       => 'varchar',
		'length'     => '100',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => ''
	));	
	self::report('Altered Table', 'dbtech_thanks_button');
}

require_once(DIR . '/includes/adminfunctions_forums.php');
build_forum_permissions();
?>