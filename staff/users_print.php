<?php 
require_once('./../functions/config.php');
define('FPDF_FONTPATH','../font/');
require('pdftable_1.9/lib/pdftable1.inc.php');
$p = new PDFTable();

if (empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo "login to cont";exit;
}
//error_reporting(E_ALL);
//$ilance->GPC['user_id']=$ilance->GPC['user_id']?$ilance->GPC['user_id']:4982;
//$stmt_date=isset($ilance->GPC['start_date'])?$ilance->GPC['start_date']:'';

if(isset($ilance->GPC['date']))
{
$enddates_list[0]=$ilance->GPC['start_date'];
}
else
{
echo "Error";
exit;
}
//error_reporting(E_ALL);
	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'print')
	{
		$user_details['user_id'] = $ilance->GPC['userid'];
		$stmt_date = $ilance->GPC['date'];
		$start=last_monday($stmt_date);
		$settledate = $ilance->GPC['settledate'];

		$p = new PDFTable();
		$ilance->statement = construct_object('api.statement');
		$stmt_qry=$ilance->statement->statement_query($user_details['user_id'],$start,$stmt_date);
		
		
		$select=$ilance->db->query($stmt_qry);
		$data_to_pdf = '';
		$listcount = $ilance->db->num_rows($select);
		
			if($ilance->db->num_rows($select) > 0)
			 {
			
				$p->AddPage();
				$p->setfont('times','',9);		
				
				 $statement_auction_price_total='';
				 $statement_buyer_fee_total='';
				 $coin_consignor_total='';
				 $statement_final_total='';
				 $statement_listing_fee_total='';
				 $statement_seller_fee_total='';
				 $statement_seller_total='';
				 $statement_buynow_total='';
				
		$sql="SELECT u.user_id, u.username, u.email,u.Check_Payable FROM " . DB_PREFIX . "users u  WHERE u.user_id='".$user_details['user_id']."' ";

		$user_query=$ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($user_query) > 0)
		{
			while($user_details=$ilance->db->fetch_array($user_query))
			{
				$name = $user_details['username'];
				$email=$user_details['email'];
				$check_payable=$user_details['Check_Payable'];
				$data_to_pdf .= '<table width="100%">
				<tr>
				<td size="13" family="helvetica" style="bold" nowrap><b>Consignor Statement GC - </b>'.$name.'</td>
				</tr>
				<tr>
				<td>'.$name.' statement on &nbsp;'.date('F d, Y',strtotime($stmt_date)).'</td>
				</tr>					
				<tr>
				<td>&nbsp;
				</td>
				</tr>
				</table>';
			}
		}
				
			
			$data_to_pdf .= '<table width="100%" border="0">
				<tr bgcolor="#CD9C9C"> 
				<td width = "5%">ID</td>
				<td width = "80%">Item Title</td>
				<td width = "5%">Listed</td>	
				<td width = "5%">Bids</td>
				<td width = "5%">Min Bid/<br>Buy Now</td>
				<td width = "5%">Final<br>Price</td>
				<td width = "5%">Listing<br> Fees</td>
				<td width = "5%">Sellers<br> Fees</td>	
				<td width = "5%">Net to Consignor</td>
				</tr>';		
			
						 
			$show['statement'] = true;							
			$row_count = 0;	
			while($coins_list_line=$ilance->db->fetch_array($select))
			{
				$all_paid=1;
				$no_of_bids=0;
				$no_of_buynow=0;
				$coin_seller_fee=0;
				$coin_final_price=0;
				$coin_insertion_fee=0;	 
				switch ($coins_list_line['filtered_auctiontype'])
					{
					case 'regular':
						$no_of_bids=$coins_list_line['bid_count'];
						$coin_final_price=$coins_list_line['escrow_invoice_total'];
						$statement_auction_price_total+=$coin_final_price;
						$statement_buyer_fee_total+=$coins_list_line['buyer_fee'];
						if($coins_list_line['no_relist_b4_statement']==0)
						{
							$coin_insertion_fee=$coins_list_line['if_total'];
						}else
						{
							$coin_insertion_fee=0;
						}
						$coin_insertion_fee=$coins_list_line['if_total']+$coins_list_line['enhancementfee_total'];
						$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['mis_total'];
						$all_paid=$coins_list_line['all_paid'];
					break;
					default:
						$no_of_buynow=intval($coins_list_line['order_count'])+intval($coins_list_line['ebay_order_count']); 
						$coin_final_price=$coins_list_line['escrow_invoice_total'];
						$statement_buynow_total+=$coin_final_price;
						
						if($coins_list_line['no_relist_b4_statement']==0)
						{

						}else
						{
							$coin_insertion_fee=0;
						}
						$coin_insertion_fee=$coins_list_line['if_total']+$coins_list_line['enhancementfee_total'];
						$coin_seller_fee=$coins_list_line['fvf_total']+$coins_list_line['mis_total'];
						$all_paid=$coins_list_line['all_paid'];
					break;
					}	
							
					$test4[] = $coins_list_line['Buy_it_now'];
					$test5[] = $coins_list_line['Minimum_bid'];
					$bidtot[] = $no_of_bids;	
					$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
					$statement_final_total+=$coin_final_price;
					$statement_listing_fee_total+=$coin_insertion_fee;
					$statement_seller_fee_total+=$coin_seller_fee;
					$statement_seller_total+=$coin_consignor_total;	
					$data_pdf['coin_id']=$coins_list_line['coin_id'];
					$data_pdf['Title']=$coins_list_line['Title'];
					$data_pdf['Site_Id'] = $coins_list_line['site_name'];
					$data_pdf['bids'] = $no_of_bids;
					$data_pdf['minimum_bid'] = $ilance->currency->format_real_no($coins_list_line['Minimum_bid']) .' / '.$ilance->currency->format(isset($coins_list_line['Buy_it_now'])?$coins_list_line['Buy_it_now']:0) ;
					$data_pdf['Certification_No']=$coins_list_line['Certification_No'];
					$data_pdf['Alternate_inventory_No']=$coins_list_line['Alternate_inventory_No'];
					$data_pdf['bidamount']=$ilance->currency->format_real_no($coins_list_line['Minimum_bid']);
					$data_pdf['binamount']=$ilance->currency->format(isset($coins_list_line['Buy_it_now'])?$coins_list_line['Buy_it_now']:0) ;
					$data_pdf['qty']=$no_of_buynow>0?$no_of_buynow:'';
					$data_pdf['fvf'] = $ilance->currency->format_real_no($coin_final_price);
					$data_pdf['listing_fee'] = $ilance->currency->format_real_no($coin_insertion_fee,0,false);
					$data_pdf['seller_fee'] = $ilance->currency->format_real_no($coin_seller_fee);
					
					$data_pdf['net_consignor'] = $ilance->currency->format_real_no($coin_consignor_total);
					//
					$data_to_pdf .= '<tr>								
										<td>'.$data_pdf['coin_id'].'</td>
										<td width = "45%">
											'.$data_pdf['Title'];
					
					$data_to_pdf .='<br/>Certificate No.: '.$data_pdf['Certification_No'].'';
					
					if (isset($show['altinv']) AND $show['altinv'])
					{
						$data_to_pdf .= '<br/>
									Alt Inventory No.: '.$data_pdf['Alternate_inventory_No'].'';
					}					
					$data_to_pdf .= '</td>								
										<td>'.$data_pdf['Site_Id'].'</td>
										<td>'.$data_pdf['bids'].'</td>
										<td>'.$data_pdf['bidamount'].'&nbsp;/&nbsp;<b> '.$data_pdf['binamount'].'</b></td>
										<td>'.$data_pdf['fvf'].'&nbsp;'.$data_pdf['qty'].'</td>
										<td>'.$data_pdf['listing_fee'].'</td>
										<td>'.$data_pdf['seller_fee'].'</td>
										<td>'.$data_pdf['net_consignor'].'</td>
									</tr>';
						
			 $row_count++;
			 
			 
			}
			
		$advance_received=0;
		$miscellanious_debit=0;
		$miscellanious_credit=0;		
		$statement_total=$statement_seller_total-$advance_received+$miscellanious_debit-$miscellanious_credit;
		$totbinamount = $ilance->currency->format(array_sum($test4),$ilconfig['globalserverlocale_defaultcurrency']);
		$totbidamount = $ilance->currency->format(array_sum($test5),$ilconfig['globalserverlocale_defaultcurrency']);
		$tot_bidbuy = $totbidamount.' / '.$totbinamount;
		$totbids = array_sum($bidtot);		
		$totseller_fee=$ilance->currency->format_real_no($statement_seller_fee_total);
		$totlisting_fee=$ilance->currency->format_real_no($statement_listing_fee_total);
		$totfvf=$ilance->currency->format_real_no($statement_seller_total);
		$totnet_consignor=$ilance->currency->format_real_no($statement_seller_total);
		$total_advance=$ilance->currency->format_real_no($advance_received);
		$tot_mis=$ilance->currency->format_real_no($miscellanious_debit-$miscellanious_credit);
		$lastamount=$ilance->currency->format_real_no($statement_total);		
		$statecount = '('.$listcount.' Items) will settle on '.$settledate .' ('.$lastamount.')';
			
			
		$data_to_pdf .= '<tr><td>&nbsp;</td></tr>
						<tr>
							<td></td>
							<td><b>Gross Total</b></td>
							<td></td>
							<td nowrap><b>'.$totbids.'</b></td>
							<td nowrap><b>'.$tot_bidbuy.'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_final_total).'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_listing_fee_total).'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_seller_fee_total).'</b></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_seller_total).'</b></td>
						</tr>
						<tr>
							<td></td>
							<td><b>Advance </b></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($advance_received).'</b></td>
						</tr>
						<tr>
							<td></td>
							<td nowrap><b>Miscellaneous </b></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($miscellanious_debit-$miscellanious_credit).'</b></td>
						</tr>
						<tr>
							<td></td><td nowrap><b>Net Total</b></td><td></td><td></td><td></td><td></td><td></td><td></td>
							<td nowrap><b>'.$ilance->currency->format_real_no($statement_total).'</b></td>
						</tr>
						</tbody></table>';

			$p->htmltable($data_to_pdf);	

			}
		$p->output('Consignor_Statement '.DATETIME24H.'.pdf','D');
				exit;
	}
	
	
function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}


 

				
?>