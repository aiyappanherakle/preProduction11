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
* Bid Tabs class to perform the majority of bid tab display and output operations within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class bid_tabs extends bid
{
        /**
        * Function for printing a specific service bid tab sql query.
        *
        * @param       string       bid tab (drafts, delisted, archived, expired, pending, active, serviceescrow)
        * @param       string       count or string
        *
        * @return     string       MySQL query or MySQL query count
        */
        function fetch_service_bidtab_sql($tab = '', $countorstring = '', $userid = 0, $extra = '')
        {
                global $ilance, $myapi, $ilconfig;

                if ($countorstring == 'count')
                {
                        if ($tab == 'drafts')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.status = 'draft'
                                            AND p.visible = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'delisted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'delisted'
                                            AND p.visible = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'archived')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'archived'
                                            AND p.visible = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'expired')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE project_state = 'service'
                                            $extra
                                            AND visible = '1'
                                            AND status = 'expired'
                                            AND user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'awarded')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE project_state = 'service'
                                            $extra
                                            AND visible = '1'
                                            AND (status = 'wait_approval' OR status = 'approval_accepted' OR status = 'finished')
                                            AND user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'pending')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0'))" : "AND p.visible = '0'") . "
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'active')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'open'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'serviceescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, b.bid_id, b.user_id as bidder_id, b.bidamount, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "users AS u,
                                        " . DB_PREFIX . "projects_escrow AS e,
                                        " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "invoices AS i
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND u.user_id = '" . intval($userid) . "'
                                            AND e.project_user_id = '" . intval($userid) . "'
                                            AND e.status != 'cancelled'
                                            AND e.bid_id = b.bid_id
                                            AND e.user_id = b.user_id
                                            AND e.project_id = p.project_id
                                            AND e.invoiceid = i.invoiceid
                                            AND i.invoicetype = 'escrow'
                                            AND p.project_state = 'service'
                                            AND i.projectid = e.project_id
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        
                        return $sqlcount;
                }
                else
                {
                        if ($tab == 'drafts')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.status = 'draft'
                                            AND p.visible = '1'
                                ";
                                
                        }
                        else if ($tab == 'delisted')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'delisted'
                                            AND p.visible = '1'
                                ";
                        }
                        else if ($tab == 'archived')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'archived'
                                            AND p.visible = '1'
                                ";
                        }
                        else if ($tab == 'expired')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'service'
                                            $extra
                                            AND p.visible = '1'
                                            AND p.status = 'expired'
                                            AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'awarded')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'service'
                                            $extra
                                            AND p.visible = '1'
                                            AND (p.status = 'wait_approval' OR p.status = 'approval_accepted' OR p.status = 'finished')
                                            AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'pending')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0'))" : "AND p.visible = '0'") . "
                                ";
                        }
                        else if ($tab == 'active')
                        {
                                $query = "
                                        SELECT p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'open'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "
                                ";
                        }
                        
                        return $query;
                }
        }
        
        /**
        * Function for printing a specific product bid tab sql query.
        *
        * @param       string       bid tab
        * @param       string       count or string
        * @param       string       group by statement
        * @param       string       order by statement
        * @param       integer      limit
        * @param       integer      user id
        * @param       string       extra sql
        *
        * @return     string       MySQL Query
        */
        function fetch_product_bidtab_sql($bidtab = '', $countorstring = '', $groupby = '', $orderby = '', $limit = '', $userid = 0, $extra = '')
        {
                global $ilance, $myapi;
                
                if ($countorstring == 'count')
                {
                        if ($bidtab == 'retracted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, b.bidamount, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id 
                                        $extra
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'open' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'open' AND b.bidstatus = 'placed' AND b.bidstate = 'retracted'
                                                OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'expired' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, b.bidamount, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.bidstate != 'retracted'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND ((p.status = 'open' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted' OR p.status = 'archived' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted' OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted' OR p.status = 'expired' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted') OR (p.status = 'finished'))
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'awarded_unique')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.uid, b.uniquebid, b.date, b.status AS uniquestatus, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.date_starts, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "projects_uniquebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.status = 'lowestunique'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.project_details = 'unique'
                                            AND p.status != 'open'
                                            AND p.haswinner = '1' AND p.winner_user_id = '" . intval($userid) . "'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'invited')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.currencyid
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations as i
                                        WHERE i.project_id = p.project_id
                                            $extra
                                            AND i.seller_user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.status = 'open'
                                            AND i.bid_placed = 'no'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'expired')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT MAX(b.bid_id) AS bid_id, MAX(b.bidamount) AS bidamount, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.bidstate != 'retracted'
                                            AND b.bidstate = 'expired'
                                            AND b.bidstatus = 'outbid'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'expired' OR p.status = 'finished')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'expired_unique')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.uid, b.uniquebid, b.date, b.status AS uniquestatus, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.date_starts, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "projects_uniquebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND b.status != 'lowestunique'
                                            AND p.project_state = 'product'
                                            AND p.project_details = 'unique'
                                            AND p.status != 'open'
                                            AND (p.haswinner = '1' AND p.winner_user_id != '" . intval($userid) . "' OR p.haswinner = '0')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'active')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, MAX(b.bidamount) AS bidamount, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.user_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.bidstate != 'retracted'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (b.bidstatus = 'placed'
                                                AND p.status != 'finished'
                                                AND p.status != 'expired'
                                                AND p.status != 'archived'
                                                AND p.status != 'closed'
                                                AND p.status != 'delisted')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'active_unique')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.uid, b.uniquebid, b.date, b.status AS uniquestatus, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.date_starts, p.project_id, p.user_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "projects_uniquebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.project_details = 'unique'
                                            AND p.haswinner = '0'
                                            AND p.status = 'open'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'productescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.escrow_id, b.bid_id, b.user_id as bidder_id, b.bidstatus, b.qty, i.invoiceid, i.buynowid, i.paid, i.invoicetype, i.paiddate, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "users AS u,
                                        " . DB_PREFIX . "projects_escrow AS e,
                                        " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "invoices AS i
                                        WHERE e.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND u.user_id = '" . intval($userid) . "'
                                            AND b.bidstate != 'retracted'
                                            AND e.status != 'cancelled'
                                            AND e.bid_id = b.bid_id
                                            AND e.user_id = b.user_id
                                            AND e.project_id = p.project_id
                                            AND e.invoiceid = i.invoiceid
                                            AND i.invoicetype = 'escrow'
                                            AND i.projectid = e.project_id
                                            AND p.project_state = 'product'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'buynowproductescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.orderid, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.qty, p.project_id, p.escrow_id, p.paymethodoptions, p.currencyid
                                        FROM " . DB_PREFIX . "buynow_orders AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE p.project_id = b.project_id
                                            $extra
                                            AND b.buyer_id = '" . intval($userid) . "'
                                            AND b.status != 'cancelled'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        return $sqlcount;
                }
                else
                {
                        if ($bidtab == 'retracted')
                        {
                                $query = "
                                        SELECT b.bid_id, b.bidamount, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'open' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'open' AND b.bidstatus = 'placed' AND b.bidstate = 'retracted'
                                                OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'expired' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $query = "
                                        SELECT b.bid_id, b.bidamount, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.bidstate != 'retracted'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND ((p.status = 'open' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted' OR p.status = 'archived' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted' OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted' OR p.status = 'expired' AND b.bidstatus = 'awarded' AND b.bidstate != 'retracted') OR (p.status = 'finished'))
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'awarded_unique')
                        {
                                $query = "
                                        SELECT b.uid, b.uniquebid, b.date, b.status AS uniquestatus, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.date_starts, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "projects_uniquebids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.status = 'lowestunique'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.project_details = 'unique'
                                            AND p.status != 'open'
                                            AND p.haswinner = '1' AND p.winner_user_id = '" . intval($userid) . "'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'invited')
                        {
                                $query = "
                                        SELECT UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime,p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations AS i,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE i.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND i.seller_user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.status = 'open'
                                            AND i.bid_placed = 'no'
                                ";
                        }
                        else if ($bidtab == 'expired')
                        {
                                $query = "
                                        SELECT MAX(b.bid_id) AS bid_id, MAX(b.bidamount) AS bidamount, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.bidstate != 'retracted'
                                            AND b.bidstate = 'expired'
                                            AND b.bidstatus = 'outbid'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'expired' OR p.status = 'finished')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'expired_unique')
                        {
                                $query = "
                                        SELECT b.uid, b.uniquebid, b.date, b.status AS uniquestatus, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.date_starts, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "projects_uniquebids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND b.status != 'lowestunique'
                                            AND p.project_state = 'product'
                                            AND p.project_details = 'unique'
                                            AND p.status != 'open'
                                            AND (p.haswinner = '1' AND p.winner_user_id != '" . intval($userid) . "' OR p.haswinner = '0')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'active')
                        {
                                $query = "
                                        SELECT b.bid_id, MAX(b.bidamount) as bidamount, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.user_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND b.bidstate != 'retracted'
                                            AND p.project_state = 'product'
                                            AND (b.bidstatus = 'placed'
                                                AND p.status != 'finished'
                                                AND p.status != 'expired'
                                                AND p.status != 'archived'
                                                AND p.status != 'closed'
                                                AND p.status != 'delisted')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'active_unique')
                        {
                                $query = "
                                        SELECT b.uid, b.uniquebid, b.date, b.status AS uniquestatus, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.date_starts, p.project_id, p.user_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
                                        FROM " . DB_PREFIX . "projects_uniquebids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.project_details = 'unique'
                                            AND p.haswinner = '0'
                                            AND p.status = 'open'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        
                        return $query;
                }
        }
        
        /**
        * Function for printing a specific bid tab sql query.
        *
        * @param       string       bid tab
        * @param       string       count or string
        * @param       string       group by statement
        * @param       string       order by statement
        * @param       integer      limit
        * @param       integer      user id
        * @param       string       extra sql query (used for listing period for service bid results)
        *
        * @return     string       MySQL Query
        */
        function fetch_bidtab_sql($bidtab = '', $countorstring = '', $groupby = '', $orderby = '', $limit = '', $userid = 0, $extra = '')
        {
                global $ilance, $myapi, $phrase;
                
                if ($countorstring == 'count')
                {
                        if ($bidtab == 'delisted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'open' AND b.bidstatus = 'declined'
                                                        OR p.status = 'closed' AND b.bidstatus = 'declined'
                                                        OR p.status = 'expired' AND b.bidstatus = 'declined'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'declined'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'archived' AND b.bidstatus = 'declined')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'retracted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate = 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'                        
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'invited')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.date_starts, p.fvf, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations as i
                                        WHERE i.project_id = p.project_id
                                                $extra
                                                AND i.seller_user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND i.bid_placed = 'no'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'expired')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, b.buyerpaymethod, b.qty, p.user_id, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'expired' AND b.bidstatus = 'placed'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'placed'
                                                        OR p.status = 'approval_accepted'AND bidstatus = 'choseanother'
                                                        OR p.status = 'finished' AND bidstatus = 'choseanother'
                                                        OR p.status = 'delisted' AND bidstatus = 'choseanother'
                                                        OR p.status = 'frozen' AND bidstatus = 'choseanother'
                                                        OR p.status = 'archived' AND bidstatus = 'choseanother')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = 0;
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'archived')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT b.bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND b.bidstate = 'archived'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT MAX(b.bid_id) AS bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'wait_approval' AND b.bidstatus = 'placed' AND b.bidstate = 'wait_approval'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'archived' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'closed' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'active')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND b.bidstatus = 'placed'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'serviceescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, b.bid_id, b.user_id AS bidder_id, b.bidamount, b.bidamounttype, b.bidstatus, b.buyerpaymethod, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "users AS u,
                                        " . DB_PREFIX . "projects_escrow AS e,
                                        " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "invoices AS i
                                        WHERE u.user_id = '" . intval($userid) . "'
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND e.user_id = u.user_id
                                                AND e.status != 'cancelled'
                                                AND e.bid_id = b.bid_id
                                                AND e.user_id = b.user_id
                                                AND e.project_id = p.project_id
                                                AND e.invoiceid = i.invoiceid
                                                AND i.invoicetype = 'escrow'
                                                AND p.project_state = 'service'
                                                AND i.projectid = e.project_id
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        return $sqlcount;
                }
                else
                {
                        if ($bidtab == 'delisted')
                        {
                                $query = "
                                        SELECT b.bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'open' AND b.bidstatus = 'declined'
                                                        OR p.status = 'closed' AND b.bidstatus = 'declined'
                                                        OR p.status = 'expired' AND b.bidstatus = 'declined'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'declined'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'archived' AND b.bidstatus = 'declined')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'retracted')
                        {
                                $query = "
                                        SELECT b.bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate = 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'                            
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'invited')
                        {
                                $query = "
                                        SELECT UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.date_starts, p.fvf, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations as i
                                        WHERE i.project_id = p.project_id
                                                $extra
                                                AND i.seller_user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND i.bid_placed = 'no'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'expired')
                        {
                                $query = "
                                        SELECT b.bid_id, b.bidamount, b.estimate_days, b.bidstatus, b.bidstate, b.bidamounttype, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                 AND (p.status = 'expired' AND b.bidstatus = 'placed'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'placed'
                                                        OR p.status = 'approval_accepted' AND bidstatus = 'choseanother'
                                                        OR p.status = 'finished' AND bidstatus = 'choseanother'
                                                        OR p.status = 'delisted' AND bidstatus = 'choseanother'
                                                        OR p.status = 'frozen' AND bidstatus = 'choseanother'
                                                        OR p.status = 'archived' AND bidstatus = 'choseanother')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'archived')
                        {
                                $query = "
                                        SELECT b.bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND b.bidstate = 'archived'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $query = "
                                        SELECT MAX(b.bid_id) AS bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'wait_approval' AND b.bidstatus = 'placed' AND b.bidstate = 'wait_approval'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'archived' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'closed' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'active')
                        {
                                $query = "
                                        SELECT bid_id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND b.bidstatus = 'placed'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        
                        return $query;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>