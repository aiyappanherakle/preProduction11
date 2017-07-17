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

/**
* Watchlist class to perform the majority of watchlist and notification tasks within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class statement
{
        /*
        * Function to send a watchlist notification based on a particular notification type
        *
        * @param       
        *
        * @return      
        */
        function statement_query($user_id, $start, $stmt_date)
        {
                global $ilance, $myapi, $ilconfig;
				$user_details['user_id']=$user_id;

				return $select="select
c.coin_id,
c.user_id,
CASE WHEN c.site_id=0 THEN 'GC' ELSE 'Ebay' END  as site_name,
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
el.ebay_order_count,
count(distinct b.bid_id) as bid_count
from 
	".DB_PREFIX."coins c 
	left join ".DB_PREFIX."ebay_listing e on e.coin_id=c.coin_id
	left join (select sum(quantity) as ebay_order_count ,coin_id from ".DB_PREFIX."ebay_listing_rows where  date(enddate) between date('".$start."') and  date('".$stmt_date."') group by coin_id) el on el.coin_id=c.coin_id
left join
	".DB_PREFIX."projects p on c.coin_id=p.project_id and p.user_id='".$user_details['user_id']."' and DATEDIFF(date_end,date_starts )>1 
left join 
	(select coin_id,user_id,count(id) as no_relist_b4_statement,max(actual_end_date) as last_listed_time from ".DB_PREFIX."coin_relist 
	where date(actual_end_date)<='".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
left join
	(select sum(qty) as order_count,project_id,orderdate from ".DB_PREFIX."buynow_orders o where owner_id='".$user_details['user_id']."' and date(orderdate)<='".$stmt_date."' and date(orderdate)>date(
	(
	select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ".DB_PREFIX."coin_relist 
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
	select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ".DB_PREFIX."coin_relist 
	where date(actual_end_date)<'".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 and coin_id=i.projectid order by actual_end_date desc limit 1
	))  group by i.projectid) i on c.coin_id=i.projectid 	
left join ".DB_PREFIX."catalog_coin cat on cat.PCGS=c.pcgs
where  (c.user_id=".$user_details['user_id']." and (
(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."') or
(date(e.end_date)>='".$start."' and date(e.end_date)<='".$stmt_date."')
))
group by c.coin_id ORDER BY  cat.Orderno ASC";
 
        
		}

		function statement_query_fix($user_id, $start, $stmt_date)
        {
                global $ilance, $myapi, $ilconfig;
				$user_details['user_id']=$user_id;

				return $select="select
c.coin_id,
c.user_id,
CASE WHEN c.site_id=0 THEN 'GC' ELSE 'Ebay' END  as site_name,
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
count(distinct o.buyer_id) as buyer_id_count,
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
el.ebay_order_count,
count(distinct b.bid_id) as bid_count
from 
	".DB_PREFIX."coins c 
	left join ".DB_PREFIX."ebay_listing e on e.coin_id=c.coin_id
	left join (select sum(quantity) as ebay_order_count ,coin_id from ".DB_PREFIX."ebay_listing_rows where  date(enddate) between date('".$start."') and  date('".$stmt_date."') group by coin_id) el on el.coin_id=c.coin_id
left join
	".DB_PREFIX."projects p on c.coin_id=p.project_id and p.user_id='".$user_details['user_id']."' and DATEDIFF(date_end,date_starts )>1 
left join 
	(select coin_id,user_id,count(id) as no_relist_b4_statement,max(actual_end_date) as last_listed_time from ".DB_PREFIX."coin_relist 
	where date(actual_end_date)<='".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
left join
	(select sum(qty) as order_count,project_id,orderdate from ".DB_PREFIX."buynow_orders o where owner_id='".$user_details['user_id']."' and date(orderdate)<='".$stmt_date."' and date(orderdate)>date(
	(
	select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ".DB_PREFIX."coin_relist 
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
	select CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ".DB_PREFIX."coin_relist 
	where date(actual_end_date)<'".$stmt_date."' and user_id='".$user_details['user_id']."' and DATEDIFF(enddate,startbydate)>1 and coin_id=i.projectid order by actual_end_date desc limit 1
	))  group by i.projectid) i on c.coin_id=i.projectid 	
left join ".DB_PREFIX."catalog_coin cat on cat.PCGS=c.pcgs
where  (c.user_id=".$user_details['user_id']." and (
(date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$stmt_date."') or 
(date(c.End_Date)>='".$start."' and date(c.End_Date)<='".$stmt_date."' and (c.project_id>0 or c.relist_count>0 )) or
(date(p.date_end)>='".$start."' and date(p.date_end)<='".$stmt_date."') or
(date(e.end_date)>='".$start."' and date(e.end_date)<='".$stmt_date."')
))
group by c.coin_id ORDER BY  cat.Orderno ASC";
 
        
		}

        
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>