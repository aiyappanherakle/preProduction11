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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'export_invoice_csv') 
	{
	$start_date=$ilance->GPC['start_date'];
	$end_date=$ilance->GPC['end_date'];
	 $fields = array(
					array('invoiceid', 'RefNumber'),
					array('Customer', 'Customer'),
					array('createdate', 'TxnDate'),
					array('payment_due', 'DueDate'),
					array('ShipDate', 'ShipDate'),
					array('ShipMethodName', 'ShipMethodName'),
					array('ShipMethodId', 'ShipMethodId'),
					array('BillAddrLine1', 'BillAddrLine1'),
					array('BillAddrLine2', 'BillAddrLine2'),
					array('BillAddrLine3', 'BillAddrLine3'),
					array('BillAddrLine4', 'BillAddrLine4'),
					array('BillAddrLineCity', 'BillAddrLineCity'),
					array('BillAddrLineState', 'BillAddrLineState'),
					array('BillAddrLinePostalCode', 'BillAddrLinePostalCode'),
					array('BillAddrLineCountry', 'BillAddrLineCountry'),
					array('ShipAddrLine1', 'ShipAddrLine1'),
					array('ShipAddrLine2', 'ShipAddrLine2'),
					array('ShipAddrLine3', 'ShipAddrLine3'),
					array('ShipAddrLine4', 'ShipAddrLine4'),
					array('ShipAddrLineCity', 'ShipAddrLineCity'),
					array('ShipAddrLineState', 'ShipAddrLineState'),
					array('ShipAddrLinePostalCode', 'ShipAddrLinePostalCode'),
					array('ShipAddrLineCountry', 'ShipAddrLineCountry'),
					array('Note', 'Note'),
					array('Msg', 'Msg'),
					array('BillEmail', 'BillEmail'),
					array('ToBePrinted', 'ToBePrinted'),
					array('ToBeEmailed', 'ToBeEmailed'),
					array('ShipAmt', 'ShipAmt'),
					array('ShipItem', 'ShipItem'),
					array('TaxAmt', 'TaxAmt'),
					array('TaxRate', 'TaxRate'),
					array('DiscountAmt', 'DiscountAmt'),
					array('DiscountRate', 'DiscountRate'),
					array('DiscountTaxable', 'DiscountTaxable'),
					array('LineItem', 'LineItem'),
					array('LineQty', 'LineQty'),
					array('LineDesc', 'LineDesc'),
					array('LineServiceDate', 'LineServiceDate'),
					array('LineUnitPrice', 'LineUnitPrice'),
					array('LineAmount', 'LineAmount'),
					array('LineTaxable', 'LineTaxable'),
					array('LineClass', 'LineClass'),

				);
				
				foreach ($fields AS $column)
				{			
					$fieldsToGenerate[] = $column[0];
					$headings[] = $column[1];				
				}
				


	$sql="SELECT CONCAT(u.first_name,' ',u.last_name) as user_name, u.first_name, u.last_name, u.address, u.address2, u.city, u.state,u.country, u.zip_code,u.email,i.invoiceid,i.combine_project,i.user_id,i.scheduled_date,i.totalamount,i.taxamount,i.istaxable,i.scheduled_date as payment_due   FROM " . DB_PREFIX . "invoices i 
	left join " . DB_PREFIX . "users u on u.user_id = i.user_id
	WHERE date(scheduled_date) between '".$start_date."' and '".$end_date."'  and combine_project!=''";
	
	$result=$ilance->db->query($sql);
	if($ilance->db->num_rows($result))
	{
	while($test=$ilance->db->fetch_array($result))
	{
		//shiping part
		$shipping_cost=0;
		$query3="SELECT shipping_cost  FROM " . DB_PREFIX . "invoice_projects WHERE final_invoice_id = '".$test['invoiceid']."' limit 1";
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
$composed['user_name']=$test['user_name']." [".$test['user_id']."]";
$composed['Invoice_Date']=date("m/d/Y",strtotime($test['scheduled_date']));
$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
$composed['ShipDate']		='';
$composed['ShipMethodName']		='';
$composed['ShipMethodId']	='';
$composed['BillAddrLine1']	=$test['address'];
$composed['BillAddrLine2']	=$test['address2'];
$composed['BillAddrLine3']	='';
$composed['BillAddrLine4']	='';
$composed['BillAddrLineCity']	=$test['city'];
$composed['BillAddrLineState']	=$test['state'];
$composed['BillAddrLinePostalCode']	=$test['zip_code'];
$composed['BillAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['ShipAddrLine1']	=$test['address'];
$composed['ShipAddrLine2']	=$test['address2'];
$composed['ShipAddrLine3']	='';
$composed['ShipAddrLine4']	='';
$composed['ShipAddrLineCity']	=$test['city'];
$composed['ShipAddrLineState']	=$test['state'];
$composed['ShipAddrLinePostalCode']	=$test['zip_code'];
$composed['ShipAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['Note']	='';
$composed['Msg']	='Invoice';
$composed['BillEmail']	=$test['email'];
$composed['ToBePrinted']	='';
$composed['ToBeEmailed']	='';
$composed['ShipAmt']	='';
$composed['ShipItem']	='';
$composed['TaxAmt']	='';
$composed['TaxRate']	='';
$composed['DiscountAmt']	='';
$composed['DiscountRate']	='';
$composed['DiscountTaxable']	='';
$composed['LineItem']	='4000';
$composed['LineQty']	='1';
$composed['LineDesc']	='Hammer Buyer';
$composed['LineServiceDate']	='';
$composed['LineUnitPrice']=$test['totalamount']-$test['taxamount']-$shipping_cost-$buyerfee;
$composed['LineAmount']	='';
$composed['LineTaxable']='N';
$composed['LineClass']='';
$res[] = $composed;
		//buyerfeespart
		if($buyerfee>0)
		{
		
		
		
		
$composed['Invoice_Number']	=$test['invoiceid'];
$composed['user_name']=$test['user_name']." [".$test['user_id']."]";
$composed['Invoice_Date']=date("m/d/Y",strtotime($test['scheduled_date']));
$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
$composed['ShipDate']		='';
$composed['ShipMethodName']		='';
$composed['ShipMethodId']	='';
$composed['BillAddrLine1']	=$test['address'];
$composed['BillAddrLine2']	=$test['address2'];
$composed['BillAddrLine3']	='';
$composed['BillAddrLine4']	='';
$composed['BillAddrLineCity']	=$test['city'];
$composed['BillAddrLineState']	=$test['state'];
$composed['BillAddrLinePostalCode']	=$test['zip_code'];
$composed['BillAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['ShipAddrLine1']	=$test['address'];
$composed['ShipAddrLine2']	=$test['address2'];
$composed['ShipAddrLine3']	='';
$composed['ShipAddrLine4']	='';
$composed['ShipAddrLineCity']	=$test['city'];
$composed['ShipAddrLineState']	=$test['state'];
$composed['ShipAddrLinePostalCode']	=$test['zip_code'];
$composed['ShipAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['Note']	='';
$composed['Msg']	='Invoice';
$composed['BillEmail']	=$test['email'];
$composed['ToBePrinted']	='';
$composed['ToBeEmailed']	='';
$composed['ShipAmt']	='';
$composed['ShipItem']	='';
$composed['TaxAmt']	='';
$composed['TaxRate']	='';
$composed['DiscountAmt']	='';
$composed['DiscountRate']	='';
$composed['DiscountTaxable']	='';
$composed['LineItem']	='4020';
$composed['LineQty']	='1';
$composed['LineDesc']	='Buyers Fees';
$composed['LineServiceDate']	='';
$composed['LineUnitPrice']=$buyerfee;
$composed['LineAmount']	='';
$composed['LineTaxable']='N';
$composed['LineClass']='';
		$res[] = $composed;
		}
		//taxpart
		if($test['istaxable']==1 and $test['taxamount']>0 )
		{
$composed['Invoice_Number']	=$test['invoiceid'];
$composed['user_name']=$test['user_name']." [".$test['user_id']."]";
$composed['Invoice_Date']=date("m/d/Y",strtotime($test['scheduled_date']));
$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
$composed['ShipDate']		='';
$composed['ShipMethodName']		='';
$composed['ShipMethodId']	='';
$composed['BillAddrLine1']	=$test['address'];
$composed['BillAddrLine2']	=$test['address2'];
$composed['BillAddrLine3']	='';
$composed['BillAddrLine4']	='';
$composed['BillAddrLineCity']	=$test['city'];
$composed['BillAddrLineState']	=$test['state'];
$composed['BillAddrLinePostalCode']	=$test['zip_code'];
$composed['BillAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['ShipAddrLine1']	=$test['address'];
$composed['ShipAddrLine2']	=$test['address2'];
$composed['ShipAddrLine3']	='';
$composed['ShipAddrLine4']	='';
$composed['ShipAddrLineCity']	=$test['city'];
$composed['ShipAddrLineState']	=$test['state'];
$composed['ShipAddrLinePostalCode']	=$test['zip_code'];
$composed['ShipAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['Note']	='';
$composed['Msg']	='Invoice';
$composed['BillEmail']	=$test['email'];
$composed['ToBePrinted']	='';
$composed['ToBeEmailed']	='';
$composed['ShipAmt']	='';
$composed['ShipItem']	='';
$composed['TaxAmt']	='';
$composed['TaxRate']	='';
$composed['DiscountAmt']	='';
$composed['DiscountRate']	='';
$composed['DiscountTaxable']	='';
$composed['LineItem']	='2020';
$composed['LineQty']	='1';
$composed['LineDesc']	='Sales Tax';
$composed['LineServiceDate']	='';
$composed['LineUnitPrice']=$test['taxamount'];
$composed['LineAmount']	='';
$composed['LineTaxable']='N';
$composed['LineClass']='';
		$res[] = $composed;
		}
		//shiping part
		if($shipping_cost>0)
		{
$composed['Invoice_Number']	=$test['invoiceid'];
$composed['user_name']=$test['user_name']." [".$test['user_id']."]";
$composed['Invoice_Date']=date("m/d/Y",strtotime($test['scheduled_date']));
$composed['Payment_Due']	=date("m/d/Y", strtotime($test['payment_due']));
$composed['ShipDate']		='';
$composed['ShipMethodName']		='';
$composed['ShipMethodId']	='';
$composed['BillAddrLine1']	=$test['address'];
$composed['BillAddrLine2']	=$test['address2'];
$composed['BillAddrLine3']	='';
$composed['BillAddrLine4']	='';
$composed['BillAddrLineCity']	=$test['city'];
$composed['BillAddrLineState']	=$test['state'];
$composed['BillAddrLinePostalCode']	=$test['zip_code'];
$composed['BillAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['ShipAddrLine1']	=$test['address'];
$composed['ShipAddrLine2']	=$test['address2'];
$composed['ShipAddrLine3']	='';
$composed['ShipAddrLine4']	='';
$composed['ShipAddrLineCity']	=$test['city'];
$composed['ShipAddrLineState']	=$test['state'];
$composed['ShipAddrLinePostalCode']	=$test['zip_code'];
$composed['ShipAddrLineCountry']	=print_country_name($test['country'],'eng', false);
$composed['Note']	='';
$composed['Msg']	='Invoice';
$composed['BillEmail']	=$test['email'];
$composed['ToBePrinted']	='';
$composed['ToBeEmailed']	='';
$composed['ShipAmt']	='';
$composed['ShipItem']	='';
$composed['TaxAmt']	='';
$composed['TaxRate']	='';
$composed['DiscountAmt']	='';
$composed['DiscountRate']	='';
$composed['DiscountTaxable']	='';
$composed['LineItem']	='4070';
$composed['LineQty']	='1';
$composed['LineDesc']	='Shipping Fees';
$composed['LineServiceDate']	='';
$composed['LineUnitPrice']=$shipping_cost;
$composed['LineAmount']	='';
$composed['LineTaxable']='N';
$composed['LineClass']='';
		$res[] = $composed;
		}
	}
	}	
 
	$reportoutput = $ilance->admincp->construct_csv_data_quickbooks($res, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "Sales_summary_details-$timeStamp";
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
	$ilance->template->fetch('main', 'invoices_exports.html', 2);
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