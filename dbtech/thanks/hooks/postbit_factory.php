<?php
// Ensure we can use our postbit
require_once(DIR . '/dbtech/thanks/includes/class_postbit.php');

switch ($postbit_type)
{
	case 'post':
	case 'dbtech_thanks':
		$custom_template = $out->templatename;
		$out = new vB_Postbit_Thanks();
		if ($this->registry->options['legacypostbit'])
		{
			$out->templatename = ($custom_template ? $custom_template : 'postbit_legacy');
		}
		$handled_type = true;
		break;
		
	case 'dbtech_thanks_downranked':
		$out = new vB_Postbit_Thanks_Downranked();
		$handled_type = true;
		break;
}
?>