<?php
if (defined('VBBLOG_CACHED_TEMPLATES'))
{
	// Workaround because they use get_class -.-
	if (
		$this->registry->options['vbblog_blogthispost']
			AND
		$this->registry->userinfo['permissions']['vbblog_general_permissions'] & $this->registry->bf_ugp_vbblog_general_permissions['blog_canviewown']
			AND
		$this->registry->userinfo['permissions']['vbblog_entry_permissions'] & $this->registry->bf_ugp_vbblog_entry_permissions['blog_canpost']
			AND
		$this->registry->userinfo['userid']
	)
	{
		$templater = vB_Template::create('blog_postbit_blog_this_post');
			$templater->register('post', $post);
		$template_hook['postbit_controls'] .= $templater->render();
	}
}

$contentid = 0;
switch (THIS_SCRIPT)
{
	case 'showthread':
	case 'showpost':
		$contentid = $this->post['postid'];
		break;
	
	case 'usernote':
		$contentid = $this->post['usernoteid'];
		break;
}

$userid = 0;
switch (THIS_SCRIPT)
{
	case 'usernote':
		$userid = $this->post['posterid'];
		break;
		
	default:
		$userid = $this->post['userid'];
		break;
}

$noticeforuminfo = $this->registry->forumcache[$thread['forumid']];
if (!THANKS::$isPro)
{
	// Lite-only shit
	$parentlist = explode(',', $noticeforuminfo['parentlist']);
	if ($parentlist[0] == -1)
	{
		// This forum
		$noticeforum = $noticeforuminfo['forumid'];		
	}
	else
	{
		$key = (count($parentlist) - 2);
		$noticeforum = $parentlist[$key];
	}
}
else
{
	// This forum
	$noticeforum = $noticeforuminfo['forumid'];
}

if (is_array(THANKS::$created['statistics']))
{
	$this->post = array_merge($this->post, THANKS::$created['statistics']);
}

$thanks_postbit = '';

if (!class_exists('vB_Template'))
{
	// Ensure we have this
	require_once(DIR . '/dbtech/thanks/includes/class_template.php');
}

if (intval($this->registry->versionnumber) == 3)
{
	global $vbcollapse;
	
	$post['thankspostid'] = 'thankspostmenu_' . $contentid . '_table';
	$post['thankspostimgid'] = 'collapseimg_' . $post['thankspostid'];
	$post['thankscollapseobj'] = $vbcollapse["collapseobj_{$post[thankspostid]}"];
	$post['thankscollapseimg'] = $vbcollapse["collapseimg_{$post[thankspostid]}"];
}

$thanks_postbit_stats = '';
foreach ((array)THANKS::$cache['button'] as $button)
{
	if (!$button['active'] OR ((int)$post['dbtech_thanks_settings'] & (int)$button['bitfield']))
	{
		// Inactive button
		continue;
	}
	
	if (!isset($post[$button['varname'] . '_given']) AND $userid AND !$hasInserted)
	{
		// Broken record
		$this->registry->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_thanks_statistics
				(userid)
			VALUES (
				" . intval($userid) . "
			)
		");
		
		// Ensure we don't try to mass insert
		$hasInserted = true;
	}
	
	if (!$button['disablestats_given'])
	{
		$templater = vB_Template::create('dbtech_thanks_postbit_stats');
			$templater->register('title', $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'] . ' (' . $vbphrase['dbtech_thanks_given'] . ')');
			$templater->register('stat', $post[$button['varname'] . '_given']);
		$thanks_postbit_stats .= $templater->render();
	}
	
	if (!$button['disablestats_received'])
	{
		$templater = vB_Template::create('dbtech_thanks_postbit_stats');
			$templater->register('title', $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'] . ' (' . $vbphrase['dbtech_thanks_received'] . ')');
			$templater->register('stat', $post[$button['varname'] . '_received']);
		$thanks_postbit_stats .= $templater->render();
	}
}

do
{
	// Not excluded
	$templater = vB_Template::create('dbtech_thanks_postbit');
		$templater->register('stylevar', 	$stylevar);
		$templater->register('post', 		$post);
		$templater->register('stats', 		$thanks_postbit_stats);
	$thanks_postbit .= $templater->render();	
	
	if (!$this->registry->options['dbtech_thanks_postbit_deployment'])
	{
		// Automatic deployment
		$template_hook['postbit_userinfo_right_after_posts'] .= $thanks_postbit;	
	}
	
	if (intval($this->registry->versionnumber) > 3)
	{
		vB_Template::preRegister('postbit', array('thanks_postbit' => $thanks_postbit));
		vB_Template::preRegister('postbit_legacy', array('thanks_postbit' => $thanks_postbit));
	}
	
	if (class_exists('POSTBITTABS'))
	{
		if (!POSTBITTABS::$created['dbtech_thanks'])
		{
			// DragonByte Tech: Postbit Tabs - registerView()
			POSTBITTABS::registerView('dbtech_thanks_stats', 'DragonByte Tech: Advanced Post Thanks / Like - Thanks Stats', (intval($this->registry->versionnumber) == 3 ? '{$thanks_postbit}' : '<dl>{vb:raw thanks_postbit}</dl>'));
			
			// Set created
			POSTBITTABS::$created['dbtech_thanks'] = true;
		}
	}
	
	if (!$this->registry->userinfo['userid'] AND $this->registry->options['dbtech_thanks_hideguests'])
	{
		// Guests can't see this
		break;
	}
	
	if ($this->registry->userinfo['dbtech_thanks_excluded'])
	{
		// We're excluded
		break;
	}
	
	if ($this->post['dbtech_thanks_excluded'])
	{
		// User is excluded
		break;
	}

	if (!THANKS::$processed)
	{
		// Haven't processed anything
		break;
	}
	
	if ($userid == $this->registry->userinfo['userid'] AND !THANKS::$entrycache['data'][$contentid] AND $this->registry->options['dbtech_thanks_cloud_displaystyle'] != 2)
	{
		// Can't click own posts
		break;
	}
	
	// Refresh AJAX post data
	$excluded = THANKS::doButtonExclusive($post);

	// Ensure this is set
	$this->registry->options['dbtech_thanks_cloud_location'] = ($this->registry->options['dbtech_thanks_cloud_location'] ? $this->registry->options['dbtech_thanks_cloud_location'] : 'postbit_end');
	
	if (defined('VB_API') AND VB_API === true AND in_array($this->registry->options['dbtech_thanks_cloud_location'], array('signature_start', 'signature_end')))
	{
		// Hack to avoid android crash
		$this->registry->options['dbtech_thanks_cloud_location'] = 'postbit_end';	
	}
	
	switch ($this->registry->options['dbtech_thanks_cloud_location'])
	{
		case 'signature_start':
			$show['dbtech_thanks_li'] 			= false;
			$show['dbtech_thanks_lineafter'] 	= true;
			break;
			
		case 'signature_end':
			$show['dbtech_thanks_li'] 			= false;
			$show['dbtech_thanks_lineafter'] 	= false;
			break;

		case 'postbit_start':
		case 'postbit_end':
			$show['dbtech_thanks_li'] 			= true;
			break;
	}
	
	// Extract the variables from the display processer
	list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, array_merge($post, array('userid' => $userid)), $thread);
	
	if ($actions)
	{
		$templater = vB_Template::create('dbtech_thanks_postbit_entries_actions');
			$templater->register('post', 	$post);		
			$templater->register('actions', $actions);
		$actions = $templater->render();
	}
	
	// Whether we're showing these areas
	$show['dbtech_thanks_area'] = ($actions OR $entries);
	
	if ($this->registry->options['dbtech_thanks_integratedactions'])
	{
		// Stuff these in the postbit_controls instead
		$template_hook['postbit_controls'] .= $actions;
		$actions = '';
	}
	
	$extrainfo = array();
	if ($this->registry->options['dbtech_thanks_displayextrainfo'])
	{
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			if (!$button['active'])
			{
				// Skip this button
				continue;
			}

			if ((int)$this->registry->forumcache[$noticeforum]['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
			{
				// Button was disabled for this forum
				continue;
			}
							
			if (((int)$this->registry->forumcache[$noticeforum]['dbtech_thanks_firstpostonly'] & (int)$button['bitfield']) AND 
				$thread['firstpostid'] != $this->post['postid']
			)
			{
				// First Post Only
				continue;
			}			
	
			// Store buttons by varname
			$extrainfo[] = intval(THANKS::$entrycache['count'][$contentid][$button['varname']]) . ' ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}
	}
	
	$templater = vB_Template::create('dbtech_thanks_postbit_entries');
		$templater->register('post', 		$post);
		$templater->register('show', 		$show);
		$templater->register('entries', 	$entries);
		$templater->register('actions', 	$actions);
		$templater->register('extrainfo', 	implode(', ', $extrainfo));
	$entryWrapper = $templater->render();
	
	switch ($this->registry->options['dbtech_thanks_cloud_location'])
	{
		case 'signature_start':
			$post['signature'] = $entryWrapper . $post['signature'];
			break;
			
		case 'signature_end':
			$post['signature'] .= $entryWrapper;
			break;

		case 'postbit_start':
		case 'postbit_end':
			$template_hook[$this->registry->options['dbtech_thanks_cloud_location']] .= $entryWrapper;
			break;
	}
}
while (false);
?>