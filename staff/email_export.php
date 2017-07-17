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
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'email_expotrt')
{

// echo "SELECT email
// FROM ilance_users_unregistered 
// WHERE DATE( date_attempt ) >=  '".$ilance->GPC['date']."' <br/><br/>";

// echo "SELECT email
// FROM ilance_users
// WHERE  DATE( date_added ) >=  '".$ilance->GPC['date']."' AND status = 'active' <br/><br/>";
	if(empty($ilance->GPC['date']))
	{
		print_action_failed("Please Check your End Date Field", '6425.php');
		exit();
	}
   	
   	if(!isset($ilance->GPC['search_by']) OR empty($ilance->GPC['search_by']))
	{
		print_action_failed("Please Check Export Staus Field", '6425.php');
		exit();
	}



        $email_list = '';
        $export_by = $ilance->GPC['search_by'];
		$sssw =   $ilance->db->query("SELECT email
		                              FROM " . DB_PREFIX . "users
		                              WHERE  DATE( date_added ) >=  '".$ilance->GPC['date']."' AND status = 'active' ");
   
		while ($uns= $ilance->db->fetch_array($sssw))
		{
			$email_user[] = $uns['email'];
		}
//echo count($email_user).'<br/>';
		
		if($ilance->GPC['search_by'] == 'registrants')
		{
			foreach ($email_user as $val) 
			{
				$email_list .= $val." ";
				$email_list .= "\n";
			}
		}
		else
		{
			$values = $ilance->db->query("SELECT email
                                      FROM " . DB_PREFIX . "users_unregistered 
                                      WHERE DATE( date_attempt ) >=  '".$ilance->GPC['date']."' GROUP BY email ");  
			while ($rr = $ilance->db->fetch_array($values))
			{
			    $email_unreg[] = $rr['email'];
			} 

//echo count($email_unreg).'<br/>';
			foreach ($email_unreg as $vals) 
			{
				if(!in_array($vals, $email_user))
				{
					$email_list .= $vals." ";
					$email_list .= "\n";
				}
				
			}
		}
 // echo $email_list;exit;
   
             //$email = array_merge($email_unreg,$email_user); 


   
             //$arrcou = array_unique($email);

 
             $result = array('Emails');

				$i = 0;
				
				foreach($result as $valw) {
				
				
				$csv_output .= $valw."; ";
				$i++;
				}
				$csv_output .= "\n";
				$csv_output .= $email_list;
				$filen = "EmailExport_".$export_by."_";
				$filename = $filen.date("Y-m-d_H-i",time());
				header("Content-type: application/vnd.ms-excel");
				header("Content-disposition: csv" . date("Y-m-d") . ".csv");
				header("Content-disposition: filename=".$filename.".csv");
				print $csv_output;
				die();

                exit();
}



$pprint_array = array('');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'email_export.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('invoicelist','buyerdetail'));
	$ilance->template->pprint('main', $pprint_array);
	exit();

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}	