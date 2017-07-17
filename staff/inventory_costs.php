<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||
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
ini_set('memory_limit', '5024M');
set_time_limit(0);

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$page_heading = 'House Account Inventory Costs Live';
	$sub_cmd = 'Live';
	$cmd_txt = 'print_live';
	 if($ilance->GPC['cmd'] == 'pending')
	 {
		$page_heading = 'House Account Inventory Costs Pending';
		$sub_cmd = 'Pending';
		$cmd_txt = 'print_pending';
	
	 }
	
	if($ilance->GPC['cmd'] == 'print_live')
	{
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Inventory_Costs_Live-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		$fields = array('Item ID','Date Entered','Title','PCGS #','Cost ','Qty Remaining','Total Cost');
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');
		fputcsv($fp, $fields);		

	
	//LIVE coins
		$sql = "SELECT p.project_id, DATE(c.Create_Date) as added_date, c.Title, c.pcgs, c.cost, p.buynow_qty, c.cost*p.buynow_qty  as total_cost
				 FROM " . DB_PREFIX . "projects p, " . DB_PREFIX . "coins c
				 WHERE p.user_id = 101 AND p.status = 'open'
				 AND p.project_id = c.coin_id " ;
				
			$live = $ilance->db->query($sql);
				if($ilance->db->num_rows($live)>0)
				{
					while($test = $ilance->db->fetch_array($live))
					{
						$data2['project_id']=$test['project_id'];
						$data2['added_date']=$test['added_date'];
						$data2['Title']=$test['Title'];
						$data2['pcgs']=$test['pcgs'];
						$data2['cost']=$test['cost'];
						$data2['buynow_qty']=$test['buynow_qty'];
						$data2['total_cost']=$test['total_cost'];
						$res[] = $data2;

						fputcsv($fp, $data2);

					}
				}
				exit();
		}
			
	if($ilance->GPC['cmd'] == 'print_pending')
	{
			$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Inventory_Costs_Pending-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		$fields = array('Item ID','Date Entered','Title','PCGS #','Cost ','Qty Remaining','Total Cost');
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');
		fputcsv($fp, $fields);		

	//pending coins
	    $sql = "SELECT coin_id, DATE(Create_Date) as added_date, Title, pcgs, cost, Quantity, cost*Quantity   as total_cost 
				FROM " . DB_PREFIX . "coins 
				WHERE user_id ='101'
				AND coin_listed = 'c'
				AND (End_Date = '0000-00-00' OR pending = '1')	
				AND project_id  = '0'
				AND status = '0' ";
			
			$pending = $ilance->db->query($sql);
				if($ilance->db->num_rows($pending)>0)
				{
					while($test = $ilance->db->fetch_array($pending))
					{
						$data['coin_id']=$test['coin_id'];
						$data['added_date']=$test['added_date'];
						$data['Title']=$test['Title'];
						$data['pcgs']=$test['pcgs'];
						$data['cost']=$test['cost'];
						$data['Quantity']=$test['Quantity'];
						$data['total_cost']=$test['total_cost'];
						$res[] = $data;
						
						fputcsv($fp, $data);

					}
				}
				
	//repenmding coins		
		$sql1 = "SELECT p.project_id, DATE(c.Create_Date) as added_date, c.Title, c.pcgs, c.cost, p.buynow_qty, c.cost*p.buynow_qty  as total_cost  
				FROM  " . DB_PREFIX . "projects p, " . DB_PREFIX . "coins c
				WHERE (p.status ='expired' OR p.status = 'closed')
				AND p.haswinner = '0'
				AND p.buynow_qty > '0'
				AND p.project_id = c.coin_id
				AND c.project_id != 0
				AND p.user_id = '101' " ;
				
			$repending = $ilance->db->query($sql1);
				if($ilance->db->num_rows($repending)>0)
				{
					while($test = $ilance->db->fetch_array($repending))
					{
						$data1['project_id']=$test['project_id'];
						$data1['added_date']=$test['added_date'];
						$data1['Title']=$test['Title'];
						$data1['pcgs']=$test['pcgs'];
						$data1['cost']=$test['cost'];
						$data1['buynow_qty']=$test['buynow_qty'];
						$data1['total_cost']=$test['total_cost'];
						$res[] = $data1;
						
						fputcsv($fp, $data1);

					}
				}	

				exit();
			
		}
		

			
	$pprint_array = array('cmd_txt','sub_cmd','page_heading','pagnation','user_name','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','checkbox');
        
       ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'inventory_costs.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	//$ilance->template->parse_loop('main', array('reports','statement'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
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