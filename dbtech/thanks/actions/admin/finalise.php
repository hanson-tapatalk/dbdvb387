<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/
@set_time_limit(0);
ignore_user_abort(1);

print_cp_header($vbphrase['dbtech_thanks_maintenance']);

$vbulletin->input->clean_array_gpc('r', array(
	'perpage' => TYPE_UINT,
	'startat' => TYPE_UINT,
	'version' => TYPE_UINT
));

if (empty($vbulletin->GPC['perpage']))
{
	$vbulletin->GPC['perpage'] = 250;
}

echo '<p>Finalising Install...</p>';

if ($vbulletin->GPC['version'] == 110)
{
	$users = $db->query_read_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "user AS user
		WHERE userid >= " . $vbulletin->GPC['startat'] . "
		ORDER BY userid
		LIMIT " . $vbulletin->GPC['perpage']
	);
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = $db->fetch_array($users))
	{
		// Shorthand
		$userid = intval($user['userid']);
		
		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "dbtech_thanks_statistics
				(userid, thanks_given, thanks_received, likes_given, likes_received, dislikes_given, dislikes_received)
			VALUES ( 
				" . intval($user['userid']) . ",
				" . intval($user['dbtech_thanks_thanks']) . ",
				" . intval($user['dbtech_thanks_thanked']) . ",
				" . intval($user['dbtech_thanks_likes']) . ",
				" . intval($user['dbtech_thanks_liked']) . ",
				" . intval($user['dbtech_thanks_dislikes']) . ",
				" . intval($user['dbtech_thanks_disliked']) . "
			)
		");	
		
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);
	}
	$finishat++;
	
	if ($checkmore = $db->query_first_slave("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&version=" . $vbulletin->GPC['version'] . "&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&amp;version=" . $vbulletin->GPC['version'] . "&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		// Grab the DBAlter class
		require_once(DIR . '/includes/class_dbalter.php');
		
		// Set some important variables
		$db_alter = new vB_Database_Alter_MySQL($db);
		if ($db_alter->fetch_table_info('post'))
		{		
			$db_alter->drop_field('dbtech_thanks_likes');
			$db_alter->drop_field('dbtech_thanks_liked');
			$db_alter->drop_field('dbtech_thanks_thanks');
			$db_alter->drop_field('dbtech_thanks_thanked');
			$db_alter->drop_field('dbtech_thanks_dislikes');
			$db_alter->drop_field('dbtech_thanks_disliked');
		}
		
		// Done!
		print_cp_message('Statistics Populated!', "thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&version=111", 1, NULL, false);
	}	
}
else if ($vbulletin->GPC['version'] == 111)
{
	$users = $db->query_read_slave("
		SELECT
			entry.entryid,
			varname,
			entry.userid AS sentuserid,
			post.userid AS receiveduserid
		FROM `" . TABLE_PREFIX . "dbtech_thanks_entry` AS entry
		LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = entry.contentid)
		WHERE entry.entryid >= " . $vbulletin->GPC['startat'] . "		
		LIMIT " . $vbulletin->GPC['perpage']
	);
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = $db->fetch_array($users))
	{
		$db->query_write("
			UPDATE `" . TABLE_PREFIX . "dbtech_thanks_entry`
			SET receiveduserid = " . intval($user['receiveduserid']) . "
			WHERE entryid = " . intval($user['entryid'])
		);	
		
		echo construct_phrase($vbphrase['processing_x'], $user['entryid']) . "<br />\n";
		vbflush();
	
		$finishat = ($user['entryid'] > $finishat ? $user['entryid'] : $finishat);
	}
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT entryid FROM $dbtech_thanks_entry WHERE entryid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&version=" . $vbulletin->GPC['version'] . "&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&amp;version=" . $vbulletin->GPC['version'] . "&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		// Done!
		print_cp_message('Entries Populated!', 'index.php?loc=' . urlencode('plugin.php?do=product'), 1, NULL, false);
	}	
}
else if ($vbulletin->GPC['version'] == 325)
{
	$users = THANKS::$db->fetchAllObject('
		SELECT `entryid`, `varname`, `userid`, `contenttype`, `contentid`, `dateline`, `receiveduserid`
		FROM $dbtech_thanks_entry
		WHERE entryid >= ?
		ORDER BY entryid
		LIMIT :limit
	', array(
		$vbulletin->GPC['startat'],
		':limit' => $vbulletin->GPC['perpage']
	));
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($entry = THANKS::$db->fetchCurrent())
	{
		// Shorthand
		$entryid = intval($entry['entryid']);
		
		echo construct_phrase($vbphrase['processing_x'], $entryid) . "<br />\n";
		vbflush();

		if (!$entryCache = THANKS::$db->fetchRow('
			SELECT * 
			FROM $dbtech_thanks_entrycache
			WHERE varname = ?
				AND contenttype = ?
				AND contentid = ?
		', array(
			$entry['varname'],
			$entry['contenttype'],
			$entry['contentid']
		)))
		{
			// Defaults
			$entryCache = array(
				'varname' 		=> $entry['varname'],
				'contenttype' 	=> $entry['contenttype'],
				'contentid' 	=> $entry['contentid'],
				'data' 			=> 'a:0:{}'
			);

			// Insert this row
			THANKS::$db->insert('dbtech_thanks_entrycache', $entryCache, array(), false);
		}

		// Make sure we have an array
		$entryCache['data'] = unserialize($entryCache['data']);

		// Store this
		$entryCache['data'][$entry['entryid']] = $entry;

		// Update points	
		THANKS::$db->update(
			'dbtech_thanks_entrycache', 
			array('data' => trim(serialize($entryCache['data']))), 
			'WHERE varname = \'' . $entry['varname'] . '\' AND contenttype = \'' . $entry['contenttype'] . '\' AND contentid = \'' . $entry['contentid'] . '\''
		);

		if ($entry['dateline'] >= (TIMENOW - 2592000))
		{
			// Newer than 30 days
			THANKS::$db->insertIgnore('dbtech_thanks_recententry', $entry);
		}
		
		$finishat = ($entryid > $finishat ? $entryid : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT entryid FROM $dbtech_thanks_entry WHERE entryid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&version=" . $vbulletin->GPC['version'] . "&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=finalise&amp;version=" . $vbulletin->GPC['version'] . "&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		// Done!
		print_cp_message('Entries Populated!', 'index.php?loc=' . urlencode('plugin.php?do=product'), 1, NULL, false);
	}
}

print_cp_footer();