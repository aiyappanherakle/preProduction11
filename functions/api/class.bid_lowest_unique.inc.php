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
* Lowest Unique Bid class to perform the majority of lowest unique bidding functions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class bid_lowest_unique extends bid
{
        /**
        * Function for returning the bid count for a particular unique auction.
        *
        * @param       integer      project id
        *
        * @return     integer      number of bids
        */
        function fetch_bid_count($projectid = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS bids
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return (int)$res['bids'];
                }
                
                return 0;
        }
        
        /**
        * Function for printing the lowest unique bid status on the detailed auction listing page.
        *
        * @param       integer      user id
        * @param       integer      project id
        *
        * @return     string       HTML representation of the lowest unique bid status
        */
        function is_lowest_unique_bidder_html($userid = 0, $projectid = 0)
        {
                global $ilance, $myapi, $phrase, $ilpage, $show;
                
                $show['iswinninglowestuniquebidder'] = false;
                
                $currencyid = fetch_auction('currencyid', intval($projectid));
                $status = fetch_auction('status', intval($projectid));
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE user_id = '" . intval($userid) . "'
                            AND status = 'lowestunique'
                            AND project_id = '" . intval($projectid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $phrase['_winning_with_lowest_unique_bid_of'] . ' <span class="blue">' . $ilance->currency->format($res['uniquebid'], $currencyid) . '</span></strong></div>';
                }
                else
                {
                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $phrase['_no_lowest_unique_bid_placed'] . '</strong></div>';
                }
                
                // is auction finished and are we the lowest unique bid winner?
                
                $sql2 = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE user_id = '" . intval($userid) . "'
                            AND status = 'lowestunique'
                            AND project_id = '" . intval($projectid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql2) > 0 AND $status == 'expired')
                {
                        $show['iswinninglowestuniquebidder'] = true;
                        
                        $res2 = $ilance->db->fetch_array($res2, DB_ASSOC);
                        
                        $invoiceid = (int)$ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($projectid) . "' AND user_id = '" . intval($userid) . "'", "invoiceid");
                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $phrase['_winner_of_auction_event_with_lowest_unique_bid_of'] . '</strong> <span class="blue"><strong>' . $ilance->currency->format($res['uniquebid'], $currencyid) . '</strong></span></div>';
                        $html .= '<div class="gray"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $invoiceid . '">' . $phrase['_pay_for_item'] . '</a></span> : ' . $phrase['_pay_your_lowest_winning_unique_bid_to_have_this_item_ship_to_you'] . '</span></div>';
                }
                
                return $html;
        }
        
        /**
        * Function to print out the lowest unique bid transaction status for a listing that is won by a winning bidder.
        *
        * @param       integer      project id
        * @param       boolean      shortform notice? (default false)
        *
        * @return     string       HTML formatted representation of the response
        */
        function fetch_transaction_status($projectid = 0, $shortform = false)
        {
                global $ilance, $myapi, $ilpage, $phrase, $show, $ilconfig;
                
                $html = '';
                
                // check if invoice is paid
                $bidderid = $ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . intval($projectid) . "'", "winner_user_id");
                if (isset($bidderid) AND $bidderid > 0)
                {
                        $status = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($projectid) . "' AND user_id = '" . intval($bidderid) . "'", "status");
                        $invoiceid = (int)$ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($projectid) . "' AND user_id = '" . intval($bidderid) . "'", "invoiceid");
                        
                        if (isset($status) AND $status == 'paid' AND isset($invoiceid) AND $invoiceid > 0)
                        {
                                $paiddate = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($projectid) . "' AND user_id = '" . intval($bidderid) . "'", "paiddate");
                                $html .= $phrase['_the_transaction_associated_with_this_listing_was_marked_as_paid_on'] . ' ' . print_date($paiddate);
                        }
                        else if (isset($status) AND $status == 'unpaid' AND isset($invoiceid) AND $invoiceid > 0)
                        {
                                $html .= $phrase['_the_transaction_associated_with_this_listing_has_not_been_paid'];
                        }
                }
                
                return $html;
        }
        
        /**
        * Function for returning the html display message in a unique bid auction listing page
        * and will determine a live message based on the status of the bidder viewing.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       integer      amount
        *
        * @return     string       HTML formatted representation of the response
        */
        function fetch_feedback_status()
        {
                global $ilance, $myapi, $ilpage, $phrase, $show, $ilconfig;
                
                $html = '';
                
                return $html;
        }
        
        /**
        * Function for returning the html display message in a unique bid auction listing page
        * and will determine a live message based on the status of the bidder viewing.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       integer      amount
        *
        * @return     string       HTML formatted representation of the response
        */
        function print_unique_bid_response($userid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi, $ilpage, $phrase, $show, $ilconfig;
                
                $html = '';
                
                if ($amount > 0)
                {
                        $currencyid = fetch_auction('currencyid', intval($projectid));
                        $status = fetch_auction('status', intval($projectid));
                        
                        $sql = $ilance->db->query("
                                SELECT status
                                FROM " . DB_PREFIX . "projects_uniquebids
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND user_id = '" . intval($userid) . "'
                                    AND uniquebid = '" . $ilance->db->escape_string($amount) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                
                                if ($res['status'] == 'lowestunique' AND $status == 'open')
                                {
                                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $this->is_lowest_unique_bidder_html($userid, intval($projectid)) . '</strong></div>';
                                        if (!isset($show['iswinninglowestuniquebidder']) OR isset($show['iswinninglowestuniquebidder']) AND $show['iswinninglowestuniquebidder'] == false)
                                        {
                                                $html .= '<div class="gray" style="padding-top:5px">' . $phrase['_your_last_bid'] . ': <strong>' . $ilance->currency->format(sprintf("%01.2f", $amount), $currencyid) . '</strong> '.$phrase['_is_currently_the_lowest_unique_bid'].'</div>';
                                        }
                                }
                                else if ($res['status'] == 'lowestunique' AND $status != 'open')
                                {
                                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $phrase['_winner_of_auction_event_with_lowest_unique_bid_of'] . '</strong> <span class="blue"><strong>' . $ilance->currency->format(sprintf("%01.2f", $amount), $currencyid) . '</strong></span></div><div class="gray">' . $phrase['_congratulations_you_are_the_winning_bidder_for_this_item_your_bid_was'] . ' [ <span class="blue"><a href="' . $ilpage['buying'] . '?cmd=management&amp;subcmd=lub&amp;bidsub=awarded">' . $phrase['_buying_activity'] . '</a></span> ]</div>';
                                }
                                else if ($res['status'] == 'nonunique')
                                {
                                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $this->is_lowest_unique_bidder_html($userid, intval($projectid)) . '</strong></div>';
                                        if (!isset($show['iswinninglowestuniquebidder']) OR isset($show['iswinninglowestuniquebidder']) AND $show['iswinninglowestuniquebidder'] == false)
                                        {
                                                $html .= '<div class="gray" style="padding-top:5px">' . $phrase['_your_last_bid'] . ': <strong>' . $ilance->currency->format(sprintf("%01.2f", $amount), $currencyid) . '</strong> '.$phrase['_is_not_unique_other_bidders_have_placed_the_same_bid_amount_try_again'].'</div>';
                                        }
                                }
                                else if ($res['status'] == 'unique')
                                {
                                        $html = '<div style="font-family: arial; font-size:15px;"><strong>' . $this->is_lowest_unique_bidder_html($userid, intval($projectid)) . '</strong></div>';
                                        if (!isset($show['iswinninglowestuniquebidder']) OR isset($show['iswinninglowestuniquebidder']) AND $show['iswinninglowestuniquebidder'] == false)
                                        {
                                                $html .= '<div class="gray" style="padding-top:5px">' . $phrase['_your_last_bid'] . ': <strong>' . $ilance->currency->format(sprintf("%01.2f", $amount), $currencyid) . '</strong> '.$phrase['_is_unique_other_lower_unique_bids_have_been_placed_try_again'].'</div>';
                                        }
                                }
                        }
                }
                
                return $html;
        }
        
        /**
        * Function for determining if an auction has a lower unique bid placed than the bidder currently
        * placing their bid.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       string       unique bid amount
        *
        * @return     bool         true or false
        */
        function has_lower_unique_bids($userid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi, $ilpage;
                
                $currencyid = fetch_auction('currencyid', intval($projectid));
                $value = 0;
                
                $sql = $ilance->db->query("
                        SELECT uid, user_id
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND uniquebid < " . $ilance->db->escape_string($amount) . "
                            AND (status = 'unique' OR status = 'lowestunique')
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        // there are currently lower unique bids than the bid amount being placed
                        $value = 1;
                }
                else
                {
                        // there are no lower unique bid amounts
                        // let's check to see if this user has any higher unique or lowestunique bidamounts
                        // and we'll update the status accordingly
                        // for example, the user might have bid 5.00 (lowestunique) and now will bid 3.00
                        // so this will change 5.00 to unique and 3.00 to lowestunique
                        $sql2 = $ilance->db->query("
                                SELECT uid, user_id, uniquebid, status
                                FROM " . DB_PREFIX . "projects_uniquebids
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND uniquebid > " . $ilance->db->escape_string($amount) . "
                                    AND (status = 'unique' OR status = 'lowestunique')
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                {
                                        if ($res2['status'] == 'lowestunique')
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_uniquebids
                                                        SET status = 'unique'
                                                        WHERE user_id = '" . $res2['user_id'] . "'
                                                            AND uid = '" . $res2['uid'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($res2['user_id'] != $userid)
                                                {
                                                    // dispatch email to previous lowest bidder informing them
                                                    // their bid is no longer valid and to suggest they rebid
                                                    $subject = 'You are no longer the lowest unique winning bidder!';
                                                    $message = "Hello,
                                                    
This email is to inform you that your bid: " . $ilance->currency->format($res2['uniquebid'], $currencyid) . " is no longer the lowest unique winning bid for item #" . intval($projectid) . ".
Another bidder has placed a bid of the same bid amount << OR >> a value that is uniquely lower than yours.  

We suggest you log-in and reclaim your bid status by placing a new lowest unique bid amount.

Place a bid: " . HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid) . "

Good luck!
Team " . SITE_NAME;
                                                    $email = fetch_user('email', $res2['user_id']);
                                                    send_email($email, $subject, $message, SITE_EMAIL);    
                                                }
                                        }                        
                                }
                        }
                }
                
                return $value;
        }
        
        /**
        * Function for returning the number of how many lower unique bids are currently placed based
        * on the amount being sent to this function.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       string       unique bid amount
        *
        * @return     integer      count of how many lower unique bids are placed
        */
        function fetch_lower_unique_bids($userid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi;
                
                $value = 0;
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS lowerbidamounts
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE uniquebid < " . $ilance->db->escape_string($amount) . "
                            AND project_id = '" . intval($projectid) . "'
                            AND (status = 'unique' OR status = 'lowestunique')
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $value = $res['lowerbidamounts'];
                }
                
                return $value;
        }
        
        /**
        * Function for returning the number of how many similiar bids are currently placed based
        * on the same amount being sent to this function.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       string       unique bid amount
        *
        * @return     integer      count of how many lower unique bids are placed
        */
        function fetch_similar_unique_bids($userid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi;
                
                $value = 0;
                $sql = $ilance->db->query("
                        SELECT COUNT(uniquebid) AS similarbids
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE uniquebid = ".$amount."
                            AND project_id = '" . intval($projectid) . "'
                        GROUP BY uniquebid HAVING COUNT(uniquebid) > 0
                        ORDER BY similarbids ASC, uniquebid ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $value = $res['similarbids'];
                }
                
                return $value;
        }
        
        /**
        * Function for returning the HTML representation of the actual bid response message that is included
        * in the bid stamp when any bidder places a bid on a unique bid auction event.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       string       unique bid amount
        *
        * @return     string       HTML representation of the bid response message included in bid stamp
        */
        function fetch_unique_bid_response($userid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '';
                $currencyid = fetch_auction('currencyid', intval($projectid));
                
                // is this amount unique?
                $sql = $ilance->db->query("
                        SELECT uid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE uniquebid = ".$amount."
                            AND project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) == 0)
                {
                        // are there any lower unique amounts?
                        // including any previous bids from this user placing the bid?
                        if ($this->has_lower_unique_bids($userid, intval($projectid), $amount))
                        {
                                // how many lower unique amounts are there?
                                $loweruniquebids = $this->fetch_lower_unique_bids($userid, intval($projectid), $amount);
                                $html = $phrase['_your_bid'] . ': ' . $ilance->currency->format($amount, $currencyid) . ' ' . $phrase['_is_unique_but_there_is_currently'] . ' <strong>' . $loweruniquebids . '</strong> ' . $phrase['_lower_unique_bids_placed_than_yours'];
                        }
                        else
                        {
                                $html = $phrase['_your_bid'] . ': ' . $ilance->currency->format($amount, $currencyid) . ' ' . $phrase['_is_currently_the_lowest_unique_bid_placed'];
                        }
                }
                else
                {
                        // how many other amounts are similar to this?
                        $similarbids = $this->fetch_similar_unique_bids($userid, intval($projectid), $amount);
                        $html = $phrase['_your_bid'] . ': ' . $ilance->currency->format($amount, $currencyid) . ' is <strong>not-unique</strong> because there is currently <strong>' . $similarbids . '</strong> other bidders with a similar bid placed.';
                }
                
                return $html;
        }
        
        /**
        * Function for returning the lowest unique bid uid key from the database
        *
        * @param       integer      project id
        *
        * @return     integer      uid key of the lowest bid placed in the database
        */
        function fetch_lowest_unique_bid_uid($projectid = 0)
        {
                global $ilance, $myapi;
                
                $value = 0;
                $sql = $ilance->db->query("
                        SELECT uid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND status = 'unique'
                        ORDER BY uniquebid ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $value = $res['uid'];
                }
                
                return $value;
        }
        
        /**
        * Function for determining if there are any lowestunique bid status in the database for the
        * current auction event and if not this function will find out if there is any other potential
        * bid currently placed that could be candidate for a lowestunique bid status.  There must be
        * at least 1 lowestunique bid placed to win the auction and it is possible that a single auction
        * might not always have a lowestunique bid status.
        *
        * @param       integer      project id
        */
        function reassign_lowest_unique_bid($projectid = 0)
        {
                global $ilance, $myapi;
                
                // do we still have a lowest unique bid placed?
                $sql = $ilance->db->query("
                        SELECT uid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND status = 'lowestunique'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        // we still have a bid placed that is unique .. nothing to do here for now.
                }
                else
                {
                        // there is no lowest unique bid placed for this event!
                        // let's find the next lowest unique bid placed and assign it!
                        $lowestuid = $this->fetch_lowest_unique_bid_uid($projectid);
                        if ($lowestuid > 0)
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects_uniquebids
                                        SET status = 'lowestunique'
                                        WHERE uid = '".intval($lowestuid)."'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                        }
                        else
                        {
                                // if we get to this point we have a bunch of bidders that has in this rare condition
                                // bid on all the same amounts and no bid currently placed can be
                                // considered the "lowest unique winning bid".
                                // should we email the admin and inform them or should we just let things ride
                                // and hope that before this event finishes there will be a new bid placed that is the
                                // lowest and is unique.
                        }
                }
        }
        
        /**
        * Function for returning a single status out of a combination of three: nonunique, unique and
        * lowestunique.
        *
        * @param       integer      user id
        * @param       integer      project id
        * @param       string       unique bid amount
        *
        * @return     string       actual database table status to use for placing the new bid amount
        */
        function fetch_unique_bid_status($userid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi;
                
                $html = 'nonunique';
                // is this amount unique?
                $sql = $ilance->db->query("
                        SELECT uid, user_id, status
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE uniquebid = ".$amount."
                            AND project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) == 0)
                {
                        // are there any lower unique amounts?
                        if ($this->has_lower_unique_bids($userid, $projectid, $amount) == 1)
                        {
                                // unique but there may be other lower unique bids
                                $html = 'unique';
                        }
                        else
                        {
                                // lowest unique bid
                                $html = 'lowestunique';
                        }
                }
                else
                {
                        // another bid is similar
                        $html = 'nonunique';
                        
                        // because the bid is similar, we must also change the status for the other bid amounts to nonunique
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects_uniquebids
                                        SET status = 'nonunique'
                                        WHERE project_id = '" . intval($projectid) . "'
                                            AND uid = '".$res['uid']."'
                                ", 0, null, __FILE__, __LINE__);
                        }
                        
                        // because we've updated the bid status'es to nonunique we may not have any status with lowestunique
                        // so now we must find the newest lowest unique bid and update it's status from unique to lowestunique
                        $this->reassign_lowest_unique_bid(intval($projectid));
                }
                
                return $html;
        }
        
        /**
        * Function for returning the very last unique bid amount placed by the bidder
        * for a particular auction event.
        *
        * @param       integer      user id
        * @param       integer      project id
        *
        * @return     string       amount of last bid placed
        */
        function fetch_last_unique_bid_amount($userid = 0, $projectid = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT uniquebid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE user_id = '".intval($userid)."'
                            AND project_id = '" . intval($projectid) . "'
                        ORDER BY uid DESC
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return $res['uniquebid'];
                }
                else
                {
                        return 0;
                }
        }
        
        /**
        * Function for returning the lowest unique bid amount for a particular auction event.
        *
        * @param       integer      project id
        *
        * @return     string       amount of lowest unique bid placed
        */
        function fetch_lowest_unique_bidamount($projectid = 0)
        {
                global $ilance, $myapi;
                
                $lowest = 0;
                $sql = $ilance->db->query("
                        SELECT MIN(uniquebid) AS uniquebid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE status = 'lowestunique'
                            AND project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $lowest = $res['uniquebid'];
                }
                
                return $lowest;
        }
        
        /**
        * Function for returning the total amount of similar bids compared to an amount being provided
        *
        * @param       integer      project id
        * @param       integer      amount
        *
        * @return     integer      total count
        */
        function fetch_total_similar_bids($projectid = 0, $amount = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND uniquebid = '".$ilance->db->escape_string($amount)."'
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                if ($res['count'] == '0')
                {
                        return '1';
                }
                else
                {
                        $sql2 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "projects_uniquebids
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND uniquebid = '".$ilance->db->escape_string($amount)."'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                // for all rows that have similar bids placed.. update the total bids counter.
                                while ($res2 = $ilance->db->fetch_array($sql2))
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects_uniquebids
                                                SET totalbids = totalbids + 1
                                                WHERE project_id = '" . intval($projectid) . "'
                                                    AND uid = '".$res2['uid']."'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                        
                        // add plus 1 because it hasn't been added to db yet.. but will be in like 1 nanosecond.. 
                        return ($res['count']+1);
                }
                
        }
        
        /**
        * Function for inserting a new lowest unique bid within a unique bid auction event.
        *
        * @param       integer      user id
        * @param       integer      owner id
        * @param       integer      project id
        * @param       string       unique bid amount
        */
        function insert_unique_bid($userid = 0, $ownerid = 0, $projectid = 0, $amount = 0)
        {
                global $ilance, $myapi;
                
                // fetch response for bid
                // responses expected:
                // Your bid: $0.83 is currently the lowest unique bid.
                // Your bid: $0.33 is unique but there are 5 other lower unique bids than yours.
                // Your bid: $0.15 is not-unique because there are 3 other bidders with a similar bid.
                $response = $this->fetch_unique_bid_response($userid, intval($projectid), sprintf("%01.2f", $amount));
                
                // status might be: nonunique (default), unique, lowestunique (winner or current winner..)
                $status = $this->fetch_unique_bid_status($userid, intval($projectid), sprintf("%01.2f", $amount));
                
                // find out how many times this bid amount has been used for updating the totalbids column accordingly
                $totalbids = $this->fetch_total_similar_bids(intval($projectid), sprintf("%01.2f", $amount));
                
                // insert unique bid
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "projects_uniquebids
                        (uid, project_id, project_user_id, user_id, uniquebid, response, date, status, totalbids)
                        VALUES(
                        NULL,
                        '" . intval($projectid) . "',
                        '" . intval($ownerid) . "',
                        '" . intval($userid) . "',
                        '" . sprintf("%01.2f", $amount) . "',
                        '" . $response . "',
                        '" . DATETIME24H . "',
                        '" . $status . "',
                        '" . $totalbids . "')
                ", 0, null, __FILE__, __LINE__);
                
                // subtract a bid credit from bidder
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET bidstoday = bidstoday + 1
                        WHERE user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__); 
        }
        
        /**
        * Function for returning a boolean value if a particular project has a lowest unique bid winner
        *
        * @param       integer      project id
        *
        * @return     bool         yes / no
        */
        function has_unique_bid_winner($projectid = 0)
        {
                global $ilance, $myapi;
                
                $value = 0;
                
                $sql = $ilance->db->query("
                        SELECT uid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND status = 'lowestunique'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $value = 1;    
                }
                
                return $value;
        }
        
        /**
        * Function for returning a boolean value if a particular project has a lowest unique bid winner
        *
        * @param       integer      project id
        *
        * @return     string       HTML representation of the lowest unique bidder and their bid
        */
        function fetch_lowest_unique_bid_winner($projectid = 0, $returnusername = true)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND status = 'lowestunique'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($returnusername)
                        {
                                $html = print_username($res['user_id'], 'href', 0, '', '');
                        }
                        else
                        {
                                $html = $res['user_id'];
                        }
                }
                
                return $html;
        }
        
        /**
        * Function for returning a bid amount value if a particular project has a lowest unique bid winner
        *
        * @param       integer      project id
        * @param       boolean      return amount with currency formatting? default true
        *
        * @return     string       HTML representation of the lowest unique bidder and their bid
        */
        function fetch_lowest_unique_bid_winner_amount($projectid = 0, $currencyformat = true)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '';
                $currencyid = fetch_auction('currencyid', intval($projectid));
                
                $sql = $ilance->db->query("
                        SELECT uniquebid
                        FROM " . DB_PREFIX . "projects_uniquebids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND status = 'lowestunique'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($currencyformat)
                        {
                                $html = $ilance->currency->format($res['uniquebid'], $currencyid);
                        }
                        else
                        {
                                $html = $res['uniquebid'];
                        }
                }
                
                return $html;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>