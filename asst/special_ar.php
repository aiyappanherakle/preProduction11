<?php
define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 
$first_sql="SELECT u.last_name,u.first_name,u.user_id,i.invoiceid,i.scheduledamount,i.miscamount FROM ilance_users u
left join (select user_id,invoiceid,amount as scheduledamount ,miscamount from ilance_invoices where status='scheduled' group by invoiceid) i on u.user_id=i.user_id where i.scheduledamount is not null 
group by i.invoiceid  ORDER BY  u.last_name ASC";
 
	 $sql = $ilance->db->query($first_sql);					
			
		$headings[0]='Last name';
		$headings[1]='First Name';
		$headings[2]='User_id';
		$headings[3]='Invoiceid';
		$headings[4]='Amount Owing';
		
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
				$timeStamp = date("Y-m-d-H-i-s");
				$fileName = "special_ar-$timeStamp";
				$action = 'csv';
					if ($action == 'csv')
					{
						header("Pragma: cache");
						header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
						header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
						echo $reportoutput;
						die();
					}
	  }	
	  
	  else
	  {
	  	echo '<tr><td>No Result Found</td></tr>';
	  }  
	
		 }
		 else
		 echo 'login	';?>