<?php 
/*  bug 2542 */
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	
// sample array 
$p['139447']=425;
$p['139448']=125;
$p['123569']=60;
$p['123563']=130;
$p['120551']=200;
$p['123564']=60;
$p['123565']=60;
$p['123566']=60;
$p['123567']=60;
$p['129150']=70;
$p['139449']=325;
$p['117639']=200;
$p['129151']=170;
$p['129152']=170;
$p['133236']=110;
$p['129153']=300;
$p['129154']=360;
$p['112352']=350;
$p['139450']=380;
$p['120557']=140;
$p['133237']=1000;
$p['120559']=100;
$p['120560']=50;
$p['120561']=100;
$p['120562']=25;
$p['120563']=100;
$p['133238']=475;
$p['129813']=240;
$p['133239']=270;
$p['123570']=525;
$p['123340']=650;
$p['132027']=280;
$p['133240']=260;
$p['129814']=160;
$p['139451']=120;
$p['102116']=50;
$p['135268']=75;
$p['123571']=330;
$p['112342']=400;
$p['130440']=450;
$p['129155']=200;
$p['129815']=180;
$p['129156']=180;
$p['129157']=160;
$p['129816']=250;
$p['129158']=700;
$p['129159']=260;
$p['123568']=450;
$p['133242']=280;
$p['123573']=360;
$p['129160']=300;
$p['120541']=450;
$p['133243']=380;
$p['129162']=800;
$p['129163']=380;
$p['132029']=850;
$p['133244']=350;
$p['129166']=400;
$p['129167']=700;
$p['129168']=400;
$p['133655']=150;
$p['133245']=475;
$p['123575']=240;
$p['117644']=350;
$p['130441']=360;
$p['133246']=360;
$p['117646']=150;


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