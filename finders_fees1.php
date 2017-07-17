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

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[main]" => $ilcrumbs["$ilpage[main]"]);
 
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
// #### SEO related ############################################################
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
// #### MEMBERS CONTROL PANEL DASHBOARD ########################################


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
		

			
				
        $pprint_array = array('myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','jobcount','itemcount','recentlyviewedflash','tagcloud','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');
        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
    
        $ilance->template->fetch('main', 'finders_fees.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
        ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();




/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>