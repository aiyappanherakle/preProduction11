<?php
 
$phrase['groups'] = array('rfp'); 
$topnavlink = array('main_listings');
define('LOCATION', 'admin');
ini_set('max_input_vars', 10000);
require_once('./../functions/config.php');
error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
 
/* validate coins and add to place bid table*/
if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='place_fixed_bid_update')
{
	if (isset($ilance->GPC['place_next_id']) AND is_array($ilance->GPC['place_next_id'])  AND count($ilance->GPC['place_next_id'])>0 ) 
	{
		$coins_list=$ilance->GPC['place_next_id'];
		$bids_list=$ilance->GPC['fixed_bidder_id'];
		$bidamount=$ilance->GPC['price_fixed'];

		$numbers1 = explode(',', $ilance->GPC['coin_not_list']);
		$numbers2 = array_filter($numbers1);
		$numbers  = array_map('trim', $numbers2);
	    $total_coin_not_lists=array();
		foreach($numbers as $number) 
		{						 
				$ipaddress      = IPADDRESS;
				$createdate     = DATETIME24H;						
				$insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
				(id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
				VALUES(
				NULL,
				'" . intval($number). "',     
				'No',
				'" . $ilance->db->escape_string($createdate) . "',
				'bid',
					'" . $ilance->db->escape_string($ipaddress) . "') ";
				$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
				$total_coin_not_lists[]=$number;
		}

		$total_coin_list=array();
		foreach($coins_list as $coin_id)
		{
			$bidder_id=$bids_list[$coin_id];
			$sql="SELECT p.user_id,p.project_id,bids.user_id as maxbidder_id,proxy.maxamount as bidders_maxamount,p.project_title,p.bids as biddingcount,p.startprice,p.currentprice,p.date_end,p.filtered_auctiontype 
			FROM " . DB_PREFIX . "projects p 
			left join (select user_id,bidamount,project_id  from " . DB_PREFIX . "project_bids where project_id = '".$coin_id."' ORDER BY bidamount DESC, date_added ASC LIMIT 1)as bids on bids.project_id=p.project_id 
			left join (SELECT user_id,maxamount,project_id FROM " . DB_PREFIX . "proxybid  WHERE user_id = '" . $bidder_id . "' AND project_id = '" . $coin_id . "' limit 1) as proxy on proxy.project_id=p.project_id
			WHERE p.project_id = '".$coin_id."' and p.status='open' and p.filtered_auctiontype='regular'";
			$result=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result) > 0)
			{
				while($line = $ilance->db->fetch_array($result))
				{
					
					$minimum_bid=0;
					$list[]=$line['project_id'];
					$cbid=$line['currentprice'];
					$sql1="SELECT amount+".$cbid." as minimum_bid FROM " . DB_PREFIX . "increments WHERE ((increment_from <= ".$cbid." AND increment_to >= ".$cbid.")  OR (increment_from < ".$cbid." AND increment_to < ".$cbid.")) AND groupname = 'default' ORDER BY amount DESC ";
					$result1=$ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($result1) > 0)
					{
						while($line1 = $ilance->db->fetch_array($result1))
						{
							 $minimum_bid=$line1['minimum_bid'];
						}
					}
					if($bidamount>0 and $minimum_bid<=$bidamount)
					{
						//place bid on the mentioned bid amount 
						//insert into cron_placebid
						$ipaddress      = IPADDRESS;
						$createdate     = DATETIME24H;
						$insert_sql="INSERT INTO " . DB_PREFIX . "cron_placebid
									(id, project_id, user_id, project_title, description, bids, filtered_auctiontype, startprice, currentprice, next_bid, date_end, bidder_id, Bid_Placed, place_bid_createdate,place_bid_date_updated,ipaddress)
									VALUES(
									NULL,
									'" . intval($line['project_id']). "',     
									'" . intval($line['user_id']). "',
									'". $ilance->db->escape_string($line['project_title'])."',
									'place next bid ',
									'" . intval($line['biddingcount']). "',
									'" . $line['filtered_auctiontype']. "',
									'" . $line['startprice'] . "',
									'" . $line['currentprice'] . "',
									'" . $bidamount."',
									'" . $ilance->db->escape_string($line['date_end']). "',
									'" . intval($bidder_id). "',
									'No',
									'" . $ilance->db->escape_string($createdate) . "',
									'0000-00-00 00:00:00',										
									'" . $ilance->db->escape_string($ipaddress) . "') ";
						$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
						$total_coin_list[]=$line['project_id'];
					}else if($bidamount>0 and $minimum_bid>$bidamount)
					{
						//dont have to insert into cron bid table.
					}
					else
					{
						//insert into cron_placebid
						//insert 0 so that it will bid in bext bid amount
						$ipaddress      = IPADDRESS;
						$createdate     = DATETIME24H;
						
						$insert_sql="INSERT INTO " . DB_PREFIX . "cron_placebid
									(id, project_id, user_id, project_title, description, bids, filtered_auctiontype, startprice, currentprice, next_bid, date_end, bidder_id, Bid_Placed, place_bid_createdate,place_bid_date_updated,ipaddress)
									VALUES(
									NULL,
									'" . intval($line['project_id']). "',     
									'" . intval($line['user_id']). "',
									'". $ilance->db->escape_string($line['project_title'])."',
									'place next bid ',
									'" . intval($line['biddingcount']). "',
									'" . $line['filtered_auctiontype']. "',
									'" . $line['startprice'] . "',
									'" . $line['currentprice'] . "',
									'0',
									'" . $ilance->db->escape_string($line['date_end']). "',
									'" . intval($bidder_id). "',
									'No',
									'" . $ilance->db->escape_string($createdate) . "',
									'0000-00-00 00:00:00',										
									'" . $ilance->db->escape_string($ipaddress) . "') ";
						$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
						$total_coin_list[]=$line['project_id'];
						
					}
				}
			}
			
		}
		if(count($total_coin_not_lists)>0)
		{
	       @$details = "<br>".count($total_coin_not_lists)." No of Not listed bid will be place by a cron job.";     
		}
		if(count($total_coin_list)>0)
			{
				print_action_success("Task have been successfully completed.<br>".count($total_coin_list)." No of bid will be place by a cron job.".@$details."", $_SERVER['PHP_SELF']);exit();	
			}else
			{
				print_action_failed("We're sorry. The end list was empty, no bid will be placed ", $_SERVER['PHP_SELF']);exit();
			}
		exit;
    }				
	else
	{
		print_action_failed("We're sorry. Select any one bid", $_SERVER['PHP_SELF']);
		exit();
	}
}
/* Page to check if the items are valid and show list of item details*/
elseif(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=="place_bids" and isset($ilance->GPC['Submit_Fixed']))
{
	$bidder_id=$ilance->GPC['fixed_bidder_id'];
	if(strlen($ilance->GPC['coin_list'])==0)
	{
		print_action_failed("We're sorry. The textarea is empty.", $_SERVER['PHP_SELF']);exit();
	}
	$SQL=0;
	if(strstr($ilance->GPC['coin_list'],'to'))
	{
		if(count(explode('to',$ilance->GPC['coin_list']))==2)
		{
			$coinlist=explode('to',trim($ilance->GPC['coin_list']));
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

			$result_checking    = @implode(",",$cins_array);
			$sql111="SELECT * FROM " . DB_PREFIX . "coins WHERE Buy_it_now='' and coin_id in (".$result_checking.") group by user_id";
			$result111=$ilance->db->query($sql111, 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result111) == 1)
			{
			}else
			{
				print_action_failed("We're sorry. The range you provided has coins that belongs to different sellers.", $_SERVER['PHP_SELF']);exit();
			}

            $total_coin_not_list  = array();
            $n = 1;
		    $varbreak='';
     	    for($i=$coin_list[0];$i<=$coin_list[1];$i++)
			{
				$RE_SQL="SELECT * FROM " . DB_PREFIX . "projects p
				left join " . DB_PREFIX . "users u on p.user_id = u.user_id
				WHERE p.project_id = ".$i." and u.status='active' and p.date_end>'".DATETIME24H."' and p.status='open' and p.filtered_auctiontype='regular'";
				$re_result=$ilance->db->query($RE_SQL, 0, null, __FILE__, __LINE__);
				$re_place_count=$ilance->db->num_rows($re_result);
				if ($re_place_count > 0)
				{ 
					 
				}
				else
				{
					$sql24="SELECT * FROM " . DB_PREFIX . "projects where ((filtered_auctiontype = 'regular' AND haswinner > 0  AND winner_user_id > 0) OR (filtered_auctiontype =  'fixed')) and project_id = ".$i."";
					$result24 = $ilance->db->query($sql24, 0, null, __FILE__, __LINE__);
					if($ilance->db->num_rows($result24)>0)
					{	
					 					 
					}
					else
					{	
						$sql25="select * from ".DB_PREFIX."coins where coin_id = ".$i."";
						$result25 = $ilance->db->query($sql25, 0, null, __FILE__, __LINE__);
						if($ilance->db->num_rows($result25)>0)
						{	
							$res_coin=$ilance->db->fetch_array($result25);
							if($res_coin['Buy_it_now'] == '')
							{
								// $ipaddress      = IPADDRESS;
								// $createdate     = DATETIME24H;						
								// $insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
								// (id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
								// VALUES(
								// NULL,
								// '" . intval($i). "',     
								// 'No',
								// '" . $ilance->db->escape_string($createdate) . "',
								// 'bid',
								// '" . $ilance->db->escape_string($ipaddress) . "') ";
								// $ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
								$total_coin_not_list[]=$i;

								$varbreak.=  $i.",";
								if($n % 15 == 0){
								$varbreak .= "<br />";
								}
								$n++;							}						 					 
						}	
					}
				}
			}
			$result_Not_list    = @$varbreak;
			$tot_coins          = count($total_coin_not_list);
			$sql1="SELECT * FROM " . DB_PREFIX . "projects WHERE status='open'  and filtered_auctiontype='regular' and date_end>'".DATETIME24H."' and project_id between ".$coin_list[0]." and ".$coin_list[1]." group by user_id";
			$result1=$ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result1) == 1)
			{
				$SQL="SELECT *,date_format(date_end,'%b %d %Y %h:%i %p') as date_end FROM " . DB_PREFIX . "projects WHERE status='open'  and filtered_auctiontype='regular' and date_end>'".DATETIME24H."' and project_id between ".$coin_list[0]." and ".$coin_list[1]."";
			}else
			{
				if($tot_coins > 0)
				{
 					foreach($total_coin_not_list as $number) 
					{						 
								$ipaddress      = IPADDRESS;
								$createdate     = DATETIME24H;						
								$insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
								(id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
								VALUES(
								NULL,
								'" . intval($number). "',     
								'No',
								'" . $ilance->db->escape_string($createdate) . "',
								'bid',
 								'" . $ilance->db->escape_string($ipaddress) . "') ";
								$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
					}
					print_action_success("Added Not Listed coins in cron job:- ".$result_Not_list."", $_SERVER['PHP_SELF']);
					exit();
				}
				else
				{
					print_action_failed("We're sorry. The range you provided has coins that belongs to different sellers.", $_SERVER['PHP_SELF']);exit();
				}

			}
		}else
		{
			print_action_failed("We're sorry. Only one line is allowed in the test area if you are using a range ", $_SERVER['PHP_SELF']);exit();
		}
	}elseif(strstr($ilance->GPC['coin_list'],'$'))
	{
		$ilance->GPC['coin_less_then']=substr(trim($ilance->GPC['coin_list']), 1);
		// $SQL="SELECT  p.project_id,p.project_title,p.bids,p.startprice,
	 //        p.currentprice,date_format(p.date_end,'%b %d %Y %h:%i %p') as date_end
		// 	FROM ".DB_PREFIX ."projects p
		// 	left join ".DB_PREFIX ."users u on p.user_id = u.user_id			
		// 	WHERE p.status='open' 
		// 	and u.status='active' 
		// 	and p.filtered_auctiontype='regular' 
		// 	and p.currentprice<=".intval($ilance->GPC['coin_less_then'])." and p.date_end>'".DATETIME24H."'
		// 	and p.project_id not in(SELECT project_id
		// 	FROM " .DB_PREFIX."project_bids pb
		// 	WHERE pb.user_id =28
		// 	GROUP BY pb.project_id
		// 	)
		// 	GROUP BY p.project_id
	 //        ";
		$SQL= "SELECT p.project_id,p.project_title,p.bids,p.startprice,p.currentprice,date_format(p.date_end,'%b %d %Y %h:%i %p') as date_end FROM " . DB_PREFIX . "projects p 
		    left join " . DB_PREFIX . "users u on p.user_id = u.user_id
			WHERE p.status='open' and u.status='active' and p.filtered_auctiontype='regular' and p.currentprice<=".intval($ilance->GPC['coin_less_then'])." and p.date_end>'".DATETIME24H."'";
	}else
	{

		$coin_list=$ilance->GPC['coin_list'];
		$numbers1 = explode(',', $ilance->GPC['coin_list']);
		$numbers2 = array_filter($numbers1);
		$numbers  = array_map('trim', $numbers2);
		$error    = 0;
		$inValidNumbers = array();
		foreach($numbers as $number) 
		{
			if(!preg_match("/^[0-9]*$/", $number)) 
			{
				$error++;                 
				array_push($inValidNumbers,$number);
			}
		}
		if($error != 0) 
		{
 		    print_action_failed("Please Check seperate the Item IDs by comma OR You can use a range my mentioning x to y, for ex., 23330 to 23340 .", $_SERVER['PHP_SELF']);exit();
		}
		$cins_array = explode(",", $coin_list);
		$result = array_unique($cins_array);
		$total_coin_not_list  = array();
		$n = 1;
		$varbreak='';
		foreach($result as $ss_array)
		{
			if ($ss_array > 0) 
			{
				$RE_SQL="SELECT * FROM " . DB_PREFIX . "projects p
				left join " . DB_PREFIX . "users u on p.user_id = u.user_id
				WHERE p.project_id = ".$ss_array." and u.status='active' and p.date_end>'".DATETIME24H."' and p.status='open' and p.filtered_auctiontype='regular'";
				$re_result=$ilance->db->query($RE_SQL, 0, null, __FILE__, __LINE__);
				$re_place_count=$ilance->db->num_rows($re_result);
				if ($re_place_count > 0)
				{ 
					 
				}
				else
				{
					$sql24="SELECT * FROM " . DB_PREFIX . "projects WHERE ((filtered_auctiontype = 'regular' AND haswinner > 0  AND winner_user_id > 0) OR (filtered_auctiontype =  'fixed')) AND project_id = ".$ss_array."";
					$result24 = $ilance->db->query($sql24, 0, null, __FILE__, __LINE__);
					if($ilance->db->num_rows($result24)>0)
					{	
					 					 
					}
					else
					{
						$sql25="select * from ".DB_PREFIX."coins where coin_id = ".$ss_array."";
						$result25 = $ilance->db->query($sql25, 0, null, __FILE__, __LINE__);
						if($ilance->db->num_rows($result25)>0)
						{	
							$res_coin=$ilance->db->fetch_array($result25);
							if($res_coin['Buy_it_now'] == '')
							{
								// $ipaddress      = IPADDRESS;
								// $createdate     = DATETIME24H;						
								// $insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
								// (id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
								// VALUES(
								// NULL,
								// '" . intval($ss_array). "',     
								// 'No',
								// '" . $ilance->db->escape_string($createdate) . "',
								// 'bid',
 							// 	'" . $ilance->db->escape_string($ipaddress) . "') ";
								// $ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
								$total_coin_not_list[]=$ss_array;

								$varbreak.=  $ss_array.",";
								if($n % 15 == 0){
								$varbreak .= "<br />";
								}
								$n++;

							}
						}
					}
				}
			}
		}

	    $final_coin_list    = @implode(",",$numbers);
	    $result_Not_list    = @$varbreak;
	    $tot_coins          = count($total_coin_not_list);
 		$SQL="SELECT p.project_id,p.project_title,p.bids,p.startprice,p.currentprice,date_format(p.date_end,'%b %d %Y %h:%i %p') as date_end FROM " . DB_PREFIX . "projects p
			left join " . DB_PREFIX . "users u on p.user_id = u.user_id
		    WHERE p.project_id in (".$final_coin_list.") and u.status='active' and p.date_end>'".DATETIME24H."' and p.status='open' and p.filtered_auctiontype='regular'";
		    $result222=$ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result222) > 0)
			{

			}
			else
			{
				if($tot_coins > 0)
				{
 					foreach($total_coin_not_list as $number) 
					{						 
								$ipaddress      = IPADDRESS;
								$createdate     = DATETIME24H;						
								$insert_sql="INSERT INTO " . DB_PREFIX . "cron_not_placebid
								(id, project_id,Bid_listed, place_bid_createdate,status,ipaddress)
								VALUES(
								NULL,
								'" . intval($number). "',     
								'No',
								'" . $ilance->db->escape_string($createdate) . "',
								'bid',
 								'" . $ilance->db->escape_string($ipaddress) . "') ";
								$ilance->db->query($insert_sql, 0, null, __FILE__, __LINE__);
					}
					print_action_success("Added Not Listed coins in cron job:- ".$result_Not_list."", $_SERVER['PHP_SELF']);
					exit();
				}
				else
				{
					print_action_failed("We're sorry. Please give valid coin details.", $_SERVER['PHP_SELF']);
					exit();
				}
			}
	}
	$result=$ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
	$place_count=$ilance->db->num_rows($result);
	$i=1;
	if ($place_count > 0)
	{
		$rowcount=0;
		while($line = $ilance->db->fetch_array($result))
		{
			$line['slno']=$i;
			$row[]=$line;
			$rowcount++;
			$i++;
		}
		$projects_list=$row;
	}else
	{
		
	}





	$result_Not_list    = @$varbreak;
	$result_Not_lists    = @implode(",",$total_coin_not_list);
	$pprint_array = array('result_Not_lists','result_Not_list','bidder_id','place_count','login_include','headinclude','form_action','inhousebidders_drop_down','buildversion','ilanceversion','login_include_admin','clientip','cmd','remember_checked','input_style','redirect','referer','rid','login','admin_cookie','enter_username','enter_password','login_include','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'place_bid_confirmation.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main',array('projects_list'));
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}
else
{
	$inhousebidders_drop_down	='<select name="fixed_bidder_id">
		
		<option value="28">2222</option>
		</select>';

		$con  = $ilance->db->query("SELECT active FROM " . DB_PREFIX . "cron WHERE cronid='32'");
		$row = $ilance->db->fetch_array($con);
 		if($row['active'])
		{
		$condition= "checked";
		}
		else
		{
		  $condition= "";
		}
		$sql6="SELECT * FROM " . DB_PREFIX . "cron_placebid WHERE Bid_Placed = 'No'";		
		$result6 = $ilance->db->query($sql6, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result6)>0)
		{
			$count=1;
			while($line6= $ilance->db->fetch_array($result6))
			{
				$place_bid=$count++;;

			}
		}
		else
		{
			$place_bid = "<span style='color:green'>Placed All Bids</span>";
		}
		$sql7="SELECT * FROM " . DB_PREFIX . "cron_not_placebid WHERE Bid_listed = 'No' AND status= 'bid'";		
		$result7 = $ilance->db->query($sql7, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result7)>0)
		{
			$count=1;
			while($line6= $ilance->db->fetch_array($result7))
			{
				$not_place_bid=$count++;;

			}
		}
		else
		{
			$not_place_bid = "<span style='color:green'>Placed All Bids</span>";
		}



	$pprint_array = array('not_place_bid','place_bid','condition','coin_list','headinclude','form_action','inhousebidders_drop_down','buildversion','ilanceversion','login_include_admin','clientip','cmd','remember_checked','input_style','redirect','referer','rid','login','admin_cookie','enter_username','enter_password','login_include','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'place_bid.html', 2);
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
