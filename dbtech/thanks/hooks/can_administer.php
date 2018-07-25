<?php
foreach($do AS $field)
{
    if ((int)$admin['dbtech_thanksadminperms'] & (int)$vbulletin->bf_misc_dbtech_thanksadminperms["$field"])
    {
        $return_value = true;
    }
} 
?>