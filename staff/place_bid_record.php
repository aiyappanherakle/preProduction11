<?php 
/* vijay */
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
require_once('./../functions/config.php');
error_reporting(e_all);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	

  			 
				$place_bid = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "place_bid_details
					  
                ");
				$number = (int)$ilance->db->num_rows($place_bid);
				
				
				  if($ilance->db->num_rows($place_bid)>0)
					{
                        while ($result = $ilance->db->fetch_array($place_bid, DB_ASSOC))
                        {
                              
								
								$result['Coin_id'] = $result['Coin_id'];
								$result['Amount'] = $result['Amount'];
								$result['bug_details'] = $result['bug_details'];
								$result['date_of_bug'] = $result['date_of_bug'];
								$result['bidplaced'] = $result['bidplaced'];
								$result['username'] = $result['username'];
								
								
                                $place_bidlist[] = $result;
                               
						}
					}
					
              
		
 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'place_bid' )
		 {
		   
			 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'place_bid_record')
			 {
			   
				$column_names = array('Coin id', 'Amount','bug details','date of bug','bidplaced','username');
				
				
				if((!empty($_FILES['upload'])) && ($_FILES['upload']['error'] == 0))
				{						
					if($_FILES['upload']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
					{				
						if($_FILES['upload']['size'] > 1000000)
						{
							print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
							exit();
						}
						else
						{
						
							$handle = fopen($_FILES['upload']['tmp_name'],'r');
							$row_count = 0;	
																	
							while (($data = fgetcsv($handle, 100000, ",")) !== FALSE)
			
							 { 							
							 	$row_count++;
								if ($row_count==1) continue;
							 	if(count($data) != count($column_names))
								{
								print_action_failed("We're sorry. CSV file is not correct. Number of columns in
 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'?module=lanceactivity');
							exit();
								}
								else
								{							    							
											$temp_data['Coin_id'] = $data[0];								
											$temp_data['Amount'] = $data[1];
											$temp_data['bug_details'] = $data[2];
											$temp_data['date_of_bug'] = $data[3];
											$temp_data['bidplaced'] = $data[4];
											$temp_data['username'] = $data[5];	
									
										
										 $ilance->db->query("
                                					INSERT INTO " . DB_PREFIX . "place_bid_details
                               						 (Coin_id, Amount, bug_details, date_of_bug, bidplaced, username)
                               						 VALUES (
                                				'" . $ilance->db->escape_string($temp_data['Coin_id']) . "',
                               					'" . $temp_data['Amount']. "',
                               					 '" . $temp_data['bug_details'] . "',
												'" . $temp_data['date_of_bug'] . "',
												'".$temp_data['bidplaced']."',									
								 				 '" .$temp_data['username']. "'
                                					)
                       						 ");
                        
										
									}
																								 						
							 }							
						  
						}							
					}
					
					else
					{
						print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
						exit();
					}	
					
					fclose($handle);
					print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF']);
								exit();									
				}			
				else 
				{
				   
					print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
					exit();							
				}
			
		}
}		
		
$pprint_array = array('place_bidlist','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'place_bid_record.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', array('place_bidlist'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
		
	print_action_success($html, 'place_bid_record.php', 'Change Variety flag for more coins');
	exit();
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  Tamil */


?>