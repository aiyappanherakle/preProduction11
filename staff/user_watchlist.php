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

   if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'user_watchlist')
   {
 
	      $value=$ilance->GPC['filterby'];
	
			$watch_view = $ilance->db->query("
			SELECT  *
			FROM 
			" . DB_PREFIX . "users 			
			WHERE $value like '".$ilance->GPC['filtervalue']."'
			");

             $table .= '<table border=1>';	
			
			if($ilance->db->num_rows($watch_view) > 0)
			{
			
				$row_view = $ilance->db->fetch_array($watch_view);
				
				$row_view['user_id'];
				$row_view['username'];

				$table .= '<tr><td size="15" family="helvetica" style="bold"  colspan="4">Watchlist report of Username - '.$row_view['username'].'  ('.date('F d, Y',strtotime(DATETIME24H)).')</td></tr>
		      <tr><td width="45px" size="10">coin id</td> 
			  <td width="45px" size="10">Title</td>
			  <td width="45px" size="10">End date</td>
			  </tr>';
				
				
				$watchlist_view = $ilance->db->query("
													SELECT u.user_id, p.project_id, DATE_FORMAT(p.date_end, '%m/%d/%Y') as end_date, p.project_title, w.watchlistid, w.user_id
													FROM " . DB_PREFIX . "watchlist w," . DB_PREFIX . "users u, " . DB_PREFIX . "projects p
												    WHERE u.user_id =  '".$row_view['user_id']."'
													AND u.user_id = w.user_id
													AND w.watching_project_id = p.project_id
													AND p.status='open'
													ORDER BY  date(p.date_end) ASC,p.project_id ASC 
			                                         ");

			
							if($ilance->db->num_rows($watchlist_view) > 0)
							{
								while($row_view_list = $ilance->db->fetch_array($watchlist_view))
								{
								
								 $table .='<tr>
								   <td>'.$row_view_list['project_id'].'</td>
								   <td>'.$row_view_list['project_title'].'</td>
								   <td>'.$row_view_list['end_date'].'</td>
								  </tr>';
								   
								}
							}

			  }
				else
			   {
			
				$table .='<tr><td size="15" family="helvetica" style="bold"  colspan="4">Watchlist report ('.date('F d, Y',strtotime(DATETIME24H)).')</td></tr>
		      <tr><td width="45px" size="10">End date</td><td width="45px" size="10">coin id</td>
			  <td width="45px" size="10">Title</td></tr>
			  <tr><td colspan="4" size="15">No Items Found in the watchlist</td></tr>';
			   }  	
			
			
			$table .='</table>';
		    $timeStamp = date("Y-m-d-H-i-s");
		 		$fileName = "watchlist_user-$timeStamp";
		            define('FPDF_FONTPATH','../font/');
					
					require('pdftable_1.9/lib/pdftable.inc.php');
					
					$p = new PDFTable();
					
					$p->AddPage();
					
					$p->setfont('times','',8);
					
					$p->htmltable($table);
					
					$p->output($fileName.'.pdf','D');  
	
	
 }	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','https_server','http_server','lanceads_header','lanceads_footer');
        
       ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'user_watchlist.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('reports','statement'));
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