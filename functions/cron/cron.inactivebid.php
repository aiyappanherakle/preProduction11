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

($apihook = $ilance->api('cron_inactive_start')) ? eval($apihook) : false;

//require_once('../config.php');

// #### NEW REGISTRATIONS ######################################################

$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "users
		WHERE status ='active' and date(lastseen) = '".THIRTYDAYSAGO."' 
		OR date(lastseen) = '".NINETYDAYSAGO."'    
		OR date(lastseen) = '".ONEEIGHTYDAYSAGO."'
		OR date(lastseen) = '".THREESIXTYDAYSAGO."'
", 0, null, __FILE__, __LINE__);

	if($ilance->db->num_rows($sql) > 0)
	{
		while($result = $ilance->db->fetch_array($sql))
		{
		 	$userid = $result['user_id'];
		   	$username = $result['first_name'];
		


			$sql1 = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "emaillog
			WHERE user_id = '".$userid."'
			AND logtype = 'inactivebid'             
			AND date LIKE '%" . DATETODAY . "%'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql1) == 0)
			{
				$email_notify = fetch_user('emailnotify', $userid);
				$query_offer_confirmation = $ilance->db->query("SELECT gcoffer FROM " . DB_PREFIX . "email_preference 
						                                                            WHERE user_id ='" . intval($userid) . "'");
						
				$row_offer_confirmation = $ilance->db->fetch_array($query_offer_confirmation);							
				 
				if( $row_offer_confirmation['gcoffer'] == '1' AND $email_notify=='1')
				{
				$ilance->email = construct_dm_object('email', $ilance);
																
				($apihook = $ilance->api('cron_inactive_start')) ? eval($apihook) : false;                                                                
																
				// email admin
				$ilance->email->logtype = 'inactivebid';
				$ilance->email->mail = fetch_user('email',$userid);
				$ilance->email->slng = fetch_site_slng();
				
				$ilance->email->get('user_inactive');		
				$ilance->email->set(array(
						'{{username}}' => $username,				
						
				));
				
				$ilance->email->send();
			}
				($apihook = $ilance->api('cron_inactive_end')) ? eval($apihook) : false;			
				
			}
		}
	}


log_cron_action('The In Active Bid  Notification successfully emailed to Users ', $nextitem);
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>