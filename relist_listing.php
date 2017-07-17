<?php	


include('./functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
//error_reporting(E_ALL);
global $ilance, $myapi, $ilpage, $phrase, $ilconfig;



$query1="SELECT c.coin_id,r.coin_id,r.id,c.user_id,r.filtered_auctiontype,crd.coin_id as returncoins,
i.invoiceid,d.dailydeal_id,i.invoicetype,i.isfvf,i.isenhancementfee,
i.ismis ,i.isif,i.projectid, i.combine_project  FROM `ilance_coins` c
left join ilance_projects p  on p.project_id = c.coin_id
left outer join ilance_invoices i on c.coin_id=i.projectid
left join ilance_coin_relist r  on r.coin_id = c.coin_id
left join ilance_dailydeal d  on d.coin_id = c.coin_id
left join ilance_coins_retruned crd on crd.coin_id = i.projectid
where  i.isfvf!=1
and i.isbuyerfee !=1
and i.isenhancementfee!=1
and i.ismis!=1
and i.isif!=1
and i.projectid!=0
and i.combine_project=''
ORDER BY `c`.`coin_id` ASC";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{

	while($line1 = $ilance->db->fetch_array($sql1))
{



echo '<br/>coin_id = '.$line1['coin_id'];
echo '<br/>lasthournotify = '.$line1['user_id'];
echo '<br/>invoiceid = '.$line1['invoiceid'];
echo '<br/>dailydeal_id = '.$line1['filtered_auctiontype'];




//$ilance->db->query("UPDATE " .DB_PREFIX . "cron_placebid SET Bid_Placed ='Yes',place_bid_date_updated='" . DATETIME24H . "' WHERE project_id = '".intval($project_id)."'");	

}
}
}
	
else
{
refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
exit();
}

?>