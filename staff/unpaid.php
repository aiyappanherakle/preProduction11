<?php
define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{

$till='2013-02-28';
if(isset($ilance->GPC['on']))
$till=$ilance->GPC['on'];


$all=" select 
i.invoiceid,i.projectid,i.status,i.createdate,s.created_date as scheduled_date,i.paiddate,
CASE WHEN i.createdate<'2013-1-1 00:00:00' THEN i.amount ELSE 0 END as unpaid_item_amountb4_13,
CASE WHEN i.createdate>='2013-1-1 00:00:00' THEN i.amount ELSE 0 END as unpaid_item_amount_after_13,b.qty
";

$columns=" select 
sum(CASE WHEN i.createdate<'2013-1-1 00:00:00' THEN i.amount ELSE 0 END) as unpaid_item_amountb4_13,
sum(CASE WHEN i.createdate>='2013-1-1 00:00:00' THEN i.amount ELSE 0 END) as unpaid_item_amount_after_13
";

$kkk=" FROM ilance_invoices i 
left join ilance_users u on i.user_id=u.user_id
left join ilance_invoice_projects_unique s on s.invoice_id=i.invoiceid
left join ilance_buynow_orders b on b.invoiceid=i.invoiceid 
WHERE i.combine_project = '' and 
i.isfvf=0 and 
i.isif=0 and 
i.isbuyerfee=0 and 
i.ismis=0 and 
i.isenhancementfee=0 and 
i.user_id>1 and 
date(i.createdate)<='".$till."' and
(
(i.status='unpaid' and date(i.createdate)<='".$till."')
or (i.status='complete' and date(i.createdate)<='".$till."' and date(s.created_date)>'".$till."')
or (i.status='paid' and date(i.createdate)<='".$till."' and date(s.created_date)>'".$till."' and date(i.paiddate)>'".$till."')
)
order by i.invoiceid desc";
$sql=$columns.$kkk;
echo $all.$kkk;

echo '<br>';
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo "unpaid_item_amountb4_13  ".$line['unpaid_item_amountb4_13'];
		echo '<br>';
		echo "unpaid_item_amountafter_13  ".$line['unpaid_item_amount_after_13'];
	}
}
}else
{
echo 'login';
}

?>