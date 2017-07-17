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
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### load required phrase groups ############################################
$phrase['groups'] = array(
        'watchlist',
        'feedback'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'flashfix',
	'jquery'
);

// #### define top header nav ##################################################
$topnavlink = array(
	'hotlist'
);

// #### setup script location ##################################################
define('LOCATION', 'hotlist');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$ilance->bbcode = construct_object('api.bbcode');
$ilance->auction = construct_object('api.auction');

$show['widescreen'] = false;
	
$area_title =  $phrase['_the_hot_list_area_title'];
$page_title = SITE_NAME . ' - ' .  $phrase['_the_hot_list_page_title'];

$navcrumb = array();

$navcrumb[""] = $phrase['_the_hot_list_bread_crumb']; 

/*echo '<pre>';
print_r($date_auction);*/

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consigned_hotlist') 
	{


	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

	$ilconfig['globalfilters_maxrowsdisplay'] = '50';

	//pagnation
	$hotlist_scriptpage = 'consigned_hotlist.php?cmd=hotlist';

	$hotlist_counter = (intval($ilance->GPC['page']) - 1) * $ilconfig['globalfilters_maxrowsdisplay'];

	$hotlist_row_count = 0;


	$hotlist = $ilance->db->query("SELECT p.hotlists,p.project_details,p.filtered_auctiontype,p.bids,p.project_state,p.description,p.status,p.currencyid,p.startprice,p.currentprice,p.project_id,p.project_title,p.date_end as ended_on,UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime ,(SELECT COUNT(attachid) AS picture_count FROM " . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,ca.filename as filehash

	FROM " . DB_PREFIX . "projects p
	left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
	left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
	WHERE p.status = 'open' 
	and p.hotlists ='1'
	and p.user_id ='".$_SESSION['ilancedata']['user']['userid']."'
	group by p.project_id
	ORDER BY p.date_end  ASC
	");
	//total ended
	$hotlist_pagin = $ilance->db->query("SELECT *, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
	FROM " . DB_PREFIX . "projects p
	left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
	left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
	WHERE p.status = 'open' 
	and p.hotlists ='1'
	and p.user_id ='".$_SESSION['ilancedata']['user']['userid']."'
	group by p.project_id
	ORDER BY p.date_end  ASC
	", 0, null, __FILE__, __LINE__);



	$hotlist_number = (int) $ilance->db->num_rows($hotlist_pagin);

	if ($ilance->db->num_rows($hotlist) > 0) {
	while ($rows = $ilance->db->fetch_array($hotlist)) {

	$row['action'] = '<input type="checkbox" id="checkbox-1" name="project_id[]" value="' . $rows['project_id'] . '" />';
	if ($rows['bids'] == 0) {
	$row['bids'] = '-'; 
	} else {
	$row['bids'] = $rows['bids'] . ' ' . $phrase['_bids_lower'];
	}

	$row['ended_date'] = date('F d, Y', strtotime($rows['ended_on']));
	$row['filehash'] = $rows['filehash'];
	$row['picture_count'] = $rows['picture_count'];

	$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
	$row['comment'] = $rows['comment'];
	$pictures = $rows['picture_count'];
	$borderwidth = 0;
	$bordercolor = "#ffffff";
	if (!empty($rows['filehash'])) {
	$row['sample'] = '
	<div class="gallery-thumbs-cell">
	<div class="gallery-thumbs-entry">
	<div class="gallery-thumbs-main-entry">
	<div class="gallery-thumbs-wide-wrapper">
	<div class="gallery-thumbs-wide-inner-wrapper">
	<a href="' . $url . '"><img src="' . HTTPS_SERVER . 'image/72/96/' . $rows['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>
	<div class="gallery-thumbs-corner-text"><span>' . $pictures . ' photos</span></div>
	</div>
	</div>
	</div>
	</div>
	</div>';
	} else {
	$row['sample'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
	}

	if ($rows['project_state'] == 'product') {
	$row['attach'] = '';
	$row['auctionpage'] = $ilpage['merch'];

	// display thumbnail
	if ($ilconfig['globalauctionsettings_seourls']) {
	$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);

	//$row['sample'] = print_item_photo($url, 'thumb', $rows['project_id']);
	$row['title'] = construct_seo_url('productauction', 0, $rows['project_id'], stripslashes($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
	} else {
	//$row['sample'] = print_item_photo($ilpage['merch'] . '?id=' . $rows['project_id'], 'thumb', $rows['project_id']);
	$row['title'] = '<a href="' . $row['auctionpage'] . '?id=' . $rows['project_id'] . '">' . stripslashes($rows['project_title']) . '</a>';
	}
	}
	$row['watching_project_id'] = $rows['project_id'];
	$row['description'] = $ilance->bbcode->strip_bb_tags($rows['description']);
	$row['description'] = short_string($row['description'], 100);
	$row['description'] = handle_input_keywords($row['description']);
	$row['status'] = print_auction_status($rows['status']);

	// is bid placed?

	$row['bidplaced'] = ($rows['bid_id'] > 0)
	? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="' . $phrase['_you_have_placed_a_bid_on_this_auction'] . '" />'
	: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="' . $phrase['_place_a_bid'] . '" />';

	// is realtime auction?
	$row['realtime'] = ($rows['project_details'] == 'realtime')
	? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'realtime.gif" alt="' . $phrase['_realtime_auction'] . '" border="0" alt="" />'
	: '';

	// currency
	$currencyid = $rows['currencyid'];

	// bids
	$bids = $rows['bids'];

	// starting price
	$startprice = $rows['startprice'];

	// current bid
	$currentbid = $rows['currentprice'];

	$row['timeleft'] = $ilance->auction->auction_time_left_internal($rows, false);

	if ($rows['project_state'] == 'product') {
	if ($bids > 0 AND $currentbid > $startprice) {
	$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
	} else if ($bids > 0 AND $currentbid == $startprice) {
	$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
	} else {
	$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
	$currentbid = $startprice;
	}


	} 

	$row['class'] = ($hotlist_row_count % 2) ? 'alt2' : 'alt1';
	$hotlist_reslt[] = $row;

	$hotlist_row_count++;
	}

	$hotlist_prevnext = print_pagnation($hotlist_number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $hotlist_counter, $hotlist_scriptpage);
	}

	if ($hotlist_row_count <= 0) {
	$show['no_hotlist'] = true;
	}
	elseif ($hotlist_row_count > 0) {
	$show['hotlist'] = true;
	} 
	else {
	$show['no_hotlist'] = false;
	}


	$pprint_array = array('tab','actiontabs','input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'hotlist_prevnext');

	$ilance->template->fetch('main', 'consingned_hotlist.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('hotlist_reslt'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}
} 
else {
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['watchlist'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}
			


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
