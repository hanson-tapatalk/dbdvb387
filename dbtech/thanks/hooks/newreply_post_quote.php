<?php
global $vbphrase;

if ($postinfo['dbtech_thanks_requiredbuttons_content'])
{
	// Remove hidden content
	THANKS::doBBCode($pagetext, $vbphrase['dbtech_thanks_stripped_content']);
}