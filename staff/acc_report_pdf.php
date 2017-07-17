<?php

require_once('./../functions/config.php');

//error_reporting(E_ALL);

        //sprt order by catalog and coinid



	
		 define('FPDF_FONTPATH','../font/');



require('pdftable_1.9/lib/pdftable.inc.php');

			

			$p = new PDFTable();

 		

			$p->AddPage();

			$p->setfont('times','',10);		

        
		
		$start =  date('Y-m-d',strtotime($ilance->GPC['start_date']));

		$end =  date('Y-m-d',strtotime($ilance->GPC['end_date']));
		
			// Buy now

					$query1 = $ilance->db->query("SELECT COUNT(*) AS buycount, SUM(amount) AS buyamount FROM " . DB_PREFIX . "buynow_orders

												WHERE date(orderdate) >= '".$start."' AND date(orderdate) <= '".$end."' ");

					$res1 = $ilance->db->fetch_array($query1);
					
					
					// Auction

					

					$query2 = $ilance->db->query("SELECT COUNT(*) AS auctioncount, SUM(bidamount) AS auctionamount FROM " . DB_PREFIX . "project_bids

												WHERE date(date_awarded) >= '".$start."' AND date(date_awarded) <= '".$end."' ");

					$res2 = $ilance->db->fetch_array($query2);

					//unsold count
					
					$query_1 = $ilance->db->query("SELECT COUNT(*) AS unsoldcount FROM " . DB_PREFIX . "projects

												WHERE date(date_starts) >= '".$start."' AND date(date_end) <= '".$end."' AND haswinner='0' AND hasbuynowwinner='0' ");

					$res_1 = $ilance->db->fetch_array($query_1);
					

					$query3 = $ilance->db->query("SELECT SUM(taxamount) AS taxamount FROM " . DB_PREFIX . "invoices

												WHERE status = 'paid'

												AND combine_project 

												AND date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."' ");

					$res3 = $ilance->db->fetch_array($query3);

					

					$query4 = $ilance->db->query("SELECT SUM(amount) AS fvfamount FROM " . DB_PREFIX . "invoices

												WHERE isfvf = '1'

												AND date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."' ");

					$res4 = $ilance->db->fetch_array($query4);

					

					$query5 = $ilance->db->query("SELECT SUM(amount) AS ifamount FROM " . DB_PREFIX . "invoices

												WHERE isif = '1'

												AND date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."' ");

					$res5 = $ilance->db->fetch_array($query5);

					

					$query6 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(paiddate) >= '".$start."' AND date(paiddate) <= '".$end."'

												 GROUP BY projectid ");

					if($ilance->db->num_rows($query6 )>0)

					{

						while($res = $ilance->db->fetch_array($query6))

						{

							$selectpjt = $ilance->db->query("SELECT * FROM ".DB_PREFIX."projects			

														WHERE project_id = '".$res['projectid']."'");

							$resultpjt = $ilance->db->fetch_array($selectpjt, DB_ASSOC);

							if($resultpjt['featured'] !=0)

							{

							$featured = $ilconfig['productupsell_featuredfee'];

							}

							else

							{

						 	$featured = '0.00';

							}

						// highlite fee amount

							if($resultpjt['highlite'] !=0)

							{

							$highlite = $ilconfig['productupsell_highlightfee'];

							}

							else

							{

						 	$highlite = '0.00';

							}

						

						// bold fee amount

							if($resultpjt['bold'] !=0)

							{

							$bold = $ilconfig['productupsell_boldfee'];

							}

							else

							{

						 	$bold = '0.00';

							}

							// buyer fee

							if($ilconfig['staffsettings_feeinnumber'] != 0 AND $resultpjt['filtered_auctiontype'] == 'regular')

							{

								$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];

							}

							else

							{

								$buyerfee_calnum = 0;

							}

							if($ilconfig['staffsettings_feeinpercentage'] != 0 AND $resultpjt['filtered_auctiontype'] == 'regular')

							{

								$buyerfee_calper = ($res['amount'] * ($ilconfig['staffsettings_feeinpercentage'] / 100));

							}

							else

							{

								$buyerfee_calper = 0;

							}

							if($buyerfee_calnum <= $buyerfee_calper )

							{

								$buyerfee1 = $buyerfee_calper;

							

							}

							else

							{

								$buyerfee1 = $buyerfee_calnum;

								

							}

							// buyer fee end

							//echo $res['projectid'].'amount'.$listfeetotal = $resultpjt['insertionfee'] + $featured + $highlite + $bold;							

							$totbuyer[] = $buyerfee1;

							$totfvf[] = $resultpjt['fvf'];

							$totins[] = $resultpjt['insertionfee'] + $featured + $highlite + $bold;

	

						}

						

					}

					

					$query7 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "user_advance

												 WHERE date(date_made) >= '".$start."' AND date(date_made) <= '".$end."'

												 AND statusnow = 'paid'

												 ");

					if($ilance->db->num_rows($query7) > 0)

					{

						$today = DATETODAY;

						while($resadv = $ilance->db->fetch_array($query7))

						{

							   $date_parts1=explode("-", $resadv['date_made']);

							   $date_parts2=explode("-", $today);

							   //gregoriantojd() Converts a Gregorian date to Julian Day Count

							   $start_date=gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);

							   $end_date=gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);

							   $diffdate =  $end_date - $start_date;

							   

							   $advtot[] = ($resadv['amount'] * ($resadv['interest']/100) * ($diffdate / 365)) .'<br>';

							

						}

					}

					

					$query8 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoice_projects

					 							WHERE date(created_date) >= '".$start."' AND date(created_date) <= '".$end."'

												 GROUP BY final_invoice_id

												 ");

					

					if($ilance->db->num_rows($query8) > 0)

					{

						while($res8 = $ilance->db->fetch_array($query8))

						{							

							$shipping_cost[] = $res8['shipping_cost'];

						}

					}

					$query9 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoice_projects

					 							WHERE date(created_date) >= '".$start."' AND date(created_date) <= '".$end."'

												 AND promocode != ''												 

												 ");

					

					if($ilance->db->num_rows($query9) > 0)

					{

						while($res9 = $ilance->db->fetch_array($query9))

						{

							$disount_cost[] = $res9['disount_val'];

						}

					}

					

					

					$query10 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 

												 AND p2b_user_id

												 AND status = 'unpaid'

												  ");

												 

					if($ilance->db->num_rows($query10) > 0)

					{

						while($res10 = $ilance->db->fetch_array($query10))

						{

							$inv_owing[] = $res10['totalamount'];

						}

					}

					

					$query11 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 												 

												 AND status = 'paid'

												  ");

												 

					if($ilance->db->num_rows($query11) > 0)

					{

						while($res11 = $ilance->db->fetch_array($query11))

						{

							$tot_pay[] = $res11['totalamount'];

						}

					}

					

					$query12 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 

												 AND paymethod

												 AND status = 'paid'

												  ");

												 

					if($ilance->db->num_rows($query12) > 0)

					{

						while($res12 = $ilance->db->fetch_array($query12))

						{

							if($res12['paymethod'] == 'paypal')

							{

								$tot_paypal[] = $res12['totalamount'];

							}
							
							if($res12['paymethod'] == 'account')

							{

								$tot_account[] = $res12['totalamount'];

							}

							if($res12['paymethod'] == 'check')

							{

								$tot_check[] = $res12['totalamount'];

							}

							if($res12['paymethod'] == 'bank')

							{

								$tot_bank[] = $res12['totalamount'];

							}

							if($res12['paymethod'] == 'creditcard')

							{

								$tot_card[] = $res12['totalamount'];

							}

							

						}

					}

					

					$query13 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 

												 AND p2b_user_id != user_id												 

												 AND p2b_user_id !=0

												 AND status = 'paid'

												  ");

					if($ilance->db->num_rows($query13) > 0)

					{

						$count_paid = $ilance->db->num_rows($query13);

						while($res13 = $ilance->db->fetch_array($query13))

						{

							$tot_paid[] = $res13['totalamount'];

						}

					}
					
				

					$query14 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 

												 AND p2b_user_id != user_id												 

												 AND p2b_user_id !=0

												 AND status = 'unpaid'

												  ");

					if($ilance->db->num_rows($query14) > 0)

					{

						while($res14 = $ilance->db->fetch_array($query14))

						{

							$tot_unpaid[] = $res14['totalamount'];

						}

					}

					

					$query15 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 

												 AND p2b_user_id != user_id												 

												 AND p2b_user_id !=0

												 AND status = 'paid'

												 AND invoicetype = 'advance'

												  ");

					if($ilance->db->num_rows($query15) > 0)

					{

						while($res15 = $ilance->db->fetch_array($query15))

						{

							$tot_advpaid[] = $res15['totalamount'];

						}

					}
					
						//Buy Now Items Not Paid
					
						$query16 = $ilance->db->query("SELECT COUNT(*) AS buycount_unpaid FROM " . DB_PREFIX . "buynow_orders b ,
						                   
										                                                   " . DB_PREFIX . "invoices  i  

												WHERE date(b.orderdate) >= '".$start."' 
												
												      AND date(b.orderdate) <= '".$end."' 
													  
													  AND i.invoiceid = b.invoiceid
													  
													  AND i.status = 'unpaid'
									
												 ");
											

					     $res16 = $ilance->db->fetch_array($query16);
						 
						 //Auction Items Not Paid
					
						$query17 = $ilance->db->query("SELECT COUNT(*) AS auction_unpaid FROM " . DB_PREFIX . "project_bids p ,
						                   
										                                                   " . DB_PREFIX . "invoices  i  

												WHERE date(date_awarded) >= '".$start."' 
												
												      AND date(date_awarded) <= '".$end."' 
													  
													  AND i.projectid = p.project_id
													  
													  AND i.status = 'unpaid'
											
												 ");
												 
											
					     $res17 = $ilance->db->fetch_array($query17);

                   //Consignor Statements Not Paid
				   
					$query18 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												 WHERE date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'

												 AND user_id 

												 AND p2b_user_id != user_id												 

												 AND p2b_user_id !=0

												 AND status = 'unpaid'

												  ");

					if($ilance->db->num_rows($query18) > 0)

					{

						$count_unpaid = $ilance->db->num_rows($query18);

						while($res18 = $ilance->db->fetch_array($query18))

						{

							$tot_paid_new[] = $res18['totalamount'];

						}

					}
					
					//Misc.credit and Debit
					
					$query19 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices

												WHERE ismis = '1'

												AND date(createdate) >= '".$start."' AND date(createdate) <= '".$end."'
												
												");

					while($res19 = $ilance->db->fetch_array($query19))
					
					{
					
					      if($res19['invoicetype'] == 'credit')

							{

								$tot_credit[] = $res19['amount'];

							}
							
							if($res19['invoicetype'] == 'debit')

							{

								$tot_debit[] = $res19['amount'];

							}
					}
					
					//Total consign
					
					$query20 = $ilance->db->query("SELECT SUM(totalamount) as tot_consign_pay FROM " . DB_PREFIX . "invoices i," . DB_PREFIX . "consign_statement c

												WHERE  date(i.createdate) >= '".$start."' AND date(i.createdate) <= '".$end."'
												
												AND i.p2b_user_id = c.seller_id
												
												");

					$res20 = $ilance->db->fetch_array($query20);
					
				

			//total 		
         $total_sold= $res2['auctioncount']+$res1['buycount'];
		 
		 $total_hammer = $res2['auctionamount']+array_sum($totbuyer)+$res1['buyamount'];
		 
		 $total_shipping = (array_sum($shipping_cost)+$res3['taxamount']+ array_sum($tot_debit))-(array_sum($tot_credit));
		 
		 $tot_payments_received = array_sum($tot_check)+array_sum($tot_paypal)+array_sum($tot_paypal)+array_sum($tot_account)+array_sum($tot_bank);
		
		$tot_adj =  array_sum($tot_debit)-array_sum($tot_credit);
		
		 $tot_consign = array_sum($totfvf)+array_sum($totins)+array_sum($tot_advpaid)+$tot_adj;
		 
		 

		 
		 $start_date =  date('m-d-Y',strtotime($ilance->GPC['start_date']));

		$end_date =  date('m-d-Y',strtotime($ilance->GPC['end_date']));
		
		$new_header = '<table> 
						
								 <tr>
		
									<td size="18" family="helvetica" style="bold" nowrap><b>GreatCollections Transaction Report Summary</b></td>
			
								</tr>
		
								<tr>
		
									<td size="13" family="helvetica" nowrap> Transactions From '.$start_date.' To  '.$end_date.'</td>
								</tr>	
											
								<tr >
		
									 <td size="13" family="helvetica" style="bold" nowrap> Items Ended</td>
						
								</tr>
						</table> 
						
						
						<table  width="100%" border="1" >
							<tr> <td >Total # of Items Sold	</td><td >'.$total_sold.'</td> </tr>
							<tr> <td >Total # of Items Unsold	</td><td >'.$res_1['unsoldcount'].'</td> </tr>
							<tr> <td >Total Hammer</td><td > '.$ilance->currency->format($res2['auctionamount'],$ilconfig['globalserverlocale_defaultcurrency']).'	</td></tr>
							<tr> <td >Total Buyer’s Fees </td><td >'.$ilance->currency->format(array_sum($totbuyer),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>		
							<tr> <td >Total Buy Now </td><td > '.$ilance->currency->format($res1['buyamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>	 
							<tr> <td  style="bold">  Total of Hammer/BF and BN	</td><td style="bold">'.$ilance->currency->format($total_hammer,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
							
						</table>
						<table  width="100%" >
								<tr >
		
									 <td size="13" family="helvetica" style="bold" nowrap> Payments Received</td>
							
								</tr>
						</table>
						<table  width="100%" border="1" >
									<tr><td >Total Shipping Fees</td><td >	'.$ilance->currency->format(array_sum($shipping_cost),$ilconfig['globalserverlocale_defaultcurrency']).'</strong></td></tr>
									 <tr><td >Total Sales Tax Collected</td><td >'.$ilance->currency->format($res3['taxamount'],$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										 <tr><td >Total Misc. Adjustments to Invoice (Credit)</td><td >'.$ilance->currency->format(array_sum($tot_credit),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>	
										 <tr><td >Total Misc. Adjustments to Invoice (Debit)</td><td >'.$ilance->currency->format(array_sum($tot_debit),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>	
										<tr><td style="bold" >Total of SF, ST, and Misc</td><td style="bold" >'.$ilance->currency->format($total_shipping,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
			
										  <tr><td >Total Check Payments</td><td >'.$ilance->currency->format(array_sum($tot_check),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										  <tr><td >Total Paypal Payments</td><td >'.$ilance->currency->format(array_sum($tot_paypal),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										  <tr><td >Total Credit Card Payments (Paypal)</td><td >'.$ilance->currency->format(array_sum($tot_paypal),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										  <tr><td >Total Credit Card Payments (BofA) </td><td >'.$ilance->currency->format(array_sum($tot_account),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										  <tr><td >Total Wire Payments	</td><td >'.$ilance->currency->format(array_sum($tot_bank),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										 <tr><td >Total Consignor Proceeds, Used Payment</td><td >'.$ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
										 <tr><td style="bold" >Total Payments Received</td><td style="bold">'.$ilance->currency->format($tot_payments_received,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
							 
					 	</table>
						<table  width="100%" >
		                  <tr >

						     <td size="13" family="helvetica" style="bold" nowrap>Accounts Receivable </td>
					
						 </tr>
						</table>
						<table  width="100%" border="1" >
						
								<tr><td >Total # of Auction Items Not Paid	</td><td >'.$res17['auction_unpaid'].'</td></tr>
								<tr><td >Total # of Buy Now Items Not Paid	</td><td>'.$res16['buycount_unpaid'].'</td></tr>
								<tr><td style="bold" >Total $ of Items Not Paid	</td><td style="bold">'.$ilance->currency->format(array_sum($tot_unpaid),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>	
						</table>
						<table  width="100%" >
							<tr >
						     <td size="13" family="helvetica" style="bold" nowrap>Consignments</td>
						   </tr>
						</table>
						<table  width="100%" border="1" >
								<tr><td >Total # of Items Sold (Auction)	</td><td >'.$res2['auctioncount'].'</td></tr>
								<tr><td >Total # of Items Sold (Buy Now)</td><td >'.$res1['buycount'].'</td></tr>
								<tr><td >Total Hammer</td><td > '.$ilance->currency->format($res2['auctionamount'],$ilconfig['globalserverlocale_defaultcurrency']).'	</td></tr>
								<tr><td >Total Seller’s Fees</td><td>'.$ilance->currency->format(array_sum($totfvf),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
								<tr><td >Total Listing Fees </td><td>'.$ilance->currency->format(array_sum($totins),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
								<tr><td >Total Advance Interest</td><td>'.$ilance->currency->format(array_sum($tot_advpaid),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
								<tr><td >Total Adjustments</td><td>$'.$tot_adj.'</td></tr>
								<tr><td style="bold" >Total Fees, Advance Interest, Adj	</td><td style="bold">'.$ilance->currency->format($tot_consign,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
						
						</table>
						<table  width="100%" >
						<tr >

						     <td size="13" family="helvetica" style="bold" nowrap>Consignors Paid</td>
					
						</tr>
						</table>
						<table  width="100%" border="1" >
								<tr><td >Total # of Statements Paid</td><td >'.$count_paid.'</td></tr>
								<tr><td >Total $ of Payments Transferred to Buyer</td><td >'.$ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>	
								<tr><td >Total $ of Payments Paid by Check</td><td >'.$ilance->currency->format(array_sum($tot_paid),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
								<tr><td >Total Consignor Payments</td><td>'.$ilance->currency->format($res20['tot_consign_pay'],$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>
	                    </table>
						<table  width="100%">
								 <tr >
									 <td size="13" family="helvetica" style="bold" nowrap>Consignor Payables</td>
								</tr>
						</table>
						<table  width="100%" border="1" >
							<tr><td >Total # of Consignor Statements Not Paid</td><td>'.$count_unpaid.'</td></tr>
							<tr><td style="bold" >Total $ of Consignor Statements Owed	</td><td style="bold">'.$ilance->currency->format(array_sum($tot_unpaid),$ilconfig['globalserverlocale_defaultcurrency']).'</td></tr>	
						</table>	';
				

							$table1=$new_header;

	

		$p->htmltable($table1);	



			$p->setfont('times','',10);				

     
	
$p->output('Account Summary From '.$start_date.' To '.$end_date.'.pdf','D');

			

?>

