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
//
//error_reporting(E_ALL);
//require_once('../config.php');
require_once(DIR_CORE . 'functions_search.php');


// #### LAST HOUR AUCTION WATCHLIST NOTIFICATIONS ##############################
$cronlog = '';

 $sql_users = $ilance->db->query("SELECT user_id FROM " . DB_PREFIX . "users
	                          WHERE status = 'active' 
                                ");
if ($ilance->db->num_rows($sql_users) > 0)
{
while($res = $ilance->db->fetch_array($sql_users))
{
	$sql = $ilance->db->query("SELECT d.offer_amt,pr.maxamount,p.project_id,p.project_title,p.project_details,p.filtered_auctiontype,p.user_id as project_user_id,p.buynow_qty,p.buynow_price,p.max_qty,p.bids,p.project_state,p.description,p.status,p.currencyid,p.date_end,p.startprice,p.currentprice,w.user_id,w.watching_project_id,w.comment,(select user_id from  " . DB_PREFIX . "project_bids where p.project_id = project_id  order by bidamount desc,date_added asc limit 1) as winning_user_id
		FROM " . DB_PREFIX . "watchlist w
		left join " . DB_PREFIX . "projects p on w.watching_project_id=p.project_id
		left join " . DB_PREFIX . "dailydeal d ON d.project_id = p.project_id
		left join " . DB_PREFIX . "project_bids b on b.project_id = p.project_id and b.user_id='".$res['user_id']."'
		left join " . DB_PREFIX . "proxybid pr on pr.user_id = '".$res['user_id']."' and pr.project_id = p.project_id
		WHERE p.status = 'open' and w.user_id='".$res['user_id']."'
		AND date(p.date_end)='".DATETODAY."'
		group by w.watching_project_id
		ORDER BY date_end  ASC
							");
		
	if ($ilance->db->num_rows($sql) > 0)
	{
	   $ilance->email = construct_dm_object('email', $ilance);
	   $ilance->auction = construct_object('api.auction');
			
	   while($res_new= $ilance->db->fetch_array($sql))
	   {
				//Auction
				$maxamt = '';
				if($res_new['filtered_auctiontype'] == 'regular')
				{
					$maxbid = $res_new['maxamount'] > 0 ? $res_new['maxamount'] : '';
				   
									   
				   if ($res_new['bids'] > 0) {
							$highbidderid = $res_new['winning_user_id'];
							if ($highbidderid == $res_new['user_id']) {
								$maxamt = "Your Secret Maximum Bid: ".$ilance->currency->format($maxbid)."\n";
							}
							else
							{
							$maxamt = '';
							}
						}
						
				  
				   $bids = ($res_new['bids']>0)?"(".$res_new['bids']." ".$phrase['_bids'].")\n":"\n";
				   $currentbid = "Current Bid: ".$ilance->currency->format($res_new['currentprice'])."";
				   // $currentbid.=$maxamt;
				   $dailydeal_price ='';
				}
				
				//Buynow
				if($res_new['filtered_auctiontype']=='fixed')
				{
											
					 if ($res_new['offer_amt'] > 0) 
					 {
					  $dailydeal_price = " (24 Hour Deal: ".$ilance->currency->format($res_new['buynow_price'])." - Today Only!)\n";
					 }
					 else
					 {
					  $dailydeal_price ="\n";
					  $res_new['offer_amt']='';
					 }	
					 $currentbid = "Buy Now: ".$ilance->currency->format($res_new['buynow_price']+$res_new['offer_amt']).$dailydeal_price;
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
				$ilance->email->send();
										  
				// unset($messagebody);
										  
				} 
											
				// email to admin
				$ilance->email->logtype = 'watchlistremind';
				$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
				$ilance->email->get('cron_auction_notification');		
				$ilance->email->set(array('{{first_name}}' => $first_name, 
						  '{{message}}' => $messagebody,
					   ));
				$ilance->email->send();
				unset($messagebody);
										
		  }
						
						
	}
}
		
	//print_r($new_user);
	$cronlog .= 'Sent auction notifications via email to ' . $sent . ' members';
}

log_cron_action('The following watchlist tasks were executed: ' . $cronlog, $nextitem);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>