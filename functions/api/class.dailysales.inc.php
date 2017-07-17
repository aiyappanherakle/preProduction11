<?php 
class dailysales
{
public $payment_per_method=array();
public $reportdate='';
public $total_payments='';
public $total_amount='';
public $total_shipping_cost='';
public $total_tax='';
public $buyer_fee_total='';
public $inhouse_hammer_total='';
public $noninhouse_hammer_total='';
public $total_revenue='';
public $unpaid_invoices_buyerfees='';
public $unpaid_item_amount='';
public $pending_total='';
public $checkedout_invoices_buyerfees='';
public $checkedout_amount='';
public $checked_out_total='';
public $total_Ar='';
public $house_acc_sold_coin_cost='';
public $profit_sold_house_acc_coin='';
public $remaining_house_acc_inventory='';
public $unbooked_buynow='';
public $unpaid_invoices_buyerfeesb4_13='';
public $unpaid_invoices_buyerfees_after_13='';
public $unpaid_invoices_buyerfees_14='';
public $unpaid_invoices_buyerfees_15='';
public $unpaid_invoices_buyerfees_16='';
public $unpaid_invoices_buyerfees_17='';
public $unpaid_item_amountb4_13='';
public $unpaid_item_amount_after_13='';
public $unpaid_item_amount_14='';
public $unpaid_item_amount_15='';
public $unpaid_item_amount_16='';
public $unpaid_item_amount_17='';
public $pending_total_2012='';
public $pending_total_2013='';
public $pending_total_2014='';
public $pending_total_2015='';
public $pending_total_2016='';
public $pending_total_2017='';


function gethtml($DATEYESTERDAY)
{
   global $ilance, $myapi, $ilconfig, $reportdate;
$reportdate=$DATEYESTERDAY;
$html=$this->payment_data($DATEYESTERDAY);
$html.=$this->unbookedbuynow_data(DATETODAY);
$html.=$this->sales_data($DATEYESTERDAY);
$html.=$this->ar_data($DATEYESTERDAY);
 
 return $html;
}
function log()
{
global $ilance;
global $reportdate;
global $payment_per_method;
global $total_payments;
global $total_amount; 
global $total_shipping_cost;
global $total_tax;
global $buyer_fee_total;
global $inhouse_hammer_total;
global $noninhouse_hammer_total;
global $total_revenue;
global $unpaid_invoices_buyerfees;
global $unpaid_item_amount;
global $pending_total;
global $checkedout_invoices_buyerfees;
global $checkedout_amount;
global $checked_out_total;
global $total_Ar;
global $house_acc_sold_coin_cost;
global $profit_sold_house_acc_coin;
global $remaining_house_acc_inventory;
global $unbooked_buynow;
global $unpaid_invoices_buyerfeesb4_13;
global $unpaid_invoices_buyerfees_after_13;
global $unpaid_invoices_buyerfees_14;
global $unpaid_invoices_buyerfees_15;
global $unpaid_invoices_buyerfees_16;
global $unpaid_invoices_buyerfees_17;
global $unpaid_item_amountb4_13;
global $unpaid_item_amount_after_13;
global $unpaid_item_amount_14;
global $unpaid_item_amount_15;
global $unpaid_item_amount_16;
global $unpaid_item_amount_17;
global $pending_total_2012;
global $pending_total_2013;
global $pending_total_2014;
global $pending_total_2015;
global $pending_total_2016;
global $pending_total_2017;

echo $query="insert into ". DB_PREFIX . "daily_report_split (report_date,payment_per_method,total_payments,total_amount,total_shipping_cost,total_tax,buyer_fee_total,inhouse_hammer_total,noninhouse_hammer_total,total_revenue,unpaid_invoices_buyerfeesb4_13,unpaid_invoices_buyerfees_after_13,unpaid_invoices_buyerfees_14,unpaid_invoices_buyerfees_15,unpaid_invoices_buyerfees_16,unpaid_invoices_buyerfees_17,unpaid_item_amountb4_13,unpaid_item_amount_after_13,unpaid_item_amount_14,unpaid_item_amount_15,unpaid_item_amount_16,unpaid_item_amount_17,pending_total_2012,pending_total_2013,pending_total_2014,pending_total_2015,pending_total_2016,pending_total_2017,checkedout_invoices_buyerfees,checkedout_amount,checked_out_total,total_Ar,house_acc_sold_coin_cost,profit_sold_house_acc_coin,remaining_house_acc_inventory,unbooked_buynow) values ('".$reportdate."',
'".serialize($payment_per_method)."',
'".$total_payments."',
'".$total_amount."',
'".$total_shipping_cost."',
'".$total_tax."',
'".$buyer_fee_total."',
'".$inhouse_hammer_total."',
'".$noninhouse_hammer_total."',
'".$total_revenue."',
'".$unpaid_invoices_buyerfeesb4_13."',
'".$unpaid_invoices_buyerfees_after_13."',
'".$unpaid_invoices_buyerfees_14."',
'".$unpaid_invoices_buyerfees_15."',
'".$unpaid_invoices_buyerfees_16."',
'".$unpaid_invoices_buyerfees_17."',
'".$unpaid_item_amountb4_13."',
'".$unpaid_item_amount_after_13."',
'".$unpaid_item_amount_14."',
'".$unpaid_item_amount_15."',
'".$unpaid_item_amount_16."',
'".$unpaid_item_amount_17."',
'".$pending_total_2012."',
'".$pending_total_2013."',
'".$pending_total_2014."',
'".$pending_total_2015."',
'".$pending_total_2016."',
'".$pending_total_2017."',
'".$checkedout_invoices_buyerfees."',
'".$checkedout_amount."',
'".$checked_out_total."',
'".$total_Ar."',
'".$house_acc_sold_coin_cost."',
'".$profit_sold_house_acc_coin."',
'".$remaining_house_acc_inventory."',
'".$unbooked_buynow."')";

$result=$ilance->db->query($query);
}
function ar_data($DATEYESTERDAY)
{
global $ilance,$unpaid_invoices_buyerfees,$unpaid_item_amount,$pending_total,$checkedout_amount,$checked_out_total,$total_Ar;
global $unpaid_invoices_buyerfeesb4_13;
global $unpaid_invoices_buyerfees_after_13;
global $unpaid_invoices_buyerfees_14;
global $unpaid_invoices_buyerfees_15;
global $unpaid_invoices_buyerfees_16;
global $unpaid_invoices_buyerfees_17;
global $unpaid_item_amountb4_13;
global $unpaid_item_amount_after_13;
global $unpaid_item_amount_14;
global $unpaid_item_amount_15;
global $unpaid_item_amount_16;
global $unpaid_item_amount_17;
global $pending_total_2012;
global $pending_total_2013;
global $pending_total_2014;
global $pending_total_2015;
global $pending_total_2016;
global $pending_total_2017;
$html='';


$unpaid_invoices_buyerfees_query="SELECT sum(CASE WHEN IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfees,
sum(CASE WHEN i.createdate<'2013-1-1 00:00:00' and IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfeesb4_13,
sum(CASE WHEN year(i.createdate)='2013' and IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfees_after_13,
sum(CASE WHEN year(i.createdate)='2014' and IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfees_14,
sum(CASE WHEN year(i.createdate)='2015' and IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfees_15,
sum(CASE WHEN year(i.createdate)='2016' and IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfees_16,
sum(CASE WHEN year(i.createdate)='2017' and IFNULL(n.invoiceid,0) THEN i.totalamount ELSE 0 END) as unpaid_invoices_buyerfees_17
 FROM ". DB_PREFIX . "invoices i
left join (select invoiceid ,user_id,projectid,totalamount,p2b_user_id from ". DB_PREFIX . "invoices where isfvf=0 and isif=0 and isenhancementfee=0 and ismis=0 and status='unpaid' and combine_project='' group by invoiceid) n on i.user_id=n.user_id and i.projectid=n.projectid  
left join ". DB_PREFIX . "users u on u.user_id=i.user_id
where i.isbuyerfee=1 and u.user_id>0 ORDER BY i.invoiceid  DESC";


$result=$ilance->db->query($unpaid_invoices_buyerfees_query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
$unpaid_invoices_buyerfees= $line['unpaid_invoices_buyerfees'];
$unpaid_invoices_buyerfeesb4_13=$line['unpaid_invoices_buyerfeesb4_13'];
$unpaid_invoices_buyerfees_after_13=$line['unpaid_invoices_buyerfees_after_13'];
$unpaid_invoices_buyerfees_14=$line['unpaid_invoices_buyerfees_14'];
$unpaid_invoices_buyerfees_15=$line['unpaid_invoices_buyerfees_15'];
$unpaid_invoices_buyerfees_16=$line['unpaid_invoices_buyerfees_16'];
$unpaid_invoices_buyerfees_17=$line['unpaid_invoices_buyerfees_17'];
}
}

//unpaid invoices
$unpaid_item_amount_query="SELECT sum(i.totalamount-i.taxamount) as unpaid_item_amount,
sum(CASE WHEN i.createdate<'2013-1-1 00:00:00' THEN i.amount ELSE 0 END) as unpaid_item_amountb4_13,
sum(CASE WHEN year(i.createdate)='2013' THEN i.amount ELSE 0 END) as unpaid_item_amount_after_13,
sum(CASE WHEN year(i.createdate)='2014' THEN i.amount ELSE 0 END) as unpaid_item_amount_14,
sum(CASE WHEN year(i.createdate)='2015' THEN i.amount ELSE 0 END) as 
unpaid_item_amount_15,
sum(CASE WHEN year(i.createdate)='2016' THEN i.amount ELSE 0 END) as 
unpaid_item_amount_16,
sum(CASE WHEN year(i.createdate)='2017' THEN i.amount ELSE 0 END) as 
unpaid_item_amount_17
 FROM ". DB_PREFIX . "invoices i 
left join ". DB_PREFIX . "users u on i.user_id=u.user_id
WHERE i.combine_project = '' and i.isfvf=0 and isif=0 and isbuyerfee=0 and ismis=0 and isenhancementfee=0 and i.status='unpaid' and u.user_id>1"; 
 $result=$ilance->db->query($unpaid_item_amount_query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
$unpaid_item_amount=$line['unpaid_item_amount'];
$unpaid_item_amountb4_13=$line['unpaid_item_amountb4_13'];
$unpaid_item_amount_after_13=$line['unpaid_item_amount_after_13'];
$unpaid_item_amount_14=$line['unpaid_item_amount_14'];
$unpaid_item_amount_15=$line['unpaid_item_amount_15'];
$unpaid_item_amount_16=$line['unpaid_item_amount_16'];
$unpaid_item_amount_17=$line['unpaid_item_amount_17'];


}
}
 

$checked_out_amount_query="select sum(i.amount) as scheduledamount,sum(i.miscamount) as misctotal from ". DB_PREFIX . "invoices i
left join ". DB_PREFIX . "users u on u.user_id=i.user_id
 where i.status='scheduled' and u.user_id>0";
$result=$ilance->db->query($checked_out_amount_query);
if($ilance->db->num_rows($result))
{
while($line=$ilance->db->fetch_array($result))
{
	$checkedout_amount= $line['scheduledamount']+$line['misctotal'];
	
}
} 

$pending_total=$unpaid_invoices_buyerfees+$unpaid_item_amount;
$pending_total_2012=$unpaid_item_amountb4_13+$unpaid_invoices_buyerfeesb4_13;
$pending_total_2013=$unpaid_item_amount_after_13+$unpaid_invoices_buyerfees_after_13;
$pending_total_2014=$unpaid_item_amount_14+$unpaid_invoices_buyerfees_14;
$pending_total_2015=$unpaid_item_amount_15+$unpaid_invoices_buyerfees_15;
$pending_total_2016=$unpaid_item_amount_16+$unpaid_invoices_buyerfees_16;
$pending_total_2017=$unpaid_item_amount_17+$unpaid_invoices_buyerfees_17;
$checked_out_total=$checkedout_amount;
$total_Ar=$pending_total+$checked_out_total;
$html.= "\r\nA/R Pending 2012: $".$pending_total_2012."(Hammer:".$unpaid_item_amountb4_13." Buyerfees:".$unpaid_invoices_buyerfeesb4_13.")";
$html.= "\r\nA/R Pending 2013: $".$pending_total_2013."(Hammer:".$unpaid_item_amount_after_13." Buyerfees:".$unpaid_invoices_buyerfees_after_13.")";
$html.= "\r\nA/R Pending 2014: $".$pending_total_2014."(Hammer:".$unpaid_item_amount_14." Buyerfees:".$unpaid_invoices_buyerfees_14.")";
$html.= "\r\nA/R Pending 2015: $".$pending_total_2015."(Hammer:".$unpaid_item_amount_15." Buyerfees:".$unpaid_invoices_buyerfees_15.")";
$html.= "\r\nA/R Pending 2016: $".$pending_total_2016."(Hammer:".$unpaid_item_amount_16." Buyerfees:".$unpaid_invoices_buyerfees_16.")";
$html.= "\r\nA/R Pending 2017: $".$pending_total_2017."(Hammer:".$unpaid_item_amount_17." Buyerfees:".$unpaid_invoices_buyerfees_17.")";
$html.= "\r\nA/R Checked Out: $".$checked_out_total;
$html.= "\r\nTotal A/R: $".$total_Ar;

return $html;
}



function unbookedbuynow_data($DATETODAY)
{
global $ilance,$unbooked_buynow;
$html='';
$unbooked_buynow=0;
//$last_monday=$this->last_monday($DATEYESTERDAY);
$query="
SELECT sum(i.totalamount-i.taxamount) as unbooked_buynow   FROM " . DB_PREFIX . "invoices i 
left join " . DB_PREFIX . "coins c on c.coin_id=i.projectid and date(i.createdate)< date(c.End_Date) 
left join (select coin_id, CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date) ELSE '0000-00-00 00:00:00' END as last_listed_time from " . DB_PREFIX . "coin_relist where filtered_auctiontype = 'fixed'  group by coin_id ) r on c.coin_id = r.coin_id
WHERE i.buynowid > 0 and i.isfvf=0 and i.isif=0 and i.isbuyerfee=0 and i.ismis=0 and date(c.End_Date)>date('".$DATETODAY."')
and date(i.createdate) > date(ifnull(r.last_listed_time,'0000-00-00 00:00:00') )
";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
	while($line=$ilance->db->fetch_array($result))
	{
	 $unbooked_buynow=$line['unbooked_buynow'];
	}
}
$html.= "\r\nBuy Now Sales Unbooked: $".$unbooked_buynow;
  
return $html;
}


function sales_data($DATEYESTERDAY)
{
global $ilance,$buyer_fee_total,$inhouse_hammer_total,$noninhouse_hammer_total,$total_revenue;
$html='';

$query="SELECT u.user_id, u.house_account, i.buyerfee_per_user,
IFNULL(sum(p.currentprice),0)+IFNULL(buynow_amount ,0) as tot_hammer
FROM " . DB_PREFIX . "users u
left join (select currentprice,user_id from " . DB_PREFIX . "projects where haswinner=1 and filtered_auctiontype='regular' and date(date_end)='".$DATEYESTERDAY."' group by project_id) p on u.user_id=p.user_id  
left join (select sum(amount) as buynow_amount,owner_id from " . DB_PREFIX . "buynow_orders where date(orderdate)='".$DATEYESTERDAY."' group by owner_id) b  on b.owner_id=u.user_id
left join (select p2b_user_id,sum(totalamount) as buyerfee_per_user from " . DB_PREFIX . "invoices where date(createdate)='".$DATEYESTERDAY."' and isbuyerfee =1 group by p2b_user_id )i on i.p2b_user_id=u.user_id 
group by u.user_id
";
 
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
$inhouse_hammer_total=0;
$noninhouse_hammer_total=0;
$buyer_fee_total=0;
while($line=$ilance->db->fetch_array($result))
{
$buyer_fee_total+=$line['buyerfee_per_user'];
	if($line['house_account']==1)
	{
		$inhouse_hammer_total+=$line['tot_hammer'];
	}else
	{
		$noninhouse_hammer_total+=$line['tot_hammer'];
	}
		
	}
}

$total_revenue=$inhouse_hammer_total+$noninhouse_hammer_total+$buyer_fee_total;


$html.="\r\nDaily Sales Report";
$html.="\r\nCoins Sold Consignors: $".$noninhouse_hammer_total;
$html.= "\r\nCoins Sold House Account: $".$inhouse_hammer_total;
$html.= "\r\nBuyer's Fee: $".$buyer_fee_total;
$html.= "\r\nTotal Revenue: $".$total_revenue;

return $html;
}
function payment_data($DATEYESTERDAY)
{

global $ilance,$payment_per_method,$total_payments,$total_amount,$total_shipping_cost,$total_tax;
$html='';
//daily payments 
$query="
SELECT sum(totalamount) total_amount_per_method,sum(taxamount) taxamount_per_method,sum(pi.calc_shipping_cost) shipping_cost_per_method,paymethod FROM 
" . DB_PREFIX . "invoices i
left join (SELECT sum(shipping_cost) as calc_shipping_cost,final_invoice_id FROM " . DB_PREFIX . "invoice_projects group by final_invoice_id) pi on pi.final_invoice_id=i.invoiceid 
where date(paiddate)='".$DATEYESTERDAY."' and combine_project!='' group by i.paymethod 
";

$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
$html="\r\nDaily Payments Report";
$total_amount=0;
$total_shipping_cost=0;
$total_tax=0;
while($line=$ilance->db->fetch_array($result))
{
$html.="\r\n".ucwords($line['paymethod']).': $'.$line['total_amount_per_method'];
$payment_per_method[$line['paymethod']]=$line['total_amount_per_method'];
$total_amount+=$line['total_amount_per_method'];
$total_shipping_cost+=$line['shipping_cost_per_method'];
$total_tax+=$line['taxamount_per_method'];
}
$total_payments=$total_amount+$total_shipping_cost+$total_tax;
$html.="\r\nTotal Payments: $".$total_payments;
$html.="\r\n(Breakdown: $".$total_amount." invoices, $".$total_shipping_cost." shipping, $".$total_tax." tax)";
}
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

}

?>