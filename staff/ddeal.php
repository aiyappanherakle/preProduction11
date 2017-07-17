<?php 
require_once('./../functions/config.php');





		$grand_statement_final_total=0;
		$grand_statement_listing_fee_total=0;
		$grand_statement_seller_fee_total=0;
		$grand_statement_seller_total=0;
		$t=0;
  $sql="SELECT d.act_amount,d.offer_amt,d.coin_id,datediff(d.enddate,d.live_date),c.user_id,
date_format(d.live_date,'%m-%d-%Y'),
case  when DAYOFWEEK(d.live_date)=1
then 
		date_format(d.live_date,'%m-%d-%Y')
else	
		date_format(DATE_ADD(d.live_date, INTERVAL (8 - IF(DAYOFWEEK(d.live_date)=1, 8, DAYOFWEEK(d.live_date))) DAY),'%m-%d-%Y')
end as liveweekend,
date_format(d.enddate,'%m-%d-%Y') as actualweekend
FROM ilance_dailydeal d
left join ilance_coins c on c.coin_id=d.coin_id
where DAYOFWEEK(d.enddate)=1 and datediff(d.enddate,d.live_date)>6 and c.user_id>0
ORDER BY d.coin_id  DESC";

$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);


if($ilance->db->num_rows($user_query))
{
while($user_details=$ilance->db->fetch_array($user_query))
	{
		//$stmt_date=$user_details['liveweekend'];
		$stmt_date=$user_details['actualweekend'];
		
	$stmt_date=alter_date($stmt_date);
	$start=last_monday($stmt_date);
	//from coin relist table
	 unset($list);
	 $html='';
	//$list=get_any_relist_or_buynow($user_details['user_id'],$start,$stmt_date);
//$any_relist_coins=count($list)>0?" or (c.coin_id in (".implode(",",$list)."))":'';
 $prime_sql="select
c.coin_id,
c.cost,
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
where  c.coin_id='".$user_details['coin_id']."' and (c.user_id=".$user_details['user_id']." and (
(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."'))    )
group by c.coin_id ORDER BY  cat.Orderno ASC";
 
 	$coins_list_query=$ilance->db->query($prime_sql, 0, null, __FILE__, __LINE__);
	$lhs=0;
 	if($ilance->db->num_rows($coins_list_query)==1)
	{ 
	 $lhs=1;
	}	
	
	
	$stmt_date=$user_details['liveweekend'];
		//$stmt_date=$user_details['actualweekend'];
		
	$stmt_date=alter_date($stmt_date);
	$start=last_monday($stmt_date);
	//from coin relist table
	 unset($list);
	 $html='';
$prime_sql="select
c.coin_id,
c.cost,
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
where  c.coin_id='".$user_details['coin_id']."' and (c.user_id=".$user_details['user_id']." and (
(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."'))    )
group by c.coin_id ORDER BY  cat.Orderno ASC";
 
	$coins_list_query=$ilance->db->query($prime_sql, 0, null, __FILE__, __LINE__);
	$rhs=0;
 	if($ilance->db->num_rows($coins_list_query)==1)
	{ 
	 $rhs=1;
	}	
	if($lhs==$rhs and $rhs==1)
	{
	$amt=$user_details['act_amount']-$user_details['offer_amt'];
	echo $user_details['coin_id'].','.$user_details['user_id'].','.$amt;
	}else
	{
	
	}
	echo '<br>';
	
	}
	
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
function alter_date($somedate)
{
list($m,$d,$y)=explode("-",$somedate);
return $y.'-'.$m.'-'.$d;
}

	?>