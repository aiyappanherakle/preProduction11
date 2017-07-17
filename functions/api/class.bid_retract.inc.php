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
* Retract Bid class to perform the majority of bid retraction functions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class bid_retract extends bid
{
        /**
        * Function for retracting all bids for a user due to an admin user "deleting" a member from the AdminCP.
        *
        * @param       integer      user id
        */
        function retract_all_bids($userid = 0)
        {
                global $ilance, $ilconfig, $ilpage;
        }
        
        /**
        * Function for creating a new bid retract on a particular auction event.  In the case where a bidder has placed more than a single bid,
        * all bid(s) for that user for the particular auction will be retracted.
        *
        * @param       integer      user id
        * @param       integer      bid id
        * @param       integer      project id
        * @param       string       reason
        * @param       boolean      is bid awarded? (default false)
        * @param       boolean      run in silent mode (no template notice) (default false)
        */
        function construct_bid_retraction($userid = 0, $bidid = 0, $projectid = 0, $reason = '', $awarded = false, $silentmode = false)
        {
                global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $navcrumb;
                
                // #### load our subscription backend ##########################
                $ilance->subscription = construct_object('api.subscription');
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->bid = construct_object('api.bid');
                
                // #### setup our defaults for the user ########################
                $totalretracts = $ilance->subscription->check_access($userid, 'bidretracts');
                $project_state = fetch_auction('project_state', intval($projectid));
                $filter_escrow = fetch_auction('filter_escrow', intval($projectid));
                $canretract = ($project_state == 'product') ? $ilconfig['productbid_bidretract'] : $ilconfig['servicebid_bidretract'];
                $canretractaward = ($project_state == 'product') ? $ilconfig['productbid_awardbidretract'] : $ilconfig['servicebid_awardbidretract'];
                
                $sql = $ilance->db->query("
                        SELECT bidretracts
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        // #### can we retract our bid? ########################
                        if ($totalretracts > 0 AND $res['bidretracts'] < $totalretracts)
                        {
                                ($apihook = $ilance->api('construct_bid_retraction_good_permissions_start')) ? eval($apihook) : false;
                                
                                // #### awarded already ########################
                                if ($awarded)
                                {
                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_start')) ? eval($apihook) : false;

                                        if ($canretract AND $canretractaward)
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_awarded_can_retract_start')) ? eval($apihook) : false;
                                                
                                                // is escrow enabled and does owner use escrow?
                                                if ($ilconfig['escrowsystem_enabled'] AND $filter_escrow == '1')
                                                {
                                                        // remove pending escrow account for the auction
                                                        $ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "projects_escrow
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                    AND user_id = '" . intval($userid) . "'
                                                                    AND status = 'pending'
                                                        ", 0, null, __FILE__, __LINE__);
                                        
                                                        // remove related unpaid escrow transaction invoice
                                                        $ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "invoices
                                                                WHERE projectid = '" . intval($projectid) . "'
                                                                    AND invoicetype = 'escrow'
                                                                    AND status = 'unpaid'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                
                                                // determine if a bid retract for this user for this listing already exists
                                                $sqlcheck = $ilance->db->query("
                                                        SELECT retractid
                                                        FROM " . DB_PREFIX . "project_bid_retracts
                                                        WHERE user_id = '" . intval($userid) . "'
                                                                AND project_id = '" . intval($projectid) . "'
                                                                AND bid_id = '" . intval($bidid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlcheck) == 0)
                                                {
                                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_do_retraction_start')) ? eval($apihook) : false;
                                                        
                                                        // #### insert new bid retraction
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "project_bid_retracts
                                                                (retractid, user_id, bid_id, project_id, reason, date)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($userid) . "',
                                                                '" . intval($bidid) . "',
                                                                '" . intval($projectid) . "',
                                                                '" . $ilance->db->escape_string($reason) . "',
                                                                '" . DATETODAY . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                        
                                                        // #### retract the bid
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                SET bidstate = 'retracted'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                    AND user_id = '" . intval($userid) . "'
                                                                    AND bid_id = '" . intval($bidid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### retract realtime bids for flash and other applets to act properly
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_realtimebids
                                                                SET bidstate = 'retracted'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                                     AND bid_id = '" . intval($bidid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### delete any proxy bids placed
                                                        $ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "proxybid
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### update total retract count for bidder
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET bidretracts = bidretracts + 1
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // winning bidder retracting bid no 2nd highest bidder logic available
                                                        $newcurrentprice = '0.00';
                                                        
                                                        // #### update listing details
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET bids = bids - 1,
                                                                bidsretracted = bidsretracted + 1,
                                                                haswinner = '0',
                                                                winner_user_id = '0',
                                                                buyerfeedback = '0',
                                                                sellerfeedback = '0',
                                                                winnermarkedaspaid = '0',
                                                                winnermarkedaspaiddate = '0000-00-00 00:00:00',
                                                                winnermarkedaspaidmethod = '',
                                                                currentprice = '" . $ilance->db->escape_string($newcurrentprice) . "'                                                                
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $existing = array(
                                                                '{{buyer}}' => fetch_user('username', fetch_auction('user_id', intval($projectid))),
                                                                '{{username}}' => fetch_user('username', intval($userid)),					  
                                                                '{{project_title}}' => stripslashes(fetch_auction('project_title', intval($projectid))),
                                                                '{{reason}}' => $reason,
                                                        );
                                                        
                                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_do_retraction_end')) ? eval($apihook) : false;
                                                        
                                                        // #### email auction owner
                                                        $ilance->email->mail = fetch_user('email', fetch_auction('user_id', intval($projectid)));
                                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                        $ilance->email->get('bids_retraction');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        
                                                        // email administrator
                                                        $ilance->email->mail = SITE_EMAIL;
                                                        $ilance->email->slng = fetch_site_slng();
                                                        $ilance->email->get('bids_retraction_admin');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        
                                                        if ($silentmode == false)
                                                        {
                                                                print_notice($phrase['_bid_retracted_from_awarded_bid'], $ilance->language->construct_phrase($phrase['_you_have_retracted_your_bid_from_this_auction_please_remember_your_current_subscription_level_provides_x_bid_retracts'], $totalretracts), "javascript: history.go(-1)", $phrase['_return_to_the_previous_menu']);
                                                                exit();
                                                        }
                                                        
                                                        return true;
                                                }
                                        }
                                        else
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_awarded_cannot_retract_start')) ? eval($apihook) : false;
                                                
                                                $area_title = $phrase['_cannot_retract_bid'];
                                                $page_title = SITE_NAME . ' - ' . $phrase['_cannot_retract_bid'];
                                                
                                                print_notice($phrase['_cannot_retract_bid'], $ilance->language->construct_phrase($phrase['_you_cannot_retract_this_bid_because_it_was_determined_as_the_winning_awarded_bid'], $totalretracts), "javascript: history.go(-1)", $phrase['_return_to_the_previous_menu']);
                                                exit();       
                                        }
                                }
                                
                                // #### not awarded ############################
                                else
                                {
                                        ($apihook = $ilance->api('construct_bid_retraction_start')) ? eval($apihook) : false;
                                        
                                        // #### can retract bids ###############
                                        if ($canretract)
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_can_retract_start')) ? eval($apihook) : false;
                                                
                                                // determine if a bid retract for this user for this listing already exists
                                                $sqlcheck = $ilance->db->query("
                                                        SELECT retractid
                                                        FROM " . DB_PREFIX . "project_bid_retracts
                                                        WHERE user_id = '" . intval($userid) . "'
                                                                AND project_id = '" . intval($projectid) . "'
                                                                AND bid_id = '" . intval($bidid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlcheck) == 0)
                                                {
                                                        ($apihook = $ilance->api('construct_bid_retraction_do_retraction_start')) ? eval($apihook) : false;
                                                        
                                                        // #### insert new retract
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "project_bid_retracts
                                                                (retractid, user_id, bid_id, project_id, reason, date)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($userid) . "',
                                                                '" . intval($bidid) . "',
                                                                '" . intval($projectid) . "',
                                                                '" . $ilance->db->escape_string($reason) . "',
                                                                '" . DATETODAY . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                        
                                                        // #### retract all bids placed by bidder
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_bids
                                                                SET bidstate = 'retracted'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                                     AND bid_id = '" . intval($bidid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_realtimebids
                                                                SET bidstate = 'retracted'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                                     AND bid_id = '" . intval($bidid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### delete any proxy bids placed by the bidder
                                                        $ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "proxybid
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        // #### update retract count for member
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET bidretracts = bidretracts + 1
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $bids = fetch_auction('bids', intval($projectid));
                                                        $currentprice = fetch_auction('currentprice', intval($projectid));
                                                        
                                                        $newcurrentprice = $currentprice;                                                        
                                                        if ($bids >= 2)
                                                        {
                                                                $temp = $ilance->bid->fetch_second_highest_bid(intval($projectid));
                                                                $newcurrentprice = $temp[0];
                                                                $newhighbidderid = $temp[1];
                                                                if ($newcurrentprice > $currentprice)
                                                                {
                                                                        $newcurrentprice = $currentprice;
                                                                }
                                                        }
        
                                                        // #### update listing bids quantity
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET bids = bids - 1,
                                                                bidsretracted = bidsretracted + 1,
                                                                currentprice = '" . $ilance->db->escape_string($newcurrentprice) . "'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        $existing = array(
                                                                '{{buyer}}' => fetch_user('username', fetch_auction('user_id', intval($projectid))),
                                                                '{{username}}' => fetch_user('username', intval($userid)),					  
                                                                '{{project_title}}' => stripslashes(fetch_auction('project_title', intval($projectid))),
                                                                '{{reason}}' => $reason,
                                                        );
                                                        
                                                        ($apihook = $ilance->api('construct_bid_retraction_do_retraction_end')) ? eval($apihook) : false;
                                                        
                                                        // #### email auction owner
                                                        $ilance->email->mail = fetch_user('email', fetch_auction('user_id', intval($projectid)));
                                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                        $ilance->email->get('bids_retraction');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        
                                                        // #### email administrator
                                                        $ilance->email->mail = SITE_EMAIL;
                                                        $ilance->email->slng = fetch_site_slng();
                                                        $ilance->email->get('bids_retraction_admin');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        
                                                        if ($silentmode == false)
                                                        {
                                                                print_notice($phrase['_bid_retracted'], $ilance->language->construct_phrase($phrase['_you_have_retracted_your_bid_from_this_auction'], $totalretracts), "javascript: history.go(-1)", $phrase['_return_to_the_previous_menu']);
                                                                exit();
                                                        }
                                                        
                                                        return true;
                                                }
                                        }
                                        
                                        // #### cannot retract bids ############
                                        else
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_cannot_retract_start')) ? eval($apihook) : false;
                                                
                                                if ($silentmode == false)
                                                {
                                                        $area_title = $phrase['_cannot_retract_bid'];
                                                        $page_title = SITE_NAME . ' - ' . $phrase['_cannot_retract_bid'];
                                                
                                                        $navcrumb = array();
                                                        $navcrumb["$ilpage[selling]?cmd=management"] = $phrase['_selling_activity'];
                                                        $navcrumb[""] = $phrase['_cannot_retract_bid'];
                                
                                                        print_notice($phrase['_cannot_retract_bid'], $phrase['_you_cannot_retract_your_bid_at_this_time_this_action_is_currently_unavailable'], "javascript: history.go(-1)", $phrase['_return_to_the_previous_menu']);
                                                        exit();
                                                }
                                                
                                                return false;
                                        }
                                }
                        }
                        
                        // #### no retractions left or subscription level does not permit bid retractions
                        else
                        {
                                ($apihook = $ilance->api('construct_bid_retraction_bad_permissions_start')) ? eval($apihook) : false;
                                
                                if ($silentmode == false)
                                {
                                        $area_title = $phrase['_cannot_retract_bid'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_cannot_retract_bid'];
                                
                                        print_notice($phrase['_maximum_bid_retracts_used_this_month'], $ilance->language->construct_phrase($phrase['_sorry_you_have_used_the_total_number_of_bid_retractions_for_your_subscription'], $totalretracts), "javascript: history.go(-1)", $phrase['_return_to_the_previous_menu']);
                                        exit();
                                }
                                
                                return false;
                        }
                }
                
                return false;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>