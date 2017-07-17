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
$navcrumb = array($ilpage['buyers'] => $ilcrumbs[$ilpage['buyers']]);

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
$show['paid_not_shipped_invoices']='false';
$not_shipped_arr='';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

	//Tamil
	$selected_invoiceid=($ilance->GPC['searchby']=='invoiceid')?'selected="select"':'';
	$selected_user_id=($ilance->GPC['searchby']=='user_id')?'selected="select"':'';
	$selected_username=($ilance->GPC['searchby']=='username')?'selected="select"':'';
	$selected_email=($ilance->GPC['searchby']=='email')?'selected="select"':'';
	$selected_totalamount=($ilance->GPC['searchby']=='totalamount')?'selected="select"':'';
	$search_by_dropdown='<select name="searchby" style="width:150px" onchange="" >							
							<option value="invoiceid" '.$selected_invoiceid.'>Invoice ID</option>
							<option value="user_id" '.$selected_user_id.'>User ID</option>
							<option value="username" '.$selected_username.'>User Name</option>
							<option value="email" '.$selected_email.'>User Email</option>
							<option value="totalamount" '.$selected_totalamount.'>Amount</option>												
							</select>';	
	$searchkey_value = $ilance->db->escape_string($ilance->GPC['searchkey']);
	//Tamil
 
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='change_shipping' AND isset($ilance->GPC['invoice_id']) AND $ilance->GPC['invoice_id']>0 )
{
   
    $invoicepaidchk=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices WHERE invoiceid='".$ilance->GPC['invoice_id']."' and status!='paid' limit 1");
	 
	$invoicepaid=$ilance->db->fetch_array($invoicepaidchk);
	if($ilance->db->num_rows($invoicepaidchk)>0)
    {
		$shipping_cost=$ilance->db->query("SELECT shipping_cost FROM " . DB_PREFIX . "invoice_projects WHERE final_invoice_id='".$ilance->GPC['invoice_id']."' limit 1");
	   
		$ship_cost=$ilance->db->fetch_array($shipping_cost);
		$old_shipping_cost=$ship_cost['shipping_cost'];
		$new_shipping_cost=$ilance->GPC['change_ship'];
		$ilance->db->query("UPDATE ".DB_PREFIX."invoices SET amount =(amount -'".$old_shipping_cost."'),totalamount = (totalamount -'".$old_shipping_cost."') WHERE invoiceid = '".$ilance->GPC['invoice_id']."'");
		$ilance->db->query("UPDATE ".DB_PREFIX."invoices SET amount =(amount +'".$new_shipping_cost."'),totalamount = (totalamount +'".$new_shipping_cost."') WHERE invoiceid = '".$ilance->GPC['invoice_id']."'");
		$ilance->db->query("UPDATE ".DB_PREFIX."invoice_projects SET shipping_cost = '".$new_shipping_cost."' WHERE final_invoice_id = '".$ilance->GPC['invoice_id']."'");
		print_action_success("Shipping Cost Changed", $ilpage['buyer']);
		exit;
	}
	else
	{
		print_action_failed("Shipping Cost cannot be changed,if Once an invoice is paid in full", $ilpage['buyer']);
		exit;	
	}
   // $new_total = $ship_cost['shipping_cost']-$ilance->GPC['change_ship'];
   // if($new_total>0)
   // {
   // $ilance->db->query("UPDATE ".DB_PREFIX."invoices SET amount =(amount -'".$new_total."'),totalamount = (totalamount -'".$new_total."') WHERE invoiceid = '".$ilance->GPC['invoice_id']."'");
	
	// $ilance->db->query("UPDATE ".DB_PREFIX."invoice_projects SET shipping_cost = '".$ilance->GPC['change_ship']."' WHERE final_invoice_id = '".$ilance->GPC['invoice_id']."'");
	
	// print_action_success("Shipping Cost Changed", $ilpage['buyer']);
	// exit;		
	// }else
	// {
		// print_action_success("Shipping Cost cannot be changed, or it was stopped from being substracted twice", $ilpage['buyer']);
		// exit;	
	// }

}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='update_invoice_payment' AND isset($ilance->GPC['payment_id']) AND $ilance->GPC['payment_id']>0)
{

$query="update " . DB_PREFIX . "invoices set paiddate='".$ilance->GPC['paymentdate']."',paymethod='".$ilance->GPC['account_id']."' WHERE invoiceid = '".$ilance->GPC['payment_id']."'";
$result=$ilance->db->query($query);
 print_action_success("Payment date/Method Changed", $ilpage['buyer']);
	exit;
}
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='edit_invoice_payment' AND isset($ilance->GPC['invoice_id']) AND $ilance->GPC['invoice_id']>0)
{

$query="SELECT *,date(paiddate) as paymentdate  FROM " . DB_PREFIX . "invoices WHERE invoiceid = '".$ilance->GPC['invoice_id']."'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
$payment_id=$line['invoiceid'];
	$paymentdate=$line['paymentdate'];
	$totalamount=$line['totalamount'];
	$paymethod=print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid'],'id="account_id"');
	$paymethod.='<script type="text/javascript">
var account_id = document.getElementById("account_id") ;

for(i=0;i<account_id.length;i++)
{
if(account_id.options[i].value=="'.$line['paymethod'].'")
	index=i
}
account_id.selectedIndex =index;
</script>';
	
}
}else
{
print_action_failed("No such Partial Payment was made", 'javascript:history.back()');
exit;

}

$pprint_array = array('payment_id','paymentdate','totalamount','paymethod','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','series_prevnext1');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'buyers_invoice_payment.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='add_misc_to_invoice' AND isset($ilance->GPC['invoice_id']) AND $ilance->GPC['invoice_id']>0 )
{
     $misc_amount = $ilance->GPC['misc_amount'];
	 
   $ilance->db->query("UPDATE ".DB_PREFIX."invoices SET 
						amount = amount-miscamount+".$misc_amount.",
						totalamount = totalamount-miscamount+".$misc_amount.",
						miscamount='".$misc_amount."',
						misc_date='".DATETIME24H."'
						WHERE invoiceid = '".$ilance->GPC['invoice_id']."'");
	
	print_action_success("Miscellaneous Charges Was Updated Successfully", $ilpage['buyer']);
	exit;
		
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='change_amount' AND isset($ilance->GPC['invoice_id']) AND $ilance->GPC['invoice_id']>0 )
{
   $new_amount = $ilance->GPC['change_amt'];
	 $query="SELECT *  FROM " . DB_PREFIX . "invoices WHERE invoiceid='".$ilance->GPC['invoice_id']."'";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
	while($line=$ilance->db->fetch_array($result))
	{
	$new_pending_amount=abs($new_amount-$line['paid']);
	$ilance->db->query("UPDATE ".DB_PREFIX."invoices SET totalamount = '".$new_amount."',amount='".$new_pending_amount."' WHERE invoiceid = '".$ilance->GPC['invoice_id']."'");
	}
	}
   
	
	print_action_success("Total Amount Was Updated Successfully", $ilpage['buyer']);
	exit;
		
}
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='update_partial_payment' AND isset($ilance->GPC['payment_id']) AND $ilance->GPC['payment_id']>0)
{
$query="update " . DB_PREFIX . "partial_payment set paymentdate='".$ilance->GPC['paymentdate']."',paymethod='".$ilance->GPC['account_id']."' WHERE id = '".$ilance->GPC['payment_id']."'";
$result=$ilance->db->query($query);
print_action_success("Partial Payment changed", $ilpage['buyer']);
exit;	
}


if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='edit_partial_payment' AND isset($ilance->GPC['payment_id']) AND $ilance->GPC['payment_id']>0)
{

$query="SELECT *,date(paymentdate) as paymentdate  FROM " . DB_PREFIX . "partial_payment WHERE id = '".$ilance->GPC['payment_id']."'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
$payment_id=$line['id'];
	$paymentdate=$line['paymentdate'];
	$partial_amount=$line['partial_amount'];
	$paymethod=print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid'],'id="account_id"');
	$paymethod.='<script type="text/javascript">
var account_id = document.getElementById("account_id") ;

for(i=0;i<account_id.length;i++)
{
if(account_id.options[i].value=="'.$line['paymethod'].'")
	index=i
}
account_id.selectedIndex =index;
</script>';
	
}
}else
{
print_action_failed("No such Partial Payment was made", 'javascript:history.back()');
exit;

}

$pprint_array = array('payment_id','paymentdate','partial_amount','paymethod','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','series_prevnext1');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'buyers_edit_partial_payment.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='_do-invoice-mark-as-partially_paid' AND isset($ilance->GPC['invoice_id']) AND $ilance->GPC['invoice_id']>0 AND isset($ilance->GPC['partial_amount']) AND isset($ilance->GPC['account_id']) )
{
if($ilance->GPC['partial_amount']>0)
{
$query="SELECT *  FROM " . DB_PREFIX . "invoices WHERE invoiceid = '".$ilance->GPC['invoice_id']."' and amount+taxamount+miscamount>=".$ilance->GPC['partial_amount']."";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)>0)
{
while($line=$ilance->db->fetch_array($result))
{

$sql=$ilance->db->query("insert into ". DB_PREFIX ."partial_payment (invoiceid,paid,amount,totalamount,paymentdate,paymethod,partial_amount) values (
						'".$ilance->GPC['invoice_id']."',
						'".$line['paid']."',
						'".$line['amount']."',
						'".$line['totalamount']."',
						'".DATETIME24H."',
						'".$ilance->GPC['account_id']."',
						'".$ilance->GPC['partial_amount']."')");

	$sql=$ilance->db->query("update ". DB_PREFIX ."invoices set amount=amount-'".$ilance->GPC['partial_amount']."',paid=paid+'".$ilance->GPC['partial_amount']."' where invoiceid='".$ilance->GPC['invoice_id']."'");
	//suku
	$ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET status = 'scheduled',scheduled_date='".DATETIME24H."' WHERE invoiceid = '" . intval($ilance->GPC['invoice_id']). "' and scheduled_date='0000-00-00 00:00:00'");
	close_child_invoice($ilance->GPC['invoice_id']);
	
	//if(floatval($line['totalamount'])==floatval($line['paid'])+floatval($ilance->GPC['partial_amount']))
	if($line['totalamount']-($line['paid']+$ilance->GPC['partial_amount'])<.00001)
	{
		combine_invoice_payment($ilance->GPC['invoice_id'],$ilance->GPC['account_id']);
		print_action_success("The whole invoice is thus Marked As Paid", $ilpage['buyer']);
		exit;
	}
	print_action_success("Invoice Status Marked As Partially Paid", $ilpage['buyer']);
	exit;
	
}
}else
{
print_action_failed("Partial payment amount cannot be higher then pending amount", 'javascript:history.back()');
	exit;
}

}else
{
	print_action_failed("Partial payment amount cannot be blank or zero", 'javascript:history.back()');
	exit;
}
}


if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='_do-invoice-mark-as-paid' AND isset($ilance->GPC['invoice_id']) AND $ilance->GPC['invoice_id']>0 AND isset($ilance->GPC['account_id']))
{
combine_invoice_payment($ilance->GPC['invoice_id'],$ilance->GPC['account_id']);
/*$sql=$ilance->db->query("update ". DB_PREFIX ."invoices set status='paid',paiddate='".DATETIME24H."',paymethod='".$ilance->GPC['account_id']."' where invoiceid='".$ilance->GPC['invoice_id']."'");*/
	print_action_success("Invoice Status Marked As Paid", $ilpage['buyer']);
	exit;
}
//exit;
if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_detail_invoice' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > '0')
{
$invoice_id=$ilance->GPC['id'];
$invoiceuser_id=$ilance->GPC['user_id'];
$sql=$ilance->db->query("select user_id,combine_project,miscamount,taxamount,taxinfo,amount,paid,totalamount,status from ".DB_PREFIX."invoices where invoiceid='".$invoice_id."' and user_id ='".$invoiceuser_id."'");
while($line=$ilance->db->fetch_array($sql))
{


$invoice_list=explode(",",$line['combine_project']);
$buyer_id=$line['user_id'];
$total_amt_new = $line['totalamount'];
//bug3826
//$totalamount=$ilance->currency->format($line['amount']+$line['paid']+$line['taxamount']);
$taxinfo=$line['taxinfo'];
$taxamount=$ilance->currency->format($line['taxamount']);
$paid=$ilance->currency->format($line['paid']);
$show['add_misc']=false;
if($line['status']!='paid')$show['add_misc']=true;
$show['partial_payment']=false;
if($line['amount']+$line['taxamount']>0)$show['partial_payment']=true;
if($line['status']!='paid')
$amount=$ilance->currency->format($line['amount']+$line['taxamount']);
else
$amount=$ilance->currency->format($line['amount']);
$miscamount=$line['miscamount'];
$miscamount_formatted=$ilance->currency->format($line['miscamount']);
if($line['paid']>0)
{
	$query="SELECT *  FROM ".DB_PREFIX."partial_payment WHERE invoiceid = '".$ilance->GPC['id']."' ORDER BY paymentdate asc";
	$result=$ilance->db->query($query);
	
	$totalamount=$ilance->currency->format($line['amount']+$line['paid']); //+$line['taxamount']
	if($ilance->db->num_rows($result))
	{
		$show['partial_payment_rows']=true;
		while($line=$ilance->db->fetch_array($result))
		{
			$line['payment_date']=print_date($line['paymentdate']);
			$line['edit']='<a href="buyers.php?cmd=edit_partial_payment&payment_id='.$line['id'].'"><img src="../images/gc/icons/pencil.gif"/></a>';
			$partial_payment_rows[]= $line;
		}
	}else
	{
	$show['partial_payment_rows']=false;
	}
}
else
{
	$totalamount=$ilance->currency->format($line['amount']+$line['paid']+$line['taxamount']);
}
}

if(count($invoice_list))
{
foreach($invoice_list as $each_invoice)
{
$sql=$ilance->db->query("select * from ".DB_PREFIX."invoices where invoiceid='".$each_invoice."' and user_id ='".$invoiceuser_id."'");
while($line1=$ilance->db->fetch_array($sql))
	{
		
		$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
											WHERE invoiceid = '".$line1['invoiceid']."'
											");
		if($ilance->db->num_rows($buy)>0)
		{
			$resbuy = $ilance->db->fetch_array($buy);
			$line1['qty'] = $resbuy['qty'];
			$line1['type']='Buynow';
		}
		else
		{
			$line1['type']='Auction';	
			$line1['qty']=1;
		}
		
		$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
								WHERE projectid = '".$line1['projectid']."'								
									AND isbuyerfee = '1'  and user_id ='".$invoiceuser_id."'" );
					if($ilance->db->num_rows($buyfee_inv) > 0)
					{
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);						
						$buyerfee1 = $res_buyfee['amount'];
						$line1['buyerfee']= $ilance->currency->format($buyerfee1);
						$buyerfees[] = $res_buyfee['amount'];
						
					}
					else
					{					
						$buyerfee1 = 0;
						$buyerfees[] = 0;
						$line1['buyerfee']= $ilance->currency->format($buyerfee1);						
					}

		
		//item details
		//new change apr25 totalamount 2 amount
		if($ilance->GPC['paidstatus'] == 'paid')
		$show['status_check'] = 'yes';
		else
		$show['status_check'] = 'no';
		
		$line1['totalamount']=$ilance->currency->format($line1['totalamount']);
		$line1['newtotal']=$ilance->currency->format($line1['amount'] + $buyerfee1);
		$line1['itemid']= $line1['projectid'];
		$project_id_ship[] =  $line1['projectid'];
		$line1['item']='<a href="consignments.php?cmd=add_single_coin&subcmd=update_edit&coin_id='.$line1['projectid'].'">'.$ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '".$line1['projectid']."'", "Title").'</a>';
		$invoicelist[]=$line1;
	}
}	
$sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($buyer_id) . "'", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
            $res = $ilance->db->fetch_array($sql);
			if(!empty($res['address']) AND !empty($res['address2']))
			{
				$res['address'] = $res['address'] .', '. $res['address2'];
			}
			else
			{
				$res['address'] = $res['address'];
			}
            $res['country'] = print_user_country($buyer_id);
			$buyerdetail[]=  $res;
        }
$sql=$ilance->db->query("select * from " . DB_PREFIX . "invoice_projects where final_invoice_id='".$invoice_id."'");
//new change
$line2=$ilance->db->fetch_array($sql);
$ship_cost_new = $line2['shipping_cost'];
$shipper_id_new = $line2['shipper_id']; 
	
if($ilance->db->num_rows($sql))
{
while($line1=$ilance->db->fetch_array($sql))
{
	$shipper_id=$line1['shipper_id'];
	$discount=$line1['disount_val'];
}
}else
{
$shipper_id==22;
}
$coin_count=$ilance->db->num_rows($sql);
/*$sql_shipper_detail=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='".$shipper_id_new."'");
if($ilance->db->num_rows($sql_shipper_detail))
{
	while($line_shipper=$ilance->db->fetch_array($sql_shipper_detail))
	{
		$shipper_title="<strong>".$line_shipper['title']."</strong>";
		$base_cost=$line_shipper['basefee'];
		$added_cost=$line_shipper['addedfee'];
	}
}
$test = ($base_cost)+(($coin_count)*$added_cost);
$shippping_cost=$ilance->currency->format(($base_cost)+(($coin_count)*$added_cost));
if($coin_count>1)
$shipping_cost_detail=" 1 Coin * ".$base_cost." + ".($coin_count)." Coins * ".$added_cost;
else
$shipping_cost_detail=" 1 Coin  ".$base_cost;
$shipper_title.=$shipping_cost_detail;
$payment_methods=print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid']);*/
}/*else
{
	
}*/
//new chnage apr25 
$sql_shipper_detail=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='".$shipper_id_new."'");
if($ilance->db->num_rows($sql_shipper_detail))
{
	$line_shipper=$ilance->db->fetch_array($sql_shipper_detail);
	
	$shipper_title="<strong>".$line_shipper['title']."</strong>";
		
}
/*$upgrade_shipper=is_shipper_upgraded($buyer_id	,$shipper_id_new ,$total_amt_new);
	 if($upgrade_shipper==true)
	 {
	 echo "upgraded ".$shipper_title;
	 }else
	 {
	 echo 'normal';
	 }*/
	
// murugan added on mar 07
$new_ship_project = implode(",",$project_id_ship);
	if ($new_ship_project != '')
	{
		$ship_sel = $ilance->db->query("SELECT track_no,shipment_date FROM ".DB_PREFIX."shippnig_details WHERE item_id IN (".$new_ship_project.") AND track_no != '' AND buyer_id = '".$buyer_id."'GROUP BY track_no ORDER BY track_no DESC");
		if($ilance->db->num_rows($ship_sel) > 0 && $ilance->db->num_rows($ship_sel) == 1)
		{
			
			$shipingarr = $ilance->db->fetch_array($ship_sel);
			$track_no = $shipingarr['track_no'];
			$ship_date = $shipingarr['shipment_date'];
			
		}
		else if ($ilance->db->num_rows($ship_sel) > 1)
		{
			while($shipingarr = $ilance->db->fetch_array($ship_sel))
			{
				$track_no1[] = $shipingarr['track_no'];
				$ship_date1[] = $shipingarr['shipment_date'];
			}
			$track_no = implode(",",$track_no1);
			$ship_date = implode(",",$ship_date1);
		}
		else
		{
		$track_no = '-';
		$ship_date = '-';
		}
	}
// mar 07 end
$shippping_cost=$ilance->currency->format($ship_cost_new);
$payment_methods=print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid']);
$buyerfee=$ilance->currency->format(array_sum($buyerfees));

// murugan added on mar 13
/*$sqlamt = $ilance->db->query("
						SELECT SUM(amount) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE status = 'unpaid'
						AND isfvf != 1
						AND isif != 1 
						AND isenhancementfee != 1
						AND isbuyerfee != 1
						AND NOT combine_project 						
						AND user_id = '".$buyer_id."'            
						", 0, null, __FILE__, __LINE__);
						if($ilance->db->num_rows($sqlamt)>0)
						{						
							$resamt = $ilance->db->fetch_array($sqlamt);
							$intunpaid = $resamt['amount'];
						}
						else
						{
							$intunpaid = 0;
						}
				$ilance->tax = construct_object('api.tax');
 				$sales_tax_reseller = fetch_user('issalestaxreseller',$buyer_id);	
				$invoicetype = 'escrow';
				if ($ilance->tax->is_taxable($buyer_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $intunpaid AND $sales_tax_reseller!='1')
           		{	
					$taxamount1 = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $amounttotal, 'buynow', 0);
				}
				$newunpaid = $intunpaid + $taxamount1;*/
				
				$sqlinv = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
						WHERE status = 'unpaid'
						AND isfvf != 1
						AND isif != 1 
						AND isenhancementfee != 1
						AND isbuyerfee != 1 
						AND not combine_project						
						AND user_id = '".$buyer_id."'								   
				", 0, null, __FILE__, __LINE__);
				 $buyerfee1 = '';
				while($resinv = $ilance->db->fetch_array($sqlinv))
				{	
						  
						$buyfee_inv = $ilance->db->query("SELECT SUM(amount) AS buyeramount FROM ".DB_PREFIX."invoices 
												WHERE projectid = '".$resinv['projectid']."'
												AND user_id = '".$buyer_id."'
												AND isbuyerfee = '1'");
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						//echo '<br>'.$resinv['projectid'].'--'.$res['user_id'].'---'.$res_buyfee['buyeramount'];
						$buyerfee1 .= $res_buyfee['buyeramount'];				
						$buyerfee1 .= '|';				   
			   }
			  
			   $buyerfeeuser = explode('|',$buyerfee1);		   
				$newbuyer = array_sum($buyerfeeuser);			
				
			   $sqlamt = $ilance->db->query("
						SELECT SUM(amount) AS amount
						FROM " . DB_PREFIX . "invoices
						WHERE status = 'unpaid'
						AND isfvf != 1
						AND isif != 1 
						AND isenhancementfee != 1
						AND isbuyerfee != 1 
						AND not combine_project						
						AND user_id = '".$buyer_id."'            
						", 0, null, __FILE__, __LINE__);
						
						$resamt = $ilance->db->fetch_array($sqlamt);
						
						$totalamount11 = 	$resamt['amount'] + $newbuyer;
						$ilance->tax = construct_object('api.tax');
 						$sales_tax_reseller = fetch_user('issalestaxreseller',$buyer_id);	
						$invoicetype = 'escrow';
					if ($ilance->tax->is_taxable($buyer_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $totalamount11 AND $sales_tax_reseller!='1')
						{	
							$taxamount1 = $ilance->tax->fetch_amount($buyer_id, $totalamount11, 'buynow', 0);
						}
						$newunpaid = $totalamount11 + $taxamount1;	
						$pending_inv=$ilance->currency->format($newunpaid);

$link='print_invoice.php?id='.$ilance->GPC['id'];
$link2='print_invoice2.php?id='.$ilance->GPC['id'];
	$pprint_array = array('miscamount_formatted','miscamount','pending_inv','track_no','ship_date','total_amt_new','ship_cost_new','buyerfee','discount','invoice_id','payment_methods','taxamount','taxinfo','amount','paid','totalamount','shipping_cost_detail','shippping_cost','added_cost','base_cost','shipper_title','invoice_id','series_prevnext','daylist','monthlist','yearlist','searchprevnext','hiddenid','hiddendo','pay_first_name','pay_last_name','pay_username','pay_email','pay_address','pay_phone','pay_invoice_id','pay_amount','payment_pulldown','reportfromrange','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','link','link2');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'buyers_invoice.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('invoicelist','buyerdetail','partial_payment_rows'));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
 
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'paid_not_shipped'){
	
	$show['paid_not_shipped_invoices']='true';
	$invoice_paid_to=$ilance->GPC['invoice_paid_to'];
	$invoice_paid_from=$ilance->GPC['invoice_paid_from'];
	
	$not_shipped_sql_1 = $ilance->db->query("select i.invoiceid,i.combine_project,i.totalamount,DATE_FORMAT(i.paiddate,'%m-%d-%Y') as paid_date,u.first_name,u.last_name,u.email,u.address,u.address2,u.city,u.state,u.zip_code,u.country,u.phone
											from ".DB_PREFIX."invoices i
											join ".DB_PREFIX."users u on u.user_id=i.user_id
											where i.status='paid'
											and date(i.paiddate) between '".$ilance->GPC['invoice_paid_from']."' and '".$ilance->GPC['invoice_paid_to']."' 
											and i.combine_project !=''
											order by i.paiddate asc");
	
	while($not_shipped_res_1=$ilance->db->fetch_array($not_shipped_sql_1)){
	
		
		$paid_item_arr=explode(",",$not_shipped_res_1['combine_project']);
		$one_paid_item=$paid_item_arr[0];
		
											
		$not_shipped_sql = $ilance->db->query("select s.coin_id from
".DB_PREFIX."invoices i left join
".DB_PREFIX."shippnig_details s  on s.coin_id=i.projectid
where s.track_no !='' and i.invoiceid='".$one_paid_item."'
											");
		if($ilance->db->num_rows($not_shipped_sql)==0){
		
			$not_shipped['invoice_id']='<a href="buyers.php?subcmd=_detail_invoice&paidstatus=paid&amp;id='.$not_shipped_res_1['invoiceid'].'"">'.$not_shipped_res_1['invoiceid'].'</a>';			
			$not_shipped['amount']=$not_shipped_res_1['totalamount'];		
			$not_shipped['paid_date']=$not_shipped_res_1['paid_date'];
			$not_shipped['username']=$not_shipped_res_1['first_name'].' '.$not_shipped_res_1['last_name'];
			$not_shipped['email']=$not_shipped_res_1['email'];
			$not_shipped['address']=$not_shipped_res_1['address'];
			$not_shipped['address2']=($not_shipped_res_1['address2'] != '')?$not_shipped_res_1['address2'].'<br>'.$not_shipped_res_1['city']:$not_shipped_res_1['city'];
			$not_shipped['state']=$not_shipped_res_1['state'];
			$not_shipped['zip_code']=$not_shipped_res_1['zip_code'];
			$country_name=print_user_country($not_shipped_res_1['country']);
			$not_shipped['country']=$country_name=='Unknown'?'':$country_name;
			$not_shipped_arr[]=$not_shipped;
		}	
		
	}
	
	
}

 if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{

		$startDate = print_array_to_datetime($ilance->GPC['range_start']);
		$startDate = substr($startDate, 0, -9);
		
		//karthik on jun20 for Search based on Username and email
		if($ilance->GPC['searchby']=='username' or $ilance->GPC['searchby']=='email')
		{
		     if($ilance->GPC['searchkey'] != '')
			 {
				$searchkey_value = $ilance->db->escape_string($ilance->GPC['searchkey']);
			 }
			 $sql=$ilance->db->query("select user_id from ".DB_PREFIX."users where ".$ilance->db->escape_string($ilance->GPC['searchby'])." = '".$ilance->db->escape_string($ilance->GPC['searchkey'])."' ", 0, null, __FILE__, __LINE__);
			  if($ilance->db->num_rows($sql) > 0)
				{	
					 $res = $ilance->db->fetch_array($sql);
					 $ilance->GPC['searchby'] = 'user_id';
					 $ilance->GPC['searchkey']=$res['user_id'];
				}
				else
				{
					 $ilance->GPC['searchby'] = 'user_id';
					 $ilance->GPC['searchkey']='1';
				}
			 
		}	 
			 
		
		$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		$endDate = substr($endDate, 0, -9);
		$sql2.="AND (createdate <= '" . $endDate . "' AND createdate >= '" . $startDate . "')";
		
		if($ilance->GPC['searchby']=='totalamount' ){
		
			 if($ilance->GPC['searchkey'] != '')
				 {
					 $searchkey_value = $ilance->db->escape_string($ilance->GPC['searchkey']);
				 }
			$sql2="AND ".$ilance->GPC['searchby']." = ".$ilance->GPC['searchkey']." ";
			 $show['all_invoices']=true;
		}
		else{
			if(!empty($ilance->GPC['searchkey']))
			$sql2="AND ".$ilance->GPC['searchby']." = '".$ilance->GPC['searchkey']."' ";
			$show['all_invoices']=true;
		}
		
        
		
	}
	if(isset($ilance->GPC['order']))
	{
		if(isset($_SESSION['sort_order']) and $_SESSION['sort_order']=='desc')
			$_SESSION['sort_order']='';
		else
			$_SESSION['sort_order']='desc';
	}
	if(isset($ilance->GPC['invoice_type']))
	$show_invoice_type=$ilance->GPC['invoice_type'];
	else if(isset($_SESSION['invoice_type']))
	{
	$show_invoice_type=$_SESSION['invoice_type'];	
	}else
	{
		$show_invoice_type='all';	
	}
	$_SESSION['invoice_type']=$show_invoice_type;
	
	if($show_invoice_type != 'all')
	$sql2= "and status='". $show_invoice_type ."' ";
	$sql2.= "order by invoiceid desc";
	
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
$ilance->GPC['series_page'] = (!isset($ilance->GPC['series_page']) OR isset($ilance->GPC['series_page']) AND $ilance->GPC['series_page'] <= 0) ? 1 : intval($ilance->GPC['series_page']);
$counter = ($ilance->GPC['series_page'] - 1) * $ilance->GPC['pp'];
if($ilance->GPC['subcmd'] == 'search')
$scriptpageprevnext="buyers.php?cmd=buyer&subcmd=search&searchby=".$ilance->GPC['searchby']."&searchkey=".$ilance->GPC['searchkey']."";
else
$scriptpageprevnext="buyers.php?";
$second_level1 = $ilance->db->query("select * from ".DB_PREFIX."invoices where combine_project ".$sql2 );
$second_level_number=(int)$ilance->db->num_rows($second_level1);
$final_query1="select * from ".DB_PREFIX."invoices where combine_project ".$sql2."  LIMIT ". (($ilance->GPC['series_page'] - 1) * 50) . "," . 50;
		$sql=$ilance->db->query($final_query1, 0, null, __FILE__, __LINE__) ;
			if($ilance->db->num_rows($sql) > 0)
		{
		   while($res = $ilance->db->fetch_array($sql))
		   {	
			 if($res['status'] == 'paid')
			 {
			   $res['status'] = ucfirst(strtolower($res['status']));
			   //new changes
			   $status_paid = $res['status'];
			 }
			if($res['status'] != 'Paid')
			 {
			 if($res['paid']==0)
			  $res['status'] = '<a href='.$ilpage['buyers']. '?cmd=buyer&amp;subcmd=update&amp;invoiceid=' . intval($res['invoiceid']) . '&amp;user_id='.intval($res['user_id']).'>Unpaid</a>';
			else
			  $res['status'] = '<a href='.$ilpage['buyers']. '?cmd=buyer&amp;subcmd=update&amp;invoiceid=' . intval($res['invoiceid']) . '&amp;user_id='.intval($res['user_id']).'>Partially Paid</a>';
			  
			  $status_paid = 'Unpaid';
			 }
			 // murugan changes on jan 19
			 //'<a href="'.$ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.$res['user_id'].'"">'.fetch_user('username', $res['user_id']).'</a>';
			 //karthik may12
			 $user_sql=$ilance->db->query("select first_name,last_name,username from ".DB_PREFIX."users where
			 user_id=".$res['user_id']."", 0, null, __FILE__, __LINE__) ;
			 $user_res=$ilance->db->fetch_array($user_sql);
			 $res['first_name'] = $user_res['first_name'];
			 $res['last_name'] = $user_res['last_name'];
			 
			 $res['user_name'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$res['user_id'].'"">'.$user_res['username'].'</a>';
			 //new change apr25 new variable added in line
			  $res['details']='<a href="buyers.php?subcmd=_detail_invoice&user_id='.$res['user_id'].'&paidstatus='.$status_paid.'&amp;id='.$res['invoiceid'].'"">Items</a>';
			 $res['print']='<a href="print_invoice2.php?id='.$res['invoiceid'].'"">Print</a>';
			 $res['paymethod']=ucfirst(strtolower($res['paymethod']));
			 
			//Tamil for 3213 * Starts
			
			if(!empty($res['combine_project'])){
			
				$combine_proj_sql = $ilance->db->query("select s.coin_id from ".DB_PREFIX."shippnig_details s 
														join
														(select projectid from ".DB_PREFIX."invoices  where invoiceid IN (".$res['combine_project'].") and user_id=".$res['user_id'].") i
														on s.coin_id=i.projectid and s.buyer_id=".$res['user_id']."
														where s.track_no !=''
														group by s.coin_id");
				
				$total_items=count(explode(",",$res['combine_project']));
				
				$complete_shipment=$ilance->db->num_rows($combine_proj_sql);				
				
				if($total_items == $complete_shipment){
					
					$res['ship_status']='Y';
				}
				else if(($total_items != $complete_shipment )  AND ($complete_shipment!=0)){
					$res['ship_status']='Partial';
				}
				else{
					$res['ship_status']='N';
				}
			 
			}
			
			unset($complete_shipment);
			unset($total_items);
			
			//Tamil for 3213 * Ends
			
			 $invoicelist[] = $res;
			
			
		   }
		   
		}
	
		$series_prevnext = print_pagnation($second_level_number, 50, $ilance->GPC['series_page'], $counter, $scriptpageprevnext, 'series_page');
		
		
		
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update')
	{	
	 $selectinvoice = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."invoices
					WHERE invoiceid = '".$ilance->GPC['invoiceid']."'");
	 $result = $ilance->db->fetch_array($selectinvoice);	
	 $pay_invoice_id = $result['invoiceid'];
	 $pay_first_name = fetch_user('first_name',$result['user_id']);
	 $pay_last_name = fetch_user('last_name',$result['user_id']);
	 $pay_username = fetch_user('username',$result['user_id']);
	 $pay_email = fetch_user('email',$result['user_id']);
	 $pay_address = fetch_user('city',$result['user_id']);
	 $pay_phone = fetch_user('phone',$result['user_id']);
	 $pay_amount = $result['amount'];
	 //$payment_pulldown = print_paymethod_pulldown('withdraw', 'account_id', $result['user_id'], $javascript = '');	 
	}
	
	//karthik on Sep29 for Schedule list
if((isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd2']) AND $ilance->GPC['subcmd2'] == 'schedule'))
{
  foreach($ilance->GPC['check'] as $value)
  {
     $sql=$ilance->db->query("update ". DB_PREFIX ."invoices set status='paid',paiddate='".DATETIME24H."' where invoiceid='".$value."'");
	
  }
  print_action_success("Invoice Status Marked As Paid", $ilpage['buyer']);
  	exit;
}
				
//end on Sep29
//New Changes on 12Mar02 for Buyers Unpaid List
   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyers_print')
   {
			       
			      header("Location:buyers_pdf.php");
    }
	//csvgenerate
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'paymentpaid')
	{
		$updateinvoice = $ilance->GPC['invoiceid'];
		$ilance->db->query("UPDATE ".DB_PREFIX."invoices 
							SET status = 'paid'
							paiddate = '".DATETIME24H."'
							WHERE invoiceid = '".$updateinvoice."' ");
		print_action_success("Invoice Status Marked As Paid", $ilpage['buyers']);
                        exit();
	}
	
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'csvgenerate')
	{
	 
	   $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
	  $sql = "SELECT * FROM " . DB_PREFIX . "shippnig_details
						WHERE shipment_date = '".$validdate."'					
						";
	  $fields = array(
					array('ship_id', 'ID'),
					array('item_id', 'ITEM ID'),
					array('buyer_id', 'Customer ID'),
					array('shipper_id', 'Shipper ID'),
					array('shipment_date', 'Date')
					
				);
				
				foreach ($fields AS $column)
				{			
					$fieldsToGenerate[] = $column[0];
					$headings[] = $column[1];				
				}
				$data = $ilance->admincp->fetch_reporting_fields($sql, $fieldsToGenerate);				
				if(count($data) > 0)
				{
				$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
				$timeStamp = date("Y-m-d-H-i-s");
				$fileName = "shippingreports-$timeStamp";
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
				else
				{
				  print_action_failed("Selected Report is Empty Check Date", $ilpage['buyers'] );
                        exit();
				}
		
	}
	


	$reportrange = '<select name="rangepast" style="font-family: verdana"><option value="-1 day"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>The Past Day</option><option value="-1 week"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>The Past Week</option><option value="-1 month"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>The Past Month</option><option value="-1 year"'; 
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>The Past Year</option></select>';
	
	// #### advanced reporting from range ##########################
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
	// Date Month Year Start
	
		$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
						$day = date('d');
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				// Date Month Year End
	

	$invoice_type_drop_down='<select name="invoice_type" id="invoice_type" onchange="window.location=\'buyers.php?invoice_type=\'+(this.value);">
						 <option value="all" '.($show_invoice_type=='all'?'selected="selected"':'').'>All Invoices</option>
						 <option value="paid" '.($show_invoice_type=='paid'?'selected="selected"':'').'>Paid Invoices</option>
						 <option value="unpaid" '.($show_invoice_type=='unpaid'?'selected="selected"':'').'>Unpaid Invoices</option>
						 </select>';
	//sekar
if((isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd1']) AND $ilance->GPC['subcmd1'] == 'pdflist'))
{
	
				$validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
				
				//$startdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];							
							$start =  date('Y-m-d',strtotime($validdate));
				
				header("Location:pdf.php?start=".$start."");
				
}
// murugan changes  on june 22 
// murugan update on july 19
    if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unpaid')
	{
	  

    $first_sql="SELECT u.user_id,u.username,u.first_name,u.last_name,DATE_FORMAT(date(u.date_added), '%m/%d/%Y') as user_date_added,u.email,u.phone,c.totalunpaid,i.scheduledamount,i.paratialamount,c.oldamount,c.newamount,
	IFNULL(totalunpaid,0)+IFNULL(scheduledamount,0)  as sortorder FROM ". DB_PREFIX . "users u
left join (select user_id,sum(amount) as scheduledamount ,paid as paratialamount from ". DB_PREFIX . "invoices where status='scheduled' group by user_id) i on u.user_id=i.user_id
left join (select user_id,SUM(amount) AS totalunpaid,
sum(CASE WHEN date(createdate) < '".FIFETEENDAYSAGO."' THEN amount ELSE 0 END) as oldamount,
sum(CASE WHEN date(createdate) >= '".FIFETEENDAYSAGO."' THEN amount ELSE 0 END) as newamount 
from ". DB_PREFIX . "invoices where status='unpaid' and combine_project = '' group by user_id) c on u.user_id=c.user_id
where i.scheduledamount is not null or c.oldamount is not null or c.newamount is not null
group by u.user_id ORDER BY  sortorder  DESC";
 
	 $sql = $ilance->db->query($first_sql);					
			
		
		$table = ' <table border=1>
		<tr><td size="20" family="helvetica" style="bold"  colspan="8">GC A/R ('.date('F d, Y',strtotime(DATETIME24H)).') CONFIDENTIAL</td></tr>
		<tr><td>User ID</td><td  width="50">User Name<br>&nbsp;&nbsp;First Name&nbsp;Last Name<br>Email</td><td>Phone</td><td>Date Joined</td><td>Unpaid <br>Invoices</td><td>Oldest<br> Date<br>(Before<br> '.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')</td><td>Latest<br> Date<br>( AFTER <br>'.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')</td><td>Total Amount</td></tr>';				
	   
	   if($ilance->db->num_rows($sql) > 0)
       {
	      while($res = $ilance->db->fetch_array($sql))
	      {					$resnew1 = $ilance->db->fetch_array($sqlnew1);
		                    $arrayoldamount[] = $res['oldamount'];
							$arraynewamount[] = $res['newamount'];
							$arrayscheduled_amount[]=$res['scheduledamount'];
							$arraytotal[]=$res['totalunpaid']+$res['scheduledamount'];
							$totalunpaid = $ilance->currency->format_no_text($res['totalunpaid']+$res['scheduledamount']);
						  
						  $table .='<tr>
						   <td>'.$res['user_id'].'</td>
						   <td>'.strtolower($res['username']).'<br><strong>&nbsp;&nbsp;'. $res['first_name'].'&nbsp;'.$res['last_name'].'</strong><br>'.strtolower($res['email']).'</td>
						   <td>'.$res['phone'].'</td>
						   <td>'.$res['user_date_added'].'</td>';
						   if($res['paratialamount'] >0)
						   {
							$table .= '<td>*'.$ilance->currency->format_no_text($res['scheduledamount']).'</td>';
						   }
						   else
						   {
							$table .= '<td>'.$ilance->currency->format_no_text($res['scheduledamount']).'</td>';
						   }
						   $table .= '<td>'.$ilance->currency->format_no_text($res['oldamount']).'</td>
						   <td>'.$ilance->currency->format_no_text($res['newamount']).'</td>';
						 
						  $table .= '<td>'.$totalunpaid.'</td>';
						   
						  						   
						  $table .= '</tr>';
			
		  }
		  $table .='<tr><td colspan ="4" align="right">Total Scheduled </td> 
					<td>'.$ilance->currency->format_no_text(array_sum($arrayscheduled_amount)).'</td>
					<td></td>
					<td> </td>
					<td> </td></tr>';
	   $table .='<tr><td colspan ="4" align="right">Total Unpaid </td> 
					<td> </td>
					<td>'.$ilance->currency->format_no_text(array_sum($arrayoldamount)).'</td>
					<td>'.$ilance->currency->format_no_text(array_sum($arraynewamount)).'</td>
					<td> </td></tr>';
	   $table .='<tr><td colspan ="4" align="right">Total Money Owed </td> 
					<td> </td>
					<td></td>
					<td> </td>
					<td>'.$ilance->currency->format_no_text(array_sum($arraytotal)).'</td></tr>';
	  }	
	  
	  else
	  {
	  	$table .='<tr><td>No Result Found</td></tr>';
	  }  
	  
		  
		  $table .='</table>';
		  
		  		$timeStamp = date("Y-m-d-H-i-s");
				$fileName = "unpaidreports-$timeStamp";
		            define('FPDF_FONTPATH','../font/');
					
					require('pdftable_1.9/lib/pdftable.inc.php');
					
					$p = new PDFTable();
					
					$p->AddPage();
					
					$p->setfont('times','',8);
					
					$p->htmltable($table);
					
					$p->output($fileName.'.pdf','D');  
		
	}
	
	
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyer' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unpaid_itemlist')
	{
	
	  $sql = $ilance->db->query("SELECT invoiceid,projectid from " . DB_PREFIX . "invoices where projectid !=0 and status = 'unpaid' 
                                
                        ");
		
		$table = '<table><tr><td size="20" family="helvetica" style="bold">List of all Items or Unpaid</td></tr></table><table>
		<tr><td>Item ID</td><td>Title</td><td>Consignor Name</td><td>Buyer Name</td><td>Hammer Price</td><td>Total Owing by Buyer + (hammer price)</td></tr>';				
	   
	   if($ilance->db->num_rows($sql))
       {
	      while($res = $ilance->db->fetch_array($sql))
	      {
		  
		       $sql2 = $ilance->db->query("SELECT project_id,project_title,buyerfeeinvoiceid,buyer_fee,currentprice,filtered_auctiontype,buynow_price,user_id,winner_user_id  from " . DB_PREFIX . "projects where project_id = '".$res['projectid']."'
                                
                        ");
						
						
						while($res2 = $ilance->db->fetch_array($sql2))
	                    {
						
		                      $check = fetch_auction('filtered_auctiontype',$res2['project_id']);
		
		                      if($check == 'regular')
		                      {
							  $buyername = fetch_user('username',$res2['winner_user_id']);
							  $prize = $res2['currentprice'];
							  
							  }
							  else
							  {
							  $userid = fetch_buynow_list('buyer_id',$res2['project_id']);
							  $buyername = fetch_user('username',$userid);
							  $prize = fetch_buynow_list('amount',$res2['project_id']);
							  }
							  $conname = fetch_user('username',$res2['user_id']);
							  
							  $total_Owing = $prize + $res2['buyer_fee'];
							 //echo $res2['project_id'].'-----'.$res2['project_title'].'---'.$conname.'-----'.$buyername.'----'.$prize.'---'.$total_Owing.'<br>';
						   $table .='<tr><td>'.$res2['project_id'].'</td><td>'.$res2['project_title'].'</td><td>'.$conname.'</td><td>'.$buyername.'</td><td>'.$ilance->currency->format($prize).'</td><td>'.$ilance->currency->format($total_Owing).'</td></tr>';
						
						}
						
		     
		  
		  }
	  }	
	  else
	  {
	      $table .='<tr><td>No Result Found</td></tr>';
	  }   
		  
		  $table .='</table>';
		  
		            define('FPDF_FONTPATH','../font/');
					
					require('pdftable_1.9/lib/pdftable.inc.php');
					
					$p = new PDFTable();
					
					$p->AddPage();
					
					$p->setfont('times','',10);
					
					$p->htmltable($table);
					
					$p->output('missing_coin_list.pdf','D');  
	
	
	}
	
	//Karthik on Sep29 for Schedule list
	
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
$ilance->GPC['series_page'] = (!isset($ilance->GPC['series_page']) OR isset($ilance->GPC['series_page']) AND $ilance->GPC['series_page'] <= 0) ? 1 : intval($ilance->GPC['series_page']);
$counter = ($ilance->GPC['series_page'] - 1) * $ilance->GPC['pp'];
	
$scriptpageprevnext='buyers.php?';
$second_level2 = $ilance->db->query("select * from ".DB_PREFIX."invoices where status = 'scheduled'");
$second_level_number1=(int)$ilance->db->num_rows($second_level2);
 
	
		$sqlquery=$ilance->db->query("select * from ".DB_PREFIX."invoices where status = 'scheduled' GROUP BY invoiceid LIMIT ". (($ilance->GPC['series_page'] - 1) * 10) . "," . 10 ) ;
			if($ilance->db->num_rows($sqlquery) > 0)
		{		   
		   while($res1 = $ilance->db->fetch_array($sqlquery))
		   {	
			
			  $res1['first_name'] = fetch_user('first_name', $res1['user_id']);
			  $res1['last_name'] = fetch_user('last_name', $res1['user_id']);
		
			 $res1['user_name'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$res1['user_id'].'"">'.fetch_user('username', $res1['user_id']).'</a>';
			
			 $res1['check']='<input type="checkbox" name="check[]" value="'.$res1['invoiceid'].'"/>';
			 $invoicelist1[] = $res1;
			
		   }
		}
		$series_prevnext1 = print_pagnation($second_level_number1, 10, $ilance->GPC['series_page'], $counter, $scriptpageprevnext, 'series_page');
		
		
		
//end on Sep29	

		
	
	$pprint_array = array('invoice_paid_to','invoice_paid_from','search_by_dropdown','searchkey_value','invoice_type_drop_down','series_prevnext','daylist','monthlist','yearlist','searchprevnext','hiddenid','hiddendo','pay_first_name','pay_last_name','pay_username','pay_email','pay_address','pay_phone','pay_invoice_id','pay_amount','payment_pulldown','reportfromrange','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','series_prevnext1');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'buyers.html', 2);
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
function is_shipper_upgraded($user_id,$shipper_id,$invoice_total)
{
	 global $ilance;
   
	 $country_id=fetch_user('country',$user_id);	
	 //if international
	 if($country_id!=500 and $shipper_id!=22 and $invoice_total<10000)//USPS International Priority
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=25 and $invoice_total>10000)//USPS Express Mail
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=27 and $invoice_total>1000 and  $invoice_total<=10000)//USPS Priority Mail
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=26 and $invoice_total<=1000)//USPS First Class Mail
	 {
	 return true;
	 }
	 return false;
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

	$ilance->db->query("update ".DB_PREFIX."invoices set status='complete' where invoiceid='".$invoice_id."'");
}
}
}

}
	
?>