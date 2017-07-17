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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'search',
	'stores',
	'wantads',
	'subscription',
	'preferences',
	'javascript',
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'search',
	'tabfx',
	'jquery',
	'modal',
	'yahoo-jar',
	'flashfix',
);

// #### define top header nav ##################
$topnavlink = array(
	'main_listings',
);

// #### setup script location ##################################################
define('LOCATION', 'search');

// #### require backend ########################################################
require_once './functions/config.php';
global $ilance,$ilconfig;
require_once(DIR_CORE . 'functions_search.php');

$sql_users = $ilance->db->query("SELECT user_id FROM " . DB_PREFIX . "users
	                          WHERE  `user_id` IN ( 19894, 28 ) 
                                ");
	
 if ($ilance->db->num_rows($sql_users) > 0)
 {
    while($res = $ilance->db->fetch_array($sql_users))
    {
					
			$sql = $ilance->db->query("SELECT p.project_id,w.user_id,p.project_title,p.currentprice,p.bids,w.watching_project_id,p.date_end,p.filtered_auctiontype,p.buynow_price,w.comment
		FROM " . DB_PREFIX . "projects p," . DB_PREFIX . "watchlist w
		WHERE  w.user_id='".$res['user_id']."' 
		AND w.state = 'auction'
		AND date(p.date_end)='2015-10-11'
		AND p.status='open'
		AND w.watching_project_id = p.project_id
						            ");
			
			if ($ilance->db->num_rows($sql) > 0)
			{
			   $ilance->email = construct_dm_object('email', $ilance);
			   $ilance->auction = construct_object('api.auction');
					
			   while($res_new= $ilance->db->fetch_array($sql))
			   {
				 				 
				//Auction
				if($res_new['filtered_auctiontype'] == 'regular')
				{
				   $maxprice =  $ilance->db->query("SELECT maxamount FROM " . DB_PREFIX . "proxybid 
									WHERE user_id = '" . $res_new['user_id'] . "'
									AND project_id = '" . $res_new['project_id'] . "'
								  ");		
				   if($ilance->db->num_rows($maxprice)>0)
				   {
					$max = $ilance->db->fetch_array($maxprice);
					$maxamt = "Your Secret Maximum Bid: ".$ilance->currency->format($max['maxamount'])."\n"; 
				   }
				   else
				   {
					$maxamt = '';
				   }
				  
				   $bids = ($res_new['bids']>0)?"(".$res_new['bids']." ".$phrase['_bids'].")\n":"\n";
				   $currentbid = "Current Bid: ".$ilance->currency->format($res_new['currentprice'])."";
				   // $currentbid.=$maxamt;
				   $dailydeal_price ='';
				}
				//Buynow
				else
				{
					 $daily_deal = $ilance->db->query("SELECT offer_amt FROM " . DB_PREFIX . "dailydeal 
									   WHERE project_id = '" . $res_new['project_id'] . "'
									   AND date(live_date) = '".DATETODAY."'
									 ");										
																
					 if($ilance->db->num_rows($daily_deal)>0)
					 {
					  $daily_deal_row = $ilance->db->fetch_array($daily_deal);
					  $dailydeal_price = " (24 Hour Deal: ".$ilance->currency->format($res_new['buynow_price'])." - Today Only!)\n";
					 }
					 else
					 {
					  $dailydeal_price ="\n";
					  $daily_deal_row['offer_amt']='';
					 }	
					 $currentbid = "Buy Now: ".$ilance->currency->format($res_new['buynow_price']+$daily_deal_row['offer_amt']).$dailydeal_price;
					 $bids = '';
				}
				 
							
				$user_id = $res_new['user_id'];
				$arr1[]=$res_new['project_id'];
					
				$messagebody .= "Item ID: ". $res_new['project_id'] . "\n";
				$messagebody .= "Item Title: " . $res_new['project_title'] . "\n";
				//$messagebody .= $bids;
				$messagebody .= $currentbid." ".$bids;
				$messagebody .=$maxamt;
				$messagebody .= "Ends: " . date("D, M d, Y h:i:s A",strtotime($res_new['date_end'])) . "  Pacific Time"."\n\n"; 
				$messagebody .= "Your Personal Note:".$res_new['comment']."\n\n"; 

			   }
			   
										
			   $arr2=array_filter($arr1);	
			   $imp=implode(',',$arr2);
						   
				   $emailcheck = $ilance->db->query("SELECT emaillogid FROM " . DB_PREFIX . "emaillog
													 WHERE logtype = 'watchlistremind'
													 AND user_id = '" .  $user_id  . "'
													 AND project_id in( " . $imp . ")                                          
												   ", 0, null, __FILE__, __LINE__);
				   if ($ilance->db->num_rows($emailcheck) == 0)
				   {
					$email_notify = fetch_user('emailnotify',$user_id);
					$query_watch = $ilance->db->query("SELECT itemtracked FROM " . DB_PREFIX . "email_preference 
								   WHERE user_id ='".$user_id."'
								 ");
					$row_watchlist = $ilance->db->fetch_array($query_watch);							
					$first_name=fetch_user('first_name',$user_id);				 
					if($row_watchlist['itemtracked'] == '1' AND $email_notify =='1')
					{		
					$ilance->email = construct_dm_object('email', $ilance);
												
					//email to user
					$ilance->email->logtype = 'watchlistremind';								
					$ilance->email->mail = fetch_user('email', $user_id);
					$ilance->email->slng = fetch_user_slng($user_id);
					$ilance->email->get('cron_auction_notification');		
					$ilance->email->set(array('{{first_name}}' => $first_name, 
								  '{{message}}' => $messagebody,
							   ));
					// $ilance->email->send();
											  
					// unset($messagebody);
											  
					} 
					

					}
					// email to admin
					$ilance->email = construct_dm_object('email', $ilance);
					$ilance->email->logtype = 'watchlistremind';
					$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
					$ilance->email->get('cron_auction_notification');		
					$ilance->email->set(array('{{first_name}}' => $first_name, 
							  '{{message}}' => $messagebody,
						   ));
					$ilance->email->send();
					unset($messagebody);
												
					
								
								
			}
    }
		
	
		
}



 
?>`