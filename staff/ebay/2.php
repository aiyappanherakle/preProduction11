<?php 
require_once('../../functions/config.php');

$query1="SELECT * FROM " . DB_PREFIX . "ebay_listing_rows where seller_id in (2330,18874)";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	 $project_id=$line1['coin_id'];
	 $seller_id=$line1['seller_id'];
	 $fvf=$line1['ebay_seller_fee'];
	 $paiddate=$duedate=$createdate=$line1['enddate'];
	 $amount=$line1['amount']*.19;
	 echo "update  " . DB_PREFIX . "invoices set amount='".$line1['amount']."',paid='".$line1['amount']."', totalamount='".$line1['amount']."' where invoiceid='".$line1['invoice_id']."';";
	 
	 echo '<br>';
	 $fvf=$amount;
	 $query2="SELECT * FROM " . DB_PREFIX . "invoices  where projectid='".$project_id."' and combine_project='' and isfvf=1";
	 $sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	 if($ilance->db->num_rows($sql2)==1)
	 {
	 	 while($line2= $ilance->db->fetch_array($sql2))
		 {
			echo "update  " . DB_PREFIX . "invoices set amount='".$amount."',paid='".$amount."', totalamount='".$amount."' where invoiceid='".$line2['invoiceid']."';";
			echo '<br>'; 
			echo "update  " . DB_PREFIX . "ebay_listing_rows set ebay_seller_fee='".$amount."' where id='".$line1['id']."';";
			echo '<br>'; 
		 }
	 }else
	 {
		$txn = construct_transaction_id();
	 
			echo "INSERT INTO " . DB_PREFIX . "invoices
			(invoiceid, projectid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate,paiddate, custommessage, transactionid, isfvf)
			VALUES(
			NULL,
			'" . intval($project_id) . "',
			'" . intval($seller_id) . "',
			'" . 'Final value fee for auction' . ' - ' . fetch_auction('project_title', intval($project_id)) . ' #' . intval($project_id) . "',
			'" .  sprintf("%01.2f", $fvf) . "',
			'" .  sprintf("%01.2f", $fvf) . "',
			'paid',
			'debit',
			'account',
			'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
			'" . $createdate . "',
			'" . $duedate . "',
			'" . $paiddate . "',
			'" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
			'" . $txn . "',
			'1');
			";
			
			echo '<br>'; 
	 }
	}
}