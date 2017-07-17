<?php 
/* Tamil */
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
require_once('./../functions/config.php');
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	

	
	$show['user_pending_invoices'] = 0;
	$hidden_fields = '';
	
	if (isset($ilance->GPC['continue']) && $ilance->GPC['continue']=='Continue')
	{ 
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-checkout')
		{
		 
			$checked_userss = $ilance->GPC['invoice_by_user'];
			$inv_parnt = array(); 
			foreach($checked_userss as $chkduser=>$frmvals)
			{
				
				$totalamountnew = $checked_userss[$chkduser]['totalhidden'];
				$totaltaxnew = $checked_userss[$chkduser]['taxhidden'];
				$amountnew = $totalamountnew - $totaltaxnew;
				$transactionid = (isset($transactionidx) AND !empty($transactionidx)) ? $transactionidx : construct_transaction_id();
				$ipaddress       = IPADDRESS;
				$referer         = REFERRER;
				$createdate      = DATETIME24H;
				$combine_invoices=explode(",",$checked_userss[$chkduser]['invhidden']);
				$prev_invoice=0;
				foreach($combine_invoices as $cinvoice)
				{
					$query="select * from ".DB_PREFIX."invoices where invoiceid ='".$cinvoice."' and user_id='".$chkduser."' and status='unpaid'";
					$sql=$ilance->db->query($query);
					if($ilance->db->num_rows($sql)>0)
					{
						$no_of_unpaid_invoices=$ilance->db->num_rows($sql);
						$query="select * from ".DB_PREFIX."invoices where combine_project like '%".$cinvoice."%' and user_id='".$chkduser."'";
						$sql=$ilance->db->query($query);
						if($ilance->db->num_rows($sql)>0)
						{
							while($line=$ilance->db->fetch_array($sql))
							{
								if(in_array($cinvoice,explode(",",$line['combine_project'])))
								{
									$prev_invoice=$line['invoiceid'];
									$sql=$ilance->db->query("delete from ".DB_PREFIX."invoices where invoiceid='".$line['invoiceid']."'");
									$sql1=$ilance->db->query("delete from ".DB_PREFIX."invoice_projects where final_invoice_id='".$line['invoiceid']."'");
								} 
							}
						}
					}else
					{
						$no_of_unpaid_invoices=0;
					}
				}
				
				if($prev_invoice==0 and $no_of_unpaid_invoices>0)
				{
					$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "invoices
							(invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,transactionid, combine_project)
							VALUES(
							NULL,
							'" . $chkduser. "',                        
							'". $phrase['_escrow_payment_forward']."',
							'" . $ilance->db->escape_string($amountnew) . "',
							'0',
							'" . $ilance->db->escape_string($totalamountnew) . "',
							'1',
							'".$totaltaxnew."',
							'including Tax',
							'unpaid',
							'escrow',
							'check',
							'" . $ilance->db->escape_string($ipaddress) . "',
							'" . $ilance->db->escape_string($referer) . "',
							'" . $ilance->db->escape_string($createdate) . "',
							'" . $ilance->db->escape_string($createdate) . "',  
							'" . $ilance->db->escape_string($transactionid) . "',
							'".$checked_userss[$chkduser]['invhidden']."'
							)
						", 0, null, __FILE__, __LINE__);
					$parent_invoice_id = $ilance->db->insert_id();
				}
				else if($no_of_unpaid_invoices>0)
				{
					$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "invoices
							(invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,transactionid, combine_project)
							VALUES(
							'".$prev_invoice."',
							'" . $chkduser. "',                        
							'". $phrase['_escrow_payment_forward']."',
							'" . $ilance->db->escape_string($amountnew) . "',
							'0',
							'" . $ilance->db->escape_string($totalamountnew) . "',
							'1',
							'".$totaltaxnew."',
							'including Tax',
							'unpaid',
							'escrow',
							'check',
							'" . $ilance->db->escape_string($ipaddress) . "',
							'" . $ilance->db->escape_string($referer) . "',
							'" . $ilance->db->escape_string($createdate) . "',
							'" . $ilance->db->escape_string($createdate) . "',  
							'" . $ilance->db->escape_string($transactionid) . "',
							'".$checked_userss[$chkduser]['invhidden']."'
							)
						", 0, null, __FILE__, __LINE__);
					 $parent_invoice_id = $ilance->db->insert_id();
				}
				if($no_of_unpaid_invoices>0)
				{
				$expinvhidden = explode(',',$checked_userss[$chkduser]['invhidden']);	
				
							
				for($i=0; $i<count($expinvhidden);$i++)
				{
					$coin_qty=1;
					//suku
					$coin_id=fetch_invoice('projectid',$expinvhidden[$i]);
					$coin_qty=$ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "invoiceid = '" .$expinvhidden[$i]. "'", "qty");
					$seller_id=$ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "invoiceid = '" .$expinvhidden[$i]. "'", "owner_id");	
						
					$ilance->db->query("INSERT INTO " . DB_PREFIX ."invoice_projects(`invoice_id`,`project_id`,`coin_id`,`qty`,`shipper_id`,`buyer_id`,`seller_id`,`status`,`created_date`,promocode, shipping_cost, disount_val, `final_invoice_id`)VALUES(
					'".$expinvhidden[$i]."',
					'".fetch_invoice('projectid',$expinvhidden[$i])."',
					'".$coin_id."',
					'".$coin_qty."',
					'".$checked_userss[$chkduser]['shipper_id']."',
					'".$chkduser."',
					'".$seller_id."',
					'unpaid',
					'".DATETIME24H."',
					'',
					'".$checked_userss[$chkduser]['shipping_cost']."',
					'0',
					'". $parent_invoice_id."')");
				}
				
				
				 
				$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices SET status = 'scheduled',scheduled_date='".DATETIME24H."'
						WHERE invoiceid = '" . intval( $parent_invoice_id). "'");
				
				$parent_invoice = $parent_invoice_id;
				$sqlinc=$ilance->db->query("select combine_project from ".DB_PREFIX."invoices where invoiceid='".$parent_invoice."'");
				if($ilance->db->num_rows($sqlinc)>0)
				{
					while($lineinc=$ilance->db->fetch_array($sqlinc))
					{
						$child_invoice_list=explode(",",$lineinc['combine_project']);
						foreach($child_invoice_list as $invoice_id)
						{
							$ilance->db->query("update ".DB_PREFIX."invoices set status='complete', scheduled_date='".DATETIME24H."' where invoiceid='".$invoice_id."'");
						}
					}
				}
				
				$inv_parnt[] = $parent_invoice;
				}
			}
			
			$invoices_to_pdf = implode(',', $inv_parnt);
			$link='<a href="paypal_invoices_pdf.php?invoice_list='.$invoices_to_pdf.'" target="_blank" style="color:green">Click here to print them All</a>';
			if(count($inv_parnt)>0)
			{
			print_action_success("Checked out invoices, list of invoices &nbsp; ".$invoices_to_pdf."<br>".$link, $ilpage['buyer']);
			exit;
			}else
			{
			print_action_failed("None of the user had any items to checkout", $ilpage['buyer']);
			exit;
			}
			
		}	
	
	}
	
	if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Search'))
	{
		$email_lists=explode("\r\n",trim($ilance->GPC['email_list']));
		$pending_by_users = array();
		$hidden_fields = '';
		foreach($email_lists as $email) 
		{
		$query="SELECT user_id,email,first_name,last_name,username,concat(first_name,' ', last_name) fullname,issalestaxreseller FROM " . DB_PREFIX . "users WHERE  email='".$email."'";
			$result=$ilance->db->query($query);
			if($ilance->db->num_rows($result))
			{
				while($line=$ilance->db->fetch_array($result))
				{
				$user=$line;
				}
			}
		$usrid=$user['user_id'];
		$securekey_hidden=$shipinhiden=$invidhidden =$qtyhidden =$totalhidden =$taxamounthidden = '';
		$submitd_users = $coins_gc = $coins_ebay = $totalamountlistcal = $subtotl = $projects = array();
		$ilance->tax = construct_object('api.tax');	
		$sql_regardlist = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices WHERE user_id = '".$usrid."' AND projectid>0 AND status = 'unpaid' and combine_project='' AND isfvf != 1 AND isif != 1  AND isbuyerfee != 1  AND isenhancementfee != 1");
		if($ilance->db->num_rows($sql_regardlist) > 0)
		{
			$invid = $totqty = array();
			while($res = $ilance->db->fetch_array($sql_regardlist))
			{
				$shipinhiden = $securekey_hidden = $invidhidden = $qtyhidden = $totalhidden=$taxamounthidden = '';
				$invid[] = $res['invoiceid'];
				$show['invoicecancelled'] = 0;
				//suku
				$totqty[]=get_quantity($res['invoiceid'],$res['projectid']);
				$id = $res['invoiceid'];
				$txn = $res['transactionid'];
				$securekey_hidden .= '<input type="hidden" name="invoice_by_user['.$usrid.'][id]" value="' . $id . '" />
										<input type="hidden" name="invoice_by_user['.$usrid.'][txn]" value="' . $txn . '" />';
				$duedate=DATETIME24H;
				$paiddate=DATETIME24H;
				$projects[] = $res['projectid'];
				$taxdetails = $res['istaxable'];
				$show['buyer'] = 0;
				$sql1="SELECT * FROM ".DB_PREFIX."invoices WHERE projectid = '".$res['projectid']."' AND user_id = '".$usrid."' AND isbuyerfee = '1'";
				$buyfee_inv = $ilance->db->query($sql1);									
				if($ilance->db->num_rows($buyfee_inv) > 0)
				{
					$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
					$buyerfee = $res_buyfee['amount'];
				}
				else
				{
					 $buyerfee = 0;
				}
				
				$totalamountlist = $ilance->currency->format(($res['amount'] + $buyerfee), $res['currency_id']);
				$totalamountlist1 = $res['amount'] + $buyerfee ;
				$show['buyer'] = 1;
				
			 
		$amountcal[] = $res['amount'];
		$taxinfolist = $res['taxinfo'];
		$invoicetype = $res['invoicetype'];
		$buyerfeecal[] = $buyerfee;
		$totalamountlistcal[] = $totalamountlist1;
		$res['item_id'] 	 = 	$res['projectid'];
		$res1['itemtitle'] = fetch_coin_table('Title',$res['projectid']);
		if($res['Site_Id'] >0)
		{
			$coins_ebay[] = $res['projectid'];
		}
		else
		{
			$coins_gc[] = $res['projectid'];
		}
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$res['item_id']='<a href="Coin/'.$res['projectid'].'/'.construct_seo_url_name($res1['itemtitle']).'" class="green"> '.$res['item_id'].'</a>';
			$res['itemtitle'] ='<a href="Coin/'.$res['projectid'].'/'.construct_seo_url_name($res1['itemtitle']).'"> '.$res1['itemtitle'].'</a>';
		}
		else
		{
			$res['item_id']='<a href="merch.php?id='.$res['projectid'].'">'.$res1['item_id'].'</a>';
			$res['itemtitle']='<a href="merch.php?id='.$res['projectid'].'">'.$res1['itemtitle'].'</a>';
		}
		$res['finalprice'] = $ilance->currency->format($res['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
	
		$subtotl[] = $res['amount']+$buyerfee;
		$res['buyerfees'] = $buyerfee;
		$res['totallistamount'] =  $totalamountlist;
		$regardlist[] = $res;
	}

			$show['taxes'] = 0;
			
			$qtyhidden = '<input type = "hidden" name="invoice_by_user['.$usrid.'][qtyhidden]" id="qtyhidden" value="'.array_sum($totqty).'">';
			$invidim = implode(',',$invid);
			$invidhidden = '<input type = "hidden" name="invoice_by_user['.$usrid.'][invhidden]" id="invhidden" value="'.$invidim.'">';
			$amounttotal = array_sum($totalamountlistcal);
			$amount = $ilance->currency->format(array_sum($totalamountlistcal),$ilconfig['globalserverlocale_defaultcurrency']);
			$_SESSION['ilancedata']['user']['totalamount']=array_sum($totalamountlistcal);
			$sales_tax_reseller = $user['issalestaxreseller'];	
			 
			if ($ilance->tax->is_taxable($usrid, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal AND $sales_tax_reseller!='1')
             {			 	
				$state = fetch_user('state',$usrid);			
				$taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($usrid, $amounttotal, 'buynow', 0).'%, '.$state.')';
				$taxinfonew = $ilance->tax->fetch_taxdetails($usrid, $amounttotal, 'buynow', 0);
				$taxamount1 = $ilance->tax->fetch_amount($usrid, $amounttotal, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
			}
			else if ($ilance->tax->is_taxable($usrid, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
			{				
				$state = fetch_user('state',$usrid);
				 $taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
				$taxamount1 = $ilance->tax->fetch_amount($usrid, 0, 'buynow', 0);
				$taxinfonew = 0.00;
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
			}
			else 
			{						
				 $taxinfo = 'Sales Tax Not Applicable (Out of State)';
				$taxamount1 = $ilance->tax->fetch_amount($usrid, 0, 'buynow', 0);
				$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
				$show['taxes'] = 1;
				$taxinfonew = 0.00;
			}
			$buyerfe = array_sum($buyerfeecal);
			$buyerfee = $ilance->currency->format($buyerfe,$ilconfig['globalserverlocale_defaultcurrency']);
			$taxamount1=empty($taxamount1)?"0":$taxamount1;
			$taxamounthidden = '<input type = "hidden" name="invoice_by_user['.$usrid.'][taxhidden]" id="taxhidden" value="'.$taxamount1.'">
			                    <input type = "hidden" name="invoice_by_user['.$usrid.'][taxinfonew]" id="taxinfonew" value="'.$taxinfonew.'">
								<input type = "hidden" name="invoice_by_user['.$usrid.'][taxhidden1]" id="taxhidden1" value="'.$taxamount1.'">
								<input type = "hidden" name="invoice_by_user['.$usrid.'][taxshipcal]" id="taxshipcal" value="0">';
			if ($taxamount1 > 0)
			{			
				$totalamount = $ilance->currency->format(($amounttotal + $taxamount1), $ilconfig['globalserverlocale_defaultcurrency']);
				$totalamountnew = $amounttotal + $taxamount1;
				$totalhidden = '<input type = "hidden" name="invoice_by_user['.$usrid.'][taxhiddenyes]" id="taxhiddenyes" value="1">';			
				$show['taxes'] = 1;
			}
			else
			{
				
				$totalamount = $ilance->currency->format($amounttotal, $ilconfig['globalserverlocale_defaultcurrency']);
				$totalamountnew = $amounttotal;
				$totalhidden = '<input type = "hidden" name="invoice_by_user['.$usrid.'][taxhiddenyes]" id="taxhiddenyes" value="0">';	
			}
			
			$totalhidden.= '<input type = "hidden" name="invoice_by_user['.$usrid.'][totalhidden_base]" id="totalhidden_base"  value="'.$amounttotal.'">';
			 
		$totlamt = array_sum($totalamountlistcal);
		$countyid = fetch_user('country', $usrid);
		$curncyid = 1;
		$shippment_nethod_pulldown = print_shippment_nethod_pulldown($projects,0,'shipper_id','return change_shipper();',array_sum($totqty),$countyid,$usrid,$curncyid,$totlamt);
	
		$first_shipment=check_if_first_shipment($projects,0,'shipper_id','return change_shipper();',array_sum($totqty),$countyid,$usrid,$curncyid,$totlamt);
		$first_shipment_text=$first_shipment==true?'<span class="green"><br>first auction purchase</span>':'';
		$shiping_amnt = 0;
		if($first_shipment==false)
		{
			$shiping_amnt = $shippment_nethod_pulldown['base_cost']+($shippment_nethod_pulldown['aded_cost']*array_sum($totqty));
		}
		$shipper_id = $shippment_nethod_pulldown['shipper_id'];
		$totalhid_amnt = $totalamountnew+$shiping_amnt;
		$totalhidden .= '<input type = "hidden" name="invoice_by_user['.$usrid.'][totalhidden]" id="totalhidden" value="'.$totalhid_amnt.'">';
		$shipinhiden = '<input id="shipping_cost" type="hidden" value="'.$shiping_amnt.'" name="invoice_by_user['.$usrid.'][shipping_cost]">
						<input id="shipper_id" type="hidden" value="'.$shipper_id.'" name="invoice_by_user['.$usrid.'][shipper_id]">';
		
		$submitd_users['user_id'] = $usrid;
		$submitd_users['username'] = $user['username'];
		$submitd_users['email'] = $user['email'];
		$submitd_users['shipng_amnt'] = $ilance->currency->format($shiping_amnt,$ilconfig['globalserverlocale_defaultcurrency']).$first_shipment_text;
		
		$submitd_users['taxamount'] = $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
		$submitd_users['GC_coins'] = implode(', ', $coins_gc);
		$submitd_users['Ebay_coins'] = implode(', ', $coins_ebay); 
		$submitd_users['totallistamount'] = $ilance->currency->format($totalhid_amnt,$ilconfig['globalserverlocale_defaultcurrency']);//round($totalamountnew, 2); 
		$submitd_users['sub_total'] = $ilance->currency->format(array_sum($subtotl),$ilconfig['globalserverlocale_defaultcurrency']);
		
		$pending_by_users[] =  $submitd_users;
		 
	}else
	{
		$empty_row['username'] = $user['username'];
		$empty_row['email'] = $user['email'];
		$empty_row['GC_coins'] = '-';		
		$empty_row['Ebay_coins'] = '-';		
		$empty_row['sub_total'] = '-';		
		$empty_row['taxamount'] = '-';		
		$empty_row['shipng_amnt'] = '-';		
		$empty_row['totallistamount'] = '-';		
		$pending_by_users[] =  $empty_row;;
	}
	
	$hidden_fields .= stripslashes($shipinhiden.$securekey_hidden.$invidhidden.$qtyhidden.$totalhidden.$taxamounthidden);
	}
		$show['user_pending_invoices'] = true;
	}
	
	//default page
	$show['change_bid_form'] = 'holding';
	$pprint_array = array('hidden_fields','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

	$ilance->template->fetch('main', 'pending_invoices_to_checkout.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('pending_by_users'));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function check_if_first_shipment($projects,$selected,$name,$onchage_script,$totqty=0,$countyid, $usrid,$currencyid=1,$totlamt)
{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	$base_cost = $aded_cost = $shiprid = 0;
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");

	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $countyid==500 and !$only_buynow)
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$usrid."' AND status='paid'");
		if($ilance->db->num_rows($sql)==0)
		{
		Echo $usrid;
			$first_shipment=true;
		}
	}
	return $first_shipment;
}
function print_shippment_nethod_pulldown($projects,$selected,$name,$onchage_script,$totqty=0,$countyid, $usrid,$currencyid=1,$totlamt)
{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	$base_cost = $aded_cost = $shiprid = 0;
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");

	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $countyid==500 and !$only_buynow)
	{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$usrid."' AND status='paid'");
		if($ilance->db->num_rows($sql)==0)
		{
			$first_shipment=true;
		}
	}
	//karthik start on Apr 12
	
	//shipping for INTERNATIONAL CLIENTS 			
	if($countyid!=500)
	{			
	///invoice  over $10,000	
		if( $totlamt >= '10000.00')
		{
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
			if($ilance->db->num_rows($sql))
			{
				$html='';
				$script='';
				while($line=$ilance->db->fetch_array($sql))
				{
					if($totqty>$line['maxitem_count'])
					{
						$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
						$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
					}
					 $base_cost = $line['basefee'];
					 $aded_cost = $line['addedfee'];
					 $shiprid = $line['shipperid'];

				}
			 }
		}	 
		else
		{	
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
			if($ilance->db->num_rows($sql))
			{
				$html='';
				$script='';
				while($line=$ilance->db->fetch_array($sql))
				{
					$selected='22';
					if($line['shipperid']==$selected)
					{
						if($totqty>$line['maxitem_count'])
						{
							$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
							$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
						}
						 $script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
						 $script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
						 $base_cost = $line['basefee'];
						 $aded_cost = $line['addedfee'];
						 $shiprid = $line['shipperid'];
					}
					else
					{
						if($totqty>$line['maxitem_count'])
						{
							$script.='international_extra_morethen_n_coins='.$line['addedfee_above_maxitem_count'].';';
							$international_extra_morethen_n_coins_text='&nbsp;&nbsp;plus' .'&nbsp;&nbsp;'.$ilance->currency->format($line['addedfee_above_maxitem_count'], $currencyid).'&nbsp;';
						}
						$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
						$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
					}
				}
			}
		}
		
	}
	else
	{
       //invoice  over $10,000	
	   if( $totlamt > '10000.00')
	   {
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
		  if($ilance->db->num_rows($sql))
		  {
				$html='';
				$script='';
				while($line=$ilance->db->fetch_array($sql))
				{
					$selected_text='selected="selected"';
					if($first_shipment)
					{
					$script.='shippers_base_cost['.$line['shipperid'].']=0;';
					$script.='shippers_added_cost['.$line['shipperid'].']=0;';	
					$base_cost = 0;
					$aded_cost = 0;
					$shiprid = $line['shipperid'];
					}else
					{
					$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
					$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
					$base_cost = $line['basefee'];
					$aded_cost = $line['addedfee'];
					$shiprid = $line['shipperid'];
					}
	             }
		     }
		  }  
	       //invoice  over $2,000,
		   else if( $totlamt > '1000.00')
	      {
		       //may2 new change add order by basefee asc
			   //$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic=1 and visible=1 order by basefee asc");
			    $sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('27') and visible=1 order by basefee asc");
				if($ilance->db->num_rows($sql))
				{
					$html='';
					$script='';
					while($line=$ilance->db->fetch_array($sql))
					{
						$selected_text='';
						$selected='27';
						if($line['shipperid']==$selected)
						$selected_text='selected="selected"';
							if($first_shipment and $line['shipperid']=='27')
							{
							$script.='shippers_base_cost['.$line['shipperid'].']=0;';
							$script.='shippers_added_cost['.$line['shipperid'].']=0;';	
							$base_cost = 0;
							$aded_cost = 0;
							$shiprid = $line['shipperid'];
							}else
							{
							$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
							$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
							$base_cost = $line['basefee'];
							$aded_cost = $line['addedfee'];
							$shiprid = $line['shipperid'];
							}
					  }
				  }
		}  
		else
		{	
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
				$html='';
				$script='';
				while($line=$ilance->db->fetch_array($sql))
				{
					$selected='26';
					if($line['shipperid']==$selected)
					  $selected_text='selected="selected"';
					 else
						$selected_text='';
					if($first_shipment AND $line['shipperid']=='26' AND !$only_buynow)
					{
						 $script.='shippers_base_cost['.$line['shipperid'].']=0;';
						 $script.='shippers_added_cost['.$line['shipperid'].']=0;';	
						 $base_cost = 0;
						 $aded_cost = 0;
						 $shiprid = $line['shipperid'];
					}	
					else
					{
						if($line['shipperid']=='26')
						{
							 $base_cost = $line['basefee'];
							 $aded_cost = $line['addedfee'];
							 $shiprid = $line['shipperid'];
						}
						else
						{
						$script.='shippers_base_cost['.$line['shipperid'].']='.$line['basefee'].';';
						$script.='shippers_added_cost['.$line['shipperid'].']='.$line['addedfee'].';';
						}
					}
					
				}//exit;
			}
		}
			if($first_shipment)
			$free_announce='<div id="free_announce"><span class="green">Standard shipping is free for your first auction purchase (U.S. only)</span></div>';

	}
	$pulldown['base_cost']=$base_cost;
	$pulldown['aded_cost']=$aded_cost;
	$pulldown['shipper_id']= $shiprid;
	return $pulldown;
}
function get_quantity($invoiceid,$projectid)
{
global $ilance;
$nocoins=1;
$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders WHERE invoiceid = '".$invoiceid."'");
	if($ilance->db->num_rows($buy)>0)
	{
		$resbuy = $ilance->db->fetch_array($buy);
		$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins WHERE coin_id = '".$projectid."'");
		$temp=$ilance->db->fetch_array($bids);						
		$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
		$nocoins = $resbuy['qty']*$coin_no_in_set;
	}
	else
	{
		$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
								WHERE coin_id = '".$projectid."'");
					$temp=$ilance->db->fetch_array($bids);		
		$nocoins = empty($temp['nocoin'])?1:intval($temp['nocoin']);
	}
				
return $nocoins;
}
?>