<?php
$table = '';
switch (THIS_SCRIPT)
{
	case 'announcement':
		$table = 'announcement';
		break;
	
	case 'usernote':
		$table = 'usernote';
		break;
	
	case 'post':
	default:
		$table = 'post';
		break;
}
$hook_query_fields .= ', dbtech_thanks_statistics.*, ' . $table . '.userid';
$hook_query_joins .= " LEFT JOIN " . TABLE_PREFIX . "dbtech_thanks_statistics AS dbtech_thanks_statistics ON(dbtech_thanks_statistics.userid = " . $table . ".userid) ";
?>