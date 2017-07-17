<?php
require_once('./functions/config.php');
error_reporting(E_ALL);

$bid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids
					WHERE bidstatus = 'awarded'");
		
	if($ilance->db->num_rows($bid) > 0)
	{	
		while($res = $ilance->db->fetch_array($bid))
		{
			
				$pid = $res['project_id'];
				
				$bidamount = $res['bidamount'];
				$bidderid = $res['project_user_id'];
				$bidid = $res['bid_id'];
				
				$sub = $ilance->db->query("SELECT fvf_id FROM " .DB_PREFIX. "coins WHERE coin_id = '".$pid."'");
				$subres = $ilance->db->fetch_array($sub);
				$fees['finalvalue_from'] = 1000;
				
				if($bidamount <= $fees['finalvalue_from'])
				{
					$fvf = ($bidamount * $subres['fvf_id'] / 100);                    
				}
				else
				{
					$fvf = 0;
				}
				
				if ($fvf > 0)
                {                             
                   
							$check = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices
														WHERE user_id = '".$bidderid."'
														AND isfvf = '1'
														AND projectid = '".$pid."'
														");
							if($ilance->db->num_rows($check) == 0)
							{
							$ilance->accounting = construct_object('api.accounting');
							// create a paid final value fee
							$invoiceid = $ilance->accounting->insert_transaction(
									0,
									intval($pid),
									0,
									intval($bidderid),
									0,
									0,
									0,
									$phrase['_final_value_fee_for_auction'] . ' - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid),
									sprintf("%01.2f", $fvf),
									sprintf("%01.2f", $fvf),
									'paid',
									'debit',
									'account',
									$res['date_awarded'],
									$res['date_awarded'],
									$res['date_awarded'],
									$phrase['_auto_debit_from_online_account_balance'],
									0,
									0,
									1
							);
							
							echo '<br>'.$invoiceid;
							// update invoice mark as final value fee invoice type
							$ilance->db->query("
									UPDATE " . DB_PREFIX . "invoices
									SET
									totalamount = '" . sprintf("%01.2f", $fvf) . "',
									isfvf = '1'
									WHERE invoiceid = '" . intval($invoiceid) . "'
									LIMIT 1
							", 0, null, __FILE__, __LINE__);
							
							// update final value fee field in bid table & project table for awarded amount
							$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_bids
									SET fvf = '" . sprintf("%01.2f", $fvf) . "'
									WHERE bid_id = '" . intval($bidid) . "'
										AND project_id = '" . intval($pid) . "'
									LIMIT 1
							", 0, null, __FILE__, __LINE__);
							
							$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET fvf = '" . sprintf("%01.2f", $fvf) . "',
									isfvfpaid = '1',
									fvfinvoiceid = '" . intval($invoiceid) . "'
									WHERE project_id = '" . intval($pid) . "'
							", 0, null, __FILE__, __LINE__);
							
							}
                              
                        }
				
		}
	}

?>