<?php
$row['title'] = fetch_censored_text($row['posttitle'] ? $row['posttitle'] : $row['threadtitle']);
$row['url'] = self::$vbulletin->options['bburl'] . '/member.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $row['threadid'] . '&amp;tab=visitor_messaging#visitor_messaging';
?>