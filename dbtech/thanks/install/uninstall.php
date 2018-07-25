<?php
// Reverted Tables
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->drop_field('dbtech_thanksadminperms');
	self::report('Reverted Table', 'administrator');
}

// Clean up
self::$db->query_write("DELETE FROM `" . TABLE_PREFIX . "datastore` WHERE `title` LIKE 'dbtech_thanks_%'");
self::report('Reverted Table', 'datastore');

// Drop
if (self::$db_alter->fetch_table_info('blog'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'blog');
}

if (self::$db_alter->fetch_table_info('blog_text'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'blog_text');
}

if (self::$db_alter->fetch_table_info('discussion'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'discussion');
}

if (self::$db_alter->fetch_table_info('forum'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::$db_alter->drop_field('dbtech_thanks_hide_threshold');
	self::report('Reverted Table', 'forum');
}

if (self::$db_alter->fetch_table_info('groupmessage'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'groupmessage');
}

if (self::$db_alter->fetch_table_info('post'))
{
	self::$db_alter->drop_field('dbtech_thanks_cache');
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::$db_alter->drop_field('dbtech_thanks_requiredbuttons_content');
	self::$db_alter->drop_field('dbtech_thanks_requiredbuttons_attach');
	self::report('Reverted Table', 'post');
}

if (self::$db_alter->fetch_table_info('thread'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'thread');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->drop_field('dbtech_thanks_points');
	self::$db_alter->drop_field('dbtech_thanks_settings');
	self::$db_alter->drop_field('dbtech_thanks_alertcount');
	self::report('Reverted Table', 'user');
}

if (self::$db_alter->fetch_table_info('usergroup'))
{
	self::$db_alter->drop_field('dbtech_thankspermissions');
	self::report('Reverted Table', 'usergroup');
}

if (self::$db_alter->fetch_table_info('usernote'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'usernote');
}

if (self::$db_alter->fetch_table_info('visitormessage'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');
	self::report('Reverted Table', 'visitormessage');
}

// Deleted Tables

// Drop
$tables = array(
	'button',
	'entry',
	'entrycache',
	'recententry',
	'statistics',
);
foreach ($tables as $table)
{
	self::$db->query_write("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "dbtech_thanks_{$table}`");
	self::report('Deleted Table', 'dbtech_thanks_' . $table);
}
?>