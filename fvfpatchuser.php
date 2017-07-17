<?php
require_once('./functions/config.php');
error_reporting(E_ALL);

$bid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids
					WHERE bidstatus = 'awarded'
					AND project_user_id = '4724'
					");
		
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
				    $subres['fvf_id'] = '5';
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
							if($ilance->db->num_rows($check) == 1)
							{
							
								// update invoice mark as final value fee invoice type
								
								
							$ilance->db->query("
									UPDATE " . DB_PREFIX . "invoices
									SET
									amount = '" . sprintf("%01.2f", $fvf) . "',
									paid = '" . sprintf("%01.2f", $fvf) . "',
									totalamount = '" . sprintf("%01.2f", $fvf) . "'									
									WHERE user_id = '" . intval($bidderid) . "'
									AND projectid = '".$pid."'
									AND isfvf = '1'
									
									LIMIT 1
							", 0, null, __FILE__, __LINE__);
							echo '<br>'.$pid. '<br>'.$bidamount.'<br>'.$fvf;
							
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
									SET fvf = '" . sprintf("%01.2f", $fvf) . "'									
									WHERE project_id = '" . intval($pid) . "'
							", 0, null, __FILE__, __LINE__);
							}
                              
                        }
				
		}
	}

?>