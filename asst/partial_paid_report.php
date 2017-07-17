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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'partial_paid_csv' AND !empty($ilance->GPC['from_date']) AND !empty($ilance->GPC['to_date']))
	{
		$from_date=$ilance->GPC['from_date'];
		$to_date=$ilance->GPC['to_date'];
		$fields = array(
					array('INVOICE#'),
					array('USER ID'),
					array('USER NAME'),
					array('TOTAL INVOICE'),
					array('PARTIALLY PAID AMOUNT'),
					array('BALANCE OWING'),
					array('DATE OF LAST PAYMENT')					
				);
				
				foreach ($fields AS $column)
				{			
					 
					$headings[] = $column[0];				
				}
				
		
		$sql = $ilance->db->query("SELECT u.first_name,u.last_name,i.invoiceid,i.user_id,i.totalamount,i.paid,
							(i.totalamount-i.paid) as due_amount,DATE_FORMAT(DATE(MAX(p.paymentdate)),'%m/%d/%Y') as last_paid_date
							FROM " . DB_PREFIX . "invoices i 
							LEFT JOIN 	" . DB_PREFIX . "partial_payment p  ON p.invoiceid=i.invoiceid
							LEFT JOIN  " . DB_PREFIX . "users u ON i.user_id=u.user_id
							WHERE i.amount != i.paid
							AND i.paid != 0
							AND i.status !='paid'
							AND i.combine_project !=''
							AND p.invoiceid=i.invoiceid
							AND (p.paymentdate BETWEEN '".$from_date."' AND '".$to_date."')
							GROUP BY i.invoiceid
							ORDER BY p.paymentdate DESC
							");
							
							
		while($res = $ilance->db->fetch_array($sql))
		{
			
			$partial_paid['invoiceid']=$res['invoiceid'];			
			$partial_paid['user_id']=$res['user_id'];
			$partial_paid['user_name']=$res['first_name'].$res['last_name'];
			$partial_paid['totalamount']=$res['totalamount'];
			$partial_paid['paid']=$res['paid'];			
			$partial_paid['due_amount']=$res['due_amount'];			
			$partial_paid['paymentdate']=$res['last_paid_date'];			
			
			$data[]=$partial_paid;
		}
		
		$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "partially_paid_report-$timeStamp";
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
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','remote_addr','rid','referfrom','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
         ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'partial_paid_report.html', 3);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');	
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>