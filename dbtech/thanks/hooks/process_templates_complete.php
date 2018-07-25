<?php
global $vbulletin, $vbphrase, $template_hook;

if (/*THANKS::$permissions['canview'] AND */version_compare($vbulletin->versionnumber, '4.2.0', '<'))
{
	if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
	{
		// We need the template class
		require_once(DIR . '/dbtech/thanks/includes/class_template.php');
	}
	
	if (intval($vbulletin->versionnumber) > 3 AND (defined('CMS_SCRIPT') OR defined('VBA_SCRIPT')) AND !defined('THANKS_NAV_LOOPED') AND THIS_SCRIPT != 'thanks')
	{
		// vB4 have an awkward design quirk with the Suite, we'll fire the plugin elsewhere
		define('THANKS_NAV_LOOPED', true);
		$vbulletin->pluginlist['process_templates_complete'] .= "\r\nrequire(DIR . '/dbtech/thanks/hooks/process_templates_complete.php');";
		vBulletinHook::set_pluginlist($vbulletin->pluginlist);
	}
	else
	{
		if ($vbulletin->options['dbtech_thanks_integration'] & 1)
		{
			$template_hook['navbar_quick_links_menu_pos4'] .= vB_Template::create('dbtech_thanks_quicklinks_link')->render();
		}
		if ($vbulletin->options['dbtech_thanks_integration'] & 2)
		{
			$template_hook['navbar_community_menu_end'] .= vB_Template::create('dbtech_thanks_quicklinks_link')->render();
		}
	}
}