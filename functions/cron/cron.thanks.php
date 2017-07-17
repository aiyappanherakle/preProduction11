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

($apihook = $ilance->api('cron_thanks_start')) ? eval($apihook) : false;

//require_once('../config.php');

// #### NEW REGISTRATIONS ######################################################

 
 $thanks = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids
 							WHERE bidstatus = 'awarded'
							 ");
 
if ($ilance->db->num_rows($onedayago) > 0)
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
        WHERE logtype = 'thanks'             
        AND date LIKE '%" . DATETODAY . "%'
", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql1) == 0)
		{
			$ilance->email = construct_dm_object('email', $ilance);
															
			($apihook = $ilance->api('cron_thanks_start')) ? eval($apihook) : false;                                                                
															
			// email admin
			$ilance->email->logtype = 'thanks';
			$ilance->email->mail = fetch_user('email',$userid);
			$ilance->email->slng = fetch_site_slng();
			
			$ilance->email->get('bid_thanks');		
			$ilance->email->set(array(
					'{{username}}' => $username,				
					
			));
			
			$ilance->email->send();
			
			($apihook = $ilance->api('cron_thanks_end')) ? eval($apihook) : false;			
			
		}
  	}
}
log_cron_action('The Thanks Notification successfully emailed to Users ', $nextitem);
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>