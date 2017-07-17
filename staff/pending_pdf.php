<?php

require_once('./../functions/config.php');

//error_reporting(E_ALL);

        //sprt order by catalog and coinid

		
	
		$select = $ilance->db->query("

					SELECT * FROM ".DB_PREFIX."users 		

					");
					

        //user consignment deatils

		$cou=$ilance->db->num_rows($select);

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
						
						$country=fetch_user('country',$res_user['user_id']);
						
						$datedis = date('m-d-Y',strtotime($res_user['End_Date']));
						

	 					$new_header = '<table width="100%">
						<tr>

    <td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
	<td>&nbsp;</td>
    <td size="13" family="helvetica" style="bold" nowrap><b>Pending Invoice</b></td>
    

  </tr>

  <tr>

    <td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
	
	                                               2030 Main Street, Suite 620, Irvine, CA 92614<br>

                                                    Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>

                                                     E-mail: info@greatcollections.com</td>

    <td >&nbsp;</td>

		
	</tr>					
						<tr >

						<td>&nbsp;Username : '.$name.' <br>E-mail : '.$email.'</td>

						</tr>
						<tr >

						<td>'.$first_name.' &nbsp; '.$last_name.'<br>'.$address.'<br>'.$city.' &nbsp; '.$state.' &nbsp; '.$zipcode.' </td>

						</tr>
						<tr>
						<td>&nbsp;
						</td>
						</tr>
						</table><table width="100%">

															<tr bgcolor="#CD9C9C"> 

																	  <td>Item</td>

																	<td width = "45%">Title</td>
                                                                     
																	
																	  <td>Quantity</td>	

																	  <td>Final Price</td>

																	   <td>Buyer Fees</td>
																	
																	  <td>Sub-Total</td>
																	  
																	  <td>Shipping</td>
																	  
																	  <td>Tax</td>
																	  
																	  <td>Total</td>

																

																</tr>';

							$table1=$new_header;

				

				

				$user_coin_list = $ilance->db->query("

					SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . $res_user['user_id']."'
			AND status = 'unpaid'	and not combine_project
			AND isfvf != 1
			AND isif != 1 
			AND isbuyerfee != 1 
			AND isenhancementfee != 1	

					");

				
				
					 if($ilance->db->num_rows($user_coin_list)>0)

					 {

				$nt=$ilance->db->num_rows($user_coin_list);

					 while($res=$ilance->db->fetch_array($user_coin_list))

					 {
					 
					 unset($qty);
				
				unset($totqty);
				
					 $amount=$ilance->currency->format($res['amount'], $res['currency_id']);
					 
					 $tax_amt =$res['taxamount'];
					 
					 $tax_amount = $ilance->currency->format($res['taxamount'], $res['currency_id']);
					 
					 $title = fetch_auction('project_title', $res['projectid']);
					 
					 	$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
											WHERE invoiceid = '".$res['invoiceid']."'
											AND buyer_id = '".$res_user['user_id']."'");
				if($ilance->db->num_rows($buy)>0)
				{
					$resbuy = $ilance->db->fetch_array($buy);
				$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
											WHERE coin_id = '".$res['projectid']."'");
								$temp=$ilance->db->fetch_array($bids);						
					
					$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
					$res_regardlist['qty'] = $resbuy['qty'];
					 $totqty[] = $res_regardlist['qty']*$coin_no_in_set;
				}
				else
				{
				//check 	nocoin  in ilance_coins for each coins
				$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
											WHERE coin_id = '".$res_regardlist['projectid']."'");
								$temp=$ilance->db->fetch_array($bids);		
								
					$res_regardlist['qty'] = 1;
					 
					$totqty[] = empty($temp['nocoin'])?1:intval($temp['nocoin']);
				}
				$qty=array_sum($totqty);
				
                        $buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
												WHERE projectid = '".$res['projectid']."'
												AND user_id = '".$res_user['user_id']."'
												AND isbuyerfee = '1'");
					if($ilance->db->num_rows($buyfee_inv) > 0)
					{
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						$totalamountlist = $ilance->currency->format(($res['amount'] + $res_buyfee['amount'] ), $res['currency_id']);
						$buyerfee =  $ilance->currency->format($res_buyfee['amount'], $res['currency_id']);
						$buyerfee1 = $res_buyfee['amount'];
						$totalamountlist1 = $res['amount'] + $res_buyfee['amount'] ;
						$show['buyer'] = 1;
					}
					else
					{
						$totalamountlist = $ilance->currency->format(($res['amount'] ), $res['currency_id']);
						$buyerfee =  $ilance->currency->format(0, $res['currency_id']);
						$buyerfee1 = 0;
						$res_buyfee['amount']=0;
						$totalamountlist1 = $res['amount'];
						$show['buyer'] = 1;
					}
					
					$total=$res['amount']+$res_buyfee['amount'];
					
					if($country =='500')
					
					{
						
						  $con1="domestic='1'";
						
						   if( $total > '10000.00' )
							   
							     $con = "shipperid='25'";
							   
						   else if($total > '1000.00')
							   
							     $con = "shipperid='27'";
							   
						   else
							   
							     $con = "shipperid='26'";
							   
					
					 }
						 
						 else
						 {
						   
						     $con1="international='1'";
							 
							 $con = ($total > '10000.00') ? "shipperid='23'" : "shipperid='22'" ;
							 
						 }	 
							   
						
						 $shipper = $ilance->db->query("
												SELECT * 
												FROM " . DB_PREFIX . "shippers
												WHERE ".$con1."
												AND ".$con."
												");
					
												
						   $ship_det = $ilance->db->fetch_array($shipper);
					
						   $ship_amt = $ship_det['basefee'] + ($ship_det['addedfee'] * $qty) ; 
						   
						   $ship_amount = $ilance->currency->format($ship_amt, $res['currency_id']);
					
					      $total_new=$res['amount'] + $res_buyfee['amount'] + $ship_amt + $tax_amt;
					
					$sub_total=$ilance->currency->format($total, $res['currency_id']);
					
					$gnt_total=$ilance->currency->format($total_new, $res['currency_id']);

	$table1.='<tr>

					<td>'.$res['projectid'].'</td> 

                    <td width = "45%">'.$title.' </td>

		
					<td>'. $qty.'</td>

					<td>'.$amount.'</td>

					<td>'.$buyerfee.'</td>

					<td>'.$sub_total.'</td>
					
					<td>'.$ship_amount.'</td>
					
					<td>'.$tax_amount.'</td>
					
					<td>'.$gnt_total.'</td>
                       

  </tr>';

					

		
					 }

					  

$table1.='</table>';
	

		$p->htmltable($table1);	

	$p->AddPage();

			$p->setfont('times','',10);				

	

 			  }

			
		}

 }


$p->output('Pending Invoices.pdf','D');

			

?>

