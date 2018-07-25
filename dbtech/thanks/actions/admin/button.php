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
if ($_REQUEST['action'] == 'button' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_thanks_button_management']);
	
	// Table header
	$headings = array();
	$headings[] = $vbphrase['title'];
	$headings[] = $vbphrase['varname'];
	$headings[] = $vbphrase['description'];
	$headings[] = $vbphrase['active'];
	$headings[] = $vbphrase['display_order'];
	$headings[] = preg_replace('/<dfn>.*<\/dfn>/isU', '', $vbphrase['dbtech_thanks_action_text']);
	if (THANKS::$isPro) $headings[] = preg_replace('/<dfn>.*<\/dfn>/isU', '', $vbphrase['dbtech_thanks_undo_text']);
	$headings[] = $vbphrase['reputation'];
	$headings[] = $vbphrase['edit'];
	if (THANKS::$isPro) $headings[] = $vbphrase['delete'];
	
	
	if (count(THANKS::$cache['button']))
	{
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'displayorder');
		print_table_header($vbphrase['dbtech_thanks_button_management'], count($headings));
		print_cells_row($headings, 0, 'thead');
		
		foreach (THANKS::$cache['button'] as $buttonid => $button)
		{
			// Table data
			$cell = array();
			$cell[] = $button['title'];
			$cell[] = $button['varname'];
			$cell[] = $button['description'];
			$cell[] = ($button['active'] ? $vbphrase['yes'] : '<span class="col-i"><strong>' . $vbphrase['no'] . '</strong></span>');
			$cell[] = "<input type=\"text\" class=\"bginput\" name=\"order[$buttonid]\" value=\"$button[displayorder]\" tabindex=\"1\" size=\"3\" title=\"" . $vbphrase['edit_display_order'] . "\" />";
			$cell[] = $button['actiontext'];
			if (THANKS::$isPro) $cell[] = $button['undotext'];
			$cell[] = $button['reputation'];
			$cell[] = construct_link_code($vbphrase['edit'], 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=button&amp;action=modify&amp;buttonid=' . $buttonid);
			
			
			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}
		
		if (THANKS::$isPro) print_submit_row($vbphrase['save_display_order'], false, count($headings), false, "<input type=\"button\" id=\"addnew\" class=\"button\" value=\"" . str_pad($vbphrase['dbtech_thanks_add_new_button'], 8, ' ', STR_PAD_BOTH) . "\" tabindex=\"1\" onclick=\"window.location = 'thanks.php?do=button&amp;action=modify'\" />");
		else print_table_footer();	
	}
	else
	{
		if (!THANKS::$isPro) print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], 0);
		
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'modify');
		print_table_header($vbphrase['dbtech_thanks_button_management'], count($headings));
		print_description_row($vbphrase['dbtech_thanks_no_buttons'], false, count($headings));
		print_submit_row($vbphrase['dbtech_thanks_add_new_button'], false, count($headings));	
	}
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$buttonid = $vbulletin->input->clean_gpc('r', 'buttonid', TYPE_UINT);
	$button = ($buttonid ? THANKS::$cache['button']["$buttonid"] : false);
	
	if (!is_array($button))
	{
		// Non-existing button
		$buttonid = 0;
	}
	
	$defaults = array(
		'varname'		=> 'recommends',
		'title' 		=> 'Recommend',
		'description' 	=> '"Recommend" this post.',
		'displayorder' 	=> 10,
		'active' 		=> 1,
		'actiontext' 	=> 'Recommend this post',
		'listtext' 		=> 'recommended this post',
		'undotext' 		=> 'Unrecommend',
		'minposts' 		=> 0,
		'clicksperday' 	=> 0,
		'reputation'	=> 1,
		'postfont' 		=> array(
			1 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			2 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			3 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			4 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			5 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
		),
	);
	
	$colours = array(
		'' 			=> '',
		
		// sRGB colours
		'White' 	=> 'White',
		'Silver' 	=> 'Silver',
		'Gray' 		=> 'Gray',
		'Black' 	=> 'Black',
		'Red' 		=> 'Red',
		'Maroon' 	=> 'Maroon',
		'Yellow' 	=> 'Yellow',
		'Olive' 	=> 'Olive',
		'Lime' 		=> 'Lime',
		'Green' 	=> 'Green',
		'Aqua' 		=> 'Aqua',
		'Teal' 		=> 'Teal',
		'Blue' 		=> 'Blue',
		'Navy' 		=> 'Navy',
		'Fuchsia' 	=> 'Fuchsia',
		'Purple' 	=> 'Purple',
	);
	
	if ($buttonid)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_thanks_editing_x_y'], $vbphrase['dbtech_thanks_button'], $button['title'])));
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'update');
		construct_hidden_code('buttonid', $buttonid);
		print_table_header(construct_phrase($vbphrase['dbtech_thanks_editing_x_y'], $vbphrase['dbtech_thanks_button'], $button['title']));

		$vbphrase['dbtech_thanks_title']  		= $vbphrase['title'] . construct_phrase($vbphrase['dbtech_thanks_title_translation'], $button['varname']);
		$vbphrase['dbtech_thanks_action_text']  = $vbphrase['dbtech_thanks_action_text'] . construct_phrase($vbphrase['dbtech_thanks_action_text_translation'], $button['varname']);
		$vbphrase['dbtech_thanks_list_text']  	= $vbphrase['dbtech_thanks_list_text'] . construct_phrase($vbphrase['dbtech_thanks_list_text_translation'], $button['varname']);
		$vbphrase['dbtech_thanks_undo_text']  	= $vbphrase['dbtech_thanks_undo_text'] . construct_phrase($vbphrase['dbtech_thanks_undo_text_translation'], $button['varname']);
	}
	else
	{
		if (!THANKS::$isPro) print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], 0);
		
		// Add
		print_cp_header($vbphrase['dbtech_thanks_add_new_button']);
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'update');
		print_table_header($vbphrase['dbtech_thanks_add_new_button']);
		
		$button = $defaults;

		$vbphrase['dbtech_thanks_title']  		= $vbphrase['title'];
	}
	
	print_description_row($vbphrase['dbtech_thanks_main_settings'], false, 2, 'optiontitle');	
	if ($buttonid)
	{
		construct_hidden_code('button[varname]', 																											$button['varname']);		
		print_label_row($vbphrase['varname'], 																												$button['varname']);
	}
	else
	{
		print_input_row($vbphrase['varname'], 									'button[varname]', 															$button['varname']);
	}
	print_input_row($vbphrase['dbtech_thanks_title'], 							'button[title]', 															$button['title']);	
	print_textarea_row($vbphrase['description'],								'button[description]',														$button['description']);
	print_input_row($vbphrase['display_order'], 								'button[displayorder]', 													$button['displayorder']);
	print_yes_no_row($vbphrase['active'],										'button[active]',															$button['active']);
	print_description_row($vbphrase['dbtech_thanks_button_settings'], false, 2, 'optiontitle');
	print_input_row($vbphrase['dbtech_thanks_image'], 							'button[image]', 															$button['image']);
	 
	print_textarea_row($vbphrase['dbtech_thanks_action_text'], 					'button[actiontext]', 														$button['actiontext']);
	print_textarea_row($vbphrase['dbtech_thanks_list_text'], 					'button[listtext]', 														$button['listtext']);
	 
	print_input_row($vbphrase['reputation'], 									'button[reputation]', 														$button['reputation']);
	print_yes_no_row($vbphrase['dbtech_thanks_default_button_attach'],			'button[defaultbutton_attach]',												$button['defaultbutton_attach']);
	print_yes_no_row($vbphrase['dbtech_thanks_default_button_content'],			'button[defaultbutton_content]',											$button['defaultbutton_content']);	
	print_yes_no_row($vbphrase['dbtech_thanks_disable_stats_given'],			'button[disablestats_given]',												$button['disablestats_given']);
	print_yes_no_row($vbphrase['dbtech_thanks_disable_stats_received'],			'button[disablestats_received]',											$button['disablestats_received']);
	THANKS::bitfieldRow($vbphrase['dbtech_thanks_post_disabled_integration'], 	'button[disableintegration]', 'nocache|dbtech_thanks_disable_integration', 	$button['disableintegration']);
	 	
	
	print_table_break();
	
	// Table header
	$headings = array();
	$headings[] = '<label><input type="checkbox" rel="^-button[permissions]" />' . $vbphrase['usergroup'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[canclick]" />' . $vbphrase['dbtech_thanks_can_click'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[canreqclick]" />' . $vbphrase['dbtech_thanks_can_require_click'] . '</label>';
	
	
	$cells = array();
	$cells[] = 'canclick';
	$cells[] = 'canreqclick';
	
	
	print_table_header($vbphrase['dbtech_thanks_permissions'], count($headings));
	print_cells_row($headings, 0, 'thead');
	foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
	{
		// Table data
		$cell = array();
		$cell[] = '<label><input type="checkbox" rel="^-button[permissions][' . $usergroupid . ']" />' . $usergroup['title'] . '</label>';
		foreach ($cells as $permtitle)
		{
			$cell[] = '<center>
				<input type="hidden" name="button[permissions][' . $usergroupid . '][' . $permtitle . ']" value="0" />
				<input type="checkbox" name="button[permissions][' . $usergroupid . '][' . $permtitle . ']" value="1"' . ($button['permissions'][$usergroupid][$permtitle] ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;button[permissions][' . $usergroupid . '][' . $permtitle . ']&quot;"' : '') . '/>
			</center>';
		}
		
		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_table_break();
	
	// Table header
	$headings = array();
	$headings[] = $vbphrase['dbtech_thanks_button'];
	$headings[] = $vbphrase['dbtech_thanks_is_exclusive'];
	
	print_table_header($vbphrase['dbtech_thanks_button_exclusivity'], count($headings));
	print_description_row($vbphrase['dbtech_thanks_button_exclusivity_descr'], false, count($headings));
	print_cells_row($headings, 0, 'thead');
	foreach (THANKS::$cache['button'] as $button_id => $button_info)
	{
		if ($button_id == $buttonid)
		{
			// Can't set to own button, lol
			continue;
		}
		
		// Table data
		$cell = array();
		$cell[] = $button_info['title'];
		$cell[] = '<center>
			<input type="hidden" name="button[exclusivity][' . $button_id . ']" value="0" />
			<input type="checkbox" name="button[exclusivity][' . $button_id . ']" value="' . $button_info['bitfield'] . '"' . (((int)$button['exclusivity'] & (int)$button_info['bitfield']) ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;button[exclusivity][' . $button_id . ']&quot;"' : '') . '/>
		</center>';
		
		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_table_break();
	print_submit_row(($buttonid ? $vbphrase['save'] : $vbphrase['dbtech_thanks_add_new_button']), $vbphrase['reset'], count($headings));	
	echo '<script type="text/javascript" src="' . THANKS::jQueryPath() . '"></script>';
	THANKS::js('_admin');	
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'buttonid' 	=> TYPE_UINT,
		'button' 	=> TYPE_ARRAY,
	));
	
	 

	// init data manager
	$dm =& THANKS::initDataManager('Button', $vbulletin, ERRTYPE_CP);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['buttonid'])
	{
		if (!$existing = THANKS::$cache['button']["{$vbulletin->GPC[buttonid]}"])
		{
			// Couldn't find the button
			print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], $vbulletin->GPC['buttonid']);
		}
		
		// Set existing
		$dm->set_existing($existing);
		
		// Added
		$phrase = $vbphrase['dbtech_thanks_edited'];
	}
	else
	{
		if (!THANKS::$isPro) print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], 0);
		
		// Added
		$phrase = $vbphrase['dbtech_thanks_added'];
	}
	
	// button fields
	foreach ($vbulletin->GPC['button'] AS $key => $val)
	{
		if (!$vbulletin->GPC['buttonid'] OR $existing["$key"] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}
	
	// Save! Hopefully.
	$dm->save();
	
	define('CP_REDIRECT', 'thanks.php?do=button');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['dbtech_thanks_button'], $phrase);	
}

// #############################################################################
if ($_POST['action'] == 'displayorder')
{
	$vbulletin->input->clean_array_gpc('p', array('order' => TYPE_ARRAY));
	
	if (is_array($vbulletin->GPC['order']))
	{
		foreach ($vbulletin->GPC['order'] as $buttonid => $displayorder)
		{
			if (!$existing = THANKS::$cache['button'][$buttonid])
			{
				// Couldn't find the button
				continue;
			}
			
			if ($existing['displayorder'] == $displayorder)
			{
				// No change
				continue;
			}
			
			// init data manager
			$dm =& THANKS::initDatamanager('Button', $vbulletin, ERRTYPE_CP);
				$dm->set_existing($existing);
				$dm->set('displayorder', $displayorder);
			$dm->save();
			unset($dm);	
		}
	}
	
	define('CP_REDIRECT', 'thanks.php?do=button');
	print_stop_message('saved_display_order_successfully');	
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'buttonid', TYPE_UINT);
	
	print_cp_header(construct_phrase($vbphrase['dbtech_thanks_delete_x'], $vbphrase['dbtech_thanks_button']));
	print_delete_confirmation('dbtech_thanks_button', $vbulletin->GPC['buttonid'], 'thanks', 'button', 'dbtech_thanks_button', array('action' => 'kill'), '', 'title');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'buttonid' 	=> TYPE_UINT,
		'kill' 		=> TYPE_BOOL
	));
	
	if (!$existing = THANKS::$cache['button'][$vbulletin->GPC['buttonid']])
	{
		// Couldn't find the button
		print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], $vbulletin->GPC['buttonid']);
	}
	
	// init data manager
	$dm =& THANKS::initDataManager('Button', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'thanks.php?do=button');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['dbtech_thanks_button'], $vbphrase['dbtech_thanks_deleted']);	
}


print_cp_footer();