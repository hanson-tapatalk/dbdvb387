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

if (!class_exists('vB_DataManager', false))
{
	exit;
}

/**
* Class to do data save/delete operations for Entries
*
* @package	Forumon
*/
class Thanks_DataManager_Entry extends vB_DataManager
{
	/**
	* Array of recognised and required fields for entries, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'entryid' 			=> array(TYPE_UINT, 	REQ_INCR, 	VF_METHOD, 	'verify_nonzero'),
		'varname' 			=> array(TYPE_STR, 		REQ_YES, 	VF_METHOD),
		'userid' 			=> array(TYPE_STR, 		REQ_YES),
		'receiveduserid' 	=> array(TYPE_STR, 		REQ_YES),		
		'contenttype' 		=> array(TYPE_STR, 		REQ_YES),
		'contentid' 		=> array(TYPE_STR, 		REQ_YES),
		'dateline' 			=> array(TYPE_AUTO, 	REQ_NO),
	);

	/**
	* Array of field names that are bitfields, together with the name of the variable in the registry with the definitions.
	*
	* @var	array
	*/
	//var $bitfields = array('adminpermissions' => 'bf_ugp_adminpermissions');

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_thanks_entry';

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('entryid = %1$d', 'entryid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function Thanks_DataManager_Entry(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::vB_DataManager($registry, $errtype);

		($hook = vBulletinHook::fetch_hook('dbtech_thanks_entrydata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the varname is valid
	*
	* @param	string	varname of the entry
	*
	* @return	boolean
	*/
	function verify_varname(&$varname)
	{
		global $vbphrase;
		
		$varname = strval($varname);
		if ($varname === '')
		{
			// Invalid
			return false;
		}
		
		foreach (THANKS::$cache['button'] as $button)
		{
			if (!$button['active'] AND !$this->info['is_automated'])
			{
				// Inactive button
				continue;
			}
			
			if ($button['varname'] == $varname)
			{
				// Exists
				$this->set_info('button', $button);
				return true;
			}
		}

		return false;
	}

	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		if ($this->presave_called !== null)
		{
			return $this->presave_called;
		}
		
		// Set the stats temporarily
		$timenow = TIMENOW;
		$this->do_set('dateline', $timenow);		
		
		$return_value = true;
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_entrydata_presave')) ? eval($hook) : false;

		$this->presave_called = $return_value;
		return $return_value;
	}

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_entrydata_postsave')) ? eval($hook) : false;
		
		// Flush the cache to ensure we don't have any stale entries
		THANKS_CACHE::flush();

		if (!$this->condition)
		{
			// Add statistics
			$this->registry->db->query_write("
				UPDATE `" . TABLE_PREFIX . "dbtech_thanks_statistics`
				SET `" . $this->fetch_field('varname') . "_given` = `" . $this->fetch_field('varname') . "_given` + 1
				WHERE userid = '" . intval($this->fetch_field('userid')) . "'
			");
			$this->registry->db->query_write("
				UPDATE `" . TABLE_PREFIX . "dbtech_thanks_statistics`
				SET `" . $this->fetch_field('varname') . "_received` = `" . $this->fetch_field('varname') . "_received` + 1
				WHERE userid = '" . intval($this->fetch_field('receiveduserid')) . "'
			");
			if (!$this->info['button']['disablenotifs'])
			{
				$this->registry->db->query_write("
					UPDATE `" . TABLE_PREFIX . "user`
					SET `dbtech_thanks_alertcount` = `dbtech_thanks_alertcount` + 1
					WHERE userid = '" . intval($this->fetch_field('receiveduserid')) . "'
				");
			}
		}

		$entry = array();
		foreach ($this->validfields as $key => $tmp)
		{
			// Shorthand
			$entry[$key] = $this->fetch_field($key);
		}

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

		if ($entry['dateline'] >= (TIMENOW - (86400 * $this->registry->options['dbtech_thanks_recententries'])))
		{
			// Newer than 30 days
			THANKS::$db->replace('dbtech_thanks_recententry', $entry);
		}

		// Prune old entries
		THANKS::$db->delete('dbtech_thanks_recententry', array((TIMENOW - (86400 * $this->registry->options['dbtech_thanks_recententries']))), 'WHERE dateline < ?');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_entrydata_delete')) ? eval($hook) : false;
		
		// Remove statistics
		$this->registry->db->query_write("
			UPDATE `" . TABLE_PREFIX . "dbtech_thanks_statistics`
			SET `" . $this->fetch_field('varname') . "_given` = IF(" . $this->fetch_field('varname') . "_given > 0, " . $this->fetch_field('varname') . "_given - 1, 0)
			WHERE userid = '" . intval($this->existing['userid']) . "'
		");
		$this->registry->db->query_write("
			UPDATE `" . TABLE_PREFIX . "dbtech_thanks_statistics`
			SET `" . $this->fetch_field('varname') . "_received` = IF(" . $this->fetch_field('varname') . "_received > 0, " . $this->fetch_field('varname') . "_received - 1, 0)
			WHERE userid = '" . intval($this->existing['receiveduserid']) . "'
		");

		if (!$entryCache = THANKS::$db->fetchRow('
			SELECT * 
			FROM $dbtech_thanks_entrycache
			WHERE varname = ?
				AND contenttype = ?
				AND contentid = ?
		', array(
			$this->existing['varname'],
			$this->existing['contenttype'],
			$this->existing['contentid']
		)))
		{
			// Defaults
			$entryCache = array(
				'varname' 		=> $this->existing['varname'],
				'contenttype' 	=> $this->existing['contenttype'],
				'contentid' 	=> $this->existing['contentid'],
				'data' 			=> 'a:0:{}'
			);

			// Insert this row
			THANKS::$db->insert('dbtech_thanks_entrycache', $entryCache, array(), false);
		}
		
		// Make sure we have an array
		$entryCache['data'] = unserialize($entryCache['data']);

		// Store this
		unset($entryCache['data'][$this->existing['entryid']]);

		// Update points
		THANKS::$db->update(
			'dbtech_thanks_entrycache', 
			array('data' => trim(serialize($entryCache['data']))), 
			'WHERE varname = \'' . $this->existing['varname'] . '\' AND contenttype = \'' . $this->existing['contenttype'] . '\' AND contentid = \'' . $this->existing['contentid'] . '\''
		);

		// Prune old entries
		THANKS::$db->delete('dbtech_thanks_recententry', array($this->existing['entryid'], (TIMENOW - (86400 * $this->registry->options['dbtech_thanks_recententries']))), 'WHERE entryid = ? OR dateline < ?');

		return true;
	}
}