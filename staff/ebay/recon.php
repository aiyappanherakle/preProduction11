<?php 
require_once('../../functions/config.php');

$query1="SELECT *,concat(first_name,' ',last_name,'[',u.user_id,']') as seller_name, date_format(e.enddate,'%d-%b-%Y') as solddate,
 date_format(DATE_ADD(e.enddate, INTERVAL (8 - IF(DAYOFWEEK(e.enddate)=1, 8 , DAYOFWEEK(e.enddate))) DAY),'%d-%b-%Y') as statement_date
 FROM " . DB_PREFIX . "ebay_listing_rows e left join ilance_users u on u.user_id=e.seller_id
 left join " . DB_PREFIX . "invoices i on i.invoiceid=e.invoice_id where i.invoiceid>0 order by e.enddate desc";
 $sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
echo '"GC Item ID", "eBay Item ID", "Seller Name"," Statement Date Appears On", "Sold Price", "Commission to GC"," Net to Consignor", "Date Sold"<br>';
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	//  Statement Date Appears On, Sold Price, Commission to GC, Net to Consignor, Date Sold
	
	  echo $line1['ebay_id'].",".$line1['coin_id'].",".$line1['seller_name'].",".$line1['statement_date'].",".$line1['amount'].",".$line1['ebay_seller_fee'].",".($line1['amount']-$line1['ebay_seller_fee']).", ".$line1['solddate'];
	  echo '<br>';
	}
}