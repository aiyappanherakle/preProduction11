<?php
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{


$no_update_item='';

$sqlquery1 = $ilance->db->query("select * from ".DB_PREFIX."coins where consignid=4505 ");

while($res1=$ilance->db->fetch_array($sqlquery1))
{
		
		// echo "UPDATE  " . DB_PREFIX . "coins
												// SET  fvf_id =10
												// WHERE  coin_id = '".$res1['coin_id']."'												 
												// ";		
		// $ilance->db->query("UPDATE  " . DB_PREFIX . "coins
												// SET  fvf_id =10
												// WHERE  coin_id = '".$res1['coin_id']."'												 
												// ");
		// echo'<br>';										
												
			
		$sqlquery2 = $ilance->db->query("select * from ".DB_PREFIX."projects 
										where project_id='".$res1['coin_id']."' 
										and status='expired'
										and winner_user_id !=0
										");
		
		while($res2=$ilance->db->fetch_array($sqlquery2))
		{
			
			$new_fvf=$res2['currentprice'] * 10/100;
			echo "UPDATE  " . DB_PREFIX . "projects
												SET  fvf = ".$new_fvf."																						      
												WHERE  project_id = '".$res2['project_id']."'												
												";
												
			echo'<br>';
			
			// $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
												// SET  fvf = ".$new_fvf."																						      
												// WHERE  project_id = '".$res2['project_id']."'												
												// ");
		
			echo "UPDATE  " . DB_PREFIX . "invoices
												SET  amount = ".$new_fvf.",
													paid = ".$new_fvf.",
													totalamount = ".$new_fvf." 
												WHERE  invoiceid = '".$res2['fvfinvoiceid']."'
												and projectid='".$res2['project_id']."'												
												";
												
			// $ilance->db->query("UPDATE  " . DB_PREFIX . "invoices
												// SET  amount = ".$new_fvf.",
													// paid = ".$new_fvf.",
													// totalamount = ".$new_fvf." 
												// WHERE  invoiceid = '".$res2['fvfinvoiceid']."'
												// and projectid='".$res2['project_id']."'												
												// ");
		}		
		
		
		echo '<br>';

echo construct_transaction_id();
	echo '<br>';
}
 
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>