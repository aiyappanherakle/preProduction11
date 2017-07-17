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
	'accounting',
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix'
);

// #### define top header nav ##########################################
$topnavlink = array(
	'rss'
);

// #### setup script location ##################################################
define('LOCATION', 'rss');

// #### require backend ########################################################
require_once('./functions/config.php');
error_reporting(0);
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[rss]" => $ilcrumbs["$ilpage[rss]"]);

($apihook = $ilance->api('rss_start')) ? eval($apihook) : false;

// #### SYNDICATION SERVICE AUCTIONS ###########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'syndication' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'serviceauctions')
{
	$area_title = $phrase['_syndicating_service_auctions'];
	$page_title = SITE_NAME . ' - ' . $phrase['_syndicating_service_auctions'];

	$ilance->GPC['limit'] = isset($ilance->GPC['limit']) ? intval($ilance->GPC['limit']) : 15;

	$myrss = construct_object('api.myrss');
	$myrss->feedVersion = $ilance->GPC['version'];
	$myrss->channelTitle = SITE_NAME . ' ' . $phrase['_service_auctions'];
	$myrss->channelLink = HTTP_SERVER;
	$myrss->channelDesc = $phrase['_service_auctions_open_for_bid'];	
	$myrss->imageTitle = SITE_NAME . ' ' . $phrase['_service_auctions'];
	$myrss->imageLink = HTTP_SERVER;
	$myrss->imageURL = HTTP_SERVER . $ilconfig['template_logo'];

	// subcategory selection
	if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] == 'all')
	{
		$extraqueryclause = "AND cid > 0 AND xml = '1'";
	}
	else if (!empty($ilance->GPC['sid']) AND $ilance->GPC['sid'] != 'all')
	{
		$cats = $ilance->GPC['sid'];
		$childrenids = $ilance->categories->fetch_children_ids($cats, 'service', "AND xml = '1'");
		$subcategorylist = $cats . ',' . $childrenids;
		$extraqueryclause = "AND (FIND_IN_SET(cid, '$subcategorylist'))";
	}
	else
	{
		$extraqueryclause = "AND cid > 0 AND xml = '1'";
	}
	
	// WHERE clause
	$extrawhereclause = "WHERE project_state = 'service' AND status = 'open' " . (($ilconfig['globalauctionsettings_payperpost'])
		? "AND visible = '1' AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))"
		: "AND visible = '1'") . " " . $extraqueryclause;
	$extralimitclause = "LIMIT " . intval($ilance->GPC['limit']);
	
	// get the RSS data
	$rssData = $myrss->GetRSS(
		DB_PREFIX . 'projects',
		'project_title', 
		'description', 
		'project_id, date_starts', 
		HTTP_SERVER . $ilpage['rfp'] . '?id={linkId}',
		$extrawhereclause, 
		$extralimitclause,
		'project_id'
	);
	
	// output the generated RSS XML
	header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
	echo $rssData;
}

// #### SYNDICATE PRODUCT AUCTIONS #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'syndication' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'productauctions')
{
	$area_title = $phrase['_syndicating_product_auctions'];
	$page_title = SITE_NAME . ' - ' . $phrase['_syndicating_product_auctions'];

	$myrss = construct_object('api.myrss');
	$myrss->feedVersion = $ilance->GPC['version'];
	$myrss->channelTitle = SITE_NAME . ' ' . $phrase['_product_auctions'];
	$myrss->channelLink = HTTP_SERVER;
	$myrss->channelDesc = $phrase['_product_auctions_open_for_bid'];
	$myrss->imageTitle = SITE_NAME . ' ' . $phrase['_product_auctions'];
	$myrss->imageLink = HTTP_SERVER;
	$myrss->imageURL = HTTP_SERVER . $ilconfig['template_logo'];
	
	// Subcategory Selection
	if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] == 'all')
	{
		$extraqueryclause = "AND cid > 0 ";
	}
	else if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] != '' AND $ilance->GPC['sid'] != 'all')
	{
		$cats = $ilance->GPC['sid'];
		//suku
		
		$sql=$ilance->db->query("select PCGS from ".DB_PREFIX."catalog_coin where coin_series_denomination_no='".$ilance->GPC['sid']."'");
		while($line=$ilance->db->fetch_array($sql))
		{
			$childrenids[]=$line['PCGS'];
		}
		//$childrenids = $ilance->categories->fetch_children_ids($cats, 'product', "AND xml = '1'");
		$subcategorylist = implode(",",$childrenids);
		$extraqueryclause = "AND (FIND_IN_SET(cid, '$subcategorylist'))";
	}
	else
	{
		$extraqueryclause = "AND cid > 0 AND xml = '1'";
	}

	// WHERE clause
	$extrawhereclause = "WHERE project_state = 'product' AND status = 'open' " . (($ilconfig['globalauctionsettings_payperpost'])
		? "AND visible = '1' AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))"
		: "AND visible = '1'") . " " . $extraqueryclause;
	
	$extralimitclause = "LIMIT " . intval($ilance->GPC['limit']);
	
	// Get the RSS data
	$rssData = $myrss->GetRSS(
		DB_PREFIX . 'projects',
		'project_title',
		'description',
		'project_id, date_starts',
		HTTP_SERVER . $ilpage['merch'] . '?id={linkId}',
		$extrawhereclause,
		$extralimitclause,
		'project_id'
	);
	
	// Output the generated RSS XML 
	header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
	echo $rssData;
}
else
{
	$area_title = $phrase['_syndication_generation_menu'];
	$page_title = SITE_NAME.' - '.$phrase['_syndication_generation_menu'];
	
	$show['generated_feed'] = false;
	$syndicate_url = '';
	$cid = isset($ilance->GPC['sid']) ? intval($ilance->GPC['sid']) : 0;
	
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'generate')
	{
		$show['generated_feed'] = true;
		$syndicate_url = HTTP_SERVER . $ilpage['rss'] . '?cmd=syndication&subcmd=' . $ilance->GPC['subcmd'] . '&sid=' . $cid . '&version=' . $ilance->GPC['version'] . '&limit=' . intval($ilance->GPC['limit']);
	}
	$top_level_category='<select name="sid"  multiple="multiple" size="15">';
	$sql=$ilance->db->query("select denomination_unique_no,denomination_long from ".DB_PREFIX."catalog_toplevel order by denomination_sort");
	while($line=$ilance->db->fetch_array($sql))
	{
	$top_level_category.='<option value="'.$line['denomination_unique_no'].'">'.$line['denomination_long'].'</option>';
	}
	
	$top_level_category.='</select>';
	$pprint_array = array('top_level_category','cid','syndicate_url','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('rss_main')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'main_rss.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>