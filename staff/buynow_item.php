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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	
	$search_buynow_text='';	
	
	if(isset($ilance->GPC['buynow_item']) AND $ilance->GPC['buynow_item']=='buynow_item')
	{
		if(is_numeric($ilance->GPC['search_buynow']))
		{
			$buynow_sql = $ilance->db->query("SELECT project_id	FROM " . DB_PREFIX . "projects  
												where project_id = '".$ilance->GPC['search_buynow']."'
												and filtered_auctiontype='fixed'
											    ");
			if($ilance->db->num_rows($buynow_sql) > 0)
			{			
				$buynow_res = $ilance->db->fetch_array($buynow_sql);
				
				$search_buynow_text = $ilance->GPC['search_buynow'];
				
				$show['buynow_results']='show_history';				
				
				
				$buynow_sql_1 = $ilance->db->query("SELECT 	b.qty, b.buyer_id,
													DATE_FORMAT( b.orderdate, '%m-%d-%Y  %l:%i:%s %p' ) as orderdate,
													i.invoiceid, i.totalamount, i.paid, i.status,
													concat(u.first_name,' ',u.last_name) as u_name,
													DATE_FORMAT( s.shipment_date, '%m-%d-%Y' ) as shipdate
													
													FROM " . DB_PREFIX . "buynow_orders  b
													
													left join " . DB_PREFIX . "invoices i on b.project_id=i.projectid and b.buyer_id = i.user_id
													
													left join " . DB_PREFIX . "users u on b.buyer_id = u.user_id
													
													left join " . DB_PREFIX . "shippnig_details s on s.item_id='".$buynow_res['project_id']."'
													and s.track_no !=''
													and s.buyer_id = b.buyer_id 
													
													where b.project_id = '".$buynow_res['project_id']."'
													
													group by b.orderid,b.orderdate
													order by b.orderdate desc
												");
												
				while($buynow_res_1 = $ilance->db->fetch_array($buynow_sql_1))
				{
					$buynow_arr['invoice_id'] = $buynow_res_1['invoiceid'];
					$buynow_arr['Bought_Date'] = $buynow_res_1['orderdate'];
					$buynow_arr['Quantity_Bought'] = $buynow_res_1['qty'];
					$buynow_arr['Customer_Name'] = $buynow_res_1['u_name'];
					$buynow_arr['Invoice_Total'] = $buynow_res_1['totalamount'];
					$buynow_arr['Invoice_Paid'] = $buynow_res_1['status'];
					$buynow_arr['Shipped_Date'] = !empty($buynow_res_1['shipdate']) ? $buynow_res_1['shipdate']:'--';				
					$buynow_list[]=$buynow_arr;
				}
				
			}
			else
			{
				print_action_failed('Please enter valid Item number', 'buynow_item.php');
				exit;
			}
		}
	}
	
	$pprint_array = array('search_buynow_text','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'buynow_item.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('buynow_list'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}




?>