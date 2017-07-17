<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
    'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$show['showsearch_coins'] = $show['coins_not_found'] = false;
	$search_img =0;
	$search_ntimg=0;
	$search_bthimg =1;
	
	
	$filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'user_id';
	$filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->db->escape_string($ilance->GPC['filtervalue']) : '';
	$searchd_coins = array();
	$user_id = $username = '';

	if($filterby == 'user_id')
		$filter_by = '<option value="user_id" selected="selected">User ID</option><option value="username">Username</option>
		<option value="email">Email</option><option value="coin_id">Coin id</option>';
	else if($filterby == 'username')
		$filter_by = '<option value="user_id">User ID</option><option value="username" selected="selected">Username</option>
		<option value="email" >Email</option><option value="coin_id">Coin id</option>';
	else if($filterby == 'coin_id')
		$filter_by = '<option value="user_id">User ID</option><option value="username">Username</option>
		<option value="email" >Email</option><option value="coin_id" selected="selected">Coin id</option>';
	else 
		$filter_by = '<option value="user_id">User ID</option><option value="username">Username</option>
		<option value="email" selected="selected">Email</option><option value="coin_id">Coin id</option>';

    if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'coins_list') 
	{
		if(!empty($ilance->GPC['filtervalue']))
		{
			$show['showsearch_coins'] = true;
			
			if($ilance->GPC['imgss'] == 'imaged')
			{	
				$chk_img_sql_cnd='and a.project_id IS NOT NULL';
				$search_img =1;
				$search_ntimg =0;
				$search_bthimg =0;
				
			}
			else if($ilance->GPC['imgss'] == 'notimaged')
			{
				$chk_img_sql_cnd='and a.project_id IS NULL';
				$search_img = 0;
				$search_ntimg =1;
				$search_bthimg =0;
			}
			else
			{
				$chk_img_sql_cnd='';
				$search_img =0;
				$search_ntimg=0;
				$search_bthimg =1;
			}
			
						
			
			
			if($ilance->GPC['filterby'] == 'coin_id')
			{
				
				$sql = $ilance->db->query("SELECT p.project_id,c.coin_id,p.user_id,c.Minimum_bid,c.Buy_it_now,p.project_title,p.startprice,p.currentprice,
				p.buynow_price,p.bids,p.date_End,u.username,a.attachid
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "coins c ON p.project_id=c.coin_id AND c.pending = 0 AND c.site_id ='0'
				LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id AND a.attachtype = 'itemphoto'
				left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
				WHERE c.coin_id = '".$filtervalue."'
				AND p.status = 'open'
				".$chk_img_sql_cnd."
				AND c.coin_id IS NOT NULL 
				AND ((p.filtered_auctiontype = 'regular' AND p.winner_user_id  = '0') 
				OR (p.buynow = '1' AND p.filtered_auctiontype = 'fixed'  AND p.buynow_qty > '0'))");	
				
				
				if ($ilance->db->num_rows($sql) > 0) 
				{
					while ($reslt = $ilance->db->fetch_array($sql, DB_ASSOC)) 
					{
						
					$user_id = $reslt['user_id'];
					$username = $reslt['username'];
					$coin['coin_id'] = $reslt['project_id'];
					$coin['title'] = $reslt['project_title'];
					$coin['bids'] = $reslt['bids'];
					$coin['min_bid'] = $ilance->currency->format($reslt['startprice']);
					$coin['max_bid'] = $ilance->currency->format($reslt['currentprice']);
					$coin['end_date'] = $reslt['date_End'];
					$coin['img']='No';
					if($reslt['attachid']>0)
					$coin['img']='Yes';
					$searchd_coins[] = $coin;
					}						
				}
				else
				{
					
					print_action_failed('No Result Found,Please Enter Correct Coin id / Project id  and try again', 'extend_end_dates.php');
					exit();
					
				}
				 
					
			}
			else
			{
				$searchby = $filterby." = '".$filtervalue."'";
				
				//echo " SELECT user_id FROM " . DB_PREFIX . "users WHERE ".$searchby;exit;
				$sql = $ilance->db->query(" SELECT user_id, username FROM " . DB_PREFIX . "users
											WHERE ".$searchby);	
				
									
			
				if ($ilance->db->num_rows($sql) > 0) 
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$user_id = $res['user_id'];
					$username = $res['username'];
					//echo $res['user_id']." <br/><br/>";//exit;
					
					
						$clsql = $ilance->db->query(" SELECT p.project_id,a.attachid,p.project_title, p.bids, p.startprice, p.currentprice, DATE(p.date_end) as date_End
									FROM " . DB_PREFIX . "projects p 
									LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id AND a.attachtype = 'itemphoto'
									WHERE p.user_id = '".$res['user_id']."' AND p.status = 'open' ".$chk_img_sql_cnd." ORDER BY p.project_id ASC ");
							 if ($ilance->db->num_rows($clsql) > 0) 
							 {
								while ($reslt = $ilance->db->fetch_array($clsql, DB_ASSOC)) 
								{
									$coin['coin_id'] = $reslt['project_id'];
									$coin['title'] = $reslt['project_title'];
									$coin['bids'] = $reslt['bids'];
									$coin['min_bid'] = $ilance->currency->format($reslt['startprice']);
									$coin['max_bid'] = $ilance->currency->format($reslt['currentprice']);
									$coin['end_date'] = $reslt['date_End'];
									$coin['img']='No';
									if($reslt['attachid']>0)
									$coin['img']='Yes';
									
									
									$searchd_coins[] = $coin;
								}
							}
				}
				else
				{
					if ($ilance->GPC['filterby'] == 'user_id') 
						{
							print_action_failed('No Result Found', 'extend_end_datess.php');
							exit();
						} 
						elseif ($ilance->GPC['filterby'] == 'username') 
						{
							print_action_failed('No Result Found', 'extend_end_datess.php');
							exit();
						} 
						else
						{
							print_action_failed('No Result Found', 'extend_end_datess.php');
							exit();
						}
				}
				
			}
				
		    
		}
		else
		{
			print_action_failed('Please Enter the value and try again', 'extend_end_dates.php');
			exit();
		}	
		//echo '<pre>';print_r($ilance->GPC);exit;
	}

	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'extend_enddate') 
	{
		$sellerid = $ilance->GPC['userid'];
		$end_date = $ilance->GPC['end_date'];
		$coin_ids = $ilance->GPC['coin_ids'];
		
		if(($sellerid AND $coin_ids AND $end_date) !='' ) 
		{
		$coins = implode(', ', $coin_ids);
		$count = count($ilance->GPC['coin_ids']);

			
		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET End_Date = concat('".$end_date." ',time(End_Date)) WHERE coin_id IN (".$coins.") AND user_id = ".$sellerid);

		$ilance->db->query("UPDATE " . DB_PREFIX . "projects SET date_end = concat('".$end_date." ',time(date_end)) WHERE project_id IN (".$coins.") AND user_id = ".$sellerid." AND status = 'open'");

		print_action_success('<b>Coins: '.$coins.'</b><br/>Totally '.$count.' coins end date was successfully changed to '.$end_date.' ', 'extend_end_dates.php');
		exit();
		}
		else
		{
			print_action_failed('Please select aleast single coin. and try again', 'extend_end_datess.php');
			exit();
			
		}
		echo "UPDATE " . DB_PREFIX . "coins SET End_Date = DATE_ADD(End_Date, INTERVAL ".$extendby." DAY) WHERE coin_id IN (".$coins.") AND user_id = ".$sellerid;
		echo "<br/><br/>UPDATE " . DB_PREFIX . "projects SET date_end = DATE_ADD(date_end, INTERVAL ".$extendby." DAY) WHERE project_id IN (".$coins.") AND user_id = ".$sellerid." AND status = 'open' ";
		exit;

		echo $coins;exit;

		//UPDATE ilance_coins SET End_Date = DATE_ADD(End_Date, INTERVAL 7 DAY) WHERE `coin_id` IN (304010,304012,304014,304016,304019,304021,304023,304025,304027,304029,304031,304033,304035,304037,304039,304041,304043,304045,304047,304049,304051,304053,304055,304057,304059,304061,304063,304065,304067,304069,304071,304073,304075,304078,304080,304082,304085,304091,304092,304094,304096,304098,304100,304102,304104,304106,304109,304111,304115,304117,304119,304121,304123,304126,304128,304132) AND `user_id` = 6484
		echo '<pre>';
		print_r($ilance->GPC);
		exit;
	}	
		$coins_count = count($searchd_coins);

	$pprint_array = array('filter_by','filtervalue','coins_count','user_id','username','search_img','search_ntimg','search_bthimg',
	'lastamount','total_advance','statecount','date1','date','totbids',
	'totbidamount','totbinamount','totfvf','totlisting_fee','totseller_fee',
	'totnet_consignor','daylist','monthlist','yearlist','daylist1','monthlist1',
	'yearlist1','buildversion','ilanceversion','login_include_admin','reportorderby',
	'reportfromrange','reportrange','radiopast','radioexact','reportcolumns','reportaction',
	'reportshow','customprevnext','reportoutput','remote_addr','rid','login_include','headinclude',
	'area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer',
	'certnum');
    
	($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'extending.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('searchd_coins','statement'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
		

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}	
?>
