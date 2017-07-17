<?php
//error_reporting(E_ALL);
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
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
        'modal',
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'merch');

// #### require backend #############s###########################################
require_once('./functions/config.php');

$res_rfp['project_id'] = '25486'; 
$pcgs_coins = fetch_coin_table('pcgs',$res_rfp['project_id']);
$grade_coins = fetch_coin_table('Grade',$res_rfp['project_id']);
$service_coins = fetch_coin_table('Grading_Service',$res_rfp['project_id']);

echo NEXTAUCTION;
                                    $messagebody .= "*********************************". "<br/>";
									$messagebody .= "Reccomend Projects"."<br/>";
									$messagebody .= "*********************************". "<br/>";	


######## # Grade 1 #########

			       $rec2 = $ilance->db->query("SELECT coin_id FROM " . DB_PREFIX . "coins 
				                              where 
				                              Grade = '".$grade_coins."' 
				                              AND Grading_Service = '".$service_coins."'
											  AND pcgs = '".$pcgs_coins."'
											  ");
											  
					if($ilance->db->num_rows($rec2))	
					{					  
											  
					   while($rec_fet2 = $ilance->db->fetch_array($rec2))
			             { 
						 
						
					     $rec3 = $ilance->db->query("SELECT project_id, project_title FROM " . DB_PREFIX . "projects 
						                             where 
													 project_id = '".$rec_fet2['coin_id']."'
													 and status = 'open'
													 ");
													 
								$rec_fet3 = $ilance->db->fetch_array($rec3);
								
				
				            $messagebody.= '<b><a href="Coin/'.$rec_fet3['project_id'].'/'.construct_seo_url_name($rec_fet3['project_title']).'">'.$rec_fet3['project_title'].'</a></b>'. "<br/>";
				
						}				 
					}	
			 									 
				
	######## # Grade 2 #########	 
		

			       $rec_grad21 = $ilance->db->query("SELECT coin_id FROM " . DB_PREFIX . "coins 
				                              where 
				                              Grade = '".$grade_coins ."' 
				                              AND Grading_Service != '".$service_coins."'
											  AND pcgs = '".$pcgs_coins."'
											  ");
				 if($ilance->db->num_rows($rec_grad21))	
					{							  
											  
					   while($rec_fet2 = $ilance->db->fetch_array($rec_grad21))
			             {	
						
				$rec_grad23 = $ilance->db->query("SELECT project_id, project_title FROM " . DB_PREFIX . "projects 
						                             where 
													 project_id = '".$rec_fet2['coin_id']."'
													 and status = 'open'
													 ");
													 
								$rec_fet6 = $ilance->db->fetch_array($rec_grad23);
								
								    $messagebody.= '<b><a href="Coin/'.$rec_fet6['project_id'].'/'.construct_seo_url_name($rec_fet6['project_title']).'">'.$rec_fet6['project_title'].'</a></b>'. "<br/>";
									  
		                 } 
		       }
		
		 
		
		 
		 ########grade3 ###########

/*			  $coin_series_unique = fetch_cat('coin_series_unique_no',$pcgs_coins);
			
			  $rec_gra31 = $ilance->db->query("SELECT PCGS FROM " . DB_PREFIX . "catalog_coin 			                       
			                                   where coin_series_unique_no = '".$coin_series_unique."'
											   	"); 
					if($ilance->db->num_rows($rec_gra31))	
					{							
				   while($rec_fet_series = $ilance->db->fetch_array($rec_gra31))
				      {
					 
					    $rec_gra32 = $ilance->db->query("SELECT p.project_title,p.project_id FROM 
						                                               " . DB_PREFIX . "coins c,
						                            	              " . DB_PREFIX . "projects p		                       
			                                                          where c.pcgs = '".$rec_fet_series['PCGS']."'
											                          AND  c.Grade = '".$grade_coins."'
																	  AND p.status = 'open'
																	  AND p.cid = c.pcgs
											                   	"); 
																$rec_fet7 = $ilance->db->fetch_array($rec_gra32);   
							   $messagebody.= '<b><a href="Coin/'.$rec_fet7['project_id'].'/'.construct_seo_url_name($rec_fet7['project_title']).'">'.$rec_fet7['project_title'].'</a></b>'. "<br/>";				
												
					     
					  }																		   
		    }*/
		
		 	

		
		############### grade 4 #########
		
		
		
	 /* $coin_series_denomination_no = fetch_cat('coin_series_denomination_no',$pcgs_coins);
			  
			  $rec_gra44 = $ilance->db->query("SELECT cc.PCGS FROM " . DB_PREFIX . "catalog_coin cc,
			                                                    " . DB_PREFIX . "catalog_toplevel ct			                       
			                                   where cc.coin_series_denomination_no = ct.denomination_unique_no
											   	"); 
					if($ilance->db->num_rows($rec_gra44))	
					{						
				   while($rec_fet_ser = $ilance->db->fetch_array($rec_gra44))
				      {
					 
					    $rec_gra35 = $ilance->db->query("SELECT p.project_title,p.project_id FROM 
						                                               " . DB_PREFIX . "coins c,
						                            	              " . DB_PREFIX . "projects p		                       
			                                                          where c.pcgs = '".$rec_fet_ser['PCGS']."'
											                          AND  c.Grade = '".$grade_coins."'
																	  AND p.status = 'open'
																	  AND p.cid = c.pcgs
											                   	"); 
																
								$rec_fet9 = $ilance->db->fetch_array($rec_gra35);  
								
																   
							   $messagebody.= '<b><a href="Coin/'.$rec_fet9['project_id'].'/'.construct_seo_url_name($rec_fet9['project_title']).'">'.$rec_fet9['project_title'].'</a></b>'. "<br/>";				
												
					     
					  }	
					  }*/
					  																   
		echo $messagebody;  
																											

																											



