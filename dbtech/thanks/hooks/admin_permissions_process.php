<?php
$vbulletin->input->clean_gpc('p', 'dbtech_thanksadminperms', TYPE_ARRAY_INT);
foreach ((array)$vbulletin->GPC['dbtech_thanksadminperms'] AS $field => $value)
{
	$admindm->set_bitfield('dbtech_thanksadminperms', $field, $value);
}
?>