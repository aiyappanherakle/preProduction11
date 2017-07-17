<?php
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


require_once('../functions/config.php');

// error_reporting(E_ALL);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


//Is ADMIN?
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{  
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']== "list_all_consignor")   
{
	if(isset($ilance->GPC['month']) and isset($ilance->GPC['year']) and $ilance->GPC['month']>0 and $ilance->GPC['year']>0)
	{
		$num = cal_days_in_month(CAL_GREGORIAN, $ilance->GPC['month'], $ilance->GPC['year']);
		$start=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.'01';
		$stmt_date=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.$num;
		$sql="SELECT u.user_id, u.username, u.email,u.first_name,u.last_name,u.address,u.address2,u.city,u.state,u.zip_code,u.phone,
				count( DISTINCT c.coin_id ) AS coin_count, 
				count( DISTINCT r.coin_id ) AS relist_coin_count,
				count( DISTINCT b.project_id) AS buynow_coin_count,
				sum(distinct a.amount) as adv_amount,
				sum(distinct CASE WHEN i.invoicetype='debit' THEN i.amount ELSE 0 END) as misc_debit,
				sum(distinct CASE WHEN i.invoicetype='credit' THEN i.amount ELSE 0 END) as misc_credit
				FROM ilance_users u
				LEFT JOIN ilance_coins c ON u.user_id = c.user_id and date(c.End_Date)='".$stmt_date."'
				LEFT JOIN ilance_coin_relist r ON u.user_id = r.user_id and date(r.actual_end_date)='".$stmt_date."'
				LEFT JOIN ilance_buynow_orders b ON u.user_id = b.owner_id and date(b.orderdate)>='".$start."' and date(b.orderdate)<='".$stmt_date."' 
				LEFT JOIN ilance_user_advance a ON u.user_id = a.user_id and a.statusnow = 'paid' and date(a.date_made)<='".$stmt_date."' and date(a.date_made)>='".$start."'
				LEFT JOIN ilance_invoices i ON i.user_id = u.user_id and i.ismis =1 and date(i.createdate)<='".$stmt_date."' and date(i.createdate)>='".$start."'
	 
				GROUP BY u.user_id having coin_count>0 or relist_coin_count>0 or buynow_coin_count>0 order by u.username";

		$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($user_query))
		{
		while($user_details=$ilance->db->fetch_array($user_query))
			{
					$data_csv['user_id']=$user_details['user_id'];
					$data_csv['first_name']=$user_details['first_name'];
					$data_csv['last_name']=$user_details['last_name'];
					$data_csv['address']=$user_details['address'];
					$data_csv['address2']=$user_details['address2'];
					$data_csv['city']=$user_details['city'];
					$data_csv['state']=$user_details['state'];
					$data_csv['zip_code']=$user_details['zip_code'];
					$data_csv['country']=print_user_country($user_details['user_id']);
					$data_csv['phone']=$user_details['phone'];
					$data_csv['email']=$user_details['email'];
					$data[]=$data_csv;
			}
			$ilance->admincp = construct_object('api.admincp');
				$headings=array('Card ID', 'First Name', 'Last Name', 'Address', 'Address2', 'City', 'State', 'Zipcode', 'Country', 'Phone', 'Email');
			    $reportoutput = $ilance->admincp->construct_csv_data($data,$headings);
			
			    header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header('Content-Disposition: attachment; filename="'.'Consignor_Details_'.date('Y-m-d h-i-s').'.csv"');
				echo $reportoutput;
				die();
		}	
	}
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']== "list_all_statements")   
{
	if(isset($ilance->GPC['month']) and isset($ilance->GPC['year']) and $ilance->GPC['month']>0 and $ilance->GPC['year']>0)
	{
		$num = cal_days_in_month(CAL_GREGORIAN, $ilance->GPC['month'], $ilance->GPC['year']);
		$start=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.'01';
		$stmt_date=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.$num;
		$sql="SELECT u.user_id, u.username, u.email,u.first_name,u.last_name,u.address,u.address2,u.city,u.state,u.zip_code,u.phone,
				count( DISTINCT c.coin_id ) AS coin_count, 
				count( DISTINCT r.coin_id ) AS relist_coin_count,
				count( DISTINCT b.project_id) AS buynow_coin_count,
				sum(distinct a.amount) as adv_amount,
				sum(distinct CASE WHEN i.invoicetype='debit' THEN i.amount ELSE 0 END) as misc_debit,
				sum(distinct CASE WHEN i.invoicetype='credit' THEN i.amount ELSE 0 END) as misc_credit
				FROM ilance_users u
				LEFT JOIN ilance_coins c ON u.user_id = c.user_id and date(c.End_Date)='".$stmt_date."'
				LEFT JOIN ilance_coin_relist r ON u.user_id = r.user_id and date(r.actual_end_date)='".$stmt_date."'
				LEFT JOIN ilance_buynow_orders b ON u.user_id = b.owner_id and date(b.orderdate)>='".$start."' and date(b.orderdate)<='".$stmt_date."' 
				LEFT JOIN ilance_user_advance a ON u.user_id = a.user_id and a.statusnow = 'paid' and date(a.date_made)<='".$stmt_date."' and date(a.date_made)>='".$start."'
				LEFT JOIN ilance_invoices i ON i.user_id = u.user_id and i.ismis =1 and date(i.createdate)<='".$stmt_date."' and date(i.createdate)>='".$start."'
				GROUP BY u.user_id having coin_count>0 or relist_coin_count>0 or buynow_coin_count>0 order by u.username ";

		$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($user_query))
		{
		while($user_details=$ilance->db->fetch_array($user_query))
			{ 
			$end_date_list=get_all_enddate_for_user_in_a_month($user_details['user_id'],$start,$stmt_date);
			for($i=1;$i<count($end_date_list);$i++)
			{
			//get consignor stmt details
			unset($consignor_stmt_details);
 
			$start_date=date('Y-m-d h:i:s',strtotime($end_date_list[$i-1])+24*60*60);
	 
			$consignor_stmt_details=get_consignor_stmt_details($user_details['user_id'],$start_date,$end_date_list[$i]);
			if(!is_array($consignor_stmt_details) 
			or ($consignor_stmt_details['statement_final_total']==0 )
			and $consignor_stmt_details['statement_listing_fee_total']==0
			and $consignor_stmt_details['statement_seller_fee_total']==0)
			{
			continue;
			}
			
			
			//yyyy-mm-dd
			list($yyyy,$mm,$dd)=explode('-',$end_date_list[$i]);
		 $end_date_stamp=strtotime($dd.'-'.$mm.'-'.$yyyy);
	
		 $purchase_id=$end_date_stamp."-".$user_details['user_id'];
		$readable_end_date=date('M d, Y',$end_date_stamp);
			
			//sales
			$data_csv['purchase_id']=$purchase_id;
			$data_csv['purchase_date']=$end_date_list[$i];
			$data_csv['description']='Consignor Sales ('.$readable_end_date.')';
			$data_csv['account_id']=$user_details['user_id'];
			$data_csv['amount']=$consignor_stmt_details['statement_final_total'];
			$data_csv['card_id']=$user_details['user_id'];
			$data_csv['name']=$user_details['first_name'].' '.$user_details['last_name'];
			$data[]=$data_csv;
			//sellect fees
			$data_csv['purchase_id']=$purchase_id;
			$data_csv['purchase_date']=$end_date_list[$i];
			$data_csv['description']='Seller Fees ('.$readable_end_date.')';
			$data_csv['account_id']=$user_details['user_id'];
			$data_csv['amount']='-$'.$consignor_stmt_details['statement_listing_fee_total'];
			$data_csv['card_id']=$user_details['user_id'];
			$data_csv['name']=$user_details['first_name'].' '.$user_details['last_name'];
			$data[]=$data_csv;
			//listing fee
			$data_csv['purchase_id']=$purchase_id;
			$data_csv['purchase_date']=$end_date_list[$i];
			$data_csv['description']='Listing Fees ('.$readable_end_date.')';
			$data_csv['account_id']=$user_details['user_id'];
			$data_csv['amount']='-$'.$consignor_stmt_details['statement_seller_fee_total'];
			$data_csv['card_id']=$user_details['user_id'];
			$data_csv['name']=$user_details['first_name'].' '.$user_details['last_name'];
			$data[]=$data_csv;
			}
			
			
			}
			$ilance->admincp = construct_object('api.admincp');
				$headings=array('Purchase NO', 'Date', 'Description', 'Account', 'Amount', 'Card Id', 'Name');
			    $reportoutput = $ilance->admincp->construct_csv_data($data,$headings);
			
			  header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header('Content-Disposition: attachment; filename="'.'Purchase_Orders_'.date('Y-m-d h-i-s').'.csv"');
				echo $reportoutput;
				die(); 
		}	
	
	}
}	
 
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']== "pending_invoice_export")   
{
	if(isset($ilance->GPC['month']) and isset($ilance->GPC['year']) and $ilance->GPC['month']>0 and $ilance->GPC['year']>0)
	{
	$num = cal_days_in_month(CAL_GREGORIAN, $ilance->GPC['month'], $ilance->GPC['year']);
	$firstday=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.'01';
	$lastday=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.$num;
	$sql="SELECT u.user_id,u.username,SUM(i.amount) AS totamount
							FROM " . DB_PREFIX . "users u, " . DB_PREFIX . "invoices i 
							WHERE u.status='active'
							AND u.user_id = i.user_id
							AND (i.status = 'unpaid' or date(i.paiddate)> '".$lastday."' )
							AND date(i.createdate) BETWEEN '".$firstday."' AND '".$lastday."'
							AND i.combine_project = ''
							AND i.isfvf != 1
							AND i.isif != 1 
							AND i.isbuyerfee != 1 
							AND i.isenhancementfee != 1						
							GROUP BY i.user_id
							ORDER BY user_id ASC					
						  ";
	$sql_pending = $ilance->db->query($sql);
			//Pending Invoice(s) in Current Month					
			if ($ilance->db->num_rows($sql_pending) > 0)
			{
			  while($res = $ilance->db->fetch_array($sql_pending))
			  {    
					//Total Buyer Fee
					$sql_buyerfee = $ilance->db->query("SELECT SUM(amount) AS totamount
														FROM  " . DB_PREFIX . "invoices 
														WHERE user_id = '".$res['user_id']."'
														AND date(createdate) BETWEEN '".$firstday."' AND '".$lastday."'
														AND isbuyerfee = 1 
													  ");
					$res_buyerfee = $ilance->db->fetch_array($sql_buyerfee);								  
									  
					$data_csv['user_id']=$res['user_id'];
					$data_csv['username']=$res['username'];
					$data_csv['total_amount'] = $ilance->currency->format($res['totamount']+$res_buyerfee['totamount']);
					$data_csv['total_hammer'] = $ilance->currency->format($res['totamount']);
					$data_csv['total_bf'] = $ilance->currency->format($res_buyerfee['totamount']);
					$total_hammer[]=$res['totamount'];
					$total_bf[]=$res_buyerfee['totamount'];
					$data[]=$data_csv;
			  }
				$ilance->admincp = construct_object('api.admincp');
				$headings=array("Customer ID", "Customer Name", "Amount of Pending Items", "Total Hammer Price", "Total Buyer's Fees");
				$data[]='';
				$data[]=array(''=>'', 'Customer Name'=>'Total', 'Amount of Pending Items'=>$ilance->currency->format(array_sum($total_hammer)+array_sum($total_bf)), 'Total Hammer Price'=>$ilance->currency->format(array_sum($total_hammer)), "Total Buyer's Fees"=>$ilance->currency->format(array_sum($total_bf)));
				$reportoutput = $ilance->admincp->construct_csv_data($data,$headings);

				header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header('Content-Disposition: attachment; filename="'.'Pending_Invoice_For_'.$ilance->GPC['year'].'-'.$ilance->GPC['month'].'.csv"');
				echo $reportoutput;
				die();
			}		
	}
}	
 
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']== "all_invoice_export")   
{
	if(isset($ilance->GPC['month']) and isset($ilance->GPC['year']) and $ilance->GPC['month']>0 and $ilance->GPC['year']>0)
	{
	$num = cal_days_in_month(CAL_GREGORIAN, $ilance->GPC['month'], $ilance->GPC['year']);
	$firstday=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.'01';
	$lastday=$ilance->GPC['year'].'-'.$ilance->GPC['month'].'-'.$num;
//error_reporting(E_ALL);
//Sales Invoice Check
$sql="
select i.createdate,
i.invoiceid,
i.paiddate, 
i.user_id,
i.status,
i.combine_project,
i.taxamount,
i.paymethod,
i.amount,
i.paid,
i.totalamount,
u.username
 from ilance_invoices i join ilance_users u on u.user_id=i.user_id 
  where (date(i.createdate) BETWEEN '".$firstday."' AND '".$lastday."' or  date(i.paiddate) BETWEEN '".$firstday."' AND '".$lastday."') and combine_project!='' ORDER BY i.user_id ASC";
 


     $sql_pending = $ilance->db->query($sql );
								
		   //Invoice(s) in Current Month					
		   if ($ilance->db->num_rows($sql_pending) > 0)
           {
		      while($res = $ilance->db->fetch_array($sql_pending))
			  {    
					$total_hammer=0;
					$total_buyer_fee=0;
				 $query="select amount,invoiceid,projectid,user_id from ".DB_PREFIX."invoices where invoiceid in (".$res['combine_project'].")";
					$result=$ilance->db->query($query);
					if($ilance->db->num_rows($result)>0)
					{
						while($line=$ilance->db->fetch_array($result))
						{
						$total_hammer+=$line['amount'];
						$query2="select * from ".DB_PREFIX."invoices where projectid='".$line['projectid']."' and user_id='".$line['user_id']."' and isbuyerfee=1";
						$result2=$ilance->db->query($query2);
						if($ilance->db->num_rows($result2)>0)
						{
							while($line2=$ilance->db->fetch_array($result2))
							{
							$total_buyer_fee+=$line2['amount'];
							}
						}
						}
					}
					$shipping_cost=0;
					 $query3="select shipping_cost from ".DB_PREFIX."invoice_projects where final_invoice_id='".$res['invoiceid']."'";
					
					$result3=$ilance->db->query($query3);
					if($ilance->db->num_rows($result3)>0)
					{
						while($line3=$ilance->db->fetch_array($result3))
						{
						  $shipping_cost=$line3['shipping_cost'];
						}
					}
					/* 
				   //Total Buyer Fee
			        $sql_buyerfee = $ilance->db->query("SELECT amount
														FROM  " . DB_PREFIX . "invoices 
														WHERE user_id = '".$res['user_id']."'
														AND projectid = '".$res['projectid']."'
														AND isbuyerfee = 1 
													  ");
					$res_buyerfee = $ilance->db->fetch_array($sql_buyerfee);								  
					*/
					$data_csv['date_created']=$res['createdate'];
					$data_csv['date_paid']=$res['paiddate'];				  
			        $data_csv['user_id']=$res['user_id'];
					//$data_csv['invoiceid']=$res['invoiceid'];
					$data_csv['username']=$res['username'];
					$data_csv['total_hammer'] = $ilance->currency->format($total_hammer);
					$data_csv['total_bf'] = $ilance->currency->format($total_buyer_fee);
					$data_csv['shipping'] = $ilance->currency->format($shipping_cost);
					$data_csv['tax'] = $ilance->currency->format($res['taxamount']);
					$data_csv['adjustment'] =  $ilance->currency->format(($total_hammer+$total_buyer_fee+$shipping_cost+$res['taxamount'])-$res['totalamount']);
					$data_csv['total_amount'] = $ilance->currency->format($res['totalamount']);
					$data_csv['total_paid'] = $res['status']=='paid'?$ilance->currency->format($res['amount']):$ilance->currency->format(0);
					$data_csv['pay_method'] = $res['paymethod']=='account'?'-':$res['paymethod'];
					$data_csv['total'] = $ilance->currency->format($res['totalamount']);
					$data[]=$data_csv;
			  }
			  
			  	$headings=array("Date Created", "Date Paid", "UserID", "Username", "Hammer", "Buyer's Fees", "Shipping", "Tax", "Adjustments", "Total Invoice", "Total Paid", "Payment Method", "Total");
			}
			/*
New Invoices (dated created in the month of report)
Total Amount - of New Invoices
Total Paid - of New Invoices (by each payment type)
Total Amount of Unpaid invoices (of New Invoices created)

From Previous Periods (dated before the month of report)
Total Amount - of Old Invoices on report (which includes all those NOT paid as at month of report and those PAID in month of report)
Total Paid - of Old Invoices (only paid in month of report run, by payment type)
Total Amount of Unpaid Invoices (of previous period invoices)

			*/
			
			//invoice type totals
			$paymethods[0]='bank';
			$paymethods[1]='card';
			$paymethods[2]='check';
			$paymethods[3]='paypal';
			$paymethods[4]='trade';
				$sub_sql='';
				foreach ($paymethods as $paymethod)
				{
				$sub_sql.="sum(CASE WHEN paymethod='".$paymethod."' and status='paid' THEN totalamount ELSE 0 END) as ".$paymethod."_total, ";
				}
				$unpaid_sub_sql="sum(CASE WHEN status!='paid' THEN totalamount ELSE 0 END) as unpaid_total, ";




			//new invoices
			$new_invoices_total=0;
		  	$query4="select ".$unpaid_sub_sql.$sub_sql."sum(totalamount) as totalamount from ".DB_PREFIX."invoices i where date(i.createdate) BETWEEN '".$firstday."' AND '".$lastday."'  
			and i.combine_project!='' ORDER BY i.user_id ASC";
			 
		 	$result4=$ilance->db->query($query4);
			if($ilance->db->num_rows($result4)>0)
			{
				while($line4=$ilance->db->fetch_array($result4))
				{
				$new_invoices_total=$line4['totalamount'];
				$data[]=array('1'=>"New Invoices Total",'New Invoices Total'=>$ilance->currency->format($new_invoices_total));
				$unpaid_total=$line4['unpaid_total'];
				$data[]=array('1'=>"Unpaid Invoices Total",'Unpaid Invoices Total'=>$ilance->currency->format($unpaid_total));
				$bank_total=$line4['bank_total'];
				$data[]=array('1'=>"Bank Paid",'Bank Paid'=>$ilance->currency->format($bank_total));
				$card_total=$line4['card_total'];
				$data[]=array('1'=>"Card Paid",'Card Paid'=>$ilance->currency->format($card_total));
				$check_total=$line4['check_total'];
				$data[]=array('1'=>"Check Paid",'Check Paid'=>$ilance->currency->format($check_total));
				$paypal_total=$line4['paypal_total'];
				$data[]=array('1'=>"Paypal Paid",'Paypal Paid'=>$ilance->currency->format($paypal_total));
				$trade_total=$line4['trade_total'];
				$data[]=array('1'=>"Trade Paid",'Trade Paid'=>$ilance->currency->format($trade_total));
				}
			}
			
			
			//old invoices
			$old_invoice_total=0;
			$query5="select  ".$unpaid_sub_sql.$sub_sql."sum(totalamount) as totalamount from ".DB_PREFIX."invoices i  where date(i.paiddate) BETWEEN '".$firstday."' AND '".$lastday."'
			and date(i.createdate) < '".$firstday."'
			and i.combine_project!='' ORDER BY i.user_id ASC";
			$result5=$ilance->db->query($query5);
			if($ilance->db->num_rows($result5)>0)
			{
				while($line5=$ilance->db->fetch_array($result5))
				{
				$old_invoice_total=$line5['totalamount'];
				$data[]=array('1'=>"Old Invoices Total",'Old Invoices Total'=>$ilance->currency->format($old_invoice_total));
				$unpaid_total=$line5['unpaid_total'];
				$data[]=array('1'=>"Unpaid Invoices Total",'Unpaid Invoices Total'=>$ilance->currency->format($unpaid_total));
				$bank_total=$line5['bank_total'];
				$data[]=array('1'=>"Bank Paid",'Bank Paid'=>$ilance->currency->format($bank_total));
				$card_total=$line5['card_total'];
				$data[]=array('1'=>"Card Paid",'Card Paid'=>$ilance->currency->format($card_total));
				$check_total=$line5['check_total'];
				$data[]=array('1'=>"Check Paid",'Check Paid'=>$ilance->currency->format($check_total));
				$paypal_total=$line5['paypal_total'];
				$data[]=array('1'=>"Paypal Paid",'Paypal Paid'=>$ilance->currency->format($paypal_total));
				$trade_total=$line5['trade_total'];
				$data[]=array('1'=>"Trade Paid",'Trade Paid'=>$ilance->currency->format($trade_total));
				}
			}
			
			
			
			
			
			$ilance->admincp = construct_object('api.admincp');
			    $reportoutput = $ilance->admincp->construct_csv_data($data,$headings);
			    header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header('Content-Disposition: attachment; filename="'.'Sales_Invoice_For_'.$ilance->GPC['year'].'-'.$ilance->GPC['month'].'.csv"');
				echo $reportoutput;
				die();
	}
}	
 
  
 $month='';
$month['1']='January';
$month['2']='February';
$month['3']='March';
$month['4']='April';
$month['5']='May';
$month['6']='June';
$month['7']='July';
$month['8']='August';
$month['9']='September';
$month['10']='October';
$month['11']='November';
$month['12']='December';
$month_html='<select name="month"><option>Select</option>';
foreach($month as $mm=>$value)
{
 if(date('m')==$mm)
$month_html.='<option value="'.$mm.'" selected="selected">'.$value.'</option>';
elseif(isset($ilance->GPC['month']) and $ilance->GPC['month']==$mm)
$month_html.='<option value="'.$mm.'" selected="selected">'.$value.'</option>';
else 
$month_html.='<option value="'.$mm.'">'.$value.'</option>';
}
$month_html.='</select>';
 $months=$month_html;
$years='<select name="year"><option>Select</option>';
for($i=date('Y');$i>=date('Y')-5;$i--)
{
if(date('Y')==$i)
$years.='<option value="'.$i.'" selected="selected">'.$i.'</option>';
elseif(isset($ilance->GPC['year']) and $ilance->GPC['year']==$i)
$years.='<option value="'.$i.'" selected="selected">'.$i.'</option>';
else 
$years.='<option value="'.$i.'">'.$i.'</option>';
}
$years.='</select>';
  
 
 
	$pprint_array = array('months','years','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
    $ilance->template->fetch('main', 'account_export.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}		


function get_all_enddate_for_user_in_a_month($user_id,$start_date,$end_date)
			{
			global $ilance;
			$query="select distinct date(en) as list_end_date from(
SELECT r.actual_end_date as en,r.user_id FROM ilance_coin_relist r where r.user_id='".$user_id."' and  datediff(startbydate,enddate)>1 group by date(actual_end_date)
union
SELECT c.End_date  as en,c.user_id FROM ilance_coins c where c.user_id=101 group by date(End_date)
) rc where rc.en>=DATE_SUB('".$start_date."',INTERVAL 7 DAY) and rc.en<='".$end_date."'";
			
			$result=$ilance->db->query($query);
			if($ilance->db->num_rows($result)>0)
			{
				while($line=$ilance->db->fetch_array($result))
				{
				$list_end_date[]=$line['list_end_date'];
				}
			}
			
			return $list_end_date;
			}   
			
function get_consignor_stmt_details($user_id,$start,$end_date)
{
global $ilance;
$cons_stmt_details='';
$user_details['user_id']=$user_id;
$stmt_date=$end_date;

$prime_sql="select
c.coin_id,
c.user_id,
c.End_Date,
r.last_listed_time,
p.date_end,
c.Title,
c.Minimum_bid,
c.Buy_it_now,
c.Alternate_inventory_No,
c.Certification_No,
c.project_id,
p.filtered_auctiontype,
p.winner_user_id,
p.hasbuynowwinner,
p.insertionfee,
p.date_starts,
o.order_count,
i.escrow_invoice_total,
i.fvf_total,
i.all_paid,
i.enhancementfee_total,
i.mis_total,
i.if_total,
r.no_relist_b4_statement,
count(distinct b.bid_id) as bid_count
from 
	".DB_PREFIX."coins c 
left join
	".DB_PREFIX."projects p on c.coin_id=p.project_id and p.user_id='".$user_details['user_id']."' and DATEDIFF(date_end,date_starts )>1 
left join 
	(select coin_id,user_id,count(id) as no_relist_b4_statement,max(actual_end_date) as last_listed_time from ".DB_PREFIX."coin_relist 
	where date(actual_end_date)<='".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
left join
	(select sum(qty) as order_count,project_id,orderdate from ".DB_PREFIX."buynow_orders o where owner_id='".$user_details['user_id']."' and date(orderdate)<='".$stmt_date."' and date(orderdate)>date(
	(
	select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ilance_coin_relist 
	where date(actual_end_date)<'".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 and coin_id=o.project_id order by actual_end_date desc limit 1
	)
	) group by project_id) o on c.coin_id=o.project_id 
left join
	".DB_PREFIX."project_bids b on c.coin_id=b.project_id and date(b.date_added)<='".$stmt_date."'
left join
	(select i.projectid,i.createdate,
	sum(CASE WHEN i.invoicetype='escrow' 	THEN i.amount ELSE 0 END) as escrow_invoice_total,
	sum( CASE WHEN i.isfvf=1  				THEN i.amount ELSE 0 END) as fvf_total,
	min( CASE WHEN i.invoicetype='escrow' AND i.status='paid'  THEN 0 ELSE 1 END) as all_paid,
	sum( CASE WHEN i.isenhancementfee=1  	THEN i.amount ELSE 0 END) as enhancementfee_total,
	sum( CASE WHEN i.ismis=1  				THEN i.amount ELSE 0 END) as mis_total,
	sum( CASE WHEN i.isif=1  				THEN i.amount ELSE 0 END) as if_total
	from ".DB_PREFIX."invoices i where (i.user_id=".$user_details['user_id']." or i.p2b_user_id=".$user_details['user_id'].") and date(i.createdate)<='".$stmt_date."' and date(i.createdate)>date((
	select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ilance_coin_relist 
	where date(actual_end_date)<'".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 and coin_id=i.projectid order by actual_end_date desc limit 1
	))  group by i.projectid) i on c.coin_id=i.projectid 	

where  (c.user_id=".$user_details['user_id']." and (
(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."') or
(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."'))    )
group by c.coin_id ORDER BY  c.coin_id ASC";
 
		 
 
 //echo $prime_sql;exit;
  //removed from line 91   and date(i.createdate) <= '".$stmt_date."' and date(i.createdate)>='".$start."'
	//from coinss table
	$coins_list_query=$ilance->db->query($prime_sql, 0, null, __FILE__, __LINE__);
 	if($ilance->db->num_rows($coins_list_query)>0)
	{ 
	 
		$statement_final_total=0;
		$statement_listing_fee_total=0;
		$statement_seller_fee_total=0;
		$statement_seller_total=0;
		$sl=1;
		while($coins_list_line=$ilance->db->fetch_array($coins_list_query))
		{
		$all_paid=1;
		$no_of_bids=0;
		$no_of_buynow=0;
		$coin_seller_fee=0;
		$coin_final_price=0;
		$coin_insertion_fee=0;	 
			switch ($coins_list_line['filtered_auctiontype'])
				{
				case 'regular':
					$no_of_bids=$coins_list_line['bid_count'];
					$coin_final_price=$coins_list_line['escrow_invoice_total'];
					$coin_insertion_fee=$coins_list_line['if_total'];
					$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['enhancementfee_total']+$coins_list_line['mis_total'];
					$all_paid=$coins_list_line['all_paid'];
				break;
				default:
					$no_of_buynow=intval($coins_list_line['order_count']); 
					$coin_final_price=$coins_list_line['escrow_invoice_total'];
					$coin_insertion_fee=$coins_list_line['if_total'];
					$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['enhancementfee_total']+$coins_list_line['mis_total'];
					$all_paid=$coins_list_line['all_paid'];
				break;
				}	
				
			$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
			  $statement_final_total+=$coin_final_price;
		 
			$statement_listing_fee_total+=$coin_insertion_fee;
			$statement_seller_fee_total+=$coin_seller_fee;
			$statement_seller_total+=$coin_consignor_total;	
			$sl++;
		}
		$cons_stmt_details['statement_final_total']=$statement_final_total;
		$cons_stmt_details['statement_listing_fee_total']=$statement_listing_fee_total;
		$cons_stmt_details['statement_seller_fee_total']=$statement_seller_fee_total;
	  
	} 

return $cons_stmt_details;
}			
?>  


