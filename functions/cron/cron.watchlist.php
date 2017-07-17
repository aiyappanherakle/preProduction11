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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

require_once(DIR_CORE . 'functions_search.php');

// #### LAST HOUR AUCTION WATCHLIST NOTIFICATIONS ##############################
$cronlog = '';
$watchlist = $ilance->db->query("
	SELECT user_id, watching_project_id
        FROM " . DB_PREFIX . "watchlist
	WHERE hourleftnotify = '1'
	    AND state = 'auction'
	    AND subscribed = '1'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($watchlist) > 0)
{
	$ilance->email = construct_dm_object('email', $ilance);
	$ilance->auction = construct_object('api.auction');
	
        $sent = 0;
        while ($watching = $ilance->db->fetch_array($watchlist, DB_ASSOC))
        {
                // select this project and see if it has less than 1 hour left
                $sql = $ilance->db->query("
                        SELECT project_id, project_title, bids
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . $watching['watching_project_id'] . "'
                            AND status = 'open'
                            AND visible = '1'
                        " . fetch_startend_sql('1', 'DATE_ADD', 'date_end', '<=') . "
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                // this project for this user has left than 1 hour
                                // we need to check to see if we've already sent this emaillog
                                // for this project for this user
                                $emailcheck = $ilance->db->query("
                                        SELECT emaillogid
                                        FROM " . DB_PREFIX . "emaillog
                                        WHERE logtype = 'watchlist'
                                            AND user_id = '" . $watching['user_id'] . "'
                                            AND project_id = '" . $watching['watching_project_id'] . "'
                                            AND sent = 'yes'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($emailcheck) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "emaillog
                                                (emaillogid, logtype, user_id, project_id, date, sent)
                                                VALUES(
                                                NULL,
                                                'watchlist',
                                                '" . $watching['user_id'] . "',
                                                '" . $watching['watching_project_id'] . "',
                                                '" . DATETODAY . "',
                                                'yes')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $ilance->email->mail = fetch_user('email', $watching['user_id']);
                                        $ilance->email->slng = fetch_user_slng($watching['user_id']);
                                        $ilance->email->get('cron_last_hour_auction_notification');		
                                        $ilance->email->set(array(
                                                '{{project_id}}' => $watching['watching_project_id'],
                                                '{{project_title}}' => stripslashes($res['project_title']),
                                                '{{bids}}' => (int)$res['bids'],
                                                '{{timeleft}}' => $ilance->auction->auction_timeleft($res['project_id'], '', '', 0, 0, 1),
                                        ));
                                        $ilance->email->send();
                                        
                                        $sent++;
                                }
                        }
                }
        }
	
        $cronlog .= 'Sent last hour auction notifications via email to ' . $sent . ' members';
}

log_cron_action('The following watchlist tasks were executed: ' . $cronlog, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>