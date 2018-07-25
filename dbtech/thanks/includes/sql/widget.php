<?php
if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 64))
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
		FROM (SELECT * FROM $dbtech_thanks_entry WHERE varname :whereCond AND contenttype = \'post\' ORDER BY entryid DESC LIMIT :limit) AS entry_post
		LEFT JOIN $post AS post ON (post.postid = entry_post.contentid)
		LEFT JOIN $thread AS thread ON (thread.threadid = post.threadid)
		WHERE post.visible = 1
	';
}



if ((bool)self::$vbulletin->products['dbtech_gallery'] AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 2))
{
	$SQL[] = '
		SELECT
			image.imageid AS postid,
			image.title_clean AS posttitle,
			image.filename AS threadtitle,		
			image.userid,
			image_instance.shortname AS threadid,
			0 AS forumid,
			entry_dbgallery_image.*
		FROM (SELECT * FROM $dbtech_thanks_entry WHERE varname :whereCond AND contenttype = \'dbgallery_image\' ORDER BY entryid DESC LIMIT :limit) AS entry_dbgallery_image
		LEFT JOIN $dbtech_gallery_images AS image ON (image.imageid = entry_dbgallery_image.contentid)
		LEFT JOIN $dbtech_gallery_instances AS image_instance ON (image_instance.instanceid = image.instanceid)		
	';
}

if ((bool)self::$vbulletin->products['dbtech_review'] AND !(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 32))
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
		FROM (SELECT * FROM $dbtech_thanks_entry WHERE varname :whereCond AND contenttype = \'dbreview_review\' ORDER BY entryid DESC LIMIT :limit) AS entry_dbreview_review
		LEFT JOIN $dbtech_review_reviews AS review ON (review.reviewid = entry_dbreview_review.contentid)
		LEFT JOIN $dbtech_review_instances AS review_instance ON (review_instance.instanceid = review.instanceid)		
	';
}

if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 4))
{
	$SQL[] = '
		SELECT
			groupmessage.gmid AS postid,
			groupmessage.title AS posttitle,
			firstgroupmessage.title AS threadtitle,		
			groupmessage.postuserid AS userid,
			groupmessage.discussionid AS threadid,
			0 AS forumid,
			entry_socialgroup.*
		FROM (SELECT * FROM $dbtech_thanks_entry WHERE varname :whereCond AND contenttype = \'socialgroup\' ORDER BY entryid DESC LIMIT :limit) AS entry_socialgroup
		LEFT JOIN $groupmessage AS groupmessage ON (groupmessage.gmid = entry_socialgroup.contentid)
		LEFT JOIN $discussion AS discussion ON (discussion.discussionid = groupmessage.discussionid)		
		LEFT JOIN $groupmessage AS firstgroupmessage ON (firstgroupmessage.gmid = discussion.firstpostid)
		WHERE groupmessage.state = \'visible\'
	';
}
if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 8))
{
	$SQL[] = '
		SELECT
			usernote.usernoteid AS postid,
			usernote.title AS posttitle,
			\'N/A\' AS threadtitle,		
			usernote.posterid AS userid,
			usernote.userid AS threadid,
			0 AS forumid,
			entry_usernote.*
		FROM (SELECT * FROM $dbtech_thanks_entry WHERE varname :whereCond AND contenttype = \'usernote\' ORDER BY entryid DESC LIMIT :limit) AS entry_usernote
		LEFT JOIN $usernote AS usernote ON (usernote.usernoteid = entry_usernote.contentid)
	';
}
if (!(self::$vbulletin->options['dbtech_thanks_disabledintegration'] & 16))
{
	$SQL[] = '
		SELECT
			visitormessage.vmid AS postid,
			visitormessage.title AS posttitle,
			\'N/A\' AS threadtitle,		
			visitormessage.postuserid AS userid,
			visitormessage.userid AS threadid,
			0 AS forumid,
			entry_visitormessage.*
		FROM (SELECT * FROM $dbtech_thanks_entry WHERE varname :whereCond AND contenttype = \'visitormessage\' ORDER BY entryid DESC LIMIT :limit) AS entry_visitormessage
		LEFT JOIN $visitormessage AS visitormessage ON (visitormessage.vmid = entry_visitormessage.contentid)
		WHERE visitormessage.state = \'visible\'
	';
}
?>