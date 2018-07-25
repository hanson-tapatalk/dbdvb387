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
* Class to do data save/delete operations for Buttons
*
* @package	Forumon
*/
class Thanks_DataManager_Button extends vB_DataManager
{
	/**
	* Array of recognised and required fields for buttons, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'buttonid' 				=> array(TYPE_UINT, 	REQ_INCR, 	VF_METHOD, 	'verify_nonzero'),
		'varname' 				=> array(TYPE_STR, 		REQ_YES, 	VF_METHOD),
		'title' 				=> array(TYPE_STR, 		REQ_YES),
		'description' 			=> array(TYPE_STR, 		REQ_NO),
		'active' 				=> array(TYPE_STR, 		REQ_NO, 	VF_METHOD),
		'actiontext' 			=> array(TYPE_STR, 		REQ_NO),
		'listtext' 				=> array(TYPE_STR, 		REQ_NO),
		'undotext' 				=> array(TYPE_STR, 		REQ_NO),
		'image' 				=> array(TYPE_STR, 		REQ_NO),
		'image_unclick' 		=> array(TYPE_STR, 		REQ_NO),
		'reputation' 			=> array(TYPE_INT, 		REQ_NO),
		'permissions' 			=> array(TYPE_NOCLEAN, 	REQ_YES, 	VF_METHOD, 	'verify_serialized'),
		'exclusivity' 			=> array(TYPE_NOCLEAN, 	REQ_NO),
		'bitfield' 				=> array(TYPE_UINT, 	REQ_AUTO),
		'displayorder' 			=> array(TYPE_UINT, 	REQ_NO),
		'minposts' 				=> array(TYPE_UINT, 	REQ_NO),
		'clicksperday' 			=> array(TYPE_UINT, 	REQ_NO),
		'postfont' 				=> array(TYPE_NOCLEAN, 	REQ_YES, 	VF_METHOD, 	'verify_serialized'),
		'defaultbutton_attach' 	=> array(TYPE_UINT, 	REQ_NO),
		'defaultbutton_content' => array(TYPE_UINT, 	REQ_NO),
		'disablenotifs' 		=> array(TYPE_UINT, 	REQ_NO),
		'disableemails' 		=> array(TYPE_UINT, 	REQ_NO),
		'disablestats_given' 	=> array(TYPE_UINT, 	REQ_NO),
		'disablestats_received' => array(TYPE_UINT, 	REQ_NO),
		'disableintegration' 	=> array(TYPE_UINT, 	REQ_NO),
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
	var $table = 'dbtech_thanks_button';

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('buttonid = %1$d', 'buttonid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function Thanks_DataManager_Button(&$registry, $errtype = ERRTYPE_STANDARD)
	{
		parent::vB_DataManager($registry, $errtype);

		($hook = vBulletinHook::fetch_hook('dbtech_thanks_buttondata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the varname is valid
	*
	* @param	string	varname of the button
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
		
		if (!preg_match('#^[a-z0-9_]+$#i', $varname)) // match a-z, A-Z, 0-9
		{
			// Invalid
			return false;
		}
			
		// Check for existing button of this name
		if ($existing = $this->registry->db->query_first("
			SELECT `varname`
			FROM `" . TABLE_PREFIX . "dbtech_thanks_button`
			WHERE `varname` = " . $this->registry->db->sql_prepare($varname) . "
				" . ($this->existing['buttonid'] ? "AND `buttonid` != " . $this->registry->db->sql_prepare($this->existing['buttonid']) : '') . "			
			LIMIT 1
		"))
		{
			// Whoopsie, exists
			$this->error('dbtech_thanks_x_already_exists_y', $vbphrase['dbtech_thanks_button'], $varname);
			return false;
		}

		return true;
	}

	/**
	* Verifies that the active flag is valid
	*
	* @param	string	Active flag
	*
	* @return	boolean
	*/
	function verify_active(&$active)
	{
		// Validate active
		$active = (!in_array($active, array('0', '1')) ? '1' : $active);
		
		return true;
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
		
		if (!$this->condition)
		{
			// Check for existing button of this name
			if ($existing = $this->registry->db->query_first("
				SELECT `bitfield`
				FROM `" . TABLE_PREFIX . "dbtech_thanks_button`
				ORDER BY `bitfield` DESC
				LIMIT 1
			"))
			{
				// Use existing bitfield
				$bitfield = ($existing['bitfield'] * 2);
			}
			else
			{
				// This is the first one
				$bitfield = 1;
			}
			
			// Set the bitfield
			$this->do_set('bitfield', $bitfield);
		}
		
		if ($this->setfields['exclusivity'])
		{		
			$bit = 0;
			foreach ((array)$this->fetch_field('exclusivity') as $val)
			{
				$bit += $val;
			}		
			$this->do_set('exclusivity', $bit);
		}		
		
		$return_value = true;
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_buttondata_presave')) ? eval($hook) : false;

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
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_buttondata_postsave')) ? eval($hook) : false;
		
		// Rebuild the cache
		THANKS_CACHE::build('button');
		
		// Ensure we can do language verifications
		require_once(DIR . '/includes/adminfunctions_language.php');

		/*
		if (!preg_match('#^[a-z0-9_\[\]]+$#i', $typename)) // match a-z, A-Z, 0-9, ',', _ only .. allow [] for help items
		{
			print_stop_message('invalid_phrase_varname');
		}
		*/

		/*insert query*/
		$this->registry->db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, varname, text, fieldname, product, username, dateline, version)
			VALUES
				(-1,
				'dbtech_thanks_button_" . $this->fetch_field('varname') . "_title',
				'" . $this->registry->db->escape_string($this->fetch_field('title')) . "',
				'global',
				'dbtech_thanks',
				'Admin',
				" . TIMENOW . ",
				'1.0.0')
		");

		/*insert query*/
		$this->registry->db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, varname, text, fieldname, product, username, dateline, version)
			VALUES
				(-1,
				'dbtech_thanks_button_" . $this->fetch_field('varname') . "_actiontext',
				'" . $this->registry->db->escape_string($this->fetch_field('actiontext')) . "',
				'global',
				'dbtech_thanks',
				'Admin',
				" . TIMENOW . ",
				'1.0.0')
		");

		/*insert query*/
		$this->registry->db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, varname, text, fieldname, product, username, dateline, version)
			VALUES
				(-1,
				'dbtech_thanks_button_" . $this->fetch_field('varname') . "_listtext',
				'" . $this->registry->db->escape_string($this->fetch_field('listtext')) . "',
				'global',
				'dbtech_thanks',
				'Admin',
				" . TIMENOW . ",
				'1.0.0')
		");

		/*insert query*/
		$this->registry->db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "phrase
				(languageid, varname, text, fieldname, product, username, dateline, version)
			VALUES
				(-1,
				'dbtech_thanks_button_" . $this->fetch_field('varname') . "_undotext',
				'" . $this->registry->db->escape_string($this->fetch_field('undotext')) . "',
				'global',
				'dbtech_thanks',
				'Admin',
				" . TIMENOW . ",
				'1.0.0')
		");
		
		// Rebuild the language
		build_language(-1);

		if (!$this->condition)
		{
			if (!class_exists('vB_Database_Alter_MySQL'))
			{
				// Grab the dbalter class
				require(DIR . '/includes/class_dbalter.php');
			}
			
			// Init db alter
			$db_alter = new vB_Database_Alter_MySQL($this->registry->db);
			
			if ($db_alter->fetch_table_info('dbtech_thanks_statistics'))
			{
				// Add the fields we need
				$db_alter->add_field(array(
					'name'       => $this->fetch_field('varname') . '_given',
					'type'       => 'int',
					'length'     => '10',
					'attributes' => 'unsigned',
					'null'       => false,	// True = NULL, false = NOT NULL
					'default'    => '0'
				));
				//$db_alter->add_index($this->fetch_field('varname') . '_given', $this->fetch_field('varname') . '_given');
				$db_alter->add_field(array(
					'name'       => $this->fetch_field('varname') . '_received',
					'type'       => 'int',
					'length'     => '10',
					'attributes' => 'unsigned',
					'null'       => false,	// True = NULL, false = NOT NULL
					'default'    => '0'
				));
				//$db_alter->add_index($this->fetch_field('varname') . '_received', $this->fetch_field('varname') . '_received');
			}
		}
		
		if (class_exists('VBACTIVITY'))
		{
			// Ensure the button adding works
			$doaddgiven 	= VBACTIVITY::add_type($this->fetch_field('varname') . 'given', 	$this->fetch_field('title') . ' Given', 	'dbtech_thanks', '/dbtech/thanks/vbactivity_type/' . $this->fetch_field('varname') . 'given.php', 		true);
			$doaddreceived 	= VBACTIVITY::add_type($this->fetch_field('varname') . 'received', 	$this->fetch_field('title') . ' Received', 	'dbtech_thanks', '/dbtech/thanks/vbactivity_type/' . $this->fetch_field('varname') . 'received.php', 	true);
			
			if ($doaddgiven)
			{
				$given = file_get_contents(DIR . '/dbtech/thanks/vbactivity_type/!given.txt');
				$given = str_replace('<varname>', $this->fetch_field('varname'), $given);
				file_put_contents(DIR . '/dbtech/thanks/vbactivity_type/' . $this->fetch_field('varname') . 'given.php', $given);
			}
			
			if ($doaddreceived)
			{
				$received = file_get_contents(DIR . '/dbtech/thanks/vbactivity_type/!received.txt');
				$received = str_replace('<varname>', $this->fetch_field('varname'), $received);
				file_put_contents(DIR . '/dbtech/thanks/vbactivity_type/' . $this->fetch_field('varname') . 'received.php', $received);
			}
		}	

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_thanks_buttondata_delete')) ? eval($hook) : false;
		
		if (!class_exists('vB_Database_Alter_MySQL'))
		{
			// Grab the dbalter class
			require(DIR . '/includes/class_dbalter.php');
		}
		
		// Init db alter
		$db_alter = new vB_Database_Alter_MySQL($this->registry->db);
		
		if ($db_alter->fetch_table_info('dbtech_thanks_statistics'))
		{		
			// Add the fields we need
			$db_alter->drop_field($this->fetch_field('varname') . '_given');
			$db_alter->drop_field($this->fetch_field('varname') . '_received');
		}
		
		// Rebuild the cache
		THANKS_CACHE::build('button');
		
		if (class_exists('VBACTIVITY'))
		{
			if ($existing = VBACTIVITY::$cache['type'][VBACTIVITY::fetch_type($this->fetch_field('varname') . 'given')])
			{
				// Remove given
				@unlink(DIR . '/dbtech/thanks/vbactivity_type/' . $this->fetch_field('varname') . 'given.php');		
				$dm =& VBACTIVITY::initDataManager('Type', $this->registry, ERRTYPE_CP);
					$dm->set_existing($existing);
				$dm->delete();
				unset($dm);
			}
			
			if ($existing = VBACTIVITY::$cache['type'][VBACTIVITY::fetch_type($this->fetch_field('varname') . 'received')])
			{
				// Remove received
				@unlink(DIR . '/dbtech/thanks/vbactivity_type/' . $this->fetch_field('varname') . 'received.php');					
				$dm =& VBACTIVITY::initDataManager('Type', $this->registry, ERRTYPE_CP);
					$dm->set_existing($existing);
				$dm->delete();
				unset($dm);
			}
		}
		
		return true;
	}
}