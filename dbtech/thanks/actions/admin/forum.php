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
if ($_REQUEST['action'] == 'forum' OR empty($_REQUEST['action']))
{
	$forumids = array();
	foreach ((array)$vbulletin->forumcache as $forumid => $forum)
	{
		if (!THANKS::$isPro AND $forum['parentid'] != -1)
		{
			// This forum isn't a parent forum
			continue;
		}
		
		$forumids[] = $forumid;
	}
	
	
	$headings = array();
	$headings[] = '<label><input type="checkbox" rel="^-forum" />' . $vbphrase['forum'] . '</label>';
	
	foreach (THANKS::$cache['button'] as $buttonid => $button)
	{
		// Show button headers
		$headings[] = '<label><input type="checkbox" rel="*-[' . $buttonid . '][dbtech" />' . $button['title'] . '</label>';
	}
	
	print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_thanks_editing_x_y'], $vbphrase['dbtech_thanks_button'], $vbphrase['forum'])));
	print_form_header('thanks', 'forum');
	construct_hidden_code('action', 'updateforum');	
	print_table_header(construct_phrase($vbphrase['dbtech_thanks_editing_x_y'], $vbphrase['dbtech_thanks_button'], $vbphrase['forum']), count($headings));
	print_cells_row($headings, 0, 'thead');
	
	foreach ((array)$forumids as $forumid)
	{
		// Shorthand
		$forum = $vbulletin->forumcache[$forumid];
		$cell = array();
		$cell[] = '<label><input type="checkbox" rel="^-forum[' . $forumid . ']" />' . construct_depth_mark($forum['depth'],'- - ') . $forum['title'] . '</label>';
		
		foreach (THANKS::$cache['button'] as $buttonid => $button)
		{
			$celldata = '<table align="center"><tr><td class="smallfont">
					<input type="hidden" name="forum[' . $forumid . '][' . $buttonid . '][dbtech_thanks_disabledbuttons]" value="0" />
					<label for="cb_forum_' . $forumid . '_' . $button['varname'] . '_dbtech_thanks_disabledbuttons">
						<input type="checkbox" name="forum[' . $forumid . '][' . $buttonid . '][dbtech_thanks_disabledbuttons]" id="cb_forum_' . $forumid . '_' . $button['varname'] . '_dbtech_thanks_disabledbuttons" value="1"' . (((int)$forum['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield']) ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;forum[' . $forumid . '][' . $button['varname'] . '][dbtech_thanks_disabledbuttons]&quot;"' : '') . '/>
						' . construct_phrase($vbphrase['dbtech_thanks_disable_x'], $button['title']) . '
					</label>
				</td></tr>
			';
			
			($hook = vBulletinHook::fetch_hook('dbtech_thanks_admin_forum_eachbutton')) ? eval($hook) : false;
			
			$cell[] = $celldata . '</table>';
		}
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);	
	}
	print_submit_row($vbphrase['save'], false, count($headings));
	echo '<script type="text/javascript" src="' . THANKS::jQueryPath() . '"></script>';
	THANKS::js('_admin');	
}

// #############################################################################
if ($_POST['action'] == 'updateforum')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'forum' 	=> TYPE_ARRAY,
	));
	
	foreach ($vbulletin->GPC['forum'] as $forumid => $buttons)
	{
		$bind = array();
		foreach ($buttons as $buttonid => $columns)
		{
			if ($buttonid == -1)
			{
				foreach ($columns as $column => $value)
				{
					// Add this to the enabled bits
					$bind[$column] = $value;
				}
			}
			else
			{
				foreach ($columns as $column => $yesno)
				{
					// Ensure this is set
					$bind[$column] = (!isset($bind[$column]) ? 0 : $bind[$column]);
					
					if (!$yesno)
					{
						// Enabled
						continue;
					}
					
					// Add this to the enabled bits
					$bind[$column] += THANKS::$cache['button'][$buttonid]['bitfield'];
				}
			}
		}
		
		// Update the db
		THANKS::$db->update('forum', $bind, 'WHERE forumid = ' . $db->sql_prepare($forumid));
	}
	
	require_once(DIR . '/includes/adminfunctions_forums.php');
	build_forum_permissions();
	
	define('CP_REDIRECT', 'thanks.php?do=forum');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['forums'], $vbphrase['dbtech_thanks_edited']);	
}
print_cp_footer();