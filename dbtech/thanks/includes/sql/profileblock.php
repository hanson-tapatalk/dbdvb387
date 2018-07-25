<?php
if (count(self::$created['numEntries']['post']) AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 64))
{
	$SQL[] = '
		SELECT
			post.postid,
			post.title AS posttitle,
			thread.title AS threadtitle,
			thread.title AS title,
			thread.threadid,
			thread.forumid,
			entry_post.*
		FROM $dbtech_thanks_entry AS entry_post
		LEFT JOIN $post AS post ON (post.postid = entry_post.contentid)
		LEFT JOIN $thread AS thread ON (thread.threadid = post.threadid)
		WHERE entry_post.entryid ' . self::$db->queryList(self::$created['numEntries']['post']) . '
			AND post.visible = 1
	';
}



if ((bool)self::$vbulletin->products['dbtech_gallery'] AND count(self::$created['numEntries']['dbgallery_image']) AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 2))
{
	$SQL[] = '
		SELECT
			image.imageid AS postid,
			image.title_clean AS posttitle,
			image.filename AS threadtitle,
			image.filename AS title,
			image_instance.shortname AS threadid,
			0 AS forumid,
			entry_dbgallery_image.*
		FROM $dbtech_thanks_entry AS entry_dbgallery_image
		LEFT JOIN $dbtech_gallery_images AS image ON (image.imageid = entry_dbgallery_image.contentid)
		LEFT JOIN $dbtech_gallery_instances AS image_instance ON (image_instance.instanceid = image.instanceid)		
		WHERE entry_dbgallery_image.entryid ' . self::$db->queryList(self::$created['numEntries']['dbgallery_image']) . '
	';
}

if ((bool)self::$vbulletin->products['dbtech_review'] AND count(self::$created['numEntries']['dbreview_review']) AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 32))
{
	$SQL[] = '
		SELECT
			review.reviewid AS postid,
			review.title_clean AS posttitle,
			review.title_clean AS threadtitle,
			review.title_clean AS title,
			review_instance.shortname AS threadid,
			0 AS forumid,
			entry_dbreview_review.*
		FROM $dbtech_thanks_entry AS entry_dbreview_review
		LEFT JOIN $dbtech_review_reviews AS review ON (review.reviewid = entry_dbreview_review.contentid)
		LEFT JOIN $dbtech_review_instances AS review_instance ON (review_instance.instanceid = review.instanceid)		
		WHERE entry_dbreview_review.entryid ' . self::$db->queryList(self::$created['numEntries']['dbreview_review']) . '
	';
}

if (count(self::$created['numEntries']['socialgroup']) AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 4))
{
	$SQL[] = '
		SELECT
			groupmessage.gmid AS postid,
			groupmessage.title AS posttitle,
			firstgroupmessage.title AS threadtitle,
			firstgroupmessage.title AS title,
			groupmessage.discussionid AS threadid,
			0 AS forumid,
			entry_socialgroup.*
		FROM $dbtech_thanks_entry AS entry_socialgroup
		LEFT JOIN $groupmessage AS groupmessage ON (groupmessage.gmid = entry_socialgroup.contentid)
		LEFT JOIN $discussion AS discussion ON (discussion.discussionid = groupmessage.discussionid)		
		LEFT JOIN $groupmessage AS firstgroupmessage ON (firstgroupmessage.gmid = discussion.firstpostid)		
		WHERE entry_socialgroup.entryid ' . self::$db->queryList(self::$created['numEntries']['socialgroup']) . '
			AND groupmessage.state = \'visible\'
	';
}
if (count(self::$created['numEntries']['usernote']) AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 8))
{
	$SQL[] = '
		SELECT
			usernote.usernoteid AS postid,
			usernote.title AS posttitle,
			\'N/A\' AS threadtitle,
			\'N/A\' AS title,
			usernote.userid AS threadid,
			0 AS forumid,
			entry_usernote.*
		FROM $dbtech_thanks_entry AS entry_usernote
		LEFT JOIN $usernote AS usernote ON (usernote.usernoteid = entry_usernote.contentid)
		WHERE entry_usernote.entryid ' . self::$db->queryList(self::$created['numEntries']['usernote']) . '
	';
}
if (count(self::$created['numEntries']['visitormessage']) AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 16))
{
	$SQL[] = '
		SELECT
			visitormessage.vmid AS postid,
			visitormessage.title AS posttitle,
			\'N/A\' AS threadtitle,
			\'N/A\' AS title,
			visitormessage.userid AS threadid,
			0 AS forumid,
			entry_visitormessage.*
		FROM $dbtech_thanks_entry AS entry_visitormessage
		LEFT JOIN $visitormessage AS visitormessage ON (visitormessage.vmid = entry_visitormessage.contentid)
		WHERE entry_visitormessage.entryid ' . self::$db->queryList(self::$created['numEntries']['visitormessage']) . '
			AND visitormessage.state = \'visible\'
	';
}
?>