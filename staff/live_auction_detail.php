<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
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
error_reporting(e_all);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{

	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
	$scriptpageprevnext = 'live_auction_detail.php?';

	$con_listing = $ilance->db->query("SELECT project_id
										FROM " . DB_PREFIX . "projects										
										WHERE status = 'open'
										ORDER BY date_end DESC										
										");
	$number = (int)$ilance->db->num_rows($con_listing);
	
	$con_listingg = $ilance->db->query("SELECT project_id,project_title,pcgs,views,startprice,currentprice,bids
										FROM " . DB_PREFIX . "projects										
										WHERE status = 'open'
										ORDER BY date_end ASC
										LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
										");
	if($ilance->db->num_rows($con_listingg) > 0){
	
		while($row_list = $ilance->db->fetch_array($con_listingg)){
		
			$res_arr['item_id'] = $row_list['project_id'];
			$res_arr['item_title'] =  '<span class="blue"><a href="'.HTTP_SERVER.$ilpage['merch'].'?id='.$row_list['project_id'].'">'.stripslashes($row_list['project_title']).'</a></span>';
			$res_arr['item_pcgs'] = $row_list['pcgs'];
			$res_arr['item_views'] = $row_list['views'];
			$res_arr['item_startprice'] = $ilance->currency->format($row_list['startprice']);
			$res_arr['item_currentprice'] = $ilance->currency->format($row_list['currentprice']);
			$res_arr['item_bid_count'] = $row_list['bids'];
			
			
			$con_listing1 = $ilance->db->query("SELECT MAX(p.maxamount) as maxamountt,u.username
											FROM " . DB_PREFIX . "proxybid p
											LEFT JOIN 	" . DB_PREFIX . "users u ON p.user_id=u.user_id								
											WHERE p.project_id = '".$row_list['project_id']."'
											
											");
										
			if($ilance->db->num_rows($con_listing1) > 0 ){
				while($row_list1 = $ilance->db->fetch_array($con_listing1)){		
					
					if(!empty($row_list1['maxamountt'])){
					
						$res_arr['item_secret_max_bid_user'] = '<div class="black"><strong>Secret Max Bid:&nbsp;'.$ilance->currency->format($row_list1['maxamountt']).'</strong>&nbsp;( <span class="blue">'.$row_list1['username']. '</span> )</div>';;
					}
					else{
					
						$res_arr['item_secret_max_bid_user']='';
					}		
					
					
				}
			}
			
			
			
			
			$con_listing2 = $ilance->db->query("SELECT count(user_id) as watchers
											FROM " . DB_PREFIX . "watchlist																	
											WHERE watching_project_id = '".$row_list['project_id']."'																
											");
											
			
			if($ilance->db->num_rows($con_listing2) > 0){
				while($row_list2 = $ilance->db->fetch_array($con_listing2)){		
				
					$res_arr['item_watch_count'] = $row_list2['watchers'];			
				}
			}
			else{
				
				$res_arr['item_watch_count'] = 0;	
			
			}
			
			$holding_area_list[] = $res_arr;
			
		}
	}
	else{
	
		$show['no']='no_live_auctions';
	}
	
	
	$prof1 = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
	
	$pprint_array = array('prof1','date_down','site_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
     
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'live_auction_detail.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('holding_area_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}