<?php
switch($filename)
{
	case 'thanks.php':
		if ($values['do'] == 'taglist')
		{
			$userinfo['activity'] = 'dbtech_thanks_taglist';
		}
		break;
}
?>