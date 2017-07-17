<?php	


include('./functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
//error_reporting(E_ALL);
global $ilance, $myapi, $ilpage, $phrase, $ilconfig;

$ilance->auction = construct_object('api.auction');

$ilance->bid = construct_object('api.bid');

$ilance->placebid_product = construct_object('api.placebid_product');


$query1="SELECT * FROM " . DB_PREFIX . "cron_placebid  where Bid_Placed='No'";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
$bid_time = date('Y-m-d H:i:s', strtotime('+59 seconds'));
$sec_add=10;
	while($line1 = $ilance->db->fetch_array($sql1))
{

$highbidnotify=0;
$lasthournotify=0;
$subscribed=0;
$shipperid=0;
$project_id=$line1['project_id'];
$project_user_id=$line1['user_id'];
$bidamount=$line1['next_bid'];
$quantity=1;
$bidder_id=$line1['bidder_id'];
$ilconfig['productbid_enableproxybid']=1;
$minimum=$line1['currentprice'];
$buyershipcost = 0;

// #### EXPIRE MARKETPLACE AUCTIONS ############################################


echo '<br/>highbidnotify = '.$highbidnotify;
echo '<br/>lasthournotify = '.$lasthournotify;
echo '<br/>subscribed = '.$subscribed;
echo '<br/>project_id = '.$project_id;
echo '<br/>project_user_id = '.$project_user_id;
echo '<br/>bidamount = '.$bidamount;
echo '<br/>quantity = '.$quantity;
echo '<br/>bidder_id = '.$bidder_id;

echo "<br/>productbid_enableproxybid = ".$ilconfig['productbid_enableproxybid'];
echo '<br/>minimum = '.$minimum;
echo "<br/>fetch_reserve_price = ".$ilance->auction->fetch_reserve_price(intval($project_id));
echo '<br/>buyershipcost = '.$buyershipcost;

echo '<br/>shipperid = '.$shipperid;
$bid_time = date('Y-m-d H:i:s',strtotime($bid_time." +$sec_add seconds"));
echo '<br/>bid time ='.$bid_time;

 $ilance->placebid_product->placebid($highbidnotify, $lasthournotify, $subscribed, intval($project_id), $project_user_id, $bidamount, $quantity,$bidder_id, $ilconfig['productbid_enableproxybid'], $minimum, $ilance->auction->fetch_reserve_price(intval($project_id)), false, $buyershipcost, $shipperid,$bid_time);


$ilance->db->query("UPDATE " .DB_PREFIX . "cron_placebid SET Bid_Placed ='Yes',place_bid_date_updated='" . DATETIME24H . "' WHERE project_id = '".intval($project_id)."'");	

// $cronlog .= $ilance->bid_product->placebid($ilance->GPC['highbidnotify'], $ilance->GPC['lasthournotify'], $ilance->GPC['subscribed'], intval($ilance->GPC['id']), intval($ilance->GPC['project_user_id']), $ilance->GPC['bidamount'], $ilance->GPC['qty'], $_SESSION['ilancedata']['user']['userid'], $ilconfig['productbid_enableproxybid'], $ilance->GPC['minimum'], $ilance->auction->fetch_reserve_price(intval($ilance->GPC['id'])), true, $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid']);

}
}
}
	
else
{
refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
exit();
}

?>