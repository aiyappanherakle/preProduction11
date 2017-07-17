<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1575
|| # -------------------------------------------------------------------- # ||
|| # Customer License # =ryotOqStzEoc1gDhm2kyaoC2VZLPe-ZTcK=-2d-y-SXgzbKia
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
        'wantads',
        'search',
        'feedback',
        'lancebb',
        'buying',
        'selling',
        'accounting',
        'rfp'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'jquery',
	'functions',
	'autocomplete'
);
// #### setup script location ##################################################
define('LOCATION', 'main');
// #### require backend ########################################################
require_once('./functions/config.php');
//for bug 3353
global $block_header, $block_content, $block_content_yellow;

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[main]" => $ilcrumbs["$ilpage[main]"]);
 
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
// #### SEO related ############################################################
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
// #### MEMBERS CONTROL PANEL DASHBOARD ########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'cp')
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'dashboard'
        );
        $show['widescreen'] = true;
        
        if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
        {
                $area_title = $phrase['_member_control_panel'];
                $page_title = SITE_NAME . ' - ' . $phrase['_member_control_panel'];
                
                // construct breadcrumb trail
                $navcrumb = array();
                $navcrumb[""] = $phrase['_my_control_panel'];
        
                $ilance->mycp = construct_object('api.mycp');
                $ilance->subscription = construct_object('api.subscription');
                
                
                unset($sreminder, $breminder, $fb);
                
                $unpaidinvoices = '0';
                $unpaidamount = $ilance->currency->format(0);
                $subscriptiondaysleft = '30';
                $subscriptionexpirydate = 'xxx xxx';
                
				
				 $query="
						SELECT p.project_id, MAX(b.bidamount) AS amount
						FROM " . DB_PREFIX . "projects p
						LEFT JOIN " . DB_PREFIX . "project_bids b ON p.project_id = b.project_id
						WHERE b.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
						AND   p.visible ='1'
						AND	  b.bidstatus = 'placed'
						AND   p.status = 'open'
						GROUP BY p.project_id					
				";
				$sql = $ilance->db->query($query, 0, null, __FILE__, __LINE__);
				$highcount = 0;
				$lowcount = 0;
				$currencyid= 0;
				
				while($ressql = $ilance->db->fetch_array($sql))
				{
				  $highbidderid = $ilance->bid->fetch_highest_bidder($ressql['project_id']);
				  if($highbidderid == $_SESSION['ilancedata']['user']['userid'])
				  {
				  	 $highcount++;
					 $highamt[] = $ressql['amount'];
				  }
				  else
				  {
				   	$lowcount++;
					$lowamt[] = $ressql['amount'];
				  }
				}
				/*echo '1'.$highcount;
				echo '2'.$lowcount;
				echo '3'.array_sum($highamt);
				echo '4'.array_sum($lowamt);*/
				if($highcount > 0 )
				{
				  $curr_win = '<strong><a href = "buy/active"> You are currently winning '.$highcount.' active item(s) (total of current bids '.$ilance->currency->format(array_sum($highamt), $currencyid).')</a></strong>';
				}
				else
				{
				  $curr_win = 'No Records Found';
				}
				
								
				if($lowcount > 0)
				{					
					$curr_out = '<strong><a href = "buy/active"> You have been outbid on '.$lowcount.' active item(s)</a></strong>';
				}
				else
				{
					$curr_out = 'No Records Found';
				}
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects  
                	WHERE  	user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
					AND     visible ='1'
					AND  	project_state = 'product'
					AND    	status = 'open'
					GROUP BY project_id	
				", 0, null, __FILE__, __LINE__);
				
				$counttest = $ilance->db->num_rows($sql2);
				while($ressql2 = $ilance->db->fetch_array($sql2))
				{				  
				  $sqlbid = $ilance->db->query("SELECT MAX(bidamount) AS bidamount FROM ".DB_PREFIX."project_bids
				  								WHERE project_id = '".$ressql2['project_id']."'");
					$restot = $ilance->db->fetch_array($sqlbid);
				  $amttot[] = $restot['bidamount'];
				}
				if($counttest > 0 )
				{
					$curr_sell = '<strong> <a href = "sell/current">You are currently selling '.$counttest.' items (total of items that will sell '.$ilance->currency->format(array_sum($amttot), $currencyid).')</a></strong>';
				}
				else
				{
					$curr_sell = 'No Records Found';
				}

                 $sqlhotlist = $ilance->db->query("SELECT hotlists  FROM ".DB_PREFIX."projects
                                                        WHERE   user_id = '".$_SESSION['ilancedata']['user']['userid']."'
                                                        AND     visible ='1'
                                                        AND     project_state = 'product'
                                                        AND     status = 'open'
                                                        and hotlists = '1' 
                                                        GROUP BY project_id ");
                    
                    $counthotlist = $ilance->db->num_rows($sqlhotlist);
                 $enable_hotlist ='0';
                 if ($counthotlist > 0)
                 {
                    $enable_hotlist ='1';

                 }
				
                //$flashstats = print_flash_stats('referrals', 'stats');

                $enable_batch_bid=$_SESSION['ilancedata']['user']['enable_batch_bid'];
                $is_auto_lower_min_bid=$_SESSION['ilancedata']['user']['is_auto_lower_min_bid'];
                $auto_min_bid_lower_prec=$_SESSION['ilancedata']['user']['auto_min_bid_lower_prec'];
                
                $pprint_array = array('counthotlist','enable_hotlist','curr_win','curr_out','access_bb','enable_batch_bid','is_auto_lower_min_bid','auto_min_bid_lower_prec','curr_sell','subscriptiondaysleft','subscriptionexpirydate','unpaidinvoices','unpaidamount','flashstats','sellingreminders','buyingreminders','datereset','bidsleft','ridlink','referalactivity','mycalendar','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
                
                ($apihook = $ilance->api('main_mycp_end')) ? eval($apihook) : false;
                
                $ilance->template->fetch('main', 'main_mycp.html');
                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
                $ilance->template->pprint('main', $pprint_array);
                exit();
        }
        else
        {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode('mygc'));
				exit();
        }
}
// #### BUYING SERVICES LANDING PAGE ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buying')
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main_buying'
        );
        
        $area_title = $phrase['_buying_products_and_services'];
        $page_title = $phrase['_buy'] . ' ' . $phrase['_services'] . ' | ' . SITE_NAME;
    
        $navcrumb = array();
        $navcrumb[""] = $phrase['_buy'];
        
        if (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product')
        {
                $area_title = $phrase['_buying_products_and_services'];
                $page_title = $phrase['_buy'] . ' ' . $phrase['_products'] . ' | ' . SITE_NAME;
        }
 
        $ilance->categories_parser = construct_object('api.categories_parser');
		$categoryresults=$ilance->categories_parser->html_denomination();
       
        
        $pprint_array = array('inviteduserlist','recursivecategory','categoryresults','title','js','session_project_title','prevnext','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        
        ($apihook = $ilance->api('main_buying')) ? eval($apihook) : false;        
    
        $ilance->template->fetch('main', 'buying.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'wantads');
        $ilance->template->parse_loop('main', 'featuredproductauctions');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
 
// #### CATEGORIES LANDING #####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'categories')
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main_categories'
        );
        
        $area_title = $phrase['_categories'];
        $page_title = $phrase['_categories'] . ' | ' . SITE_NAME;
	
		// #### require mootools ###############################################
		if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
		{
			$jsurl = SUB_FOLDER_ROOT . DIR_FUNCT_NAME;
		}
		else
		{
			$jsurl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME;
		}
		//new change
		/*$headinclude .= '<script language="javascript" type="text/javascript" src="' . $jsurl . '/javascript/functions_mootools.js"></script>';*/
		unset($jsurl);
    
        $navcrumb = array();
        $navcrumb[""] = $phrase['_categories'];
        
        $show['widescreen'] = true;
        
        $popularcategories = $popularlistingcategories = $trendcategories = $latestawardedcategories = $viewingnowcategories = $watchlistcategories = $savedsearchcategories = '';
        
        // #### popular categories #############################################
        $extra = '';
        if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] AND $ilconfig['globalauctionsettings_productauctionsenabled'])
        {
                $extra .= "AND (cattype = 'service' OR cattype = 'product')";        
        }
        else if ($ilconfig['globalauctionsettings_productauctionsenabled'] AND $ilconfig['globalauctionsettings_serviceauctionsenabled'] == false)
        {
                $extra .= "AND cattype = 'product'";        
        }
        else if ($ilconfig['globalauctionsettings_productauctionsenabled'] == false AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
        {
                $extra .= "AND cattype = 'service'";        
        }
        
        $sql = $ilance->db->query("
                SELECT coin_series_unique_no,coin_series_name
                FROM " . DB_PREFIX . "catalog_second_level
                 ORDER BY traffic_count DESC
                LIMIT 10
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $popularcategories .= '<ol style="padding-top:7px">';
                
                $i = 1;
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $style = '';
                        if ($i == 1)
                        {
                                $style = 'style="font-weight:bold"';
                        }
                        
                        //new herakle
						 if ($ilconfig['globalauctionsettings_seourls'])
					     $url = 'Series/'.$res['coin_series_unique_no'].'/'.construct_seo_url_name($res['coin_series_name']).'';
						 else
						 $url = $ilpage['search'] . '?mode=product&series=' . $res['coin_series_unique_no'];
                               // $url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatplain', $res['cid'], $auctionid = 0, $res['title_' . $_SESSION['ilancedata']['user']['slng']], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : $ilpage['search'] . '?mode=product&series=' . $res['coin_series_unique_no'];
                       
                        $popularcategories .= '<li><span class="blue"><a href="' . $url . '" ' . $style . '>' . $res['coin_series_name'] . '</a></span></li>';
                        $i++;
                }
                
                $popularcategories .= '</ol>';
        }
        
        // #### popular categories #############################################
        $sql = $ilance->db->query("
                SELECT coin_series_unique_no,coin_series_name
                FROM " . DB_PREFIX . "catalog_second_level 
				 ORDER BY auction_count DESC
				LIMIT 10
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $popularlistingcategories .= '<ol style="padding-top:7px">';
                
                $i = 1;
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $style = '';
                        if ($i == 1)
                        {
                                $style = 'style="font-weight:bold"';
                        }
						
						//new herakle
						
						 if ($ilconfig['globalauctionsettings_seourls'])
					     $url = 'Series/'.$res['coin_series_unique_no'].'/'.construct_seo_url_name($res['coin_series_name']).'';
						 else
						 $url = $ilpage['search'] . '?mode=product&series=' . $res['coin_series_unique_no'];
                              // $url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatplain', $res['cid'], $auctionid = 0, $res['title_' . $_SESSION['ilancedata']['user']['slng']], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : $ilpage['search'] . '?mode=product&series=' . $res['coin_series_unique_no'];
                         
                        
                        $popularlistingcategories .= '<li><span class="blue"><a href="' . $url . '" ' . $style . '>' . $res['coin_series_name'] . '</a></span></li>';
                        $i++;
                }
                
                $popularlistingcategories .= '</ol>';
        }
        
        /*$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, $propersort = true);
        $categorycacheservice = $ilance->categories->fetch;
        
        $ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, $propersort = true);
        $categorycacheproduct = $ilance->categories->fetch;*/
        
        // #### FEATURE SPOTLIGHT AUCTIONS #####################################
        $show['featuredproductauctions'] = $show['latestproductauctions'] = false;
        $featuredproductauctions = $latestproductauctions = array();
        
        
        if ($ilconfig['globalauctionsettings_productauctionsenabled'])
        {
                $featuredproductauctions = $ilance->auction->fetch_featured_auctions('product', 3, 1, 0, '');
                $latestproductauctions = $ilance->auction->fetch_latest_auctions('product', 5);
        }
        
        $recentlyviewedflash = print_flash_gallery('recentlyviewed');
        
        $pprint_array = array('seoproductcategories','seolistings','seocategories','popularlistingcategories','recentlyviewedflash','trendcategories','popularcategories','title','categoryresults','js','prevnext','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        
        ($apihook = $ilance->api('main_categories')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'main_categories.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'featuredproductauctions');
        $ilance->template->parse_loop('main', 'latestproductauctions');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### CATEGORIES LANDING #####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
        $show['widescreen'] = true;
        
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main_listings'
        );
        
        $area_title = $phrase['_listings'];
        $page_title = $phrase['_listings'] . ', ' . $phrase['_find_what_youre_looking_for'] . ' | ' . SITE_NAME;
    
        $navcrumb = array();
        $navcrumb[""] = $phrase['_listings'];
        
        $pprint_array = array('seoproductcategories','seolistings','seocategories','trendcategories','popularcategories','title','categoryresults','js','prevnext','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        
        ($apihook = $ilance->api('main_listings')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'main_listings.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'featuredproductauctions');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### XML FEED RESOURCES LANDING PAGE ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'resources')
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main'
        );
        
        $area_title = $phrase['_resources_menu'];
        $page_title = SITE_NAME . ' - ' . $phrase['_resources_menu'];
    
        $navcrumb = array();
        $navcrumb[""] = $phrase['_resources'];
        
        $feedoptions = '';
        $rssfeeds = $ilance->db->query("
                SELECT rssid, rssname, rssurl, sort
                FROM " . DB_PREFIX . "rssfeeds
                ORDER BY sort
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($rssfeeds) == 0)
        {
                $feedoptions = '<option value="0">' . $phrase['_no_feeds_currently_exist'] . '</option>';
        }
        else
        {
                while ($feed = $ilance->db->fetch_array($rssfeeds))
                {
                        $feedoptions .= '<option value="' . $feed['rssid'] . '">' . stripslashes($feed['rssname']) . '</option>';
                }
        }
    
        if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $feed = $ilance->db->query_fetch("
                        SELECT rssid, rssname, rssurl, sort
                        FROM " . DB_PREFIX . "rssfeeds
                        WHERE rssid = '" . intval($ilance->GPC['id']) . "'
                ", 0, null, __FILE__, __LINE__);
                
                $rss = $feed['rssurl'];
                $rssname = $feed['rssname'];
                $headline_style = $description_style = $tag = $title = $description = $link = $image = $code2 = '';
                $show_detail = $insideitem = $insideimage = false;
                $max = 10;
                $count = 0;
                
                construct_feed($rss, true, 'news', 'news', $max);
        }
        else 
        {
                $code2 = $phrase['_please_select_a_live_rss_feed'];
        }
        
        $pprint_array = array('rssname','code2','feedoptions','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        
        ($apihook = $ilance->api('main_resources')) ? eval($apihook) : false;
    
        $ilance->template->fetch('main', 'main_resources.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### HIDE ADMINCP NOTICE UNDER BREADCRUMB ###################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'hideacpnag')
{
        if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
        {
                set_cookie('hideacpnag', 'true', true);
                
		$url = $ilpage['main'];
		$phr = $phrase['_home'];
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			$url = urldecode($ilance->GPC['returnurl']);
			$phr = $phrase['_back'];
		}
		
                print_notice($phrase['_admincp_nag_notice_removed'], $phrase['_you_have_removed_the_admincp_clientside_nag_notice'], $url, $phr);
                exit();
        }
}
// #### DYNAMIC TEMPLATE PARSER ################################################
if (isset($ilance->GPC['cmd']) AND !empty($ilance->GPC['cmd']))
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main'
        );
        if($ilance->GPC['cmd'] == '404')
		{
			$frompage = $_SERVER['REQUEST_URI'];
			if(strstr($frompage,'%25'))
				{
					$encodes = array("%25");
					$actual   = array("%");
					$frompage=str_replace($encodes, $actual, $frompage);
				}
			if(strstr($frompage,'%3F'))
			{
				
			$encodes = array("%3F", "%3D", "%26");
			$actual   = array("?", "=", "&");
			$to_page=str_replace($encodes, $actual, $frompage);
				header("Location: $to_page");
				exit;
			}
			$date = date('Y-m-d');
			$user_ip = $_SERVER['REMOTE_ADDR'];  
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$user_id = $_SESSION['ilancedata']['user']['userid'];
			}
			else
			{
				$user_id = '0';
			}		
			$existing = array(
							'{{referrer}}' => $frompage,
							'{{user_id}}' => $user_id,
							'{{date}}' => $date,
                            '{{user_ip}}' =>$user_ip							
						);	
						
						
					  $browse_name= $ilance->common->getBrowser(); 
			
                                                 $ipadd=$_SERVER['REMOTE_ADDR'];
						$date_time = DATETIME24H;
						  	
			$ins = $ilance->db->query("INSERT INTO ".DB_PREFIX."404 (error_from,user_id,referrer,referrer1,browser_name,version,ipaddress,date) 
										VALUES ('".mysql_escape_string($frompage)."','".$user_id."','".$_SESSION['referrer1']."','".$_SESSION['referrer2']."','".$browse_name['name']."','".$browse_name['version']."','".$ipadd."','".$date_time."')");	
										
						$ilance->email = construct_dm_object('email', $ilance);
						
						$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];                
						$query1="SELECT * FROM ".DB_PREFIX."404 WHERE  date(date) = '".DATETODAY."' AND ipaddress = '".$ipadd."'";
                            $emailerror=$ilance->db->query($query1);
                            if($emailerr=$ilance->db->num_rows($emailerror)<=25)
                                {				
            						$ilance->email->slng = fetch_site_slng();
            						$ilance->email->get('error_404_admin');		
            						$ilance->email->set($existing);
            						$ilance->email->send();	
                                }
		}
        // these would be the template files residing in the ./templates/default/ folder (resources.html, terms.html, etc)
		$accepted = print_accepted_array();
		$grading_service=array('PCGS','NGC','ANACS','CAC');
        $pprint_array = array('login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        
        // custom hook in the case you would like to expand your array above via array_merge()
        ($apihook = $ilance->api('main_external_template')) ? eval($apihook) : false;
        
        if (in_array($ilance->GPC['cmd'], $accepted))
        {
		    //1457 on apl19 sen
		    if (in_array($ilance->GPC['cmd'], $grading_service))
            {
		       $myfeature=featured_auction2('2',$ilance->GPC['cmd']);	
		    }
		    else
		    {
		      $myfeature = '<div style="margin-top: 150px;" align="center">NO RESULTS FOUND</div>';
		    }   
		        
		   //  end 1457
		    
		       // changes on Sep27
		        if($ilance->GPC['cmd']=='allitem')
				{
                  $area_title = 'Items Sold by GreatCollections';
                  $page_title = SITE_NAME . ' - All Items are Sold by GreatCollections Coin Auctions';
				}
				
				else if($ilance->GPC['cmd']=='why-greatcollections')
				{
					$area_title = $phrase['_why_gc_bread_crumb'];
					$page_title = $phrase['_why_gc_page_title'];
					
					//EDITED BY TAMIL FOR BUG NO 2005 ON 1/11/2012 * START
					
					$total_why_gc = $ilance->db->query("SELECT COUNT( * ) as total_ended
                                               FROM  " . DB_PREFIX . "projects 
                                               WHERE  status =  'expired'
                                               OR status =  'closed'");
											   
					$ended_why_gc = $ilance->db->fetch_array($total_why_gc);
					
					$why_gc_count = number_format($ended_why_gc['total_ended']);
					
					//EDITED BY TAMIL FOR BUG NO 2005 ON 1/11/2012 * END
				  
				}
				
				//for bug 3353
				else if($ilance->GPC['cmd']=='greatcollections-vs-ebay')
				{
				    
					$area_title = $phrase['_gc_vs_ebay_bread_crumb'];
					$page_title = $phrase['_gc_vs_ebay_page_title'];
					
					$total_why_gc = $ilance->db->query("SELECT COUNT( * ) as total_ended
                                               FROM  " . DB_PREFIX . "projects 
                                               WHERE  status =  'expired'
                                               OR status =  'closed'");
											   
					$ended_why_gc = $ilance->db->fetch_array($total_why_gc);
					
					$why_gc_count = number_format($ended_why_gc['total_ended']);
				  
				}
				//for bug 3353 end
				
				//for bug 5097 starts
				else if($ilance->GPC['cmd']=='larryking')
				{
				    
					$area_title = $phrase['_larryking_bread_crumb'];
					$page_title = $phrase['_larryking_page_title'];
					
								  
				}
				//for bug 5097 end
				
				else
				{
				  $area_title = ucfirst($ilance->GPC['cmd']);
                  $page_title = SITE_NAME . ' - ' . ucfirst($ilance->GPC['cmd']);
				}  
                // murugan changes on Oct 7
				if($ilance->GPC['cmd'] == 'sell')
				{
					$area_title = 'Selling Coins';				
               		$page_title = SITE_NAME . ' - Selling Coins through GreatCollections Coin Auctions';
				}
				
				/* TAMIL for Bug 2503 on 26Mar13 * Starts */
				if($ilance->GPC['cmd'] == 'wantlist')
				{
					$area_title = 'Buying Coins';				
               		$page_title = SITE_NAME . ' - Buying Coins through GreatCollections Coin Auctions';
				}
				/* TAMIL for Bug 2503 on 26Mar13 * Ends */
				
				/* for Bug 4886 */
				if($ilance->GPC['cmd'] == 'selling-instructions')
				{
					$area_title = 'Consigning Instructions';
				}	
				
                $navcrumb = array();
                $navcrumb[""] = $area_title;
				
				// THE FOLLOWING ARRAY ( L NO 1679) EDITED 'why_gc_count' BY TAMIL FOR BUG NO 2005 ON 1/11/2012 * END
				
                $pprint_array = array('login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','myfeature','why_gc_count');
                $ilance->template->fetch_parsed_template('main', 'main_' . mb_strtolower($ilance->GPC['cmd']), $_SESSION['ilancedata']['user']['styleid']);
                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', 'image');
                $ilance->template->pprint('main', $pprint_array);
                exit();
        }
        else
        {
                print_notice($phrase['_invalid_page_request'], $phrase['_were_sorry_the_page_you_requested_could_not_be_found'], $ilpage['main'], $phrase['_main_menu']);
                exit();
        }
}
// #### MAIN MENU LANDING PAGE #################################################
else
{
        // #### define top header nav ##########################################
        $topnavlink = array(
                'main'
        );
        
        $show['widescreen'] = false;
		$show['hide'] = 'home';
        
        $area_title = $phrase['_main_menu'];
        $page_title = $ilconfig['template_metatitle'] . ' | ' . SITE_NAME;
        
        $navcrumb = array();
        $navcrumb[""] = $phrase['_marketplace'];
        
	 
		$ilance->categories_parser = construct_object('api.categories_parser');
		$denominationslist=$ilance->categories_parser->leftnav_denomination();
		$product_cat=$denominationslist;
		
	    //buy confidence
		
		//new change order by views change to bids
		//nov21 for bug id 995
		
	    $res20=$ilance->db->query("select c.cid,c.project_id,c.project_title,c.currentprice,c.bids
	   from 	   
		" . DB_PREFIX . "projects c
		where 
		
		c.project_state = 'product'
		AND c.status = 'open'
		AND c.visible = '1' and
		
		c.filtered_auctiontype='regular'
		group by c.project_id order by c.bids DESC limit 7
		
		");
		if ($ilance->db->num_rows($res20) > 0)
		{  	
			while($row_pre = $ilance->db->fetch_array($res20))
			{
				//sekar on bug id 995 Center Block on Home Page... 
                $bids = '<font color="#5B6C7E">'.$row_pre['bids'].'</font>';
				$bodamot = '$'.$row_pre['currentprice'];
				 $title = $row_pre['project_title'].' '. $bodamot .' <font color="#5B6C7E">('.$bids.' '. 'bids'.')</font>';
               
				
				if ($ilconfig['globalauctionsettings_seourls'])
				$row_pre['titi'] = '<b><a href="Coin/'.$row_pre['project_id'].'/'.construct_seo_url_name($row_pre['project_title']).'">'.$title.'</a></b>';
				else
				$row_pre['titi'] = '<b><a href="merch.php?id='.$row_pre['project_id'].'">'.$title.'</a></b>';
				$info_val[] = $row_pre;
			}
		}
		else
		{
		$show['no'] = 'total';
		}
	    //nov21 for bug id 995 finished
	    //new acution 
		// murugan changes here for random listing in july 6
		$sql="select c.date_starts, UNIX_TIMESTAMP(c.date_end) - UNIX_TIMESTAMP('".DATETIME24H."') AS mytime, UNIX_TIMESTAMP(c.date_starts) - UNIX_TIMESTAMP('".DATETIME24H."') AS starttime,c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice,a.filename as filehash,dd.live_date
				   from 					
					" . DB_PREFIX . "projects c
					left join " . DB_PREFIX . "dailydeal dd on dd.project_id=c.project_id
					left join " . DB_PREFIX . "attachment a on a.project_id=c.project_id and a.visible='1' and a.attachtype='itemphoto'
					where 
					c.featured = '0' 
					AND c.project_state = 'product'
					AND c.bids != 0
					AND c.status = 'open'
					AND c.visible = '1'
                    AND a.attachtype IS NOT NULL
					group by c.project_id order by RAND() limit 4";
	    $select_featured= $ilance->db->query($sql);
		if ($ilance->db->num_rows($select_featured) > 0)
		{   
		    $myfeat = '';
			$c = 0;
			while($row_pre_fea = $ilance->db->fetch_array($select_featured))
			{
			
			             
					if(!is_null($row_pre_fea['filehash']) and strlen($row_pre_fea['filehash'])>0)
					{
						
                        $uselistra = HTTPS_SERVER.'image/140/170/' . $row_pre_fea['filehash'] ; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"> <img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
						else
						$htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
						
						
					}else
					{
					    $uselistra =  $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
						else
					    $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
					}
				
				if($c > 2)
				$sep = '';
				else
				$sep = '<div id="seperator"></div>';
				
				 //$yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], '', 'right', $timeintext = 0, $showlivebids = 0, 0);
				 $yutq =auction_time_left_new($row_pre_fea,false); 
				 //###########sekar on sep23
				$myfeat.= '<div id="abox01">
						
						<div id="fetit">';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeat.= '<h4 style="font-weight:bold; font-size: 12px; color: #303030; text-decoration: none; "><a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">';
						else
						$myfeat.= '<h4 style="font-weight:bold; font-size: 12px; color: #303030; text-decoration: none; "><a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeat.= $row_pre_fea['project_title'].'</a></h4></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					    <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:
						<br>
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
				
				if($row_pre_fea['filtered_auctiontype'] == 'fixed')
				{
				   $image = 'buy_now_but.jpg';
				}
				else
				{
				   $image = 'bid_now_butt.jpg';
				}
				if ($ilconfig['globalauctionsettings_seourls'])		
				$myfeat.='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="' . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
				else
				$myfeat.='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'. $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
				
		 
					if(!is_null($row_pre_fea['live_date']))
					{
					 
					  $daily = ($row_pre_fea['live_date'] == DATETODAY) ? 'Ends' : '24-Hour Deal Ends';
					  //$daily = '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
				$myfeat.='
						</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>
                        '.$sep.'';
			$c++;
			}
		}
		
		// Featured Auctions 
		// New Changes On 12Jun 01 
		
		 $myfeature=featured_auction3('1','');
		
		
		//deal daily
				$sql="SELECT p.currentprice,p.project_id,p.project_title,p.buynow_price,d.deal_name,a.filename as filehash
                FROM " . DB_PREFIX . "dailydeal d
				left join " . DB_PREFIX . "projects p on p.project_id = d.project_id
				left join " . DB_PREFIX . "attachment a on a.project_id = d.project_id and  a.visible='1' AND a.attachtype='itemphoto' 
				WHERE  	d.live_date = '".DATETODAY."' 
				AND p.visible = '1'
				AND p.status = 'open'
				group by d.project_id
               
                ";
		 $select_daily = $ilance->db->query($sql);   $mydaily = '';
		if ($ilance->db->num_rows($select_daily) > 0)
		{   
		 
			while($row_pre_dis = $ilance->db->fetch_array($select_daily))
			{
			 
					if(!is_null($row_pre_dis['filehash']))
					{
						$uselistr = HTTPS_SERVER.'image/105/170/' . $row_pre_dis['filehash'] ; 
						if ($ilconfig['globalauctionsettings_seourls'])	
						$htm ='<a href="Coin/'.$row_pre_dis['project_id'].'/'.construct_seo_url_name($row_pre_dis['project_title']).'"><img src="'.$uselistr.'" style="padding: 10px;" alt="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
						else
						$htm ='<a href="merch.php?id='.$row_pre_dis['project_id'].'"><img src="'.$uselistr.'" style="padding: 10px;" alt="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';						
					}else
					{
					    $uselistr =  $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$htm ='<a href="Coin/'.$row_pre_dis['project_id'].'/'.construct_seo_url_name($row_pre_dis['project_title']).'"><img src="images/gc/nophoto.gif" style="padding: 10px;" alt="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
						else
					    $htm ='<a href="merch.php?id='.$row_pre_dis['project_id'].'"><img src="images/gc/nophoto.gif" style="padding: 10px;" alt="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
					}
				  //
				  if ($ilconfig['globalauctionsettings_seourls'])	
						$lin ='<a href="Coin/'.$row_pre_dis['project_id'].'/'.construct_seo_url_name($row_pre_dis['project_title']).'">'.$row_pre_dis['project_title'].'</a>';
						else
					    $lin ='<a href="merch.php?id='.$row_pre_dis['project_id'].'">'.$row_pre_dis['project_title'].'</a>';
				  //             
							   
			$mydaily.= '<div class="panel"> 
				<div style="padding-left:0px; padding-top:5px; margin-left:-13px;" ><div align="center" style="width:230px; padding-left:20px; padding-right:20px;">'.$lin.'</div></div>
				<div style="padding-left:20px; padding-top:5px;" ><div id="imgs" align="center">'.$htm.'</div></div>
				<div style="border: 2px solid rgb(74, 80, 102); width: 195px; height: 31px;margin-left: 20px;"><div style="float: left; padding-left: 5px; padding-top: 5px; width: 91px;"id="open">$ '.$row_pre_dis['buynow_price'].'</div><div style="float: left; padding-left: 0px;">';
				if ($ilconfig['globalauctionsettings_seourls'])	
				$mydaily.= '<a href="Coin/'.$row_pre_dis['project_id'].'/'.construct_seo_url_name($row_pre_dis['project_title']).'"><img src="'. $ilconfig['template_imagesfolder'] . 'buy_now_but.jpg" /></a>';
				else
				$mydaily.= '<a href="merch.php?id='.$row_pre_dis['project_id'].'"><img src="'. $ilconfig['template_imagesfolder'] . 'buy_now_but.jpg" /></a>';
				$mydaily.= '</div>
				</div>
			
			</div>	';
			
			}
		}
		else
		{
		  $sql="SELECT p.currentprice,p.project_id,p.project_title,p.buynow_price,d.deal_name,a.filename as filehash
                FROM " . DB_PREFIX . "dailydeal d
				left join " . DB_PREFIX . "projects p on p.project_id = d.project_id
				left join " . DB_PREFIX . "attachment a on a.project_id = d.project_id and  a.visible='1' AND a.attachtype='itemphoto' 
                WHERE  	d.live_date BETWEEN '".THREEDAYSAGO."' AND '".DATEYESTERDAY."'
				and p.project_id = d.project_id				
				group by d.project_id               
                ";
		   $select_daily = $ilance->db->query($sql);
		if ($ilance->db->num_rows($select_daily) > 0)
		{   
		    $mydaily = '';
			while($row_pre_dis = $ilance->db->fetch_array($select_daily))
			{
			
			 
					if(!is_null($row_pre_dis['filehash']))
					{
						
						$uselistr = HTTPS_SERVER.'image/105/170/' . $row_pre_dis['filehash'] ; 
                        $htm ='<img src="'.$uselistr.'" style="padding: 10px;" alt="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'">';
					}else{
					    $uselistr = $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						
					    $htm ='<img src="images/gc/nophoto.gif" style="padding: 10px;" alt="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_dis['project_title'].' '.$phrase['_at_gc_image_tag'].'">';
					}
				
				
						
					//
				  if ($ilconfig['globalauctionsettings_seourls'])	
						$lin ='<a href="Coin/'.$row_pre_dis['project_id'].'/'.construct_seo_url_name($row_pre_dis['project_title']).'">'.$row_pre_dis['project_title'].'</a>';
						else
					    $lin ='<a href="merch.php?id='.$row_pre_dis['project_id'].'">'.$row_pre_dis['project_title'].'</a>';
				  // 
						
				$mydaily.= '<div class="panel">
				<div style="padding-left:0px; padding-top:5px; margin-left:-13px;" ><div align="center" style="width:230px; padding-left:20px; padding-right:20px;">'.$lin.'</div></div>
				<div style="padding-left:20px; padding-top:5px;" ><div id="imgs" align="center">'.$htm.'</div></div>
				<div style="border: 2px solid rgb(74, 80, 102); width: 195px; height: 33px;margin-left: 20px;"><div style="float: left; padding-left: 5px; padding-top: 5px; width: 91px;"id="open">$ '.$row_pre_dis['buynow_price'].'</div><div style="float: left; padding-left: 0px;"><img src="'. $ilconfig['template_imagesfolder'] . 'sold_butt.jpg" alt="Sold by GreatCollections Coin Auctions" title="Sold by GreatCollections Coin Auctions" width="99" height="33" /></div>
				</div>
			
			</div>	';
			
			}
		}
		else
		{
		$mydaily.= '<div class="panel">
			
				<div align="center" style="margin-top: 85px;">
				All 24-Hour Deals<br>Are Sold Out
				</div>
			
			</div>	';
		}
	
		}
		
		
				//banner
	$sql_banner = $ilance->db->query("SELECT * FROM banner_order o left join  " . DB_PREFIX . "banner b on b.id=o.banner_id ORDER BY o.id ASC
									", 0, null, __FILE__, __LINE__);
	$v=1;
	while($fetch_banner=$ilance->db->fetch_array($sql_banner))
	{
	
	$bann='';
	
	$image = ($fetch_banner['image_link'])?'<a href="'.$fetch_banner['image_link'].'"><div class="image"><img alt="'.$fetch_banner['inner_text'].'" title="'.$fetch_banner['inner_text'].'" src="banner/images/'.$fetch_banner['filename'].'" /></a>':'<div class="image"><img alt="'.$fetch_banner['inner_text'].'" title="'.$fetch_banner['inner_text'].'" src="banner/images/'.$fetch_banner['filename'].'" />';
	
	$bann.=($fetch_banner['inner_text_link'])?'<a href="'.$fetch_banner['inner_text_link'].'">'.$fetch_banner['inner_text'].'</a>':$fetch_banner['inner_text'];
	
	if($fetch_banner['inner_text1'])
	{
	$bann.=($fetch_banner['inner_text_link1'])?'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$fetch_banner['inner_text_link1'].'">'.$fetch_banner['inner_text1'].'</a>':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fetch_banner['inner_text1'];
	}
	if($fetch_banner['inner_text2'])
	{
	$bann.=($fetch_banner['inner_text_link2'])?'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$fetch_banner['inner_text_link2'].'">'.$fetch_banner['inner_text2'].'</a>':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fetch_banner['inner_text2'];
	}
	
	$banner['list']=' <li class="jcarousel-item jcarousel-item-horizontal jcarousel-item-4 jcarousel-item-4-horizontal" jcarouselindex="4">'.$image.'<div align="center" class="new_inner"><h4 style="font-size: 14px; color: #000000; margin:0px;">'.$bann.'</h4></div></div></li>';
	
	$banner_loop[]=$banner;
	
	$dot_list['dots']='<a href="#" class=" ">'.$v.'</a>';
	$v++;
	$dots_loop[]=$dot_list;
	
	
	
	
	}
			
			
				
        $pprint_array = array('myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','jobcount','itemcount','recentlyviewedflash','tagcloud','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
    
        $ilance->template->fetch('main', 'main.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val');
		$ilance->template->parse_loop('main', 'info_feat');
		$ilance->template->parse_loop('main', 'banner_loop');
		$ilance->template->parse_loop('main', 'dots_loop');
        ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

function auction_time_left_new($result,$showfullformat)
{
global $ilance, $myapi, $ilconfig, $ilconfig, $phrase;

                                $dif = $result['mytime'];
                                $ndays = floor($dif / 86400);
                                $dif -= $ndays * 86400;
                                $nhours = floor($dif / 3600);
                                $dif -= $nhours * 3600;
                                $nminutes = floor($dif / 60);
                                $dif -= $nminutes * 60;
                                $nseconds = $dif;
                                $sign = '+';
                                if ($result['mytime'] < 0)
                                {
                                        $result['mytime'] = - $result['mytime'];
                                        $sign = '-';
                                }
                                if ($sign == '-')
                                {
                                        // expired
                                        $timeleft = $phrase['_ended'];
                                        $expiredauction = 1;
                                }
                                else
                                {
                                        if ($ndays != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $ndays    . $phrase['_d_shortform'] . ', ';	
                                                        $timeleft .= $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft = $ndays . $phrase['_d_shortform'] . ', ' . $nhours . $phrase['_h_shortform'];
                                                }
                                        }
                                        else if ($nhours != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];        
                                                }
                                                else
                                                {
                                                        $timeleft = $nhours . $phrase['_h_shortform'] . ', ' . $nminutes . $phrase['_m_shortform'];
                                                }
                                        }
                                        else
                                        {
                                                if ($nminutes != '0')
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nminutes . $phrase['_m_shortform'] . ', ' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                                else
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                        }
                                }
                          return $timeleft;
}

function featured_auction3($row=0 , $grading_service='')
{
 	global $ilance,$show,$ilconfig,$phrase;
	 
	$column=$row*4;
	$count_gal=1;
	$myfeature= '<table cellpadding="9" border="0" ><tr>';       
					
	if($grading_service)
	 {
	   $select_featurednew=	$ilance->db->query("SELECT  a.filehash,c.Grading_Service, c.project_id, p.currentprice, p.featured, p.project_id, p.project_title,  p.status ,p.date_starts, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('".DATETIME24H."') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('".DATETIME24H."') AS starttime
			                            FROM " . DB_PREFIX . "coins as c
						    LEFT JOIN " . DB_PREFIX . "projects as p on c.project_id=p.project_id
						    LEFT JOIN " . DB_PREFIX . "attachment as a on (a.project_id=c.project_id AND a.visible = '1'
							                                                         AND a.attachtype = 'itemphoto')
							left join " . DB_PREFIX . "dailydeal dd on p.project_id = dd.project_id
						    WHERE c.Grading_Service = '" . $grading_service . "' 
							AND p.featured='1'
			                AND p.status='open'
							GROUP BY c.project_id
							ORDER BY rand() LIMIT ".$column."
			   	                      ");
	 }
	 else
	 {
	 $sql="SELECT  a.filename as filehash, c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice,c.date_starts, UNIX_TIMESTAMP(c.date_end) - UNIX_TIMESTAMP('".DATETIME24H."') AS mytime, UNIX_TIMESTAMP(c.date_starts) - UNIX_TIMESTAMP('".DATETIME24H."') AS starttime,dd.live_date
			                FROM " . DB_PREFIX . "projects as c
						    LEFT JOIN " . DB_PREFIX . "attachment as a on (a.project_id=c.project_id AND a.visible = '1'
							                                          AND a.attachtype = 'itemphoto')
							left join " . DB_PREFIX . "dailydeal dd on c.project_id = dd.project_id
						    WHERE c.featured = '1'  
						    AND c.project_state = 'product'
						    AND c.status = 'open'
						    GROUP BY c.project_id 
						    ORDER BY RAND()  LIMIT ".$column."
						    ";
	 $select_featurednew=	$ilance->db->query($sql);
	 }
	
			
	    if($ilance->db->num_rows($select_featurednew) >0)
		{
		$show['result']=true;
			
			while($row_pre_fea = $ilance->db->fetch_array($select_featurednew))
			{
				
			                
					if(strlen($row_pre_fea['filehash'])>0)
					{
					   
						 $uselistra =  'image/140/170/' . $row_pre_fea['filehash'];
						//echo $uselistra;
						if ($ilconfig['globalauctionsettings_seourls'])	
						     $htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
						else
						     $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';						
					}
					else
					{
						 
					    $uselistra =  $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])	
						     $htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.HTTP_SERVER.'images/gc/nophoto.gif" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" style="padding-top: 6px;"></a>';
						else
					        $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.HTTP_SERVER.'images/gc/nophoto.gif" alt="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].' '.$phrase['_at_gc_image_tag'].'" style="padding-top: 6px;"></a>';
					}
				
				
				        $myfeature.= '<td>';	
				
				if($count_gal%4==0)
				  $sep = '';
				else
				  $sep = '<td id="seperator"></td>';
				
				// $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], '', 'right', $timeintext = 0, $showlivebids = 0, 0);
				 $yutq =auction_time_left_new($row_pre_fea,false);
				 $myfeature.= '<div id="abox01">
						
						<div id="fetit">';
						$myfeature.=$ilconfig['globalauctionsettings_seourls']?
						'<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">':
						'<a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeature.=$row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					        <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
						
						if($row_pre_fea['filtered_auctiontype'] == 'fixed')
						{
				  			 $image = 'buy_now_but.jpg';
						}
						else
						{
							$image = 'bid_now_butt.jpg';
				   			
						}
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeature.= '<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'. $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
						else
						$myfeature.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="' . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
						
 //$sql_idly = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "dailydeal WHERE project_id ='".$row_pre_fea['project_id']."'");
				   
					if(!is_null($row_pre_fea['live_date']))
					{
					  $daily = '24-Hour Deal Ends';
					}
					else
					{					
					  $daily = 'Ends';
					}
						$myfeature.= '</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>';
						
				 if($count_gal%4==0)
				 {
				  if($count_gal==$column)
				  $myfeature.= $sep.'</tr><tr>';
				  else
                                  $myfeature.= $sep.'</tr><tr><td colspan="7"><hr></td></tr><tr>';
			         }
				 else
				 {
				  $myfeature.= $sep;	
				 }
				$count_gal++;
			}
			
		}	
		     
			$myfeature.='</table>';
       
	   return  $myfeature;
	 
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>
