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

$ilance->GPC['cmd'] = 'hotlist';

$date_auction= tab_auction_date();

/*echo '<pre>';
print_r($date_auction);*/
$tabact=array();
$i=0;
foreach($date_auction as $dte_auction)
{	
$active_tab='';
if ($dte_auction==$ilance->GPC['date'])
{
$active_tab='on';
}
if (!isset($ilance->GPC['date']) &&  $i==0)
{
$active_tab='on';

}
$tab_auction=date('l, F j, Y',strtotime($dte_auction));
$actiontabs.='<li class="'.$active_tab.'" title=""><a href="hotlist.php?cmd=hotlist&date='.$dte_auction.'">'.$tab_auction.'</a></li>';
$tabact[]=$dte_auction;
$i++;

}



if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'hotlist') 
{

	$ilance->GPC['date'] = (!isset($ilance->GPC['date']) OR $ilance->GPC['date'] == '') ? $tabact[0]: $ilance->GPC['date'];

	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

	//$ilconfig['globalfilters_maxrowsdisplay'] = '250';

	//pagnation
	//$hotlist_scriptpage = 'hotlist.php?cmd=hotlist&date='.$ilance->GPC['date'];

	//$hotlist_counter = (intval($ilance->GPC['page']) - 1) * $ilconfig['globalfilters_maxrowsdisplay'];

	
	$text="List of all Denominations / Categories";
	$cat_id=$ilance->GPC['cat_id'];
	$categoryresults= html_denomination_hotlist($ilance->GPC['date'],$cat_id);
	$cat_id=$ilance->GPC['cat_id'];
	$cat_sqlcnd ='';
	if(isset($cat_id) and $cat_id > 0)
	{
	$cat_sqlcnd ="and p.coin_series_denomination_no ='" .$cat_id."'";
	}
	$hotlist_row_count = 0;
	
	
	$hotlist = $ilance->db->query("SELECT p.hotlists,p.project_details,p.filtered_auctiontype,p.bids,p.project_state,p.description,p.status,p.currencyid,p.startprice,p.currentprice,p.project_id,p.project_title,p.date_end as ended_on,UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime ,(SELECT COUNT(attachid) AS picture_count FROM " . DB_PREFIX . "attachment  WHERE project_id=p.project_id) as picture_count,ca.filename as filehash

	FROM " . DB_PREFIX . "projects p
	left join  " . DB_PREFIX . "catalog_toplevel ct on p.coin_series_denomination_no = ct.denomination_unique_no
	left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
	left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto'
	WHERE p.status = 'open' 
	and p.hotlists ='1'
	$cat_sqlcnd
	and date(p.date_end)='" .$ilance->GPC['date']."'
	group by p.project_id
	ORDER BY p.date_end  ASC
	");
	/*
	LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay']
	, 0, null, __FILE__, __LINE__);
	
	
	//total ended
	$hotlist_pagin = $ilance->db->query("SELECT *, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
	FROM " . DB_PREFIX . "projects p
	left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
	WHERE p.status = 'open'
	and p.hotlists ='1'
	$cat_sqlcnd
	and date(p.date_end)='" .$ilance->GPC['date']."'
	group by p.project_id
	ORDER BY p.date_end  ASC
	", 0, null, __FILE__, __LINE__);



	$hotlist_number = (int) $ilance->db->num_rows($hotlist_pagin);*/

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

	//$hotlist_prevnext = print_pagnation($hotlist_number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $hotlist_counter, $hotlist_scriptpage);
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


	$pprint_array = array('tab','actiontabs','text','categoryresults','input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'hotlist_prevnext');

	$ilance->template->fetch('main', 'hotlist.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('hotlist_reslt'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}


function tab_auction_date(){
        global $ilance,$show,$ilconfig,$phrase;
			
		$auction_dates_sql="SELECT date(date_end) as auction_date FROM " . DB_PREFIX . "projects WHERE status='open' AND hotlists='1' AND ((filtered_auctiontype = 'regular' AND winner_user_id = '0') OR (buynow = '1' AND filtered_auctiontype = 'fixed' AND buynow_qty > '0')) group by date(date_end)";							
						 
		$auction_dates_row =$ilance->db->query($auction_dates_sql);

	    if($ilance->db->num_rows($auction_dates_row) >0)
		{
					
			while($auction_dates_rslt= $ilance->db->fetch_array($auction_dates_row))
			{
				
				$auction_dates_rsl[]= $auction_dates_rslt['auction_date'];
							                
				
			}
			
		}	
		           
	   return  $auction_dates_rsl;
	 
}
function html_denomination_hotlist($date,$selected = 0) {
	global $ilance,$show,$ilconfig,$phrase;
		$html = '';
		$html .= print_subcategory_hotlist($selected,4, 'product', 1, 'eng', 0, $date);
		return $html;
	}
function print_subcategory_hotlist($selected = 0,$columns = 1, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
	
	global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
	$ilance->timer->start();
	if (!empty($cacheid)) {
		$cacheid = '_' . $cacheid;
	}
	$html = '';
	$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
	/* $this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
	 */
	$denomination_details = fetch_auction_hotlist($extra,$selected);
	$check = 0;
	foreach ($denomination_details as $denom_detail) {
		//$count=$this->get_project_count($denom_detail['id']);
		if ($check % $columns == 0) {
			$html .= '</tr><tr>';
		}
		$classcss ='';	
		if ($selected == $denom_detail['id'])
		{
			 $classcss ='id="link1"';				
		}
		
		if ($ilconfig['globalauctionsettings_seourls']) {
			$html .= '<td><a '.$classcss.' href="' . HTTP_SERVER . 'hotlist.php?cmd=hotlist&date=' . $extra . '&cat_id=' .$denom_detail['id'] . '&cat_name=' .construct_seo_url_name($denom_detail['denomination_long']) . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['cnt'] . ')' . '</a></td>';
		} else {
			$html .= '<td><a '.$classcss.' href="' . HTTP_SERVER . 'hotlist.php?cmd=hotlist&date=' . $extra . '&cat_id=' .$denom_detail['id']. '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['cnt'] . ')' . '</a></td>';
		}

		$check++;
	}
	$html .= '<tr><td></td></table>';
	$ilance->timer->stop();
//	DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	return $html;
}
function fetch_auction_hotlist($date = '') {
	global $ilance,$show,$ilconfig,$phrase;
	$con = "AND date(date_end) = '" . $date . "'";
	$result = $ilance->db->query("SELECT count(*) as cnt, p.coin_series_denomination_no,c.denomination_long,c.id
													  FROM " . DB_PREFIX . "projects p,
														   " . DB_PREFIX . "catalog_toplevel c
												WHERE p.coin_series_denomination_no = c.denomination_unique_no
												AND  p.status =  'open'
												AND p.hotlists ='1'
												$con
												group by c.denomination_long
												order by c.denomination_unique_no asc
								", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result)) {
		$count = 0;
		while ($row = $ilance->db->fetch_array($result)) {
			$denom[$count] = $row;
			$count++;
			if ($denomination_id != 0) {
				return $row;
			}

		}
	} else {
	}
	return $denom;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>