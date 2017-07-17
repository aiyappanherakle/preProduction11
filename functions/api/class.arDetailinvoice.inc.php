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
class arDetailinvoice
{
	function ar_detail_invoice(){
	
		global $ilance;
		$first_sql="SELECT u.last_name,u.first_name,u.user_id,i.invoiceid,i.scheduledamount,i.miscamount FROM ilance_users u
		left join (select user_id,invoiceid,amount as scheduledamount ,miscamount from ilance_invoices where status='scheduled' group by invoiceid) i on u.user_id=i.user_id where i.scheduledamount is not null 
		group by i.invoiceid  ORDER BY  u.last_name ASC";

		$sql = $ilance->db->query($first_sql);
		
		$headings=array('LAST NAME','FIRST NAME','USER_ID','INVOICEID','AMOUNT OWING');

		if($ilance->db->num_rows($sql) > 0)
		{
			while($res = $ilance->db->fetch_array($sql))
			{					
				$row[0]=$res['last_name'];
				$row[1]=$res['first_name'];
				$row[2]=$res['user_id'];
				$row[3]=$res['invoiceid'];
				$row[4]=$ilance->currency->format_no_text($res['scheduledamount']+$res['miscamount']);
				$data[]=$row;
			}
			$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
			
		}	

		else
		{
			$reportoutput='';
		}		
		
		return $reportoutput;
		
	}

}