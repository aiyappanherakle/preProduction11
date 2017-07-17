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
error_reporting(E_All);
// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_shipping.php');
$ilance->subscription = construct_object('api.subscription');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{


		/* vijay  for bug 5829 * start 20.12.13 */	
		
		if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='split_invoices')
		{
		
		$giv_user_id=(isset($ilance->GPC['giv_user_id'])) ? $ilance->GPC['giv_user_id'] : '';
		 
		$sql="SELECT user_id,country
		FROM " . DB_PREFIX . "users AS u
		WHERE u.status = 'active'
		and u.user_id = '".$giv_user_id."'
		";

		$pen_uid= $ilance->db->query($sql);

		$update_coin=$ilance->db->fetch_array($pen_uid);
			$show_coin_search=0;
			if($ilance->db->num_rows($pen_uid) > 0)
			{
			$show_coin_search=1;
			$ifcoins=0;
			$giv_countryid=$update_coin['country'];
			
			$ilance->tax = construct_object('api.tax');	
			// Murugan Code On Feb 11 Starts Here for Changes of invoice payment
				$sql_regardlist = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . $update_coin['user_id']."'
				AND status = 'unpaid'	and not combine_project
				AND isfvf != 1
				AND isif != 1 
				AND isbuyerfee != 1 
				AND isenhancementfee != 1
				limit 380		
				");

				
				$invcount = $ilance->db->num_rows($sql_regardlist);

				if($invcount > 0)
				{
					$ifcoins=1;
					
					while($res_regardlist = $ilance->db->fetch_array($sql_regardlist))
					{
						
						// echo '<pre>';
						// print_r($res_regardlist);
						
						$invid[] = $res_regardlist['invoiceid'];
						
						$show['invoicecancelled'] = 0;
						$showchkbox=0;
						if($invcount > 1)
						{
						$showchkbox=1;
						$res_regardlist['action'] = '<input type="checkbox" id="checkbox-1" name="select_invoiceid_split[]" value="'. $res_regardlist['invoiceid'].'" />';
						
						}
						else{
						$res_regardlist_single_proj = '<input type="hidden"  name="select_invoiceid_split[]" value="'.$res_regardlist['invoiceid'].'" />';
						}

						
						$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
													WHERE invoiceid = '".$res_regardlist['invoiceid']."'
													AND buyer_id = '".$giv_user_id."'");
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
							if ($res_regardlist['p2b_user_id'] == $giv_user_id)
							{
								$show['paymentpulldown'] = 0;
								$cmd = '_do-invoice-action';
							}
							else if ($res_regardlist['user_id'] == $giv_user_id)
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
														AND user_id = '".$giv_user_id."'
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
						$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
							
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
					 $totalamount_pending_in=array_sum($totalamountlistcal);
					// end
					
					//karthik on sep06 for sales tax reseller
					 $sales_tax_reseller = fetch_user('issalestaxreseller',$giv_user_id);	
					 
					if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
					 {			 	
						$state = fetch_user('state',$giv_user_id);			
						 $taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0).'%, '.$state.')';
						 
						 //new changes apr22 hiddeen taxinfo variable
						 
						$taxinfonew = $ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						
						$show['taxes'] = 1;
					}
					else if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
					{						
						 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						
						$taxinfonew = 0.00;
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						$show['taxes'] = 1;
					}
					else 
					{						
						 $taxinfo = 'Sales Tax Not Applicable (Out of State)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
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



				
				
				if( $totalamount_pending_in < 10000)
				{	
					$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
												<optgroup label="Online Payment">';
					
					$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
															from ".DB_PREFIX."users 
															where user_id =".$giv_user_id);
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
															where user_id =".$giv_user_id);
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


				

				$shippment_nethod_pulldown = print_shippment_cost($projects,$selected=0,'shipper_id',array_sum($totqty),$giv_user_id,$giv_countryid,$totalamount_pending_in);

				$shipper_drop_down = $shippment_nethod_pulldown['html'];

				$itemcount= array_sum($totqty);

				$totalamount=$totalamountnew+$shipper_drop_down;
				
				$shipping_cost_amount=$ilance->currency->format($shipper_drop_down,$ilconfig['globalserverlocale_defaultcurrency']);

				$totalamount=$ilance->currency->format($totalamount,$ilconfig['globalserverlocale_defaultcurrency']);
				
				$user_id=$giv_user_id;
				
				$pprint_array = array('shipping_cost_amount','giv_countryid','qtyhidden','res_regardlist_single_proj','giv_user_id','ifcoins','shipper_drop_down','taxamounthidden','totalhidden','invidhidden','project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','miscamount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;


				$ilance->template->fetch('main', 'split_invoices.html',2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('regardlist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();



				}

			$pprint_array = array('giv_countryid','giv_user_id','res_regardlist_single_proj','ifcoins','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
			$ilance->template->fetch('main', 'split_invoices.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();


			}
		}
		if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='sltd_proj_split_invoices')
		{
		
			require_once(DIR_CORE . 'functions_shipping.php');
			$ilance->tax = construct_object('api.tax');
			$ilance->subscription = construct_object('api.subscription');
			
				$selectedinv_ar = array();
				$giv_user_id=$ilance->GPC['select_usrid'];
				$invoiceid_split=$ilance->GPC['select_invoiceid_split'];
				$giv_countryid=$ilance->GPC['giv_countryid'];
				
				/* echo '<pre>';
				print_r($ilance->GPC);
				exit; */
				
				foreach($invoiceid_split as $invoice_split)
				{
					$selectedinv_ar[]=$invoice_split;				
					
				}
				$numberofinv=count($selectedinv_ar);
				
				
			if(isset($numberofinv) and $numberofinv > 0)
			{
				$selectedinv = implode(',',$selectedinv_ar);
				
				
				$sql_regardlist = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE invoiceid in(".$selectedinv.")
				AND status = 'unpaid'	and not combine_project
				AND isfvf != 1
				AND isif != 1 
				AND isbuyerfee != 1 
				AND isenhancementfee != 1		
				");
				
				$invcount = $ilance->db->num_rows($sql_regardlist);
				
				if($invcount > 0)
				{
					$ifcoins=1;
					
					while($res_regardlist_seltd = $ilance->db->fetch_array($sql_regardlist))
					{
						
						 // echo '<pre>';
						 // print_r($res_regardlist_seltd);
						
						$invid[] = $res_regardlist_seltd['invoiceid'];
						
						$show['invoicecancelled'] = 0;
						
						
						$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
													WHERE invoiceid = '".$res_regardlist_seltd['invoiceid']."'
													AND buyer_id = '".$giv_user_id."'");
						if($ilance->db->num_rows($buy)>0)
						{
							$resbuy = $ilance->db->fetch_array($buy);
						$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
													WHERE coin_id = '".$res_regardlist_seltd['projectid']."'");
										$temp=$ilance->db->fetch_array($bids);						
							
							$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
							$res_regardlist_seltd['qty'] = $resbuy['qty'];
							 $totqty[] = $res_regardlist_seltd['qty']*$coin_no_in_set;
						}
						else
						{
						//check 	nocoin  in ilance_coins for each coins
						$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
													WHERE coin_id = '".$res_regardlist_seltd['projectid']."'");
										$temp=$ilance->db->fetch_array($bids);		
										
							$res_regardlist_seltd['qty'] = 1;
							 
							$totqty[] = empty($temp['nocoin'])?1:intval($temp['nocoin']);
						}
						

				//$res_invoice = $ilance->db->fetch_array($sql_invoice);
						$id = $res_regardlist_seltd['invoiceid'];
						$txn = $res_regardlist_seltd['transactionid'];
						$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';

						($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;
					
				// total amount paid for this invoice
						//$amountpaid = $ilance->currency->format($res_regardlist_seltd['paid'], $res_regardlist_seltd['currency_id']);
				$amountpaid =  $ilance->currency->format(0);
				// invoice creation date
						$createdate = print_date($res_regardlist_seltd['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$show['miscamount']=false;
						 $res_regardlist_seltd['miscamount'];
						if($res_regardlist_seltd['miscamount']>0)
						{
						$show['miscamount']=true;
						$miscamount =  $ilance->currency->format($res_regardlist_seltd['miscamount']);
						}
				// invoice due date
				if ($res_regardlist_seltd['duedate'] == "0000-00-00 00:00:00")
				{
					$duedate = '--';		
				}
				else
				{
					$duedate = print_date($res_regardlist_seltd['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				// invoice paid date
				if ($res_regardlist_seltd['paiddate'] == "0000-00-00 00:00:00")
				{
					$paiddate = '--';
				}
				else
				{
					$paiddate = print_date($res_regardlist_seltd['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}

				// invoice identifier
				$invoiceid = $id;

				$show['ispaid'] = $show['isunpaid'] = $show['isscheduled'] = $show['iscomplete'] = $show['iscancelled'] = 0;

				if ($res_regardlist_seltd['status'] == 'paid')
				{
					$show['ispaid'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'unpaid')
				{
					$show['isunpaid'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'scheduled')
				{
					$show['isscheduled'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'complete')
				{
					$show['iscomplete'] = 1;
				}
				if ($res_regardlist_seltd['status'] == 'cancelled')
				{
					$show['iscancelled'] = 1;
				}			
				if ($res_regardlist_seltd['invoicetype'] == 'subscription')
				{
					$show['subscriptionpayment'] = true;
				}
				else
				{
					$show['subscriptionpayment'] = false;
				}


						
						if ($res_regardlist_seltd['status'] == 'unpaid' OR $res_regardlist_seltd['status'] == 'scheduled')
						{
							if ($res_regardlist_seltd['p2b_user_id'] == $giv_user_id)
							{
								$show['paymentpulldown'] = 0;
								$cmd = '_do-invoice-action';
							}
							else if ($res_regardlist_seltd['user_id'] == $giv_user_id)
							{
								$show['paymentpulldown'] = 1;
								$cmd = '_do-invoice-preview';
							}
						}
						else if ($res_regardlist_seltd['status'] == 'cancelled')
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
						if ($res_regardlist_seltd['projectid'] > 0)
						{
						$show['listing'] = 1;
						$listing = fetch_coin_table('Title',$res_regardlist_seltd['projectid']);
						$haswinner = fetch_auction('haswinner', $res_regardlist_seltd['projectid']);			
						$project_id = $res_regardlist_seltd['projectid'];
						$projects[] = $res_regardlist_seltd['projectid'];
						}
						// tax check 
						$taxdetails = $res_regardlist_seltd['istaxable'];
						$show['buyer'] = 0;
						$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
														WHERE projectid = '".$res_regardlist_seltd['projectid']."'
														AND user_id = '".$giv_user_id."'
														AND isbuyerfee = '1'");
							if($ilance->db->num_rows($buyfee_inv) > 0)
							{
								$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
								$totalamountlist = $ilance->currency->format(($res_regardlist_seltd['amount'] + $res_buyfee['amount'] ), $res_regardlist_seltd['currency_id']);
								$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $res_regardlist_seltd['currency_id']);
								$buyerfee1 = $res_buyfee['amount'];
								$totalamountlist1 = $res_regardlist_seltd['amount'] + $res_buyfee['amount'] ;
								$show['buyer'] = 1;
							}
							else
							{
								$totalamountlist = $ilance->currency->format(($res_regardlist_seltd['amount'] ), $res_regardlist_seltd['currency_id']);
								$buyerfee =  $ilance->currency->format(0, $res_regardlist_seltd['currency_id']);
								$buyerfee1 = 0;
								$totalamountlist1 = $res_regardlist_seltd['amount'];
								$show['buyer'] = 1;
							}
							
							
							$paymethod = ucwords($res_regardlist_seltd['paymethod']);
							$paystatus = ucwords($res_regardlist_seltd['status']);
							$providername = $phrase['_billing_and_payments'];
							$provider = SITE_NAME;
							$providerinfo = SITE_ADDRESS;
							
							$show['viewingasprovider'] = $show['escrowblock'] = false;
							if ($res_regardlist_seltd['invoicetype'] == 'escrow')
							{
					// escrow handling
					$show['providerblock'] = true;
					$show['escrowblock'] = true;
					
								
					$ilance->auction = construct_object('api.auction');
					$ilance->escrow = construct_object('api.escrow');
					
					
						$customer = fetch_user('username', $res_regardlist_seltd['user_id']);
						$customeremail = fetch_user('email', $res_regardlist_seltd['user_id']);
						$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
						$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
							
					// display invoice type on invoice payment form
					$invoicetype = print_transaction_type($res_regardlist_seltd['invoicetype']);
				}

							if ($res_regardlist_seltd['invoicetype'] == 'debit')
							{
					// escrow handling
					$show['providerblock'] = true;
					$show['escrowblock'] = true;
					
								
					$ilance->auction = construct_object('api.auction');
					$ilance->escrow = construct_object('api.escrow');
					
					
						$customer = fetch_user('username', $res_regardlist_seltd['user_id']);
						$customeremail = fetch_user('email', $res_regardlist_seltd['user_id']);
						$customerinfo = fetch_business_numbers(fetch_user('user_id', '', $customer)) . print_shipping_address_text_herakle(fetch_user('user_id', '', $customer));				
						$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));	
							
					// display invoice type on invoice payment form
					$invoicetype = print_transaction_type($res_regardlist_seltd['invoicetype']);
				}
							
							else if ($res_regardlist_seltd['invoicetype'] == 'buynow')
							{
								$show['providerblock'] = true;
								$customer = fetch_user('username', $res_regardlist_seltd['user_id']);
								$customeremail = fetch_user('email', $res_regardlist_seltd['user_id']);						
								$invoicetype = print_transaction_type($res_regardlist_seltd['invoicetype']);
								$customerinfo = print_shipping_address_text($res_regardlist_seltd['user_id']) . fetch_business_numbers($res_regardlist_seltd['user_id']);						
								$customername = fetch_user('fullname', $res_regardlist_seltd['user_id']);
								
							}
						$description .= stripslashes($res_regardlist_seltd['description']).'<br>';
						$amountcal[] = $res_regardlist_seltd['amount'];
						$taxinfolist = $res_regardlist_seltd['taxinfo'];
						$invoicetype = $res_regardlist_seltd['invoicetype'];
						$buyerfeecal[] = $buyerfee1;
						$totalamountlistcal[] = $totalamountlist1;
					
						
						$res_regardlist_seltd['item_id'] 	 = 	$res_regardlist_seltd['projectid'];
						$res_regardlist_seltd1['itemtitle'] = fetch_coin_table('Title',$res_regardlist_seltd['projectid']);
						/*======vijay bug id:4714 start=====*/
						if($res_regardlist_seltd['Site_Id'] >0)
						{
						$res_regard='eBay';
						}
						else
						{
						$res_regard='GC';
						}
						$res_regardlist_seltd['Site_Id'] 	 =$res_regard;
						/*======vijay bug id:4714 end=====*/
						
						if ($ilconfig['globalauctionsettings_seourls'])
						{
						
							$res_regardlist_seltd['item_id']='<a href="Coin/'.$res_regardlist_seltd['projectid'].'/'.construct_seo_url_name($res_regardlist_seltd1['itemtitle']).'"> '.$res_regardlist_seltd['item_id'].'</a>';
							$res_regardlist_seltd['itemtitle'] ='<a href="Coin/'.$res_regardlist_seltd['projectid'].'/'.construct_seo_url_name($res_regardlist_seltd1['itemtitle']).'"> '.$res_regardlist_seltd1['itemtitle'].'</a>';
							
						}
						else
						{
							$res_regardlist_seltd['item_id']='<a href="merch.php?id='.$res_regardlist_seltd['projectid'].'">'.$res_regardlist_seltd1['item_id'].'</a>';
							$res_regardlist_seltd['itemtitle']='<a href="merch.php?id='.$res_regardlist_seltd['projectid'].'">'.$res_regardlist_seltd1['itemtitle'].'</a>';
						}
								
							
						//$res_regardlist_seltd['itemtitle'] = fetch_auction('project_title', $res_regardlist_seltd['projectid']);
						$res_regardlist_seltd['finalprice'] = $ilance->currency->format($res_regardlist_seltd['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res_regardlist_seltd['buyerfees'] = $buyerfee;
						$res_regardlist_seltd['totallistamount'] =  $totalamountlist;
						$regardlist[] = $res_regardlist_seltd;
						 		
						
										
					}
					
					
					$show['taxes'] = 0;
					
					$qtyhidden = '<input type = "hidden" name="qtyhidden" id="qtyhidden" value="'.array_sum($totqty).'">';
					$invidim = implode(',',$invid);
					$invidhidden = '<input type = "hidden" name="invhidden" id="invhidden" value="'.$invidim.'">';
					$amounttotal = array_sum($totalamountlistcal);
					$amount = $ilance->currency->format(array_sum($totalamountlistcal),$ilconfig['globalserverlocale_defaultcurrency']);
					//karthik start Apr 15
					 $totalamount_pending_in =array_sum($totalamountlistcal);
					// end
					
					//karthik on sep06 for sales tax reseller
					 $sales_tax_reseller = fetch_user('issalestaxreseller',$giv_user_id);	
					
					if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
					 {	
					   	$state = fetch_user('state',$giv_user_id);			
						 $taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0).'%, '.$state.')';
						 
						 //new changes apr22 hiddeen taxinfo variable
						 
						$taxinfonew = $ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						
						$show['taxes'] = 1;
					}
					else if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
					{		
		
						 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						
						$taxinfonew = 0.00;
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						$show['taxes'] = 1;
					}
					else 
					{		
						 $taxinfo = 'Sales Tax Not Applicable (Out of State)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
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

				

				if( $totalamount_pending_in < 10000)
				{	
					$payment_method_pulldown='<select name="account_id" style="font-family: verdana">
												<optgroup label="Online Payment">';
					
					$user_paymethods_sql=$ilance->db->query("select allowed_paymethods 
															from ".DB_PREFIX."users 
															where user_id =".$giv_user_id);
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
															where user_id =".$giv_user_id);
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


				$shippment_nethod_pulldown = print_shippment_nethod_pulldown($projects,$selected=0,'shipper_id','return change_shipper();',array_sum($totqty),$giv_user_id,$giv_countryid,$totalamount_pending_in);


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
				$user_id=$giv_user_id;
				$pprint_array = array('qtyhidden','giv_user_id','ifcoins','shipper_drop_down','taxamounthidden','totalhidden','invidhidden','project_id','customeremail','buyerfee','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','miscamount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
				($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;


				$ilance->template->fetch('main', 'split_buyer_invoice.html',2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('regardlist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();



				}
			}
			else{
				
				print_action_failed('Please the select aleast one checkbox to split invoice', "split_invoices.php");
				exit();
				
			}
		}

		$pprint_array = array('giv_countryid','giv_user_id','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'split_invoices.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
		/* vijay  ends 3.12.13 */
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}



function print_shippment_cost($projects,$selected,$name,$totqty=0,$giv_user_id,$giv_countryid,$totalamount_pending_in)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $giv_countryid==500 and !$only_buynow)
	{
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$giv_user_id."'AND status IN('unpaid','paid')");
	if($ilance->db->num_rows($sql)<1 AND $ilance->db->num_rows($sql) ==0)
	{
	$first_shipment=true;
	}
	}
	

	
	//shipping for INTERNATIONAL CLIENTS 
	
	
	
	if($giv_countryid!=500)
	{			
	///invoice  over $10,000	
	if( $totalamount_pending_in >= '5000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
		if($ilance->db->num_rows($sql))
		{
		
			while($line=$ilance->db->fetch_array($sql))
			{
			
				if($totqty>$line['maxitem_count'])
				{
				$international_extra_morethen_n_coins=$line['addedfee_above_maxitem_count'];
				}
				$html.=$line['basefee']+ ($line['addedfee'] *$totqty) +$international_extra_morethen_n_coins;

			}
		 
		}
	}
	
	else if( $totalamount_pending_in >= '1000.00')
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid in('22','23') and visible=1");
		if($ilance->db->num_rows($sql))
		{
		
					while($line=$ilance->db->fetch_array($sql))
					{
					
					// oct-31
					$selected='22';
					if($line['shipperid']==$selected)
					{
					if($totqty>$line['maxitem_count'])
					{						
					$international_extra_morethen_n_coins_text=$line['addedfee_above_maxitem_count'];
					}
					$html.=$line['basefee']+($line['addedfee'] *$totqty)+ $international_extra_morethen_n_coins_text;
					
					
					}
					
					}
		  
		 }
	}
	
	else
	{	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
		if($ilance->db->num_rows($sql))
		{

			while($line=$ilance->db->fetch_array($sql))
			{
				
				
				$selected='22';
				if($line['shipperid']==$selected)
				{
				if($totqty>$line['maxitem_count'])
				{
					$international_extra_morethen_n_coins=$line['addedfee_above_maxitem_count'];
				}
				$html.=$line['basefee']+ ($line['addedfee'] *$totqty) +$international_extra_morethen_n_coins;
				}

			}
		}
	}
	
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $totalamount_pending_in > '10000.00')
	   {
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql))
		  {
				
				while($line=$ilance->db->fetch_array($sql))
				{
					 $html.=$line['basefee']+ ($line['addedfee'] * $totqty);
	             }
		        
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $totalamount_pending_in > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			    $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql))
				{
					while($line=$ilance->db->fetch_array($sql))
					{
					  					   
						$selected='27';
						
						if($first_shipment and $line['shipperid']=='27')
						{
						$html.='0.00';	
						}else
						{	
							if($line['shipperid']==$selected)
							{						
							 $html.=$line['basefee']+ ($line['addedfee'] *$totqty);
							}	
							
						}
												
							
					}
				    
				}
		}  
		
		else
		{	
            //new change apr19  order by carrier to basefee asc
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			
			while($line=$ilance->db->fetch_array($sql))
				{
				
				//	Shipping is free for your first auction purchase (U.S. only)
			   
				$selected='26';
				
					if($first_shipment AND $line['shipperid']=='26' AND !$only_buynow)
					{
						 $html.='0.00';	
					}

					else
					{
					if($line['shipperid']==$selected){
						 $html.=$line['basefee']+ ($line['addedfee'] *$totqty);
					}
					}
					
				}
				
			}
			}
			if($first_shipment)
			$free_announce='0.00';	
		}

	$html.=$free_announce;
	$shipping_add['html']=$html;
	
	
	return $shipping_add;
	}

function print_shippment_nethod_pulldown($projects,$selected,$name,$onchage_script,$totqty=0,$giv_user_id,$giv_countryid,$totalamount_pending_in)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $giv_countryid==500 and !$only_buynow)
	{
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$giv_user_id."'AND status IN('unpaid','paid')");
	if($ilance->db->num_rows($sql)<1 AND $ilance->db->num_rows($sql) ==0)
	{
	$first_shipment=true;
	}
	}
	
	//karthik start on Apr 12
	
	//shipping for INTERNATIONAL CLIENTS 
	
	
	
	if($giv_countryid!=500)
	{			
	///invoice  over $10,000	
	if( $totalamount_pending_in >= '10000.00')
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
		 
	else
	{	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
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
	}
	}
	$html.='</select>';
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
	   if( $totalamount_pending_in > '10000.00')
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
		   else if( $totalamount_pending_in > '1000.00')
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

$html.=$free_announce;
	$pulldown['html']=$html;
	$pulldown['script']=$script;
	
	return $pulldown;
	}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
