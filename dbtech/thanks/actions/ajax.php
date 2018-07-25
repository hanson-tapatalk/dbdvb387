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

if (!$_REQUEST['action'])
{
	print_output();
}

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// #######################################################################
if ($_REQUEST['action'] == 'entry')
{
	// Init this
	$retval = array();
		
	// Grab these
	$contenttype = $vbulletin->input->clean_gpc('p', 'contenttype', TYPE_STR);
	$contenttype = ($contenttype ? preg_replace('/[^\w-]/i', '', $contenttype) : 'post');
	
	if (!file_exists(DIR . '/dbtech/thanks_pro/contenttypes/' . $contenttype . '/save.php'))
	{
		if (file_exists(DIR . '/dbtech/thanks/contenttypes/' . $contenttype . '/save.php'))
		{
			// We can do this
			require(DIR . '/dbtech/thanks/contenttypes/' . $contenttype . '/save.php');
		}		
	}
	else
	{
		// We can do this
		require(DIR . '/dbtech/thanks_pro/contenttypes/' . $contenttype . '/save.php');
	}
	
	// Return the compiled list
	THANKS::outputXML($retval);
}