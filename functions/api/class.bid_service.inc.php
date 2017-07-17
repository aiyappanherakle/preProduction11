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

if (!class_exists('bid'))
{
	exit;
}

/**
* Function to handle inserting a service reverse auction bid
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class bid_service extends bid
{
	/**
        * Function for inserting a new service bid proposal on a service auction event.
        * If a service provider has already placed a bid on this particular project
        * this function will update that previous bid to the new amount specified.
        * Additionally, if the previous bid was declined this new bid will be inserted
        * vs being updated.
        *
        * @param       integer      bidder id
        * @param       string       bid proposal message
        * @param       integer      low bid notify filter (optional)
        * @param       integer      last hour notify filter (optional)
        * @param       integer      project id
        * @param       integer      project owner id
        * @param       string       bid amount
        * @param       integer      estimated number of days
        * @param       string       bid state status
        * @param       string       bid amount type
        * @param       string       custom argument
        * @param       array        bid field answers
        * @param       string       payment method chosen by the provider during bid
        * @param       boolean      show error messages (disable if you want to call this function via API to hide html error messages; this will then only return true or false) - default true
        */
        function placebid($bidderid = 0, $proposal = '', $lowbidnotify = 0, $lasthournotify = 0, $subscribed = 0, $id = 0, $project_user_id = 0, $bidamount = 0, $estimate_days = 0, $bidstate = '', $bidamounttype = '', $bidcustom = '', $bidfieldanswers = '', $paymethod = '', $showerrormessages = true)
        {
                global $ilance, $myapi, $ilpage, $phrase, $ilconfig, $area_title, $page_title;

                $ilance->subscription = construct_object('api.subscription');
                $ilance->watchlist = construct_object('api.watchlist');
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->bid_fields = construct_object('api.bid_fields');

                if ($ilance->subscription->check_access(intval($bidderid), 'servicebid') == 'no')
                {
                        $area_title = $phrase['_buying_menu_denied_upgrade_subscription'];
                        $page_title = SITE_NAME . ' - ' . $phrase['_buying_menu_denied_upgrade_subscription'];
                        
                        if ($showerrormessages)
                        {
                                print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('servicebid'));
                                exit();
                        }
                        else
                        {
                                return false;
                        }        
                }
                
                $area_title = $phrase['_submitting_bid_proposal'];
                $page_title = SITE_NAME . ' ' . $phrase['_submitting_bid_proposal'];
                
		$currencyid = fetch_auction('currencyid', $id);
		
                $sqlexpiry = $ilance->db->query("
                        SELECT UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, status, bids, project_title
                        FROM " . DB_PREFIX . "projects 
                        WHERE project_id = '" . intval($id) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlexpiry) > 0)
                {
                        $resexpiry = $ilance->db->fetch_array($sqlexpiry, DB_ASSOC);
                        if ($resexpiry['mytime'] < 0 OR $resexpiry['status'] != 'open')
                        {
                                $area_title = $phrase['_this_rfp_has_expired_bidding_is_over'];
                                $page_title = SITE_NAME . ' - ' . $phrase['_this_rfp_has_expired_bidding_is_over'];
                                
                                if ($showerrormessages)
                                {
                                        print_notice($area_title, $phrase['_this_rfp_has_expired_bidding_is_over'], $ilpage['main'], $phrase['_main_menu']);
                                        exit();
                                }
                                else
                                {
                                        return false;
                                }
                        }
                }
                unset($sqlexpiry, $resexpiry);
                
                // #### add project to watchlist if applicable #################
                $ilance->watchlist->insert_item(intval($bidderid), $id, 'auction', 'n/a', $lowbidnotify, 0, $lasthournotify, $subscribed);
                
                if (empty($bidcustom))
                {
                        $bidcustom = '';
                }
    
                if (empty($proposal))
                {
                        $proposal = '';	
                }
    
                // #### determine if listing is realtime auction ###############
                $project_details = fetch_auction('project_details', intval($id));
    
                // #### did we already place a bid on this project? ############
                $sql = $ilance->db->query("
                        SELECT bid_id, bidstatus, bidstate
                        FROM " . DB_PREFIX . "project_bids
                        WHERE user_id = '" . intval($bidderid) . "'
                            AND project_id = '" . intval($id) . "'
                        ORDER BY bid_id DESC
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($res['bidstatus'] == 'declined' OR $res['bidstate'] == 'retracted')
                        {
                                // #### insert bid proposal ####################
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "project_bids
                                        (bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                        VALUES(
                                        NULL,
                                        '" . intval($bidderid) . "',
                                        '" . intval($id) . "',
                                        '" . intval($project_user_id) . "',
                                        '" . $ilance->db->escape_string($proposal) . "',
                                        '" . sprintf("%01.2f", $bidamount) . "',
                                        '" . intval($estimate_days) . "',
                                        '" . DATETIME24H . "',
                                        'placed',
                                        '" . $ilance->db->escape_string($bidstate) . "',
                                        '" . $ilance->db->escape_string($bidamounttype) . "',
                                        '" . $ilance->db->escape_string($bidcustom) . "',
                                        'service',
					'" . $ilance->db->escape_string($paymethod) . "')
                                ", 0, null, __FILE__, __LINE__);
                                $this_bid_id = $ilance->db->insert_id();
                                
                                // insert realtime bid proposal
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                        VALUES(
                                        NULL,
                                        '" . intval($this_bid_id) . "',
                                        '" . intval($bidderid) . "',
                                        '" . intval($id) . "',
                                        '" . intval($project_user_id) . "',
                                        '" . $ilance->db->escape_string($proposal) . "',
                                        '" . sprintf("%01.2f", $bidamount) . "',
                                        '" . intval($estimate_days) . "',
                                        '" . DATETIME24H . "',
                                        'placed',
                                        '" . $ilance->db->escape_string($bidstate) . "',
                                        '" . $ilance->db->escape_string($bidamounttype) . "',
                                        '" . $ilance->db->escape_string($bidcustom) . "',
                                        'service',
					'" . $ilance->db->escape_string($paymethod) . "')
                                ", 0, null, __FILE__, __LINE__);    
                                
                                // update bid count for auction
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET bids = bids + 1
                                        WHERE project_id = '" . intval($id) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                // will increase bidstoday and bidsthismonth for the user placing a bid
                                $this->set_bid_counters(intval($bidderid), 'increase');
                        }
                        else
                        {
                                // update/revise existing bid amount placed
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "project_bids
                                        SET proposal = '" . $ilance->db->escape_string($proposal) . "',
                                        bidamount = '" . sprintf("%01.2f", $bidamount) . "',
                                        estimate_days = '" . intval($estimate_days) . "',
                                        bidamounttype = '" . $ilance->db->escape_string($bidamounttype) . "',
                                        bidcustom = '" . $ilance->db->escape_string($bidcustom) . "',
					winnermarkedaspaidmethod = '" . $ilance->db->escape_string($paymethod) . "'
                                        WHERE bid_id = '" . $res['bid_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                // make sure our realtime applet has some live bid history info
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                        VALUES(
                                        NULL,
                                        '" . $res['bid_id'] . "',
                                        '" . intval($bidderid) . "',
                                        '" . intval($id) . "',
                                        '" . intval($project_user_id) . "',
                                        '" . $ilance->db->escape_string($proposal) . "',
                                        '" . sprintf("%01.2f", $bidamount) . "',
                                        '" . intval($estimate_days) . "',
                                        '" . DATETIME24H . "',
                                        'placed',
                                        '".$ilance->db->escape_string($bidstate) . "',
                                        '".$ilance->db->escape_string($bidamounttype) . "',
                                        '".$ilance->db->escape_string($bidcustom) . "',
                                        'service',
					'" . $ilance->db->escape_string($paymethod) . "')
                                ", 0, null, __FILE__, __LINE__);    
                                
                                $this_bid_id = $res['bid_id'];
                        }
                }
                
		// #### brand new bid proposal #################################
		else
                {               
                        // #### insert bid proposal ############################
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "project_bids
                                (bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                VALUES(
                                NULL,
                                '" . intval($bidderid) . "',
                                '" . intval($id) . "',
                                '" . intval($project_user_id) . "',
                                '" . $ilance->db->escape_string($proposal) . "',
                                '" . sprintf("%01.2f", $bidamount) . "',
                                '" . intval($estimate_days) . "',
                                '" . DATETIME24H . "',
                                'placed',
                                '" . $ilance->db->escape_string($bidstate) . "',
                                '" . $ilance->db->escape_string($bidamounttype) . "',
                                '" . $ilance->db->escape_string($bidcustom) . "',
                                'service',
				'" . $ilance->db->escape_string($paymethod) . "')
                        ", 0, null, __FILE__, __LINE__);
                        $this_bid_id = $ilance->db->insert_id();
                        
                        // #### make sure our realtime applet has some live bid history info
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "project_realtimebids
                                (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                VALUES(
                                NULL,
                                '" . $this_bid_id . "',
                                '" . intval($bidderid) . "',
                                '" . intval($id) . "',
                                '" . intval($project_user_id) . "',
                                '" . $ilance->db->escape_string($proposal) . "',
                                '" . sprintf("%01.2f", $bidamount) . "',
                                '" . intval($estimate_days) . "',
                                '" . DATETIME24H . "',
                                'placed',
                                '" . $ilance->db->escape_string($bidstate) . "',
                                '" . $ilance->db->escape_string($bidamounttype) . "',
                                '" . $ilance->db->escape_string($bidcustom) . "',
                                'service',
				'" . $ilance->db->escape_string($paymethod) . "')
                        ", 0, null, __FILE__, __LINE__);    
                        
                        // #### update bid count for auction
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET bids = bids + 1
                                WHERE project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        // will increase bidstoday and bidsthismonth
                        $this->set_bid_counters(intval($bidderid), 'increase');
                        
                        // was this service provider invited?
                        $sql_invites = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "project_invitations
                                WHERE project_id = '" . intval($id) . "'
                                    AND seller_user_id = '" . intval($bidderid) . "'
                                    AND bid_placed = 'no'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_invites) > 0)
                        {
                                // update invitations with bid placed for invited service provider
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "project_invitations
                                        SET bid_placed = 'yes',
                                        date_of_bid = '" . DATETIME24H . "'
                                        WHERE seller_user_id = '" . intval($bidderid) . "'
                                            AND project_id = '" . intval($id) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                
                                $url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($id);
                                
                                // email user
                                $ilance->email->mail = fetch_user('email', $project_user_id);
                                $ilance->email->slng = fetch_user_slng($project_user_id);
                                $ilance->email->get('invited_bid_placed_buyer');		
                                $ilance->email->set(array(
                                        '{{buyer}}' => fetch_user('username', $project_user_id),
                                        '{{vendor}}' => fetch_user('username', $bidderid),
                                        '{{rfp_title}}' => fetch_auction('project_title', intval($id)),
                                        '{{project_id}}' => intval($id),
                                        '{{url}}' => $url,
                                ));                                
                                $ilance->email->send();
                        }
                }
    
                // #### capture custom bid fields ######################
                $ilance->bid_fields->process_custom_bid_fields($bidfieldanswers, intval($id), $this_bid_id);
                
                // #### lower bid notification bulk email sender #######
                $ilance->watchlist->send_notification(intval($bidderid), 'lowbidnotify', intval($id), $bidamount);
    
                $ilance->email->mail = fetch_user('email', $project_user_id);
                $ilance->email->slng = fetch_user_slng($project_user_id);
                $ilance->email->get('bid_notification_alert');		
                $ilance->email->set(array(
                        '{{provider}}' => fetch_user('username', $bidderid),
                        '{{price}}' => $ilance->currency->format($bidamount, $currencyid),
                        '{{p_id}}' => intval($id),
			'{{project_title}}' => strip_tags($resexpiry['project_title']),
			'{{bids}}' => $resexpiry['bids'],
                ));
                $ilance->email->send();
                
                if ($showerrormessages)
                {
                        // todo: detect seo
                        refresh($ilpage['rfp'] . '?id=' . intval($id));
                        exit();
                }
		
                return true;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>