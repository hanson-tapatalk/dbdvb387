<?php
if(!defined('IN_MOBIQUO'))
{
    define('CWD1',dirname(__FILE__));
    $commonPath = dirname(__FILE__) . '/include/common.php';
    include_once( $commonPath );
}
require_once(DIR.'/'.$vbulletin->options['tapatalk_directory'].'/include/function_push.php');

function tapatalk_push_pm($pm){
    global $vbulletin;

    $ttp_id = ($pm->existing['pmtextid'] ? $pm->existing['pmtextid'] : $pm->pmtext['pmtextid']);
    $ttp_title = fetch_censored_text(trim(fetch_trimmed_title(strip_bbcode($pm->pmtext['title'], true, false, false, true), 100)));

    $push_data = array(
        'url'       => $vbulletin->options['bburl'],
        'type'      => 'pm',
        'id'        => 'textid_' . $ttp_id,
        'title'     => tt_hook_encode($ttp_title),
        'author'    => $pm->pmtext['fromusername'],
        'authorid'  => $pm->pmtext['fromuserid'],
        'dateline'  => $pm->pmtext['dateline'],
    );
    $ttp_touser = '';
    if (is_array($pm->info['recipients'])){
        foreach ($pm->info['recipients'] as $user_id=> $user){
            if ($user_id == $vbulletin->userinfo['userid']) continue;
            if (!is_tapatalk_user($user_id)) continue;
            $ttp_touser = empty($ttp_touser) ? $user_id : $ttp_touser . ',' .$user_id;
            $push_data['userid'] = $user_id;
            tapatalk_push_log($push_data);
        }
    }
    $push_data['userid'] = $ttp_touser;
    if(isset($vbulletin->options['push_key']) && !empty($vbulletin->options['push_key']))
    {
        $push_data['key'] = $vbulletin->options['push_key'];
    }
    if(isset($vbulletin->options['push_notifications']) && $vbulletin->options['push_notifications']){
        $push_data['content'] = parse_content($vbulletin->GPC['message'],true,$vbulletin->GPC['forumid']);
    }
    $return_status = do_post_request($push_data);
}

function tapatalk_push_reply($type, $post, $threadinfo){
    global $vbulletin;

    if (!$post['visible'] OR in_coventry($vbulletin->userinfo['userid'], true) OR !$post['postid'] OR !$threadinfo['threadid']){
        return false;
    }
    $threadid = $threadinfo['threadid'];
    $push_title = fetch_censored_text(trim(fetch_trimmed_title(strip_bbcode(unhtmlspecialchars($threadinfo['title']), true, false, false, true), 100)));

    // get last reply time
    $dateline = $vbulletin->db->query_first("
                SELECT dateline
                FROM " . TABLE_PREFIX . "post
                WHERE postid = {$post['postid']}
            ");

    $push_data = array(
        'url'  => $vbulletin->options['bburl'],
        'id'        => $threadinfo['threadid'],
        'subid'     => $post['postid'],
        'subfid'    => $vbulletin->GPC['forumid'],
        'title'     => tt_hook_encode($push_title),
        'author'    => tt_hook_encode($vbulletin->userinfo['username']),
        'authorid'  => $vbulletin->userinfo['userid'],
        'dateline'  => $dateline['dateline'],
    );
    if(isset($vbulletin->options['push_key']) && !empty($vbulletin->options['push_key'])){
        $push_data['key'] = $vbulletin->options['push_key'];
    }
    if(isset($vbulletin->options['push_notifications']) && $vbulletin->options['push_notifications']){
        $push_data['content'] = parse_content($vbulletin->GPC['message'],true,$vbulletin->GPC['forumid']);
    }
    $pushed_users = array();

    if($type == 'thread'){
        //Add new_topic push data
        handle_newtopic_push($post, $threadinfo, $push_data, $pushed_users);
    }else{
        //Add sub push data
        handle_sub_push($post, $threadinfo, $push_data, $pushed_users);
    }

    //Add quote push data
    handle_quote_push($post, $threadinfo, $push_data, $pushed_users);

    //Add @/Tag push data
    handle_tag_push($post, $threadinfo, $push_data, $pushed_users);
}
function handle_sub_push($post, $threadinfo, $push_data, &$pushed_users = array()){
    global $vbulletin;
    $push_data['type'] = 'sub';
    $userid = $vbulletin->userinfo['userid'];
    $useremails = $vbulletin->db->query_read_slave("
        SELECT user.*, subscribethread.emailupdate, subscribethread.subscribethreadid
        FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
        INNER JOIN " . TABLE_PREFIX . "user AS user ON (subscribethread.userid = user.userid)
        INNER JOIN " . TABLE_PREFIX . "tapatalk_users AS tt_user ON (subscribethread.userid = tt_user.userid)
        LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON (usergroup.usergroupid = user.usergroupid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid)
        WHERE subscribethread.threadid = {$threadinfo['threadid']} AND
            subscribethread.canview = 1 AND
            " . ($userid ? "CONCAT(' ', IF(usertextfield.ignorelist IS NULL, '', usertextfield.ignorelist), ' ') NOT LIKE '% " . intval($userid) . " %' AND" : '') . "
            user.usergroupid <> 3 AND
            user.userid <> " . intval($userid) . " AND
            (usergroup.genericoptions & " . $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'] . ")
    ");
    $users = '';
    while ($touser = $vbulletin->db->fetch_array($useremails))
    {
        if (!($vbulletin->usergroupcache[$touser["usergroupid"]]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'])) continue;
        if($vbulletin->userinfo['userid'] == $touser['userid']) continue; // Don't send to author himself.
        if(in_array($touser['userid'], $pushed_users)) continue;
        $users = empty($users) ?  $touser['userid'] : $users . ',' . $touser['userid'];
        $pushed_users[] = $touser['userid'];
        $push_data['userid'] = $touser['userid'];
//        tapatalk_push_log($push_data);
    }
    $push_data['userid'] = $users;
    $return_status = do_post_request($push_data);
}

function handle_newtopic_push($post, $threadinfo, $push_data, &$pushed_users = array()){
    global $vbulletin;
    $push_data['type'] = 'newtopic';
    if(!isset($threadinfo['forumid']) || empty($threadinfo['forumid'])){
        return false;
    }
    $results = $vbulletin->db->query_read_slave("
        SELECT subscribe.userid
        FROM " . TABLE_PREFIX . "subscribeforum AS subscribe
        INNER JOIN " . TABLE_PREFIX . "forum AS forum ON (subscribe.forumid = forum.forumid)
        INNER JOIN " . TABLE_PREFIX . "tapatalk_users AS tt_user ON (subscribe.userid = tt_user.userid)
        WHERE subscribe.forumid = " . $threadinfo['forumid']
    );
    $users = '';
    while ($row = $vbulletin->db->fetch_array($results)) {
        if($vbulletin->userinfo['userid'] == $row['userid']) continue; // Don't send to author himself.
        if(in_array($row['userid'], $pushed_users)) continue;
        $users = empty($users) ?  $row['userid'] : $users . ',' . $row['userid'];
        $pushed_users[] = $row['userid'];
        $push_data['userid'] = $row['userid'];
//        tapatalk_push_log($push_data);
    }
    $push_data['userid'] = $users;
    $return_status = do_post_request($push_data);
}

function handle_quote_push($post, $threadinfo, $push_data, &$pushed_users = array()){
    global $vbulletin;
    $push_data['type'] = 'quote';
    if(!preg_match_all('/\[quote=(.*?);(\d+)\]/si', $post['message'], $quote_matches)) return false;

    $quote_postids = $quote_matches[2];
    $quote_post_data = $vbulletin->db->query_read_slave("
        SELECT post.postid, post.title, post.pagetext, post.dateline, post.userid, post.visible AS postvisible,
            IF(user.username <> '', user.username, post.username) AS username,
            thread.threadid, thread.title AS threadtitle, thread.postuserid, thread.visible AS threadvisible,
            forum.forumid, forum.password
            $hook_query_fields
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
        INNER JOIN " . TABLE_PREFIX . "tapatalk_users AS tt_user ON (post.userid = tt_user.userid)
        INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
        INNER JOIN " . TABLE_PREFIX . "forum AS forum ON (thread.forumid = forum.forumid)
        $hook_query_joins
        WHERE post.postid IN (" . implode(',', $quote_postids) . ")
    ");
    while ($quote_post = $vbulletin->db->fetch_array($quote_post_data))
    {
        if (
        ((!$quote_post['postvisible'] OR $quote_post['postvisible'] == 2) AND !can_moderate($quote_post['forumid'])) OR
        ((!$quote_post['threadvisible'] OR $quote_post['threadvisible'] == 2) AND !can_moderate($quote_post['forumid']))
        )
        {
           // no permission to view this post
           continue;
        }

        $forumperms = fetch_permissions($quote_post['forumid']);
        if (
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])) OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND ($quote_post['postuserid'] != $vbulletin->userinfo['userid'] OR $vbulletin->userinfo['userid'] == 0)) OR
            !verify_forum_password($quote_post['forumid'], $quote_post['password'], false) OR
            (in_coventry($quote_post['postuserid']) AND !can_moderate($quote_post['forumid'])) OR
            (in_coventry($quote_post['userid']) AND !can_moderate($quote_post['forumid']))
        )
        {
            // no permission to view this post
            continue;
        }

        if (($limit_thread == 'only' AND $quote_post['threadid'] != $threadid) OR
        ($limit_thread == 'other' AND $quote_post['threadid'] == $threadid) OR $limit_thread == 'all')
        {
            $unquoted_posts++;
            continue;
        }

        $skip_post = false;
        ($hook = vBulletinHook::fetch_hook('quotable_posts_logic')) ? eval($hook) : false;

        if ($skip_post)
        {
            continue;
        }

        $quote_posts["$quote_post[postid]"] = $quote_post;
        $quote_status_users[$quote_post['userid']] = 1;
    }

    $users = '';
    foreach($quote_posts as $post_id => $quote_post)
    {
        //check if the quoted users has permission to view this forum
        $userinfo = fetch_userinfo($quote_post['userid']);
        $forumperms = fetch_permissions($threadinfo['forumid'], $quote_post['userid'], $userinfo);
        if (
        (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])) OR
        (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND ($quote_post['postuserid'] != $vbulletin->userinfo['userid'] OR $vbulletin->userinfo['userid'] == 0)) OR
        !verify_forum_password($quote_post['forumid'], $quote_post['password'], false) OR
        (in_coventry($quote_post['postuserid']) AND !can_moderate($quote_post['forumid'])) OR
        (in_coventry($quote_post['userid']) AND !can_moderate($quote_post['forumid']))
        )
        {
            // no permission to view this post
            continue;
        }
        $quotedUsers[] = $quote_post['userid'];
        if($vbulletin->userinfo['userid'] == $quote_post['userid']) continue; // Don't send to author himself.
        if(in_array($quote_post['userid'], $pushed_users)) continue;
        $users = empty($users) ?  $quote_post['userid'] : $users . ',' . $quote_post['userid'];
        $pushed_users[] = $quote_post['userid'];
        $push_data['userid'] = $quote_post['userid'];
//        tapatalk_push_log($push_data);
    }
    $push_data['userid'] = $users;
    if (!empty($users)){
        $return_status = do_post_request($push_data);
    }
}

function handle_tag_push($post, $threadinfo, $push_data, &$pushed_users = array()){
    global $vbulletin;
    if ( !preg_match_all( '/(?<=^@|\s@)(#(.{1,50})#|\S{1,50}(?=[,\.;!\?]|\s|$))/U', $post['message'], $tags ) ) return false;
    $push_data['type'] = 'tag';
    foreach ($tags[2] as $index => $tag)
    {
        if ($tag) $tags[1][$index] = $tag;
    }
    $tagged_usernames =  array_unique($tags[1]);
    if(!empty($tagged_usernames))
    {
        foreach($tagged_usernames as $index => $tagged_username)
        $tagged_usernames[$index] = $vbulletin->db->escape_string($tagged_username);
    }
    $tagged_users  = array();
    //initial query conditions
    global $vbphrase;
    $option = 0;
    $languageid = 0;

    $query_text = "
        SELECT " . iif(($option & FETCH_USERINFO_ADMIN), ' administrator.*, ') . "
        user.*, UNIX_TIMESTAMP(passworddate) AS passworddate, user.languageid AS saved_languageid,
        IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid" .
        iif(($option & FETCH_USERINFO_AVATAR) AND $vbulletin->options['avatarenabled'], ', avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, customavatar.width AS avwidth, customavatar.height AS avheight, customavatar.height_thumb AS avheight_thumb, customavatar.width_thumb AS avwidth_thumb, customavatar.filedata_thumb').
        iif(($option & FETCH_USERINFO_PROFILEPIC), ', customprofilepic.userid AS profilepic, customprofilepic.dateline AS profilepicdateline, customprofilepic.width AS ppwidth, customprofilepic.height AS ppheight') .
        iif(($option & FETCH_USERINFO_SIGPIC), ', sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline, sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight') .
        (($option & FETCH_USERINFO_USERCSS) ? ', usercsscache.cachedcss, IF(usercsscache.cachedcss IS NULL, 0, 1) AS hascachedcss, usercsscache.buildpermissions AS cssbuildpermissions' : '') .
        (isset($vbphrase) ? '' : fetch_language_fields_sql()) .
        (($vbulletin->userinfo['userid'] AND ($option & FETCH_USERINFO_ISFRIEND)) ?
        ", IF(userlist1.friend = 'yes', 1, 0) AS isfriend, IF (userlist1.friend = 'pending' OR userlist1.friend = 'denied', 1, 0) AS ispendingfriend" .
        ", IF(userlist1.userid IS NOT NULL, 1, 0) AS u_iscontact_of_bbuser, IF (userlist2.friend = 'pending', 1, 0) AS requestedfriend" .
        ", IF(userlist2.userid IS NOT NULL, 1, 0) AS bbuser_iscontact_of_user" : "") . "
        $hook_query_fields
        FROM " . TABLE_PREFIX . "user AS user
        LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON (user.userid = userfield.userid)
        INNER JOIN " . TABLE_PREFIX . "tapatalk_users AS tt_user ON (user.userid = tt_user.userid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (usertextfield.userid = user.userid) " .
            iif(($option & FETCH_USERINFO_AVATAR) AND $vbulletin->options['avatarenabled'], "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON (avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON (customavatar.userid = user.userid) ") .
            iif(($option & FETCH_USERINFO_PROFILEPIC), "LEFT JOIN " . TABLE_PREFIX . "customprofilepic AS customprofilepic ON (user.userid = customprofilepic.userid) ") .
            iif(($option & FETCH_USERINFO_ADMIN), "LEFT JOIN " . TABLE_PREFIX . "administrator AS administrator ON (administrator.userid = user.userid) ") .
            iif(($option & FETCH_USERINFO_SIGPIC), "LEFT JOIN " . TABLE_PREFIX . "sigpic AS sigpic ON (user.userid = sigpic.userid) ") .
            (($option & FETCH_USERINFO_USERCSS) ? 'LEFT JOIN ' . TABLE_PREFIX . 'usercsscache AS usercsscache ON (user.userid = usercsscache.userid)' : '') .
            iif(!isset($vbphrase), "LEFT JOIN " . TABLE_PREFIX . "language AS language ON (language.languageid = " . (!empty($languageid) ? $languageid : "IF(user.languageid = 0, " . intval($vbulletin->options['languageid']) . ", user.languageid)") . ") ") .
            (($vbulletin->userinfo['userid'] AND ($option & FETCH_USERINFO_ISFRIEND)) ?
        "LEFT JOIN " . TABLE_PREFIX . "userlist AS userlist1 ON (userlist1.relationid = user.userid AND userlist1.type = 'buddy' AND userlist1.userid = " . $vbulletin->userinfo['userid'] . ")" .
        "LEFT JOIN " . TABLE_PREFIX . "userlist AS userlist2 ON (userlist2.userid = user.userid AND userlist2.type = 'buddy' AND userlist2.relationid = " . $vbulletin->userinfo['userid'] . ")" : "") . "
        WHERE user.username IN ('" . implode('\',\'', $tagged_usernames) . "')
    ";

    $users = '';
    $results = $vbulletin->db->query_read_slave($query_text);
    while ($touser = $vbulletin->db->fetch_array($results))
    {
        if($vbulletin->userinfo['userid'] == $touser['userid']) continue; // Don't send to author himself.
        if(in_array($touser['userid'], $pushed_users)) continue;
        $users = empty($users) ?  $touser['userid'] : $users . ',' . $touser['userid'];
        $pushed_users[] = $touser['userid'];
        $push_data['userid'] = $touser['userid'];
//        tapatalk_push_log($push_data);
    }
    $push_data['userid'] = $users;
    if (!empty($users)){
        $return_status = do_post_request($push_data);
    }
}

function is_tapatalk_user($user_id){
    global $vbulletin;
    $result = $vbulletin->db->query_first("SELECT userid FROM " . TABLE_PREFIX . "tapatalk_users WHERE userid='$user_id'");
    return !empty($result);
}

function tapatalk_push_log($push_data){
    global $vbulletin;
    $result = $vbulletin->db->query_write("
        INSERT INTO " . TABLE_PREFIX . "tapatalk_push
            (userid, type, id, title, author, dateline)
        VALUES
            ('{$push_data['userid']}', '{$push_data['type']}', '{$push_data['id']}', '{$push_data['title']}', '{$push_data['author']}', '{$push_data['dateline']}')"
    );
}
