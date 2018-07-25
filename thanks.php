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
define('IN_THANKS', true);

if (isset($_REQUEST['do']) AND $_REQUEST['do'] == 'ajax')
{
	define('CSRF_PROTECTION', true);
	define('LOCATION_BYPASS', 1);
	define('NOPMPOPUP', 1);
	define('VB_ENTRY', 'ajax.php');
	define('SESSION_BYPASS', true);
	define('VB_ENTRY_TIME', microtime(true));
}

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('dbtech_thanks', 'user', 'posting', 'album', 'messaging');

// get templates used by all actions
$globaltemplates = array(
	'dbtech_thanks',
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'main' => array(
		'dbtech_thanks_main',
	),
	'hottest' => array(
		'dbtech_thanks_hottest',
		'dbtech_thanks_hottest_threadbit',
		'dbtech_thanks_hottest_postbit',
		'dbtech_thanks_statistics_statisticbit',
	),
	'statistics' => array(
		'dbtech_thanks_statistics',
		'dbtech_thanks_statistics_userbit',
		'dbtech_thanks_statistics_statisticbit',
	),
	'list' => array(
		'dbtech_thanks_list',
		'dbtech_thanks_list_bit',
	),
	'profile' => array(
		'USERCP_SHELL',
		'usercp_nav_folderbit',
	),
);

// get special data templates from the datastore
require_once('./dbtech/thanks/includes/specialtemplates.php');
$specialtemplates = $extracache;

// ############################### default do value ######################
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = $_GET['do'] = 'statistics';
}

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

if (!class_exists('vB_Template'))
{
	// Ensure we have this
	require_once(DIR . '/dbtech/thanks/includes/class_template.php');
}

if (!class_exists('THANKS'))
{
	eval(standard_error($vbphrase['dbtech_thanks_deactivated']));
}

if ($_REQUEST['do'] == 'devinfo' AND $_REQUEST['devkey'] == 'dbtech')
{
	THANKS::outputJSON(array(
		'version' 		=> THANKS::$version,
		'versionnumber' => THANKS::$versionnumber,
		'pro'			=> THANKS::$isPro,
		'vbversion'		=> $vbulletin->versionnumber
	));
}

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

// Strip non-valid characters
$action = preg_replace('/[^\w-]/i', '', $action);

// Core page template
$page_template = 'dbtech_thanks';

if (!file_exists(DIR . '/dbtech/thanks/actions/' . $action . '.php'))
{
	if (!file_exists(DIR . '/dbtech/thanks_pro/actions/' . $action . '.php'))
	{
		// Throw error from invalid action
		eval(standard_error(fetch_error('dbtech_thanks_error_x', $vbphrase['dbtech_thanks_invalid_action'])));
	}
	else
	{
		// Include the selected file
		include_once(DIR . '/dbtech/thanks_pro/actions/' . $action . '.php');	
	}
}
else
{
	// Include the selected file
	include_once(DIR . '/dbtech/thanks/actions/' . $action . '.php');	
}

if (intval($vbulletin->versionnumber) == 3)
{
	// Create navbits
	$navbits = construct_navbits($navbits);	
	eval('$navbar = "' . fetch_template('navbar') . '";');
}
else
{
	$navbar = render_navbar_template(construct_navbits($navbits));	
}

// Finish the main template
$templater = vB_Template::create($page_template);
	$templater->register_page_templates();
	$templater->register('navclass', 		$navclass);
	$templater->register('HTML', 			$HTML);
	$templater->register('navbar', 			$navbar);
	$templater->register('pagetitle', 		$pagetitle);
	$templater->register('template_hook', 	$template_hook);
	$templater->register('includecss', 		$includecss);
	$templater->register('year',			date('Y'));
	$templater->register('jQueryVersion',	THANKS::$jQueryVersion);
	$templater->register('jQueryPath',		THANKS::jQueryPath());
	$templater->register('version',			THANKS::$version);
	$templater->register('versionnumber', 	THANKS::$versionnumber);
print_output($templater->render());