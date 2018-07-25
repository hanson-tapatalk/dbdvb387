<?php
global $vbphrase;

if ($postinfo['pagetext'] AND $postinfo['dbtech_thanks_requiredbuttons_content'])
{
	// Remove hidden content - ffffuuuuuuuu needless if checks -.- RAGEGUY
	THANKS::doBBCode($postinfo['pagetext'], $vbphrase['dbtech_thanks_stripped_content']);
}