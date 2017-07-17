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

// if (!isset($GLOBALS['ilance']->db))
// {
        // die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
// }

//($apihook = $ilance->api('cron_consignment_start')) ? eval($apihook) : false;

require_once('../config.php');

// #### NEW REGISTRATIONS ######################################################
 $consign = $ilance->db->query("select CONVERT(GROUP_CONCAT(consignid SEPARATOR ',')USING utf8) as consign_ids,receive_date,user_id,sum(coins) 
							as coins FROM " . DB_PREFIX . "consignments 
							WHERE receive_date = '" . DATEYESTERDAY . "' GROUP BY user_id ");

if ($ilance->db->num_rows($consign) > 0)
{	
	while($result = $ilance->db->fetch_array($consign))
	{	
		$userid = $result['user_id'];
		$username =fetch_user('username',$result['user_id']);
		$receicedate = $result['receive_date'];
		$coins = $result['coins'];
		
		echo '<pre>';
		print_r($result);

		
		//ensure that the user have enabled Email preference for Consignment Related, Items received 
		
		$email_notify = fetch_user('emailnotify',$userid);
		
		$query_consign1 = $ilance->db->query("SELECT related FROM " . DB_PREFIX . "email_preference 
																WHERE user_id ='".$userid."'");
		$row_consign1 = $ilance->db->fetch_array($query_consign1);							
					 
		if( $row_consign1['related'] == '1' AND $email_notify =='1')
		
		{							
		// cron logic to ensure daily reports only send once per day
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "emaillog
				WHERE  user_id = '".$userid."'
				AND logtype = 'consignments'
					AND date LIKE '%" . DATETODAY . "%'
		      ", 0, null, __FILE__, __LINE__);
		      if ($ilance->db->num_rows($sql) >= 0)
		      {
					$ilance->email = construct_dm_object('email', $ilance);
																	
					//($apihook = $ilance->api('cron_consignment_start')) ? eval($apihook) : false;                                                                
																	
					// email admin
					$ilance->email->logtype = 'consignments';
					$ilance->email->mail = $ilconfig['globalserversettings_developer_email'];
					$ilance->email->slng = fetch_site_slng();
					
					$ilance->email->get('user_consignments');		
					$ilance->email->set(array(
							'{{username}}' => $username,				
							'{{receivedate}}' => $receicedate,
							'{{coins}}' => $coins,
					));
					
					$ilance->email->send();
					
					//($apihook = $ilance->api('cron_consignment_end')) ? eval($apihook) : false;			
				}	
		 }	
				 
  	}
}
echo 'sent mail';
					exit;
exit;



log_cron_action('The Consignement Related Notification successfully emailed to Users ', $nextitem);
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>