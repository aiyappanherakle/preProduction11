<?php
require_once('./../functions/config.php');
//delete this fiile
/*
SELECT i.totalamount,b.totalamount FROM ilance_invoices i
left join ilance_invoices b on i.projectid=b.projectid and i.user_id=b.user_id and i.invoiceid!=b.invoiceid and i.isbuyerfee=1
WHERE i.user_id=2775 and i.status='unpaid' 
*/
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{ 
 $first_sql="SELECT 
u.user_id,
u.username,
u.first_name,
u.last_name,
DATE_FORMAT(date(u.date_added), '%m/%d/%Y') as user_date_added,
u.email,
u.phone,
u.client_representative,
c.totalunpaid,
i.scheduledamount,
i.paratialamount,
c.oldamount,
c.newamount,
IFNULL(totalunpaid,0)+IFNULL(scheduledamount,0)  as sortorder, 
sum(miscamount) as misctotal 
FROM ". DB_PREFIX . "users u
left join (
	select 
	user_id,
	sum(amount) as scheduledamount ,
	sum(paid) as paratialamount 
	from ". DB_PREFIX . "invoices 
	where status='scheduled' group by user_id
) i on u.user_id=i.user_id
left join (
	select 
	t.user_id,
	SUM(t.amount+IFNULL(b.totalamount,0)) AS totalunpaid,
	t.invoiceid,
	sum(CASE WHEN date(t.createdate) < '".FIFETEENDAYSAGO."' THEN t.amount+IFNULL(b.totalamount,0) ELSE 0 END) as oldamount,
	sum(CASE WHEN date(t.createdate) >= '".FIFETEENDAYSAGO."' THEN t.amount+IFNULL(b.totalamount,0) ELSE 0 END) as newamount 
	from ". DB_PREFIX . "invoices t
	left join 
		". DB_PREFIX . "invoices b on 
		t.projectid=b.projectid 
		and t.user_id=b.user_id 
		and t.invoiceid!=b.invoiceid 
		and b.isbuyerfee=1
	where t.status='unpaid' 
	and t.combine_project = '' 
	and t.isfvf=0 
	and t.isif=0 
	and t.isenhancementfee=0 group by t.user_id
) c on u.user_id=c.user_id
left join  
". DB_PREFIX . "invoices  m on 
u.user_id=m.user_id and m.status!='paid'
where i.scheduledamount is not null or c.oldamount is not null or c.newamount is not null
group by u.user_id ORDER BY  sortorder desc ";

								// echo $first_sql;
// exit;


	 $sql = $ilance->db->query($first_sql);					

		
		$table = ' <table style="font-size:15px;">
		<tr><td size="20" family="helvetica" style="bold" >GC A/R ('.date('F d, Y',strtotime(DATETIME24H)).') CONFIDENTIAL</td></tr></table>
		 <table border=1 style="font-size:15px;">
		<tr>
		<td style="bold">User ID</td style="bold">
		<td style="bold">User Name<br>&nbsp;&nbsp;First Name&nbsp;Last Name<br>Email</td>
		<td style="bold"  width="5">Phone</td>
		<td style="bold">Date Joined</td>
		<td style="bold">Unpaid <br>Invoices</td>
		<td style="bold">Oldest<br> Date<br>(Before<br> '.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')</td>
		<td style="bold">Latest<br> Date<br>( AFTER <br>'.date('m/d/Y',strtotime(FIFETEENDAYSAGO)).')</td>
		<td style="bold">Total Amount</td><td style="bold">Client rep</td>
		<td style="bold">Last InfoNote</td><td style="bold">F/U Date</td></tr>';				
	   
	   if($ilance->db->num_rows($sql) > 0)
       {
	      while($res = $ilance->db->fetch_array($sql))
	      {
             if(($res['client_representative'] == 'ian')|| ($res['client_representative'] == 'Ian'))
             {	 
              $user_note =$ilance->db->query("select * FROM ". DB_PREFIX . "users_note where user_id = '".$res['user_id']."' 
			  ORDER BY  note_id DESC limit 1 ");
				$follow_up_date='';
				 $note='';
				 $client_rep='';
            while($note1 = $ilance->db->fetch_array($user_note))
	          {
                 $note=$note1['note'];
                 $follow_up_date= $note1['follow_up_date'];
              }
		  $resnew1 = $ilance->db->fetch_array($sqlnew1);
		                    $arrayoldamount[] = $res['oldamount'];
							$arraynewamount[] = $res['newamount'];
							$arrayscheduled_amount[]=$res['scheduledamount']+$res['misctotal'];
							$arraytotal[]=$res['totalunpaid']+$res['scheduledamount']+$res['misctotal'];
							$totalunpaid = $ilance->currency->format_no_text($res['totalunpaid']+$res['scheduledamount']+$res['misctotal']);
							
							$client_rep =$res['client_representative'];
				  
				  
				  if($res['paratialamount'] >0)
				   {
					$un_amount = $res['scheduledamount']+$res['misctotal'];
					$un_table = '<td style="bold"  width="12">*'.$ilance->currency->format_no_text($un_amount).'</td>';
				   }
				   else
				   {
					$un_amount = $res['scheduledamount']+$res['misctotal'];
					$un_table = '<td style="bold"  width="12">'.$ilance->currency->format_no_text($un_amount).'</td>';
				   }
				  
				  if($un_amount>0 || $res['oldamount'] >0 || $res['newamount'] > 0 || $res['totalunpaid']>0 )
				  {
				  $table .='<tr>
				   <td style="bold">'.$res['user_id'].'</td>
				   <td style="bold">'.strtolower($res['username']).'<br><strong>&nbsp;&nbsp;'. $res['first_name'].'&nbsp;'.$res['last_name'].'</strong><br>'.strtolower($res['email']).'</td>
				   <td style="bold" width="5">'.$res['phone'].'</td>
				   <td style="bold"  width="12">'.$res['user_date_added'].'</td>';
				   
				   $table .= $un_table;
						   $table .= '<td style="bold"  width="12">'.$ilance->currency->format_no_text($res['oldamount']).'</td>
						   <td style="bold"  width="12">'.$ilance->currency->format_no_text($res['newamount']).'</td>';
						 
						  $table .= '<td style="bold">'.$totalunpaid.'</td>				   
						  <td style="bold"> '.$client_rep.'</td>
				          <td style="bold" width="50">'.$note.'</td>
					     <td style="bold">'.$follow_up_date.'</td>';
						   
						  						   
						  //coins in pendinf invoice
						  $sql1="select 
								t.user_id,
								p.project_title,
								date_format(p.date_end,'%m/%d/%Y') as END_DATE,
								SUM(t.amount+IFNULL(b.totalamount,0)) AS totalunpaid,
								IFNULL(b.totalamount,0) as buyerfee,
								t.amount as itemamount,
								t.projectid
								from ". DB_PREFIX . "invoices t
								left join 
									". DB_PREFIX . "invoices b on 
									t.projectid=b.projectid 
									and t.user_id=b.user_id 
									and t.invoiceid!=b.invoiceid 
									and b.isbuyerfee=1
								left join ". DB_PREFIX . "projects p on p.project_id=t.projectid
								where t.status='unpaid' 
								and t.combine_project = '' 
								and t.isfvf=0 
								and t.isif=0 
								and t.isenhancementfee=0  and t.user_id='".$res['user_id']."' group by t.projectid";
								

								
						  $res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
						  if($ilance->db->num_rows($res1)>0)
						  {
						   
							  while($line1=$ilance->db->fetch_array($res1))
							  {
								 // echobgcolor="#CCCCCC" ;
								$table.='<tr bgcolor="#f1f1f1" height="10" border=0 size=7><td  border=0 >&nbsp;</td>
										<td border=0>'.$line1['projectid'].'</td>
										<td  border=0 colspan="6" >'.$line1['project_title'].'</td>
										<td border=0>'.$line1['END_DATE'].'</td>
										<td border=0>'.$line1['itemamount'].'&nbsp;&nbsp;<font color="#CC3366">'.$line1['buyerfee'].'</font>&nbsp;&nbsp;'.$line1['totalunpaid'].'</td>										
										</tr>';
								
							  }
							   
						  }
				}
			}			  
			     
			
               			
				 
				 
		  }
		  $table .='<tr><td colspan ="4" align="right">Total Scheduled </td> 
					<td >'.$ilance->currency->format_no_text(array_sum($arrayscheduled_amount)).'</td>
					<td></td>
					<td> </td>
					<td> </td></tr>';
	   $table .='<tr><td colspan ="4" align="right">Total Unpaid </td> 
					<td> </td>
					<td>'.$ilance->currency->format_no_text(array_sum($arrayoldamount)).'</td>
					<td>'.$ilance->currency->format_no_text(array_sum($arraynewamount)).'</td>
					<td> </td></tr>';
	   $table .='<tr><td colspan ="4" align="right">Total Money Owed </td> 
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
		  $timeStamp = date("Y-m-d-H-i-s");
				$fileName = "unpaidreports-$timeStamp";
		            define('FPDF_FONTPATH','../font/');
					
					require('pdftable_1.9/lib/pdftable.inc.php');
					
					$p = new PDFTable("landscape");
					
					$p->AddPage();
					
					$p->setfont('times','',8);
					
					$p->htmltable($table);
					
					$p->output($fileName.'.pdf','D');  
		 }
		 else
		 echo 'login	';?>