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
        'rfp',
		'global'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix',
	'jquery'
);

// #### setup script location ##################################################
define('LOCATION', 'main');

// #### require backend ########################################################
require_once('./functions/config.php');
// #### setup default breadcrumb ###############################################
$pages_title= $phrase['_random_coin_auctions_page_title'];

$navcrumb = array('randomauction.php' => $phrase['_random_coin_auctions_bread_crumb']);

$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');



// #### SEO related ############################################################
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);

		$myfeature_random=random_auction('13');
		$pprint_array = array('myfeature_random','login_include','headinclude','pages_title');

		($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  

		$ilance->template->fetch('main', 'randomauction.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'res_gcdealing');
		($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();

?>