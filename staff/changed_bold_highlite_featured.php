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
	'administration',
	
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
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['consignment'] => $ilcrumbs[$ilpage['consignment']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	

		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'CBHF_list_edit')
		{
			// if ((isset($ilance->GPC['bold']) AND is_array($ilance->GPC['bold'])  AND count($ilance->GPC['bold'])>0 ) OR (isset($ilance->GPC['highlite']) AND is_array($ilance->GPC['highlite'])  AND count($ilance->GPC['highlite'])>0 ) OR (isset($ilance->GPC['featured']) AND is_array($ilance->GPC['featured'])  AND count($ilance->GPC['featured'])>0 ))  
			// {
				$bold=$ilance->GPC['bold'];
				$highlite=$ilance->GPC['highlite'];
				$featured=$ilance->GPC['featured'];
				$project_id=$ilance->GPC['project_id'];
                 
         
				 foreach($project_id as $pb) 
				{

					if (in_array($pb, $bold))
                    {
                    		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET bold = '1' WHERE  coin_id ='" . $pb . "'");
						    $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET bold = '1' WHERE  project_id ='" . $pb . "'");
                    }
                    else
                    {
                    		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET bold = '0' WHERE  coin_id ='" . $pb . "'");
						    $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET bold = '0' WHERE  project_id ='" . $pb . "'");
                    }
				}

				foreach($project_id as $ph) 
				{

					if (in_array($ph, $highlite))
                    {
                    		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET highlite = '1' WHERE  coin_id ='" . $ph . "'");
						    $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET highlite = '1' WHERE  project_id ='" . $ph . "'");
                    }
                    else
                    {
                    		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET highlite = '0' WHERE  coin_id ='" . $ph . "'");
						    $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET highlite = '0' WHERE  project_id ='" . $ph . "'");
                    }
				}
				foreach($project_id as $pf) 
				{

					if (in_array($pf, $featured))
                    {
                    		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET featured = '1' WHERE  coin_id ='" . $pf . "'");
						    $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET featured = '1' WHERE  project_id ='" . $pf . "'");
                    }
                    else
                    {
                    		$ilance->db->query("UPDATE " . DB_PREFIX . "coins SET featured = '0' WHERE  coin_id ='" . $pf . "'");
						    $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET featured = '0' WHERE  project_id ='" . $pf . "'");
                    }
				}		 

                 
		        print_action_success("Task have been successfully completed. you can check it in the user end ", $_SERVER['PHP_SELF']);
		        exit();

				 
 

			// }
	  //   	else
			// {
			// 	print_action_failed("We're sorry. Select any one items for bold, highlite and featured", $_SERVER['PHP_SELF']);
			// 	exit();
			// }
	    }




 
	   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
       {
					
					$show['search_list'] = 'search_list';
					
					 $filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : '';
                        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
                        $where = "AND ";
						 

			 
			if (!empty($filtervalue) AND !empty($filterby))
			{
				if($filterby == 'c.coin_id' || $filterby == 'u.username' || $filterby == 'c.consignid' || $filterby == 'u.user_id' || $filterby == 'u.email')
				{
					  $where .= $filterby . " = '" . $filtervalue . "'";
					  
					  $get_filtervalue = $ilance->GPC['filtervalue'];  

						$consign = $ilance->db->query("SELECT p.project_id, p.project_title, p.bids, p.currentprice, p.bold, p.highlite, p.featured, p.filtered_auctiontype, p.buynow_qty  FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id  where p.status='open' and u.status='active' $where ORDER BY p.project_id desc", 0, null, __FILE__, __LINE__);
						
						$number_search = (int)$ilance->db->num_rows($consign);
					
						$consign1 = $ilance->db->query("SELECT p.project_id, p.project_title, p.bids, p.currentprice, p.bold, p.highlite, p.featured, u.user_id, p.filtered_auctiontype, p.buynow_qty  FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id  where p.status='open' and u.status='active' $where ORDER BY p.project_id desc", 0, null, __FILE__, __LINE__);

						 

					
						$number = (int)$ilance->db->num_rows($consign1);					

						if($ilance->db->num_rows($consign) > 0)
						{
								$row_con_list=0;
								$row_list = array();	
								while($resconsign_user = $ilance->db->fetch_array($consign))
								{
								  $row_list['project_id'] = $resconsign_user['project_id'];
								  $row_list['project_title'] = $resconsign_user['project_title'];
								  if($resconsign_user['filtered_auctiontype'] == 'regular')
								  {

								  $row_list['bids'] = $resconsign_user['bids'];
								  $row_list['buy'] = '0';

								  }

								  if($resconsign_user['filtered_auctiontype'] == 'fixed')
								  {

								  $row_list['buy'] = $resconsign_user['buynow_qty'];
								  $row_list['bids'] = '0';

								  }
								  $row_list['currentprice'] = $ilance->currency->format($resconsign_user['currentprice']);
								  if($resconsign_user['bold'] == '0')
								  $row_list['bold'] = '';
								  else
								  $row_list['bold'] = 'checked="checked"';
								  if($resconsign_user['highlite'] == '0')
								  $row_list['highlite'] = '';
								  else
								  $row_list['highlite'] = 'checked="checked"';
								  if($resconsign_user['featured'] == '0')
								  $row_list['featured'] = '';
								  else
								  $row_list['featured'] = 'checked="checked"';
 						   		  $bold_highlite_featured_search[] = $row_list;	
								  $row_con_list++; 
									
								}	
						}
						else
						{				
							$show['no'] = 'list_search';
						}	
					
					 							
				}
				else
				{				
					$show['no'] = 'list_search';
				}
				 
			}
			else
			{				
				$show['no'] = 'list_search';
			}
			 		
						
						
						

		} 	
	              								
		            								
		 //contactus listing
		 
		   $counter = ($ilance->GPC['page'] - 1) * 50;
				 $scriptpageprevnext = 'changed_bold_highlite_featured.php?cmd=listing';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
		
		
		$consign_user = $ilance->db->query("SELECT p.project_id, p.project_title, p.bids, p.currentprice, p.bold, p.highlite, p.featured, u.user_id, p.filtered_auctiontype, p.buynow_qty  FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id  where p.status='open' and u.status='active' ORDER BY p.project_id desc LIMIT " . (($ilance->GPC['page'] - 1) * 50) . "," . '50'."", 0, null, __FILE__, __LINE__);
		
	
		$consign1_user = $ilance->db->query("SELECT p.project_id, p.project_title, p.bids, p.currentprice, p.bold, p.highlite, p.featured, u.user_id, p.filtered_auctiontype, p.buynow_qty FROM " . DB_PREFIX . "projects p left join " . DB_PREFIX . "users u on u.user_id=p.user_id left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id  where p.status='open' and u.status='active' ORDER BY p.project_id desc", 0, null, __FILE__, __LINE__);
       
		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($consign1_user);
		$row_list = array();
		while($resconsign_user = $ilance->db->fetch_array($consign_user))
		{
		
			
				  $row_list['project_id'] = $resconsign_user['project_id'];
				  $row_list['project_title'] = $resconsign_user['project_title'];

				  if($resconsign_user['filtered_auctiontype'] == 'regular')
				  {

				  $row_list['bids'] = $resconsign_user['bids'];
				  $row_list['buy'] = '0';

				  }

				  if($resconsign_user['filtered_auctiontype'] == 'fixed')
				  {

				  $row_list['buy'] = $resconsign_user['buynow_qty'];
				  $row_list['bids'] = '0';

				  }

				  

				  $row_list['currentprice'] = $ilance->currency->format($resconsign_user['currentprice']);
				  if($resconsign_user['bold'] == '0')
				  $row_list['bold'] = '';
				  else
				  $row_list['bold'] = 'checked="checked"';
				  if($resconsign_user['highlite'] == '0')
				  $row_list['highlite'] = '';
				  else
				  $row_list['highlite'] = 'checked="checked"';
				  if($resconsign_user['featured'] == '0')
				  $row_list['featured'] = '';
				  else
				  $row_list['featured'] = 'checked="checked"';
   				  $bold_highlite_featured[] = $row_list;	
				  $row_con_list++; 
			
		}			
		
		
    $listing_pagnation = print_pagnation($number, 50, $ilance->GPC['page'], $counter, $scriptpageprevnext);


 	$pprint_array = array('number_search','number','listing_pagnation', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer','login_include_admin','ilanceversion','get_filtervalue');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'changed_bold_highlite_featured.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('bold_highlite_featured','bold_highlite_featured_search'));
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