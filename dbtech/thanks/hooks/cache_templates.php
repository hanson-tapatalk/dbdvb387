<?php
if (!isset($cache))
{
	$cache  = array();
}

if (intval($vbulletin->versionnumber) == 4)
{
	$cache = array_merge($cache, array(
		'dbtech_thanks_navbar',
		'dbtech_thanks_block_entries',
	));
}

$cache = array_merge($cache, array(
	'dbtech_thanks_post_form',
));

switch (THIS_SCRIPT)
{
	case 'usernote':
	case 'showthread':
	case 'showpost':
	case 'private':
	case 'announcement':
	case 'vbcms':
		$cache = array_merge(array(
			'dbtech_thanks_postbit_entries',
			'dbtech_thanks_postbit_entries_actions',
			'dbtech_thanks_postbit_entries_entrybit',
			'dbtech_thanks_postbit_entries_actionbit',
			'dbtech_thanks_postbit_entries_actionbit_notext',
			'dbtech_thanks_postbit',
			'dbtech_thanks_postbit_stats',
			'dbtech_thanks_postbit_entries_usernote',
			'dbtech_thanks_css',
		), $cache);
		break;
		
	case 'blog':
	case 'entry':
		$cache = array_merge(array(
			'dbtech_thanks_postbit_entries_actionbit',
			'dbtech_thanks_postbit_entries_actionbit_notext',
			'dbtech_thanks_postbit_entries_actions',
			'dbtech_thanks_postbit_entries_entrybit',
			'dbtech_thanks_postbit_entries_blog',
		), $cache);	
		break;
		
	case 'group':
		$cache = array_merge(array(
			'dbtech_thanks_postbit_entries_actionbit',
			'dbtech_thanks_postbit_entries_actionbit_notext',
			'dbtech_thanks_postbit_entries_actions',
			'dbtech_thanks_postbit_entries_entrybit',
			'dbtech_thanks_postbit_entries_socialgroup',
		), $cache);	
		break;
		
	case 'dbtech_gallery':
		$cache = array_merge(array(
			'dbtech_thanks_postbit_entries_actionbit',
			'dbtech_thanks_postbit_entries_actionbit_notext',
			'dbtech_thanks_postbit_entries_actions',
			'dbtech_thanks_postbit_entries_entrybit',
			'dbtech_thanks_postbit_entries_dbgallery_image',
		), $cache);	
		break;
		
	case 'dbtech_review':
		$cache = array_merge(array(
			'dbtech_thanks_postbit_entries_actionbit',
			'dbtech_thanks_postbit_entries_actionbit_notext',
			'dbtech_thanks_postbit_entries_actions',
			'dbtech_thanks_postbit_entries_entrybit',
			'dbtech_thanks_postbit_entries_dbreview_review',
		), $cache);	
		break;
		
	case 'member':
		$cache = array_merge(array(
			'dbtech_thanks_postbit_entries_actionbit',
			'dbtech_thanks_postbit_entries_actionbit_notext',
			'dbtech_thanks_postbit_entries_blog',
			'dbtech_thanks_postbit_entries_visitormessage',
			'dbtech_thanks_postbit_entries_actions',
			'dbtech_thanks_postbit_entries_entrybit',

		), $cache);	
		break;
}

//if ($vbulletin->userinfo['permissions']['dbtech_thankspermissions'] & $vbulletin->bf_ugp_dbtech_thankspermissions['canview'])
//{
	// Global templates
	if ($vbulletin->options['dbtech_thanks_integration'] & 1 OR $vbulletin->options['dbtech_thanks_integration'] & 2)
	{
		$cache[] = 'dbtech_thanks_quicklinks_link';
	}
//}



if (THIS_SCRIPT == 'member')
{
	$cache[] = 'dbtech_thanks_member_css';	
	$cache[] = 'dbtech_thanks_memberinfo_block_thanks';
	$cache[] = 'dbtech_thanks_result';
	$cache[] = 'dbtech_thanks_result_bit';
}

if (intval($vbulletin->versionnumber) == 3)
{
	$cache[] = 'dbtech_thanks.css';
	//$cache[] = 'dbtech_vbshout_colours.css';
	//$cache[] = 'dbtech_vbshout_archive_shoutbit';
	//$cache[] = 'dbtech_vbshout_archive_topshoutbit';	
	
	$globaltemplates = array_merge($globaltemplates, $cache);
}
?>