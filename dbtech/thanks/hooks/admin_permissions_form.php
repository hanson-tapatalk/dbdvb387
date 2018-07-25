<?php
foreach (convert_bits_to_array($user['dbtech_thanksadminperms'], $vbulletin->bf_misc_dbtech_thanksadminperms) AS $field => $value)
{
	print_yes_no_row($vbphrase["$field"], "dbtech_thanksadminperms[$field]", $value);
}
?>