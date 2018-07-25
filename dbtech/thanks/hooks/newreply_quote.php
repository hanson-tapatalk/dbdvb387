<?php
global $vbphrase;

if ($quote_post['dbtech_thanks_requiredbuttons_content'])
{
	// Remove hidden content
	THANKS::doBBCode($pagetext, $vbphrase['dbtech_thanks_stripped_content']);
}