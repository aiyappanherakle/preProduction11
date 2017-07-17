<?php
require_once('./../functions/config.php');
if(isset($_POST['submit']))
{
	
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
	{
		
		$consigner_id = $_POST['user_id'];
		$coin_count=1;
		$all_coins_result=$ilance->db->query("SELECT * FROM ilance_coins WHERE user_id =".$consigner_id." order by coin_id");
		
		if($ilance->db->num_rows($all_coins_result)>0)
		{
			$row_con_list = 1;
			while($row_list = $ilance->db->fetch_array($all_coins_result))
			{
				$row_list_coin['sno'] = $row_con_list;
				$row_list_coin['coin_id'] = $row_list['coin_id'];
				$row_list_coin['coin_title'] = $row_list['Title'];
				$now_qty = $row_list['Quantity'];
				
				$row_list_coin['coinqty'] = $row_list['Quantity'];
				$select_pjt = $ilance->db->query("SELECT * FROM ilance_projects WHERE project_id = '".$row_list['coin_id']."'");
				if($ilance->db->num_rows($select_pjt)>0)
				{
					$res = $ilance->db->fetch_array($select_pjt);
					$qu = $ilance->db->query("SELECT projectid FROM ilance_invoices where projectid = '".$res['project_id']."' AND 	invoicetype = 'escrow'");
					$con = $ilance->db->num_rows($qu);
					$qu1 = $ilance->db->query("SELECT sum(amount) as tot FROM ilance_invoices where projectid = '".$res['project_id']."' AND invoicetype = 'escrow' GROUP BY projectid");
					$mur = $ilance->db->fetch_array($qu1);
					
					$ee = $ilance->db->query("SELECT sum(qty) as qtyq FROM ilance_buynow_orders WHERE project_id = '".$row_list['coin_id']."'");
					
					
					$re = $ilance->db->fetch_array($ee);
					if($re['qtyq'] > 0 )
					$row_list_coin['buynow_qty'] = $re['qtyq'];
					else
					$row_list_coin['buynow_qty'] = '-';
					
					
					$row_list_coin['amount'] = $res['currentprice'];
					if($mur['tot'] > 0)
					$row_list_coin['total_amount'] = $mur['tot'];
					else
					$row_list_coin['total_amount'] = '-';
					$row_list_coin['sold_qty'] = $con;
					
					$pjt_qty = $res['buynow_qty'];
					$row_list_coin['pjtqty'] = $res['buynow_qty'];
					$type = $res['filtered_auctiontype'];
					if($type == 'fixed')
					{
						$row_list_coin['buynow'] = 'Yes';
						$row_list_coin['auction'] = '-';
					}
					if($type == 'regular')
					{
						$row_list_coin['auction'] = 'Yes';
						$row_list_coin['buynow'] = '-';
					}
					if($res['status'] == 'open')
					{
						$row_list_coin['Winner_user_id'] = '-';
						$row_list_coin['curr_status'] = 'Live Item';
					}
					if($res['haswinner'] == 1)
					{
						$row_list_coin['Winner_user_id'] = $res['winner_user_id'];
						$row_list_coin['curr_status'] = 'Sold By Auction';
					}
					if($res['hasbuynowwinner'] == 1)
					{
						
						
						
						$row_list_coin['Winner_user_id'] = '-';
						$row_list_coin['curr_status'] = 'Sold By Buynow';
					}
				}
				else
				{
					$row_list_coin['Winner_user_id'] = '-';
					$row_list_coin['curr_status'] = 'Pendings';
				}
				
				$coin_list[] = $row_list_coin;
				$row_con_list++;
			}
		}
		else
		{
		$show['no'] = true;
		}
		
		
	}
	
}
$pprint_array = array('form_action');		

			//($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;		

		$ilance->template->fetch('main', 'coin_list_user.html', 2);

		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));

		$ilance->template->parse_loop('main', array('coin_list'));

		$ilance->template->parse_if_blocks('main');

		$ilance->template->pprint('main', $pprint_array);

		exit();
?>

