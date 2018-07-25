<?php
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/
defined('CWD1') or exit;
defined('IN_MOBIQUO') or exit;

define('THIS_SCRIPT', 'subscription');
define('CSRF_PROTECTION', false);
// get special phrase groups
$phrasegroups = array('user', 'forumdisplay');

// get special data templates from the datastore
$specialtemplates = array(
    'iconcache',
    'noavatarperms'
);

// pre-cache templates used by all actions
$globaltemplates = array(
    'USERCP_SHELL',
    'usercp_nav_folderbit',
);

// pre-cache templates used by specific actions
$actiontemplates = array(
    'viewsubscription' => array(
        'forumdisplay_sortarrow',
        'threadbit',
        'SUBSCRIBE'
        ),
    'addsubscription' => array(
        'subscribe_choosetype'
        ),
    'editfolders' => array(
        'subscribe_folderbit',
        'subscribe_showfolders'
        ),
    'dostuff' => array(
        'subscribe_move'
        )
);

$actiontemplates['none'] =& $actiontemplates['viewsubscription'];

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_user.php');

function subscribe_forum_func($xmlrpc_params)
{
    global $vbulletin,$permissions,$db;
    global $vbphrase, $folderid, $folderselect, $foldernames, $messagecounters, $subscribecounters, $folder;

    $params = php_xmlrpc_decode($xmlrpc_params);

    $forumid = $params[0];
    $vbulletin->GPC['emailupdate'] = 0;
    $vbulletin->GPC['folderid'] = 0;
    $threadinfo = array();
    $foruminfo = array();

    $vbulletin->GPC['forumid'] = $forumid ;
    if ($forumid)
    {
        $foruminfo = mobiquo_verify_id('forum', $vbulletin->GPC['forumid'], 0, 1);
        $forumid =& $foruminfo['forumid'];
        if (($foruminfo['styleoverride'] == 1 OR $vbulletin->userinfo['styleid'] == 0) AND !defined('BYPASS_STYLE_OVERRIDE'))
        {
            $codestyleid = $foruminfo['styleid'];
        }
    }

    if ((!$vbulletin->userinfo['userid'] AND $_REQUEST['do'] != 'removesubscription') OR ($vbulletin->userinfo['userid'] AND !($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview'])) OR $userinfo['usergroupid'] == 3 OR $vbulletin->userinfo['usergroupid'] == 4 OR !($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
    {
        $return = array(20,'security error (user may not have permission to access this feature)');
        return return_fault($return);
    }

    if (!$foruminfo['forumid'])
    {
        $return = array(6, 'Invalid forum id');
        return return_fault($return);
    }

    $forumperms = fetch_permissions($foruminfo['forumid']);
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
    {
        $return = array(20,'security error (user may not have permission to access this feature)');
        return return_fault($return);
    }

    if (!$foruminfo['allowposting'] OR $foruminfo['link'] OR !$foruminfo['cancontainthreads'])
    {
        $return = array(20,'security error (user may not have permission to access this feature)');
        return return_fault($return);
    }

    // check if there is a forum password and if so, ensure the user has it set
    //verify_forum_password($foruminfo['forumid'], $foruminfo['password']);

    verify_forum_password($foruminfo['forumid'], $foruminfo['password']);

    $db->query_write("
        REPLACE INTO " . TABLE_PREFIX . "subscribeforum (userid, emailupdate, forumid)
        VALUES (" . $vbulletin->userinfo['userid'] . ", " . $vbulletin->GPC['emailupdate'] . ", " . $vbulletin->GPC['forumid'] . ")
    ");

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result'      => new xmlrpcval(true, 'boolean'),
        'result_text' => new xmlrpcval('', 'base64')
    ), 'struct'));
}
