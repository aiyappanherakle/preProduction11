<?php
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
//error_reporting(E_ALL);
		$select = $ilance->db->query("		
		SELECT coin_id,Quantity,sold_qty,u.username,c.Title,bqty
		FROM ".DB_PREFIX."coins c 
		LEFT JOIN (select sum(qty) as bqty,project_id from ilance_buynow_orders b WHERE project_id !=0
		GROUP BY project_id) as b on b.project_id = c.coin_id		JOIN ". DB_PREFIX ."users u on u.user_id = c.user_id
		WHERE c.coin_listed = 'c' 
		AND (c.Buy_it_now > '1')	
		AND c.status = '0'
		AND c.Quantity >1
		ORDER BY `c`.`coin_id`  DESC
		");
			
        //user consignment deatils
		$cou=$ilance->db->num_rows($select);
		if($ilance->db->num_rows($select) > 0)
         {
		
			
					$new_header = '
					<table width="100%">
						<tr>
							<td size="20" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
							<td>&nbsp;</td>
							<td size="13" family="helvetica" style="bold" nowrap><b>Buynow Report</b></td>
						</tr>
						<tr>
							<td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
							17500 Red Hill Avenue, Suite 160, Irvine, CA 92614-7290<br>
							Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
							E-mail: info@greatcollections.com</td>
							<td >&nbsp;</td>
						</tr>					
						<tr>
							<td>&nbsp;
							</td>
						</tr>
				</table>
				
				<table width="100%">
						<tr bgcolor="#CD9C9C"> 
							<td>Item ID</td>
							<td width = "45%">Title</td>
							<td>Consignor Name</td>
							<td>Available Qty </td>
							<td>Sold Qty </td>
						</tr>';
							$table1=$new_header;	
                while($res = $ilance->db->fetch_array($select))
                {
	 												
					$table1.='
						<tr>
							<td>'.$res['coin_id'].'</td> 
							<td width = "45%">'.$res['Title'].'</td>
							<td>'.$res['username'].'</td>  
							<td>'.$res['Quantity'].'</td>  
							
							<td>'.$res['bqty'].'</td>                        
						</tr>';
				
	
 			  }
			
		}
define('FPDF_FONTPATH','../font/');
require('pdftable_1.9/lib/pdftable.inc.php');
$p = new PDFTable();
$p->AddPage();
$p->setfont('times','',10);
$p->htmltable($table1);
$p->output('Buynow_Report_'.date('Y-m-d h-i-s').'.pdf','D');
}
else
	{
		  print_action_failed("Please Login to Continue",reports.'.php');
							
	      exit();
	 }		   				
			
?>