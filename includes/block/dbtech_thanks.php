<?php
class vB_BlockType_Dbtech_thanks extends vB_BlockType
{
	/**
	 * The Productid that this block type belongs to
	 * Set to '' means that it belongs to vBulletin forum
	 *
	 * @var string
	 */
	protected $productid = 'dbtech_thanks';
	
	/**
	 * The block settings
	 * It uses the same data structure as forum settings table
	 * e.g.:
	 * <code>
	 * $settings = array(
	 *     'varname' => array(
	 *         'defaultvalue' => 0,
	 *         'optioncode'   => 'yesno'
	 *         'displayorder' => 1,
	 *         'datatype'     => 'boolean'
	 *     ),
	 * );
	 * </code>
	 * @see print_setting_row()
	 *
	 * @var string
	 */
	protected $settings = array(
		'dbtech_thanks_buttonids' => array(
			'defaultvalue' => -1,
			'optioncode'   => 'selectmulti:eval
$options = vB_BlockType_Dbtech_thanks::buttonIdChooser(fetch_phrase("dbtech_thanks_all_buttons", "dbtech_thanks"));',
			'displayorder' => 5,
			'datatype'     => 'arrayinteger'
		),
		'dbtech_thanks_limit' => array(
			'defaultvalue' => 5,
			'displayorder' => 9001,
			'datatype'     => 'integer'
		),
		'dbtech_thanks_datecutoff_amount' => array(
			'defaultvalue' => 1,
			'displayorder' => 10,
			'datatype'     => 'integer'
		),		
		'dbtech_thanks_datecutoff_timespan' => array(
			'defaultvalue' => 86400,
			'optioncode'   => 'radio:piped
3600|hours
86400|days
604800|weeks',
			'displayorder' => 20,
			'datatype'     => 'integer'
		),
		'dbtech_thanks_usergroups' => array(
			'defaultvalue' => -1,
			'optioncode'   => 'selectmulti:eval
$options = vB_BlockType_Dbtech_thanks::userGroupChooser(fetch_phrase("dbtech_thanks_no_usergroups", "dbtech_thanks"));',
			'displayorder' => 30,
			'datatype'     => 'arrayinteger'
		),		
	);
	
	public static function buttonIdChooser($topname = null)
	{
		global $vbphrase;
		
		$selectoptions = array();

		if ($topname)
		{
			$selectoptions['-1'] = $topname;
		}
		
		foreach ((array)THANKS::$cache['button'] as $buttonid => $button)
		{
			if (!$button['active'])
			{
				// Skip inactive buttons
				continue;
			}
			
			// Add to select options
			$selectoptions[$buttonid] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}

		return $selectoptions;
	}
	
	public static function userGroupChooser($topname = null)
	{
		global $vbulletin;
	
		$selectoptions = array();

		if ($topname)
		{
			$selectoptions['-1'] = $topname;
		}
		
		foreach ((array)$vbulletin->usergroupcache as $usergroupid => $usergroup)
		{
			// Add to select options
			$selectoptions[$usergroupid] = $usergroup['title'];
		}

		return $selectoptions;
	}

	/**
	 * This function allows the data returned to be cached.
	 *
	 * @return array 	The results
	 */
	public function getData()
	{
		global $vbphrase;
		
		if (!class_exists('THANKS'))
		{
			// Not displaying any results
			return '';
		}		
		
		if (!$this->config['dbtech_thanks_limit'])
		{
			// Not displaying any results
			return '';
		}
		
		$buttons = array();
		foreach ((array)THANKS::$cache['button'] as $buttonid => $button)
		{
			if (!$button['active'])
			{
				// Skip inactive buttons
				continue;
			}
			
			if (!in_array($buttonid, $this->config['dbtech_thanks_buttonids']) AND $this->config['dbtech_thanks_buttonids'][0] != -1)
			{
				// Button was not to be included
				continue;
			}
			
			// Add to select options
			$buttons[] = $button['varname'];
		}
		
		if (!count($buttons))
		{
			// No buttons selected
			return '';
		}
		
		$this->config['dbtech_thanks_datecutoff_amount'] = ($this->config['dbtech_thanks_datecutoff_amount'] ? $this->config['dbtech_thanks_datecutoff_amount'] : 1);
		$this->config['dbtech_thanks_datecutoff_timespan'] = ($this->config['dbtech_thanks_datecutoff_timespan'] ? $this->config['dbtech_thanks_datecutoff_timespan'] : 86400);
		
		$globalignore = '';
		if (trim($this->registry->options['globalignore']) != '')
		{
			require_once(DIR . '/includes/functions_bigthree.php');
			if ($Coventry = fetch_coventry('string'))
			{
				$globalignore = "AND user.userid NOT IN ($Coventry) ";
			}
		}
		
		$lookup = $lookup2 = array();
		foreach (THANKS::$cache['button'] as $buttonid => $button)
		{
			if (!$button['active'])
			{
				// Skip inactive buttons
				continue;
			}
			
			$lookup[$button['varname']] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
			$lookup2[$button['varname']] = $buttonid;
		}
		
		// Load union sql
		$SQL = THANKS::loadUnionSql('widget');
		
		$results = THANKS::$db->fetchAll('
			SELECT
				entry.*,
				user.*
				:avatarSelect
			FROM (
				(' . implode(') UNION ALL (', $SQL) . ')
			) AS entry
			LEFT JOIN $user AS user USING(userid)
			:avatarJoin
			WHERE 1=1
				:globalIgnore
		', array(
			':avatarSelect' => ($this->registry->options['avatarenabled'] ? ',avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight' : ''),
			':avatarJoin' 	=> ($this->registry->options['avatarenabled'] ? 'LEFT JOIN $avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN $customavatar AS customavatar ON(customavatar.userid = user.userid)' : ''),
			':whereCond' 	=> THANKS::$db->queryList($buttons),
			':limit' 		=> $this->config['dbtech_thanks_limit'],
			':globalIgnore' => $globalignore,
		));			
		$resultsToSort = array();
		foreach ($results as $result)
		{
			$resultsToSort[$result['entryid']] = $result;
		}
		krsort($resultsToSort, SORT_NUMERIC);
		
		$recentEntries = array();
		foreach ($resultsToSort as $entryid => $result)
		{
			// Set some important variables
			$result['buttontitle'] = $vbphrase['dbtech_thanks_button_' . $result['varname'] . '_title'];
			$result['buttonimage'] = THANKS::$cache['button'][$lookup2[$result['varname']]]['image'] ? THANKS::$cache['button'][$lookup2[$result['varname']]]['image'] : 'dbtech/thanks/images/' . $result['varname'] . '.png';
			$result['date'] = vbdate($this->registry->options['dateformat'], $result['dateline'], true);
			$result['time'] = vbdate($this->registry->options['timeformat'], $result['dateline']);
			
			// get avatar
			$this->fetch_avatarinfo($result);
			
			// Fetch markup username
			fetch_musername($result);
			
			// Store the result
			$recentEntries[$result['varname']][$entryid] = $result;
		}
		
		foreach ($recentEntries as $varname => $rows)
		{
			while (count($recentEntries[$varname]) > $this->config['dbtech_thanks_limit'])
			{
				// Shorten array
				array_pop($recentEntries[$varname]);
			}
		}
		
		// Load union sql
		$SQL = THANKS::loadUnionSql('widget_top');
		
		$results = THANKS::$db->fetchAll('
			SELECT
				entry.*
			FROM (
				(' . implode(') UNION ALL (', $SQL) . ')
			) AS entry
			LEFT JOIN $user AS user USING(userid)
			WHERE 1=1
				:globalIgnore
		', array(
			':whereCond' 	=> THANKS::$db->queryList($buttons),
			':limit' 		=> $this->config['dbtech_thanks_limit'],
			':globalIgnore' => $globalignore,
			':dateline' 	=> (TIMENOW - ($this->config['dbtech_thanks_datecutoff_amount'] * $this->config['dbtech_thanks_datecutoff_timespan'])),
		));
		
		$resultsToSort = array();
		foreach ($results as $key => $result)
		{
			$resultsToSort[$key] = $result['numentries'];
		}
		arsort($resultsToSort, SORT_NUMERIC);
		
		$topEntries = array();
		foreach ($resultsToSort as $key => $result)
		{
			$result = $results[$key];
			
			// Set some important variables
			$result['buttontitle'] = $lookup[$result['varname']];
			$result['buttonimage'] = THANKS::$cache['button'][$lookup2[$result['varname']]]['image'] ? THANKS::$cache['button'][$lookup2[$result['varname']]]['image'] : 'dbtech/thanks/images/' . $result['varname'] . '.png';

			$topEntries[$result['varname']][] = $result;
		}
		
		foreach ($topEntries as $varname => $rows)
		{
			while (count($topEntries[$varname]) > $this->config['dbtech_thanks_limit'])
			{
				// Shorten array
				array_pop($topEntries[$varname]);
			}
		}
		return array('recent' => $recentEntries, 'top' => $topEntries);
	}

	public function getHTML($entries = false)
	{
		global $vbphrase;

		if (!class_exists('THANKS'))
		{
			// Not displaying any results
			return '';
		}
		
		if (in_array($this->registry->userinfo['usergroupid'], (array)$this->config['dbtech_thanks_usergroups']))
		{
			// No access
			return '';
		}
		
		if (!$entries)
		{
			$entries = $this->getData();
		}
	
		if ($entries)
		{
			foreach ((array)$entries['recent'] as $varname => $rows)
			{
				foreach ($rows as $key => $row)
				{
					// Parses the row and sets title / etc
					THANKS::parseRow($entries['recent'][$varname][$key]);
					
					// trim the title after fetching the url
					//$entries[$key]['title'] = fetch_trimmed_title($row['title'], $this->config['dbtech_thanks_entries_titlemaxchars']);
				}
			}

			foreach ((array)$entries['top'] as $varname => $rows)
			{
				foreach ($rows as $key => $row)
				{
					// Parses the row and sets title / etc
					THANKS::parseRow($entries['top'][$varname][$key]);
					
					// trim the title after fetching the url
					//$entries[$key]['title'] = fetch_trimmed_title($row['title'], $this->config['dbtech_thanks_entries_titlemaxchars']);
				}
			}

			$lookup = array();
			foreach (THANKS::$cache['button'] as $buttonid => $button)
			{
				if (!count($entries['recent'][$button['varname']]) AND !count($entries['top'][$button['varname']]))
				{
					// Skip this
					continue;
				}
				
				$lookup[$button['varname']] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
			}

			global $footer;
			$footer .= '<script type="text/javascript" src="' . THANKS::jQueryPath() . '"></script>';
			$footer .= THANKS::js('_widget', true, false);			

			$templater = vB_Template::create('dbtech_thanks_block_entries');
				$templater->register('blockinfo', 		$this->blockinfo);
				$templater->register('recentEntries', 	$entries['recent']);
				$templater->register('topEntries', 		$entries['top']);
				$templater->register('lookup', 			$lookup);
			return $templater->render();
		}
	}

	/**
	 * Generates a hash used for block caching.
	 * If the block output depends on permissions,
	 * ensure it's unique either per-user or for all
	 * users with similar permissions
	 *
	 * @return string 	The hash
	 */
	public function getHash()
	{
		$context = new vB_Context('forumblock' ,
		array(
			'blockid' 		=> $this->blockinfo['blockid'],
			'permissions' 	=> $this->userinfo['forumpermissions'],
			'ignorelist' 	=> $this->userinfo['ignorelist'],
			THIS_SCRIPT)
		);

		return strval($context);
	}
}