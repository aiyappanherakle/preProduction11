<?php
require_once('../functions/config.php');
//error_reporting(E_ALL);
if (empty($_SESSION['ilancedata']['user']['userid']) or $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo 'login';
exit;
}
$total_paid=0;
$invoice_id = $ilance->GPC['id'];
$sql=$ilance->db->query("select user_id,combine_project,taxamount,taxinfo,paid,miscamount,totalamount,status,createdate,paiddate,amount  from " . DB_PREFIX . "invoices where invoiceid='".$invoice_id."'");
while($line=$ilance->db->fetch_array($sql))
{
$invoice_list=explode(",",$line['combine_project']);
$buyer_id=$line['user_id'];

/*if($line['paiddate']=='0000-00-00 00:00:00')
{
$invoice_datetime=$line['createdate'];
$invoice_date_text="DATE CREATED";
}else
{*/
$invoice_datetime=$line['paiddate'];
$invoice_date_text="DATE PAID";
/*}*/
$invoice_created_datetime=$line['createdate'];
$inv_total=$line['amount'];
$totalamount_in_no=$line['totalamount'];

$totalamount=$ilance->currency->format($line['totalamount']);
$show['miscamount']=$line['miscamount']>0?true:false;
$miscamount=$ilance->currency->format($line['miscamount']);
$taxinfo=$line['taxinfo'];
// murugan changes on apr 25
$show['taxamount'] = true;
	if($line['taxamount']>0)
	{
		$show['taxamount'] = true;
	$taxamount=$ilance->currency->format($line['taxamount']);
	}
	else
	{
	$show['taxamount'] = false;
		$taxamount = '-';
	}

	if($line['status']=='paid')
	{
	$total_paid=$line['totalamount'];
	$total_due_amount=0;
	}
	else
	{
	$total_paid=$line['paid'];
	$total_due_amount=$line['amount']+$line['taxamount'];
	}
	if($line['paid']>0)
	{
		$query="SELECT *  FROM ".DB_PREFIX."partial_payment WHERE invoiceid = '".$ilance->GPC['id']."' ORDER BY paymentdate";
		$result=$ilance->db->query($query);
		$partial_payment_html='';
		$show_pending=false;
		if($ilance->db->num_rows($result))
		{
		$partial_payment_html='<tr >
				   <td  width="100%"><table>
				   <tr><td colspan="3" family="helvetica" size="10"><strong>Partial Payments Made</srtong></td></tr>
				   <tr><td family="helvetica" size="10">Payment date</td><td family="helvetica" size="10">Amount</td><td family="helvetica" size="10">Payment Method</td></tr>';
			while($line=$ilance->db->fetch_array($result))
			{
			$show_pending=true;
			$partial_payment_html.='<tr>';
				$line['payment_date']=print_date($line['paymentdate']);
				$partial_payment_html.='<td family="helvetica" size="10">'.$line['payment_date'].'</td>';
				$partial_payment_html.='<td family="helvetica" size="10">'.$ilance->currency->format($line['partial_amount']).'</td>';
				$partial_payment_html.='<td family="helvetica" size="10">'.$line['paymethod'].'</td>';
			$partial_payment_html.='</tr>';
			}
		$partial_payment_html.='</table></td></tr>';
		}
	}

}
$item_count=count($invoice_list);
if(count($invoice_list))
{
$inv = '';
foreach($invoice_list as $each_invoice)
{
if($inv == '')
{
	$inv .= $each_invoice;
}
else
{
	$inv .= ','.$each_invoice;
}

}
$tot=0;
//$sql=$ilance->db->query("select * from ".DB_PREFIX."invoices where invoiceid='".$each_invoice."'");
$sql=$ilance->db->query("select * from ".DB_PREFIX."invoices where invoiceid  in ($inv) ORDER BY projectid ASC ");
while($line1=$ilance->db->fetch_array($sql))
	{
	$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders WHERE invoiceid = '".$line1['invoiceid']."'");
		if($ilance->db->num_rows($buy)>0)
		{
			$resbuy = $ilance->db->fetch_array($buy);
			 $quantity = $resbuy['qty'];
			$line1['type']='Buynow';
		}
		else
		{
			$line1['type']='Auction';
			$quantity =1;
		}
		$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices
								WHERE projectid = '".$line1['projectid']."'
								AND isbuyerfee = '1'");
					if($ilance->db->num_rows($buyfee_inv) > 0)
					{
						$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
						$buyerfee1 = $res_buyfee['amount'];
						$buyerfees = $buyerfee1;


					}
					else
					{
						$buyerfee1 = 0;
						$buyerfees= $buyerfee1;
					}


		//item details
		$itemid= $line1['projectid'];

		$amount=$ilance->currency->format($line1['amount']);
		 $total = ($buyerfees + $line1['amount']);

		 $totalnew[] = ($buyerfees + $line1['amount']);
		 $tot = $ilance->currency->format($total);
		 if($buyerfees > 0)
		 {
		 $buyerfees = $ilance->currency->format($buyerfees);
		 }
		 else
		 {
		 	$buyerfees = '-';
		 }
		 //echo $saleamt = " .(array_sum($total)). ";

		$title=$ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '".$line1['projectid']."'", "Title");
		$item=str_replace('★','*',$title);
		
		//For Bug #4961
		$pcgs_no = '';
		// echo "123 = ".$line1['user_id'];;exit;
		if($buyer_id == 28 || $buyer_id == 82)
		{
			$pcgsno=$ilance->db->fetch_field(DB_PREFIX . "coins", "coin_id = '".$line1['projectid']."'", "pcgs");
			$pcgs_no = '<td  family="helvetica" size="10">'.$pcgsno.'</td>';
		}
		//
		       $invoiceid = $line1['invoiceid'];

			  $html.= '<tr>
  <td  family="helvetica" size="10">'.$quantity.'</td><td nowrap family="helvetica" size="10">'.$itemid.'</td><td  family="helvetica" size="10">'.$item.'</td>'.$pcgs_no.'<td nowrap family="helvetica" size="10">'.$amount.'</td><td nowrap family="helvetica" size="10">'.$buyerfees.'</td><td nowrap family="helvetica" size="10">'.$tot.'</td>
  </tr>';
		//$invoicelist[]=$line1;
		$r_temp+=$total;
	}


// murugan changes on apr 25
 $r_temp = $ilance->currency->format($r_temp);
$sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($buyer_id) . "'", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
			$res = $ilance->db->fetch_array($sql);
			 $firstname = $res['first_name'];
			 $lastname = $res['last_name'];
			 $address = $res['address'];
			 $address2 = $res['address2'];
			$city = $res['city'];
			$state = $res['state'];
			$zip_code = $res['zip_code'];
            $phone = $res['phone'];
            $res['country'] = print_user_country($buyer_id);
			$country = $res['country'];
			$username = $res['username'];
			$email = $res['email'];
			//$buyerdetail[]=  $res;
        }

$sql=$ilance->db->query("select * from " . DB_PREFIX . "invoice_projects where final_invoice_id='".$invoice_id."'");
if($ilance->db->num_rows($sql))
{
while($line1=$ilance->db->fetch_array($sql))
{
	$shipper_id=$line1['shipper_id'];
	$shippping_cost=$line1['shipping_cost'];
	$show['discount'] = true;
	 if($line1['disount_val']=='')
	 {
	 	$show['discount'] = false;
	 	$discount='-';
	 }
	 else
	 {
	 	if($line1['disount_val']>0)
		{
			$show['discount'] = true;
	  		$discount=sprintf("%01.2f",$line1['disount_val']);
		}
		else
		{
			$show['discount'] = false;
			$discount='-';
		}
	 }

}
}else
{
$shipper_id==22;
}
$coin_count=$ilance->db->num_rows($sql);
$sql_shipper_detail=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='".$shipper_id."'");
if($ilance->db->num_rows($sql_shipper_detail))
{
	while($line_shipper=$ilance->db->fetch_array($sql_shipper_detail))
	{
		$shippername="<strong>".$line_shipper['title']."</strong>";
		$shipper_title="<strong>".$line_shipper['title']."</strong>";
		$shipper_title_upgraded=$line_shipper['title'];
		$base_cost=$line_shipper['basefee'];
		$added_cost=$line_shipper['addedfee'];
	}
}
$test = ($base_cost)+(($coin_count-1)*$added_cost);
//$shippping_cost=$ilance->currency->format(($base_cost)+(($coin_count-1)*$added_cost));
if($coin_count>1)
$shipping_cost_detail=" 1 Coin * ".$base_cost." + ".($coin_count-1)." Coins * ".$added_cost;
else
$shipping_cost_detail=" 1 Coin  ".$base_cost;
$shipper_title.=$shipping_cost_detail;
$payment_methods=print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid']);

}
else
{

}

//for Bug #4961
$pcgs_header = '';
if($buyer_id == 28 || $buyer_id == 82)
{
	$pcgs_header = '<td nowrap color="#ffffff" family="helvetica" size="10">PCGS</td>';
}
  

$upgrade_shipper=is_shipper_upgraded($buyer_id	,$shipper_id ,$totalamount_in_no-$shippping_cost);
	 if($upgrade_shipper==true)
	 {
	 $added_html='<tr >
				   <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">Upgraded Shipping: </td>
				   <td  align="left"  nowrap family="helvetica" size="10">'.$shipper_title_upgraded.'</td>
				  </tr> ';
	 }else
	 {
	 $added_html="";
	 }
	$table1 = '
	<table   border="0">
  <tr>
    <td size="20" family="helvetica" style="bold" nowrap><b>GreatCollections.com, LLC</b></td>

  </tr>
  <tr>
    <td valign="top" size="10" family="helvetica" >2030 Main Street, Suite 620, Irvine, CA 92614<br>
      Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
    E-mail: info@greatcollections.com</td>
    <td >&nbsp;</td>
    <td >&nbsp;</td>

    Invoice #: '.$invoice_id.'</td>

  </tr>
  <tr>
    <td  ></td>

  </tr>
  <tr>
     <td family="helvetica" size="10">Username: '.$username.'
	 </td>
  </tr>

  <tr>
     <td family="helvetica" size="10">Email: '.$email.'
	 </td>
  </tr>

  <tr>
    <td  family="helvetica">Bill To:<br><br>
	'.$firstname.' '.$lastname.' <br> '.$address.', '.$address2.'<br> '.$city.', '.$state.' '.$zip_code.' <br>'.$country .' <br>Phone: '.$phone.'
</td>
     <td  family="helvetica">Ship To:<br><br>
'.$firstname.' '.$lastname.' <br> '.$address.', '.$address2.'<br> '.$city.', '.$state.' '.$zip_code.' <br>'.$country .' <br>Phone: '.$phone.'	</td>
  </tr>



</table>
<table width="100%">
 <tr >
  <td>&nbsp;</td>
  </tr><tr >
  <td>&nbsp;</td>
  </tr>
 <tr bgcolor="#333333">
  <td nowrap color="#ffffff" family="helvetica" size="10"></td><td nowrap color="#ffffff" family="helvetica" size="10"></td><td nowrap color="#ffffff" family="helvetica" size="10">SHIP VIA</td><td nowrap color="#ffffff" family="helvetica" size="10">Total Items</td><td nowrap color="#ffffff" family="helvetica" size="10">PPD</td> <td nowrap color="#ffffff" family="helvetica" size="10"></td><td nowrap color="#ffffff" family="helvetica" size="10">'.$invoice_date_text.'</td>
  </tr>

  <tr>
  <td nowrap family="helvetica" size="10"></td><td nowrap family="helvetica" size="10"></td><td nowrap family="helvetica" size="10">'.$shippername.'</td><td nowrap family="helvetica" size="10">'.$item_count.'</td><td nowrap family="helvetica" size="10">PPD</td> <td nowrap family="helvetica" size="10"></td><td nowrap family="helvetica" size="10">'.$invoice_datetime.'</td>
  </tr>
  </table>
<table width="100%">
  <tr bgcolor="#333333">
  <td nowrap color="#ffffff" family="helvetica" size="10">QTY.</td>
  <td nowrap color="#ffffff" family="helvetica" size="10">ITEM NO.</td>
  <td nowrap color="#ffffff" family="helvetica" size="10" width="60%">DESCRIPTION</td>
  '.$pcgs_header.'
  <td nowrap color="#ffffff" family="helvetica" size="10">PRICE</td>
  <td color="#ffffff" family="helvetica" size="10">BUYER FEE</td>
  <td nowrap color="#ffffff" family="helvetica" size="10">TOTAL</td>
  </tr>
<tr>
<td>
'. $html.'
</td>
</tr>
</table>
<table width="100%" border="0" >
<tr >
<td  width="120%"></td><td  align="right"  nowrap color="#000000" family="helvetica" size="10">SALE AMT : </td><td  align="left"  nowrap family="helvetica" size="10">'.$ilance->currency->format(array_sum($totalnew)).'</td>
  </tr>
<tr >
  <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">SHIPPING : </td><td  align="left"  nowrap family="helvetica" size="10">'. $ilance->currency->format($shippping_cost).'</td>
  </tr>';
  if($show['taxamount']== true)
  {
  $table1 .='

  <tr >
  <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">TAX: </td><td  align="left"  nowrap family="helvetica" size="10">'.$taxamount.'</td>
  </tr>';
  }
  if($show['discount']== true)
  {
  $table1 .='

  <tr >
   <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">PROMOCODE: </td><td  align="left"  nowrap family="helvetica" size="10">'."US$".$discount.'</td>
  </tr>';
  }
  
  if($show['miscamount']== true)
  {
  $table1 .='

  <tr >
   <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">Misc Amount: </td><td  align="left"  nowrap family="helvetica" size="10">'.$miscamount.'</td>
  </tr>';
  }
  $table1 .='
  <tr >
   <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">TOTAL: </td><td  align="left"  nowrap family="helvetica" size="10">'.$totalamount.'</td>
  </tr>
  <tr >
   <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">PAID: </td><td  align="left"  nowrap family="helvetica" size="10">'.$ilance->currency->format($total_paid).'</td>
  </tr>';
  if($show_pending)
  {
    $table1 .='
	  <tr >
	   <td  width="100%"></td> <td align="right"  nowrap color="#000000" family="helvetica" size="10">Amount Due: </td><td  align="left"  nowrap family="helvetica" size="10">'.$ilance->currency->format($total_due_amount).'</td>
	  </tr>';
  }
  $table1 .=$added_html.$partial_payment_html.'</table>	';
//echo $table1;

define('FPDF_FONTPATH','../font/');
require('pdftable_1.9/lib/pdftable.inc.php');
$p = new PDFTable();
$p->AddPage();
$p->setfont('times','',10);
$p->htmltable($table1);
//echo $shipper_id.'|'.$totalamount_in_no;
$p->output('invoice'.DATETIME24H.'.pdf','D');



function is_shipper_upgraded($user_id,$shipper_id,$invoice_total)
{
	 global $ilance;

	 $country_id=fetch_user('country',$user_id);
	 //if international
	 if($country_id!=500 and $shipper_id!=22 and $invoice_total<10000)//USPS International Priority
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=25 and $invoice_total>10000)//USPS Express Mail
	 {
	 return true;
	 }elseif($country_id==500 and $shipper_id!=27 and $invoice_total>1000 and  $invoice_total<=10000)//USPS Priority Mail
	 {
	 if($shipper_id!=26)
	 return true;
	 }elseif($country_id==500 and $shipper_id!=26 and $invoice_total<=1000)//USPS First Class Mail
	 {
	 return true;
	 }
	 return false;
}

?>