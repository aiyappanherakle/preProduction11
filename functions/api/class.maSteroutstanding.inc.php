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
class maSteroutstanding
{
	function master_outstanding_invoice(){
	
		global $ilance;
		$first_sql="SELECT i.invoiceid,u.first_name,u.last_name,u.user_id,i.totalamount,i.paid,i.amount   FROM ". DB_PREFIX . "invoices i left join ". DB_PREFIX . "users u on u.user_id=i.user_id WHERE i.combine_project != '' and i.status='scheduled' order by i.amount desc";
		$res=0;
		$sql = $ilance->db->query($first_sql);					
		if($ilance->db->num_rows($sql) > 0)
		{
			while($res = $ilance->db->fetch_array($sql))
			{
			$data['invoiceid']=$res['invoiceid'];
			$data['first_name']=$res['first_name'];
			$data['last_name']=$res['last_name'];
			$data['user_id']=$res['user_id'];
			$data['totalamount']=$res['totalamount'];
			$data['paid']=$res['paid'];
			$data['amount']=$res['amount'];
			$result[]=$data;     
			}
		}

		$heading['InvoiceId']='InvoiceId';
		$heading['FirstName']='FirstName';
		$heading['LastName']='LastName';
		$heading['CustomerId']='CustomerId';
		$heading['Invoice $ Total']='Invoice $ Total';
		$heading['PaidAmount $']='PaidAmount $';
		$heading['Owing $']='Owing $';

		$reportoutput = $ilance->admincp->construct_csv_data($result, $heading);
		
		return $reportoutput;
		
	}

}