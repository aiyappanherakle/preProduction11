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
	


	   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
       {
					
					$show['search_list'] = 'search_list';
					
					 $filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : '';
                        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
						$where = "WHERE ";   

			 
			if (!empty($filtervalue) AND !empty($filterby))
			{
				if($filterby == 'project_id')
				{
					  $where .= $filterby . " = '" . $filtervalue . "'";
 

						$consign = $ilance->db->query("SELECT *	FROM " . DB_PREFIX . "minimum_bid_log $where 
														ORDER BY id desc", 0, null, __FILE__, __LINE__);
						
						$number_search = (int)$ilance->db->num_rows($consign);
					
						$consign1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."minimum_bid_log $where
							   ORDER BY id desc", 0, null, __FILE__, __LINE__);

					
						$number = (int)$ilance->db->num_rows($consign1);
						

						if($ilance->db->num_rows($consign) > 0)
						{
								$row_con_list=0;
								$row_list = array();	
								while($resminbidlog_list = $ilance->db->fetch_array($consign))
								{
								
									
								      $row_list['project_id'] = $resminbidlog_list['project_id'];

								      $row_list['original_minimum_bid'] = "$".$resminbidlog_list['original_minimum_bid'];

								      $date = date("d-M-Y", strtotime($resminbidlog_list['changed_on']));
								      $time = date("g:i:s a", strtotime($resminbidlog_list['changed_on']));
					                  $row_list['changeddate'] = $date;
					                  $row_list['changedtime'] = $time;
								      $row_list['changed_minimum_bid'] = "$".$resminbidlog_list['changed_minimum_bid'];

					   				  $minbidlog_listing_search[] = $row_list;	
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
	              								
		            								
		 //Minimum Bid Log listing
		 
		   $counter = ($ilance->GPC['page'] - 1) * 100;
				 $scriptpageprevnext = 'minbidlog.php?';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
		
		
		$minbidlog = $ilance->db->query("SELECT *	FROM " . DB_PREFIX . "minimum_bid_log ORDER BY id desc LIMIT " . (($ilance->GPC['page'] - 1) * 100) . "," . '100'."", 0, null, __FILE__, __LINE__);
		
	
		$minbidlog1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."minimum_bid_log ORDER BY id desc", 0, null, __FILE__, __LINE__);

		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($minbidlog1);
		$row_list = array();
		while($resminbidlog_list = $ilance->db->fetch_array($minbidlog))
		{
		
			
			      $row_list['project_id'] = $resminbidlog_list['project_id'];

			      $row_list['original_minimum_bid'] = "$".$resminbidlog_list['original_minimum_bid'];

			      $date = date("d-M-Y", strtotime($resminbidlog_list['changed_on']));
			      $time = date("g:i:s a", strtotime($resminbidlog_list['changed_on']));
                  $row_list['changeddate'] = $date;
                  $row_list['changedtime'] = $time;
			      $row_list['changed_minimum_bid'] = "$".$resminbidlog_list['changed_minimum_bid'];

   				  $minbidlog_listing[] = $row_list;	
				  $row_con_list++; 
			
		}			
		
		
    $listing_pagnation = print_pagnation($number, 100, $ilance->GPC['page'], $counter, $scriptpageprevnext);


 	$pprint_array = array('number_search','number','listing_pagnation','login_include_admin','ilanceversion','filtervalue');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'minbidlog_listing.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('minbidlog_listing','minbidlog_listing_search'));
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