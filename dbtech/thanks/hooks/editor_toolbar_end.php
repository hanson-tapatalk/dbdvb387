<?php
if ($vbulletin->GPC['dbtech_thanks_in_posting'] AND !$vbulletin->userinfo['dbtech_thanks_excluded'])
{
	if (!class_exists('vB_Template'))
	{
		// Ensure we have this
		require_once(DIR . '/dbtech/thanks/includes/class_template.php');
	}
	
	foreach (THANKS::$cache['button'] as $button)
	{
		if (!$button['active'])
		{
			// Inactive button
			continue;
		}
		
		if ((int)$vbulletin->forumcache[$noticeforum]['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
		{
			// Button was disabled for this forum
			continue;
		}
		
		if (THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'candisableclick') AND THANKS::$isPro)
		{
			// We can require click
			$templater = vB_Template::create('dbtech_thanks_post_form');
				$templater->register('name', 'disabledbuttons[' . $button['buttonid'] . ']');
				$templater->register('phrase', construct_phrase($vbphrase['dbtech_thanks_disable_x'], $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title']));
				$templater->register('checked', (((int)$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield']) ? ' checked="checked"' : ''));
			$disablesmiliesoption .= $templater->render();			
		}
		
		if (THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'canreqclick'))
		{
			foreach (array('content', 'attach') as $type)
			{
				// We can require click
				$templater = vB_Template::create('dbtech_thanks_post_form');
					$templater->register('name', 'requiredbuttons[' . $type . '][' . $button['buttonid'] . ']');
					$templater->register('phrase', construct_phrase($vbphrase['dbtech_thanks_require_x_to_see_y'], $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'], $vbphrase['dbtech_thanks_' . $type]));
					$templater->register('checked', (((int)$vbulletin->GPC['dbtech_thanks_postinfo']['dbtech_thanks_requiredbuttons_' . $type] & (int)$button['bitfield']) ? ' checked="checked"' : ''));
				$disablesmiliesoption .= $templater->render();
			}
		}
	}
}
//$vbulletin->GPC['dbtech_thanks_postinfo']