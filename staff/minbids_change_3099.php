<?php 
/*  bug 2542 */
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	
// sample array 
$p['136982']=4800;
$p['137033']=300;
$p['137032']=250;
$p['137034']=1000;
$p['137035']=400;
$p['136983']=1000;
$p['136984']=450;
$p['136985']=800;
$p['136986']=1500;
$p['137036']=1300;
$p['137037']=1000;
$p['137038']=800;
$p['137039']=150;
$p['136987']=150;
$p['137040']=1400;
$p['136991']=180;
$p['136988']=200;
$p['136989']=150;
$p['136992']=180;
$p['137041']=1500;
$p['136990']=150;
$p['137042']=180;
$p['136993']=150;
$p['136994']=80;
$p['136995']=800;
$p['136996']=400;
$p['136997']=950;
$p['136998']=850;
$p['137005']=380;
$p['137043']=13000;
$p['137044']=26500;
$p['137045']=3500;
$p['137046']=3750;
$p['137047']=4500;
$p['137048']=4200;
$p['137006']=680;
$p['137049']=23000;
$p['137050']=11500;
$p['137053']=8000;
$p['137052']=17000;
$p['137007']=950;
$p['136999']=480;
$p['137000']=1300;
$p['137001']=700;
$p['137002']=3250;
$p['137003']=1450;
$p['137004']=2500;
$p['137113']=400;
$p['137114']=1500;
$p['137111']=300;
$p['137112']=200;
$p['137115']=4000;

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