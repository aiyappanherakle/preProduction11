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
/*==========================================================================*\
Added by Tamil on 03/01/13 for bug 1975
*/

/*This function generate mail to Ian if any house account has zero cost */

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}


$sql_house_acc= $ilance->db->query("SELECT c.coin_id,c.title,c.pcgs, DATE_FORMAT( DATE( c.Create_date ) ,  '%m-%d-%Y' ) as Create_date,u.username FROM
										" . DB_PREFIX . "users u,
										" . DB_PREFIX . "coins c
										WHERE u.house_account = '1'
										AND u.user_id=c.user_id
										AND c.cost='0'
							");
if($ilance->db->num_rows($sql_house_acc)>0)
{
	$messagebody .= "-----------------------------------"."\n";
	$messagebody .= "No Cost Items of House Account"."\n";
	$messagebody .= "-----------------------------------"."\n";
	$count=0;
	while($res_house_acc = $ilance->db->fetch_array($sql_house_acc))
	{
		$username = $res_house_acc['username'];
		$messagebody .= "Item ID : ".$res_house_acc['coin_id']. "\n";
		$messagebody .= "Item Title : " . $res_house_acc['title']. "\n";
		$messagebody .= "Item PCGS No : " . $res_house_acc['pcgs']. "\n";
		$messagebody .= "Added on : ". $res_house_acc['Create_date'] . "\n";					
		$messagebody .= "\n";
		$count++;
	}

	$messagebody .="Total no of coins : ".$count;
	
	$ilance->email = construct_dm_object('email', $ilance);
	$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
	$ilance->email->get('no_cost');
	$ilance->email->set(array('{{message}}' => $messagebody));
	$ilance->email->send();	
			
	
	unset($messagebody);
	
	log_cron_action('The report of House accounts with zero cost items was successfully emailed to ian@greatcollections.com' , '');

}

?>