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
Added by Tamil on 03/01/13 for bug 2160
*/

/*This function generate mail to Ian on last day of the month about partial payment of the current month */

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}
require_once('../config.php');

$from_date=date('Y-m-01');
$to_date=date('Y-m-d');
 
$headings='INVOICE#'.',';
$headings .='USER ID'.',';
$headings .='USER NAME'.',';
$headings .='TOTAL INVOICE'.',';
$headings .='PARTIALLY PAID AMOUNT'.',';
$headings .='BALANCE OWING'.',';
$headings .='DATE OF LAST PAYMENT';



$sql = $ilance->db->query("SELECT u.first_name,u.last_name,i.invoiceid,i.user_id,i.totalamount,i.paid,
					(i.totalamount-i.paid) as due_amount,DATE_FORMAT(DATE(MAX(p.paymentdate)),'%m/%d/%Y') as last_paid_date
					FROM " . DB_PREFIX . "invoices i 
					LEFT JOIN 	" . DB_PREFIX . "partial_payment p  ON p.invoiceid=i.invoiceid
					LEFT JOIN  " . DB_PREFIX . "users u ON i.user_id=u.user_id
					WHERE i.amount != i.paid
					AND i.paid != 0
					AND i.status !='paid'
					AND i.combine_project !=''
					AND p.invoiceid=i.invoiceid
					AND (p.paymentdate BETWEEN '".$from_date."' AND '".$to_date."')
					GROUP BY i.invoiceid
					ORDER BY p.paymentdate DESC
					");
					
$data="\n";			
while($res = $ilance->db->fetch_array($sql))
{
	
	$data .='"'.$res['invoiceid'].'",';			
	$data .='"'.$res['user_id'].'",';
	$data .='"'.$res['first_name'].$res['last_name'].'",';
	$data .='"'.$res['totalamount'].'",';
	$data .='"'.$res['paid'].'",';		
	$data .='"'.$res['due_amount'].'",';		
	$data .='"'.$res['last_paid_date'].'"';	
	$data .="\n";
}


$messagebody =$headings;
$messagebody .=$data;
$messagebody .=  "\n";

$ilance->email = construct_dm_object('email', $ilance);

$ilance->email->logtype = 'Pending invoices greater than USD5000';

$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];

$ilance->email->slng = fetch_site_slng();

$ilance->email->get('partial_payment_report');	

$ilance->email->set(array('{{message}}' =>$messagebody));

$ilance->email->send();


log_cron_action('The partial payment report for current month was successfully emailed to ian@greatcollections.com' , '');

?>