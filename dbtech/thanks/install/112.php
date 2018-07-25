<?php
if (self::$db_alter->fetch_table_info('dbtech_thanks_button'))
{
	self::$db_alter->add_field(array(
		'name'       => 'exclusivity',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));		
	self::report('Altered Table', 'dbtech_thanks_button');
}