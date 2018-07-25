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

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'thanks');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('dbtech_thanks', 'cphome', 'logging', 'threadmanage', 'banning', 'cpuser', 'cpoption', 'cppermission');

// get special data templates from the datastore
require_once('../dbtech/thanks/includes/specialtemplates.php');
$specialtemplates = $extracache;

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS ######################
if (!can_administer('canadminthanks'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ##############################
log_admin_action(iif($_REQUEST['action'] != '', 'action = ' . $_REQUEST['action']));

// ############################# VBPHRASE   ##############################
$vbphrase['dbtech_thanks_importer'] = '[DBTech] Advanced Post Thanks / Like Importer';
$vbphrase['dbtech_thanks_abe_importer'] = 'Abe Post Thank You Hack to [DBTech] Advanced Post Thanks / Like';
$vbphrase['dbtech_thanks_vbseo_importer'] = 'vBSEO Likes to [DBTech] Advanced Post Thanks / Like';
$vbphrase['dbtech_thanks_entries_per_page'] = 'Thanks per page';
$vbphrase['dbtech_thanks_entries_per_page2'] = 'Likes per page';
$vbphrase['dbtech_thanks_importing_thanks'] = 'Importing Thanks';
$vbphrase['dbtech_thanks_importing_likes'] = 'Importing Likes';
$vbphrase['dbtech_thanks_thanks_imported'] = 'Thanks imported successfully! Please run the Recalculate function under Post Thanks - Maintenance.';

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

if (!empty($_POST['do']))
{
	// $_POST requests take priority
	$action = $_POST['do'];
}
else if (!empty($_GET['do']))
{
	// We had a GET request instead
	$action = $_GET['do'];
}
else
{
	// No request
	$action = 'main';
}

print_cp_header($vbphrase['dbtech_thanks_importer']);
switch ($action)
{
	case 'main':
		print_form_header('thanksimport', 'doimport');	
		print_table_header($vbphrase['dbtech_thanks_abe_importer']);
		print_input_row($vbphrase['dbtech_thanks_entries_per_page'], 'perpage', 250);
		print_submit_row($vbphrase['submit'], 0);

		print_form_header('thanksimport', 'doimport2');	
		print_table_header($vbphrase['dbtech_thanks_vbseo_importer']);
		print_input_row($vbphrase['dbtech_thanks_entries_per_page2'], 'perpage', 250);
		print_submit_row($vbphrase['submit'], 0);
		break;
		
	case 'doimport':
		$vbulletin->input->clean_array_gpc('r', array(
			'perpage' 		=> TYPE_UINT,
			'startat' 		=> TYPE_UINT
		));
	
		if (empty($vbulletin->GPC['perpage']))
		{
			$vbulletin->GPC['perpage'] = 250;
		}
		
		echo '<p>' . $vbphrase['dbtech_thanks_importing_thanks'] . '...</p>';
		
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($db);
		
		if ($db_alter->fetch_table_info('dbtech_thanks_entry'))
		{
			// Check what thanks column we should use
			$thanks_column = ($db_alter->table_field_data['varname'] ? 'varname' : 'entrytype');
		}
		else
		{
			die('Please install Advanced Post Thanks / Like before running the importer.');
		}
		
		$thanks = $db->query_read_slave("
			SELECT thanks.*, post.userid AS receiveduserid
			FROM " . TABLE_PREFIX . "post_thanks AS thanks
			LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = thanks.postid)
			WHERE id >= " . $vbulletin->GPC['startat'] . "
			ORDER BY id
			LIMIT " . $vbulletin->GPC['perpage']
		);
		$finishat = ($vbulletin->GPC['startat'] + $vbulletin->GPC['perpage']);
		
		while ($thanksinfo = $db->fetch_array($thanks))
		{
			if ($existing = $db->query_first("
				SELECT entryid FROM " . TABLE_PREFIX . "dbtech_thanks_entry
				WHERE $thanks_column = 'thanks'
					AND userid = " . $db->sql_prepare($thanksinfo['userid']) . "
					AND receiveduserid = " . $db->sql_prepare($thanksinfo['receiveduserid']) . "
					AND contenttype = 'post'
					AND contentid = " . $db->sql_prepare($thanksinfo['postid']) . "
					AND dateline = " . $db->sql_prepare($thanksinfo['date']) . "

			"))
			{
				// Already imported
				echo "Skipping: $thanksinfo[id]<br />\n";
				vbflush();
				continue;
			}

			// Basic info
			$entryinfo = array(
				'userid' 			=> $thanksinfo['userid'],
				'receiveduserid' 	=> $thanksinfo['receiveduserid'],
				'contenttype' 		=> 'post',
				'contentid' 		=> $thanksinfo['postid'],
				'dateline' 			=> $thanksinfo['date'],
			);

			// Extra info
			$entryinfo[$thanks_column] = 'thanks';

			// init data manager
			$dm =& THANKS::initDataManager('Entry', $vbulletin, ERRTYPE_SILENT);
				$dm->set_info('is_automated', true);
			
			// button fields
			foreach ($entryinfo AS $key => $val)
			{
				// These values are always fresh
				$dm->set($key, $val);
			}
			
			// Save! Hopefully.
			$dm->save();
			
			echo construct_phrase($vbphrase['processing_x'], $thanksinfo['id']) . "<br />\n";
			vbflush();
		
			$finishat = ($thanksinfo['id'] > $finishat ? $thanksinfo['id'] : $finishat);
		}
		
		$finishat++;
		
		if ($checkmore = $db->query_first_slave("SELECT id FROM " . TABLE_PREFIX . "post_thanks WHERE id >= $finishat LIMIT 1"))
		{
			print_cp_redirect("thanksimport.php?" . $vbulletin->session->vars['sessionurl'] . "do=doimport&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
			echo "<p><a href=\"thanksimport.php?" . $vbulletin->session->vars['sessionurl'] . "do=doimport&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
		}
		else
		{
			print_cp_message($vbphrase['dbtech_thanks_thanks_imported']);
		}

		break;
		
	case 'doimport2':
		$vbulletin->input->clean_array_gpc('r', array(
			'perpage' 		=> TYPE_UINT,
			'startat' 		=> TYPE_UINT
		));
	
		if (empty($vbulletin->GPC['perpage']))
		{
			$vbulletin->GPC['perpage'] = 250;
		}
		
		echo '<p>' . $vbphrase['dbtech_thanks_importing_likes'] . '...</p>';
		
		require_once(DIR . '/includes/class_dbalter.php');
		$db_alter = new vB_Database_Alter_MySQL($db);
		
		if ($db_alter->fetch_table_info('dbtech_thanks_entry'))
		{
			// Check what thanks column we should use
			$thanks_column = ($db_alter->table_field_data['varname'] ? 'varname' : 'entrytype');
		}
		else
		{
			die('Please install Advanced Post Thanks / Like before running the importer.');
		}
		
		$thanks = $db->query_read_slave("
			SELECT *
			FROM " . TABLE_PREFIX . "vbseo_likes AS thanks
			WHERE l_contentid >= " . $vbulletin->GPC['startat'] . "
			ORDER BY l_contentid
			LIMIT " . $vbulletin->GPC['perpage']
		);
		$finishat = $vbulletin->GPC['startat'];
		
		while ($thanksinfo = $db->fetch_array($thanks))
		{
			if ($existing = $db->query_first("
				SELECT entryid FROM " . TABLE_PREFIX . "dbtech_thanks_entry
				WHERE $thanks_column = 'likes'
					AND userid = " . $db->sql_prepare($thanksinfo['l_from_userid']) . "
					AND receiveduserid = " . $db->sql_prepare($thanksinfo['l_dest_userid']) . "
					AND contenttype = " . $db->sql_prepare(($thanksinfo['l_ctype'] == 1 ? 'post' : 'blog')) . "
					AND contentid = " . $db->sql_prepare($thanksinfo['l_contentid']) . "
					AND dateline = " . $db->sql_prepare($thanksinfo['l_dateline']) . "

			"))
			{
				// Already imported
				echo "Skipping: $thanksinfo[l_contentid]<br />\n";
				vbflush();
				continue;
			}

			if (!in_array($thanksinfo['l_ctype'], array(1, 2)))
			{
				// Only supports blog and post
				continue;
			}

			// Basic info
			$entryinfo = array(
				'userid' 			=> $thanksinfo['l_from_userid'],
				'receiveduserid' 	=> $thanksinfo['l_dest_userid'],
				'contenttype' 		=> ($thanksinfo['l_ctype'] == 1 ? 'post' : 'blog'),
				'contentid' 		=> $thanksinfo['l_contentid'],
				'dateline' 			=> $thanksinfo['l_dateline'],
			);

			// Extra info
			$entryinfo[$thanks_column] = 'likes';

			// init data manager
			$dm =& THANKS::initDataManager('Entry', $vbulletin, ERRTYPE_SILENT);
				$dm->set_info('is_automated', true);

			// button fields
			foreach ($entryinfo AS $key => $val)
			{
				// These values are always fresh
				$dm->set($key, $val);
			}
			
			// Save! Hopefully.
			$dm->save();
			
			echo construct_phrase($vbphrase['processing_x'], $thanksinfo['l_contentid']) . "<br />\n";
			vbflush();
		
			$finishat = ($thanksinfo['l_contentid'] > $finishat ? $thanksinfo['l_contentid'] : $finishat);
		}
		
		$finishat++;
		
		if ($checkmore = $db->query_first_slave("SELECT l_contentid FROM " . TABLE_PREFIX . "vbseo_likes WHERE l_contentid >= $finishat LIMIT 1"))
		{
			print_cp_redirect("thanksimport.php?" . $vbulletin->session->vars['sessionurl'] . "do=doimport2&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
			echo "<p><a href=\"thanksimport.php?" . $vbulletin->session->vars['sessionurl'] . "do=doimport2&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
		}
		else
		{
			print_cp_message($vbphrase['dbtech_thanks_thanks_imported']);
		}

		break;
}
print_cp_footer();