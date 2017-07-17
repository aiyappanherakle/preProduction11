<?php

require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{ 
	$select = $ilance->db->query("SELECT user_id FROM ".DB_PREFIX."invoices		
					              WHERE status='unpaid'
								  AND invoicetype = 'escrow'
					              AND isfvf = 0
								  AND isif = 0 
								  AND isenhancementfee = 0
								  AND isbuyerfee = 0
								  GROUP BY user_id
					            ");


   if($ilance->db->num_rows($select) > 0)
   {
		define('FPDF_FONTPATH','../font/');
		require('pdftable_1.9/lib/pdftable.inc.php');
		$p = new PDFTable();
		$p->AddPage();
		$p->setfont('times','',10);	
			
        while( $res_user = $ilance->db->fetch_array($select))
        {
			$name = fetch_user('username',$res_user['user_id']);
			$email=fetch_user('email',$res_user['user_id']);
			$first_name=fetch_user('first_name',$res_user['user_id']);
			$last_name=fetch_user('last_name',$res_user['user_id']);
			$address=fetch_user('address',$res_user['user_id']);
			$city=fetch_user('city',$res_user['user_id']);
			$state=fetch_user('state',$res_user['user_id']);
			$zipcode=fetch_user('zip_code',$res_user['user_id']);
			$datedis = date('m-d-Y',strtotime($res_user['End_Date']));
						
			unset($totalamt_combine);
			unset ($buyerfee_combine);
			unset ($totalamt);
			unset ($buyerfee);
	 					
			$new_header = '<table width="100%">
						    <tr>
								<td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
								<td>&nbsp;</td>
								<td size="13" family="helvetica" style="bold" nowrap><b>Unpaid Invoice</b></td>
					        </tr>
						    <tr>
							   <td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
	                                               17500 Red Hill Avenue, Suite 160, Irvine, CA 92614-7290<br>
                                                    Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
                                                     E-mail: info@greatcollections.com</td>
	                        </tr>
        					 <tr>
						        <td>&nbsp;Consignor Username : '.$name.' <br>E-mail : '.$email.'</td>
						    </tr>
						    <tr>
						        <td>'.$first_name.' &nbsp; '.$last_name.'<br>'.$address.'<br>'.$city.' &nbsp; '.$state.' &nbsp; '.$zipcode.' </td>
						    </tr>
						    <tr><td>&nbsp;</td></tr>
						</table>
						
						<table width="100%">
							<tr bgcolor="#CD9C9C"> 
								  <td>Invoice ID</td>
								  <td>Buyer Name</td>	
								  <td>Project ID</td>
								  <td>Buyer Fee</td>
								  <td>Amount</td>
								  <td>Payment Method</td>								 
							</tr>';

				 $table1=$new_header;

				$user_coin_list = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
														WHERE user_id='".$res_user['user_id']."'
														AND status = 'unpaid'
														AND isfvf != 1
														AND isif != 1 
														AND isbuyerfee != 1 
														AND isenhancementfee != 1
					                                ");
													
			     if($ilance->db->num_rows($user_coin_list)>0)
				 {
				   while($result = $ilance->db->fetch_array($user_coin_list))
				   {
				     if($result['combine_project']!='')
				     {
					   
				       $exp_project = explode(',',$result['combine_project']);
				       for($i=0;$i <=count($exp_project);$i++)
		               {
			             $sele_pro = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
										                 WHERE invoiceid = '".$exp_project[$i]."'
													   ");
					     if($ilance->db->num_rows($sele_pro)>0)
					     {
						     $print = '1';
						     $res_inv = $ilance->db->fetch_array($sele_pro);
					         $buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
								                               WHERE projectid = '".$res_inv['projectid']."'
								                               AND user_id = '".$res_user['user_id']."'
								                               AND isbuyerfee = '1'
														   ");
														
							if($ilance->db->num_rows($buyfee_inv) > 0)
							{
								$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
								$buyerfee_combine[] = $res_buyfee['totalamount'];
							}
							else
							{
								$res_buyfee['totalamount']='0';
							}
				           $totalamt_combine[]=$res_inv['totalamount'];
							
							$table1.='<tr><td width="10%">'.$exp_project[$i].'</td> 
												<td >'.$name.'</td>
												<td >'.$res_inv['projectid'].'</td>
												<td>'.$ilance->currency->format($res_buyfee['totalamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</td>
												<td>'.$ilance->currency->format($res_inv['totalamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</td>
												<td>'.$res_inv['paymethod'].'</td>
											 </tr>';
					 }
					 else
					 {
					   $print = '0';
					 }
 		          }
		       }
					 if($result['combine_project'] == '')
					 {
					   $print = '1';
					   $buyfee_inv1 = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
														 WHERE projectid = '".$result['projectid']."'
														 AND user_id = '".$res_user['user_id']."'
														 AND isbuyerfee = '1'
													   ");
								if($ilance->db->num_rows($buyfee_inv1) > 0)
									$res_buyfee1 = $ilance->db->fetch_array($buyfee_inv1);
								else
									$res_buyfee1['totalamount'] = '0';
									
									$buyerfee[] = $res_buyfee1['totalamount'];
									
									$totalamt[]=  $result['totalamount'];
					
								$table1.='<tr><td width="10%">'.$result['invoiceid'].'</td> 
															<td >'.$name.'</td>
															<td >'.$result['projectid'].'</td>
															<td>'.$ilance->currency->format($res_buyfee1['totalamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</td>
															<td>'.$ilance->currency->format($result['totalamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</td>
															<td>'.$result['paymethod'].'</td>
														 </tr>';
					  }									 

	                  $amounttotal = array_sum($totalamt_combine) + array_sum($totalamt);
		              $buyerfee_total = array_sum($buyerfee_combine) + array_sum($buyerfee);
			       }
	            }

	  
          $grand_total = $amounttotal + $buyerfee_total;		

		   $table1.='<tr><td>&nbsp;</td></tr>
				     <tr><td>Gross Total</td>
						  <td>&nbsp;</td>
						  <td>&nbsp;</td>
						  <td nowrap><b>'.$ilance->currency->format($buyerfee_total,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>
						  <td nowrap><b>'.$ilance->currency->format($amounttotal,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>
						   <td>&nbsp;</td>
                     </tr>
					  <tr><td>&nbsp;</td></tr>
					  <tr><td nowrap> Grand Total</td>
						   <td>(Buyer fee + Amount)</td>
                           <td>&nbsp;</td>
						   <td>&nbsp;</td>
						   <td><b>'.$ilance->currency->format($grand_total,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>
							<td>&nbsp;</td>
						</tr>
							';
$table1.='</table>';
 
$table1.='<table width="100%"><tr> <td>&nbsp;</td></tr><tr><td>Thank you for consigning to GreatCollections.<br>We appreciate your business.</td></tr></table>';
	 
	
 if($print == '1')
       {
	$p->htmltable($table1);	
	$p->AddPage();
    $p->setfont('times','',10);	
}			

 			 }
}	
$p->output('Unpaid_Report_'.date('Y-m-d h-i-s').'.pdf','D');
}

else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>

