<?php
include("../functions/config.php");
$dir="/home/gc/public_html/cache/";
$r=glob($dir."*.log");

//echo '<pre>';
//echo count($r);
$i=0;
$sql='';
foreach($r as $log)
{
	$fp=fopen($log, "r");
	$temp=parse_str(content_between("PAYPAL SENT: ","END",fread($fp, filesize($log))),$arr);
	$e=explode("|",$arr['custom']);	
	$array=array_merge($arr,array_combine(array_map("asdd",array_keys($e)), $e));
	$sql='insert into ilance_paypal_check (`'.implode('`,`',array_keys($array)).'`) values (\''.implode('\',\'',$array).'\');';
	$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	fclose($fp);
}

function asdd($k)
{
	return 'custom_'.$k;
}

function content_between($start,$end,$string)
{
	return substr($string, strpos($string,$start)+strlen($start),strpos($string,$end)-strpos($string,$start)-strlen($start));
}
/*
CREATE TABLE `test` ( 
`id` INT(12) NOT NULL AUTO_INCREMENT,
`do` VARCHAR(200) NOT NULL , 
`transaction_subject` VARCHAR(200) NOT NULL , 
`payment_date` VARCHAR(200) NOT NULL , 
`txn_type` VARCHAR(200) NOT NULL , 
`last_name` VARCHAR(200) NOT NULL , 
`residence_country` VARCHAR(200) NOT NULL , 
`item_name` VARCHAR(200) NOT NULL , 
`payment_gross` VARCHAR(200) NOT NULL , 
`mc_currency` VARCHAR(200) NOT NULL , 
`business` VARCHAR(200) NOT NULL , 
`payment_type` VARCHAR(200) NOT NULL , 
`protection_eligibility` VARCHAR(200) NOT NULL , 
`verify_sign` VARCHAR(200) NOT NULL , 
`payer_status` VARCHAR(200) NOT NULL , 
`payer_email` VARCHAR(200) NOT NULL , 
`txn_id` VARCHAR(200) NOT NULL , 
`quantity` VARCHAR(200) NOT NULL , 
`receiver_email` VARCHAR(200) NOT NULL , 
`first_name` VARCHAR(200) NOT NULL , 
`payer_id` VARCHAR(200) NOT NULL , 
`receiver_id` VARCHAR(200) NOT NULL , 
`item_number` VARCHAR(200) NOT NULL , 
`payment_status` VARCHAR(200) NOT NULL , 
`payment_fee` VARCHAR(200) NOT NULL , 
`mc_fee` VARCHAR(200) NOT NULL , 
`mc_gross` VARCHAR(200) NOT NULL , 
`custom` VARCHAR(200) NOT NULL , 
`charset` VARCHAR(200) NOT NULL , 
`notify_version` VARCHAR(200) NOT NULL , 
`ipn_track_id` VARCHAR(200) NOT NULL , 
`cmd` VARCHAR(200) NOT NULL , 
`custom_0` VARCHAR(200) NOT NULL , 
`custom_1` VARCHAR(200) NOT NULL , 
`custom_2` VARCHAR(200) NOT NULL , 
`custom_3` VARCHAR(200) NOT NULL , 
`custom_4` VARCHAR(200) NOT NULL , 
`custom_5` VARCHAR(200) NOT NULL , 
`custom_6` VARCHAR(200) NOT NULL , 
`custom_7` VARCHAR(200) NOT NULL , 
`custom_8` VARCHAR(200) NOT NULL , 
`custom_9` VARCHAR(200) NOT NULL , 
PRIMARY KEY (`id`)) ENGINE = InnoDB;
*/
?>