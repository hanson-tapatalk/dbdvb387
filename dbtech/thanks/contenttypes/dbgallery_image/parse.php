<?php
$row['title'] = fetch_censored_text($row['posttitle']);
$row['url'] = self::$vbulletin->options['dbtech_gallery_urlpath_text'] . self::$vbulletin->options['dbtech_gallery_filename_text'] . '?' . self::$vbulletin->session->vars['sessionurl'] . 'do=view_image&amp;id=' . $row['postid'] . '&amp;gal=' . $row['threadid'];
?>