<?php
require_once('./../functions/config.php');
error_reporting(E_ALL);
$DATETODAY='2013-01-28';

unbookedbuynow_data($DATETODAY);


function unbookedbuynow_data($DATETODAY)
{
global $ilance,$unbooked_buynow;
$html='';
$unbooked_buynow=0;
//$last_monday=last_monday($DATEYESTERDAY);
echo $query="
SELECT sum(i.totalamount-i.taxamount) as unbooked_buynow   FROM " . DB_PREFIX . "invoices i 
left join " . DB_PREFIX . "coins c on c.coin_id=i.projectid and date(i.createdate)< date(c.End_Date) 
left join (select coin_id, CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date) ELSE '0000-00-00 00:00:00' END as last_listed_time from " . DB_PREFIX . "coin_relist where filtered_auctiontype = 'fixed'  group by coin_id ) r on c.coin_id = r.coin_id
WHERE i.buynowid > 0 and i.isfvf=0 and i.isif=0 and i.isbuyerfee=0 and i.ismis=0 and date(c.End_Date)>date('".$DATETODAY."')
and date(i.createdate) > date(ifnull(r.last_listed_time,'0000-00-00 00:00:00') )
";

/*
SELECT i.projectid,i.buynowid,i.user_id,i.totalamount-i.taxamount as amount,i.createdate,c.end_date 
FROM ilance_invoices i 
left join ilance_coins c on c.coin_id=i.projectid and date(i.createdate)< date(c.End_Date) 
left join ilance_invoices bf on bf.projectid=i.projectid and i.user_id=bf.user_id and i.invoice_id!=bf.invoice_id
left join (select coin_id, CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date) ELSE '0000-00-00 00:00:00' END as last_listed_time from ilance_coin_relist where filtered_auctiontype = 'fixed' group by coin_id ) 
r on c.coin_id = r.coin_id 
WHERE i.buynowid > 0 
and i.isfvf=0 and i.isif=0 and i.isbuyerfee=0 and i.ismis=0 
and date(c.End_Date)>date('2013-01-31') 
and date(i.createdate) > date(ifnull(r.last_listed_time,'0000-00-00 00:00:00') ) 



SELECT FROM " . DB_PREFIX . "invoices i
left join " . DB_PREFIX . "coins c on c.coin_id=i.projectid and date(i.createdate)< date(c.End_Date)
left join 
	(select coin_id,user_id,CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time  from ".DB_PREFIX."coin_relist 
	where DATEDIFF(enddate,startbydate)>1 group by coin_id) r on r.coin_id=c.coin_id
WHERE i.buynowid > 0 and i.isfvf=0 and i.isif=0 and i.isbuyerfee=0 and i.ismis=0  and date(c.End_Date)>date('".$DATETODAY."')

*/
//date(r.last_listed_time)>='".$start."' and date(r.last_listed_time)<='".$DATETODAY."'

exit;
echo '<br>';
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
	while($line=$ilance->db->fetch_array($result))
	{
	 $unbooked_buynow=$line['unbooked_buynow'];
	}
}
echo $html.= "\r\nBuy Now Sales Unbooked: $".$unbooked_buynow;
  
return $html;
}


function last_monday($anydate)
{
	/*list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);*/
	//$last_monday=date('Y-m-d', strtotime('last Monday', strtotime($anydate)));
	if( intval(date('w', strtotime($anydate)))==1)
		$last_monday=$anydate;  
	else
		$last_monday=date('Y-m-d', strtotime('last Monday', strtotime($anydate)));
	 
	return $last_monday;
}

?>