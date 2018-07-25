<?php
if (THANKS::$isPro)
{
	// Show hidden content
	THANKS::doBBCode($previewmessage, '$1');
}