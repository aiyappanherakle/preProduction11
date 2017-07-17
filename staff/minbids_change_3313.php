<?php 
/*  bug 2542 */
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	
// sample array 
$p['134801']=470;
$p['134803']=100;
$p['134805']=36;
$p['134808']=59;
$p['134809']=70;
$p['134813']=100;
$p['134814']=95;
$p['134815']=300;
$p['134816']=100;
$p['102518']=99;
$p['89364']=99;
$p['89365']=129;
$p['86584']=899;
$p['102523']=499;
$p['102522']=499;
$p['102520']=499;
$p['102766']=229;
$p['89366']=119;
$p['102767']=229;

$count=0;
$changed='';
$changed_only_coin='';
$not_changed='';
$donot_exist='';
	foreach($p as $id=>$bid)
	{
		$count++;
		$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id and status='open'");
		if($ilance->db->num_rows($sqlquery) > 0)
		{
			
			$sqlquery1 = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id and bids=0 AND filtered_auctiontype =  'regular'");
			if($ilance->db->num_rows($sqlquery1) > 0)
			{
				//No bids	
				echo "UPDATE  " . DB_PREFIX . "coins
											SET  Minimum_bid = ".$bid."   
											WHERE  coin_id = '".$id."'												
											;";
  
			// $ilance->db->query("UPDATE  " . DB_PREFIX . "coins
														 // SET  Minimum_bid = ".$bid."   
														 // WHERE  coin_id = '".$id."'															
														 // ");	
				echo '</br>';
				
				echo "UPDATE  " . DB_PREFIX . "projects
														SET  startprice = ".$bid.",
															currentprice=".$bid."
															 WHERE  project_id = '".$id."'															 
														;";
				
				echo '</br>';
				
				 // $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
														 // SET  startprice = ".$bid.",
															 // currentprice=".$bid."
															  // WHERE  project_id = '".$id."'															 
														 // ");	
				$changed[]=$id;
			
			}
			else
			{
				//With bids
				$not_changed[]=$id;
			}
			
		}
		else
		{
			$sqlquery2 = $ilance->db->query("select * from ".DB_PREFIX."coins where coin_id=$id");
			if($ilance->db->num_rows($sqlquery2) > 0)
			{
				 echo "UPDATE  " . DB_PREFIX . "coins
												SET  Minimum_bid = ".$bid."   
													 WHERE  coin_id = '".$id."'													
												;";
	  
			 // $ilance->db->query("UPDATE  " . DB_PREFIX . "coins
														 // SET  Minimum_bid = ".$bid."   
														 // WHERE  coin_id = '".$id."'															 
														 // ");  
				echo '</br>';
				
				$changed_only_coin[]=$id;
			}
			else
			{			
				$donot_exist[]=$id;
				
			}
			
		}	
		

	}
	$changed=empty($changed)?0:implode(",",$changed);
	$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
	$not_changed=empty($not_changed)?0:implode(",",$not_changed);
	$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
	echo '</br>';
	echo 'Total count -'.$count;
	echo '</br>';
	echo '</br>';
	echo 'Changed in both tables -'.$changed;
	echo '</br>';
	echo '</br>';
	echo  'Changed only in coin table -'.$changed_only_coin;
	echo '</br>';
	echo '</br>';
	echo  'Not changed,With bids -'.$not_changed;
	echo '</br>';
	echo '</br>';	
	echo  'Do not exist -'.$donot_exist;
	echo '</br>';
	
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  bug 2542 */

?>