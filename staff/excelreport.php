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

// #### setup default breadcrumb  ######### ######################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'unpaid_excel')
	{

	 $sql = $ilance->db->query("SELECT u.user_id,u.username,u.first_name,u.last_name,date(u.date_added) as date_added ,u.email,u.phone,SUM(i.amount) AS totamount  
							FROM " . DB_PREFIX . "users u, " . DB_PREFIX . "invoices i 
							WHERE u.user_id = i.user_id
							AND i.status = 'unpaid'
							AND i.combine_project = ''							
							GROUP BY i.user_id
							ORDER BY totamount desc
												
							");
							
							
										
							
	if($ilance->db->num_rows($sql) > 0)
       {
	   

			
	      while($res = $ilance->db->fetch_array($sql))
	      {
		  
		  		$test['user_id'] = $res['user_id'];
			$test['username']= $res['username'];
			$test['first_name']= $res['first_name'];
			$test['last_name']= $res['last_name'];
			$test['email']= $res['email'];
			$test['phone']= $res['phone'];
			$test['date_added']= $res['date_added'];
			//$test['date_added'].= $res['date_added'];
				
			
			
		  	$sqlnew = $ilance->db->query("SELECT SUM(i.amount) AS newamount  
								FROM  " . DB_PREFIX . "invoices i 								
								WHERE i.status = 'unpaid'
								AND i.combine_project = ''
								AND date(i.createdate) >= '".FIFETEENDAYSAGO."'
								AND i.user_id = '".$res['user_id']."'												
								");
								
		  					$resnew = $ilance->db->fetch_array($sqlnew);
							
				$sqlnew1 = $ilance->db->query("SELECT SUM(i.amount) AS oldamount  
								FROM  " . DB_PREFIX . "invoices i 								
								WHERE i.status = 'unpaid'
								AND i.combine_project = ''
								AND date(i.createdate) < '".FIFETEENDAYSAGO."'
								AND i.user_id = '".$res['user_id']."'												
								");
		  					$resnew1 = $ilance->db->fetch_array($sqlnew1);
						
							 //$test1[]= $ilance->currency->format($res['totamount']);
							$tot_amt = $tot_amt+$res['totamount'];
							if($resnew1['oldamount'] > 0)
							{
								$test['oldamount']= $resnew1['oldamount'];
								$oldamt_tot = $oldamt_tot+$resnew1['oldamount']; 
							}
							else
							{
								//$test['oldamount']= $ilance->currency->format(0);
							}
							
							if($resnew['newamount'] > 0)
							{
								$test['newamount']= $resnew['newamount'];
								
								$new_amt = $new_amt+$resnew['newamount'];
							}
							else
							{
								//$test['newamount']= $ilance->currency->format(0);
							}
							$test['amount'] = $res['totamount'];
							
							
							
							$ttt[]=$test;
				
							
						 	
					}
					
				}
				
						 $result = array('User ID','User Name','First Name','Last Name','Email','Phone','Date Joined','Oldest Date(Before '.FIFETEENDAYSAGO.')','Latest Date( AFTER '.FIFETEENDAYSAGO.')','Total Amount');
		
			    $i = 0;
				
				foreach($result as $valw) {
				
				
				$csv_output .= $valw."; ";
				$i++;
				}
				$csv_output .= "\n";
					//	echo '<pre>';
					//	print_r($ttt);	
					//	echo '</pre>';
						
					//	exit;
							
					//print_r($ttt);	
				foreach ($ttt as $sss) {
				
			    foreach ($sss as $rrr)
				{
				
				$csv_output .= $rrr."; ";
				
				}
				$csv_output .= "\n";
				}
						
					
						
				//$email = $user_id,$username,$first_name,$last_name,$email,$phone,$date_added,$oldamount,$newamount,$totamount;  			
   
            
   
               
  
  
 
             //$result = $table;

			
				
				
			
				
				$filename = "EmailExport_".date("Y-m-d_H-i",time());
				header("Content-type: application/vnd.ms-excel");
				header("Content-disposition: csv" . date("Y-m-d") . ".csv");
				header("Content-disposition: filename=".$filename.".csv");
				print $csv_output;
				die();

                exit();
				}
				
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}