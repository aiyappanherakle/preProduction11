<?php 
/*  bug 2542 */
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	
$p['128554']=225;
$p['129099']=3500;
$p['129105']=180;
$p['128558']=90;
$p['128561']=260;
$p['129108']=180;
$p['129117']=70;
$p['128444']=130;
$p['143152']=45;
$p['129127']=115;
$p['129100']=290;
$p['129119']=60;
$p['129125']=240;
$p['128442']=110;
$p['33515']=15;
$p['129096']=300;
$p['128451']=110;
$p['129111']=275;
$p['128461']=20;
$p['129102']=525;
$p['128563']=450;
$p['129104']=700;
$p['129114']=110;
$p['129115']=90;
$p['128555']=225;
$p['128427']=195;
$p['128430']=270;
$p['33422']=290;
$p['128559']=90;
$p['128484']=5;
$p['128417']=90;
$p['129109']=190;
$p['128440']=90;
$p['128437']=130;
$p['128450']=190;
$p['128487']=115;
$p['128429']=90;
$p['128449']=275;
$p['128468']=15;
$p['128469']=30;
$p['33516']=15;
$p['128458']=25;
$p['128425']=90;
$p['33534']=50;
$p['128441']=110;
$p['128416']=375;
$p['128424']=185;
$p['78977']=325;
$p['128479']=55;
$p['128466']=50;
$p['128448']=75;
$p['128467']=50;
$p['128477']=20;
$p['128463']=20;
$p['128435']=65;

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