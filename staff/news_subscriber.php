<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||
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
ini_set('memory_limit', '5024M');
set_time_limit(0);

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
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
			
			 $fromdate = (isset($ilance->GPC['from_date']) AND !empty($ilance->GPC['from_date'])) ? $ilance->GPC['from_date'] : '';
			 $todate = (isset($ilance->GPC['to_date']) AND !empty($ilance->GPC['to_date'])) ? $ilance->GPC['to_date'] : '';

		 if($ilance->GPC['filter'] !='all')
		 {
		 				$status = (isset($ilance->GPC['filter']) AND !empty($ilance->GPC['filter'])) ? $ilance->GPC['filter'] : '0';
		 }
		  


			 


 

		      if (!empty($filtervalue) AND !empty($filterby))
			  {

                 $condition = "email" . " = '" . $filtervalue . "'";
			 
			  }
			  if (!empty($fromdate) AND !empty($todate))
			  {
				 $condition1 = "subscribe_date BETWEEN '".$fromdate."' AND '".$todate."'";
				 if(!empty($filtervalue))
				 {
					 $condition1 = "and subscribe_date BETWEEN '".$fromdate."' AND '".$todate."'";
				 }
			     
			  }
			  if (!empty($status) OR ($status == '0') )
			  {
				 $condition2 = "status = '".$status."'";
				 if(!empty($filtervalue) OR (!empty($fromdate) AND !empty($todate))) 
				 {
					 $condition2 = "and status = '".$status."'";
				 }
			     
			  }
			  else
			  {
			  	 $condition2 = "status IN ('0', '1')" ;
				 if(!empty($filtervalue) OR (!empty($fromdate) AND !empty($todate))) 
				 {
			  	 $condition2 = "AND status IN ('0', '1')" ;
				 }
 
			  }
			 


		                $consign = $ilance->db->query("SELECT *	FROM " . DB_PREFIX . "subscriber
						                                WHERE $condition
						                                $condition1
						                                $condition2
														ORDER BY subscriber_id desc", 0, null, __FILE__, __LINE__);
														
														 
						
						$number_search = (int)$ilance->db->num_rows($consign);
					
						$consign1 = $ilance->db->query("SELECT *	FROM " . DB_PREFIX . "subscriber
						                                WHERE $condition
						                                $condition1
						                                $condition2
														ORDER BY subscriber_id desc", 0, null, __FILE__, __LINE__);

					
						$number = (int)$ilance->db->num_rows($consign1);
						
		if(isset($ilance->GPC['export']) AND $ilance->GPC['export'] == 'excel')
		{
						$timeStamp = date("Y-m-d-H-i-s");
			$fileName = "consignor_owned_list_-$timeStamp";
			header('Content-Type: text/csv; charset=utf-8');

			 
				$fields = array('Subscriber Id','Subscriber Email','Subscriber Date','Action');

			 
			 

			header('Content-Disposition: attachment; filename='.$fileName.'.csv');		

			$fp = fopen('php://output', 'w');
			fputcsv($fp, $fields);

 						if($ilance->db->num_rows($consign) > 0)
						{
								$row_con_list=0;
								$row_list = array();	
								while($resconsign_list = $ilance->db->fetch_array($consign))
								{
								
									
									$row_list['subscriber_id'] = $resconsign_list['subscriber_id'];

									$row_list['email'] = $resconsign_list['email'];
									$date = date("d-M-Y", strtotime($resconsign_list['subscribe_date']));
									$time = date("g:i:s a", strtotime($resconsign_list['subscribe_date']));
									$row_list['subscribe_date'] = $date;
									$row_list['subscribe_time'] = $time;	
									$txt =  ($resconsign_list['status']) ? 'btn-success' : 'btn-danger';
									$txt_status =  ($resconsign_list['status']) ? 'Subscriber' : 'Un-Subscriber';

									$row_list['delete'] = '<i data="' . $resconsign_list['subscriber_id'] . '" class="status_checks btn '.$txt.'">'.$txt_status.'</i>';			  

									$contactus_listing_search[] = $row_list;	
									$row_con_list++; 

				                    $data2['subscriber_id']= $resconsign_list['subscriber_id'];
									$data2['subscriber_email']=$resconsign_list['email'];
									$data2['subscribe_date']= $resconsign_list['subscribe_date'];
									$data2['status']= $txt_status;
									$res[] = $data2;
		 						    fputcsv($fp, $data2); 

									
								}
								exit;	
						}
						else
						{	
						 		
							$data3['status']= "No result found";
							$res[] = $data3;
		 				    fputcsv($fp, $data3);
							exit;
						}
			 
		}else
		{
						if($ilance->db->num_rows($consign) > 0)
						{
								$row_con_list=0;
								$row_list = array();	
								while($resconsign_list = $ilance->db->fetch_array($consign))
								{
								
									
									$row_list['subscriber_id'] = $resconsign_list['subscriber_id'];

									$row_list['email'] = $resconsign_list['email'];
									$date = date("d-M-Y", strtotime($resconsign_list['subscribe_date']));
									$time = date("g:i:s a", strtotime($resconsign_list['subscribe_date']));
									$row_list['subscribe_date'] = $date;
									$row_list['subscribe_time'] = $time;	
									$txt =  ($resconsign_list['status']) ? 'btn-success' : 'btn-danger';
									$txt_status =  ($resconsign_list['status']) ? 'Subscriber' : 'Un-Subscriber';

									$row_list['delete'] = '<i data="' . $resconsign_list['subscriber_id'] . '" class="status_checks btn '.$txt.'">'.$txt_status.'</i>';			  

									$contactus_listing_search[] = $row_list;	
									$row_con_list++; 
									
								}	
						}
						else
						{	
						 		
							$show['no'] = 'list_search';
						}
		}			 
 				

		}else{
 
			$show['no'] = 'list_search';
		}	
	              								
		            								
		 //contactus listing
		 
		   $counter = ($ilance->GPC['page'] - 1) * 10;
				 $scriptpageprevnext = 'news_subscriber.php?cmd=listing';
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
				 
		
		
		$consign = $ilance->db->query("SELECT *	FROM " . DB_PREFIX . "subscriber ORDER BY subscriber_id desc LIMIT " . (($ilance->GPC['page'] - 1) * 10) . "," . '10'."", 0, null, __FILE__, __LINE__);
										
									 
		
	
		$consign1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "subscriber ORDER BY subscriber_id desc", 0, null, __FILE__, __LINE__);

		$row_con_list=0;	
		$number = (int)$ilance->db->num_rows($consign1);
		$row_list = array();
		while($resconsign_list = $ilance->db->fetch_array($consign))
		{
		
			
			      $row_list['subscriber_id'] = $resconsign_list['subscriber_id'];

			      $row_list['email'] = $resconsign_list['email'];
			      $date = date("d-M-Y", strtotime($resconsign_list['subscribe_date']));
			      $time = date("g:i:s a", strtotime($resconsign_list['subscribe_date']));
				  $row_list['subscribe_date'] = $date;
			      $row_list['subscribe_time'] = $time;
			    
			      $txt =  ($resconsign_list['status']) ? 'btn-success' : 'btn-danger';
			      $txt_status =  ($resconsign_list['status']) ? 'Subscriber' : 'Un-Subscriber';

			      $row_list['delete'] = '<i data="' . $resconsign_list['subscriber_id'] . '" class="status_checks btn '.$txt.'">'.$txt_status.'</i>';	

			     

   				  $contactus_listing[] = $row_list;	
				  $row_con_list++; 
			
		}			
		
		
    $listing_pagnation = print_pagnation($number, 10, $ilance->GPC['page'], $counter, $scriptpageprevnext);


 	$pprint_array = array('number_search','number','listing_pagnation', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer','login_include_admin','ilanceversion');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'news_subscriber_listing.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('contactus_listing','contactus_listing_search'));
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
