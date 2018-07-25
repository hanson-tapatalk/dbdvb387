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

if (!$vbulletin->options['dbtech_thanks_enablehottest'])
{
	// Disabled
	print_no_permission();
}

// Add to the navbits
$navbits[''] = $pagetitle = $vbphrase['dbtech_thanks_hottest_threads_posts'];

// draw cp nav bar
THANKS::setNavClass('hottest');

// Set the limit on number of users to fetch
$limit = (isset($vbulletin->options['dbtech_thanks_statistics_topx']) ? $vbulletin->options['dbtech_thanks_statistics_topx'] : 5);

// #######################################################################
if ($_REQUEST['action'] == 'main' OR !$_REQUEST['action'])
{
	$vbulletin->input->clean_array_gpc('r', array(
		'dateline_start'  	=> TYPE_UNIXTIME,
		'dateline_end'     	=> TYPE_UNIXTIME,
	));

	// Shorthand the forumids we're allowed to see
	$forumids = THANKS::getForumIds();
	
		foreach ((array)THANKS::$cache['button'] as $buttonid => $button)
		{
			if (!$button['active'])
			{
				// Inactive button
				continue;
			}
			
			$threadbits = array();
			$postbits = array();
			
			$cacheResult = THANKS_CACHE::read('hottest', 'hottest.threads.' . $vbulletin->GPC['dateline_start'] . '.' . $vbulletin->GPC['dateline_end'] . '.' . $button['varname']);
			if (!is_array($cacheResult))
			{
				// Fetch entries
				$threads = THANKS::$db->fetchAllKeyed('
					SELECT 
						COUNT(*) AS entrycount,
						thread.title,
						thread.threadid,
						thread.forumid
					FROM $dbtech_thanks_entry AS entry
					LEFT JOIN $post AS post ON(post.postid = entry.contentid)
					LEFT JOIN $thread AS thread ON(thread.threadid = post.threadid)
					WHERE contenttype = \'post\'
						AND varname = ?
						AND thread.title IS NOT NULL
						:dateStart
						:dateEnd
					GROUP BY thread.threadid
					ORDER BY entrycount DESC
					LIMIT :limit
				', 'threadid', array(
					$button['varname'],
					':limit' 		=> $limit,
					':dateStart' 	=> ($vbulletin->GPC['dateline_start'] 	? ' AND thread.dateline >= ' . intval($vbulletin->GPC['dateline_start']) : ''),
					':dateEnd' 		=> ($vbulletin->GPC['dateline_end'] 	? ' AND thread.dateline <= ' . intval($vbulletin->GPC['dateline_end']) : '')
				));

				if ($cacheResult != -1)
				{
					// Write to the cache
					THANKS_CACHE::write($threads, 'hottest', 'hottest.threads.' . $vbulletin->GPC['dateline_start'] . '.' . $vbulletin->GPC['dateline_end'] . '.' . $button['varname']);
				}
			}
			else
			{
				// Set the entry cache
				$threads = $cacheResult;
			}

			// begin sorted threads
			$sortedThreads = array();
			foreach ($threads as $threadid => $info)
			{
				// Prepare for sort
				$sortedThreads[$threadid] = $info['entrycount'];
			}
			arsort($sortedThreads, SORT_NUMERIC);
			
			$cacheResult = THANKS_CACHE::read('hottest', 'hottest.posts.' . $vbulletin->GPC['dateline_start'] . '.' . $vbulletin->GPC['dateline_end'] . '.' . $button['varname']);
			if (!is_array($cacheResult))
			{
				// Fetch entries
				$posts = THANKS::$db->fetchAllKeyed('
					SELECT 
						COUNT(*) AS entrycount,
						post.title AS posttitle,
						thread.title AS threadtitle,
						thread.title AS title,
						post.postid,
						thread.forumid
					FROM $dbtech_thanks_entry AS entry
					LEFT JOIN $post AS post ON(post.postid = entry.contentid)
					LEFT JOIN $thread AS thread ON(thread.threadid = post.threadid)
					WHERE contenttype = ?
						AND varname = ?
						AND thread.title IS NOT NULL
						:dateStart
						:dateEnd
					GROUP BY entry.contentid
					ORDER BY entrycount DESC
					LIMIT :limit
				', 'threadid', array(
					'post',
					$button['varname'],
					':limit' 		=> $limit,
					':dateStart' 	=> ($vbulletin->GPC['dateline_start'] 	? ' AND entry.dateline >= ' . intval($vbulletin->GPC['dateline_start']) : ''),
					':dateEnd' 		=> ($vbulletin->GPC['dateline_end'] 	? ' AND entry.dateline <= ' . intval($vbulletin->GPC['dateline_end']) : '')
				));

				if ($cacheResult != -1)
				{
					// Write to the cache
					THANKS_CACHE::write($posts, 'hottest', 'hottest.posts.' . $vbulletin->GPC['dateline_start'] . '.' . $vbulletin->GPC['dateline_end'] . '.' . $button['varname']);
				}
			}
			else
			{
				// Set the entry cache
				$posts = $cacheResult;
			}

			// begin sorted POSTS
			$sortedPosts = array();
			foreach ($posts as $postid => $info)
			{
				// Prepare for sort
				$sortedPosts[$postid] = $info['entrycount'];
			}
			arsort($sortedPosts, SORT_NUMERIC);

			$key = 0;
			foreach ($sortedThreads as $threadid => $entrycount)
			{
				if (!$entrycount)
				{
					// Skip all the things!
					continue;
				}
				
				if (!in_array($threads[$threadid]['forumid'], $forumids))
				{
					$threads[$threadid]['title'] 	= $vbphrase['dbtech_thanks_stripped_content'];
					$threads[$threadid]['threadid'] = 0;
				}
				
				$j = ++$key;
				$templater = vB_Template::create('dbtech_thanks_hottest_threadbit');
					$templater->register('threadinfo', $threads[$threadid]);
				$threadbits[$j] .= $templater->render();			
			}
			
			for ($k = 1; $k <= $limit; $k++)
			{
				if (!$threadbits[$k])
				{
					// Didn't have this point
					$threadbits[$k] = vB_Template::create('dbtech_thanks_hottest_threadbit')->render();
				}
			}
			
			$templater = vB_Template::create('dbtech_thanks_statistics_statisticbit');
				$templater->register('phrase', $vbphrase['dbtech_thanks_hottest_threads'] . ' - ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title']);
				$templater->register('userbits', implode('', $threadbits));
			$entries['threads'] .= $templater->render();	
			
			$key = 0;
			foreach ($sortedPosts as $postid => $entrycount)
			{
				if (!$entrycount)
				{
					// Skip all the things!
					continue;
				}
				
				if (!in_array($posts[$postid]['forumid'], $forumids))
				{
					$posts[$postid]['title'] 	= $vbphrase['dbtech_thanks_stripped_content'];
					$posts[$postid]['postid'] 	= 0;
				}

				$j = ++$key;
				$templater = vB_Template::create('dbtech_thanks_hottest_postbit');
					$templater->register('postinfo', $posts[$postid]);
				$postbits[$j] .= $templater->render();			
			}
			
			for ($k = 1; $k <= $limit; $k++)
			{
				if (!$postbits[$k])
				{
					// Didn't have this point
					$postbits[$k] = vB_Template::create('dbtech_thanks_hottest_postbit')->render();
				}
			}
			
			$templater = vB_Template::create('dbtech_thanks_statistics_statisticbit');
				$templater->register('phrase', $vbphrase['dbtech_thanks_hottest_posts'] . ' - ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title']);
				$templater->register('userbits', implode('', $postbits));
			$entries['posts'] .= $templater->render();			
		}

	$timeselects = THANKS::timeRow($vbphrase['start_date'], 'dateline_start', 	$vbulletin->GPC['dateline_start']);
	$timeselects .= THANKS::timeRow($vbphrase['end_date'], 	'dateline_end', 	$vbulletin->GPC['dateline_end']);
	
	// Create the template
	$templater = vB_Template::create('dbtech_thanks_hottest');
		$templater->register('pagetitle', 	$pagetitle);
		$templater->register('entries', 	$entries);
		$templater->register('timeselects', $timeselects);
	$HTML .= $templater->render();
}