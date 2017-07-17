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
	'administration',
	'accounting'
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

if (!empty($_SESSION['ilancedata']['user']['userid']) 
		AND $_SESSION['ilancedata']['user']['userid'] > 0 
		AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

	// #### advanced reporting from range ##########################
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
	// Date Month Year Start


 	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'paypal_invoice')
	{

		//$startDate = print_array_to_datetime($ilance->GPC['range_start']);
		//$startDate = substr($startDate, 0, -9);
					 
		
		//$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		//$endDate = substr($endDate, 0, -9);
		

		$startDate = $ilance->GPC['start_date'];
		$endDate = $ilance->GPC['end_date'];
		//echo $startDate.'<br/>';		
		//echo $endDate.'<br/>';
		//exit;		
		if(isset($ilance->GPC['print']))
			header("Location:paypal_invoices_pdf.php?start_date=".$startDate."&end_date=".$endDate."");

	}	
		
	
	$pprint_array = array('invoice_paid_to','invoice_paid_from','search_by_dropdown','searchkey_value','invoice_type_drop_down','series_prevnext','daylist','monthlist','yearlist','searchprevnext','hiddenid','hiddendo','pay_first_name','pay_last_name','pay_username','pay_email','pay_address','pay_phone','pay_invoice_id','pay_amount','payment_pulldown','reportfromrange','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','series_prevnext1');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'paypal_invoices.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('invoicelist','reportlist','invoicelist1','not_shipped_arr'));
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