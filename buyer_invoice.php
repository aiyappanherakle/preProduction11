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
	'flashfix',
	'jquery'
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
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode('buyer_invoice.php' . print_hidden_fields(true, array(), true)));
	exit();
}
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'myloadvalue')
{
refresh(HTTPS_SERVER .'buyer_invoice.php');
exit();
}
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view')
{
	
	$area_title = $phrase['_invoice_payment_menu'] . ' #' . $ilance->GPC['txn'];
	$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu'];
	
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]?cmd=com-transactions"] = $phrase['_accounting'];
	$navcrumb[""] = $phrase['_transaction'] . ' #' . $ilance->GPC['txn'];
			// murugan removed this condition on Jan 06 AND status = 'paid'	
	$sql_inv = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid']."'			
			AND isfvf != 1
			AND isif != 1 
			AND isbuyerfee != 1 
			AND isenhancementfee != 1
			AND transactionid = '".$ilance->GPC['txn']."'			
		");			
	if($ilance->db->num_rows($sql_inv) > 0)	
	{
		$result = $ilance->db->fetch_array($sql_inv);
		$muruid = $result['invoiceid'];
		$amountpaid = $ilance->currency->format($result['paid']);
		$paiddate = print_date($result['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$createdate = print_date($result['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$customer = fetch_user('username', $result['user_id']);
		$customeremail = fetch_user('email', $result['user_id']);
		$customerinfo = print_shipping_address_text_herakle($result['user_id']) . fetch_business_numbers($result['user_id']);						
		$customername = fetch_user('fullname', $result['user_id']);
		$description = 'Paying by '.$result['paymethod'];
		$exp_project = explode(',',$result['combine_project']);
		$totalamount = $result['totalamount'];
		 $miscamount = $result['miscamount'];
		$taxamount1=$result['taxamount'];
		$pending_amount =  $ilance->currency->format($result['amount']+$result['taxamount']);
		
		if($result['paid']>0)
		{
		$pending_amount =  $ilance->currency->format($result['amount']+$result['taxamount']);
		 
			$query="SELECT *  FROM ".DB_PREFIX."partial_payment WHERE invoiceid = '".$result['invoiceid']."' ORDER BY paymentdate";
			$result1=$ilance->db->query($query);
			$show['partial_payment_rows']=false;
			if($ilance->db->num_rows($result1))
			{
				$show['partial_payment_rows']=true;
				while($line1=$ilance->db->fetch_array($result1))
				{
					$line1['payment_date']=print_date($line1['paymentdate']);
					$line1['partial_amount']=$ilance->currency->format($line1['partial_amount']);
					$line1['paymethod']=ucwords($line1['paymethod']);
					
					$partial_payment_rows[]= $line1;
				}
			}
		}
		if ($result['status'] == 'paid')
		{
			$show['ispaid'] = 1;
		}
		if ($result['status'] == 'unpaid')
		{
			$show['isunpaid'] = 1;
		}
		if ($result['status'] == 'scheduled')
		{
			$show['isscheduled'] = 1;
		}
		if ($result['status'] == 'complete')
		{
			$show['iscomplete'] = 1;
		}
		if ($result['status'] == 'cancelled')
		{
			$show['iscancelled'] = 1;
		}			
		for($i=0;$i <=count($exp_project);$i++)
		{
		
			$sele_pro = $ilance->db->query("SELECT *
										FROM " . DB_PREFIX . "invoices
										WHERE invoiceid = '".$exp_project[$i]."'");
					if($ilance->db->num_rows($sele_pro))
					{
						while($res_inv = $ilance->db->fetch_array($sele_pro))
						{
							
							if ($res_inv['projectid'] > 0)
							{
									$show['listing'] = 1;
									$listing = fetch_coin_table('Title',$res_regardlist['projectid']);;
									$haswinner = fetch_auction('haswinner', $res_inv['projectid']);			
									$project_id = $res_inv['projectid'];
							}
									$show['buyer'] = 0;
					$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
								WHERE projectid = '".$res_inv['projectid']."'
								AND user_id = '".$_SESSION['ilancedata']['user']['userid']."'
								AND isbuyerfee = '1'");
					if($ilance->db->num_rows($buyfee_inv) > 0)
					{
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						$totalamountlist = $ilance->currency->format(($res_inv['amount'] + $res_buyfee['amount'] ), $res_inv['currency_id']);
						$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $res_inv['currency_id']);
						$buyerfee1 = $res_buyfee['amount'];
						$totalamountlist1 = $res_inv['amount'] + $res_buyfee['amount'] ;
						$show['buyer'] = 1;
					}
					else
					{
						$totalamountlist = $ilance->currency->format(($res_inv['amount'] ), $res_inv['currency_id']);
						$buyerfee =  $ilance->currency->format(0, $res_inv['currency_id']);
						$buyerfee1 = 0;
						$totalamountlist1 = $res_inv['amount'];
						$show['buyer'] = 1;
					}
				 
							$totalamountlistcal[] = $totalamountlist1;			
				
							$res_inv1['item_id'] 	 = 	$res_inv['projectid'];
							$res_inv1['itemtitle'] = fetch_coin_table('Title', $res_inv['projectid']);
							//fetch_auction('project_title', $res_inv['projectid']);
							
							if ($ilconfig['globalauctionsettings_seourls'])
							{
							
							$res_inv['item_id']='<a href="Coin/'.$res_inv['projectid'].'/'.construct_seo_url_name($res_inv1['itemtitle']).'"> '.$res_inv1['item_id'].'</a>';
							$res_inv['itemtitle'] ='<a href="Coin/'.$res_inv['projectid'].'/'.construct_seo_url_name($res_inv1['itemtitle']).'"> '.$res_inv1['itemtitle'].'</a>';
							
							}
							else
							{
							$res_inv['item_id']='<a href="merch.php?id='.$res_inv['projectid'].'">'.$res_inv1['item_id'].'</a>';
							$res_inv['itemtitle']='<a href="merch.php?id='.$res_inv['projectid'].'">'.$res_inv1['itemtitle'].'</a>';
							}
							
							/*======vijay bug id:4714 start=====*/
							if($res_inv['Site_Id'] >0)
							{
							$res_regard='eBay';
							}
							else
							{
							$res_regard='GC';
							}
							$res_inv['Site_Id'] = $res_regard;
							
							/*======vijay bug id:4714 end=====*/
							//$res_regardlist['itemtitle'] = fetch_auction('project_title', $res_regardlist['projectid']);
							$res_inv['finalprice'] = $ilance->currency->format($res_inv['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
							
							$res_inv['buyerfees'] = $buyerfee;
							$res_inv['totallistamount'] =  $totalamountlist;
							$itemlist[] = $res_inv;
						}
						
					}
		}
		$amounttotal = array_sum($totalamountlistcal);
		$amount = $ilance->currency->format(array_sum($totalamountlistcal),$ilconfig['globalserverlocale_defaultcurrency']);
		$ilance->tax = construct_object('api.tax');	
		$state = fetch_user('state',$_SESSION['ilancedata']['user']['userid']);	
		$buyerfe = array_sum($buyerfeecal);
		$buyerfee = $ilance->currency->format($buyerfe,$ilconfig['globalserverlocale_defaultcurrency']);		
		$show['taxes'] = 0;	
		$shipping = $ilance->currency->format(0, $ilconfig['globalserverlocale_defaultcurrency']);
		
		$sele_invo = $ilance->db->query("SELECT *
										FROM " . DB_PREFIX . "invoice_projects
										WHERE final_invoice_id = '".$muruid."'");
		if($ilance->db->num_rows($sele_invo) > 0)
		{
			$resinv = $ilance->db->fetch_array($sele_invo);
			$shipping = $ilance->currency->format($resinv['shipping_cost'], $ilconfig['globalserverlocale_defaultcurrency']);
		}
 
		if ($taxamount1 > 0)
		{	
			//$totalamount = $ilance->currency->format(($amounttotal-$result['paid'] + $taxamount1), $ilconfig['globalserverlocale_defaultcurrency']);
			$totalamountnew = $amounttotal + $taxamount1;					
			$taxamount= $ilance->currency->format($taxamount1);
			$taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($_SESSION['ilancedata']['user']['userid'], $amounttotal, 'buynow', 0).'%, '.$state.')';
			$show['taxes'] = 1;
		}
		else
		{				
			//$totalamount = $ilance->currency->format($amounttotal-$result['paid'], $ilconfig['globalserverlocale_defaultcurrency']);
			$totalamountnew = $amounttotal;
			//suku1
			$totalhidden = '<input type = "hidden" name="totalhidden" id="totalhidden"  value="'.$totalamountnew.'">';			
		}
		$totalamount = $ilance->currency->format($totalamount);
		$show['miscamount']=false;
		if($miscamount>0)
		{
		$show['miscamount']=true;
		$miscamount =  $ilance->currency->format($miscamount);
		}
		//$itemlist[] = $result;
echo '<!--suku'.$totalamount.' -->';
		
		$pprint_array = array('pending_amount','shipping','project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','miscamount','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('invoicepayment_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'invoice_paid.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->parse_loop('main', array('itemlist','partial_payment_rows'));
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		print_notice($phrase['_no_invoice_found'], "There is no invoices related with this transaction",$ilpage['main'].'?cmd=cp', $phrase['_my_cp']);
	}
		
}
		$ilance->tax = construct_object('api.tax');	
		// Murugan Code On Feb 11 Starts Here for Changes of invoice payment
		$sql_regardlist = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid']."'
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
											AND buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'");
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
				
		
		//$res_invoice = $ilance->db->fetch_array($sql_invoice);
				$id = $res_regardlist['invoiceid'];
				$txn = $res_regardlist['transactionid'];
				$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	
				($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;
			
		// total amount paid for this invoice
				//$amountpaid = $ilance->currency->format($res_regardlist['paid'], $res_regardlist['currency_id']);
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
					if ($res_regardlist['p2b_user_id'] == $_SESSION['ilancedata']['user']['userid'])
					{
						$show['paymentpulldown'] = 0;
						$cmd = '_do-invoice-action';
					}
					else if ($res_regardlist['user_id'] == $_SESSION['ilancedata']['user']['userid'])
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
												AND user_id = '".$_SESSION['ilancedata']['user']['userid']."'
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
					/*if($ilconfig['staffsettings_feeinnumber'] != 0 AND $haswinner > 0  AND $res_regardlist['isif'] != 1 AND $res_regardlist['isfvf'] != 1 AND $res_regardlist['isenhancementfee'] != 1)
					{
						$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];
					}
					else
					{
						$buyerfee_calnum = 0;
					}
					if($ilconfig['staffsettings_feeinpercentage'] != 0 AND $haswinner > 0  AND $res_regardlist['isif'] != 1 AND $res_regardlist['isfvf'] != 1 AND $res_regardlist['isenhancementfee'] != 1)
					{
						$buyerfee_calper = ($res_regardlist['amount'] * ($ilconfig['staffsettings_feeinpercentage'] / 100));
					}
					else
					{
						$buyerfee_calper = 0;
					}
					if($buyerfee_calnum <= $buyerfee_calper )
					{
						$totalamountlist = $ilance->currency->format(($res_regardlist['amount'] + $buyerfee_calper ), $res_regardlist['currency_id']);
						$buyerfee =  $ilance->currency->format($buyerfee_calper, $res_regardlist['currency_id']);
						$buyerfee1 = $buyerfee_calper;
						$totalamountlist1 = $res_regardlist['amount'] + $buyerfee_calper ;
						$show['buyer'] = 1;
					}
					else
					{
						$totalamountlist = $ilance->currency->format(($res_regardlist['amount'] + $buyerfee_calnum ), $res_regardlist['currency_id']);
						$buyerfee =  $ilance->currency->format($buyerfee_calnum, $res_regardlist['currency_id']);
						$buyerfee1 = $buyerfee_calnum;
						$totalamountlist1 = $res_regardlist['amount'] + $buyerfee_calnum ;
						$show['buyer'] = 1;
					}*/
					
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
			
			// quick auction checkup
			// murugan commented here on Jan 11
			/*$sql_auction = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_regardlist['projectid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_auction) == 0)
			{
				$area_title = $phrase['_invoice_payment_menu_denied_payment'];
				$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
				
				print_notice($phrase['_invoice_error'], $phrase['_were_sorry_this_invoice_does_not_exist'], HTTPS_SERVER . $ilpage['main'].'?cmd=cp', $phrase['_my_cp']);
				exit();
			}*/
			
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			
				$customer = fetch_user('username', $res_regardlist['user_id']);
				$customeremail = fetch_user('email', $res_regardlist['user_id']);
				$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
					
			// display invoice type on invoice payment form
			$invoicetype = print_transaction_type($res_regardlist['invoicetype']);
		}
		
					if ($res_regardlist['invoicetype'] == 'debit')
					{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
			
			// quick auction checkup
			// murugan commented here on Jan 11
			/*$sql_auction = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_regardlist['projectid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_auction) == 0)
			{
				$area_title = $phrase['_invoice_payment_menu_denied_payment'];
				$page_title = SITE_NAME . ' - ' . $phrase['_invoice_payment_menu_denied_payment'];
				
				print_notice($phrase['_invoice_error'], $phrase['_were_sorry_this_invoice_does_not_exist'], HTTPS_SERVER . $ilpage['main'].'?cmd=cp', $phrase['_my_cp']);
				exit();
			}*/
			
			$ilance->auction = construct_object('api.auction');
			$ilance->escrow = construct_object('api.escrow');
			
			
				$customer = fetch_user('username', $res_regardlist['user_id']);
				$customeremail = fetch_user('email', $res_regardlist['user_id']);
				$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
					
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
				
					$res_regardlist['item_id']='<a href="Coin/'.$res_regardlist['projectid'].'/'.construct_seo_url_name($res_regardlist1['itemtitle']).'"> '.$res_regardlist['item_id'].'</a>';
					$res_regardlist['itemtitle'] ='<a href="Coin/'.$res_regardlist['projectid'].'/'.construct_seo_url_name($res_regardlist1['itemtitle']).'"> '.$res_regardlist1['itemtitle'].'</a>';
					
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
			 $sales_tax_reseller = fetch_user('issalestaxreseller',$_SESSION['ilancedata']['user']['userid']);	
			 
			if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
             {			 	
				$state = fetch_user('state',$_SESSION['ilancedata']['user']['userid']);			
				 $taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($_SESSION['ilancedata']['user']['userid'], $amounttotal, 'buynow', 0).'%, '.$state.')';
				 
				 //new changes apr22 hiddeen taxinfo variable
				 
				$taxinfonew = $ilance->tax->fetch_taxdetails($_SESSION['ilancedata']['user']['userid'], $amounttotal, 'buynow', 0);
				$taxamount1 = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $amounttotal, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				
				$show['taxes'] = 1;
			}
			else if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
			{						
				 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
				$taxamount1 = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], 0, 'buynow', 0);
				
				$taxinfonew = 0.00;
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
			}
			else 
			{						
				$taxinfo = 'Sales Tax Not Applicable (Out of State)';
				$taxamount1 = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], 0, 'buynow', 0);
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
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] >0)
	{
	
		$payment_method_pulldown = print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid']);
		
	}
	else
	{

		if( $_SESSION['ilancedata']['user']['totalamount'] < 10000)
		{	
			$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
										<optgroup label="Online Payment">';
			
			$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$_SESSION['ilancedata']['user']['userid']);
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
													where user_id =".$_SESSION['ilancedata']['user']['userid']);
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
				

	if($_SESSION['ilancedata']['user']['userid']==82)
	{
		$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
									<optgroup label="Online Payment">';
		
		$user_paymethods_sql=$ilance->db->query("	select allowed_paymethods 
													from ".DB_PREFIX."users 
													where user_id =".$_SESSION['ilancedata']['user']['userid']);
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
		
		//new change apr28
		
		$onload = 'javascript:document.invoicepayment.reset();change_shipper();';
		$user_id=$_SESSION['ilancedata']['user']['userid'];
		$pprint_array = array('qtyhidden','user_id','shipper_drop_down','taxamounthidden','totalhidden','invidhidden','project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','miscamount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('invoicepayment_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'buyer_invoice.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->parse_loop('main', array('regardlist'));
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
		else
		{
			print_notice($phrase['_no_invoice_found'], $phrase['_there_is_no_unpaid_invoices_in_your_account'],$ilpage['main'].'?cmd=cp', $phrase['_my_cp']);
		}
		
	function print_shippment_nethod_pulldown($projects,$selected,$name,$onchage_script,$totqty=0)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $_SESSION['ilancedata']['user']['countryid']==500 and !$only_buynow)
	{
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$_SESSION['ilancedata']['user']['userid']."' AND status='paid'");
	                                                 
    if($ilance->db->num_rows($sql)<1 AND $ilance->db->num_rows($sql) ==0)
	{
	$first_shipment=true;
	}
	}
	
	//karthik start on Apr 12
	
	//shipping for INTERNATIONAL CLIENTS 
				
	if($_SESSION['ilancedata']['user']['countryid']!=500)
	{			
	///invoice  over $5,000	
	if( $_SESSION['ilancedata']['user']['totalamount'] >= '5000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
		if($ilance->db->num_rows($sql))
		{
		$html='';
		$script='';
		$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
		while($line=$ilance->db->fetch_array($sql))
		{
	      /* if($line['shipperid']==$selected)
	     {*/
			if($totqty>$line['maxitem_count'])
			{
				$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
				$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
			}
			$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';'; 
			/*}
			else
			{
			
			$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}*/
	      }
		  $html.='</select>';
		 }
	}
	
	
	else if( $_SESSION['ilancedata']['user']['totalamount'] >= '1000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid in('22','23') and visible=1");
		if($ilance->db->num_rows($sql))
		{
		$html='';
		$script='';
		$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
					while($line=$ilance->db->fetch_array($sql))
					{
					if($line['domestic'] == 1)
					{
						//echo $test = $line['title'];
					}
					if($line['international'] == 1)
					{
						//echo 'inter';
						//echo $raga = $line['title'];
					}
					// oct-31
					$selected='22';
					if($line['shipperid']==$selected)
					{
					if($totqty>$line['maxitem_count'])
					{
						$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
						$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
					}
					$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
					 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
					 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
					
					}
					else
					{
					if($totqty>$line['maxitem_count'])
					{
						$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
						$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
						
					}
					$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
					$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
					$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';

					
					}
					}
		  $html.='</select>';
		 }
	}
		 
	else
	{	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by basefee asc");
	if($ilance->db->num_rows($sql))
	{
	$html='';
	$script='';
	$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
	while($line=$ilance->db->fetch_array($sql))
	{
	$selected='21';
	if($line['shipperid']==$selected)
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
	}
	$html.='<option value="'.$line['shipperid'].'" selected="selected">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
	
	}
	else
	{
	if($totqty>$line['maxitem_count'])
	{
		$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
		$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
		
	}
	$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.$international_extra_morethen_n_coins_text.')'.'</option>';
	$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
	$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';

	
	}
	}
	}
	}
	$html.='</select>';
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $_SESSION['ilancedata']['user']['totalamount'] > '10000.00')
	   {
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql))
		  {
				$html='';
				$script='';
				$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
				while($line=$ilance->db->fetch_array($sql))
				{
				   
					
					//$selected_text='';
					//if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment)
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
	             }
		        $html.='</select>';
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $_SESSION['ilancedata']['user']['totalamount'] > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			    $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql))
				{
					$html='';
					$script='';
					$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
					while($line=$ilance->db->fetch_array($sql))
					{
					   
					   
						$selected_text='';
                       
					    //oct-31
						$selected='27';
						if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
						$html.='<option value="'.$line['shipperid'].'" '.$selected_text.' >'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
							if($first_shipment and $line['shipperid']=='27')
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';		
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							}
							
							
					  }
				     $html.='</select>';
				  }
		       }  
		
		else
		{	
            //new change apr19  order by carrier to basefee asc
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			$html='';
			$script='';
			$html.='<select name="'.$name.'" id="'.$name.'" onchange="'.$onchage_script.'"><option value="-1"  >Select Shipper</option>';
			while($line=$ilance->db->fetch_array($sql))
			{
			
			//	Shipping is free for your first auction purchase (U.S. only)
           //oct-31
			$selected='26';
			if($line['shipperid']==$selected)
		      $selected_text='selected="selected"';
			 else
			    $selected_text='';
	      if($first_shipment AND $line['shipperid']=='26' AND !$only_buynow)
	      {
		  
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			 $script.='shippers_base_cost['.$line['shipperid'].']=0;';
		     $script.='shippers_added_cost['.$line['shipperid'].']=0;';		
			}
						
			else
			{
			 
			$html.='<option value="'.$line['shipperid'].'" '.$selected_text.'>'.$line['title'].'&nbsp;&nbsp;('.$ilance->currency->format($line['basefee'], $currencyid). '&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee'], $currencyid).'&nbsp;&nbsp; per coin'.')'.'</option>';
			$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
			$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
			}
				
			}
			
			}
			}
			if($first_shipment)
			$free_announce='<div id="free_announce"><span class="green">Standard shipping is free for your first auction purchase (U.S. only)</span></div>';
			$html.='</select>';
		}
		//karthik end
/*	if($first_shipment)
	{
		for($j=0;$j<count($projects);$j++)
		{
			if(fetch_auction('filtered_auctiontype',$projects[$j]) == 'regular')
			{
			$myself[] = $projects[$j].'<br>';
			}
			
	     }
	
	//$line['addedfee']=0;
	}
	$count_project=count($myself);
	
	/*$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$_SESSION['ilancedata']['user']['userid']."'");
	if($ilance->db->num_rows($sql)>0)
	{
	$html.='<input type="hidden" value="0" id="total_val">';
	}
	
	if($first_shipment == 'true')
	{
	$html.='<input type="hidden" value="'.$count_project.'" id="total_val"><br><span class="green">Shipping is free for your first auction purchase (U.S. only)</span>';
	}
	else
	{
	$html.='<input type="hidden" value="0" id="total_val">';
	}
	$html.='</select>
$free_announce="<div id="free_announce"><span class=\"green\">First class shipping is free for your first auction purchase (U.S. only)</span></div>";
	<div id="free_announce"></div>';
*/
$html.=$free_announce;
	$pulldown['html']=$html;
	$pulldown['script']=$script;
	
	return $pulldown;
	}
/*=====
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
