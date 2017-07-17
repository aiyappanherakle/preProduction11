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
* Auction tabs class to perform the majority of printing and displaying of auction tabs within the MyCP areas of the front end.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class auction_tabs extends auction
{
        /**
        * Function to print sql code based on specific auction tabs being called to this function ultimately
        * saving many lines of code within the main php script files.
        *
        * @param       string       tab to process
        * @param       string       type of tab to process (actual count or sql string)
        * @param       integer      user id
        * @param       string       extra sql query (for listing period control)
        *
        * @return      string       count result of sql or sql string itself
        */
        function product_auction_tab_sql($tab, $countorstring, $userid, $extra = '')
        {
                global $ilance, $myapi, $ilconfig;

                if ($countorstring == 'count')
                {
                        if ($tab == 'drafts')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'delisted')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'delisted'
                                                AND p.user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'archived')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'archived'
                                                AND p.user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'expired')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'expired'
                                                AND p.user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'pending')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.user_id = '" . intval($userid) . "'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0'))" : "AND p.visible = '0'") . "
                                            
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'active')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.status != 'archived'
                                                AND p.status != 'delisted'
                                                AND p.status != 'expired'
                                                AND p.status != 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0'))" : "AND p.visible = '1'") . "
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'sold')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND (p.haswinner = '1' OR p.hasbuynowwinner = '1')
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0'))" : "AND p.visible = '1'") . "
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'productescrow')
                        {
                                $extra = str_replace('date_added', 'p.date_added', $extra);
                                
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                    SELECT p.project_id
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
                                                AND i.projectid = e.project_id
                                         AND p.project_state = 'product'
                                ", 0, null, __FILE__, __LINE__);
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
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'delisted')
                        {
                                $query = "
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'delisted'
                                                AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'archived')
                        {
                                $query = "
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'archived'
                                                AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'expired')
                        {
                                $query = "
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'expired'
                                                AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'pending')
                        {
                                $query = "
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.user_id = '" . intval($userid) . "'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0'))" : "AND p.visible = '0'") . "
                                ";
                        }
                        else if ($tab == 'active')
                        {
                                $query = "
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.status != 'archived'
                                                AND p.status != 'delisted'
                                                AND p.status != 'expired'
                                                AND p.status != 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0'))" : "AND p.visible = '1'") . "
                                ";
                        }
                        else if ($tab == 'sold')
                        {
                                $query = "
                                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND (p.haswinner = '1' OR p.hasbuynowwinner = '1')
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0'))" : "AND p.visible = '1'") . "
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