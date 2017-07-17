<?php
require_once('./../functions/config.php');
//delete this fiile
/*
SELECT i.totalamount,b.totalamount FROM ilance_invoices i
left join ilance_invoices b on i.projectid=b.projectid and i.user_id=b.user_id and i.invoiceid!=b.invoiceid and i.isbuyerfee=1
WHERE i.user_id=2775 and i.status='unpaid' 
*/
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 
$first_sql="SELECT u.user_id,u.username,u.first_name,u.last_name,DATE_FORMAT(date(u.date_added), '%m/%d/%Y') as user_date_added,u.email,u.phone,c.totalunpaid,i.scheduledamount,i.paratialamount,c.oldamount,c.newamount,
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
	   
	   if($ilance->db->num_rows($sql) > 0)
       {
	      while($res = $ilance->db->fetch_array($sql))
	      {					$resnew1 = $ilance->db->fetch_array($sqlnew1);
		                    $arrayoldamount[] = $res['oldamount'];
							$arraynewamount[] = $res['newamount'];
							$arrayinvoiceid[] = $res['invoiceid'];
							$arrayscheduled_amount[]=$res['scheduledamount'];
							$arraytotal[]=$res['totalunpaid']+$res['scheduledamount'];
							$totalunpaid = $ilance->currency->format_no_text($res['totalunpaid']+$res['scheduledamount']);
						  
						  $table .='<tr>
						   <td>'.$res['user_id'].'</td>
						   <td>'.$res['invoiceid'].'</td>
						   <td>'.'<strong>&nbsp;&nbsp;'. $res['first_name'].'&nbsp;'.$res['last_name'].'</strong>'.'</td>
						   <td>'.$res['user_date_added'].'</td>';
						   if($res['paratialamount'] >0)
						   {
							$table .= '<td>*'.$ilance->currency->format_no_text($res['scheduledamount']).'</td>';
						   }
						   else
						   {
							$table .= '<td>'.$ilance->currency->format_no_text($res['scheduledamount']).'</td>';
						   }
						   $table .= '<td>'.$ilance->currency->format_no_text($res['oldamount']).'</td>
						   <td>'.$ilance->currency->format_no_text($res['newamount']).'</td>';
						 
						  $table .= '<td>'.$totalunpaid.'</td>';
						   
						  						   
						  $table .= '</tr>';
			
		  }
		  $table .='<tr><td colspan ="3" align="right">Total Scheduled </td> 
					<td>'.$ilance->currency->format_no_text(array_sum($arrayscheduled_amount)).'</td>
					<td></td>
					<td> </td>
					<td> </td></tr>';
	   $table .='<tr><td colspan ="3" align="right">Total Unpaid </td> 
					<td> </td>
					<td>'.$ilance->currency->format_no_text(array_sum($arrayoldamount)).'</td>
					<td>'.$ilance->currency->format_no_text(array_sum($arraynewamount)).'</td>
					<td> </td></tr>';
	   $table .='<tr><td colspan ="3" align="right">Total Money Owed </td> 
					<td> </td>
					<td></td>
					<td> </td>
					<td>'.$ilance->currency->format_no_text(array_sum($arraytotal)).'</td></tr>';
	  }	
	  
	  else
	  {
	  	$table .='<tr><td>No Result Found</td></tr>';
	  }  
	  
	$table .='</table>';
	echo $table;
		 }
		 else
		 echo 'login	';?>