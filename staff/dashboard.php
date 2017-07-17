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
		/*
		
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
	   // #### Dashboard Top 10 A / R Report Listing Code Starts Here ##########################################
	   // ######################################################################################################
	   // Jan 12
	   $selectuser = $ilance->db->query("SELECT * FROM ".DB_PREFIX."users
	   					WHERE available_balance !='0' AND income_reported !='0'
						ORDER BY income_reported DESC LIMIT 10  ");
		
	   if( $ilance->db->num_rows($selectuser) > 0)
	   {
	    
		 while ($fetchuser = $ilance->db->fetch_array($selectuser))
		 {
		   $selectinv = $ilance->db->query("SELECT sum(amount) AS amount FROM ".DB_PREFIX."invoices
	   					WHERE user_id = '".$fetchuser['user_id']."'
						AND iswithdraw != '0'
						  ");
			$invres = $ilance->db->fetch_array($selectinv);
			$totwithdraw = array_sum($invres);
			$apamount = $fetchuser['income_reported'] - $totwithdraw;
			$fetchuser['amount'] = $ilance->currency->format($apamount,$ilconfig['globalserverlocale_defaultcurrency']);
		    $apreport[] = $fetchuser;
		 }
		 
	   }
	   // ######################################################################################################
	   // #### Dashboard Top 10 A / P Report Listing Code End Here ##########################################
	   // ######################################################################################################
		
		
	   // ######################################################################################################
	   // #### Dashboard Top 10 A / R Report Listing Code Starts Here ##########################################
	   // ######################################################################################################
	   
	  
				
				$selectinvoice = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices
							WHERE (isfvf ='1' OR isif = '1' OR isportfoliofee = '1' OR isenhancementfee = '1' OR isescrowfee = '1' OR iswithdrawfee = '1' OR isp2bfee = '1')							
							AND status = 'paid'							
							ORDER BY amount ASC
							LIMIT 10
							");							
				while ($fetchres = $ilance->db->fetch_array($selectinvoice))
				{
					
					$selectamt = $ilance->db->query("SELECT sum(amount) as amount  FROM " . DB_PREFIX . "invoices
							WHERE (isfvf ='1' OR isif = '1' OR isportfoliofee = '1' OR isenhancementfee = '1' OR isescrowfee = '1' OR iswithdrawfee = '1' OR isp2bfee = '1')							
							AND status = 'paid'
							AND user_id = '".$fetchres['user_id']."'
							");	
				$test = $ilance->db->fetch_array($selectamt);
				$arreportresult['amount'] = $fetchres['amount'];		
				$arreportresult['cust_id'] = $fetchres['user_id'];
				$arreportresult['date'] = $fetchres['createdate'];
				$arreportresult['name'] =  fetch_user('last_name', $fetchres['user_id']);
				$arreportresult['due_days'] = $fetchres['duedate'];
				$arreport[] = $arreportresult;
				}
		
		

	   // ######################################################################################################
	   // #### Dashboard Top 10 A / R Report Listing Code End Here #############################################
	   
	   
	   
	   // ######################################################################################################
	   // #### Dashboard Number Of Item Viewed And Registration Code Starts Here ###############################
	   // ######################################################################################################
	   
	   // To Calculate Nuber Of Registration By Date wise
	   $todayuser = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE date(date_added) = '".DATETODAY."'");
	   $today_reg = $ilance->db->num_rows($todayuser);
	   
	   $yesuser = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE date(date_added) = '".DATEYESTERDAY."'");
	   $yes_reg = $ilance->db->num_rows($todayuser);
	   
	   $monthuser = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE (date(date_added) <= '" .DATETODAY . "' AND date(date_added) >= '" . THIRTYDAYSAGO . "')");
	   $month_reg = $ilance->db->num_rows($monthuser);
	   
	   $sixtyuser = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE (date(date_added) <= '" .DATETODAY . "' AND date(date_added) >= '" . SIXTYDAYSAGO . "')");
	   $sixty_reg = $ilance->db->num_rows($sixtyuser);
	   
	   $ninetyuser = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE (date(date_added) <= '" .DATETODAY . "' AND date(date_added) >= '" . NINETYDAYSAGO . "')");
	   $ninety_reg = $ilance->db->num_rows($ninetyuser);
	  
	  //  Total Buyer Count 
	  
	   $todaybid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE bidstatus = 'awarded' AND date(date_awarded) = '".DATETODAY."' GROUP BY user_id");
	   $todaybin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders WHERE date(orderdate) = '".DATETODAY."' GROUP BY buyer_id");
	   $todaybidres = $ilance->db->num_rows($todaybid);
	   $todaybinres = $ilance->db->num_rows($todaybin);
	   $today_buyer = $todaybidres + $todaybinres;
	   
	   $yesbid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE bidstatus = 'awarded' AND date(date_awarded) = '".DATEYESTERDAY."' GROUP BY user_id");
	   $yesbin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders WHERE date(orderdate) = '".DATEYESTERDAY."' GROUP BY buyer_id");
	   $yesbidres = $ilance->db->num_rows($yesbid);
	   $yesbinres = $ilance->db->num_rows($yesbin);
	   $yes_buyer = $yesbidres + $yesbinres;
	   
	   $monthbid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE bidstatus = 'awarded' AND (date(date_awarded) <= '" .DATETODAY . "' AND date(date_awarded) >= '" . THIRTYDAYSAGO . "') GROUP BY user_id");
	   $monthbin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders WHERE (date(orderdate) <= '" .DATETODAY . "' AND date(orderdate) >= '" . THIRTYDAYSAGO . "') GROUP BY buyer_id");
	   
	   $monthbidres = $ilance->db->num_rows($monthbid);
	   $monthbinres = $ilance->db->num_rows($monthbin);
	   $month_buyer = $monthbidres + $monthbinres;
	   
	   
	   $sixtybid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE bidstatus = 'awarded' AND (date(date_awarded) <= '" .DATETODAY . "' AND date(date_awarded) >= '" . SIXTYDAYSAGO . "') GROUP BY user_id");
	    $sixtybin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders WHERE (date(orderdate) <= '" .DATETODAY . "' AND date(orderdate) >= '" . SIXTYDAYSAGO . "') GROUP BY buyer_id");
	   $sixtybidres = $ilance->db->num_rows($sixtybid);
	   $sixtybinres = $ilance->db->num_rows($sixtybin);
	   $sixty_buyer = $sixtybidres +  $sixtybinres;
	   
	   $ninetybid = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE bidstatus = 'awarded' AND (date(date_awarded) <= '" .DATETODAY . "' AND date(date_awarded) >= '" . NINETYDAYSAGO . "') GROUP BY user_id");
	    $ninetybin = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "buynow_orders WHERE (date(orderdate) <= '" .DATETODAY . "' AND date(orderdate) >= '" . NINETYDAYSAGO . "') GROUP BY buyer_id");
	   $ninetybidres = $ilance->db->num_rows($sixtybid);
	   $ninetybinres = $ilance->db->num_rows($sixtybin);
	   $ninety_buyer = $ninetybidres +  $ninetybinres;
	    
	   
	    // ######################################################################################################
	   // #### Dashboard Number Of Item Viewed And Registration Code End Here ###################################
	   
	   
	   //$monthnow = date("F, Y"); 
	   		
			for($i=0;$i<6;$i++){
			$time = strtotime("today - $i months");
			$monthnow = date("F - Y",$time);
			$testmonth =  date("m",$time);
			
			$buyerfeeselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "invoices WHERE isp2bfee = '1' AND month(paiddate) = '".$testmonth."'");
			$buyerfeeres = $ilance->db->fetch_array($buyerfeeselect);
			
			$advanceselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "invoices WHERE invoicetype = 'advance' AND month(paiddate) = '".$testmonth."'");
			$advanceres = $ilance->db->fetch_array($advanceselect);
			
			$fvfselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "invoices WHERE isfvf = '1' AND month(paiddate) = '".$testmonth."'");
			$fvfres = $ilance->db->fetch_array($fvfselect);
			
			$ifselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "invoices WHERE isif = '1' AND month(paiddate) = '".$testmonth."'");
			$ifres = $ilance->db->fetch_array($ifselect);
			
			$test['months'] = $monthnow;	
			$test['buyerfee'] = $ilance->currency->format($buyerfeeres['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
			$test['fvffee'] = $ilance->currency->format($fvfres['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
			$test['insfee'] = $ilance->currency->format($ifres['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
			$test['adv_int'] = $ilance->currency->format($advanceres['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
			
			
								
			$feecal[] = $test;
			
			}
		
	  
	   
	   // ######################################################################################################
	   // #### Dashboard Detail Listing Code Starts Here #######################################################
	   // ######################################################################################################
	  	   
	   // Total Number of Auction Yesturday
	    $auctionyesselect = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "project_bids WHERE date(date_awarded) = '".DATEYESTERDAY."'");
	    $auctionyes = $ilance->db->num_rows($auctionyesselect);
	   
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
	   	   
	   */
       //#######################################################################################################
	   // #### Dashboard Detail Listing Code Starts Here ###################################################### #   
   
	
	$pprint_array = array('monthnow','today_buyer','yes_buyer','month_buyer','sixty_buyer','ninety_buyer','ninety_reg','sixty_reg','month_reg','yes_reg','today_reg','arreport','totamtowing','totamtowing15','totamtowing30','totamtowingclient','auctionyes','binyes','totauctionyes','totbinyes','totamount','totliveamount','totitem','totliveitem','totadv','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'dashboard.html', 2);
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