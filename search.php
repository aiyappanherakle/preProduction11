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
	'flashfix',
);

// #### define top header nav ##################
$topnavlink = array(
	'main_listings',
);

// #### setup script location ##################################################
define('LOCATION', 'search');

// #### require backend ########################################################
require_once './functions/config.php';
//error_reporting(E_ALL);
require_once DIR_CORE . 'functions_search.php';
require_once DIR_CORE . 'functions_search_prefs.php';

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[search]" => $ilcrumbs["$ilpage[search]"]);
$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

global $is_series_page, $is_denom_page,$sold_ended_hidden;

// #### SEARCH HELP : FULLTEXT BOOLEAN INFO ####################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'help') {
	$pprint_array = array('cb_username', 'cb_latestfeedback', 'cb_online', 'cb_description', 'cb_icons', 'cb_currencyconvert', 'cb_displayfeatured', 'cb_hidelisted', 'cb_proxybit', 'cb_hideverbose', 'productavailable', 'productselected', 'keywords', 'searcherror', 'fromprice', 'toprice', 'budgetfilter', 'tab', 'search_offersrange_pulldown', 'search_country_pulldown2', 'search_soldrange_pulldown', 'search_itemsrange_pulldown', 'search_opensincerange_pulldown', 'product_category_selection', 'search_productauctions_img', 'search_productauctions_collapse', 'search_experts_collapse', 'pfp_category_left', 'rfp_category_left', 'search_country_pulldown', 'search_jobtype_pulldown', 'search_ratingrange_pulldown', 'search_awardrange_pulldown', 'search_bidrange_pulldown', 'search_listed_pulldown', 'search_closing_pulldown', 'input_style', 'remote_addr', 'rid', 'default_exchange_rate', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server');

	$ilance->template->fetch('main', 'search_help.html');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

//for bug #5480
if (isset($ilance->GPC['denom_all']) && $ilance->GPC['denom_all'] == 1) {
	$ilance->GPC['series'] = 0;
}

// #### TIMER TO PREVENT SEARCH FLOODING #######################################
$show['searcherror'] = $searchwaitleft = 0;
$searchwait = $ilconfig['searchflooddelay'];

if (!empty($ilance->GPC['mode'])) {

	if ($ilconfig['searchfloodprotect'] AND isset($ilance->GPC['q']) AND $ilance->GPC['q'] != '') {
		if (empty($_SESSION['ilancedata']['user']['searchexpiry'])) {
			// start timer
			$_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
		} else {
			if (($timeexpired = TIMESTAMPNOW - $_SESSION['ilancedata']['user']['searchexpiry']) < $searchwait AND $searchwait != 0) {
				$show['searcherror'] = 1;
				$searchwaitleft = ($searchwait - $timeexpired);
			} else {
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
$ilance->encrypt = construct_object( 'api.encrypt' );

// #### selected category id ###################################################
$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;

// #### print multiple selection category menu #################################
$service_category_selection = $product_category_selection = $provider_category_selection = $search_category_pulldown = '';
if ($ilconfig['globalauctionsettings_serviceauctionsenabled']) {
	$service_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng']);
	$provider_category_selection = $ilance->categories_pulldown->print_cat_pulldown(0, 'service', 'levelmultisearch', 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '350px', $uid = 0, $forcenocount = 0, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $ilance->categories->cats, $onclickjs = true);
	if (isset($ilance->GPC['mode']) AND ($ilance->GPC['mode'] == 'service' OR $ilance->GPC['mode'] == 'experts')) {
		$search_category_pulldown = $service_category_selection;
	}
}

$profilebidfilters = '<div id="profile_filters_text">' . $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service') . '</div>';

// build our product category cache
//$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
//$ilance->categories->catproductfetch = $ilance->categories->fetch;

if ($ilconfig['globalauctionsettings_productauctionsenabled']) {
	// #### require shipping backend #######################################
	require_once DIR_CORE . 'functions_shipping.php';
	//suku
	// murugan changes on feb 18 for catalog order search
	//	$product_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['ilancedata']['user']['slng']);
	$drop_sql = $ilance->db->query("SELECT coin_series_unique_no,coin_series_name FROM " . DB_PREFIX . "catalog_second_level order by coin_series_unique_no asc", 0, null, __FILE__, __LINE__);
	$product_category_selection .= '<select name="series"><option value="">All Categories</option><option value="">------------------------------------------------</option>';
	while ($drop_line = $ilance->db->fetch_array($drop_sql)) {
		if (isset($ilance->GPC['series']) and $ilance->GPC['series'] > 0 and $drop_line['coin_series_unique_no'] == $ilance->GPC['series']) {
			$product_category_selection .= '<option value="' . $drop_line['coin_series_unique_no'] . '" selected="selected">' . $drop_line['coin_series_name'] . '</option>';
		} else {
			$product_category_selection .= '<option value="' . $drop_line['coin_series_unique_no'] . '">' . $drop_line['coin_series_name'] . '</option>';
		}

	}
	$product_category_selection .= '</select>';
	if (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product') {
		$search_category_pulldown = $product_category_selection;
	}
}

if (!empty($ilance->GPC['mode']) AND $show['searcherror'] == 0) {
	// #### PREPARE DEFAULT URLS ###########################################
	$scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do', 'cmd', 'page', 'budget', 'searchid', 'list'), true, '', '', $htmlentities = true, $urldecode = false);

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

	//for Bug #3379	start
	if (isset($ilance->GPC['series']) && $ilance->GPC['series'] > 0) {
		$denomi_id = 0;
		if (isset($ilance->GPC['denomination']) && is_array($ilance->GPC['denomination']) && count($ilance->GPC['denomination']) == 1) {
			$denomi_id = $ilance->GPC['denomination'][0];
		}
		$series_id = $ilance->db->escape_string(intval($ilance->GPC['series']));

		$coin_sers = $ilance->categories_parser->fetch_coin_series($denomi_id,  $series_id);

		if (!empty($coin_sers)) {
			if (!isset($ilance->GPC['denomination']) OR ((isset($ilance->GPC['denomination'])) && count($ilance->GPC['denomination']) == 1)) {
				$is_series_page = '<div class="block" style="margin-bottom: 20px;">
					<div class="block-top"><div class="block-right"><div class="block-left"></div></div></div>
					<div class="block-header">' . $coin_sers['coin_series_name'] . '</div>

					<div class="block-content" style="background:url(https://www.greatcollections.com/images/gc/icons/bg_gradient_yellow_1x1000.gif) repeat-x; padding:14px">
					<div><strong>About ' . $coin_sers['coin_series_name'] . ':</strong> &nbsp;' . $coin_sers['coin_series_description_long'] . '</div></div><div class="block-footer"><div class="block-right"><div class="block-left"></div></div></div></div>';
			}
		}

	}
	//for Bug #3379	end
	//for Bug #3380	start
	if (isset($ilance->GPC['denomination']) && is_array($ilance->GPC['denomination']) && count($ilance->GPC['denomination']) > 0) {

		foreach ($ilance->GPC['denomination'] as $denom_no) {
			$denomina_dets = $ilance->categories_parser->fetch_denominations($denom_no);
			if (!empty($denomina_dets)) {
				$is_denom_page = '<div style="padding:9px" class="block-content-yellow">
						<div class="smaller"><strong>' . $denomina_dets['denomination_long'] . '</strong> information:&nbsp;' . $denomina_dets['denomination_description'] . '
						</div></div>';
			}
		}
	}elseif(isset($ilance->GPC['denomination']) && intval($ilance->GPC['denomination']) > 0){
		$denomina_dets = $ilance->categories_parser->fetch_denominations(intval($ilance->GPC['denomination']));
			if (!empty($denomina_dets)) {
				$is_denom_page = '<div style="padding:9px" class="block-content-yellow">
						<div class="smaller"><strong>' . $denomina_dets['denomination_long'] . '</strong> information:&nbsp;' . $denomina_dets['denomination_description'] . '
						</div></div>';
			}
	}
	//for Bug #3380	end

	switch ($ilance->GPC['mode']) {
		// #### SEARCHING FOR PROJECT ID ###############################
		case 'rfpid':
			{
				if (empty($ilance->GPC['q']) OR !isset($ilance->GPC['q'])) {
					header("Location: " . $ilpage['search'] . "?tab=0");
					exit();
				}

				header("Location: " . $ilpage['rfp'] . "?id=" . intval($ilance->GPC['q']) . "");
				exit();
			}

		// #### SEARCHING FOR ITEM ID ##################################
		case 'itemid':
			{
				if (empty($ilance->GPC['q']) OR !isset($ilance->GPC['q'])) {
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
				$show['mode_product'] = false;

				if ($ilance->GPC['mode'] == 'product') {
					$navcrumb[""] = $phrase['_products'];
					$show['mode_product'] = true;
					$project_state = 'product';
					$sqlquery['projectstate'] = "AND (p.project_state = 'product')";
				}

				// #### ensure auctions shown in result have not yet expired..
				//karthik on jun16
				if (isset($ilance->GPC['ended'])) {
					$sqlquery['projectstatus'] = "AND (p.status = 'expired')";
					$sqlquery['orderby'] = "ORDER BY (p.date_end) DESC";
				} else {
					$sql['enddate'] = isset($ilance->GPC['date_end']) ? " AND date(p.date_end)='" . $ilance->GPC['date_end'] . "'" : '';
					//$sqlquery['timestamp'] = "AND (UNIX_TIMESTAMP(p.date_end) > UNIX_TIMESTAMP('" . DATETIME24H . "'))" . $sql['enddate'];
					$sqlquery['timestamp'] = "AND p.date_end > '" . DATETIME24H . "'" . $sql['enddate'];
					$sqlquery['projectstatus'] = "AND (p.status = 'open')";
					$sqlquery['orderby'] = "ORDER BY (p.date_end) ASC";
				}
				// here we should take the user to "all category listings" if he chose a "mode" but didn't select a category and keyword.
				if (!empty($ilance->GPC['mode']) AND empty($ilance->GPC['searchuser']) AND empty($ilance->GPC['state']) AND empty($ilance->GPC['country']) AND empty($ilance->GPC['sort']) AND (empty($ilance->GPC['q']) AND (empty($ilance->GPC['series']) OR !empty($ilance->GPC['series']) AND $ilance->GPC['series'] == 0))) {
					switch ($ilance->GPC['mode']) {
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

				// murugan changes in Sep 16 for search
				/*if($keyword_text == '*')
			{
			$keyword_text = '★';
			}*/
				if (strstr($keyword_text, '*')) {
					$keyword_text = str_replace('*', '★', $keyword_text);
				}
				if ($keyword_text == '$' || $keyword_text == '$1') {
					$keyword_text = 'Dollar';
				}
				if (strtoupper($keyword_text) == 'FBL') {
					$keyword_text = 'fl';
				}
				if (strtoupper($keyword_text) == 'PENNY' || strtoupper($keyword_text) == 'CENT') {
					$keyword_text = 'Cent';
				}
				if (strtoupper($keyword_text) == 'EAGLE' || strtoupper($keyword_text) == 'EAGEL') {
					$keyword_text = 'Eagle';
				}
				if (strtoupper($keyword_text) == 'UHR') {
					$keyword_text = 'Ultra High Relief';
				}
				if (strtoupper($keyword_text) == 'SILVER EAGLE' || strtoupper($keyword_text) == 'EAGLE SILVER' || strtoupper($keyword_text) == 'SILVER EAGEL' || strtoupper($keyword_text) == 'EAGEL SILVER') {
					$keyword_text = 'Silver Eagle';
					//$keyword_text = '\"'.$keyword_text.'\"';
				}

				$dkeywords = explode(' ', $keyword_text);

				// murugan on sep 16
				$coinpf = $ilance->db->query("
                                        SELECT *
                                        FROM "	 . DB_PREFIX . "coin_proof
                                "	, 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($coinpf) > 0) {
					while ($res = $ilance->db->fetch_array($coinpf)) {
						$first = $res['proof'] . '' . $res['value'];
						$second = $res['proof'] . '-' . $res['value'];
						$third = $res['proof'] . ' ' . $res['value'];

						if (strtoupper($keyword_text) == strtoupper($first) || strtoupper($keyword_text) == strtoupper($second) || strtoupper($keyword_text) == strtoupper($third)) {
							//$keyword_text = '\"'.$second.'\"';
							$keyword_text = $second;
						}

						foreach($dkeywords as $key=>$dval)
						{
							if (strtoupper($dval) == strtoupper($first) || strtoupper($dval) == strtoupper($second) || strtoupper($dval) == strtoupper($third)) {
								$dkeywords[$key] = $second;
							}
						}
					}

					if(is_array($dkeywords) && count($dkeywords)>1)
						$keyword_text = implode(' ', $dkeywords);
				}
				for ($i = 40; $i < 75; $i++) {
					$first = 'pf' . $i;
					$second = 'pf-' . $i;
					$third = 'pf ' . $i;
					$fourth = 'proof-' . $i;
					$fifth = 'proof' . $i;
					$sixth = 'proof ' . $i;
					$seven = 'pr' . $i;
					$eight = 'pr-' . $i;
					$nine = 'pr ' . $i;
					$tenth = $i;
					if (strtoupper($keyword_text) == strtoupper($first) || strtoupper($keyword_text) == strtoupper($second) || strtoupper($keyword_text) == strtoupper($third) || strtoupper($keyword_text) == strtoupper($fourth) || strtoupper($keyword_text) == strtoupper($fifth) || strtoupper($keyword_text) == strtoupper($sixth) || strtoupper($keyword_text) == strtoupper($seven) || strtoupper($keyword_text) == strtoupper($eight) || strtoupper($keyword_text) == strtoupper($nine) || strtoupper($keyword_text) == strtoupper($tenth)) {
						//$keyword_text ='\"'.$fourth.'\"';
						$keyword_text = $fourth;
					}
				}

				//for bug #6390 start
				$checktxt_cent = array('one','two','three', 'four', 'five', 'six', 'seven', 'eight','nine', 'ten', 'twenty');
				$check_cents = array('cent');
				$check_cent_text = explode(' ', strtolower($keyword_text));

				$centkeys = array();
				$ki = $kj = 0;
				$centtxt = $centkey = false;
				foreach ($check_cent_text as $key => $value) {
					//echo $value.'<br/>';
					if(in_array($value, $check_cents))
					{
						$centtxt = true;
						$centkeys[$key] = $value;
						$kj++;
					}

					if(in_array($value, $checktxt_cent))
					{
						$centkey = true;
						$centkeys[$key] = $value;
						$ki++;
					}
						
				}

				if($centkey && $centtxt && $ki==1 && $kj==1 && count($centkeys)==2)
				{
					$final_centtxt = implode('-',$centkeys);
					foreach ($centkeys as $key => $value) {
						unset($check_cent_text[$key]);
					}
					$check_cent_text[] = $final_centtxt;
					$keyword_text = implode(' ',$check_cent_text);
				}
				//for bug #6390 end


				// murugan changes in Sep 16 for search End
				// #### popular keyword search handler #################
				if (!empty($keyword_text)) {
					// build's a usable database of recent search keywords
					handle_search_keywords($keyword_text, $ilance->GPC['mode']);
				}

				// #### BEGIN SEARCH SQL QUERY #########################
				//$sqlquery['groupby'] = "GROUP BY p.project_id";

				//june 1 pagenagation 50
				$sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

				// #### accepted display sorting orders ################
				$acceptedsort = array('01', '02', '11', '12', '21', '22', '31', '32', '41', '42', '51', '52', '61', '62', '71', '72', '81', '82', '91', '92', '101', '102', '111', '112', '333', '335');
				//suku
				// #### build our core sql search pattern fields and store them in an array for later usage
				$surfer_id = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;

				$sqlquery['fields'] = " p.project_id,
												p.featured,
												p.reserve,
												p.bold,
												p.highlite,
												p.buynow_qty,
												p.buynow,
												p.buynow_price,
												p.currentprice,
												p.cid,
												p.description,
												p.date_starts,
												p.date_added,
												p.date_end,
												p.user_id,
												p.visible,
												p.views,
												p.project_title,
												p.additional_info,
												p.bids,
												p.status,
												p.close_date,
												p.project_details,
												p.project_type,
												p.bid_details,
												p.startprice,
												p.retailprice,
												p.filtered_auctiontype,
												p.filtered_budgetid,
												p.donation,
												p.charityid,
												p.donationpercentage,
												UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('"	 . DATETIME24H . "') AS mytime,
												UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('"	 . DATETIME24H . "') AS starttime,
												p.currencyid,
												p.countryid AS country,
												p.city,
												p.state,
												p.zipcode AS zip_code,
                                                p.hotlists,
												u.rating,
												u.score,
												(SELECT COUNT(attachid) AS picture_count FROM "	 . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,
												(SELECT user_id from  "	 . DB_PREFIX . "project_bids where p.project_id = project_id order by bidamount desc,date_added asc limit 1) as winning_user_id,
												(SELECT filename from  "	 . DB_PREFIX . "attachment where p.project_id = project_id and attachtype = 'itemphoto' limit 1) as filehash,
												(SELECT maxamount from  "	 . DB_PREFIX . "proxybid where p.project_id = project_id and user_id = '" . $surfer_id . "' limit 1 ) as maxamount,
												(SELECT comment FROM " . DB_PREFIX . "watchlist WHERE watching_project_id= p.project_id AND user_id='" . $surfer_id . "') as comment
												"	;

				$sqlquery['from'] = "FROM " . DB_PREFIX . "projects AS p";

				$sqlquery['leftjoin'] = "LEFT JOIN " . DB_PREFIX . "users u ON p.user_id = u.user_id " . ((isset($ilance->GPC['images']) AND $ilance->GPC['images'])
					? "LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id" : "");

				// $sqlquery['leftjoin_attachment'] = "LEFT JOIN " . DB_PREFIX . "attachment att ON p.project_id = att.project_id and att.attachtype = 'itemphoto'";

				//$sqlquery['leftjoin_proxy'] = "LEFT JOIN " . DB_PREFIX . "proxybid pb ON p.project_id = pb.project_id and pb.user_id = '".$surfer_id."'";

				// #### hook below is useful for changing any specifics from the above
				($apihook = $ilance->api('search_query_fields')) ? eval($apihook) : false;

				// #### categories #####################################
				$sqlquery['categories'] = '';
				//suku
				if (!empty($ilance->GPC['cid']) or isset($ilance->GPC['series'])) {

					$subcategorylist = $subcatname = '';
					$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : '0';

					//suku
					if (isset($ilance->GPC['series']) and $ilance->GPC['series'] > 0 and $cid == 0) {
						$series_id = $ilance->db->escape_string(intval($ilance->GPC['series']));
						$cmode = $ilance->GPC['mode'] . 'series';
						$ilance->categories_parser = construct_object('api.categories_parser');
						$series_details = $ilance->categories_parser->fetch_coin_series(0, $series_id);
						$denomination_detail = $ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);
						$series_save_search = $series_details;
						$subcatname .= ', <span class="black">' . $series_details['coin_series_name'] . '</span>';
						$childrenids = $ilance->categories_parser->fetch_children_pcgs($series_id);
						$navcrumb = array();
						//murugan june 18
						//sekar on oct 13 for bug
						//$navcrumb["$ilpage[denomination]?denomination=".$denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];
						//Karthik on Nov-01 for SEO Changes

						//$navcrumb["$ilpage[denomination]?denomination=".$denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];

						//new change on Dec-04

						if ($ilance->GPC['ended'] OR $ilance->GPC['completed']) {

							$CoinPrices = 'Coin Prices';

							$navcrumb["CoinPrices"] = $CoinPrices;

							if ($ilconfig['globalauctionsettings_seourls']) {
								$nav_url = HTTP_SERVER . CoinPrices . '/' . $denomination_detail['denomination_unique_no'] . '/' . construct_seo_url_name($denomination_detail['denomination_long']);
							} else {
								$nav_url = $ilpage['denomination'] . '?denomination=' . $denomination_detail['denomination_unique_no'] . '&ended=1';
							}
							
							if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
								$navcrumb[$nav_url.'/'.$ilance->GPC['date_end']] = $denomination_detail['denomination_long'];
							else
								$navcrumb[$nav_url] = $denomination_detail['denomination_long'];

							
							
							if ($ilconfig['globalauctionsettings_seourls']) {

								$nav_url = HTTP_SERVER . CoinPrices . '/' . SeriesCoin . '/' . $series_details['coin_series_unique_no'] . '/' . construct_seo_url_name($series_details['coin_series_name']);

							} else {
								$nav_url = $ilpage['search'] . '?mode=product&series="' . $series_details['coin_series_unique_no'] . '"=' . $series_details['coin_series_name'] . '&ended=1';
							}

							if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
								$navcrumb[$nav_url.'/'.$ilance->GPC['date_end']] = $series_details['coin_series_name'];
							else
								$navcrumb[$nav_url] = $series_details['coin_series_name'];

						} else {

							if ($ilconfig['globalauctionsettings_seourls']) {

								$nav_url = HTTP_SERVER . Denomination . '/' . $denomination_detail['denomination_unique_no'] . '/' . construct_seo_url_name($denomination_detail['denomination_long']);

							} else {

								$nav_url = $ilpage['denomination'] . '?denomination=' . $denomination_detail['denomination_unique_no'];

							}

							//end nov-01

							if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
							{
								$navcrumb[$nav_url.'/'.$ilance->GPC['date_end']] = $denomination_detail['denomination_long'];
								$navcrumb["Series/" . $series_details['coin_series_unique_no'] . "/" . construct_seo_url_name($series_details['coin_series_name']) . "/".$ilance->GPC['date_end']] = $series_details['coin_series_name'];
							}	
							else	
							{
								$navcrumb[$nav_url] = $denomination_detail['denomination_long'];
								$navcrumb["Series/" . $series_details['coin_series_unique_no'] . "/" . construct_seo_url_name($series_details['coin_series_name']) . ""] = $series_details['coin_series_name'];
							}

							$page_title = $series_details['coin_series_name'] . ' at ' . SITE_NAME;
							$area_title = $series_details['coin_series_name'] . ' at ' . SITE_NAME;
						}

						$subcategorylist .= $childrenids;

						//june 29
						$duplicatecid = explode(',', $childrenids);

						foreach ($duplicatecid as $element) {

							$txt[] = intval($element);

						}

						$subcidinsert = implode(',', $txt);

						$sqlquery['categories'] .= "AND p.coin_series_unique_no = '" . $series_id . "'";
					}

					if ($cid > 0) {
						$cmode = $ilance->GPC['mode'] . 'cat';
						$subcatname .= ', <span class="black">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], $cid) . '</span>';
						$childrenids = $ilance->categories->fetch_children_ids($cid, $ilance->GPC['mode']);

						$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';

						if (!empty($subcatname)) {
							handle_search_verbose('<span class="gray">' . $phrase['_in'] . '</span> <span class="black">' . mb_substr($subcatname, 1) . '</span>, ');
							handle_search_verbose_save($phrase['_categories'] . ': <strong>' . mb_substr($subcatname, 1) . '</strong>, ');
						}

						$sqlquery['categories'] .= "AND p.coin_series_unique_no = '" . $series_id . "'";

						// #### update category view count #####
						add_category_viewcount(intval($ilance->GPC['cid']));
						$cat_details = $ilance->categories_parser->fetch_coin_class(0, $ilance->GPC['cid']);

						$series_details = $ilance->categories_parser->fetch_coin_series(0,  $series_id);
						$denomination_detail = $ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);

						$subcatname .= ', <span class="black">' . $series_details['coin_series_name'] . '</span>';
						$childrenids = $ilance->categories_parser->fetch_children_pcgs($series_id);
						$navcrumb = array();
						//murugan june 18

						if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
						{
							$navcrumb["$ilpage[denomination]?denomination=" . $denomination_detail['denomination_unique_no'].'/'.$ilance->GPC['date_end']] = $denomination_detail['denomination_long'];
							$navcrumb["$ilpage[search]?mode=product&series=" . $series_details['coin_series_unique_no'].'/'.$ilance->GPC['date_end']] = $series_details['coin_series_name'];
						}	
						else	
						{
							$navcrumb["$ilpage[denomination]?denomination=" . $denomination_detail['denomination_unique_no']] = $denomination_detail['denomination_long'];
							$navcrumb["$ilpage[search]?mode=product&series=" . $series_details['coin_series_unique_no']] = $series_details['coin_series_name'];
						}
						

					}
				}

				unset($cmode, $subcatname, $childrenids, $subcategorylist);

				// #### finalize our display order for search results ##
				if (isset($ilance->GPC['sort']) AND !empty($ilance->GPC['sort']) AND in_array($ilance->GPC['sort'], $acceptedsort)) {

					$sphrase = fetch_sort_options($project_state);
					$tphrase = $sphrase[$ilance->GPC['sort']];

					$sortconditions = sortable_array_handler('listings');

					if (strlen($sortconditions[$ilance->GPC['sort']]['field']) > 0) {
						$sqlquery['orderby'] = 'ORDER BY ' . $sortconditions[$ilance->GPC['sort']]['field'] . ' ' . $sortconditions[$ilance->GPC['sort']]['sort'] . ' ' . $sortconditions[$ilance->GPC['sort']]['extra'];
					}

					unset($sphrase, $tphrase);
				}

				// #### default sort display order if none selected ####
				else {
					$ilance->GPC['sort'] = '01';
					//karthik on july26 for sort order
					if ($ilance->GPC['ended']) {
						$sqlquery['orderby'] = "ORDER BY (p.date_end) DESC";
					} else {
						$sqlquery['orderby'] = "ORDER BY (p.date_end) ASC";
					}

					$sphrase = fetch_sort_options($project_state);
					$tphrase = $sphrase['01'];

					unset($sphrase, $tphrase);
				}
				$ilance->GPC['listing_type'] = isset($ilance->GPC['listing_type']) ? $ilance->GPC['listing_type'] : null;
				//Tamil for 3222 * Starts
				if ((isset($ilance->GPC['ended']) || (!empty($ilance->GPC['listing_type']) and ($ilance->GPC['listing_type'] == '4'))) && $ilance->GPC['sort'] == '01') {
					$sqlquery['orderby'] = "ORDER BY (p.date_end) DESC";
				}
				//Tamil for 3222 * Ends

				// #### hold display order for modals as sort is removed due to main search bar above listings
				$sort = $ilance->GPC['sort'];

				// #### search options: is user hiding their own results?
				$sqlquery['hidequery'] = '';
				if ($selected['hidelisted'] == 'true' AND !empty($_SESSION['ilancedata']['user']['userid'])) {
					$sqlquery['hidequery'] = "AND (u.user_id != '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";

					handle_search_verbose('<span class="black">' . $phrase['_excluding_results_that_are_listed_by_me'] . '</span>, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_excluding_results_that_are_listed_by_me_uppercase'] . '</strong>, ');
				}

				// #### filter search method (titles only or everything)
				$titlesonly = isset($ilance->GPC['titlesonly']) ? intval($ilance->GPC['titlesonly']) : '-1';
				if ($titlesonly == '-1') {
					//handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_search_entire_auctions'] . '</strong>, ');
				} else {
					$removeurl = rewrite_url($scriptpage, 'titlesonly=' . $ilance->GPC['titlesonly']);

					//handle_search_verbose('<span class="black">' . $phrase['_searching_keywords_in_titles_only'] . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
					//handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_search_auction_titles_only'] . '</strong>, ');
				}

				// #### search exact username? #########################
				$sqlquery['userquery'] = $clear_searchuser = $clear_searchuser_url = '';

				// #### search via auction type ########################
				$show['allbuyingformats'] = false;
				$sqlquery['projectdetails'] = $buyingformats = '';
				if (empty($ilance->GPC['buynow']) AND empty($ilance->GPC['auction']) AND empty($ilance->GPC['lub']) AND empty($ilance->GPC['scheduled']) AND empty($ilance->GPC['inviteonly']) AND empty($ilance->GPC['penny'])) {
					$show['allbuyingformats'] = true;
					if ($ilance->GPC['mode'] == 'product') {
						//handle_search_verbose_save($phrase['_buying_formats'] . ': <strong>' . $phrase['_all'] . '</strong>, ');
					} else {
						//handle_search_verbose_save($phrase['_hiring_formats'] . ': <strong>' . $phrase['_all'] . '</strong>, ');
					}
				} else {
					$removeurl = $scriptpage;
					// #### include auctions #######################
					if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction']) {

						$removeurl = rewrite_url($scriptpage, 'auction=' . $ilance->GPC['auction']);
						//new change may13
						//$sqlquery['projectdetails'] .= "AND (p.project_details = 'public') ";
						$sqlquery['projectdetails'] .= "AND (p.buynow = '0' and p.filtered_auctiontype = 'regular') ";
						$buyingformats .= ($ilance->GPC['mode'] == 'product') ? $phrase['_auction'] . ', ' : $phrase['_reverse_auction'] . ', ';
					}

					// #### filter auctions with buynow available ##########
					if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow']) {
						$removeurl = rewrite_url($scriptpage, 'buynow=' . $ilance->GPC['buynow']);
						$sqlquery['projectdetails'] .= "AND (p.buynow = '1') ";
						$buyingformats .= $phrase['_buy_now'] . ', ';
						//$sqlquery['options'] .= "AND (p.buynow = '1') ";
					}

					// #### include invite only auctions ###########
					if (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly']) {
						$removeurl = rewrite_url($scriptpage, 'inviteonly=' . $ilance->GPC['inviteonly']);
						$sqlquery['projectdetails'] .= "AND (p.project_details = 'invite_only') ";
						$buyingformats .= $phrase['_invite_only'] . ', ';
					}

					// #### include upcoming scheduled events ######
					if (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled']) {
						$removeurl = rewrite_url($scriptpage, 'scheduled=' . $ilance->GPC['scheduled']);
						$sqlquery['projectdetails'] .= "AND (p.project_details = 'realtime') ";
						$buyingformats .= $phrase['_scheduled'] . ', ';
					}

					// #### include lowest unique bid events #######
					if (isset($ilance->GPC['lub']) AND $ilance->GPC['lub']) {
						$removeurl = rewrite_url($scriptpage, 'lub=' . $ilance->GPC['lub']);
						$sqlquery['projectdetails'] .= "AND (p.project_details = 'unique') ";
						$buyingformats .= $phrase['_lowest_unique_bid'] . ', ';
					}

					// #### include penny auction events ###########
					if (isset($ilance->GPC['penny']) AND $ilance->GPC['penny']) {
						$removeurl = rewrite_url($scriptpage, 'penny=' . $ilance->GPC['penny']);
						$sqlquery['projectdetails'] .= "AND (p.project_details = 'penny') ";
						$buyingformats .= $phrase['_penny_auction'] . ', ';
					}

					if (!empty($buyingformats)) {
						$buyingformats = substr($buyingformats, 0, -2);

						if ($ilance->GPC['mode'] == 'product') {
							handle_search_verbose('<span class="black"><!--<span class="gray">' . $phrase['_buying_formats'] . ':</span> -->' . $buyingformats . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
							handle_search_verbose_save($phrase['_buying_formats'] . ': <strong>' . $buyingformats . '</strong>, ');
						} else {
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
				$StopWords = array("coins)","a", "able", "about", "across", "after", "all", "almost", "also", "am", "among", "an", "and", "any", "are", "as", "at", "be", "because", "been", "but", "by", "can", "cannot", "could", "dear", "did", "do", "does", "either", "else", "ever", "every", "for", "from", "get", "got", "had", "has", "have", "he", "her", "hers", "him", "his", "how", "however", "i", "if", "in", "into", "is", "it", "its", "just", "least", "let", "like", "likely", "may", "me", "might", "most", "must", "my", "neither", "no", "nor", "not", "of", "off", "often", "on", "only", "oh", "or", "other", "our", "own", "rather", "said", "say", "says", "she", "should", "since", "so", "some", "than", "that", "the", "their", "them", "then", "there", "these", "they", "this", "tis", "to", "too", "twas", "us", "wants", "was", "we", "were", "what", "when", "where", "which", "while", "who", "whom", "why", "will", "with", "would", "yet", "you", "your", "ain't", "aren't", "can't", "could've", "couldn't", "didn't", "doesn't", "don't", "hasn't", "he'd", "he'll", "he's", "how'd", "how'll", "how's", "i'd", "i'll", "i'm", "i've", "isn't", "it's", "might've", "mightn't", "must've", "mustn't", "shan't", "she'd", "she'll", "she's", "should've", "shouldn't", "that'll", "that's", "there's", "they'd", "they'll", "they're", "they've", "wasn't", "we'd", "we'll", "we're", "weren't", "what'd", "what's", "when'd", "when'll", "when's", "where'd", "where'll", "where's", "who'd", "who'll", "who's", "why'd", "why'll", "why's", "won't", "would've", "wouldn't", "you'd", "you'll", "you're", "you've", "first", "★", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-s", "-d", "-p", "-w", "-o", "-m", "$1", "$5", "$10", "$20", "$25", "$50", "$100", "rd", "v.d.b.", "morg", "+", "plus", "two", "one", "three", "four", "five", "six", "seven", "eight", "nine", "ten", "twenty", "new");

				$StopWords_replace = array("s", "d", "p", "w", "o", "m");
				$with_hyphen = array("one","two","three","twenty","five");

				// #### build our sql state based on keyword input #####
				if (isset($keyword_text) AND !empty($keyword_text)) {

					$keyword_formatted .= '<strong>' . $keyword_text . '</strong>, ';
					$keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
					$keyword_formatted_favtext = $keyword_formatted;

					if (strcmp(intval($keyword_text), $keyword_text) == 0) {
						$sqlquery['keywords'] .= "AND (p.project_id = '$keyword_text' OR p.project_title LIKE '%$keyword_text%')";
					} else {
						//for replacing space and nochar with a hypen between year and mintmark
						//$keyword_text = preg_replace('/((?<=[0-9]{4}) (?=[sdpwocccmSDPWOCCCM])|(?<=[0-9]{4})(?=[sdpwocccmSDPWOCCCM]))/','-', $keyword_text);
						$keyword_text = preg_replace('/((?<=[0-9]{4}) (?=[sdpwocccmSDPWOCCCM]{1,2}($|\s))|(?<=[0-9]{4})(?=[sdpwocccmSDPWOCCCM]))/', '-', $keyword_text);
						$keyword_text = trim($keyword_text);

						//5592
						if (strpos($keyword_text, "/") > 0) {
							$arrayss = explode(" ", $keyword_text);
							if (isset($arrayss[1]) && strpos($arrayss[1], "/") > 0) {
								foreach ($arrayss as $key => $text) {
									if ($key == 0) {
										$keywrdtxt = $text;
									} else if ($key == 1) {
										$keywrdtxt .= '-' . $text;
									} else {
										$keywrdtxt .= ' ' . $text;
									}

								}
								$keyword_text = $keywrdtxt; 	//exit;
							}

						}

						$array = explode(" ", $keyword_text);
						$sqlquery['keywords'] = '';

						foreach ($array as $key => $keyword) {

							if (in_array(strtolower($keyword), $StopWords_replace)) {
								$keyword = substr_replace("-", $keyword, 1);
							}

							if (stristr($keyword, 'FBL')) {
								$keyword = str_replace('FBL', 'FL', $keyword);
							}

							if (!in_array(strtolower($keyword), $StopWords)) {
								if (strlen($keyword) == 1) {
									$sqlquery['keywords'] .= " AND  (p.project_title like '%".$ilance->db->escape_string($keyword)." %' OR p.project_title like '% ".$ilance->db->escape_string($keyword)."%') ";
								} else if ($keyword == 'together') {
									$sqlquery['keywords'] .= " AND (p.project_title LIKE '%together %' OR p.description LIKE '%together %')";
								} else if (strpos($keyword, "-") > 0 AND strpos($keyword, "+") == 0) {
									//$sqlquery['keywords'] .= ' AND MATCH (p.project_title,p.description) AGAINST (\'"+' . $ilance->db->escape_string($keyword) . '"\' IN BOOLEAN MODE)';
									$sqlquery['keywords'] .= " AND  ((p.project_title like '%".$ilance->db->escape_string($keyword)." %' OR p.project_title like '% ".$ilance->db->escape_string($keyword)."%') OR (p.description like '%".$ilance->db->escape_string($keyword)." %' OR p.description like '% ".$ilance->db->escape_string($keyword)."%')) ";
								} else if (strcasecmp($keyword, 'C') == 0) {
									$sqlquery['keywords'] .= " AND (p.project_title LIKE '%-c %')";
								} else if (strpos($keyword, "+") > 0 AND substr($keyword, -1) == '+' AND strlen($keyword) > 1) {
									$sqlquery['keywords'] .= " AND  (p.project_title like '%" . $ilance->db->escape_string($keyword) . "%') ";
								} else if (strpos($keyword, "/")  !== false AND strlen($keyword) > 1) {
									$sqlquery['keywords'] .= " AND  (p.project_title like '%" . $ilance->db->escape_string($keyword) . "%') ";
								}
								else if (stripos($keyword, "through")  !== false AND strlen($keyword) > 1) {
									$sqlquery['keywords'] .= " AND  (p.project_title like '%" . $ilance->db->escape_string($keyword) . "%') ";
								}
								else {
									$sqlquery['keywords'] .= ' AND MATCH (p.project_title,p.description) AGAINST ("+' . $ilance->db->escape_string($keyword) . '" IN BOOLEAN MODE)';
								}

							} else
							if (strtolower($keyword) == 'rd') {
								$sqlquery['keywords'] .= " AND  (p.project_title like '% " . $ilance->db->escape_string($keyword) . "%' OR p.description like '% ".$ilance->db->escape_string($keyword)."%' ) ";
							} else {

								if (in_array(strtolower($keyword), $with_hyphen)) 
									$sqlquery['keywords'] .= " AND  (p.project_title like '%" . $ilance->db->escape_string($keyword) . " %' OR p.project_title like '% " . $ilance->db->escape_string($keyword) . "%'  OR p.description like '%".$ilance->db->escape_string($keyword)." %' )";
								else	
									$sqlquery['keywords'] .= " AND  (p.project_title like '%" . $ilance->db->escape_string($keyword) . " %' OR p.description like '%".$ilance->db->escape_string($keyword)." %' )";
							}

						}
					}

					if (strcasecmp($keyword_text, '-C') == 0) {
						$sqlquery['keywords'] = '';
						$sqlquery['keywords'] .= "
                                                            AND (p.project_id != '-CC' OR p.project_title NOT LIKE '%-CC')
                                                            AND (p.project_id = '$keyword_text' OR p.project_title LIKE '%$keyword_text %')
                                                            ";
					}
					if (strcasecmp($keyword_text, 'C') == 0) {

						$sqlquery['keywords'] = '';
						$sqlquery['keywords'] .= " AND (p.project_title LIKE '%-c %')";
					}
					if (strcasecmp($keyword_text, '-CC') == 0) {
						$sqlquery['keywords'] = '';
						$sqlquery['keywords'] .= "AND ( p.project_title  LIKE '%-CC %') ";
					}

				}
				//echo $sqlquery['keywords'];
				$sqlquery['options'] = '';

				// #### filter nonprofit assigned listings #############
				if (isset($ilance->GPC['donation']) AND $ilance->GPC['donation']) {
					$removeurl = rewrite_url($scriptpage, 'donation=' . $ilance->GPC['donation']);
					$sqlquery['options'] .= "AND (p.donation = '1') ";

					handle_search_verbose('<span class="black">' . $phrase['_including_nonprofits'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_including_nonprofits'] . '</strong>, ');
				}

				if (isset($ilance->GPC['charityid']) AND $ilance->GPC['charityid'] > 0) {
					$removeurl = rewrite_url($scriptpage, 'charityid=' . intval($ilance->GPC['charityid']));
					$sqlquery['options'] .= "AND (p.charityid = '" . intval($ilance->GPC['charityid']) . "') ";

					$tmp = fetch_charity_details(intval($ilance->GPC['charityid']));

					handle_search_verbose('<span class="black">' . $phrase['_nonprofit'] . ':</span> <span class="gray"><strong>' . $tmp['title'] . '</strong></span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_nonprofit'] . ': ' . $tmp['title'] . '</strong>, ');
					unset($tmp);
				}

				// #### filter escrow secured listings #################
				if (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow']) {
					$removeurl = rewrite_url($scriptpage, 'escrow=' . $ilance->GPC['escrow']);
					$sqlquery['options'] .= "AND (p.filter_escrow = '1') ";

					handle_search_verbose('<span class="black">' . $phrase['_showing_services_that_require_secure_escrow'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_services_that_require_secure_escrow'] . '</strong>, ');
				}

				// #### filter auctions with public message boards #####
				if (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard']) {
					$removeurl = rewrite_url($scriptpage, 'publicboard=' . $ilance->GPC['publicboard']);
					$sqlquery['options'] .= "AND (p.filter_publicboard = '1') ";

					handle_search_verbose('<span class="black">' . $phrase['_showing_listings_that_allow_public_message_board'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_listings_that_allow_public_message_board'] . '</strong>, ');
				}

				// #### filter auctions with free shipping #############
				if (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping']) {
					$removeurl = rewrite_url($scriptpage, 'freeshipping=' . $ilance->GPC['freeshipping']);
					/*$sqlquery['options'] .= "AND (sd.freeshipping_1 = '1' OR sd.freeshipping_2 = '1' OR sd.freeshipping_3 = '1' OR sd.freeshipping_4 = '1' OR sd.freeshipping_5 = '1') ";
				 */
					handle_search_verbose('<span class="black">' . $phrase['_listing_items_with_free_shipping'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_listing_items_with_free_shipping'] . '</strong>, ');
				}

				// #### filter auctions listed as lots format ##########
				if (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots']) {
					$removeurl = rewrite_url($scriptpage, 'listedaslots=' . $ilance->GPC['listedaslots']);
					$sqlquery['options'] .= "AND (p.filtered_auctiontype = 'fixed' AND p.buynow_price > 0 AND p.buynow_qty > 0) ";

					handle_search_verbose('<span class="black">' . $phrase['_showing_items_listed_as_lots'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_items_listed_as_lots'] . '</strong>, ');
				}

				// #### filter auctions only with images? ##############
				if (isset($ilance->GPC['images']) AND $ilance->GPC['images']) {
					$removeurl = rewrite_url($scriptpage, 'images=' . $ilance->GPC['images']);
					$sqlquery['options'] .= "AND (a.attachtype = 'itemphoto' AND a.user_id = p.user_id) ";

					handle_search_verbose('<span class="black">' . $phrase['_showing_only_items_with_images'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_showing_only_items_with_images'] . '</strong>, ');
				}
				// #### include completed events ###############
				if (isset($ilance->GPC['completed']) AND $ilance->GPC['completed']) {
					$removeurl = rewrite_url($scriptpage, 'completed=' . $ilance->GPC['completed']);
					//$sqlquery['projectdetails'] .= "AND (p.haswinner = '1' OR p.hasbuynowwinner = '1') ";
					$sqlquery['timestamp'] = "";
					$sqlquery['projectstatus'] = "AND (p.status = 'expired')";

					handle_search_verbose('<span class="black">' . $phrase['_show_only_completed_listings'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_show_only_completed_listings'] . '</strong>, ');
				}

				//5086
	            if (isset($ilance->GPC['sold']) AND $ilance->GPC['sold']) 
	            {
	                $sqlquery['projectdetails'] .= " AND ((p.haswinner = '1' AND p.winner_user_id > 0) OR p.hasbuynowwinner = '1') ";
	                $completed_url_var='&amp;sold=1&amp;ended=1';
	            }

				// #### currency selector ##############################
				if ($ilconfig['globalserverlocale_currencyselector']) {
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

					if (isset($ilance->GPC['cur']) AND $ilance->GPC['cur'] != '') {
						//$this->currencies[$currencies['currency_id']
						$curs = '';
						if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == true) {
							$temp = explode(',', $ilance->GPC['cur']);
							foreach ($temp AS $key => $value) {
								if ($value != '') {
									$curs .= $ilance->currency->currencies[$value]['currency_abbrev'] . ', ';
								}
							}
							if (!empty($curs)) {
								$curs = substr($curs, 0, -2);
							}
							unset($temp);
						} else if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == false) {
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
				if (isset($ilance->GPC['endstart'])) {
					$removeurl = rewrite_url($scriptpage, 'endstart=' . $ilance->GPC['endstart']);
					switch ($ilance->GPC['endstart']) {
					case '1':
							{
								// ending within
								if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1') {
									$sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_ADD', 'p.date_end', '<=');
								}

								handle_search_verbose('<span class="black">' . $phrase['_ending_within_lower'] . ' ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span>' . (($ilance->GPC['endstart_filter'] != '-1') ? ' <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ' : ', '));
								handle_search_verbose_save($phrase['_listings'] . ': ' . $phrase['_ending_within_lower'] . ' <strong>' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
								break;
							}
					case '2':
							{
								// ending in more than
								if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1') {
									$sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_ADD', 'p.date_end', '>=');
								}

								handle_search_verbose('<span class="black">' . $phrase['_ending_in_more_than_lower'] . ' ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
								handle_search_verbose_save($phrase['_listings'] . ': ' . $phrase['_ending_in_more_than_lower'] . ' <strong>' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
								break;
							}
					case '3':
							{
								// started within
								if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1') {
									$sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_SUB', 'p.date_added', '>=');
								}

								handle_search_verbose('<span class="black">' . $phrase['_started_within_lower'] . ' ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>, ');
								handle_search_verbose_save($phrase['_listings'] . ': ' . $phrase['_started_within_lower'] . ' <strong>' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
								break;
							}
					}
				}

				// #### filter listings with non-disclosed budgets #####
				if (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] == '-1' AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0) {
					$removeurl = rewrite_url($scriptpage, 'budget=' . $ilance->GPC['budget']);
					$clear_budgetrange = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					$sqlquery['options'] .= "AND (p.filter_budget = '0') ";

					handle_search_verbose('<span class="black">' . $phrase['_showing_services_with_nondisclosed_budgets'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ' <strong>' . $phrase['_showing_services_with_nondisclosed_budgets'] . '</strong>, ');
				} else if (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] > 0 AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0) {
					$overview = $ilance->auction->construct_budget_overview(intval($ilance->GPC['cid']), intval($ilance->GPC['budget']));
					$removeurl = rewrite_url($scriptpage, 'budget=' . intval($ilance->GPC['budget']));
					$sqlquery['options'] .= "AND (p.filter_budget = '1' AND p.filtered_budgetid = '" . intval($ilance->GPC['budget']) . "') ";

					handle_search_verbose('<span class="black">' . $phrase['_budget_range'] . ': ' . $overview . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
					handle_search_verbose_save($phrase['_filter'] . ' <strong>' . $phrase['_budget'] . ': ' . $overview . '</strong>, ');
					unset($overview);
				} else {
					$ilance->GPC['budget'] = '';
				}

				// #### search number of bids range ####################
				if (!empty($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] != '-1') {
					$removeurl = rewrite_url($php_self, 'bidrange=' . $ilance->GPC['bidrange']);

					// #### link to clear region from left nav menu header
					$clear_bidrange = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';

					switch ($ilance->GPC['bidrange']) {
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
								handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_between_ten_and_twenty_bids_placed'] . '</strong><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
								break;
							}
					case '3':
							{
								$sqlquery['options'] .= "AND (p.bids > 20) ";

								handle_search_verbose('<span class="black">' . $phrase['_with_more_than_twenty_bids_placed'] . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
								handle_search_verbose_save($phrase['_filter'] . ': <strong>' . $phrase['_with_more_than_twenty_bids_placed'] . '</strong>, ');
								break;
							}
					}
				} else {
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

				//sekar works on back to search on july 28
				$_SESSION['ilancedata']['user']['search'] = $php_self;
				$_SESSION['ilancedata']['user']['denomin'] = '';
				// #### finalize country verbose text so it's placed after the region

				// #### search via price range #########################
				$sqlquery['pricerange'] = $clear_price = '';
				$sqlquery['grading_service'] = $sqlquery['year_range'] = $sqlquery['grade_range'] = $sqlquery['bid_range'] = $sqlquery['listing_type'] = $sqlquery['join_coins'] = $sqlquery['denomination'] = '';
				if ($show['mode_product']) {
					if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0) {
						$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
						$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
						$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';

						$sqlquery['pricerange'] .= "AND (p.currentprice >= " . intval($ilance->GPC['fromprice']) . " ";
						handle_search_verbose('<span class="black"><!--' . $phrase['_min_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong></span> &ndash; ');
						handle_search_verbose_save($phrase['_min_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong>, ');
					} else {
						$sqlquery['pricerange'] .= "AND (p.currentprice >= 0 ";
					}

					if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0) {
						$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
						$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
						$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';

						$sqlquery['pricerange'] .= "AND p.currentprice <= " . intval($ilance->GPC['toprice']) . ") ";
						handle_search_verbose('<span class="black"><!--' . $phrase['_max_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
						handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong>, ');
					} else {
						$sqlquery['pricerange'] .= ")";
						handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $phrase['_unlimited'] . '</strong>, ');
					}

					//Tamil bug 2389 starts

					if (!empty($ilance->GPC['grading_service']) || !empty($ilance->GPC['fromyear']) || !empty($ilance->GPC['toyear']) || !empty($ilance->GPC['grade_range_1']) || !empty($ilance->GPC['grade_range_2']) || !empty($ilance->GPC['frombid']) || !empty($ilance->GPC['tobid']) || !empty($ilance->GPC['listing_type']) || !empty($ilance->GPC['denom_all']) || !empty($ilance->GPC['denomination'])) {

						//denomination
						if (!empty($ilance->GPC['denom_all']) || !empty($ilance->GPC['denomination'])) {

							if (!empty($ilance->GPC['denom_all']) && empty($ilance->GPC['denomination'])) {

								$sqlquery['denomination'] = '';
							}
							if (empty($ilance->GPC['denom_all']) && !empty($ilance->GPC['denomination'])) {
								$gpc_denomination_arr = $ilance->GPC['denomination'];
								if(is_array(($gpc_denomination_arr)))
								{
									$sqlquery['denomination'] = " AND (p.coin_series_denomination_no IN(" . implode(",",array_map("intval",$gpc_denomination_arr)) . ")) ";	
								}else
								{
									$gpc_denomination_arr_1=intval($ilance->GPC['denomination']);
									$sqlquery['denomination'] = " AND p.coin_series_denomination_no=".$gpc_denomination_arr_1." ";	
								}
							}
						} else {
							$sqlquery['denomination'] = '';
						}

						//grading company

						//Tamil for 3208 starts
						if (!empty($ilance->GPC['grading_service'])) {
							$gpc_sql_grading_service_arr = $ilance->GPC['grading_service'];
							if ($gpc_sql_grading_service_arr[0] == 'ALL') {
								$sqlquery['grading_service'] = '';
							} elseif (in_array('Raw/Other', $gpc_sql_grading_service_arr) && count($gpc_sql_grading_service_arr) == 1) {

								$sqlquery['grading_service'] = " AND (p.Grading_Service ='') ";
							} /*elseif (in_array('CAC', $gpc_sql_grading_service_arr) && count($gpc_sql_grading_service_arr) == 1) {

								$sqlquery['grading_service'] = " AND (p.Cac =1) ";
							} */else {

								for ($i = 0; $i < count($gpc_sql_grading_service_arr); $i++) {
									$gpc_sql_grading_service_arr_1[] = "'" . $gpc_sql_grading_service_arr[$i] . "'";
								}

								if (in_array('CAC', $gpc_sql_grading_service_arr)) {

									$sqlquery_grading_service_cac = " OR p.Cac=1 ";
								} /*else {
									$sqlquery_grading_service_cac = " AND p.Cac=0 ";
								}*/

								if (in_array('QA', $gpc_sql_grading_service_arr)) {

									$sqlquery_grading_service_qa = " OR p.QA=1 ";
								} /*else {
									$sqlquery_grading_service_cac = " AND p.QA=0 ";
								}*/

								if (in_array('Raw/Other', $gpc_sql_grading_service_arr)) {

									$sqlquery_grading_service_raw = " OR p.Grading_Service='' ";

									$gpc_sql_grading_service_arr_1 = array_diff($gpc_sql_grading_service_arr_1, array("'Raw/Other'"));
								} else {

									$sqlquery_grading_service_raw = '';
								}

								$gpc_sql_grading_service_arr_1 = implode(",", $gpc_sql_grading_service_arr_1);

								$sqlquery['grading_service'] = " AND (p.Grading_Service IN(" . $gpc_sql_grading_service_arr_1 . ") " . $sqlquery_grading_service_cac . $sqlquery_grading_service_raw . $sqlquery_grading_service_qa. " )";

							}

						
						} else {
							$sqlquery['grading_service'] = '';
						}

						// echo $sqlquery['grading_service'];
						// echo '<br>'.$gpc_sql_grading_service_arr_1;
						//Tamil for 3208 ends

						//year range
						if (!empty($ilance->GPC['fromyear']) || !empty($ilance->GPC['toyear'])) {

							$temp = preg_split('#(?<=\d)[/+|\s|_|-]?(?=[a-z])#i', $ilance->GPC['fromyear']);
							$ilance->GPC['fromyear1'] = $temp[0];
							$ilance->GPC['mintage'] = $temp[1];

							$temp = preg_split('#(?<=\d)[/+|\s|_|-]?(?=[a-z])#i', $ilance->GPC['toyear']);
							$ilance->GPC['toyear1'] = $temp[0];
							//$ilance->GPC['mintage']=$temp[1];

							if (!empty($ilance->GPC['fromyear']) && !empty($ilance->GPC['toyear'])) {

								$sqlquery['year_range'] = " AND (p.coin_detail_year BETWEEN " . intval($ilance->GPC['fromyear1']) . " AND " . intval($ilance->GPC['toyear1']) . " ) ";
							}
							if (!empty($ilance->GPC['fromyear']) && empty($ilance->GPC['toyear'])) {

								$sqlquery['year_range'] = " AND (p.coin_detail_year BETWEEN " . intval($ilance->GPC['fromyear1']) . " AND " . date("Y") . " ) ";
							}
							if (empty($ilance->GPC['fromyear']) && !empty($ilance->GPC['toyear'])) {

								$sqlquery['year_range'] = " AND (p.coin_detail_year BETWEEN ''  AND " . intval($ilance->GPC['toyear1']) . ") ";
							}

							if (!empty($ilance->GPC['mintage'])) {
								$sqlquery['year_range'] .= " AND (p.mintmark = '" . $ilance->GPC['mintage'] . "' ) ";
							}

						}

						//grade range

						if (!empty($ilance->GPC['grade_range_1']) && !empty($ilance->GPC['grade_range_2'])) {

							$gr_1 = ($ilance->GPC['grade_range_1'] == '1') ? 0 : $ilance->GPC['grade_range_1'];
							$gr_2 = ($ilance->GPC['grade_range_2'] == '1') ? 0 : $ilance->GPC['grade_range_2'];
							$gr_1 = ($gr_1 < $gr_2) ? $gr_1 : $gr_2;
							$gr_2 = ($gr_2 > $gr_1) ? $gr_2 : $gr_1;
							
							$gr_1=$ilance->db->escape_string(intval($gr_1));
							$gr_2=$ilance->db->escape_string(intval($gr_2));
							if(isset($ilance->GPC['ungraded']) and $ilance->GPC['ungraded']==1)
								$sqlquery['grade_range'] = " AND (p.Grade BETWEEN " . $gr_1 . " AND " . $gr_2 . " or p.Grade=0) ";
							elseif(isset($ilance->GPC['ungraded']) and $ilance->GPC['ungraded']==0)
								$sqlquery['grade_range'] = " AND (p.Grade BETWEEN " . $gr_1 . " AND " . $gr_2 . " and p.Grade>0) ";
							elseif(!isset($ilance->GPC['ungraded']))
								$sqlquery['grade_range'] = " AND (p.Grade BETWEEN " . $gr_1 . " AND " . $gr_2 . " ) ";

						} else {

							$sqlquery['grade_range'] = '';
						}

						//bid range
						if (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid'] == '') {
							$ilance->GPC['tobid'] = 500;
						}

						if (!empty($ilance->GPC['frombid']) || (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid'] >= 0)) {

							$fr_b=$ilance->GPC['frombid'] = intval($ilance->GPC['frombid']);
							$to_b=$ilance->GPC['tobid'] = intval($ilance->GPC['tobid']);
							$fr_b=$ilance->db->escape_string($fr_b);
							$to_b=$ilance->db->escape_string($to_b);

							if ($fr_b == 0 AND $to_b != 500) {
								$bid_only = "AND p.filtered_auctiontype='regular'";
							} else {
								$bid_only = '';
							}

							if (!empty($fr_b) && !empty($to_b)) {

								$fr_b = ($fr_b < $to_b) ? $fr_b : $to_b;
								$to_b = ($to_b < $fr_b) ? $fr_b : $to_b;
								$sqlquery['bid_range'] = " AND (p.bids>=" . $fr_b . " AND p.bids<=" . $to_b . " " . $bid_only . " ) ";

							}

							if (!empty($fr_b) && (isset($to_b) AND $to_b >= 0)) {

								$to_b = ($to_b < $fr_b) ? $fr_b : $to_b;
								$sqlquery['bid_range'] = " AND (p.bids >= " . $fr_b . "  AND p.bids <= " . $to_b . " " . $bid_only . " )";
							}
							if (empty($fr_b) && (isset($to_b) AND $to_b >= 0)) {

								$sqlquery['bid_range'] = " AND (p.bids >= 0 AND p.bids <= " . $to_b . " " . $bid_only . " )";
							}

						}

						//listing type

						if (!empty($ilance->GPC['listing_type'])) {

							switch ($ilance->GPC['listing_type']) {
							case '1':
									{
										$sqlquery['listing_type'] = " AND (p.filtered_auctiontype='regular') ";
										break;
									}
							case '2':
									{
										$sqlquery['listing_type'] = " AND (p.filtered_auctiontype='fixed') ";
										$sqlquery['bid_range'] = '';
										break;
									}
							case '3':
									{
										$sqlquery['join_coins'] = " JOIN " . DB_PREFIX . "coins c ON p.project_id = c.coin_id AND c.nocoin > 1 ";
										break;
									}
							case '4':
									{
										//$sqlquery['timestamp'] = "AND (UNIX_TIMESTAMP(p.date_end) < UNIX_TIMESTAMP('" . DATETIME24H . "'))";
										$sqlquery['timestamp'] = " ";
										$sqlquery['projectstatus'] = "AND (p.status = 'expired')";
										break;
									}
							}
						}

					}

					//Tamil for bug 2389 ends

				}

				if ($show['mode_product']) {
					if (is_array($selected['productselected']) AND !empty($ilance->GPC['radiuszip']) AND in_array('distance', $selected['productselected'])) {
						$show['distancecolumn'] = 1;
					}
				}

				// #### searchable category questions ##################
				$sqlquery['genrequery'] = '';
				if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid'])) {
					// #### question groups selected : &qid=9.1,8.1,etc
					$qids = explode(',', $ilance->GPC['qid']);

					$tempgenrequery = '';
					$sqlquery['genrequery'] .= "AND (p.project_id IN(";

					foreach ($qids AS $keyquestionid => $keyanswerid) {
						$aids = explode('.', $keyanswerid);
						if (isset($aids[1]) AND !empty($aids[1])) {
							$questiontitle = fetch_searchable_question_title($aids[0], $project_state);
							if ($questiontitle != '') {
								$answertitle = '<span class="black">' . fetch_searchable_answer_title($aids[0], $aids[1], $project_state) . '</span>';

								$showqidurl = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url("{$project_state}catplain", intval($ilance->GPC['cid']), 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], intval($ilance->GPC['cid'])), '', 0, '', 0, 0)
								: $ilpage['search'] . print_hidden_fields(true, array('page', 'searchid', 'list'), true, '', '', $htmlentities = true, $urldecode = true);

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

					if (!empty($tempgenrequery)) {
						$tempgenrequery = (strrchr($tempgenrequery, ',')) ? substr($tempgenrequery, 0, -1) : $tempgenrequery;
						$sqlquery['genrequery'] .= $tempgenrequery;
						$sqlquery['genrequery'] .= ")) ";
					} else {
						$sqlquery['genrequery'] = '';
					}

					unset($questiontitle, $answertitle, $tempgenrequery, $qids);
				}

				// #### complete final search query parameters
				$sqlquery['select'] = "SELECT $sqlquery[fields] $sqlquery[from] $sqlquery[leftjoin] $sqlquery[join_coins] $sqlquery[leftjoin_attachment] $sqlquery[leftjoin_proxy]  WHERE p.user_id = u.user_id AND u.status = 'active' " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "";

				$sqlquery['select_count'] = "SELECT p.project_id $sqlquery[from]  $sqlquery[leftjoin] $sqlquery[join_coins] WHERE p.user_id = u.user_id AND u.status = 'active' " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "";

				//sukumar
				// #### build sql query ################################

				$SQL = "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[grading_service] $sqlquery[year_range] $sqlquery[grade_range] $sqlquery[bid_range] $sqlquery[listing_type] $sqlquery[denomination] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[orderby] $sqlquery[limit]";

				//echo $SQL;exit;
				$SQL2 = "$sqlquery[select_count] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[grading_service] $sqlquery[year_range] $sqlquery[grade_range] $sqlquery[bid_range] $sqlquery[listing_type] $sqlquery[denomination] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery]";

				$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
				$number= $ilance->db->num_rows($numberrows);

				 
				$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
				$row_count = 0;

				// #### build our search engine verbose output #########
				if (!empty($keyword_text)) {
					//$keyword_formatted = '<span>' . short_string($keyword_formatted, 90, $symbol = ' ...') . '</span>';
					$vebsave = print_search_verbose_saved('verbose_save');
					$favtext = '<div>' . $phrase['_keywords'] . ': <strong>' . stripslashes($keyword_formatted_favtext) . '</strong></div>' . $vebsave;
					$favtext = !empty($vebsave) ? mb_substr($favtext, 0, -2) : $favtext;

					$vebsave = print_search_verbose_saved('verbose');
					if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true') {
						$text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_keywords'] . ' <span class="black">' . stripslashes($keyword_formatted) . '</span>';
					} else {
						$text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $phrase['_listings_found_with_keywords'] . ' <span class="black">' . stripslashes($keyword_formatted) . '</span>.&nbsp;' . $vebsave;
						$text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
					}
					unset($vebsave);

					$text = '<span id="verbosetext">' . $text . '</span>';
				} else {
					// favorite search text results
					$vebsave = print_search_verbose_saved('verbose_save');
					$favtext = '<div>' . $phrase['_keywords'] . ': <strong>' . $phrase['_none'] . '</strong></div>' . $vebsave;
					$favtext = !empty($vebsave) ? mb_substr($favtext, 0, -2) : $favtext;

					$vebsave = print_search_verbose_saved('verbose');

					//new change on Dec-04
					if (isset($ilance->GPC['ended']) OR isset($ilance->GPC['completed'])) {

						$list = 'Coin Auctions Found';

					} else {

						$list = $phrase['_listings_found_with_no_keywords'];

					}

					if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true') {
						$text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $list;
					} else {

						$text = '<span style="font-size:19px" class="blueonly"><strong>' . $number . '</strong></span> ' . $list . ' ' . (!empty($vebsave) ? ' ' . $vebsave : $vebsave);
						$text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
					}
					unset($vebsave);

					$text = '<span id="verbosetext">' . $text . '</span>';
				}

				if ($ilconfig['globalauctionsettings_seourls'] AND $cid > 0) {
					$categoryname = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $project_state, $cid);
					$showallurl = construct_seo_url("{$project_state}catplain", $cid, 0, $categoryname, '', 0, '', 0, 0, 'qid');
				} else {
					$showallurl = $ilpage['search'] . '?mode=' . $ilance->GPC['mode'] . '&amp;sort=' . intval($ilance->GPC['sort']) . '&amp;page=' . intval($ilance->GPC['page']);
				}

				define('PHP_SELF_NOQID', $showallurl);
				$showtext = print_search_verbose_saved('verbose_filter');
				if (!empty($showtext)) {
					$showtext = mb_substr($showtext, 0, -2) . '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blue"><a href="' . $showallurl . '" rel="nofollow">' . $phrase['_show_all'] . '</a></span> ]</span>';
					//$text .= '<div style="padding-top:12px"><strong>' . $phrase['_specifics'] . '</strong>: ' . $showtext . '</div>';
					$text .= ', <span><strong>' . $showtext . '</strong></span>';
				}

				// #### save this search ###############################
				if (isset($ilance->GPC['searchid']) AND $ilance->GPC['searchid'] > 0) {
					$_SESSION['ilancedata']['user']['searchid'] = intval($ilance->GPC['searchid']);
					// todo: add hit tracker to show hit count of saved search
					$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites&amp;returnurl=' . $php_self_urlencoded . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /> ' . $phrase['_view_saved_searches'] . '</a></span> ]</span>';
					$text1 .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#update_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_update_search'] . '</a></span> ]</span>';
				} else {
					if (isset($_SESSION['ilancedata']['user']['searchid']) AND $_SESSION['ilancedata']['user']['searchid'] > 0) {
						$text1 .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#update_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_update_search'] . '</a></span> ]</span>';
						$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_save_as_favorite_search'] . '</a></span> ]</span>';
					} else {
						$text .= '&nbsp;&nbsp;<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_save_as_favorite_search'] . '</a></span> ]</span>';
					}
				}

				$metatitle = '';
				if ($cid > 0) {
					$metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $project_state, $cid);
					$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $project_state, $cid);
					$metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $project_state, $cid, $commaafter = true, $showinputkeywords = true);
				}

				if (!isset($page_title)) {
					if ($ilconfig['fulltextsearch']) {
						$page_title = ((isset($keyword_text) AND !empty($keyword_text)) ? $keyword_text . ', ' : '') . '' . $phrase['_find'] . ' ' . $keyword_text . ' ' . (($project_state == 'service') ? $phrase['_services'] : 'Coins/' . $phrase['_items']) . ' ' . ((!empty($metadescription)) ? $phrase['_in'] . ' ' . $metatitle . ', ' . $metadescription : '') . ' at ' . SITE_NAME;
					} else {
						$page_title = ((isset($keyword_text[0]) AND !empty($keyword_text)) ? $keyword_text . ', ' : '') . '' . $phrase['_find'] . ' ' . $keyword_text . ' ' . (($project_state == 'service') ? $phrase['_services'] : 'Coins/' . $phrase['_items']) . ' ' . ((!empty($metadescription)) ? $phrase['_in'] . ' ' . $metatitle . ', ' . $metadescription : '') . ' at ' . SITE_NAME;
					}
				}
				//end on aug23

				$search_results_rows = $excludelist = array();
				$result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);

				if ($ilance->db->num_rows($result) > 0) {
					$ilance->bbcode = construct_object('api.bbcode');

					while ($row = $ilance->db->fetch_array($result, DB_ASSOC)) {
						$excludelist[] = $row['project_id'];

						$td['featured'] = $row['featured'];
						$td['bold'] = $row['bold'];
						$td['highlite'] = $row['highlite'];
						$td['project_id'] = $row['project_id'];

						if ($show['mode_product']) {

							$row['project_state'] = 'product';
							$td['project_state'] = $row['project_state'];

							// auction description (may contain bbcode)
							switch ($row['project_details']) {
							case 'public':
									{
										$td['description'] = strip_vulgar_words($row['description']);
										$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
										$td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
										break;
									}

							}

							// is buynow available for this auction listing?
							$td['proxybit'] = $td['buynow'] = $td['buynowimg'] = $td['buynowtxt'] = '';
							$td['filtered_auctiontype'] = $row['filtered_auctiontype'];

							//new change herakle
							if (isset($ilance->GPC['list'])) {
								$selected['list'] = $ilance->GPC['list'];
							}

							//print_r($selected);

							if ($selected['list'] == 'list') {
								$td['buynow'] = $td['buynowimg'] = $td['buynowtxt'] = '';

								// display thumbnail
								$url = construct_seo_url('productauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);

								$borderwidth = 0;
								$bordercolor = "#ffffff";

								$pictures = $row['picture_count'];

								if (!empty($row['filehash'])) {
									$td['sample'] = '
								<div class="gallery-thumbs-cell">
								<div class="gallery-thumbs-entry">
										<div class="gallery-thumbs-main-entry">
												<div class="gallery-thumbs-wide-wrapper_search">
														<div class="gallery-thumbs-wide-inner-wrapper">
								<a href="'	 . $url . '">
								<img src="' . HTTPS_SERVER . 'image/122/89/' . $row['filehash'] . '"
								 border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" />
								 </a>
								<div class="gallery-thumbs-corner-text"><span>'	 . $pictures . ' photos</span></div>
														</div>
												</div>
										</div>
								</div>
								</div>'	;
								} else {
									$td['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
								}

								unset($url);

								// #### regular auction
								if ($row['project_details'] == 'public') {

									if ($row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'fixed' OR $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'regular') {
										$td['price'] = '';
										if ($row['filtered_auctiontype'] == 'regular') {
											// current price & buy now price
											// murugan changes on mar 01 for show bid or buy in $td['price']
											if ($row['bids'] > 0) {
												$td['price'] = ($selected['currencyconvert'] == 'true')
												? '<div class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
												: '<div class="black"><strong> Bid ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
											} else {
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
											if (!empty($_SESSION['ilancedata']['user']['userid'])) {
												//$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
												$pbit = $row['maxamount'];
												if ($pbit > 0) {

													$highbidderidtest = $row['winning_user_id'];
													// murugan on feb 25
													if ($highbidderidtest == $_SESSION['ilancedata']['user']['userid']) {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													} else {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													}

												}
												unset($pbit);
											}
										} else if ($row['filtered_auctiontype'] == 'fixed') {
											// buy now price
											$td['price'] = ($selected['currencyconvert'] == 'true')
											? '<div class="black"><strong> Buy Now ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</strong></div>'
											: '<div class="black"><strong> Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</strong></div>';

											$td['bids'] = '<div class="smaller blue"> Buy Now </div>';
										}
									}

									// #### no buy now
									else {

										if ($row['bids'] > 0) {
											//$currentbid = $ilance->bid->fetch_current_bid($row['project_id'], 1);
											$currentbid = $row['currentprice'];
											//new change on Dec-04
											$haswinner = $row['haswinner'];
											if (isset($ilance->GPC['ended']) OR isset($ilance->GPC['completed'])) {

												
												$buyer_fee = $row['buyer_fee'];
												$hammer = '<br><font color="#999999">(' . $ilance->currency->format($row['currentprice']) . '&nbsp;hammer)</font>';


												$soldprice = $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']);
                                    			$hamerprice = $ilance->currency->format($row['currentprice'], $row['currencyid']);
                                    			$encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');
                                    			

								 				if($haswinner == '1') 
								 				{
								 					if($ilconfig['settings_sold_coins_price_to_image'])
                                   	 				{
									 					if($row['highlite'])
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'&c=" />';
									 					else 
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'" />';
									 				}
									 				else
									 				{
									 					$td['price'] = '<span class="black"><strong>  Sold: ' . $soldprice . '</span><br/><span style="color: #999999;">('.$hamerprice.' hammer)</span></strong>';
									 				}
								 				}
								 				else
								 				{
								 					$td['price'] = '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
								 				}

												//$td['price'] = ($haswinner == '1')
												//? $td['price'] = '<span class="black"><strong>Sold ' . $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']) . '</strong>' . $hammer . '</span>' : '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';

											} else {

												if($haswinner == '1') 
								 				{
								 					$buyer_fee = $row['buyer_fee'];
								 					$soldprice = $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']);
                                    				$hamerprice = $ilance->currency->format($row['currentprice'], $row['currencyid']);
                                    				$encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');

                                    				if($ilconfig['settings_sold_coins_price_to_image'])
                                   	 				{
									 					if($row['highlite'])
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'&c=" />';
									 					else 
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'" />';
									 				}
									 				else
									 				{
									 					$td['price'] = '<span class="black"><strong>  Sold: ' . $soldprice . '</span><br/><span style="color: #999999;">('.$hamerprice.' hammer)</span></strong>';
									 				}
								 					
								 				}
								 				else
								 				{
								 					$td['price'] = ($selected['currencyconvert'] == 'true')
														? '<span class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $currentbid, $row['currencyid']) . '</strong></span>'
														: '<span class="black"><strong> Bid ' . $ilance->currency->format($currentbid, $row['currencyid']) . '</strong></span>';
								 				}
												

											}

											if (!empty($_SESSION['ilancedata']['user']['userid'])) {
												//$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
												$pbit = $row['maxamount'];
												if ($pbit > 0) {

													$highbidderidtest = $row['winning_user_id'];
													// murugan on feb 25
													if ($highbidderidtest == $_SESSION['ilancedata']['user']['userid']) {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													} else {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													}

												}
												unset($pbit);
											}

											$td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
										} else {
											if (isset($ilance->GPC['ended']) OR isset($ilance->GPC['completed'])) {
												$haswinner = $row['haswinner'];
												$buyer_fee = $row['buyer_fee'];
												$hammer = '<br><font color="#999999">(' . $ilance->currency->format($row['currentprice']) . '&nbsp;hammer)</font>';

												$soldprice = $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']);
                                    			$hamerprice = $ilance->currency->format($row['currentprice'], $row['currencyid']);
                                    			$encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');
		                                    
												if($haswinner == '1') 
								 				{
								 					if($ilconfig['settings_sold_coins_price_to_image'])
                                   	 				{
									 					if($row['highlite'])
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'&c=" />';
									 					else 
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'" />';
									 				}
									 				else
									 				{
									 					$td['price'] = '<span class="black"><strong>  Sold: ' . $soldprice . '</span><br/><span style="color: #999999;">('.$hamerprice.' hammer)</span></strong>';
									 				}
								 				}
								 				else
								 				{
								 					$td['price'] = '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
								 				}

												//$td['price'] = ($haswinner == '1')
												//? $td['price'] = '<span class="black"><strong>Sold ' . $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']) . '</strong>' . $hammer . '</span>' : '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
											} else {
												$td['price'] = ($selected['currencyconvert'] == 'true')
												? '<div class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['startprice'], $row['currencyid']) . '</strong></div>'
												: '<div class="black"><strong> Bid ' . $ilance->currency->format($row['startprice'], $row['currencyid']) . '</strong></div>';
											}

											$td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
										}
									}

								}
							} else if ($selected['list'] == 'gallery') {

								// display thumbnail
								$url = construct_seo_url('productauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
								$borderwidth = 0;
								$bordercolor = "#ffffff";
								$pictures = 1;

								if (!empty($row['filehash'])) {
									$td['sample'] = '<a href="' . $url . '"><img src="' . HTTPS_SERVER . 'image/150/150/' . $row['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a> ';
								} else {
									$td['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
								}
								/*$td['sample'] = ($ilconfig['globalauctionsettings_seourls'])
							? print_item_photo($url, 'thumbgallery', $row['project_id'])
							: print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumbgallery', $row['project_id']);*/
								unset($url);

								if ($row['project_details'] == 'public') {
									if ($row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'fixed' OR $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'regular') {
										if ($row['filtered_auctiontype'] == 'regular') {
											$td['bids'] = ($row['bids'] > 0)
											? '<span class="blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue">0&nbsp;' . $phrase['_bids_lower'] . '</span>';

											if ($row['bids'] == 0) {

												$td['buynow'] = '<span class="black"> Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</span>';
											} else {
												$td['buynow'] = '<span class="black">' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</span>';
											}

											// proxy bid information
											if (!empty($_SESSION['ilancedata']['user']['userid'])) {
												//$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
												$pbit = $row['maxamount'];
												if ($pbit > 0) {

													//nrw change
													$highbidderidtest = $row['winning_user_id'];
													if ($highbidderidtest == $_SESSION['ilancedata']['user']['userid']) {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													} else {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													}

												}
												unset($pbit);
											}

										} else if ($row['filtered_auctiontype'] == 'fixed') {

											$td['bids'] = '';
											$td['buynow'] = '<span class="black"><strong>Buy Now ' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</strong></span>';
										}
									}

									// #### no buy now
									else {
										if ($row['bids'] > 0) {
											//$currentbid = $ilance->bid->fetch_current_bid($row['project_id'], 1);
											$currentbid = $row['currentprice'];
											//new change on Dec-04

											if ($ilance->GPC['ended'] OR $ilance->GPC['completed']) {

												$haswinner = $row['haswinner'];
												$buyer_fee = $row['buyer_fee'];
												$hammer = '<br><font color="#999999">(' . $ilance->currency->format($row['currentprice']) . '&nbsp;hammer)</font>';

												$soldprice = $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']);
                                    			$hamerprice = $ilance->currency->format($row['currentprice'], $row['currencyid']);
                                    			$encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');

												if($haswinner == '1') 
								 				{
								 					if($ilconfig['settings_sold_coins_price_to_image'])
                                   	 				{
									 					if($row['highlite'])
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'&c=" />';
									 					else 
									 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'" />';
									 				}
									 				else
									 				{
									 					$td['price'] = '<span class="black"><strong>  Sold: ' . $soldprice . '</span><br/><span style="color: #999999;">('.$hamerprice.' hammer)</span></strong>';
									 				}
								 				}
								 				else
								 				{
								 					$td['price'] = '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
								 				}

												//$td['price'] = ($haswinner == '1')
												//? $td['price'] = '<span class="black"><strong>Sold ' . $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']) . '</strong>' . $hammer . '</span>' : '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';

											} else {
												$td['price'] = ($selected['currencyconvert'] == 'true')
												? '<span class="black"><strong> Bid ' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $currentbid, $row['currencyid']) . '</strong></span>'
												: '<span class="black"><strong> Bid ' . $ilance->currency->format($currentbid, $row['currencyid']) . '</strong></span>';
											}

											if (!empty($_SESSION['ilancedata']['user']['userid'])) {
												//$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
												$pbit = $row['maxamount'];
												if ($pbit > 0) {
													$highbidderidtest = $row['winning_user_id'];
													if ($highbidderidtest == $_SESSION['ilancedata']['user']['userid']) {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													} else {
														$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true')
														? '<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $row['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>'
														: '';
													}

												}
												unset($pbit);
											}

											$td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
										} else {

											$td['bids'] = ($row['bids'] > 0)
											? '<div class="smaller blue">' . $row['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</div>'
											: '<div class="smaller blue"> Bid </div>';
										}
									}
									//new change on Dec-04
									if ($ilance->GPC['ended'] OR $ilance->GPC['completed']) {

										$haswinner = $row['haswinner'];
										$buyer_fee = $row['buyer_fee'];
										$hammer = '<br><font color="#999999">(' . $ilance->currency->format($row['currentprice']) . '&nbsp;hammer)</font>';

										$soldprice = $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']);
                                    	$hamerprice = $ilance->currency->format($row['currentprice'], $row['currencyid']);
                                    	$encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');

										if($haswinner == '1') 
						 				{
						 					if($ilconfig['settings_sold_coins_price_to_image'])
                           	 				{
							 					if($row['highlite'])
							 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'&c=" />';
							 					else 
							 						$td['price'] = '<img width="180" height="33" src="images.php?q='.$encramnt.'" />';
							 				}
							 				else
							 				{
							 					$td['price'] = '<span class="black"><strong>  Sold: ' . $soldprice . '</span><br/><span style="color: #999999;">('.$hamerprice.' hammer)</span></strong>';
							 				}
						 					
						 				}
						 				else
						 				{
						 					$td['price'] = '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
						 				}

										//$td['price'] = ($haswinner == '1')
										//? $td['price'] = '<span class="black"><strong>Sold ' . $ilance->currency->format($row['currentprice'] + $buyer_fee, $row['currencyid']) . '</strong>' . $hammer . '</span>' : '<span class="black"><strong>Unsold ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';

									} else {

										$td['price'] = '<span class="black"><strong>Bid ' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></span>';
									}
								}

							}

	//5189 starts

							$watch_cmnts = $watch_checkd = '';
							if (isset($_SESSION['ilancedata']['user']['userid'])) 
							{
								if (!is_null($row['comment'])) 
								{
									$watch_cmnts = "<div><strong>Notes: </strong>" . $row['comment'] . "</div>";
									$watch_checkd = 'checked="checked"';
								}
							}

							// #### vijay work for hot list

							$hotlistimg = '';
							if (isset($row['hotlists']) AND $row['hotlists'] > 0) {
								$hotlistimg = '<a href="' . HTTP_SERVER .'hotlist.php" title="To view all HotList, click here"><img border="0" alt="This coin has been selected for the GreatCollections HotList" src="https://www.greatcollections.com/images/gc/hotlist.png"  style="width: 25px; height: 35px;"></a>';
							}
 
							$td['sel'] = '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" ' . $watch_checkd . ' />

                           '	 . $hotlistimg;

	////5189 ends
							//new change herakle you item
							if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $row['user_id']) {
								$owner_id = '<br><br>You own this item';
							} else {
								$owner_id = '';
							}
							//new change
							if (isset($ilance->GPC['list']) and $ilance->GPC['list'] == 'gallery') {
								$you_bid = $td['proxybit'];
							} else if (isset($selected['list']) and $selected['list'] == 'gallery') {
								$you_bid = $td['proxybit'];
							} else {
								$you_bid = '';
							}

							// is bold feature enabled?
							if ($row['bold']) {
								$td['title'] = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('productauction', 0, $row['project_id'], htmlspecialchars_uni($row['project_title']), $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) . '' . $owner_id . '<br>' . $you_bid . ''
								: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '"><strong>' . htmlspecialchars_uni($row['project_title']) . '</strong></a>' . $owner_id . '<br>' . $you_bid . '';
							} else {
								$td['title'] = ($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('productauction', 0, $row['project_id'], htmlspecialchars_uni($row['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '' . $owner_id . '<br>' . $you_bid . ''
								: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '">' . htmlspecialchars_uni($row['project_title']) . '</a>' . $owner_id . '<br><br>' . $you_bid . '';
							}

							$td['class'] = ($row['highlite']) ? $ilconfig['productupsell_highlightcolor'] : (($row_count % 2) ? 'alt1' : 'alt1');

							//new change on Dec-04
							//Tamil for 3222 * Starts
							if (isset($ilance->GPC['ended']) OR ($ilance->GPC['listing_type'] == '4')) {
								$td['timeleft'] = '<strong>' . date("F d, Y", strtotime($row['date_end'])) . '</strong>';
							} else {

								$td['timeleft'] = '<strong>' . auction_time_left_new($row, false) . '</strong>';
							}
							//Tamil for 3222 * Ends
							//$td['icons'] = $ilance->auction->auction_icons($row['project_id'], $row['user_id']);
							$td['shipping'] = '';
							$td['location'] = '' . $watch_cmnts;
							$td['views'] = $row['views'];

							($apihook = $ilance->api('search_results_products_loop')) ? eval($apihook) : false;

							$search_results_rows[] = $td;

							$row_count++;
						}

					}

					$show['no_rows_returned'] = false;
				} else {
					$show['no_rows_returned'] = true;
				}
				$series = isset($ilance->GPC['series']) ? $ilance->GPC['series'] : 0;

				// murugan changes on feb 17 for featured auction list
				// #### featured spotlight auction listings ############
				$featuredserviceauctions = $featuredproductauctions = array();
				//sukumar
				//Tamil rewritten for Bug 3046 * Starts
				$featuredproductauctions = ($show['mode_product']) ? fetch_featured_auctions_new('product', 4, 1, $cid, $keyword_text, false, $excludelist, $series, $sqlquery['denomination'], $sqlquery['grading_service'], $sqlquery['year_range'], $sqlquery['grade_range'], $sqlquery['bid_range'], $sqlquery['listing_type'], $sqlquery['join_coins'], $sqlquery['projectstatus'], $sqlquery['timestamp'], $sqlquery['keywords']) : '';
				//Tamil rewritten for Bug 3046 * Ends

				//5086
				if (isset($ilance->GPC['sold']) AND $ilance->GPC['sold'])              
				{
					$show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
				}

				// #### BUILD OUR PAGNATOR #############################
				//june1
				//vijay <h1> tag for pagination
				if (!$series == 0) {
					$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);
				} else {
					$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);
				}
				// #### PRINT OUR SEARCH RESULTS TABLE #################
				$search_results_table = print_search_results_table($search_results_rows, $project_state, $prevnext);

				$keywords = (!empty($ilance->GPC['q'])) ? un_htmlspecialchars($ilance->GPC['q']) : '';

				// #### fewer keywords search ##########################
				$fewer_keywords = print_fewer_keywords_search($keywords_array, $ilance->GPC['mode'], $number);

				// #### category budget ################################
				$budget = isset($ilance->GPC['budget']) ? intval($ilance->GPC['budget']) : '';

				// $budgetfilter = $ilance->auction_post->print_budget_logic_type_links($cid, $cattype = 'service', $budget);
				$budgettemp = $ilance->auction_post->print_budget_logic_type_js($cid, $ilance->GPC['mode'], $budget);
				if (isset($show['budgetgroups']) AND $show['budgetgroups'] AND is_array($budgettemp)) {
					$budget_slider_1 = $budgettemp[0];
					$budget_slider_2 = $budgettemp[1];
				}
				unset($budgettemp);

				if (isset($show['mode_service']) AND $show['mode_service'] OR isset($show['mode_providers']) AND $show['mode_providers']) {
					$v3left_nav = $ilance->template->print_left_nav('service', $cid, $dosubcats = 1, $displayboth = 0, $ilconfig['globalfilters_enablecategorycount'], true);
				} else {

					// pre-populate from price and to price field inputs for left nav search menu
					$fromprice = isset($ilance->GPC['fromprice']) ? sprintf("%01.2f", $ilance->GPC['fromprice']) : '';
					$toprice = isset($ilance->GPC['toprice']) ? sprintf("%01.2f", $ilance->GPC['toprice']) : '';

					$v3left_nav = $ilance->template->print_left_nav('product', $cid, $dosubcats = 1, $displayboth = 0, $ilconfig['globalfilters_enablecategorycount'], true);

				}

				// #### SAVE AS FAVORITE SEARCH OPTION #################
				if ($ilconfig['savedsearches'] AND !empty($favtext)) {
					// build search request parameters
					$favorites = array();
					foreach ($ilance->GPC AS $search => $option) {
						if ($search != 'submit' AND $search != 'search' AND $search != 'page' AND $search != 'sef') {
							$favorites[] = array($search => $option);
						}
					}
					if (!empty($favorites) AND is_array($favorites)) {
						$encrypt = serialize($favorites);
						$encrypt = urlencode($encrypt);
					}

					$favoritesearchurl = $encrypt;
					$favtext = ilance_htmlentities($favtext);
				}

				// ####  build our category breadcrumb navigator
				if ($show['mode_product']) {
					$sortmode = $mode = 'product';

				}

				$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
				$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', $sortmode);
				$city = isset($ilance->GPC['city']) ? handle_input_keywords($ilance->GPC['city']) : '';
				$state = isset($ilance->GPC['state']) ? handle_input_keywords($ilance->GPC['state']) : '';
				$zip_code = isset($ilance->GPC['zip_code']) ? handle_input_keywords($ilance->GPC['zip_code']) : '';
				$radiuszip = isset($ilance->GPC['radiuszip']) ? handle_input_keywords($ilance->GPC['radiuszip']) : '';

				$hiddenfields = print_hidden_fields(false, array('searchid', 'sef', 'cid', 'buynow', 'sort', 'images', 'freeshipping', 'listedaslots', 'budget', 'publicboard', 'escrow', 'underage', 'endstart', 'endstart_filter', 'q', 'page'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$hiddenfields_leftnav = print_hidden_fields(false, array('searchid', 'sef', 'exactname', 'searchuser', 'budget', 'country', 'auctiontype', 'buynow', 'images', 'freeshipping', 'listedaslots', 'budget', 'publicboard', 'escrow', 'underage', 'endstart', 'endstart_filter', 'page', 'radius', 'radiuscountry', 'radiuszip'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);

				($apihook = $ilance->api('search_results_auctions_end')) ? eval($apihook) : false;

				// #### DISPLAY SEARCH RESULTS VIA XML #################
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'xml') {
					if (!empty($search_results_rows) AND is_array($search_results_rows)) {
						$ilance->xml = construct_object('api.xml');

						$xml = $ilance->xml->search_to_xml($search_results_rows, false);
						echo $xml;
					}
					exit();
				}

				// #### DISPLAY SEARCH RESULTS VIA SERIALIZED ARRAY ####
				else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'array') {
					if (!empty($search_results_rows) AND is_array($search_results_rows)) {
						echo urlencode(serialize($search_results_rows));
					}
					exit();
				}

				// #### DISPLAY SEARCH RESULTS TEMPLATE ################
				else {
					// #### init our budget range slider ###################
					if (!empty($budgetfilter) OR isset($show['budgetgroups']) AND $show['budgetgroups']) {
						$onload .= "init_budgetSlider(); ";
						$onload .= (isset($ilance->GPC['budget'])) ? "set_budgetSlider('" . intval($ilance->GPC['budget']) . "'); " : "";
					}

					// attempt to correct the spelling for the user if applicable (not in use at the moment)
					$didyoumean = print_did_you_mean($keyword_text, $mode);

					// if we're a guest and we don't have the region modal cookie let's ask for it
					$cookieregion = (!empty($_COOKIE[COOKIE_PREFIX . 'region'])) ? $_COOKIE[COOKIE_PREFIX . 'region'] : '';
					$full_country_pulldown = construct_country_pulldown(0, $cookieregion, 'region', true, '', false, true, true);

					if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal'])) {
						//$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow(); ';

						// don't ask this guest for region info via popup modal for 3 days
						set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
					}

					//new changes on Dec-06
					if (isset($ilance->GPC['ended']) OR isset($ilance->GPC['completed'])) {
						$total_realized = $ilance->db->query("SELECT COUNT( * ) as total_ended
                                               FROM  "	 . DB_PREFIX . "projects
                                               WHERE  status =  'expired'
                                               OR status =  'closed'"	, 0, null, __FILE__, __LINE__);

						$ended_count = $ilance->db->fetch_array($total_realized);

						$count = number_format($ended_count['total_ended']);

						$show['total_count'] = true;

					}
					//this is the actual search result
					$page_title = "Find " . $keywords . " Coins at GreatCollections Coin Auctions & Rare Coins";
					//$metakeywords="";
					$metadescription = "Searching 1000s of " . $keywords . " certified coins at GreatCollections Coin Auctions & Rare Coin Sales";
					if (!empty($ilance->GPC['series'])) {
						$series_details = $ilance->categories_parser->fetch_coin_series(0,  $series_id);
						$denomination_details = $ilance->categories_parser->fetch_denominations($series_details['coin_series_denomination_no']);

						$series_save_search = $series_details['coin_series_name'];
						$save_search_denomination = $denomination_details['denomination_long'];
					}
					//Tamil bug 2389 starts
					$grading_comp_arr = array('ALL', 'PCGS', 'NGC', 'ANACS', 'Raw/Other', 'CAC', 'QA');

					$grading_service_dropdown = '<select name="grading_service[]" multiple>';

					$gpc_grading_arr = !empty($ilance->GPC['grading_service']) ? $ilance->GPC['grading_service'] : '';
					if (!empty($gpc_grading_arr)) {

						foreach ($grading_comp_arr as $default_grading_company) {
							if (in_array($default_grading_company, $ilance->GPC['grading_service'])) {
								$grading_service_dropdown .= '<option value="' . $default_grading_company . '" selected="selected">' . $default_grading_company . '</option>';
							} else {
								$grading_service_dropdown .= '<option value="' . $default_grading_company . '" >' . $default_grading_company . '</option>';
							}
						}
					} else {
						foreach ($grading_comp_arr as $result2_val) {
							$grading_service_dropdown .= '<option value="' . $result2_val . '" >' . $result2_val . '</option>';
						}
					}
					$grading_service_dropdown .= '</select>';

					$grade_range_dropdown_1 = '<select name="grade_range_1">';
					$grade_range_dropdown_2 = '<select name="grade_range_2">';
					$grade_range_sql = $ilance->db->query("select * from "	 . DB_PREFIX . "coin_proof where value>0");
					while ($grade_range_res = $ilance->db->fetch_array($grade_range_sql)) {
						if (isset($ilance->GPC['grade_range_1']) and $ilance->GPC['grade_range_1'] == $grade_range_res['value']) {
							$grade_range_dropdown_1 .= '<option value="' . $grade_range_res['value'] . '" selected>' . $grade_range_res['value'] . '</option>';
						} else {
							if ($grade_range_res['value'] == 1) {
								$grade_range_dropdown_1 .= '<option value="' . $grade_range_res['value'] . '" selected>' . $grade_range_res['value'] . '</option>';
							} else {
								$grade_range_dropdown_1 .= '<option value="' . $grade_range_res['value'] . '">' . $grade_range_res['value'] . '</option>';
							}

						}

						if (isset($ilance->GPC['grade_range_2'])) {

							if ($ilance->GPC['grade_range_2'] == $grade_range_res['value']) {
								$grade_range_dropdown_2 .= '<option value="' . $grade_range_res['value'] . '" selected>' . $grade_range_res['value'] . '</option>';
							} else {
								$grade_range_dropdown_2 .= '<option value="' . $grade_range_res['value'] . '">' . $grade_range_res['value'] . '</option>';
							}

						} else {

							if ($grade_range_res['value'] == '70') {
								$grade_range_dropdown_2 .= '<option value="' . $grade_range_res['value'] . '" selected>' . $grade_range_res['value'] . '</option>';
							} else {
								$grade_range_dropdown_2 .= '<option value="' . $grade_range_res['value'] . '">' . $grade_range_res['value'] . '</option>';
							}

						}

					}
					$grade_range_dropdown_1 .= '</select>';
					$grade_range_dropdown_2 .= '</select>';

					if(isset($ilance->GPC['ungraded']) and $ilance->GPC['ungraded']==1)
					{
						$ungraded_checked='checked="checked"';
					}elseif(isset($ilance->GPC['ungraded']) and $ilance->GPC['ungraded']==0)
					{
						$ungraded_checked='';
					}elseif(!isset($ilance->GPC['ungraded']))
					{
						$ungraded_checked='checked="checked"';
					}

					$fromyear = !empty($ilance->GPC['fromyear']) ? $ilance->GPC['fromyear'] : '';
					$toyear = !empty($ilance->GPC['toyear']) ? $ilance->GPC['toyear'] : date('Y');

					$frombid = !empty($ilance->GPC['frombid']) ? $ilance->GPC['frombid'] : '0';
					$tobid = (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid'] >= 0) ? $ilance->GPC['tobid'] : '500';

					//changed for bug #5480
					if (!empty($ilance->GPC['denom_all']) and ($ilance->GPC['denom_all'] == '1')) {
						$denom_checkbox = ' checked="checked" ';
						$denom_drop_is_sel = 'style="display:none;"';
						$checkbox_denom_value_all = '(Select All)';
					} else {
						$denom_checkbox = '';
						if (!empty($ilance->GPC['denomination']) || !empty($ilance->GPC['series'])) {
							$denom_drop_is_sel = '';
							$checkbox_denom_value_all = '(Edit)';
						} else {
							$denom_drop_is_sel = 'style="display:none;"';
							$checkbox_denom_value_all = '(Select All)';
						}

					}

					$denom_drop_sql = $ilance->db->query("SELECT denomination_unique_no,denomination_long FROM " . DB_PREFIX . "catalog_toplevel order by denomination_unique_no asc", 0, null, __FILE__, __LINE__);
					$product_denom_selection = '<select name="denomination[]" id="denom_dropdown"  multiple>';

					$gpc_denom_arr = isset($ilance->GPC['denomination']) ? $ilance->GPC['denomination'] : null;
					if (!empty($ilance->GPC['series'])) {
						$series_id = $ilance->db->escape_string(intval($ilance->GPC['series']));
						$series_details = $ilance->categories_parser->fetch_coin_series(0, $series_id);
					}
					if (!empty($gpc_denom_arr)) {

						while ($denom_drop_res = $ilance->db->fetch_array($denom_drop_sql)) {

							if (in_array($denom_drop_res['denomination_unique_no'], $gpc_denom_arr)) {
								$product_denom_selection .= '<option value="' . $denom_drop_res['denomination_unique_no'] . '" selected="selected">' . $denom_drop_res['denomination_long'] . '</option>';

							} else {
								$product_denom_selection .= '<option value="' . $denom_drop_res['denomination_unique_no'] . '">' . $denom_drop_res['denomination_long'] . '</option>';
							}

						}
					} else {

						while ($denom_drop_res = $ilance->db->fetch_array($denom_drop_sql)) {

							if (!empty($ilance->GPC['series'])) {

								if ($series_details['coin_series_denomination_no'] == $denom_drop_res['denomination_unique_no']) {

									$product_denom_selection .= '<option value="' . $denom_drop_res['denomination_unique_no'] . '" selected="selected">' . $denom_drop_res['denomination_long'] . '</option>';
								} else {
									$product_denom_selection .= '<option value="' . $denom_drop_res['denomination_unique_no'] . '">' . $denom_drop_res['denomination_long'] . '</option>';
								}

							} else {

								$product_denom_selection .= '<option value="' . $denom_drop_res['denomination_unique_no'] . '">' . $denom_drop_res['denomination_long'] . '</option>';
							}

						}
					}

					$product_denom_selection .= '</select>';

					$ilance->GPC['listing_type'] = isset($ilance->GPC['listing_type']) ? $ilance->GPC['listing_type'] : null;
					$checkbox_lt_1 = ($ilance->GPC['listing_type'] == '1') ? ' checked="checked" ' : '';
					$checkbox_lt_2 = ($ilance->GPC['listing_type'] == '2') ? ' checked="checked" ' : '';
					$checkbox_lt_3 = ($ilance->GPC['listing_type'] == '3') ? ' checked="checked" ' : '';
					$checkbox_lt_4 = ($ilance->GPC['listing_type'] == '4') ? ' checked="checked" ' : '';

					//5086
                    if (isset($ilance->GPC['sold']) AND $ilance->GPC['sold'])
                    {
                        $sold_ended_hidden = '<input type="hidden" name="ended" value="1" /> <input type="hidden" name="sold" value="1" />';
                    }

					$sort_value = $ilance->GPC['sort'];
					if (isset($ilance->GPC['action'])) {
						$action_field_hidden = '<input type="hidden" name="action" value="' . $ilance->GPC['action'] . '">';
					} else {
						$action_field_hidden = '';
					}

					//Tamil for bug 2389 ends

					//Tamil bug 2957 starts
					if (isset($ilance->GPC['dEnom_search']) && $ilance->GPC['dEnom_search'] == '1' && count($ilance->GPC['denomination']) == 1) {
						$denom_single_arr = $ilance->GPC['denomination'];
						if(is_array($denom_single_arr))
						$ilance->GPC['denomination'] = $denom_single_arr[0];
						$seolistings = print_seo_url($ilconfig['listingsidentifier']);
						$seocategories = print_seo_url($ilconfig['categoryidentifier']);
						$show['widescreen'] = false;
						$area_title = $phrase['_viewing_all_categories'];
						$topnavlink = array('main_categories');
						$ilance->categories_parser = construct_object('api.categories_parser');
						$denomination_details = $ilance->categories_parser->fetch_denominations($ilance->GPC['denomination']);
						$text1 = $denomination_details['denomination_long'];
						$categoryresults = $ilance->categories_parser->print_coin_series_html($ilance->GPC['denomination']);

						if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
							$navcrumb = array(HTTP_SERVER . Denomination . '/' . $ilance->GPC['denomination'] . '/' . construct_seo_url_name($denomination_details['denomination_long']).'/'.$ilance->GPC['date_end'] => $text1);
						else	
							$navcrumb = array(HTTP_SERVER . Denomination . '/' . $ilance->GPC['denomination'] . '/' . construct_seo_url_name($denomination_details['denomination_long']) => $text1);

						$page_title = $denomination_details['denomination_meta_title'];
						$metakeywords = $denomination_details['denomination_meta_description'];
						$metadescription = $denomination_details['denomination_description'];
						$search_category_pulldown = $ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'], 'series', 0, true);
						$show['secondary_search_form'] = 'no';
						$show['denom_search_form'] = 'yes';

					} else {

						$show['secondary_search_form'] = 'yes';
						$show['denom_search_form'] = 'no';
					}
					//Tamil for bug 2957 ends
					if (isset($ilance->GPC['series'])) {
						$series_hidden_fld = '<input type="hidden" name="series" value="' . $ilance->GPC['series'] . '">';
					} else {
						$series_hidden_fld = '';
					}
					if (isset($ilance->GPC['edit']) and $ilance->GPC['edit'] == 1) {
						$onload .= "javascript:jQuery('#update_search_modal').jqm({modal: false}).jqmShow();";
					}

					//for bug #5384
                    $show['show_end_date'] = $show_end_date_text = $end_date_removed_url = 0;
                    if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
                    {
                        $show['show_end_date'] =  1;
                        $show_end_date_text = 'Only coins ending on '. date("F d,Y",strtotime($ilance->GPC['date_end']));
                        $datend_hidn = '<input type="hidden" name="date_end" value="'.$ilance->GPC['date_end'].'" />';
                        $reqst_url = explode('&', $_SERVER['QUERY_STRING']);

                        $emptykey = count($reqst_url)-1;
                        foreach($reqst_url as $key=>$reqsturl)
                        {
                            $requrl = explode('=', $reqsturl);
                            if($requrl[0] == 'date_end')
                                unset($reqst_url[$key]);
                        }
                        unset($reqst_url[$emptykey]);
                        
                        $end_date_removed_url = implode('&',$reqst_url);
                    }

					$ilance->template->fetch('main', 'search_results.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_loop('main', array('featuredproductauctions', 'featuredserviceauctions'));
					$ilance->template->parse_if_blocks('main');

					//'searchid' ADDED TO THE BELOW ARRAY FOR BUG 1989

					$pprint_array = array('sold_ended_hidden','datend_hidn','end_date_removed_url','show_end_date_text','is_denom_page', 'is_series_page', 'series_hidden_fld', 'sort_value', 'action_field_hidden', 'text1', 'seolistings', 'seocategories', 'categoryresults', 'checkbox_denom_value_all', 'denom_drop_is_sel', 'denom_checkbox', 'product_denom_selection', 'frombid', 'tobid', 'toyear', 'checkbox_lt_1', 'checkbox_lt_2', 'checkbox_lt_3', 'checkbox_lt_4', 'fromyear', 'toyear', 'grade_range_dropdown_1', 'grade_range_dropdown_2','ungraded_checked', 'clear_grading_service', 'grading_service_dropdown', 'searchid', 'series_save_search', 'save_search_denomination', 'clear_budgetrange', 'text1', 'pennyauctions', 'clear_local', 'clear_distance', 'clear_searchuser_url', 'country', 'leftnav_options', 'leftnav_currencies', 'clear_currencies', 'clear_options', 'sort', 'clear_bidrange', 'clear_listtype', 'leftnav_buyingformats', 'showallurl', 'clear_searchuser', 'clear_price', 'clear_region', 'leftnav_regions', 'full_country_pulldown', 'didyoumean', 'searchuser', 'search_bidrange_pulldown_service', 'search_bidrange_pulldown_product', 'search_country_pulldown_product', 'search_country_pulldown_service', 'search_radius_country_pulldown_product', 'search_radius_country_pulldown_service', 'budget_slider_1', 'budget_slider_2', 'favtext', 'profilebidfilters', 'fewer_keywords', 'budgetfilter', 'hiddenfields_leftnav', 'city', 'state', 'zip_code', 'radiuszip', 'mode', 'hiddenfields', 'fromprice', 'toprice', 'search_results_table', 'sortpulldown', 'favoritesearchurl', 'keywords', 'search_product_category_pulldown', 'php_self', 'php_self_urlencoded', 'pfp_category_left', 'pfp_category_js', 'rfp_category_left', 'rfp_category_js', 'search_country_pulldown', 'search_country_pulldown2', 'search_jobtype_pulldown', 'search_ratingrange_pulldown', 'search_awardrange_pulldown', 'search_bidrange_pulldown', 'search_listed_pulldown', 'search_closing_pulldown', 'search_category_pulldown', 'input_style', 'distance', 'subcategory_name', 'text', 'prevnext', 'prevnext2', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'count');

					($apihook = $ilance->api('search_results_auctions_template')) ? eval($apihook) : false;

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

if (isset($ilance->GPC['country'])) {
	$country = $ilance->GPC['country'];
} else {
	$country = !empty($_SESSION['ilancedata']['user']['country']) ? $_SESSION['ilancedata']['user']['country'] : 'all';
}

if (isset($ilance->GPC['q'])) {
	if (!empty($ilance->GPC['q'])) {
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
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'saveoptions') {
	if (is_array($ilance->GPC)) {
		$options = array();
		foreach ($ilance->GPC AS $search => $option) {
			if ($search != 'defaultupdate' AND $search != 'membersupdate' AND $search != 'tab' AND $search != 'search' AND $search != 'cmd' AND $search != 'returnurl' AND $search != 'redirect') {
				$options["$search"] = $option;
			}
		}
		if (empty($options['online'])) {
			$options['online'] = 'false';
		}
		if (empty($options['latestfeedback'])) {
			$options['latestfeedback'] = 'false';
		}
		if (empty($options['username'])) {
			$options['username'] = 'false';
		}
		if (empty($options['description'])) {
			$options['description'] = 'false';
		}
		if (empty($options['icons'])) {
			$options['icons'] = 'false';
		}
		if (empty($options['currencyconvert'])) {
			$options['currencyconvert'] = 'false';
		}
		if (empty($options['displayfeatured'])) {
			$options['displayfeatured'] = 'false';
		}
		if (empty($options['hidelisted'])) {
			$options['hidelisted'] = 'false';
		}
		if (empty($options['hideverbose'])) {
			$options['hideverbose'] = 'false';
		}
		if (empty($options['proxybit'])) {
			$options['proxybit'] = 'false';
		}

		($apihook = $ilance->api('search_saveoptions_submit_end')) ? eval($apihook) : false;

		$searchoptions = serialize($options);
		$uid = (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0;
		update_default_searchoptions($uid, $searchoptions);

		if (isset($ilance->GPC['defaultupdate']) AND $ilance->GPC['defaultupdate'] == 'true') {
			update_default_searchoptions_guests($searchoptions);
		}

		if (isset($ilance->GPC['membersupdate']) AND $ilance->GPC['membersupdate'] == 'true') {
			update_default_searchoptions_users($searchoptions);
		}

		if (!empty($ilance->GPC['returnurl'])) {
			refresh($ilance->GPC['returnurl']);
			exit();
		}

		refresh($ilpage['search'] . '?tab=3');
		exit();
	} else {
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

$ilance->template->fetch('main', 'search.html');
$ilance->template->parse_if_blocks('main');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));

$pprint_array = array('colsperrow', 'region_pulldown_product', 'locatedin_pulldown_product', 'availableto_pulldown_product', 'search_bidrange_pulldown_product', 'search_radius_country_pulldown_product', 'search_country_pulldown_product', 'profilebidfilters', 'skills_selection', 'returnurl', 'js_start', 'perpage', 'sortpulldown', 'sortpulldown2', 'rb_list_gallery', 'rb_list_list', 'rb_showtimeas_flash', 'rb_showtimeas_static', 'cb_username', 'cb_latestfeedback', 'cb_online', 'cb_description', 'cb_icons', 'cb_currencyconvert', 'cb_displayfeatured', 'cb_hidelisted', 'cb_proxybit', 'cb_hideverbose', 'productavailable', 'productselected', 'keywords', 'searcherror', 'fromprice', 'toprice', 'budgetfilter', 'tab', 'search_offersrange_pulldown', 'search_wantedsincerange_pulldown', 'search_country_pulldown2', 'search_soldrange_pulldown', 'search_itemsrange_pulldown', 'search_opensincerange_pulldown', 'product_category_selection', 'search_productauctions_img', 'search_productauctions_collapse', 'pfp_category_left', 'rfp_category_left', 'input_style', 'search_country_pulldown', 'search_jobtype_pulldown', 'search_ratingrange_pulldown', 'search_awardrange_pulldown', 'search_bidrange_pulldown', 'search_listed_pulldown', 'search_closing_pulldown', 'input_style', 'remote_addr', 'rid', 'default_exchange_rate', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server');

($apihook = $ilance->api('search_start_template')) ? eval($apihook) : false;

$ilance->template->pprint('main', $pprint_array);
exit();



function fetch_featured_auctions_new($auctiontype = '', $columns = 4, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false, $excludelist = array(), $series, $feat_denomination = '', $feat_grading_sevice = '', $feat_year_range = '', $feat_grade_range = '', $feat_bid_range = '', $feat_listing_type = '', $feat_join_coins = '', $feat_projectstatus = '', $feat_timestamp = '', $kwcondition) {
	global $ilance, $ilconfig, $show, $phrase, $ilpage;

	$ilance->timer->start();

	$ilance->bbcode = construct_object('api.bbcode');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');

	if ($ilconfig['showfeaturedlistings'] == false) {
		$show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
		return;
	}

	$query_fields = $cidcondition = $subcategorylist = '';

	$cidcondition = "AND p.cid > 0";

	//$kwcondition='';

	// build exclusion query bit to prevent the same listings as the one being viewed to show up
	$excluded = '';
	if (isset($excludelist) AND !empty($excludelist) AND is_array($excludelist) AND count($excludelist) > 0) {
		if (count($excludelist) == 1) {
//may31
			$excluded .= "AND p.project_id = '" . intval($excludelist[0]) . "'";
		} else if (count($excludelist) > 1) {
			$excluded .= "AND (";

			$notin_list = implode(',', $excludelist);

			$excluded .= "p.project_id NOT IN ('" . $notin_list . "')";

			$excluded .= ")";

		}
	}

	($apihook = $ilance->api('fetch_featured_auctions_start')) ? eval($apihook) : false;

	$featuredauctions = array();

	//karthik starton Apr 30

	$sqlquery['pricerange'] = $clear_price = '';
	if ($show['mode_product']) {
		if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0) {
			$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
			$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
			$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';

			$sqlquery['pricerange'] .= "AND (p.currentprice >= " . intval($ilance->GPC['fromprice']) . " ";
			handle_search_verbose('<span class="black"><!--' . $phrase['_min_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong></span> &ndash; ');
			handle_search_verbose_save($phrase['_min_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong>, ');
		} else {
			$sqlquery['pricerange'] .= "AND (p.currentprice >= 0 ";
		}

		if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0) {
			$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
			$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
			$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';

			$sqlquery['pricerange'] .= "AND p.currentprice <= " . intval($ilance->GPC['toprice']) . ") ";
			handle_search_verbose('<span class="black"><!--' . $phrase['_max_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
			handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong>, ');
		} else {
			$sqlquery['pricerange'] .= ")";
			handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $phrase['_unlimited'] . '</strong>, ');
		}
	}
	$con = $sqlquery['pricerange'];

	if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow']) {
		$featured = " AND p.buynow='1' ";
	} else if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction']) {
		$featured = " AND p.buynow = '0' ";
	} else {
		$featured = '';
	}
	//end may 03

	//Tamil for bug 2463 on 08/03/13 *Starts

	if (!empty($series)) {
		$series_condition = "AND p.coin_series_unique_no ='" . intval($series) . "'";
	} else {
		$series_condition = '';
	}

	//vijay for  adding start watchlist starts
	$watchlist_check = '';
	$watchlist_check_column = '';
	if ($_SESSION['ilancedata']['user']['userid'] > 0) {
		$watchlist_check = " left join " . DB_PREFIX . "watchlist w on  p.project_id = w.watching_project_id and w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'";
		$watchlist_check_column = ", w.watchlistid ";
	}
	//vijay for  adding start watchlist ends
	// Tamil for bug 2463 on 08/03/13 *Ends
	//echo $kwcondition."lop";
	if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled']) {
		$limit = $columns * $rows;
		$fsql = "
                                SELECT ab.attachid,ab.project_id,p.hotlists,p.user_id, p.project_id, p.project_title, p.description, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, p.retailprice, p.buynow, p.buynow_qty, p.buynow_price, p.currentprice, $query_fields p.highlite, p.currencyid, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
                                " . $watchlist_check_column . "
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                LEFT JOIN (select a.project_id,a.attachid from " . DB_PREFIX . "attachment a
								JOIN " . DB_PREFIX . "projects p on p.project_id =a.project_id 
								where a.visible='1' and a.attachtype='itemphoto' and a.attachid IS NOT NULL and p.project_state = 'product' 
								and p.featured = '1' 
								and p.visible = '1'
								and p.status ='open') as ab on ab.project_id=p.project_id
                                " . $watchlist_check . "
								$feat_join_coins
                                WHERE p.project_state = 'product'
                                    AND p.featured = '1'
                                    AND p.visible = '1'
									$featured
									 $con
                                    $excluded
                                    $cidcondition
                                    $kwcondition
									$series_condition
									$feat_denomination
									$feat_grading_sevice
									$feat_year_range
									$feat_grade_range
									$feat_bid_range
									$feat_listing_type
									$feat_projectstatus
									$feat_timestamp
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                GROUP BY p.project_id
                                ORDER BY RAND(),
                                ab.project_id ASC
                                LIMIT $limit
                        ";

		$sqlproductauctions = $ilance->db->query($fsql, 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlproductauctions) > 0) {
			$rowstotal = $ilance->db->num_rows($sqlproductauctions);
			$width = number_format(100 / $columns, 1);
			$separator = '';

			if ($rowstotal != $columns && $rowstotal != 0) {
				$neededtds = $columns - $rowstotal;
				for ($i = 0; $i < $neededtds; $i++) {
					$separator .= '<td width="' . $width . '%" valign="top"></td>';
				}
			}

			$show['featuredproductauctions'] = true;
			$resrows = 0;
			while ($res = $ilance->db->fetch_array($sqlproductauctions, DB_ASSOC)) {
				if ($ilconfig['globalfilters_maxcharacterstitle'] != '0') {
					$temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);

					if (strcmp($temp_title, $res['project_title']) != '0') {
						$res['project_title'] = $temp_title . '...';
					}

					$temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);

					if (strcmp($temp_desc, $res['description']) != '0') {
						$res['description'] = $temp_desc . '...';
					}
				}

				$res['width'] = $width;
				if ($ilconfig['globalauctionsettings_seourls']) {
					$url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
					$res['photo'] = print_item_photo($url, 'results_zoom', $res['project_id']);
				} else {
					$res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'results_zoom', $res['project_id']);
				}

				$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>';
				$res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
				$res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
				$res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
				$res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];

				if ($res['project_details'] == 'unique') {
					$res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
					$res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
					$res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
				} else {
					if ($res['buynow_price'] > 0 AND $res['buynow']) {
						$res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
						$res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
						$res['buynowtxt'] = $phrase['_buy'] . ':';
						//karthik may03

						$res['price'] = $res['bid'] = '';
						$res['newbids'] = '';
						//karthik may03 end
						// $res['price'] = $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']);
					} else {
						//karthik may03
						$res['newbids'] = '<div class="smaller gray">(' . $res['bids'] . ' bids)</div>';

						//karthik may03 end
						$res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
						$res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>';
					}
				}

				$res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$res['timeleft'] = auction_time_left_new($res, false);

				$haswinner = $res['haswinner'];
				

				if($haswinner == 1)
				{
					$buyer_fee = $res['buyer_fee'];

                    $soldprice = $ilance->currency->format($res['currentprice'] + $buyer_fee, $res['currencyid']);
                	$hamerprice = $ilance->currency->format($res['currentprice'], $res['currencyid']);
                	$encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');

                	if($ilconfig['settings_sold_coins_price_to_image'])
                    {
                        $res['price'] = '<img width="152" height="33" src="images.php?fq='.$encramnt.'" />';
                    }
                    else
                    {
                        $res['price'] = '<strong>  Sold: ' . $soldprice . '<br/><span style="color: #999999;">('.$hamerprice.' hammer)</span></strong>';
                    }
					//$res['price'] = '<img src="images.php?fq='.$encramnt.'" />';
					$res['haswinner'] = '';
					$res['newbids'] = '<div class="smaller gray">(' . $res['bids'] . ' bids)</div>';
				}
				else
				{
					$res['haswinner'] = '<div style="height:20px;">&nbsp;</div>';
				}

				//vijay work for add watchlist star  starts

				if (!empty($_SESSION['ilancedata']['user']['userid'])) {

					$watch_user_id = $_SESSION['ilancedata']['user']['userid'];

					if (isset($res['watchlistid']) and $res['watchlistid'] > 0) {
						$res['watchingitemsss'] = '<div class="smaller gray"><a href="javascript:void(0)" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_on.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_watch_' . $res['project_id'] . '" onclick="update_watch(' . $res['project_id'] . ',' . $watch_user_id . ');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
                                            <span>
                                                <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                                <strong>Click to Remove from your Watchlist</strong>
                                             </span>
                                              </a>';
					} else {

						$res['watchingitemsss'] = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_off.gif" alt="' . $phrase['_click_to_enable_disable'] . '" border="0" id="inline_watch_' . $res['project_id'] . '" onclick="update_watch(' . $res['project_id'] . ',' . $watch_user_id . ');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
                                             <span>
                                                <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                                <strong>Click to Add to your Watchlist</strong>
                                             </span>
                                            </a>';
					}
				} else {
					$res['watchlist'] = '<a href="javascript:void(0)" onclick="login_watch(' . $res['project_id'] . ');" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_off.gif" alt=""   border="0" />
                                    <span>
                                        <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
                                        <strong>Click to Add your to Watchlist</strong>
                                     </span>
                                            </a>';

				}

				//vijay work end.

				// $res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
				$res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');

				$resrows++;

				if ($resrows == 1) {
					$res['separator_begin'] = '<tr>';
					$td = 0;
				} else {
					$res['separator_begin'] = '';
				}

				if ($resrows == $rowstotal) {
					$res['separator_end'] = '</tr>';
				} else {
					$res['separator_end'] = '';
				}

				if ($rows != 1) {
					if ($td == $columns - 1 OR $td == $rowstotal - 1) {
						$res['separator_end'] = '</tr>';
					} else if ($td == $columns) {
						$res['separator_end'] = '';
						$res['separator_begin'] = '<tr>';
						$td = 0;
					} else {
						$res['separator_end'] = '';
					}

					$td++;
					if ($res['separator_end'] == '</tr>') {
						$res['separator'] = $separator;
					} else {
						$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
					}
				} else {
					$res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';
					if ($resrows == $rowstotal) {
						$res['separator'] .= $separator;
					}
				}

				$featuredauctions[] = $res;
			}
		} else {
			$show['featuredproductauctions'] = false;
		}

		if ($show['featuredproductauctions'] AND !empty($_SESSION['ilancedata']['user']['searchoptions'])) {
			$temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
			if (isset($temp['displayfeatured']) AND $temp['displayfeatured'] == 'false') {
				$show['featuredproductauctions'] = false;
			}
		}
	}

	$ilance->timer->stop();
	DEBUG("fetch_featured_auctions(\$auctiontype = $auctiontype, \$columns = $columns, \$rows = $rows) in " . $ilance->timer->get() . " seconds", 'FUNCTION');

	return $featuredauctions;
}

//Tamil rewritten for Bug 3046 * Ends

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
