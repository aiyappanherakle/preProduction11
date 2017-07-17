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
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
//error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'export_consignor_csv') 
	{ 
 
	 
 		$stmt_date= $ilance->GPC['start_date'];
		$formatted_statement_date=date('m/d/Y',strtotime($stmt_date));
		$formatted_due_date=date('m/d/Y',strtotime($stmt_date.'+10 days'));
		$start=last_monday($stmt_date);
		$user_where=isset($ilance->GPC['user_id'])?'  WHERE  u.user_id='.$ilance->GPC['user_id']:'';
			$grand_statement_final_total=0;
			$grand_statement_listing_fee_total=0;
			$grand_statement_seller_fee_total=0;
			$grand_statement_seller_total=0;
			$t=0;
		$sql="SELECT u.user_id, u.username, u.email,u.first_name,u.last_name,u.address,u.address2,u.city,u.state,u.zip_code,
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
		 ".$user_where." 
		GROUP BY u.user_id having coin_count>0 or relist_coin_count>0 or buynow_coin_count>0 order by u.last_name";

		$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
 
		$html.= '<table width="100%">
		<tr>
		<td size="15" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
		<td>&nbsp;</td>
		<td size="12" family="helvetica" style="bold" nowrap><b>Consignor Statement Summary</b></td><td>'.$stmt_date.'</td></tr>
		<tr><tr>
		<td>Username</td><td>Email</td><td>Gross Total Sales Final Price</td><td> Listing Fees</td><td>Sellers Fees</td><td>Net to Consignor</td></tr>';


		if($ilance->db->num_rows($user_query))
		{
		while($user_details=$ilance->db->fetch_array($user_query))
			{
			
			 unset($list);
			
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
		(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
		(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."'))    )
		group by c.coin_id ORDER BY  c.coin_id ASC";
			$coins_list_query=$ilance->db->query($prime_sql, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($coins_list_query)>0)
			{ 
				$user_id=$user_details['user_id'];
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
							if($coins_list_line['no_relist_b4_statement']==0)
							{
								$coin_insertion_fee=$coins_list_line['if_total'];
							}else
							{
								$coin_insertion_fee=0;
							}
							$coin_insertion_fee=$coins_list_line['if_total']+$coins_list_line['enhancementfee_total'];
							$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['mis_total'];
							$all_paid=$coins_list_line['all_paid'];
						break;
						default:
							$no_of_buynow=intval($coins_list_line['order_count']); 
							$coin_final_price=$coins_list_line['escrow_invoice_total'];
							if($coins_list_line['no_relist_b4_statement']>0)
							{
								$coin_insertion_fee=0;
							}
							$coin_insertion_fee=$coins_list_line['if_total']+$coins_list_line['enhancementfee_total'];
							$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['mis_total'];
							$all_paid=$coins_list_line['all_paid'];
						break;
						}	
					$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
					$statement_final_total+=$coin_final_price;
					$statement_listing_fee_total+=$coin_insertion_fee;
					$statement_seller_fee_total+=$coin_seller_fee;
					$statement_seller_total+=$coin_consignor_total;	
				}
				$html.='<td>'.$ilance->currency->format_real_no($statement_final_total)		.'</td>';
				$html.='<td>'.$ilance->currency->format_real_no($statement_listing_fee_total).'</td>';
				$html.='<td>'.$ilance->currency->format_real_no($statement_seller_fee_total).'</td>';
				$html.='<td>'.$ilance->currency->format_real_no($statement_seller_total)	.'</td>';
				$html.='</tr>';
				
				$advance_received=0;
				$miscellanious_debit=0;
				$miscellanious_credit=0;
				
				$advance_received=$user_details['adv_amount'];
				$miscellanious_debit= $user_details['misc_debit'];
				$miscellanious_credit= $user_details['misc_credit'];
				
				$statement_total=$statement_seller_total-$advance_received+$miscellanious_debit-$miscellanious_credit;		
				$grand_statement_final_total+=$statement_final_total;
				$grand_statement_listing_fee_total+=$statement_listing_fee_total;
				$grand_statement_seller_fee_total+=$statement_seller_fee_total;
				$grand_statement_seller_total+=$statement_seller_total;
				$statement_number=get_statemant_number($user_id,$stmt_date);
				$data['statement_number']=$statement_number;
			if($statement_final_total>0)
			{
				$data['statement_date']=$formatted_statement_date;
				$data['payment_due']=$formatted_due_date;
				$data['user_id']=$user_id;
				$data['summary']='Consignor Sales';
				$data['description']='Auction System Consignor Statement';
				//$data['description']='Hammer Consignor';
				$data['account_name']='Hammer Consignor';
				//$data['account_name']='5000';
				$data['account_number']='5000';
				$data['line_total']=$statement_final_total;
				$data['tags']='';
				$res[]=$data;
			}
				if($statement_seller_fee_total>0)
				{
				$data['statement_date']=$formatted_statement_date;
				$data['payment_due']=$formatted_due_date;
				$data['user_id']=$user_id;
				$data['summary']='Consignor Sales';
				$data['description']='Auction System Consignor Statement';
				$data['account_name']='Seller Fees';
				$data['account_number']='4030';
				$data['line_total']='-'.$statement_seller_fee_total;
				$data['tags']='';
				$res[]=$data;
				}
				if($statement_listing_fee_total>0)
				{
				$data['statement_date']=$formatted_statement_date;
				$data['payment_due']=$formatted_due_date;
				$data['user_id']=$user_id;
				$data['summary']='Consignor Sales';
				$data['description']='Auction System Consignor Statement';
				$data['account_name']='Listing Fees';
				$data['account_number']='4040';
				$data['line_total']='-'.$statement_listing_fee_total;
				$data['tags']='';
				$res[]=$data;
				}
				/*
				$data['statement_date']=$formatted_statement_date;
				$data['payment_due']=$formatted_due_date;
				$data['user_id']=$user_id;
				$data['summary']='Consignor Sales';
				$data['description']='Grading Fees';
				$data['account_name']='4060';
				$data['line_total']=$statement_final_total;
				$data['tags']='';
				$res[]=$data;
				*/
			}
			}

		}
		
		
	$headings[0]='Consignor_Statement_Number';
	$headings[1]='Statement_Date';
	$headings[2]='Consignor_Payment_Due';
	$headings[3]='User_ID';
	$headings[4]='Summary';
	$headings[5]='Description';
	//$headings[5]='Description';
	$headings[6]='Account_name';
	//$headings[6]='Account_Name';
	$headings[7]='Account_number';
	$headings[8]='Line_Item_Total';
	$headings[9]='Tags';


	$reportoutput = $ilance->admincp->construct_csv_data($res, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "Consignors_summary_details-$timeStamp";
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
	$ilance->template->fetch('main', 'consignors.html', 3);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	

}else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}


function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}

function get_statemant_number($user_id,$statement_date)
{
global $ilance;
$query="SELECT *  FROM " . DB_PREFIX . "consignor_satement WHERE user_id = '".$user_id."' and statement_date='".$statement_date."'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
return $line['id'];
}
}else
{
$query1="INSERT INTO gc_ilance.ilance_consignor_satement (user_id ,statement_date)VALUES ('".$user_id."', '".$statement_date."')";
	$ilance->db->query($query1);
	return $ilance->db->insert_id();
}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>