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
	

	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'changed_invoice_update')
    {



    	if ((isset($ilance->GPC['changed_user_id']) AND empty($ilance->GPC['changed_user_id']))  OR (isset($ilance->GPC['user_id']) AND empty($ilance->GPC['user_id'])))
		{
			print_action_failed("We're sorry. Enter the Valid changed user id for pending invoice", $_SERVER['PHP_SELF']);
			exit();
		}
		if (isset($ilance->GPC['change_pending_project_id']) AND is_array($ilance->GPC['change_pending_project_id'])  AND count($ilance->GPC['change_pending_project_id'])>0 ) 
		{
			

			 $coins_list=$ilance->GPC['change_pending_project_id'];
  			 $user_id = $ilance->GPC['user_id'];
	  		 $changed_user_id=$ilance->GPC['changed_user_id'];
			
			 if (!preg_match('/^[0-9]*$/', $ilance->GPC['changed_user_id'])) {
			print_action_failed('Please give valid Moved User Id  details','pendinginvoice.php');
		    exit();
			 }
			 $userid = fetch_user('user_id',$ilance->GPC['changed_user_id']);

	  		 if (!preg_match('/^[0-9]*$/', $userid)) {
			print_action_failed('Please give valid Moved User Id  details','pendinginvoice.php');
		    exit();
			 }

	  		 if($user_id == $changed_user_id)
	  		 {

	  	     print_action_failed("We're sorry. Enter the Valid Moved user id for pending invoice", $_SERVER['PHP_SELF']);
			 exit();

	  		 }
             $items = array();
             $itemss = array();
	 		 foreach($coins_list as $inv => $coin_id)
		     {	

 
			    $sql_seller = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = ".$coin_id, 0, null, __FILE__, __LINE__);

				$res_seller = $ilance->db->fetch_array($sql_seller);                


				if($res_seller['user_id'] != $changed_user_id)
				{

					 

			        $sql1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = ".$coin_id, 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql1) > 0) 
					{   

					    $items[] = $coin_id;                
	                   	while ($res1 = $ilance->db->fetch_array($sql1)) 
						{

	 

							if(fetch_auction('filtered_auctiontype', $res1['project_id']) == 'regular')
							{

								//echo '<br/><br/>';
								//echo "UPDATE " . DB_PREFIX . "invoices SET user_id='".$changed_user_id."'  WHERE projectid = '".$res1['project_id']."' AND user_id =".$user_id;
								$ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET user_id='".$changed_user_id."'  WHERE projectid = '".$res1['project_id']."' AND user_id =".$user_id);
	 
								//echo '<br/><br/>';
								//echo "UPDATE " . DB_PREFIX . "projects SET winner_user_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND winner_user_id =".$user_id;
								$ilance->db->query("UPDATE " . DB_PREFIX . "projects SET winner_user_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND winner_user_id =".$user_id);
								 

								$chk_sql = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "proxybid WHERE project_id = '".$res1['project_id']."' AND user_id =".$changed_user_id, 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($chk_sql) > 0) 
								{
									//echo '<br/><br/>';
									//echo " DELETE * FROM " . DB_PREFIX . "proxybid
	                                               // WHERE project_id = '".$res1['project_id']."' AND user_id = ".$changed_user_id;
									$ilance->db->query(" DELETE FROM " . DB_PREFIX . "proxybid
	                                                WHERE project_id = '".$res1['project_id']."' AND user_id = ".$changed_user_id);

									//echo '<br/><br/>';
									//echo " DELETE *  FROM " . DB_PREFIX . "project_bids WHERE user_id = '".$change_to_userid."' AND project_id = '".$res2['projectid']."' ";
									//$ilance->db->query(" DELETE *  FROM " . DB_PREFIX . "project_bids WHERE user_id = '".$change_to_userid."' AND project_id = '".$res2['projectid']."' ");
								}

								//echo '<br/><br/>';
								//echo "UPDATE " . DB_PREFIX . "proxybid SET user_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND user_id = ".$user_id;
								$ilance->db->query("UPDATE " . DB_PREFIX . "proxybid SET user_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND user_id = ".$user_id);
	 
								//echo '<br/><br/>';
								//echo "UPDATE " . DB_PREFIX . "project_bids SET user_id='".$changed_user_id."' WHERE user_id = '".$user_id."' AND project_id = '".$res1['project_id']."' ";
								$ilance->db->query("UPDATE " . DB_PREFIX . "project_bids SET user_id='".$changed_user_id."' WHERE user_id = '".$user_id."' AND project_id = '".$res1['project_id']."' ");
								 

								//echo '<br/><br/>';
								//echo "UPDATE " . DB_PREFIX . "projects_escrow SET user_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND user_id = ".$user_id;
								$ilance->db->query("UPDATE " . DB_PREFIX . "projects_escrow SET user_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND user_id = ".$user_id);
								 
							}
							else
							{

								//echo '<br/><br/>';
	 							//echo "UPDATE " . DB_PREFIX . "invoices SET user_id='".$changed_user_id."'  WHERE projectid = '".$res1['project_id']."' AND invoiceid = '".$inv."' AND user_id =".$user_id; 
								$ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET user_id='".$changed_user_id."'  WHERE projectid = '".$res1['project_id']."' AND invoiceid = '".$inv."' AND user_id =".$user_id);
	 
	 							//echo '<br/><br/>';
								//echo "UPDATE " . DB_PREFIX . "buynow_orders SET buyer_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND invoiceid = '".$inv."' AND buyer_id = ".$user_id;
								$ilance->db->query("UPDATE " . DB_PREFIX . "buynow_orders SET buyer_id='".$changed_user_id."' WHERE project_id = '".$res1['project_id']."' AND invoiceid = '".$inv."' AND buyer_id = ".$user_id);
								 
							}


						}


					}
 
                }
                else
                {
			      $itemss[] = $coin_id;
                }

		     }


		     $moved = implode(',', $items); 
		     $not_moved = implode(',', $itemss);

 	
			$cinsresult = count($itemss);
			$cinsresults = count($items); 
			if($cinsresults > 0)
			{
                $result_items = "The Remaining ( ".$moved." ) coins are moved to pending invoice, you can check it in the user end";
                $result_check ="Pending invoice Moved from ".$user_id." to ".$changed_user_id.".";
			}
			else
			{
			
				$result_items ="";
			}

			if($cinsresult > 0)
			{
				print_action_success("Task have been successfully completed. ".$result_check."  The (".$not_moved.") coins are not moved to pending invoice, you can check it in the different seller user id, ".$result_items.", ", $_SERVER['PHP_SELF']);exit();

			}else
			{
				print_action_success("Task have been successfully completed. Pending invoice Moved from ".$user_id." to ".$changed_user_id.". and The (".$moved.") coins are moved to pending invoice, you can check it in the user end ", $_SERVER['PHP_SELF']);exit();

			}

	exit;

              
    	}				
		else
		{
			print_action_failed("We're sorry. Select any one pending invoice", $_SERVER['PHP_SELF']);
			exit();
		}





	      



	         
	}




 
	   if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
       {
					
					$show['search_list'] = 'search_list';
					
					  $filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : '';
                       $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
 
						 

			 
			if (!empty($filtervalue) AND !empty($filterby))
			{

				$where = "WHERE ".$filterby."='".$filtervalue."' ";

				$sql = $ilance->db->query("
	                                SELECT user_id
	                                FROM " . DB_PREFIX . "users
	                                $where
	                        	", 0, null, __FILE__, __LINE__);

				if($ilance->db->num_rows($sql) > 0)
				{
 
					    $res_list_sec = $ilance->db->fetch_array($sql);

					    $user_id = $res_list_sec['user_id'];
 
				 	  	$consign = $ilance->db->query("SELECT *
									FROM " . DB_PREFIX . "invoices
									WHERE status = 'unpaid'	and not combine_project							 
									AND user_id= '".$res_list_sec['user_id']."' 
									AND isfvf != 1
									AND isif != 1 
									AND isbuyerfee != 1 
									AND isenhancementfee != 1", 0, null, __FILE__, __LINE__);						

						if($ilance->db->num_rows($consign) > 0)
						{				    


								$row_con_list=0;
								$row_list = array();	
								while($resconsign_list = $ilance->db->fetch_array($consign))
								{
								
								$row_list['invoiceid'] = $resconsign_list['invoiceid'];
							      $row_list['project_id'] = $resconsign_list['projectid'];

							      $row_list['project_title'] = fetch_coin_table('Title',$resconsign_list['projectid']);

							      if($resconsign_list['Site_Id'] >0)
								  {
								  $res_regard='eBay';
								  }
								  else
								  {
								  $res_regard='GC';
								  }
								  $row_list['Site_Id'] 	 =$res_regard;

									$buy = $ilance->db->query("SELECT qty, invoiceid FROM " . DB_PREFIX . "buynow_orders
																WHERE invoiceid = '".$resconsign_list['invoiceid']."'
																AND buyer_id = '".$user_id."'");
									if($ilance->db->num_rows($buy)>0)
									{
										$resbuy = $ilance->db->fetch_array($buy);						
 										$row_list['qty'] = $resbuy['qty'];
 										//$row_list['invoiceid'] = $resbuy['invoiceid'];

 									}
									else
									{								 		
													
										$row_list['qty'] = 1;
										//$row_list['invoiceid'] = '';

										 
 									}

									$row_list['finalprice'] = $ilance->currency->format($resconsign_list['amount'],$ilconfig['globalserverlocale_defaultcurrency']);


									$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
																WHERE projectid = '".$resconsign_list['projectid']."'
																AND user_id = '".$user_id."'
																AND isbuyerfee = '1'");
									if($ilance->db->num_rows($buyfee_inv) > 0)
									{
										$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
										$totalamountlist = $ilance->currency->format(($resconsign_list['amount'] + $res_buyfee['amount'] ), $resconsign_list['currency_id']);
										$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $resconsign_list['currency_id']);
										$buyerfee1 = $res_buyfee['amount'];
										$totalamountlist1 = $resconsign_list['amount'] + $res_buyfee['amount'] ;
										$show['buyer'] = 1;
									}
									else
									{
										$totalamountlist = $ilance->currency->format(($resconsign_list['amount'] ), $resconsign_list['currency_id']);
										$buyerfee =  $ilance->currency->format(0, $resconsign_list['currency_id']);
										$buyerfee1 = 0;
										$totalamountlist1 = $resconsign_list['amount'];
										$show['buyer'] = 1;
									}

								  $row_list['buyerfees'] = $buyerfee;
								  $row_list['totallistamount'] =  $totalamountlist;
		                          $pending_listing_search[] = $row_list;
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
		 			
		
		
 

 	$pprint_array = array('number_search','number','login_include_admin','ilanceversion','user_id','username','filtervalue','invoices_count','buildversion','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'pending_listing.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('contactus_listing','pending_listing_search'));
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