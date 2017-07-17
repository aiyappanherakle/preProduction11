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
	'administration'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
       // ####  Murugan Work On Nov 25 For Dashboard ###########################################################
	 
	   // ######################################################################################################
	   // #### Dashboard TOTAL OWING Listing Code Starts Here ##################################################
	   // ######################################################################################################
	   
	    // Total Amount Owing to GC
		
		
	   	$totamtowingselect = $ilance->db->query("
							SELECT sum(amount) FROM " . DB_PREFIX . "invoices
							WHERE (isfvf ='1' OR isif = '1' OR isportfoliofee = '1' OR isenhancementfee = '1' OR isescrowfee = '1' OR iswithdrawfee = '1' OR isp2bfee = '1')							
							AND status = 'unpaid'");
		$totamtowingselectresult = $ilance->db->fetch_array($totamtowingselect);			
	  	$totamtowing = $ilance->currency->format($totamtowingselectresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	  
	  
	  	
	    // Total Amount Owing in Past 15 Days
		
		$totamtowing15select = $ilance->db->query("SELECT sum(amount) FROM " . DB_PREFIX . "invoices
							WHERE (isfvf ='1' OR isif = '1' OR isportfoliofee = '1' OR isenhancementfee = '1' OR isescrowfee = '1' OR iswithdrawfee = '1' OR isp2bfee = '1')							
							AND status = 'unpaid'
							AND (date(createdate) <= '" .DATETODAY . "' AND date(createdate) >= '" . FIFETEENDAYSAGO . "')");
	    $totamtowing15selectresult = $ilance->db->fetch_array($totamtowing15select);
		$totamtowing15 = $ilance->currency->format($totamtowing15selectresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	   
							
	  	 
		// Total Amount Owing in Past 30 Days
		$totamtowing30select = $ilance->db->query("SELECT sum(amount) FROM " . DB_PREFIX . "invoices
							WHERE (isfvf ='1' OR isif = '1' OR isportfoliofee = '1' OR isenhancementfee = '1' OR isescrowfee = '1' OR iswithdrawfee = '1' OR isp2bfee = '1')							
							AND status = 'unpaid'
							AND (date(createdate) <= '" .DATETODAY . "' AND date(createdate) >= '" . THIRTYDAYSAGO . "')");
	    $totamtowing30selectresult = $ilance->db->fetch_array($totamtowing30select);			
	  	$totamtowing30 = $ilance->currency->format($totamtowing30selectresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	  
		// Total Amount Owing to Client
		$totamtowingclientselect = $ilance->db->query("SELECT sum(amount) FROM " . DB_PREFIX . "invoices
							WHERE p2b_user_id != '0'							
							AND status = 'unpaid'
							");
	    $totamtowingclientselectresult = $ilance->db->fetch_array($totamtowingclientselect);	
	  	$totamtowingclient = $ilance->currency->format($totamtowingclientselectresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	  	   
	   
	   // ######################################################################################################
	   // #### Dashboard TOTAL OWING Listing Code End Here #####################################################
	   
	   	


		
	  
	   
	   // ######################################################################################################
	   // #### Dashboard Detail Listing Code Starts Here #######################################################
	   // ######################################################################################################
	   
	   	   
	   // Total Number of items in Current Auction 
	
	    $totliveitemprojectY_CurrentAuction  = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE status ='open' and date_end   LIKE  '%".CURRENTAUCTION."%'" );
	   $totliveitem_CurrentAuction = $ilance->db->num_rows($totliveitemprojectY_CurrentAuction);
	   
	   // Total Number of items in next Auction 
	
	    $totliveitemprojectY_nextAuction  = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE status ='open' and date_end   LIKE  '%".NEXTAUCTION."%'" );
	   $totliveitem_nextAuction = $ilance->db->num_rows($totliveitemprojectY_nextAuction);
	   
	   // Total Number of items in Current Daily Deal
	
	    $totliveitemprojectY_Currentdailydeal  = $ilance->db->query("SELECT p.currentprice,p.project_title FROM " . DB_PREFIX . "projects p, " . DB_PREFIX . "dailydeal  d  WHERE p.status='open' AND p.project_id = d.project_id AND d.live_date = p.date_starts");
	   $totliveitem_Currentdailydeal = $ilance->db->num_rows($totliveitemprojectY_Currentdailydeal);
	   
	    // Total Number of items in Next Daily Deal
	
	    $totliveitemprojectY_nextdailydeal  = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "dailydeal  d  WHERE  d.project_id = '0'");
	   $totliveitem_nextdailydeal = $ilance->db->num_rows($totliveitemprojectY_nextdailydeal);
	   
	  
	   // Total Number of Auction Yesturday
	    $auctionyesselect = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE date(date_awarded) = '".DATEYESTERDAY."'");
	    $auctionyes = $ilance->db->num_rows($auctionyesselect);
		
		// Total Number of Auction bid placed Yesturday
	    $auctionYesselect = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE date(date_added) = '".DATEYESTERDAY."'");
	    $auctionYessel = $ilance->db->num_rows($auctionYesselect);
	   
	    // Total Number of Auction bid placed
	    $auctionselect = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids");
	    $auctionsel = $ilance->db->num_rows($auctionselect);
		
	   // Total Number Of Buy Now Yesturday
	   $binyesselect = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders WHERE date(orderdate) = '".DATEYESTERDAY."'");
	   $binyes = $ilance->db->num_rows($binyesselect);
	   
	   // Total Amount Of Auction Yesturday 	   
	   $totauctionyesselect = $ilance->db->query("SELECT sum(bidamount) FROM " . DB_PREFIX . "project_bids WHERE date(date_awarded) = '".DATEYESTERDAY."'");
	   $totauctionyesresult = $ilance->db->fetch_array($totauctionyesselect);	   
	   $totauctionyes = $ilance->currency->format($totauctionyesresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	  
	   
	   // Total Amount Of Buy Now Yesturday 	   
	   $totbinyesselect = $ilance->db->query("SELECT sum(amount) FROM " . DB_PREFIX . "buynow_orders WHERE date(orderdate) = '".DATEYESTERDAY."'");
	   $totbinyesresult = $ilance->db->fetch_array($totbinyesselect);	   
	   $totbinyes = $ilance->currency->format($totbinyesresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	   
	   // Total Amount From Item Still now Posted In Site
	   $totamountselectproject = $ilance->db->query("SELECT sum(startprice) FROM " . DB_PREFIX . "projects");
	   $totamountresult = $ilance->db->fetch_array($totamountselectproject);
	   $totamount = $ilance->currency->format($totamountresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	   
	   
	   // Total Amount From Live Items
	   $totliveamountselectproject = $ilance->db->query("SELECT sum(startprice) FROM " . DB_PREFIX . "projects WHERE status ='open'");
	   $totliveamountresult = $ilance->db->fetch_array($totliveamountselectproject);
	   $totliveamount = $ilance->currency->format($totliveamountresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	
	   
	   // Total Number Of Item Still now Posted In Site 	
	   $totitemselectproject = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects");
	   $totitem = $ilance->db->num_rows($totitemselectproject);
	   
	   // Total Number Of Item Live Now
	   $totliveitemselectproject = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE status ='open'");
	   $totliveitem = $ilance->db->num_rows($totliveitemselectproject);
	   
	   // Total Advance Amount Paid
	   $totadvselect = $ilance->db->query("SELECT sum(amount) FROM " . DB_PREFIX . "invoices WHERE invoicetype ='advance'");
	   $totadvresult = $ilance->db->fetch_array($totadvselect);
	   $totadv = $ilance->currency->format($totadvresult[0],$ilconfig['globalserverlocale_defaultcurrency']);
	   	   
	   
       //#######################################################################################################
	   // #### Dashboard Detail Listing Code Starts Here ###################################################### #   
   
	
	$pprint_array = array('monthnow','today_buyer','yes_buyer','month_buyer','sixty_buyer','ninety_buyer','ninety_reg','sixty_reg','month_reg','yes_reg','today_reg','arreport','totamtowing','totamtowing15','totamtowing30','totamtowingclient','auctionyes','auctionYessel','auctionsel','totliveitem_CurrentAuction','totliveitem_Currentdailydeal','totliveitem_nextdailydeal','totliveitem_nextAuction','binyes','totauctionyes','totbinyes','totamount','totliveamount','totitem','totliveitem','totadv','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'dashboard_newstats.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('arreport','feecal','apreport'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>