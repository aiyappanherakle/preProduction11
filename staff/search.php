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
        'wantads',
        'subscription',
        'preferences',
        'javascript',
		'administration'
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
	'jquery_custom_ui',
	'modal',
	'yahoo-jar',
	'flashfix'
);

// #### define top header nav ##################
$topnavlink = array(
        'main_listings'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[search]" => $ilcrumbs["$ilpage[search]"]);
//$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

$searchurl = $_SERVER['QUERY_STRING'];
//new changes
// murugan changes on mar 26
$ilance->bbcode = construct_object('api.bbcode');

$ilance->GPC['description'] = !empty($ilance->GPC['description']) ? $ilance->GPC['description'] : '';
$wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'saveurl')
{
				
				$message = $ilance->GPC['description'];
				$message = $ilance->bbcode->prepare_special_codes('PHP', $message);
				$message = $ilance->bbcode->prepare_special_codes('HTML', $message);
				$message = $ilance->bbcode->prepare_special_codes('CODE', $message);
				$message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
				$message = html_entity_decode($message);

	$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "staff_search
                                (id, title, url,description)
                                VALUES (
                                NULL,
                                '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['url']) . "',
								'" . $ilance->db->escape_string($message) . "'                           
                                )
                        ");
	print_action_success('The search url was created successfully ', $ilpage['staffsettings'] . '?cmd=searchpage');
				exit();
}
// #### SEARCH HELP : FULLTEXT BOOLEAN INFO ####################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'help')
{
        $pprint_array = array('wysiwyg_area','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_displayfeatured','cb_hidelisted','cb_proxybit','cb_hideverbose','serviceavailable','serviceselected','productavailable','productselected','expertavailable','expertselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','wantads_category_selection','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','stores_category_selection','product_category_selection','service_category_selection','search_serviceauctions_img','search_serviceauctions_collapse','search_productauctions_img','search_productauctions_collapse','search_experts_collapse','search_experts_img','pfp_category_left','rfp_category_left','input_style','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','input_style','remote_addr','rid','default_exchange_rate','login_include','login_include_admin','ilanceversion','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        $ilance->template->fetch('main', 'search_help.html',2);
        $ilance->template->parse_if_blocks('main');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

// #### TIMER TO PREVENT SEARCH FLOODING #######################################
$show['searcherror'] = $searchwaitleft = 0;
$searchwait = $ilconfig['searchflooddelay'];

if (!empty($ilance->GPC['mode']))
{
        
		if ($ilconfig['searchfloodprotect'] AND isset($ilance->GPC['q']) AND $ilance->GPC['q'] != '')
        {
                if (empty($_SESSION['ilancedata']['user']['searchexpiry']))
                {
                        // start timer
                        $_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
                }
                else
                {
                        if (($timeexpired = TIMESTAMPNOW - $_SESSION['ilancedata']['user']['searchexpiry']) < $searchwait AND $searchwait != 0)
                        {
                                $show['searcherror'] = 1;
                                $searchwaitleft = ($searchwait - $timeexpired);
                        }
                        else
                        {
                                // restart timer
                                $_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
                        }
                }
        }
        
        ($apihook = $ilance->api('search_mode_condition_end')) ? eval($apihook) : false;
}

// #### SEARCH ENGINE HANDLER ##################################################
$sqlquery = array();

// construct our common classes
$ilance->auction = construct_object('api.auction');
$ilance->categories_skills = construct_object('api.categories_skills');
$ilance->categories_pulldown = construct_object('api.categories_pulldown');
$ilance->distance = construct_object('api.distance');
$ilance->subscription = construct_object('api.subscription');
$ilance->feedback = construct_object('api.feedback');
$ilance->feedback_rating = construct_object('api.feedback_rating');  
$ilance->auction_post = construct_object('api.auction_post');                      

// #### build our service category cache #######################################
$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
$ilance->categories->catservicefetch = $ilance->categories->fetch;

// #### selected category id ###################################################
$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;

// #### print multiple selection category menu #################################
$service_category_selection = $product_category_selection = $provider_category_selection = $search_category_pulldown = '';
if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
{
        $service_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng']);
        $provider_category_selection = $ilance->categories_pulldown->print_cat_pulldown(0, 'service', 'levelmultisearch', 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '350px', $uid = 0, $forcenocount = 0, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $ilance->categories->cats, $onclickjs = true);
	if (isset($ilance->GPC['mode']) AND ($ilance->GPC['mode'] == 'service' OR $ilance->GPC['mode'] == 'experts'))
	{
		$search_category_pulldown = $service_category_selection;
	}
}

$profilebidfilters = '<div id="profile_filters_text">' . $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service') . '</div>';

// build our product category cache
$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
$ilance->categories->catproductfetch = $ilance->categories->fetch;

if ($ilconfig['globalauctionsettings_productauctionsenabled'])
{
	// #### require shipping backend #######################################
	require_once(DIR_CORE . 'functions_shipping.php');
	//suku
	// murugan changes on feb 18 for catalog order search
//	$product_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['ilancedata']['user']['slng']);
$drop_sql=$ilance->db->query("SELECT coin_series_unique_no,coin_series_name FROM ilance_catalog_second_level order by coin_series_sort");
$product_category_selection.='<select name="series"><option value="">All Categories</option><option value="">------------------------------------------------</option>';
while($drop_line=$ilance->db->fetch_array($drop_sql))
{
if(isset($ilance->GPC['series']) and $ilance->GPC['series']>0 and $drop_line['coin_series_unique_no']==$ilance->GPC['series'])
$product_category_selection.= '<option value="'.$drop_line['coin_series_unique_no'].'" selected="selected">'.$drop_line['coin_series_name'].'</option>';
else
$product_category_selection.= '<option value="'.$drop_line['coin_series_unique_no'].'">'.$drop_line['coin_series_name'].'</option>';
}
$product_category_selection.='</select>';
	if (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product')
	{
		$search_category_pulldown = $product_category_selection;
	}
}

if (!empty($ilance->GPC['mode']) AND $show['searcherror'] == 0)
{
        // #### PREPARE DEFAULT URLS ###########################################
        $scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	
	// remove unwanted url vars ############################################
	$searchid = isset($ilance->GPC['searchid']) ? intval($ilance->GPC['searchid']) : '';
	$list = isset($ilance->GPC['list']) ? $ilance->GPC['list'] : '';
	
	$pageurl = rewrite_url(PAGEURL, 'searchid=' . $searchid);
	$pageurl = rewrite_url($pageurl, 'list=' . $list);
	
        $php_self = ($ilconfig['globalauctionsettings_seourls']) ? $pageurl : $scriptpage;
        $php_self_urlencoded = ($ilconfig['globalauctionsettings_seourls']) ? urlencode($pageurl) : urlencode($php_self);
        
        define('PHP_SELF', $php_self);
        unset($pageurl);
	
//        $show['widescreen'] = true;
        
        switch ($ilance->GPC['mode'])
        {
                // #### SEARCHING FOR PROJECT ID ###############################
                case 'rfpid':
                {
                        if (empty($ilance->GPC['q']) OR !isset($ilance->GPC['q']))
                        {
                                header("Location: " . $ilpage['search'] . "?tab=0");
                                exit();
                        }
                        
                        header("Location: " . $ilpage['rfp'] . "?id=" . intval($ilance->GPC['q']) . "");
                        exit();
                }
                
                // #### SEARCHING FOR ITEM ID ##################################
                case 'itemid':
                {
                        if (empty($ilance->GPC['q']) OR !isset($ilance->GPC['q']))
                        {
                                header("Location: " . $ilpage['search'] . "?tab=1");
                                exit();
                        }
                        
                        header("Location: " . $ilpage['merch'] . "?id=" . intval($ilance->GPC['q']) . "");
                        exit();
                }
                
                // #### SEARCHING SERVICE AND PRODUCT AUCTIONS #################
                case 'service':
                case 'product':
                {
                        $ilance->bid = construct_object('api.bid');
                        $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                        $ilance->bid_proxy = construct_object('api.bid_proxy');
                        $ilance->auction = construct_object('api.auction');
                        $ilance->auction_post = construct_object('api.auction_post');
                        
                        $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				? 1
				: intval($ilance->GPC['page']);
                        
                        // search mode logic
                        $show['mode_product'] = $show['mode_providers'] = $show['mode_service'] = false;
                        
                        if ($ilance->GPC['mode'] == 'service')
                        {
                                $navcrumb[""] = $phrase['_services'];                                
                                $show['mode_service'] = true;
                                $project_state = 'service';
                                $sqlquery['projectstate'] = "AND (p.project_state = 'service')";
                        }
                        else if ($ilance->GPC['mode'] == 'product')
                        {
                                $navcrumb[""] = $phrase['_products'];                                
                                $show['mode_product'] = true;
                                $project_state = 'product';
                                $sqlquery['projectstate'] = "AND (p.project_state = 'product')";
                        }
			
			// #### ensure auctions shown in result have not yet expired..
                        $sqlquery['timestamp'] = "AND (UNIX_TIMESTAMP(p.date_end) > UNIX_TIMESTAMP('" . DATETIME24H . "'))";
                        $sqlquery['projectstatus'] = "AND (p.status = 'open')";
                        
                        // here we should take the user to "all category listings" if he chose a "mode" but didn't select a category and keyword.
                        if (!empty($ilance->GPC['mode']) AND empty($ilance->GPC['searchuser']) AND empty($ilance->GPC['state']) AND empty($ilance->GPC['country']) AND empty($ilance->GPC['sort']) AND (empty($ilance->GPC['q']) AND (empty($ilance->GPC['series']) OR !empty($ilance->GPC['series']) AND $ilance->GPC['series'] == 0)))
                        {
                                switch ($ilance->GPC['mode'])
                                {
                                        case 'service':
                                        {
                                                $reurl = '';
                                                $reurl = ($ilconfig['globalauctionsettings_seourls'])
							? HTTP_SERVER . print_seo_url($ilconfig['servicecatmapidentifier'])
							: HTTP_SERVER . $ilpage['rfp'] . '?cmd=listings';
                                        }
					break;
                                        case 'product':
                                        {
                                                $reurl = '';
                                                $reurl = ($ilconfig['globalauctionsettings_seourls'])
							? HTTP_SERVER . print_seo_url($ilconfig['productcatmapidentifier'])
							: HTTP_SERVER . $ilpage['merch'] . '?cmd=listings';
                                        }
					break;
                                }
                                
                                header("Location: " . $reurl . "");
                                exit();                
                        }
                        
                        // init verbose search engine text, favorite searches text, etc
                        $text = $showtext = $favtext = '';
                        
			// #### keywords entered by user #######################
                        $keyword_text = (!empty($ilance->GPC['q'])) ? $ilance->common->xss_clean($ilance->GPC['q']) : '';
                        
                        // #### popular keyword search handler #################
                        if (!empty($keyword_text))
                        {
                                // build's a usable database of recent search keywords
                                handle_search_keywords($keyword_text, $ilance->GPC['mode']);
                        }
                        
                        // #### BEGIN SEARCH SQL QUERY #########################
                        $sqlquery['groupby'] = "GROUP BY p.project_id";
                        $sqlquery['orderby'] = "ORDER BY p.date_end ASC";
                        $sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * fetch_perpage()) . ',' . fetch_perpage();
                        
			// #### accepted display sorting orders ################
                        $acceptedsort = array('01','02','11','12','21','22','31','41','42','51','52','61','62','71','72','81','82','91','92','101','102','111','112');
			
                        // #### build our core sql search pattern fields and store them in an array for later usage
                        $sqlquery['fields'] = "p.featured, p.reserve, p.bold, p.highlite, p.buynow_qty, p.buynow, p.buynow_price, p.currentprice, p.project_id, p.cid, p.description, p.date_starts, p.date_added, p.date_end, p.user_id, p.visible, p.views, p.project_title, p.additional_info, p.bids, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.startprice, p.retailprice, p.filtered_auctiontype, p.filtered_budgetid, p.donation, p.charityid, p.donationpercentage, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.currencyid, p.countryid AS country, p.city, p.state, p.zipcode AS zip_code, u.rating, u.score";
                        $sqlquery['from'] = "FROM " . DB_PREFIX . "projects AS p";
			
                        $sqlquery['leftjoin'] = "LEFT JOIN " . DB_PREFIX . "users u ON p.user_id = u.user_id " . ((isset($ilance->GPC['images']) AND $ilance->GPC['images'])
				? "LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id"
				: "");
			
                        $sqlquery['leftjoin_attachment'] = ((isset($ilance->GPC['images']) AND $ilance->GPC['images'])
				? "LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id"
				: "");
			
			// #### left join for shipping logic ###################
			if ($show['mode_product'])
			{
				$sqlquery['fields'] .= ", s.ship_method, s.ship_handlingtime, s.ship_handlingfee, ";
				for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
				{
					$sqlquery['fields'] .= "sd.ship_options_$i, sd.ship_service_$i, sd.ship_fee_$i, sd.freeshipping_$i, ";
				}
				$sqlquery['fields'] = substr($sqlquery['fields'], 0, -2);
				$sqlquery['leftjoin'] .= "
					LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
					LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON p.project_id = sd.project_id";
				/*$sqlquery['leftjoin'] .= "
					LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id";*/
			}

                        // #### hook below is useful for changing any specifics from the above                        
                        ($apihook = $ilance->api('search_query_fields')) ? eval($apihook) : false;
                     
                        // #### categories #####################################
                        $sqlquery['categories'] = '';
						//suku
                        if (!empty($ilance->GPC['cid']) or isset($ilance->GPC['series']))
                        {
						   
                                $subcategorylist = $subcatname = '';
                                $cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : '0';
                                
                               //suku	
								if(isset($ilance->GPC['series']) and $ilance->GPC['series']>0 and $cid == 0)
								{
								$series_id=$ilance->GPC['series'];
								$cmode=$ilance->GPC['mode'].'series';
								$ilance->categories_parser = construct_object('api.categories_parser');
								$series_details=$ilance->categories_parser->fetch_coin_series(0,$series_id);
								$denomination_detail=$ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
								 
								$subcatname .= ', <span class="black">' . $series_details['coin_series_name'] . '</span>';
								$childrenids=$ilance->categories_parser->fetch_children_pcgs($series_id);
								$navcrumb=array();
								 
								$navcrumb["$ilpage[merch]?denomination=".$denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];
								$navcrumb["$ilpage[search]?mode=product&series=".$series_details['coin_series_unique_no']] = $series_details['coin_series_name'];
								$subcategorylist .= $childrenids;
								$sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
								}
							    
                                
                                if ($cid > 0)
                                {
                                        $cmode = $ilance->GPC['mode'] . 'cat';
                                        $subcatname .= ', <span class="black">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], $cid) . '</span>';
                                        $childrenids = $ilance->categories->fetch_children_ids($cid, $ilance->GPC['mode']);
                                        
										$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
                                        
                                        if (!empty($subcatname))
                                        {
                                                handle_search_verbose('<span class="gray">' . $phrase['_in'] . '</span> <span class="black">' . mb_substr($subcatname, 1) . '</span>, ');
                                                handle_search_verbose_save($phrase['_categories'] . ': <strong>' . mb_substr($subcatname, 1) . '</strong>, ');
                                        }
                                        
                                        $sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                                        
                                        // #### update category view count #####
                                        add_category_viewcount(intval($ilance->GPC['cid']));
								$cat_details=$ilance->categories_parser->fetch_coin_class(0,$ilance->GPC['cid']);
								 
								$series_details=$ilance->categories_parser->fetch_coin_series(0,$ilance->GPC['series']);
								$denomination_detail=$ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
								 
								$subcatname .= ', <span class="black">' . $series_details['coin_series_name'] . '</span>';
								$childrenids=$ilance->categories_parser->fetch_children_pcgs($series_id);
								$navcrumb=array();
								 
								$navcrumb["$ilpage[merch]?denomination=".$denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];
								$navcrumb["$ilpage[search]?mode=product&series=".$series_details['coin_series_unique_no']] = $series_details['coin_series_name'];
                                }
                        }
						
					
                        unset($cmode, $subcatname, $childrenids, $subcategorylist);

                        // #### finalize our display order for search results ##
                        if (isset($ilance->GPC['sort']) AND !empty($ilance->GPC['sort']) AND in_array($ilance->GPC['sort'], $acceptedsort))
                        {
                              
								$sphrase = fetch_sort_options($project_state);
                                $tphrase = $sphrase[$ilance->GPC['sort']];
				
				$sortconditions = sortable_array_handler('listings');
				$sqlquery['orderby'] = 'ORDER BY ' . $sortconditions[$ilance->GPC['sort']]['field'] . ' ' . $sortconditions[$ilance->GPC['sort']]['sort'] . ' ' . $sortconditions[$ilance->GPC['sort']]['extra'];
                              
                                //handle_search_verbose('<span class="black">' . $phrase[$tphrase] . '</span>, ');
                                //handle_search_verbose_save($phrase['_display_order'] . ': <strong>' . $phrase[$tphrase] . '</strong>, ');
                                
                                unset($sphrase, $tphrase);
                        }
			
			// #### default sort display order if none selected ####
                        else
                        {
				$ilance->GPC['sort'] = '01';
				$sqlquery['orderby'] = "ORDER BY p.date_end ASC";
				
                                $sphrase = fetch_sort_options($project_state);
                                $tphrase = $sphrase['01'];
                                
                                //handle_search_verbose_save($phrase['_display_order'] . ': <strong>' . $phrase[$tphrase] . '</strong>, ');
                                
                                unset($sphrase, $tphrase);
                        }
			
			// #### hold display order for modals as sort is removed due to main search bar above listings
			$sort = $ilance->GPC['sort'];
                        
                        // #### search options: is user hiding their own results?
                        $sqlquery['hidequery'] = '';
                        if ($selected['hidelisted'] == 'true' AND !empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $sqlquery['hidequery'] = "AND (u.user_id != '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";
                                        
                                handle_search_verbose('<span class="black">' . $phrase['_excluding_results_that_are_listed_by_me'] . '</span>, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_excluding_results_that_are_listed_by_me_uppercase'] . '</strong>, ');
                        }
                        
                        // #### filter search method (titles only or everything)
                        $titlesonly = isset($ilance->GPC['titlesonly']) ? intval($ilance->GPC['titlesonly']) : '-1';
                        if ($titlesonly == '-1')
                        {
                                //handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_search_entire_auctions'] . '</strong>, ');
                        }
                        else
                        {
                                $removeurl = rewrite_url($scriptpage, 'titlesonly=' . $ilance->GPC['titlesonly']);
                                
                                //handle_search_verbose('<span class="black">' . $phrase['_searching_keywords_in_titles_only'] . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
                                //handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_search_auction_titles_only'] . '</strong>, ');
                        }
                        
                        // #### search exact username? #########################
                        $sqlquery['userquery'] = $clear_searchuser = $clear_searchuser_url = '';
                        if (isset($ilance->GPC['searchuser']) AND !empty($ilance->GPC['searchuser']))
                        {
                                $searchuser = $ilance->GPC['searchuser'];
                                $searchuser = $ilance->common->xss_clean($searchuser);
                                $removeurl = rewrite_url($scriptpage, 'searchuser=' . urlencode($searchuser));
				
				// #### clear left nav search user #############
				$clear_searchuser = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
				$clear_searchuser_url = $removeurl;
                                $favexactphrase = '';
                                
                                if (isset($ilance->GPC['exactname']) AND $ilance->GPC['exactname'])
                                {
                                        $removeurl = rewrite_url($removeurl, 'exactname=' . $ilance->GPC['exactname']);
					
					// #### clear left nav search exact user
					$clear_searchuser = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					$clear_searchuser_url = $removeurl;
                                        $exactphrase = $phrase['_exactly_match'];
                                        $favexactphrase = '( <strong>' . $phrase['_exact_matches'] . '</strong> )';
                                        
                                        $sqlquery['userquery'] = "AND (u.username = '" . $ilance->db->escape_string($searchuser) . "')";
                                }
                                else
                                {
                                        $exactphrase = $phrase['_match'];
                                        $sqlquery['userquery'] = "AND (u.username LIKE '%" . $ilance->db->escape_string($searchuser) . "%')";
                                }
                                
                                handle_search_verbose('<span class="black">' . $phrase['_searching_all_members_that'] . ' ' . $exactphrase . ' ' . $searchuser . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_username'] . ': <strong>' . $searchuser . '</strong> ' . $favexactphrase . ', ');
                        }
                        
                        // #### search via auction type ########################
			$show['allbuyingformats'] = false;
                        $sqlquery['projectdetails'] = $buyingformats = '';
			if (empty($ilance->GPC['buynow']) AND empty($ilance->GPC['auction']) AND empty($ilance->GPC['lub']) AND empty($ilance->GPC['scheduled']) AND empty($ilance->GPC['inviteonly']) AND empty($ilance->GPC['penny']))
                        {
				$show['allbuyingformats'] = true;
				if ($ilance->GPC['mode'] == 'product')
				{
					//handle_search_verbose_save($phrase['_buying_formats'] . ': <strong>' . $phrase['_all'] . '</strong>, ');
				}
				else
				{
					//handle_search_verbose_save($phrase['_hiring_formats'] . ': <strong>' . $phrase['_all'] . '</strong>, ');
				}
			}
			else
			{
				$removeurl = $scriptpage;
				// #### include auctions #######################
				if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'])
				{
					$removeurl = rewrite_url($scriptpage, 'auction=' . $ilance->GPC['auction']);
					$sqlquery['projectdetails'] .= "AND (p.project_details = 'public') ";
					$buyingformats .= ($ilance->GPC['mode'] == 'product') ? $phrase['_auction'] . ', ' : $phrase['_reverse_auction'] . ', ';
				}
				
				// #### filter auctions with buynow available ##########
				if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'])
				{
					$removeurl = rewrite_url($scriptpage, 'buynow=' . $ilance->GPC['buynow']);
					$sqlquery['projectdetails'] .= "AND (p.buynow = '1') ";
					$buyingformats .= $phrase['_buy_now'] . ', ';
					//$sqlquery['options'] .= "AND (p.buynow = '1') ";
				}
				
				// #### include invite only auctions ###########
				if (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'])
				{
					$removeurl = rewrite_url($scriptpage, 'inviteonly=' . $ilance->GPC['inviteonly']);
					$sqlquery['projectdetails'] .= "AND (p.project_details = 'invite_only') ";
					$buyingformats .= $phrase['_invite_only'] . ', ';
				}
				
				// #### include upcoming scheduled events ######
				if (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'])
				{
					$removeurl = rewrite_url($scriptpage, 'scheduled=' . $ilance->GPC['scheduled']);
					$sqlquery['projectdetails'] .= "AND (p.project_details = 'realtime') ";
					$buyingformats .= $phrase['_scheduled'] . ', ';
				}
				
				// #### include lowest unique bid events #######
				if (isset($ilance->GPC['lub']) AND $ilance->GPC['lub'])
				{
					$removeurl = rewrite_url($scriptpage, 'lub=' . $ilance->GPC['lub']);
					$sqlquery['projectdetails'] .= "AND (p.project_details = 'unique') ";
					$buyingformats .= $phrase['_lowest_unique_bid'] . ', ';
				}
				
				// #### include penny auction events ###########
				if (isset($ilance->GPC['penny']) AND $ilance->GPC['penny'])
				{
					$removeurl = rewrite_url($scriptpage, 'penny=' . $ilance->GPC['penny']);
					$sqlquery['projectdetails'] .= "AND (p.project_details = 'penny') ";
					$buyingformats .= $phrase['_penny_auction'] . ', ';
				}
				
				if (!empty($buyingformats))
				{
					$buyingformats = substr($buyingformats, 0, -2);
					
					if ($ilance->GPC['mode'] == 'product')
					{
						handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_buying_formats'] . ':</span> -->' . $buyingformats . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
						handle_search_verbose_save($phrase['_buying_formats'] . ': <strong>' . $buyingformats . '</strong>, ');
					}
					else
					{
						handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_hiring_formats'] . ':</span> -->' . $buyingformats . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
						handle_search_verbose_save($phrase['_hiring_formats'] . ': <strong>' . $buyingformats . '</strong>, ');
					}
				}
			}
                        
                        // #### buying formats (auction type) selector #########
			// also generates variable: $clear_listtype_url for usage below
			$leftnav_buyingformats = print_buying_formats();
			  
			// #### link to clear region from left nav menu header
			$clear_listtype = ($show['allbuyingformats'])
				? ''
				: '<a href="' . $clear_listtype_url . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
			
			unset($buynow, $clear_listtype_url);
			
			// #### handle keyword input ###########################
                        $sqlquery['keywords'] = $keyword_formatted = '';
                        $keywords_array = array();
                        
			// #### build our sql state based on keyword input #####
                        if (isset($keyword_text) AND !empty($keyword_text))
                        {
                                // #### fulltext mode ##########################
                                if ($ilconfig['fulltextsearch'])
                                {
                                        $keyword_formatted .= '<strong>' . $keyword_text . '</strong>, ';
                                        $keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
                                        $keyword_formatted_favtext = $keyword_formatted;
                                        $sqlquery['keywords'] .= ($titlesonly == '-1') ? "AND MATCH (p.project_title,p.description,p.additional_info,p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)" : "AND MATCH (p.project_title,p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)";
                                }
                                
                                // #### non-fulltext mode ######################
                                else
                                {
                                        // splits spaces and commas into array
                                        $keyword_text = preg_split("/[\s,]+/", trim($keyword_text));
                                        
                                        // #### MULTIPLE KEYWORDS ##############
                                        if (sizeof($keyword_text) > 1)
                                        {
                                                $sqlquery['keywords'] .= 'AND (';
                                                for ($i = 0; $i < sizeof($keyword_text); $i++)
                                                {
                                                        $keyword_formatted .= '<strong>' . $keyword_text[$i] . '</strong>, ';
                                                        $sqlquery['keywords'] .= "p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR ";
                                                        $keywords_array[] = $keyword_text[$i];
                                                        
                                                        if ($titlesonly == '-1')
                                                        {
                                                                // search everything
                                                                $sqlquery['keywords'] .= "p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR ";
                                                        }
                                                }				
                                                $sqlquery['keywords'] = mb_substr($sqlquery['keywords'], 0, -4) . ')';
                                                $keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
                                                $keyword_formatted_favtext = $keyword_formatted;
                                        }
                                        
                                        // #### SINGLE KEYWORD #################
                                        else
                                        {
                                                $keyword_formatted = '<strong>' . $keyword_text[0] . '</strong>';
                                                $keyword_formatted_favtext = '<strong>' . $keyword_text[0] . '</strong>';
                                                $keywords_array[] = $keyword_text[0];
                                                $sqlquery['keywords'] .= ($titlesonly == '-1') ? "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%')" : "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%')";
                                        }        
                                }
                        }
                        
                        $show['allowlisting'] = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], $cid);
                        $sqlquery['options'] = '';

                        // #### filter nonprofit assigned listings #############
                        if (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'])
                        {
                                $removeurl = rewrite_url($scriptpage, 'donation=' . $ilance->GPC['donation']);
                                $sqlquery['options'] .= "AND (p.donation = '1') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_including_nonprofits'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_including_nonprofits'] . '</strong>, ');
                        }
			
                        if (isset($ilance->GPC['charityid']) AND $ilance->GPC['charityid'] > 0)
                        {
                                $removeurl = rewrite_url($scriptpage, 'charityid=' . intval($ilance->GPC['charityid']));
                                $sqlquery['options'] .= "AND (p.charityid = '" . intval($ilance->GPC['charityid']) . "') ";
                                
                                $tmp = fetch_charity_details(intval($ilance->GPC['charityid']));
                                
                                handle_search_verbose('<span class="black">' . $phrase['_nonprofit'] . ':</span> <span class="gray"><strong>' . $tmp['title'] . '</strong></span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_nonprofit'] . ': ' . $tmp['title'] . '</strong>, ');
                                unset($tmp);
                        }
                        
                        // #### filter escrow secured listings #################
                        if (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'])
                        {
                                $removeurl = rewrite_url($scriptpage, 'escrow=' . $ilance->GPC['escrow']);
                                $sqlquery['options'] .= "AND (p.filter_escrow = '1') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_showing_services_that_require_secure_escrow'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_services_that_require_secure_escrow'] . '</strong>, ');
                        }
			
                        // #### filter auctions with public message boards #####
                        if (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'])
                        {
                                $removeurl = rewrite_url($scriptpage, 'publicboard=' . $ilance->GPC['publicboard']);
                                $sqlquery['options'] .= "AND (p.filter_publicboard = '1') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_showing_listings_that_allow_public_message_board'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_listings_that_allow_public_message_board'] . '</strong>, ');
                        }
			
                        // #### filter auctions with free shipping #############
                        if (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'])
                        {
                                $removeurl = rewrite_url($scriptpage, 'freeshipping=' . $ilance->GPC['freeshipping']);
                                $sqlquery['options'] .= "AND (sd.freeshipping_1 = '1' OR sd.freeshipping_2 = '1' OR sd.freeshipping_3 = '1' OR sd.freeshipping_4 = '1' OR sd.freeshipping_5 = '1') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_listing_items_with_free_shipping'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_listing_items_with_free_shipping'] . '</strong>, ');
                        }
			
                        // #### filter auctions listed as lots format ##########
                        if (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'])
                        {
                                $removeurl = rewrite_url($scriptpage, 'listedaslots=' . $ilance->GPC['listedaslots']);
                                $sqlquery['options'] .= "AND (p.filtered_auctiontype = 'fixed' AND p.buynow_price > 0 AND p.buynow_qty > 0) ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_showing_items_listed_as_lots'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_items_listed_as_lots'] . '</strong>, ');
                        }
			
                        // #### filter auctions only with images? ##############
                        if (isset($ilance->GPC['images']) AND $ilance->GPC['images'])
                        {
                                $removeurl = rewrite_url($scriptpage, 'images=' . $ilance->GPC['images']);
                                $sqlquery['options'] .= "AND (a.attachtype = 'itemphoto' AND a.user_id = p.user_id) ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_showing_only_items_with_images'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_only_items_with_images'] . '</strong>, ');
                        }
			// #### include completed events ###############
			if (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'])
			{
				$removeurl = rewrite_url($scriptpage, 'completed=' . $ilance->GPC['completed']);
				$sqlquery['projectdetails'] .= "AND (p.haswinner = '1' OR p.hasbuynowwinner = '1') ";
				$sqlquery['timestamp'] = "";
				$sqlquery['projectstatus'] = "AND (p.status != 'open')";
				
				handle_search_verbose('<span class="black">' . $phrase['_show_only_completed_listings'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_show_only_completed_listings'] . '</strong>, ');
			}
			
			// #### currency selector ##############################
			if ($ilconfig['globalserverlocale_currencyselector'])
			{
				$ilance->GPC['cur'] = isset($ilance->GPC['cur'])
					? handle_input_keywords($ilance->GPC['cur'])
					: '';
					
				$leftnav_currencies = print_currencies('projects AS p', 'p.currencyid', $ilance->GPC['cur'], 5, "AND status = 'open'");
				$clear_currencies = !empty($clear_currencies_all)
					? '<a href="' . $clear_currencies_all . '" rel="nofollow">' . $phrase['_clear'] . '</a>'
					: '';
				
				$removeurl = rewrite_url($scriptpage, 'cur=' . $ilance->GPC['cur']);
				$sqlquery['options'] .= (!empty($ilance->GPC['cur']))
					? "AND (FIND_IN_SET(p.currencyid, '" . $ilance->db->escape_string($ilance->GPC['cur']) . "')) "
					: '';
				
				if (isset($ilance->GPC['cur']) AND $ilance->GPC['cur'] != '')
				{
					//$this->currencies[$currencies['currency_id']
					$curs = '';
					if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == true)
					{
						$temp = explode(',', $ilance->GPC['cur']);
						foreach ($temp AS $key => $value)
						{
							if ($value != '')
							{
								$curs .= $ilance->currency->currencies[$value]['currency_abbrev'] . ', ';
							}
						}
						if (!empty($curs))
						{
							$curs = substr($curs, 0, -2);
						}
						unset($temp);
					}
					else if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == false)
					{
						$ilance->GPC['cur'] = intval($ilance->GPC['cur']);
						$curs .= $ilance->currency->currencies[$ilance->GPC['cur']]['currency_abbrev'];
					}
					
					handle_search_verbose('<!--<span class="gray">' . $phrase['_currency'] . ':</span> --><span class="black">' . $curs . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_currency'] . ': <strong>' . $curs . '</strong>, ');
					unset($curs);
				}
			}
			
			// #### options selector ###############################
			$leftnav_options = print_options($ilance->GPC['mode']);
			$clear_options = !empty($clear_options_all)
				? '<a href="' . $clear_options_all . '" rel="nofollow">' . $phrase['_clear'] . '</a>'
				: '';
			
                        // #### start / end date range filter ##################
                        if (isset($ilance->GPC['endstart']))
                        {
                                $removeurl = rewrite_url($scriptpage, 'endstart=' . $ilance->GPC['endstart']);
                                switch ($ilance->GPC['endstart'])
                                {
                                        case '1':
                                        {
                                                // ending within
                                                if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1')
                                                {
                                                        $sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_ADD', 'p.date_end', '<=');
                                                }
                                                
                                                handle_search_verbose('<span class="black">' . $phrase['_ending_within_lower'] . ' ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span>' . (($ilance->GPC['endstart_filter'] != '-1') ? ' <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ' : ', '));
                                                handle_search_verbose_save($phrase['_listings'] . ': ' . $phrase['_ending_within_lower'] . ' <strong>' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
                                                break;
                                        }                                    
                                        case '2':
                                        {
                                                // ending in more than
                                                if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1')
                                                {
                                                        $sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_ADD', 'p.date_end', '>=');
                                                }
                                                
                                                handle_search_verbose('<span class="black">' . $phrase['_ending_in_more_than_lower'] . ' ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
                                                handle_search_verbose_save($phrase['_listings'].': '.$phrase['_ending_in_more_than_lower'].' <strong>'.fetch_startend_phrase($ilance->GPC['endstart_filter']).'</strong>, ');
                                                break;
                                        }                                    
                                        case '3':
                                        {
                                                // started within
                                                if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1')
                                                {
                                                        $sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_SUB', 'p.date_added', '>=');
                                                }
                                                
                                                handle_search_verbose('<span class="black">' . $phrase['_started_within_lower'] . ' ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
                                                handle_search_verbose_save($phrase['_listings'] . ': ' . $phrase['_started_within_lower'] . ' <strong>'.fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
                                                break;
                                        }
                                }    
                        }
                        
                        // #### filter listings with non-disclosed budgets #####
                        if (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] == '-1' AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
                        {
                                $removeurl = rewrite_url($scriptpage, 'budget=' . $ilance->GPC['budget']);
				$clear_budgetrange = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
                                $sqlquery['options'] .= "AND (p.filter_budget = '0') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_showing_services_with_nondisclosed_budgets'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ' <strong>' . $phrase['_showing_services_with_nondisclosed_budgets'] . '</strong>, ');
                        }
                        else if (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] > 0 AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
                        {
                                $overview = $ilance->auction->construct_budget_overview(intval($ilance->GPC['cid']), intval($ilance->GPC['budget']));
                                $removeurl = rewrite_url($scriptpage, 'budget=' . intval($ilance->GPC['budget']));
                                $sqlquery['options'] .= "AND (p.filter_budget = '1' AND p.filtered_budgetid = '" . intval($ilance->GPC['budget']) . "') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_budget_range'] . ': ' . $overview . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ' <strong>' . $phrase['_budget'] . ': ' . $overview . '</strong>, ');
                                unset($overview);
                        }
                        else
                        {
                                $ilance->GPC['budget'] = '';
                        }
                        
                        // #### search number of bids range ####################
                        if (!empty($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] != '-1')
                        {
                                $removeurl = rewrite_url($php_self, 'bidrange=' . $ilance->GPC['bidrange']);
				
				// #### link to clear region from left nav menu header
				$clear_bidrange = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
				
                                switch ($ilance->GPC['bidrange'])
                                {
                                        case '1':
                                        {
                                                $sqlquery['options'] .= "AND (p.bids < 10) ";
                                                
                                                handle_search_verbose('<span class="black">' . $phrase['_with_less_than_ten_bids_placed'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_with_less_than_ten_bids_placed'] . '</strong>, ');
                                                break;
                                        }                                        
                                        case '2':
                                        {
                                                $sqlquery['options'] .= "AND (p.bids BETWEEN 10 AND 20) ";
                                                
                                                handle_search_verbose('<span class="black">' . $phrase['_between_ten_and_twenty_bids_placed'] . '</span>, ');
                                                handle_search_verbose_save($phrase['_filter'] . ': <strong>'.$phrase['_between_ten_and_twenty_bids_placed'] . '</strong><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                break;
                                        }                                        
                                        case '3':
                                        {
                                                $sqlquery['options'] .= "AND (p.bids > 20) ";
                                                
                                                handle_search_verbose('<span class="black">' . $phrase['_with_more_than_twenty_bids_placed'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_filter'] . ': <strong>'.$phrase['_with_more_than_twenty_bids_placed'] . '</strong>, ');
                                                break;
                                        }
                               }
                        }
                        else
                        {
				$clear_bidrange = '';
                                //handle_search_verbose_save($phrase['_bids'] . ': <strong>' . $phrase['_any'] . '</strong>, ');
                        }
			
			$ilance->GPC['bidrange'] = !empty($ilance->GPC['bidrange']) ? $ilance->GPC['bidrange'] : '';
			
			// #### left nav bid range link presentation ###########
                        $search_bidrange_pulldown_product = print_bid_range_pulldown($ilance->GPC['bidrange'], 'bidrange', 'productbidrange', 'links');
                        $search_bidrange_pulldown_service = print_bid_range_pulldown($ilance->GPC['bidrange'], 'bidrange', 'servicebidrange', 'links');
			
			// #### search via country #############################
			$sqlquery['location'] = $country = $countryid = $countryids = '';
			$removeurlcountry = $php_self;
			
			// #### searching via country name #####################
			if (!empty($ilance->GPC['country']))
                        {
				// #### populate proper country id #############
				$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
				$country = $ilance->GPC['country'];
				
				// #### populate regional information ##########
				$ilance->GPC['region'] = fetch_region_title_by_countryid($countryid);
				$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
				$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
                                $removeurlcountry = rewrite_url($php_self, 'country=' . urlencode($ilance->GPC['country']));
                                $sqlquery['location'] .= "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
                        }
			
			// #### searching via country identifier ###############
			else if (!empty($ilance->GPC['countryid']) AND $ilance->GPC['countryid'] > 0)
			{
				// #### populate proper country name ###########
				$countryid = intval($ilance->GPC['countryid']);
				$ilance->GPC['country'] = print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false);
				$country = $ilance->GPC['country'];
				
				// #### populate regional information ##########
				$ilance->GPC['region'] = fetch_region_title_by_countryid($countryid);
				$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
				$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
				
                                $removeurlcountry = rewrite_url($php_self, 'countryid=' . urlencode($countryid));
                                $sqlquery['location'] .= "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
			}
			
			// #### region selector ################################
			$region = (isset($ilance->GPC['region']) AND !empty($ilance->GPC['region'])) ? $ilance->GPC['region'] : '';
			$regiontype = isset($ilance->GPC['regiontype']) AND !empty($ilance->GPC['regiontype']) ? intval($ilance->GPC['regiontype']) : '';
			$regionname = '';
			
			// #### check if our selected region contains a country id
			
			if (strrchr($region, '.'))
			{
				$regtemp = explode('.', $region);
				if (!empty($regtemp[0]) AND !empty($regtemp[1]))
				{
					$regionname = fetch_region_title($regtemp[0]);
					
					// #### populate our selected country via special region type url
					$countryid = $regtemp[1];
					$ilance->GPC['country'] = print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false);
					$country = $ilance->GPC['country'];
					
					// #### build our sql country region query 
					$sqlquery['location'] = "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
				}
				else if (!empty($regtemp[0]))
				{
					$regionname = fetch_region_title($regtemp[0]);	
				}
				unset($regtemp);
			}
			else
			{
				$regionname = fetch_region_title($region);
				$countryids = fetch_country_ids_by_region($regionname);
				$sqlquery['location'] = (!empty($countryids))
					? "AND (FIND_IN_SET(p.countryid, '" . $countryids . "')) "
					: "";
			}
			
			// #### link to clear region from left nav menu header
			$clear_region = '';
			if (!empty($regionname))
			{
				$removeurl = rewrite_url($php_self, 'region=' . $region);
				$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
				$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
				$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
				$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
				$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
				$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
				
				$clear_region = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
				
				handle_search_verbose('<span class="gray"><!--<strong>' . $phrase['_region'] . ': --><span class="black">' . $regionname . '</span></strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');                                                
				handle_search_verbose_save($phrase['_region'] . ': <strong>' . $regionname . '</strong>, ');
			}
			
			$leftnav_regions = print_regions('', $region, $_SESSION['ilancedata']['user']['slng'], '', 'links');
			
			// #### finalize country verbose text so it's placed after the region
			if ($countryid > 0)
			{
				handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_country'] . ':</span> --><strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_country'] . ': <strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong>, ');
			}
                        
                        // #### search via price range #########################
                        $sqlquery['pricerange'] = $clear_price = '';
                        if ($show['mode_product'])
                        {
                                if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
                                {
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
					$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					
                                        $sqlquery['pricerange'] .= "AND (p.currentprice >= " . intval($ilance->GPC['fromprice']) . " ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_min_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong></span> &ndash; ');
                                        handle_search_verbose_save($phrase['_min_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= "AND (p.currentprice >= 0 ";
                                }
                                
                                if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
                                {
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
                                        $removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					
                                        $sqlquery['pricerange'] .= "AND p.currentprice <= " . intval($ilance->GPC['toprice']) . ") ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_max_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= ")";
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $phrase['_unlimited'] . '</strong>, ');
                                }
                        }
                        
                        // #### radius searching ###############################
			// are we guest and do we have a zip code from the location modal nag popup?
			// this is useful for users to get a rough estimate on shipping
			if (empty($_SESSION['ilancedata']['user']['userid']))
			{
				// user not searching by zip so check cookie
				if (empty($ilance->GPC['radiuszip']))
				{
					// cookie appears to have a zip.. we'll use this
					if (!empty($_COOKIE[COOKIE_PREFIX . 'radiuszip']))
					{
						$ilance->GPC['radiuszip'] = $_COOKIE[COOKIE_PREFIX . 'radiuszip'];
					}
				}	
			}
			else
			{
				// member not searching by zip so check profile
				if (empty($ilance->GPC['radiuszip']))
				{
					if (!empty($_SESSION['ilancedata']['user']['postalzip']))
					{
						$ilance->GPC['radiuszip'] = $_SESSION['ilancedata']['user']['postalzip'];
					}
				}
				
				// default zip
				else
				{
					// check if cookie exist and is the same as the entered zipcode
					// if not, use entered zipcode
					if (!empty($_COOKIE[COOKIE_PREFIX . 'radiuszip']) AND $_COOKIE[COOKIE_PREFIX . 'radiuszip'] == $ilance->GPC['radiuszip'])
					{
						$ilance->GPC['radiuszip'] = $_COOKIE[COOKIE_PREFIX . 'radiuszip'];
					}
				}
			}
			
                        $show['radiussearch'] = false;
                        $sqlquery['radius'] = $clear_distance = '';
			if ($ilconfig['globalserver_enabledistanceradius'] AND !empty($ilance->GPC['radiuszip']) AND $countryid > 0)
                        {
                                $show['radiussearch'] = true;
				
                                // user supplied a radius.  which country are we trying to do a radius search on?
                                $radiuscountryid = intval($countryid);

				$removeurl = rewrite_url($php_self, 'radiuszip=' . urlencode($ilance->GPC['radiuszip']));
				$ilance->GPC['radiusstate'] = '';
                                $ilance->GPC['radiuszip'] = mb_strtoupper(trim($ilance->GPC['radiuszip']));
                                $ilance->GPC['radius'] = (isset($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0) ? intval($ilance->GPC['radius']) : '';
                                $removeurl = rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']);
				
				// #### build sql to fetch zips in range of zip code entered by user for the viewing region
				if (!empty($ilance->GPC['radius']))
				{
					$radiusresult = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = true, $radiusjoin = false);
					if (!empty($radiusresult) AND is_array($radiusresult) AND count($radiusresult) > 1)
					{
						// the proper zipcode + country id was selected..
						$sqlquery['leftjoin'] .= $radiusresult['leftjoin'];
						$sqlquery['fields'] .= $radiusresult['fields'];
						$sqlquery['radius'] = $radiusresult['condition'];
	
						$zipcodesrange = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = true);
						$sqlquery['radius'] .= (isset($zipcodesrange) AND is_array($zipcodesrange)) ? $zipcodesrange['condition'] : '';
						
						$zipcodecityname = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = false, $fetchcityonly = true);
	
						handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_radius'] . ':</span> -->' . number_format(intval($ilance->GPC['radius'])) . ' ' . $phrase['_mile_radius_from'] . ' ' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : (!empty($zipcodecityname) ? $zipcodecityname . ', ' : '')) . intval($ilance->GPC['radiuszip']) . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
						handle_search_verbose_save($phrase['_radius'] . ': <strong>' . number_format($ilance->GPC['radius']) . '</strong> ' . $phrase['_mile_radius_from'] . ' ' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : '') . intval($ilance->GPC['radiuszip']) . ', ');
					}
				}
				
				$clear_distance = (!empty($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0) ? '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>' : '';
				
				// #### enable distance column ordering ################
				if ($ilconfig['globalserver_enabledistanceradius'])
				{
					$acceptedsort2 = array('121','122');
					$acceptedsort = array_merge($acceptedsort, $acceptedsort2);
					unset($acceptedsort2);
				}				
                        }
                        
			// #### does user search in cities? ####################
			$clear_local = $removeurl_local = '';
			$removeurl = $php_self;
			if (!empty($ilance->GPC['city']) AND !empty($ilance->GPC['country']))
			{
				// does user enter a city in search?
				$removeurl = rewrite_url($scriptpage, 'city=' . $ilance->GPC['city']);
				$removeurl_local = rewrite_url($removeurl, 'city=' . $ilance->GPC['city']);
				
				$ilance->GPC['city'] = ucfirst(trim($ilance->GPC['city']));
				$sqlquery['location'] .= "AND (u.city LIKE '%" . $ilance->db->escape_string($ilance->GPC['city']) . "%') ";
				
				handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_city'] . ':</span> --><strong>' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_city'] . ': <strong>' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</strong>, ');
			}
			    
			// #### does user search in state or provinces? ########
			if (!empty($ilance->GPC['state']) AND !empty($ilance->GPC['country']))
			{
				// does user enter a city in search?
				$removeurl = rewrite_url($scriptpage, 'state=' . $ilance->GPC['state']);
				$removeurl_local = rewrite_url($removeurl_local, 'state=' . $ilance->GPC['state']);
				
				$ilance->GPC['state'] = ucfirst(trim($ilance->GPC['state']));
				$sqlquery['location'] .= "AND (u.state LIKE '%" . $ilance->db->escape_string($ilance->GPC['state']) . "%') ";
				
				handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_state_or_province'] . ':</span> -->' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_state'] . ': <strong>' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</strong>, ');
			}
			    
			// #### does user search in zip codes? #################
			if (!empty($ilance->GPC['zip_code']) AND !empty($ilance->GPC['country']))
			{
				$ilance->GPC['zip_code'] = mb_strtoupper(trim($ilance->GPC['zip_code']));
				$distanceresult = $ilance->distance->fetch_sql_as_distance($ilance->GPC['zip_code'], $ilance->GPC['country'], 'p.zipcode');
				if (is_array($distanceresult))
				{
				        $sqlquery['leftjoin'] .= $distanceresult['leftjoin'];
				        $sqlquery['fields'] .= $distanceresult['fields'];
				}
				
				$removeurl = rewrite_url($scriptpage, 'zip_code=' . $ilance->GPC['zip_code']);
				$removeurl_local = rewrite_url($removeurl_local, 'zip_code=' . $ilance->GPC['zip_code']);
				
				$sqlquery['location'] .= "AND (u.zip_code LIKE '%" . $ilance->db->escape_string(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zip_code'])))) . "%') ";
				
				handle_search_verbose('<span class="black"><!--' . $phrase['_zip_code'] . ': --><strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_zip_slash_postal_code'] . ': <strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong>, ');
			}
			
			$clear_local = (!empty($removeurl_local)) ? '<a href="' . $removeurl_local . '" rel="nofollow">' . $phrase['_clear'] . '</a>' : '';
			unset($removeurl_local);
			
			// #### confirm or reject the ability to see the distance column based on user search preferences
			if ($show['mode_service'])
			{
				if (is_array($selected['serviceselected']) AND !empty($ilance->GPC['radiuszip']) AND in_array('distance', $selected['serviceselected']))
				{
					$show['distancecolumn'] = 1;
				}
			}
			else if ($show['mode_product'])
                        {
				if (is_array($selected['productselected']) AND !empty($ilance->GPC['radiuszip']) AND in_array('distance', $selected['productselected']))
				{
					$show['distancecolumn'] = 1;
				}
			}

                     
					    // #### searchable category questions ##################
                        $sqlquery['genrequery'] = '';
                        if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
                        {
				// #### question groups selected : &qid=9.1,8.1,etc
				$qids = explode(',', $ilance->GPC['qid']);
				
				$tempgenrequery = '';
				$sqlquery['genrequery'] .= "AND (p.project_id IN(";
				
                                foreach ($qids AS $keyquestionid => $keyanswerid)
                                {
					$aids = explode('.', $keyanswerid);					
                                        if (isset($aids[1]) AND !empty($aids[1]))
                                        {
                                                $questiontitle = fetch_searchable_question_title($aids[0], $project_state);
						if ($questiontitle != '')
						{
							$answertitle = '<span class="black">' . fetch_searchable_answer_title($aids[0], $aids[1], $project_state) . '</span>';
							
							$showqidurl = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url("{$project_state}catplain", intval($ilance->GPC['cid']), 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], intval($ilance->GPC['cid'])), '', 0, '', 0, 0)
								: $ilpage['search'] . print_hidden_fields(true, array('page','searchid','list'), true, '', '', $htmlentities = true, $urldecode = true);
							
							$showqidurl = urldecode($showqidurl);
							$showqidurl = rewrite_url($showqidurl, '' . $aids[0] . '.' . $aids[1] . ',');
							$showqidurl = rewrite_url($showqidurl, ',' . $aids[0] . '.' . $aids[1]);
							$showqidurl = rewrite_url($showqidurl, '' . $aids[0] . '.' . $aids[1]);
							
							$tempgenrequery .= fetch_searchable_sql_condition($aids[0], $aids[1], $project_state);
	
							handle_search_verbose_filters('<span class="gray"><!--' . $questiontitle . ': --><strong>' . $answertitle . '</strong></span><!-- <a href="' . $showqidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');                                                
							handle_search_verbose_save($questiontitle . ': <strong>' . $answertitle . '</strong>, ');
						}
                                        }
                                }
				
				if (!empty($tempgenrequery))
				{
					$tempgenrequery = (strrchr($tempgenrequery, ',')) ? substr($tempgenrequery, 0, -1) : $tempgenrequery;
					$sqlquery['genrequery'] .= $tempgenrequery;
					$sqlquery['genrequery'] .= ")) ";
				}
				else
				{
					$sqlquery['genrequery'] = '';
				}

                                unset($questiontitle, $answertitle, $tempgenrequery, $qids);
                        }
			
                        // #### complete final search query parameters
                        $sqlquery['select'] = "SELECT $sqlquery[fields] $sqlquery[from] $sqlquery[leftjoin] WHERE p.user_id = u.user_id AND u.status = 'active' " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "";
                        
                        // #### build sql query ################################
                        $SQL  = "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[groupby] $sqlquery[orderby] $sqlquery[limit]";
                        $SQL2 = "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[groupby] $sqlquery[orderby]";
			
                        $numberrows = $ilance->db->query($SQL2);
                        $number = $ilance->db->num_rows($numberrows);
                        $counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
                        $row_count = 0;

                        // #### build our search engine verbose output #########
                        if (!empty($keyword_text))
                        {
				//$keyword_formatted = '<span>' . short_string($keyword_formatted, 90, $symbol = ' ...') . '</span>';
				$vebsave = print_search_verbose_saved('verbose_save');
                                $favtext = '<div>' . $phrase['_keywords'] . ': <strong>' . stripslashes($keyword_formatted_favtext) . '</strong></div>' . $vebsave;
                                $favtext = !empty($vebsave) ? mb_substr($favtext, 0, -2) : $favtext;
                                
				$vebsave = print_search_verbose_saved('verbose');
                                if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
                                {
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_keywords'] . ' <span class="black">' . stripslashes($keyword_formatted) . '</span>';
                                }
                                else
                                {
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_keywords'] . ' <span class="black">' . stripslashes($keyword_formatted) . '</span> ' . $vebsave;
                                        $text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
                                }
                                unset($vebsave);
				
                                $text = '<span id="verbosetext">' . $text . '</span>';
                        }
                        else
                        {
                                // favorite search text results
				$vebsave = print_search_verbose_saved('verbose_save');
                                $favtext = '<div>' . $phrase['_keywords'] . ': <strong>' . $phrase['_none'] . '</strong></div>' . $vebsave;
                                $favtext = !empty($vebsave) ? mb_substr($favtext, 0, -2) : $favtext;
				
				$vebsave = print_search_verbose_saved('verbose');
                                if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
                                {
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_no_keywords'];
                                }
                                else
                                {
					
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_no_keywords'] . ' ' . (!empty($vebsave) ? ' ' . $vebsave : $vebsave);
                                        $text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
                                }
                                unset($vebsave);
				
                                $text = '<span id="verbosetext">' . $text . '</span>';
                        }
			
                        if ($ilconfig['globalauctionsettings_seourls'] AND $cid > 0)
                        {
                                $categoryname = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $project_state, $cid);
                                $showallurl = construct_seo_url("{$project_state}catplain", $cid, 0, $categoryname, '', 0, '', 0, 0, 'qid');
                        }
                        else
                        {
                                $showallurl = $ilpage['search'] . '?mode=' . $ilance->GPC['mode'] . '&amp;sort=' . intval($ilance->GPC['sort']) . '&amp;page=' . intval($ilance->GPC['page']);
                        }
			
                        define('PHP_SELF_NOQID', $showallurl);                        
                        $showtext = print_search_verbose_saved('verbose_filter');
                        if (!empty($showtext))
                        {
                                $showtext = mb_substr($showtext, 0, -2) . '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blue"><a href="' . $showallurl . '" rel="nofollow">' . $phrase['_show_all'] . '</a></span> ]</span>';
                                //$text .= '<div style="padding-top:12px"><strong>' . $phrase['_specifics'] . '</strong>: ' . $showtext . '</div>';
				$text .= ', <span><strong>' . $showtext . '</strong></span>';
                        }
			
			// #### save this search ###############################
			if (isset($ilance->GPC['searchid']) AND $ilance->GPC['searchid'] > 0)
			{
				// todo: add hit tracker to show hit count of saved search
				$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites&amp;returnurl=' . $php_self_urlencoded . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /> ' . $phrase['_view_saved_searches'] . '</a></span> ]</span>';
			}
			else
			{
				$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_save_as_favorite_search'] . '</a></span> ]</span>';
			}
				
                        $metatitle = '';
                        if ($cid > 0)
                        {
                                $metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $project_state, $cid);
                                $metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $project_state, $cid);
                                $metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $project_state, $cid, $commaafter = true, $showinputkeywords = true);
                        }
                        
                        $area_title = $phrase['_search_results_display'] . ': ' . $metatitle . ' (' . $number . ' ' . $phrase['_results_found'] . ')';
						
						//new feb14
						$page_title = ((isset($keyword_text[0]) AND !empty($keyword_text[0])) ? $keyword_text[0] . ', ' : '') . '' . $phrase['_find'] . ' '.$keyword_text[0].' ' . (($project_state == 'service') ? $phrase['_services'] : 'Coins/'.$phrase['_items']) . ' ' . ((!empty($metadescription)) ? $phrase['_in'] . ' ' . $metatitle . ', ' . $metadescription : '') . ' at ' . SITE_NAME;
                      //  $page_title = ((isset($keyword_text) AND !empty($keyword_text)) ? $keyword_text . ', ' : '') . '' . $phrase['_find'] . ' ' . (($project_state == 'service') ? $phrase['_services'] : $phrase['_items']) . ' ' . ((!empty($metadescription)) ? $phrase['_in'] . ' ' . $metatitle . ', ' . $metadescription : '') . ' | ' . SITE_NAME;
                        
                        $search_results_rows = $excludelist = array();
                        $result = $ilance->db->query($SQL);
                        if ($ilance->db->num_rows($result) > 0)
                        {
                                $ilance->bbcode = construct_object('api.bbcode');
                                
                                while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
                                        $excludelist[] = $row['project_id'];
                                        
                                        $td['featured'] = $row['featured'];
                                        $td['bold'] = $row['bold'];
                                        $td['highlite'] = $row['highlite'];
                                        $td['project_id'] = $row['project_id'];
					
					// only fetch distance between point a to b in the distance column
					$row['distance'] = (!isset($row['distance'])) ? 0 : $row['distance'];
                                        $td['distance'] = (isset($show['distancecolumn']) AND $show['distancecolumn'] AND !empty($ilance->GPC['radiuszip']))
						? '<div class="smaller gray">' . $ilance->distance->print_distance_results($row['country'], $row['zip_code'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . '</div>'
						: '-';
						
					// display the location under the title
						// murugan changes  on jan 13
					/*$countryrowname = (isset($ilance->GPC['country']))
						? ''
						: ', ' . print_country_name($row['country'], $_SESSION['ilancedata']['user']['slng'], false);*/
						
					$td['location'] = (!empty($row['state']) ? $row['state'] . '' : '') . $countryrowname;
					
					// show the distance bit after the location
					$td['location'] .= ($td['distance'] != '-' AND !empty($countryid))
						? '<span>&nbsp;&nbsp;(<span class="black">' . $ilance->distance->print_distance_results($row['country'], $row['zip_code'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . ' ' . $phrase['_from_lowercase'] . '</span> <span class="blue"><a href="javascript:void(0)" onclick="javascript:jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();">' . handle_input_keywords($ilance->GPC['radiuszip']) . '<!--, ' . handle_input_keywords($ilance->GPC['country']) . '--></a></span>)</span>'
						: '';
					unset($countryrowname);
					
                                        // #### SERVICE AUCTION LOGIC ##############################
                                        if ($show['mode_service'])
                                        {
                                                $row['project_state'] = 'service';
                                                $td['project_state'] = $row['project_state'];
                                                
                                                if ($row['bold'])
                                                {
                                                        $td['title'] = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('serviceauction', 0, $row['project_id'], $row['project_title'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0)
								: '<a href="' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '"><strong>' . stripslashes($row['project_title']).'</strong></a>';
                                                }
                                                else
                                                {
                                                        $td['title'] = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('serviceauction', 0, $row['project_id'], $row['project_title'], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
								: '<a href="' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '">' . stripslashes($row['project_title']).'</a>';
                                                }
                                                                
                                                // auction description (may contain bbcode)
                                                switch ($row['project_details'])
                                                {
                                                        case 'public':
                                                        {
                                                                // guest
                                                                // vulgar censor
                                                                $td['description'] = strip_vulgar_words($row['description']);
                                                                $td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                $td['additional_info'] = short_string($row['additional_info'], 75);
                                                                break;
                                                        }                                                        
                                                        case 'invite_only':
                                                        {
                                                                // guests
                                                                $td['description'] = "= " . $phrase['_full_description_available_to_invited_providers_only'] . " =";
                                                                if ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) OR ($row['user_id'] == $_SESSION['ilancedata']['user']['userid']))
                                                                {
                                                                        $sqlinvites = $ilance->db->query("
                                                                                SELECT *
                                                                                FROM " . DB_PREFIX . "project_invitations
                                                                                WHERE project_id = '" . $row['project_id'] . "'
                                                                                    AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                                        ");
                                                                        if ($ilance->db->num_rows($sqlinvites) > 0 OR $row['user_id'] == $_SESSION['ilancedata']['user']['userid'])
                                                                        {
                                                                                // member invited
                                                                                
                                                                                // vulgar censor
                                                                                $td['description'] = strip_vulgar_words($row['description']);
                                                                                $td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                                $td['additional_info'] = short_string(print_string_wrap($row['additional_info'], 50), 50);
                                                                        }
                                                                        unset($sqlinvites);
                                                                }
                                                                break;
                                                        }                                                        
                                                        case 'realtime':
                                                        {
                                                                // guests                                                                
                                                                // vulgar censor
                                                                $td['description'] = strip_vulgar_words($row['description']);
                                                                $td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                $td['additional_info'] = handle_input_keywords(short_string(print_string_wrap($row['additional_info'], 50), 50));
                                                                break;
                                                        }
                                                }

                                                if ($ilconfig['globalauctionsettings_seourls'])
                                                {
                                                        $td['category'] = construct_seo_url('servicecat', $row['cid'], $auctionid = 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $row['cid']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                        $td['category'] = '<span class="blue">' . $td['category'] . '</span>';
                                                }
                                                else
                                                {
                                                        $td['category'] = '<a href="' . $ilpage['rfp'] . '?cid=' . $row['cid'] . '">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $row['cid']) . '</a>';
                                                        $td['category'] = '<span class="blue">' . $td['category'] . '</span>';
                                                }
                                                
                                                $td['username'] = fetch_user('username', $row['user_id']);
                                                $td['city'] = ucfirst(fetch_user('city', $row['user_id']));
                                                $td['zipcode'] = mb_strtoupper(fetch_user('zip_code', $row['user_id']));
                                                $td['state'] = ucfirst(fetch_user('state', $row['user_id']));
                                                $td['country'] = print_user_country($row['user_id'], $_SESSION['ilancedata']['user']['slng']);
                                                                
                                                // hide average bid amount on results page if auction is "sealed"
                                                if ($row['bid_details'] == 'open' OR $row['bid_details'] == 'blind')
                                                {
                                                        $avg = $ilance->bid->fetch_average_bid($row['project_id'], true, $row['bid_details'], true);
                                                        if ($avg > 0)
                                                        {
                                                                $td['averagebid'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $avg, $row['currencyid']);
                                                                $td['averagebid_plain'] = $ilance->currency->format($avg, $row['currencyid']);
                                                        }
                                                        else
                                                        {
                                                                $td['averagebid'] = $td['averagebid_plain'] = '-';
                                                        }
                                                        unset($avg);
                                                }
                                                else
                                                {
                                                        $td['averagebid'] = $td['averagebid_plain'] = '= ' . $phrase['_sealed'] . ' =';
                                                }
                                
                                                $td['sel'] = (!empty($_SESSION['ilancedata']['user']['userid']))
							? '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />'
							: '<input type="hidden" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
							
                                                $td['class'] = ($row['highlite'])
							? $ilconfig['serviceupsell_highlightcolor']
							: (($row_count % 2)
								? 'alt1'
								: 'alt1');
							
                                                $td['timeleft'] = '<strong>' . $ilance->auction->auction_timeleft($row['project_id'], $td['class'], 'center') . '</strong>';
                                                $td['icons'] = $ilance->auction->auction_icons($row['project_id'], $row['user_id']);
                                                $td['bids'] = ($row['bids'] > 0)
							? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
							: '-';
							
                                                $td['views'] = $row['views'];
                                                $td['budget'] = '<div class="gray">' . $ilance->auction->construct_budget_overview($row['cid'], $row['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true) . '</div>';
                                                
                                                ($apihook = $ilance->api('search_results_services_loop')) ? eval($apihook) : false;
                                                
                                                $search_results_rows[] = $td;
                                                $row_count++;
                                        }
                                        
                                        // #### PRODUCT AUCTION LOGIC ##########
                                        else if ($show['mode_product'])
                                        {
                                                 
											    $row['project_state'] = 'product';
                                                $td['project_state'] = $row['project_state'];
                                                //$td['username'] = (!empty($_SESSION['ilancedata']['user']['userid'])) ? fetch_user('username', $row['user_id']) : '';
												// murugan changes for hide user name on search result on feb 21
												$td['username'] = '';
                                                $td['city'] = ucfirst(fetch_user('city', $row['user_id']));
                                                $td['zipcode'] = mb_strtoupper(fetch_user('zip_code', $row['user_id']));
                                                $td['state'] = ucfirst(fetch_user('state', $row['user_id']));
                                                $td['country'] = print_user_country($row['user_id'], $_SESSION['ilancedata']['user']['slng']);
                                                
                                                // auction description (may contain bbcode)
                                                switch ($row['project_details'])
                                                {
                                                        case 'public':
                                                        {
                                                                $td['description'] = strip_vulgar_words($row['description']);
																$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                break;
                                                        }                                                        
                                                        case 'invite_only':
                                                        {
                                                                $td['description'] = '= ' . $phrase['_full_description_available_to_invited_providers_only'] . ' =';
                                                                if ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) OR ($row['user_id'] == $_SESSION['ilancedata']['user']['userid']))
                                                                {
                                                                        $sql_invites = $ilance->db->query("
                                                                                SELECT *
                                                                                FROM " . DB_PREFIX . "project_invitations
                                                                                WHERE project_id = '" . $row['project_id'] . "'
                                                                                    AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                                        ");
                                                                        if ($ilance->db->num_rows($sql_invites) > 0 OR $row['user_id'] == $_SESSION['ilancedata']['user']['userid'])
                                                                        {
                                                                                // member invited
                                                                                
                                                                                // vulgar censor
                                                                                $td['description'] = strip_vulgar_words($row['description']);
																				$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                        }
                                                                }
                                                                break;
                                                        }                                                        
                                                        case 'realtime':
                                                        {
                                                                // guests
                                                                // vulgar censor
                                                                $td['description'] = strip_vulgar_words($row['description']);
																$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                break;
                                                        }
                                                }
                                                
                                                $td['category'] = ($ilconfig['globalauctionsettings_seourls'])
							? construct_seo_url('productcat', $row['cid'], $auctionid = 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $row['cid']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
							: '<a href="' . $ilpage['merch'] . '?cid=' . $row['cid'] . '">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $row['cid']) . '</a>';
                                                $td['category'] = '<span class="blue">' . $td['category'] . '</span>';
                                                
                                                // is buynow available for this auction listing?
                                                $td['proxybit'] = $td['buynow'] = $td['buynowimg'] = $td['buynowtxt'] = '';
                                                $td['filtered_auctiontype'] = $row['filtered_auctiontype'];
												
												//new change herakle 
												if(isset($ilance->GPC['list']))
												{
												$selected['list'] = $ilance->GPC['list'];
												}
												
												//print_r($selected);

                                                if ($selected['list'] == 'list')
                                                {
                                                        $td['buynow'] = $td['buynowimg'] = $td['buynowtxt'] = '';
							
							// display thumbnail
							$url = construct_seo_url('productauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							$td['sample'] = ($ilconfig['globalauctionsettings_seourls'])
								? print_item_photo($url, 'thumb', $row['project_id'])
								: print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id']);
							unset($url);
                                                        
                                                        // #### unique bid auction
                                                        if ($row['project_details'] == 'unique')
                                                        {
                                                                $td['description'] = strip_vulgar_words($row['description']);
								$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
								$td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                                $bids = $ilance->bid_lowest_unique->fetch_bid_count($row['project_id']);
								$td['bids'] = ($bids > 0)
									? '<div class="smaller blue">' . $bids . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
									: '-';
									
                                                                $td['price'] = ($selected['currencyconvert'] == 'true')
									? '<div class="black"><strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['retailprice'], $row['currencyid']) . '</strong></div>'
									: '<div class="black"><strong>' . $ilance->currency->format($row['retailprice'], $row['currencyid']) . '</strong></div>';
                                                        }
                                                        
                                                        // #### regular auction
                                                        else
                                                        {
                                                              
																if ($row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'fixed' OR $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'regular')
                                                                {
                                                                        $td['price'] = '';
                                                                        if ($row['filtered_auctiontype'] == 'regular')
                                                                        {
                                                                                // current price & buy now price
																				// murugan changes on mar 01 for show bid or buy in $td['price']
                                                                                if ($row['bids'] > 0)
                                                                                {
                                                                                        $td['price'] = ($selected['currencyconvert'] == 'true')
												? '<div class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
												: '<div class="black"><strong> Bid ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
												
                                                                                      /*  $td['price'] .= ($selected['currencyconvert'] == 'true')
												? '<div class="gray" style="padding-top:3px">' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</div>'
												: '<div class="gray" style="padding-top:3px">' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</div>';*/
                                                                                }
                                                                                else
                                                                                {
                                                                                        $td['price'] = ($selected['currencyconvert'] == 'true')
												? '<div class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
												: '<div class="black"><strong> Bid ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
												
                                                                                        $td['price'] .= ($selected['currencyconvert'] == 'true')
												? '<div class="gray" style="padding-top:3px"> Buy Now ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</div>'
												: '<div class="gray" style="padding-top:3px"> Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</div>';
                                                                                }
                                                                                
                                                                                $td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
                                                                                
                                                                                // proxy bid information
                                                                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                                                                {
                                                                                        $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
                                                                                        if ($pbit > 0)
                                                                                        {
																						//$highbidderidtest = $ilance->bid->fetch_highest_bidder($res['project_id']);
                                                                                               /* $td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' - <em>' . $phrase['_invisible'] . '</em></div>'
													: '';*/
																								// Murugan Change On feb 19 For show msg red
																								$highbid = $ilance->db->query("
																													SELECT b.user_id
																													FROM " . DB_PREFIX . "project_bids AS b,
																													" . DB_PREFIX . "proxybid AS p
																													WHERE b.project_id = '" . intval($projectid) . "'
																														AND b.project_id = p.project_id
																														AND b.user_id = p.user_id
																													ORDER BY b.bidamount DESC, p.date_added ASC
																													LIMIT 1
																											", 0, null, __FILE__, __LINE__);
																											if ($ilance->db->num_rows($highbid) > 0)
																											{
																													$res = $ilance->db->fetch_array($highbid);
																													$highbidderidtest = $res['user_id'];
																											}
																										// murugan on feb 25
																								if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
																								else
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
                                                                                        }
                                                                                        unset($pbit);
                                                                                }
                                                                        }
                                                                        else if ($row['filtered_auctiontype'] == 'fixed')
                                                                        {
                                                                                // buy now price
                                                                                $td['price'] = ($selected['currencyconvert'] == 'true')
											? '<div class="black"><strong> Buy Now ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</strong></div>'
											: '<div class="black"><strong> Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</strong></div>';
											
                                                                                $td['bids'] = '<div class="smaller blue"> Buy Now </div>';
                                                                        }
                                                                }
                                                                
                                                                // #### no buy now
                                                                else
                                                                {
                                                                         
																	    if ($ilance->bid->has_bids($row['project_id']) > 0)
                                                                        {
                                                                                $currentbid = $ilance->bid->fetch_current_bid($row['project_id'], 1);
                                                                                $td['price'] = ($selected['currencyconvert'] == 'true')
											? '<span class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $currentbid, $row['currencyid']) . '</strong></span>'
											: '<span class="black"><strong> Bid ' . $ilance->currency->format($currentbid, $row['currencyid']) . '</strong></span>';
                                                                                
                                                                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                                                                {
                                                                                         $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
                                                                                        if ($pbit > 0)
                                                                                        {
																						// $highbidderidtest = $ilance->bid->fetch_highest_bidder($res['project_id']);
                                                                                               /* $td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' - ' . $phrase['_invisible'] . '</div>'
													: '';*/
													
																					// Murugan Change On feb 19 For show msg red
																					// murugan on feb 28
																								$highbid = $ilance->db->query("
																													SELECT b.user_id
																													FROM " . DB_PREFIX . "project_bids AS b,
																													" . DB_PREFIX . "proxybid AS p
																													WHERE b.project_id = '" . intval($row['project_id']) . "'
																														AND b.project_id = p.project_id
																														AND b.user_id = p.user_id
																													ORDER BY b.bidamount DESC, p.date_added ASC
																													LIMIT 1
																											", 0, null, __FILE__, __LINE__);
																											if ($ilance->db->num_rows($highbid) > 0)
																											{
																													$res = $ilance->db->fetch_array($highbid);
																													$highbidderidtest = $res['user_id'];
																											}
																										// murugan on feb 25
																								if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
																								else
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
                                                                                        }
                                                                                        unset($pbit);
                                                                                }
                                                                                
                                                                                $td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
                                                                        }
                                                                        else 
                                                                        {
                                                                                // starting bid price
																				
																				//new changes strong herakle
																				
                                                                                $td['price'] = ($selected['currencyconvert'] == 'true')
											? '<div class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['startprice'], $row['currencyid']) . '</strong></div>'
											: '<div class="black"><strong> Bid ' . $ilance->currency->format($row['startprice'], $row['currencyid']) . '</strong></div>';
											
                                                                                $td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
                                                                        }
                                                                }
																
                                                        }
                                                }
                                                else if ($selected['list'] == 'gallery')
                                                {
												
												
												
							// display thumbnail
							$url = construct_seo_url('productauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							$td['sample'] = ($ilconfig['globalauctionsettings_seourls'])
								? print_item_photo($url, 'thumbgallery', $row['project_id'])
								: print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumbgallery', $row['project_id']);
							unset($url);
							
                                                        // #### lowest unique bid
                                                        if ($row['project_details'] == 'unique')
                                                        {
                                                                $td['description'] = strip_vulgar_words($row['description']);
								$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
                                                                $td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
                                                        	$td['bids'] = '<span class="blue">' . $ilance->bid_lowest_unique->fetch_bid_count($row['project_id']) . '&nbsp;' . $phrase['_bids_lower'] . '</span>';
                                                                $td['price'] = '<span class="black">' . $ilance->currency->format($row['retailprice'], $row['currencyid']) . '</span>';
                                                        }
                                                        
                                                        // #### regular auction
                                                        else
                                                        {
                                                                if ($row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'fixed' OR $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'regular')
                                                                {
                                                                        if ($row['filtered_auctiontype'] == 'regular')
                                                                        {
                                                                                $td['bids'] = ($row['bids'] > 0)
											? '<span class="blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue">0&nbsp;' . $phrase['_bids_lower'] . '</span>';
											
                                                                                if($row['bids'] == 0)
																				{
																					
																					$td['buynow'] = '<span class="black"> Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</span>';
																				}
																				
																				else
																				{
																				   $td['buynow'] = '<span class="black">' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</span>';
																				}
																				
																				 // proxy bid information
                                                                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                                                                {
                                                                                        $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
                                                                                        if ($pbit > 0)
                                                                                        {
																						//$highbidderidtest = $ilance->bid->fetch_highest_bidder($res['project_id']);
                                                                                               /* $td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';*/
																								// Murugan Change On feb 19 For show msg red
																								$highbid = $ilance->db->query("
																													SELECT b.user_id
																													FROM " . DB_PREFIX . "project_bids AS b,
																													" . DB_PREFIX . "proxybid AS p
																													WHERE b.project_id = '" . intval($projectid) . "'
																														AND b.project_id = p.project_id
																														AND b.user_id = p.user_id
																													ORDER BY b.bidamount DESC, p.date_added ASC
																													LIMIT 1
																											", 0, null, __FILE__, __LINE__);
																											if ($ilance->db->num_rows($highbid) > 0)
																											{
																													$res = $ilance->db->fetch_array($highbid);
																													$highbidderidtest = $res['user_id'];
																											}
																										// murugan on feb 25
																								if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
																								else
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
                                                                                        }
                                                                                        unset($pbit);
                                                                                }
																				
																				
                                                                        }
                                                                        else if ($row['filtered_auctiontype'] == 'fixed')
                                                                        {
																		
                                                                                $td['bids'] = '';
                                                                                $td['buynow'] = '<span class="black"><strong>Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</strong></span>';
                                                                        }
                                                                }
                                                                
                                                                // #### no buy now
                                                                else
                                                                {
																        if ($ilance->bid->has_bids($row['project_id']) > 0)
                                                                        {
                                                                                $currentbid = $ilance->bid->fetch_current_bid($row['project_id'], 1);
                                                                                $td['price'] = ($selected['currencyconvert'] == 'true')
											? '<span class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $currentbid, $row['currencyid']) . '</strong></span>'
											: '<span class="black"><strong> Bid ' . $ilance->currency->format($currentbid, $row['currencyid']) . '</strong></span>';
                                                                                
                                                                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                                                                {
                                                                                         $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
                                                                                        if ($pbit > 0)
                                                                                        {
																						// $highbidderidtest = $ilance->bid->fetch_highest_bidder($res['project_id']);
                                                                                               /* $td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' - ' . $phrase['_invisible'] . '</div>'
													: '';*/
													
																					// Murugan Change On feb 19 For show msg red
																					// murugan on feb 28
																								$highbid = $ilance->db->query("
																													SELECT b.user_id
																													FROM " . DB_PREFIX . "project_bids AS b,
																													" . DB_PREFIX . "proxybid AS p
																													WHERE b.project_id = '" . intval($row['project_id']) . "'
																														AND b.project_id = p.project_id
																														AND b.user_id = p.user_id
																													ORDER BY b.bidamount DESC, p.date_added ASC
																													LIMIT 1
																											", 0, null, __FILE__, __LINE__);
																											if ($ilance->db->num_rows($highbid) > 0)
																											{
																													$res = $ilance->db->fetch_array($highbid);
																													$highbidderidtest = $res['user_id'];
																											}
																										// murugan on feb 25
																								if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
																								else
																								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
													? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
													: '';
                                                                                        }
                                                                                        unset($pbit);
                                                                                }
                                                                                
                                                                                $td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
                                                                        }
                                                                        else 
                                                                        {
                                                                                // starting bid price
																				
																				//new changes strong herakle
																				
                                                                                $td['price'] = ($selected['currencyconvert'] == 'true')
											? '<div class="black"><strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['startprice'], $row['currencyid']) . '</strong></div>'
											: '<div class="black"><strong>' . $ilance->currency->format($row['startprice'], $row['currencyid']) . '</strong></div>';
											
                                                                                $td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
                                                                        }
                                                                }
                                                                
                                                              $td['price'] = '<span class="black"><strong>Bid ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
                                                        }
                                                }  
												
												                                           
                                                               
                                                // display auction checkbox "action"
                                                $td['sel'] = (!empty($_SESSION['ilancedata']['user']['userid']))
							? '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />'
							: '<input type="hidden" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';                     
							
							                     //new change herakle you item  
												
												  $check_box = $ilance->db->query("
											SELECT * FROM
											" . DB_PREFIX . "projects
											WHERE  project_id = '".$row['project_id']."'
											AND user_id='".$_SESSION['ilancedata']['user']['userid']."'
											
											");											
				                          
					                       if($ilance->db->num_rows($check_box) > 0)
					                       {
										   $owner_id = '<br><br>You own this item';
										   }
										   else
										   {
										   $owner_id = '';
										   }   
										   
										        if(isset($ilance->GPC['list']) and $ilance->GPC['list'] == 'gallery')
												$you_bid = $td['proxybit'];
												else
												$you_bid = '';
												
                                                                
                                                // is bold feature enabled?
                                                if ($row['bold'])
                                                {
                                                        $td['title'] = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('productauction', 0, $row['project_id'], htmlspecialchars_uni($row['project_title']), $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0).''.$owner_id.'<br>'.$you_bid.''
								: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '"><strong>' . htmlspecialchars_uni($row['project_title']) . '</strong></a>'.$owner_id.'<br>'.$you_bid.'';
                                                }
                                                else
                                                {
                                                        $td['title'] = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('productauction', 0, $row['project_id'], htmlspecialchars_uni($row['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0).''.$owner_id.'<br>'.$you_bid.''
								: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '">' . htmlspecialchars_uni($row['project_title']) . '</a>'.$owner_id.'<br><br>'.$you_bid.'';
                                                }
                                                                
                                                $td['class'] = ($row['highlite']) ? $ilconfig['productupsell_highlightcolor'] : (($row_count % 2) ? 'alt1' : 'alt1');
                                                $td['timeleft'] = '<strong>' . $ilance->auction->auction_timeleft($row['project_id'], $td['class'], 'center') . '</strong>';
                                                $td['icons'] = $ilance->auction->auction_icons($row['project_id'], $row['user_id']);
						
						if ($row['ship_method'] == 'localpickup')
						{
							// murugan changes on jan 13
							//td['shipping'] = '<div class="smaller gray">' . $phrase['_local_pickup'] . '</div>';
							$td['shipping'] = '';
						}
						else
						{
							
							$shipping = array();
							for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
							{
								if ($row['freeshipping_' . $i] == 0 AND $row['ship_fee_' . $i] > 0)
								{
									$shipping[] = ($row['ship_fee_' . $i] + $row['ship_handlingfee']);
								}
							}
							// murugan changes on jan 13
							//$td['shipping'] = '<div class="smaller gray">' . fetch_lowest_shipping_cost($shipping, true, $row['project_id']) . '</div>';
							$td['shipping'] = '';
							unset($shipping);
						}
						
                                                $td['views'] = $row['views'];
                        
                                                ($apihook = $ilance->api('search_results_products_loop')) ? eval($apihook) : false;
                                               
                                                $search_results_rows[] = $td;
												
                                                $row_count++;
                                        }
										
                                }
                              
											
                                $show['no_rows_returned'] = false;
                        }
                        else
                        {
                                $show['no_rows_returned'] = true;
                        }
						$series = $ilance->GPC['series'];
						// murugan changes on feb 17 for featured auction list	 
                        // #### featured spotlight auction listings ############
                        $featuredserviceauctions = $featuredproductauctions = array();
						$featuredserviceauctions = ($show['mode_service']) ? $ilance->auction->fetch_featured_auctions('service', 4, 1, $cid, $keyword_text, false, $excludelist) : '';
                        //$featuredproductauctions = ($show['mode_product']) ? $ilance->auction->fetch_featured_auctions('product', 4, 1, $cid, $keyword_text, false, $excludelist,$series) : '';
						$featuredproductauctions = ($show['mode_product']) ? $ilance->auction->fetch_featured_auctions_new('product', 4, 1, $cid, $keyword_text, false, $excludelist,$series) : '';
                         
                        // #### BUILD OUR PAGNATOR #############################
                        $prevnext = print_pagnation($number, fetch_perpage(), intval($ilance->GPC['page']), $counter, $scriptpage);
                        
                        // #### PRINT OUR SEARCH RESULTS TABLE #################
                        $search_results_table = print_search_results_table($search_results_rows, $project_state, $prevnext);
                        
                        $keywords = (!empty($ilance->GPC['q'])) ? un_htmlspecialchars($ilance->GPC['q']) : '';
                       
                        // #### fewer keywords search ##########################
                        $fewer_keywords = print_fewer_keywords_search($keywords_array, $ilance->GPC['mode'], $number);
                        
                        // #### category budget ################################
                        $budget = isset($ilance->GPC['budget']) ? intval($ilance->GPC['budget']) : '';
                        
                        // $budgetfilter = $ilance->auction_post->print_budget_logic_type_links($cid, $cattype = 'service', $budget);
                        $budgettemp = $ilance->auction_post->print_budget_logic_type_js($cid, $ilance->GPC['mode'], $budget);
                        if (isset($show['budgetgroups']) AND $show['budgetgroups'] AND is_array($budgettemp))
                        {
                                $budget_slider_1 = $budgettemp[0];
                                $budget_slider_2 = $budgettemp[1];
                        }
                        unset($budgettemp);
                        
                        if (isset($show['mode_service']) AND $show['mode_service'] OR isset($show['mode_providers']) AND $show['mode_providers'])
                        {
                                $v3left_nav = $ilance->template->print_left_nav('service', $cid, $dosubcats = 1, $displayboth = 0, $ilconfig['globalfilters_enablecategorycount'], true);
                        }
                        else 
                        {
                             
							    // pre-populate from price and to price field inputs for left nav search menu
                                $fromprice = isset($ilance->GPC['fromprice']) ? sprintf("%01.2f", $ilance->GPC['fromprice']) : '';
                                $toprice = isset($ilance->GPC['toprice']) ? sprintf("%01.2f", $ilance->GPC['toprice']) : '';
                                
                                $v3left_nav = $ilance->template->print_left_nav('product', $cid, $dosubcats = 1, $displayboth = 0, $ilconfig['globalfilters_enablecategorycount'], true);
							
                        }
                        
                        // #### SAVE AS FAVORITE SEARCH OPTION #################
                        if ($ilconfig['savedsearches'] AND !empty($favtext))
                        {
                                // build search request parameters
                                $favorites = array();
                                foreach ($ilance->GPC AS $search => $option)
                                {
                                        if ($search != 'submit' AND $search != 'search' AND $search != 'page' AND $search != 'sef')
                                        {
                                                $favorites[] = array($search => $option);
                                        }
                                }
                                if (!empty($favorites) AND is_array($favorites))
                                {
                                        $encrypt = serialize($favorites);
                                        $encrypt = urlencode($encrypt);
                                }
                                
                                $favoritesearchurl = $encrypt;
                                $favtext = ilance_htmlentities($favtext);
                        }
			
			// ####  build our category breadcrumb navigator
                        if ($show['mode_service'])
                        {
                                $sortmode = $mode = 'service';
                                $navcrumb = array();
                                $ilance->categories->breadcrumb($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng']);
                                if (empty($cid) OR $cid == 0 OR $cid == '')
                                {
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['categoryidentifier'])] = $phrase['_categories'];
						//$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['servicecatmapidentifier'])] = $phrase['_services'];
						$navcrumb[HTTP_SERVER . "$ilpage[search]?tab=0"] = $phrase['_search'];
						$navcrumb[""] = $phrase['_services'];
					}
					else
					{
						$navcrumb["$ilpage[main]?cmd=categories"] = $phrase['_categories'];
						//$navcrumb["$ilpage[rfp]?cmd=listings"] = $phrase['_services'];
						$navcrumb["$ilpage[search]?tab=0"] = $phrase['_search'];
						$navcrumb[""] = $phrase['_services'];
					}
                                }
                        }
                        else if ($show['mode_product'])
                        {
                                $sortmode = $mode = 'product';
                                /*$navcrumb = array();
                                $ilance->categories->breadcrumb($cid, 'productcatmap', $_SESSION['ilancedata']['user']['slng']);
                                if (empty($cid) OR $cid == 0 OR $cid == '')
                                {
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['categoryidentifier'])] = $phrase['_categories'];
						$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['productcatmapidentifier'])] = $phrase['_buy'];
						$navcrumb[HTTP_SERVER . "$ilpage[search]?tab=1"] = $phrase['_search'];
						$navcrumb[""] = $phrase['_products'];
					}
					else
					{
						$navcrumb["$ilpage[main]?cmd=categories"] = $phrase['_categories'];
						$navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
						$navcrumb["$ilpage[search]?tab=1"] = $phrase['_search'];
						$navcrumb[""] = $phrase['_products'];
					}
                                }*/
                        }
                        
                        $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
                        $sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', $sortmode);
                        $city = isset($ilance->GPC['city']) ? handle_input_keywords($ilance->GPC['city']) : '';
                        $state = isset($ilance->GPC['state']) ? handle_input_keywords($ilance->GPC['state']) : '';
                        $zip_code = isset($ilance->GPC['zip_code']) ? handle_input_keywords($ilance->GPC['zip_code']) : '';
                        $radiuszip = isset($ilance->GPC['radiuszip']) ? handle_input_keywords($ilance->GPC['radiuszip']) : '';
                        
			$hiddenfields = print_hidden_fields(false, array('searchid','sef','cid','buynow','sort','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','q','page'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
                        $hiddenfields_leftnav = print_hidden_fields(false, array('searchid','sef','exactname','searchuser','budget','country','auctiontype','buynow','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','page','radius','radiuscountry','radiuszip'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
			
                        ($apihook = $ilance->api('search_results_auctions_end')) ? eval($apihook) : false;                     
                       
                        // #### DISPLAY SEARCH RESULTS VIA XML #################
                        if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'xml')
                        {
                                if (!empty($search_results_rows) AND is_array($search_results_rows))
                                {
                                        $ilance->xml = construct_object('api.xml');
                                
                                        $xml = $ilance->xml->search_to_xml($search_results_rows, false);
                                        echo $xml;
                                }
                                exit();
                        }
                        
                        // #### DISPLAY SEARCH RESULTS VIA SERIALIZED ARRAY ####
                        else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'array')
                        {
                                if (!empty($search_results_rows) AND is_array($search_results_rows))
                                {
                                        echo urlencode(serialize($search_results_rows));
                                }
                                exit();
                        }
                        
                        // #### DISPLAY SEARCH RESULTS TEMPLATE ################
                        else
                        {
				// #### init our budget range slider ###################
				if (!empty($budgetfilter) OR isset($show['budgetgroups']) AND $show['budgetgroups'])
				{
					$onload .= "init_budgetSlider(); ";
					$onload .= (isset($ilance->GPC['budget'])) ? "set_budgetSlider('" . intval($ilance->GPC['budget']) . "'); " : "";
				}
				
				// attempt to correct the spelling for the user if applicable (not in use at the moment)
                                $didyoumean = print_did_you_mean($keyword_text, $mode);
				
				// if we're a guest and we don't have the region modal cookie let's ask for it
				$cookieregion = (!empty($_COOKIE[COOKIE_PREFIX . 'region'])) ? $_COOKIE[COOKIE_PREFIX . 'region'] : '';
				$full_country_pulldown = construct_country_pulldown(0, $cookieregion, 'region', true, '', false, true, true);
					
				if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal']))
				{
					//$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow(); ';
				
					// don't ask this guest for region info via popup modal for 3 days
					set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
				}
				
                                $ilance->template->fetch('main', 'search_results.html',2);
                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                $ilance->template->parse_loop('main', array('featuredproductauctions','featuredserviceauctions'));
                                $ilance->template->parse_if_blocks('main');
                                
                                $pprint_array = array('wysiwyg_area','searchurl','clear_budgetrange','pennyauctions','clear_local','clear_distance','clear_searchuser_url','country','leftnav_options','leftnav_currencies','clear_currencies','clear_options','sort','clear_bidrange','clear_listtype','leftnav_buyingformats','showallurl','clear_searchuser','clear_price','clear_region','leftnav_regions','full_country_pulldown','didyoumean','searchuser','search_bidrange_pulldown_service','search_bidrange_pulldown_product','search_country_pulldown_product','search_country_pulldown_service','search_radius_country_pulldown_product','search_radius_country_pulldown_service','budget_slider_1','budget_slider_2','favtext','profilebidfilters','fewer_keywords','budgetfilter','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','hiddenfields','fromprice','toprice','search_results_table','sortpulldown','keywords','favoritesearchurl','keywords','search_product_category_pulldown','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','input_style','search_country_pulldown','search_country_pulldown2','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','input_style','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','login_include_admin','ilanceversion','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                
                                ($apihook = $ilance->api('search_results_auctions_template')) ? eval($apihook) : false;
         
                                $ilance->template->pprint('main', $pprint_array);
                                exit();
                        }
                        
                        break;
                }
            
                // #### SEARCHING SERVICE PROVIDERS ############################
                case 'experts':
                {
                        $show['mode_service'] = false;
                        $show['mode_providers'] = true;
			$mode = 'experts';
                        $text = $favtext = $subcatname = $keyword_text = '';
                        $mode_buynow = 0;
                        
                        // #### page we're on ##################################
                        $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']); 
                        
                        $sqlquery['groupby'] = "GROUP BY user_id";
			$sqlquery['orderby'] = "ORDER BY u.rating DESC";
                        $sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * fetch_perpage()) . ',' . fetch_perpage();
                        
			// #### accepted display order sorting #################
                        $acceptedsort = array('01','02','11','12','21','22','31','41','42','51','52','61','62','71','72','81','82','91','92','101','102','111','112');
			
                        // #### put our keyword text in a temporary variable ###
                        if (!empty($ilance->GPC['q']))
                        {
                                $keyword_text = $ilance->GPC['q'];
                                $keyword_text = $ilance->common->xss_clean($keyword_text);
                        }
                            
                        // #### handle keywords and add new words/phrases to our search index table
                        if (!empty($keyword_text))
                        {
                                handle_search_keywords($keyword_text, 'experts');
                        }
                            
                        // #### EXCLUDED EXPERTS SQL QUERY BUILDER #############
                        // subscription permissions checkup for 'searchresults'
                        // this will build a list of user id's not to include in the search
                        // this will also build a list of user id's that do not wish to be listed in search
                        // the 'u.' represents the table field identifier (example: u.user_id vs no identifier: user_id) - useful for TABLE JOINS
                        //$sqlquery['userquery'] = build_expert_search_exclusion_sql('u.', 'searchresults');
			$sqlquery['userquery'] = '';
                        
                        // #### BEGIN SEARCH SQL QUERY #########################
                        $sqlquery['timestamp'] = $sqlquery['projectstatus'] = $sqlquery['projectdetails'] = $sqlquery['projectstate'] = $sqlquery['pricerange'] = '';
                        $sqlquery['fields'] = "u.user_id, u.username, u.city, u.state, u.zip_code, u.country, u.status, u.serviceawards, u.rating, u.score, u.profileintro, p.cid";
                        $sqlquery['from'] = "FROM " . DB_PREFIX . "users u";
                        $sqlquery['leftjoin'] = "
                                LEFT JOIN " . DB_PREFIX . "profile_categories p ON u.user_id = p.user_id
                                LEFT JOIN " . DB_PREFIX . "portfolio o ON p.user_id = o.user_id
                                LEFT JOIN " . DB_PREFIX . "attachment l ON u.user_id = l.user_id
                                LEFT JOIN " . DB_PREFIX . "locations c ON u.country = c.locationid";
                        
                        // hook below is useful for changing any specifics from the above                        
                        ($apihook = $ilance->api('search_experts_query_fields')) ? eval($apihook) : false;

                        $sqlquery['genrequery'] = $sqlquery['keywords'] = $keyword_formatted = '';
                        
                        // #### handle keywords entered by user ################
                        $keywords_array = array();
                        if (isset($keyword_text) AND !empty($keyword_text))
                        {
                                // #### FULLTEXT MODE ##########################
                                if ($ilconfig['fulltextsearch'])
                                {
                                        $keyword_formatted .= '<strong>' . $keyword_text . '</strong>, ';
                                        $keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
                                        $keyword_formatted_favtext = $keyword_formatted;
                                        $sqlquery['keywords'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios']) ? "AND MATCH (u.username, u.profileintro, o.description, o.caption, c.location_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)" : "AND MATCH (u.username, u.profileintro, c.location_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)";
                                }
                                
                                // #### NON-FULLTEXT MODE ######################
                                else
                                {
                                        // #### splits spaces and commas into array
                                        $keyword_text = preg_split("/[\s,]+/", trim($keyword_text));
                                        
                                        // #### multiple keywords detected #####
                                        if (sizeof($keyword_text) > 1)
                                        {

                                                $sqlquery['keywords'] .= 'AND (';
                                                for ($i = 0; $i < sizeof($keyword_text); $i++)
                                                {
                                                        $keyword_formatted .= '<strong>' . $keyword_text[$i] . '</strong>, ';
                                                        $sqlquery['keywords'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios']) ? " u.username LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR o.description LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR o.caption LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR " : " u.username LIKE '%" . $ilance->db->escape_string($keyword_text[$i]) . "%' OR ";
                                                }				
                                                $sqlquery['keywords'] = mb_substr($sqlquery['keywords'], 0, -4) . ')';
                                                $keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
                                                $keyword_formatted_favtext = $keyword_formatted;
                                        }
                                        
                                        // #### single keyword #################
                                        else
                                        {
                                                $keyword_formatted = '<strong>' . $keyword_text[0] . '</strong>';
                                                $keyword_formatted_favtext = '<strong>' . $keyword_text[0] . '</strong>';
                                                $sqlquery['keywords'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios']) ? "AND (u.username LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%' OR o.description LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%' OR o.caption LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%') " : "AND (u.username LIKE '%" . $ilance->db->escape_string($keyword_text[0]) . "%') ";
                                        }
                                }
                        }
                        
                        // #### categories #####################################
                        $sqlquery['categories'] = '';
                        if (empty($ilance->GPC['cid']) OR (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] == 0))
                        {
                                // we are here because the searcher chose no categories to search within
                                $cid = 0;
                                $subcategorylist = $ilance->categories->fetch_children_ids('all', 'service');
                                $count = count(explode(',', $subcategorylist));
                                
                                if (!empty($subcategorylist) AND $count > 1)
                                {
                                        $sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                                }
                                
                                handle_search_verbose('<strong><span class="gray">' . $phrase['_in'] . '</span> <span class="black">' . $phrase['_best_matching_categories'] . '</span></strong>, ');
                                handle_search_verbose_save($phrase['_category'] . ': <strong>' . $phrase['_best_matching'] . '</strong>, ');
                        }
                        else
                        {
                                $subcategorylist = $subcatname = '';
                                $cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : '0';
                                
                                // category visibility checkup
                                if ($ilance->categories->visible($_SESSION['ilancedata']['user']['slng'], 'service', $cid) == 0)
                                {
                                        $area_title = $phrase['_category_not_available'];
                                        $page_title = SITE_NAME . ' - ' . $phrase['_category_not_available'];
                                        
                                        print_notice($phrase['_invalid_category'], $phrase['_this_category_is_currently_unavailable_please_choose_a_different_category'], $ilpage['search'], $phrase['_search']);
                                        exit();
                                }
                                
                                if ($cid > 0)
                                {
                                        $subcatname .= ', <span class="black">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $cid) . '</span>';
                                        $ilance->categories->fetch = $ilance->categories->catservicefetch;
                                        $childrenids = $ilance->categories->fetch_children_ids($cid, 'service');
                                        $subcategorylist .= $cid . ',' . $childrenids;
                                        if (!empty($subcatname))
                                        {
                                                $removeurl = rewrite_url($scriptpage, $remove = 'cid=' . $cid);
                                                
                                                handle_search_verbose('<strong><span class="gray">' . $phrase['_in'] . '</span> <span class="black">' . mb_substr($subcatname, 1) . '</span></strong>, ');
                                                handle_search_verbose_save($phrase['_categories'] . ': <strong>' . mb_substr($subcatname, 1) . '</strong>, ');
                                        }
                                        
                                        $sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                                        
                                        // update category view count
                                        add_category_viewcount($cid);
                                }	    
                        }
                           
			if (isset($ilance->GPC['sort']) AND !empty($ilance->GPC['sort']) AND in_array($ilance->GPC['sort'], $acceptedsort))
                        {
                                $sphrase = fetch_sort_options('experts');
                                $tphrase = $sphrase[$ilance->GPC['sort']];
				
				$sortconditions = sortable_array_handler('experts');
				$sqlquery['orderby'] = 'ORDER BY ' . $sortconditions[$ilance->GPC['sort']]['field'] . ' ' . $sortconditions[$ilance->GPC['sort']]['sort'] . ' ' . $sortconditions[$ilance->GPC['sort']]['extra'];
				
                                //handle_search_verbose('<span class="black">' . $phrase[$tphrase] . '</span>, ');
                                //handle_search_verbose_save($phrase['_display_order'] . ': <strong>' . $phrase[$tphrase] . '</strong>, ');
                                
                                unset($sphrase, $tphrase);
                        }
			
			// #### default sort display order if none selected ####
                        else
                        {
				$ilance->GPC['sort'] = '52';
				$sqlquery['orderby'] = "ORDER BY u.rating DESC";
				
                                $sphrase = fetch_sort_options('experts');
                                $tphrase = $sphrase['52'];
                                
                                //handle_search_verbose_save($phrase['_display_order'] . ': <strong>' . $phrase[$tphrase] . '</strong>, ');
                                
                                unset($sphrase, $tphrase);
                        }
			
			// #### hold display order for modals as sort is removed due to main search bar above listings
			$sort = $ilance->GPC['sort'];
                        
                        // #### search options: is user hiding their own results?
                        $sqlquery['hidequery'] = '';
                        if ($selected['hidelisted'] == 'true')
                        {
                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
                                        $sqlquery['hidequery'] = "AND (u.user_id != '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";
                                }
                                
                                handle_search_verbose('<span class="black"><strong>' . $phrase['_excluding_results_that_are_listed_by_me'] . '</strong></span>, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_excluding_results_that_are_listed_by_me_uppercase'] . '</strong>, ');
                        }
                         
                        // #### user searching keywords within portfolio titles and descriptions?
                        if (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios'])
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'portfolios=' . $ilance->GPC['portfolios']);
                                
                                handle_search_verbose('<span class="black"><strong>' . $phrase['_including_service_experts_portfolios'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_titles_and_descriptions_within_portfolios'] . '</strong>, ');
                        }
                        
                        $navcrumb = array();
                        $ilance->categories->breadcrumb($cid, 'experts', $_SESSION['ilancedata']['user']['slng']);
                        if (empty($cid) OR $cid == 0 OR $cid == '')
                        {
                                $navcrumb[""] = $phrase['_providers'];
                        }
                                  
                        // #### filtering search via number of service auction awards
                        $sqlquery['options'] = '';
                        if (!empty($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] != '-1')
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'projectrange=' . $ilance->GPC['projectrange']);
                                switch ($ilance->GPC['projectrange'])
                                {
                                        case '1':
                                        {
                                                $sqlquery['options'] .= "AND (u.serviceawards < 10) ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_less_than_ten_awards'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_awards'] . ': <strong>' . $phrase['_with_less_than_ten_awards'] . '</strong>, ');
                                                break;
                                        }
                                        case '2':
                                        {
                                                $sqlquery['options'] .= "AND (u.serviceawards BETWEEN 10 AND 20) ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_between_ten_and_twenty_awards'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_awards'] . ': <strong>' . $phrase['_between_ten_and_twenty_awards'] . '</strong>, ');
                                                break;        
                                        }                                    
                                        case '3':
                                        {
                                                $sqlquery['options'] .= "AND (u.serviceawards > 20) ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_more_than_twenty_awards'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_awards'] . ': <strong>' . $phrase['_with_more_than_twenty_awards'] . '</strong>, ');
                                                break;       
                                        }
                                }
                        }
                        else
                        {
                                //handle_search_verbose($phrase['_with_any_number_of_awards'] . ', ');
                                //handle_search_verbose_save($phrase['_awards'] . ': <strong>' . $phrase['_any'] . '</strong>, ');
                        }
			
			
                        $ilance->GPC['projectrange'] = isset($ilance->GPC['projectrange']) ? $ilance->GPC['projectrange'] : '';
			$clear_award = '';
                        $leftnav_awardrange = print_award_range_pulldown($ilance->GPC['projectrange'], 'projectrange', 'projectrange', 'links');
			
                        // #### search filter via rating? ######################
                        if (!empty($ilance->GPC['rating']) AND $ilance->GPC['rating'] != '0')
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'rating=' . $ilance->GPC['rating']);
                                switch ($ilance->GPC['rating'])
                                {
                                        case '5':
                                        {
                                                $sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_5_stars_value'] . "') ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_at_least_a_five_star_rating'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_rating'] . ': <strong>' . $phrase['_with_at_least_a_five_star_rating'] . '</strong>, ');
                                                break;        
                                        }
                                        case '4':
                                        {
                                                $sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_4_stars_value'] . "') ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_at_least_a_four_star_rating'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_rating'] . ': <strong>' . $phrase['_with_at_least_a_four_star_rating'] . '</strong>, ');
                                                break;        
                                        }
                                        case '3':
                                        {
                                                $sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_3_stars_value'] . "') ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_at_least_a_three_star_rating'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_rating'] . ': <strong>' . $phrase['_with_at_least_a_three_star_rating'] . '</strong>, ');
                                                break;        
                                        }
                                        case '2':
                                        {
                                                $sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_2_stars_value'] . "') ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_at_least_a_two_star_rating'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_rating'] . ': <strong>' . $phrase['_with_at_least_a_two_star_rating'] . '</strong>, ');
                                                break;        
                                        }
                                        case '1':
                                        {
                                                $sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_1_stars_value'] . "') ";
                                                
                                                handle_search_verbose('<span class="black"><strong>' . $phrase['_with_at_least_a_one_star_rating'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_rating'] . ': <strong>' . $phrase['_with_at_least_a_one_star_rating'] . '</strong>, ');
                                                break;        
                                        }
                                }
                        }
                        else
                        {
                                //handle_search_verbose($phrase['_with_any_ratings'] . ', ');
                                //handle_search_verbose_save($phrase['_rating'] . ': <strong>' . $phrase['_with_any_ratings'] . '</strong>, ');
                        }
			
			$ilance->GPC['rating'] = isset($ilance->GPC['rating']) ? $ilance->GPC['rating'] : '';
			$clear_rating = '';
                        $leftnav_ratingrange = print_rating_range_pulldown($ilance->GPC['rating'], 'rating', 'rating', 'links');
			
			// #### search filter via feedback rating? #############
                        if (!empty($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] != '0')
                        {
                                $removeurl = rewrite_url($scriptpage, 'feedback=' . $ilance->GPC['feedback']);
                                switch ($ilance->GPC['feedback'])
                                {
                                        case '5':
                                        {
                                                $sqlquery['options'] .= "AND (u.feedback >= '95') ";
                                                
                                                handle_search_verbose('<span class="black"><strong><span class="gray">' . $phrase['_feedback'] . ':</span> ' . $phrase['_above_95_positive'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_feedback'] . ': <strong>' . $phrase['_above_95_positive'] . '</strong>, ');
                                                break;        
                                        }
                                        case '4':
                                        {
                                                $sqlquery['options'] .= "AND (u.feedback >= '90') ";
                                                
                                                handle_search_verbose('<span class="black"><strong><span class="gray">' . $phrase['_feedback'] . ':</span> ' . $phrase['_above_90_positive'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_feedback'] . ': <strong>' . $phrase['_above_90_positive'] . '</strong>, ');
                                                break;        
                                        }
                                        case '3':
                                        {
                                                $sqlquery['options'] .= "AND (u.feedback >= '85') ";
                                                
                                                handle_search_verbose('<span class="black"><strong><span class="gray">' . $phrase['_feedback'] . ':</span> ' . $phrase['_above_85_positive'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_feedback'] . ': <strong>' . $phrase['_above_85_positive'] . '</strong>, ');
                                                break;        
                                        }
                                        case '2':
                                        {
                                                $sqlquery['options'] .= "AND (u.feedback >= '75') ";
                                                
                                                handle_search_verbose('<span class="black"><strong><span class="gray">' . $phrase['_feedback'] . ':</span> ' . $phrase['_above_75_positive'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_feedback'] . ': <strong>' . $phrase['_above_75_positive'] . '</strong>, ');
                                                break;        
                                        }
                                        case '1':
                                        {
                                                $sqlquery['options'] .= "AND (u.feedback >= '50') ";
                                                
                                                handle_search_verbose('<span class="black"><strong><span class="gray">' . $phrase['_feedback'] . ':</span> ' . $phrase['_above_50_positive'] . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                                handle_search_verbose_save($phrase['_feedback'] . ': <strong>' . $phrase['_above_50_positive'] . '</strong>, ');
                                                break;        
                                        }
                                }
                        }
                        else
                        {
                                //handle_search_verbose($phrase['_with_any_ratings'] . ', ');
                                //handle_search_verbose_save($phrase['_feedback'] . ': <strong>' . $phrase['_any'] . '</strong>, ');
                        }
			
			$ilance->GPC['feedback'] = isset($ilance->GPC['feedback']) ? $ilance->GPC['feedback'] : '';
			$clear_feedback = '';
			$leftnav_feedbackrange = print_feedback_range_pulldown($ilance->GPC['feedback'], 'feedback', 'feedback', 'links');
                        
			// #### search via country #############################
			$sqlquery['location'] = $country = $countryid = $countryids = '';
			$removeurlcountry = $php_self;
			if (!empty($ilance->GPC['country']))
                        {
				// #### populate proper country id #############
				$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
				$country = $ilance->GPC['country'];
				
				// #### populate regional information ##########
				$ilance->GPC['region'] = fetch_region_title_by_countryid($countryid);
				$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
				$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
                                $removeurlcountry = rewrite_url($php_self, 'country=' . urlencode($ilance->GPC['country']));
                                $sqlquery['location'] .= "AND (u.country = '" . intval($countryid) . "') ";
                        }
			else if (!empty($ilance->GPC['countryid']))
			{
				// #### populate proper country name ###########
				$countryid = $ilance->GPC['countryid'];
				$ilance->GPC['country'] = print_country_name(intval($countryid), $_SESSION['ilancedata']['user']['slng'], $shortform = false);
				$country = $ilance->GPC['country'];
				
				// #### populate regional information ##########
				$ilance->GPC['region'] = fetch_region_title_by_countryid($countryid);
				$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
				$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
				
                                $removeurlcountry = rewrite_url($php_self, 'countryid=' . urlencode($countryid));
                                $sqlquery['location'] .= "AND (u.country = '" . intval($countryid) . "') ";
			}
			
			// #### region selector ################################
			$region = (isset($ilance->GPC['region']) AND !empty($ilance->GPC['region'])) ? $ilance->GPC['region'] : '';
			$regiontype = isset($ilance->GPC['regiontype']) AND !empty($ilance->GPC['regiontype']) ? intval($ilance->GPC['regiontype']) : '';
			$regionname = '';
			
			// #### check if our selected region contains a country id
			if (strrchr($region, '.'))
			{
				$regtemp = explode('.', $region);
				if (!empty($regtemp[0]) AND !empty($regtemp[1]))
				{
					$regionname = fetch_region_title($regtemp[0]);
					
					// #### populate our selected country via special region type url
					$countryid = $regtemp[1];
					$ilance->GPC['country'] = print_country_name(intval($countryid), $_SESSION['ilancedata']['user']['slng'], false);
					
					// #### build our sql country region query 
					$sqlquery['location'] = "AND (u.country = '" . intval($countryid) . "') ";
				}
				else if (!empty($regtemp[0]))
				{
					$regionname = fetch_region_title($regtemp[0]);	
				}
				unset($regtemp);
			}
			else
			{
				$regionname = fetch_region_title($region);
				$countryids = fetch_country_ids_by_region($regionname);
				$sqlquery['location'] = (!empty($countryids)) ? "AND (FIND_IN_SET(u.country, '" . $countryids . "')) " : "";
			}
			
			// #### link to clear region from left nav menu header
			$clear_region = '';
			if (!empty($regionname))
			{
				$removeurl = rewrite_url($php_self, 'region=' . $region);
				$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
				$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
				$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
				$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
				$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
				$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
				
				$clear_region = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
				
				handle_search_verbose('<span class="gray"><!--<strong>' . $phrase['_region'] . ': --><span class="black"><strong>' . $regionname . '</strong></span></strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');                                                
				handle_search_verbose_save($phrase['_region'] . ': <strong>' . $regionname . '</strong>, ');
			}
			
			$leftnav_regions = print_regions('', $region, $_SESSION['ilancedata']['user']['slng'], '', 'links');
			
			// #### finalize country verbose text so it's placed after the region
			if ($countryid > 0)
			{
				handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_country'] . ':</span> --><strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_country'] . ': <strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong>, ');
			}
			
			// #### search via radius ##############################
                        $show['radiussearch'] = false;
                        $sqlquery['radius'] = $clear_distance = '';
			if ($ilconfig['globalserver_enabledistanceradius'] AND !empty($ilance->GPC['radiuszip']) AND $countryid > 0)
                        {
                                $show['radiussearch'] = true;
				
                                // user supplied a radius.  which country are we trying to do a radius search on?
                                $radiuscountryid = intval($countryid);

				$removeurl = rewrite_url($php_self, 'radiuszip=' . urlencode($ilance->GPC['radiuszip']));
				$ilance->GPC['radiusstate'] = '';
                                $ilance->GPC['radiuszip'] = mb_strtoupper(trim($ilance->GPC['radiuszip']));
                                $ilance->GPC['radius'] = (isset($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0) ? intval($ilance->GPC['radius']) : '';
                                $removeurl = rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']);
				
				// #### build sql to fetch zips in range of zip code entered by user for the viewing region
				if (!empty($ilance->GPC['radius']))
				{
					$radiusresult = $ilance->distance->fetch_zips_in_range('users u', 'u.zip_code', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = true, $radiusjoin = false);
					if (!empty($radiusresult) AND is_array($radiusresult) AND count($radiusresult) > 1)
					{
						// the proper zipcode + country id was selected..
						$sqlquery['leftjoin'] .= $radiusresult['leftjoin'];
						$sqlquery['fields'] .= $radiusresult['fields'];
						$sqlquery['radius'] = $radiusresult['condition'];
	
						$zipcodesrange = $ilance->distance->fetch_zips_in_range('users u', 'u.zip_code', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = true);
						$sqlquery['radius'] .= (isset($zipcodesrange) AND is_array($zipcodesrange)) ? $zipcodesrange['condition'] : '';
						
						$zipcodecityname = $ilance->distance->fetch_zips_in_range('users u', 'u.zip_code', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = false, $fetchcityonly = true);
	
						handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_radius'] . ':</span> --><strong>' . number_format($ilance->GPC['radius']) . ' ' . $phrase['_mile_radius_from'] . ' ' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : (!empty($zipcodecityname) ? $zipcodecityname . ', ' : '')) . handle_input_keywords($ilance->GPC['radiuszip']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
						handle_search_verbose_save($phrase['_radius'] . ': <strong>' . number_format($ilance->GPC['radius']) . '</strong> ' . $phrase['_mile_radius_from'] . ' ' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : '') . handle_input_keywords($ilance->GPC['radiuszip']) . ', ');
					}
				}
				
				$clear_distance = ((!empty($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0)
					? '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>'
					: '');
				
				if ($ilconfig['globalserver_enabledistanceradius'])
				{
					$acceptedsort2 = array('121','122');
					$acceptedsort = array_merge($acceptedsort, $acceptedsort2);
					unset($acceptedsort2);
				}
				/*if (!empty($ilance->GPC['sort']) AND in_array($ilance->GPC['sort'], $acceptedsort))
				{
					$sortconditions = sortable_array_handler('experts');
					$sqlquery['orderby'] = 'ORDER BY ' . $sortconditions[$ilance->GPC['sort']]['field'] . ' ' . $sortconditions[$ilance->GPC['sort']]['sort'] . ' ' . $sortconditions[$ilance->GPC['sort']]['extra'];
				}*/
                        }
                        
			// #### does user search in cities? ####################
			$clear_local = $removeurl_local = '';
			$removeurl = $php_self;
			if (!empty($ilance->GPC['city']) AND !empty($ilance->GPC['country']))
			{
				// does user enter a city in search?
				$removeurl = rewrite_url($scriptpage, 'city=' . $ilance->GPC['city']);
				$removeurl_local = rewrite_url($removeurl, 'city=' . $ilance->GPC['city']);

				
				$ilance->GPC['city'] = ucfirst(trim($ilance->GPC['city']));
				$sqlquery['location'] .= "AND (u.city LIKE '%" . $ilance->db->escape_string($ilance->GPC['city']) . "%') ";
				
				handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_city'] . ':</span> --><strong>' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_city'] . ': <strong>' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</strong>, ');
			}
			    
			// #### does user search in state or provinces? ########
			if (!empty($ilance->GPC['state']) AND !empty($ilance->GPC['country']))
			{
				// does user enter a city in search?
				$removeurl = rewrite_url($scriptpage, 'state=' . $ilance->GPC['state']);
				$removeurl_local = rewrite_url($removeurl_local, 'state=' . $ilance->GPC['state']);
				
				$ilance->GPC['state'] = ucfirst(trim($ilance->GPC['state']));
				$sqlquery['location'] .= "AND (u.state LIKE '%" . $ilance->db->escape_string($ilance->GPC['state']) . "%') ";
				
				handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_state_or_province'] . ':</span> --><strong>' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</strong></span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_state'] . ': <strong>' . ucwords($ilance->GPC['state']) . '</strong>, ');
			}
			    
			// #### does user search in zip codes? #################
			if (!empty($ilance->GPC['zip_code']) AND !empty($ilance->GPC['country']))
			{
				$removeurl = rewrite_url($scriptpage, 'zip_code=' . $ilance->GPC['zip_code']);
				$removeurl_local = rewrite_url($removeurl_local, 'zip_code=' . $ilance->GPC['zip_code']);
				
				$ilance->GPC['zip_code'] = mb_strtoupper(trim($ilance->GPC['zip_code']));
				$distanceresult = $ilance->distance->fetch_sql_as_distance($ilance->GPC['zip_code'], $ilance->GPC['country'], 'u.zip_code');
				if (is_array($distanceresult))
				{
				        $sqlquery['leftjoin'] .= $distanceresult['leftjoin'];
				        $sqlquery['fields'] .= $distanceresult['fields'];
				}
				
				$sqlquery['location'] .= "AND (u.zip_code LIKE '%" . $ilance->db->escape_string(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zip_code'])))) . "%') ";
				
				handle_search_verbose('<span class="black"><!--' . $phrase['_zip_code'] . ': --><strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
				handle_search_verbose_save($phrase['_zip_slash_postal_code'] . ': <strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong>, ');
			}
			
			$clear_local = (!empty($removeurl_local)) ? '<a href="' . $removeurl_local . '" rel="nofollow">' . $phrase['_clear'] . '</a>' : '';
			unset($removeurl_local);
                        
                        if (is_array($selected['expertselected']) AND (!empty($_SESSION['ilancedata']['user']['postalzip']) OR !empty($ilance->GPC['zip_code']) OR !empty($ilance->GPC['radiuszip'])) AND in_array('distance', $selected["expertselected"]))
                        {
                                $show['distancecolumn'] = 1;
                        }
                        
                        // #### show only with active profile logos ############
                        if (isset($ilance->GPC['images']) AND $ilance->GPC['images'])
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'images=' . $ilance->GPC['images']);
                                $sqlquery['options'] .= "AND (l.user_id = u.user_id AND l.visible = '1' AND l.attachtype = 'profile') ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_with_active_profile_logos_only'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_with_active_profile_logos_only'] . '</strong>, ');
                        }
                        
                        // #### show only online logged in members #############
                        if (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'])
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'isonline=' . $ilance->GPC['isonline']);
                                $sqlquery['options'] .= "AND (s.userid = u.user_id) ";
                                
                                handle_search_verbose('<span class="black">' . $phrase['_showing_members_online_and_logged_in'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_members_online_and_logged_in'] . '</strong>, ');
                        }
                        
                        
			
			// #### currency selector ##############################
			$clear_currencies = '';
			if ($ilconfig['globalserverlocale_currencyselector'])
			{
				$ilance->GPC['cur'] = isset($ilance->GPC['cur'])
					? handle_input_keywords($ilance->GPC['cur'])
					: '';
					
				$leftnav_currencies = print_currencies('users AS u', 'u.currencyid', $ilance->GPC['cur'], 5, "AND status = 'active'");
				$clear_currencies = !empty($clear_currencies_all)
					? '<a href="' . $clear_currencies_all . '" rel="nofollow">' . $phrase['_clear'] . '</a>'
					: '';
				
				$sqlquery['options'] .= (!empty($ilance->GPC['cur']))
					? "AND (FIND_IN_SET(u.currencyid, '" . $ilance->db->escape_string($ilance->GPC['cur']) . "')) "
					: '';
				
				if (isset($ilance->GPC['cur']) AND $ilance->GPC['cur'] != '')
				{
					//$this->currencies[$currencies['currency_id']
					$curs = '';
					if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == true)
					{
						$temp = explode(',', $ilance->GPC['cur']);
						foreach ($temp AS $key => $value)
						{
							if ($value != '')
							{
								$curs .= $ilance->currency->currencies[$value]['currency_abbrev'] . ', ';
							}
						}
						if (!empty($curs))
						{
							$curs = substr($curs, 0, -2);
						}
						unset($temp);
					}
					else if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == false)
					{
						$ilance->GPC['cur'] = intval($ilance->GPC['cur']);
						$curs .= $ilance->currency->currencies[$ilance->GPC['cur']]['currency_abbrev'];
					}
					
					handle_search_verbose('<!--<span class="gray">' . $phrase['_currency'] . ':</span> --><span class="black">' . $curs . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_currency'] . ': <strong>' . $curs . '</strong>, ');
					unset($curs);
				}
			}
			
			// #### handle hourly rate price range #################
			$clear_price = '';
			$removeurl = $scriptpage;
			if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
                        {
                                $sqlquery['pricerange'] .= "AND (u.rateperhour >= " . intval($ilance->GPC['fromprice']) . " ";
				
				$removeurl = rewrite_url($removeurl, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
                                handle_search_verbose('<span class="black">' . $phrase['_min_hourly_rate'] . ': ' . $ilance->currency->format($ilance->GPC['fromprice']) . '</span>, ');
                                handle_search_verbose_save($phrase['_min_hourly_rate'] . ': <strong>' . $ilance->currency->format($ilance->GPC['fromprice']) . '</strong>, ');
                        }
                        else
                        {
                                $sqlquery['pricerange'] .= "AND (u.rateperhour >= 0 ";
                        }
                        
                        if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
                        {
                                $sqlquery['pricerange'] .= "AND u.rateperhour <= " . intval($ilance->GPC['toprice']) . ") ";
				
                                $removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
				$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
                                
                                handle_search_verbose('<span class="black">' . $phrase['_max_hourly_rate'] . ': ' . $ilance->currency->format($ilance->GPC['toprice']) . '</span> <!--<a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_max_hourly_rate'] . ': <strong>' . $ilance->currency->format($ilance->GPC['toprice']) . '</strong>, ');
                        }
                        else
                        {
                                $sqlquery['pricerange'] .= ")";
                        }
                        unset($removeurl);
			
                        $fromprice = isset($ilance->GPC['fromprice']) ? sprintf("%01.2f", $ilance->GPC['fromprice']) : '';
                        $toprice = isset($ilance->GPC['toprice']) ? sprintf("%01.2f", $ilance->GPC['toprice']) : '';		
			
                        // #### multiple skills being searched #################
                        $sqlquery['skillsquery'] = "";
                        if (isset($ilance->GPC['sid']) AND is_array($ilance->GPC['sid']))
                        {
                                $sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "skills_answers a ON u.user_id = a.user_id ";
                                $findinset = $showtextskills = $favtextskills = '';
                                foreach ($ilance->GPC['sid'] AS $sid => $value)
                                {
                                        if (isset($sid) AND $sid > 0 AND isset($value) AND !empty($value))
                                        {
                                                $answertitle = print_skill_title($sid);
                                                $showqidurl = $ilpage['search'] . print_hidden_fields(true, array('page','searchid'), true, '', '', true, true, true);
                                                $showqidurl = rewrite_url($showqidurl, $remove = 'sid[' . $sid . ']=' . $value);
                                                
                                                $showtextskills .= '<span class="black"><strong>' . $answertitle . '</strong></span><!-- <a href="' . $showqidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ';
                                                $favtextskills .= '<strong>' . $answertitle . '</strong>, ';
                                                
                                                $findinset .= "$sid,";
                                        }
                                }
                                
                                $sqlquery['skillsquery'] = "AND (FIND_IN_SET(a.cid, '$findinset'))";
                                
                                handle_search_verbose_filters('<span class="black"><strong>' . $phrase['_matching_skills'] . '</strong></span> ' . $showtextskills . '');
                                handle_search_verbose_save($phrase['_skills'] . ': ' . $favtextskills);
                        }
                        
                        // #### single skill category being searched ###########
                        else if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] > 0 AND !is_array($ilance->GPC['sid']))
                        {
                                $answertitle = print_skill_title($ilance->GPC['sid']);
                                $showqidurl = $ilpage['search'] . print_hidden_fields(true, array('page','sid','searchid'), true, '', '', true, true);
				$showqidurl = rewrite_url($showqidurl, $remove = 'sid=' . $ilance->GPC['sid']);
                                
                                $sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "skills_answers a ON u.user_id = a.user_id ";
                                $sqlquery['skillsquery'] = "AND (a.cid = '" . intval($ilance->GPC['sid']) . "')";
                                
                                handle_search_verbose_filters('<span class="black"><strong>' . $phrase['_matching_skills'] . '</strong></span> <span class="black"><strong>' . $answertitle . '</strong></span><!-- <a href="' . $showqidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                handle_search_verbose_save($phrase['_skills'] . ': <strong>' . $answertitle . '</strong>, ');
                        }
                        
                        // #### match any skills we can based on user any supplied keywords
                        else
                        {
                                if (isset($keyword_text) AND !empty($keyword_text))
                                { 
                                        $sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "skills_answers a ON u.user_id = a.user_id ";
                                        $sqlquery['skillsquery'] = build_skills_inclusion_sql('a.', $keyword_text);
                                        
                                        if (!empty($sqlquery['keywords']) AND !empty($sqlquery['skillsquery']))
                                        {
                                                $sqlquery['keywords'] = substr($sqlquery['keywords'], 4);
                                                $sqlquery['keywords'] = "AND (" . $sqlquery['keywords'] . " OR ";
                                                $sqlquery['skillsquery'] = substr($sqlquery['skillsquery'], 4);
                                                $sqlquery['skillsquery'] = $sqlquery['skillsquery'] . ")";
                                                
                                                // #### build a special keywords query
                                                $sqlquery['keywords'] = $sqlquery['keywords'] . $sqlquery['skillsquery'];
                                                $sqlquery['skillsquery'] = '';
                                        }
                                }
                        }

                        // #### profile answers logic ##########################
                        $sqlquery['profileanswersquery'] = $sqlquery['profileanswersqueryextra'] = "";
                        if (isset($ilance->GPC['pa']) AND is_array($ilance->GPC['pa']))
                        {
                                $showtextprofiles = $favtextprofiles = $profiletitle = '';
                                $showtextpro = $emptypa = array();
                                $qs = 0;
                                
                                foreach ($ilance->GPC['pa'] AS $profileqid)
                                {
                                        if (isset($profileqid) AND is_array($profileqid))
                                        {
                                                foreach ($profileqid AS $profileid => $profileoptions)
                                                {
                                                        $emptypa[$profileid] = 'false';
                                                        $profiletitle = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . intval($profileid) . "'", "question");
                                                        $showtextpro[$profileid]['title'] = $profiletitle;
                                                        
                                                        if (isset($profileoptions) AND is_array($profileoptions))
                                                        {
                                                                $pass = false;
                                                                foreach ($profileoptions AS $profilekey => $profilevalue)
                                                                {
                                                                        if (isset($profilevalue) AND !empty($profilevalue))
                                                                        {
                                                                                if ($profilekey == 'from')
                                                                                {
                                                                                        $fromrange = $profilevalue;
                                                                                }
                                                                                if ($profilekey == 'to')
                                                                                {
                                                                                        $torange = $profilevalue;
                                                                                }
                                                                                if ($profilekey == 'custom')
                                                                                {
                                                                                        $custom = $profilevalue;
                                                                                        
                                                                                        $showpidurl = $ilpage['search'] . print_hidden_fields(true, array('page','searchid'), true, '', '', true, true, true);
                                                                                        $showpidurl = rewrite_url($showpidurl, $remove = 'pa[choice_' . str_replace(' ', '_', mb_strtolower($custom)) . '][' . $profileid . '][custom]=' . $custom);
                                                                                        $showtextpro[$profileid]['options'][] = '<strong>' . $custom . '</strong><!-- <a href="' . $showpidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ';
                                                                                }
                                                                                $pass = true;
                                                                        }
                                                                }
                                                        }
                                                        
                                                        // range integer type
                                                        if (!empty($fromrange) AND !empty($torange) AND !empty($profileid) AND empty($custom) AND $pass)
                                                        {
                                                                $sqlquery['profileanswersquery'] .= "(pa.user_id = u.user_id AND pa.questionid = '" . intval($profileid) . "' AND pa.answer BETWEEN $fromrange AND $torange) OR ";
                                                                
                                                                $showpidurl = $ilpage['search'] . print_hidden_fields(true, array('page','searchid'), true, '', '', true, true, true);
                                                                $showpidurl = rewrite_url($showpidurl, $remove = '&pa[range][' . $profileid . '][from]=' . $fromrange . '&pa[range][' . $profileid . '][to]=' . $torange);
                                                                
                                                                $showtextpro[$profileid]['title'] = $profiletitle;
                                                                $showtextpro[$profileid]['options'][] = '<strong>' . $phrase['_between_upper'] . ' ' . $fromrange . ' ' . $phrase['_and'] . ' ' . $torange . '</strong><!-- <a href="' . $showpidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ';
                                                        }
                                                        
                                                        // multiple choices
                                                        else if (!empty($custom) AND $pass)
                                                        {
                                                                $sqlquery['profileanswersquery'] .= "(pa.user_id = u.user_id AND pa.questionid = '" . intval($profileid) . "' AND pa.answer LIKE '%" . $ilance->db->escape_string($custom) . "%') OR ";
                                                        }
                                                        else
                                                        {
                                                                $emptypa[$profileid] = 'true';
                                                        }
                                                        $qs++;
                                                }
                                        }
                                }
                                
                                // #############################################
                                // handle display of custom profile questions used on the advanced search form
                                if (!empty($showtextpro) AND is_array($showtextpro))
                                {
                                        foreach ($showtextpro AS $profilequestionid => $profilearray)
                                        {
                                                if ($emptypa[$profilequestionid] == 'false')
                                                {
                                                        $showtextprofiles .= $showtextpro[$profilequestionid]['title'] . ': ';
                                                        foreach ($profilearray AS $vv => $values)
                                                        {
                                                                if (isset($values) AND is_array($values))
                                                                {
                                                                        foreach ($values AS $choice)
                                                                        {
                                                                                $showtextprofiles .= $choice;        
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
                                
                                if (!empty($sqlquery['profileanswersquery']) AND !empty($profiletitle))
                                {
                                        $sqlquery['fields'] .= ", pa.questionid, pa.answer";
                                        $sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "profile_answers pa ON u.user_id = pa.user_id ";
                                        $sqlquery['profileanswersquery'] = "AND (" . mb_substr($sqlquery['profileanswersquery'], 0, -4) . ')';
                                        
                                        //handle_search_verbose_filters($phrase['_other'] . ': <strong>' . $profiletitle . '</strong> <a href="' . $showpidurl . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
                                        //handle_search_verbose_save(', <strong>' . $phrase['_other'] . '</strong>: <strong>' . $profiletitle . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['profileanswersquery'] = '';        
                                }
                        }
			
			// #### options selector ###############################
			$leftnav_options = print_options('experts');
			$clear_options = !empty($clear_options_all) ? '<a href="' . $clear_options_all . '" rel="nofollow">' . $phrase['_clear'] . '</a>' : '';
                        
                        // #### BUILD MAIN SEARCH SQL QUERY ####################
                        $sqlquery['select'] = "SELECT $sqlquery[fields] $sqlquery[from] $sqlquery[leftjoin] " . ((isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline']) ? "LEFT JOIN " . DB_PREFIX . "sessions s ON u.user_id = s.userid" : "") . " WHERE u.user_id = p.user_id ";

                        $SQL  = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[pricerange] $sqlquery[genrequery] $sqlquery[profileanswersquery] $sqlquery[skillsquery] $sqlquery[groupby] $sqlquery[orderby] $sqlquery[limit]";
                        $SQL2 = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[pricerange] $sqlquery[genrequery] $sqlquery[profileanswersquery] $sqlquery[skillsquery] $sqlquery[groupby] $sqlquery[orderby]";
			
                        $row_count = 0;
                        $numberrows = $ilance->db->query($SQL2);
                        $number = $ilance->db->num_rows($numberrows);
                        $counter = ($ilance->GPC['page'] - 1) * fetch_perpage();
                        
                        // #### build our search engine verbose output #########
                        if (!empty($keyword_text))
                        {
                                $favtext = '<div>' . $phrase['_keywords'] . ': <strong>' . stripslashes($keyword_formatted_favtext) . '</strong></div>' . print_search_verbose_saved('verbose_save');
                                $favtext = mb_substr($favtext, 0, -2) . '';
                                
                                if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
                                {
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_keywords'] . ' <span class="black">' . stripslashes($keyword_formatted) . '</span>';
                                }
                                else
                                {
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_keywords'] . ' <span class="black">' . stripslashes($keyword_formatted) . '</span> ' . print_search_verbose_saved('verbose');
                                        $text = mb_substr($text, 0, -2) . '';
                                }
                                
                                $text = '<span id="verbosetext">' . $text . '</span>';
                        }
                        else
                        {
                                // favorite search text results
                                $favtext = '<div>' . $phrase['_keywords'] . ': <strong>' . $phrase['_none'] . '</strong></div>' . print_search_verbose_saved('verbose_save');
                                $favtext = mb_substr($favtext, 0, -2) . '';
                                
                                if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
                                {
                                        $text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_no_keywords'] . '';
                                }
                                else
                                {
                                        $text = '<span style="font-size:19px" class="blueonly""><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_no_keywords'] . ' ' . print_search_verbose_saved('verbose');
                                        $text = mb_substr($text, 0, -2) . '';        
                                }
                                
                                $text = '<span id="verbosetext">' . $text . '</span>';
                        }
                        
                        $showallurl = $ilpage['search'] . print_hidden_fields(true, array('page','qid','q','sid','pa','searchid'), true, '', '', true, false);
                        define('PHP_SELF_NOQID', $showallurl);
                        
                        $showtext = print_search_verbose_saved('verbose_filter');
                        if (!empty($showtext))
                        {
                                $showtext = mb_substr($showtext, 0, -2) . ' &nbsp;&nbsp;&nbsp;<span class="smaller gray">[ <span class="blue"><a href="' . $showallurl . '" rel="nofollow">' . $phrase['_show_all'] . '</a></span> ]</span>';
                                $text .= ', <span>' . $showtext . '</span>';
                        }
                        
                        if (!empty($showtextprofiles))
                        {
                                $showtextprofiles = mb_substr($showtextprofiles, 0, -2) . ' &nbsp;&nbsp;&nbsp;<span class="smaller gray">[ <span class="blue"><a href="' . $showallurl . '" rel="nofollow">' . $phrase['_show_all'] . '</a></span> ]</span>';
                                $text .= ', <!--<span><strong>' . $phrase['_profile_filters'] . ':</strong></span> --><span class="black"><strong>' . $showtextprofiles . '</strong></span>';
                        }
			
			// #### save this search ###############################
			if (isset($ilance->GPC['searchid']) AND $ilance->GPC['searchid'] > 0)
			{
				// todo: add hit tracker to show hit count of saved search
				$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites&amp;returnurl=' . $php_self_urlencoded . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /> ' . $phrase['_view_saved_searches'] . '</a></span> ]</span>';
			}
			else
			{
				$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_save_as_favorite_search'] . '</a></span> ]</span>';
			}
                        
                        $metatitle = '';
                        if ($cid > 0)
                        {
                                $metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                                $metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                                $metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], 'service', $cid, $commaafter = true, $showinputkeywords = true);
                        }
                        
                        $area_title = $phrase['_experts_search_results'] . ': ' . $metatitle . ' (' . $number . ' ' . $phrase['_results_found'] . ')';
                        $page_title = ((isset($keyword_text) AND !empty($keyword_text)) ? $keyword_text . ', ' : '') . '' . $phrase['_find'] . ' ' . $phrase['_experts'] . ' ' . ((!empty($metadescription)) ? $phrase['_in'] . ' ' . $metatitle . ', ' . $metadescription : '') . ' | ' . SITE_NAME;
                                                
                        $search_results_rows = array();
                        $result = $ilance->db->query($SQL);
                        if ($ilance->db->num_rows($result) > 0)
                        {
                                while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
                                        $memberinfo = array();
                                        $memberinfo = $ilance->feedback->datastore($row['user_id']);
                                        
                                        $td['sel'] = (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
						? '<input type="checkbox" name="vendor_id[]" value="' . $row['user_id'] . '" id="experts_' . $row['user_id'] . '" />'
						: '<input type="hidden" name="vendor_id[]" value="' . $row['user_id'] . '" id="experts_' . $row['user_id'] . '" disabled="disabled" />';
					
					$td['profileintro'] = short_string(print_string_wrap($row['profileintro'], 50), 65);
                                        $td['latestfeedback'] = short_string(print_string_wrap(print_latest_feedback_received($row['user_id'], 'provider', $shownone = true), 50), 50);
                                        $td['isonline'] = print_online_status($row['user_id'], 'litegray', 'blue');
                                        $td['expert'] = print_username($row['user_id'], 'href');
                                        $td['username'] = $td['expert'];
                                        $td['user_id'] = $row['user_id'];
                                        $td['city'] = ucfirst(fetch_user('city', $row['user_id']));
                                        $td['zipcode'] = mb_strtoupper(fetch_user('zip_code', $row['user_id']));
                                        $td['country'] = print_user_country($row['user_id'], $_SESSION['ilancedata']['user']['slng']);
                                        $td['state'] = fetch_user('state', $row['user_id']);
                                        $td['rated'] = ($memberinfo['rating'] == 0)
						? '-'
						: '<span class="smaller">' . number_format($memberinfo['rating'], 2, '.', '') . '&nbsp;/&nbsp;5.00</span>';
						
                                        $td['feedback'] = '<span class="smaller">' . $memberinfo['pcnt'] . '%<!--&nbsp;' . $phrase['_positive'] . '--></span>';
                                        $td['credentials'] = fetch_verified_credentials($row['user_id']);
                                        $td['reviews'] = print_username($row['user_id'], 'custom', 0, '', '', fetch_service_reviews_reported($row['user_id']) . '&nbsp;' . $phrase['_reviews']);
                                        $td['awards'] = fetch_user('serviceawards', $row['user_id']);
                                        $td['awards'] = '<div class="smaller">' . (($td['awards'] == 0) ? '-' : $td['awards']) . '</div>';
                                        $td['earnings'] = '<span class="smaller">' . print_income_reported($row['user_id']) . '</span>';
                                        $td['portfolio'] = (has_portfolio($row['user_id']) > 0)
						? '<span class="smaller blueonly"><a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?id=' . $row['user_id'] . '" rel="nofollow">' . $phrase['_portfolio'] . '</a></span>'
						: '-';
					
                                        // only fetch distance between point a to b in the distance column
					$row['distance'] = (!isset($row['distance'])) ? 0 : $row['distance'];
                                        $td['distance'] = (isset($show['distancecolumn']) AND $show['distancecolumn'] AND !empty($ilance->GPC['radiuszip']))
						? '<div class="smaller gray">' . $ilance->distance->print_distance_results($row['country'], $row['zip_code'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . '</div>'
						: '-';
						
					// display the location under the title
					$countryrowname = (isset($ilance->GPC['country'])) ? '' : ', ' . print_country_name($row['country'], $_SESSION['ilancedata']['user']['slng'], false);
				
					$td['location'] = $row['city'] . (!empty($row['state']) ? ', ' . $row['state'] . '' : '') . $countryrowname;
					
					// show the distance bit after the location
					// murugan changes on jan 13
					$td['location'] .= ($td['distance'] != '-' AND !empty($countryid))
						? '&nbsp;&nbsp;(<span class="black">' . $ilance->distance->print_distance_results($row['country'], $row['zip_code'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . ' ' . $phrase['_from_lowercase'] . '</span> <span class="blue"><a href="javascript:void(0)" onclick="javascript:jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();">' . handle_input_keywords($ilance->GPC['radiuszip']) . '<!--, ' . handle_input_keywords($ilance->GPC['country']) . '--></a></span>)'
						: '';
					unset($countryrowname);
					
					// gender
					$gender = fetch_user('gender', $row['user_id'], '', '', false);
					if ($gender == '' OR $gender == 'male')
					{
						$td['profilelogo'] = '<a href="' . print_username($row['user_id'], 'url') . '" onmouseover="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2_sel.gif\')" onmouseout="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif' . '" border="0" alt="" width="80" height="80" name="nophoto_experts_' . $row['user_id'] . '" /></a>';
					}
					else if ($gender == 'female')
					{
						$td['profilelogo'] = '<a href="' . print_username($row['user_id'], 'url') . '" onmouseover="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3_sel.gif\')" onmouseout="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif' . '" border="0" alt="" width="80" height="80" name="nophoto_experts_' . $row['user_id'] . '" /></a>';
					}
					
                                        $sqlattach = $ilance->db->query("
                                                SELECT attachid, filehash
                                                FROM " . DB_PREFIX . "attachment
                                                WHERE user_id = '" . $row['user_id'] . "' 
                                                    AND visible = '1'
                                                    AND attachtype = 'profile'
                                                LIMIT 1
                                        ");
                                        if ($ilance->db->num_rows($sqlattach) > 0)
                                        {
                                                $resattach = $ilance->db->fetch_array($sqlattach, DB_ASSOC);
                                                $td['profilelogo'] = '<img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $resattach['filehash'] . '" border="0" alt="" />';
                                        }
                                        unset($sqlattach, $resattach);
					
					// rate per hour
					$hourlyrate = fetch_user('rateperhour', $row['user_id']);
                                        $td['rateperhour'] = ($hourlyrate > 0)
						? '<strong>' . $ilance->currency->format($hourlyrate, fetch_user('currencyid', $row['user_id'])) . '</strong>'
						: '-';
						
                                        $td['skills'] = print_skills($row['user_id'], 3);
                                        $td['level'] = $ilance->subscription->print_subscription_icon($row['user_id']);                                        
                                        $td['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
                                        
                                        ($apihook = $ilance->api('search_results_providers_loop')) ? eval($apihook) : false;
                                        
                                        $search_results_rows[] = $td;
                                        $row_count++;
                                }
                                
                                $show['no_rows_returned'] = false;
                        }
                        else
                        {
                                $show['no_rows_returned'] = true;
                        }
                        
                        // #### BUILD OUR PAGNATOR #############################
                        $prevnext = print_pagnation($number, fetch_perpage(), intval($ilance->GPC['page']), $counter, $scriptpage);
                        
                        // #### PRINT OUR SEARCH RESULTS TABLE #################
                        $search_results_table = print_search_results_table($search_results_rows, 'experts', $prevnext);
                    
                        // #### SEARCH FORM ELEMENTS: CATEGORY PULLDOWN ########
                        $cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
                        $radiuscountry = (isset($ilance->GPC['radiuscountry']) AND $ilance->GPC['radiuscountry'] > 0) ? $ilance->GPC['radiuscountry'] : (!empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all');
                        
                        //$search_radius_country_pulldown_experts = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertradiuscountry');
                        
                        if (!empty($ilance->GPC['q']))
                        {
                                $keywords = htmlspecialchars($ilance->GPC['q']);
                        }
                        
                        // fewer keywords search
                        $fewer_keywords = print_fewer_keywords_search($keywords_array, 'experts', $number);
                        
                        // #### SAVE AS FAVORITE SEARCH OPTION #################
                        if ($ilconfig['savedsearches'])
                        {
                                // build search request parameters
                                $favorites = array();
                                foreach ($ilance->GPC AS $search => $option)
                                {
                                        if ($search != 'submit' AND $search != 'search' AND $search != 'page')
                                        {
                                                $favorites[] = array($search => $option);
                                        }
                                }
                                if (!empty($favorites) AND is_array($favorites))
                                {
                                        $encrypt = serialize($favorites);
                                        $encrypt = urlencode($encrypt);
                                }
                                
                                $favoritesearchurl = $encrypt;
                                $favtext = ilance_htmlentities($favtext);
                        }
                        else
                        {
                                $favtext = '';        
                        }
                        
                        $v3left_nav = $ilance->template->print_left_nav('serviceprovider', $cid, 1, 0, $ilconfig['globalfilters_enablecategorycount'], true);
                        
                        ($apihook = $ilance->api('search_results_providers_end')) ? eval($apihook) : false;
                        
                        $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
                        $sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', $expertsmode = true);
                        
                        $hiddenfields = print_hidden_fields(false, array('searchid','cid','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','q','sort','page'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
                        $hiddenfields_leftnav = print_hidden_fields(false, array('searchid','feedback','country','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','page','radius','radiuscountry','radiuszip'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
                        
                        $city = isset($ilance->GPC['city']) ? strip_tags($ilance->GPC['city']) : '';
                        $state = isset($ilance->GPC['state']) ? strip_tags($ilance->GPC['state']) : '';
                        $zip_code = isset($ilance->GPC['zip_code']) ? strip_tags($ilance->GPC['zip_code']) : '';
                        $radiuszip = isset($ilance->GPC['radiuszip']) ? strip_tags($ilance->GPC['radiuszip']) : '';
                        
                        // #### DISPLAY SEARCH RESULTS VIA XML #################
                        if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'xml')
                        {
                                if (!empty($search_results_rows) AND is_array($search_results_rows))
                                {
                                        $ilance->xml = construct_object('api.xml');
                                
                                        $xml = $ilance->xml->search_to_xml($search_results_rows, false);
                                        echo $xml;
                                }
                                exit();
                        }
                        
                        // #### DISPLAY SEARCH RESULTS VIA SERIALIZED ARRAY ####
                        else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'array')
                        {
                                if (!empty($search_results_rows) AND is_array($search_results_rows))
                                {
                                        echo urlencode(serialize($search_results_rows));
                                }
                                exit();
                        }
                        
                        // #### DISPLAY SEARCH RESULTS TEMPLATE ################
                        else
                        {
                                // attempt to correct the spelling for the user if applicable (not in use at the moment)
                                $didyoumean = print_did_you_mean($keyword_text, 'experts');
				
				// if we're a guest and we don't have the region modal cookie let's ask for it
				$full_country_pulldown = construct_country_pulldown(0, '', 'region', true, '', false, true, true);
				
				if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal']))
				{
					$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();';
				
					// don't ask this guest for region info via popup modal for 3 days
					set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
				}
				
                                $ilance->template->fetch('main', 'search_results.html',2);
                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                $ilance->template->parse_if_blocks('main');
                                
                                $pprint_array = array('wysiwyg_area','searchurl','clear_budgetrange','clear_currencies','leftnav_currencies','clear_local','clear_feedback','leftnav_feedbackrange','leftnav_ratingrange','clear_rating','clear_award','leftnav_awardrange','sort','country','clear_price','clear_options','leftnav_options','leftnav_options','showallurl','clear_region','leftnav_regions','full_country_pulldown','didyoumean','search_radius_country_pulldown_experts','search_country_pulldown_experts','favtext','favoritesearchurl','profilebidfilters','fewer_keywords','fromprice','toprice','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','search_country_pulldown2','hiddenfields','search_results_table','sortpulldown2','keywords','two_column_category_vendors','keywords','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','input_style','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','input_style','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','login_include_admin','ilanceversion','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                                
                                ($apihook = $ilance->api('search_results_providers_template')) ? eval($apihook) : false;
 
                                $ilance->template->pprint('main', $pprint_array);
                                exit();
                        }			
                        break;
                }
        }
}

// #### ADVANCED SEARCH MENU ###################################################

$area_title = $phrase['_search_the_marketplace'];
$page_title = SITE_NAME . ' - ' . $phrase['_search_the_marketplace'];

$search_bidrange_pulldown_service = print_bid_range_pulldown('', 'bidrange', 'servicebidrange', 'pulldown');
$search_bidrange_pulldown_product = print_bid_range_pulldown('', 'bidrange', 'productbidrange', 'pulldown');
$search_awardrange_pulldown = print_award_range_pulldown('', 'projectrange', 'projectrange', 'pulldown');
$search_ratingrange_pulldown = print_rating_range_pulldown('', 'rating', 'rating');

if (isset($ilance->GPC['country']))
{
        $country = $ilance->GPC['country'];
}
else
{
        $country = !empty($_SESSION['ilancedata']['user']['country']) ? $_SESSION['ilancedata']['user']['country'] : 'all';
}

$search_country_pulldown_experts = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountry', true);
$availableto_pulldown_experts    = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountryto', true);
$locatedin_pulldown_experts      = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountryin', true);
$region_pulldown_experts         = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'expertregionin', 'pulldown', $onchange = true, '3');

$search_country_pulldown_service = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountry', true);
$availableto_pulldown_service    = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountryto', true);
$locatedin_pulldown_service      = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountryin', true);
$region_pulldown_service         = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'serviceregionin', 'pulldown', $onchange = true, '1');

$search_country_pulldown_product = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountry', true);
$availableto_pulldown_product    = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountryto', true);
$locatedin_pulldown_product      = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountryin', true);
$region_pulldown_product         = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'productregionin', 'pulldown', $onchange = true, '2');

//$radiuscountry = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all';
if (isset($ilance->GPC['radiuscountry']) AND $ilance->GPC['radiuscountry'] > 0)
{
        $radiuscountry = $ilance->GPC['radiuscountry'];
}
else
{
        $radiuscountry = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all';
}

//$search_radius_country_pulldown_experts = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertradiuscountry');
//$search_radius_country_pulldown_service = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'serviceradiuscountry');
//$search_radius_country_pulldown_product = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productradiuscountry');

if (isset($ilance->GPC['q']))
{
        if (!empty($ilance->GPC['q']))
        {
                $q = ilance_htmlentities($ilance->GPC['q']);
        }
}

($apihook = $ilance->api('search_menu_start')) ? eval($apihook) : false;

$searcherror = $ilance->language->construct_phrase($phrase['_we_require_that_you_wait_x_seconds_between_searches_please_try_again_in_x_seconds'], array($searchwait, $searchwaitleft));

// #### SEARCH OPTIONS #########################################################
$js_start = print_searchoptions_js();

// #### SEARCH OPTION CONTROLS #################################################
$perpage = print_perpage_searchoption();
$colsperrow = print_colsperrow_searchoption();
$sortpulldown = print_sort_pulldown();

$cb_username = print_checkbox_status('username');
$cb_latestfeedback = print_checkbox_status('latestfeedback');
$cb_online = print_checkbox_status('online');
$cb_description = print_checkbox_status('description');
$cb_icons = print_checkbox_status('icons');
$cb_currencyconvert = print_checkbox_status('currencyconvert');
$cb_displayfeatured = print_checkbox_status('displayfeatured');
$cb_hidelisted = print_checkbox_status('hidelisted');
$cb_hideverbose = print_checkbox_status('hideverbose');
$cb_proxybit = print_checkbox_status('proxybit');
$rb_showtimeas_static = print_time_static_radiobox_status();
$rb_showtimeas_flash = print_time_flash_radiobox_status();
$rb_list_gallery = print_list_gallery_radiobox_status();
$rb_list_list = print_list_list_radiobox_status();

// #### SAVING SEARCH OPTIONS ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'saveoptions')
{
        if (is_array($ilance->GPC))
        {
                $options = array();
                foreach ($ilance->GPC AS $search => $option)
                {
                        if ($search != 'defaultupdate' AND $search != 'membersupdate' AND $search != 'tab' AND $search != 'search' AND $search != 'cmd' AND $search != 'returnurl' AND $search != 'redirect')
                        {
                                $options["$search"] = $option;
                        }
                }
                if (empty($options['online']))
                {
                        $options['online'] = 'false';
                }
                if (empty($options['latestfeedback']))
                {
                        $options['latestfeedback'] = 'false';
                }
                if (empty($options['username']))
                {
                        $options['username'] = 'false';
                }
                if (empty($options['description']))
                {
                        $options['description'] = 'false';
                }
                if (empty($options['icons']))
                {
                        $options['icons'] = 'false';
                }
                if (empty($options['currencyconvert']))
                {
                        $options['currencyconvert'] = 'false';
                }
                if (empty($options['displayfeatured']))
                {
                        $options['displayfeatured'] = 'false';
                }
                if (empty($options['hidelisted']))
                {
                        $options['hidelisted'] = 'false';
                }
                if (empty($options['hideverbose']))
                {
                        $options['hideverbose'] = 'false';
                }
                if (empty($options['proxybit']))
                {
                        $options['proxybit'] = 'false';
                }
                
                ($apihook = $ilance->api('search_saveoptions_submit_end')) ? eval($apihook) : false;
                
                $searchoptions = serialize($options);
                $uid = (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0;
                update_default_searchoptions($uid, $searchoptions);
                
                if (isset($ilance->GPC['defaultupdate']) AND $ilance->GPC['defaultupdate'] == 'true')
                {
                        update_default_searchoptions_guests($searchoptions);
                }
                
                if (isset($ilance->GPC['membersupdate']) AND $ilance->GPC['membersupdate'] == 'true')
                {
                        update_default_searchoptions_users($searchoptions);
                }
                
                if (!empty($ilance->GPC['returnurl']))
                {
                        refresh($ilance->GPC['returnurl']);
                        exit();
                }
                
                refresh($ilpage['search'] . '?tab=3');
                exit();
        }
        else
        {
                refresh($ilpage['login']);
                exit();      
        }
}

$show['widescreen'] = $show['leftnav'] = false;

$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
$sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', $expertsmode = true);
$returnurl = !empty($ilance->GPC['returnurl']) ? handle_input_keywords($ilance->GPC['returnurl']) : '';

// #### advanced search skills selector for experts ############################
$skills_selection = $ilance->categories_skills->print_skills_columns($_SESSION['ilancedata']['user']['slng'], $showcount = 1, $prepopulate = false);

$headinclude .= '
<script language="javascript" type="text/javascript">
<!-- 
function print_profile_filters()
{
	var ajaxRequest;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	} 
	catch (e)
	{
		// Internet Explorer Browsers
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) 
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (e)
			{
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			var ajaxDisplay = fetch_js_object(\'profile_filters_text\');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	
        var selected_cid = fetch_js_object(\'cid_list\').options[fetch_js_object(\'cid_list\').selectedIndex].value;
	var queryString = "&cid=" + selected_cid + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	
	ajaxRequest.open("GET", "' . HTTP_SERVER . 'ajax.php?do=profilefilters" + queryString, true);
	ajaxRequest.send(null); 
}
//-->
</script>';

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'search.html',2);
$ilance->template->parse_if_blocks('main');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));

$pprint_array = array('colsperrow','region_pulldown_experts','region_pulldown_service','region_pulldown_product','locatedin_pulldown_experts','locatedin_pulldown_service','locatedin_pulldown_product','availableto_pulldown_experts','availableto_pulldown_service','availableto_pulldown_product','search_bidrange_pulldown_service','search_bidrange_pulldown_product','search_radius_country_pulldown_service','search_radius_country_pulldown_product','search_country_pulldown_service','search_country_pulldown_product','search_country_pulldown_experts','search_radius_country_pulldown_experts','provider_category_selection','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_displayfeatured','cb_hidelisted','cb_proxybit','cb_hideverbose','serviceavailable','serviceselected','productavailable','productselected','expertavailable','expertselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','wantads_category_selection','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','stores_category_selection','product_category_selection','service_category_selection','search_serviceauctions_img','search_serviceauctions_collapse','search_productauctions_img','search_productauctions_collapse','search_experts_collapse','search_experts_img','pfp_category_left','rfp_category_left','input_style','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','input_style','remote_addr','rid','default_exchange_rate','login_include','login_include_admin','ilanceversion','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

($apihook = $ilance->api('search_start_template')) ? eval($apihook) : false;
 
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>