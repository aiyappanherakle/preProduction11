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
        'search',
        'stores',
		'accounting',
        'wantads',
        'subscription',
        'preferences',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'search',
	'tabfx',
	'jquery',
	'modal',
	'yahoo-jar',
	'flashfix'
);

// #### define top header nav ##################
$topnavlink = array(
        'main_listings'
);

// #### setup script location ##################################################
define('LOCATION', 'search');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once DIR_CORE . 'functions_search.php';
//error_reporting(E_ALL);

// #### setup default breadcrumb ###############################################
$navcrumb = array("past_auction_records.php" => 'Past Auction Records');
$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';
$auction_records = array();
$page_title = 'Past Auction Records | GreatCollections';

$past_details = $past_records = $auction_date = $sort_coin = $sort_title = $sort_realized = '';
$auction_id = $total_realized = 0;

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);		
	$sql_limit = 'LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

$page_url = 'past_auction_records.php';

$icon_coin = $icon_title = $icon_realized = 'expand_collapsed.gif';

	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd']=='details' AND (isset($ilance->GPC['id']) AND $ilance->GPC['id']>0) AND (isset($ilance->GPC['date']) AND $ilance->GPC['date']!=''))
	{

		$end_dates = $ilance->GPC['date'];
		$dates_arr  = explode('-', $end_dates);
		if (count($dates_arr) == 3) {
		    if (checkdate($dates_arr[1], $dates_arr[2], $dates_arr[0])) {
		        // valid date ...
		    } else {
		        print_notice("Something went wrong, Please try again later", $_SERVER['PHP_SELF'], $phrase['_back']);
               	exit();
		    }
		} else {
		    print_notice("Something went wrong, Please try again later", $_SERVER['PHP_SELF'], $phrase['_back']);
            exit();
		}

		$auction_id = $ilance->GPC['id'];
		$navcrumb[''] = 'Auction Results: Internet Auction #'.$auction_id;

		$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
		$ilance->GPC['ord'] = isset($ilance->GPC['ord']) ? $ilance->GPC['ord'] : '';

		$order_by = 'asc';
		$sort_by = 'p.project_id';

		if($ilance->GPC['sort']>0 && ($ilance->GPC['ord'] == 'asc' || $ilance->GPC['ord'] == 'desc'))
		{
			$sort_coin = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=1&ord=asc';//;
			$sort_title = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=2&ord=asc';
			$sort_realized = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=3&ord=asc';

			$order_by = $ilance->GPC['ord'];
			if($ilance->GPC['sort'] == 1)
			{
				$sort_by = 'p.project_id';
				if($ilance->GPC['ord'] == 'asc')
				{
					$sort_coin = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=1&ord=desc';
					$icon_coin = 'expand.gif';
				}
			}
				

			if($ilance->GPC['sort'] == 2)
			{
				$sort_by = 'p.project_title';
				if($ilance->GPC['ord'] == 'asc')
				{
					$sort_title = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=2&ord=desc';
					$icon_title = 'expand.gif';
				}
			}

			if($ilance->GPC['sort'] == 3)
			{
				$sort_by = 'p.currentprice';
				if($ilance->GPC['ord'] == 'asc')
				{
					$sort_realized = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=3&ord=desc';
					$icon_realized = 'expand.gif';
				}
			}
				
		}
		else
		{
			$sort_coin = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=1&ord=asc';//;
			$sort_title = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=2&ord=asc';
			$sort_realized = $page_url.'?cmd=details&id='.$auction_id.'&date='.$end_dates.'&sort=3&ord=asc';
		}

		// $sort_coin = $page_url.'?cmd=details&id='.$auction_id.'&sort=1&ord=desc';//;
		// $sort_title = $page_url.'?cmd=details&id='.$auction_id.'&sort=2&ord=desc';
		// $sort_realized = $page_url.'?cmd=details&id='.$auction_id.'&sort=3&ord=desc';

			$res = $ilance->db->fetch_array($sql);
			$auction_date = date('D, F d, Y', strtotime($ilance->GPC['date']));

			// $pquery="SELECT project_id, project_title, currentprice
			// 	FROM " . DB_PREFIX . "projects 
			// 	WHERE DATE(date_end) = '".$ilance->GPC['date']."'
			// 	AND DAYOFWEEK(date_end) = 1
			// 	AND filtered_auctiontype = 'regular'
			// 	AND winner_user_id>0 AND haswinner=1 AND bids>0
			// 	AND status != 'open'
			// 	ORDER BY  $sort_by $order_by  ";

			$pquery="SELECT p.project_id, p.project_title, p.currentprice, i.amount as buyerfee
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "invoices i ON p.project_id = i.projectid AND i.isbuyerfee = 1 AND i.user_id = p.winner_user_id
				WHERE DATE(p.date_end) = '2016-04-10' AND DAYOFWEEK(p.date_end) = 1 
				AND p.filtered_auctiontype = 'regular' 
				AND p.winner_user_id>0 AND p.haswinner=1 
				AND p.bids>0 AND p.status != 'open' ORDER BY $sort_by $order_by ";
		

			$psql = $ilance->db->query($pquery, 0, null, __FILE__, __LINE__);	
			if ($ilance->db->num_rows($psql) > 0)
			{
				//echo $ilance->db->num_rows($psql);
				$show['past_details'] = 1;
				while($resq = $ilance->db->fetch_array($psql))
				{
					//echo '<pre>';print_r($ressq);exit;
					$realized = $resq['currentprice']+$resq['buyerfee'];
					$total_realized += $realized;
					$resq['coin_price'] =$ilance->currency->format($resq['currentprice']+$resq['buyerfee']);
					$resq['url'] = construct_seo_url('productauctionplain', 0, $resq['project_id'], stripslashes($resq['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
					$auction_records[] = $resq;
				}

			}
			

		$total_realized = $ilance->currency->format($total_realized);
		$show['details'] = 1;
		
	}
	else
	{
		$show['records'] = 1;
		$query="SELECT DATE(date_end) as enddates 
				FROM " . DB_PREFIX . "projects 
				WHERE date_end <= '".date('Y-m-d h:i:s')."' AND filtered_auctiontype = 'regular' 
				AND DAYOFWEEK(date_end) = 1
				AND winner_user_id>0 AND haswinner=1 AND bids>0
				GROUP BY DATE(date_end)  
				ORDER BY enddates  DESC ";


		$sql = $ilance->db->query($query.$sql_limit, 0, null, __FILE__, __LINE__);
		 
		
		if ($ilance->db->num_rows($sql) > 0)
        {
			$show['past_records'] = 1;
			$b = 0;
			$sql1 = $ilance->db->query($query, 0, null, __FILE__, __LINE__);
			$k = $ilance->db->num_rows($sql1)-(($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']);


			$scriptpage = HTTP_SERVER .'past_auction_records.php?'. print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
			$number = $ilance->db->num_rows($sql1);

			$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);	


			while($ressq = $ilance->db->fetch_array($sql))
			{
				$b++;
				$start = $end = '';
				if($b == 1)
					$past_records .= '<tr>';	

				$end_Date = $ressq['enddates'];
				$ressq['enddates'] = date('D, F d, Y', strtotime($ressq['enddates']));
				$past_records .= '<td><a href="past_auction_records.php?cmd=details&id='.$k.'&date='.$end_Date.'">Internet Auction #'.$k.'<span>'.$ressq['enddates'].'</span>'.'</a></td>';
				
				if($b == 2)
				{
					$b=0;
					$end = '<tr>';
				}

				$k--;
				
			}
		}
	}
	


($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'past_auction_records.html');
$ilance->template->parse_if_blocks('main');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('auction_records'));

$pprint_array = array('total_realized','icon_coin','icon_title','icon_realized','sort_coin','sort_title','sort_realized','page_url','auction_date','auction_id','past_details','past_records','prevnext','region_pulldown_product','locatedin_pulldown_product','availableto_pulldown_product','search_bidrange_pulldown_product','search_radius_country_pulldown_product','search_country_pulldown_product','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_displayfeatured','cb_hidelisted','cb_proxybit','cb_hideverbose','productavailable','productselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','product_category_selection','search_productauctions_img','search_productauctions_collapse','pfp_category_left','rfp_category_left','input_style','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');

($apihook = $ilance->api('search_start_template')) ? eval($apihook) : false;
 
$ilance->template->pprint('main', $pprint_array);
exit();


		
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>