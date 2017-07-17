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
* Subscription class to perform the majority of subscription functionality in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class vjsub
{

function send_category_notification_subscriptions()
        {
            global $ilance, $ilconfig, $phrase, $ilpage;
            
            $ilance->email = construct_dm_object('email', $ilance);
            
            $cronlog = '';
            
            if ($ilconfig['globalauctionsettings_productauctionsenabled'])
            {
                    
                $new_projects_array = $sellers = $seller_array = $emailsDuplicatePrevention = array();
                    
                // fetch service auctions all bid coins
                $newprojects = $ilance->db->query("
                        SELECT count(p.project_id) as projects_count, p.coin_series_unique_no,
                        p.coin_series_denomination_no, c.coin_series_name, t.denomination_long FROM " . DB_PREFIX . "projects p
                        LEFT JOIN " . DB_PREFIX . "catalog_toplevel t ON p.coin_series_denomination_no = t.denomination_unique_no
                        LEFT JOIN " . DB_PREFIX . "catalog_second_level c ON p.coin_series_unique_no = c.coin_series_unique_no
                        WHERE p.status = 'open'
                            AND p.project_details != 'invite_only'
                            AND p.project_state = 'product'
                            AND p.visible = '1'
                            AND p.date_end >= '".date('Y-m-d H:i:s')."'
                            GROUP BY p.coin_series_unique_no
                ", 0, null, __FILE__, __LINE__);

                while ($row = $ilance->db->fetch_array($newprojects, DB_ASSOC))
                {
                    $key = $row['coin_series_unique_no'];
                    $new_projects_array[$key] = $row;
                }
                   
                // fetch sellers with active category subscriptions   
                $users = $ilance->db->query("
                        SELECT user_id, username, email, notifyproductscats, country, zip_code, city
                        FROM " . DB_PREFIX . "users
                        WHERE status = 'active'
                            AND notifyproducts = '1'
                            AND notifyproductscats != ''
                            AND emailnotify = '1'
                            AND email != ''
                ", 0, null, __FILE__, __LINE__);    

                        
                if ($ilance->db->num_rows($users) > 0)
                {
                    while ($row = $ilance->db->fetch_array($users, DB_ASSOC))
                    {
                            if (!in_array($row['email'], $emailsDuplicatePrevention))
                            {
                                    $sellers[] = $row;
                                                                                    
                                    $emailsDuplicatePrevention[] = $row['email'];
                            }
                            
                           
                    }
                    unset($row);
                    //$ilance->categories_parser = construct_object('api.categories_parser');
                    if (!empty($sellers) AND count($sellers) > 0)
                    {
                        $sent = 0;

                        foreach ($sellers AS $seller)
                        {
                            $messagebody = '';
                            $requested_categories = explode(',', $seller['notifyproductscats']);
                            $projectsToSend = array();
                                                
                            foreach ($requested_categories AS $categories)
                            {   
                               if($categories>0 AND $categories !='')
                               {
                                    if(isset($new_projects_array[$categories]))
                                    {
                                        $projectsToSend[] = $new_projects_array[$categories];
                                    }
                               }
                            }
                            
                            $count_prj = 0;           
                            if (count($projectsToSend) > 0)
                            {
                                $denom = array(); 
                                foreach ($projectsToSend AS $project)
                                { 
                                    $count_prj = $count_prj+$project['projects_count'];
                                    if(!in_array($project['coin_series_denomination_no'],$denom)) 
                                    {
                                        if(count($denom)>0)
                                        {
                                            $messagebody .= "===========================\n";
                                        }

                                        $denom[] = $project['coin_series_denomination_no'];
                                       
                                        $messagebody .= "\n".un_htmlspecialchars(stripslashes($project['denomination_long']))." \n"; 

                                        $text_under = '';
                                        for($i=0; $i<strlen($project['denomination_long']); $i++)
                                        {
                                            $text_under .= '*';
                                        }
                                         $messagebody .= $text_under."\n\n";
                                    }


                                    $messagebody .= un_htmlspecialchars(stripslashes($project['coin_series_name'])) ." (".$project['projects_count']." Listings) \n"; 
                                    $messagebody .= "Link: ".HTTP_SERVER . 'Series/' . $project['coin_series_unique_no'] . '/'.construct_seo_url_name($project['coin_series_name'])."\n\n";
                                    
                                }
                                                        
                                $messagebody .= "\n";
                                $messagebody .= $phrase['_sell_merchandise_via_product_auctions'] . ":\n";
                                $messagebody .= HTTP_SERVER . "main.php?cmd=selling\n\n";
                                $messagebody .= $phrase['_browse_product_auctions_and_other_merchandise'] . ":\n";
                                $messagebody .= HTTP_SERVER . "merch.php?cmd=listings\n\n";
                                $messagebody .= "************\n";
                                $messagebody .= $phrase['_please_contact_us_if_you_require_any_additional_information_were_always_here_to_help'];
                                                        
                                
                                $ilance->email->mail = 'nataraj@herakle.com';
                                $ilance->email->slng = fetch_user_slng($seller['user_id']);                                                                
                                $ilance->email->get('cron_daily_auction_newsletter');       
                                $ilance->email->set(array(
                                        '{{newsletterbody}}' => $messagebody,
                                        '{{total}}' => $count_prj,
                                ));                                                                
                                $ilance->email->send();

                            }
                        }
                    }
                    unset($sellers);
                    
                    
                    unset($sent);
                }
            }
                
                return $cronlog;
        }
 }
 
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
