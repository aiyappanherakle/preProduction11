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
class arExcelnew
{
	function ar_excel_new(){
		global $ilance;
		$first_sql="SELECT u.user_id,u.username,u.first_name,u.last_name,DATE_FORMAT(date(u.date_added), '%m/%d/%Y') as user_date_added,u.email,u.phone,c.totalunpaid,i.invoiceid,i.scheduledamount,i.paratialamount,c.oldamount,c.newamount,
		IFNULL(totalunpaid,0)+IFNULL(scheduledamount,0)  as sortorder FROM ". DB_PREFIX . "users u
		left join (select user_id,invoiceid,sum(amount) as scheduledamount ,sum(paid) as paratialamount from ". DB_PREFIX . "invoices where status='scheduled' group by invoiceid) i on u.user_id=i.user_id
		left join (
		select t.user_id,SUM(t.amount+IFNULL(b.totalamount,0)) AS totalunpaid,
		sum(CASE WHEN date(t.createdate) < '".FIFETEENDAYSAGO."' THEN t.amount+IFNULL(b.totalamount,0) ELSE 0 END) as oldamount,
		sum(CASE WHEN date(t.createdate) >= '".FIFETEENDAYSAGO."' THEN t.amount+IFNULL(b.totalamount,0) ELSE 0 END) as newamount 
		from ". DB_PREFIX . "invoices t
		left join ". DB_PREFIX . "invoices b on t.projectid=b.projectid and t.user_id=b.user_id and t.invoiceid!=b.invoiceid and b.isbuyerfee=1
		where t.status='unpaid' and t.combine_project = '' and t.isfvf=0 and t.isif=0 and t.isenhancementfee=0 group by t.user_id) c on u.user_id=c.user_id
		where i.scheduledamount is not null or c.oldamount is not null or c.newamount is not null
		group by u.user_id ORDER BY  sortorder  DESC";

		$sql = $ilance->db->query($first_sql);					

		$table = ' <table border=1>
		<tr><td size="20" family="helvetica" style="bold"  colspan="8">GC A/R ('.date('F d, Y',strtotime(DATETIME24H)).') CONFIDENTIAL</td></tr>
		<tr><td>User ID</td><td>Invoice id</td><td  width="50">User Name</td><td>Date Joined</td><td>Unpaid <br>Invoices</td><td>Oldest<br> Date<br>(Before<br> '.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')</td><td>Latest<br> Date<br>( AFTER <br>'.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')</td><td>Total Amount</td></tr>';				
$headings=array('User ID','Invoice id','User Name','Date Joined','Unpaid Invoices','Oldest Date(Before '.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')','Latest Date( AFTER '.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')','Total Amount');

		if($ilance->db->num_rows($sql) > 0)
		{
			while($res = $ilance->db->fetch_array($sql))
			{
				$resnew1 = $ilance->db->fetch_array($sqlnew1);
				$arrayoldamount[] = $res['oldamount'];
				$arraynewamount[] = $res['newamount'];
				$arrayinvoiceid[] = $res['invoiceid'];
				$arrayscheduled_amount[]=$res['scheduledamount'];
				$arraytotal[]=$res['totalunpaid']+$res['scheduledamount'];
				$totalunpaid = $ilance->currency->format_no_text($res['totalunpaid']+$res['scheduledamount']);
			
				$data_row['user_id']=$res['user_id'];
				$data_row['invoice_id']=$res['invoiceid'];
				$data_row['user_name']=$res['first_name'].' '.$res['last_name'];
				$data_row['user_date_added']=$res['user_date_added'];
				if($res['paratialamount'] >0)
				{					
					$data_row['unpaid_invoices']=$ilance->currency->format_no_text($res['scheduledamount']);
				}
				else
				{
					$data_row['unpaid_invoices']=$ilance->currency->format_no_text($res['scheduledamount']);			
				}
				
				$data_row['old_date_amount']=$ilance->currency->format_no_text($res['oldamount']);
				$data_row['new_date_amount']=$ilance->currency->format_no_text($res['newamount']);
				$data_row['total_unpaid']=$totalunpaid;				
				$data[]=$data_row;
			}
			unset($data_row);
			$data_row['user_id']=$data_row['invoice_id']=' ';
			$data_row['user_name']='Total Scheduled ';
			$data_row['user_date_added']=$ilance->currency->format_no_text(array_sum($arrayscheduled_amount));
			$data_row['unpaid_invoices']=$data_row['old_date_amount']=$data_row['new_date_amount']=$data_row['total_unpaid']=' ';
			$data[]=$data_row;
			unset($data_row);
			
			$data_row['user_id']=$data_row['invoice_id']=' ';
			$data_row['user_name']='Total Unpaid ';
			$data_row['user_date_added']=' ';
			$data_row['unpaid_invoices']=$ilance->currency->format_no_text(array_sum($arrayoldamount));
			$data_row['old_date_amount']=$ilance->currency->format_no_text(array_sum($arraynewamount));
			$data_row['new_date_amount']=$data_row['total_unpaid']=' ';
			$data[]=$data_row;
			unset($data_row);
			
			$data_row['user_id']=$data_row['invoice_id']=' ';
			$data_row['user_name']='Total Money Owed ';
			$data_row['user_date_added']=$data_row['unpaid_invoices']=$data_row['old_date_amount']=$data_row['new_date_amount']=' ';
			$data_row['total_unpaid']=$ilance->currency->format_no_text(array_sum($arraytotal));
			
			$data[]=$data_row;
			
			$ilance->admincp = construct_object('api.admincp');
			$reportoutput= $ilance->admincp->construct_csv_data($data, $headings);
			
		}	

		else
		{
			$reportoutput='';
		}	
		
		
		return $reportoutput;		
		
	}

}