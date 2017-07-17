<?php

require_once('./../functions/config.php');

if (empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] != '1')

{

echo "login to cont";

exit;

}

//error_reporting(E_ALL);

//sprt order by catalog and coinid

unset($total_seller);

unset($total_listing);

unset($total_final);

unset($total_net_consignor);

unset($total_buynow);

unset($total_hammer);



define('FPDF_FONTPATH','../font/');

require('pdftable_1.9/lib/pdftable.inc.php');

$p = new PDFTable();

$p->AddPage();

$p->setfont('times','',10);	



if($ilance->GPC['start_date'] == '')

{

	echo "enter start and end date";

	exit;

}



//01-01-2012

//if from date picker

$ilance->GPC['start_date']=alter_date($ilance->GPC['start_date']);



$buynow_end_date=get_buynow_relist_end_date($ilance->GPC['start_date']);



$buynow_start_date=last_monday($buynow_end_date);

$ilance->GPC['end_date']=$buynow_start_date;

if(isset($ilance->GPC['user_id']))

$user_where='where user_id='.$ilance->GPC['user_id'];



$select = $ilance->db->query("SELECT distinct user_id from ".DB_PREFIX."consignments ".$user_where." order by user_id");

//user consignment deatils

$cou=$ilance->db->num_rows($select);

if($ilance->db->num_rows($select) > 0)

{

while( $res_user = $ilance->db->fetch_array($select))

{



	

$table1=get_header_pdf($res_user['user_id'],$buynow_end_date);

unset($totins);

unset($totfvf);

unset($test4);

unset($test5);

unset($totfinal);

 

	unset($coins_list);

	$cointable_query=$ilance->db->query("select coin_id from ".DB_PREFIX."coins where date(End_Date) <= date('".$buynow_end_date."') and date(End_Date) >= date('".$buynow_start_date."') AND user_id='".$res_user['user_id']."'");

	

	if($ilance->db->num_rows($cointable_query))

	{

		while($line_coin=$ilance->db->fetch_array($cointable_query))

		{

			$coins_list[]=$line_coin['coin_id'];

		}

	}

	$relisttable_query=$ilance->db->query("select coin_id from ".DB_PREFIX."coin_relist where date(enddate) <= date('".$buynow_end_date."') and date(enddate) >= date('".$buynow_start_date."') AND user_id='".$res_user['user_id']."'");

	

	if($ilance->db->num_rows($relisttable_query))

	{

		while($line_relist=$ilance->db->fetch_array($relisttable_query))

		{

			$coins_list[]=$line_relist['coin_id'];

		}

	}

 

if(count($coins_list)>0)

{

$user_coin_list = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coins WHERE coin_id in (".implode(',',$coins_list).") AND project_id!=0 AND user_id='".$res_user['user_id']."'  GROUP BY coin_id ORDER BY coin_id");







if($ilance->db->num_rows($user_coin_list)>0)

{

$i=0;

$listcount=$ilance->db->num_rows($user_coin_list);

while($res=$ilance->db->fetch_array($user_coin_list))

{



	$selectbid = $ilance->db->query("SELECT MIN(bidamount) AS bidamount, MAX(bidamount) AS final,count(*) AS count FROM ".DB_PREFIX."project_bids WHERE project_id = '".$res['project_id']."'");

$result = $ilance->db->fetch_array($selectbid, DB_ASSOC);

 // AND date(date_awarded)>='".$ilance->GPC['start_date']."' and date(date_awarded)<='".$ilance->GPC['end_date']."'

  	$selectbin =$ilance->db->query("SELECT SUM(amount) AS binamount, SUM(qty) AS qty FROM ".DB_PREFIX."buynow_orders 

	WHERE project_id = '".$res['project_id']."' AND date(orderdate)>='".$buynow_start_date."' and date(orderdate)<='".$buynow_end_date."'");

$result1 = $ilance->db->fetch_array($selectbin, DB_ASSOC);



	$selectpjt = $ilance->db->query("SELECT insertionfee, fvf, featured, highlite, bold FROM ".DB_PREFIX."projects			

	WHERE project_id = '".$res['project_id']."'");

$resultpjt = $ilance->db->fetch_array($selectpjt, DB_ASSOC);



//echo $res['project_id'].'='.$result1['binamount'].''.$selectbid['bidamount'].'<br>';

if($result1['binamount']>0)

{

//only if buynow

	$selectinvoice = $ilance->db->query("SELECT SUM(amount) AS newfvf FROM ".DB_PREFIX."invoices			

	WHERE projectid = '".$res['project_id']."' AND isfvf = '1'");



	$item_buynow=true;

}else

{

	//if it is not buynow

$selectinvoice = $ilance->db->query("SELECT SUM(amount) AS newfvf FROM ".DB_PREFIX."invoices
								WHERE projectid = '".$res['project_id']."' AND isfvf = '1' ");

$item_buynow=false;	

}

$resultinvoice = $ilance->db->fetch_array($selectinvoice, DB_ASSOC);



	$not_all_invoices_paid=false;

	$selectdisp = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices

	WHERE projectid = '".$res['project_id']."' AND date(createdate)>='".$ilance->GPC['start_date']."' and invoicetype='escrow' and date(createdate)<='".$ilance->GPC['end_date']."'");								

	while($resultdisp = $ilance->db->fetch_array($selectdisp, DB_ASSOC))

	{



	if($resultdisp['status']!='paid')

	{

		$not_all_invoices_paid=true;

		continue;

	}

	}

	// murugan changes on jun 24 								



	$enhancementfee = $ilance->db->query("SELECT SUM(amount) AS newenhance FROM ".DB_PREFIX."invoices			

	WHERE projectid = '".$res['project_id']."' AND isenhancementfee = '1'  AND date(createdate)>='".$ilance->GPC['start_date']."' and date(createdate)<='".$ilance->GPC['end_date']."'");

	

	

	$disp='';

	//karthik on july22 for display *

	if($not_all_invoices_paid==false)

	$disp='<font color="#FF0000">*</font>';





	// murugan june 24

	$resenhancementfee = $ilance->db->fetch_array($enhancementfee, DB_ASSOC);



	// miscellaneous Calculatation Murugan on jun 4

	$misselect = $ilance->db->query("SELECT amount,invoicetype FROM ". DB_PREFIX ."invoices

	WHERE user_id ='".$res_user['user_id']."' AND projectid = '".$res['project_id']."' AND ismis = 1 ");

	if ($ilance->db->num_rows($misselect) > 0)

	{

		$resmis = $ilance->db->fetch_array($misselect, DB_ASSOC);

		//murugan july 7

		if($resmis['invoicetype'] == 'debit')

		{

			$misdebit[] = $resmis['amount'];

		}

		if($resmis['invoicetype'] == 'credit')

		{

			$miscredit[] = $resmis['amount'];

		}							

		$miscell[] = $resmis['amount'];

		//$misamt = $ilance->currency->format($resmis['amount'],$ilconfig['globalserverlocale_defaultcurrency']);

	}

	else

	{

		$miscell[] = 0;

		$miscredit[] = 0;

		$misdebit[] = 0;

		//$misamt = $ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']);

	}		

	// Featured fee Amount

	$featured=$resultpjt['featured'] !=0?$ilconfig['productupsell_featuredfee']:'0.00';



	// highlite fee amount

	$highlite = $resultpjt['highlite'] !=0 ? $ilconfig['productupsell_highlightfee']:'0.00';



	// bold fee amount

	$bold = $resultpjt['bold'] !=0 ? $ilconfig['productupsell_boldfee']:'0.00';



	// Total Amount (insertionfee , bold,highlight,featured)

	if($res['relist_count'] == 0)

	{

		$resultpjt['insertionfee'] = $resultpjt['insertionfee'];

	}

	else

	{

		$resultpjt['insertionfee'] = 0;

	}

	

	$listfeetotal = $resultpjt['insertionfee'] + $resenhancementfee['newenhance'];

	$totfvf[$i]= $resultinvoice['newfvf'];

	$totins[$i]= $resultpjt['insertionfee'] + $resenhancementfee['newenhance'];

	$res['bids']= $result['count'];

	$bidtot[$i]= $result['count'];

	if($res['Minimum_bid'] != '')

	{						

		

		$res['bidamount'] = $ilance->currency->format($res['Minimum_bid'],$ilconfig['globalserverlocale_defaultcurrency']);

	}

	else

	{

		$res['bidamount'] = '0.00';

	}

	if($res['Buy_it_now'] != '')

	{	

	//suku to add a qty condition here					

		$test4[$i]= $res['Buy_it_now']*$result1['qty'];

		$res['binamount']  = $ilance->currency->format($res['Buy_it_now'],$ilconfig['globalserverlocale_defaultcurrency']);

	}

	else

	{

		$res['binamount']  = '0.00';

	}

 

	if($result['final'] != '')

	{

		$res['finalprice'] = $result['final'];

		$res['qty'] = '';

		$res['final_price'] = $ilance->currency->format($result['final'],$ilconfig['globalserverlocale_defaultcurrency']);

		$test5[$i]= $result['final'];

	}

	else

	{

		$res['finalprice'] = $result1['binamount'];

		if($result1['qty'] > 1)

		$res['qty'] = '<b>('.$result1['qty'].')</b>';

		else

		$res['qty'] = '';

		$res['final_price'] = $ilance->currency->format($result1['binamount'],$ilconfig['globalserverlocale_defaultcurrency']);

	}	 

	// Total Final price

	$totfinal[$i] = $res['finalprice'];

	

	//murugan july 7

	$mis_totdebit = array_sum($misdebit);

	$mis_totcredit = array_sum($miscredit);

	$miscellan = array_sum($miscell);

	// murugan july 21

	//$mis_total =  $mis_totcredit - $mis_totdebit;

	$mis_total = $mis_totdebit - $mis_totcredit;

	$tot_mis = $mis_total > 0?$ilance->currency->format($mis_total,$ilconfig['globalserverlocale_defaultcurrency']):'US$'.number_format(abs($mis_total), 2, '.', '');

	

	$res['seller_fee'] = $ilance->currency->format($resultinvoice['newfvf'],$ilconfig['globalserverlocale_defaultcurrency']);

	$res['listing_fee'] = $ilance->currency->format($listfeetotal,$ilconfig['globalserverlocale_defaultcurrency']);

	if($result['bidamount'] != '')

	{							

		$res['net_consignor1'] = $result['final'] - ( $resultinvoice['newfvf'] + $listfeetotal) ;

		if($res['net_consignor1'] > 0)

			$res['net_consignor'] = $disp.$ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);

		else

			$res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';

	}

	if($result1['binamount'] != '')

	{

		$res['net_consignor1'] = $result1['binamount'] - ( $resultinvoice['newfvf'] + $listfeetotal);

		if($res['net_consignor1'] > 0)

			$res['net_consignor'] =$disp.$ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);

		else

			$res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';

	}

	if($result['bidamount'] == '' AND $result1['binamount'] == '' )

	{

		$res['net_consignor1'] =  ($resultinvoice['newfvf'] + $listfeetotal);

		if($res['net_consignor1'] > 0)

			$res['net_consignor'] = '- US$'.$res['net_consignor1']. '.00';

		else

			$res['net_consignor'] = 'US$0.00';

	}

	$test[$i] = $res['net_consignor1'];

	//$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);

	if($res['Site_Id'] == '0')

	{

	$res['Site_Id'] ='GC';

	$res['Title'] = '<a href="'.$ilpage['merch'].'?id='.$res['coin_id'].'">'.$res['Title'].'</a>';

	$res['stateid'] = '<a href="'.$ilpage['merch'].'?id='.$res['coin_id'].'">'.$res['coin_id'].'</a>';

	}

	else

	{

		$sitesel = $ilance->db->query("

		SELECT site_name FROM ".DB_PREFIX."affiliate_listing				

		WHERE id = '".$res['Site_Id']."'					

		");

		$siteres = $ilance->db->fetch_array($sitesel, DB_ASSOC);

		$res['Site_Id'] =$siteres['site_name'];

		$res['Title'] = $res['Title'];

		$res['stateid'] = $res['coin_id'];

	}

	$bgcolor=($item_buynow)?'#cccccc':'#FFFFFF';

	$table1.='<tr style="background:'.$bgcolor.'">

	<td>'.$res['coin_id'].'</td>

	<td width = "45%">'.$res['Title'].' <br> Cert #:'.$res['Certification_No'].' <br> Alt. Inv #:'.$res['Alternate_inventory_No'].' </td>

	<td>'.$res['Site_Id'].'</td>

	<td>'.$res['bids'].'</td>

	<td>'.$res['bidamount'].'/<br>'.$res['binamount'].'</td>

	<td>'.$res['final_price'].'</td>

	<td>'.$res['listing_fee'].'</td>

	<td>'.$res['seller_fee'].'</td>	

	<td>'.$res['net_consignor'].'</td>

	</tr>';

	$i++;



	

$item_buynow=false;

}



$advanceselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "user_advance WHERE statusnow = 'paid' AND user_id ='".$res_user['user_id']."' ");

$advanceres = $ilance->db->fetch_array($advanceselect);

$sum_inset = array_sum($totins);

$sum_finalvaluefe = array_sum($totfvf);

$sum_totfinalval = array_sum($totfinal);

$newnettotal = $sum_totfinalval - $sum_finalvaluefe - $sum_inset;				

//$totnet_consignor = $ilance->currency->format(array_sum($test),$ilconfig['globalserverlocale_defaultcurrency']);

//$totnet_consignor = 'US$'.$newnettotal;

$totnet_consignor = $newnettotal;

//$totseller_fee = $ilance->currency->format(array_sum($test1),$ilconfig['globalserverlocale_defaultcurrency']);

$totseller_fee = array_sum($totfvf);

$total_seller[]=array_sum($totfvf);

$totlisting_fee = array_sum($totins);

$totfvf = array_sum($totfinal);

 

$totbinamount =  array_sum($test4) ;

 

$totbidamount = array_sum($test5);

$totbids = array_sum($bidtot);				

$total_advance = $advanceres['amount'];

$total_listing[]=array_sum($totins);

$total_final[]=array_sum($totfinal);



$total_buynow[]=$totbinamount;

$total_hammer[]=$totbidamount;





$total_net_consignor[]=$newnettotal;

// murugan FEB 23

//murugan july 7

$mis_totdebit = array_sum($misdebit);

$mis_totcredit = array_sum($miscredit);

$miscellan = array_sum($miscell);

// murugan july 21

//$mis_total =  $mis_totcredit - $mis_totdebit;

$mis_total = $mis_totdebit - $mis_totcredit;

if($mis_total > 0)

{

	$tot_mis = $ilance->currency->format($mis_total,$ilconfig['globalserverlocale_defaultcurrency']);

}

else

{

	$tot_mis = 'US$'.number_format(abs($mis_total), 2, '.', '');

}	

//$lastamountvalue = array_sum($test) - $advanceres['amount'];

// murugan changes on july 21

//$lastamountvalue = $newnettotal - $advanceres['amount'];

$lastamountvalue = $newnettotal - $advanceres['amount'] - $mis_totcredit + $mis_totdebit;

$lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);

//$lastamount = 'US$'.$lastamountvalue;

//$statecount = '('.$listcount.' items) will settle on '.$settledate .' ('.$lastamount.')';

//default amount for Miscellaneous and Paid

$default = 0;

$def =   $ilance->currency->format($default,$ilconfig['globalserverlocale_defaultcurrency']);

}

 



//suku removed buynow from here

if($listcount>0)

{

$table1.='<tr><td>&nbsp;</td></tr><tr><td>Gross Total</td><td ></td><td ></td><td ></td><td ></td><td nowrap><b>'.$ilance->currency->format($totfvf,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td><td nowrap><b>'.$ilance->currency->format($totlisting_fee,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td><td nowrap><b>'.$ilance->currency->format($totseller_fee,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td><td nowrap><b>'.$ilance->currency->format($totnet_consignor,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td></tr><tr ><td>&nbsp;</td></tr><tr><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><tdnowrap>Advance </td><td></b></td><td></b></td><td nowrap><b>'.$ilance->currency->format($total_advance,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td></tr><tr><tdnowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap>Miscellaneous </td><td></b></td><td></b></td><td nowrap><b>'.$ilance->currency->format($tot_mis,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td></tr><tr><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><tdnowrap></td><td nowrap>Paid </td><td></b></td><td></b></td><td nowrap><b>'.$ilance->currency->format($def,$ilconfig['globalserverlocale_defaultcurrency']) .'</b></td></tr><tr><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><td nowrap></td><tdnowrap>Balance </td><td></b></td><td></b></td><td nowrap><b>'.$ilance->currency->format($lastamount,$ilconfig['globalserverlocale_defaultcurrency']).'</b></td></tr></table>';

$table1.='<table width="100%"><tr><td>Thank you for consigning to GreatCollections.<br>We appreciate your business.</td></tr></table>';



$p->htmltable($table1);	

$p->AddPage();

$p->setfont('times','',10);		

}

$listcount=0;

}



 

}

}

$table2='<table width="100%">';

$table2.='

<tr><td>Total</td></tr>

<tr>

<td>Total FinalPrice</td>

<td nowrap><b>'.$ilance->currency->format(array_sum($total_final),$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>

</tr><tr>

<td>Total Buy Now</td>

<td nowrap><b>'.$ilance->currency->format(array_sum($total_buynow),$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>

</tr><tr>

<td>Total Hammer Price </td>

<td nowrap><b>'.$ilance->currency->format(array_sum($total_hammer),$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>

</tr>

<tr>	

<td>Total Listing Fees</td>

<td nowrap><b>'. $ilance->currency->format(array_sum($total_listing),$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>

</tr>

<tr>		

<td>Total Seller Fees</td>

<td nowrap><b>'.$ilance->currency->format(array_sum($total_seller),$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>

</tr>

<tr>		

<td>Total Net to Consignor</td>

<td nowrap><b>'.$ilance->currency->format(array_sum($total_net_consignor),$ilconfig['globalserverlocale_defaultcurrency']).'</b></td>

</tr>';

$table2.='</table>';

$p->htmltable($table2);	

//$p->AddPage();

$p->setfont('times','',10);				

$timeStamp = date("Y-m-d-H-i-s");



/*$pdf->Output(''.$name.'_Consignment_Report_'.$orderby.'.pdf', 'D'); 		*/

try

{

$p->output('Statement_'.$timeStamp.'.pdf','D');

}catch(Exception $e)

{

echo $table1.$table2;

}

//echo $table1.$table2;





function get_header_pdf($user_id,$end_date)

{

global $ilance;

$FETCH_USER=$ilance->db->fetch_array($ilance->db->query("SELECT * from ".DB_PREFIX."users where user_id='".$user_id."'"));

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

2030 Main Street, Suite 620, Irvine, CA 92614-7290<br>

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

</table><table width="100%">

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



function get_buynow_relist_end_date($anydate)

{

	list($y,$m,$d)=explode("-",$anydate);

	 $h = mktime(0, 0, 0, $m, $d, $y);

	 $w= date("w", $h) ;

	if($w!=0)

	{

	$rest_sec=(7-$w)*24*60*60;

	$near_sunday=date("Y-m-d",$h+$rest_sec);

	}else

	return $anydate;

	return $near_sunday;

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

function alter_date($somedate)

{

list($m,$d,$y)=explode("-",$somedate);

return $y.'-'.$m.'-'.$d;

}

?>