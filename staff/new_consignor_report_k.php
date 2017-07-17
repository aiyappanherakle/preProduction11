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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 

	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'consignor_sales_fees_report_generate')
	{
		$start_date = '2014-01-01';
		$end_date = $ilance->GPC['end_date'];
		
		$i=0;
		$new_user_list=array();
		
		$sel_new_user_query = "SELECT user_id
						   FROM " . DB_PREFIX . "users 
						   WHERE date(date_added) between  '".$start_date."' AND '".$ilance->GPC['end_date']."'
						   AND status = 'active'
						   ";
		echo $sel_new_user_query;
		exit;
		$res_new_user_query = $ilance->db->query($sel_new_user_query);
		if($ilance->db->num_rows($res_new_user_query))
		{
			while($line=$ilance->db->fetch_array($res_new_user_query))
			{
				echo "hai";
				echo $new_user_list[$i];
				$new_user_list[$i]=$line['user_id'];
				$i++;
			}
		}	

		echo "<pre>";
		print_r($new_user_list);
		exit;
		
	}
	
	 

		$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'new_consignor_report_k.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	
   

}


?>	
	