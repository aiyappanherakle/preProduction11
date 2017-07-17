<?php 
require_once('./../functions/config.php');
define('FPDF_FONTPATH','../font/');

//require('pdftable_1.9/lib/pdftable.inc.php');
require('pdftable_1.9/lib/pdftable1.inc.php');
$p = new PDFTable();

if (empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo "login to cont";exit;
}
error_reporting(E_ALL);
//$ilance->GPC['user_id']=$ilance->GPC['user_id']?$ilance->GPC['user_id']:4982;
//$stmt_date=isset($ilance->GPC['start_date'])?$ilance->GPC['start_date']:'';

if(isset($ilance->GPC['start_date']))
{
$enddates_list[0]=$ilance->GPC['start_date'];
}elseif(isset($ilance->GPC['user_id']))
{
$enddates_list=get_all_end_dates($ilance->GPC['user_id']);
}else
{
echo "either a date(to get all users) or user id(to get all statements for that user) is must";
exit;
}

foreach($enddates_list as $stmt_date)
{
$stmt_date=alter_date($stmt_date);
$start=last_monday($stmt_date);
$user_where=isset($ilance->GPC['user_id'])?'  WHERE  u.user_id='.$ilance->GPC['user_id']:'';


		$grand_statement_final_total=0;
		$grand_statement_listing_fee_total=0;
		$grand_statement_seller_fee_total=0;
		$grand_statement_seller_total=0;
		$t=0;
 if(isset($ilance->GPC['user_id']) and $ilance->GPC['user_id']>0)
		{
	 $sql="SELECT u.user_id, u.username, u.email,u.first_name,u.last_name,u.address,u.address2,u.city,u.state,u.zip_code,u.Check_Payable
		FROM ilance_users u
		 ".$user_where." 
		GROUP BY u.user_id order by u.first_name";
		//exit;
		}
		else
		{
	  $sql="SELECT u.user_id, u.username, u.email,u.first_name,u.last_name,u.address,u.address2,u.city,u.state,u.zip_code,u.Check_Payable,
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
		LEFT JOIN ilance_invoices i ON i.user_id = u.user_id and i.ismis =1 and date(i.statement_date)<='".$stmt_date."' and date(i.statement_date)>='".$start."'
		 ".$user_where." 
		GROUP BY u.user_id having coin_count>0 or relist_coin_count>0 or buynow_coin_count>0 order by u.first_name";
		
		//exit;
		}
$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);


if($ilance->db->num_rows($user_query))
{
while($user_details=$ilance->db->fetch_array($user_query))
	{
	//from coin relist table
	 unset($list);
	 $html='';
	//$list=get_any_relist_or_buynow($user_details['user_id'],$start,$stmt_date);
//$any_relist_coins=count($list)>0?" or (c.coin_id in (".implode(",",$list)."))":'';
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
p.buyer_fee,
o.order_count,
i.escrow_invoice_total,
i.fvf_total,
i.all_paid,
i.enhancementfee_total,
i.mis_total,
i.if_total,
r.no_relist_b4_statement,
cat.Orderno,
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
left join ".DB_PREFIX."catalog_coin cat on cat.PCGS=c.pcgs
where  (c.user_id=".$user_details['user_id']." and (
(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."'))    )
group by c.coin_id ORDER BY  cat.Orderno ASC";
 
 
 echo $prime_sql;exit;
  //removed from line 91   and date(i.createdate) <= '".$stmt_date."' and date(i.createdate)>='".$start."'
	//from coinss table
	$coins_list_query=$ilance->db->query($prime_sql, 0, null, __FILE__, __LINE__);
 	if($ilance->db->num_rows($coins_list_query)>0)
	{ 
	$html.=get_header_pdf($user_details,$stmt_date);
	
		$statement_final_total=0;
		$statement_listing_fee_total=0;
		$statement_seller_fee_total=0;
		$statement_seller_total=0;
		$statement_auction_price_total=0;
		$statement_buynow_total=0;
		$statement_buyer_fee_total=0;
		$sl=1;
		while($coins_list_line=$ilance->db->fetch_array($coins_list_query))
		{
		
		//echo $coins_list_line['coin_id'].'|'.$coins_list_line['no_relist_b4_statement'].'</br>';
		//continue;
		
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
					
					/*$coin_final_price=$coins_list_line['a_escrow_invoice_total'];
					//it can only be charged for first listing
					if($coins_list_line['no_relist_b4_statement']==0)
					{
						$coin_insertion_fee=$coins_list_line['a_if_total'];
					}else
					{
						$coin_insertion_fee=0;
					}
					$coin_seller_fee=$coins_list_line['a_fvf_total']+$coins_list_line['a_enhancementfee_total']+$coins_list_line['a_mis_total'];
					$all_paid=$coins_list_line['a_all_paid'];
					*/
					
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
					$no_of_buynow=intval($coins_list_line['order_count']); 
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
				break;
				}	
				
			$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
			$statement_final_total+=$coin_final_price;
			$statement_listing_fee_total+=$coin_insertion_fee;
			$statement_seller_fee_total+=$coin_seller_fee;
			$statement_seller_total+=$coin_consignor_total;	
			
			//echo $coins_list_line['coin_id'];
		//	echo '<br>';			
			 
		$html.='<tr>';
//		$html.='<td size="9">'.$sl.'/'.$coins_list_line['Orderno'].'</td>';
//		$html.='<td size="9">'.$sl.'</td>';
		$html.='<td size="9">'.$coins_list_line['coin_id'].'</td>';
		$html.='<td width = "80%" size="9">'.$coins_list_line['Title'].'<br />Cert #: '.$coins_list_line['Certification_No'].'&nbsp;&nbsp;';
		$html.='Alt Inv#:'.$coins_list_line['Alternate_inventory_No'].'</td>';
		$html.='<td size="9">GC</td>';
		$html.='<td size="9">'.$no_of_bids.'/<br>'.$no_of_buynow.'</td>';
		$html.='<td size="9">'.$ilance->currency->format_real_no($coins_list_line['Minimum_bid']).'/<br>'. $ilance->currency->format(isset($coins_list_line['Buy_it_now'])?$coins_list_line['Buy_it_now']:0) .'</td>';
		$html.='<td size="9">'.$ilance->currency->format_real_no($coin_final_price).'</td>';	
		$html.='<td size="9">'.$ilance->currency->format_real_no($coin_insertion_fee,0,false).'</td>';	
		$html.='<td size="9">'.$ilance->currency->format_real_no($coin_seller_fee).'</td>';	
			$disply_paid=($all_paid and $ilance->GPC['option']=='display' and $coin_consignor_total>0)?'<font size="9" color="#FF0000">*</font>':'';
		$html.='<td size="9">'.$ilance->currency->format_real_no($coin_consignor_total).$disply_paid.'</td>';
		$html.='</tr>';
			$sl++;
			
		}
		
		$html.='<tr>';
		$html.='<td colspan="5"></td>';
		$html.='<td size="9">'.$ilance->currency->format_real_no($statement_final_total)		.'</td>';
		$html.='<td size="9">'.$ilance->currency->format_real_no($statement_listing_fee_total).'</td>';
		$html.='<td size="9">'.$ilance->currency->format_real_no($statement_seller_fee_total).'</td>';
		$html.='<td size="9">'.$ilance->currency->format_real_no($statement_seller_total)	.'</td>';
		$html.='</tr>';
		
		$advance_received=0;
		$miscellanious_debit=0;
		$miscellanious_credit=0;
	 	
		$advance_received=$user_details['adv_amount'];
		$miscellanious_debit= $user_details['misc_debit'];
		$miscellanious_credit= $user_details['misc_credit'];
		
		$statement_total=$statement_seller_total-$advance_received+$miscellanious_debit-$miscellanious_credit;
		
		$html.='<tr><td colspan=8 align="right" size="9">Advance</td><td size="9">'.$ilance->currency->format_real_no($advance_received).'</td></tr>';
		$html.='<tr><td colspan=8 align="right" size="9">Miscellaneous</td><td size="9">'.$ilance->currency->format_real_no($miscellanious_debit-$miscellanious_credit).'</td></tr>';
		$html.='<tr><td colspan=8 align="right" size="9">Paid</td><td size="9">'.$ilance->currency->format_real_no(0).'</td></tr>';
		$html.='<tr><td colspan=8 align="right" size="9">Balance</td><td size="9">'.$ilance->currency->format_real_no($statement_total).'</td></tr>';
		$html.='<tr><td colspan=9 size="9">Thank you for consigning to GreatCollections.<br>We appreciate your business.</td></tr></table>';
		$p->AddPage();
		$p->setfont('times','',10);		
	//	echo $html;
	$p->htmltable($html);	
		
		$grand_statement_final_total+=$statement_final_total;
		$grand_statement_auction_price_total+=$statement_auction_price_total;
		$grand_statement_buynow_total+=$statement_buynow_total;
		$grand_statement_buyer_fee_total+=$statement_buyer_fee_total;
		$grand_statement_listing_fee_total+=$statement_listing_fee_total;
		$grand_statement_seller_fee_total+=$statement_seller_fee_total;
		$grand_statement_seller_total+=$statement_seller_total;
			
	}else
	{
	 
	}
	
	
	}

	$html='<table><tr>
			<td size="9"><strong>Total</strong></td><td></td></tr><tr>
			<td size="9">Total FinalPrice</td><td>'.$ilance->currency->format_real_no($grand_statement_final_total).'</td></tr><tr>
			<td size="9">Total Auction price</td><td>'.$ilance->currency->format_real_no($grand_statement_auction_price_total).'</td></tr><tr>
			<td size="9">Total Buynow Price</td><td>'.$ilance->currency->format_real_no($grand_statement_buynow_total).'</td></tr><tr>
			<td size="9">Total Buyer Fees</td><td>'.$ilance->currency->format_real_no($grand_statement_buyer_fee_total).'</td></tr><tr>
			<td size="9">Total Listing Fees</td><td>'.$ilance->currency->format_real_no($grand_statement_listing_fee_total).'</td></tr><tr>
			<td size="9">Total Seller Fees</td><td>'.$ilance->currency->format_real_no($grand_statement_seller_fee_total).'</td></tr><tr>
			<td size="9">Total Net to Consignor</td><td>'.$ilance->currency->format_real_no($grand_statement_seller_total).'</td></tr>
			</table>';
			$p->AddPage();
		$p->setfont('times','',10);	
	//	echo $html;	
		$p->htmltable($html);
}

}
$p->output('Statement_new_'.DATETIME24H.'.pdf','D');

//echo $html;


exit;



function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}
function alter_date($somedate)
{
list($m,$d,$y)=explode("-",$somedate);
return $y.'-'.$m.'-'.$d;
}
function get_header_pdf($FETCH_USER,$end_date)
{
$name = $FETCH_USER['username'];
$email=$FETCH_USER['email'];
$check_payable=$FETCH_USER['Check_Payable'];
$first_name=$FETCH_USER['first_name'];
$last_name=$FETCH_USER['last_name'];
$address=$FETCH_USER['address'];
$address2=$FETCH_USER['address2'];
$city=$FETCH_USER['city'];
$state=$FETCH_USER['state'];
$zipcode=$FETCH_USER['zip_code'];
$new_header = '<table width="100%">
<tr>
<td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
<td>&nbsp;</td>
<td size="13" family="helvetica" style="bold" nowrap><b>Consignor Statement</b></td>
</tr>
<tr>
<td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
17500 Red Hill Avenue, Suite 160, Irvine, CA 92614-7290<br>
Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
E-mail: info@greatcollections.com</td>
<td >&nbsp;</td>
<td >Date of Sale:&nbsp;'.date('F d, Y',strtotime($end_date)).'</td>
</tr>					
<tr >
<td size="12">&nbsp;Consignor Username : '.$name.' <br>E-mail : '.$email;
if(strlen($check_payable)>0)
$new_header.='<br>Check Payable : '.$check_payable;
$new_header.='</td>
</tr>
<tr >
<td size="12"><br>'.$first_name.' &nbsp; '.$last_name.'<br>'.$address.'<br>';
if($address2 != '')
{
	$new_header.=$address2.'<br>';
}
$new_header.=$city.' &nbsp; '.$state.' &nbsp; '.$zipcode.' </td>
</tr>
<tr>
<td>&nbsp;
</td>
</tr>
</table><table width="100%" border=0>
<tr bgcolor="#CD9C9C">
<!--<td></td>-->
<td size="9">ID</td>
<td size="9">Item Title</td>
<td size="9">Listed</td>	
<td size="9">Bids/<br>Buy</td>
<td size="9">Min Bid/<br>Buy Now</td>
<td size="9">Final<br>Price</td>
<td size="9">Listing<br> Fees</td>
<td size="9">Sellers<br> Fees</td>	
<td size="9">Net to Consignor</td>
</tr>';
return $new_header;
}
		 
		function lastlisted_date($project_id,$stmt_date)
		{
		global $ilance;
			$query=$ilance->db->query("select actual_end_date from ".DB_PREFIX."coin_relist where coin_id='".$project_id."' and date(actual_end_date)<'".$stmt_date."' order by enddate DESC limit 1", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($query)>0)
			{
				while($line=$ilance->db->fetch_array($query))
				{
				return $line['actual_end_date'];
				}
			}else
			{
			return false;
			}
		}
		function get_any_relist_or_buynow($user_id,$start,$stmt_date)
		{
		global $ilance;
		 $list=array();
		 //$coin_relist_list_query=$ilance->db->query("select * from ".DB_PREFIX."coin_relist where date(actual_end_date)>='".$start."' and date(actual_end_date)<='".$stmt_date."' and user_id='".$user_id."' group by coin_id", 0, null, __FILE__, __LINE__);
			$coin_relist_list_query=$ilance->db->query("select * from ".DB_PREFIX."coin_relist where  date(actual_end_date)='".$stmt_date."' and user_id='".$user_id."' group by coin_id", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($coin_relist_list_query)>0)
			{
				while($coin_relist_list_line=$ilance->db->fetch_array($coin_relist_list_query))
				{
				$list[]=$coin_relist_list_line['coin_id'];
				}
			} 
		 
			$coin_relist_list_query=$ilance->db->query("select project_id from ".DB_PREFIX."projects where  date(date_end)='".$stmt_date."' and user_id='".$user_id."'", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($coin_relist_list_query)>0)
			{
				while($coin_relist_list_line=$ilance->db->fetch_array($coin_relist_list_query))
				{
				 $list[$i++]=$coin_relist_list_line['project_id'];
				}
			} 
			/*
			//suku removed bug 1549 
			$buynow_list_query=$ilance->db->query("select project_id from ".DB_PREFIX."buynow_orders where date(orderdate)>='".$start."' and date(orderdate)<='".$stmt_date."' and owner_id='".$user_id."' group by project_id  ", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($buynow_list_query)>0)
			{
				while($buynow_list_line=$ilance->db->fetch_array($buynow_list_query))
				{
				$list[]=$buynow_list_line['project_id'];
				}
			}
			*/
 
			return array_unique($list);
		}
		function get_all_end_dates($user_id)
		{
		global $ilance;
			$query=$ilance->db->query("select End_Date from ".DB_PREFIX."coins where user_id='".$user_id."' and End_Date!='0000-00-00 00:00:00' and End_Date<='".DATETIME24H."' and DAYOFWEEK(End_Date)='1' group by date(End_Date)", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($query)>0)
			{
				while($line=$ilance->db->fetch_array($query))
				{
				list($date,$time)=explode(" ",$line['End_Date']);
				$end_date_list[]=strtotime($date);
				}
			}
			
			$query=$ilance->db->query("select 	actual_end_date from ".DB_PREFIX."coin_relist where user_id='".$user_id."' and actual_end_date!='0000-00-00 00:00:00' and actual_end_date<='".DATETIME24H."' and DAYOFWEEK(actual_end_date)='1'  group by date(actual_end_date)", 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($query)>0)
			{
				while($line=$ilance->db->fetch_array($query))
				{
				list($date,$time)=explode(" ",$line['actual_end_date']);
				$end_date_list1[]=strtotime($date);
				}
			} 
			/*list($date,$time)=explode(" ",$line['actual_end_date']);
				list($y,$m,$d)=explode("-",$date);
				$m.'-'.$d.'-'.$y;*/
				$merged=array_merge($end_date_list,$end_date_list1);
			rsort($merged);
			$list= array_unique($merged);
			
			foreach($list as $date)
			{
			$last_list[]=date("m-d-Y", $date);
			}
			
			return $last_list;
		}
?>