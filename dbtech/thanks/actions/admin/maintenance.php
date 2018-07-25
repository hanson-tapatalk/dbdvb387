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

// #############################################################################
if ($_REQUEST['action'] == 'maintenance' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['maintenance']);
	
	print_form_header('thanks', 'maintenance');
	construct_hidden_code('action', 'cleanup');
	print_table_header($vbphrase['dbtech_thanks_cleanup'], 2, 0);
	print_description_row($vbphrase['dbtech_thanks_cleanup_descr']);
	print_input_row($vbphrase['number_of_posts_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['dbtech_thanks_cleanup']);
	
	print_form_header('thanks', 'maintenance');
	construct_hidden_code('action', 'recalc');	
	print_table_header($vbphrase['dbtech_thanks_recalc'], 2, 0);
	print_description_row($vbphrase['dbtech_thanks_recalc_descr']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['dbtech_thanks_recalc']);
	
	print_form_header('thanks', 'maintenance');
	construct_hidden_code('action', 'rebuildstatistics');	
	print_table_header($vbphrase['dbtech_thanks_rebuild_statistics'], 2, 0);
	print_description_row($vbphrase['dbtech_thanks_rebuild_statistics_descr']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['dbtech_thanks_rebuild_statistics']);
	
	print_form_header('thanks', 'maintenance');
	construct_hidden_code('action', 'rebuildentrycache');
	print_table_header($vbphrase['dbtech_thanks_rebuild_entrycache'], 2, 0);
	print_description_row($vbphrase['dbtech_thanks_rebuild_entrycache_descr']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['dbtech_thanks_rebuild_entrycache']);
	
	print_form_header('thanks', 'maintenance');
	construct_hidden_code('action', 'recalcrep');	
	print_table_header($vbphrase['dbtech_thanks_recalcrep'], 2, 0);
	print_description_row($vbphrase['dbtech_thanks_recalcrep_descr']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['dbtech_thanks_recalcrep']);
	
	($hook = vBulletinHook::fetch_hook('dbtech_thanks_maintenance')) ? eval($hook) : false;
	
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'cleanup')
{
	print_cp_header($vbphrase['maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_thanks_cleaning_up_posts'] . '</p>';
	
	$entries = THANKS::$db->fetchAllObject('
		SELECT entry.*
		FROM $dbtech_thanks_entry AS entry
		LEFT JOIN $post AS post ON(post.postid = entry.contentid)
		WHERE entry.entryid >= ?
			AND contenttype = \'post\'
			AND post.postid IS NULL
		ORDER BY entry.entryid
		LIMIT :limit
	', array(
		$vbulletin->GPC['startat'],
		':limit' 		=> $vbulletin->GPC['perpage']
	));
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($entry = THANKS::$db->fetchCurrent())
	{
		echo construct_phrase($vbphrase['processing_x'], $entry['entryid']) . "<br />\n";
		vbflush();
		
		// init data manager
		$dm =& THANKS::initDataManager('Entry', $vbulletin, ERRTYPE_CP);
			$dm->set_existing($entry);
		$dm->delete();
	
		$finishat = ($entry['entryid'] > $finishat ? $entry['entryid'] : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('
		SELECT entry.entryid FROM $dbtech_thanks_entry AS entry
		LEFT JOIN $post AS post ON(post.postid = entry.contentid)
		WHERE entry.entryid >= ?
			AND contenttype = \'post\'
			AND post.postid IS NULL
	', array(
		$finishat
	)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=cleanup&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=cleanup&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		
		define('CP_REDIRECT', 'thanks.php?do=maintenance');
		print_stop_message('dbtech_thanks_cleanup_succeeded');
	}
	
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'recalc')
{
	print_cp_header($vbphrase['maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_thanks_recalculating'] . '</p>';
	
	$users = THANKS::$db->fetchAllObject('
		SELECT userid
		FROM $user AS user
		WHERE userid >= ?
		ORDER BY userid
		LIMIT :limit
	', array(
		$vbulletin->GPC['startat'],
		':limit' => $vbulletin->GPC['perpage']
	));
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = THANKS::$db->fetchCurrent())
	{
		// Shorthand
		$userid = intval($user['userid']);
		
		echo construct_phrase($vbphrase['processing_x'], $userid) . "<br />\n";
		vbflush();
		
		// Default value
		$user['dbtech_thanks_points'] = 0;
		
		// Fetch given entries
		$entryGiven = THANKS::$db->fetchAll('
			SELECT COUNT(*) as value, varname
			FROM $dbtech_thanks_entry
			WHERE userid = ?
			GROUP BY varname
		', array($userid));
		
		// All given entries
		foreach ($entryGiven as $given)
		{
			// Store given
			$user[$given['varname'] . '_given'] = $given['value'];
			$user['dbtech_thanks_points'] 		+= $given['value'];
		}

		// Fetch received entries
		$entryReceived = THANKS::$db->fetchAll('
			SELECT COUNT(*) as value, varname
			FROM $dbtech_thanks_entry
			WHERE receiveduserid = ?
			GROUP BY varname
		', array($userid));
		
		// All received entries
		foreach ($entryReceived as $received)
		{
			// Store received
			$user[$received['varname'] . '_received'] 	= $received['value'];
			$user['dbtech_thanks_points'] 				+= $received['value'];
		}
		
		// Update points	
		THANKS::$db->update('user', array('dbtech_thanks_points' => intval($user['dbtech_thanks_points'])), 'WHERE userid = ' . $userid);
		
		// Begin columns
		$SQL = array();
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			$SQL[$button['varname'] . '_given'] 	= intval($user[$button['varname'] . '_given']);
			$SQL[$button['varname'] . '_received'] 	= intval($user[$button['varname'] . '_received']);
		}
			
		if (count($SQL))
		{
			// Update the record
			THANKS::$db->update('dbtech_thanks_statistics', $SQL, 'WHERE userid = ' . $userid);
		}
		$finishat = ($userid > $finishat ? $userid : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT userid FROM $user WHERE userid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=recalc&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=recalc&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		define('CP_REDIRECT', 'thanks.php?do=maintenance');
		print_stop_message('dbtech_thanks_recalc_successful');
	}
	
	print_cp_footer();
}


// #############################################################################
if ($_REQUEST['action'] == 'recalc2')
{
	print_cp_header($vbphrase['maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_thanks_recalculating'] . '</p>';
	
	$users = THANKS::$db->fetchAllObject('
		SELECT *
		FROM $dbtech_thanks_entry
		WHERE entryid >= ?
		ORDER BY entryid
		LIMIT :limit
	', array(
		$vbulletin->GPC['startat'],
		':limit' => $vbulletin->GPC['perpage']
	));
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = THANKS::$db->fetchCurrent())
	{
		// Shorthand
		$userid = intval($user['entryid']);
		
		echo construct_phrase($vbphrase['processing_x'], $userid) . "<br />\n";
		vbflush();
		
		if ($user['receiveduserid'])
		{
			// Skip this, already has received user id
			continue;
		}
		
		if ($user['contenttype'] != 'post')
		{
			// Skip this, already has received user id
			continue;
		}
		
		// Set received user id
		$receiveduserid = THANKS::$db->fetchOne('SELECT userid FROM $post WHERE postid = ?', array($user['contentid']));
		
		// Update entry
		THANKS::$db->update('dbtech_thanks_entry', array('receiveduserid' => $receiveduserid), 'WHERE entryid = ' . $userid);
		
		$finishat = ($userid > $finishat ? $userid : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT entryid FROM $dbtech_thanks_entry WHERE entryid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=recalc2&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=recalc2&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		define('CP_REDIRECT', 'thanks.php?do=maintenance');
		print_stop_message('dbtech_thanks_recalc_successful');
	}
	
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'rebuildstatistics')
{
	print_cp_header($vbphrase['maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_thanks_recalculating'] . '</p>';	
	
	$users = THANKS::$db->fetchAllObject('
		SELECT userid
		FROM $user AS user
		WHERE userid >= ?
		ORDER BY userid
		LIMIT :limit
	', array(
		$vbulletin->GPC['startat'],
		':limit' => $vbulletin->GPC['perpage']
	));
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = THANKS::$db->fetchCurrent())
	{
		// Shorthand
		$userid = intval($user['userid']);
		
		echo construct_phrase($vbphrase['processing_x'], $userid) . "<br />\n";
		vbflush();
		
		// Insert the record
		THANKS::$db->query('
			INSERT IGNORE INTO $dbtech_thanks_statistics
				(userid)
			VALUES (?)
		', array($userid));
		
		$SQL = array();
		$entries = THANKS::$db->fetchAll('
			SELECT *
			FROM $dbtech_thanks_entry
			WHERE userid = ?
				OR receiveduserid = ?
		', array($userid, $userid));
		foreach ($entries as $entry)
		{
			// Set the array
			$SQL[$entry['varname'] . ($entry['receiveduserid'] == $userid ? '_received' : '_given')]++;
		}
		
		if (count($SQL))
		{
			// Update the record
			THANKS::$db->update('dbtech_thanks_statistics', $SQL, 'WHERE userid = ' . $userid);
		}
		$finishat = ($userid > $finishat ? $userid : $finishat);
	}
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT userid FROM $user WHERE userid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=rebuildstatistics&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=rebuildstatistics&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		print_cp_message('Statistics Populated!', 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=maintenance', 1, NULL, false);
	}
	
	print_cp_footer();
}


// #############################################################################
if ($_REQUEST['action'] == 'rebuildentrycache')
{
	print_cp_header($vbphrase['maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_thanks_recalculating'] . '</p>';
	
	$users = THANKS::$db->fetchAllObject('
		SELECT *
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

		if ($entry['dateline'] >= (TIMENOW - (86400 * $vbulletin->options['dbtech_thanks_recententries'])))
		{
			// Newer than 30 days
			THANKS::$db->insertIgnore('dbtech_thanks_recententry', $entry);
		}

		$finishat = ($entryid > $finishat ? $entryid : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT entryid FROM $dbtech_thanks_entry WHERE entryid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=rebuildentrycache&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=rebuildentrycache&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		define('CP_REDIRECT', 'thanks.php?do=maintenance');
		print_stop_message('dbtech_thanks_recalc_successful');
	}
	
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'recalcrep')
{
	print_cp_header($vbphrase['maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_thanks_recalculating'] . '</p>';	
	
	$users = THANKS::$db->fetchAllObject('
		SELECT *
		FROM $dbtech_thanks_statistics
		WHERE userid >= ?
		ORDER BY userid
		LIMIT :limit
	', array(
		$vbulletin->GPC['startat'],
		':limit' => $vbulletin->GPC['perpage']
	));
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = THANKS::$db->fetchCurrent())
	{
		// Shorthand
		$userid = intval($user['userid']);
		
		echo construct_phrase($vbphrase['processing_x'], $userid) . "<br />\n";
		vbflush();
		
		$reputation = 0;
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			// Add to the reputation
			$reputation += ($button['reputation'] * $user[$button['varname'] . '_received']);
		}
		
		if ($reputation)
		{
			// Update the record
			THANKS::$db->query('
				UPDATE $user
				SET reputation = reputation + ?
				WHERE userid = ?
			', array($reputation, $userid));
		}
		$finishat = ($userid > $finishat ? $userid : $finishat);
	}
	$finishat++;
	
	if ($checkmore = THANKS::$db->fetchOne('SELECT userid FROM $user WHERE userid >= ?', array($finishat)))
	{
		print_cp_redirect("thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=recalcrep&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=recalcrep&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		print_cp_message('Reputation Recalculated!', 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=maintenance', 1, NULL, false);
	}
	
	print_cp_footer();
}