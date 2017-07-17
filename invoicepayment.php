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
	'accounting',
	'subscription',
	'buying',
	'selling',
	'rfp'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'invoicepayment'
);

// #### setup script location ##################################################
define('LOCATION','invoicepayment');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');

$ilance->subscription = construct_object('api.subscription');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[invoicepayment]" => $ilcrumbs["$ilpage[invoicepayment]"]);

// #### build our encrypted array for decoding purposes
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['invoicepayment'] . print_hidden_fields(true, array(), true)));
	exit();
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='combine' AND isset($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid']>0)
{
	exit;
}

//##### Welcome page after from paypal #########################################33
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view')
{
	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'complete')
	{
				print_notice('Invoice Payment', 'Thank you for submitting your payment. Once your payment has been verified, we will mark your invoice as paid and advise you of shipping information.  Thank you for your business.', $ilpage['mycp'], $phrase['_mycp']);
				exit();
	}else if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancel')	
	{
				print_notice('Invoice Payment', 'Your invoice remains unpaid.  To pay for your pending invoice at a later time, please return to My GC and click on Buyer Invoices.  Thank you for using GreatCollections', $ilpage['mycp'], $phrase['_mycp']);
				exit();
	}
}

// #### USER WHO GENERATED AN INVOICE IS UPDATING THE STATUS ###########        
if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'p2baction')
{
	// #### mark as paid ###########################################
	if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markaspaid' AND isset($uncrypted['invoiceid']) AND $uncrypted['invoiceid'] > 0 AND isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET p2b_markedaspaid = '1',
			status = 'paid',
			paiddate = '" . DATETIME24H . "',
			paid = totalamount
			WHERE invoiceid = '" . intval($uncrypted['invoiceid']). "'
				AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		$area_title = $phrase['_marking_provider_generated_invoice_as_paid'];
		$page_title = SITE_NAME . ' - ' . $phrase['_marking_provider_generated_invoice_as_paid'];
		
		print_notice($phrase['_invoice_marked_as_paid'], $phrase['_you_have_successfully_confirmed_payment_status_on_this_generated_invoice'], HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&txn=' . $uncrypted['txn'], $phrase['_return_to_previous_menu']);
		exit(); 
	}
	
	// #### mark as unpaid #########################################
	else if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasunpaid' AND isset($uncrypted['invoiceid']) AND $uncrypted['invoiceid'] > 0 AND isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET p2b_markedaspaid = '0',
			status = 'unpaid',
			paiddate = '0000-00-00 00:00:00',
			paid = '0.00'
			WHERE invoiceid = '" . intval($uncrypted['invoiceid']). "'
				AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		$area_title = $phrase['_marking_provider_generated_invoice_as_unpaid'];
		$page_title = SITE_NAME . ' - ' . $phrase['_marking_provider_generated_invoice_as_unpaid'];
		
		print_notice($phrase['_invoice_marked_as_unpaid'], $phrase['_you_have_set_the_payment_status_of_this_transaction_to_unpaid'], HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&txn=' . $uncrypted['txn'], $phrase['_return_to_previous_menu']);
		exit(); 
	}
	
	// #### mark as cancelled ######################################
	else if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markascancelled' AND isset($uncrypted['invoiceid']) AND $uncrypted['invoiceid'] > 0 AND isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET p2b_markedaspaid = '0',
			status = 'cancelled',
			paiddate = '0000-00-00 00:00:00',
			paid = '0.00'
			WHERE invoiceid = '" . intval($uncrypted['invoiceid']). "'
				AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		$area_title = $phrase['_marking_provider_generated_invoice_as_cancelled'];
		$page_title = SITE_NAME . ' - ' . $phrase['_marking_provider_generated_invoice_as_cancelled'];
		
		print_notice($phrase['_invoice_marked_as_cancelled'], $phrase['_you_have_set_the_payment_status_of_this_transaction_as_cancelled'], HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&txn=' . $uncrypted['txn'], $phrase['_return_to_previous_menu']);
		exit(); 
	}
}

// #### PRINTABLE INVOICE PREVIEW ######################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'print' AND (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != ''))
{
	// are we admin?
	if ($_SESSION['ilancedata']['user']['isadmin'] == '1' AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
	{
		// admin views invoice popup via admin cp
		if (isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != '')
		{
			// via transaction order id
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . intval($ilance->GPC['uid']) . "'
					AND transactionid = '" . $ilance->db->escape_string($ilance->GPC['txn']) . "'
				LIMIT 1
			");
		}
		else if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			// via invoice id
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . intval($ilance->GPC['uid']) . "'
					AND invoiceid = '" . intval($ilance->GPC['id']) . "'
				LIMIT 1
			");
		}
	}
	else
	{
		// client views his own invoice popup
		if (isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != '')
		{
			    // via transaction order id
			    $sql = $ilance->db->query("
				    SELECT *
				    FROM " . DB_PREFIX . "invoices
				    WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
					    AND transactionid = '" . $ilance->db->escape_string($ilance->GPC['txn']) . "'
				    LIMIT 1
			    ");
		}
		else if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			    // via invoice id
			    // via transaction order id
			    $sql = $ilance->db->query("
				    SELECT *
				    FROM " . DB_PREFIX . "invoices
				    WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
					    AND invoiceid = '" . intval($ilance->GPC['id']) . "'
				    LIMIT 1
			    ");
		}
	}
    
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			// #### invoice that do not show a provider or merchant
			$invoice['siteaddress'] = SITE_ADDRESS;
			$invoice['sitebusinessnumber'] = '';		
			$invoice['providerfullname'] = $invoice['providerbusinessnumber'] = $invoice['providerusername'] = $invoice['providerfulladdress'] = '--';
			$invoice['sitebusinessnumber'] .= ($ilconfig['globalserversettings_registrationnumber'] != '')
				? '<br /><br /><strong>' . $phrase['_company_registration_number'] . '</strong><br />' . $ilconfig['globalserversettings_registrationnumber']
				: '';
				
			$invoice['sitebusinessnumber'] .= ($ilconfig['globalserversettings_vatregistrationnumber'] != '')
				? '<br /><br /><strong>' . $phrase['_vat_registration_number'] . '</strong><br />' . $ilconfig['globalserversettings_vatregistrationnumber']
				: '';
			
			// #### customer info ##########################
			$invoice['fullname'] = fetch_user('fullname', $res['user_id']);
			$invoice['username'] = fetch_user('username', $res['user_id']);
			$invoice['businessnumber'] = fetch_business_numbers($res['user_id'], 1);
			$invoice['fulladdress'] = print_shipping_address_text($res['user_id']);
			
			// #### invoice info ###########################
			$invoice['purchasedate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, $res['createdate']);
			$invoice['maxpaymentdays'] = $ilconfig['invoicesystem_maximumpaymentdays'];
			$invoice['duedate'] = $res['duedate'];
			$invoice['duedate'] = ($invoice['duedate'] == '0000-00-00 00:00:00')
				? '--'
				: print_date($res['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, $res['duedate']);
				
			$invoice['txn'] = $res['transactionid'];
			$invoice['invoiceid'] = $res['invoiceid'];
			$invoice['comments'] = isset($res['comments'])
				? stripslashes($res['comments'])
				: $phrase['_thank_you_for_your_business'];
				
			$invoice['description'] = handle_input_keywords($res['description']);
			$invoice['amount'] = $ilance->currency->format($res['amount'], $res['currency_id']);
			$invoice['totalamount'] = ($res['istaxable'])
				? $ilance->currency->format($res['amount'] + $res['taxamount'], $res['currency_id'])
				: $ilance->currency->format($res['amount'], $res['currency_id']);
				
			$invoice['totalpaid'] = isset($res['paid'])
				? $ilance->currency->format($res['paid'], $res['currency_id'])
				: $ilance->currency->format(0, $res['currency_id']);
				
			$invoice['notaxlogic'] = 0;
			$invoice['taxlogic'] = '';
	
			// #### for printable invoice view, show if overall invoice was credit or debit
			switch ($res['invoicetype'])
			{
				case 'storesubscription':
				{
					$invoice['type'] = $phrase['_debit'] . ' / ' . $phrase['_subscription'];
					break;
				}		
				case 'subscription':
				{
					$invoice['type'] = $phrase['_debit'] . ' / ' . $phrase['_subscription'];
					break;
				}		
				case 'commission':
				{
					$invoice['type'] = $phrase['_debit'] . ' / ' . $phrase['_commission'];
					break;
				}		
				case 'p2b':
				{
					$invoice['type'] = $phrase['_debit'] . ' / ' . $phrase['_provider_to_buyer_generated_invoice'];
					$invoice['notaxlogic'] = 1;
					break;
				}		
				case 'buynow':
				{
					$invoice['type'] = $phrase['_debit'] . ' / ' . $phrase['_escrow'];
					$invoice['notaxlogic'] = 1;
					break;
				}		
				case 'credential':
				{
					$invoice['type'] = $phrase['_debit'];
					break;
				}		
				case 'debit':
				{
					$invoice['type'] = $phrase['_debit'];
					break;
				}		
				case 'credit':
				{
					$invoice['type'] = $phrase['_credit'];
					$invoice['notaxlogic'] = 1;
					break;
				}		
				case 'escrow':
				{
					$invoice['type'] = $phrase['_debit'] . ' / ' . $phrase['_escrow'];
					$invoice['notaxlogic'] = 1;
					break;
				}
			}
	
			if ($invoice['notaxlogic'] == 0)
			{
				$taxinfo = $res['taxinfo'];
	    
				// create the tax bit in html
				if (!empty($taxinfo))
				{
					    $invoice['taxlogic'] = '
					    <tr>
						   <td align="right" class="tablehead"> ' . $phrase['_applicable_tax'] . ': &nbsp;&nbsp;</td>
						   <td align="left" class="tablehead" nowrap="nowrap">' . $taxinfo . '</td>
					    </tr>';
				}
			}
	
			// #### invoice type settings (show or not show another opponent on invoice page)
			if ($res['invoicetype'] == 'p2b')
			{
				$invoice['providerfullname'] = fetch_user('fullname', $res['p2b_user_id']);
				$invoice['providerbusinessnumber'] = fetch_business_numbers($res['p2b_user_id'], 1);
				$invoice['providerusername'] = fetch_user('username', $res['p2b_user_id']);
				$invoice['providerfulladdress'] = print_shipping_address_text($res['p2b_user_id']);
			}
			else if ($res['invoicetype'] == 'buynow')
			{
				$invoice['providerfullname'] = fetch_user('fullname', $res['p2b_user_id']);
				$invoice['providerbusinessnumber'] = fetch_business_numbers($res['p2b_user_id'], 1);
				$invoice['providerusername'] = fetch_user('username', $res['p2b_user_id']);
				$invoice['providerfulladdress'] = print_shipping_address_text($res['p2b_user_id']);
			}
			else if ($res['invoicetype'] == 'escrow')
			{
				$ilance->auction = construct_object('api.auction');
				$ilance->escrow = construct_object('api.escrow');
				
				if ($ilance->auction->fetch_auction_type($res['projectid']) == 'service')
				{
					$invoice['providerusername'] = $ilance->escrow->fetch_escrow_opponent($res['projectid'], $res['invoiceid'], 'service');
					$invoice['providerfullname'] = fetch_user('fullname', fetch_user('user_id', '', $invoice['providerusername']));
					$invoice['providerbusinessnumber'] = fetch_business_numbers(fetch_user('user_id', '', $invoice['providerusername']), 1);
					$invoice['providerfulladdress'] = print_shipping_address_text(fetch_user('user_id', '', $invoice['providerusername']));
				}
				else if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product')
				{
					$invoice['providerusername'] = $ilance->escrow->fetch_escrow_opponent($res['projectid'], $res['invoiceid'], 'product');
					$invoice['providerfullname'] = fetch_user('fullname', fetch_user('user_id', '', $invoice['providerusername']));
					$invoice['providerbusinessnumber'] = fetch_business_numbers(fetch_user('user_id', '', $invoice['providerusername']), 1);
					$invoice['providerfulladdress'] = print_shipping_address_text(fetch_user('user_id', '', $invoice['providerusername']));
				}
			}
			
			$invoice[] = $invoice;
		}
	}

	$pprint_array = array('login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

	$ilance->template->load_popup('main', 'invoicepayment_print.html');
	$ilance->template->parse_loop('main', 'invoice');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### INVOICE DOWNLOAD ACTION ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-action' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['invcmd']))
{
	switch ($ilance->GPC['invcmd'])
	{
		case 'csv':
		{
			header('Content-type: application/csv; charset="' . $ilconfig['template_charset'] . '"');
			header("Content-Disposition: \"inline; filename=invoice-(" . date('Y') . "-" . date('m') . "-" . date('d') . ").csv");
			
			echo $phrase['_invoice_pound'] . "," . $phrase['_userid'] . "," . $phrase['_description'] . "," . $phrase['_amount'] . "," . $phrase['_paid'] . "," . $phrase['_invoice_status'] . "," . $phrase['_invoice_type'] . "," . $phrase['_pay_method'] . "," . $phrase['_ip_address'] . "," . $phrase['_create_date'] . "," . $phrase['_due_date'] . "," . $phrase['_paid_date'] . "," . $phrase['_invoice_notes'] . "\n";
			
			$csv_results = array();
			
			$csv_query = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
					AND invoiceid = '" . intval($ilance->GPC['id']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			while ($csv_results = $ilance->db->fetch_array($csv_query, DB_ASSOC))
			{
				echo $csv_results['invoiceid'] . "," . $csv_results['user_id'] . "," . stripslashes($csv_results['description']) . "," . $ilance->currency->format($csv_results['amount'], $csv_results['currency_id']) . "," . $ilance->currency->format($csv_results['paid'], $csv_results['currency_id']) . "," . mb_strtoupper($csv_results['status']) . "," . mb_strtoupper(print_transaction_type($csv_results['invoicetype'])) . "," . mb_strtoupper($csv_results['paymethod']) . "," . $csv_results['ipaddress'] . "," . $csv_results['createdate'] . "," . $csv_results['duedate'] . "," . $csv_results['paiddate'] . "," . stripslashes($csv_results['custommessage']) . "\n";
			}
			
			$area_title = $phrase['_downloading_csv_invoice_reports'];
			$page_title = SITE_NAME . ' - ' . $phrase['_downloading_csv_invoice_reports'];
			break;
		}		    
		case 'txt':
		{
			break;
		}                
		case 'pdf':
		{
			break;
		}
	}
	exit();
}

// #### PROVIDER GENERATING BUYER INVOICE ##############################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_generate-invoice' AND isset($uncrypted['buyer_id']) AND $uncrypted['buyer_id'] > 0 AND isset($uncrypted['seller_id']) AND $uncrypted['seller_id'] > 0 AND isset($uncrypted['project_id']) AND $uncrypted['project_id'] > 0)
{
	$ilance->subscription = construct_object('api.subscription');
	$ilance->auction = construct_object('api.auction');
	$ilance->auction_post = construct_object('api.auction_post');
	
	if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'generateinvoices') == 'no')
	{
		$area_title = $phrase['_access_denied_to_invoice_generation'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_invoice_generation'];
		
		print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', HTTP_SERVER . $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('generateinvoices'));
		exit();        
	}
	
	// verify that the provider generating the invoice to the buyer is actually him!
	if ($uncrypted['seller_id'] != $_SESSION['ilancedata']['user']['userid'])
	{
		$area_title = $phrase['_access_denied_to_invoice_generation'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_invoice_generation'];
		
		print_notice($phrase['_access_denied'], $phrase['_this_action_can_only_be_executed_by_the_awarded_service_provider'], HTTP_SERVER . $ilpage['main'], $phrase['_main_menu']);
		exit();        
	}
	
	$area_title = $phrase['_generating_new_invoice_to_service_buyer'];
	$page_title = SITE_NAME . ' - ' . $phrase['_generating_new_invoice_to_service_buyer'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[accounting]?cmd=management"] = $phrase['_accounting'];
	$navcrumb["$ilpage[selling]?cmd=management"] = $phrase['_selling_activity'];
	$navcrumb[] = $phrase['_invoice_generation_to_buyer'];

	$currency = print_left_currency_symbol();
	
	if ($ilconfig['invoicesystem_enablep2btransactionfees'])
	{
		$show['p2b_transaction_fee'] = true;
		$commissionfee = ($ilconfig['invoicesystem_p2bfeesfixed'])
			? $ilance->currency->format($ilconfig['invoicesystem_p2bfee'])
			: $ilconfig['invoicesystem_p2bfee'] . '%';
		
		$txnfee = (!empty($commissionfee)) ? $commissionfee : 0;
	}
	else
	{
		$show['p2b_transaction_fee'] = false;
	}
	
	// auction information
	$sql_auction = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . $uncrypted['project_id'] . "' 
			AND user_id = '" . $uncrypted['buyer_id'] . "'
	");
	if ($ilance->db->num_rows($sql_auction) > 0)
	{
		$result_auction = $ilance->db->fetch_array($sql_auction, DB_ASSOC);
		
		$project_id = $result_auction['project_id'];
		$project_title = stripslashes($result_auction['project_title']);
		
		// auction owner
		$sql_owner = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $uncrypted['buyer_id'] . "'
		");
		if ($ilance->db->num_rows($sql_owner) > 0)
		{
			$result_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
			$customer = stripslashes($result_owner['username']);
			$buyer_id = $uncrypted['buyer_id'];
			
			// service provider info
			$sql_provider = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $uncrypted['seller_id'] . "'
			");
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$result_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				$seller_id = $uncrypted['seller_id'];
				
				// related invoices association and links to view them
				$inv_assoc = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "invoices
					WHERE projectid = '" . $uncrypted['project_id'] . "' 
						AND p2b_user_id = '" . $uncrypted['seller_id'] . "' 
						AND user_id = '" . $uncrypted['buyer_id'] . "'
				");
				if ($ilance->db->num_rows($inv_assoc) > 0)
				{
					$show['other_invoices'] = true;
					
					$invoiceassociation = '';
					while ($inv_results = $ilance->db->fetch_array($inv_assoc, DB_ASSOC))
					{
						if ($inv_results['status'] == 'paid')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>' . $phrase['_paid'] . '</strong></div>';
						}
						else if ($inv_results['status'] == 'unpaid')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <span style="color:red"><strong>' . $phrase['_unpaid'] . '</strong></span></div>';
						}
						else if ($inv_results['status'] == 'scheduled')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>' . $phrase['_scheduled'] . '</strong></div>';
						}
						else if ($inv_results['status'] == 'complete')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>' . $phrase['_paid'] . '</strong></div>';
						}
						else if ($inv_results['status'] == 'cancelled')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>' . $phrase['_cancelled'] . '</strong></div>';
						}
					}
				}
				else
				{
					$show['other_invoices'] = false;
				}
				
				// latest last bid amount placed
				$sql_bidamount = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "project_bids
					WHERE user_id = '" . $uncrypted['seller_id'] . "'
						AND project_id = '" . $uncrypted['project_id'] . "'
					ORDER BY bid_id DESC
				");
				if ($ilance->db->num_rows($sql_bidamount) > 0)
				{
					$result_bidamount = $ilance->db->fetch_array($sql_bidamount, DB_ASSOC);
					$bidamount = $ilance->currency->format($result_bidamount['bidamount'], fetch_auction('currencyid', $result_bidamount['project_id']));
				}
				
				$paymentstatuspulldown = '<select name="paymentstatus" style="font-family: verdana"><option value="unpaid" selected="selected">' . $phrase['_mark_as_unpaid'] . '</option><option value="paid">' . $phrase['_mark_as_paid'] . '</option></select>';
				
				$paymentmethodpulldown = $ilance->auction_post->print_payment_method('p2b_paymethod', 'p2b_paymethod');
				
				// specific javascript includes
				$headinclude .= '
<script type="text/javascript">
<!--
function validatep2binvoice(f)
{
var Chars = "0123456789.,";
haveerrors = 0;
(f.amount.value.length < 1) ? showImage("amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
for (var i = 0; i < f.amount.value.length; i++)
{
    if (Chars.indexOf(f.amount.value.charAt(i)) == -1)
    {
	    alert(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
	    haveerrors = 1;
    }
}
if (f.amount.value == "0.00" || f.amount.value == "0")
{
    alert(phrase[\'_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount\']);
    haveerrors = 1;
}				    
return (!haveerrors);
}
//-->
</script>';
				$pprint_array = array('paymentstatuspulldown','paymentmethodpulldown','bidamount','invoiceassociation','project_title','project_id','customer','txnfee','session_amount','session_comments','currency','buyer_id','seller_id','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'invoicepayment_p2b.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
		}
	}
}

// #### PROVIDER INVOICE TO BUYER PREVIEW ##############################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-generate-preview' AND isset($ilance->GPC['amount']) AND $ilance->GPC['amount'] != "" AND isset($ilance->GPC['seller_id']) AND $ilance->GPC['seller_id'] > 0 AND isset($ilance->GPC['buyer_id']) AND $ilance->GPC['buyer_id'] > 0 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$area_title = $phrase['_preview_generation_of_new_invoice_to_buyer'];
	$page_title = SITE_NAME . ' - ' . $phrase['_preview_generation_of_new_invoice_to_buyer'];

	$navcrumb = array();
	$navcrumb["$ilpage[accounting]?cmd=management"] = $phrase['_accounting'];
	$navcrumb["$ilpage[selling]?cmd=management"] = $phrase['_selling_activity'];
	$navcrumb[] = $phrase['_invoice_generation_to_buyer'];
	
	$ilance->subscription = construct_object('api.subscription');

	// can this service provider generate invoices to their buyers?
	if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'generateinvoices') == 'no')
	{
		$area_title = $phrase['_access_denied_to_invoice_generation'];
		$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_invoice_generation'];
		
		print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', HTTP_SERVER . $ilpage['subscription'], $phrase['_click_here'], fetch_permission_name('generateinvoices'));
		exit();        
	}
	
	$ilance->GPC['amount'] = $ilance->currency->string_to_number($ilance->GPC['amount']);
	
	if ($ilconfig['invoicesystem_enablep2btransactionfees'])
	{
		$show['p2b_transaction_fee'] = true;
		$commissionfee = ($ilconfig['invoicesystem_p2bfeesfixed'])
			? $ilconfig['invoicesystem_p2bfee']
			: ($ilance->GPC['amount'] * $ilconfig['invoicesystem_p2bfee'] / 100);
		
		if (!empty($commissionfee))
		{
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $commissionfee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($commissionfee);
		}
		else 
		{
			$txn_fee_hidden = '';
			$transaction_fee_formatted = $ilance->currency->format(0);	
		}
	}
	else
	{
		$show['p2b_transaction_fee'] = false;
		$txn_fee_hidden = '';
	}
	
	$amount = $ilance->GPC['amount'];
	$amount_formatted = $ilance->currency->format($ilance->GPC['amount']);
	
	// fetch auction information
	$sql_auction = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
			AND user_id = '" . intval($ilance->GPC['buyer_id']) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql_auction) > 0)
	{
		$result_auction = $ilance->db->fetch_array($sql_auction, DB_ASSOC);
		
		$project_id = $result_auction['project_id'];
		$project_title = stripslashes($result_auction['project_title']);
		
		// fetch service auction buyer
		$sql_owner = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($ilance->GPC['buyer_id']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql_owner) > 0)
		{
			$result_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
			$customer = stripslashes($result_owner['username']);
			$buyer_id = $result_owner['user_id'];
			
			// service provider information
			$sql_provider = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['seller_id']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$result_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				
				$seller_id = intval($ilance->GPC['seller_id']);
				$instantpay = (isset($ilance->GPC['instantpay']) AND $ilance->GPC['instantpay'] > 0) ? 1 : 0;
				$comments = (isset($ilance->GPC['comments']) AND !empty($ilance->GPC['comments'])) ? ilance_htmlentities($ilance->GPC['comments']) : $phrase['_no_comments_available'];
				$paymentstatus = $ilance->GPC['paymentstatus'];
				$paymentstatus_formatted = ucwords($ilance->GPC['paymentstatus']);
				$paymentmethod = $ilance->GPC['p2b_paymethod'];
				$paymentmethod_formatted = $ilance->GPC['p2b_paymethod'];
				
				$pprint_array = array('paymentmethod_formatted','paymentstatus_formatted','paymentstatus','paymentmethod','comments','instantpay','description','amount_formatted','txn_fee_hidden','amount','transaction_fee_formatted','transaction_amount','project_title','project_id','customer','txnfee','currency','buyer_id','seller_id','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'invoicepayment_p2b_preview.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
		}
	}
}

// #### SERVICE PROVIDER INVOICE TO BUYER HANDLER ######################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-generate-submit' AND isset($ilance->GPC['amount']) AND !empty($ilance->GPC['amount']) AND isset($ilance->GPC['seller_id']) AND $ilance->GPC['seller_id'] > 0 AND isset($ilance->GPC['buyer_id']) AND $ilance->GPC['buyer_id'] > 0 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$area_title = $phrase['_new_invoice_was_generated_to_buyer'];
	$page_title = SITE_NAME . ' - ' . $phrase['_new_invoice_was_generated_to_buyer'];
	
	$ilance->accounting = construct_object('api.accounting');
	$ilance->accounting_p2b = construct_object('api.accounting_p2b');
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb["$ilpage[accounting]?cmd=management"] = $phrase['_accounting'];
	$navcrumb["$ilpage[selling]?cmd=management"] = $phrase['_selling_activity'];
	$navcrumb[] = $phrase['_invoice_generation_to_buyer'];
	
	$txnfee = 0;
	if (isset($ilance->GPC['transaction_fee']) AND $ilance->GPC['transaction_fee'] != '' AND $ilance->GPC['transaction_fee'] != '0' AND $ilance->GPC['transaction_fee'] != '0.00' AND $ilance->GPC['transaction_fee'] != '0.0')
	{
		$txnfee = $ilance->GPC['transaction_fee'];
		if ($txnfee < 0)
		{
			$txnfee = 0;
		}
	}

	$comments = $phrase['_no_comments_available'];		
	if (isset($ilance->GPC['comments']) AND !empty($ilance->GPC['comments']))
	{
		$comments = $ilance->GPC['comments'];
	}
	
	$instantpay = 0;
	if (isset($ilance->GPC['instantpay']) AND $ilance->GPC['instantpay'] > 0)
	{
		$instantpay = 1;
	}
	
	$ilance->GPC['paymentstatus'] = isset($ilance->GPC['paymentstatus']) ? $ilance->GPC['paymentstatus'] : 'unpaid';
	$ilance->GPC['paymentmethod'] = isset($ilance->GPC['paymentmethod']) ? $ilance->GPC['paymentmethod'] : '';
	
	$ilance->accounting_p2b->construct_p2b_transaction($ilance->GPC['amount'], intval($ilance->GPC['seller_id']), (int)$ilance->GPC['buyer_id'], (int)$ilance->GPC['project_id'], $comments, $txnfee, $instantpay, $ilance->GPC['paymentstatus'], $ilance->GPC['paymentmethod']);
	
	print_notice($phrase['_invoice_generation_complete'], $phrase['_you_have_successfully_generated_an_invoice_to_your_customer'] . '<br /><br />' . $phrase['_please_remember_if_you_have_not_paid_your_transaction_fee_for_the_generation_of_this_invoice_your_account_may_become_inactive_after_a_specific_period_of_time'], HTTPS_SERVER . $ilpage['accounting'], $phrase['_my_account']);
	exit();
}

// #### INVOICE PREVIEW ################################################
/*else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-preview' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
   
	$area_title = $phrase['_invoice_payment_preview_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_preview_menu'];
	
	if (!isset($ilance->GPC['account_id']) OR empty($ilance->GPC['account_id']))
	{
		$area_title = $phrase['_invoice_payment_menu_denied_payment'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
		
		print_notice($phrase['_invoice_error'], $phrase['_no_payment_method_was_selected'], HTTPS_SERVER . $ilpage['accounting'], $phrase['_my_account']);
		exit();
	}
	
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]"] = $phrase['_accounting'];
	$navcrumb["$ilpage[invoicepayment]?id=" . intval($ilance->GPC['id'])] = $phrase['_invoice_payment_preview_menu'];
	$navcrumb[""] = $phrase['_invoice'] . ' #' . intval($ilance->GPC['id']);

	$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	
	
		
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "' 
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		// invoice type
		$invoicetype = $res['invoicetype'];
		
		// invoice description
		$description = stripslashes($phrase['_payment_paypal_text']);
		
		// amount
		$amount = $res['amount'];
		$_SESSION['amountnew'] = $res['amount'];
		
		// murugan changes End
		if($ilconfig['staffsettings_feeinnumber'] != 0)
		{
			$buyerfee_calnum1 = $ilconfig['staffsettings_feeinnumber'];
		}
		else
		{
			$buyerfee_calnum1 = 0;
		}
		if($ilconfig['staffsettings_feeinpercentage'] != 0)
		{
			$buyerfee_calper1 = ($amount * ($ilconfig['staffsettings_feeinpercentage'] / 100));
		}
		else
		{
			$buyerfee_calper1 = 0;
		}
		if($buyerfee_calnum1 <= $buyerfee_calper1 )
		{
			$amount = $amount + $buyerfee_calper1;	
			
		}
		else
		{
		 	$amount = $amount + $buyerfee_calnum1;			
			
		}
    	// Murugan Changes On Jan 28 END
		
			// murugan changes for promo code auction Oct 12
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
			
			$totalpreviewamount = $ilance->currency->format($res['totalamount'], $res['currency_id']);
			
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
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$directpaymentform = $ilance->paypal->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['paypal_business_email'], $ilconfig['paypal_master_currency'], '', $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
					
		$ilance->stormpay = construct_object('api.stormpay', $ilance->GPC);
		$directpaymentform = $ilance->stormpay->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['stormpay_business_email'], $ilconfig['stormpay_master_currency'], $ilconfig['stormpay_secret_code'], $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->moneybookers = construct_object('api.moneybookers', $ilance->GPC);
		$directpaymentform = $ilance->moneybookers->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['moneybookers_business_email'], $ilconfig['moneybookers_master_currency'], $ilconfig['moneybookers_secret_code'], $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
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
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_invoice) > 0)
			{
				$res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
				
				$invoicetype = $res_invoice['invoicetype'];
				$description = stripslashes($phrase['_payment_paypal_text']);
			
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
			
			$pprint_array = array('taxlogic','directpaymentform','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'invoicepayment_preview.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
}*/

// murugan changes on Feb 15 For Buyer Invoice
// #### INVOICE PREVIEW ################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-preview' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
   	$not_first_shipment=false;
	$not_first_shipment=not_first_shipment();
	if($ilance->GPC['shipping_cost']==0 and $not_first_shipment==true)
	{
	print_notice($phrase['_invoice_error'], "Shipping cost was not rendered correctly, You have to select shipping before you submit the previous page", "javascript:history.back()", $phrase['_my_cp']);
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
            $query="select * from ".DB_PREFIX."invoices where invoiceid ='".$cinvoice."' and user_id='".$_SESSION['ilancedata']['user']['userid']."' and status='unpaid'";
	$sql=$ilance->db->query($query);
		if($ilance->db->num_rows($sql)==0)
		{
			print_notice($phrase['_invoice_error'], "There is a problem in checking out this invoice, Logout and login again to check out", "javascript:history.back()", $phrase['_my_cp']);
                        exit();
		}
	 $query="select * from ".DB_PREFIX."invoices where combine_project like '%".$cinvoice."%' and user_id='".$_SESSION['ilancedata']['user']['userid']."'";
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
                        '" . $_SESSION['ilancedata']['user']['userid']. "',                        
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
			}else
			{
				 $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "invoices
                        (invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,transactionid, combine_project)
                        VALUES(
                        '".$prev_invoice."',
                        '" . $_SESSION['ilancedata']['user']['userid']. "',                        
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
				'".$_SESSION['ilancedata']['user']['userid']."',
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
		
		print_notice($phrase['_invoice_error'], $phrase['_no_payment_method_was_selected'], HTTPS_SERVER . mygc, $phrase['_my_cp']);
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
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
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
		
		// murugan changes End
		/*if($ilconfig['staffsettings_feeinnumber'] != 0)
		{
			$buyerfee_calnum1 = $ilconfig['staffsettings_feeinnumber'];
		}
		else
		{
			$buyerfee_calnum1 = 0;
		}
		if($ilconfig['staffsettings_feeinpercentage'] != 0)
		{
			$buyerfee_calper1 = ($amount * ($ilconfig['staffsettings_feeinpercentage'] / 100));
		}
		else
		{
			$buyerfee_calper1 = 0;
		}
		if($buyerfee_calnum1 <= $buyerfee_calper1 )
		{
			$amount = $amount + $buyerfee_calper1;	
			
		}
		else
		{
		 	$amount = $amount + $buyerfee_calnum1;			
			
		}*/
    	// Murugan Changes On Jan 28 END
		
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
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0|'.$pay;
		
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$directpaymentform = $ilance->paypal->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['paypal_business_email'], $ilconfig['paypal_master_currency'], '', $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0|'.$pay;
		
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$directpaymentform = $ilance->paypal->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['paypal_business_email'], $ilconfig['paypal_master_currency'], '', $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
					
		$ilance->stormpay = construct_object('api.stormpay', $ilance->GPC);
		$directpaymentform = $ilance->stormpay->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['stormpay_business_email'], $ilconfig['stormpay_master_currency'], $ilconfig['stormpay_secret_code'], $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_check($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_bank($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form_trade($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
		
		$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
		$previewamount = $ilance->currency->format($amount);
		
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		
		$ilance->moneybookers = construct_object('api.moneybookers', $ilance->GPC);
		$directpaymentform = $ilance->moneybookers->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['moneybookers_business_email'], $ilconfig['moneybookers_master_currency'], $ilconfig['moneybookers_secret_code'], $customencrypted, 0);
		
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
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
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
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
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_invoice) > 0)
			{
				$res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
				
				$invoicetype = $res_invoice['invoicetype'];
				$description = stripslashes($phrase['_payment_paypal_text']);
			
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
			
			$pprint_array = array('taxlogic','directpaymentform','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'invoicepayment_preview.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
}

//feb 24 herakle
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment-checkorder')
{
$show['fbook']=true;
$user_invoice_check_query="select * from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice']."' and user_id='".$_SESSION['ilancedata']['user']['userid']."'";
$user_invoice_check_result=$ilance->db->query($user_invoice_check_query);
if($ilance->db->num_rows($user_invoice_check_result)==0)
{
		print_notice('You dont have one such invoice','Please login again', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);
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
			    $useremail = fetch_user('email',$ilance->GPC['userid']);
				$username = fetch_user('username',$ilance->GPC['userid']);
				
				
			    $to = $ilconfig['globalserversettings_siteemail'];
				$subject = 'Payment Forward For Check';
				$txt .= '<table broder="1"><tr><td>
						<b>Username: </b>'.$username. '<br></td></tr>'
						.'<tr><td><b>Inovice Amount: </b>'.$ilance->currency->format($ilance->GPC['amount'], $res['currency_id']). '<br><td></tr>'
						.'<tr><td><b>Inovice ID: </b>'.$ilance->GPC['invoice']. '<br><td></tr>'
						.'<tr><td><b>Check Number: </b>'.$ilance->GPC['checknum']. '<br><td></tr>' 
						.'</table>'						 
						. "\r\n";	
									

				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$ianemail=$ilconfig['globalserversettings_adminemail'];
				$headers .= 'From: '.$ianemail.''. "\r\n" ;


				
				
				$success= send_email_enquiry($to,$subject, $txt,OWNER_EMAIL,SITE_NAME,true);
				
				$sql_totatlamount = $ilance->db->query("
			SELECT totalamount
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid  = '" . $ilance->GPC['invoice'] . "'");
				
			$res_totamount = $ilance->db->fetch_array($sql_totatlamount, DB_ASSOC);
				
				//print_notice('Thank you for your payment notification','GreatCollections will start to prepare your order for shipment, and will send you an e-mail when we receive payment and your item(s) ship.  We appreciate your business.', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);
		print_notice('Thank you for your payment notification','Thank you for paying by check.<br><br>The total of your invoice is: $'.$res_totamount['totalamount'].' (including insured shipping).<br><br>Please make the check payable to GreatCollections and mail to us at:<br><br>GreatCollections,<br>17500 Red Hill Avenue, Suite 160,<br>Irvine CA 92614.<br><br>We appreciate your business.', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);
		
		 //jai end for bug 1597	
		exit(); 

}

else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment-bank')
{
$show['fbook']=true;
$user_invoice_check_query="select * from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice']."' and user_id='".$_SESSION['ilancedata']['user']['userid']."'";
$user_invoice_check_result=$ilance->db->query($user_invoice_check_query);
if($ilance->db->num_rows($user_invoice_check_result)==0)
{
		print_notice('You dont have one such invoice','Please login again', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);

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
			    $useremail = fetch_user('email',$ilance->GPC['userid']);
				$username = fetch_user('username',$ilance->GPC['userid']);
				
				
			     $to = $ilconfig['globalserversettings_siteemail'];
				$subject = 'Payment Forward For bank';
				$txt .= '<table broder="1"><tr><td>
						<b>Username: </b>'.$username. '<br></td></tr>'
						.'<tr><td><b>Inovice Amount: </b>'.$ilance->currency->format($ilance->GPC['amount'], $res['currency_id']). '<br><td></tr>'
						.'<tr><td><b>Inovice ID: </b>'.$ilance->GPC['invoice']. '<br><td></tr>'
						.'<br><td></tr>' 
						.'</table>'						 
						. "\r\n";	
									
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$ianemail=$ilconfig['globalserversettings_adminemail'];
				$headers .= 'From: '.$ianemail.''. "\r\n" ;


				
				

				$success= send_email_enquiry($to,$subject, $txt,OWNER_EMAIL,SITE_NAME,true);	
				
				
				
				print_notice('Invoice Marked as Payment Pending','You have successfully confirmed that your invoice will be paid by bank wire.  If you need our wire instructions, please contact us.  Once we receive your wire, we will mark your invoice as paid and ship the items to you via your preferred method.  Thank you for your business.', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);
		exit(); 

}
// murugan added in july 20 
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment-trade')
{
$show['fbook']=true;
$user_invoice_check_query="select * from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice']."' and user_id='".$_SESSION['ilancedata']['user']['userid']."'";
$user_invoice_check_result=$ilance->db->query($user_invoice_check_query);
if($ilance->db->num_rows($user_invoice_check_result)==0)
{
		print_notice('You dont have one such invoice','Please login again', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);

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
			    $useremail = fetch_user('email',$ilance->GPC['userid']);
				$username = fetch_user('username',$ilance->GPC['userid']);
				
			    $to = $ilconfig['globalserversettings_siteemail'];
				$subject = 'Payment Forward For Trade Consignor Proceeds';
				$txt .= '<table broder="1"><tr><td>
						<b>Username: </b>'.$username. '<br></td></tr>'
						.'<tr><td><b>Invoice Amount: </b>'.$ilance->currency->format($ilance->GPC['amount'], $res['currency_id']). '<br><td></tr>'
						.'<tr><td><b>Invoice ID: </b>'.$ilance->GPC['invoice']. '<br><td></tr>'
						.'<br><td></tr>' 
						.'</table>'						 
						. "\r\n";	
									
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$ianemail=$ilconfig['globalserversettings_adminemail'];
				$headers .= 'From: '.$ianemail.''. "\r\n" ;

				$success= send_email_enquiry($to,$subject, $txt,OWNER_EMAIL,SITE_NAME,true);
			
				 /* vijay work regarding bug id :#4571 start*/
				 
					$donnamail = $ilconfig['globalserversettings_accountsemail'];
					$donnasubject = 'Payment Forward For Trade Consignor Proceeds';
					$donnatxt .= '<table broder="1"><tr><td>
					<b>Username: </b>'.$username. '<br></td></tr>'
					.'<tr><td><b>Invoice Amount: </b>'.$ilance->currency->format($ilance->GPC['amount'], $res['currency_id']). '<br><td></tr>'
					.'<tr><td><b>Invoice ID: </b>'.$ilance->GPC['invoice']. '<br><td></tr>'
					.'<br><td></tr>' 
					.'</table>'						 
					. "\r\n";	

					$donnaheaders = "MIME-Version: 1.0" . "\r\n";
					$donnaheaders .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
					$iansemail=$ilconfig['globalserversettings_adminemail'];
					$donnaheaders .= 'From: '.$iansemail.''. "\r\n" ;

					$success= send_email_enquiry($donnamail,$donnasubject, $donnatxt,OWNER_EMAIL,SITE_NAME,true);

				 
				  /* vijay work end*/
				 
				 
				
				
				print_notice('Invoice Marked as Payment Pending',$phrase['_thanks_message_for_invoice_patment_made_by_trade_consignor_proceed'], HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=mycp', $phrase['_return_to_previous_menu']);
		exit(); 

}
// #### INVOICE PAYMENT HANDLER ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['account_id']) AND isset($ilance->GPC['invoicetype']))
{
	print_r($ilance_GPC);
	exit;
	($apihook = $ilance->api('invoicepayment_process_start')) ? eval($apihook) : false;
	// // murugan changes on Oct 12 for promo code auction here added one more variable that called $_SESSION['amountnew']
	
	$success = invoice_payment_handler($ilance->GPC['id'], $ilance->GPC['invoicetype'], $ilance->GPC['amount'],$_SESSION['amountnew'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['account_id'], false);		
	if ($success == false)
	{
		$area_title = $phrase['_invoice_error'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invoice_error'];
		
		print_notice($phrase['_invoice_error'], $phrase['_were_sorry_your_transaction_has_encountered_an_error_and_can_not_be_processed_at_the_moment'], HTTPS_SERVER . $mygc, $phrase['_my_cp']);
		exit();
	}
	
	($apihook = $ilance->api('invoicepayment_process_end')) ? eval($apihook) : false;
}

// #### DETAILED TRANSACTION VIEW ##############################
else
{

	($apihook = $ilance->api('invoicepayment_view_start')) ? eval($apihook) : false;
	
	$txn = $securekey_hidden = '';
	$id  = 0;
	
	if (isset($uncrypted['id']) AND $uncrypted['id'] > 0)
	{
		$id  = intval($uncrypted['id']);
	}
	else if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$id  = intval($ilance->GPC['id']);
	}
	else if (isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$txn = $uncrypted['txn'];
	}
	else if (isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != '')
	{
		$txn  = $ilance->GPC['txn'];
	}
	else
	{
		$area_title = $phrase['_invoice_payment_menu_denied_payment'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
		
		//print_notice($phrase['_invoice_error'], "There are no more pending invoices in your account", HTTPS_SERVER .mygc, $phrase['_my_cp']);
		print_notice("Invoice Information", "Please check your unpaid invoices in buyer invoice menu link", HTTPS_SERVER . $ilpage['buyer_invoice'], "Buyer Invoice");
		exit();
	}
	
	($apihook = $ilance->api('invoicepayment_start')) ? eval($apihook) : false;

	if (!empty($txn))
	{
 	    
		$sqlinvoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE transactionid = '" . $ilance->db->escape_string($txn) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
		if ($ilance->db->num_rows($sqlinvoice) > 0)
		{
			$res = $ilance->db->fetch_array($sqlinvoice);
			$id = $res['invoiceid'];
		}else
		{
		print_notice($phrase['_invoice_error'], "There are no such invoice in your account", HTTPS_SERVER .mygc, $phrase['_my_cp']);
				exit();
		}
		
		$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	}
	else if (isset($id) AND $id > 0)
	{
		$sql_invoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($id) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
	    
		$sqlinvoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($id) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		if ($ilance->db->num_rows($sqlinvoice) > 0)
		{
			$res = $ilance->db->fetch_array($sqlinvoice);
			$txn = $res['transactionid'];
		}
		
		$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	}

	$headtitle = (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view') ? $phrase['_review_invoice_details'] . ' (' . $id . ')' : $phrase['_secure_payment_preview'];
	$headmessage = (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view') ? $phrase['_you_can_review_download_and_print_this_invoice_page_for_your_records'] : $phrase['_make_a_secure_payment_using_our_billing'];
	
	$area_title = $phrase['_invoice_payment_menu'] . ' #' . $txn;
	$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]"] = $phrase['_accounting'];
	$navcrumb[""] = $phrase['_transaction'] . ' #' . $txn;
	
	if ($ilance->db->num_rows($sql_invoice) > 0)
	{
		$show['invoicecancelled'] = 0;
		
		$res_invoice = $ilance->db->fetch_array($sql_invoice);
		if ($res_invoice['status'] == 'unpaid' OR $res_invoice['status'] == 'scheduled')
		{
			if ($res_invoice['p2b_user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['paymentpulldown'] = 0;
				$cmd = '_do-invoice-action';
			}
			else if ($res_invoice['user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['paymentpulldown'] = 1;
				$cmd = '_do-invoice-preview';
			}
		}
		else if ($res_invoice['status'] == 'cancelled')
		{
			$show['invoicecancelled'] = 1;
		}
		else
		{
			$show['paymentpulldown'] = 0;
			$cmd = '_do-invoice-action';
		}
		
		$paymethod = ucwords($res_invoice['paymethod']);
		$paystatus = ucwords($res_invoice['status']);
		$providername = $phrase['_billing_and_payments'];
		$provider = SITE_NAME;
		$providerinfo = SITE_ADDRESS;
		
		$show['viewingasprovider'] = $show['escrowblock'] = false;
		
		if ($res_invoice['invoicetype'] == 'subscription')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'commission')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'p2b')
		{
			$show['providerblock'] = true;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$provider = fetch_user('username', $res_invoice['p2b_user_id']);
			$invoicetype = $phrase['_generated_invoice'];
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$providerinfo = print_shipping_address_text($res_invoice['p2b_user_id']) . fetch_business_numbers($res_invoice['p2b_user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
			$providername = fetch_user('fullname', $res_invoice['p2b_user_id']);
			$paymethod = $res_invoice['p2b_paymethod'];
			
			if ($res_invoice['p2b_user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['viewingasprovider'] = true;
				
				$crypted = array(
					'cmd' => 'p2baction',
					'subcmd' => 'markaspaid',
					'invoiceid' => $res_invoice['invoiceid'],
					'txn' => $res_invoice['transactionid'],
				);
				
				$markedaspaidurl = HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted);
				unset($crypted);
				
				$crypted = array(
					'cmd' => 'p2baction',
					'subcmd' => 'markasunpaid',
					'invoiceid' => $res_invoice['invoiceid'],
					'txn' => $res_invoice['transactionid'],
				);
				
				$markedasunpaidurl = HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted);
				unset($crypted);
				
				$crypted = array(
					'cmd' => 'p2baction',
					'subcmd' => 'markascancelled',
					'invoiceid' => $res_invoice['invoiceid'],
					'txn' => $res_invoice['transactionid'],
				);
				
				$markedascancelledurl = HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted);
				unset($crypted);
			}
			
			if (empty($res_invoice['p2b_paymethod']) OR $res_invoice['p2b_paymethod'] == '')
			{
				$paymethod = $phrase['_contact_trading_partner'];
			}
		}
		else if ($res_invoice['invoicetype'] == 'buynow')
		{
			$show['providerblock'] = true;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$provider = fetch_user('username', $res_invoice['p2b_user_id']);
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$providerinfo = print_shipping_address_text($res_invoice['p2b_user_id']) . fetch_business_numbers($res_invoice['p2b_user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
			$providername = fetch_user('fullname', $res_invoice['p2b_user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'credential')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'debit')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'credit')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$customeremail = fetch_user('email', $res_invoice['user_id']);
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'escrow')
		{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
			
			// quick auction checkup
			$sql_auction = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_invoice['projectid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_auction) == 0)
			{
				$area_title = $phrase['_invoice_payment_menu_denied_payment'];
				$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
				
				print_notice($phrase['_invoice_error'], "There are no more pending invoices in your account", HTTPS_SERVER .mygc, $phrase['_my_cp']);
				exit();
			}
			
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			if ($ilance->auction->fetch_auction_type($res_invoice['projectid']) == 'service')
			{
				// buyer is about to pay service escrow invoice to service provider escrow account held by site owner
				$customer = $ilance->escrow->fetch_escrow_owner($res_invoice['projectid'], $res_invoice['invoiceid'], 'service');
				$provider = $ilance->escrow->fetch_escrow_opponent($res_invoice['projectid'], $res_invoice['invoiceid'], 'service');
				$customerinfo = print_shipping_address_text(fetch_user('user_id', '', $customer)) . fetch_business_numbers(fetch_user('user_id', '', $customer));
				$providerinfo = print_shipping_address_text(fetch_user('user_id', '', $provider)) . fetch_business_numbers(fetch_user('user_id', '', $provider));
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));
				$providername = fetch_user('fullname', fetch_user('user_id', '', $provider));
			}
			else if ($ilance->auction->fetch_auction_type($res_invoice['projectid']) == 'product')
			{
				// bidder/winner is about to pay product escrow invoice to merchant provider held by site owner
				$customer = $ilance->escrow->fetch_escrow_opponent($res_invoice['projectid'], $res_invoice['invoiceid'], 'product');
				$provider = $ilance->escrow->fetch_escrow_owner($res_invoice['projectid'], $res_invoice['invoiceid'], 'product');
				$customerinfo = print_shipping_address_text(fetch_user('user_id', '', $customer)) . fetch_business_numbers(fetch_user('user_id', '', $customer));
				$providerinfo = print_shipping_address_text(fetch_user('user_id', '', $provider)) . fetch_business_numbers(fetch_user('user_id', '', $provider));
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));
				$providername = fetch_user('fullname', fetch_user('user_id', '', $provider));
			}
					
			// display invoice type on invoice payment form
			$invoicetype = print_transaction_type($res_invoice['invoicetype']);
		}
		
		($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;
		
		// transaction description
		$description = stripslashes($phrase['_payment_paypal_text']);
		
		// transaction identifier
		$transactionid = $res_invoice['transactionid'];
		
		// comments left by invoicer / receiver
		$comments = stripslashes($res_invoice['custommessage']);
		
		// invoice amount
		$amount = $ilance->currency->format($res_invoice['amount'], $res_invoice['currency_id']);
		
		// total invoice amount (after taxes what customer will pay)
		$show['taxes'] = 0;
		if ($res_invoice['istaxable'])
		{
			$tot = $res_invoice['amount'] + $res_invoice['taxamount'];
			$totalamount = $ilance->currency->format(($res_invoice['amount'] + $res_invoice['taxamount']), $res_invoice['currency_id']);
			$show['taxes'] = 1;
		}
		else
		{
			$tot = $res_invoice['amount'];
			$totalamount = $ilance->currency->format($res_invoice['amount'], $res_invoice['currency_id']);
		}
		
		// murugan Changes Jan 28
		$show['buyer'] = 0;
		if($ilconfig['staffsettings_feeinnumber'] != 0)
		{
			$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];
		}
		else
		{
			$buyerfee_calnum = 0;
		}
		if($ilconfig['staffsettings_feeinpercentage'] != 0)
		{
			$buyerfee_calper = ($res_invoice['amount'] * ($ilconfig['staffsettings_feeinpercentage'] / 100));
		}
		else
		{
			$buyerfee_calper = 0;
		}
		if($buyerfee_calnum <= $buyerfee_calper )
		{
			$totalamount = $ilance->currency->format(($tot + $buyerfee_calper ), $res_invoice['currency_id']);
			$buyerfee =  $ilance->currency->format($buyerfee_calper, $res_invoice['currency_id']);
			$show['buyer'] = 1;
		}
		else
		{
		 	$totalamount = $ilance->currency->format(($tot + $buyerfee_calnum ), $res_invoice['currency_id']);
			$buyerfee =  $ilance->currency->format($buyerfee_calnum, $res_invoice['currency_id']);
			$show['buyer'] = 1;
		}
    	// Murugan Changes On Jan 28 END
		
		// total amount paid for this invoice
		$amountpaid = $ilance->currency->format($res_invoice['paid'], $res_invoice['currency_id']);
		
		// invoice creation date
		$createdate = print_date($res_invoice['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);

		// invoice due date
		if ($res_invoice['duedate'] == "0000-00-00 00:00:00")
		{
			$duedate = '--';		
		}
		else
		{
			$duedate = print_date($res_invoice['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		// invoice paid date
		if ($res_invoice['paiddate'] == "0000-00-00 00:00:00")
		{
			$paiddate = '--';
		}
		else
		{
			$paiddate = print_date($res_invoice['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		
		// custom invoice message
		$custommessage = stripslashes($res_invoice['custommessage']);
		$show['comments'] = 1;
		if (empty($custommessage))
		{
			$show['comments'] = 0;
		}
		
		$show['listing'] = 0;
		$project_id = 0;
		if ($res_invoice['projectid'] > 0)
		{
			$show['listing'] = 1;
			$listing = fetch_auction('project_title', $res_invoice['projectid']);
			
			$project_id = $res_invoice['projectid'];
		}
		
		// invoice identifier
		$invoiceid = $id;

		// payment method pulldown
		$payment_method_pulldown = print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid']);
		
		// tax information
		$taxinfo = $res_invoice['taxinfo'];
		$taxamount = $ilance->currency->format($res_invoice['taxamount'], $res_invoice['currency_id']);
		
		$show['ispaid'] = $show['isunpaid'] = $show['isscheduled'] = $show['iscomplete'] = $show['iscancelled'] = 0;
		
		if ($res_invoice['status'] == 'paid')
		{
			$show['ispaid'] = 1;
		}
		if ($res_invoice['status'] == 'unpaid')
		{
			$show['isunpaid'] = 1;
		}
		if ($res_invoice['status'] == 'scheduled')
		{
			$show['isscheduled'] = 1;
		}
		if ($res_invoice['status'] == 'complete')
		{
			$show['iscomplete'] = 1;
		}
		if ($res_invoice['status'] == 'cancelled')
		{
			$show['iscancelled'] = 1;
		}			
		if ($res_invoice['invoicetype'] == 'subscription')
		{
			$show['subscriptionpayment'] = true;
		}
		else
		{
			$show['subscriptionpayment'] = false;
		}
		
		$pprint_array = array('project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('invoicepayment_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'invoicepayment.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = $phrase['_invoice_payment_menu_denied_payment'];
		$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
		
		print_notice($phrase['_invoice_error'], "There are no more pending invoices in your account", HTTPS_SERVER . mygc, $phrase['_my_cp']);
		exit();
	}
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
$query="select invoiceid from ".DB_PREFIX."invoices where user_id='".$_SESSION['ilancedata']['user']['userid']."' and combine_project!='' and status='paid'and Site_Id !='1'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)>0)
{
return true;
}else
{
return false;
}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>