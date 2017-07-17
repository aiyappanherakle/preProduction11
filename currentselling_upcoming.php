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
        'javascript'
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
		'flashfix'
	);

// #### define top header nav ##################
	$topnavlink = array(
        'main_listings'
	);

// #### setup script location ##################################################
	define('LOCATION', 'search');

// #### require backend ########################################################
	require_once('./functions/config.php');
//error_reporting(E_ALL);

// #### setup default breadcrumb ###############################################
	$navcrumb = array("currentselling_upcoming.php" => $phrase['_current_selling_upcoming_bread_crumb']);
	$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';
	$page_title = $phrase['_current_selling_upcoming_page_title'];


	($apihook = $ilance->api('search_start')) ? eval($apihook) : false;
			$select_default_current_selling_coin = $ilance->db->query("SELECT *
													FROM " . DB_PREFIX . "issues_coin
													WHERE cointype = 1
													ORDER BY releasedate DESC limit 35");
													
			$number = $ilance->db->num_rows($select_default_current_selling_coin);

			if ($ilance->db->num_rows($select_default_current_selling_coin) > 0)
			{
				
                               
					while ($result_default_current_selling_coin = $ilance->db->fetch_array($select_default_current_selling_coin, DB_ASSOC))
					{
						$result_default_current_selling_coin['title'];
						$result_default_current_selling_coin['description'];
						$result_default_current_selling_coin['link'];
						$result_default_current_selling_coin['categorieslink'];

						$currentissues_coin[] = $result_default_current_selling_coin;
					}

					
			}

			$select_upcoming_selling_coin = $ilance->db->query("SELECT *
													FROM " . DB_PREFIX . "issues_coin
													WHERE cointype = 0
													ORDER BY releasedate asc limit 35");
													
			$number = $ilance->db->num_rows($select_upcoming_selling_coin);

			if ($ilance->db->num_rows($select_upcoming_selling_coin) > 0)
			{
				
                               
					while ($result_upcoming_selling_coin = $ilance->db->fetch_array($select_upcoming_selling_coin, DB_ASSOC))
					{
					
						$result_upcoming_selling_coin['title'];
						$result_upcoming_selling_coin['description'];
						$result_upcoming_selling_coin['releasedate'];
						$result_upcoming_selling_coin['excepteddate'];
						$result_upcoming_selling_coin['categorieslink'];

						$upcoming_coin[] = $result_upcoming_selling_coin;
					}

					
			}				
	
	




($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'currentselling_upcoming.html');
$ilance->template->parse_if_blocks('main');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('currentissues_coin','upcoming_coin'));


$pprint_array = array('cac_noncac_list','colsperrow','region_pulldown_product','locatedin_pulldown_product','availableto_pulldown_product','search_bidrange_pulldown_product','search_radius_country_pulldown_product','search_country_pulldown_product','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_displayfeatured','cb_hidelisted','cb_proxybit','cb_hideverbose','productavailable','productselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','product_category_selection','search_productauctions_img','search_productauctions_collapse','pfp_category_left','rfp_category_left','input_style','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');

($apihook = $ilance->api('search_start_template')) ? eval($apihook) : false;
 
$ilance->template->pprint('main', $pprint_array);
exit();


		
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>