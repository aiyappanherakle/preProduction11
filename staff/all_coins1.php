<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
 
// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
 
$html='<table>';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
$consigner_id=101;
$color[0]='#CCCCCC';
$color[1]='#D3DCE3';

$all_coins_result=$ilance->db->query("SELECT * FROM ilance_coins WHERE user_id =".$consigner_id." order by coin_id");
$coin_count=1;
while($all_coins_line=$ilance->db->fetch_array($all_coins_result))
{
if($all_coins_line['project_id']>0)
{
$bg=$color[$row_count%2];
$qty_greater='';
if($all_coins_line['Quantity']>1) $qty_greater='#FF0000';
	$html.='<tr bgcolor="'.$bg.'"><td>'.$coin_count.'.</td><td>coin_id '.$all_coins_line['coin_id'].'</td><td bgcolor="'.$qty_greater.'">quantity '.$all_coins_line['Quantity'].'</td></tr>';
		$coin_id_result=$ilance->db->query("select * from ilance_buynow_orders where project_id=".$all_coins_line['coin_id']);
		if($ilance->db->num_rows($coin_id_result)>0)
		{$coin_buy_now_count=1;
			while($coin_id_line=$ilance->db->fetch_array($coin_id_result))
			{
				$html.='<tr bgcolor="'.$bg.'"><td></td><td>'.$coin_buy_now_count.'.</td><td>Buynow Orders</td><td  bgcolor="#FFCC33">'.$coin_id_line['buyer_id'].'</td><td>'.$coin_id_line['qty'].'</td><td>'.$coin_id_line['amount'].'</td></tr>';
				$coin_buy_now_count++;
			}
		}
		$coin_id_result=$ilance->db->query('select * from ilance_project_bids where bidstatus="awarded" and project_id="'.$all_coins_line['coin_id'].'"');
		if($ilance->db->num_rows($coin_id_result)>0)
		{
			while($coin_id_line=$ilance->db->fetch_array($coin_id_result))
			{
				$html.='<tr bgcolor="'.$bg.'"><td></td><td>1.</td><td>Auction Awarded</td><td bgcolor="#99FFFF">'.$coin_id_line['user_id'].'</td><td>'.$coin_id_line['qty'].'</td><td>'.$coin_id_line['bidamount'].'</td></tr>';
			}
		}
		$coin_id_result=$ilance->db->query('select * from ilance_projects where status="open" and project_id="'.$all_coins_line['coin_id'].'"');
		if($ilance->db->num_rows($coin_id_result)>0)
		{
			while($coin_id_line=$ilance->db->fetch_array($coin_id_result))
			{
				$html.='<tr bgcolor="'.$bg.'"><td></td><td>1.</td><td> live</td><td bgcolor="">'.$coin_id_line['filtered_auctiontype'].'</td><td>'.$coin_id_line['buynow_qty'].'</td><td>'.$coin_id_line['date_end'].'</td></tr>';
			}
		}
		
		
	}else
	{
				$html.='<tr bgcolor="'.$bg.'"><td>'.$coin_count.'.</td><td>1.</td><td> Pendings</td><td bgcolor="">'.$all_coins_line['coin_id'].'</td><td>'.$all_coins_line['Quantity'].'</td><td>'.$all_coins_line['relist_count'].'</td></tr>';
		
		
	}
	$coin_count++;
	$row_count++;	
}
echo '</table>';
echo $html;
exit;
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>