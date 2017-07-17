<?php
require_once('../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
$sel = $ilance->db->query("SELECT * FROM  ".DB_PREFIX."projects WHERE  `winner_user_id` = 5270 ");
while($re = $ilance->db->fetch_array($sel))
{
	$new = $re['currentprice'] * 10/100;
	$pr = $re['currentprice'] + $new;	
	$selco = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coins WHERE coin_id = '".$re['project_id']."'");
	$rec = $ilance->db->fetch_array($selco);
	
	$con_insert = $ilance->db->query("
										INSERT INTO " . DB_PREFIX . "coins
										(coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id,  Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed,in_notes,Sets,nocoin,listing_fee,fvf_id)
										VALUES (
										NULL,
										'6874',
										'" . $rec['pcgs'] . "',
										'" . $ilance->db->escape_string($rec['Title']) . "',
										'" . $ilance->db->escape_string($rec['Description']) . "',
										'" . $ilance->db->escape_string($rec['Grading_Service']) . "',
										'" . $ilance->db->escape_string($rec['Grade'] ). "',
										'" . $ilance->db->escape_string($rec['Quantity']) . "',
										'" . $ilance->db->escape_string($rec['Max_Quantity_Purchase']) . "',
										'" . $ilance->db->escape_string($rec['Certification_No']) . "',
										'" . $rec['Condition_Attribute'] . "',
										'" . $rec['Cac'] . "',
										'" . $rec['Star'] . "',
										'" . $rec['Plus'] . "',
										'" . $rec['Coin_Series'] . "',
										'" . $rec['Pedigee'] . "',
										'" .$rec['Site_Id'] . "',
										'" . round($pr) . "',
										'0000-00-00',
										'" . $rec['Alternate_inventory_No'] . "',
										'" . $rec['Category'] . "',
										'" . $rec['Other_information']  . "',
										'1406', 
										'" .$rec['coin_listed'] . "',
										'".$ilance->db->escape_string($rec['inotes'])."',
										'".$rec['Sets']."',
										'".$rec['nocoin']."',										
										'".$ilance->db->escape_string($rec['listingfee'])."',
										'".$rec['fvf_id']."'
										)
								");
								echo $re['project_id'].' - Completed <br>';
}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>