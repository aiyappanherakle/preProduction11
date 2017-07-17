<?php 
$phrase['groups'] = array('rfp'); 
$topnavlink = array('main_listings');
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(0);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{	
if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=="add_hotlist")
{
	if(strlen($ilance->GPC['coin_list'])==0)
	{
        print_action_failed("We're sorry. The textarea is empty.", $_SERVER['PHP_SELF']);exit();
	}  
	$item=explode("\r\n",rtrim($ilance->GPC['coin_list']));
 $cins_array=array();
	foreach($item as $item_value)
	{
	    $words = preg_split('/\s+/', $item_value); 
	    $to = strtolower($words[1]);
	    $SQL=0;
	    
	    if(isset($to) and $to=="through")
		{
	         if(strstr($item_value,$to))
		     {
		     	if(count(explode($to,$item_value))==2)
			    {
			    	$coinlist =explode($to,trim($item_value));
				    $coin_list=array_map('trim', $coinlist);
				    $fresult  = (!preg_match("/^[0-9]*$/", $coin_list[0] )) ? "1" :"0";
				    $sresult  = (!preg_match("/^[0-9]*$/", $coin_list[1] )) ? "1" :"0";
				    $firesult = ($coin_list[0] <= $coin_list[1] ) ? "1" :"0";
				    if ($fresult=="1" OR $sresult=="1" OR $firesult=="0" )
				    {	
				        print_action_failed("You can use a range my mentioning x to y, for ex., (23330 to 23340)", $_SERVER['PHP_SELF']);
		     	        exit();	
		     	    }
				    for($list = $coin_list[0]; $list <= $coin_list[1]; $list++) 
					{
						$cins_array[] = $list;
					}							
	    	    }
			    else
			    {
			    	print_action_failed("You can use a range my mentioning x to y, for ex., 23330 to 23340", $_SERVER['PHP_SELF']);
		     	    exit();
			    }
		     }
		     else
		     {
		     	print_action_failed("You can use a range my mentioning x to y, for ex., 23330 to 23340", $_SERVER['PHP_SELF']);
		     	exit();
		     }
	 	}
		else
		{
			//$coin_list=$item_value; 
			// $numbers1 = $item_value;
			// $numbers2 = array_filter($numbers1);
			// $numbers  =array_map('trim', $numbers2);
			// $error    = 0;
			// $inValidNumbers = array();
			// foreach($numbers as $number) 
			// {
			// 	if(!preg_match("/^[0-9]*$/", $number)) 
			// 	{
			// 		$error++;                 
			// 		array_push($inValidNumbers,$number);
			// 	}
			// }
			// echo $error;exit;
			// if($error != 0) 
			// {
	 	// 	    print_action_failed("Please Check seperate the Item IDs by comma OR You can use a range my mentioning x to y, for ex., 23330 to 23340 .", $_SERVER['PHP_SELF']);exit();
			// }
				$fresult  = (!preg_match("/^[0-9]*$/", $item_value )) ? "1" :"0"; 
			    if ($fresult=="1")
			    {	
			        print_action_failed("Please Check valid Coin Id Details", $_SERVER['PHP_SELF']);
	     	        exit();	
	     	    }
	     	    else
	     	    {
	     	    	$cins_array[]  = $item_value;
	     	    }
		     	
		}
	}	
	$result = array_unique($cins_array);
 	$items  = array();
	$itemss = array();
	$total_coin_not_list = array();
	foreach($result as $ss_array)
	{
		if ($ss_array > 0) 
		{
		$sql1 = $ilance->db->query("SELECT p.project_id,p.hotlists FROM " . DB_PREFIX . "projects p
		left join " . DB_PREFIX . "users u on p.user_id = u.user_id
		WHERE p.project_id in (".$ss_array.") and u.status='active' and p.status='open'");
		$project_id = $ilance->db->fetch_array($sql1);
			if ($ilance->db->num_rows($sql1) > 0) 
			{ 	
				$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
				SET hotlists='1'	
				WHERE  project_id = '".$ss_array."'");					 
				$items[] = $ss_array;
			}
			else
			{
					$sql26="select * from ".DB_PREFIX."projects where project_id = ".$ss_array."";
					$result26 = $ilance->db->query($sql26, 0, null, __FILE__, __LINE__);
					if($ilance->db->num_rows($result26)>0)
					{
						$sql2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE  ((filtered_auctiontype = 'regular' AND winner_user_id  = '0') OR (buynow = '1' AND filtered_auctiontype = 'fixed' AND buynow_qty > '0')) AND project_id = ".$ss_array, 0, null, __FILE__, __LINE__);
				        $project_id = $ilance->db->fetch_array($sql2);
						if ($ilance->db->num_rows($sql2) == 0) 
						{ 	

						}
						else
						{
							$sql25="select * from ".DB_PREFIX."coins where coin_id = ".$ss_array."";
							$result25 = $ilance->db->query($sql25, 0, null, __FILE__, __LINE__);
							if($ilance->db->num_rows($result25)>0)
							{
						        $ipaddress      = IPADDRESS;
								$createdate     = DATETIME24H;						
								$insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
								(id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
								VALUES(
								NULL,
								'" . intval($ss_array). "',     
								'No',
								'" . $ilance->db->escape_string($createdate) . "',
								'hot',
								'" . $ilance->db->escape_string($ipaddress) . "') ";
								$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
								$total_coin_not_list[]=$ss_array;
							}
						}
					}
					else
					{
							$sql25="select * from ".DB_PREFIX."coins where coin_id = ".$ss_array."";
							$result25 = $ilance->db->query($sql25, 0, null, __FILE__, __LINE__);
							if($ilance->db->num_rows($result25)>0)
							{
								$ipaddress      = IPADDRESS;
								$createdate     = DATETIME24H;						
								$insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
								(id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
								VALUES(
								NULL,
								'" . intval($ss_array). "',     
								'No',
								'" . $ilance->db->escape_string($createdate) . "',
								'hot',
								'" . $ilance->db->escape_string($ipaddress) . "') ";
								$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
								$total_coin_not_list[]=$ss_array;
							}

					}
			}
		}
	}
	$live_coin       = implode(",",$items);
	$sold_coin       = implode(",",$total_coin_not_list);
	$tot_coins       = count($total_coin_not_list);
	$tot_live_coins  = count($items);
	if($tot_live_coins > 0)
	{		
		$live_Details="Task have been successfully completed. Added Hot list coins: ".$live_coin." No of coins will be added in hotlist from now, you can check it in the user end<br/><br/>";  	 
 	}		
 	if($tot_coins > 0)
	{		
		$Details="<div style='width:800px; word-wrap: break-word;'>The coins are added to hotlist Cron - Kindly check following list: ".$sold_coin."</div>";  	 
 	}	
    if(($tot_live_coins > 0) OR ($tot_coins > 0))
    {
    print_action_success("".$live_Details."".$Details, $_SERVER['PHP_SELF']);
	exit();	

    }else
    {
	    print_action_failed("Please Check Valid coin Id", $_SERVER['PHP_SELF']);
		exit();
    } 					

}
else
{
		$sql7="SELECT * FROM " . DB_PREFIX . "cron_not_placebid WHERE Bid_listed = 'No' AND status= 'hot'";		
		$result7 = $ilance->db->query($sql7, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result7)>0)
		{
			$count=1;
			while($line6= $ilance->db->fetch_array($result7))
			{
				$not_hotlist=$count++;;

			}
		}
		else
		{
			$not_hotlist = "<span style='color:green'>Updated All Hot List Coins</span>";
		}
	
	$pprint_array = array('not_hotlist','coin_list','headinclude','form_action','buildversion','ilanceversion','login_include_admin','clientip','cmd','remember_checked','input_style','redirect','referer','rid','login','admin_cookie','login_include','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'new_add_hotlist.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
} 
}
else
{
refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
exit();
}
?>
