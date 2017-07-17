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

($apihook = $ilance->api('cron_dailydeal_start')) ? eval($apihook) : false;

//require_once('../config.php');

// #### NEW REGISTRATIONS ######################################################

 $deal = ilanc->db->query("SELECT * FROM " . DB_PREFIX . "wantlist
 							WHERE live_date LIKE '%" . DATETODAY . "%'
							 ");

if ($ilance->db->num_rows($deal) != 0)
{
	$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "users       
", 0, null, __FILE__, __LINE__);
	while($result = $ilance->db->fetch_array($sql))
	{
	$userid = $result['user_id'];
	$username = $result['first_name'];
// cron logic to ensure daily reports only send once per day
	$sql1 = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "emaillog
        WHERE logtype = 'dailydeals'             
        AND date LIKE '%" . DATETODAY . "%'
", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql1) == 0)
		{
			$ilance->email = construct_dm_object('email', $ilance);
															
			($apihook = $ilance->api('cron_dailydeal_start')) ? eval($apihook) : false;                                                                
															
			// email admin
			$ilance->email->logtype = 'dailydeals';
			$ilance->email->mail = fetch_user('email',$userid);
			$ilance->email->slng = fetch_site_slng();
			
			$ilance->email->get('user_daily_deals');		
			$ilance->email->set(array(
					'{{username}}' => $username,				
					
			));
			
			$ilance->email->send();
			
			($apihook = $ilance->api('cron_dailydeal_end')) ? eval($apihook) : false;			
			
		}
  	}
}
log_cron_action('The Daily Deal Notification successfully emailed to Users ', $nextitem);
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>