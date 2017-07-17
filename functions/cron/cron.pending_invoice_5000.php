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
Added by Tamil on 03/01/13 for bug 2109
*/

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

($apihook = $ilance->api('cron_unpaid_start')) ? eval($apihook) : false;
	
	$threshold=2500;
	
	$sql="SELECT MIN(DATE(i.createdate)) AS first_iv_date,MAX(DATE(i.createdate)) AS last_iv_date,sum(i.amount+ifnull(b.amount,0)) as totalunpaid,u.user_id,u.first_name,u.last_name,u.email,u.phone FROM " . DB_PREFIX . "invoices i
	left join " . DB_PREFIX . "users u on u.user_id=i.user_id
	left join " . DB_PREFIX . "invoices b on i.projectid=b.projectid and i.user_id=b.user_id and b.isbuyerfee=1 
	WHERE i.status='unpaid'  and i.combine_project='' and i.isfvf=0 and i.isif=0 and i.isbuyerfee=0 and i.isenhancementfee=0 
	group by i.user_id
	having sum(i.amount+ifnull(b.amount,0))>'".$threshold."'
	ORDER BY totalunpaid  DESC";
	
$sql_combine = $ilance->db->query( $sql);
		
if ($ilance->db->num_rows($sql_combine) > 0)
{	
	$messagebody = "-----------------------------------------------------"."\n";
	$messagebody .= "Users With Pending invoices Over $5000"."\n";
	$messagebody .= "-----------------------------------------------------"."\n";	
	$messagebody .= "\n";
	while($res_combine = $ilance->db->fetch_array($sql_combine))
	{
				
		 
		$sql_combine_paid = $ilance->db->query("
									SELECT invoiceid
									FROM " . DB_PREFIX . "invoices
									WHERE user_id='".$res_combine['user_id']."'
									AND status = 'paid'
									AND combine_project !=''								
									", 0, null, __FILE__, __LINE__);	
		
		$messagebody .="Name : ".$res_combine['first_name']." ".$res_combine['last_name']."\n";
		
		$messagebody .="Mail : ".$res_combine['email']."\n";
		
		$messagebody .="Phone : ".$res_combine['phone']."\n";
		
		$messagebody .="Pending : $".$res_combine['totalunpaid']."\n";
		
		$messagebody .="Date Range : ".print_date($res_combine['first_iv_date'],'%m/%d/%Y').'-'.print_date($res_combine['last_iv_date'],'%m/%d/%Y')."\n";
		
		$messagebody .=($ilance->db->num_rows($sql_combine_paid))>0 ?("First Invoice : N"."\n"):('First Invoice : Y'."\n");
	  
		$messagebody .= "\n";
	  
	}
	
	
	
	$ilance->email = construct_dm_object('email', $ilance);
	
	$ilance->email->logtype = 'Pending invoices greater than USD5000';

	$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
	$ilance->email->slng = fetch_site_slng();
	
	$ilance->email->get('pending_invoices_5k');	
	
	$ilance->email->set(array('{{message}}' =>$messagebody));
	
	$ilance->email->send();

	unset($messagebody);
}

($apihook = $ilance->api('cron_unpaid_end')) ? eval($apihook) : false;
        
log_cron_action('The Pending invoices greater than USD5000 report was successfully emailed to ian@greatcollections.com' , '');
?>