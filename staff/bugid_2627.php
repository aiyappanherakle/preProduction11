<?php
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{ 
 $sql=$ilance->db->query("SELECT i.invoiceid, i.user_id, i.totalamount, i.projectid,i.combine_project,i.buynowid,i.invoiceid,i.isif,i.ismis,i.invoicetype
			FROM ". DB_PREFIX . "invoices i,
			". DB_PREFIX . "child_invoices ci
			where i.projectid != '0'
			and i.combine_project = '' 
			and i.invoiceid = ci.child_invoices
			and i.isif = '0'
			and i.ismis ='0'
			and i.isfvf ='0'
			and i.invoicetype != 'subscription'
			and i.isbuyerfee = '0'
			
			
			
			");
	
	   if($ilance->db->num_rows($sql) > 0)
       {
	      while($res = $ilance->db->fetch_array($sql))
	      {
		   
		   $res['user_id'];
		   $res['totalamount'];
		   $res['projectid'];
		   $res['invoiceid'];
		   $res['buynowid'];

		  $dup_invoice=$ilance->db->query("SELECT invoiceid, user_id, totalamount, projectid
			FROM ". DB_PREFIX . "invoices where buynowid = '".$res['buynowid']."' and totalamount = '".$res['totalamount']."'
			and user_id = '".$res['user_id']."' and invoiceid != '".$res['invoiceid']."' and projectid = '".$res['projectid']."'
			and isif = '0'
			and ismis ='0'
			and isfvf ='0'
			and invoicetype != 'subscription'
			and isbuyerfee = '0'
			");

			
	      while($res1 = $ilance->db->fetch_array($dup_invoice))
	      {

		  echo 'invoiceid = '.$res['invoiceid'.' and '.'projectid='.$res1['projectid'].' and buynowid='.$res1['buynowid'].' and totalamount='.$res1['totalamount'].' and user_id='.$res1['user_id'].'</br>';

		  }        
	  }		  	  
	 }
  
}
 else
 {
 echo 'login	';
 }
		 
		 ?>