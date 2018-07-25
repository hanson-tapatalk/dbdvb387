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
if ($_REQUEST['action'] == 'search' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_thanks_log_title']);
	
	// ###################### Start modify #######################
	$users = $db->query_read("
		SELECT DISTINCT entry.userid, user.username
		FROM " . TABLE_PREFIX . "dbtech_thanks_entry AS entry
		LEFT JOIN " . TABLE_PREFIX . "user AS user USING(userid)
		ORDER BY username
	");
	$userlist = array('no_value' => $vbphrase['all_log_entries']);
	while ($user = $db->fetch_array($users))
	{
		if (!$user['username'])
		{
			// No username found
			continue;
		}
		$userlist[$user['userid']] = $user['username'];
	}
	
	$entrylist = array('no_value' => $vbphrase['dbtech_thanks_all_entry_types']);
	foreach (THANKS::$cache['button'] as $button)
	{
		$entrylist[$button['varname']] = $button['title'];
	}
	
	print_form_header('thanks', 'search');
	construct_hidden_code('action', 'searchresults');
	print_table_header($vbphrase['dbtech_thanks_log_title']);
	print_input_row($vbphrase['log_entries_to_show_per_page'], 'perpage', 15);
	print_select_row($vbphrase['show_only_entries_generated_by'], 'userid', $userlist);
	print_select_row($vbphrase['dbtech_thanks_entrytype'], 'varname', $entrylist);
	print_time_row($vbphrase['start_date'], 'startdate', 0, 0);
	print_time_row($vbphrase['end_date'], 'enddate', 0, 0);
	print_select_row($vbphrase['order_by'], 'orderby', array('date' => $vbphrase['date'], 'user' => $vbphrase['username'], 'varname' => $vbphrase['varname']), 'date');
	print_submit_row($vbphrase['view'], 0);
	
	
	
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'searchresults')
{
	print_cp_header($vbphrase['dbtech_thanks_log_title']);
	
	// ###################### Start view #######################
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage'    => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'userid'     => TYPE_UINT,
		'varname'    => TYPE_STR,
		'orderby'    => TYPE_NOHTML,
		'startdate'  => TYPE_UNIXTIME,
		'enddate'    => TYPE_UNIXTIME,
	));
	
	$sqlconds = array();
	$hook_query_fields = $hook_query_joins = '';
	
	if ($vbulletin->GPC['perpage'] < 1)
	{
		$vbulletin->GPC['perpage'] = 15;
	}
	
	if ($vbulletin->GPC['userid'])
	{
		$sqlconds[] = "entry.userid = " . $vbulletin->GPC['userid'];
	}
	
	if ($vbulletin->GPC['varname'])
	{
		$sqlconds[] = "entry.varname LIKE '" . $db->escape_string_like($vbulletin->GPC['varname']) . "%'";
	}
	
	if ($vbulletin->GPC['startdate'])
	{
		$sqlconds[] = "entry.dateline >= " . $vbulletin->GPC['startdate'];
	}
	
	if ($vbulletin->GPC['enddate'])
	{
		$sqlconds[] = "entry.dateline <= " . $vbulletin->GPC['enddate'];
	}
	
	//($hook = vBulletinHook::fetch_hook('admin_modlogviewer_query')) ? eval($hook) : false;
	
	$counter = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "dbtech_thanks_entry AS entry
		WHERE entry.contenttype = 'post'
		" . (!empty($sqlconds) ? " AND " . implode("\r\n\tAND ", $sqlconds) : "") . "
	");
	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);
	
	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];
	
	switch($vbulletin->GPC['orderby'])
	{
		case 'user':
			$order = 'username ASC, dateline DESC';
			break;
		case 'varname':
			$order = 'varname ASC, dateline DESC';
			break;
		case 'date':
		default:
			$order = 'dateline DESC';
	}
	
	$logs = $db->query_read("
		SELECT entry.*, user.username, post.title AS posttitle, thread.title AS threadtitle
			$hook_query_fields
		FROM " . TABLE_PREFIX . "dbtech_thanks_entry AS entry
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = entry.userid)
		LEFT JOIN " . TABLE_PREFIX . "post AS post ON (post.postid = entry.contentid)
		LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (thread.threadid = post.threadid)
		$hook_join_fields
		WHERE entry.contenttype = 'post'
		" . (!empty($sqlconds) ? " AND " . implode("\r\n\tAND ", $sqlconds) : "") . "
		ORDER BY $order
		LIMIT $startat, " . $vbulletin->GPC['perpage'] . "
	");
	
	if ($db->num_rows($logs))
	{
		$vbulletin->GPC['varname'] = htmlspecialchars_uni($vbulletin->GPC['varname']);
	
		if ($vbulletin->GPC['pagenumber'] != 1)
		{
			$prv = $vbulletin->GPC['pagenumber'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" value=\"&laquo; " . $vbphrase['first_page'] . "\" tabindex=\"1\" onclick=\"window.location='thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" value=\"&lt; " . $vbphrase['prev_page'] . "\" tabindex=\"1\" onclick=\"window.location='thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$prv'\">";
		}
	
		if ($vbulletin->GPC['pagenumber'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['pagenumber'] + 1;
			$nextpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['next_page'] . " &gt;\" tabindex=\"1\" onclick=\"window.location='thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" value=\"" . $vbphrase['last_page'] . " &raquo;\" tabindex=\"1\" onclick=\"window.location='thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . $vbulletin->GPC['orderby'] . "&page=$totalpages'\">";
		}
	
		$headings = array();
		//$headings[] = $vbphrase['id'];
		$headings[] = "<a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=user&page=" . $vbulletin->GPC['pagenumber'] . "\">" . str_replace(' ', '&nbsp;', $vbphrase['username']) . "</a>";
		$headings[] = "<a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=date&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['date'] . "</a>";
		$headings[] = "<a href=\"thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search&action=searchresults&varname=" . $vbulletin->GPC['varname'] . "&u=" . $vbulletin->GPC['userid'] . "&pp=" . $vbulletin->GPC['perpage'] . "&orderby=varname&page=" . $vbulletin->GPC['pagenumber'] . "\">" . $vbphrase['dbtech_thanks_content_type'] . "</a>";
		$headings[] = $vbphrase['dbtech_thanks_entrytype'];
		$headings[] = $vbphrase['title'];
		if (THANKS::$isPro)
		{
			$headings[] = $vbphrase['delete'];
		}
	
		print_form_header('', '');
		print_description_row(construct_link_code($vbphrase['restart'], "thanks.php?" . $vbulletin->session->vars['sessionurl'] . "do=search"), 0, count($headings), 'thead', 'right');
		print_table_header(construct_phrase($vbphrase['dbtech_thanks_entry_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['pagenumber']), vb_number_format($totalpages), vb_number_format($counter['total'])), count($headings));
		print_cells_row($headings, 1);
	
		while ($log = $db->fetch_array($logs))
		{
			$cell = array();
			//$cell[] = $log['entryid'];
			$cell[] = ($log['username'] ? "<a href=\"user.php?" . $vbulletin->session->vars['sessionurl'] . "do=edit&u=$log[userid]\"><b>$log[username]</b></a>" : 'N/A');
			$cell[] = '<span class="smallfont">' . vbdate($vbulletin->options['logdateformat'], $log['dateline']) . '</span>';
			$cell[] = '<span class="smallfont">' . $log['contenttype'] . '</span>';
			$cell[] = '<span class="smallfont"><a href="thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=search&action=searchresults&varname=' . $log['varname'] . '&u=' . $vbulletin->GPC['userid'] . '&pp=' . $vbulletin->GPC['perpage'] . '&orderby=' . $vbulletin->GPC['orderby'] . '&page=' . $vbulletin->GPC['pagenumber'] . '">' . $vbphrase["dbtech_thanks_{$log[varname]}"] . '</a></span>';
			$cell[] = ($log['posttitle'] ? $log['posttitle'] : $log['threadtitle']);
			
			if (THANKS::$isPro)
			{
				$cell[] = construct_link_code($vbphrase['delete'], 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=deleteentry&amp;entryid=' . $log['entryid']);
			}
			//($hook = vBulletinHook::fetch_hook('admin_modlogviewer_query_loop')) ? eval($hook) : false;
	
			print_cells_row($cell, 0, 0, -4);
		}
	
		print_table_footer(count($headings), "$firstpage $prevpage &nbsp; $nextpage $lastpage");
	}
	else
	{
		print_stop_message('no_results_matched_your_query');
	}
	
	
	print_cp_footer();
}

