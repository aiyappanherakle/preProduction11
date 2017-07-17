<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Core PMB functions for ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/*
* Function to fetch and print the last subject title posted within the messages area
*
* @param        integer     project id
* @param        integer     event id
* @param        integer     to user id
* @param        bool        specifies if we should not bold the subject text?
*
* @return	string      Returns the formatted subject text
*/
function fetch_last_pmb_subject($projectid, $eventid, $toid, $nobold = 0)
{
        global $ilance, $myapi, $phrase;
        
        $id = 0;
        
        $sql = $ilance->db->query("
                SELECT id, subject
                FROM " . DB_PREFIX . "pmb
                WHERE project_id = '" . $projectid . "'
                        AND event_id = '" . $eventid . "'
                ORDER BY id DESC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                $id = $res['id'];
                if (!empty($res['subject']))
                {
                        $html = stripslashes($res['subject']);
                }
                else
                {
                        $html = $phrase['_no_subject'];
                }
        }
        else
        {
                $html = $phrase['_no_subject'];
        }
        
        // is the latest post in this message board new to the user viewing?
        $sql2 = $ilance->db->query("
                SELECT to_status
                FROM " . DB_PREFIX . "pmb_alerts
                WHERE project_id = '" . $projectid . "'
                        AND event_id = '" . $eventid . "'
                        AND id = '" . $id . "'
                        AND to_id = '" . $toid . "'
                ORDER BY id DESC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql2) > 0)
        {
                $res2 = $ilance->db->fetch_array($sql2);
                if ($res2['to_status'] == 'new')
                {
                        $html = '<strong>' . $html . '</strong>';    
                }
        }
        
        return $html;
}

/*
* Function to fetch and print the total number of private message posts within a particular message board
*
* @param        integer     project id
* @param        integer     event id
*
* @return	string      Returns the number of messages posted in the board
*/
function fetch_pmb_posts($projectid = 0, $eventid = 0)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS count
                FROM " . DB_PREFIX . "pmb
                WHERE project_id = '" . intval($projectid) . "'
                        AND event_id = '" . intval($eventid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return $res['count'];
        }
        
        return 0;
}

/*
* Function to fetch and print the total number of private messages posted within a particular message folder
*
* @param        integer     project id
* @param        string      defines what folder we are looking into (received, sent, etc)
*
* @return	string      Returns the number of message boards
*/
function fetch_pmb_count($userid, $dowhat)
{
        global $ilance, $myapi;
        
        if (isset($dowhat) AND !empty($dowhat))
        {
                switch ($dowhat)
                {
                        case 'received':
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "pmb_alerts
                                        WHERE to_id = '" . intval($userid) . "'
                                            AND to_status != 'deleted'
                                            AND to_status != 'archived'
                                        GROUP BY event_id
                                ", 0, null, __FILE__, __LINE__);
                                $html = (int)@$ilance->db->num_rows($sql);
                                break;
                        }                    
                        case 'sent':
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "pmb_alerts
                                        WHERE from_id = '" . intval($userid) . "'
                                            AND from_status != 'deleted'
                                            AND from_status != 'archived'
                                        GROUP BY event_id
                                ", 0, null, __FILE__, __LINE__);
                                $html = (int)@$ilance->db->num_rows($sql);
                                break;
                        }                    
                        case 'archived':
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM
                                        " . DB_PREFIX . "pmb_alerts
                                        WHERE (from_id = '" . intval($userid) . "' AND from_status = 'archived'
                                                OR to_id = '" . intval($userid) . "' AND to_status = 'archived')
                                        GROUP BY event_id
                                ", 0, null, __FILE__, __LINE__);
                                $html = (int)@$ilance->db->num_rows($sql);
                                break;
                        }
                }
        }
        
        return $html;
}

/**
* Function to print out an attachment gauge based on a supplied user id
*
* @param        integer         user id
*
* @return       string          Returns HTML formatted bar of attachment usage
*/
function print_pmb_gauge($userid)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
        $total = fetch_pmb_count(intval($userid), 'received');
        $total += fetch_pmb_count(intval($userid), 'sent');
        $total += fetch_pmb_count(intval($userid), 'archived');
        
        $ilance->subscription = construct_object('api.subscription');
        
        $limit = $ilance->subscription->check_access($userid, 'pmbtotal');
        if (!is_numeric($limit))
        {
             $limit = $total;   
        }
        if ($limit == 0 AND $total == 0)
        {
                $percentage_used = 0;
        }
        else
        {
                $percentage_used = round(($total/$limit)*100);
        }
        $percentage_left = (100 - $percentage_used);
        
        $html = '
        <table width="100%" border="0" align="left" cellpadding="0" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
        <tr> 
            <td width="69%" class="gaugeArea">
                <table width="100%" height="9" align="center" cellpadding="0" cellspacing="0" class="gaugeLayout" dir="' . $ilconfig['template_textdirection'] . '">
                <tr> 
                        <td width="4"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'begin-filled.gif" /></td>
                        <td title="'.round($percentage_left).'% '.$phrase['_left'].'" width="'.$percentage_used.'%" style="background:url('.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'fill.gif); background-repeat:repeat-x; background-position:center"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'fill.gif" alt="'.round($percentage_left).'% '.$phrase['_left'].'" /></td>
                        <td width="'.$percentage_left.'%" style="background:url('.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'empty.gif); background-repeat:repeat-x; background-position:center"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'empty.gif" alt="" /></td>
                        <td width="4"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'end-empty.gif" /></td>
                </tr>
                </table>
            </td>
            <td width="31%" nowrap="nowrap"><div align="center"><strong>'.$percentage_used.'%</strong> '.$phrase['_used'].'</div></td>
        </tr>
        </table>
        <br /><span style="float:left">'.$ilance->language->construct_phrase($phrase['_you_have_x_pmbs_stored_of_a_total_x_allowed'], array($total, $limit)).'</span>';
        
        return $html;
}

/*
* Function to fetch and print the total number of unread private message posts within a particular message board
*
* @param        integer     project id
* @param        integer     event id
* @param        integer     user id
*
* @return	string      Returns the number of unread messages posted in a particular message board
*/
function fetch_unread_pmb_posts($projectid, $eventid, $userid)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "pmb_alerts
                WHERE project_id = '" . intval($projectid) . "'
                        AND to_id = '" . intval($userid) . "'
                        AND event_id = '" . intval($eventid) . "'
                        AND to_status = 'new'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return $ilance->db->num_rows($sql);
        }
        
        return 0;
}

/*
* Function to track and update any messages within a message board posted as read to maintain proper read/unread functionality
*
* @param        integer     private message board id
* @param        integer     user id
*
* @return	void
*/
function update_pmb_tracker($id, $userid)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "pmb_alerts
                WHERE id = '" . intval($id) . "'
                        AND to_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['to_status'] == 'new')
                {
                        // update as active, user read message.
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "pmb_alerts
                                SET to_status = 'active',
                                track_dateread = '" . DATETIME24H . "',
                                track_status = 'read'
                                WHERE id = '" . intval($id) . "'
                                        AND to_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
        }
}

/*
* Function to remove a private message board post
*
* @param        integer     private message board post number id
*
* @return	void
*/
function remove_pmb_post($id)
{
        global $ilance, $myapi;
        
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "pmb_alerts
                WHERE id = '" . intval($id) . "'
        ", 0, null, __FILE__, __LINE__);
        
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "pmb
                WHERE id = '" . intval($id) . "'
        ", 0, null, __FILE__, __LINE__);
}

/*
* Function to fetch the PMB event id based on a supplied project id, from user id and a to user id.
*
* @param        integer      project id
* @param        integer      from user id
* @param        integer      to user id
*
* @return	integer      Returns an event id, if none exists, will return one
*/
function fetch_pmb_eventid($projectid = 0, $fromid = 0, $toid = 0)
{
        global $ilance, $myapi;
        
        /*$sql = $ilance->db->query("
                SELECT event_id
                FROM " . DB_PREFIX . "pmb_alerts
                WHERE project_id = '" . intval($projectid) . "'
                        AND (from_id = '" . intval($fromid) . "' OR to_id = '" . intval($fromid) . "' OR to_id = '" . intval($toid) . "' OR from_id = '" . intval($toid) . "')
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);*/
        
        $sql = $ilance->db->query("
                SELECT event_id
                FROM " . DB_PREFIX . "pmb_alerts
                WHERE project_id = '" . intval($projectid) . "'
                        AND from_id = '" . intval($fromid) . "'
                        AND to_id = '" . intval($toid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return $res['event_id'];
        }
        
        return TIMESTAMPNOW;
}

/*
* Function to compose a private message from one user to the next
*
* @param        integer      to user id
* @param        integer      from user id
* @param        string       message subject
* @param        string       message body
* @param        integer      project id
* @param        integer      pmb event id
* @param        boolean      is admin composing message (default false)
*
* @return	nothing
*/
function compose_private_message($to_id = 0, $from_id = 0, $subject = '', $message = '', $project_id = 0, $event_id = '', $isadmin = 0)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
        if (empty($event_id))
        {
                $event_id = time();
        }
        
        $pmb['message'] = $message;
        $pmb['subject'] = (isset($subject) AND !empty($subject)) ? $subject : $phrase['_no_subject'];
        
        $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "pmb
                (id, project_id, event_id, datetime, message, subject)
                VALUES(
                NULL,
                '" . intval($project_id) . "',
                '" . intval($event_id) . "',
                '" . DATETIME24H . "',
                '" . $ilance->db->escape_string($pmb['message']) . "',
                '" . $ilance->db->escape_string($pmb['subject']) . "')
        ", 0, null, __FILE__, __LINE__);
        $insertid = $ilance->db->insert_id();
        
        $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "pmb_alerts
                (id, event_id, project_id, from_id, to_id, from_status, to_status, isadmin)
                VALUES(
                '" . $insertid . "',
                '" . intval($event_id) . "',
                '" . intval($project_id) . "',
                '" . intval($from_id) . "',
                '" . intval($to_id) . "',
                'active',
                'new',
                '" . $isadmin . "')
        ", 0, null, __FILE__, __LINE__);
        
        // since we're the poster let's update the message to "active"
        $sql_active = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "pmb_alerts
                WHERE event_id = '" . intval($event_id) . "'
                AND id = '" . intval($insertid) . "'
        ", 0, null, __FILE__, __LINE__);					
        while ($res_active = $ilance->db->fetch_array($sql_active))
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "pmb_alerts
                        SET from_status = 'active'
                        WHERE event_id = '" . $res_active['event_id'] . "'
                                AND id = '" . intval($insertid) . "'
                                AND from_id = '" . intval($from_id) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        
        if (isset($isadmin) AND $isadmin)
        {
                $sql_fromid = $ilance->db->query("
                        SELECT username, email
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($from_id) . "'
                        AND isadmin = '1'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_fromid) > 0)
                {
                        // hide/mask admin's username
                        $result_fromid = $ilance->db->fetch_array($sql_fromid);
                        //$result_fromid['username'] = 'Administrator';
                }
        }
        else
        {
                $sql_fromid = $ilance->db->query("
                        SELECT username, email
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($from_id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_fromid) > 0)
                {
                        $result_fromid = $ilance->db->fetch_array($sql_fromid);
                }
        }		
        
        $sql_toid = $ilance->db->query("
                SELECT username, email
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($to_id) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_toid) > 0)
        {
                $result_toid = $ilance->db->fetch_array($sql_toid);
        }
        
        $ilance->email = construct_dm_object('email', $ilance);
        
        $ilance->email->slng = fetch_user_slng(intval($to_id));
        $ilance->email->mail = ($ilconfig['globalfilters_enablepmbspy']) ? array($result_toid['email'], SITE_EMAIL) : $result_toid['email'];
                
        $ilance->email->get('pmb_email_alert');		
        $ilance->email->set(array(
                '{{receiver}}' => $result_toid['username'],
                '{{sender}}' => $result_fromid['username'],
                '{{message}}' => $pmb['message'],
                '{{pmb_insert_id}}' => $insertid,
        ));
        
        $ilance->email->send();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>