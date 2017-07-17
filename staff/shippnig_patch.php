<?php
// #### require backend ########################################################
require_once('./../functions/config.php');
$query="SELECT s.ship_id,s.buyer_id,s.coin_id,i.invoiceid  FROM `ilance_shippnig_details` s
left join ilance_invoices i on i.projectid=s.coin_id and s.buyer_id=i.user_id and i.isbuyerfee=0 
where s.coin_id>0
ORDER BY s.`ship_id` DESC ";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
		if($line['invoiceid']>0)
		{
		$final_invoice_id=0;
		$query1="SELECT * FROM " . DB_PREFIX . "invoices WHERE combine_project like '%".$line['invoiceid']."%' and user_id='".$line['buyer_id']."'";
			$result1=$ilance->db->query($query1);
			if($ilance->db->num_rows($result1))
			{
				while($line1=$ilance->db->fetch_array($result1))
				{
				$final_invoice_id=$line1['invoiceid'];
				}
			}
			 $ilance->db->query("update ilance_shippnig_details set invoice_id=".$line['invoiceid']." ,final_invoice_id='".$final_invoice_id."'  where ship_id=".$line['ship_id'] );
		}else
		{
		echo $line['ship_id'];
		echo '<Br>';
		}		
		}
	}
?>