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
error_reporting(E_ALL);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'unpaid_csv')
	{
		$from_date=$ilance->GPC['from_date'];
		$to_date=$ilance->GPC['to_date'];
		$headings=array('Invoice ','Created Date','Paid Date','Sub Total','Buyers Fee','Shipping','Sales Tax','Discount','Total Order','Name','Address','City','State','Zip Code');
		
		$query="SELECT i.invoiceid,i.user_id,i.amount,i.combine_project,u.username,u.state,i.paiddate,i.taxamount,i.totalamount,i.createdate,u.first_name,u.last_name,u.address,u.address2,u.city,u.state,u.zip_code  FROM ilance_invoices i left join ilance_users u on u.user_id=i.user_id   WHERE combine_project != '' and u.state='California' and i.paiddate between '".$from_date."' and '".$to_date."' group by i.invoiceid ORDER BY i.invoiceid  DESC";
		
		$sql = $ilance->db->query($query);
		while($res = $ilance->db->fetch_array($sql))
		{
			$sales_detail['invoiceid']=$res['invoiceid'];
			$sales_detail['createdate']=$res['createdate'];
			$sales_detail['paiddate']=$res['paiddate'];
			$sql1="SELECT sum(i.amount) as subtotal,sum(f.amount) as buyerfee  FROM " . DB_PREFIX . "invoices i
			left join " . DB_PREFIX . "invoices f on f.projectid=i.projectid and f.user_id=i.user_id and f.isbuyerfee=1
			WHERE  i.invoiceid in (".$res['combine_project'].")";
			$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($res1)>0)
			{
				while($line1=$ilance->db->fetch_array($res1))
				{
					$sales_detail['subtotal']= $line1['subtotal'];
					$sales_detail['buyerfee']=$line1['buyerfee'];
				}
			}
					if($sales_detail['subtotal']==NULL)
					{
					continue;
					}
			//$sales_detail['subtotal']=0;
			$query2 = $ilance->db->query("
										SELECT i.disount_val,i.shipping_cost,sum(p.amount-p.taxamount) as buyerfee FROM ".DB_PREFIX."invoice_projects i
										left join ".DB_PREFIX."invoices p on p.projectid=i.project_id and p.user_id='".$res['user_id']."' and p.isbuyerfee=1
										where i.final_invoice_id ='".$res['invoiceid']."' order by shipping_cost desc limit 1
										");
						$row2 = $ilance->db->fetch_array($query2);
						
						$sales_detail['shipping'] = $row2['shipping_cost'];
						
						$sales_detail['salestax']=$res['taxamount'];
						$sales_detail['Discount'] = $row2['disount_val'];
			$sales_detail['total_order']=$res['totalamount'];
			//$sales_detail['county']=$res['paiddate'];
			
			$sales_detail['name']=$res['first_name']." ".$res['last_name'];
			$sales_detail['address']=$res['address'].", ".$res['address2'];
			$sales_detail['city']=$res['city'];
			$sales_detail['state']=$res['state'];
			$sales_detail['zip']=$res['zip_code'];
			
			
			$data[]=$sales_detail;
		}
		
	$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "california_sales_datails-$timeStamp";
	$action = 'csv';
		if ($action == 'csv')
		{
			header("Pragma: cache");
			header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
			header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
			echo $reportoutput;
			die();
		}

		exit();
	}
	else
	{
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','remote_addr','rid','referfrom','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
         ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'user_report.html', 2);
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