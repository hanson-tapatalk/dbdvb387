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
// Thanks functionality class

/**
* Handles everything to do with Thanks.
*/
class THANKS
{
	/**
	* Version info
	*
	* @public	mixed
	*/
	public static $jQueryVersion 	= '1.7.2';
	public static $version 			= '3.2.7';
	public static $versionnumber	= '327';
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $vbulletin 	= NULL;
	
	/**
	* The database object
	*
	* @private	Thanks_Database
	*/	
	public static $db 				= NULL;
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $prefix 		= 'dbtech_';
	
	/**
	* The vBulletin registry object
	*
	* @private	vB_Registry
	*/	
	protected static $bitfieldgroup	= array(
		'thankspermissions'
	);
	
	/**
	* Array of permissions to be returned
	*
	* @public	array
	*/	
	public static $permissions 		= NULL;
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public static $cache			= array();
	
	/**
	* Whether we've called the DM fetcher
	*
	* @public	boolean
	*/		
	protected static $called		= false;
	
	/**
	* Array of created things
	*
	* @public	array
	*/		
	public static $created		= array();
	
	/**
	* Array of cached items
	*
	* @public	array
	*/		
	public static $unserialize		= array(
		'button' => array(
			'permissions',
			'postfont',
		),
	);
	
	/**
	* Whether we have the pro version or not
	*
	* @public	boolean
	*/		
	public static $isPro		= false;
	
	/**
	* Array of cached entries
	*
	* @public	array
	*/		
	public static $entrycache	= array(
		'data' 			=> array(),
		'display' 		=> array(),
		'others' 		=> array(),
		'clickcount' 	=> array(),
	);
	
	/**
	* Whether we've processed entries
	*
	* @public	boolean
	*/		
	public static $processed	= false;

	
	/**
	* Does important checking before anything else should be going on
	*
	* @param	vB_Registry	Registry object
	*/
	public static function init($vbulletin)
	{
		// Check if the vBulletin Registry is an object
		if (!is_object($vbulletin))
		{
			// Something went wrong here I think
			trigger_error("Registry object is not an object", E_USER_ERROR);
		}
		
		// Set registry
		self::$vbulletin =& $vbulletin;
		
		// Set database object
		self::$db = new Thanks_Database($vbulletin->db);
		
		// Set permissions shorthand
		self::_getPermissions();
		
		// What permissions to override
		$override = array();
		
		foreach ($override as $permname)
		{
			// Override various permissions
			self::$permissions[$permname] = (self::$permissions['ismanager'] ? 1 : self::$permissions[$permname]);
		}
		
		foreach (self::$unserialize as $cachetype => $keys)
		{
			foreach ((array)self::$cache[$cachetype] as $id => $arr)
			{
				foreach ($keys as $key)
				{
					// Do unserialize
					self::$cache[$cachetype][$id][$key] = @unserialize($arr[$key]);
					self::$cache[$cachetype][$id][$key] = (is_array(self::$cache[$cachetype][$id][$key]) ? self::$cache[$cachetype][$id][$key] : array());
				}
			}
		}
		
		// Set pro version
		
		
	}
		
	/**
	* Check if we have permissions to perform an action
	*
	* @param	array		User info
	* @param	array		Permissions info
	*/		
	public static function checkPermissions(&$user, $permissions, $bitIndex)
	{
		if (!$user['usergroupid'] OR (!isset($user['membergroupids']) AND $user['userid']))
		{
			// Ensure we have this
			$user = fetch_userinfo($user['userid']);
		}
		
		if (!is_array($user['permissions']))
		{
			// Ensure we have the perms
			cache_permissions($user);
		}
		
		$ugs = fetch_membergroupids_array($user);		
		if (!$ugs[0])
		{
			// Hardcode guests
			$ugs[0] = 1;
		}
		
		$bits = array(
			'default' 	=> 4
		);
		$bit = $bits[$bitIndex];
		
		//self::$vbulletin->usergroupcache
		foreach ($ugs as $usergroupid)
		{
			$value = $permissions[$usergroupid][$bitIndex];
			$value = (isset($value) ? $value : 0);
			
			switch ($value)
			{
				case 1:
					// Allow
					return true;
					break;
				
				case -1:
					// Usergroup Default		
					if (!($user[self::$prefix . self::$bitfieldgroup[0]] & $bit))
					{
						// Allow by default
						return true;
					}
					break;
			}
		}
		
		// We didn't make it
		return false;
	}
	
	/**
	* Class factory. This is used for instantiating the extended classes.
	*
	* @param	string			The type of the class to be called (user, forum etc.)
	* @param	vB_Registry		An instance of the vB_Registry object.
	* @param	integer			One of the ERRTYPE_x constants
	*
	* @return	vB_DataManager	An instance of the desired class
	*/
	public static function &initDataManager($classtype, &$registry, $errtype = ERRTYPE_STANDARD)
	{
		if (empty(self::$called))
		{
			// include the abstract base class
			require_once(DIR . '/includes/class_dm.php');
			self::$called = true;
		}
	
		if (preg_match('#^\w+$#', $classtype))
		{
			require_once(DIR . '/dbtech/thanks/includes/class_dm_' . strtolower($classtype) . '.php');
	
			$classname = 'Thanks_DataManager_' . $classtype;
			$object = new $classname($registry, $errtype);
	
			return $object;
		}
	}
	
	/**
	* JS class fetcher for AdminCP
	*
	* @param	string	The JS file name or the code
	* @param	boolean	Whether it's a file or actual JS code
	*/
	public static function js($js = '', $file = true, $echo = true)
	{
		$output = '';
		if ($file)
		{
			$output = '<script type="text/javascript" src="' . self::$vbulletin->options['bburl'] . '/dbtech/thanks/clientscript/thanks' . $js . '.js?v=' . self::$versionnumber . '"></script>';
		}
		else
		{
			$output = "
				<script type=\"text/javascript\">
					<!--
					$js
					// -->
				</script>
			";
		}
		
		if ($echo)
		{
			echo $output;
		}
		else
		{
			return $output;
		}
	}
	
	/**
	* Determines the path to jQuery based on browser settings
	*/
	public static function jQueryPath()
	{
		// create the path to jQuery depending on the version
		if (self::$vbulletin->options['customjquery_path'])
		{
			$path = str_replace('{version}', self::$jQueryVersion, self::$vbulletin->options['customjquery_path']);
			if (!preg_match('#^https?://#si', self::$vbulletin->options['customjquery_path']))
			{
				$path = REQ_PROTOCOL . '://' . $path;
			}
			return $path;
		}
		else
		{
			switch (self::$vbulletin->options['remotejquery'])
			{
				case 1:
				default:
					// Google CDN
					return REQ_PROTOCOL . '://ajax.googleapis.com/ajax/libs/jquery/' . self::$jQueryVersion . '/jquery.min.js';
					break;

				case 2:
					// jQuery CDN
					return REQ_PROTOCOL . '://code.jquery.com/jquery-' . self::$jQueryVersion . '.min.js';
					break;

				case 3:
					// Microsoft CDN
					return REQ_PROTOCOL . '://ajax.aspnetcdn.com/ajax/jquery/jquery-' . self::$jQueryVersion . '.min.js';
					break;
			}
		}
	}

	/**
	* @param	integer	Depth of item (0 = no depth, 3 = third level depth)
	* @param	string	Character or string to repeat $depth times to build the depth mark
	* @param	string	Existing depth mark to append to
	*
	* @return	string
	*/
	function getDepthMark($depth, $depthchar, $depthmark = '')
	{
		for ($i = 0; $i < $depth; $i++)
		{
			$depthmark .= $depthchar;
		}
		return $depthmark;
	}

	/**
	* Breaks down a difference (in seconds) into its days / hours / minutes / seconds components.
	*
	* @param	integer	Difference (in seconds)
	*
	* @return	array
	*/
	function getTimeBreakdown($difference)
	{
		
		$breakdown = array();
		
		// Set days
		$breakdown['days'] = intval($difference / 86400);
		$difference -= ($breakdown['days'] * 86400);
		
		// Set hours
		$breakdown['hours'] = intval($difference / 3600);
		$difference -= ($breakdown['hours'] * 3600);
		
		// Set minutes
		$breakdown['minutes'] = intval($difference / 60);
		$difference -= ($breakdown['minutes'] * 60);
		
		// Set seconds
		$breakdown['seconds'] = intval($difference);
		
		return $breakdown;
	}
	
	/**
	* Quick Method of building the CPNav Template
	*
	* @param	string	The selected item in the CPNav
	*/	
	public static function setNavClass($selectedcell = 'main')
	{
		global $navclass;
	
		$cells = array(
			'main',
			
			'hottest',
			'statistics',
			'list',
		);
	
		//($hook = vBulletinHook::fetch_hook('usercp_nav_start')) ? eval($hook) : false;
		
		// set the class for each cell/group
		$navclass = array();
		foreach ($cells AS $cellname)
		{
			$navclass[$cellname] = (intval(self::$vbulletin->versionnumber) == 3 ? 'alt2' : 'inactive');
		}
		$navclass[$selectedcell] = (intval(self::$vbulletin->versionnumber) == 3 ? 'alt1' : 'active');
		
		//($hook = vBulletinHook::fetch_hook('usercp_nav_complete')) ? eval($hook) : false;
	}
	
	/**
	* Escapes a string and makes it JavaScript-safe
	*
	* @param	mixed	The string or array to make JS-safe
	*/	
	public static function jsEscapeString(&$arr)
	{
		$find = array(
			"\r\n",
			"\n",
			"\t",
			'"'
		);
		
		$replace = array(
			'\r\n',
			'\n',
			'\t',
			'\"',
		);
		
		$arr = str_replace($find, $replace, $arr);
	}
	
	/**
	* Encodes a string as a JSON object (consistent behaviour instead of relying on PHP built-in functions)
	*
	* @param	mixed	The string or array to encode
	* @param	boolean	(Optional) Whether this is an associative array
	* @param	boolean	(Optional) Whether we should escape the string or if they have already been escaped
	*/	
	public static function encodeJSON($arr, $assoc = true, $doescape = true)
	{
		if ($doescape)
		{
			self::jsEscapeString($arr);
		}
		if (!$assoc)
		{
			// Not associative, simple return
			return '{"' . implode('","', $arr) . '"}';
		}
		
		$content = array();
		foreach ((array)$arr as $key => $val)
		{
			if (is_array($val))
			{
				// Recursion, definition: see recursion
				$val = self::encodeJSON($val);
				$content[] = '"' . $key . '":' . $val;
			}
			else
			{
				$content[] = '"' . $key . '":"' . $val . '"';
			}
		}
		
		return '{' . implode(',', $content) . '}';
	}

	/**
	* Outputs an XML string to the browser 
	*
	* @param	mixed	array to output
	*/
	public static function outputXML($arr)
	{
		require_once(DIR . '/includes/class_xml.php');

		$xml = new vB_AJAX_XML_Builder(self::$vbulletin, 'text/xml');
			$xml->add_group('aptl');
				
				if (count($arr['colorOptions']))
				{
					$xml->add_group('colorOptions');
					foreach ($arr['colorOptions'] as $varname => $options)
					{
						foreach ($options as $num => $info)
						{
							$xml->add_tag('colorOption', $info['color'], array('numclicks' => $num, 'varname' => $varname, 'settings' => $info['settings']));
						}
					}
					$xml->close_group();
				}

				if (count($arr['thanksEntries']))
				{
					$xml->add_group('thanksEntries');
					foreach ($arr['thanksEntries'] as $varname => $num)
					{
						$xml->add_tag('thanksEntry', $varname, array('numclicks' => $num));
					}
					$xml->close_group();
				}

				foreach (array(
					'entries',
					'actions',
					'error',
				) as $key)
				{
					if (!isset($arr[$key]))
					{
						continue;
					}

					// Singular values
					$xml->add_tag($key, 		$arr[$key]);
				}

			$xml->close_group();
		$xml->print_xml();
	}
	
	/**
	* Outputs a JSON string to the browser 
	*
	* @param	mixed	array to output
	*/	
	public static function outputJSON($json, $full_shutdown = false)
	{
		if (!headers_sent())
		{
			// Set the header
			header('Content-type: application/json');
		}
		
		// Create JSON
		$json = self::encodeJSON($json);
		
		// Turn off debug output
		self::$vbulletin->debug = false;
		
		if (defined('VB_API') AND VB_API === true)
		{
			print_output($json);
		}

		//run any registered shutdown functions
		if (intval(self::$vbulletin->versionnumber) > 3)
		{
			$GLOBALS['vbulletin']->shutdown->shutdown();
		}
		exec_shut_down();
		self::$vbulletin->db->close();
		
		$sendHeader = false;
		switch(self::$vbulletin->options['ajaxheader'])
		{
			case 0 :
				$sendHeader = true;
				
			case 1 :
				$sendHeader = false;
				
			case 2 :
			default:
				$sendHeader = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false);
		}

		if ($sendHeader)
		{
			// this line is causing problems with mod_gzip/deflate, but is needed for some IIS setups
			@header('Content-Length: ' . strlen($json));
		}
		
		// Finally spit out JSON
		echo $json;
		die();
	}
	
	/**
	* Constructs some <option>s for use in the templates
	*
	* @param	array	The key:value data array
	* @param	mixed	(Optional) The selected id(s)
	* @param	boolean	(Optional) Whether we should HTMLise the values
	*/	
	public static function createSelectOptions($array, $selectedid = '', $htmlise = false)
	{
		if (!is_array($array))
		{
			return '';
		}
		
		$options = '';
		foreach ($array as $key => $val)
		{
			if (is_array($val))
			{
				// Create the template
				$templater = vB_Template::create('optgroup');
					$templater->register('optgroup_label', 	($htmlise ? htmlspecialchars_uni($key) : $key));
					$templater->register('optgroup_options', self::createSelectOptions($val, $selectedid, $tabindex, $htmlise));
				$options .= $templater->render();
			}
			else
			{
				if (is_array($selectedid))
				{
					$selected = iif(in_array($key, $selectedid), ' selected="selected"', '');
				}
				else
				{
					$selected = iif($key == $selectedid, ' selected="selected"', '');
				}
				
				$templater = vB_Template::create('option');
					$templater->register('optionvalue', 	($key !== 'no_value' ? $key : ''));
					$templater->register('optionselected', 	$selected);
					$templater->register('optiontitle', 	($htmlise ? htmlspecialchars_uni($val) : $val));
				$options .= $templater->render();
			}
		}
		
		return $options;
	}
	
	/**
	* Constructs a time selector
	*
	* @param	string	The title of the time select
	* @param	name	(Optional) The HTML form name
	* @param	array	(Optional) The time we should start with
	* @param	name	(Optional) The vertical align state
	* 
	* @return	string	The constructed time row
	*/	
	public static function timeRow($title, $name = 'date', $unixtime = '', $valign = 'middle')
	{
		global $vbphrase, $vbulletin;
		
		$output = '';
	
		$monthnames = array(
			0  => '- - - -',
			1  => $vbphrase['january'],
			2  => $vbphrase['february'],
			3  => $vbphrase['march'],
			4  => $vbphrase['april'],
			5  => $vbphrase['may'],
			6  => $vbphrase['june'],
			7  => $vbphrase['july'],
			8  => $vbphrase['august'],
			9  => $vbphrase['september'],
			10 => $vbphrase['october'],
			11 => $vbphrase['november'],
			12 => $vbphrase['december'],
		);
	
		if (is_array($unixtime))
		{
			require_once(DIR . '/includes/functions_misc.php');
			$unixtime = vbmktime(0, 0, 0, $unixtime['month'], $unixtime['day'], $unixtime['year']);
		}
	
		if ($unixtime)
		{
			$month = vbdate('n', $unixtime, false, false);
			$day = vbdate('j', $unixtime, false, false);
			$year = vbdate('Y', $unixtime, false, false);
			$hour = vbdate('G', $unixtime, false, false);
			$minute = vbdate('i', $unixtime, false, false);
		}
	
		$cell = array();
		$cell[] = "<label for=\"{$name}_month\">$vbphrase[month]</label><br /><select name=\"{$name}[month]\" id=\"{$name}_month\" tabindex=\"1\" class=\"primary select\"" . iif($vbulletin->debug, " title=\"name=&quot;$name" . "[month]&quot;\"") . ">\n" . self::createSelectOptions($monthnames, $month) . "\t\t</select>";
		$cell[] = "<label for=\"{$name}_date\">$vbphrase[day]</label><br /><input type=\"text\" class=\"primary textbox\" name=\"{$name}[day]\" id=\"{$name}_date\" value=\"$day\" size=\"4\" maxlength=\"2\" tabindex=\"1\"" . iif($vbulletin->debug, " title=\"name=&quot;$name" . "[day]&quot;\"") . ' />';
		$cell[] = "<label for=\"{$name}_year\">$vbphrase[year]</label><br /><input type=\"text\" class=\"primary textbox\" name=\"{$name}[year]\" id=\"{$name}_year\" value=\"$year\" size=\"4\" maxlength=\"4\" tabindex=\"1\"" . iif($vbulletin->debug, " title=\"name=&quot;$name" . "[year]&quot;\"") . ' />';
		$inputs = '';
		foreach($cell AS $html)
		{
			$inputs .= "\t\t<td style=\"padding-left:6px;\"><span class=\"smallfont\">$html</span></td>\n";
		}
		
		$output .= "<div id=\"ctrl_$name\" class=\"" . (intval(self::$vbulletin->versionnumber) == 3 ? 'alt1' : 'blockrow') . "\">$title: <table cellpadding=\"0\" cellspacing=\"2\" border=\"0\"><tr>\n$inputs\t\n</tr></table></div><br />";	
		
		return $output;
	}
	
	/**
	* Grabs what permissions we have got
	*/
	protected static function _getPermissions()
	{
		if (!self::$vbulletin->userinfo['permissions'])
		{
			// For some reason, this is missing
			cache_permissions(self::$vbulletin->userinfo);
		}
		
		foreach (self::$bitfieldgroup as $bitfieldgroup)
		{
			// Override bitfieldgroup variable
			$bitfieldgroup = self::$prefix . $bitfieldgroup;
			
			if (!is_array(self::$vbulletin->bf_ugp[$bitfieldgroup]))
			{
				// Something went wrong here I think
				require_once(DIR . '/includes/class_bitfield_builder.php');
				if (vB_Bitfield_Builder::build(false) !== false)
				{
					$myobj =& vB_Bitfield_Builder::init();
					if (sizeof($myobj->data['ugp'][$bitfieldgroup]) != sizeof(self::$vbulletin->bf_ugp[$bitfieldgroup]))
					{
						require_once(DIR . '/includes/adminfunctions.php');
						$myobj->save(self::$vbulletin->db);
						build_forum_permissions();
						
						if (IN_CONTROL_PANEL === true)
						{
							define('CP_REDIRECT', self::$vbulletin->scriptpath);
							print_stop_message('rebuilt_bitfields_successfully');
						}
						else
						{
							self::$vbulletin->url = self::$vbulletin->scriptpath;
							eval(print_standard_redirect(array('redirect_updatethanks', self::$vbulletin->userinfo['username']), true, true));
						}
					}
				}
				else
				{
					echo "<strong>error</strong>\n";
					print_r(vB_Bitfield_Builder::fetch_errors());
					die();
				}
			}
			
			foreach ((array)self::$vbulletin->bf_ugp[$bitfieldgroup] as $permname => $bit)
			{
				// Set the permission
				self::$permissions[$permname] = (!$bit ? self::$vbulletin->userinfo['permissions'][$bitfieldgroup][$permname] : (self::$vbulletin->userinfo['permissions'][$bitfieldgroup] & $bit ? 1 : 0));
			}
		}
	}
	
	/**
	* Constructs a bitfield row
	*/	
	public static function bitfieldRow($text, $name, $bitfield, $value)
	{
		global $vbulletin, $vbphrase;
	
		require_once(DIR . '/includes/adminfunctions.php');
		require_once(DIR . '/includes/adminfunctions_options.php');
		
		// make sure all rows use the alt1 class
		$bgcounter--;
	
		$value = intval($value);
		$HTML = '';
		$bitfielddefs =& fetch_bitfield_definitions($bitfield);
	
		if ($bitfielddefs === NULL)
		{
			print_label_row($text, construct_phrase("<strong>$vbphrase[settings_bitfield_error]</strong>", implode(',', vB_Bitfield_Builder::fetch_errors())), '', 'top', $name, 40);
		}
		else
		{
			#$HTML .= "<fieldset><legend>$vbphrase[yes] / $vbphrase[no]</legend>";
			$HTML .= "<div id=\"ctrl_{$name}\" class=\"smallfont\">\r\n";
			$HTML .= "<input type=\"hidden\" name=\"{$name}[0]\" value=\"0\" />\r\n";
			foreach ($bitfielddefs AS $key => $val)
			{
				$val = intval($val);
				$HTML .= "<table style=\"width:175px; float:left\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr valign=\"top\">
				<td><input type=\"checkbox\" name=\"{$name}[$val]\" id=\"{$name}_$key\" value=\"$val\"" . (($value & $val) ? ' checked="checked"' : '') . " /></td>
				<td width=\"100%\" style=\"padding-top:4px\"><label for=\"{$name}_$key\" class=\"smallfont\">" . fetch_phrase_from_key($key) . "</label></td>\r\n</tr></table>\r\n";
			}
	
			$HTML .= "</div>\r\n";
			#$HTML .= "</fieldset>";
			print_label_row($text, $HTML, '', 'top', $name, 40);
		}		
	}	
	
	/**
	* Fetches all valid forum ids
	*
	* @return	array	List of forum ids we can access
	*/	
	public static function getForumIds()
	{
		$forumcache = self::$vbulletin->forumcache;
		/*
		$excludelist = explode(',', self::$vbulletin->options['dbtech_infopanels_forum_exclude']);
		foreach ($excludelist AS $key => $excludeid)
		{
			$excludeid = intval($excludeid);
			unset($forumcache[$excludeid]);
		}
		*/
	
		$forumids = array_keys($forumcache);
		
		// get forum ids for all forums user is allowed to view
		foreach ($forumids AS $key => $forumid)
		{
			if (is_array($includearray) AND empty($includearray[$forumid]))
			{
				unset($forumids[$key]);
				continue;
			}
	
			$fperms =& self::$vbulletin->userinfo['forumpermissions'][$forumid];
			$forum =& self::$vbulletin->forumcache[$forumid];
	
			if (!((int)$fperms & (int)self::$vbulletin->bf_ugp_forumpermissions['canview']) OR !((int)$fperms & (int)self::$vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR !verify_forum_password($forumid, $forum['password'], false))
			{
				unset($forumids[$key]);
			}
		}
		
		// Those shouts with 0 as their forumid
		$forumids[] = 0;
		
		return $forumids;
	}
	
	/**
	* Parses the [HIDE] BBCode
	*
	* @param	string	Original message
	* @param	string	Overriding
	*/
	public static function doBBCode(&$message, $override)
	{
		if (!self::$isPro)
		{
			// We may or may not require something
			$message = $override;
		}
		else
		{
			// We may or may not require something
			$message = preg_replace('/\[hide\](.*)\[\/hide\]/isU', $override, $message);
		}
	}
	
	/**
	* Entry cache parser for the front-end
	*/
	public static function processEntryCache()
	{
		self::$processed = true;
		
		foreach ((array)self::$entrycache['data'] as $postid => $entrytypes)
		{
			foreach ($entrytypes as $entrytype => $results)
			{
				// Count how many of each result we have
				$i = 0;
				
				// Reset the array
				self::$entrycache['display'][$postid][$entrytype] = array();
				self::$entrycache['count'][$postid][$entrytype] = 0;
				
				foreach ($results as $key => $result)
				{
					self::$entrycache['count'][$postid][$entrytype]++;
					if (self::$isPro AND self::$vbulletin->options['dbtech_thanks_postbit_maxusers'])
					{										
						// Check how many we've done
						$i++;
						
						if ($i > self::$vbulletin->options['dbtech_thanks_postbit_maxusers'])
						{
							// Increment the "others" counter
							self::$entrycache['others'][$postid][$entrytype]++;
							continue;				
						}
					}
					
					// Fetch musername
					fetch_musername($result);
					
					self::$entrycache['display'][$postid][$entrytype][$result['userid']] = '<a href="member.php?' . self::$vbulletin->session->vars['sessionurl'] . 'u=' . $result['userid'] . '" target="_blank" title="' . vbdate(self::$vbulletin->options['dateformat'], $result['dateline']) . ' ' . vbdate(self::$vbulletin->options['timeformat'], $result['dateline']) . '">' . trim($result['musername']) . '</a>';
				}
			}
		}	
	}
	
	/**
	* Processes the display of entries and actions
	*
	* @param	array	Post info
	* @param	array	Thread info
	*
	* @return 	array	HTML code for buttons and entries
	*/	
	public static function processDisplay($noticeforum, $excluded, $post, $thread, $contenttype = 'post')
	{
		global $vbphrase, $show;
		
		$entries = '';
		$actions = '';
		
		foreach ((array)self::$cache['button'] as $button)
		{
			if (!$button['active'])
			{
				// Inactive button
				continue;
			}

			// Override this
			$button['title'] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
			
			if (!empty(self::$entrycache['display'][$post['postid']][$button['varname']]) AND self::$vbulletin->options['dbtech_thanks_cloud_displaystyle'] != 2)
			{
				// List of users who clicked
				$users = implode(', ', (array)self::$entrycache['display'][$post['postid']][$button['varname']]);
				$otherusers = self::$entrycache['others'][$post['postid']][$button['varname']];
				
				

				$button['buttonimage'] = $button['image'] ? $button['image'] : 'dbtech/thanks/images/' . $button['varname'] . '.png';

				$button['listtext'] = $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_listtext'];

				$templater = vB_Template::create('dbtech_thanks_postbit_entries_entrybit');
					$templater->register('post', 		$post);		
					$templater->register('button', 		$button);
					$templater->register('users', 		$users);
					$templater->register('otherusers', 	$otherusers);
				$entries .= $templater->render();
			}
			
			if ((int)$thread['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
			{
				// Button was disabled for this thread
				continue;
			}
			
			if ((int)$post['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
			{
				// break was disabled for this post
				continue;
			}

			switch ($contenttype)
			{
				case 'post':
					if ((int)self::$vbulletin->forumcache[$noticeforum]['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
					{
						// Button was disabled for this forum
						continue 2;
					}
									
					if (((int)self::$vbulletin->forumcache[$noticeforum]['dbtech_thanks_firstpostonly'] & (int)$button['bitfield']) AND 
						$thread['firstpostid'] != $post['postid']
					)
					{
						// First Post Only
						continue 2;
					}
					break;
				
				case 'blog':
					if ((int)$button['disableintegration'] & 1)
					{
						// Button was disabled for this forum
						self::$cache['button'][$button['buttonid']]['active'] = 0;
						continue 2;
					}
					break;
				
				case 'dbgallery_image':
					if ((int)$button['disableintegration'] & 2)
					{
						// Button was disabled for this forum
						self::$cache['button'][$button['buttonid']]['active'] = 0;
						continue 2;
					}
					break;
				
				case 'socialgroup':
					if ((int)$button['disableintegration'] & 4)
					{
						// Button was disabled for this forum
						self::$cache['button'][$button['buttonid']]['active'] = 0;
						continue 2;
					}
					break;
				
				case 'usernote':
					if ((int)$button['disableintegration'] & 8)
					{
						// Button was disabled for this forum
						self::$cache['button'][$button['buttonid']]['active'] = 0;
						continue 2;
					}
					break;
				
				case 'visitormessage':
					if ((int)$button['disableintegration'] & 16)
					{
						// Button was disabled for this forum
						self::$cache['button'][$button['buttonid']]['active'] = 0;
						continue 2;
					}
					break;
				
				case 'blog':
					if ((int)$button['disableintegration'] & 1)
					{
						// Button was disabled for this forum
						self::$cache['button'][$button['buttonid']]['active'] = 0;
						continue 2;
					}
					break;
			}
			
			$button['canclick'] = false;
			do
			{
				if (!self::$vbulletin->userinfo['userid'])
				{
					// Guests can't thank
					break;
				}
				
				if (self::$vbulletin->userinfo['dbtech_thanks_excluded'])
				{
					// Excluded users can't do this
					break;
				}
				
				if (self::$vbulletin->userinfo['userid'] == $post['userid'])
				{
					// Can't click own posts' buttons
					break;
				}
				
				if (self::$entrycache['data'][$post['postid']][$button['varname']][self::$vbulletin->userinfo['userid']])
				{
					if (!self::checkPermissions(self::$vbulletin->userinfo, $button['permissions'], 'canunclick') OR !self::$isPro)
					{
						// We can't un-click this button
						break;
					}
				}
				else
				{
					if (!self::checkPermissions(self::$vbulletin->userinfo, $button['permissions'], 'canclick'))
					{
						// We can't click this button
						break;
					}
				}
				
				$userinfo = $post;
				if (self::checkPermissions($userinfo, $button['permissions'], 'immune'))
				{
					// The target user is immune to this button click
					break;
				}
				
				if (self::$vbulletin->userinfo['posts'] < $button['minposts'])
				{
					// Too few posts
					break;
				}
				
				if (in_array($button['varname'], $excluded))
				{
					// We clicked another button that prevented this button click
					break;
				}

				

				// We can click!
				$button['canclick'] = true;
			}
			while (false);

			if ($button['canclick'] OR self::$vbulletin->options['dbtech_thanks_cloud_displaystyle'] == 2)
			{
				$button['clickcount'] = (int)self::$entrycache['count'][$post['postid']][$button['varname']];

				if (self::$entrycache['data'][$post['postid']][$button['varname']][self::$vbulletin->userinfo['userid']])
				{
					$button['buttonimage'] = $button['image'] ? $button['image'] : 'dbtech/thanks/images/' . $button['varname'] . '.png';
					
				}
				else
				{
					$button['buttonimage'] = $button['image'] ? $button['image'] : 'dbtech/thanks/images/' . $button['varname'] . '.png';
				}
				
				$show['thanks_posfix'] = in_array($contenttype, array('post', 'usernote'));
				$templater = vB_Template::create(((self::$vbulletin->options['dbtech_thanks_notext'] OR self::$vbulletin->options['dbtech_thanks_cloud_displaystyle'] == 2) ? 'dbtech_thanks_postbit_entries_actionbit_notext' : 'dbtech_thanks_postbit_entries_actionbit'));
					$templater->register('post', 	$post);
					$templater->register('button', 	$button);
					$templater->register('phrase', 	(!self::$entrycache['data'][$post['postid']][$button['varname']][self::$vbulletin->userinfo['userid']] ? $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_actiontext'] : $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_undotext']));
				$actions .= $templater->render();
			}
		}
		
		return (array($entries, $actions));
	}
	
	/**
	* Processes the display of entries and actions
	*
	* @return 	array	Arrays of entries for use in the colour checker
	*/	
	public static function processEntries()
	{
		$colorOptions = array();
		foreach ((array)self::$cache['button'] as $buttonid => $button)
		{
			$colorOptionsSort = array();			
			foreach ($button['postfont'] as $key => $arr)
			{
				if (!$arr['threshold'])
				{
					// Skip this
					continue;
				}
				
				$colorOptionsSort[$key] = $arr['threshold'];
			}
			arsort($colorOptionsSort, SORT_NUMERIC);
			
			foreach ($colorOptionsSort as $key => $num)
			{
				// Sorted list
				$colorOptions[$button['varname']][$num] = array('color' => $button['postfont'][$key]['color'], 'settings' => $button['postfont'][$key]['settings']);
			}
		}
		
		$thanksEntries = array();
		foreach ((array)self::$entrycache['data'] as $postid => $entrytypes)
		{
			foreach ($entrytypes as $entrytype => $results)
			{
				// Store number of entries
				$thanksEntries[$postid][$entrytype] = count($results);
			}
		}	
		
		foreach ($thanksEntries as $postid => $entrytypes)
		{
			if (count($entrytypes) == 1)
			{
				// Skip this post
				continue;
			}
			
			$highest = array(
				'varname' 	=> '',
				'num' 		=> ''
			);
			
			foreach ($entrytypes as $entrytype => $num)
			{
				if ($num > $highest['num'])
				{
					$highest['varname'] = $entrytype;
					$highest['num'] 	= $num;
				}
			}
			
			foreach ($entrytypes as $entrytype => $num)
			{
				if ($entrytype == $highest['varname'])
				{
					// Highest
					continue;
				}
				
				// We don't need this no moar
				unset($thanksEntries[$postid][$entrytype]);
			}
		}	
		return (array($colorOptions, $thanksEntries));
	}
	
	/**
	* Refreshes the content for an AJAX post
	*
	* @param	integer	The content id we're refreshing
	* @param	string	The content type we're refreshing
	*
	* @return	array	Array of excluded buttons
	*/
	public static function refreshAjaxPost($contentid, $contenttype = 'post')
	{
		// Grab entry data
		self::fetchEntriesByContent($contentid, $contenttype);
		
		
		
		return self::doButtonExclusive(array('postid' => $contentid));
	}

	/**
	* Grabs all entries by content ID and content type
	*
	* @param	integer	The content id we're refreshing
	* @param	string	The content type we're refreshing
	*/
	public static function fetchEntriesByContent($contentid, $contenttype = 'post')
	{
		global $vbphrase;

		// Init this
		self::$entrycache = array(
			'data' 			=> array(),
			'display' 		=> array(),
			'others' 		=> array(),
			'clickcount' 	=> array(),
		);

		if (!is_array($contentid))
		{
			// Make sure this is an array
			$contentid = array($contentid);
		}

		// Grab our entry, if it exists
		if (!$cacheEntries = self::$db->fetchAll('
			SELECT contentid, data FROM $dbtech_thanks_entrycache
			WHERE contentid :contentId 
				AND contenttype = ?
		', array(
			':contentId' 	=> self::$db->queryList($contentid),
			$contenttype
		)))
		{
			// We didn't have anything to init
			return;
		}

		$userIds = array();
		foreach ($cacheEntries as $cacheEntry)
		{
			// Grab our entry list
			$data = unserialize($cacheEntry['data']);

			foreach ($data as $entryid => $entry)
			{
				// Store all user ids
				$userIds[] = $entry['userid'];
			}

			unset($data);
		}

		if (count($userIds))
		{
			$userIds = array_unique($userIds);

			$userInfo = self::$db->fetchAllKeyed('
				SELECT
					userid,
					username,
					usergroupid,
					membergroupids,
					infractiongroupid,
					displaygroupid
					:vBShop				
				FROM $user
				WHERE userid :queryList
			', 'userid', array(
				':vBShop' 		=> (self::$vbulletin->products['dbtech_vbshop'] ? ", dbtech_vbshop_purchase" : ''),
				':queryList' 	=> self::$db->queryList($userIds)
			));

			foreach ($cacheEntries as $cacheEntry)
			{
				// Grab our entry list
				$data = unserialize($cacheEntry['data']);

				$userIds = array();
				foreach ($data as $entryid => $entry)
				{
					if (!is_array($userInfo[$entry['userid']]))
					{
						// Default information
						$userInfo[$entry['userid']] = array(
							'userid' 				=> 0,
							'username' 				=> $vbphrase['n_a'],
							'usergroupid' 			=> 1,
							'membergroupids' 		=> '',
							'infractiongroupid' 	=> 0,
							'displaygroupid' 		=> 0,
						);
					}

					// Finally store the entry cache we need
					self::$entrycache['data'][$entry['contentid']][$entry['varname']][$entry['userid']] = array_merge($entry, $userInfo[$entry['userid']]);
				}

				unset($data);
			}
		}

		
	}

	

	/**
	* Fetches the list of excluded buttons
	*
	* @param	array	Post info
	*
	* @return	array	Array of excluded buttons
	*/
	public static function doButtonExclusive($post)
	{
		$excluded = array();
		foreach ((array)self::$cache['button'] as $button)
		{
			if (!self::$entrycache['data'][$post['postid']][$button['varname']][self::$vbulletin->userinfo['userid']] OR !$button['exclusivity'])
			{
				// No exclusivity or no clicks
				continue;
			}
			
			foreach (self::$cache['button'] as $excludebutton)
			{
				if (!((int)$button['exclusivity'] & (int)$excludebutton['bitfield']))
				{
					// No exclusivity
					continue;
				}
				
				// This button is exclusive
				$excluded[] = $excludebutton['varname'];
			}
		}
		
		return $excluded;		
	}
	
	/**
	* Fetches the list of excluded buttons
	*
	* @param	string	The action file to load
	*
	* @return	array	Array of SQL items
	*/
	public static function loadUnionSql($action, $buttons = NULL)
	{
		$SQL = array();
		if (file_exists(DIR . '/dbtech/thanks/includes/sql/' . $action . '.php'))
		{
			// Grab ze code
			require(DIR . '/dbtech/thanks/includes/sql/' . $action . '.php');
		}
		return $SQL;
	}
	
	public static function parseRow(&$row)
	{
		global $vbphrase;
		
		$lookup = array();
		foreach ((array)self::$cache['button'] as $buttonid => $button)
		{
			$lookup[$button['varname']] = $button;
		}		

		if (!file_exists(DIR . '/dbtech/thanks_pro/contenttypes/' . $row['contenttype'] . '/parse.php'))
		{
			if (file_exists(DIR . '/dbtech/thanks/contenttypes/' . $row['contenttype'] . '/parse.php'))
			{
				// We can do this
				require(DIR . '/dbtech/thanks/contenttypes/' . $row['contenttype'] . '/parse.php');
			}		
		}
		else
		{
			// We can do this
			require(DIR . '/dbtech/thanks_pro/contenttypes/' . $row['contenttype'] . '/parse.php');
		}
		
		
	}
}

// #############################################################################
// database functionality class

/**
* Class that handles database wrapper
*/
class Thanks_Database
{
	/**
	* The vBulletin database object
	*
	* @private	vB_Database
	*/		
	private $db;
	
	/**
	* The query result we executed
	*
	* @private	MySQL_Result
	*/	
	private $result;
	
	/**
	* The query result we executed
	*
	* @private	MySQL_Result
	*/	
	private $resultLoopable;
	
	/**
	* Whether we're debugging output
	*
	* @public	boolean
	*/	
	public $debug = false;


	/**
	* Does important checking before anything else should be going on
	*
	* @param	vB_Registry		Registry object
	*/
	function __construct($dbobj)
	{
		$this->db = $dbobj;
	}

	/**
	 * Hides DB errrors
	 * 
	 * @return void
	 */
	public function hideErrors()
	{
		$this->db->hide_errors();
	}

	/**
	 * Shows DB errrors
	 * 
	 * @return void
	 */
	public function showErrors()
	{
		$this->db->show_errors();
	}

	/**
	 * Inserts a table row with specified data.
	 *
	 * @param mixed $table The table to insert data into.
	 * @param array $bind Column-value pairs.
	 * @param array $exclusions Array of field names that should be ignored from the $queryvalues array
	 * @param boolean $displayErrors Whether SQL errors should be displayed
	 * @param string $type Whether it's insert, insert ignore or replace
	 * 
	 * @return int The number of affected rows.
	 */
	public function insert($table, array $bind, array $exclusions = array(), $displayErrors = true, $type = 'insert')
	{
		// Store the query
		$sql = fetch_query_sql($bind, $table, '', $exclusions);

		switch ($type)
		{
			case 'ignore':
				$sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
				break;

			case 'replace':
				$sql = str_replace('INSERT INTO', 'REPLACE INTO', $sql);
				break;
		}
		
		if ($this->debug)
		{
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			die();
		}
		
		if (!$displayErrors)
		{
			$this->db->hide_errors();
		}
		$this->db->query_write($sql);
		if (!$displayErrors)
		{
			$this->db->show_errors();
		}

		// Return insert ID if only one row was inserted, otherwise return number of affected rows
		$affected = $this->db->affected_rows();
		return($affected === 1 ? $this->db->insert_id() : $affected);
	}

	/**
	 * Inserts a table row with specified data, ignoring duplicates.
	 *
	 * @param mixed $table The table to insert data into.
	 * @param array $bind Column-value pairs.
	 * @param array $exclusions Array of field names that should be ignored from the $queryvalues array
	 * @param boolean $displayErrors Whether SQL errors should be displayed
	 * 
	 * @return int The number of affected rows.
	 */
	public function insertIgnore($table, array $bind, array $exclusions = array(), $displayErrors = true)
	{
		return $this->insert($table, $bind, $exclusions, $displayErrors, 'ignore');
	}

	/**
	 * Inserts a table row with specified data, replacing duplicates.
	 *
	 * @param mixed $table The table to insert data into.
	 * @param array $bind Column-value pairs.
	 * @param array $exclusions Array of field names that should be ignored from the $queryvalues array
	 * @param boolean $displayErrors Whether SQL errors should be displayed
	 * 
	 * @return int The number of affected rows.
	 */
	public function replace($table, array $bind, array $exclusions = array(), $displayErrors = true)
	{
		return $this->insert($table, $bind, $exclusions, $displayErrors, 'replace');
	}
	
	/**
	 * Updates table rows with specified data based on a WHERE clause.
	 *
	 * @param  mixed		$table The table to update.
	 * @param  array		$bind  Column-value pairs.
	 * @param  mixed		$where UPDATE WHERE clause(s).
	 * @param  mixed		$exclusions Array of field names that should be ignored from the $queryvalues array
	 * 
	 * @return int		  The number of affected rows.
	 */
	public function update($table, array $bind, $where, array $exclusions = array())
	{
		$sql = fetch_query_sql($bind, $table, $where, $exclusions);
		
		if ($this->debug)
		{
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			die();
		}
		
		$this->db->query_write($sql);
		return $this->db->affected_rows();
	}
	
	/**
	 * Deletes table rows based on a WHERE clause.
	 *
	 * @param  mixed		$table The table to update.
	 * @param  mixed  		$bind Data to bind into DELETE placeholders.
	 * @param  mixed		$where DELETE WHERE clause(s).
	 * 
	 * @return int		  The number of affected rows.
	 */
	public function delete($table, array $bind, $where = '')
	{
		/**
		 * Build the DELETE statement
		 */
		$sql = "DELETE FROM "
			 . TABLE_PREFIX . $table
			 . ' ' . $where;

		/**
		 * Execute the statement and return the number of affected rows
		 */
		$result = $this->query($sql, $bind, 'query_write');
		return $this->db->affected_rows();
	}
	
	/**
	 * Fetches all SQL result rows as a sequential array.
	 *
	 * @param string $sql  An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * 
	 * @return array
	 */
	public function fetchAll($sql, $bind = array())
	{
		$results = array();
		
		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$results[] = $row;
		}
		return $results;
	}
	
	/**
	 * Fetches all SQL result rows and returns loopable object.
	 *
	 * @param string $sql  An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * 
	 * @return array
	 */
	public function fetchAllObject($sql, $bind = array())
	{
		$this->resultLoopable = $this->query($sql, $bind, 'query_read');
		return $this->resultLoopable;
	}
	
	/**
	 * Fetches all SQL result rows and returns loopable object.
	 *
	 * @param string $sql  An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * 
	 * @return array
	 */
	public function fetchCurrent()
	{
		return $this->db->fetch_array($this->resultLoopable);
	}
	
	/**
	 * Fetches results from the database with a specified column from each row keyed according to preference.
	 * The 'key' parameter provides the column name with which to key the result.
	 * The 'column' parameter provides the column name with which to use as the result.
	 * For example, calling fetchAllSingleKeyed('SELECT item_id, title, date FROM table', 'item_id', 'title')
	 * would result in an array keyed by item_id:
	 * [$itemId] => $title
	 *
	 * Note that the specified key must exist in the query result, or it will be ignored.
	 *
	 * @param string SQL to execute
	 * @param string Column with which to key the results array
	 * @param string Column to use as the result for that key
	 * @param mixed Parameters for the SQL
	 *
	 * @return array
	 */
	public function fetchAllSingleKeyed($sql, $key, $column, $bind = array())
	{
		$results = array();
		$i = 0;

		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$results[(isset($row[$key]) ? $row[$key] : $i)] = $row[$column];
			$i++;
		}

		return $results;
	}
	
	/**
	 * Fetches results from the database with each row keyed according to preference.
	 * The 'key' parameter provides the column name with which to key the result.
	 * For example, calling fetchAllKeyed('SELECT item_id, title, date FROM table', 'item_id')
	 * would result in an array keyed by item_id:
	 * [$itemId] => array('item_id' => $itemId, 'title' => $title, 'date' => $date)
	 *
	 * Note that the specified key must exist in the query result, or it will be ignored.
	 *
	 * @param string SQL to execute
	 * @param string Column with which to key the results array
	 * @param mixed Parameters for the SQL
	 *
	 * @return array
	 */
	public function fetchAllKeyed($sql, $key, $bind = array())
	{
		$results = array();
		$i = 0;

		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$results[(isset($row[$key]) ? $row[$key] : $i)] = $row;
			$i++;
		}

		return $results;
	}

	/**
	 * Fetches all SQL result rows as an associative array.
	 *
	 * The first column is the key, the entire row array is the
	 * value.  You should construct the query to be sure that
	 * the first column contains unique values, or else
	 * rows with duplicate values in the first column will
	 * overwrite previous data.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed $bind Data to bind into SELECT placeholders.
	 * 
	 * @return array
	 */
	public function fetchAssoc($sql, $bind = array())
	{
		$data = array();
		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			$key = key($row);
			$data[$row[$key]] = $row;
		}
		return $data;
	}	
	
	/**
	 * Fetches the first row of the SQL result.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * @param mixed  $fetchMode Override current fetch mode.
	 * 
	 * @return array
	 */
	public function fetchRow($sql, $bind = array())
	{
		// Check the limit and fix $sql
		$limit = explode('limit', strtolower($sql));
		if (sizeof($limit) != 2 OR !is_numeric(trim($limit[1])))
		{
			// Append limit
			$sql .= ' LIMIT 1';
		}
		
		$result = $this->query($sql, $bind, 'query_first');
		return $result;
	}
	
	/**
	 * Fetches the first column of all SQL result rows as an array.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * @param mixed  $column OPTIONAL - Key to use for the column index
	 * @return array
	 */
	public function fetchCol($sql, $bind = array(), $column = '')
	{
		$data = array();
		$this->query($sql, $bind, 'query_read');
		while ($row = $this->db->fetch_array($this->result))
		{
			// Validate the key
			$key = ((isset($row[$column]) AND $column) ? $column : key($row));
			$data[] = $row[$key];
		}
		return $data;
	}
	
	/**
	 * Fetches the first column of the first row of the SQL result.
	 *
	 * @param string $sql An SQL SELECT statement.
	 * @param mixed  $bind Data to bind into SELECT placeholders.
	 * @param mixed  $column OPTIONAL - Key to use for the column index
	 * @return string
	 */
	public function fetchOne($sql, $bind = array(), $column = '')
	{
		$result = $this->fetchRow($sql, $bind);
		return ($column ? $result[$column] : (is_array($result) ? reset($result) : ''));
	}
	
	/**
	 * Prepares and executes an SQL statement with bound data.
	 *
	 * @param  mixed  $sql  The SQL statement with placeholders.
	 * @param  mixed  $bind An array of data to bind to the placeholders.
	 * @param  string Which query method to use
	 * 
	 * @return mixed  Result
	 */
	public function query($sql, $bind = array(), $which = 'query_read')
	{
		// make sure $bind is an array
		if (!is_array($bind))
		{
			$bind = (array)$bind;
		}
		
		if (!in_array($which, array('query_read', 'query_write', 'query_first')))
		{
			// Default to query read
			$which = 'query_read';
		}
		
		foreach ($bind as $key => $val)
		{
			if (is_numeric($key))
			{
				// Sort string mapping
				$val = (is_numeric($val) ? "'$val'" : "'" . $this->db->escape_string($val) . "'");
				
				// Replace first instance of ?
				$sql = implode($val, explode('?', $sql, 2));
			}
		}
		
		foreach ($bind as $key => $val)
		{
			if (!is_numeric($key))
			{
				// Array of token replacements
				$sql = str_replace($key, $val, $sql);
			}
		}
		
		// Set the table prefix
		$sql = preg_replace('/\s+`?\$/U', ' ' . TABLE_PREFIX, $sql);
		
		if ($this->debug)
		{
			echo "<pre>";
			echo $sql;
			echo "</pre>";
			die();
		}		
		
		// Execute the query
		$this->result = $this->db->$which($sql);
		return $this->result;
	}
	
	/**
	 * Helper function for IN statements for SQL queries.
	 * For example, with an array $userids = array(1, 2, 3, 4, 5);
	 * the query would be WHERE userid IN' . $this->queryList($userids) . '
	 *
	 * @param  array The array to work with
	 * 
	 * @return mixed  Properly escaped and parenthesised IN() list
	 */
	public function queryList($arr)
	{
		$values = array();
		foreach ($arr as $val)
		{
			// Ensure the value is escaped properly
			$values[] = (is_numeric($val) ? $val : $this->db->sql_prepare($val));
		}
		
		if (!count($values))
		{
			// Ensure there's no SQL errors
			$values[] = 0;
		}
		
		return 'IN(' . implode(', ', $values) . ')';
	}
}

// #############################################################################
// filter functionality class

/**
* Class that handles filtering arrays
*/
class THANKS_FILTER
{
	/**
	* Id Field we are using
	*
	* @private	string
	*/	
	private static $idfield 	= NULL;
	
	/**
	* Id value we are looking for
	*
	* @private	mixed
	*/	
	private static $idval 		= NULL;
	
	
	
	/**
	* Sets up and begins the filtering process 
	*
	* @param	array	Array to filter
	* @param	string	What the ID Field is
	* @param	mixed	What we are looking for
	*
	* @return	array	Filtered array
	*/
	public static function filter($array, $idfield, $idval)
	{
		// Set the two things we can't pass on to the callback
		self::$idfield 	= $idfield;
		self::$idval	= $idval;
		
		// Filter this shiet
		return array_filter($array, array(__CLASS__, 'do_filter'));
	}
	
	/**
	* Checks if this element should be included
	*
	* @param	array	Array to filter
	*
	* @return	boolean	Whether we should include this or not
	*/	
	protected static function do_filter($array)
	{
		$idfield 	= self::$idfield;
		$idval		= self::$idval;
		return ($array[$idfield] == $idval);
	}
}