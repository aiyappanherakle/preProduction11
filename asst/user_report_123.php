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
	'flashfix',
	'jquery'
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
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'unpaid_csv')
	{
		$from_date=$ilance->GPC['from_date'];
		$to_date=$ilance->GPC['to_date'];
		$fields = array(
					array('FIRST NAME', 'first_name'),
					array('MIDDLE INITIALS', 'first_name'),
					array('LAST NAME', 'last_name'),
					array('ORGANISATION', 'companyname'),
					array('CONTACT ID','user_id'),
					array('IS ACTIVE','a'),
					array('SEND AUTOMATIC STATEMENTS','a'),
					array('IS CUSTOMER', 'a'),
					array('IS SUPPLIER ','a')
				);
				
				foreach ($fields AS $column)
				{			
					 
					$headings[] = $column[0];				
				}
				
		
		$sql = $ilance->db->query("SELECT user_id,first_name,last_name,email,companyname,status  
							FROM " . DB_PREFIX . "users
							WHERE date(date_added) BETWEEN '".$from_date."' AND '".$to_date."'
							ORDER BY date_added ASC												
							");
		while($res = $ilance->db->fetch_array($sql))
		{
			
			$name_arr=explode(" ",$res['first_name']);
			$user_detail['first_name']=$name_arr[0];	
			
			$user_detail['middle_initials']=(!empty($name_arr[1]))?$name_arr[1]:'';
			if(strlen($user_detail['middle_initials'])>3)
			{
			$user_detail['middle_initials']=substr($user_detail['middle_initials'],0,3);			
			}
			$user_detail['last_name']=trim($res['last_name']);
			$user_detail['companyname']=$res['companyname'];
			$user_detail['user_id']=$res['user_id'];			
			$user_detail['status']=($res['status']=='active')?'1':'0';			
			$user_detail['send_automatic']='FALSE';
			$user_detail['is_customer']='TRUE';
			$user_detail['is_supplier']='TRUE';
			
			$data[]=$user_detail;
		}
		
	$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "user_reg_datails-$timeStamp";
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
	
	$ilance->template->fetch('main', 'user_report_123.html', 3);
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