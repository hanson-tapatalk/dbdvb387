<?php
$row['title'] = fetch_censored_text($row['posttitle']);
$row['url'] = self::$vbulletin->options['dbtech_review_urlpath_text'] . self::$vbulletin->options['dbtech_review_filename_text'] . '?' . self::$vbulletin->session->vars['sessionurl'] . 'do=view_review&amp;id=' . $row['postid'] . '&amp;ri=' . $row['threadid'];
?>