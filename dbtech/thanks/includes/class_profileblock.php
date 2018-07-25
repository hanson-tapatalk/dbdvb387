<?php
/**
* Thanks Block for Advanced Post Thanks / Like
*
* @package Advanced Post Thanks / Like
*/
class vB_ProfileBlock_APTL_Thanks extends vB_ProfileBlock
{
	/**
	* The name of the template to be used for the block
	*
	* @var string
	*/
	var $template_name = 'dbtech_thanks_memberinfo_block_thanks';
	
	var $nowrap = true;
	
	var $skip_privacy_check = true;
	
	/**
	* Variables to automatically prepare
	*
	* @var array
	*/
	var $auto_prepare = array();

	/**
	* Sets/Fetches the default options for the block
	*
	*/
	function fetch_default_options()
	{
		$this->option_defaults = array(
			'pagenumber' => 1,
			'perpage'    => 25,
		);
	}

	/**
	* Whether to return an empty wrapper if there is no content in the blocks
	*
	* @return bool
	*/
	function confirm_empty_wrap()
	{
		return false;
	}

	/**
	* Whether or not the block is enabled
	*
	* @return bool
	*/
	function block_is_enabled($id)
	{
		return true;
	}

	/**
	* Prepare any data needed for the output
	*
	* @param	string	The id of the block
	* @param	array	Options specific to the block
	*/
	function prepare_output($id = '', $options = array())
	{
		global $show, $vbphrase;
		
		$lookup = array();
		foreach (THANKS::$cache['button'] as $button)
		{
			$lookup[$button['varname']] = $button;
		}		
		
		if (intval($this->registry->versionnumber) == 3)
		{
			$this->nowrap = false;
		}
		
		$bypassCache = false;
		if ($this->profile->userinfo['userid'] == $this->registry->userinfo['userid'] AND $this->registry->userinfo['dbtech_thanks_alertcount'] AND $_REQUEST['tab'] == 'thanks')
		{
			// Reset tag count
			THANKS::$db->update('user', array('dbtech_thanks_alertcount' => 0), 'WHERE userid = ' . $this->registry->userinfo['userid']);
			$this->registry->userinfo['dbtech_thanks_alertcount'] = 0;
			$bypassCache = true;
		}

		$displayGiven = true;
		
		
		// Shorthands to faciliate easy copypaste
		$pagenumber = $options['pagenumber'];
		$perpage = $options['perpage'];
		
		$cacheResult = THANKS_CACHE::read('profile', 'profile.count.' . intval($displayGiven) . '.' . $this->profile->userinfo['userid']);
		if (!is_array($cacheResult) OR $bypassCache)
		{
			// Count number of users
			$count = intval(THANKS::$db->fetchOne('
				SELECT COUNT(*)
				FROM $dbtech_thanks_recententry AS entry
				WHERE entry.receiveduserid = ?
			', array($this->profile->userinfo['userid'])));

			if ($displayGiven)
			{
				$count += intval(THANKS::$db->fetchOne('
					SELECT COUNT(*)
					FROM $dbtech_thanks_recententry AS entry
					WHERE entry.userid = ?
				', array($this->profile->userinfo['userid'])));
			}

			if ($cacheResult != -1)
			{
				// Write to the cache
				THANKS_CACHE::write(array('count' => $count), 'profile', 'profile.count.' . intval($displayGiven) . '.' . $this->profile->userinfo['userid']);
			}
		}
		else
		{
			// Set the entry cache
			$count = $cacheResult['count'];
		}
	
		if (!$count)
		{
			// We're done here
			return true;
		}
		
		// Ensure every result is as it should be
		sanitize_pageresults($count, $pagenumber, $perpage);
		
		// Find out where to start
		$startat = ($pagenumber - 1) * $perpage;
		
		$pageinfo = array(
			'tab' => $id
		);
		if ($perpage)
		{
			$pageinfo['pp'] = $perpage;
		}

		if (intval($this->registry->versionnumber) > 3)
		{
			$this->block_data['pagenav'] = construct_page_nav(
				$pagenumber,
				$perpage,
				$count,
				'',
				'',
				$id,
				'member',
				$this->profile->userinfo,
				$pageinfo
			);
		}
		else
		{
			$this->block_data['pagenav'] = construct_page_nav(
				$pagenumber,
				$perpage,
				$count,
				'member.php?' . $this->registry->session->vars['sessionurl'] . "u=" . $this->profile->userinfo['userid'] . "&amp;tab=$id" .
				(!empty($perpage) ? "&amp;pp=$perpage" : ""), '', $id
			);
		}
		
		$cacheResult = THANKS_CACHE::read('profile', 'profile.sortresults.' . intval($displayGiven) . '.' . $startat . '.' . $perpage . '.' . $this->profile->userinfo['userid']);
		if (!is_array($cacheResult) OR $bypassCache)
		{
			$resultsToSort = THANKS::$db->fetchAssoc('
				SELECT entryid, contenttype
				FROM $dbtech_thanks_recententry
				WHERE receiveduserid = ?
				ORDER BY entryid DESC
				LIMIT :limitStart, :limitEnd
			', array(
				$this->profile->userinfo['userid'],
				':limitStart' 	=> $startat,
				':limitEnd' 	=> $perpage
			));

			if ($displayGiven)
			{
				$resultsToSort = $resultsToSort + THANKS::$db->fetchAssoc('
					SELECT entryid, contenttype
					FROM $dbtech_thanks_recententry
					WHERE userid = ?
					ORDER BY entryid DESC
					LIMIT :limitStart, :limitEnd
				', array(
					$this->profile->userinfo['userid'],
					':limitStart' 	=> $startat,
					':limitEnd' 	=> $perpage
				));
			}

			krsort($resultsToSort, SORT_NUMERIC);
			
			while (count($resultsToSort) > $perpage)
			{
				// Remove one element off the end of the page
				array_pop($resultsToSort);
			}

			if ($cacheResult != -1)
			{
				// Write to the cache
				THANKS_CACHE::write($resultsToSort, 'profile', 'profile.sortresults.' . intval($displayGiven) . '.' . $startat . '.' . $perpage . '.' . $this->profile->userinfo['userid']);
			}
		}
		else
		{
			// Set the entry cache
			$resultsToSort = $cacheResult;
		}

		if (!count($resultsToSort))
		{
			// Had no results
			return true;
		}
		
		foreach ($resultsToSort as $result)
		{
			// Set entries by content type
			THANKS::$created['numEntries'][$result['contenttype']][] = $result['entryid'];
		}
			
		// Load union sql
		$SQL = THANKS::loadUnionSql('profileblock');

		$results_q = array();
		if (count($SQL))
		{
			$cacheResult = THANKS_CACHE::read('profile', 'profile.results.' . intval($displayGiven) . '.' . $startat . '.' . $perpage . '.' . dechex(crc32(implode(',', $SQL))) . '.' . $this->profile->userinfo['userid']);
			if (!is_array($cacheResult) OR $bypassCache)
			{
				// Fetch users
				$results_q = THANKS::$db->fetchAll('
					SELECT
						entry.*,
						user.username,
						user.usergroupid,
						user.membergroupids,
						user.infractiongroupid,
						user.displaygroupid,
						receiveduser.username AS receivedusername,
						receiveduser.usergroupid AS receivedusergroupid,
						receiveduser.membergroupids AS receivedmembergroupids,
						receiveduser.infractiongroupid AS receivedinfractiongroupid,
						receiveduser.displaygroupid AS receiveddisplaygroupid
						:vBShop
					FROM (
						(' . implode(') UNION ALL (', $SQL) . ')
					) AS entry
					LEFT JOIN $user AS user ON(user.userid = entry.userid)
					LEFT JOIN $user AS receiveduser ON(receiveduser.userid = entry.receiveduserid)
					ORDER BY entryid DESC
					LIMIT :limitEnd			
				', array(
					':profileUser' 	=> $this->profile->userinfo['userid'],
					':limitStart' 	=> $startat,
					':limitEnd' 	=> $perpage,		
					':vBShop' 		=> ($this->registry->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase, receiveduser.dbtech_vbshop_purchase AS receivedpurchase" : ''),
				));

				if ($cacheResult != -1)
				{
					// Write to the cache
					THANKS_CACHE::write($results_q, 'profile', 'profile.results.' . intval($displayGiven) . '.' . $startat . '.' . $perpage . '.' . dechex(crc32(implode(',', $SQL))) . '.' . $this->profile->userinfo['userid']);
				}
			}
			else
			{
				// Set the entry cache
				$results_q = $cacheResult;
			}
		}
			
		$results = array();
		foreach ($results_q as $results_r)
		{
			// Ensure we have the proper day selected for the grouping
			$day = vbdate($this->registry->options['dateformat'], $results_r['dateline']);

			$received = array(
				'userid' 					=> $results_r['receiveduserid'],
				'username' 					=> $results_r['receivedusername'],
				'usergroupid' 				=> $results_r['receivedusergroupid'],
				'membergroupids' 			=> $results_r['receivedmembergroupids'],
				'infractiongroupid' 		=> $results_r['receivedinfractiongroupid'],
				'displaygroupid' 			=> $results_r['receiveddisplaygroupid'],
				'dbtech_vbshop_purchase' 	=> $results_r['receivedpurchase'],
			);
			
			// Grab the markup username
			fetch_musername($results_r);
			fetch_musername($received);
			
			// Parses the row and sets title / etc
			THANKS::parseRow($results_r);
			
			$user = $results_r['musername'];
			if ($results_r['userid'])
			{
				// Also add link
				$user = '<a href="member.php?' . $this->registry->session->vars['sessionurl'] . 'u=' . $results_r['userid'] . '" target="_blank">' . $user . '</a>';
			}
			
			$content = $results_r['title'];
			if ($results_r['url'])
			{
				// Also add link
				$content = '<a href="' . $results_r['url'] . '" target="_blank">' . $content . '</a>';
			}
			
			// Initialise the text
			$text = construct_phrase($vbphrase['dbtech_thanks_x_clicked_y_for_z_' . $results_r['contenttype'] . '_a'],
				vbdate($this->registry->options['timeformat'], $results_r['dateline']) . ' - ' . $user,
				$vbphrase['dbtech_thanks_button_' . $results_r['varname'] . '_title'],
				$content,
				'<a href="member.php?' . $this->registry->session->vars['sessionurl'] . 'u=' . $received['userid'] . '" target="_blank">' . $received['musername'] . '</a>'
			);

			$templater = vB_Template::create('dbtech_thanks_result_bit');
				$templater->register('text', $text);
			$results[$day][] = $templater->render();
		}
	
		$resultbits = '';
		foreach ($results as $day => $result)
		{
			$templater = vB_Template::create('dbtech_thanks_result');
				$templater->register('day', $day);
				$templater->register('resultbits', implode('', $result));
			$resultbits .= $templater->render();
		}
		$this->block_data['resultbits'] = $resultbits;		
		
		// Make sure we can check the options
		//$this->block_data['options'] = $options;
	}
}