<?php
if (self::$db_alter->fetch_table_info('dbtech_thanks_button'))
{
	self::$db_alter->add_field(array(
		'name'       => 'disablestats_given',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'disablestats_received',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::$db_alter->add_field(array(
		'name'       => 'disableintegration',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'dbtech_thanks_button');
}

if (self::$db_alter->fetch_table_info('visitormessage'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_thanks_disabledbuttons',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));	
	self::report('Altered Table', 'visitormessage');
}
?>