<?php
$row['title'] = fetch_censored_text($row['posttitle'] ? $row['posttitle'] : $row['threadtitle']);
$row['url'] = self::$vbulletin->options['bburl'] . '/group.php?' . self::$vbulletin->session->vars['sessionurl'] . 'discussionid=' . $row['threadid'];
?>