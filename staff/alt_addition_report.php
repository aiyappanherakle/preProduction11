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
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
  global $ilance;
			$userid1=15500;
			$userid2=22789;
			
           /* $sql="SELECT sum(Alternate_inventory_No) as main_account  FROM " . DB_PREFIX . "coins  
			WHERE user_id = '".$userid1."'"; */

			$sql_live = "SELECT sum(Alternate_inventory_No) as listed_amount
					FROM " . DB_PREFIX . "coins 
					WHERE user_id ='".$userid1."'													
					AND coin_id IN (SELECT project_id  FROM " . DB_PREFIX . "projects WHERE user_id = '".$userid1."' AND status = 'open') "; 

			$sql_pending = " SELECT sum(Alternate_inventory_No) as pending_amount
								FROM " . DB_PREFIX . "coins 
								WHERE user_id ='".$userid1."'												
								AND coin_listed = 'c'
								AND (End_Date = '0000-00-00' OR pending = '1')	
								AND project_id  = '0'
								AND status = '0' ";
										
			
			$listed_list = $ilance->db->query($sql_live);
			$listed_coins = $ilance->db->fetch_array($listed_list);	
			$listed_amount1 = $listed_coins['listed_amount'];

			$pending_list = $ilance->db->query($sql_pending);
			$pending_coins = $ilance->db->fetch_array($pending_list);	
			$pending_amount1 = $pending_coins['pending_amount'];

			//echo $listed_amount1.' == '.$pending_amount1;exit;
			

			$user1_amount = $listed_amount1+$pending_amount1;		
			$totamt = $ilance->currency->format($user1_amount,$ilconfig['globalserverlocale_defaultcurrency']);

			
			$sql_live1 = "SELECT sum(Alternate_inventory_No) as listed_amount
					FROM " . DB_PREFIX . "coins 
					WHERE user_id ='".$userid2."'													
					AND coin_id IN (SELECT project_id  FROM " . DB_PREFIX . "projects WHERE user_id = '".$userid2."' AND status = 'open') "; 

			$sql_pending1 = " SELECT sum(Alternate_inventory_No) as pending_amount
								FROM " . DB_PREFIX . "coins 
								WHERE user_id ='".$userid2."'													
								AND coin_listed = 'c'
								AND (End_Date = '0000-00-00' OR pending = '1')	
								AND project_id  = '0'
								AND status = '0' ";
										
			//echo $sql_pending1;exit;
			$listed_list1 = $ilance->db->query($sql_live1);
			$listed_coins1 = $ilance->db->fetch_array($listed_list1);	
			$listed_amount2 = $listed_coins1['listed_amount'];

			$pending_list1 = $ilance->db->query($sql_pending1);
			$pending_coins1 = $ilance->db->fetch_array($pending_list1);	
			$pending_amount2 = $pending_coins1['pending_amount'];

			if($listed_amount2=='')
				$listed_amount2 = 0;

			if($pending_amount2=='')
				$pending_amount2 = 0;
			
				
				
				
			

			$user2_amount = $listed_amount2+$pending_amount2;				
			$totamt1 = $ilance->currency->format($user2_amount,$ilconfig['globalserverlocale_defaultcurrency']);


			$listing_items ='<table border="0">
			<tr>
			<td size="20" family="helvetica" style="bold" nowrap><b>GreatCollections.com, LLC</b></td>
			</tr>
			<tr>
			<td valign="top" size="10" family="helvetica" >17500 Red Hill Avenue, Suite 160, Irvine, CA 92614<br>
			Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
			E-mail: info@greatcollections.com</td>
			<td >&nbsp;</td>
			<td >&nbsp;</td>

			Report Run Date : '.date("m.d.y h:i A").'</td>

			</tr>

			<tr>
			<td  ></td>
			</tr>
			<tr>
			<td family="helvetica" size="10">Main Account: '.$totamt.'
			</td>
			</tr>

			<tr>
			<td family="helvetica" size="10">World Account: '.$totamt1.'
			</td>
			</tr>
			</table>';
					
			$Alt_inventory_sum= $user1_amount + $user2_amount ;
			
			$ilance->currency->format($Alt_inventory_sum,$ilconfig['globalserverlocale_defaultcurrency']);
						
			$listing_items.= '<table><tr><td family="helvetica" size="10">Total: '.$ilance->currency->format($Alt_inventory_sum,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr></table>';	
		
			
			
			define('FPDF_FONTPATH','../font/');
			
			require('pdftable_1.9/lib/pdftable.inc.php');
			
			$p = new PDFTable();
			
			$p->AddPage();
			
			$p->setfont('times','',10);
			
			$p->htmltable($listing_items);
			
			$p->output('alt_addition_report_'.date('Y-m-d h-i-s').'.pdf','D');  
			
				
	}
	else
	{
		refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}



	
	/*======================================================================*\
	|| ####################################################################||
	|| # worked for bug id :#6057 - New Report (add to new report menu)    ||
	|| ####################################################################||
	\*======================================================================*/
	?>