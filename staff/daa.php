<?php
require_once('./../functions/config.php');
error_reporting(E_ALL);
//and coin_id=6960  
$select = $ilance->db->query("select relist_count,coin_id from ilance_coins where Buy_it_now >0 and relist_count>0  order by coin_id desc");
if($ilance->db->num_rows($select) > 0)
{
while( $line = $ilance->db->fetch_array($select))
{
echo "select date(orderdate) as orderdate,owner_id from ilance_buynow_orders where project_id=".$line['coin_id']." group by orderdate ".'<br>';
	$query=$ilance->db->query("select date(orderdate) as orderdate,owner_id from ilance_buynow_orders where project_id=".$line['coin_id']." group by orderdate ");
	unset($dates_array);
	while($line1=$ilance->db->fetch_array($query))
	{
	$dates_array[]=$line1['orderdate'].' 00:00:00';
	$owner_id=$line1['owner_id'];
	}
	
	if(isset($dates_array) and count($dates_array)){
	$prev_end_date=0;
	foreach(array_unique(movetosunday($dates_array)) as $prev_end_date)
	{
	$startbydate=movetoprev_monday($prev_end_date);
	echo $query="insert into ilance_coin_relist (coin_id,enddate,startbydate,user_id)values('".$line['coin_id']."','".$prev_end_date."','".$startbydate."','".$owner_id."')";
	echo '<br>';
	$ilance->db->query($query);
	}
	}
}
}
function movetosunday($days)
{
	foreach ($days as $day)
	{
	list($d,$t)=explode(" ",$day);
	list($y,$m,$d)=explode("-",$d);
	list($h,$i,$s)=explode(":",$t);
	 $stamp = mktime($h, $i, $s, $m, $d, $y);
	$w= date("w", $stamp) ;
	$rest_sec=(7-$w)*24*60*60;
	$near_sunday=date("Y-m-d h:i:s",$stamp+$rest_sec);
	$sundayeddates[]=$near_sunday;
	}
	return $sundayeddates;
}
function movetoprev_monday($day)
{
	
	list($d,$t)=explode(" ",$day);
	list($y,$m,$d)=explode("-",$d);
	list($h,$i,$s)=explode(":",$t);
    $stamp = mktime($h, $i, $s, $m, $d, $y);
	$w= date("w", $stamp) ;
	$rest_sec=($w-6)*24*60*60;
	$near_sunday=date("Y-m-d h:i:s",$stamp+$rest_sec);
	return $near_sunday;
}
?>