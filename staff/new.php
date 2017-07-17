<?php 
require_once('./../functions/config.php');
if (empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo "login to cont";exit;
}
error_reporting(E_ALL);
$ilance->GPC['user_id']=$ilance->GPC['user_id']?$ilance->GPC['user_id']:4982;
$stmt_date=isset($ilance->GPC['stmt_date'])?$ilance->GPC['stmt_date']:'04-22-2012';

$stmt_date=alter_date($stmt_date);
$start=last_monday($stmt_date);
$user_where=isset($ilance->GPC['user_id'])?' where user_id='.$ilance->GPC['user_id']:'';
$html='';
$user_query=$ilance->db->query("select user_id from ".DB_PREFIX."users".$user_where);
if($ilance->db->num_rows($user_query))
{
while($user_details=$ilance->db->fetch_array($user_query))
	{
	$html.=get_header_pdf($user_details['user_id'],$stmt_date);
	//from coin relist table
	 unset($list);
	$list=get_any_relist_or_buynow($user_details['user_id'],$start,$stmt_date);
 
	$any_relist_coins=count($list)>0?" or coin_id in (".implode(",",$list).")":'';
	//from coinss table
	$coins_list_query=$ilance->db->query("select coin_id,Title,Minimum_bid,Buy_it_now,Alternate_inventory_No,Certification_No,project_id from ".DB_PREFIX."coins where user_id='".$user_details['user_id']."' and (date(End_Date)>='".$start."' and date(End_Date)<='".$stmt_date."' ".$any_relist_coins.") group by coin_id order by coin_id");
 	if($ilance->db->num_rows($coins_list_query)>0)
	{
		$statement_final_total=0;
		$statement_listing_fee_total=0;
		$statement_seller_fee_total=0;
		$statement_seller_total=0;
 
		while($coins_list_line=$ilance->db->fetch_array($coins_list_query))
		{
		$no_of_bids=0;$no_of_buynow=0;
			$project_detail_query=$ilance->db->query("select project_id,filtered_auctiontype,winner_user_id,hasbuynowwinner,insertionfee from ".DB_PREFIX."projects where project_id='".$coins_list_line['coin_id']."'");
				if($ilance->db->num_rows($project_detail_query)>0)
				{
					$coin_seller_fee=0;
					$coin_final_price=0;
					$coin_insertion_fee=0;
					while($project_detail_line=$ilance->db->fetch_array($project_detail_query))
					{
					$coin_insertion_fee=$project_detail_line['insertionfee'];
					if(in_array($project_detail_line['project_id'],$list))
					{
						//select last relist date from coin_relist and find the insertion fee invoice between statement date and relisdate, take all b4 stmt date
						$last_relist_query=$ilance->db->query("select actual_end_date from ".DB_PREFIX."coin_relist where coin_id='".$project_detail_line['project_id']."' and date(actual_end_date)='".$stmt_date."'  order by actual_end_date limit 1");
						if($ilance->db->num_rows($last_relist_query)>0)
						{
							while($last_relist_line=$ilance->db->fetch_array($last_relist_query))
							{
							// get insertion fee invoice
								$insertion_fee_invoice_query=$ilance->db->query("select amount from ".DB_PREFIX."invoices where projectid='".$project_detail_line['project_id']."' and createdate<='".$last_relist_line['actual_end_date']."' and isif = 1");
								if($ilance->db->num_rows($insertion_fee_invoice_query)>0)
								{
									while($insertion_fee_invoice_line=$ilance->db->fetch_array($insertion_fee_invoice_query))
									{
										$coin_seller_fee+=$insertion_fee_invoice_line['amount'];
									}
								}
							}
						}	
						
					}
					
				
					
					switch ($project_detail_line['filtered_auctiontype'])
					{
					case 'regular':
					$no_of_bids=no_of_bids($project_detail_line['project_id'],$stmt_date);
					$regular_project_invoice_query=$ilance->db->query("select invoiceid, projectid, totalamount, amount, invoicetype, createdate, isfvf, isif, isenhancementfee, ismis  from ".DB_PREFIX."invoices where projectid='".$project_detail_line['project_id']."'");
					if($ilance->db->num_rows($regular_project_invoice_query)>0)
					{
						while($regular_project_invoice_line=$ilance->db->fetch_array($regular_project_invoice_query))
						{
					if($regular_project_invoice_line['invoicetype']=='escrow')
							$coin_final_price=$regular_project_invoice_line['amount'];
					if($regular_project_invoice_line['isfvf']==1 or $regular_project_invoice_line['isenhancementfee']==1 or $regular_project_invoice_line['ismis']==1)
							$coin_seller_fee+=$regular_project_invoice_line['amount'];
						 
						}
					} 
					break;
					default:
					$no_of_buynow=no_of_buynow($project_detail_line['project_id'],$stmt_date,$start); 
					
					/*$project_lastlisted_date=lastlisted_date($project_detail_line['project_id'],$stmt_date);
									 
									 
					if($project_lastlisted_date!=false)
					$coin_last_time_listed_where=" AND date(createdate)>='".$project_lastlisted_date."'";
					//only buynow
					
					 $ssqf="select * from ".DB_PREFIX."invoices where projectid='".$project_detail_line['project_id']."' and date(createdate) <= '".$stmt_date."'".$coin_last_time_listed_where;*/
					 
					 $ssqf="select * from ".DB_PREFIX."invoices where projectid='".$project_detail_line['project_id']."' and date(createdate) <= '".$stmt_date."' and date(createdate)>='".$start."'" ;
					 
					 if(39075==$project_detail_line['project_id'])
					{
					echo $no_of_buynow;
					echo '<br>';
					echo $ssqf;
					}
					
				  
					$fixed_project_invoices_query=$ilance->db->query($ssqf);
					if($ilance->db->num_rows($fixed_project_invoices_query)>0)
					{
						while($fixed_project_invoices_line=$ilance->db->fetch_array($fixed_project_invoices_query))
						{
					if($fixed_project_invoices_line['invoicetype']=='escrow')
						$coin_final_price=$fixed_project_invoices_line['amount'];
					if($fixed_project_invoices_line['isfvf']==1 or $fixed_project_invoices_line['isenhancementfee']==1 or $fixed_project_invoices_line['ismis']==1)
						$coin_seller_fee+=$fixed_project_invoices_line['amount'];
						}
					}
					
					break;
					}
					}
				}
			$coin_consignor_total=$coin_final_price-($coin_insertion_fee+$coin_seller_fee);	
			$statement_final_total+=$coin_final_price;
			$statement_listing_fee_total+=$coin_insertion_fee;
			$statement_seller_fee_total+=$coin_seller_fee;
			$statement_seller_total+=$coin_consignor_total;	
			
			 
			 
			$html.='<tr>';
			$html.='<td>'.$coins_list_line['coin_id'].'</td>';
			$html.='<td>'.$coins_list_line['Title'].'<br />Cert #:'.$coins_list_line['Certification_No'].'<br />';
			$html.='Alt Inv#:'.$coins_list_line['Alternate_inventory_No'].'</td>';
			$html.='<td>GC</td>';
			$html.='<td>'.$no_of_bids.'/'.$no_of_buynow.'</td>';
			$html.='<td>'.$ilance->currency->format_real_no($coins_list_line['Minimum_bid']).'/'. $ilance->currency->format($coins_list_line['Buy_it_now']) .'</td>';
			$html.='<td><font color="#FF0000"></font>'.$ilance->currency->format_real_no($coin_final_price).'</td>';	
			$html.='<td>'.$ilance->currency->format_real_no($coin_insertion_fee,0,false).'</td>';	
			$html.='<td>'.$ilance->currency->format_real_no($coin_seller_fee).'</td>';	
			$html.='<td>'.$ilance->currency->format_real_no($coin_consignor_total).'</td>';
			$html.='</tr>';
			$misamt = $ilance->currency->format_real_no($coins_list_line['Minimum_bid']);	
			 
			
		}
		
		$html.='<tr>';
		$html.='<td colspan="5"></td>';
		$html.='<td>'.$statement_final_total.'</td>';
		$html.='<td>'.$statement_listing_fee_total.'</td>';
		$html.='<td>'.$statement_seller_fee_total.'</td>';
		$html.='<td>'.$statement_seller_total.'</td>';
		$html.='</tr>';
		 
	}else
	{
	echo 'no item';
	}
	
	
	}
}
echo $html;
exit;
function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}
function alter_date($somedate)
{
list($m,$d,$y)=explode("-",$somedate);
return $y.'-'.$m.'-'.$d;
}
function get_header_pdf($user_id,$end_date)
{
global $ilance;
$FETCH_USER=$ilance->db->fetch_array($ilance->db->query("SELECT username,email,first_name,last_name,address,address2,city,state ,zip_code from ".DB_PREFIX."users where user_id='".$user_id."'"));
$name = $FETCH_USER['username'];
$email=$FETCH_USER['email'];
$first_name=$FETCH_USER['first_name'];
$last_name=$FETCH_USER['last_name'];
$address=$FETCH_USER['address'];
$address2=$FETCH_USER['address2'];
$city=$FETCH_USER['city'];
$state=$FETCH_USER['state'];
$zipcode=$FETCH_USER['zip_code'];
$new_header = '<table width="100%">
<tr>
<td size="24" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
<td>&nbsp;</td>
<td size="13" family="helvetica" style="bold" nowrap><b>Consignor Statement</b></td>
</tr>
<tr>
<td valign="top" size="10" family="helvetica" >Certified Coin Auctions & Direct Sales<br>
17500 Red Hill Avenue, Suite 160, Irvine, CA 92614-7290<br>
Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
E-mail: info@greatcollections.com</td>
<td >&nbsp;</td>
<td >Date of Sale:&nbsp;'.$end_date.'</td>
</tr>					
<tr >
<td>&nbsp;Consignor Username : '.$name.' <br>E-mail : '.$email.'</td>
</tr>
<tr >
<td>'.$first_name.' &nbsp; '.$last_name.'<br>'.$address.'<br>'.$address2.'<br>'.$city.' &nbsp; '.$state.' &nbsp; '.$zipcode.' </td>
</tr>
<tr>
<td>&nbsp;
</td>
</tr>
</table><table width="100%" border=1>
<tr bgcolor="#CD9C9C">
<td>ID</td>
<td width = "45%">Item Title</td>
<td>Listed</td>	
<td>Bids</td>
<td>Min Bid/<br>Buy Now</td>
<td>Final<br>Price</td>
<td>Listing<br> Fees</td>
<td>Sellers<br> Fees</td>	
<td>Net to Consignor</td>
</tr>';
return $new_header;
}
function dump1()
{ $sql="SELECT r.enddate, r.user_id, r.coin_id,c.Title, c.coin_id, c.user_id, c.Quantity, c.Title, c.End_Date, c.Minimum_bid, c.Buy_it_now, c.Certification_No, c.Alternate_inventory_No
									FROM ".DB_PREFIX."coin_relist r
									LEFT JOIN ".DB_PREFIX."coins c ON ( c.coin_id = r.coin_id )
									WHERE r.user_id =".$user_details['user_id']."
									AND 
									(
									(date( r.actual_end_date ) > '".last_monday($stmt_date)."' AND date( r.actual_end_date ) <= '".$stmt_date."')
									OR
									(date( c.End_Date ) > '".last_monday($stmt_date)."' AND date( c.End_Date ) <= '".$stmt_date."')
									) group by r.coin_id  order by c.coin_id ";
	$coins_query=$ilance->db->query( $sql);
		if($ilance->db->num_rows($coins_query)>0)								
		{
		while($coin_list=$ilance->db->fetch_array($coins_query))
		{
		//item buynow or auction
		
		
		
		$project_details_query=$ilance->db->query();
			$html.='<tr>';
			$html.='<td>'.$coin_list['coin_id'].'</td>';
			$html.='<td>'.$coin_list['Title'].'<br />Cert #:'.$coin_list['Certification_No'].'
<br />Alt Inv#:'.$coin_list['Alternate_inventory_No'].'
</td>';
			$html.='<td>GC</td>';
			$html.='<td>GC</td>';
			$html.='<td>'.$ilance->currency->format($coin_list['Minimum_bid']).'/'.$ilance->currency->format($coin_list['Buy_it_now']).'</td>';
			$html.='<td></td>';
			$html.='</tr>';		
			$misamt = $ilance->currency->format($coin_list['Minimum_bid']);	
		}
		}else
		{
		echo '<br>';
		echo $ilance->db->num_rows($coins_query);
		echo 'no item';
		}}
		
		function no_of_bids($project_id,$report_date)
		{
		global $ilance;
		 $sql="select count(bid_id) from ".DB_PREFIX."project_bids where project_id='".$project_id."' and date(date_added)<='".$report_date."'";
			$query=$ilance->db->query($sql);
			if($ilance->db->num_rows($query)>0)
			{
				while($line=$ilance->db->fetch_array($query))
				{
				return $line[0];
				}
			}else
			{
				return 0;
			}
		}
		
		function no_of_buynow($project_id,$report_date,$start)
		{
		global $ilance;
		/*$project_lastlisted_date=lastlisted_date($project_id,$report_date);
		$coin_last_time_listed_where='';
		if($project_lastlisted_date!=false)
		{
		 $coin_last_time_listed_where=" AND date(orderdate)>='".$project_lastlisted_date."'";
		}*/
		
			 $query=$ilance->db->query("select count(orderid) from ".DB_PREFIX."buynow_orders where project_id='".$project_id."' and date(orderdate)<='".$report_date."' and date(orderdate)>='".$start."'");
			if($ilance->db->num_rows($query)>0)
			{
				while($line=$ilance->db->fetch_array($query))
				{
				return $line['0'];
				}
			}else
			{
			return 0;
			}
		}
		function lastlisted_date($project_id,$stmt_date)
		{
		global $ilance;
			$query=$ilance->db->query("select actual_end_date from ".DB_PREFIX."coin_relist where coin_id='".$project_id."' and date(actual_end_date)<'".$stmt_date."' order by enddate DESC limit 1");
			if($ilance->db->num_rows($query)>0)
			{
				while($line=$ilance->db->fetch_array($query))
				{
				return $line['actual_end_date'];
				}
			}else
			{
			return false;
			}
		}
		function get_any_relist_or_buynow($user_id,$start,$stmt_date)
		{
		global $ilance;
		 $list=array();
	 
			$coin_relist_list_query=$ilance->db->query("select * from ".DB_PREFIX."coin_relist where date(actual_end_date)>='".$start."' and date(actual_end_date)<='".$stmt_date."' and user_id='".$user_id."' group by coin_id");
			if($ilance->db->num_rows($coin_relist_list_query)>0)
			{
				while($coin_relist_list_line=$ilance->db->fetch_array($coin_relist_list_query))
				{
				$list[]=$coin_relist_list_line['coin_id'];
				}
			} 
			
			$buynow_list_query=$ilance->db->query("select project_id from ".DB_PREFIX."buynow_orders where date(orderdate)>='".$start."' and date(orderdate)<='".$stmt_date."' and owner_id='".$user_id."' group by project_id  ");
			if($ilance->db->num_rows($buynow_list_query)>0)
			{
				while($buynow_list_line=$ilance->db->fetch_array($buynow_list_query))
				{
				$list[]=$buynow_list_line['project_id'];
				}
			}
 
			return array_unique($list);
		}
?>