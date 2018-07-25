<?php
class vBActivity_Type_likesgiven extends vBActivity_Type_Core
{
	/**
	* Function to call before every action
	*/	
	public function action()
	{
		if (!parent::action())
		{
			// This type is inactive
			return false;
		}
		
		// We made it!
		return ($this->registry->products['dbtech_thanks']);
	}
	
	/**
	* What happens on recalculate points
	*
	* @param	array	The user info
	*/	
	public function recalculate_points($user)
	{
		if (!$this->action())
		{
			// Disabled
			return false;
		}
		
		// Reset the points
		parent::reset_points($user);
		
		$tbl 				= 'dbtech_thanks_entry';
		$idfield 			= 'entryid';
		$datefield 			= $tbl . '.dateline';
		$hook_query_select 	= " , thread.forumid";
		$hook_query_join 	= " LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = $tbl.contentid)
								LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)
		";
		$hook_query_where 	= " AND varname = 'likes' AND dbtech_thanks_entry.userid = " . $user['userid'];
		$multiplier 		= 1;
			
		if (VBACTIVITY::$isPro)
		{
			if ($this->registry->GPC['startdate'])
			{
				// Set dateline
				$hook_query_where = " AND " . ($datefield ? $datefield : $tbl . '.dateline') . " >= " . $this->registry->GPC['startdate'] . $hook_query_where;
			}
		}

		$results = $this->registry->db->query_read_slave("
			SELECT * $hook_query_select
			FROM " . TABLE_PREFIX . "$tbl AS $tbl
			$hook_query_join
			WHERE 1=1 
				$hook_query_where
		");
		while ($result = $this->registry->db->fetch_array($results))
		{
			if ($result['type'] != 'post')
			{
				// Ensure this doesn't snafu
				$result['forumid'] = 0;
			}

			// Insert points log
			VBACTIVITY::insert_points($this->config['typename'], $result[$idfield], $user['userid'], $multiplier, $result['dateline'], $result['forumid']);
		}
		$this->registry->db->free_result($results);
		unset($result);
	}
	
	/**
	* Checks whether we meet a certain criteria
	*
	* @param	integer	The criteria ID we are checking
	* @param	array	Information regarding the user we're checking
	* 
	* @return	boolean	Whether this criteria has been met
	*/	
	public function check_criteria($conditionid, &$userinfo)
	{
		if (!$this->action())
		{
			// Disabled
			return false;
		}

		// Ensure we've got points cached
		parent::check_criteria($conditionid, $userinfo);
		
		if (!$condition = VBACTIVITY::$cache['condition'][$conditionid])
		{
			// condition doesn't even exist
			return false;
		}
		
		// grab us the type name
		$typename = VBACTIVITY::$cache['type'][$condition['typeid']]['typename'];
		
		/*
		$changedTypeName = false;
		if ($condition['forumid'] AND !$userinfo[$typename . '_forumid_' . $condition['forumid']])
		{
			// Fetch this
			$userinfo[$typename . '_forumid_' . $condition['forumid']] = VBACTIVITY::$db->fetchOne('
				SELECT COUNT(*)
				FROM $dbtech_usertag_entry AS entry
				INNER JOIN $post AS post ON(post.postid = entry.contentid)
				INNER JOIN $thread AS thread USING(threadid)
				WHERE thread.forumid = ?
					AND entry.userid = ?
					AND entry.type = \'post\'
					AND entry.varname = \'likes\'
			', array(
				$condition['forumid'],
				$userinfo['userid'],
			));

			// Override type name
			$changedTypeName = $typename . '_forumid_' . $condition['forumid'];
		}
		
		if ($condition['type'] == 'points' OR $userinfo[$typename] OR $condition['forumid'])
		{
			// This has been cached
			return VBACTIVITY::check_criteria($conditionid, $userinfo, $changedTypeName);
		}
		*/
		
		if ($condition['type'] == 'points' OR $userinfo[$typename])
		{
			// This has been cached
			return VBACTIVITY::check_criteria($conditionid, $userinfo);
		}
		
		if (!$userinfo[$typename])
		{
			// We need more info
			$additionalinfo = $this->registry->db->query_first_slave("
				SELECT likes_given AS $typename
				FROM " . TABLE_PREFIX . "dbtech_thanks_statistics
				WHERE userid = " . $userinfo['userid'] . "
			");
			
			// We had this info
			$userinfo[$typename] = intval($additionalinfo[$typename]);
		}
		
		// Pass criteria checking onwards
		return VBACTIVITY::check_criteria($conditionid, $userinfo);
	}	
}
?>