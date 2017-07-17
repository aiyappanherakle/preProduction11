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
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 

	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'payment_generate')
	{

		$fields = array(
					array('uniqueid', 'RefNumber'),
					array('paiddate', 'TxnDate'),
					array('last_name', 'Customer'),
					array('payment_method', 'PaymentMethod'),
					array('deposit', 'DepositToAccount'),
					array('invoiceid', 'InvoiceApplyTo'),
					array('paid', 'Amount')
					
					
					
				);
				
		foreach ($fields AS $column)
		{			
			$fieldsToGenerate[] = $column[0];
			$headings[] = $column[1];				
		}
		
		$i=0;
	 
  
	 $query3="SELECT invoiceid  
					FROM " . DB_PREFIX . "partial_payment 
					WHERE DATE(paymentdate) between  '".$ilance->GPC['from_payment']."' AND '".$ilance->GPC['to_payment']."'
					group by invoiceid";
					
			$result3=$ilance->db->query($query3);
			$partial_invoices=array();
  
			if($ilance->db->num_rows($result3))
			{
				while($line3=$ilance->db->fetch_array($result3))
				{
					$partial_invoices[]=$line3['invoiceid'];
				}
			}
			
			$partial_part='';
			$order_by="ORDER BY u.first_name";
			
			if(count($partial_invoices)>0)
			{
				$partial_part="  or (i.invoiceid in (".implode(",",$partial_invoices).") )";
			
			}
			
			$order_by="ORDER BY final_paymethod,u.first_name";
		 
  
			
			 $query1="SELECT i.paiddate,i.invoiceid,i.paid,i.paymethod,u.first_name,u.last_name,u.user_id,p.paymentdate,p.paymethod as partial_paymethod,p.partial_amount,case when p.id>0 and i.status!='paid' then p.paymethod else i.paymethod end as final_paymethod   
			FROM " . DB_PREFIX . "invoices i
			left join " . DB_PREFIX . "users u on i.user_id=u.user_id
			left join (select * from " . DB_PREFIX . "partial_payment group by invoiceid) p on p.invoiceid=i.invoiceid
			WHERE DATE(i.paiddate) 
			between  '".$ilance->GPC['from_payment']."' AND '".$ilance->GPC['to_payment']."'  
			and i.combine_project!=''".$partial_part.' '.$order_by;

			$result1=$ilance->db->query($query1);
			
			if($ilance->db->num_rows($result1))
			{
				$total=0;
				while($line1=$ilance->db->fetch_array($result1))
				{
				
				if($line1['final_paymethod']=='others')
								{
									$deposit = 1060;
								}
								else
								{
									$deposit = 1040;
								}
				$res['uniqueid'] = $line1['invoiceid'];
						$res['paid_date'] = date("d-M-Y",strtotime($line1['paiddate']));
					$res['descripton'] 	= $line1['first_name'].' '.$line1['last_name'].' ['.$line1['user_id'].']';
						$res['Method']	= $line1['final_paymethod'];
						$res['deposit']	= $deposit;
						$res['Reference'] 	= $line1['invoiceid'];
						$res['paid'] 		= $line1['paid'];
						//$total+=$res['paid'];
						$data[]=$res;
						
						
					 
				}
				
				 
			}
	  
		 
	 	    
		 
				$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
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
	