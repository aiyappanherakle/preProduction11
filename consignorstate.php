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
        'portfolio',
        'preferences',
        'selling',
        'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
    'inline_edit',
	'flashfix',
	'jquery'
);

error_reporting(E_ALL);

// #### define top header nav ##################################################
$topnavlink = array(
        'preferences'
);

// #### setup script location ##################################################
define('LOCATION', 'preferences');

// #### require backend ########################################################
require_once('./functions/config.php');
$file="consignor_statement_4723.php";
//error_reporting(E_ALL);
 $show['widescreen'] = true;
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[preferences]" => $ilcrumbs["$ilpage[preferences]"]);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{

	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'print')
	{
		$user_details['user_id'] = $_SESSION['ilancedata']['user']['userid'];
		$stmt_date = $ilance->GPC['date'];
		$start=last_monday($stmt_date);
		$settledate = $ilance->GPC['settledate'];
		//echo '<pre>';print_r($ilance->GPC);exit;
		$sql = "SELECT cs.coin_id, o.order_count,i.if_total
from beta_consignor_statement cs
LEFT JOIN beta_coins c on c.coin_id = cs.coin_id
left join beta_ebay_listing e on e.coin_id=cs.coin_id
left join beta_projects p on p.project_id=cs.coin_id

left join (select sum(quantity) as ebay_order_count ,coin_id from beta_ebay_listing_rows where date(enddate) between date('2015-11-16') and date('2016-03-01') group by coin_id) el on el.coin_id=c.coin_id 

left join (select sum(qty) as order_count,project_id,orderdate from beta_buynow_orders o where date(orderdate)>='2015-11-16' and date(orderdate)<=date('2016-03-01') group by project_id) o on cs.coin_id=o.project_id

left join beta_project_bids b on cs.coin_id=b.project_id and (date(b.date_added)>='2015-11-16' and date(b.date_added)<='2016-03-01') 

left join (select i.projectid,i.createdate, sum(CASE WHEN i.invoicetype='escrow' THEN i.amount ELSE 0 END) as escrow_invoice_total, sum( CASE WHEN i.isfvf=1 THEN i.amount ELSE 0 END) as fvf_total, min( CASE WHEN i.invoicetype='escrow' AND i.status='paid' THEN 0 ELSE 1 END) as all_paid, sum( CASE WHEN i.isenhancementfee=1 THEN i.amount ELSE 0 END) as enhancementfee_total, sum( CASE WHEN i.ismis=1 THEN i.amount ELSE 0 END) as mis_total, sum( CASE WHEN i.isif=1 THEN i.amount ELSE 0 END) as if_total from beta_invoices i where (date(i.createdate)>='2015-11-16' and date(i.createdate)<='2016-03-01') group by i.projectid) i on cs.coin_id=i.projectid

WHERE (date(cs.Current_End_Date)>='2015-11-16' and date(cs.Current_End_Date)<='2016-03-01') ";

$sql = "SELECT cs.coin_id, cs.daily_deal, p.bids, cs.daily_deal_date, cs.user_id, 
cs.Create_Date,cs.Current_End_date, cs.filtered_auctiontype,
c.coin_id, c.user_id, CASE WHEN c.site_id=0 THEN 'GC' ELSE 'Ebay' END as site_name, c.End_Date,
p.date_end, c.Title, c.Minimum_bid, c.Buy_it_now, c.Alternate_inventory_No, c.Certification_No, c.project_id, p.filtered_auctiontype, p.winner_user_id, p.hasbuynowwinner, p.insertionfee, p.date_starts, p.buyer_fee, o.order_count, i.escrow_invoice_total, i.fvf_total, i.all_paid, i.enhancementfee_total, i.mis_total, i.if_total, el.ebay_order_count, count(distinct b.bid_id) as bid_count 

from beta_consignor_statement cs
LEFT JOIN beta_coins c on c.coin_id = cs.coin_id
left join beta_ebay_listing e on e.coin_id=cs.coin_id
left join beta_projects p on p.project_id=cs.coin_id

left join (select sum(quantity) as ebay_order_count ,coin_id,order_date from beta_ebay_listing_rows group by coin_id) el on el.coin_id=c.coin_id AND (date(el.order_date)>=date(cs.Create_Date) and date(el.order_date)<=date(cs.Current_End_Date))

left join (select sum(qty) as order_count,project_id,orderdate from beta_buynow_orders o group by project_id) o on cs.coin_id=o.project_id AND (date(o.orderdate)>=date(cs.Create_Date) and date(o.orderdate)<=date(cs.Current_End_Date))

left join beta_project_bids b on cs.coin_id=b.project_id and (date(b.date_added)>=date(cs.Create_Date) and date(b.date_added)<=date(cs.Current_End_Date)) 

left join (select i.projectid,i.createdate, 
sum( CASE WHEN i.invoicetype='escrow' THEN i.amount ELSE 0 END) as escrow_invoice_total, 
sum( CASE WHEN i.isfvf=1 THEN i.amount ELSE 0 END) as fvf_total, 
min( CASE WHEN i.invoicetype='escrow' AND i.status='paid' THEN 0 ELSE 1 END) as all_paid, 
sum( CASE WHEN i.isenhancementfee=1 THEN i.amount ELSE 0 END) as enhancementfee_total, 
sum( CASE WHEN i.ismis=1 THEN i.amount ELSE 0 END) as mis_total, 
sum( CASE WHEN i.isif=1 THEN i.amount ELSE 0 END) as if_total from beta_invoices i 
group by i.projectid) i on cs.coin_id=i.projectid AND (date(i.createdate)>=date(cs.Create_Date) and date(i.createdate)<=date(cs.Current_End_Date))

WHERE (date(cs.Current_End_Date)>='2015-11-23' and date(cs.Current_End_Date)<='2015-11-29')  
AND cs.user_id=15500 GROUP BY cs.coin_id ORDER By cs.coin_id ASC";
echo $sql;exit;
//AND DATEDIFF(cs.Current_End_Date,cs.Create_Date)>1 


// $select=$ilance->db->query($sql);

// while($coin_details=$ilance->db->fetch_array($select, DB_ASSOC))
// {
// 	echo '<pre>';
// 	print_r($coin_details);

// }
// echo $sql;exit;
//exit;
		
		
		define('FPDF_FONTPATH','../font/');
		require('staff/pdftable_1.9/lib/pdftable1.inc.php');
		$p = new PDFTable();
		$ilance->statement = construct_object('api.statement');
		$stmt_qry=$ilance->statement->statement_query($user_details['user_id'],$start,$stmt_date);
		echo $stmt_qry;exit;
		 //$stmt_qry="";
//echo $stmt_qry;exit;		
		$select=$ilance->db->query($sql);
		$data_to_pdf = '';
		$listcount = $ilance->db->num_rows($select);
			if($ilance->db->num_rows($select) > 0)
			 {
			
				$p->AddPage();
				$p->setfont('times','',9);		
				
				 $statement_auction_price_total='';
				 $statement_buyer_fee_total='';
				 $coin_consignor_total='';
				 $statement_final_total='';
				 $statement_listing_fee_total='';
				 $statement_seller_fee_total='';
				 $statement_seller_total='';
				 $statement_buynow_total='';
				
		$sql=" SELECT u.user_id, u.username, u.email,u.Check_Payable FROM " . DB_PREFIX . "users u  WHERE u.user_id='".$user_details['user_id']."' ";

		$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($user_query) > 0)
		{
			while($user_details=$ilance->db->fetch_array($user_query))
			{
				$name = $user_details['username'];
				$email=$user_details['email'];
				$check_payable=$user_details['Check_Payable'];
				$data_to_pdf .= '<table width="100%">
				<tr>
				<td size="13" family="helvetica" style="bold" nowrap><b>Consignor Statement GC - </b>'.$name.'</td>
				</tr>
				<tr>
				<td >My statement on &nbsp;'.date('F d, Y',strtotime($stmt_date)).'</td>
				</tr>					
				<tr>
				<td>&nbsp;
				</td>
				</tr>
				</table>';
			}
		}
				
			
			$data_to_pdf .= '<table width="100%" border="0">
				<tr bgcolor="#CD9C9C"> 
				<td width = "5%">ID</td>
				<td width = "80%">Item Title</td>
				<td width = "5%">Listed</td>	
				<td width = "5%">Bids</td>
				<td width = "5%">Min Bid/<br>Buy Now</td>
				<td width = "5%">Final<br>Price</td>
				<td width = "5%">Listing<br> Fees</td>
				<td width = "5%">Sellers<br> Fees</td>	
				<td width = "5%">Net to Consignor</td>
				</tr>';		
			
						 
			$show['statement'] = true;							
			$row_count = 0;	
			while($coins_list_line=$ilance->db->fetch_array($select))
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
						$statement_auction_price_total+=$coin_final_price;
						$statement_buyer_fee_total+=$coins_list_line['buyer_fee'];
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
						$no_of_buynow=intval($coins_list_line['order_count'])+intval($coins_list_line['ebay_order_count']); 
						//if(intval($coins_list_line['order_count']) > 0 || intval($coins_list_line['ebay_order_count']) > 0)
						//{
							$coin_final_price=$coins_list_line['escrow_invoice_total'];
							$statement_buynow_total+=$coin_final_price;
							
							if($coins_list_line['no_relist_b4_statement']==0)
							{

							}else
							{
								$coin_insertion_fee=0;
							}
							$coin_insertion_fee=$coins_list_line['if_total']+$coins_list_line['enhancementfee_total'];
							$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['mis_total'];
							$all_paid=$coins_list_line['all_paid'];
						//}
						
					break;
					}	
							
					$test4[] = $coins_list_line['Buy_it_now'];
					$test5[] = $coins_list_line['Minimum_bid'];
					$bidtot[] = $no_of_bids;	
					$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
					$statement_final_total+=$coin_final_price;
					$statement_listing_fee_total+=$coin_insertion_fee;
					$statement_seller_fee_total+=$coin_seller_fee;
					$statement_seller_total+=$coin_consignor_total;	
					$data_pdf['coin_id']=$coins_list_line['coin_id'];
					$data_pdf['Title']=$coins_list_line['Title'];
					$data_pdf['Site_Id'] = $coins_list_line['site_name'];

					if($coins_list_line['daily_deal']>0)
						$data_pdf['Title'] .= ' - <font color="#FF0000">( Daily deal )</font>';

					$data_pdf['bids'] = $no_of_bids;
					$data_pdf['minimum_bid'] = $ilance->currency->format_real_no($coins_list_line['Minimum_bid']) .' / '.$ilance->currency->format(isset($coins_list_line['Buy_it_now'])?$coins_list_line['Buy_it_now']:0) ;
					$data_pdf['Certification_No']=$coins_list_line['Certification_No'];
					$data_pdf['Alternate_inventory_No']=$coins_list_line['Alternate_inventory_No'];
					$data_pdf['bidamount']=$ilance->currency->format_real_no($coins_list_line['Minimum_bid']);
					$data_pdf['binamount']=$ilance->currency->format(isset($coins_list_line['Buy_it_now'])?$coins_list_line['Buy_it_now']:0) ;
					$data_pdf['qty']=$no_of_buynow>0?$no_of_buynow:'';
					$data_pdf['fvf'] = $ilance->currency->format($coin_final_price);
					$data_pdf['listing_fee'] = $ilance->currency->format_real_no($coin_insertion_fee,0,false);
					$data_pdf['seller_fee'] = $ilance->currency->format_real_no($coin_seller_fee);
					
					if($coin_consignor_total > 0){
						$data_pdf['net_consignor'] = $ilance->currency->format($coin_consignor_total,$ilconfig['globalserverlocale_defaultcurrency']);
					}
					else{
						$coin_consignor_total_csv=str_replace("-","",$coin_consignor_total);
						$data_pdf['net_consignor'] = $ilance->currency->format_real_no($coin_consignor_total); //'$'.$coin_consignor_total_csv. '.00';
					}
					
					//
					$data_to_pdf .= '<tr>								
										<td>'.$data_pdf['coin_id'].'</td>
										<td width = "45%">
											'.$data_pdf['Title'];
					
					$data_to_pdf .='<br/>Certificate No.: '.$data_pdf['Certification_No'].'';
					
					if (isset($show['altinv']) AND $show['altinv'])
					{
						$data_to_pdf .= '<br/>
									Alt Inventory No.: '.$data_pdf['Alternate_inventory_No'].'';
					}		

					$data_to_pdf .= '</td>								
										<td>'.$data_pdf['Site_Id'].'</td>
										<td>'.$data_pdf['bids'].'</td>
										<td>'.$data_pdf['bidamount'].'&nbsp;/&nbsp;<b> '.$data_pdf['binamount'].'</b></td>
										<td>'.$data_pdf['fvf'].'&nbsp;'.$data_pdf['qty'].'</td>
										<td>'.$data_pdf['listing_fee'].'</td>
										<td>'.$data_pdf['seller_fee'].'</td>
										<td>'.$data_pdf['net_consignor'].'</td>
									</tr>';
						
			 $row_count++;
			 
			 
			}
			
		$advance_received=0;
		$miscellanious_debit=0;
		$miscellanious_credit=0;		
		$statement_total=$statement_seller_total-$advance_received+$miscellanious_debit-$miscellanious_credit;
		$totbinamount = $ilance->currency->format(array_sum($test4),$ilconfig['globalserverlocale_defaultcurrency']);
		$totbidamount = $ilance->currency->format(array_sum($test5),$ilconfig['globalserverlocale_defaultcurrency']);
		$tot_bidbuy = $totbidamount.' / '.$totbinamount;
		$totbids = array_sum($bidtot);		
		$totseller_fee=$ilance->currency->format_real_no($statement_seller_fee_total);
		$totlisting_fee=$ilance->currency->format_real_no($statement_listing_fee_total);
		$totfvf=$ilance->currency->format_real_no($statement_seller_total);
		$totnet_consignor=$ilance->currency->format_real_no($statement_seller_total);
		$total_advance=$ilance->currency->format_real_no($advance_received);
		$tot_mis=$ilance->currency->format_real_no($miscellanious_debit-$miscellanious_credit);
		$lastamount=$ilance->currency->format_real_no($statement_total);		
		$statecount = '('.$listcount.' Items) will settle on '.$settledate .' ('.$lastamount.')';
			
			
		$data_to_pdf .= '<tr><td>&nbsp;</td></tr>
						<tr>
							<td></td>
							<td><b>Gross Total</b></td>
							<td></td>
							<td nowrap><b>'.$totbids.'</b></td>
							<td nowrap><b>'.$tot_bidbuy.'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_final_total).'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_listing_fee_total).'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_seller_fee_total).'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_seller_total).'</b></td>
						</tr>
						<tr>
							<td></td>
							<td><b>Advance </b></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($advance_received).'</b></td>
						</tr>
						<tr>
							<td></td>
							<td nowrap><b>Miscellaneous </b></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($miscellanious_debit-$miscellanious_credit).'</b></td>
						</tr>
						<tr>
							<td></td><td nowrap><b>Net Total</b></td><td></td><td></td><td></td><td></td><td></td><td></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_total).'</b></td>
						</tr>
						</tbody></table>';

			$p->htmltable($data_to_pdf);	

			}
		$p->output('Consignor Statement '.DATETIME24H.'.pdf','D');
				exit;
	}

	$area_title = 'Consignor Statement newwww';
	$page_title = SITE_NAME . ' - ' . 'Statement';
	$user_id = $_SESSION['ilancedata']['user']['userid'];
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * 50) . ',' . 50;		
	 
	
	$SQL2 = "select 
date_format(DATE(c.End_Date + INTERVAL (6 - WEEKDAY(c.End_Date)) DAY),'%M %d, %Y') as show_statement_date,
date(c.End_Date + INTERVAL (6 - WEEKDAY(c.End_Date)) DAY) as statement_date, 
DATE_ADD(date(c.End_Date), INTERVAL IF(DAYOFWEEK(date(c.End_Date))=2, 0, (if(2-DAYOFWEEK(date(c.End_Date))>0,-6, 2-DAYOFWEEK(date(c.End_Date))))) DAY) as start
from 
(SELECT date(end_date) as end_date FROM ".DB_PREFIX."coins WHERE user_id = '".$_SESSION['ilancedata']['user']['userid']."'  group by end_date
union
SELECT date(actual_end_date) as end_date  FROM ".DB_PREFIX."coin_relist WHERE user_id = ".$_SESSION['ilancedata']['user']['userid']." group by enddate) c
where c.end_date!='0000-00-00' 
group by show_statement_date order by c.end_date desc ";

	$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$counter = (intval($ilance->GPC['page']) - 1) * 50;
	$series = isset($ilance->GPC['series'])?$ilance->GPC['series']:0;
	$scriptpageprevnext = $file.'?sef=1';
	$paginationn = '';
	if($number > 0)
		$paginationn = print_pagnation($number, 50, intval($ilance->GPC['page']), $counter, $scriptpageprevnext);
	$res = $ilance->db->query($SQL2.$sqlquery['limit']."");
	$datecount = 0;							            
	if($ilance->db->num_rows($res) > 0)
	{
			while($line = $ilance->db->fetch_array($res))
			{
			
			$stmt_date=$line['statement_date'];
			$start=last_monday($stmt_date);
			$user_details['user_id']=$_SESSION['ilancedata']['user']['userid'];
			$coin_count=$line['coin_count'];
			$item_count = $res_date_co['endcount'];
			$query2="SELECT id,item_count FROM " . DB_PREFIX . "consignor_satement WHERE user_id = ".$user_details['user_id']." and date(statement_date)='".$stmt_date."'";
			 
				$result2=$ilance->db->query($query2);
				if($ilance->db->num_rows($result2))
				{
					while($line2=$ilance->db->fetch_array($result2))
					{
					 if($line2['item_count']==0)
					 {
						// $coin_count=statement_coin_count($user_details,$start,$stmt_date);
						// $query3="update " . DB_PREFIX . "consignor_satement set item_count='".$coin_count."' where id='".$line2['id']."'";
						// $ilance->db->query($query3);
					 }else
					 {
						$coin_count=$line2['item_count'];
					 }
					}
				}else{
				// $coin_count=statement_coin_count($user_details,$start,$stmt_date);
				 
				// $ilance->db->query("insert " . DB_PREFIX . "consignor_satement ( user_id,statement_date,item_count ) values ('".$user_details['user_id']."','".$stmt_date."','".$coin_count."')");
				}
			
			//echo $line['statement_date'];
			$coin_count=statement_coin_count($user_details,$start,$stmt_date);
			$date1 = strtotime($line['statement_date']);
			$date2 = strtotime(date('Y-m-d'));
			if($date1 > $date2)
			{
				$date_down.='<a href="'.$file.'?sef=1&subcmd=print&date='.$line['statement_date'].'"& >'.$line['show_statement_date'].' <b>('.$coin_count.' items) - Pending</b></a> <br/>';	
			}
			else
			{
				$date_down.='<a href="'.$file.'?sef=1&subcmd=print&date='.$line['statement_date'].'"& >'.$line['show_statement_date'].' <b>('.$coin_count.' items)</b></a> <br/>';	
			}
			$datecount++;
			}
	}					

	$pprint_array = array('tot_mis','user_id','date_down','lastamount','total_advance','statecount','date1','date','totbids','totbidamount','totbinamount','totfvf','totlisting_fee','totseller_fee','totnet_consignor','daylist','monthlist','yearlist','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','date_new','paginationn');

	$ilance->template->fetch('main', 'consigner_statement_4723.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('statement'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect='.$file);
	exit();
}

function statement_coin_count($user_details,$start,$stmt_date)
{
global $ilance;
$query1="select count(c.coin_id) as coin_count from  ".DB_PREFIX."coins c 
			left join ".DB_PREFIX."ebay_listing e on e.coin_id=c.coin_id
			left join ".DB_PREFIX."projects p on c.coin_id=p.project_id and p.user_id='".$user_details['user_id']."' and DATEDIFF(date_end,date_starts )>1 
			left join 
				(select coin_id,user_id,count(id) as no_relist_b4_statement,max(actual_end_date) as last_listed_time from ".DB_PREFIX."coin_relist 
				where date(actual_end_date)<='".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
			where  (c.user_id=".$user_details['user_id']." and (
			(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
			(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>1 )) or
			(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."') or
			(date(e.end_date)>='".$start."' and date(e.end_date)<='".$stmt_date."')
			)) GROUP BY c.coin_id";
			$result1=$ilance->db->query($query1);
			if($ilance->db->num_rows($result1))
			{
				$coin_count=$ilance->db->num_rows($result1);
				// while($line1=$ilance->db->fetch_array($result1))
				// {
				// $coin_count=$line1['coin_count'];
				// }
			}
			return $coin_count;
}

function getSundaysList($startDate, $endDate, $weekdayNumber)
{
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    $dateArr = array();

    do
    {
        if(date("w", $startDate) != $weekdayNumber)
        {
            $startDate += (24 * 3600); // add 1 day
        }
    } while(date("w", $startDate) != $weekdayNumber);


    while($startDate <= $endDate)
    {
        $dateArr[] = date('Y-m-d', $startDate);
        $startDate += (7 * 24 * 3600); // add 7 days
    }
	 $dateArr	=	array_reverse($dateArr);
    return($dateArr);
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


