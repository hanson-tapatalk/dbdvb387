<?php
// Fetch required classes
require_once(DIR . '/dbtech/thanks/includes/class_core.php');
require_once(DIR . '/dbtech/thanks/includes/class_cache.php');
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/thanks/includes/class_template.php');
}

if (isset($this) AND is_object($this))
{
	// Loads the cache class
	THANKS_CACHE::init($vbulletin, $this->datastore_entries);
}
else
{
	// Loads the cache class
	THANKS_CACHE::init($vbulletin, $specialtemplates);
}

// Initialise thanks
THANKS::init($vbulletin);

//if (THANKS::$permissions['canview'])
//{
	$show['thanks'] = $vbulletin->options['dbtech_thanks_navbar'];
	$show['thanks_ispro'] = THANKS::$isPro;
	if ($vbulletin->options['dbtech_thanks_integration'] & 1)
	{
		$show['thanks_ql'] = true;
	}
	if ($vbulletin->options['dbtech_thanks_integration'] & 2)
	{
		$show['thanks_com'] = true;
	}
//}

// Show branding or not
$show['thanks_branding'] = $vbulletin->options['dbtech_thanks_branding_free'] != '25599748-40762228ffdcc52c2d61ca69407b00f0';
$show['dbtech_thanks_producttype'] = (THANKS::$isPro ? ' (Pro)' : ' (Lite)');

if (in_array(THIS_SCRIPT, array('thanks', 'showthread', 'blog', 'dbtech_gallery')) AND $show['thanks_branding'] AND !$show['_dbtech_branding_override'])
{
	$brandingVariables = array(
		'flavour' 			=> 'Feedback Buttons provided by ',
		'productid' 		=> 22,
		'utm_source' 		=> str_replace('www.', '', $_SERVER['HTTP_HOST']),		
		'utm_content' 		=> (THANKS::$isPro ? 'Pro' : 'Lite'),
		'referrerid' 		=> $vbulletin->options['dbtech_thanks_referral'],
		'title' 			=> 'Advanced Post Thanks / Like',
		'displayversion' 	=> $vbulletin->options['dbtech_thanks_displayversion'],
		'version' 			=> THANKS::$version,
		'producttype' 		=> $show['dbtech_thanks_producttype'],
		'showhivel' 		=> (!THANKS::$isPro AND !$vbulletin->options['dbtech_thanks_nohivel'])
	);

	$str = $brandingVariables['flavour'] . '
		<a rel="nofollow" href="http://www.dragonbyte-tech.com/vbecommerce.php' . ($brandingVariables['productid'] ? '?productid=' . $brandingVariables['productid'] . '&do=product&' : '?') . 'utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=Footer%2BLinks&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">' . $brandingVariables['title'] . ($brandingVariables['displayversion'] ? ' v' . $brandingVariables['version'] : '') . $brandingVariables['producttype'] . '</a> - 
		<a rel="nofollow" href="http://www.dragonbyte-tech.com/?utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=Footer%2BLinks&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">vBulletin Mods &amp; Addons</a> Copyright &copy; ' . date('Y') . ' DragonByte Technologies Ltd.' . 
		($brandingVariables['showhivel'] ? ' Runs best on <a rel="nofollow" href="http://www.hivelocity.net/?utm_source=Iain%2BKidd&utm_medium=back%2Blink&utm_term=Dedicated%2BServer%2BSponsor&utm_campaign=Back%2BLinks%2Bfrom%2BIain%2BKidd" target="_blank">HiVelocity Hosting</a>.' : '');
	$vbulletin->options['copyrighttext'] = (trim($vbulletin->options['copyrighttext']) != '' ? $str . '<br />' . $vbulletin->options['copyrighttext'] : $str);
}