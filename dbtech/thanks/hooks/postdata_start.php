<?php
$this->validfields = array_merge($this->validfields, array(
	'dbtech_thanks_disabledbuttons' 			=> array(TYPE_UINT, REQ_NO),
	'dbtech_thanks_requiredbuttons_content' 	=> array(TYPE_UINT, REQ_NO),
	'dbtech_thanks_requiredbuttons_attach' 		=> array(TYPE_UINT, REQ_NO),
));