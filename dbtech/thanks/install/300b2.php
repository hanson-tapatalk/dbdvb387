<?php
if (self::$db_alter->fetch_table_info('discussion'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'discussion');
}

if (self::$db_alter->fetch_table_info('groupmessage'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'groupmessage');
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

if (self::$db_alter->fetch_table_info('usernote'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'usernote');
}
?>