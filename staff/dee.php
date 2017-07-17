<?php
require_once('./../functions/config.php');
error_reporting(E_ALL);
$query=$ilance->db->query("select * from ilance_test");
while($line=$ilance->db->fetch_array($query))
{
	$startbydate=movetoprev_monday($line['enddate']);
	$ilance->db->query("update ilance_test set startbydate='".$startbydate."' where id=".$line['id']);
}


function movetoprev_monday($day)
{
	
	list($d,$t)=explode(" ",$day);
	list($y,$m,$d)=explode("-",$d);
	list($h,$i,$s)=explode(":",$t);
    $h = mktime($h, $i, $s, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=($w-6)*24*60*60;
	$near_sunday=date("Y-m-d h:i:s",$h+$rest_sec);
	return $near_sunday;
}
?>