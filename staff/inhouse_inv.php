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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
		$from_date=$ilance->GPC['from_date'];
		$to_date=$ilance->GPC['to_date'];
		$fields = array(
					array('coin_id', 'coin_id'),
					array('title', 'title'),
					array('user_id', 'user_id'),
					array('cost', 'cost'),
					array('quantity','quantity'),
					array('total','total')
				);
				
				foreach ($fields AS $column)
				{			
					 
					$headings[] = $column[0];				
				}
				
		
		$sql = $ilance->db->query("SELECT c.coin_id,c.title,c.user_id,c.cost,c.quantity,c.buy_it_now,c.minimum_bid,c.Quantity*c.cost as total FROM ilance_coins c left join ilance_users u on u.user_id=c.user_id where u.house_account=1 and (c.pending=1 or c.end_date='0000-00-00')
ORDER BY `c`.`user_id`  DESC");
		while($res = $ilance->db->fetch_array($sql))
		{
			
			$coin_detail['coin_id']=$res['coin_id'];
			$coin_detail['title']=$res['title'];
			$coin_detail['user_id']=$res['user_id'];
			$coin_detail['cost']=$res['cost'];
			$coin_detail['quantity']=$res['quantity'];
			$coin_detail['total']=$res['total'];
			
			
			$data[]=$coin_detail;
		}
		
		
		$sql1 = $ilance->db->query("SELECT p.project_id,p.project_title,p.user_id,p.buynow_qty,c.cost, p.buynow_qty*c.cost as total FROM ilance_projects p 
left join ilance_users u on p.user_id=u.user_id 
left join ilance_coins c on c.coin_id=p.project_id
where p.status='open' and u.house_account=1
ORDER BY p.project_id ASC");
		while($res1 = $ilance->db->fetch_array($sql1))
		{
			
			$coin_detail['coin_id']=$res1['project_id'];
			$coin_detail['title']=$res1['project_title'];
			$coin_detail['user_id']=$res1['user_id'];
			$coin_detail['cost']=$res1['cost'];
			$coin_detail['quantity']=$res1['buynow_qty'];
			$coin_detail['total']=$res1['total'];
			
			
			$data[]=$coin_detail;
		}
		
		
		
	$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "inhouse_inventory-$timeStamp";
	$action = 'csv';
		if ($action == 'csv')
		{
			header("Pragma: cache");
			header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
			header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
			echo $reportoutput;
			die();
		}

		exit();

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>