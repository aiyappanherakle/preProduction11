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
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 

	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'payment_generate')
	{

		 
		$query="SELECT paymethod  
				FROM " . DB_PREFIX . "invoices 
				where date(paiddate) between  '".$ilance->GPC['from_payment']."' AND '".$ilance->GPC['to_payment']."'  
				and combine_project!='' 
				group by paymethod
				order by CAST(paymethod AS CHAR) ";
				
		$result=$ilance->db->query($query);
		
		if($ilance->db->num_rows($result))
		{
			while($line=$ilance->db->fetch_array($result))
			{
				$paymethods[$i]=$line['paymethod'];
				$i++;
			}
		}
	   
		echo $query="select * from (
SELECT p.invoiceid as uniqueid ,date_format(paymentdate,'%m/%d/%Y') as paid_date,concat(u.first_name,' ',u.last_name, ' [',u.user_id,']') as customer, p.paymethod,
 p.invoiceid as Reference, p.partial_amount, 'partial' as tablename FROM ilance_partial_payment p
left join ilance_invoices ip on ip.invoiceid=p.invoiceid
left join ilance_users u on u.user_id=ip.user_id
where  date(paymentdate) between  '2014-09-01' AND '2014-09-30'

 
union distinct

SELECT i.invoiceid as uniqueid ,date_format(i.paiddate,'%m/%d/%Y') as paid_date,concat(ui.first_name,' ',ui.last_name, ' [',ui.user_id,']') as customer, i.paymethod,
 i.invoiceid as Reference, i.paid, 'full' as tablename  FROM ilance_invoices i
 left join ilance_users ui on ui.user_id=i.user_id where date(i.paiddate) between  '2014-09-01' AND '2014-09-30' and i.combine_project!='' 
 
 
) f order by paymethod,customer";
			
		$result=$ilance->db->query($query);
		
		if($ilance->db->num_rows($result))
		{
			while($line=$ilance->db->fetch_array($result))
			{
				
			}
		}
	 
		
			$reportoutput = $ilance->admincp->construct_csv_data_quickbooks($data, $headings);
				$timeStamp = date("Y-m-d-H-i-s");
				$date_formatted=date('mdY',strtotime($ilance->GPC['to_payment']));
				$fileName = $date_formatted."payment_export-$timeStamp";
				$action = 'csv';
					if ($action == 'csv')
					{
						header("Pragma: cache");
						header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
						header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
						echo $reportoutput;
						die();
					}
				
			exit;
		
		
				
		
	}
	
	 

		$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
		$ilance->template->fetch('main', 'payments_report.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	
   

}
 
?>	
	