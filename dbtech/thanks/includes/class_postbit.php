<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/


/**
* Postbit optimized for posts with no content - requires thanks
*/
class vB_Postbit_Thanks extends vB_Postbit_Post
{
	/**
	* May not be displayed.
	*/
	function process_attachments()
	{
		global $show, $vbphrase;
		
		$override = '';
		
		$checkArr = (version_compare($this->registry->versionnumber, '4.1.10') >= 0 ? $this->post['allattachments'] : $this->post['attachments']);
		if (
			$checkArr AND 
			$this->post['dbtech_thanks_requiredbuttons_attach'] AND 
			$this->post['userid'] != $this->registry->userinfo['userid'] AND
			!($this->registry->userinfo['permissions']['dbtech_thankspermissions'] & $this->registry->bf_ugp_dbtech_thankspermissions['canbypassreq'])
		)
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

				if (!((int)$this->post['dbtech_thanks_requiredbuttons_attach'] & (int)$button['bitfield']))
				{
					// We didn't require attachments
					continue;
				}

				if (THANKS::$entrycache['data'][$this->post['postid']][$button['varname']][$this->registry->userinfo['userid']])
				{
					// This user has clicked the required button
					continue;
				}

				if (THANKS::checkPermissions($this->registry->userinfo, $button['permissions'], 'canbypassreq'))
				{
					// We can bypass requirements
					continue;
				}
				
				// Override message on the attach field
				$override .= '<li>' . construct_phrase($vbphrase['dbtech_thanks_require_x_attach'], $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title']) . '</li>';
			}
		}
		
		if ($override)
		{
			// We require thanks
			$show['attachments'] = true;
			$show['otherattachment'] = true;
			$this->post['otherattachments'] = $override;
		}
		else
		{
			if (THIS_SCRIPT == 'showpost')
			{
				$this->post['allattachments'] = $this->post['attachments'];
			}
			parent::process_attachments();
		}		
	}
	
	/**
	* Override message
	*/
	function parse_bbcode()
	{
		global $vbphrase;
		
		if (!defined('IN_MOBIQUO') AND !defined('IN_FRNR'))
		{
			if (!$this->post['message'])
			{
				// Weird ass workaround
				$this->post['message'] =& $this->post['pagetext'];
			}
		}
					
		$override = array();
		if (
			$this->post['dbtech_thanks_requiredbuttons_content'] AND
			$this->post['userid'] != $this->registry->userinfo['userid'] AND
			!($this->registry->userinfo['permissions']['dbtech_thankspermissions'] & $this->registry->bf_ugp_dbtech_thankspermissions['canbypassreq'])
		)
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

				if (!((int)$this->post['dbtech_thanks_requiredbuttons_content'] & (int)$button['bitfield']))
				{
					// We didn't require attachments
					continue;
				}

				if (THANKS::$entrycache['data'][$this->post['postid']][$button['varname']][$this->registry->userinfo['userid']])
				{
					// This user has clicked the required button
					continue;
				}

				if (THANKS::checkPermissions($this->registry->userinfo, $button['permissions'], 'canbypassreq'))
				{
					// We can bypass requirements
					continue;
				}

				// Override message on the attach field
				$override[] = construct_phrase($vbphrase['dbtech_thanks_require_x_post'], $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title']);
			}
		}
		
		// Ensure this works as intended
		$override = implode("\n\n", $override);		
		
		if (!THANKS::$isPro)
		{					
			if ($override)
			{
				// We require something
				$this->post['message'] = $override;
			}
			else
			{
				// Just parse BBCode normally
				parent::parse_bbcode();
			}
		}
		else
		{
			// Should really be an option around this methinks
			unset($this->post['pagetext_html']);				
					
			// We may or may not require something
			THANKS::doBBCode($this->post['message'], ($override ? $override : '$1'));
			
			// Also parse standard BBCode
			parent::parse_bbcode();
		}
	}
}

/**
* Postbit optimized for downranked posts
*/
class vB_Postbit_Thanks_Downranked extends vB_Postbit_Post
{
	/**
	* The name of the template that will be used to display this post.
	*
	* @var	string
	*/
	var $templatename = 'dbtech_thanks_postbit_downranked';

	/**
	* Will not be displayed. No longer does anything.
	*/
	function process_attachments()
	{
	}

	/**
	* Will not be displayed. No longer does anything.
	*/
	function process_im_icons()
	{
	}

	/**
	* Will not be displayed. No longer does anything.
	*/
	function parse_bbcode()
	{
	}
}
?>