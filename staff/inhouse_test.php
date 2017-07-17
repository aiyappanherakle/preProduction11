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
// #### require backend ########################################################
require_once('./../functions/config.php');
//error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$html='';
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'calc_inhouse_profit') 
	{ 
		$stmt_date=$ilance->GPC['start_date'];
		$start=last_monday($stmt_date);
		$user_where='  WHERE  u.house_account=1';
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
		c.cost,
		count(distinct b.bid_id) as bid_count,
		bf.totalbuyerfee
		from 
			".DB_PREFIX."coins c 
		left join
			".DB_PREFIX."projects p on c.coin_id=p.project_id and p.user_id='".$user_details['user_id']."' and DATEDIFF(date_end,date_starts )>1 
		left join 
			(select coin_id,user_id,count(id) as no_relist_b4_statement,max(actual_end_date) as last_listed_time from ".DB_PREFIX."coin_relist 
			where date(actual_end_date)<='".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
		left join
			(select sum(qty) as order_count,project_id,orderdate,buyer_id from ".DB_PREFIX."buynow_orders o where owner_id='".$user_details['user_id']."' and date(orderdate)<='".$stmt_date."' and date(orderdate)>date(
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
		left join (select sum(bfi.totalamount) as totalbuyerfee,bfi.projectid,bfi.user_id,bfi.totalamount from ".DB_PREFIX."invoices bfi where bfi.isbuyerfee=1 and date(bfi.createdate)<='".$stmt_date."' and date(bfi.createdate)>date((
			select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ilance_coin_relist 
			where date(actual_end_date)<'".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 and coin_id=bfi.projectid order by actual_end_date desc limit 1
			)) group by bfi.invoiceid) bf on bf.projectid=c.coin_id
		
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
				$buyerfee=0;
				$cost_total=0;
				$statement_listing_fee_total=0;
				$statement_seller_fee_total=0;
				$statement_seller_total=0;
				$sl=1;
				$hammer_sold_item='';
				$sno_count=1;
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
							if($no_of_bids>0)
							$cost=$coins_list_line['cost'];
							else
							$cost=0;
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
							$cost=$coins_list_line['cost']*$no_of_buynow;
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
					$buyerfee+=$coins_list_line['totalbuyerfee'];
					$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
					$cost_total+=$cost;
					$statement_final_total+=$coin_final_price;
					if(!empty($coin_final_price))
					{
						$hammer_sold_item.='<tr><td>'.$sno_count.'</td><td>'.$coins_list_line['coin_id'].'</td>';
						$hammer_sold_item.='<td>'.$coin_final_price.'</td></tr>';
						$sno_count++;
					}
					$statement_listing_fee_total+=$coin_insertion_fee;
					$statement_seller_fee_total+=$coin_seller_fee;
					$statement_seller_total+=$coin_consignor_total;	
				}
				$profit_loss=$statement_final_total-$cost_total;
				$html.= '<br>UserId: '.$user_id;
				$html.= '<br>Hammer Sold:'.$statement_final_total;
				$html.= '<br>Buyers Fees Charged:'.$buyerfee;
				$html.= '<br>Cost of items Sold:'.$cost_total;
				$html.= '<br>Profit/loss:'.$profit_loss;
				$html.='<br><br><table border="1" style="width:300px;">';
				$html.='<tr><td>S.NO</td><td>ITEM</td><td>HAMMER PRICE</td></tr>';
				$html.=$hammer_sold_item;				
				$html.='</table>';
				
			}
			}

		}
		
	}
	 
	 
	$pprint_array = array('html','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
    ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'inhouse.html', 2);
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


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>