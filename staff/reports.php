<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright 20002010 ILance Inc. All Rights Reserved.                # ||
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

$filtervalue = '';
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
     if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'prices_realized') 
	{
		//error_reporting(E_ALL);
	 
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Prices-Realized-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		$fields = array('PCGS','Sale Name','Sale Date','Lot#','Grade','Grading Service','Price Realized with BP','Item Title','Item URL');
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');
		fputcsv($fp, $fields);		
	   $from = $ilance->GPC['from_date'];
	   $to   =  $ilance->GPC['to_date'];
	   if($ilance->GPC['display']=='auctions')
	   {
	     $status = "AND p.status = 'expired'";
	    $con="AND p.filtered_auctiontype = 'regular' AND p.haswinner='1'  AND p.winner_user_id = u.user_id";
		}
		else
		{
		 $status = "AND (p.status = 'expired' OR p.status = 'closed')";
		$con='AND(p.haswinner=1 OR p.hasbuynowwinner=1)';
		}
	    $sql = "SELECT date(p.date_end) as date_end,(p.currentprice + p.buyer_fee) as currentprice,p.project_title,p.project_id,c.Grade,c.Grading_Service,c.pcgs,CONCAT('GreatCollections Coin Auctions ',DATE_FORMAT(date(p.date_end), '%m/%d/%Y')) as End_Date,CONCAT('http://www.greatcollections.com/Coin/',p.project_id,'/') as new_title,p.filtered_auctiontype  FROM " . DB_PREFIX . "projects p
						left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
						left join " . DB_PREFIX . "users u on  p.winner_user_id = u.user_id 
						WHERE   p.visible = '1'
						$status
						$con
						AND u.isexclude_pricesrealized !=1
						AND (date(p.date_end) between '".$from."' and '".$to."')
						AND p.project_id=c.project_id
						AND c.pcgs NOT BETWEEN 6000000 AND 6000500 
						group by p.project_id
						order by p.date_end
						
						";
			
				$sel = $ilance->db->query($sql);
				if($ilance->db->num_rows($sel)>0)
				{
				while($test = $ilance->db->fetch_array($sel))
				{
					/*$test1 = HTTP_SERVER.'Coin/'.$ressel['project_id'].'/'.construct_seo_url_name($test['project_title']);
					if ($ilconfig['globalauctionsettings_seourls'])
					{
					$data['new_title'] = $test1;						
					}
					else
					{
					$data['new_title'] = $test['project_title'];
					}*/
					$data['pcgs']=$test['pcgs'];
					$data['End_Date']=$test['End_Date'];
					$data['date_end']=$test['date_end'];
					$data['project_id']=$test['project_id'];
					if(strstr($test['project_title'],'Details'))
					{
					$data['Grade']='0';
					}
					else
					{
					$data['Grade']=$test['Grade'];
					}
					if(strstr($test['project_title'],'+'))
					{
					$data['Grade']=$test['Grade'].'+';
					}
					else
					{
					$data['Grade']=$test['Grade'];
					}
					$data['Grading_Service']=$test['Grading_Service'];
					$data['proce_relized']=$test['currentprice'];
					$data['project_title']=$test['project_title'];
					$data['url']=HTTP_SERVER.'Coin/'.$test['project_id'].'/'.construct_seo_url_name($test['project_title']);;	
					$res[] = $data;
					


    fputcsv($fp, $data);
	

				}
				}
				exit();
				
				/*if($ilance->db->num_rows($sel)>0)
				{			
				$reportoutput = $ilance->admincp->construct_csv_data($res, $headings);
				$timeStamp = date("Y-m-d-H-i-s");
				$fileName = "PricesRealized-$timeStamp";
				$action = 'csv';
					if ($action == 'csv')
					{
						header("Pragma: cache");
						header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
						header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
						echo $reportoutput;
						die();
					}
				}
				else
				{
				  print_action_failed("Selected Report is Empty Check Date", reports.'.php' );
                        exit();
				}*/
		
	}
    
    if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'reports')
	{
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_do-reports')
		{
			$show['showreportoutput'] = true;
			$action = $ilance->GPC['action'];
			if (isset($action))
			{
				if (empty($ilance->GPC['doshow']))
				{
					print_action_failed($phrase['_you_did_not_select_a_desired_report_type_please_go_back_and_retry'], reports.'.php');
				}
				
				
				
				// #### generate custom reporting sql
				switch ($ilance->GPC['doshow'])
				{
					// This Summary Report Comes from consignment table order by consignid
					case 'consignmentsummary':
					{
						// #### date range in the past
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						
						$sql = "
							SELECT *
							FROM " . DB_PREFIX . "consignments
							WHERE user_id != '' 
							AND (end_date <= '" . $endDate . "' AND end_date >= '" . $startDate . "')";
							
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY consignid ASC";
						}
						else
						{
						$sql .= " ORDER BY consignid DESC";
						}
						
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "consignments");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "consignmentsreports-$timeStamp";
						break;
					}
					
					//// This Detail Report Comes from Coin table order by consignid
					case 'consignmentdetail':
					{
						
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						
						$sql = "SELECT c.* FROM " . DB_PREFIX . "coins c, " . DB_PREFIX . "consignments con
						where c.consignid = con.consignid
						AND (c.End_date <= '" . $endDate . "' AND c.End_date >= '" . $startDate . "')
						ORDER BY consignid ASC
						";
						/*$select_consign = $ilance->db->query("SELECT *
							FROM " . DB_PREFIX . "consignments");
						while ($result = $ilance->db->fetch_array($select_consign))
						{
							
							$sql =  $ilance->db->query("SELECT *
							FROM " . DB_PREFIX . "coins
							WHERE consignid = '".$result['consignid']."'
							AND (End_date <= '" . $endDate . "' AND End_date >= '" . $startDate . "')
							ORDER BY consignid ASC
							");
						}*/
						//$sql .= " AND (end_date <= '" . $endDate . "' AND end_date >= '" . $startDate . "')";
						
								
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "coins");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "detailsreports-$timeStamp";
						break;
					}
					
					// // This Advance Report Comes from Invoice table order by invoice id and checking invoicetype is advance
					case 'advance':
					{
						
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						//SELECT * FROM ilance_projects WHERE bids = 0 AND (status = 'closed' or status = 'expired' or status = 'finished')
						$sql = "SELECT * FROM " . DB_PREFIX . "invoices
						WHERE invoicetype = 'advance'						
						AND (paiddate <= '" . $endDate . "' AND paiddate >= '" . $startDate . "')
						";
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY invoiceid ASC";
						}
						else
						{
						$sql .= " ORDER BY invoiceid DESC";
						}
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "invoices");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "advancereports-$timeStamp";
						break;
					
						
					}
					
					//// This Afflidate Report Comes from affiliate_buyer table order by site id
					case 'ebay':
					{
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						//buyer_id, amount, ebay_number, lvf, fvf, pay_email, how_to_pay, coin_id, Site_Id, user_id
						$sql = "SELECT * FROM " . DB_PREFIX . "affiliate_buyer
						WHERE Site_Id != '0'												
						";
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY Site_Id ASC";
						}
						else
						{
						$sql .= " ORDER BY Site_Id DESC";
						}
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "affiliate_buyer");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "affiliatereports-$timeStamp";
						break;
					}
					
					//
					case 'consignorstatement':
					{
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						
						$sql ="
					SELECT co.pcgs,co.Title,co.Certification_No,co.Site_Id,co.Category,pj.project_id,pj.date_added,pj.bids,pj.status FROM ".DB_PREFIX."coins co, ".DB_PREFIX."projects pj, ".DB_PREFIX."buynow_orders bo, ".DB_PREFIX."affiliate_buyer ab, ".DB_PREFIX."project_bids pb					
					WHERE co.user_id != ''
					Group BY co.coin_id 
					";
						
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY co.consignid ASC";
						}
						else
						{
						$sql .= " ORDER BY co.consignid DESC";
						}
						$fields = array(
						array('pcgs', 'PCGS'),
						array('Certification_No', 'Certification No'),
						array('Site_Id', 'Site Id'),
						array('Category', 'Category'),
						array('project_id', 'project id'),
						array('date_added', 'date added'),
						array('bids', 'bids'),
						array('status', 'status')
										);
						foreach ($fields AS $column)
						{
					
					    $fieldsToGenerate[] = $column[0];
						$headings[] = $column[1];
					
						}				
						
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "consignorstatementreports-$timeStamp";
						break;
					}
					
					//This Unsold Report Comes from projects table check whether project are closed or finsihed
					case 'unsold':
					{
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						//SELECT * FROM ilance_projects WHERE bids = 0 AND (status = 'closed' or status = 'expired' or status = 'finished')
						$sql = "SELECT * FROM " . DB_PREFIX . "projects
						WHERE bids = 0 
						AND (status = 'closed' or status = 'expired' or status = 'finished')
						AND (date_end <= '" . $endDate . "' AND date_end >= '" . $startDate . "')
						";
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY project_id ASC";
						}
						else
						{
						$sql .= " ORDER BY project_id DESC";
						}
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "projects");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "unsoldreports-$timeStamp";
						break;
					}
					
					//This Return Report Comes from Cancel Sale table check its return to seller or not
					case 'return':
					{
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						//SELECT * FROM ilance_projects WHERE bids = 0 AND (status = 'closed' or status = 'expired' or status = 'finished')
						$sql = "SELECT * FROM " . DB_PREFIX . "cancel_sale
						WHERE gc = 0						
						AND (sold_date <= '" . $endDate . "' AND sold_date >= '" . $startDate . "')
						";
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY cancel_id ASC";
						}
						else
						{
						$sql .= " ORDER BY cancel_id DESC";
						}
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "cancel_sale");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "returnreports-$timeStamp";
						break;
					}
					
					//This Raw Coin Report Comes from rawcoin table order by consign id
					case 'rawcoin':
					{
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						
						$sql = "
							SELECT *
							FROM " . DB_PREFIX . "raw_coins
							WHERE user_id != ''
							AND (End_date <= '" . $endDate . "' AND End_date >= '" . $startDate . "') ";
							
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY consignid ASC";
						}
						else
						{
						$sql .= " ORDER BY consignid DESC";
						}
						
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "raw_coins");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "rawcoinreports-$timeStamp";
						break;
					}
					
					//
					case 'referal':
					{
						
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						
						$sql = "
							SELECT *
							FROM " . DB_PREFIX . "consignments
							WHERE user_id != ''
							AND (end_date <= '" . $endDate . "' AND end_date >= '" . $startDate . "') ";
							
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY referal_id ASC";
						}
						else
						{
						$sql .= " ORDER BY referal_id DESC";
						}
						
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "consignments");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "referalreports-$timeStamp";
						break;
					
						
					}
					
					// Sales, Invoices/Statements created, Returns, Shipments. 
					case 'activity':
					{
						
						if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
						{
						$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
						$endDate = print_datetime_from_timestamp(time());
						}
						// #### date range exactly as entered
						else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
						{
						$startDate = print_array_to_datetime($ilance->GPC['range_start']);
						$startDate = substr($startDate, 0, -9);
						
						$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
						$endDate = substr($endDate, 0, -9);
						}
						
						$sql = "SELECT * FROM " . DB_PREFIX . "invoices
						WHERE status = 'paid'						
						AND (paiddate <= '" . $endDate . "' AND paiddate >= '" . $startDate . "')
						";
						if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
						{
						$sql .= " ORDER BY invoiceid ASC";
						}
						else
						{
						$sql .= " ORDER BY invoiceid DESC";
						}
						$sql_test = $ilance->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "invoices");
						$timeStamp = date("Y-m-d-H-i-s");
						$fileName = "activityreports-$timeStamp";
						break;
					}
					
				}
				
										  
				
				$fields = array();		                          
				while($arr = $ilance->db->fetch_array($sql_test))
				{						
				
					$fields[]= array($arr['Field'],$arr['Field']);
				
				}		
				
				/*$fields = array(
					array('invoiceid', 'ID'),
					array('transactionid', $phrase['_transaction_id']),
					array('status', $phrase['_status']),
					array('invoicetype', $phrase['_type']),
					array('amount', $phrase['_amount']),
					array('taxamount', $phrase['_tax']),
					array('totalamount', $phrase['_total']),
					array('paid', $phrase['_paid']),
					array('description', $phrase['_description']),
					array('user_id', 'UID'),
					array('projectid', 'PID'),
					array('createdate', $phrase['_created']),
					array('duedate', $phrase['_due']),
					array('paiddate', $phrase['_paid_date']),
					array('custommessage', $phrase['_message'])
				);*/
				
				foreach ($fields AS $column)
				{
					
					    $fieldsToGenerate[] = $column[0];
						$headings[] = $column[1];
					
				}
				
				// #### date range in the past
				/*if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
				{
					$startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
					$endDate = print_datetime_from_timestamp(time());
				}
				// #### date range exactly as entered
				else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
				{
					$startDate = print_array_to_datetime($ilance->GPC['range_start']);
					$startDate = substr($startDate, 0, -9);
					
					$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
					$endDate = substr($endDate, 0, -9);
				}*/
				
				//$sql .= " AND (end_date <= '" . $endDate . "' AND end_date >= '" . $startDate . "')";
				
				// #### display order
				/*if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
				{
					$sql .= " ORDER BY consignid ASC";
				}
				else
				{
					$sql .= " ORDER BY consignid DESC";
				}*/
				
				//echo $sql;				
				$data = $ilance->admincp->fetch_reporting_fields($sql, $fieldsToGenerate);			
				switch ($action)
				{
					case 'csv':
					default:
					{
						$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
						break;
					}
					case 'tsv':
					{
						$reportoutput = $ilance->admincp->construct_tsv_data($data, $headings);
						break;
					}                                   
					
				}
				
				//$timeStamp = date("Y-m-d-H-i-s");
				//$fileName = "reports-$timeStamp";
				if ($action == 'csv')
				{
					header("Pragma: cache");
					header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
					header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
					echo $reportoutput;
					die();
				}
				else if ($action == 'tsv')
				{
					header("Pragma: cache");
					header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
					header("Content-Disposition: attachment; filename=" . $fileName . ".txt");
					echo $reportoutput;
					die();
				}
			}            
			$range = $ilance->GPC['range'];
			$rangepast = $ilance->GPC['rangepast'];
		}
		else
		{
			$show['showreportoutput'] = false;
		}         
   	}
	
	// #### reporting action #######################################
	
	$reportaction = '<select name="action" style="font-family: verdana">'; 
	$reportaction .= '<option value="csv"'; 
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'csv')
	{
		$reportaction .= ' selected="selected"';
	}
	$reportaction .= '>' . $phrase['_download_comma_delimited_file'] . '</option>';
	$reportaction .= '<option value="tsv"';
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'tsv')
	{
		$reportaction .= ' selected="selected"'; 
	}
	$reportaction .= '>' . $phrase['_download_tab_delimited_file'] . '</option></select>';
	
	
		
	
	// #### date range #############################################
	$radiopast = '<input type="radio" name="range" value="past"'; 
	if ((!isset($ilance->GPC['action']) OR (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past")))
	{
		$radiopast .= ' checked="checked"';
	}
	$radiopast .= '>';
	$radioexact = '<input type="radio" name="range" value="exact"'; 
	if ((!isset($ilance->GPC['action']) OR (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "exact")))
	{
		$radioexact .= ' checked="checked"';
	}
	$radioexact .= '>';
	$reportrange = '<select name="rangepast" style="font-family: verdana"><option value="-1 day"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>The Past Day</option><option value="-1 week"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>The Past Week</option><option value="-1 month"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>The Past Month</option><option value="-1 year"'; 
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>The Past Year</option></select>';
	
	// #### advanced reporting from range ##########################
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
   
		// #### order by ascending / desending #########################
	$reportorderby = '<input type="radio" name="order" value="ascending"';
	if (!isset($ilance->GPC['action']) OR $ilance->GPC['order'] == "ascending")
	{
		$reportorderby .= ' checked="checked"'; 
	}
	$reportorderby .= '>' . $phrase['_ascending'] . ' &nbsp;&nbsp;&nbsp; <input type="radio" name="order" value="descending"';
	if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == "descending")
	{
		$reportorderby .= ' checked="checked"';
	}
	$reportorderby .= '>' . $phrase['_descending'];
	
	
	// murugan changes on mar 12 
		
		
		$user_down = '<select name="user_down" id="user_down" >
								              <option value="" selected="selected">Select</option>';
										$con_date = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "consignments	
										GROUP BY user_id
										ORDER BY user_id
										");
										$datecount = 0;							            
							            if($ilance->db->num_rows($con_date) > 0)
										{
										      
											    while($res_date = $ilance->db->fetch_array($con_date))
												{
												
												 										  
												 $user_down.='<option value="'.$res_date['user_id'].'">'.fetch_user('username',$res_date['user_id']).'</option>';												
												 $datecount++;
												}
										}
										
		$user_down.='</select>';
 	
	
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'statement')
		{
			$_SESSION['ilancedata']['user']['userstatement'] =  $ilance->GPC['user_down'];
			//$user_statement = $_SESSION['ilancedata']['user']['userstatement'];
			
		}
 		$area_title = 'Conginer Statement';
		$page_title = SITE_NAME . ' - ' . 'Statement';
		$user_id = $_SESSION['ilancedata']['user']['userstatement'];
			
		$date_down = '<select name="date_down" id="date_down" >
								              <option value="" selected="selected">Select</option>';
										$con_date = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coins 										
										WHERE user_id = '".$_SESSION['ilancedata']['user']['userstatement']."'
										AND End_Date != '0000-00-00'										
										GROUP BY date(End_Date)
										");
										$datecount = 0;							            
							            if($ilance->db->num_rows($con_date) > 0)
										{
										      
											    while($res_date = $ilance->db->fetch_array($con_date))
												{
												
												  $date_coin = explode('-',$res_date['End_Date']);
										          $date_day = explode(' ',$date_coin[2]);
									              $month_name = $date_day[0].'-'.$date_coin[1].'-'.$date_coin[0]; 
												  $month_namev = $date_coin[0].'-'.$date_coin[1].'-'.$date_day[0];
											
																							
												$con_date_co = $ilance->db->query("
												SELECT COUNT(*) AS endcount
												FROM " . DB_PREFIX . "coins 
												WHERE user_id = '".$_SESSION['ilancedata']['user']['userstatement']."' 
												AND date(End_Date) = '".$month_namev."'
												
										
												");
												$res_date_co = $ilance->db->fetch_array($con_date_co);
												$item_count = $res_date_co['endcount'];
												  
												 $date_down.='<option value="'.$month_namev.'">'.$month_name.' <b>('.$item_count.' items)</b></option>';												
												 $datecount++;
												}
										}
										
		$date_down.='</select>';
		
		
		
		if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
		{
		  
		  $userstatementid = $ilance->GPC['userstatementid'];
		  
		  $dateexp = explode('-',$ilance->GPC['date_down']);
		  $ilance->GPC['year'] = $dateexp[0];
		  $ilance->GPC['month'] = $dateexp[1];
		  $ilance->GPC['day'] = $dateexp[2];
		   if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
            {
                       		$validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
							$validdate1 = intval($ilance->GPC['month']) . '-' . $ilance->GPC['day'] . '-' . $ilance->GPC['year'];
							
							$settledate = intval($ilance->GPC['month']) + 1 . '-' . $ilance->GPC['day'] . '-' . $ilance->GPC['year'];
							$date1 =  $validdate1;
							$date =  date('Y-m-d',strtotime( $validdate));
			}
			
			$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
						$day = $ilance->GPC['day'];
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				$month = $ilance->GPC['month'];
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
				
				$year = $ilance->GPC['year'];
				for ($k = $year;$k > $year-5;$k--) 
				{
   				$s = ($k == $year)?' selected':'';
   					$yearlist .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
				}
				
		  $show['no_statement'] = false;
		   $select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs			
					WHERE co.user_id = '".$userstatementid."'
					AND date(co.End_Date) = '".$ilance->GPC['date_down']."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no					
					GROUP BY co.coin_id
					ORDER BY cs.coin_series_sort,
					cc.coin_detail_year
					
					");	
		
					
		}
		else
		{
		
		$show['no_statement'] = false;
		
		$date1 = date('m-d-Y');
		$date = DATETODAY;
		$settledate = date(date('m') + 1 . '-' . date('d') . '-' . date('Y'));
		$select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs		
					WHERE co.user_id = '".$_SESSION['ilancedata']['user']['userstatement']."'
					AND date(co.End_Date) = '".$date."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no
					GROUP BY co.coin_id
					ORDER BY cs.coin_series_sort,
					cc.coin_detail_year
					");
		
		}
		$listcount = $ilance->db->num_rows($select);
		
		if ($ilance->db->num_rows($select) > 0)
         {
				$show['statement'] = true;							
				$row_count = 0;				
				
                 while ($res = $ilance->db->fetch_array($select))
                  {
				   	if($res['project_id'] == '0')
					{
						$res['bids'] = '-';
						$res['fvf'] = '-';
						$res['bidamount'] = '-';
						$res['binamount'] = '-';
						$res['seller_fee'] = '-';
						$res['listing_fee'] = '-';
						$res['net_consignor'] = '-';
						
					}
					else
					{
						$selectbid = $ilance->db->query("SELECT MIN(bidamount) AS bidamount, MAX(bidamount) AS final,count(*) AS count FROM ".DB_PREFIX."project_bids			
														WHERE project_id = '".$res['project_id']."'
														");
						$selectbin = $ilance->db->query("SELECT SUM(amount) AS binamount, SUM(qty) AS qty FROM ".DB_PREFIX."buynow_orders			
														WHERE project_id = '".$res['project_id']."'
														");
						$selectpjt = $ilance->db->query("SELECT insertionfee, fvf, featured, highlite, bold FROM ".DB_PREFIX."projects			
														WHERE project_id = '".$res['project_id']."'
														");
														
						$selectinvoice = $ilance->db->query("SELECT SUM(amount) AS newfvf FROM ".DB_PREFIX."invoices			
						WHERE projectid = '".$res['project_id']."'
						AND isfvf = '1'
						");
						// murugan changes on jun 24 								
						$enhancementfee = $ilance->db->query("SELECT SUM(amount) AS newenhance FROM ".DB_PREFIX."invoices			
						WHERE projectid = '".$res['project_id']."'
						AND isenhancementfee = '1'
						");
						$result = $ilance->db->fetch_array($selectbid, DB_ASSOC);
						$result1 = $ilance->db->fetch_array($selectbin, DB_ASSOC);
						$resultpjt = $ilance->db->fetch_array($selectpjt, DB_ASSOC);
						
						$resultinvoice = $ilance->db->fetch_array($selectinvoice, DB_ASSOC);
						
						// murugan june 24 
						$resenhancementfee = $ilance->db->fetch_array($enhancementfee, DB_ASSOC);
						
						 // miscellaneous Calculatation Murugan on jun 4 
						$misselect = $ilance->db->query("SELECT amount,invoicetype FROM ". DB_PREFIX ."invoices
				  						WHERE user_id ='".$userstatementid."'
				  						AND projectid = '".$res['project_id']."'
				  						AND ismis = 1 ");
							
						if ($ilance->db->num_rows($misselect) > 0)
						{
							$resmis = $ilance->db->fetch_array($misselect, DB_ASSOC);
							//murugan july 7
							if($resmis['invoicetype'] == 'debit')
							{
								$misdebit[] = $resmis['amount'];
							}
							if($resmis['invoicetype'] == 'credit')
							{
								$miscredit[] = $resmis['amount'];
							}							
							$miscell[] = $resmis['amount'];
							//$misamt = $ilance->currency->format($resmis['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$miscell[] = 0;
							$miscredit[] = 0;
							$misdebit[] = 0;
							//$misamt = $ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']);
						}
						
						
						// Featured fee Amount 
						if($resultpjt['featured'] !=0)
						{
							$featured = $ilconfig['productupsell_featuredfee'];
						}
						else
						{
						 	$featured = '0.00';
						}
						// highlite fee amount
						if($resultpjt['highlite'] !=0)
						{
							$highlite = $ilconfig['productupsell_highlightfee'];
						}
						else
						{
						 	$highlite = '0.00';
						}
						
						// bold fee amount
						if($resultpjt['bold'] !=0)
						{
							$bold = $ilconfig['productupsell_boldfee'];
						}
						else
						{
						 	$bold = '0.00';
						}
						// Total Amount (insertionfee , bold,highlight,featured)
						//$listfeetotal = $resultpjt['insertionfee'] + $featured + $highlite + $bold;
						// july 12
						$listfeetotal = $resultpjt['insertionfee'] + $resenhancementfee['newenhance'];
						
						//$totfvf[] = $resultpjt['fvf'];
						$totfvf[] = $resultinvoice['newfvf'];
						//$totins[] = $resultpjt['insertionfee'] + $featured + $highlite + $bold;
						// july 12
						$totins[] = $resultpjt['insertionfee'] + $resenhancementfee['newenhance'];
						$res['bids'] = $result['count'];
						$bidtot[] = $result['count'];
						
						
						if($res['Minimum_bid'] != '')
						{						
							//if($res['bidamount'] != '')
							//$test5[] = $result['bidamount'];
							//$res['bidamount'] = $ilance->currency->format($result['bidamount'],$ilconfig['globalserverlocale_defaultcurrency']);
							$test5[] = $res['Minimum_bid'];
							$res['bidamount'] = $ilance->currency->format($res['Minimum_bid'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
						  	$res['bidamount'] = '0.00';
						}
						
						/*if($result1['binamount'] != '')
						{						
							$test4[] = $result1['binamount'];
							$res['binamount']  = $ilance->currency->format($result1['binamount'],$ilconfig['globalserverlocale_defaultcurrency']);
							
						}*/
						if($res['Buy_it_now'] != '')
						{						
							$test4[] = $res['Buy_it_now'];
							$res['binamount']  = $ilance->currency->format($res['Buy_it_now'],$ilconfig['globalserverlocale_defaultcurrency']);
							
						}
						else
						{
						  	$res['binamount']  = '0.00';
						}
						if($result['final'] != '')
						{
							$res['finalprice'] = $result['final'];
							$res['qty'] = '';
							$res['fvf'] = $ilance->currency->format($result['final'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$res['finalprice'] = $result1['binamount'];
							if($result1['qty'] > 1)
							$res['qty'] = '<b>('.$result1['qty'].')</b>';
							else
							$res['qty'] = '';
							$res['fvf'] = $ilance->currency->format($result1['binamount'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						// Total Final price
						$totfinal[] = $res['finalprice'];
						
						//$res['seller_fee'] = $ilance->currency->format($resultpjt['fvf'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res['seller_fee'] = $ilance->currency->format($resultinvoice['newfvf'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res['listing_fee'] = $ilance->currency->format($listfeetotal,$ilconfig['globalserverlocale_defaultcurrency']);
						/*if($result['bidamount'] != '')
						{
							
							 $res['net_consignor1'] = $result['final'] - ($res['seller_fee'] + $resultpjt['fvf'] + $listfeetotal);
							 $res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 
						}
						if($result1['binamount'] != '')
						{
							
							$res['net_consignor1'] = $result1['binamount'] - ($res['seller_fee'] + $resultpjt['fvf'] + $listfeetotal);
							$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						if($result['bidamount'] == '' AND $result1['binamount'] == '' )
						{
							$res['net_consignor1'] =  ($res['seller_fee'] + $resultpjt['fvf'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = '<span class="red">US$ -'.$res['net_consignor1']. '</span>';
							else
							$res['net_consignor'] = 'US$0.00';
						}
*/
					if($result['bidamount'] != '')
						{							
							 $res['net_consignor1'] = $result['final'] - ( $resultinvoice['newfvf'] + $listfeetotal);
							 if($res['net_consignor1'] > 0)
							 $res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
							 
						}
						if($result1['binamount'] != '')
						{
							
							$res['net_consignor1'] = $result1['binamount'] - ( $resultinvoice['newfvf'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
						}
						if($result['bidamount'] == '' AND $result1['binamount'] == '' )
						{
							$res['net_consignor1'] =  ($resultinvoice['newfvf'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = '- US$'.$res['net_consignor1']. '.00';
							else
							$res['net_consignor'] = 'US$0.00';
						}
						$test[] = $res['net_consignor1'];
						//$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
						//$res['net_consignor'] = 'US$'.$res['net_consignor1'];
					}
				  		
					if($res['Site_Id'] == '0')
					{
					  $res['Site_Id'] ='GC';
					  $res['Title'] = $res['Title'];
					  $res['stateid'] = $res['coin_id'];
					}
					else
					{
					$sitesel = $ilance->db->query("
					SELECT site_name FROM ".DB_PREFIX."affiliate_listing				
					WHERE id = '".$res['Site_Id']."'					
					");
						$siteres = $ilance->db->fetch_array($sitesel, DB_ASSOC);
					 $res['Site_Id'] =$siteres['site_name'];
					 $res['Title'] = $res['Title'];
					$res['stateid'] = $res['coin_id'];
					}										
					$statement[] = $res;
					$row_count++;
				  }
				  // Advance Calculateion
				  
				 	
				  $advanceselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "user_advance WHERE statusnow = 'paid' AND user_id ='".$_SESSION['ilancedata']['user']['userstatement']."'");
				  $advanceres = $ilance->db->fetch_array($advanceselect);
				  // Feb 23 for consignor statement changes 
				  $sum_inset = array_sum($totins);
				  $sum_finalvaluefe = array_sum($totfvf);
				  $sum_totfinalval = array_sum($totfinal);
				  $newnettotal = $sum_totfinalval - $sum_finalvaluefe - $sum_inset;				  
				  //$totnet_consignor = $ilance->currency->format(array_sum($test),$ilconfig['globalserverlocale_defaultcurrency']);
				  
				  
				  $totnet_consignor = 'US$'.$newnettotal;
				  //$totseller_fee = $ilance->currency->format(array_sum($test1),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totseller_fee = $ilance->currency->format(array_sum($totfvf),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totlisting_fee = $ilance->currency->format(array_sum($totins),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totfvf = $ilance->currency->format(array_sum($totfinal),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totbinamount = $ilance->currency->format(array_sum($test4),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totbidamount = $ilance->currency->format(array_sum($test5),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totbids = array_sum($bidtot);				
				  $total_advance = $ilance->currency->format($advanceres['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
				  
				 // murugan changes on july 12
				 
				  $mis_totdebit = array_sum($misdebit);
				  $mis_totcredit = array_sum($miscredit);
				  	$miscellan = array_sum($miscell);
					$mis_total =  $mis_totdebit - $mis_totcredit;
					if($mis_total > 0)
					{
					$tot_mis = $ilance->currency->format($mis_total,$ilconfig['globalserverlocale_defaultcurrency']);
					}
					else
					{
					$tot_mis = 'US$'.number_format(abs($mis_total), 2, '.', '');
					}
				 
				  // murugan FEB 23
				  				
				  //$lastamountvalue = array_sum($test) - $advanceres['amount'];
				  
					// murugan changes on july 12
					
				  //$lastamountvalue = $newnettotal - $advanceres['amount'];
				  
				  $lastamountvalue = $newnettotal - $advanceres['amount'] - $mis_totcredit + $mis_totdebit;
				  //$lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
				   if($lastamountvalue > 0)
							 $lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							  $lastamount = '- US$'.abs($lastamountvalue). '';
				  //$lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
				 // $lastamount = 'US$'.$lastamountvalue;
				  $statecount = '('.$listcount.' Items), will settle on '.$settledate .' ('.$lastamount.')';
				 
		 }
		 else
		 {
		 	$show['no_statement'] = true;
		 }
		 // Accounting Report Work on March 14 By Murugan
		 		$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
						$day = date('d')-1;
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
				
				$year = date("Y");
				for ($k = $year+1;$k > 2008;$k--) 
				{
   				$s = ($k == $year)?' selected':'';
   					$yearlist .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
				}
				$yearlist .='</select>';
				// Date Month Year End
				
				$daylist1 = '';
				$monthlist1 = '';
				$yearlist1 = '';
				$daylist1 .='<select name="day1" id="day"><option value="">DATE</option>';
						$day = date('d')-1;
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist1 .= "<option value='$i' selected>$i</option>";
						else
						$daylist1 .= "<option value='$i'>$i</option>";
	
				$daylist1 .='</select>';
				
				$monthlist1 .='<select name="month1" id="month"><option value="">MONTH</option>';
				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist1 .= "<option value='$j' selected>$j</option>";
						else
						$monthlist1 .= "<option value='$j'>$j</option>";
						
						
				$monthlist1 .= '</select>';
				
				$yearlist1 .= '<select name="year1" id="year"><option value="">YEAR</option>';
				
				
				$year = date("Y");
				for ($k = $year+1;$k > 2008;$k--) 
				{
   				$s = ($k == $year)?' selected':'';
   					$yearlist1 .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
				}
				$yearlist1 .='</select>';
				// Date1 Month1 Year1 End
				if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'account')
				{
					$show['showoutput'] = true;
					if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
            			{
                       		$startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
							$start =  date('Y-m-d',strtotime( $startdate));
							$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
							$end =  date('Y-m-d',strtotime( $enddate));
					}
					// Buy now
					$query1 = $ilance->db->query("SELECT COUNT(*) AS buycount, SUM(amount) AS buyamount FROM " . DB_PREFIX . "buynow_orders
												WHERE date(orderdate) >= '".$start."' AND date(orderdate) <= '".$end."' ");
					$res1 = $ilance->db->fetch_array($query1);
					// Auction
					
					$query2 = $ilance->db->query("SELECT COUNT(*) AS auctioncount, SUM(bidamount) AS auctionamount FROM " . DB_PREFIX . "project_bids
												WHERE date(date_awarded) >= '".$start."' AND date(date_awarded) <= '".$end."' ");
					$res2 = $ilance->db->fetch_array($query2);
					
					$query3 = $ilance->db->query("SELECT SUM(taxamount) AS taxamount FROM " . DB_PREFIX . "invoices
												WHERE status = 'paid'
												AND combine_project 
												AND date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."' ");
					$res3 = $ilance->db->fetch_array($query3);
					
					$query4 = $ilance->db->query("SELECT SUM(amount) AS fvfamount FROM " . DB_PREFIX . "invoices
												WHERE isfvf = '1'
												AND date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."' ");
					$res4 = $ilance->db->fetch_array($query4);
					
					$query5 = $ilance->db->query("SELECT SUM(amount) AS ifamount FROM " . DB_PREFIX . "invoices
												WHERE isif = '1'
												AND date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."' ");
					$res5 = $ilance->db->fetch_array($query5);
					
					$query6 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."'
												 GROUP BY projectid ");
					if($ilance->db->num_rows($query6 )>0)
					{
						while($res = $ilance->db->fetch_array($query6))
						{
							$selectpjt = $ilance->db->query("SELECT * FROM ".DB_PREFIX."projects			
														WHERE project_id = '".$res['projectid']."'");
							$resultpjt = $ilance->db->fetch_array($selectpjt, DB_ASSOC);
							if($resultpjt['featured'] !=0)
							{
							$featured = $ilconfig['productupsell_featuredfee'];
							}
							else
							{
						 	$featured = '0.00';
							}
						// highlite fee amount
							if($resultpjt['highlite'] !=0)
							{
							$highlite = $ilconfig['productupsell_highlightfee'];
							}
							else
							{
						 	$highlite = '0.00';
							}
						
						// bold fee amount
							if($resultpjt['bold'] !=0)
							{
							$bold = $ilconfig['productupsell_boldfee'];
							}
							else
							{
						 	$bold = '0.00';
							}
							// buyer fee
						/*	if($ilconfig['staffsettings_feeinnumber'] != 0 AND $resultpjt['filtered_auctiontype'] == 'regular')
							{
								$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];
							}
							else
							{
								$buyerfee_calnum = 0;
							}
							if($ilconfig['staffsettings_feeinpercentage'] != 0 AND $resultpjt['filtered_auctiontype'] == 'regular')
							{
								$buyerfee_calper = ($res['amount'] * ($ilconfig['staffsettings_feeinpercentage'] / 100));
							}
							else
							{
								$buyerfee_calper = 0;
							}
							if($buyerfee_calnum <= $buyerfee_calper )
							{
								$buyerfee1 = $buyerfee_calper;
							
							}
							else
							{
								$buyerfee1 = $buyerfee_calnum;
								
							}*/
							// buyer fee end
							//echo $res['projectid'].'amount'.$listfeetotal = $resultpjt['insertionfee'] + $featured + $highlite + $bold;							
							//$totbuyer[] = $buyerfee1;
							$totfvf[] = $resultpjt['fvf'];
							$totins[] = $resultpjt['insertionfee'] + $featured + $highlite + $bold;
	
						}
						
					}
					$buyer_fee = $ilance->db->query("SELECT SUM(amount) AS buyer_amt FROM " . DB_PREFIX . "invoices
												WHERE isbuyerfee = '1'
												AND date(createdate) >= '".$start."' AND date(createdate) <= '".$end."' ");
					$res_buyerfee = $ilance->db->fetch_array($buyer_fee);
					
					$query7 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "user_advance
												 WHERE date(date_made) >= '".$start."' AND date(date_made) <= '".$end."'
												 AND statusnow = 'paid'
												 ");
					if($ilance->db->num_rows($query7) > 0)
					{
						$today = DATETODAY;
						while($resadv = $ilance->db->fetch_array($query7))
						{
							   $date_parts1=explode("-", $resadv['date_made']);
							   $date_parts2=explode("-", $today);
							   //gregoriantojd() Converts a Gregorian date to Julian Day Count
							   $start_date=gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
							   $end_date=gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
							   $diffdate =  $end_date - $start_date;
							   
							   $advtot[] = ($resadv['amount'] * ($resadv['interest']/100) * ($diffdate / 365)) .'<br>';
							
						}
					}
					
					$query8 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoice_projects
					 							WHERE date(created_date) >= '".$start."' AND date(created_date) <= '".$end."'
												 GROUP BY final_invoice_id
												 ");
					
					if($ilance->db->num_rows($query8) > 0)
					{
						while($res8 = $ilance->db->fetch_array($query8))
						{							
							$shipping_cost[] = $res8['shipping_cost'];
						}
					}
					$query9 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoice_projects
					 							WHERE date(created_date) >= '".$start."' AND date(created_date) <= '".$end."'
												 AND promocode != ''												 
												 ");
					
					if($ilance->db->num_rows($query9) > 0)
					{
						while($res9 = $ilance->db->fetch_array($query9))
						{
							$disount_cost[] = $res9['disount_val'];
						}
					}
					
					//karthik on aug31 removing "status='unpaid'" from this query
					$query10 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												 AND user_id 
												 AND p2b_user_id
											
												  ");
												 
					if($ilance->db->num_rows($query10) > 0)
					{
						while($res10 = $ilance->db->fetch_array($query10))
						{
							$inv_owing[] = $res10['totalamount'];
						}
					}
					
					$query11 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												 AND user_id 												 
												 AND status = 'paid'
												  ");
												 
					if($ilance->db->num_rows($query11) > 0)
					{
						while($res11 = $ilance->db->fetch_array($query11))
						{
							$tot_pay[] = $res11['totalamount'];
						}
					}
					
					$query12 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												 AND user_id 
												 AND paymethod
												 AND status = 'paid'
												  ");
												 
					if($ilance->db->num_rows($query12) > 0)
					{
						while($res12 = $ilance->db->fetch_array($query12))
						{
							if($res12['paymethod'] == 'paypal')
							{
								$tot_paypal[] = $res12['totalamount'];
							}
							if($res12['paymethod'] == 'check')
							{
								$tot_check[] = $res12['totalamount'];
							}
							if($res12['paymethod'] == 'bank')
							{
								$tot_bank[] = $res12['totalamount'];
							}
							if($res12['paymethod'] == 'creditcard')
							{
								$tot_card[] = $res12['totalamount'];
							}
							
						}
					}
					
					$query13 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												 AND user_id 
												 AND p2b_user_id != user_id												 
												 AND p2b_user_id !=0
												 AND status = 'paid'
												  ");
					if($ilance->db->num_rows($query13) > 0)
					{
						$count_paid = $ilance->db->num_rows($query13);
						while($res13 = $ilance->db->fetch_array($query13))
						{
							$tot_paid[] = $res13['totalamount'];
						}
					}
					
					$query14 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												 AND user_id 
												 AND p2b_user_id != user_id												 
												 AND p2b_user_id !=0
												 AND status = 'unpaid'
												  ");
					if($ilance->db->num_rows($query14) > 0)
					{
						while($res14 = $ilance->db->fetch_array($query14))
						{
							$tot_unpaid[] = $res14['totalamount'];
						}
					}
					
					$query15 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												 AND user_id 
												 AND p2b_user_id != user_id												 
												 AND p2b_user_id !=0
												 AND status = 'paid'
												 AND invoicetype = 'advance'
												  ");
					if($ilance->db->num_rows($query15) > 0)
					{
						while($res15 = $ilance->db->fetch_array($query15))
						{
							$tot_advpaid[] = $res15['totalamount'];
						}
					}
					
					$showoutput = ' <table align = "center" height = "600" width = "400">
									<tr>
									<th>
									STATISTICS
									</th>
									</tr>
									
									<tr>
									<td>
									<strong>'.$res2['auctioncount'].'</strong> of Auctions Sold
									</td>
									</tr>
									
									<tr>
									<td>
									<strong>'.$res1['buycount'].'</strong> of Buy Now Sold
									</td>
									</tr>
									
									<tr>
									<td>
									&nbsp;
									</td>
									</tr>
									
									<tr>
									<th>
									SALES MADE
									</th>
									</tr>
									
									<tr>
									<td>
									 Total <strong>'.$ilance->currency->format($res2['auctionamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Auctions Sold
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format($res1['buyamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Buy Now Sold
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format($res_buyerfee['buyer_amt'],$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Buyers Fee
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($totins),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Listing Fees
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($totfvf),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of sellers Fees
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format($res3['taxamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Sales Tax Charged
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($shipping_cost),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Shipping/Handling Charged
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($disount_cost),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Promo Codes Redeemed
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($advtot),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Advance Interest
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($inv_owing),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Invoices Owing (at end Date)
									</td>
									</tr>
									
									<tr>
									<td>
									&nbsp;
									</td>
									</tr>
									
									<tr>
									<th>
									PAYMENTS RECEIVED
									</th>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_pay),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Payments Received
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_paypal),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Paypal
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_card),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Credit Card
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_check),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Check / Money Order
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_bank),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Wire
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Trade Against Consignor Proceeds
									</td>
									</tr>
									
									<tr>
									<td>
									&nbsp;
									</td>
									</tr>
									
									<tr>
									<th>
									CONSIGNORS PAID
									</th>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$count_paid.'</strong> of Consignors Paid
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_paid),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Consignor Payments (Checks)
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_unpaid),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Consignor Payments Owing (at end Date)
									</td>
									</tr>
									
									<tr>
									<td>
									Total <strong>'.$ilance->currency->format(array_sum($tot_advpaid),$ilconfig['globalserverlocale_defaultcurrency']).'</strong> of Consignor Advances Owing (at end Date)
									</td>
									</tr>
									
									</table>';
					
				}
	
//Karthik may 04
	            if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'statement_print')
		       {
			        $show['showoutput'] = true;
					if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
            			{
						
                       		$startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
							 $start =  date('Y-m-d',strtotime( $startdate));
							$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
							 $end =  date('Y-m-d',strtotime( $enddate));
					    }
						if(!empty($ilance->GPC['user_id']))
						{
						$user_id_query='&user_id='.$ilance->GPC['user_id'];
						}
					if(isset($ilance->GPC['print']))
					 header("Location:statement_pdf.php?start_date=".$start."&end_date=".$end."&option=".$ilance->GPC['display'].$user_id_query);
					else if(isset($ilance->GPC['summary']))
					 header("Location:statement_pdf_summary.php?start_date=".$start."&end_date=".$end."&option=".$ilance->GPC['display'].$user_id_query);
								     
				 
			   }
			   
			   
			     if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'statement_print_new')
		       {
			        $show['showoutput'] = true;
					if (!empty($ilance->GPC['start_date']))
            			{
						$user_id_query='';
						
						if(!empty($ilance->GPC['user_id']))
						{
						$user_id_query='&user_id='.$ilance->GPC['user_id'];
						}
					if(isset($ilance->GPC['print']))
					  header("Location:statement_new.php?start_date=".$ilance->GPC['start_date']."&option=".$ilance->GPC['display'].$user_id_query);
					else if(isset($ilance->GPC['summary']))
					  header("Location:statement_new_summary.php?start_date=".$ilance->GPC['start_date']."&option=".$ilance->GPC['display'].$user_id_query);
						  
                       		/*$startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
							 $start =  date('Y-m-d',strtotime( $startdate));
							$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
							 $end =  date('Y-m-d',strtotime( $enddate));*/
					    }
			   }
			//karthik end may04
			
			
			//Karthik on aug18 for pending invoices pdf
	            if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'pending_invoices')
		       {
			        $show['showoutput'] = true;
				
			      header("Location:pending_pdf.php");
			   }
			//karthik end  on aug18 for pending invoices pdf
			
			//Karthik on Dec04 for Sales Tax PDF 
	            if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'sales_tax')
		       {
			        $startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
					$start =  date('Y-m-d',strtotime( $startdate));
					$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
					 $end =  date('Y-m-d',strtotime( $enddate));
				
			      header("Location:salestax_pdf.php?start_date=".$start."&end_date=".$end."");
			   }
			//karthik end  on Dec04 for Sales Tax PDF 
			
			
			
			//Karthik on Dec05 for All Sales Tax CSV 
	            if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'all_sales_tax')
		       {
			        $startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
					$start =  date('Y-m-d',strtotime( $startdate));
					$enddate = intval($ilance->GPC['year1']) . '-' . $ilance->GPC['month1'] . '-' . $ilance->GPC['day1'];							
					 $end =  date('Y-m-d',strtotime( $enddate));
				
			      header("Location:all_salestax_csv.php?start_date=".$start."&end_date=".$end."");
			   }
			//karthik end  on Dec05  for All Sales Tax CSV 
			
			//multi_buynow PDF 
	            if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'multi_buynow')
		       {
			      header("Location:buynow_report_pdf.php");
			   }
			//end for multi_buynow PDF 
			
			//sekar start here: working on june 10
			
			//new change on Dec-06
		/*
		//vijay work for Specific Reports on 4-9-2015- Regarding bug id:#5978// */

		if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consignorstatus')
		{
			if($ilance->GPC['filterby']=='username' )
			{
				$sql = $ilance->db->query("
					SELECT user_id, username, email
					FROM " . DB_PREFIX . "users
					WHERE  username = '".$ilance->GPC['filtervalue']."'
					");
				$res = $ilance->db->fetch_array($sql);
				$userid = $res['user_id'];
				$user_name = $res['username'];
				$email_id = $res['email'];			
			}		 
			else
			{
				$userid = $ilance->GPC['filtervalue'];
			}
			
			if(!empty($ilance->GPC['end_date']))
			{
				$condition="AND date(c.End_Date) = '".$ilance->GPC["end_date"]."'";
				$end_date = "End Date:".date('d-m-Y',strtotime($ilance->GPC["end_date"]));
				
			}
			else
			{
				$condition='';
				$end_date='';
			}
			
			

			
      $sql="SELECT c.coin_id,c.cost,c.Title,p.currentprice,c.Alternate_inventory_No,c.pcgs as pcgs,c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,p.coin_series_unique_no,p.coin_series_denomination_no,count(w.user_id) as watchcount 
			FROM " . DB_PREFIX . "coins c 
			left join " . DB_PREFIX . "projects p on c.coin_id=p.project_id AND p.status='open' AND ((p.filtered_auctiontype = 'regular' AND p.winner_user_id  = '0') OR (p.buynow = '1' AND p.filtered_auctiontype = 'fixed'  AND p.buynow_qty > '0'))
			left join " . DB_PREFIX . "watchlist w on c.coin_id=w.watching_project_id
			left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS 
			WHERE c.user_id = '".$userid."' 
			$condition
			group by c.coin_id
			ORDER BY  cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC ";

			

			
			$consinorslist = $ilance->db->query($sql);

			$listing_items = '<table border="0"><tr>
			<td width="70px">Seller Name:'.$user_name.'</td>
			<td width="70px">Seller Email:'.$email_id.'</td>
			<td width="50px">'.$end_date.'</td>
			
			</tr>
			</table>
			<table border="1">
			<tr>
			<td>Item ID</td>
			<td>Title</td>
			<td>PCGS</td>
			<td>Current Bid</td><td width="20px">Secret Max Bid</td><td width="20px">Cost</td><td>Alt Inventory</td><td width="20px>APR</td><td>Watchers</td>
			';
			
			
			if($ilance->db->num_rows($consinorslist)>0)
			{
				while($listing = $ilance->db->fetch_array($consinorslist))
				{
					
					$projectid = $listing['coin_id'];
					$projecttitle = $listing['Title'];
					$current_price = $listing['currentprice']; 
					$Alt_inventory_No = $listing['Alternate_inventory_No'];
					if($listing['watchcount']==0)
					{
						$Watchers ="";
					}else
					{
						$Watchers = $listing['watchcount'];
					}

					$sql1="SELECT MAX(maxamount) as high,project_id 
					FROM " . DB_PREFIX . "proxybid pr
					where pr.project_id ='".$projectid."'";
					

					$consinorslist_max = $ilance->db->query($sql1);
					$max_sec_bid = $ilance->db->fetch_array($consinorslist_max);
					
					
					$coin_apr=get_coin_history_price($listing['coin_id'],$listing['pcgs'],$listing['Grade'],$listing['Grading_Service'],$listing['Cac'],$listing['Star'],$listing['plus'],$listing['coin_series_unique_no'],$listing['coin_series_denomination_no']);
					
					
					

					
					$listing_items.= '<tr><td>'.$projectid.'</td><td>'.$projecttitle.'</td><td>'.$listing['pcgs'].'</td>
					<td>'.$current_price.'</td><td>'.$max_sec_bid['high'].'</td><td>'.$listing['cost'].'</td><td>'.$Alt_inventory_No.'</td><td>'.$coin_apr.'</td>
					<td>'.$Watchers.'</td>';

					$totalcurrentbids+=$listing['currentprice'];
					$totalhighbids+=$max_sec_bid['high'];
					$totalcost+=$listing['cost'];
					
					
					
				}
			}
			
			
			
			
			$listing_items.='</table>';

			$listing_items.='<table>
				<tr>						
				<td size="11" >Total of Current Bids : <b>'.$ilance->currency->format($totalcurrentbids).'</b></td>
				</tr> 
			    <tr>
				<td size="11" >Total of High Bids : <b>'.$ilance->currency->format($totalhighbids).'</b></td>
				</tr> 
				<tr>
				<td size="11" >Total of Cost : <b>'.$ilance->currency->format($totalcost).'</b></td>
				</tr> 
				</table>';

			$listing_items.='<br/><br/></table>';
			
			
			
			define('FPDF_FONTPATH','../font/');
			
			require('pdftable_1.9/lib/pdftable.inc.php');
			
			$p = new PDFTable();
			
			$p->AddPage();
			
			$p->setfont('times','',10);
			
			$p->htmltable($listing_items);
			
			$p->output('consignor_status_'.date('Y-m-d h-i-s').'.pdf','D');  
			
			
		}
		

		
		/*
		//vijay work for Specific Reports ends// */

	
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consignorlist')
	{

		$filtervalue = $ilance->GPC['filtervalue'];

			if($ilance->GPC['filterby']=='username' OR $ilance->GPC['filterby']=='email')
			{
		   $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
								WHERE  username = '".$ilance->GPC['filtervalue']."'
								 OR    email    = '".$ilance->GPC['filtervalue']."'
                        ");
			$res = $ilance->db->fetch_array($sql);
			$userid = $res['user_id'];			
			}		 
       		else
			{
				 $userid = $ilance->GPC['filtervalue'];
			}
	    if($ilance->GPC["excsold"]=="old")
		{
		$condition="AND c.End_Date between '".NINETYDAYSAGO."' AND '".DATETODAY."'";
		$cond = '';	
		$conds = '';	
		}
		else
		{
		$condition='';
		$cond = "AND haswinner != 1 AND hasbuynowwinner != 1";
		$conds = "AND p.haswinner != 1 AND p.hasbuynowwinner != 1";
		}
		 
		//Bug #5260		
		$orderby = "";
		if(isset($ilance->GPC['order_by']) AND $ilance->GPC['order_by'] == 'coin_id')
		{
			$orderby .= "c.coin_id, ";
		}
		


		if($ilance->GPC["excsold"]=="sold")
		{
		 	// $sql="SELECT c.coin_id,c.site_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.cost,c.pcgs,
				// c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no FROM
				// 							" .DB_PREFIX. "coins c left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS
				// 							WHERE c.user_id = '".$userid."' 
				// 							AND c.sold_qty =0
				// 							GROUP BY c.coin_id
				// 							ORDER BY ".$orderby." cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC
				// 							 ";

			$sql="SELECT c.coin_id,c.site_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.cost,c.pcgs,
					c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no,c.actual_qty
					FROM " . DB_PREFIX . "coins c 
					left join " . DB_PREFIX . "projects p on c.coin_id=p.project_id
					left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS 
					left join " . DB_PREFIX . "ebay_listing e on c.coin_id=e.coin_id 
					WHERE c.user_id = '".$userid."' 
					AND ((p.filtered_auctiontype = 'fixed' AND p.buynow_qty>0 )
					  OR (p.filtered_auctiontype = 'regular' AND c.sold_qty = 0 AND e.coin_id IS NULL)  
					  OR (p.filtered_auctiontype IS NULL AND c.sold_qty = 0 AND e.coin_id IS NULL ) 
					) 
					GROUP BY c.coin_id 
					ORDER BY ".$orderby." cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC
				 ";
				 								 
		 	$consinorslist = $ilance->db->query($sql);

		}
		if($ilance->GPC["excsold"]=="old")
		{
		  // 	$sql="SELECT c.coin_id,c.site_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.cost,c.pcgs, 
				// c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no FROM
				// 							" .DB_PREFIX. "coins c left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS
				// 							WHERE c.user_id = '".$userid."'											
				// 							AND c.End_Date between '".NINETYDAYSAGO."' AND '".DATETODAY."'
				// 								GROUP BY c.coin_id
				// 								ORDER BY ".$orderby." cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC
				// 							 ";
			$sql=" SELECT c.coin_id,c.site_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.cost,c.pcgs,
					c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no,c.actual_qty
					FROM " . DB_PREFIX . "coins c 
					left join " . DB_PREFIX . "projects p on c.coin_id=p.project_id
					left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS 
					left join " . DB_PREFIX . "ebay_listing e on c.coin_id=e.coin_id 
					WHERE c.user_id = '".$userid."' 
					AND ((p.filtered_auctiontype = 'fixed' AND p.buynow_qty>0 )
					  OR (p.filtered_auctiontype = 'regular' AND c.sold_qty = 0 AND e.coin_id IS NULL)  
					  OR (p.filtered_auctiontype IS NULL AND c.sold_qty = 0 AND e.coin_id IS NULL ) 
					) AND c.End_Date between '".NINETYDAYSAGO."' AND '".DATETODAY."'
					GROUP BY c.coin_id 
					ORDER BY ".$orderby." cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC
				 ";

		 	$consinorslist = $ilance->db->query($sql);

		}
		if(empty($ilance->GPC["excsold"]))
		{				
		  $sql="SELECT c.coin_id,c.site_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.cost,c.pcgs,  
				c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no FROM 
										    " .DB_PREFIX. "coins c left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS
											  WHERE c.user_id = '".$userid."'										   
												GROUP BY c.coin_id
												ORDER BY ".$orderby." cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC												
											";
         $consinorslist = $ilance->db->query($sql);

		}
		if(empty($ilance->GPC["excsold"]))
		{		
		 	$sql="SELECT c.coin_id,c.site_id, c.Title, c.Certification_No,c.cost,c.pcgs,
				c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,cc.coin_series_denomination_no,cc.coin_series_unique_no FROM 
										    " .DB_PREFIX. "coins_retruned c left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS
											 WHERE c.user_id = '".$userid."' 
											  $condition
												GROUP BY c.coin_id
												ORDER BY ".$orderby." cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC
											";					
		 	$return_list =  $ilance->db->query($sql);		
		}							
//cs.coin_series_sort,cc.coin_detail_year asc,cd.denomination_sort,
		//pdf start										 
		if(isset($ilance->GPC['export']) AND $ilance->GPC['export'] == 'pdf')
		{
			if($ilance->db->num_rows($consinorslist)>0)
	   		{
				$listing_items = '<table border="1"><tr>
				<td>Coin_id</td>
				<td>Site</td>
				<td>Sold</td>
				<td>Title</td><td>PCGS</td><td>Certificate No</td><td>Min Bid</td><td>Buy Now</td>
				<td>Bids</td><td>Cost</td>';

				if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
				{
					$listing_items .= '<td>APR</td>';
				}

				$listing_items .= '<td>Total</td></tr>';	

       
	            while($listing = $ilance->db->fetch_array($consinorslist))
	            {
									
				  	$projectid = $listing['coin_id'];
	              	$projecttitle = $listing['Title'];
				    $certnum = $listing['Certification_No'];
				    $cost=$listing['cost'];
									
                	$selprj = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
                		WHERE project_id = '".$listing['coin_id']."'");
								
					if($ilance->db->num_rows($selprj) > 0 )
					{
						$pjt_listing = $ilance->db->fetch_array($selprj);					
				        if($pjt_listing['haswinner'] == '1')
				        {
					      	$sold = 'X';
						}
				        elseif($pjt_listing['hasbuynowwinner'] == '1')
				        {
					      	$sold = 'X';
					    }
					    else	
					    {
						    $sold = ''; 
					    }		 
								
					    if($pjt_listing['filtered_auctiontype'] == 'fixed')
					    {
						  	$buynow_price = $ilance->currency->format($pjt_listing['buynow_price']); 
						  	$bp = $pjt_listing['buynow_price'];
						}						
					   	else
					   	{
					     	$buynow_price = '-';
						 	$bp = '-';
					   	}	
					    if($pjt_listing['filtered_auctiontype'] == 'regular')
					    {
						           $no_bids = $pjt_listing['bids'];
								   $current_price = $ilance->currency->format($pjt_listing['currentprice']); 
								   $cp = $pjt_listing['currentprice'];
						}
					  	else
						{
							$no_bids = '-';
							  $current_price = '-';
							  $cp = '-';
						}
	           		}
					else	
					{
					 	if(!empty($listing['Buy_it_now']))
						{
						
							$buynow_price = $listing['Buy_it_now'];
							$bp = $listing['Buy_it_now'];
						}
						else
						{
						      $buynow_price = '-';
							 $bp = '-';
						}
						if(!empty($listing['Minimum_bid']))
						{
						
							$current_price =  $ilance->currency->format($listing['Minimum_bid']);
							$cp = $listing['Minimum_bid'];

						}
						else
						{
							 $current_price = '-';
							  $cp = '-';
						}
					   $sold='Pending';						  
					   
					   $no_bids = '-';
							   
					}
							
				if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
				{
					$coin_apr=get_coin_history_price($listing['coin_id'],$listing['pcgs'],$listing['Grade'],$listing['Grading_Service'],$listing['Cac'],$listing['Star'],$listing['plus'],$listing['coin_series_unique_no'],$listing['coin_series_denomination_no']);
				}
				$total =  $ilance->currency->format($cp+$bp);
				if($listing['site_id']==0)
					$site_name='Gc';
				else
					$site_name='EBay';

				$listing_items.= '<tr><td>'.$projectid.'</td><td>'.$site_name.'</td>
									<td>'.$sold.'</td><td>'.$projecttitle.'</td><td>'.$listing['pcgs'].'</td>
									<td>'.$certnum.'</td><td>'.$current_price.'</td><td>'.$buynow_price.'</td>
									<td>'.$no_bids.'</td><td>'.$cost.'</td>';
				if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
				{
					$listing_items.='<td>'.$coin_apr.'</td>';
				}

					$listing_items.=	'<td>'.$total.'</td></tr>';
     
			}
		}
		else
		{
			$no_results = true;
		}
							 
		if(empty($ilance->GPC["excsold"]))
		{	
			if($ilance->db->num_rows($return_list)>0)
			{
				while($listing = $ilance->db->fetch_array($return_list))
				{				
				   $projectid = $listing['coin_id'];
	               $projecttitle = $listing['Title'];
				   $certnum = $listing['Certification_No'];
				   $cost=$listing['cost'];					
				   $sold='R';				   
				   $buynow_price = '-';
				   $bp = '-';				   
				   $no_bids = '-';
				   $current_price = '-';
				   $cp = '-';
		 
		         	$total =  $ilance->currency->format($cp+$bp);

					if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
					{
						$coin_apr=get_coin_history_price($listing['coin_id'],$listing['pcgs'],$listing['Grade'],$listing['Grading_Service'],$listing['Cac'],$listing['Star'],$listing['plus'],$listing['coin_series_unique_no'],$listing['coin_series_denomination_no']);
					}									 
					$listing_items.= '<tr><td>'.$projectid.'</td><td></td><td>'.$sold.'</td>
									<td>'.$projecttitle.'</td><td>'.$listing['pcgs'].'</td><td>'.$certnum.'</td>
									<td>'.$current_price.'</td><td>'.$buynow_price.'</td><td>'.$no_bids.'</td><td>'.$cost.'</td>';

					if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
					{
						$listing_items.= '<td>'.$coin_apr.'</td>';
					}									
						$listing_items.= '<td>'.$total.'</td></tr>';
     
				}
			} 
			else
			{
				$listing_items.='<tr><td>No Result Found</td></tr>'; 
			}	
		}
				$listing_items.='</table>';
						  
				//$listing_items.='</table>';
		if(isset($no_results) AND $no_results)		
		{
			$show['no_records'] = true;
		}
		else
		{
			define('FPDF_FONTPATH','../font/');
					
			require('pdftable_1.9/lib/pdftable.inc.php');
			
			$p = new PDFTable();
			
			$p->AddPage();
			
			$p->setfont('times','',10);
			
			$p->htmltable($listing_items);
			
			$p->output('consignor_owned_list_'.date('Y-m-d h-i-s').'.pdf','D');
		}
				
					  

		}//pdf end

			//excel start
		if(isset($ilance->GPC['export']) AND $ilance->GPC['export'] == 'excel')
		{
 			if($ilance->db->num_rows($consinorslist)>0)
			{
 
			$timeStamp = date("Y-m-d-H-i-s");
			$fileName = "consignor_owned_list_-$timeStamp";
			header('Content-Type: text/csv; charset=utf-8');
			if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
			{
				$fields = array('Coin_id','Site','Sold','Title','PCGS ','Certificate No','Min Bid','Buy Now','Cost','Bids','APR','Total');
			}
			else
			{
				$fields = array('Coin_id','Site','Sold','Title','PCGS ','Certificate No','Min Bid','Buy Now','Cost','Bids','Total');
			}
			 
			header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
			$fp = fopen('php://output', 'w');
			fputcsv($fp, $fields);
			   
		            while($listing = $ilance->db->fetch_array($consinorslist))
		            {
											
					  	$projectid = $listing['coin_id'];
		              	$projecttitle = $listing['Title'];
					    $certnum = $listing['Certification_No'];
      				    $cost=$listing['cost'];
											
                $selprj = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects 
                	WHERE project_id = '".$listing['coin_id']."'");
								
							if($ilance->db->num_rows($selprj) > 0 )
							{
								$pjt_listing = $ilance->db->fetch_array($selprj);						
													
						        if($pjt_listing['haswinner'] == '1')
						        {
							      	$sold = 'X';
								}
						        elseif($pjt_listing['hasbuynowwinner'] == '1')
						        {
							      	$sold = 'X';
							    }
							    else	
							    {
								    $sold = ''; 
							    }
	 
										
							    if($pjt_listing['filtered_auctiontype'] == 'fixed')
							    {
								  	$buynow_price = $ilance->currency->format($pjt_listing['buynow_price']); 
								  	$bp = $pjt_listing['buynow_price'];
								  	
								}						
							   	else
							   	{
							     	$buynow_price = '-';
								 	$bp = '-';
							   	}
						 	
							    if($pjt_listing['filtered_auctiontype'] == 'regular')
							    {
								           $no_bids = $pjt_listing['bids'];
										   $current_price = $ilance->currency->format($pjt_listing['currentprice']);
										 
		 								   $cp = $pjt_listing['currentprice'];
								}
							  	else
								{
									$no_bids = '-';
									  $current_price = '-';
									  $cp = '-';
								}
							    
			           		}
							else	
							{
								
							 	if(!empty($listing['Buy_it_now']))
								{
								
									$buynow_price = $listing['Buy_it_now'];
									$bp = $listing['Buy_it_now'];
								}
								else
								{
								      $buynow_price = '-';
									 $bp = '-';
								}
								if(!empty($listing['Minimum_bid']))
								{
			
									$current_price =  $ilance->currency->format($listing['Minimum_bid']);
									$cp = $listing['Minimum_bid'];
								}
								else
								{
									 $current_price = '-';
									  $cp = '-';
								}
							   $sold='Pending';	
							   $no_bids = '-';
									   
							}
									
						if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
						{
							 $coin_apr=get_coin_history_price_excel($listing['coin_id'],$listing['pcgs'],$listing['Grade'],$listing['Grading_Service'],$listing['Cac'],$listing['Star'],$listing['plus'],$listing['coin_series_unique_no'],$listing['coin_series_denomination_no']);
						}
						$total =  $ilance->currency->format($cp+$bp);
						
						if($listing['site_id']==0)
							$site_name='Gc';
						else
							$site_name='EBay';
						$data['Coin_id']=$projectid;
						$data['Site']=$site_name;
						$data['Sold']=$sold;
						$data['Title']=$projecttitle;
						$data['PCGS']=$listing['pcgs'];
						$data['Certificate_No']=$certnum;
						$data['Min_Bid']=$current_price;
						$data['Buy_Now']=$buynow_price;
						$data['cost']=$cost;
						$data['Bids']=$no_bids;	
						if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
						{
 					    
 					    $data['APR']=$coin_apr;
						}
 						$data['Total']=$total;
 						$res[] = $data;
						
						fputcsv($fp, $data);
						 
		     
					}
				}
				else
				{
					$no_results = true;
				}	
		 
			if(isset($no_results) AND $no_results)
			{
				$show['no_records'] = true;
			}	
			else
			{
				if(empty($ilance->GPC["excsold"]))
				{	
					if($ilance->db->num_rows($return_list)>0)
					{
						while($listing = $ilance->db->fetch_array($return_list))
						{				
						   $projectid = $listing['coin_id'];
			               $projecttitle = $listing['Title'];
						   $certnum = $listing['Certification_No'];	
						   $cost=$listing['cost'];					
						   $sold='R';				   
						   $buynow_price = '-';
						   $bp = '-';				   
						   $no_bids = '-';
						   $current_price = '-';
						   $cp = '-';
				 
				         	$total =  $ilance->currency->format($cp+$bp);
							if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
							{
								 $coin_apr=get_coin_history_price_excel($listing['coin_id'],$listing['pcgs'],$listing['Grade'],$listing['Grading_Service'],$listing['Cac'],$listing['Star'],$listing['plus'],$listing['coin_series_unique_no'],$listing['coin_series_denomination_no']);
							}									 
						 
							$data1['Coin_id']=$projectid;
							$data1['Site']='';
							$data1['Sold']=$sold;
							$data1['Title']=$projecttitle;
							$data1['PCGS']=$listing['pcgs'];
							$data1['Certificate_No']=$certnum;
							$data1['Min_Bid']=$current_price;
							$data1['Buy_Now']=$buynow_price;
							$data1['cost']=$cost;
							$data1['Bids']=$no_bids;
							// $final_Total_Minimum_Bid+= $listing['Total_Minimum_Bid'];
							// $final_Total_Buy_Now+= $listing['Total_Buy_Now'];
							
						   if(isset($ilance->GPC['apr_option']) AND $ilance->GPC['apr_option'] == 'with_apr')
						   { 							
 							$data1['APR']=$coin_apr;
						   }
 							$data1['Total']=$total;
 						    $res[] = $data1;
 						    fputcsv($fp, $data1);
		     
						}
					} 
 	
				}
							$data2['Coin_id']='';
							$data2['Site']='';
							$data2['Sold']='';
							$data2['Title']='';
							$data2['PCGS']='';
							$data2['Certificate_No']='';
							$data2['cost']='';
							// $data2['final_Total_Minimum_Bid']= 'Total Min Bid :'.$ilance->currency->format_real_no($final_Total_Minimum_Bid);
							// $data2['final_Total_Buy_Now']='Total Buy Now :'.$ilance->currency->format_real_no($final_Total_Buy_Now);
							$data2['Bids']='';
							$data2['APR']='';
							$data2['Total']='';
							 $res[] = $data2;
 						    fputcsv($fp, $data2);
		 
							exit();
			} 				 
				
		} //excel end 		  
							 

	}
			

			
	$pprint_array = array('filtervalue','tot_mis','showoutput','user_down','user_id','date_down',
		'lastamount','total_advance','statecount','date1','date','totbids',
		'totbidamount','totbinamount','totfvf','totlisting_fee','totseller_fee',
		'totnet_consignor','daylist','monthlist','yearlist','daylist1','monthlist1',
		'yearlist1','buildversion','ilanceversion','login_include_admin','reportorderby',
		'reportfromrange','reportrange','radiopast','radioexact','reportcolumns','reportaction',
		'reportshow','customprevnext','reportoutput','remote_addr','rid','login_include','headinclude',
		'area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer',
		'certnum');
        
       ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'reports.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('reports','statement'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}



function get_coin_history_price($coin_id,$pcgs,$grade,$grading_service,$cac,$star,$plus,$seried_id,$denomination_id)
 {
 global $ilance;
 //except denomination Raw Collections & Lots , World and Ancient Coins
 $excepmt_array=array();
 $excepmt_array[]=30;
 $excepmt_array[]=33;
 $excepmt_array[]=29; //except Error Coins by Bug #8397
 if(in_array($denomination_id,$excepmt_array))
 {
 	return '';
 }
 
	$query="SELECT p.project_id,p.date_end,p.grade,p.grading_service,p.currentprice,p.Cac
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "coins c ON c.coin_id = p.project_id
				WHERE p.cid='$pcgs' 
				and p.date_end>= DATE_SUB(NOW(),INTERVAL 2 YEAR)
				and p.status='expired' 
				and p.grade='".$grade."'
				and p.project_title not like '%DETAILS%'
				and (p.haswinner=1 or p.hasbuynowwinner=1)  
				AND c.Plus = '".$plus."'
				order by 
				case when p.grading_service='".$grading_service."' then 1 else 2 end, 
				p.date_end desc, p.currentprice desc limit 5";
 
	 $result=$ilance->db->query($query);
	 if($ilance->db->num_rows($result))
	 {
		 while($line=$ilance->db->fetch_array($result))
		 {
			//$list[]='<a href="'.HTTP_SERVER.'coins/'.$line['project_id'].'">'.$line['currentprice'].substr($line['grading_service'],0,1).'</a>';

			if($line['Cac'])
				$list[]=$line['currentprice'].substr($line['grading_service'],0,1).'C';
			else	
				$list[]=$line['currentprice'].substr($line['grading_service'],0,1);
		 }
		 return implode(', ',$list);
	 }
 }
 
 function get_coin_history_price_excel($coin_id,$pcgs,$grade,$grading_service,$cac,$star,$plus,$seried_id,$denomination_id)
 {
 global $ilance;
 //except denomination Raw Collections & Lots , World and Ancient Coins
 $excepmt_array=array();
 $excepmt_array[]=30;
 $excepmt_array[]=33;
 $excepmt_array[]=29; //except Error Coins by Bug #8397
 if(in_array($denomination_id,$excepmt_array))
 {
 	return '';
 }
 
	$query="SELECT p.project_id,p.date_end,p.grade,p.grading_service,p.currentprice,p.Cac
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "coins c ON c.coin_id = p.project_id
				WHERE p.cid='$pcgs' 
				and p.date_end>= DATE_SUB(NOW(),INTERVAL 2 YEAR)
				and p.status='expired' 
				and p.grade='".$grade."'
				and p.project_title not like '%DETAILS%'
				and (p.haswinner=1 or p.hasbuynowwinner=1)  
				AND c.Plus = '".$plus."'
				order by 
				case when p.grading_service='".$grading_service."' then 1 else 2 end, 
				p.date_end desc, p.currentprice desc limit 5";
 
	 $result=$ilance->db->query($query);
	 if($ilance->db->num_rows($result))
	 {
		while($line=$ilance->db->fetch_array($result))
		{
			//$list[]= $line['currentprice'].substr($line['grading_service'],0,1);
			if($line['Cac'])
				$list[]=$line['currentprice'].substr($line['grading_service'],0,1).'C';
			else	
				$list[]=$line['currentprice'].substr($line['grading_service'],0,1);
		}
		return implode(', ',$list);
	 }
 }



/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
