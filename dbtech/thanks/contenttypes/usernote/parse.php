<?php
$row['title'] = fetch_censored_text($row['posttitle'] ? $row['posttitle'] : $row['threadtitle']);
$row['url'] = self::$vbulletin->options['bburl'] . '/usernote.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $row['threadid'];
?>