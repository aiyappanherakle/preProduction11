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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{ 

if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'payment_generate')
	{

$fields = array(
					array('paiddate', 'Date'),
					array('invoiceid', 'Invoice'),
					array('last_name', 'Last Name'),
					array('first_name', 'First Name'),
					array('paid', 'Amount Paid')
					
				);
				
				foreach ($fields AS $column)
				{			
					$fieldsToGenerate[] = $column[0];
					$headings[] = $column[1];				
				}
	
	  $query="SELECT paymethod  FROM " . DB_PREFIX . "invoices where date(paiddate) between  '".$ilance->GPC['from_payment']."' AND '".$ilance->GPC['to_payment']."'  and combine_project!='' group by paymethod";
	  $result=$ilance->db->query($query);
	  if($ilance->db->num_rows($result))
	  {
	  while($line=$ilance->db->fetch_array($result))
	  {
	  $query1="SELECT *  FROM " . DB_PREFIX . "invoices i
	  left join " . DB_PREFIX . "users u on i.user_id=u.user_id
	  WHERE i.paymethod = '".$line['paymethod']."' and  
	  DATE(i.paiddate) between  '".$ilance->GPC['from_payment']."' AND '".$ilance->GPC['to_payment']."'  and i.combine_project!='' ";
		$result1=$ilance->db->query($query1);
		if($ilance->db->num_rows($result1))
		{
		$total=0;
			while($line1=$ilance->db->fetch_array($result1))
			{
			$res['paiddate'] 	= $line1['paiddate'];
			$res['invoiceid'] 	= $line1['invoiceid'];
			$res['first_name'] 	= $line1['first_name'];
			$res['last_name'] 	= $line1['last_name'];
			$res['paid'] 		= $line1['paid'];
			$res['paymethod'] 	= $line1['paymethod'];
			$total+=$res['paid'] ;
			$data[]=$res;
			}
			$grouped['label']='Total paid by '.$line['paymethod'];
			$grouped['total']=$total;
			$data[]=$grouped;
		}
	  
	  }
	  }

  
	 	    
		 
				$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
				$timeStamp = date("Y-m-d-H-i-s");
				$fileName = "payment_export-$timeStamp";
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
$ilance->template->fetch('main', 'payments.html', 2);
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

?>	
	