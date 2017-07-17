<?php
require_once('./../functions/config.php');
//error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND ($_SESSION['ilancedata']['user']['isstaff'] == '1' OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
{
	
	
		$select = $ilance->db->query("
					
					SELECT i.*,ip.disount_val,ip.shipping_cost,sum(ib.amount-ib.taxamount) as buyerfee,date(i.createdate) as createdate ,date(i.paiddate) as  paiddate,i.user_id,i.totalamount as amount,u.username, u.first_name, u.last_name, u.email ,u.address,u.city,u.zip_code,u.issalestaxreseller
					FROM ilance_invoices i

					JOIN ".DB_PREFIX."users u ON u.user_id = i.user_id
					
					left JOIN ".DB_PREFIX."invoice_projects ip ON ip.final_invoice_id = i.invoiceid
					AND ip.buyer_id =i.user_id

					left JOIN ".DB_PREFIX."projects as p ON p.project_id=ip.project_id and p.haswinner =1
					
					left JOIN ".DB_PREFIX."invoices ib ON ib.projectid = p.project_id and ib.subscriptionid=0 AND ib.isfvf = 0 AND ib.isif = 0 AND ib.isenhancementfee = 0 AND ib.isescrowfee = 0 AND ib.isbuyerfee = 1 AND ib.Site_Id = 0 AND ib.combine_project ='' and ib.status = 'paid'
					
					where i.paiddate between '".$ilance->GPC['start_date']."' and '".$ilance->GPC['end_date']."'
						  AND i.status =  'paid' 					  
						  AND i.combine_project !=''
						  group by i.invoiceid
                          order by i.paiddate asc

					
					");
		$cou=$ilance->db->num_rows($select);
		if($ilance->db->num_rows($select) > 0)
         {

		
			
				$new_header = ' ';

							$table1=$new_header;	
							$counties_list=array();
							$sales_list=array();
							$shipping_list=array();
                while($res = $ilance->db->fetch_array($select))

                {

				

						
						$name = $res['username'];
						
						$email=$res['email'];
						
						$first_name=$res['first_name'];
						
						$last_name=$res['last_name'];
						
						$address=$res['address'];
						
						$city=$res['city'];
						
						$state=$res['state'];
						
						$zipcode=$res['zip_code'];
						
						$name = $first_name." ".$last_name;

						$user_county=get_county($res['zip_code']);
						
						$reseller = $res['issalestaxreseller']>0?$res['issalestaxreseller']:'';
						

                       //$buyer_fee =  fetch_auction('buyer_fee',$res['projectid']);
					   
						
						
						$shippin_amt = $res['shipping_cost'];
						$buyer_fee = $res['buyerfee']>0?$res['buyerfee']:0;
						$discount_amt = $res['disount_val'];
						$res['amount']=$res['amount']-$buyer_fee-$shippin_amt-$res['taxamount'];
						
if($user_county=='-')
{
$user_county=0;
}
						
						if(array_key_exists($user_county,$counties_list))
						{
						$counties_list[$user_county]+=$res['taxamount'];
						$sales_list[$user_county]+=$res['amount'];
						$shipping_list[$user_county]+=$shippin_amt;
						}
						else
						{
						$counties_list[$user_county]=$res['taxamount'];
						$sales_list[$user_county]=$res['amount'];
						$shipping_list[$user_county]+=$shippin_amt;
						
						}
						
			
							$total = ($res['amount']) + $buyer_fee + $shippin_amt + $res['taxamount'] + $discount_amt;
							$summ_sub_total+=$res['amount'];
							$summ_buyer_fees+=$buyer_fee;
							$summ_shipping+=$shippin_amt;
							$summ_sales_tax+=$res['taxamount'];
							$summ_discount+=$discount_amt;
							$summ_total+=$total;
							
							$data_row['invoiceid']	=$res['invoiceid'];
							$data_row['createdate']	=$res['createdate'];
							$data_row['paiddate']	=$res['paiddate'];
							$data_row['amount']		=$res['amount'];
							$data_row['buyer_fee']	=$buyer_fee;
							$data_row['shippin_amt']=$shippin_amt;
							$data_row['taxamount']	=$res['taxamount'];
							$data_row['discount_amt']=$discount_amt;
							$data_row['total']		=$total;
							$data_row['reseller']	=$reseller;
							$data_row['user_county']=$user_county;
							$data_row['name']		=$name;
							$data_row['address']	=$address;
							$data_row['city']		=$city;
							$data_row['state']		=$state;
							$data_row['zipcode']	=$zipcode;
							$data[$row_count]=$data_row;
							$row_count++;
 			  }
		}
		unset($data_row);
							$data_row['invoiceid']	="";
							$data_row['createdate']	="";
							$data_row['paiddate']	="TOTAL";
							$data_row['amount']	=$summ_sub_total;
							$data_row['buyer_fee']	=$summ_buyer_fees;
							$data_row['shippin_amt']	=$summ_shipping;
							$data_row['taxamount']	=$summ_sales_tax;
							$data_row['discount_amt']	=$summ_discount;
							$data_row['total']	=$summ_total;
							$data[$row_count]=$data_row;
							$row_count++;

																

//echo $table1;exit;
$ilance->admincp = construct_object('api.admincp');
$headings=array('Invoice ','Created Date','Paid Date','Sub Total','Buyers Fee','Shipping','Sales Tax','Discount','Total Order','Reseller','County','Name','Address','City','State','Zip Code');
$reportoutput= $ilance->admincp->construct_csv_data($data, $headings);
$headings=array('County','Amount','Sub Total');
ksort($counties_list);															
if(count($counties_list))																
{
unset($data);
unset($data_row);
	foreach($counties_list as $county=>$amounts)
	{
	$countyname=$county;
	if($county==0) $countyname="-";
	
	$data_row['county']=$countyname;
	$data_row['amount']=$amounts;
	$data_row['sub_total']=$sales_list[$county]+$shipping_list[$county];
	
	$data[$row_count]=$data_row;
	
	$row_count++;
	
}
}

$reportoutput1= $ilance->admincp->construct_csv_data($data, $headings);

				header("Pragma: cache");
				header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
				header('Content-Disposition: attachment; filename="'.'AllSalesTax_'.date('Y-m-d h-i-s').'.csv"');
				echo $reportoutput;
				echo $reportoutput1;
				die();
	

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function get_county($zip_code)
{
	global $ilance;
	$county=0;
	if(strpos($zip_code,"-"))
	list($zip_code,$pobox)=explode("-",$zip_code);
	$county_query=$ilance->db->query("select reportingcodecounty from ".DB_PREFIX."taxes where zipcode='".$zip_code."'");
	$county_details=$ilance->db->fetch_array($county_query);
	$county=$county_details['reportingcodecounty'];
	//if($county==0) echo $zip_code.'<br>';
	return  $county;
}		
?>
