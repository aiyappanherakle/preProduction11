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
        'buying',
        'selling',
        'rfp',
        'search',
        'feedback',
        'accounting',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
        'tabfx',
	'flashfix',
	'jquery'
);

// #### setup script location ##################################################
define('LOCATION', 'buying');
// #### require backend ########################################################
require_once('./functions/config.php');
require_once DIR_CORE . 'functions_search.php';
 $show['widescreen'] = true;
// #### setup default breadcrumb ###############################################
$area_title = $phrase['_access_denied'];
$page_title = SITE_NAME . ' - ' . $ilance->GPC['cmd'];
$navcrumb = array("$ilpage[buying]" => $ilcrumbs["$ilpage[buying]"]);

$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';

if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['buying'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)?1:intval($ilance->GPC['page']);
$sql_limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

$date_filter_values['0'] = '2011-01-01';
$date_filter_values['1'] = DATEYESTERDAY;
$date_filter_values['2'] = SEVENDAYSAGO;
$date_filter_values['3'] = THIRTYDAYSAGO;
$date_filter_values['4'] = SIXTYDAYSAGO;
$date_filter_values['5'] = NINETYDAYSAGO;
$date_filter_values['6'] = ONEEIGHTYDAYSAGO;
$date_filter_values['7'] = THREESIXTYDAYSAGO;
$date_filter_values['8'] = SEVENTWENTYDAYSAGO;
$date_filter_values['9'] = THOUSANDEIGHTYDAYSAGO;

// #### BUYING ACTIVITY MENU ###################################################
 
				///Active start
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'active')
		{
		$area_title = 'Currently Buying';
		
		$ilance->bid = construct_object('api.bid');
		$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
		$ilance->bid_proxy = construct_object('api.bid_proxy');
		$ilance->bid_permissions = construct_object('api.bid_permissions');
		//july 4sort date_end
			$gc_buy = " SELECT *,
			(SELECT MAX(bidamount) FROM  " . DB_PREFIX . "project_bids WHERE project_id = p.project_id limit 1 ) as current_bid,
			(SELECT user_id FROM " . DB_PREFIX . "project_bids WHERE project_id =  p.project_id ORDER BY bidamount DESC, bid_id DESC LIMIT 1) as winning_user_id,
			(SELECT maxamount FROM " . DB_PREFIX . "proxybid WHERE project_id = p.project_id  AND user_id = '".$_SESSION['ilancedata']['user']['userid']."' LIMIT 1 ) as proxy_amount,
			(SELECT filename FROM " . DB_PREFIX . "attachment WHERE visible='1' AND project_id = p.project_id AND attachtype='itemphoto' limit 1 ) as filename,
			UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
	                FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "project_bids b on p.project_id = b.project_id		
	                WHERE b.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND   p.visible ='1'
					AND	  b.bidstatus = 'placed'
					AND   p.status = 'open'
					GROUP BY p.project_id
					ORDER BY p.date_end ASC,b.bid_id DESC ";
			$sql_gcbuy1 = $ilance->db->query($gc_buy , 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($sql_gcbuy1);
					
			$sql_gcbuy = $ilance->db->query($gc_buy.$sql_limit, 0, null, __FILE__, __LINE__);
				
			$scriptpage = HTTP_SERVER . 'Buy/Active?' . print_hidden_fields(true, array('subcmd', 'cmd', 'page', 'budget'), true, '', '', $htmlentities = true, $urldecode = false);
			$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);

				if ($ilance->db->num_rows($sql_gcbuy) > 0)
				{
				//status
				while ($res_gcact = $ilance->db->fetch_array($sql_gcbuy))
					{
						$pbit=$res_gcact['proxy_amount'];
						if ($pbit > 0)
						{
							$highbidderidtest = $res_gcact['winning_user_id'];
							if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
							$proxybit = '<span class="green">'.$phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit,$ilconfig['globalserverlocale_defaultcurrency']) . '</span>';
							else
							$proxybit = '<span class="red">'.$phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $ilconfig['globalserverlocale_defaultcurrency']) . '</span>';
						}
					
					if(strlen($res_gcact['filename'])>0)
					{
						$uselistr = HTTPS_SERVER . 'image/105/170/' . $res_gcact['filename'] ;
						if ($ilconfig['globalauctionsettings_seourls'])
							$htm ='<a href="Coin/'.$res_gcact['project_id'].'/'.construct_seo_url_name($res_gcact['project_title']).'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
					    else
					    	$htm ='<a href="merch.php?id='.$res_gcact['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
					}else
					{
					   	$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						$htm ='<img src="' . $uselistr . '">';
					}
				  
					
					$res_gc_active['thumbnail'] = $htm;
					
					$res_gc_active['item_id'] = $res_gcact['project_id']; 
							// nov 28 from seo
					if ($ilconfig['globalauctionsettings_seourls'])	
						$res_gc_active['item_title'] ='<a href="Coin/'.$res_gcact['project_id'].'/'.construct_seo_url_name($res_gcact['project_title']).'">'.$res_gcact['project_title'].'</a>';
					else
					   	$res_gc_active['item_title']='<a href="merch.php?id='.$res_gcact['project_id'].'" >'.$res_gcact['project_title'].'</a>';
					
					
					//$res_gc_active['item_title'] = '<a href="'. $ilpage['merch'] .'?id='.$res_gcact['project_id'].'">'.$res_gcact['project_title'].'</a>';
					$res_gc_active['bids'] = $res_gcact['bids'];
					$res_gc_active['timelef'] = $res_gcact['date_end'];
					$res_gc_active['status'] = $res_gcact['status'];
					$res_gc_active['timeleft'] = $res_gcact['date_starts'];
					$res_gc_active['current_bid'] = $res_gcact['current_bid'];
					//karthik
					$res_gc_active['timelef'] = auction_time_left_new($res_gcact,false);
						// end
					if($res_gcact['proposal'] !='')
					{
					$res_gc_active['proposal'] = substr($res_gcact['proposal'], 0, 30); 
					}
					else{
					$res_gc_active['proposal'] = '-';
					}
					$res_gc_active['proposal'] = $proxybit;
					$res_gcacting[] = $res_gc_active;
					}
				}
				else
				{				
				$res_gcacting['act'] = 'Nofound';
				}
///Active end
$pprint_array = array('prevnext','daylist','monthlist','yearlist','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'buy_active.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('service_buying_activity','res_gcacting','res_gcswining','res_gcsnotwining','res_gc_buynow'));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
//I Won start
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'won')
{	
	$area_title = 'Item Won';
	$scriptpage = HTTP_SERVER . 'Buy/Won'. print_hidden_fields(true, array('page', 'budget'), true, '', '', $htmlentities = true, $urldecode = false);   	
	$endDate = DATETODAY;
	$ilance->GPC['searchbuy'] =isset($ilance->GPC['searchbuy'])?intval($ilance->GPC['searchbuy']):2;
	$gcwon = " SELECT p.project_id,p.date_end,b.bidamount,p.bids,p.project_title,i.status as invoice_status,i.invoiceid,i.paiddate,
			(SELECT filename FROM " . DB_PREFIX . "attachment WHERE visible='1' AND project_id = p.project_id AND attachtype='itemphoto' limit 1) as filename
			FROM " . DB_PREFIX . "projects p 
			left join " . DB_PREFIX . "project_bids b on p.project_id = b.project_id
			left join (SELECT * from " . DB_PREFIX . "invoices where user_id ='".$_SESSION['ilancedata']['user']['userid']."' and isbuyerfee=0  and status!='cancelled'  group by projectid) i  on p.project_id=i.projectid
			WHERE  	p.winner_user_id = '".$_SESSION['ilancedata']['user']['userid']."' 
			AND		b.bidstatus = 'awarded'
			AND    	p.haswinner = '1'
			AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchbuy']]."')
			ORDER BY p.date_end DESC "; 
	$sql_gcwon1 = $ilance->db->query($gcwon, 0, null, __FILE__, __LINE__); 
	$number = $ilance->db->num_rows($sql_gcwon1);

	$sql_gcwon = $ilance->db->query($gcwon.$sql_limit, 0, null, __FILE__, __LINE__); 
	$ilance->GPC['searchbuy']=isset($ilance->GPC['searchbuy'])?intval($ilance->GPC['searchbuy']):0;
	$drop_value=form_days_drop_down('searchbuy',$ilance->GPC['searchbuy']);

	$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);

		if ($ilance->db->num_rows($sql_gcwon) > 0)
		{
		while ($res_gcwon = $ilance->db->fetch_array($sql_gcwon))
			{
			if(strlen($res_gcwon['filename'])>0)
				$uselistr = HTTPS_SERVER . 'image/105/170/' . $res_gcwon['filename'] ;
			else
				$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';

			if ($ilconfig['globalauctionsettings_seourls'])
			 	$htm ='<a href="Coin/'.$res_gcwon['project_id'].'/'.construct_seo_url_name($res_gcwon['project_title']).'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
            else
		    	$htm ='<a href="merch.php?id='.$res_gcwon['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
		    if($res_gcwon['invoiceid']>0)
		    {
		    $sql_com = $ilance->db->query("SELECT status,paiddate FROM " . DB_PREFIX . "invoices
	                                               WHERE combine_project LIKE '%".$res_gcwon['invoiceid']."%'
	                                               AND user_id ='".$_SESSION['ilancedata']['user']['userid']."'" , 0, null, __FILE__, __LINE__);
						
				if($ilance->db->num_rows($sql_com)>0)
				{
					$fetch_com=$ilance->db->fetch_array($sql_com);
					//
					if($fetch_com['status'] == 'paid')
				   {
					 $res_gc_won['invoice'] = 'Paid-'.$fetch_com['paiddate'];
				   }
				   else if($fetch_com['status'] == 'unpaid')
				   {
					 $res_gc_won['invoice'] = '<a href="'. HTTPS_SERVER .'buyer_invoice.php'. '">Click to Pay</a>';
				   } 
				   else if($fetch_com['status'] == 'scheduled')
				   {
					 $res_gc_won['invoice'] = 'Payment Pending';
				   }
				   else
				   {
					$res_gc_won['invoice'] = $fetch_com['status'];
				   }
				}else{
					$res_gc_won['invoice'] = '<a href="'. HTTPS_SERVER .'buyer_invoice.php'. '">Click to Pay</a>';	
				}	
		    }
			else{
			 	$res_gc_won['invoice'] = '<a href="'. HTTPS_SERVER .'buyer_invoice.php'. '">Click to Pay</a>';
			 }
			

			$res_gc_won['thumbnail'] = $htm; 
			$res_gc_won['item_id'] = $res_gcwon['project_id'];
						//nov 28 for seo
	 		if ($ilconfig['globalauctionsettings_seourls'])	
				$res_gc_won['item_title'] ='<a href="Coin/'.$res_gcwon['project_id'].'/'.construct_seo_url_name($res_gcwon['project_title']).'">'.$res_gcwon['project_title'].'</a>';
			else
				$res_gc_won['item_title']='<a href="merch.php?id='.$res_gcwon['project_id'].'">'.$res_gcwon['project_title'].'</a>';
			
			//$res_gc_won['item_title'] = '<a href="'. $ilpage['merch'] .'?id='.$res_gcwon['project_id'].'">'.$res_gcwon['project_title'].'</a>';
			$res_gc_won['bids'] = $res_gcwon['bids'];
			$res_gc_won['timelef'] = date('m-d-Y',strtotime($res_gcwon['date_end']));
			$res_gc_won['status'] = 'Ended';
			$res_gc_won['current_bid'] = $res_gcwon['bidamount'];
			$res_gcswining[] = $res_gc_won;
			}
		}
		else
		{				
		$res_gcswining['won'] = 'Nofound';
		}
	$pprint_array = array('drop_value','daylist','monthlist','yearlist','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'buy_won.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('service_buying_activity','res_gcacting','res_gcswining','res_gcsnotwining','res_gc_buynow'));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// I donot won start
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'notwon')
{
$area_title = 'Item Not Win';
	$ilance->GPC['searchbuy']=isset($ilance->GPC['searchbuy'])?intval($ilance->GPC['searchbuy']):2;
	$drop_value=form_days_drop_down('searchbuy',$ilance->GPC['searchbuy']);
 
	$scriptpage = HTTP_SERVER . 'Buy/Notwon'. print_hidden_fields(true, array('page', 'budget'), true, '', '', $htmlentities = true, $urldecode = false);	
	$endDate = DATETODAY;
	/*$gcnotwon1 = " SELECT p.project_id FROM " . DB_PREFIX . "projects p
					left join " . DB_PREFIX . "project_bids b on p.project_id = b.project_id
					WHERE  	b.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND     b.bidstatus ='outbid'
					AND 	p.winner_user_id != '".$_SESSION['ilancedata']['user']['userid']."' 					
					AND		(p.status = 'expired' OR p.status='finished')
					AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchbuy']]."') "; 
			

	$gcnotwon = " SELECT p.project_id,p.project_title,p.bids,a.filename,p.date_end,p.currentprice,
			(select maxamount from " . DB_PREFIX . "proxybid where project_id=p.project_id and user_id = '".$_SESSION['ilancedata']['user']['userid']."' ) as maxamount 
			FROM " . DB_PREFIX . "projects p
				left join " . DB_PREFIX . "project_bids b on p.project_id = b.project_id
				left join " . DB_PREFIX . "attachment a on a.visible='1' AND p.project_id = a.project_id AND a.attachtype='itemphoto' 
					WHERE  	b.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND     b.bidstatus ='outbid'
					AND 	p.winner_user_id != '".$_SESSION['ilancedata']['user']['userid']."' 					
					AND		(p.status = 'expired' OR p.status='finished')
					AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchbuy']]."') 
					order by p.project_id "; */
	$gcnotwon1="select  p.project_id
			   from ilance_project_bids b 
				left join ilance_projects p on p.project_id=b.project_id
				where b.user_id='".$_SESSION['ilancedata']['user']['userid']."'  
				AND p.winner_user_id!='".$_SESSION['ilancedata']['user']['userid']."'  and p.status != 'open'
				AND (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchbuy']]."') 
				group by b.project_id";

	$gcnotwon="select b.project_id,max(bidamount),
				p.project_id,p.project_title,p.bids,a.filename,p.date_end,p.currentprice,
			    (select maxamount from " . DB_PREFIX . "proxybid where project_id=p.project_id and user_id = '".$_SESSION['ilancedata']['user']['userid']."' ) as maxamount 
				from ilance_project_bids b 
				left join ilance_projects p on p.project_id=b.project_id
				left join " . DB_PREFIX . "attachment a on a.visible='1' AND p.project_id = a.project_id AND a.attachtype='itemphoto' 
				where b.user_id='".$_SESSION['ilancedata']['user']['userid']."'  
				AND p.winner_user_id!='".$_SESSION['ilancedata']['user']['userid']."'  and p.status != 'open'
				AND (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchbuy']]."') 
				group by b.project_id  
				ORDER BY b.project_id DESC";
			$sql_gcnotwon1 = $ilance->db->query($gcnotwon1, 0, null, __FILE__, __LINE__); 
			$number = $ilance->db->num_rows($sql_gcnotwon1);

			$sql_gcnotwon = $ilance->db->query($gcnotwon.$sql_limit, 0, null, __FILE__, __LINE__);
		
		
			$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);				

				if ($ilance->db->num_rows($sql_gcnotwon) > 0)
				{
				//status
				while ($res_gcnotwon = $ilance->db->fetch_array($sql_gcnotwon))
					{
					
					$res_gc_notwon['max_bid'] = $res_gcnotwon['maxamount'];

					if(strlen($res_gcnotwon['filename'])>0)
					{
						$uselistr = HTTPS_SERVER . 'image/105/170/' . $res_gcnotwon['filename'] ;
						if ($ilconfig['globalauctionsettings_seourls'])
							$htm ='<a href="Coin/'.$res_gcnotwon['project_id'].'/'.construct_seo_url_name($res_gcnotwon['project_title']).'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
						else
							$htm ='<a href="merch.php?id='.$res_gcnotwon['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
					}else
					{
					   $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
					   $htm ='<img src="' . $uselistr . '">';
					}
					
					$res_gc_notwon['thumbnail'] = $htm;
					$res_gc_notwon['item_id'] = $res_gcnotwon['project_id'];
					if ($ilconfig['globalauctionsettings_seourls'])	
					    $res_gc_notwon['item_title'] ='<a href="Coin/'.$res_gcnotwon['project_id'].'/'.construct_seo_url_name($res_gcnotwon['project_title']).'">'.$res_gcnotwon['project_title'].'</a>';
					else
				   		$res_gc_notwon['item_title']='<a href="merch.php?id='.$res_gcnotwon['project_id'].'">'.$res_gcnotwon['project_title'].'</a>';
					
					$res_gc_notwon['bids'] = $res_gcnotwon['bids'];
					$res_gc_notwon['timelef'] = date('m-d-Y H:m:s',strtotime($res_gcnotwon['date_end']));
					$res_gc_notwon['status'] = 'Ended';
					$res_gc_notwon['current_bid'] = $res_gcnotwon['currentprice'];					
					$res_gcsnotwining[] = $res_gc_notwon;
					}
				}
				else
				{				
				$res_gcsnotwining['notwon'] = 'Nofound';
				}

$pprint_array = array('drop_value','daylist','monthlist','yearlist','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'buy_notwon.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('service_buying_activity','res_gcacting','res_gcswining','res_gcsnotwining','res_gc_buynow'));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();				


}
///I donot won end
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buynow')
{
$area_title = 'Items Bought';

$scriptpage = HTTP_SERVER . 'Buy/Buynow' . print_hidden_fields(true, array('page', 'budget'), true, '', '', $htmlentities = true, $urldecode = false);
	$endDate = DATETODAY;
	$ilance->GPC['searchbuy']=(isset($ilance->GPC['searchbuy']))?intval($ilance->GPC['searchbuy']):2;
	$gcbuy = " SELECT * FROM " . DB_PREFIX . "projects p,
			" . DB_PREFIX . "buynow_orders b 
            WHERE  	b.buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'
			AND     p.project_id = b.project_id	
			AND   (date(b.orderdate) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchbuy']]."')
			GROUP BY b.orderid			
			ORDER BY b.orderid DESC	
            ";

	$sql_gcbuy1 = $ilance->db->query($gcbuy, 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($sql_gcbuy1);

	$sql_gcbuy = $ilance->db->query($gcbuy.$sql_limit, 0, null, __FILE__, __LINE__);
		


		
		$drop_value=form_days_drop_down('searchbuy',$ilance->GPC['searchbuy']);
		$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);					
								
				if ($ilance->db->num_rows($sql_gcbuy) > 0)
				{
				//status
				while ($res_gcbuy = $ilance->db->fetch_array($sql_gcbuy))
					{
					 $sql_atty = $ilance->db->query("
                       SELECT * FROM
                       " . DB_PREFIX . "attachment
                       WHERE visible='1'
                                               AND project_id = '".$res_gcbuy['project_id']."'
                                               AND attachtype='itemphoto'
                                               
                       ", 0, null, __FILE__, __LINE__);
               				 $fetch_new=$ilance->db->fetch_array($sql_atty);
                               
					   if($ilance->db->num_rows($sql_atty) == 1)
					   {
							   $uselistr = HTTPS_SERVER . 'image/105/170/' . $fetch_new['filename'] ;
							   //HTTPS_SERVER . $ilpage['image'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_new['filehash'] .'&w=170&h=105';
							   
							   				   //nov 28 for seo
							   
				if ($ilconfig['globalauctionsettings_seourls'])

						$htm ='<a href="Coin/'.$res_gcbuy['project_id'].'/'.construct_seo_url_name($res_gcbuy['project_title']).'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
	              else
					    $htm ='<a href="merch.php?id='.$res_gcbuy['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
							   
							   
							   //$htm ='<a href="'. $ilpage['merch'] .'?id='.$res_gcbuy['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
					   }
					   if($ilance->db->num_rows($sql_atty) == 0)
			   
					   {
						   $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
							   
						   $htm ='<img src="' . $uselistr . '">';
					   }
					
					$res_gcbuynow['thumbnail'] = $htm;
					$res_gcbuynow['item_id'] = $res_gcbuy['project_id'];
									//nov 28 for seo
			 if ($ilconfig['globalauctionsettings_seourls'])	
				  $res_gcbuynow['item_title'] ='<a href="Coin/'.$res_gcbuy['project_id'].'/'.construct_seo_url_name($res_gcbuy['project_title']).'">'.$res_gcbuy['project_title'].'</a>';
			else
				 $res_gcbuynow['item_title']='<a href="merch.php?id='.$res_gcbuy['project_id'].'">'.$res_gcbuy['project_title'].'</a>';
					
					//$res_gcbuynow['item_title'] = '<a href="'. $ilpage['merch'] .'?id='.$res_gcbuy['project_id'].'">'.$res_gcbuy['project_title'].'</a>';
					$res_gcbuynow['bids'] = $res_gcbuy['qty'];
					$res_gcbuynow['timelef'] = date('m-d-Y',strtotime($res_gcbuy['orderdate']));
					$res_gcbuynow['status'] = 'Ended';
					if($res_gcbuy['invoiceid'] != '0')
					{
					$check_invoice_date=$ilance->db->query("
                       SELECT DATE(createdate) AS date_inv, transactionid  FROM
                       " . DB_PREFIX . "invoices
                       WHERE invoiceid= '".$res_gcbuy['invoiceid']."'                                          
                                               
                       ", 0, null, __FILE__, __LINE__);
					   
					  $date_inv=$ilance->db->fetch_array($check_invoice_date); 
					  
					$res_gcbuynow['invoice'] = '<a href="invoicepayment.php?cmd=view&txn='.$date_inv['transactionid'].'">'.date('m-d-Y ',strtotime($date_inv['date_inv'])).'</a>';
					}
					else
					{
					  $res_gcbuynow['invoice'] = 'Offline Payment';
					}
					$res_gcbuynow['current_bid'] = $res_gcbuy['amount'];					
					$res_gc_buynow[] = $res_gcbuynow;
					}
					
				}
				else
				{				
				$res_gc_buynow['buynow'] = 'Nofound';
				}
//buy now end
///#########################
$pprint_array = array('drop_value','daylist','monthlist','yearlist','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'buy_now.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('service_buying_activity','res_gcacting','res_gcswining','res_gcsnotwining','res_gc_buynow'));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	$pprint_array = array('daylist','monthlist','yearlist','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'buy_active.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('service_buying_activity','res_gcacting','res_gcswining','res_gcsnotwining','res_gc_buynow')); 
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

}

function form_days_drop_down($select_name="searchbuy",$selected_val=0)
 {
 	$days_drop_down_list["0"]="-------All-------";
 	$days_drop_down_list["1"]="Last 24 Hours";
 	$days_drop_down_list["2"]="Last 7 days";
 	$days_drop_down_list["3"]="Last 30 days";
 	$days_drop_down_list["4"]="Last 60 days";
 	$days_drop_down_list["5"]="Last 90 days";
 	$days_drop_down_list["6"]="Last 180 days";
 	$days_drop_down_list["7"]="Last 360 days";
 	$days_drop_down_list["8"]="Last 720 days";
 	$days_drop_down_list["9"]="Last 1080 days";
 	$drop_value='<select name="'.$select_name.'">';
 	foreach ($days_drop_down_list as $day_option_id=>$day_option_value)
 	{
 		if(($selected_val==$day_option_id))
 			$drop_value.='<option value="'.$day_option_id.'"  selected="selected">'.$day_option_value.'</option>';
 		else
 			$drop_value.='<option value="'.$day_option_id.'">'.$day_option_value.'</option>';
 	}
 	$drop_value.='</select>';
 	return $drop_value;
 }
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
