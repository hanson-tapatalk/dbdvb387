<?php
if (!isset(THANKS::$entrycache['hasCached']))
{
	// Init this
	THANKS::$entrycache = array(
		'data' 			=> array(),
		'display' 		=> array(),
		'others' 		=> array(),
		'clickcount' 	=> array(),
		'hasCached' 	=> true,
	);

	$postIds = THANKS::$db->fetchAllSingleKeyed('
		SELECT postid 
		FROM $post 
		WHERE visible = 1
			AND threadid = ? 
		ORDER BY dateline :postOrder 
		LIMIT :limitStart, :limitEnd
	', 'postid', 'postid', array(
		$threadid,
		':postOrder' 	=> $postorder,
		':limitStart' 	=> $startat,
		':limitEnd' 	=> $vbulletin->GPC['perpage']
	));

	// Fetch rewards
	$results = THANKS::$db->fetchAll('
		SELECT *
		FROM $dbtech_thanks_entry
		WHERE contentid :postIds
			AND contenttype = \'post\'
			AND userid = ?
	', array(
		$vbulletin->userinfo['userid'],
		':postIds' => THANKS::$db->queryList($postIds),		
	));
	foreach ($results as $result)
	{
		THANKS::$entrycache['data'][$result['contentid']][$result['varname']][$result['userid']] = $result;
	}
}

$override = array();
if ($post['dbtech_thanks_requiredbuttons_content'])
{
	foreach (THANKS::$cache['button'] as $button)
	{
		if (!$button['active'])
		{
			// Inactive button
			continue;
		}
		
		if (!$button['active'])
		{
			// Inactive button
			continue;
		}
		
		if (((int)$post['dbtech_thanks_requiredbuttons_content'] & (int)$button['bitfield']) AND 
			$post['userid'] != $vbulletin->userinfo['userid'] AND 
			!THANKS::$entrycache['data'][$post['postid']][$button['varname']][$vbulletin->userinfo['userid']]
		)
		{
			// Override message on the attach field
			$override[] = $vbphrase['dbtech_thanks_stripped_content'];
		}
	}
}

// Ensure this works as intended
$override = implode('<br />', $override);		

if (!THANKS::$isPro)
{					
	if ($override)
	{
		// We require something
		$post['message'] = $override;
	}
}
else
{
	// We may or may not require something
	THANKS::doBBCode($post['message'], ($override ? $override : '$1'));
}