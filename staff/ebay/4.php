<?php 
require_once('../../functions/config.php');

$query1="SELECT invoice_id,buyer_id
 FROM " . DB_PREFIX . "ebay_listing_rows e left join ilance_users u on u.user_id=e.seller_id
 left join " . DB_PREFIX . "invoices i on i.invoiceid=e.invoice_id where i.invoiceid>0 order by e.enddate desc";
 $sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	$query2="SELECT invoiceid,user_id,status FROM " . DB_PREFIX . "invoices  where user_id='".$line1['buyer_id']."' and combine_project like '%".$line1['invoice_id']."%'";
	$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($sql2))
	{
		while($line2 = $ilance->db->fetch_array($sql2))
		{
			//$t="https://www.greatcollections.com/staff/buyers.php?subcmd=_detail_invoice&user_id=".$line2['user_id']."&paidstatus=".$line2['status']."&id=".$line2['invoiceid'];
//	  	  echo '<a href="'.$t.'">'.$t.'</a>' ;
echo $line2['invoiceid'];
	  	  echo '<br>';

		}
	}
	}
}