<?php
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{ 
 $first_sql="SELECT invoiceid, totalamount, istaxable, 
			STATUS , isbuyerfee, combine_project
			FROM ". DB_PREFIX . "invoices 
			WHERE combine_project !=  ''  
			order by invoiceid desc";

	 $sql = $ilance->db->query($first_sql);					
			
	$parent_invoice_amount='';
    $child_invoice_amunt='';	
	   
	   $html = '<table border="1"><tr><td>invoiceid</td><td>count(child_invoice)</td><td>parent_invoice_amount</td><td>child_invoice_amount</td><td>ship amount</td><td>buyer feee amount</td><td>item amount</td></tr>';
	   
	   if($ilance->db->num_rows($sql) > 0)
       {
	      while($res = $ilance->db->fetch_array($sql))
	      {
		 
             $parent_invoice_amount= $res['totalamount'];			 
			 $parent_invoice_id=$res['invoiceid'];

                $sep_invoice =$ilance->db->query("select *  FROM ". DB_PREFIX . "invoices where invoiceid in(".$res['combine_project'].") ");
 
                $count = 0;				
				while($note1 = $ilance->db->fetch_array($sep_invoice))
				{
    					 
					$ship_invoice =$ilance->db->query("select shipping_cost,final_invoice_id FROM ". DB_PREFIX . "invoice_projects
					where  final_invoice_id='".$res['invoiceid']."'  
					limit 1
					");   
					$ship_amount = $ilance->db->fetch_array($ship_invoice);
									

					$buyerfee_invoice =$ilance->db->query("select invoiceid,projectid,totalamount FROM ". DB_PREFIX . "invoices where projectid = '".$note1['projectid']."' and isbuyerfee ='1'
					"); 

					$buyerfee_amount = $ilance->db->fetch_array($buyerfee_invoice);
					$total_ship_amount+=$ship_amount['shipping_cost'];
					$total_buyerfee_amount+=$buyerfee_amount['totalamount'];					
					$total_item_amount+=$note1['totalamount'];

                    $count++;
				}	

				$child_invoice_amount= $total_ship_amount+$total_buyerfee_amount+$total_item_amount;
			
              if($parent_invoice_id != $child_invoice_amount)	
                {		
		
				$html .= '<tr>
				      <td>'.$parent_invoice_id.'</td>
					  <td>'.$count.'</td>
					  <td>'.$parent_invoice_amount.'</td>
					  <td>'.$child_invoice_amount.'</td>
					  <td>'.$total_ship_amount.'</td>
					  <td>'.$total_buyerfee_amount.'</td>
					  <td>'.$total_item_amount.'</td>
				      </tr>';
					  }
				
				$total_ship_amount=0;
				$total_buyerfee_amount=0;
				$total_item_amount=0;
				
		  }
        $html .= '</table>';		  		       
	  }		  	  
	  echo $html;
	  
	  
	  
	  /* $timeStamp = date("Y-m-d-H-i-s");
				$fileName = "invoice fee tally report-$timeStamp";
		            define('FPDF_FONTPATH','../font/');
					
					require('pdftable_1.9/lib/pdftable.inc.php');
					
					$p = new PDFTable("landscape");
					
					$p->AddPage();
					
					$p->setfont('times','',8);
					
					$p->htmltable($html);
					
					$p->output($fileName.'.pdf','D');   */
	  
}
		 else
		 {
		 echo 'login	';
		 }
		 
		 ?>