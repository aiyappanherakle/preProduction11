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

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'live_item_list')
	{
		
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Live-items-PCGS-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		$fields = array('ITEM','URL','TITLE','PCGS','CERTIFICATION','ENDDATE');
		
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');		
		fputcsv($fp, $fields);		
		
		$sql = $ilance->db->query("SELECT p.project_id AS Item, CONCAT(  'http://www.greatcollections.com/Coin/', p.project_id ) AS URL, p.project_title AS TITLE, p.pcgs PCGS,c.Certification_No AS Certification, DATE_FORMAT( date_end,  '%m/%d/%Y' ) AS EndDate
									FROM  " . DB_PREFIX . "projects p
									join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
									WHERE  p.status =  'open'
									AND  p.Grading_Service =  'PCGS'
									ORDER BY p.date_end ASC ");
		while($res = $ilance->db->fetch_array($sql))
		{	
			$user_detail['ITEM']=trim($res['Item']);
			$user_detail['URL']=$res['URL'];
			$user_detail['TITLE']=$res['TITLE'];			
			$user_detail['PCGS']=$res['PCGS'];			
			$user_detail['CERTIFICATION']=$res['Certification'];
			$user_detail['ENDDATE']=$res['EndDate'];
			
			fputcsv($fp, $user_detail);
		}

		exit();
	}
	else
	{
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','remote_addr','rid','referfrom','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
         ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'live_items_list.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');	
	$ilance->template->pprint('main', $pprint_array);
	exit();
	}

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>