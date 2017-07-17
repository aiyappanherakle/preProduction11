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

//require_once('../config.php');

($apihook = $ilance->api('cron_consignment_start')) ? eval($apihook) : false;
 
 
$consign1 = $ilance->db->query("select CONVERT(GROUP_CONCAT(coins SEPARATOR ',')USING utf8) as consign_coins,CONVERT(GROUP_CONCAT(consign_type SEPARATOR ',')USING utf8) as consign_types, receive_date,user_id FROM " . DB_PREFIX . "consignments WHERE receive_date = '" . DATEYESTERDAY . "' GROUP BY user_id ");
	
if ($ilance->db->num_rows($consign1) > 0)
{	
	$consign_typess = array(1=>'Certified Coins',2=>'Uncertified Coins',3=>'Certified Currency',4=>'Uncertified Currency',0=>'Other Items');
	while($result1 = $ilance->db->fetch_array($consign1))
	{	
		$userid1 = $result1['user_id'];
		$username1 =fetch_user('username',$result1['user_id']);
		$receicedate1 = $result1['receive_date'];
		
		$coins1 = $result1['consign_coins'];
		$coin1 = explode(",", $coins1);
		$consign_types1 = $result1['consign_types'];
		$consign_type1 = explode(",", $consign_types1);
		
		$coins_type = array();
		$coins_total =  array(0,0,0,0,0);
		foreach($coin1 as $indx=>$val)
		{
			 
			if($consign_type1[$indx] == 1 && $val>0)
			{
				$coins_total[1] = $coins_total[1]+$val;
			}
			else if($consign_type1[$indx] == 2 && $val>0)
			{
				$coins_total[2] = $coins_total[2]+$val;
			}
			else if($consign_type1[$indx] == 3 && $val>0)
			{
				$coins_total[3] = $coins_total[3]+$val;
			}
			else if($consign_type1[$indx] == 4 && $val>0)
			{
				$coins_total[4] = $coins_total[4]+$val;
			}
			else if($consign_type1[$indx] == 0 && $val>0)
			{
				$coins_total[0] = $coins_total[0]+$val;
			}
			else
			{
				
			}
		}
	//	echo '<pre>';print_r($coins_total);exit;
		foreach($coins_total as $keyy=>$vals)
		{
			if($vals > 0)
			{
				$coins_type[] = $vals.' '.$consign_typess[$keyy];
			}
		}
		//exit;
		$email_text = implode(", ", $coins_type);
	
		$email_notify1 = fetch_user('emailnotify',$userid1);
		
		$query_consign2 = $ilance->db->query("SELECT related FROM " . DB_PREFIX . "email_preference 
																WHERE user_id ='".$userid1."'");
		$row_consign2 = $ilance->db->fetch_array($query_consign2);							
					 
		if( $row_consign2['related'] == '1' AND $email_notify1 =='1')
		{							
		// cron logic to ensure daily reports only send once per day
			$sql1 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "emaillog
				WHERE  user_id = '".$userid1."'
				AND logtype = 'consignments'
					AND date LIKE '%" . DATETODAY . "%'
		      ", 0, null, __FILE__, __LINE__);
		      if ($ilance->db->num_rows($sql1) >= 0)
		      {
					$ilance->email = construct_dm_object('email', $ilance);
					
					//echo '<br/><br/>'.$email_text. ' = '.$userid1;
					
					// email developer test
					$ilance->email->logtype = 'consignments';
					$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('user_consignments');		
					$ilance->email->set(array(
							'{{username}}' => $username1,				
							'{{receivedate}}' => $receicedate1,
							'{{coins}}' => $email_text,
							'{{user_id}}' => $userid1,
					));
					
					$ilance->email->send();
					
					// email ian test
					$ilance->email->logtype = 'consignments';
					$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('user_consignments');		
					$ilance->email->set(array(
							'{{username}}' => $username1,				
							'{{receivedate}}' => $receicedate1,
							'{{coins}}' => $email_text,
							'{{user_id}}' => $userid1,
					));
					
					$ilance->email->send();
					
					// email admin
					$ilance->email->logtype = 'consignments';
					$ilance->email->mail = fetch_user('email',$userid1);
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('user_consignments');		
					$ilance->email->set(array(
							'{{username}}' => $username1,				
							'{{receivedate}}' => $receicedate1,
							'{{coins}}' => $email_text,
							'{{user_id}}' => $userid1,
					));
					$ilance->email->send();
					
					($apihook = $ilance->api('cron_consignment_end')) ? eval($apihook) : false;			
				}	
		 }	
				 
  	}
}




//for bug #4292 end


$deal = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects
 							WHERE date(date_end) = '". DATEYESTERDAY . "'
							AND status != 'open'
							AND (haswinner = '1' OR hasbuynowwinner = '1')
							GROUP BY user_id
							 ");

if ($ilance->db->num_rows($deal) > 0)
{	
	while($result = $ilance->db->fetch_array($deal))
	{
	
	$userid = $result['user_id'];
	$username =fetch_user('username',$result['user_id']);
	$title = $result['project_title'];
	$price = $result['current_price'];
	
// cron logic to ensure daily reports only send once per day
	$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "emaillog
        WHERE  user_id = '".$userid."'
		AND logtype = 'itemsell'
            AND date LIKE '%" . DATETODAY . "%'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) == 0)
{
			$ilance->email = construct_dm_object('email', $ilance);
															
			($apihook = $ilance->api('cron_consignment_start')) ? eval($apihook) : false;                                                                
															
			// email admin
			$ilance->email->logtype = 'itemsell';
			$ilance->email->mail = fetch_user('email',$userid);
			$ilance->email->slng = fetch_site_slng();
			
			$ilance->email->get('user_itemsoldlist');		
			$ilance->email->set(array(
					'{{username}}' => $username,				
					'{{projecttile}}' => $title,
					'{{price}}' => $price,
					'{{user_id}}' => $userid,
			));
			
			$ilance->email->send();
			
			($apihook = $ilance->api('cron_consignment_end')) ? eval($apihook) : false;	
	}		
			
		
  	}
}


$project = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects
 							WHERE date(date_starts) = '". DATEYESTERDAY . "'
							AND status = 'open'
							AND visible = '1'
							GROUP BY user_id							
							 ");

if ($ilance->db->num_rows($project) > 0)
{	
	while($result = $ilance->db->fetch_array($project))
	{
		$projectsel = $ilance->db->query("SELECT project_id, project_title, currentprice FROM " . DB_PREFIX . "projects
 							WHERE date(date_starts) = '". DATEYESTERDAY . "'
							AND status = 'open'
							AND visible = '1'
							AND user_id = '".$result['user_id']."'							
							 ");
		while($respjt = $ilance->db->fetch_array($projectsel))
		{
			
			$message = '<b>Item Title: </b>'.fetch_auction('project_title',$respjt['project_id']). '<br>' .'<b>Item Current Bid: </b>'.fetch_auction('currentprice',$respjt['project_id']). '<br>' . "\r\n"; 
		}					 
	$userid = $result['user_id'];
	$username =fetch_user('username',$result['user_id']);
	$title = $result['project_title'];
	$price = $result['currentprice'];
	
// cron logic to ensure daily reports only send once per day
	$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "emaillog
        WHERE  user_id = '".$userid."'
		AND logtype = 'itempost'
            AND date LIKE '%" . DATETODAY . "%'
", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) == 0)
	{
				$ilance->email = construct_dm_object('email', $ilance);
																
				($apihook = $ilance->api('cron_consignment_start')) ? eval($apihook) : false;                                                                
																
				// email admin
				$ilance->email->logtype = 'itempost';
				$ilance->email->mail = fetch_user('email',$userid);
				$ilance->email->slng = fetch_site_slng();
				
				$ilance->email->get('user_itempostlist');		
				$ilance->email->set(array(
						'{{username}}' => $username,				
						'{{projecttile}}' => $title,
						'{{price}}' => $price,
						'{{message}}' => $message,
						'{{user_id}}' => $userid,
				));
				
				$ilance->email->send();
				
				($apihook = $ilance->api('cron_consignment_end')) ? eval($apihook) : false;			
		}		
		
  	}
}


log_cron_action('The Consignement Related Notification successfully emailed to Users ', $nextitem);
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>