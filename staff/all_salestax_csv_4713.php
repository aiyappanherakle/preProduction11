<?php
require_once('./../functions/config.php');
//error_reporting(E_ALL);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND ($_SESSION['ilancedata']['user']['isstaff'] == '1' OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
{
		$select = $ilance->db->query("

					SELECT *,date(createdate) as createdate ,date(paiddate) as  paiddate,user_id,totalamount as amount FROM ".DB_PREFIX."invoices
					where paiddate between date('".$ilance->GPC['start_date']."') and date('".$ilance->GPC['end_date']."')
						  AND status =  'paid' 
						  AND combine_project !=''
						  order by paiddate asc
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

						$user_data=$ilance->db->query("select * from ".DB_PREFIX."users where user_id='".$res['user_id']."'");
						$user_details = $ilance->db->fetch_array($user_data);
						$name = $user_details['username'];
						
						$email=$user_details['email'];
						
						$first_name=$user_details['first_name'];
						
						$last_name=$user_details['last_name'];
						
						$address=$user_details['address'];
						
						$city=$user_details['city'];
						
						$state=$user_details['state'];
						
						$zipcode=$user_details['zip_code'];
						
						$name = $first_name." ".$last_name;

						$user_county=get_county($user_details['zip_code']);
						
						$reseller = $user_details['issalestaxreseller']>0?$user_details['issalestaxreseller']:'';
						

                       //$buyer_fee =  fetch_auction('buyer_fee',$res['projectid']);
					   
						
						$query = $ilance->db->query("
										SELECT i.disount_val,i.shipping_cost,sum(p.amount-p.taxamount) as buyerfee FROM ".DB_PREFIX."invoice_projects i
										left join ".DB_PREFIX."invoices p on p.projectid=i.project_id and p.user_id='".$res['user_id']."' and p.isbuyerfee=1
										where i.final_invoice_id ='".$res['invoiceid']."'
										");
						$row = $ilance->db->fetch_array($query);
						
						$shippin_amt = $row['shipping_cost'];
						$buyer_fee = $row['buyerfee']>0?$row['buyerfee']:0;
						$discount_amt = $row['disount_val'];
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
$headings=array('Invoice ','Created_Date','Paid_Date','Sub_Total','Buyers_Fee','Shipping','Sales_Tax','Discount','Total_Order','Reseller','County','Name','Address','City','State','Zip_Code');
$reportoutput= $ilance->admincp->construct_csv_data($data, $headings);
$headings=array('County','Amount','Sub_Total');
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
				header('Content-Disposition: attachment; filename="'.'Bug#4713_'.date('Y-m-d h-i-s').'.csv"');
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