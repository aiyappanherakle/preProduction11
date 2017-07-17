<?php
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 
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
                $fileName="master_outstanding_invoice_list".DATETIME24H;
                header("Pragma: cache");
                header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
                header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
                echo $reportoutput;
                die();
		 }
		 else
		 echo 'login	';?>