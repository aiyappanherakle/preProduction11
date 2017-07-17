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


										
	$sql = $ilance->db->query("SELECT COUNT( * ) AS tot, buyer_id, shipment_date, shipper_id
                                FROM " . DB_PREFIX . "shippnig_details 
                                 WHERE DATE( shipment_date ) =  '".DATETODAY."'
								 AND track_no != ''
                                 GROUP BY buyer_id, shipment_date, shipper_id		
								          ");
				
					if($ilance->db->num_rows($sql)>0)
					 {
					
					 	$package = $ilance->db->num_rows($sql);
					    while($totallist=$ilance->db->fetch_array($sql))
						 {	
						 
						     //$totallist['shipper_id'];
							 $amount = shipping_details($totallist['shipper_id'],$totallist['tot']);
							 	 
							 $shipper_id =$totallist['shipper_id'];
							
							 if($shipper_id == '22')
							 {
							 	$amounttw[] = $amount;
								$coutw = $coutw + 1;
							 }
							 if($shipper_id == '23')
							 {
							 	$amounttth[] = $amount;
								$coutth = $coutth + 1;
							 }
							 if($shipper_id == '25')
							 {
							 	$amounttwfiv[] = $amount;
								$coutwfiv = $coutwfiv + 1;
							 }
							 if($shipper_id == '26')
							 {
							 	$amounttwsix[] = $amount;
								$coutwsix = $coutwsix + 1;
							 }
							 if($shipper_id == '27')
							 {
							 	$amounttwsev[] = $amount;
								$coutwsev = $coutwsev + 1;
							 }
						
						 }
					 }
					 
					   $messagebody='Shipping Stats:'. "\n";

							$messagebody.='You shipped '.$package.' packages today.'. "\n";
							
							if($coutwfiv == '')
							{
							$messagebody.= '0 by Express Mail (total shipping charged: $0.00)' ."\n";
							}
							else
							{
							$messagebody.= $coutwfiv.' by Express Mail (total shipping charged: $'.array_sum($amounttwfiv).')'. "\n";
							}
							
							if($coutwsev == '')
							{
							$messagebody.= '0 by Priority Mail (total shipping charged: $0.00)' ."\n";
							}
							else
							{
							$messagebody.= $coutwsev.' by Priority Mail (total shipping charged: $'.array_sum($amounttwsev).')'. "\n";
							}
							
							if($coutwsix == '')
							{							
							$messagebody.='0 by First Class Mail (total shipping charged: $0.00)' ."\n";
							}
							else
							{							
							$messagebody.=$coutwsix.' by First Class Mail (total shipping charged: $'.array_sum($amounttwsix).')'. "\n";
							}
							
							if($coutw == '')
							{
							$messagebody.='0 by International Priority Mail (total shipping charged: $0.00)'. "\n";
							}
							else
							{
							$messagebody.=$coutw.' by International Priority Mail (total shipping charged: $'.array_sum($amounttw).')'. "\n";
							}
							
							
							if($coutth == '')
							{
							$messagebody.='0 by International Express Mail (total shipping charged: $0.00)' ."\n";
							}
							else
							{
							$messagebody.=$coutth .' by International Express Mail (total shipping charged: $'.array_sum($amounttth).')'. "\n";
							}
							
							
							$ilance->email = construct_dm_object('email', $ilance);
							$ilance->email->mail = SITE_EMAIL;
							$ilance->email->slng = fetch_site_slng();
							$ilance->email->logtype = 'shipping details';
							$ilance->email->get('shipping_details');
							$ilance->email->set(array(  
							'{{message}}' => $messagebody,
							));
								
							$ilance->email->send();
					 
							$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
							$ilance->email->slng = fetch_site_slng();
							$ilance->email->logtype = 'shipping details';							
							$ilance->email->get('shipping_details');
							$ilance->email->set(array(  
							'{{message}}' => $messagebody,
							));

							$ilance->email->send();  
							
							// work for bug id :5208 - Please add ron@greatcollections.com to this e-mail 
							//starts
							
							$ilance->email->mail = $ilconfig['globalserversettings_staff_ron'];
							$ilance->email->slng = fetch_site_slng();	
							$ilance->email->logtype = 'shipping details';
							$ilance->email->get('shipping_details');
							$ilance->email->set(array(  
							'{{message}}' => $messagebody,
							));

							$ilance->email->send(); 
							
							//ends
