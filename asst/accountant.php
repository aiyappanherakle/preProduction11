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
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'export_invoice_csv') 
	{
	$start_date=$ilance->GPC['start_date'];
	$end_date=$ilance->GPC['end_date'];
	 $fields = array(
					array('invoiceid', 'invoiceid'),
					array('createdate', 'createdate'),
					array('payment_due', 'payment_due'),
					array('user_id', 'user_id'),
					array('Summary','Summary'),
					array('Description','Description'),
					array('Account_Name','Account_Name'),
					array('Account_Name','Account_Number'),
					array('totalamount', 'totalamount'),
					array('Tags','Tags')
				);
				
				foreach ($fields AS $column)
				{			
					$fieldsToGenerate[] = $column[0];
					$headings[] = $column[1];				
				}
				
	$sql="SELECT *,DATE_ADD(scheduled_date, INTERVAL 5 DAY) as payment_due   FROM " . DB_PREFIX . "invoices WHERE date(scheduled_date) between '".$start_date."' and '".$end_date."'  and combine_project!=''";
	$result=$ilance->db->query($sql);
	if($ilance->db->num_rows($result))
	{
	while($test=$ilance->db->fetch_array($result))
	{
		//shiping part
		$shipping_cost=0;
		$query3="SELECT *  FROM " . DB_PREFIX . "invoice_projects WHERE final_invoice_id = '".$test['invoiceid']."' limit 1";
		$result3=$ilance->db->query($query3);
		if($ilance->db->num_rows($result3))
		{
			while($line3=$ilance->db->fetch_array($result3))
			{
			 $shipping_cost=$line3['shipping_cost'];
			}
		}	
		$buyerfee=0;
		$query4="SELECT sum(totalamount) as buyerfee  FROM " . DB_PREFIX . "invoices WHERE isbuyerfee = 1 and projectid in (SELECT group_concat( projectid SEPARATOR ', ' ) FROM `ilance_invoices` WHERE invoiceid IN (".$test['combine_project'].") group by invoiceid) and user_id='".$test['user_id']."'";
	 	$result4=$ilance->db->query($query4);
		if($ilance->db->num_rows($result4))
		{
		while($line4=$ilance->db->fetch_array($result4))
		{
		$buyerfee=$line4['buyerfee'];
		}
		}
		//hammer part
		$composed['Invoice_Number']	=$test['invoiceid'];
		$composed['Invoice_Date']	=date("m/d/Y", strtotime($test['scheduled_date']));
		$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
		$composed['User_ID']		=$test['user_id'];
		$composed['Summary']		='Auction Invoice';
		$composed['Description']	='Auction System Invoice';
		$composed['Account_Name']	='Hammer Buyer';
		$composed['Account_Number']	='4000';
		$composed['Line_Item_Total']=$test['totalamount']-$test['taxamount']-$shipping_cost-$buyerfee;
		$composed['Tags']='';
		$res[] = $composed;
		//buyerfeespart
		if($buyerfee>0)
		{
		$composed['Invoice_Number']	=$test['invoiceid'];
		$composed['Invoice_Date']	=date("m/d/Y", strtotime($test['scheduled_date']));
		$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
		$composed['User_ID']		=$test['user_id'];
		$composed['Summary']		='Auction Invoice';
		$composed['Description']	='Auction System Invoice';
		$composed['Account_Name']	='Buyers Fees';
		$composed['Account_Number']	='4020';
		$composed['Line_Item_Total']=$buyerfee;
		$composed['Tags']='';
		$res[] = $composed;
		}
		//taxpart
		if($test['istaxable']==1 and $test['taxamount']>0 )
		{
		$composed['Invoice_Number']	=$test['invoiceid'];
		$composed['Invoice_Date']	=date("m/d/Y", strtotime($test['scheduled_date']));
		$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
		$composed['User_ID']		=$test['user_id'];
		$composed['Summary']		='Auction Invoice';
		$composed['Description']	='Auction System Invoice';
		$composed['Account_Name']	='Sales Tax';
		$composed['Account_Number']	='2020';
		$composed['Line_Item_Total']=$test['taxamount'];
		$composed['Tags']='';
		$res[] = $composed;
		}
		//shiping part
		if($shipping_cost>0)
		{
		$composed['Invoice_Number']	=$test['invoiceid'];
		$composed['Invoice_Date']	=date("m/d/Y", strtotime($test['scheduled_date']));
		$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
		$composed['User_ID']		=$test['user_id'];
		$composed['Summary']		='Auction Invoice';
		$composed['Description']	='Auction System Invoice';
		$composed['Account_Name']	='Shipping Fees';
		$composed['Account_Number']	='4070';
		$composed['Line_Item_Total']=$shipping_cost;
		$composed['Tags']='';
		$res[] = $composed;
		}
	}
	}	
 
	$reportoutput = $ilance->admincp->construct_csv_data($res, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "Sales_summary_datails-$timeStamp";
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
	 
	 
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
    ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'accountant.html', 3);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	

}else
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