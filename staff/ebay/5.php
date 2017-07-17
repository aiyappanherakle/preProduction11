<?php 
require_once('../../functions/config.php');
error_reporting(E_ALL);
$query1="SELECT i.projectid,e.amount as eamount,e.buyer_id,i.invoiceid  FROM ilance_invoices i left join ilance_ebay_listing_rows e on e.invoice_id=i.invoiceid WHERE i.Site_Id = 1 and i.combine_project='' and i.isfvf=0";
 $sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	$query2="SELECT * FROM " . DB_PREFIX . "invoices  where user_id='".$line1['buyer_id']."' and combine_project like '%".$line1['invoiceid']."%'";
	$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($sql2))
	{
		while($line2 = $ilance->db->fetch_array($sql2))
		{
		  if( $line2['totalamount']-4 != $line1['eamount'])
		  {
		  echo $line2['invoiceid'].','.$line2['totalamount'].','.$line1['eamount'];
	  	  echo '<br>';
	  	  }
		}
	}
	}
}