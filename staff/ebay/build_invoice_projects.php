<?php 
require_once('../../functions/config.php');
$query="SELECT * FROM " . DB_PREFIX . "invoices WHERE combine_project in (732229,754480,754484,754487,754492,754494,754497,754499,754501)";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
		$list=explode(',',$line['combine_project']);
		foreach($list as $child)
		{
			$invoiceid=$child;
			$query1="SELECT * FROM " . DB_PREFIX . "invoices WHERE invoiceid = ".$child;
				$result1=$ilance->db->query($query1);
				if($ilance->db->num_rows($result1))
				{
					while($line1=$ilance->db->fetch_array($result1))
					{
					$project_id=$line1['projectid'];
					
					$coin_id=$line1['projectid'];
					$qty=1;
					$shipper_id=26;
					$buyer_id=$line1['user_id'];
					$seller_id=$line1['p2b_user_id'];
					$status='unpaid';
					$created_date=$line1['createdate'];
					$final_invoice_id=$line['invoiceid'];
					}
				}
			echo $query="INSERT INTO ilance_invoice_projects (invoice_id, project_id, coin_id, qty, shipper_id, buyer_id, seller_id, status, created_date, promocode, shipping_cost, final_invoice_id, disount_val, inv_address) VALUES ('$invoiceid', '$project_id', '$coin_id', '$qty', '$shipper_id', '$buyer_id', '$seller_id', '$status', '$created_date', NULL, '0', '$final_invoice_id', NULL, '');";
			echo '<br>';
		}

		
		}
	}
?>