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
* Watchlist class to perform the majority of watchlist and notification tasks within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class watchlist
{
        /*
        * Function to send a watchlist notification based on a particular notification type
        *
        * @param       
        *
        * @return      
        */
        function send_notification($bidderid = 0, $type = 'lowbidnotify', $id = 0, $bidamount = '')
        {
                global $ilance, $myapi, $ilconfig;
                
                $ilance->email = construct_dm_object('email', $ilance);
                
                if ($type == 'lowbidnotify')
                {
                        // select all bidders that are watching this auction with lowbidnotify enabled
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE lowbidnotify = '1'
                                    AND subscribed = '1'
                                    AND watching_project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($bidders = $ilance->db->fetch_array($sql))
                                {
                                        $sql_low = $ilance->db->query("
                                                SELECT bidamount
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($id) . "'
                                                    AND user_id = '" . $bidders['user_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_low) > 0)
                                        {
                                                $result_low = $ilance->db->fetch_array($sql_low);
                                                if (isset($bidamount) AND $bidamount < $result_low['bidamount'])
                                                {
                                                        // new bid is lower than this bidders
                                                        $sel_email = $ilance->db->query("
                                                                SELECT email
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . $bidders['user_id'] . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sel_email) > 0)
                                                        {
                                                                while ($result_email = $ilance->db->fetch_array($sel_email))
                                                                {
                                                                        $ilance->email->mail = $result_email['email'];
                                                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                                        
                                                                        $ilance->email->get('lower_bid_notification_alert');		
                                                                        $ilance->email->set(array(
                                                                                '{{p_id}}' => $id,
                                                                                '{{site_name}}' => SITE_NAME,					  
                                                                                '{{site_email}}' => SITE_EMAIL,
                                                                                '{{site_phone}}' => SITE_PHONE,
                                                                                '{{site_address}}' => SITE_ADDRESS,
                                                                                '{{http_server_admin}}' => HTTP_SERVER_ADMIN,
                                                                                '{{https_server_admin}}' => HTTPs_SERVER_ADMIN,
                                                                        ));
                                                                        
                                                                        $ilance->email->send();
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
                else if ($type == 'highbidnotify')
                {
                        // select all bidders that are watching this auction with lowbidnotify enabled
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE highbidnotify = '1'
                                    AND subscribed = '1'
                                    AND watching_project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($bidders = $ilance->db->fetch_array($sql))
                                {
                                        // fetch last bid placed by this user
                                        $sql_high = $ilance->db->query("
                                                SELECT bidamount
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($id) . "'
                                                    AND user_id = '" . $bidders['user_id'] . "'
                                                ORDER BY bid_id DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_high) > 0)
                                        {
                                                $result = $ilance->db->fetch_array($sql_high);
                                                if (isset($bidamount) AND $bidamount > $result['bidamount'])
                                                {
                                                        // new bid is higher than this bidders last bid
                                                        $sel_email = $ilance->db->query("
                                                                SELECT email
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . $bidders['user_id'] . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sel_email) > 0)
                                                        {
                                                                while ($result_email = $ilance->db->fetch_array($sel_email))
                                                                {
                                                                        $ilance->email->mail = $result_email['email'];
                                                                        $ilance->email->slng = fetch_user_slng($bidders['user_id']);
                                                                        
                                                                        $ilance->email->get('higher_bid_notification_alert');		
                                                                        $ilance->email->set(array(
                                                                                '{{p_id}}' => $id,
                                                                                '{{site_name}}' => SITE_NAME,					  
                                                                                '{{site_email}}' => SITE_EMAIL,
                                                                                '{{site_phone}}' => SITE_PHONE,
                                                                                '{{site_address}}' => SITE_ADDRESS,
                                                                                '{{http_server_admin}}' => HTTP_SERVER_ADMIN,
                                                                                '{{https_server_admin}}' => HTTPs_SERVER_ADMIN,
                                                                        ));
                                                                        
                                                                        $ilance->email->send();
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }

        /*
        * Function to insert/add a new watchlist entry
        *
        * @param       
        *
        * @return      
        */
        function insert_item($userid = 0, $watchingid = 0, $watchtype = '', $comment = '', $lowbidnotify = 0, $highbidnotify = 0, $hourleftnotify = 0, $subscribed = 0)
        {
                global $ilance, $myapi, $phrase, $ilconfig;
                
                if ($watchingid > 0 AND $userid > 0)
                {
                        
						
						//Tamil - Commented the following for bug 2433
						/* if (empty($comment))
                        {
                                $comment = $phrase['_added'] . ' ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        } */
                        
                        if ($watchtype == 'mprovider')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'mprovider'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_user_id, comment, state)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'mprovider')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        return true;
                                }
                                else
                                {
                                        return false;
                                }
                        }
                        else if ($watchtype == 'sprovider')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'sprovider'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_user_id, comment, state)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'sprovider')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        return true;
                                }
                                else
                                {
                                        return false;
                                }
                        }
                        else if ($watchtype == 'buyer')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'buyer'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_user_id, comment, state)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'buyer')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        return true;
                                }
                                else
                                {
                                        return false;
                                }
                        }
                        else if ($watchtype == 'auction')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_project_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'auction'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT ignore INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_project_id, comment, state)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'auction')
                                        ", 0, null, __FILE__, __LINE__);
										
										//jai work for watchlist_log
										
										$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist_log
                                                (watchlistid, user_id, watching_project_id, comment, state)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'auction')
                                        ", 0, null, __FILE__, __LINE__);
										
										
                                        $insertid = $ilance->db->insert_id();
                                }
                                else
                                {
                                         while ($res = $ilance->db->fetch_array($sql))
                                         {
                                               $insertid = $res['watchlistid'];
                                         }
                                }
                                
                                // notification elements
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET lowbidnotify = '" . $lowbidnotify . "',
                                        highbidnotify = '" . $highbidnotify . "',
                                        hourleftnotify = '" . $hourleftnotify . "',
                                        subscribed = '" . $subscribed . "'
                                        WHERE watchlistid = '" . intval($insertid) . "'
                                        LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                
								//jai work for watchlist_log
								$ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist_log
                                        SET lowbidnotify = '" . $lowbidnotify . "',
                                        highbidnotify = '" . $highbidnotify . "',
                                        hourleftnotify = '" . $hourleftnotify . "',
                                        subscribed = '" . $subscribed . "'
                                        WHERE watchlistid = '" . intval($insertid) . "'
                                        LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
								
                                return true;  
                        }
                        else if ($watchtype == 'category')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_category_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'category'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_category_id, comment, state)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'cat')
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        return true;
                                }
                                else
                                {
                                        return false;
                                }
                        }
                }
                else
                {
                        return false;
                }
        }
        
        /*
        * Function to update a watchlist entry
        *
        * @param       
        *
        * @return      
        */
        function update_item($userid = 0, $watchingid = 0, $watchtype = '', $comment = '')
        {
                global $ilance, $myapi, $phrase, $ilconfig;
                
                if ($watchingid > 0 AND $userid > 0 AND !empty($watchtype))
                {
                       /* jai work for bug id 2505
					   if (empty($comment))
                        {
                                $comment = $phrase['_added'] . ' ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        }
                        else
                        {
                                $comment = $comment;
                        } */
                       

                      $comment = $comment;
					   
                        if ($watchtype == 'mprovider')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'mprovider'
                                ", 0, null, __FILE__, __LINE__);
                                
								//jai work for watchlist_log
								
								$ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist_log
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'mprovider'
                                ", 0, null, __FILE__, __LINE__);
								
								
                                return true;
                        }
                        else if ($watchtype == 'sprovider')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'sprovider'
                                ", 0, null, __FILE__, __LINE__);
                               
                                 //jai work for watchlist_log
							   
							   $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist_log
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'sprovider'
                                ", 0, null, __FILE__, __LINE__);
							   
                                return true;
                        }
                        else if ($watchtype == 'buyer')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'buyer'
                                ", 0, null, __FILE__, __LINE__);
                                
								 //jai work for watchlist_log
								 
								$ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist_log
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'buyer'
                                ", 0, null, __FILE__, __LINE__);
								
                                return true;
                        }
                        else if ($watchtype == 'auction')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_project_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'auction'
                                ", 0, null, __FILE__, __LINE__);
                            
                         //jai work for watchlist_log
							
							$ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist_log
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_project_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'auction'
                                ", 0, null, __FILE__, __LINE__);
								
                                return true;
                        }
                        else if ($watchtype == 'category')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_category_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'cat'
                                ", 0, null, __FILE__, __LINE__);
                           
                          //jai work for watchlist_log
						   
						    $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist_log
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_category_id = '" . intval($watchingid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND state = 'cat'
                                ", 0, null, __FILE__, __LINE__);
						   
                                return true;
                        }
                }
                else
                {
                        return false;
                }
        }
        
        /*
        * Function to determine if a user is watching a particular auction
        *
        * @param       integer          auction id
        *
        * @return      boolean          Returns true or false   
        */
        function is_listing_added_to_watchlist($auctionid = 0)
        {
                global $ilance;
                
                // is added to watchlist?
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                    AND watching_project_id = '" . intval($auctionid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                return true;
                        }
                }
                
                return false;
        }
        
        /*
        * Function to determine if a user is watching a particular seller
        *
        * @param       integer          seller id
        *
        * @return      boolean          Returns true or false   
        */
        function is_seller_added_to_watchlist($userid = 0)
        {
                global $ilance;
                
                // is added to watchlist?
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                    AND watching_user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                return true;
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
