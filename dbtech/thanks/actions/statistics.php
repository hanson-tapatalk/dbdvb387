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

// Add to the navbits
$navbits[''] = $pagetitle = $vbphrase['dbtech_thanks_statistics'];

// draw cp nav bar
THANKS::setNavClass('statistics');

// Set the limit on number of users to fetch
$limit = (isset($vbulletin->options['dbtech_thanks_statistics_topx']) ? $vbulletin->options['dbtech_thanks_statistics_topx'] : 5);

$types = array();
$types2 = array();
$hiddenTypes = array();

foreach (THANKS::$cache['button'] as $button)
{
	if (!$button['active'])
	{
		// Inactive button
		continue;
	}

	$canAccessForum = false;
	foreach ($vbulletin->forumcache as $forumid => $forum)
	{
		if ($forum['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
		{
			// This button was disabled
			continue;
		}

		$fperms =& $vbulletin->userinfo['forumpermissions'][$forumid];
		if (
			!((int)$fperms & (int)$vbulletin->bf_ugp_forumpermissions['canview']) OR 
			!((int)$fperms & (int)$vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR 
			!verify_forum_password($forumid, $forum['password'], false)
		)
		{
			// We didn't have access to this forum
			continue;
		}

		$canAccessForum = true;
		break;
	}

	if (!$canAccessForum)
	{
		// This button was only enabled in a forum they couldn't see
		continue;
	}
	
	$types[] = $button['varname'] . '_given';
	$types[] = $button['varname'] . '_received';
	
	if (THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'cannotseeclicks') AND THANKS::$isPro)
	{
		// We can't see who clicked
		$hiddenTypes[] = $button['varname'] . '_given';
		$hiddenTypes[] = $button['varname'] . '_received';
	}	
	
	$types2[$button['varname'] . '_given'] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'] . ' (' . $vbphrase['dbtech_thanks_given'] . ')';
	$types2[$button['varname'] . '_received'] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'] . ' (' . $vbphrase['dbtech_thanks_received'] . ')';
}

// Init this array
$leaders = array();

// Init these
$leaderboardbits = '';

if (class_exists('vb_optimise'))
{
	vb_optimise::cache('thanksstats', $types, $leaders, $limit);
}

foreach ($types as $type)
{
	// Init this
	$userbits = array();
	$xmlbits = array();
	$leaderResult = $leaders[$type];

	if (!count($leaderResult))
	{
		// Fetch entries
		$leaderResult = THANKS::$db->fetchAllKeyed('
			SELECT 
				:type AS value,
				user.userid,
				user.username,
				user.usergroupid,
				user.membergroupids,
				user.infractiongroupid,
				user.displaygroupid
				:vBShop
			FROM $dbtech_thanks_statistics AS entry
			LEFT JOIN $user AS user USING(userid)
			LEFT JOIN $usergroup AS usergroup ON (usergroup.usergroupid = user.usergroupid)
			ORDER BY value DESC
			LIMIT :limit
		', 'userid', array(
			':type' 	=> $type,
			':limit' 	=> $limit,
			':vBShop' 	=> ($vbulletin->products['dbtech_vbshop'] ? ", dbtech_vbshop_purchase" : ''),
		));
	}

	// begin sorted threads
	$sortedLeaders = array();
	foreach ($leaderResult as $userid => $info)
	{
		if (!$info['value'])
		{
			// Skip this
			continue;
		}
		
		// Prepare for sort
		$sortedLeaders[$userid] = $info['value'];
	}
	arsort($sortedLeaders, SORT_NUMERIC);
	
	$key = 0;
	foreach ($sortedLeaders as $leaderId => $value)
	{
		// Shorthand
		$userinfo = $leaderResult[$leaderId];
		
		// Grab the musername
		fetch_musername($userinfo);
		
		if ($userinfo['value'] == 0)
		{
			// We don't want to count empty users
			continue;
		}
		
		if (in_array($type, $hiddenTypes))
		{
			$userinfo['musername'] = $vbphrase['dbtech_thanks_stripped_content'];
		}
		else
		{
			$userinfo['musername'] = '<a href="member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $userinfo['userid'] . '" target="_blank">' . $userinfo['musername'] . '</a>';
		}
		
		$j = ++$key;
		$templater = vB_Template::create('dbtech_thanks_statistics_userbit');
			$templater->register('userinfo', $userinfo);
		$userbits[$j] .= $templater->render();

		if ($_REQUEST['xml'])
		{
			$xmlbits[] = array($userinfo['username'], $link, $userinfo['value']);
		}
	}
	
	for ($k = 1; $k <= $limit; $k++)
	{
		if (!$userbits[$k])
		{
			// Didn't have this point
			$userbits[$k] = vB_Template::create('dbtech_thanks_statistics_userbit')->render();
		}
	}
	
	// Make sure we also got the phrase
	$phrase = construct_phrase($vbphrase['dbtech_thanks_top_x'], $limit, $types2[$type]);
	
	if ($_REQUEST['xml'])
	{
		$this->xml[] = array(
			'phrase'	=> $phrase,
			'bits'		=> $xmlbits,
		);
	}
	
	$templater = vB_Template::create('dbtech_thanks_statistics_statisticbit');
		$templater->register('phrase', $phrase);
		$templater->register('userbits', implode('', $userbits));
	$leaderboardbits .= $templater->render();
}

// Begin the page template
$page_templater = vB_Template::create('dbtech_thanks_statistics');
	$page_templater->register('pagetitle', $pagetitle);
	$page_templater->register('leaderboardbits', $leaderboardbits);
$HTML = $page_templater->render();