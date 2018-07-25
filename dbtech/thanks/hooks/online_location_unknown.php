<?php
if (strpos($userinfo['activity'], 'dbtech_thanks_') === 0)
{
	$handled = true;	
	switch ($userinfo['activity'])
	{
		case 'dbtech_thanks_taglist':
			// Archive HO
			$userinfo['action'] = $vbphrase['dbtech_thanks_viewing_tag_list'];
			break;
			
		default:
			$handled = false;
			break;
	}
}
?>