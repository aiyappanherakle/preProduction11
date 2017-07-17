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
	'administration','accounting'
);
error_reporting(E_ALL);
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

$navroot = '1';

//error_reporting(E_ALL);


// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');
$ilance->subscription = construct_object('api.subscription');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[invoicepayment]" => $ilcrumbs["$ilpage[invoicepayment]"]);
// #### build our encrypted array for decoding purposes
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
//error_reporting(E_ALL);
if (((!empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0) OR (!empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0) ) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

	//pending invoice starts
	
	//##### Welcome page after from paypal #########################################33
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'did_invoice')
	{
		if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'complete')
		{
					print_action_success('<b>Invoice Payment:</b> &nbsp;Thank you for submitting your payment. Once your payment has been verified, we will mark your invoice as paid and advise you of shipping information.  Thank you for your business.', $ilpage['users'].'?subcmd=_update-customer&id='.intval($ilance->GPC['uid']));
					exit();
		}
		else if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancel')	
		{
					print_action_failed('<b>Invoice Payment:</b> &nbsp;Your invoice remains unpaid.  To pay for your pending invoice at a later time, please return to My GC and click on Buyer Invoices.  Thank you for using GreatCollections','_invoice.php?subcmd=view_pending_invoice&uid='.intval($ilance->GPC['uid']));
					exit();
		}
	}
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-preview' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0) // 
	{

		//error_reporting(E_ALL);
		$custmr_uid = $ilance->GPC['uid'];

		$not_first_shipment=false;
	$not_first_shipment=not_first_shipment();
	//exit;
	if($ilance->GPC['shipping_cost']==0 and $not_first_shipment==true)
	{
	print_action_failed("Shipping cost was not rendered correctly, You have to select shipping before you submit the previous page", "javascript:history.back()");
				exit();
	}

	$ilance->accounting = construct_object('api.accounting');
	$totalamountnew = $ilance->GPC['totalhidden'];
	$totaltaxnew = $ilance->GPC['taxhidden'];
	$amountnew = $totalamountnew - $totaltaxnew;
	$transactionid = (isset($transactionidx) AND !empty($transactionidx)) ? $transactionidx : construct_transaction_id();
	 $ipaddress       = IPADDRESS;
     $referer         = REFERRER;
     $createdate      = DATETIME24H;
	$combine_invoices=explode(",",$ilance->GPC['invhidden']);
	$prev_invoice=0;
	foreach($combine_invoices as $cinvoice)
	{
            $query="select * from ".DB_PREFIX."invoices where invoiceid ='".$cinvoice."' and user_id='".$ilance->GPC['uid']."' and status='unpaid'";
	$sql=$ilance->db->query($query);
		if($ilance->db->num_rows($sql)==0)
		{
			print_action_failed("There is a problem in checking out this invoice, Logout and login again to check out", "javascript:history.back()");
                        exit();
		}
	 $query="select * from ".DB_PREFIX."invoices where combine_project like '%".$cinvoice."%' and user_id='".$ilance->GPC['uid']."'";
	$sql=$ilance->db->query($query);
		if($ilance->db->num_rows($sql)>0)
		{
			while($line=$ilance->db->fetch_array($sql))
			{
			if(in_array($cinvoice,explode(",",$line['combine_project'])))
			{
				$sql=$ilance->db->query("delete from ".DB_PREFIX."invoices where invoiceid='".$line['invoiceid']."'");
				$sql1=$ilance->db->query("delete from ".DB_PREFIX."invoice_projects where final_invoice_id='".$line['invoiceid']."'");
				$prev_invoice=$line['invoiceid'];
			} 
			}
		}
	}
	
			if($prev_invoice==0)
			{
	 $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "invoices
                        (invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,transactionid, combine_project)
                        VALUES(
                        NULL,
                        '" . $ilance->GPC['uid']. "',                        
                        '". $phrase['_escrow_payment_forward']."',
                        '" . $ilance->db->escape_string($amountnew) . "',
                        '0',
                        '" . $ilance->db->escape_string($totalamountnew) . "',
						'1',
						'".$totaltaxnew."',
						'including Tax',
                        'unpaid',
						'escrow',
                        '".$ilance->GPC['account_id']."',
                        '" . $ilance->db->escape_string($ipaddress) . "',
                        '" . $ilance->db->escape_string($referer) . "',
                        '" . $ilance->db->escape_string($createdate) . "',
                        '" . $ilance->db->escape_string($createdate) . "',  
                        '" . $ilance->db->escape_string($transactionid) . "',
						'".$ilance->GPC['invhidden']."'
                       )
                ", 0, null, __FILE__, __LINE__);
				$ilance->GPC['id'] = $ilance->db->insert_id();
			}
			else
			{
				 $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "invoices
                        (invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,transactionid, combine_project)
                        VALUES(
                        '".$prev_invoice."',
                        '" . $ilance->GPC['uid']. "',                        
                        '". $phrase['_escrow_payment_forward']."',
                        '" . $ilance->db->escape_string($amountnew) . "',
                        '0',
                        '" . $ilance->db->escape_string($totalamountnew) . "',
						'1',
						'".$totaltaxnew."',
						'including Tax',
                        'unpaid',
						'escrow',
                        '".$ilance->GPC['account_id']."',
                        '" . $ilance->db->escape_string($ipaddress) . "',
                        '" . $ilance->db->escape_string($referer) . "',
                        '" . $ilance->db->escape_string($createdate) . "',
                        '" . $ilance->db->escape_string($createdate) . "',  
                        '" . $ilance->db->escape_string($transactionid) . "',
						'".$ilance->GPC['invhidden']."'
                       )
                ", 0, null, __FILE__, __LINE__);
				$ilance->GPC['id'] = $ilance->db->insert_id();
			}			
						   
				$expinvhidden = explode(',',$ilance->GPC['invhidden']);	
		
							
				for($i=0; $i<count($expinvhidden);$i++)
				{
				//suku
				$coin_id=fetch_invoice('projectid',$expinvhidden[$i]);
			 	$coin_qty=$ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "invoiceid = '" .$expinvhidden[$i]. "'", "qty");
			 	$seller_id=$ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "invoiceid = '" .$expinvhidden[$i]. "'", "owner_id");	
				//id, invoice_id, project_id, coin_id, qty, shipper_id, buyer_id, seller_id, status, created_date, promocode, shipping_cost, disount_val, final_invoice_id			
				$ilance->db->query("INSERT INTO " . DB_PREFIX . "invoice_projects(`invoice_id`,`project_id`,`coin_id`,`qty`,`shipper_id`,`buyer_id`,`seller_id`,`status`,`created_date`,promocode, shipping_cost, disount_val, `final_invoice_id`)VALUES(
				'".$expinvhidden[$i]."',
				'".fetch_invoice('projectid',$expinvhidden[$i])."',
				'".$coin_id."',
				'".$coin_qty."',
				'".$ilance->GPC['shipper_id']."',
				'".$ilance->GPC['uid']."',
				'".$seller_id."',
				'unpaid',
				'".DATETIME24H."',
				'".$ilance->GPC['promocode']."',
				'".$ilance->GPC['shipping_cost']."',
				'".$ilance->GPC['disount_val']."',
				'".$ilance->GPC['id']."')");
				 //  $ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET status = 'cancelled', custommessage = '*CONSOLIDATED* ' WHERE invoiceid = '". $expinvhidden[$i]."' ");
				}
  
  
	$area_title = $phrase['_invoice_payment_preview_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_preview_menu'];
	
	if (!isset($ilance->GPC['account_id']) OR empty($ilance->GPC['account_id']))
	{
		$area_title = $phrase['_invoice_payment_menu_denied_payment'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
		
		print_action_failed($phrase['_no_payment_method_was_selected'], "javascript:history.back()");
		exit();
	}
	
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]"] = $phrase['_accounting'];
	$navcrumb["$ilpage[invoicepayment]?id=" . intval($ilance->GPC['id'])] = $phrase['_invoice_payment_preview_menu'];
	$navcrumb[""] = $phrase['_invoice'] . ' #' . intval($ilance->GPC['id']);

	$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;

	$onload = 'onlaodtextchange();';
		
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "' 
			AND user_id = '" . $ilance->GPC['uid'] . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		// invoice type
		$invoicetype = $res['invoicetype'];
		
		// invoice description
		$description = stripslashes($phrase['_payment_paypal_text']);
		$status = stripslashes($res['status']);
		// amount
		$amount = $res['amount'];
		$_SESSION['amountnew'] = $res['amount'];
		
	
			// murugan changes for promo code auction Oct 12
		if($ilance->GPC['checkpromo'] != '')
		{
	 		$checkvalue = explode(' ',$ilance->GPC['checkpromo']);
		   if($checkvalue[1] == '%' || $checkvalue[1]== '$')
		   {
			 if($checkvalue[1] == '%')
			 {									
				 $calculate = $amount * ($checkvalue[0]/100);				
				 $amount = $amount - $calculate;
				 $previewamount = $ilance->currency->format($amount, $res['currency_id']);
			 }
			 if($checkvalue[1] == '$')
			 {				  
				$amount = $amount - $checkvalue[0];	
				$previewamount = $ilance->currency->format($amount, $res['currency_id']);	
			 }
		  
		   }
	   }
	   else
	   {
	     	$previewamount = $ilance->currency->format($amount, $res['currency_id']);	
	   }
	   
		
		// preview the amount
		$totalpreviewamount = $ilance->currency->format($amount);
		// Below code is commended By murugan on Oct 12
		//$previewamount = $ilance->currency->format($res['amount'], $res['currency_id']);
		
		$taxlogic = '';
		
		// do we pay taxes?
		if ($res['istaxable'] > 0 AND $res['totalamount'] > 0)
		{
			$taxinfo = $res['taxinfo'];
    
			// change regular amount to total amount (including added taxes)
			$amount = $res['totalamount'];
			
			if($ilance->GPC['checkpromo'] != '')
		{
	 		$checkvalue = explode(' ',$ilance->GPC['checkpromo']);
		   if($checkvalue[1] == '%' || $checkvalue[1]== '$')
		   {
			 if($checkvalue[1] == '%')
			 {									
				 $calculate = $amount * ($checkvalue[0]/100);				
				 $amount = $amount - $calculate;
				 $totalpreviewamount = $ilance->currency->format($amount, $res['currency_id']);
			 }
			 if($checkvalue[1] == '$')
			 {				  
				$amount = $amount - $checkvalue[0];	
				$totalpreviewamount = $ilance->currency->format($amount, $res['currency_id']);	
			 }
		  
		   }
	   }
	   else
	   {
	     	
			$totalpreviewamount = $ilance->currency->format($amount, $res['currency_id']);	
	   }
	   $_SESSION['amountnew'] = $amount;
			
			//$totalpreviewamount = $ilance->currency->format($res['totalamount'], $res['currency_id']);
			
			// create the tax bit in html
			if (!empty($taxinfo))
			{
				$taxlogic = '
				<tr class="alt1">
				       <td align="right"><span class="gray">' . $phrase['_applicable_tax'] . ':</span></td>
				       <td align="left"><span class="blue">' . $taxinfo . '</span></td>
				</tr>';
			}
		}
		
		
		
		$customername = fetch_user('fullname', $custmr_uid);
		
		$createdate = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$duedate = ($res['duedate'] == '0000-00-00 00:00:00') ? '--' : print_date($res['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		print_date($res['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		
		$account_id = $ilance->GPC['account_id'];
		$custommessage = stripslashes($res['custommessage']);
		$invoiceid = intval($ilance->GPC['id']);
	}

	// #### INVOICE PAYMENT PREVIEW VIA ONLINE ACCOUNT BALANCE #####
	if ($ilance->GPC['account_id'] == 'account')
	{
		
		$show['transactionfees'] = $show['directpayment'] = 0;
		$payment_method = $phrase['_online_account_instant_payment'];
		$directpaymentform = '';
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### INVOICE PAYMENT VIA PAYPAL #############################
	else if ($ilance->GPC['account_id'] == 'paypal')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_paypal'];
		
		// #### gateway transaction fees #######################
		if ($ilconfig['paypal_transaction_fee'] > 0 OR $ilconfig['paypal_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['paypal_transaction_fee'];
			$fee_b = $ilconfig['paypal_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		//jai for bug2150
		if($status = 'unpaid')
		{
		$description = 'Payment Pending';
		}
		else
		{
		$description = stripslashes($phrase['_payment_paypal_text']);
		
		}
		//jai end for bug2150
		
		//aug 30 sekar kkk
		//works on sep 30 sekar kkk bug num 915
		$pay = 'paypal';
		$customername = fetch_user('fullname', $custmr_uid);
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0|'.$pay;
		
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$directpaymentform = $ilance->paypal->print_payment_form_staff($ilance->GPC['uid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['paypal_business_email'], $ilconfig['paypal_master_currency'], '', $customencrypted, 0);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','previewamount','payment_method','invoiceid','invoicetype','description','amount','customername','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	//sekar on oct12 paymethod
	else if ($ilance->GPC['account_id'] == 'card')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = 'Credit Card (Visa, Mastercard, Discover, American Express)';
		$customername = fetch_user('fullname', $custmr_uid);
		// #### gateway transaction fees #######################
		if ($ilconfig['paypal_transaction_fee'] > 0 OR $ilconfig['paypal_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['paypal_transaction_fee'];
			$fee_b = $ilconfig['paypal_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		//aug 30 sekar 
		//works on sep 30 sekar kkk bug num 915
		$pay = 'card';
		
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0|'.$pay;
		
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$directpaymentform = $ilance->paypal->print_payment_form_staff($ilance->GPC['uid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['paypal_business_email'], $ilconfig['paypal_master_currency'], '', $customencrypted, 0);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### INVOICE PAYMENT VIA STORMPAY ###########################
	else if ($ilance->GPC['account_id'] == 'stormpay')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_stormpay'];
		
		// #### gateway transaction fees #######################
		if ($ilconfig['stormpay_transaction_fee'] > 0 OR $ilconfig['stormpay_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['stormpay_transaction_fee'];
			$fee_b = $ilconfig['stormpay_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		$customername = fetch_user('fullname', $custmr_uid);
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
					
		$ilance->stormpay = construct_object('api.stormpay', $ilance->GPC);
		$directpaymentform = $ilance->stormpay->print_payment_form_staff($ilance->GPC['uid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['stormpay_business_email'], $ilconfig['stormpay_master_currency'], $ilconfig['stormpay_secret_code'], $customencrypted, 0);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### INVOICE PAYMENT VIA CASHU ##############################
	else if ($ilance->GPC['account_id'] == 'cashu')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_cashu'];
		
		// #### gateway transaction fees #######################
		if ($ilconfig['cashu_transaction_fee'] > 0 OR $ilconfig['cashu_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['cashu_transaction_fee'];
			$fee_b = $ilconfig['cashu_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		$customername = fetch_user('fullname', $custmr_uid);
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_staff($ilance->GPC['uid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	else if ($ilance->GPC['account_id'] == 'check')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_check_money_order'];
		$show['checkpayment'] = 1;
		// #### gateway transaction fees #######################
		/*if ($ilconfig['cashu_transaction_fee'] > 0 OR $ilconfig['cashu_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['cashu_transaction_fee'];
			$fee_b = $ilconfig['cashu_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}*/
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		$customername = fetch_user('fullname', $custmr_uid);
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_check_staff($ilance->GPC['uid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	else if ($ilance->GPC['account_id'] == 'bank')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_wire'];
		
		// #### gateway transaction fees #######################
		/*if ($ilconfig['cashu_transaction_fee'] > 0 OR $ilconfig['cashu_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['cashu_transaction_fee'];
			$fee_b = $ilconfig['cashu_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}*/
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		$customername = fetch_user('fullname', $custmr_uid);
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_bank_staff($ilance->GPC['uid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else if ($ilance->GPC['account_id'] == 'trade')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_trade_against_consignor_proceeds'];
		
		// #### gateway transaction fees #######################
		/*if ($ilconfig['cashu_transaction_fee'] > 0 OR $ilconfig['cashu_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['cashu_transaction_fee'];
			$fee_b = $ilconfig['cashu_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}*/
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		$customername = fetch_user('fullname', $custmr_uid);
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_trade_staff($ilance->GPC['uid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	
	// #### INVOICE PAYMENT VIA MONEYBOOKERS #######################
	else if ($ilance->GPC['account_id'] == 'moneybookers')
	{
		
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = $phrase['_moneybookers'];
		
		// #### gateway transaction fees #######################
		if ($ilconfig['moneybookers_transaction_fee'] > 0 OR $ilconfig['moneybookers_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['moneybookers_transaction_fee'];
			$fee_b = $ilconfig['moneybookers_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		$customername = fetch_user('fullname', $custmr_uid);
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $ilance->GPC['uid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->moneybookers = construct_object('api.moneybookers', $ilance->GPC);
		$directpaymentform = $ilance->moneybookers->print_payment_form($ilance->GPC['uid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['moneybookers_business_email'], $ilconfig['moneybookers_master_currency'], $ilconfig['moneybookers_secret_code'], $customencrypted, 0);
		
		$pprint_array = array('custmr_uid','directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}

	// #### INVOICE PAYMENT VIA CREDIT CARD ########################
	else
	{
		$show['directpayment'] = 0;
		
		$invoiceid = intval($ilance->GPC['id']);
		
		$result_active_cards = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "creditcards
			WHERE cc_id = '" . intval($ilance->GPC['account_id']) . "'
				AND user_id = '" . $ilance->GPC['uid'] . "'
				AND creditcard_status = 'active'
				AND authorized = 'yes'
			LIMIT 1
		");
		if ($ilance->db->num_rows($result_active_cards) > 0)
		{
			$res_cc = $ilance->db->fetch_array($result_active_cards);
			
			$dec_CardNumber = $ilance->crypt->three_layer_decrypt($res_cc['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
			$dec_CardNumber = str_replace(' ', '', $dec_CardNumber);
			
			$ccnum_hidden = substr_replace($dec_CardNumber, 'XX XXXX XXXX ', 2, (mb_strlen($dec_CardNumber) - 6));
			$payment_method = mb_strtoupper($res_cc['creditcard_type']) . '# ' . $ccnum_hidden;
			
			$sql_invoice = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
					AND user_id = '" . $ilance->GPC['uid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_invoice) > 0)
			{
				$res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
				
				$invoicetype = $res_invoice['invoicetype'];
				$description = stripslashes($phrase['_payment_paypal_text']);
				$customername = fetch_user('fullname', $custmr_uid);
				$transaction_fee_formatted = '';
				$transaction_fee = 0;

				$show['transactionfees'] = false;
				if ($ilconfig['cc_transaction_fee'] > 0 OR $ilconfig['cc_transaction_fee2'] > 0)
				{
					$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="'.sprintf("%01.2f", (($res_invoice['amount'] * $ilconfig['cc_transaction_fee']) +  $ilconfig['cc_transaction_fee2'])).'" />';
					$transaction_fee = sprintf("%01.2f", (($res_invoice['amount'] * $ilconfig['cc_transaction_fee']) + $ilconfig['cc_transaction_fee2']));
					$transaction_fee_formatted = ($ilconfig['cc_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['cc_transaction_fee2']);
					
					$show['transactionfees'] = true;
				}
				
				$previewamount = $ilance->currency->format($res_invoice['amount'], $res_invoice['currency_id']);
				$totalpreviewamount = $ilance->currency->format(($res_invoice['amount'] + $transaction_fee), $res_invoice['currency_id']);
				
				$taxlogic = '';
				
				// do we pay taxes?
				if ($res_invoice['istaxable'] > 0 AND $res_invoice['totalamount'] > 0)
				{
					$taxinfo = $res_invoice['taxinfo'];
		    
					// change regular amount to total amount (including added taxes)
					$amount = ($res_invoice['totalamount'] + $transaction_fee);
		    
					$totalpreviewamount = $ilance->currency->format(($res_invoice['totalamount'] + $transaction_fee), $res_invoice['currency_id']);
					
					// create the tax bit in html
					if (!empty($taxinfo))
					{
						$taxlogic = '<tr><td align="right"> '.$phrase['_applicable_tax'].':</td><td align="left">'.$taxinfo.'</td></tr>';
					}
				}
				else
				{
					$amount = $res_invoice['amount'];
				}
				
				$createdate = print_date($res_invoice['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				
				$duedate = '--';
				if ($res_invoice['duedate'] != '0000-00-00 00:00:00')
				{
					$duedate = print_date($res_invoice['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				}
				
				$paiddate = '--';
				if ($res_invoice['paiddate'] != '0000-00-00 00:00:00')
				{
					$paiddate = print_date($res_invoice['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0);
				}
			}				
			
			$pprint_array = array('taxlogic','directpaymentform','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','customername','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'users_invoicepayment_preview.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
		
		
		
		
		
		
		
		
		
	}
	
	
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment-checkorder')
{
// echo '<pre>';
// print_r($ilance->GPC);
// exit;
$show['fbook']=true;
$user_invoice_check_query="select * from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice']."' and user_id='".$ilance->GPC['userid']."'";
$user_invoice_check_result=$ilance->db->query($user_invoice_check_query);
if($ilance->db->num_rows($user_invoice_check_result)==0)
{
		print_action_failed('You dont have one such invoice', "javascript:history.back()");
}
  // [cmd] => _do-invoice-payment-checkorder
    /*[userid] => 34
    [display_text] => Escrow Payment Forward
    [currency] => 
    [amount] => 66.00
    [language] => en
    [email] => 121231@ianrussell.com
    [txt1] => Escrow Payment Forward
    [invoice] => 456
    [checknum] => r54666
    [agreecheck] => on*/

            //update invoice for check order and email to admin
            $ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET	status = 'scheduled',scheduled_date='".DATETIME24H."'
			
			WHERE invoiceid = '" . intval($ilance->GPC['invoice']). "'
			
		    ");
			close_child_invoice($ilance->GPC['invoice']);		

				
				$sql_totatlamount = $ilance->db->query("
			SELECT totalamount
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid  = '" . $ilance->GPC['invoice'] . "'");
				
			$res_totamount = $ilance->db->fetch_array($sql_totatlamount, DB_ASSOC);
				
		
		print_action_success('<b>Thank you for your payment notification</b><br/>Thank you for paying by check.<br><br>The total of your invoice is: $'.$res_totamount['totalamount'].' (including insured shipping).<br><br>Please make the check payable to GreatCollections and mail to us at:<br><br>GreatCollections,<br>2030 Main Street, Suite 620,<br>Irvine CA 92614.<br><br>We appreciate your business.', $ilpage['users'] . '?subcmd=_update-customer&id='.intval($ilance->GPC['userid']));
		
		 //jai end for bug 1597	
		exit(); 

}
	

if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment-bank')
{

$show['fbook']=true;
$user_invoice_check_query="select * from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice']."' and user_id='".$ilance->GPC['userid']."'";
$user_invoice_check_result=$ilance->db->query($user_invoice_check_query);
if($ilance->db->num_rows($user_invoice_check_result)==0)
{
		print_action_failed('User dont have one such invoice<br/>Please login again', $ilpage['users'].'?subcmd=_update-customer&id='.intval($ilance->GPC['userid']));

}
  // [cmd] => _do-invoice-payment-checkorder
    /*[userid] => 34
    [display_text] => Escrow Payment Forward
    [currency] => 
    [amount] => 66.00
    [language] => en
    [email] => 121231@ianrussell.com
    [txt1] => Escrow Payment Forward
    [invoice] => 456
    [checknum] => r54666
    [agreecheck] => on*/

            //update invoice for check order and email to admin
			////sekar works onsep 17 for paymethod
			
            $ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET	status = 'scheduled',paymethod = 'bank',scheduled_date='".DATETIME24H."'
			
			WHERE invoiceid = '" . intval($ilance->GPC['invoice']). "'
			
		    ");
				close_child_invoice($ilance->GPC['invoice']);			
			   
				
				
				print_action_success('<b>Invoice Marked as Payment Pending:</b> You have successfully confirmed that your invoice will be paid by bank wire.  If you need our wire instructions, please contact us.  Once we receive your wire, we will mark your invoice as paid and ship the items to you via your preferred method.  Thank you for your business.', $ilpage['users'].'?subcmd=_update-customer&id='.intval($ilance->GPC['userid']));
		        exit(); 

}	
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'view_pending_invoice' AND $ilance->GPC['uid'] > 0 )
	{
		$custmr_uid = $ilance->GPC['uid'];			
		$ilance->tax = construct_object('api.tax');	
		$sql_regardlist = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $ilance->GPC['uid']."'
			AND status = 'unpaid'	and not combine_project
			AND isfvf != 1
			AND isif != 1 
			AND isbuyerfee != 1 
			AND isenhancementfee != 1		
		");
		if($ilance->db->num_rows($sql_regardlist) > 0)
		{
			
			while($res_regardlist = $ilance->db->fetch_array($sql_regardlist))
			{
				$invid[] = $res_regardlist['invoiceid'];
				
				$show['invoicecancelled'] = 0;
		
				$area_title = $phrase['_invoice_payment_menu'] . ' #' . $txn;
				$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu'];
	
				$navcrumb = array();
				$navcrumb["$ilpage[accounting]?cmd=com-transactions"] = $phrase['_accounting'];
				$navcrumb[""] = $phrase['_transaction'] . ' #' . $txn;
				
				$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
											WHERE invoiceid = '".$res_regardlist['invoiceid']."'
											AND buyer_id = '".$ilance->GPC['uid']."'");
				if($ilance->db->num_rows($buy)>0)
				{
					$resbuy = $ilance->db->fetch_array($buy);
				$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
											WHERE coin_id = '".$res_regardlist['projectid']."'");
								$temp=$ilance->db->fetch_array($bids);						
					
					$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
					$res_regardlist['qty'] = $resbuy['qty'];
					 $totqty[] = $res_regardlist['qty']*$coin_no_in_set;
				}
				else
				{
				//check 	nocoin  in ilance_coins for each coins
				$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
											WHERE coin_id = '".$res_regardlist['projectid']."'");
								$temp=$ilance->db->fetch_array($bids);		
								
					$res_regardlist['qty'] = 1;
					 
					$totqty[] = empty($temp['nocoin'])?1:intval($temp['nocoin']);
				}
				
		
	
				$id = $res_regardlist['invoiceid'];
				$txn = $res_regardlist['transactionid'];
				$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	
				($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;

		$amountpaid =  $ilance->currency->format(0);
		// invoice creation date
				$createdate = print_date($res_regardlist['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$show['miscamount']=false;
				 $res_regardlist['miscamount'];
				if($res_regardlist['miscamount']>0)
				{
				$show['miscamount']=true;
				$miscamount =  $ilance->currency->format($res_regardlist['miscamount']);
				}
		// invoice due date
		if ($res_regardlist['duedate'] == "0000-00-00 00:00:00")
		{
			$duedate = '--';		
		}
		else
		{
			$duedate = print_date($res_regardlist['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		// invoice paid date
		if ($res_regardlist['paiddate'] == "0000-00-00 00:00:00")
		{
			$paiddate = '--';
		}
		else
		{
			$paiddate = print_date($res_regardlist['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		
		// invoice identifier
		$invoiceid = $id;
		
		$show['ispaid'] = $show['isunpaid'] = $show['isscheduled'] = $show['iscomplete'] = $show['iscancelled'] = 0;
		
		if ($res_regardlist['status'] == 'paid')
		{
			$show['ispaid'] = 1;
		}
		if ($res_regardlist['status'] == 'unpaid')
		{
			$show['isunpaid'] = 1;
		}
		if ($res_regardlist['status'] == 'scheduled')
		{
			$show['isscheduled'] = 1;
		}
		if ($res_regardlist['status'] == 'complete')
		{
			$show['iscomplete'] = 1;
		}
		if ($res_regardlist['status'] == 'cancelled')
		{
			$show['iscancelled'] = 1;
		}			
		if ($res_regardlist['invoicetype'] == 'subscription')
		{
			$show['subscriptionpayment'] = true;
		}
		else
		{
			$show['subscriptionpayment'] = false;
		}
		
		
				
				if ($res_regardlist['status'] == 'unpaid' OR $res_regardlist['status'] == 'scheduled')
				{
					if ($res_regardlist['p2b_user_id'] == $ilance->GPC['uid'])
					{
						$show['paymentpulldown'] = 0;
						$cmd = '_do-invoice-action';
					}
					else if ($res_regardlist['user_id'] == $ilance->GPC['uid'])
					{
						$show['paymentpulldown'] = 1;
						$cmd = '_do-invoice-preview';
					}
				}
				else if ($res_regardlist['status'] == 'cancelled')
				{
					$show['invoicecancelled'] = 1;
				}
				else
				{
					$show['paymentpulldown'] = 0;
					$cmd = '_do-invoice-action';
				}
				
				
				$show['listing'] = 0;
				$project_id = 0;
				if ($res_regardlist['projectid'] > 0)
				{
				$show['listing'] = 1;
				$listing = fetch_coin_table('Title',$res_regardlist['projectid']);
				$haswinner = fetch_auction('haswinner', $res_regardlist['projectid']);			
				$project_id = $res_regardlist['projectid'];
				$projects[] = $res_regardlist['projectid'];
				}
				// tax check 
				$taxdetails = $res_regardlist['istaxable'];
				$show['buyer'] = 0;
				$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
												WHERE projectid = '".$res_regardlist['projectid']."'
												AND user_id = '".$ilance->GPC['uid']."'
												AND isbuyerfee = '1'");
					if($ilance->db->num_rows($buyfee_inv) > 0)
					{
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						$totalamountlist = $ilance->currency->format(($res_regardlist['amount'] + $res_buyfee['amount'] ), $res_regardlist['currency_id']);
						$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $res_regardlist['currency_id']);
						$buyerfee1 = $res_buyfee['amount'];
						$totalamountlist1 = $res_regardlist['amount'] + $res_buyfee['amount'] ;
						$show['buyer'] = 1;
					}
					else
					{
						$totalamountlist = $ilance->currency->format(($res_regardlist['amount'] ), $res_regardlist['currency_id']);
						$buyerfee =  $ilance->currency->format(0, $res_regardlist['currency_id']);
						$buyerfee1 = 0;
						$totalamountlist1 = $res_regardlist['amount'];
						$show['buyer'] = 1;
					}
										
					$paymethod = ucwords($res_regardlist['paymethod']);
					$paystatus = ucwords($res_regardlist['status']);
					$providername = $phrase['_billing_and_payments'];
					$provider = SITE_NAME;
					$providerinfo = SITE_ADDRESS;
					
					$show['viewingasprovider'] = $show['escrowblock'] = false;
					if ($res_regardlist['invoicetype'] == 'escrow')
					{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
			
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			
				$customer = fetch_user('username', $res_regardlist['user_id']);
				$customeremail = fetch_user('email', $res_regardlist['user_id']);
				$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
				$customername = fetch_user('fullname', $custmr_uid);	
					
			// display invoice type on invoice payment form
			$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
		}
		
					if ($res_regardlist['invoicetype'] == 'debit')
					{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
				
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			
				$customer = fetch_user('username', $res_regardlist['user_id']);
				$customeremail = fetch_user('email', $res_regardlist['user_id']);
				$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
				$customername = fetch_user('fullname', $custmr_uid);
					
			// display invoice type on invoice payment form
			$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
		}
					
					else if ($res_regardlist['invoicetype'] == 'buynow')
					{
						$show['providerblock'] = true;
						$customer = fetch_user('username', $res_regardlist['user_id']);
						$customeremail = fetch_user('email', $res_regardlist['user_id']);						
						$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
						$customerinfo = print_shipping_address_text($res_regardlist['user_id']) . fetch_business_numbers($res_regardlist['user_id']);						
						$customername = fetch_user('fullname', $res_regardlist['user_id']);
						
					}
				$description .= stripslashes($res_regardlist['description']).'<br>';
				$amountcal[] = $res_regardlist['amount'];
				$taxinfolist = $res_regardlist['taxinfo'];
				$invoicetype = $res_regardlist['invoicetype'];
				$buyerfeecal[] = $buyerfee1;
				$totalamountlistcal[] = $totalamountlist1;
			
				
				$res_regardlist['item_id'] 	 = 	$res_regardlist['projectid'];
				$res_regardlist1['itemtitle'] = fetch_coin_table('Title',$res_regardlist['projectid']);
				/*======vijay bug id:4714 start=====*/
				if($res_regardlist['Site_Id'] >0)
				{
				$res_regard='eBay';
				}
				else
				{
				$res_regard='GC';
				}
				$res_regardlist['Site_Id'] 	 =$res_regard;
				/*======vijay bug id:4714 end=====*/
				
				if ($ilconfig['globalauctionsettings_seourls'])
				{
				
					$res_regardlist['item_id']='<a href="'.HTTPS_SERVER.'Coin/'.$res_regardlist['projectid'].'/'.construct_seo_url_name($res_regardlist1['itemtitle']).'"> '.$res_regardlist['item_id'].'</a>';
					$res_regardlist['itemtitle'] ='<a href="'.HTTPS_SERVER.'Coin/'.$res_regardlist['projectid'].'/'.construct_seo_url_name($res_regardlist1['itemtitle']).'"> '.$res_regardlist1['itemtitle'].'</a>';
					
				}
				else
				{
					$res_regardlist['item_id']='<a href="merch.php?id='.$res_regardlist['projectid'].'">'.$res_regardlist1['item_id'].'</a>';
					$res_regardlist['itemtitle']='<a href="merch.php?id='.$res_regardlist['projectid'].'">'.$res_regardlist1['itemtitle'].'</a>';
				}
						
					
				//$res_regardlist['itemtitle'] = fetch_auction('project_title', $res_regardlist['projectid']);
				$res_regardlist['finalprice'] = $ilance->currency->format($res_regardlist['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
				$res_regardlist['buyerfees'] = $buyerfee;
				$res_regardlist['totallistamount'] =  $totalamountlist;
			  	$regardlist[] = $res_regardlist;
			}
			$show['taxes'] = 0;
			
			$qtyhidden = '<input type = "hidden" name="qtyhidden" id="qtyhidden" value="'.array_sum($totqty).'">';
			$invidim = implode(',',$invid);
			$invidhidden = '<input type = "hidden" name="invhidden" id="invhidden" value="'.$invidim.'">';
			$amounttotal = array_sum($totalamountlistcal);
			$amount = $ilance->currency->format(array_sum($totalamountlistcal),$ilconfig['globalserverlocale_defaultcurrency']);
			//karthik start Apr 15
			 $_SESSION['ilancedata']['user']['totalamount']=array_sum($totalamountlistcal);
			// end
			
			//karthik on sep06 for sales tax reseller
			 $sales_tax_reseller = fetch_user('issalestaxreseller',$ilance->GPC['uid']);	
			 
			if ($ilance->tax->is_taxable($ilance->GPC['uid'], $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
             {			 	
				$state = fetch_user('state',$ilance->GPC['uid']);			
				 $taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($ilance->GPC['uid'], $amounttotal, 'buynow', 0).'%, '.$state.')';
				 
				 //new changes apr22 hiddeen taxinfo variable
				 
				$taxinfonew = $ilance->tax->fetch_taxdetails($ilance->GPC['uid'], $amounttotal, 'buynow', 0);
				$taxamount1 = $ilance->tax->fetch_amount($ilance->GPC['uid'], $amounttotal, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				
				$show['taxes'] = 1;
			}
			else if ($ilance->tax->is_taxable($ilance->GPC['uid'], $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
			{						
				 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
				$taxamount1 = $ilance->tax->fetch_amount($ilance->GPC['uid'], 0, 'buynow', 0);
				
				$taxinfonew = 0.00;
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
			}
			else 
			{						
				 $taxinfo = 'Sales Tax Not Applicable (Out of State)';
				$taxamount1 = $ilance->tax->fetch_amount($ilance->GPC['uid'], 0, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
				
				$taxinfonew = 0.00;
			}
			$buyerfe = array_sum($buyerfeecal);
			$buyerfee = $ilance->currency->format($buyerfe,$ilconfig['globalserverlocale_defaultcurrency']);
			//suku1
			$taxamount1=empty($taxamount1)?"0":$taxamount1;
			$taxamounthidden = '<input type = "hidden" name="taxhidden" id="taxhidden" value="'.$taxamount1.'">
			                    <input type = "hidden" name="taxinfonew" id="taxinfonew" value="'.$taxinfonew.'">
								<input type = "hidden" name="taxhidden1" id="taxhidden1" value="'.$taxamount1.'">
								<input type = "hidden" name="taxshipcal" id="taxshipcal" value="0">';
		
		// murugan changes on feb 28	
		//if ($taxdetails)
		if ($taxamount1 > 0)
		{			
			$totalamount = $ilance->currency->format(($amounttotal + $taxamount1), $ilconfig['globalserverlocale_defaultcurrency']);
			$totalamountnew = $amounttotal + $taxamount1;
			//suku
			$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden" value="'.$totalamountnew.'">
			                <input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="1">';			
			$show['taxes'] = 1;
		}
		else
		{
			
			$totalamount = $ilance->currency->format($amounttotal, $ilconfig['globalserverlocale_defaultcurrency']);
			$totalamountnew = $amounttotal;
			//suku1
			$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden"  value="'.$totalamountnew.'">
			                <input type = "hidden" name="taxhiddenyes" id="taxhiddenyes" value="0">';			
		}
		
		$totalhidden.= '<input type = "hidden" name="totalhidden_base" id="totalhidden_base"  value="'.$totalamountnew.'">';



//bug # 4514 kumaravel start

		//vijay work start for bug id #4409  - Payment Restrictions Not Working
	if (!empty($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] >0)
	{
	
		$payment_method_pulldown = print_paymethod_pulldown('invoicepayment', 'account_id', $ilance->GPC['uid'],'','staff');
		
	}
	else
	{

		if( $_SESSION['ilancedata']['user']['totalamount'] < 10000)
		{	
			$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
										<optgroup label="Online Payment">';
			
			$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$ilance->GPC['uid']);
			$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
						
			$paymethod_sql=$ilance->db->query("	select * 
												from ".DB_PREFIX."payment_methods 
												where id in (".$user_paymethods['allowed_paymethods'].") 
												order by sort");
			while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
			{
				$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
			}
				$payment_method_pulldown.='</optgroup></select>';		
		}	
		else
		{
			$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
										<optgroup label="Online Payment">';
			
			$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$ilance->GPC['uid']);
			$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
			
			// bug # 4654 - kumaravel 			
			$paymethod_sql=$ilance->db->query("	select * 
												from ".DB_PREFIX."payment_methods 
												where id in (".$user_paymethods['allowed_paymethods'].") 
												and id NOT IN (6,10)
												order by sort");
			while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
			{
				$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
			}
				$payment_method_pulldown.='</optgroup></select>';		
		}

	}
				//vijay work end for bug id #4409  - Payment Restrictions Not Working
				

	if($ilance->GPC['uid']==82)
	{
		$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
									<optgroup label="Online Payment">';
		
		$user_paymethods_sql=$ilance->db->query("	select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$ilance->GPC['uid']);
		$user_paymethods=$ilance->db->fetch_array($user_paymethods_sql);
        // bug # 4654 - kumaravel 
		if( $_SESSION['ilancedata']['user']['totalamount'] < 10000)
		{
			$paymethod_sql=$ilance->db->query("	select * 
											from ".DB_PREFIX."payment_methods 
											where id in (".$user_paymethods['allowed_paymethods'].") 
											order by sort"); 			
		}
		else
		{
			$paymethod_sql=$ilance->db->query("	select * 
											from ".DB_PREFIX."payment_methods 
											where id in (".$user_paymethods['allowed_paymethods'].")
											and id NOT IN (6,10)
											order by sort"); 			
		}									
		while($allowed_methods=$ilance->db->fetch_array($paymethod_sql))
		{
			$payment_method_pulldown.='<option value="'.$allowed_methods['value'].'">'.$phrase[$allowed_methods['title']].'</option>';
		}
			$payment_method_pulldown.='</optgroup></select>';
	}
    


//bug # 4514 kumaravel end



	
		$shippment_nethod_pulldown = print_shippment_nethod_pulldown($projects,$selected=0,'shipper_id','return change_shipper();',array_sum($totqty));
		
	
		$shipper_drop_down = $shippment_nethod_pulldown['html'];
		//suku
		$headinclude.='<script>
		function change_shipper()
		{
          
		var shippers_base_cost=new Array(); 
		var shippers_added_cost=new Array();
		var international_extra_morethen_n_coins=0;
		
		'.$shippment_nethod_pulldown['script'].'
		var shipper=document.getElementById("shipper_id").value;
		
		// karthik start apr 16
		var taxamt = document.getElementById("taxhidden").value;
		

		var taxpresent = document.getElementById("taxhiddenyes").value;
		

		var taxinfonew = document.getElementById("taxinfonew").value;
		

		
		if(shipper == 26 && shippers_base_cost[shipper] == 0)
		
		{
		document.getElementById("free_announce").innerHTML ="<span class=\"green\">Standard shipping is free for your first auction purchase (U.S. only)</span>";
		
		}
		
		else
		
		{
		
         
		  
		}
		
		  //end
		//var totalproject = document.getElementById("total_val").value;
		if(shipper>0)
		{
			//document.getElementById("sub").disabled = false;
			
			invhidden=document.getElementById("invhidden").value;
			qtyhiddennew=document.getElementById("qtyhidden").value;
			projectlist=invhidden.split(",");
			
			//var txt = parseFloat(projectlist.length) - parseFloat(totalproject);
			var txt = parseFloat(projectlist.length);
			// muruagn changes on apr 17 for qty
			//var no_item=txt;
			var no_item=qtyhiddennew;
					 
			if(projectlist.length > 0)
		    {
			var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
		    }
			else
			{
			
			}
			var shipping_total=(no_item)*shippers_added_cost[shipper]+shippers_base_cost[shipper];
			 
			shipping_total=shipping_total+international_extra_morethen_n_coins;
			shipping_cost=shipping_total.toFixed(2);
 //new change calculating  tax amount for shipping
			
			var taxcount = (taxinfonew *  shipping_cost) / 100;
			
			document.getElementById("taxshipcal").value = taxcount;
						
			var taxadd = parseFloat(document.getElementById("taxhidden1").value) + parseFloat(taxcount);
			
			newtaxadd = taxadd.toFixed(2);
		   
		    document.getElementById("taxhidden").value = newtaxadd;
			
			//end
			document.getElementById("shipping_cost").value=shipping_cost;
			calculate_total();
		}else
		{
		
		//document.getElementById("sub").disabled = true;
		document.getElementById("shipping_cost").value="0";
		calculate_total();
		}
 	  return false;
		}
 
function promocodecheck(val,user_id)
{
 if (window.XMLHttpRequest) { // Mozilla & other compliant browsers
		request = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // Internet Explorer
		request = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	request.onreadystatechange = function ajaxResponse(){
		if (request.readyState==4){
		returned=request.responseText;
			result=returned.split("|");
			if(result[1]=="$" || result[1]=="%")
			{
			var discount=parseFloat(result[0]);
			var temp22=discount.toFixed(2);
			document.getElementById("disount_val").value=temp22;
			if(result[1]=="$")
			discount_str="US$"+temp22+" from total amount";
			if(result[1]=="%")
			discount_str=discount+" % from total amount";
			document.getElementById("promodiv").innerHTML= "You have saved "+discount_str;
			calculate_total();
			}else
			{
			document.getElementById("promodiv").innerHTML= returned;
			document.getElementById("disount_val").value=0;
			calculate_total();
			}
		}else
		{
			document.getElementById("promodiv").innerHTML= "<img src=\"images/default/working.gif\"/>";	
		}
	}
	url ="ajax.php?promocodeauction=" +val+"&projectid="+user_id;
	request.open("GET", url,true);
	request.send(null);
}
function calculate_total()
{
totalhidden_base=parseFloat(document.getElementById("totalhidden_base").value);
disount_val=parseFloat(document.getElementById("disount_val").value);
shipping_cost=parseFloat(document.getElementById("shipping_cost").value);
//new changes apr22
tax_cost=parseFloat(document.getElementById("taxhidden").value);
tax_cost_inship = parseFloat(document.getElementById("taxshipcal").value);
totalhidden=totalhidden_base-disount_val+shipping_cost+tax_cost_inship;
document.getElementById("totalhidden").value=totalhidden;
disount_val_text=disount_val.toFixed(2);
shipping_cost_text=shipping_cost.toFixed(2);
totalhidden_text=totalhidden.toFixed(2);
//apr22
document.getElementById("sales_tax_div").innerHTML="US$"+tax_cost.toFixed(2)+"";
document.getElementById("dicount_amount_div").innerHTML="(US$"+disount_val_text+")";
document.getElementById("ship_cost_div").innerHTML="US$"+shipping_cost_text;
document.getElementById("totalamount_area").innerHTML="US$"+totalhidden_text;
//oct-31

document.getElementById("totalamt_area").innerHTML="US$"+totalhidden_text;
}
</script>
		';
		
		
		$onload = 'javascript:document.invoicepayment.reset();change_shipper();';
		$user_id=$ilance->GPC['uid'];
		$pprint_array = array('custmr_uid','qtyhidden','user_id','shipper_drop_down','taxamounthidden','totalhidden','invidhidden','project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','miscamount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'users_invoice.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->parse_loop('main', array('regardlist'));
		$ilance->template->pprint('main', $pprint_array);
		
		exit();
		}
		else
		{
			print_action_failed('There are no unpaid invoices in this user account.', $ilpage['users']);
		}
	}
	
	
	

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function close_child_invoice($parent_invoice)
{

global $ilance;
$sql=$ilance->db->query("select combine_project from ".DB_PREFIX."invoices where invoiceid='".$parent_invoice."'");
if($ilance->db->num_rows($sql)>0)
{
while($line=$ilance->db->fetch_array($sql))
{
$child_invoice_list=explode(",",$line['combine_project']);
foreach($child_invoice_list as $invoice_id)
{

	$ilance->db->query("update ".DB_PREFIX."invoices set status='complete', scheduled_date='".DATETIME24H."' where invoiceid='".$invoice_id."'");
}
}
}

}
function not_first_shipment()
{
global $ilance;
$query="select invoiceid from ".DB_PREFIX."invoices where user_id='".$ilance->GPC['uid']."' and combine_project!='' and status='paid'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)>0)
{
return true;
}else
{
return false;
}
}
exit;

/*=====
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>