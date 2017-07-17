<?php
require_once('./../functions/config.php');
$query="select * from ".DB_PREFIX."invoice_projects group by final_invoice_id order by final_invoice_id desc limit 1000";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)>0)
{
	while($line=$ilance->db->fetch_array($result))
	{
	;
	$shipper_id=$line['shipper_id'];
	$user_id=$line['buyer_id'];
	$query2="select sum(totalamount) as tot from ".DB_PREFIX."invoices where invoiceid='".$line['final_invoice_id']."'";
	$result2=$ilance->db->query($query2);
	if($ilance->db->num_rows($result2)>0)
	{
		while($line2=$ilance->db->fetch_array($result2))
		{
		$invoice_total=$line2['tot'];
		}
	}
	if(is_shipper_upgraded($user_id,$shipper_id,$invoice_total))
	{
	echo $line['final_invoice_id'].'<br>';
	}
	}
}

function is_shipper_upgraded($user_id,$shipper_id,$invoice_total)
{
	 global $ilance;
   
	 $country_id=fetch_user('country',$user_id);	
	 //if international
	 if($country_id!=500 and $shipper_id!=22 and $invoice_total<10000)//USPS International Priority
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=25 and $invoice_total>10000)//USPS Express Mail
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=27 and $invoice_total>1000 and  $invoice_total<=10000)//USPS Priority Mail
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=26 and $invoice_total<=1000)//USPS First Class Mail
	 {
	 return true;
	 }
	 return false;
}
	
?>