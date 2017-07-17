<?php 
require_once('../../functions/config.php');
error_reporting(E_ALL);
$query="SELECT * FROM ilance_ebay_listing_rows where invoice_status!='paid'";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
		
			$query1="SELECT * FROM " . DB_PREFIX . "invoice_projects WHERE invoice_id=".$line['invoice_id'];
			$result1=$ilance->db->query($query1);
			if($ilance->db->num_rows($result1)==0)
			{
				$query2="SELECT * FROM " . DB_PREFIX . "invoices WHERE combine_project  like ".$line['invoice_id'];
				$result2=$ilance->db->query($query2);
				if($ilance->db->num_rows($result2))
				{
					while($line2=$ilance->db->fetch_array($result2))
					{

						$invoiceid 			= $line2['combine_project'];
						$project_id			= $line['coin_id'];
						$coin_id			= $line['coin_id'];
						$qty				= 1;
						$shipper_id			= 26;
						$buyer_id			= $line['buyer_id'];
						$seller_id			= $line['seller_id'];
						$status 			= 'paid';
						$created_date		= $line['enddate'];
						$final_invoice_id	= $line2['invoiceid'];
						$shipping_cost		= 4;
						
						$query6="INSERT INTO " . DB_PREFIX . "invoice_projects (invoice_id, project_id, coin_id, qty, shipper_id, buyer_id, 
							seller_id, status, created_date, promocode, shipping_cost, final_invoice_id, disount_val, inv_address) VALUES
							 ('$invoiceid', '$project_id', '$coin_id', '$qty', '$shipper_id', '$buyer_id', '$seller_id', '$status', 
							 	'$created_date', NULL, $shipping_cost, '$final_invoice_id', NULL, '');";

						echo $query6.'<br>';
					}	
				}

				
			}

		}

	}
?>