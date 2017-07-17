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

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'ipn');

// #### require backend ########################################################
require_once('./../functions/config.php');

if (empty($ilance->GPC['do']))
{
	echo 'This script cannot be parsed indirectly.  Operation aborted.';
	exit();
}

//$ilance->email = construct_dm_object('email', $ilance);
$ilance->subscription = construct_object('api.subscription');
$ilance->accounting = construct_object('api.accounting');

// #### require shipping and payment tools backend #############################
require_once(DIR_CORE . 'functions_shipping.php');

($apihook = $ilance->api('payment_start')) ? eval($apihook) : false;

// #### PAYPAL RESPONSE HANLDER ################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_paypal')
{

	if ($ilconfig['paypal_active'] == false)
	{
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
		
	$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
	$ilance->paypal->error_email = SITE_EMAIL;
	$ilance->paypal->timeout = 120;
	$ilance->paypal->send_response();
	 $ilconfig['paypal_business_email'] = 'nataraj-facilitator@herakle.com';
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER AUTOMATION #############
	// #### ILANCE RESPONSE VERIFICATION ###################################
	if ((isset($ilance->GPC['business']) AND mb_strtolower(urldecode($ilance->GPC['business'])) == mb_strtolower($ilconfig['paypal_business_email']) OR isset($ilance->GPC['receiver_email']) AND mb_strtolower(urldecode($ilance->GPC['receiver_email'])) == mb_strtolower($ilconfig['paypal_business_email'])))
	{
		if (isset($ilance->GPC['custom']) AND !empty($ilance->GPC['custom']))
		{
			$custom = isset($ilance->GPC['custom']) ? urldecode($ilance->GPC['custom']) : '';
			$custom = explode('|', $custom);
			
			$ilance->GPC['paymentlogic'] 	= !empty($custom[0]) ? $custom[0] 	  : '';
			$ilance->GPC['orderid'] 	= !empty($custom[1]) ? intval($custom[1]) : 0;
			$ilance->GPC['projectid'] 	= !empty($custom[2]) ? intval($custom[2]) : 0;
			
			if ($ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
			{
				// #### update our buy now purchase as being paid in full
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET paiddate = '" . DATETIME24H . "',
					winnermarkedaspaid = '1',
					winnermarkedaspaiddate = '" . DATETIME24H . "',
					winnermarkedaspaidmethod = 'PayPal'
					WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
						AND project_id = '" . intval($ilance->GPC['projectid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				
				($apihook = $ilance->api('payment_paypal_buynow_win')) ? eval($apihook) : false;
			}
			else if ($ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['orderid'] > 0)
			{
				// #### update our listing as the buyer paying the seller in full
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_bids
					SET winnermarkedaspaid = '1',
					winnermarkedaspaiddate = '" . DATETIME24H . "',
					winnermarkedaspaidmethod = 'PayPal'
					WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
						AND bidstatus = 'awarded'
						AND state = 'product'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_realtimebids
					SET winnermarkedaspaid = '1',
					winnermarkedaspaiddate = '" . DATETIME24H . "',
					winnermarkedaspaidmethod = 'PayPal'
					WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
						AND bidstatus = 'awarded'
						AND state = 'product'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				
				mark_lub_listing_as_paid(intval($ilance->GPC['orderid']), 'PayPal');
				
				($apihook = $ilance->api('payment_paypal_item_win')) ? eval($apihook) : false;
			}
		}
	}
	
	// #### ILANCE RESPONSE VERIFICATION ###################################
	if ((isset($ilance->GPC['business']) AND mb_strtolower(urldecode($ilance->GPC['business'])) == mb_strtolower($ilconfig['paypal_business_email']) OR isset($ilance->GPC['receiver_email']) AND mb_strtolower(urldecode($ilance->GPC['receiver_email'])) == mb_strtolower($ilconfig['paypal_business_email'])))
	{
		// break down custom response
		$custom = isset($ilance->GPC['custom']) ? urldecode($ilance->GPC['custom']) : '';
		
		// decrypt our custom response originally sent to paypal regarding our transaction details
		if (isset($custom) AND !empty($custom))
		{
			$custom = explode('|', $custom);
		}
		else
		{
			echo 'This script requires well-formed parameters.  Operation aborted.';
			exit();
		}
		
		$ilance->GPC['paymentlogic'] 	= !empty($custom[0]) ? $custom[0] 	  : '';
		$ilance->GPC['userid'] 		= !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['invoiceid'] 	= !empty($custom[2]) ? intval($custom[2]) : 0;
		$ilance->GPC['creditamount'] 	= !empty($custom[3]) ? $custom[3] 	  : 0;
		$ilance->GPC['invoicetype'] 	= !empty($custom[3]) ? $custom[3] 	  : 0;
		$ilance->GPC['length'] 		= isset($custom[4])  ? intval($custom[4]) : 0;
		$ilance->GPC['units'] 		= isset($custom[5])  ? $custom[5] 	  : 0;
		$ilance->GPC['subscriptionid'] 	= isset($custom[6])  ? intval($custom[6]) : 0;
		$ilance->GPC['cost'] 		= isset($custom[7])  ? $custom[7]         : 0;
		$ilance->GPC['roleid'] 		= isset($custom[8])  ? intval($custom[8]) : '-1';
		//aug 30 sekar
		$ilance->GPC['paymethodnew'] 	= !empty($custom[9]) ? $custom[9] 	  : '';

		// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
		
		// #### PAYPAL RECURRING SUBSCRIPTION PAYMENT HANDLER ##########
		if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'RECURRINGSUBSCRIPTION' AND $ilconfig['paypal_subscriptions'])
		{
			($apihook = $ilance->api('payment_paypal_recurring')) ? eval($apihook) : false;
			
			// #### SUBSCRIPTION START OR SIGNUP ###################
			if ($ilance->paypal->get_transaction_type() == 'subscr_signup' OR ($ilance->paypal->is_verified() AND $ilance->paypal->get_transaction_type() == 'subscr_payment'))
			{
				// paypal tells us this is a subscription payment notification signup
				if ($ilance->GPC['userid'] > 0)
				{
					// #### COMPLETED SUBSCRIPTION PAYMENT
					// update new subscription
					
					$startdate = DATETIME24H;
					$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
					$recurring = 1;
					$paymethod = 'paypal';
					
					// create new invoice associated with this paypal subscription transaction
					$invoiceid = $ilance->accounting->insert_transaction(
						$ilance->GPC['subscriptionid'],
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->GPC['subscr_id'] . ']',
						sprintf("%01.2f", $ilance->GPC['cost']),
						sprintf("%01.2f", $ilance->GPC['cost']),
						'paid',
						'debit',
						'paypal',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->GPC['subscr_id'],
						0,
						0,
						1,
						'',
						0,
						0
					);
					
					// activate subscription plan
					activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
					
					// #### REFERRAL SYSTEM TRACKER ############################
					update_referral_action('subscription', $ilance->GPC['userid']);
					
					($apihook = $ilance->api('payment_paypal_recurring_is_verified')) ? eval($apihook) : false;
				}
			}
			
			// #### SUBSCRIPTION MODIFICATION ######################
			else if ($ilance->paypal->get_transaction_type() == 'subscr_modify')
			{
				($apihook = $ilance->api('payment_paypal_recurring_subscr_modify')) ? eval($apihook) : false;
				
				// update new subscription
				// because paypal's does not like to use NEW 'custom' we'll have to
				// find out plan details based on this ipn response.. :(
				unset($ilance->GPC['units']);
				unset($ilance->GPC['length']);
				unset($ilance->GPC['subscriptionid']);
				unset($ilance->GPC['roleid']);
				unset($ilance->GPC['cost']);

				$ilance->GPC['subscriptionid'] = $ilance->GPC['item_number'];
				
				$ilance->GPC['units'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "units");
				$ilance->GPC['length'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "length");
				$ilance->GPC['roleid'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "roleid");
				$ilance->GPC['cost'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "cost");
		
				$startdate = DATETIME24H;
				$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
				$recurring = 1;
				$paymethod = 'paypal';
				
				// create new invoice associated with this paypal subscription transaction
				$invoiceid = $ilance->accounting->insert_transaction(
					$ilance->GPC['subscriptionid'],
					0,
					0,
					intval($ilance->GPC['userid']),
					0,
					0,
					0,
					$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->GPC['subscr_id'] . ']',
					sprintf("%01.2f", $ilance->GPC['cost']),
					sprintf("%01.2f", $ilance->GPC['cost']),
					'paid',
					'debit',
					'paypal',
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					$ilance->GPC['subscr_id'],
					0,
					0,
					1,
					'',
					0,
					0
				);
				
				// #### activate subscription plan #############
				activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
			}
			
			// #### SUBSCRIPTION CANCELLATION OR END OF SUBSCRIPTION TERM
			else if ($ilance->paypal->get_transaction_type() == 'subscr_cancel' OR $ilance->paypal->get_transaction_type() == 'subscr_eot')
			{
				($apihook = $ilance->api('payment_paypal_recurring_subscr_cancel_eot')) ? eval($apihook) : false;
				
				// deactivate this users subscription plan
				deactivate_subscription_plan($ilance->GPC['userid']);
				
				// send email to admin and customer
				//$ilance->email = construct_dm_object('email', $ilance);

				//$ilance->email->mail = SITE_EMAIL;
				//$ilance->email->slng = fetch_site_slng();
				
				//$ilance->email->get('recurring_subscription_cancelled_admin');		
				//$ilance->email->set(array(
					// '{{username}}' => fetch_user('username', $ilance->GPC['userid']),
					// '{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
					// '{{gateway}}' => 'Paypal',
					// '{{txn_type}}' => $ilance->paypal->get_transaction_type(),
				// ));
				
				//$ilance->email->send();
				
				//$ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
				//$ilance->email->slng = fetch_user_slng($ilance->GPC['userid']);
				
				//$ilance->email->get('recurring_subscription_cancelled');		
				//$ilance->email->set(array(
					// '{{username}}' => fetch_user('username', $ilance->GPC['userid']),
					// '{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
					// '{{gateway}}' => 'Paypal',
					// '{{txn_type}}' => $ilance->paypal->get_transaction_type(),
				// ));
				
				//$ilance->email->send();
			}			
			// #### SUBSCRIPTION PAYMENT IS REVERSED OR REFUNDED ###
			else if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
			{
				($apihook = $ilance->api('payment_paypal_recurring_reversed_refunded')) ? eval($apihook) : false;
				
				// #### deactivate members subscription ########
				deactivate_subscription_plan($ilance->GPC['userid']);
			}
		}
		
		// #### PAYPAL SUBSCRIPTION PAYMENT (REGULAR) ##################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
		{
			($apihook = $ilance->api('payment_paypal_subscription')) ? eval($apihook) : false;
			
			// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
			if ($ilance->paypal->is_verified())
			{
				// The payment has been completed, and the funds have been added successfully to your account balance at Paypal.
				
				// this IPN will trigger when the member received email via cron
				// regarding unpaid invoice so they click the link in email
				// go to paypal and make payment .. the ipn handler is told to come here
				// and verify/update account to active
				
				if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
				{
					$sql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "invoices
						WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							AND user_id = '" . intval($ilance->GPC['userid']) . "'
							AND invoicetype = 'subscription'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$sql_invoice_array = $ilance->db->fetch_array($sql);
						
						$sql_user = $ilance->db->query("
							SELECT username, email
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_user) > 0)
						{
							$res_user = $ilance->db->fetch_array($sql_user);
							
							// update subscription invoice as paid in full
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET paid = '" . sprintf("%01.2f", $ilance->paypal->get_transaction_amount()) . "',
								status = 'paid',
								paymethod = 'paypal',
								paiddate = '" . DATETIME24H . "',
								referer = '" . $ilance->db->escape_string(REFERRER) . "',
								custommessage = '" . $ilance->db->escape_string($ilance->paypal->get_transaction_id()) . "'
								WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
									AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							
							// adjust members total amount paid for subscription plan
							insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->paypal->get_transaction_amount()), 'credit');
							
							// update customers subscription to active
							$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '" . intval($ilance->GPC['userid']) . "'", "subscriptionid");
							$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "units");
							$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "length");
							$subscription_length = $ilance->subscription->subscription_length($units, $length);
							$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
							
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "subscription_user
								SET active = 'yes',
								paymethod = 'paypal',
								startdate = '" . DATETIME24H . "',
								renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
								invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
								WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							
							// #### REFERRAL SYSTEM TRACKER ############################
							update_referral_action('subscription', intval($ilance->GPC['userid']));
							
							//$ilance->email = construct_dm_object('email', $ilance);
							
							//$ilance->email->mail = SITE_EMAIL;
							//$ilance->email->slng = fetch_site_slng();
							
							//$ilance->email->get('subscription_paid_via_paypal_admin');		
							//$ilance->email->set(array(
								// '{{provider}}' => ucfirst($res_user['username']),
								// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
								// '{{invoice_amount}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
								// '{{paymethod}}' => 'Paypal',
							// ));
							
							//$ilance->email->send();
							
							//$ilance->email->mail = $res_user['email'];
							//$ilance->email->slng = fetch_user_slng($res_user['user_id']);
							
							//$ilance->email->get('subscription_paid_via_paypal');		
							//$ilance->email->set(array(
								// '{{provider}}' => ucfirst($res_user['username']),
								// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
								// '{{invoice_amount}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
								// '{{paymethod}}' => 'Paypal',
							// ));
							
							//$ilance->email->send();
							
							($apihook = $ilance->api('payment_paypal_subscription_is_verified')) ? eval($apihook) : false;
						}
					}	
				}	
			}
			else if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
			{
				($apihook = $ilance->api('payment_paypal_subscription_reversed_refunded')) ? eval($apihook) : false;
				
				// #### deactivate members subscription ########
				deactivate_subscription_plan($ilance->GPC['userid']);
			}
		}
		
		// #### HANDLE DEPOSIT PAYMENTS ################################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
		{
			($apihook = $ilance->api('payment_paypal_deposit')) ? eval($apihook) : false;
			
			// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
			if ($ilance->paypal->is_verified())
			{
				// just in case paypal decides to ping the site twice or more with this payment
				// let's do a quick txn_id checkup so we don't double credit the member!
				// based on report: http://www.ilance.ca/forum/showthread.php?t=2429
				$validtransaction = 1;
				if (is_duplicate_txn_id($ilance->paypal->get_transaction_id()))
				{
					$validtransaction = 0;
				}
				
				if ($validtransaction)
				{
					// deposit amount variables ex: 150.00
					$deposit['echeck'] = 0;
					
					#### PAYPAL E-CHECK SUPPORT ########################
					// using echeck limits the total fees to 5.00 vs 2.9% of total amount
					if ($ilance->paypal->get_payment_type() == 'echeck')
					{
						$deposit['echeck'] = 1;
					}
					else if ($ilance->paypal->get_payment_type() == 'instant')
					{
						$deposit['echeck'] = 0;
					}
					
					// select amount for existing user
					$accountbal = $ilance->db->query("
						SELECT total_balance, available_balance
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($accountbal) > 0)
					{
						$sel_account_result = $ilance->db->fetch_array($accountbal);
						
						$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
						$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
						
						// construct new deposit transaction
						$ilance->accounting = construct_object('api.accounting');
						$deposit_invoice_id = $ilance->accounting->insert_transaction(
							0,
							0,
							0,
							intval($ilance->GPC['userid']),
							0,
							0,
							0,
							$phrase['_account_deposit_credit_via'] . ' Paypal [TXN_ID: ' . $ilance->paypal->get_transaction_id() . '] ' . $phrase['_into_online_account'] . ': ' . $ilance->currency->format($ilance->GPC['creditamount']),
							sprintf("%01.2f", $ilance->paypal->get_transaction_amount()),
							sprintf("%01.2f", $ilance->paypal->get_transaction_amount()),
							'paid',
							'credit',
							'paypal',
							DATETIME24H,
							DATEINVOICEDUE,
							DATETIME24H,
							$ilance->paypal->get_transaction_id(),
							0,
							0,
							1,
							'',
							1,
							0
						);
						
						// update the transaction with the acual amount we're crediting this user for
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoiceid = '" . intval($deposit_invoice_id) . "'
						", 0, null, __FILE__, __LINE__);
						    
						// update customers online account balance information
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
							total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						", 0, null, __FILE__, __LINE__);
						
						//$ilance->email = construct_dm_object('email', $ilance);
        
						//$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
						//$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
						
						//$ilance->email->get('member_deposit_funds_creditcard');		
						//$ilance->email->set(array(
							// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
							// '{{ip}}' => IPADDRESS,
							// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
							// '{{cost}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
							// '{{invoiceid}}' => $deposit_invoice_id,
							// '{{paymethod}}' => 'Paypal',
						// ));
						
						//$ilance->email->send();
						
						//$ilance->email->mail = SITE_EMAIL;
						//$ilance->email->slng = fetch_site_slng();
						
						//$ilance->email->get('member_deposit_funds_creditcard_admin');		
						//$ilance->email->set(array(
							// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
							// '{{ip}}' => IPADDRESS,
							// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
							// '{{cost}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
							// '{{invoiceid}}' => $deposit_invoice_id,
							// '{{paymethod}}' => 'Paypal',
						// ));
						
						//$ilance->email->send();
						
						($apihook = $ilance->api('payment_paypal_deposit_is_verified')) ? eval($apihook) : false;
					}		
				}
			}
			else if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
			{
				($apihook = $ilance->api('payment_paypal_deposit_reversed_refunded')) ? eval($apihook) : false;
				
				// changes -19.95 to 19.95
				$chargeback['amount'] = preg_replace("#^-#", "", $ilance->paypal->get_transaction_amount());
				
				// fetch the transaction in the database associated with the old txn_id being charged back
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "invoices
					WHERE custommessage = '" . $ilance->db->escape_string($ilance->GPC['parent_txn_id']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					
					// record debit and remove funds from users online account balances
					$accountbal = $ilance->db->query("
						SELECT total_balance, available_balance
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $res['user_id'] . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($accountbal) > 0)
					{
						$sel_account_result = $ilance->db->fetch_array($accountbal);
						
						$new_credit_total_balance = ($sel_account_result['total_balance'] - $chargeback['amount']);
						$new_credit_avail_balance = ($sel_account_result['available_balance'] - $chargeback['amount']);
						
						// construct new chargeback debit transaction
						$ilance->accounting = construct_object('api.accounting');
						$deposit_invoice_id = $ilance->accounting->insert_transaction(
							0,
							0,
							0,
							intval($res['user_id']),
							0,
							0,
							0,
							'Paypal [' . $ilance->paypal->get_payment_status() . '] Trigger Received [TXN_ID: ' . $ilance->paypal->get_transaction_id() . ']',
							sprintf("%01.2f", $chargeback['amount']),
							sprintf("%01.2f", $chargeback['amount']),
							'paid',
							'debit',
							'account',
							DATETIME24H,
							DATEINVOICEDUE,
							DATETIME24H,
							$ilance->paypal->get_transaction_id(),
							0,
							0,
							1,
							'',
							1,
							0
						);
						    
						// debit members account
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
							total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
							WHERE user_id = '" . intval($res['user_id']) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}	
			}
		}
		
		// #### HANDLE DIRECT PAYMENTS #################################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
		{
			($apihook = $ilance->api('payment_paypal_direct')) ? eval($apihook) : false;
			
			// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
			if ($ilance->paypal->is_verified())
			{
			
			//aug 30 sekar
				invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->paypal->get_transaction_amount(),0, $ilance->GPC['userid'], 'ipn', $ilance->GPC['paymethodnew'], $ilance->paypal->get_transaction_id(), true);
				
				($apihook = $ilance->api('payment_paypal_direct_is_verified')) ? eval($apihook) : false;
			}
			else if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
			{
				// unused
				($apihook = $ilance->api('payment_paypal_direct_reversed_refunded')) ? eval($apihook) : false;
			}
		}
	}

	if (empty($ilance->paypal->paypal_post_vars))
	{
		$ilance->paypal->paypal_post_vars = array();
	}
	@reset($ilance->paypal->paypal_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->paypal->paypal_post_vars))
	{
		// skip our do=_paypal query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	
	//$ilance->email->mail = SITE_EMAIL;
	//$ilance->email->slng = fetch_site_slng();
	//$ilance->email->get('paypal_external_payment_received_admin');		
	//$ilance->email->set(array(
		//'{{response}}' => $responsecodes,
	//));
	//$ilance->email->send();
	
	($apihook = $ilance->api('payment_paypal_end')) ? eval($apihook) : false;
}

// #### CASHU RESPONSE HANDLER #################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_cashu')
{
	if ($ilconfig['cashu_active'] == false)
	{
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	
	$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
	$ilance->cashu->error_email = SITE_EMAIL;
	
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER AUTOMATION #############
	if (isset($ilance->GPC['txt2']) AND !empty($ilance->GPC['txt2']))
	{
		$custom = isset($ilance->GPC['txt2']) ? urldecode($ilance->GPC['txt2']) : '';
		$custom = explode('|', $custom);
		
		$ilance->GPC['paymentlogic'] 	= !empty($custom[0]) ? $custom[0] 	  : '';
		$ilance->GPC['orderid'] 	= !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['projectid'] 	= !empty($custom[2]) ? intval($custom[2]) : 0;
		
		if ($ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our buy now purchase as being paid in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'CashU'
				WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
					AND project_id = '" . intval($ilance->GPC['projectid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_cashu_buynow_win')) ? eval($apihook) : false;
		}
		else if ($ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['orderid'] > 0)
		{
			// #### update our listing as the buyer paying the seller in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'CashU'
				WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'CashU'
				WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			mark_lub_listing_as_paid(intval($ilance->GPC['orderid']), 'CashU');
			
			($apihook = $ilance->api('payment_cashu_item_win')) ? eval($apihook) : false;
		}
	}
	
	// break down custom response
	$custom = isset($ilance->GPC['txt2']) ? urldecode($ilance->GPC['txt2']) : '';
	
	// decrypt our custom response originally sent to cashu regarding our transaction details
	if (isset($custom) AND !empty($custom))
	{
		$custom = explode('|', $custom);
	}
	else
	{
		echo 'This script requires well-formed parameters.  Operation aborted.';
		exit();
	}
	
	if ($ilconfig['cashu_active'] == false)
	{
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	
	$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
	$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
	$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
	$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
	$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : '';
	$ilance->GPC['length'] = isset($custom[4]) ? intval($custom[4]) : 0;
	$ilance->GPC['units'] = isset($custom[5]) ? $custom[5] : 0;
	$ilance->GPC['subscriptionid'] = isset($custom[6]) ? intval($custom[6]) : 0;
	$ilance->GPC['cost'] = isset($custom[7]) ? $custom[7] : 0;
	$ilance->GPC['roleid'] = isset($custom[8]) ? intval($custom[8]) : '-1';
	
	// #### CASHU SUBSCRIPTION PAYMENT (REGULAR) ###########################
	// note: cashU does not support recurring subscription payment
	if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
	{
		($apihook = $ilance->api('payment_cashu_subscription')) ? eval($apihook) : false;
		
		// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->cashu->is_verified())
		{
			// #### COMPLETED SUBSCRIPTION PAYMENT #################
			
			// this IPN will trigger when the member received email via cron
			// regarding unpaid invoice so they click the link in email
			// go to stormpay and make payment .. the ipn handler is told to come here
			// and verify/update account to active
			if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
			{
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "invoices
					WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						AND user_id = '" . intval($ilance->GPC['userid']) . "'
						AND invoicetype = 'subscription'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$sql_invoice_array = $ilance->db->fetch_array($sql);
					
					$sql_user = $ilance->db->query("
						SELECT username, email
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_user) > 0)
					{
						$res_user = $ilance->db->fetch_array($sql_user);
						// update subscription invoice as paid in full
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET paid = '" . sprintf("%01.2f", $ilance->cashu->get_transaction_amount()) . "',
							status = 'paid',
							paymethod = 'cashu',
							paiddate = '" . DATETIME24H . "',
							referer = '" . $ilance->db->escape_string(REFERRER) . "',
							custommessage = '" . $ilance->db->escape_string($ilance->cashu->get_transaction_id()) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						", 0, null, __FILE__, __LINE__);
						
						// adjust members total amount paid for subscription plan
						insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->cashu->get_transaction_amount()), 'credit');
						
						// update customers subscription to active
						$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '".intval($ilance->GPC['userid'])."'", "subscriptionid");
						$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '".$subscriptionid."'", "units");
						$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '".$subscriptionid."'", "length");
						$subscription_length = $ilance->subscription->subscription_length($units, $length);
						$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET active = 'yes',
							paymethod = 'cashu',
							startdate = '" . DATETIME24H . "',
							renewdate = '" . $subscription_renew_date . "',
							invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						
						// #### REFERRAL SYSTEM TRACKER ############################
						update_referral_action('subscription', intval($ilance->GPC['userid']));
						
						//$ilance->email->mail = SITE_EMAIL;
						//$ilance->email->slng = fetch_site_slng();
						//$ilance->email->get('subscription_paid_via_paypal_admin');		
						//$ilance->email->set(array(
							// '{{provider}}' => ucfirst($res_user['username']),
							// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
							// '{{invoice_amount}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
							// '{{paymethod}}' => 'CashU',
						// ));
						//$ilance->email->send();
						
						//$ilance->email->mail = $res_user['email'];
						//$ilance->email->slng = fetch_user_slng($res_user['user_id']);
						//$ilance->email->get('subscription_paid_via_paypal');		
						//$ilance->email->set(array(
							// '{{provider}}' => ucfirst($res_user['username']),
							// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
							// '{{invoice_amount}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
							// '{{paymethod}}' => 'CashU',
						// ));
						//$ilance->email->send();
						
						($apihook = $ilance->api('payment_cashu_subscription_is_verified')) ? eval($apihook) : false;
					}
				}	
			}
		}
	}
	
	// #### HANDLE DEPOSIT PAYMENTS ################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
	{
		($apihook = $ilance->api('payment_cashu_deposit')) ? eval($apihook) : false;
		
		// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->cashu->is_verified())
		{
			// just in case cashu decides to ping the site twice or more with this payment
			// let's do a quick txn_id checkup so we don't double credit the member!
			// based on report: http://www.ilance.ca/forum/showthread.php?t=2429
			$validtransaction = 1;
			if (is_duplicate_txn_id($ilance->cashu->get_transaction_id()))
			{
				$validtransaction = 0;
			}
			
			if ($validtransaction)
			{
				// select amount for existing user
				$accountbal = $ilance->db->query("
					SELECT total_balance, available_balance
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($accountbal) > 0)
				{
					$sel_account_result = $ilance->db->fetch_array($accountbal);
					
					$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
					$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
					
					// construct new deposit transaction
					$ilance->accounting = construct_object('api.accounting');
					$deposit_invoice_id = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$phrase['_account_deposit_credit_via'] . ' CashU [TXN_ID: ' . $ilance->cashu->get_transaction_id() . '] ' . $phrase['_into_online_account'] . ': ' . $ilance->currency->format($ilance->GPC['creditamount']),
						$ilance->cashu->get_transaction_amount(),
						$ilance->cashu->get_transaction_amount(),
						'paid',
						'credit',
						'cashu',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->cashu->get_transaction_id(),
						0,
						0,
						1,
						'',
						1,
						0
					);
					
					// update the transaction with the acual amount we're crediting this user for
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							AND invoiceid = '" . intval($deposit_invoice_id) . "'
					", 0, null, __FILE__, __LINE__);
					    
					// update customers online account balance information
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
						total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
					", 0, null, __FILE__, __LINE__);
					
					//$ilance->email = construct_dm_object('email', $ilance);
        
					//$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
					//$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
					
					//$ilance->email->get('member_deposit_funds_creditcard');		
					//$ilance->email->set(array(
						// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						// '{{ip}}' => IPADDRESS,
						// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						// '{{cost}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
						// '{{invoiceid}}' => $deposit_invoice_id,
						// '{{paymethod}}' => 'CashU',
					// ));
					
					//$ilance->email->send();
					
					//$ilance->email->mail = SITE_EMAIL;
					//$ilance->email->slng = fetch_site_slng();
					
					//$ilance->email->get('member_deposit_funds_creditcard_admin');		
					//$ilance->email->set(array(
						// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						// '{{ip}}' => IPADDRESS,
						// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						// '{{cost}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
						// '{{invoiceid}}' => $deposit_invoice_id,
						// '{{paymethod}}' => 'CashU',
					// ));
					
					//$ilance->email->send();
					
					($apihook = $ilance->api('payment_cashu_deposit_isverified')) ? eval($apihook) : false;
				}		
			}
		}
	}
	
	// #### HANDLE DIRECT PAYMENTS #########################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
	{
		($apihook = $ilance->api('payment_cashu_direct')) ? eval($apihook) : false;
		
		// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
		if ($ilance->cashu->is_verified())
		{
			invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->cashu->get_transaction_amount(), $ilance->GPC['userid'], 'ipn', 'cashu', $ilance->cashu->get_transaction_id(), true);
			
			($apihook = $ilance->api('payment_cashu_direct_is_verified')) ? eval($apihook) : false;
		}
	}
	
	if (empty($ilance->cashu->cashu_post_vars))
	{
		$ilance->cashu->cashu_post_vars = array();
	}
	@reset($ilance->cashu->cashu_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->cashu->cashu_post_vars))
	{
		// skip our do=_cashu query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	
	//$ilance->email->mail = SITE_EMAIL;
	//$ilance->email->slng = fetch_site_slng();
	//$ilance->email->get('paypal_external_payment_received_admin');		
	//$ilance->email->set(array(
		// '{{response}}' => $responsecodes,
	// ));
	//$ilance->email->send();
	
	($apihook = $ilance->api('payment_cashu_end')) ? eval($apihook) : false;
}

// #### STORMPAY RESPONSE HANDLER ##############################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_stormpay')
{
	if ($ilconfig['stormpay_active'] == false)
	{
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	
	$ilance->stormpay = construct_object('api.stormpay', $ilance->GPC);
	$ilance->stormpay->error_email = SITE_EMAIL;
	
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER AUTOMATION #############
	if (isset($ilance->GPC['user1']) AND !empty($ilance->GPC['user1']))
	{
		$custom = isset($ilance->GPC['user1']) ? urldecode($ilance->GPC['user1']) : '';
		$custom = explode('|', $custom);
		
		$ilance->GPC['paymentlogic'] 	= !empty($custom[0]) ? $custom[0] 	  : '';
		$ilance->GPC['orderid'] 	= !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['projectid'] 	= !empty($custom[2]) ? intval($custom[2]) : 0;
		
		if ($ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our buy now purchase as being paid in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'StormPay'
				WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
					AND project_id = '" . intval($ilance->GPC['projectid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_stormpay_buynow_win')) ? eval($apihook) : false;
		}
		else if ($ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our listing as the buyer paying the seller in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'StormPay'
				WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'StormPay'
				WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			mark_lub_listing_as_paid(intval($ilance->GPC['orderid']), 'StormPay');
			
			($apihook = $ilance->api('payment_stormpay_item_win')) ? eval($apihook) : false;
		}
	}
	
	// break down custom response
	$custom = isset($ilance->GPC['user1']) ? urldecode($ilance->GPC['user1']) : '';
	
	// decrypt our custom response originally sent to paypal regarding our transaction details
	if (isset($custom) AND !empty($custom))
	{
		$custom = explode('|', $custom);
	}
	else
	{
		echo 'This script requires well-formed parameters.  Operation aborted.';
		exit();
	}
	
	// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
	
	$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
	$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
	$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
	$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
	$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : '';
	$ilance->GPC['length'] = isset($custom[4]) ? intval($custom[4]) : 0;
	$ilance->GPC['units'] = isset($custom[5]) ? $custom[5] : 0;
	$ilance->GPC['subscriptionid'] = isset($custom[6]) ? intval($custom[6]) : 0;
	$ilance->GPC['cost'] = isset($custom[7]) ? $custom[7] : 0;
	$ilance->GPC['roleid'] = isset($custom[8]) ? intval($custom[8]) : '-1';
	
	// #### STORMPAY RECURRING SUBSCRIPTION PAYMENT HANDLER ##########
	if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'RECURRINGSUBSCRIPTION' AND $ilconfig['stormpay_subscriptions'])
	{
		($apihook = $ilance->api('payment_stormpay_recurring')) ? eval($apihook) : false;
		
		// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
		if ($ilance->stormpay->is_verified())
		{
			// stormpay tells us this is a subscription payment notification
			if ($ilance->GPC['userid'] > 0)
			{
				// #### COMPLETED SUBSCRIPTION PAYMENT
				// update new subscription
				
				$startdate = DATETIME24H;
				$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
				$recurring = 1;
				$paymethod = 'stormpay';
				
				// create new invoice associated with this paypal subscription transaction
				$invoiceid = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					intval($ilance->GPC['userid']),
					0,
					0,
					0,
					$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->stormpay->get_transaction_id() . ']',
					sprintf("%01.2f", $ilance->GPC['cost']),
					sprintf("%01.2f", $ilance->GPC['cost']),
					'paid',
					'debit',
					'stormpay',
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					$ilance->stormpay->get_transaction_id(),
					0,
					0,
					1,
					'',
					0,
					0
				);
				
				// activate subscription plan
				activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
				
				// #### REFERRAL SYSTEM TRACKER ############################
				update_referral_action('subscription', $ilance->GPC['userid']);
				
				($apihook = $ilance->api('payment_stormpay_recurring_is_verified')) ? eval($apihook) : false;
			}
		}
		else if ($ilance->stormpay->get_payment_status() == 'CANCEL' OR $ilance->stormpay->get_payment_status() == 'CHARGEBACK' OR $ilance->stormpay->get_payment_status() == 'REFUND')
		{
			// #### deactivate members subscription ################
			deactivate_subscription_plan($ilance->GPC['userid']);
			
			// #### send email #####################################
			//$ilance->email = construct_dm_object('email', $ilance);

			//$ilance->email->mail = SITE_EMAIL;
			//$ilance->email->slng = fetch_site_slng();
			
			//$ilance->email->get('recurring_subscription_cancelled_admin');		
			//$ilance->email->set(array(
				// '{{username}}' => fetch_user('username', $ilance->GPC['userid']),
				// '{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
				// '{{gateway}}' => 'StormPay',
				// '{{txn_type}}' => $ilance->stormpay->get_payment_status(),
			// ));
			
			//$ilance->email->send();
			
			//$ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
			//$ilance->email->slng = fetch_user_slng($ilance->GPC['userid']);
			
			//$ilance->email->get('recurring_subscription_cancelled');		
			//$ilance->email->set(array(
				// '{{username}}' => fetch_user('username', $ilance->GPC['userid']),
				// '{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
				// '{{gateway}}' => 'StormPay',
				// '{{txn_type}}' => $ilance->stormpay->get_payment_status(),
			// ));
			
			//$ilance->email->send();
		}
	}
	
	// #### STORMPAY SUBSCRIPTION PAYMENT (REGULAR) ##################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
	{
		($apihook = $ilance->api('payment_stormpay_subscription')) ? eval($apihook) : false;
		
		// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->stormpay->is_verified())
		{
			#### COMPLETED SUBSCRIPTION PAYMENT ##############
			// this IPN will trigger when the member received email via cron
			// regarding unpaid invoice so they click the link in email
			// go to stormpay and make payment .. the ipn handler is told to come here
			// and verify/update account to active
			if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
			{
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "invoices
					WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						AND user_id = '" . intval($ilance->GPC['userid']) . "'
						AND invoicetype = 'subscription'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$sql_invoice_array = $ilance->db->fetch_array($sql);
					
					$sql_user = $ilance->db->query("
						SELECT username, email
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_user) > 0)
					{
						$res_user = $ilance->db->fetch_array($sql_user);
						
						// update subscription invoice as paid in full
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET paid = '" . sprintf("%01.2f", $ilance->stormpay->get_transaction_amount()) . "',
							status = 'paid',
							paymethod = 'stormpay',
							paiddate = '" . DATETIME24H . "',
							referer = '" . $ilance->db->escape_string(REFERRER) . "',
							custommessage = '" . $ilance->db->escape_string($ilance->stormpay->get_transaction_id()) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						
						// adjust members total amount paid for subscription plan
						insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->stormpay->get_transaction_amount()), 'credit');
						
						// update customers subscription to active
						$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '" . intval($ilance->GPC['userid']) . "'", "subscriptionid");
						$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "units");
						$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "length");
						$subscription_length = $ilance->subscription->subscription_length($units, $length);
						$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET active = 'yes',
							paymethod = 'stormpay',
							startdate = '" . DATETIME24H . "',
							renewdate = '" . $subscription_renew_date . "',
							invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						
						// #### REFERRAL SYSTEM TRACKER ############################
						update_referral_action('subscription', intval($ilance->GPC['userid']));
						
						//$ilance->email = construct_dm_object('email', $ilance);
                
						//$ilance->email->mail = SITE_EMAIL;
						//$ilance->email->slng = fetch_site_slng();
						
						//$ilance->email->get('subscription_paid_via_paypal_admin');		
						//$ilance->email->set(array(
							// '{{provider}}' => ucfirst($res_user['username']),
							// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
							// '{{invoice_amount}}' => $ilance->currency->format($ilance->stormpay->get_transaction_amount()),
							// '{{paymethod}}' => 'Stormpay',
						// ));
						
						//$ilance->email->send();
						
						//$ilance->email->mail = $res_user['email'];
						//$ilance->email->slng = fetch_user_slng($res_user['user_id']);
						
						//$ilance->email->get('subscription_paid_via_paypal');		
						//$ilance->email->set(array(
							// '{{provider}}' => ucfirst($res_user['username']),
							// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
							// '{{invoice_amount}}' => $ilance->currency->format($ilance->stormpay->get_transaction_amount()),
							// '{{paymethod}}' => 'Stormpay',
						// ));
						
						//$ilance->email->send();
						
						($apihook = $ilance->api('payment_stormpay_subscription_is_verified')) ? eval($apihook) : false;
					}
				}
			}
		}
		else if ($ilance->stormpay->get_payment_status() == 'CHARGEBACK' OR $ilance->stormpay->get_payment_status() == 'REFUND')
		{
			($apihook = $ilance->api('payment_stormpay_subscription_chargeback_refund')) ? eval($apihook) : false;
			
			// #### deactivate members subscription ########
			deactivate_subscription_plan($ilance->GPC['userid']);
		}
	}
	
	// #### HANDLE DEPOSIT PAYMENTS ################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
	{
		($apihook = $ilance->api('payment_stormpay_deposit')) ? eval($apihook) : false;
		
		// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->stormpay->is_verified())
		{
			// just in case cashu decides to ping the site twice or more with this payment
			// let's do a quick txn_id checkup so we don't double credit the member!
			// based on report: http://www.ilance.ca/forum/showthread.php?t=2429
			$validtransaction = 1;
			if (is_duplicate_txn_id($ilance->stormpay->get_transaction_id()))
			{
				$validtransaction = 0;
			}
			
			if ($validtransaction)
			{
				// select amount for existing user
				$accountbal = $ilance->db->query("
					SELECT total_balance, available_balance
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($accountbal) > 0)
				{
					$sel_account_result = $ilance->db->fetch_array($accountbal);
					
					$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
					$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
					
					// construct new deposit transaction
					$ilance->accounting = construct_object('api.accounting');
					$deposit_invoice_id = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$phrase['_account_deposit_credit_via'] . ' StormPay [TXN_ID: ' . $ilance->stormpay->get_transaction_id() . '] ' . $phrase['_into_online_account'] . ': ' . $ilance->currency->format($ilance->GPC['creditamount']),
						$ilance->stormpay->get_transaction_amount(),
						$ilance->stormpay->get_transaction_amount(),
						'paid',
						'credit',
						'stormpay',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->stormpay->get_transaction_id(),
						0,
						0,
						1,
						'',
						1,
						0
					);
					    
					// update the transaction with the acual amount we're crediting this user for
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							AND invoiceid = '" . intval($deposit_invoice_id) . "'
					", 0, null, __FILE__, __LINE__);
					
					// update customers online account balance information
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
						total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
					", 0, null, __FILE__, __LINE__);
					
					//$ilance->email = construct_dm_object('email', $ilance);
        
					//$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
					//$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
					
					//$ilance->email->get('member_deposit_funds_creditcard');		
					//$ilance->email->set(array(
						// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						// '{{ip}}' => IPADDRESS,
						// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						// '{{cost}}' => $ilance->currency->format($ilance->stormpay->get_transaction_amount()),
						// '{{invoiceid}}' => $deposit_invoice_id,
						// '{{paymethod}}' => 'StormPay',
					// ));
					
					//$ilance->email->send();
					
					//$ilance->email->mail = SITE_EMAIL;
					//$ilance->email->slng = fetch_site_slng();
					
					//$ilance->email->get('member_deposit_funds_creditcard_admin');		
					//$ilance->email->set(array(
						// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						// '{{ip}}' => IPADDRESS,
						// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						// '{{cost}}' => $ilance->currency->format($ilance->stormpay->get_transaction_amount()),
						// '{{invoiceid}}' => $deposit_invoice_id,
						// '{{paymethod}}' => 'StormPay',
					// ));
					
					//$ilance->email->send();
					
					($apihook = $ilance->api('payment_stormpay_deposit_is_verified')) ? eval($apihook) : false;
				}
			}
		}
		else if ($ilance->stormpay->get_payment_status() == 'CHARGEBACK' OR $ilance->stormpay->get_payment_status() == 'REFUND')
		{
			// unused
			($apihook = $ilance->api('payment_stormpay_deposit_chargeback_refund')) ? eval($apihook) : false;
		}
	}
	
	// #### HANDLE DIRECT PAYMENTS #########################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
	{
		($apihook = $ilance->api('payment_stormpay_direct')) ? eval($apihook) : false;
		
		// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
		if ($ilance->stormpay->is_verified())
		{
			invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->stormpay->get_transaction_amount(), $ilance->GPC['userid'], 'ipn', 'stormpay', $ilance->stormpay->get_transaction_id(), true);
			
			($apihook = $ilance->api('payment_stormpay_direct_is_verified')) ? eval($apihook) : false;
		}
		else if ($ilance->stormpay->get_payment_status() == 'CHARGEBACK' OR $ilance->stormpay->get_payment_status() == 'REFUND')
		{
			// unused
			($apihook = $ilance->api('payment_stormpay_direct_chargeback_refund')) ? eval($apihook) : false;
		}
	}
	
	if (empty($ilance->stormpay->stormpay_post_vars))
	{
		$ilance->stormpay->stormpay_post_vars = array();
	}
	@reset($ilance->stormpay->stormpay_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->stormpay->stormpay_post_vars))
	{
		// skip our do=_cashu query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	
	//$ilance->email->mail = SITE_EMAIL;
	//$ilance->email->slng = fetch_site_slng();
	//$ilance->email->get('paypal_external_payment_received_admin');		
	//$ilance->email->set(array(
		// '{{response}}' => $responsecodes,
	// ));
	//$ilance->email->send();
	
	($apihook = $ilance->api('payment_stormpay_end')) ? eval($apihook) : false;
}

// #### MONEYBOOKERS RESPONSE HANDLER ##########################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_moneybookers')
{
	if ($ilconfig['moneybookers_active'] == false)
	{
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	
	$ilance->moneybookers = construct_object('api.moneybookers', $ilance->GPC);
	$ilance->moneybookers->error_email = SITE_EMAIL;
	
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER AUTOMATION #############
	if (isset($ilance->GPC['merchant_fields']) AND !empty($ilance->GPC['merchant_fields']))
	{
		$custom = isset($ilance->GPC['merchant_fields']) ? urldecode($ilance->GPC['merchant_fields']) : '';
		$custom = explode('|', $custom);
		
		$ilance->GPC['paymentlogic'] 	= !empty($custom[0]) ? $custom[0] 	  : '';
		$ilance->GPC['orderid'] 	= !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['projectid'] 	= !empty($custom[2]) ? intval($custom[2]) : 0;
		
		if ($ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our buy now purchase as being paid in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'MoneyBookers'
				WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
					AND project_id = '" . intval($ilance->GPC['projectid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_moneybookers_buynow_win')) ? eval($apihook) : false;
		}
		else if ($ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['orderid'] > 0)
		{
			// #### update our listing as the buyer paying the seller in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'MoneyBookers'
				WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'MoneyBookers'
				WHERE project_id = '" . intval($ilance->GPC['orderid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			mark_lub_listing_as_paid(intval($ilance->GPC['orderid']), 'MoneyBookers');
			
			($apihook = $ilance->api('payment_moneybookers_item_win')) ? eval($apihook) : false;
		}
	}
	
	// break down custom response
	$custom = isset($ilance->GPC['merchant_fields']) ? urldecode($ilance->GPC['merchant_fields']) : '';
	
	// decrypt our custom response originally sent to paypal regarding our transaction details
	if (isset($custom) AND !empty($custom))
	{
		$custom = explode('|', $custom);
	}
	else
	{
		echo 'This script requires well-formed parameters.  Operation aborted.';
		exit();
	}
	
	// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
	
	$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
	$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
	$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
	$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
	$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : '';
	$ilance->GPC['length'] = isset($custom[4]) ? intval($custom[4]) : 0;
	$ilance->GPC['units'] = isset($custom[5]) ? $custom[5] : 0;
	$ilance->GPC['subscriptionid'] = isset($custom[6]) ? intval($custom[6]) : 0;
	$ilance->GPC['cost'] = isset($custom[7]) ? $custom[7] : 0;
	$ilance->GPC['roleid'] = isset($custom[8]) ? intval($custom[8]) : '-1';
	
	// #### STORMPAY RECURRING SUBSCRIPTION PAYMENT HANDLER ##########
	if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'RECURRINGSUBSCRIPTION' AND $ilconfig['moneybookers_subscriptions'])
	{
		($apihook = $ilance->api('payment_moneybookers_recurring')) ? eval($apihook) : false;
		
		// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
		
		if ($ilance->moneybookers->get_recurring_transaction_type() == 'recurring')
		{
			if ($ilance->moneybookers->is_verified())
			{
				// moneybookers tells us this is a subscription payment notification
				if ($ilance->GPC['userid'] > 0)
				{
					// #### COMPLETED SUBSCRIPTION PAYMENT
					// update new subscription
					
					$startdate = DATETIME24H;
					$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
					$recurring = 1;
					$paymethod = 'moneybookers';
					
					// create new invoice associated with this paypal subscription transaction
					$invoiceid = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->moneybookers->get_transaction_id() . ']',
						sprintf("%01.2f", $ilance->GPC['cost']),
						sprintf("%01.2f", $ilance->GPC['cost']),
						'paid',
						'debit',
						'moneybookers',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->moneybookers->get_transaction_id(),
						0,
						0,
						1,
						'',
						0,
						0
					);
					
					// activate subscription plan
					activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
					
					// #### REFERRAL SYSTEM TRACKER ############################
					update_referral_action('subscription', $ilance->GPC['userid']);
					
					($apihook = $ilance->api('payment_moneybookers_recurring_is_verified')) ? eval($apihook) : false;
				}
			}
			else if ($ilance->moneybookers->get_payment_status() == 'CANCEL' OR $ilance->moneybookers->get_payment_status() == 'CHARGEBACK' OR $ilance->moneybookers->get_payment_status() == 'REFUND')
			{
				($apihook = $ilance->api('payment_moneybookers_recurring_cancel_chargeback_refund')) ? eval($apihook) : false;
				
				// #### deactivate members subscription ################
				deactivate_subscription_plan($ilance->GPC['userid']);
				
				// #### send email #####################################
				//$ilance->email = construct_dm_object('email', $ilance);
	
				//$ilance->email->mail = SITE_EMAIL;
				//$ilance->email->slng = fetch_site_slng();
				
				//$ilance->email->get('recurring_subscription_cancelled_admin');		
				//$ilance->email->set(array(
					// '{{username}}' => fetch_user('username', $ilance->GPC['userid']),
					// '{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
					// '{{gateway}}' => 'MoneyBookers',
					// '{{txn_type}}' => $ilance->moneybookers->get_payment_status(),
				// ));
				
				//$ilance->email->send();
				
				//$ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
				//$ilance->email->slng = fetch_user_slng($ilance->GPC['userid']);
				
				//$ilance->email->get('recurring_subscription_cancelled');		
				//$ilance->email->set(array(
					// '{{username}}' => fetch_user('username', $ilance->GPC['userid']),
					// '{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
					// '{{gateway}}' => 'MoneyBookers',
					// '{{txn_type}}' => $ilance->moneybookers->get_payment_status(),
				// ));
				
				//$ilance->email->send();
			}
		}
	}
	
	// #### MONEYBOOKERS SUBSCRIPTION PAYMENT (REGULAR) ####################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
	{
		($apihook = $ilance->api('payment_moneybookers_subscription')) ? eval($apihook) : false;
		
		// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->moneybookers->is_verified())
		{
			#### COMPLETED SUBSCRIPTION PAYMENT ##############
			
			// this IPN will trigger when the member received email via cron
			// regarding unpaid invoice so they click the link in email
			// go to stormpay and make payment .. the ipn handler is told to come here
			// and verify/update account to active
			
			if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
			{
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "invoices
					WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						AND user_id = '" . intval($ilance->GPC['userid']) . "'
						AND invoicetype = 'subscription'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$sql_invoice_array = $ilance->db->fetch_array($sql);
					
					$sql_user = $ilance->db->query("
						SELECT username, email
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_user) > 0)
					{
						$res_user = $ilance->db->fetch_array($sql_user);
						
						// update subscription invoice as paid in full
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET paid = '" . sprintf("%01.2f", $ilance->moneybookers->get_transaction_amount()) . "',
							status = 'paid',
							paymethod = 'moneybookers',
							paiddate = '" . DATETIME24H . "',
							referer = '" . $ilance->db->escape_string(REFERRER) . "',
							custommessage = '" . $ilance->db->escape_string($ilance->moneybookers->get_transaction_id()) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						
						// adjust members total amount paid for subscription plan
						insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->moneybookers->get_transaction_amount()), 'credit');
						
						// update customers subscription to active
						$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '" . intval($ilance->GPC['userid']) . "'", "subscriptionid");
						$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "units");
						$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "length");
						$subscription_length = $ilance->subscription->subscription_length($units, $length);
						$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET active = 'yes',
							paymethod = 'moneybookers',
							startdate = '" . DATETIME24H . "',
							renewdate = '" . $subscription_renew_date . "',
							invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						
						// #### REFERRAL SYSTEM TRACKER ############################
						update_referral_action('subscription', intval($ilance->GPC['userid']));
						
						//$ilance->email = construct_dm_object('email', $ilance);
                
						//$ilance->email->mail = SITE_EMAIL;
						//$ilance->email->slng = fetch_site_slng();
						
						//$ilance->email->get('subscription_paid_via_paypal_admin');		
						//$ilance->email->set(array(
							// '{{provider}}' => ucfirst($res_user['username']),
							// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
							// '{{invoice_amount}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
							// '{{paymethod}}' => 'MoneyBookers',
						// ));
						
						//$ilance->email->send();
						
						//$ilance->email->mail = $res_user['email'];
						//$ilance->email->slng = fetch_user_slng($res_user['user_id']);
						
						//$ilance->email->get('subscription_paid_via_paypal');		
						//$ilance->email->set(array(
							// '{{provider}}' => ucfirst($res_user['username']),
							// '{{invoice_id}}' => $ilance->GPC['invoiceid'],
							// '{{invoice_amount}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
							// '{{paymethod}}' => 'MoneyBookers',
						// ));
						
						//$ilance->email->send();
						
						($apihook = $ilance->api('payment_moneybookers_subscription_is_verified')) ? eval($apihook) : false;
					}
				}
			}
		}
		else if ($ilance->moneybookers->get_payment_status() == 'CHARGEBACK')
		{
			($apihook = $ilance->api('payment_moneybookers_subscription_chargeback')) ? eval($apihook) : false;
			
			// #### deactivate members subscription ########
			deactivate_subscription_plan($ilance->GPC['userid']);
		}
	}
	
	// #### HANDLE DEPOSIT PAYMENTS ################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
	{
		($apihook = $ilance->api('payment_moneybookers_deposit')) ? eval($apihook) : false;
		
		// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->moneybookers->is_verified())
		{
			// just in case moneybookers decides to ping the site twice or more with this payment
			// let's do a quick txn_id checkup so we don't double credit the member!
			// based on report: http://www.ilance.ca/forum/showthread.php?t=2429
			$validtransaction = 1;
			if (is_duplicate_txn_id($ilance->moneybookers->get_transaction_id()))
			{
				$validtransaction = 0;
			}
			
			if ($validtransaction)
			{
				// select amount for existing user
				$accountbal = $ilance->db->query("
					SELECT total_balance, available_balance
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($accountbal) > 0)
				{
					$sel_account_result = $ilance->db->fetch_array($accountbal);
					
					$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
					$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
					
					// construct new deposit transaction
					$ilance->accounting = construct_object('api.accounting');
					$deposit_invoice_id = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$phrase['_account_deposit_credit_via'] . ' MoneyBookers [TXN_ID: ' . $ilance->moneybookers->get_transaction_id() . '] ' . $phrase['_into_online_account'] . ': ' . $ilance->currency->format($ilance->GPC['creditamount']),
						$ilance->moneybookers->get_transaction_amount(),
						$ilance->moneybookers->get_transaction_amount(),
						'paid',
						'credit',
						'moneybookers',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->moneybookers->get_transaction_id(),
						0,
						0,
						1,
						'',
						1,
						0
					);
					    
					// update the transaction with the acual amount we're crediting this user for
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							AND invoiceid = '" . intval($deposit_invoice_id) . "'
					", 0, null, __FILE__, __LINE__);
					
					// update customers online account balance information
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
						total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
					", 0, null, __FILE__, __LINE__);
					
					//$ilance->email = construct_dm_object('email', $ilance);
        
					//$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
					//$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
					
					//$ilance->email->get('member_deposit_funds_creditcard');		
					//$ilance->email->set(array(
						// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						// '{{ip}}' => IPADDRESS,
						// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						// '{{cost}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
						// '{{invoiceid}}' => $deposit_invoice_id,
						// '{{paymethod}}' => 'MoneyBookers',
					// ));
					
					//$ilance->email->send();
					
					//$ilance->email->mail = SITE_EMAIL;
					//$ilance->email->slng = fetch_site_slng();
					
					//$ilance->email->get('member_deposit_funds_creditcard_admin');		
					//$ilance->email->set(array(
						// '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						// '{{ip}}' => IPADDRESS,
						// '{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						// '{{cost}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
						// '{{invoiceid}}' => $deposit_invoice_id,
						// '{{paymethod}}' => 'MoneyBookers',
					// ));
					
					//$ilance->email->send();
					
					($apihook = $ilance->api('payment_moneybookers_deposit_is_verified')) ? eval($apihook) : false;
				}
			}
		}
		else if ($ilance->moneybookers->get_payment_status() == 'CHARGEBACK')
		{
			// unused
			($apihook = $ilance->api('payment_moneybookers_deposit_chargeback')) ? eval($apihook) : false;
		}
	}
	
	// #### HANDLE DIRECT PAYMENTS #########################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
	{
		($apihook = $ilance->api('payment_moneybookers_direct')) ? eval($apihook) : false;
		
		// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
		if ($ilance->moneybookers->is_verified())
		{
			invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->moneybookers->get_transaction_amount(), $ilance->GPC['userid'], 'ipn', 'moneybookers', $ilance->moneybookers->get_transaction_id(), true);
			
			($apihook = $ilance->api('payment_moneybookers_direct_is_verified')) ? eval($apihook) : false;
		}
		else if ($ilance->moneybookers->get_payment_status() == 'CHARGEBACK')
		{
			// unused
			($apihook = $ilance->api('payment_moneybookers_direct_chargeback')) ? eval($apihook) : false;
		}
	}
	
	if (empty($ilance->moneybookers->moneybookers_post_vars))
	{
		$ilance->moneybookers->moneybookers_post_vars = array();
	}
	@reset($ilance->moneybookers->moneybookers_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->moneybookers->moneybookers_post_vars))
	{
		// skip our do=_cashu query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	
	//$ilance->email->mail = SITE_EMAIL;
	//$ilance->email->slng = fetch_site_slng();
	//$ilance->email->get('paypal_external_payment_received_admin');		
	//$ilance->email->set(array(
		// '{{response}}' => $responsecodes,
	// ));
	//$ilance->email->send();
	
	($apihook = $ilance->api('payment_moneybookers_end')) ? eval($apihook) : false;
}

// #### AUTHORIZE.NET ABR RESPONSE HANDLER #####################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_authorizenet')
{
	if ($ilconfig['authnet_enabled'] == false)
	{
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	
	$ilance->authorizenet = construct_object('api.authorizenet', $ilance->GPC);
	$ilance->authorizenet->error_email = SITE_EMAIL;
	$ilance->authorizenet->timeout = 120;
	
	$custom = isset($ilance->GPC['refId']) ? urldecode($ilance->GPC['refId']) : '';
	
	$ilance->GPC['subscriptionid'] = $ilance->GPC['subscriptionid'];
	$ilance->GPC['roleid'] = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '-1';
	$ilance->GPC['amount'] = $ilance->GPC['amount'];
	$ilance->GPC['refId'] = $ilance->GPC['refId'];
	$ilance->GPC['name'] = $ilance->GPC['name'];
	$ilance->GPC['length'] = $ilance->GPC['length'];
	$ilance->GPC['unit'] = $ilance->GPC['unit'];
	$ilance->GPC['units'] = $ilance->GPC['units'];
	$ilance->GPC['startDate'] = $ilance->GPC['startDate'];
	$ilance->GPC['totalOccurrences'] = $ilance->GPC['totalOccurrences'];
	$ilance->GPC['trialOccurrences'] = $ilance->GPC['trialOccurrences'];
	$ilance->GPC['trialAmount'] = $ilance->GPC['trialAmount'];
	$ilance->GPC['cardNumber'] = $ilance->GPC['cardNumber'];
	$ilance->GPC['expirationDate'] = $ilance->GPC['creditcard_year'] . '-' . $ilance->GPC['creditcard_month'];
	$ilance->GPC['firstName'] = $ilance->GPC['firstName'];
	$ilance->GPC['lastName'] = $ilance->GPC['lastName'];
	$ilance->GPC['cardType'] = $ilance->GPC['cardType'];
	
	// #### build our special recurring subscription xml data ##############
	$xml = $ilance->authorizenet->build_recurring_subscription_xml($ilance->GPC['mode'], $ilance->GPC);
	$method = 'curl'; // curl or fsockopen can be used
	
	//echo $xml; exit;
	
	// #### post and fetch gateway response ################################
	if ($xml != '')
	{
		$gatewayresponse = $ilance->authorizenet->send_response($method, $xml, 'https://api.authorize.net', '/xml/v1/request.api');
		if (!empty($gatewayresponse) AND $gatewayresponse != false)
		{
			$refId = $resultCode = $code = $text = $subscriptionId = '';
			list($refId, $resultCode, $code, $text, $subscriptionId) = $ilance->authorizenet->parse_return($gatewayresponse);
			
			if (strtolower($resultCode) == 'ok')
			{
				// #### completed authorize.net recurring payment
				$navcrumb = array();
				$navcrumb["$ilpage[subscription]"] = $phrase['_subscription'];
				$navcrumb[""] = $phrase['_completed'];
		
				$startdate = DATETIME24H;
				$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
				$recurring = 1;
				$paymethod = $ilance->GPC['cardType'];
				
				// create new invoice associated with this transaction
				$invoiceid = $ilance->accounting->insert_transaction(
					$ilance->GPC['subscriptionid'],
					0,
					0,
					$_SESSION['ilancedata']['user']['userid'],
					0,
					0,
					0,
					$ilance->GPC['name'] . ' [SUBSCR_ID: ' . $subscriptionId . ']',
					sprintf("%01.2f", $ilance->GPC['amount']),
					sprintf("%01.2f", $ilance->GPC['amount']),
					'paid',
					'debit',
					$paymethod,
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					$subscriptionId,
					0,
					0,
					1,
					'',
					0,
					0
				);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET paymentgateway = 'authnet'
					WHERE invoiceid = '" . intval($invoiceid) . "'
				", 0, null, __FILE__, __LINE__);
				
				// activate subscription plan
				activate_subscription_plan($_SESSION['ilancedata']['user']['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['amount']);
				
				// #### REFERRAL SYSTEM TRACKER ############################
				update_referral_action('subscription', $_SESSION['ilancedata']['user']['userid']);
				
				$area_title = $phrase['_invoice_payment_complete'];
				$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_complete'];
				
				print_notice($phrase['_invoice_payment_complete'], $phrase['_thank_you_your_subscription_was_paid_in_full'], $ilpage['subscription'], $phrase['_subscription_menu']);
				exit();
			}
			else
			{
				$area_title = $phrase['_payment_gateway_communication_error'];
				$page_title = SITE_NAME . ' - ' . $phrase['_payment_gateway_communication_error'];
				
				$navcrumb = array();
				$navcrumb["$ilpage[subscription]"] = $phrase['_subscription'];
				$navcrumb[""] = $phrase['_notice'];
				
				$transaction_message = $text;
				$date_time = DATETIME24H;
				
				$pprint_array = array('date_time','transaction_message','transaction_code','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				
				$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
		}
		else
		{
			$area_title = $phrase['_payment_gateway_communication_error'];
			$page_title = SITE_NAME . ' - ' . $phrase['_payment_gateway_communication_error'];
			
			$navcrumb = array();
			$navcrumb["$ilpage[subscription]"] = $phrase['_subscription'];
			$navcrumb[""] = $phrase['_notice'];
			
			$transaction_message = $phrase['_could_not_communicate_with_payment_gateway'];
			$date_time = DATETIME24H;
			
			$pprint_array = array('date_time','transaction_message','transaction_code','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	else
	{
		$area_title = $phrase['_payment_gateway_communication_error'];
		$page_title = SITE_NAME . ' - ' . $phrase['_payment_gateway_communication_error'];
		
		$navcrumb = array();
		$navcrumb["$ilpage[subscription]"] = $phrase['_subscription'];
		$navcrumb[""] = $phrase['_notice'];
		
		$transaction_message = $phrase['_could_not_build_a_valid_payment_gateway_response'];
		$date_time = DATETIME24H;
		
		$pprint_array = array('date_time','transaction_message','transaction_code','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

($apihook = $ilance->api('payment_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>