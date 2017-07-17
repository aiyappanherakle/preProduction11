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


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{	
	//send mail
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'send_email')
    {
    	if (isset($ilance->GPC['end_Date']) AND $ilance->GPC['end_Date'] != '')
    	{
    		$date_parts=explode("-", $ilance->GPC['end_Date']);
    		$end_DATE = $date_parts[2].'-'.$date_parts[0].'-'.$date_parts[1];
    		

    		$flag=0;
	 		$notify = $ilance->db->query("SELECT user_id,username,email FROM " . DB_PREFIX . "users
	 										WHERE notifyauction = '1' ");		
			
			if ($ilance->db->num_rows($notify)>0)
			{
	            $messagebody .= "*********************************"."\n";
				$messagebody .= "Notify Win Auction Details - ".date('d F, Y', strtotime($end_DATE))."\n";
				$messagebody .= "*********************************"."\n";	

	        	while($result = $ilance->db->fetch_array($notify))
	          	{
					$username = $result['username'];
					$email =$result['email'];
					$userid = $result['user_id'];

			 		$notify_user = $ilance->db->query("SELECT sum(currentprice) as crt,count(project_id) as prt FROM " . DB_PREFIX . "projects
	 							WHERE haswinner = '1' AND winner_user_id = '".$result['user_id']."' AND date(date_end) = '".$end_DATE."'");
									
					if($ilance->db->num_rows($notify_user) > 0)
					{
		  				$result_sum = $ilance->db->fetch_array($notify_user);	
						$flag+=$result_sum['prt'];
							
						$total = $result_sum['crt'];
						$itemcount= $result_sum['prt'];
							
						if($result_sum['prt'] > 0)
						{
							$messagebody .= "Username :". $username. "\n";
							$messagebody .= "Email :". $email. "\n";
							$messagebody .= "Total No of Item Count :" . $itemcount. "\n";
							$messagebody .= "Total Amounts :" . ": " . $total . "\n";
							$messagebody .= "*********************************"."\n";
						}	
					}
				} 

				if($flag > 0)
			   	{	
	           		$ilance->email = construct_dm_object('email', $ilance);
	                
					$ilance->email->mail = $ilconfig['globalserversettings_siteemail'];	 
					$ilance->email->get('notifywin_auction');
					
						$ilance->email->set(array(
                                        '{{message}}' => $messagebody,
                                       
                                ));
						
					$ilance->email->send();
					
				
					$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];	
					$ilance->email->get('notifywin_auction');
					
						$ilance->email->set(array(
                                        '{{message}}' => $messagebody,
                                       
                                ));
						
					$ilance->email->send();

					$ilance->email->mail = 'nataraj@herakle.com';	
					$ilance->email->get('notifywin_auction');
					
						$ilance->email->set(array(
                                        '{{message}}' => $messagebody,
                                       
                                ));
						
					$ilance->email->send();
				}	
				
				unset($messagebody);
				print_action_success("Auction Notifiy E-mail sent Successfully", "notify_win.php");				
				exit();

			}
			
    	}	

	} 	
						   
	  


					
										
	$pprint_array = array('numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'notify_win.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
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