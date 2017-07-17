<?php 
//saved_search for one user
require_once('./functions/config.php');
error_reporting(E_ALL);
send_saved_search_subscriptions();
 function send_saved_search_subscriptions($limit = 50)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                if ($ilconfig['savedsearches'] == false)
                {
                        return;
                }
                
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->xml = construct_object('api.xml');
                $ilance->auction = construct_object('api.auction');
                
                // limits results per email (ie: show 50 results in email sent for services)
                $limit = intval($limit); 
                $cronlog = '';
                
                
                if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        $ilance->bid = construct_object('api.bid');
                        $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                        
                        // #### product auctions ###############################
                        // 1. select all subscriptions from search_favorites where subscribed = 1 and lastsent != today for products
                         $query="
                                SELECT searchid, user_id, searchoptions, searchoptionstext, title, added, lastseenids
                                FROM " . DB_PREFIX . "search_favorites
                                WHERE cattype = 'product'
                                        AND subscribed = '1'
										AND user_id=7
                        ";
                        $sql = $ilance->db->query($query);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $lastseen = $lastseenids = $last = array();
                                        
                                        $url = HTTP_SERVER . $ilpage['search'] . '?do=array' . html_entity_decode($res['searchoptions']);
                                        
                                        $c = curl_init();
                                        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($c, CURLOPT_URL, $url);
                                        $results = curl_exec($c);
                                        curl_close($c);
                                        
                                        if (!empty($res['lastseenids']) AND is_serialized($res['lastseenids']))
                                        {
                                                $lastseen = unserialize($res['lastseenids']);
                                        }
                                        
										
                                        if (!empty($results))
                                        {
                                                $results = urldecode($results);
                                                if (is_serialized($results))
                                                {
                                                        $results = unserialize($results);
                                       
                                                        $messagebody = '';
                                                        
                                                        $sent = 0;
                                                        foreach ($results AS $key => $listing)
                                                        { // items found
                                                                foreach ($listing AS $field => $value)
                                                                { // fields
                                                                        if ($field == 'project_id' AND !in_array($value, $lastseen))
                                                                        { // save item id's so we don't resend duplicates in future (on a different day)
                                                                                $lastseenids[] = $value;
                                                                        }
                                                                }
															
                                                                if ($sent <= $limit)
                                                                {
															
                                                                        $messagebody .= "<div style=\"padding-bottom:9px\">******************<div>Item ID :" . $listing['project_id'] . "</div><div>" . strip_vulgar_words(un_htmlspecialchars(stripslashes($listing['title']))) . "</div><div><strong>" . strip_tags($listing['price'], '<p><a>') . "</strong>(".strip_tags($listing['bids'], '<p><a>') .")</div><div>" . $phrase['_time_left'] . ": " . $ilance->auction->auction_timeleft($listing['project_id'], '', '', 0, 0, 1) . "</div></div>";                                                                      
                                                                        
                                                                        $sent++;
                                                                }
                                                        }
                                                }
                                        }
                                        
                                        if (!empty($lastseenids) AND is_array($lastseenids))
                                        {
                                                if (!empty($lastseen) AND is_array($lastseen))
                                                {
                                                        $last = array_merge($lastseenids, $lastseen);
                                                }
                                                else
                                                {
                                                        $last = $lastseenids;
                                                }
                                           //ensure that the user have enabled Email preference for Saved Search Reminders
										   
										  $email_notify = fetch_user('emailnotify',$res['user_id']);
										   
										  $query_saved_search = $ilance->db->query("SELECT wantlist FROM " . DB_PREFIX . "email_preference 
						                                                            WHERE user_id ='" .$res['user_id']. "'");
						
						                   $row_saved_search = $ilance->db->fetch_array($query_saved_search);							
				 
				                           if( $row_saved_search['wantlist'] == '1' AND $email_notify =='1')
						                   {
                                                // dispatch product email
                                                $ilance->email->mail = fetch_user('email', $res['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res['user_id']);
                                                $ilance->email->dohtml = true;
                                                $ilance->email->logtype = 'alert';                                                                                                
                                                $ilance->email->get('cron_send_product_saved_searches');		
                                                $ilance->email->set(array(
                                                        '{{searchtitle}}' => un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => un_htmlspecialchars($res['searchoptionstext']),					  
                                                        '{{username}}' => fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => nl2br($messagebody),
                                                ));                                                
                                                $ilance->email->send();
                                                
												
												echo $ilconfig['globalserversettings_adminemail'];
												$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
												$ilance->email->slng = fetch_user_slng($res['user_id']);
                                                $ilance->email->dohtml = true;
                                                $ilance->email->logtype = 'alert';                                                                                                
                                                $ilance->email->get('cron_send_service_saved_searches');		
                                                $ilance->email->set(array(
                                                        '{{searchtitle}}' => un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => un_htmlspecialchars($res['searchoptionstext']),					  
                                                        '{{username}}' => fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => nl2br($messagebody),
                                                ));                                                
                                                $ilance->email->send();
												
												
                                                $last = serialize($last);
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "search_favorites
                                                        SET lastseenids = '" . $ilance->db->escape_string($last) . "',
                                                        lastsent = '" . DATETIME24H. "'
                                                        WHERE searchid = '" . $res['searchid'] . "'
                                                ");
												
											}	
                                        }
                                }
                        }
                }
                
                return $cronlog;
        }
       
?>