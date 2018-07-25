<?php
if (defined('IN_THANKS') AND $idfield == $table . 'id')
{
	$idfield = substr($table, strlen('dbtech_thanks_')) . 'id';
	$handled = true;
	
	$item = $vbulletin->db->query_first("
		SELECT $idfield, $titlename AS title
		FROM " . TABLE_PREFIX . "$table
		WHERE $idfield = '$itemid'
	");
}
?>