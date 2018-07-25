<?php
// Shorthand the forumids we're allowed to see
$forumids = self::getForumIds();

if (in_array($row['forumid'], $forumids))
{
	// We can access this forum
	$row['title'] = fetch_censored_text($row['posttitle'] ? $row['posttitle'] : $row['threadtitle']);	
	$row['url'] = (intval(self::$vbulletin->versionnumber) == 4 ? fetch_seo_url('thread|bburl', $row, array('p' => $row['postid']), 'threadid', 'title') . "#post$row[postid]" : self::$vbulletin->options['bburl'] . '/showpost.php?' . self::$vbulletin->session->vars['sessionurl'] . 'p=' . $row['postid']);
}
else
{
	// We cannot
	$row['title'] = $vbphrase['dbtech_thanks_stripped_content'];
}
?>