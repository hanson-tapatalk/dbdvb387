<?php
if (self::$db_alter->fetch_table_info('discussion'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');	
	self::report('Altered Table', 'discussion');
}

if (self::$db_alter->fetch_table_info('groupmessage'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');	
	self::report('Altered Table', 'groupmessage');
}
?>