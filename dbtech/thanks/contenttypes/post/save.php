<?php
if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 64)
{
	// Invalid varname
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_invalid_button']
	));
}

// Grab these
$contentid = $vbulletin->input->clean_gpc('p', 'postid', TYPE_UINT);
$varname = $vbulletin->input->clean_gpc('p', 'varname', TYPE_STR);

foreach (THANKS::$cache['button'] as $button)
{
	if (!$button['active'])
	{
		// Inactive button
		continue;
	}
	
	if ($button['varname'] == $varname)
	{
		// Copy this
		break;
	}
}

if (empty($button))
{
	// Invalid varname
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_invalid_button']
	));
}

if (!$vbulletin->userinfo['userid'])
{
	// We can't click this button
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_no_permissions_click']
	));
}

if ($vbulletin->userinfo['dbtech_thanks_excluded'])
{
	// We can't click this button
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_no_permissions_click']
	));
}

if (!THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'canclick'))
{
	// We can't click this button
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_no_permissions_click']
	));
}

// Grab the post info
if (!$post = THANKS::$db->fetchRow('
	SELECT
		post.title AS posttitle,
		thread.title AS threadtitle,
		post.postid,
		post.userid,
		post.dbtech_thanks_disabledbuttons,
		thread.firstpostid,
		thread.forumid,
		thread.dbtech_thanks_disabledbuttons AS disabledbuttons_thread,
		user.username,
		user.userid,
		user.usergroupid,
		user.displaygroupid,
		user.membergroupids,
		user.customtitle
	FROM $post AS post
	LEFT JOIN $thread AS thread ON(thread.threadid = post.threadid)
	LEFT JOIN $user AS user ON(user.userid = post.userid)
	WHERE post.postid = ?
', array(
	$contentid
)))
{
	// Invalid post id
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_invalid_postid'] . ': ' . $contentid
	));
}

if ($post['userid'] == $vbulletin->userinfo['userid'])
{
	// Can't click for own posts
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_cant_click_own_posts']
	));
}

$foruminfo = $vbulletin->forumcache[$post['forumid']];
if (!THANKS::$isPro)
{
	// Lite-only shit
	$parentlist = explode(',', $foruminfo['parentlist']);
	if ($parentlist[0] == -1)
	{
		// This forum
		$noticeforum = $foruminfo['forumid'];		
	}
	else
	{
		$key = (count($parentlist) - 2);
		$noticeforum = $parentlist["$key"];
	}
}
else
{
	// This forum
	$noticeforum = $foruminfo['forumid'];
}

$forumperms = fetch_permissions($foruminfo['forumid']);
if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
{
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_invalid_postid'] . ': ' . $contentid
	));
}	

if ((int)$vbulletin->forumcache[$noticeforum]['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
{
	// Button was disabled for this forum
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_button_disabled_forum']
	));
}

if ((int)$post['disabledbuttons_thread'] & (int)$button['bitfield'])
{
	// Button was disabled for this thread
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_button_disabled_thread']
	));
}

if ((int)$post['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
{
	// Button was disabled for this post
	THANKS::outputXML(array(
		'error' => $vbphrase['dbtech_thanks_button_disabled_post']
	));
}

// Refresh AJAX post data
$excluded = THANKS::refreshAjaxPost($contentid);



// We now have everything we need to build the entry info
$entryinfo = array(
	'varname' 			=> $varname,
	'userid' 			=> $vbulletin->userinfo['userid'],
	'contenttype' 		=> 'post',
	'contentid' 		=> $contentid,
	'receiveduserid' 	=> $post['userid']
);

if (!in_array($entryinfo['varname'], $excluded))
{
	// We clicked another button that prevented this button click
	$userinfo = fetch_userinfo($post['userid']);
	
	if ($existing = THANKS::$db->fetchRow('
		SELECT *
		FROM $dbtech_thanks_entry
		WHERE varname = ?
			AND userid = ?
			AND contenttype = \'post\'
			AND contentid = ?
	', array(
		$varname,
		$vbulletin->userinfo['userid'],
		$contentid
	)))
	{
		if (!THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'canunclick') OR !THANKS::$isPro)
		{
			// We can't un-click this button
			THANKS::outputXML(array(
				'error' => $vbphrase['dbtech_thanks_no_permissions_unclick']
			));
		}	

		// init data manager
		$dm =& THANKS::initDataManager('Entry', $vbulletin, ERRTYPE_SILENT);
			$dm->set_existing($existing);
		$dm->delete();
		
		if ($button['reputation'])
		{
			// Subtract reputation
			$userinfo['reputation'] -= $button['reputation'];
		}
	}
	else
	{
		// init data manager
		$dm =& THANKS::initDataManager('Entry', $vbulletin, ERRTYPE_SILENT);
		
		// button fields
		foreach ($entryinfo AS $key => $val)
		{
			// These values are always fresh
			$dm->set($key, $val);
		}
		
		// Save! Hopefully.
		$entryid = $dm->save();
		
		if (!$entryid)
		{
			// Unknown error
			THANKS::outputXML(array(
				'error' => $vbphrase['dbtech_thanks_unknown_click_error']
			));
		}
		
		if ($button['reputation'])
		{
			// Add reputation
			$userinfo['reputation'] += $button['reputation'];
		}
		
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_postsave')) ? eval($hook) : false;
		
		
	}
			
	if ($button['reputation'])
	{
		// Determine this user's reputationlevelid.
		$reputationlevel = THANKS::$db->fetchRow('
			SELECT reputationlevelid
			FROM $reputationlevel
			WHERE ? >= minimumreputation
			ORDER BY minimumreputation DESC
		', array($userinfo['reputation']));
	
		// init user data manager
		$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
			$userdata->set_existing($userinfo);
			$userdata->set('reputation', $userinfo['reputation']);
			$userdata->set('reputationlevelid', intval($reputationlevel['reputationlevelid']));
		$userdata->save();
	}
}


// Refresh AJAX post data
$excluded = THANKS::refreshAjaxPost($contentid);

// Process the display for this
THANKS::processEntryCache();

// Extract the variables from the entry processer
list($colorOptions, $thanksEntries) = THANKS::processEntries();

// Extract the variables from the display processer
list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, $post, array('dbtech_thanks_disabledbuttons' => $post['disabledbuttons_thread'], 'firstpostid' => $post['firstpostid']));

$retval = array(
	'entries' 		=> $entries,
	'actions' 		=> $actions,
	'thanksEntries' => (array)$thanksEntries[$post['postid']],
	'colorOptions' 	=> $colorOptions,
);
?>