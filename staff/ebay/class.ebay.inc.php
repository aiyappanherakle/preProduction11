<?php 
class ebay
{
var $html='';
var $referer_Page='';
var $coin;
var $ebay;
var $sale;
function import_sales()
{
//	$this->get_access_rules();
//	exit;
	global $ilance, $myapi, $ilconfig, $reportdate;
	global $coin,$ebay;
	$query1="SELECT id as ebay_listing_id,coin_id,ebay_id,listedon,DATE_ADD(listedon,INTERVAL 30 DAY) as  listed_date_plus_thirty_days,end_date , DATE_ADD(end_date,INTERVAL 30 DAY) as  end_date_plus_thirty_days,user_id,item_type,quantity FROM 
		" . DB_PREFIX . "ebay_listing where status='listed' ";
	//195796
	//buynow 234490,201332
	//
	$query1="SELECT id as ebay_listing_id,coin_id,ebay_id,listedon,DATE_ADD(listedon,INTERVAL 30 DAY) as  listed_date_plus_thirty_days,end_date , DATE_ADD(end_date,INTERVAL 30 DAY) as  end_date_plus_thirty_days,user_id,item_type,quantity FROM 
		" . DB_PREFIX . "ebay_listing where coin_id in (195820)";
	
	$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($sql1))
	{
		while($line1 = $ilance->db->fetch_object($sql1))
		{ 
			$this->resetobj($coin);			
			$coin=$line1;
			echo '<br>'.$line1->coin_id.'[<b>'.$line1->ebay_id.'</b>]'.$line1->end_date.'&nbsp;&nbsp;&nbsp;&nbsp;';
			$end_date_plus_thirty_days=$line1->end_date_plus_thirty_days;
			
			$responce=$this->get_responce();

			if(!$responce)
			{
				//error message have to email the developer
				echo $this->html;
				echo '<br>';
			}else
			{
				//echo 'test';
			}
		}
	}
   return 'cron to import ebay sales done';
}


function get_access_rules()
{
 global $devID, $appID, $certID, $serverUrl, $userToken,$compatabilityLevel,$coin,$ebay;
	$siteID = 0;
	$verb = 'GetApiAccessRules';
	$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
	$requestXmlBody .= '<'.$verb.'Request xmlns="urn:ebay:apis:eBLBaseComponents">'; 
	$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
	$requestXmlBody .= '</'.$verb.'Request>';
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
	$responseXml = $session->sendHttpRequest($requestXmlBody);
	if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
	{	
		$this->send_notification('<P>Error sending request');
		die('<P>Error sending request');
	}
	$responseDoc = new DomDocument();
	$responseDoc->loadXML($responseXml);
	$errors = $responseDoc->getElementsByTagName('Errors');
	if($errors->length > 0)
	{
		
		//display each error
		//Get error code, ShortMesaage and LongMessage
		$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
		$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
		$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
		$classification  = $errors->item(0)->getElementsByTagName('ErrorClassification');
		if($code->item(0)->nodeValue!=17)
		{
		$this->html.='<P>'.$coin->ebay_id.'['.$coin->coin_id.']<B>eBay returned the following error(s):</B>';
		$this->html.='<P>'.$code->item(0)->nodeValue. ' : '. str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue)).'<BR>'. 
			str_replace(">", "&gt;", str_replace("<", "&lt;", $classification->item(0)->nodeValue));
		if(count($longMsg) > 0)
			$this->html.='<BR>'. str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
		}else
		{
		echo 'item deleted in ebay<br>';
		}
		return false;	
	}else
	{
        $responses = $responseDoc->getElementsByTagName($verb."Response");
		$xml = $responseDoc->saveXML($responseDoc);
		
		print_r($xml);
		//exit;
		//echo '<hr>';
		unset($responses);
	}


}
function get_responce()
{
	 global $devID, $appID, $certID, $serverUrl, $userToken,$compatabilityLevel,$coin,$ebay;
	 
  
	$this->resetobj($ebay);
	$siteID = 0;
	$verb = 'GetItemTransactions';
	$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
	$requestXmlBody .= '<'.$verb.'Request xmlns="urn:ebay:apis:eBLBaseComponents">'; 
	 
	if($coin->item_type=='regular')
	{
	$ModTime='<ModTimeFrom>'.$this->ebay_date($coin->end_date).'</ModTimeFrom>
	<ModTimeTo>'.$this->ebay_date($coin->end_date_plus_thirty_days).'</ModTimeTo>';
	}else
	{
	$ModTime='<ModTimeFrom>'.$this->ebay_date($coin->listedon).'</ModTimeFrom>
	<ModTimeTo>'.$this->ebay_date($coin->listed_date_plus_thirty_days).'</ModTimeTo>';
	}
						//$ModTime='';
	$requestXmlBody .= '<ItemID>'.$coin->ebay_id.'</ItemID>'.$ModTime;
	$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
	$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
	$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
	$requestXmlBody .= "<Version>$compatabilityLevel</Version>"; 
	$requestXmlBody .= '</'.$verb.'Request>';
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
	$responseXml = $session->sendHttpRequest($requestXmlBody);
	if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
	{	
		$this->send_notification('<P>Error sending request');
		die('<P>Error sending request');
	}
	$responseDoc = new DomDocument();
	$responseDoc->loadXML($responseXml);
	$errors = $responseDoc->getElementsByTagName('Errors');
	if($errors->length > 0)
	{
		
		//display each error
		//Get error code, ShortMesaage and LongMessage
		$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
		$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
		$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
		$classification  = $errors->item(0)->getElementsByTagName('ErrorClassification');
		if($code->item(0)->nodeValue!=17)
		{
		$this->html.='<P>'.$coin->ebay_id.'['.$coin->coin_id.']<B>eBay returned the following error(s):</B>';
		$this->html.='<P>'.$code->item(0)->nodeValue. ' : '. str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue)).'<BR>'. 
			str_replace(">", "&gt;", str_replace("<", "&lt;", $classification->item(0)->nodeValue));
		if(count($longMsg) > 0)
			$this->html.='<BR>'. str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
		}else
		{
		echo 'item deleted in ebay<br>';
		}
		return false;	
	}else
	{
        $responses = $responseDoc->getElementsByTagName($verb."Response");
		$xml = $responseDoc->saveXML($responseDoc);
		
		$this->parse_responce($responses);
		print_r($xml);
		//exit;
		//echo '<hr>';
		unset($responses);
	}
}

function parse_responce($responses)
{
global $ebay,$coin,$sale;

	foreach($responses as $response)
	{
		$this->resetobj($sale);	
		$this->form_values($response);
		$this->check_listing_status();
		
	}
}
function form_values($response)
{
global $ebay;

$ebay->ItemID					= $this->nodeval($response,"ItemID");
$ebay->ListingStatus			= $this->nodeval($response,"ListingStatus");
$ebay->ListingType				= $this->nodeval($response,"ListingType");
$ebay->project_id				= $this->nodeval($response,"SKU");
$ebay->sold_quantity			= $this->nodeval($response,"QuantitySold");
$this->referer_Page 			= $this->nodeval($response,"ViewItemURL");
$ebay->createdate				= $this->mysql_date($this->nodeval($response,"EndTime"));
$ebay->BuyerArray 				= $response->getElementsByTagName("Transaction");
$ebay->CurrentPrice				= $this->nodeval($response,"CurrentPrice");
}
function check_listing_status()
{
	global $ilance,$ebay,$coin;
	$this->get_transactions();
	
}

var $buyer_id=0;
function get_transactions()
{
	global $ebay,$coin,$sale; 
	if($ebay->BuyerArray->length>0)
	{
		foreach($ebay->BuyerArray as $buyer_detail)
		{
			$this->resetobj($sale);
			$this->form_sale_value($buyer_detail);
			$sale->buyer_id=$this->getbuyer_registered($buyer_detail);
			$this->create_invoice();
			$this->resetobj($sale);
		}
	}else
	{
		$this->update_ebay_listing();
	}
}



function form_sale_value($response)
{
global $sale,$ebay;

$sale->CurrentPrice 			= $ebay->CurrentPrice;	
$sale->Payment_details 			= $this->nodeval($response,"PaymentStatus")=='Succeeded'?'paid':'unpaid';
$sale->transaction_id			= $this->nodeval($response,"TransactionID");
$sale->QuantityPurchased 		= $this->nodeval($response,"QuantityPurchased");
$sale->ExternalTransactionID	= $this->nodeval($response,"ExternalTransactionID");
$sale->salestax_percent			= $this->nodeval($response,"SalesTaxPercent");
$sale->salestax					= $this->nodeval($response,"SalesTaxAmount");
$sale->shippingservice			= $this->nodeval($response,"ShippingService");
$sale->shippingcost				= $this->nodeval($response,"ShippingServiceCost");
$sale->AmountPaid				= $this->nodeval($response,"AmountPaid");
$sale->item_total				= $sale->CurrentPrice*$sale->QuantityPurchased;
$sale->paymentmethod			= $this->nodeval($response,"PaymentMethodUsed");
$sale->paiddate					= $this->mysql_date($this->nodeval($response,"PaidTime"));
$sale->PaymentTime				= $this->mysql_date($this->nodeval($response,"PaymentTime"));
$sale->CreatedDate				= $this->mysql_date($this->nodeval($response,"CreatedDate"));

}

function transaction_not_accounted($transaction_id,$ExternalTransactionID)
{
global $ilance,$coin;
$sql_transaction_part='';
	if($transaction_id!=0)	
		$sql_transaction_part="ebay_Transaction_id = '".$transaction_id."' or ";

	$query="SELECT * FROM " . DB_PREFIX . "ebay_listing_rows WHERE ( ".$sql_transaction_part." ExternalTransactionID='".$ExternalTransactionID."') and ebay_id='".$coin->ebay_id."'";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result)>0)
	{
	return false;	
	}else
	{
	return true;
	}
}
function create_invoice()
{
	global $coin,$ebay,$sale;
	if($this->transaction_not_accounted($sale->transaction_id,$sale->ExternalTransactionID))
	{
	$sale->invoiceid=$this->build_child_invoice(); 
	$fvf_invoice=$this->build_fvf_invoice();
	if($sale->invoiceid>0)
	{
		$sale->parent_invoice=$this->build_parent_invoice($sale->invoiceid);
   	}
	$this->ebay_listing_row($sale->parent_invoice,$sale->invoiceid);
	$this->update_ebay_listing();
	}
	
}

function update_ebay_listing()
{
global $coin,$ebay,$sale,$ilance;

	$query="SELECT sum(quantity) as sold_quantity_sum FROM " . DB_PREFIX . "ebay_listing_rows WHERE ebay_id='".$coin->ebay_id."'";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
			$query2="update " . DB_PREFIX . "ebay_listing set sld_quantity='".$line['sold_quantity_sum']."' where ebay_id='".$coin->ebay_id."'";
			$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);	
			if($ebay->sold_quantity==$coin->quantity)
			{
				$query2="update " . DB_PREFIX . "ebay_listing set status='sold' where ebay_id='".$coin->ebay_id."'";
				$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
			}
		}
	}
	if($ebay->ListingStatus=='Completed' and $ebay->sold_quantity<$coin->quantity)
	{
		$query2="update " . DB_PREFIX . "ebay_listing set status='expired' where ebay_id='".$coin->ebay_id."'";
		$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
	}
}

function build_child_invoice()
{
	global $ilance,$coin,$ebay,$sale;

	$description 	= 'Sold on Ebay sales ebay item id-'.$coin->ebay_id.'['.$coin->coin_id.']';
	$referer_Page 	= $this->referer_Page;

	$statement_date	= fetch_coin_table('End_Date',$coin->coin_id);
	
	
	$transactionid 	= construct_transaction_id();
	 
	$child_invoice="INSERT INTO " . DB_PREFIX . "invoices
	(invoiceid, projectid, user_id, p2b_user_id, description, amount, paid,totalamount, 
	status,invoicetype, paymethod, ipaddress, referer, createdate, duedate, paiddate,statement_date, transactionid ,Site_Id)
	VALUES(
	NULL,
	'" . intval($coin->coin_id) . "',
	'" . intval($sale->buyer_id) . "',
	'" . intval($coin->user_id) . "',
	'" . $ilance->db->escape_string($description) . "',
	'" . $ilance->db->escape_string($sale->item_total) . "',
	'" . $ilance->db->escape_string($sale->item_total) . "',
	'" . $ilance->db->escape_string($sale->item_total) . "',
	'" . $ilance->db->escape_string($sale->Payment_details) . "',
	'escrow',
	'" . $ilance->db->escape_string($sale->paymentmethod) . "',
	'" . IPADDRESS . "',
	'" . $ilance->db->escape_string($referer_Page) . "',
	'" . $ilance->db->escape_string($ebay->createdate) . "',
	'" . $ilance->db->escape_string(DATETIME24H) . "',
	'" . $ilance->db->escape_string($sale->paiddate) . "',
	'" . $ilance->db->escape_string($statement_date) . "',
	'" . $ilance->db->escape_string($transactionid) . "',1)";
	
	$ilance->db->query($child_invoice);
	return $invoiceid = $ilance->db->insert_id();
}

function build_parent_invoice($invoiceid)
{
	global $ilance,$coin,$ebay,$sale;
	$transactionid = construct_transaction_id();
	$description 	= 'Ebay sales ebay item id-'.$coin->ebay_id.'['.$coin->coin_id.']';
	$amount			= 0;
	$referer_Page 	= $this->referer_Page;
	if($sale->Payment_details=='paid')
	{
		$combined_invoice="
		INSERT INTO " . DB_PREFIX . "invoices
		(invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, 
			referer, createdate, duedate,paiddate,scheduled_date,transactionid, combine_project,Site_Id)
		VALUES(
		NULL,
		'" . intval($sale->buyer_id). "',                        
		'". $ilance->db->escape_string($description)."',
		'" . $ilance->db->escape_string($sale->AmountPaid) . "',
		'" . $ilance->db->escape_string($sale->AmountPaid) . "',
		'" . $ilance->db->escape_string($sale->AmountPaid) . "',
		'1',
		'ebay_listing_tax',
		'including Tax',
		'" . $ilance->db->escape_string($sale->Payment_details) . "',
		'escrow',
		'".$ilance->db->escape_string($sale->paymentmethod)."',
		'" . $ilance->db->escape_string(IPADDRESS) . "',
		'" . $ilance->db->escape_string($referer_Page) . "',
		'" . $ilance->db->escape_string($ebay->createdate) . "',
		'" . $ilance->db->escape_string(DATETIME24H) . "',  
		'" . $ilance->db->escape_string($sale->PaymentTime) . "',  
		'" . $ilance->db->escape_string($sale->PaymentTime) . "',  
		'" . $ilance->db->escape_string($transactionid) . "',
		'".$invoiceid."',1
		)";
		$ilance->db->query($combined_invoice);
	}else if($sale->Payment_details=='unpaid')
	{
		//create a scheduled invoie 
		$combined_invoice="
		INSERT INTO " . DB_PREFIX . "invoices
		(invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, 
			referer, createdate, duedate,paiddate,scheduled_date,transactionid, combine_project,Site_Id)
		VALUES(
		NULL,
		'" . intval($sale->buyer_id). "',                        
		'" . $ilance->db->escape_string($description). "',
		'" . $ilance->db->escape_string($sale->AmountPaid) . "',
		'" . $ilance->db->escape_string($sale->AmountPaid) . "',
		'" . $ilance->db->escape_string($sale->AmountPaid) . "',
		'1',
		'" .$ilance->db->escape_string($sale->salestax). "',
		'ebay_listing_tax',
		'including Tax',
		'scheduled',
		'escrow',
		'" .$ilance->db->escape_string($sale->paymentmethod). "',
		'" . $ilance->db->escape_string(IPADDRESS) . "',
		'" . $ilance->db->escape_string($referer_Page) . "',
		'" . $ilance->db->escape_string($ebay->createdate) . "',
		'" . $ilance->db->escape_string(DATETIME24H) . "',  
		'" . $ilance->db->escape_string($sale->PaymentTime) . "',  
		'" . $ilance->db->escape_string($sale->PaymentTime) . "',  
		'" . $ilance->db->escape_string($transactionid) . "',
		'".$invoiceid."',1
		)";
		$ilance->db->query($combined_invoice);
		$ilance->db->query("update " . DB_PREFIX . "invoices set status='completed' where invoiceid='".$invoiecid."'");
		//if combined with any combine that

	}
	

	return $invoiceid = $ilance->db->insert_id();
	
	
}

function build_fvf_invoice()
{
	global $ilance,$phrase,$coin,$ebay,$sale;
	$sale->fvfinvoiceid=0;
	$sale->fvf=0;
	$fvf 			= $this->calculate_ebay_fvf_value($coin->coin_id,$sale->CurrentPrice,$sale->buyer_id,$coin->user_id);
	$statement_date	= fetch_coin_table('End_Date',$coin->coin_id);
	if($fvf>0)
	{
		
		$txn = construct_transaction_id();
		$fvf_query="
		INSERT INTO " . DB_PREFIX . "invoices
		(invoiceid, projectid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate,paiddate,	statement_date, custommessage, transactionid, isfvf,Site_Id)
		VALUES(
		NULL,
		'" . intval($coin->coin_id) . "',
		'" . intval($coin->user_id) . "',
		'" . 'Final value fee for auction' . ' - ' . fetch_auction('project_title', intval($coin->coin_id)) . ' #' . intval($coin->coin_id) . "',
		'" .  sprintf("%01.2f", $fvf) . "',
		'" .  sprintf("%01.2f", $fvf) . "',
		'paid',
		'debit',
		'account',
		'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
		'" . $ebay->createdate . "',
		'" . $sale->paiddate . "',
		'" . $sale->paiddate . "',
		'" . $statement_date . "',
		'" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
		'" . $txn . "',
		'1','1')
		";  
		$ilance->db->query($fvf_query);
		$fvfinvoiceid = $ilance->db->insert_id();
		$sale->fvfinvoiceid=$fvfinvoiceid;
		$sale->fvf=$fvf;
		//return $temp;
	}
	
	
            
	
}

function ebay_listing_row($parent_invoice,$invoiceid)
{
	global $ilance,$ebay,$coin,$sale;
	
	$item_type				= $ebay->ListingType=='Chinese'?'regular':'fixed';
	$ebay_buyer_fee			= 0;
	$buyerfee_invoice_id	= 0;
	$ebay_seller_fee		= $sale->fvf;
	$fvf_invoice_id			= $sale->fvfinvoiceid;
	if($sale->shippingcost>0)
	{
	$this->shipping_invoice_projects();
	}
	$invoice_status			=$sale->Payment_details;
	$ebay_row_query="
		INSERT INTO " . DB_PREFIX . "ebay_listing_rows  (coin_id,ebay_id,ebay_listing_id,ebay_Transaction_id,ExternalTransactionID,seller_id,buyer_id,type,quantity,enddate,invoice_id,
		master_invoice_id,ebay_buyer_fee,buyerfee_invoice_id,ebay_seller_fee,fvf_invoice_id,amount,salestax,
		shippingservice,shippingcost,salestax_percent,invoice_status,order_date) 								 
		VALUES (
	'".$ilance->db->escape_string($coin->coin_id)."', 
	'".$ilance->db->escape_string($coin->ebay_id)."', 
	'".$ilance->db->escape_string($coin->ebay_listing_id)."', 
	'".$ilance->db->escape_string($sale->transaction_id)."', 
	'".$ilance->db->escape_string($sale->ExternalTransactionID)."', 
	'".intval($coin->user_id)."', 
	'".intval($sale->buyer_id)."', 
	'".$item_type."', 
	'".$ilance->db->escape_string($sale->QuantityPurchased)."', 
	'".$ebay->createdate."', 
	'".$ilance->db->escape_string($invoiceid)."', 
	'".$ilance->db->escape_string($parent_invoice)."', 
	'".$ilance->db->escape_string($ebay_buyer_fee)."', 
	'".$ilance->db->escape_string($buyerfee_invoice_id)."', 
	'".$ilance->db->escape_string($ebay_seller_fee)."', 
	'".$ilance->db->escape_string($fvf_invoice_id)."', 
	'".$sale->CurrentPrice."',
	'".$ilance->db->escape_string($sale->salestax)."', 
	'".$ilance->db->escape_string($sale->shippingservice)."', 
	'".$ilance->db->escape_string($sale->shippingcost)."', 
	'".$ilance->db->escape_string($sale->salestax_percent)."', 
	'".$ilance->db->escape_string($invoice_status)."',
	'".$ilance->db->escape_string($sale->CreatedDate)."')";
	$ilance->db->query($ebay_row_query);
}

function shipping_invoice_projects()
{
	global $ilance,$coin,$ebay,$sale;
	$invoice_status			=$sale->Payment_details=='Succeeded'?'paid':'unpaid';
	$shipper_id=$this->get_shipper_id($sale->shippingservice);
	$query="INSERT INTO " . DB_PREFIX . "invoice_projects (invoice_id, project_id, coin_id, qty, shipper_id, buyer_id, seller_id, status, created_date, promocode, shipping_cost, final_invoice_id, disount_val, inv_address) VALUES (
	'".$sale->invoiceid."',
	'".$coin->coin_id."',
	'".$coin->coin_id."',
	'0',
	'".$shipper_id."',
	'".$sale->buyer_id."',
	'".$coin->user_id."',
	'".$invoice_status."',
	'".$ebay->createdate."',
	'',
	'".$ilance->db->escape_string($sale->shippingcost)."',
	'".$sale->parent_invoice."',
	'0',
	'')";
	$result=$ilance->db->query($query);
	 
}
function get_shipper_id($shipper)
{
global $ilance;
$query="SELECT * FROM " . DB_PREFIX . "shippers WHERE shipcode = '".$shipper."'";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
		while($line=$ilance->db->fetch_array($result))
		{
			return $line['shipperid'];
		}
	}else
	return 26;
}


function getbuyer_registered($Buyerreg)
{
	global $ilance,$coin;
	$Buyer 					= $Buyerreg->getElementsByTagName('Buyer');
	$Buyerregemail 			= $Buyerreg->getElementsByTagName("Email");
	$Buyerregisteremail   	= $Buyerregemail->item(0)->nodeValue;
	$ebayBuyerregisteremail1= $Buyerreg->getElementsByTagName("StaticAlias"); 
	$ebayBuyerregisteremail = '';
	if($ebayBuyerregisteremail1->length>0)
	$ebayBuyerregisteremail	= $ebayBuyerregisteremail1->item(0)->nodeValue;
	$Buyerregisteremail 	= $Buyerregisteremail=='Invalid Request'?$ebayBuyerregisteremail:$Buyerregisteremail;
	$Buyerreguser			= $Buyerreg->getElementsByTagName("UserID");
	$Buyerregusr   			= $Buyerreguser->item(0)->nodeValue;
	$BuyerregName 			= $Buyerreg->getElementsByTagName("Name");
	$BuyerregNam   			= $BuyerregName->item(0)->nodeValue;
	$BuyerregSplitNam		= explode(' ',$BuyerregNam);		
	$BuyerregLastNam		= array_pop($BuyerregSplitNam);
	$BuyerregFirstNam		= implode(' ',$BuyerregSplitNam);
	$BuyerregStreet1 		= $Buyerreg->getElementsByTagName("Street1");
	$BuyerregSt1   			= $BuyerregStreet1->item(0)->nodeValue;
	$BuyerregCityName 		= $Buyerreg->getElementsByTagName("CityName");
	$BuyerregCityNam   		= $BuyerregCityName->item(0)->nodeValue;
	$BuyerregStateOrProvince= $Buyerreg->getElementsByTagName("StateOrProvince");
	$BuyerregState  		= $BuyerregStateOrProvince->item(0)->nodeValue;
	$BuyerregCountry 		= $Buyerreg->getElementsByTagName("Country");
	$BuyerregCoun   		= fetch_country_id($BuyerregCountry->item(0)->nodeValue);
//	$BuyerregCoun   		= $BuyerregCountry->item(0)->nodeValue;
	$BuyerregPostalCode 	= $Buyerreg->getElementsByTagName("PostalCode");
	$BuyerregPostCode  	 	= $BuyerregPostalCode->item(0)->nodeValue;
	$BuyerregPhonenumber    = $Buyerreg->getElementsByTagName("Phone");
	$BuyerregPhone  	 	= '';
	if($BuyerregPhonenumber->length>0)
	$BuyerregPhone  	 	= $BuyerregPhonenumber->item(0)->nodeValue;
	$referer_Page 			= $this->referer_Page;
	if ($BuyerregCoun==0)
	{
		$BuyerregCon = fetch_country_id();
	}
	
	$preference=0;
	 if (!empty($Buyerregusr) )
        {
			$check =  $ilance->db->query("SELECT user_id,email FROM " . DB_PREFIX . "users
										WHERE email = '".$ilance->db->escape_string($Buyerregisteremail)."' 
										or username = '".$ilance->db->escape_string($Buyerregusr)."' limit 1");
			if($ilance->db->num_rows($check) == 0 )
			{  
				$randomPass = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@"), 0, 7);
				$randomSalt = substr(str_shuffle("123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ@!#*&?"), 0, 4);
				$user['salt']=$randomSalt;
				$user['password']= md5(md5($randomPass).$randomSalt);;
				$ilance->db->query("
                INSERT INTO " . DB_PREFIX . "users
                (user_id, username, password, salt, email, first_name, last_name, address, city, state, zip_code, phone,client_representative, country, date_added, status, lastseen, dob, styleid,  displayprofile, emailnotify)
                VALUES(
                NULL,
                '" . $ilance->db->escape_string($Buyerregusr) . "',
                '" . $ilance->db->escape_string($user['password']) . "',
				'" . $ilance->db->escape_string($user['salt']) . "',
                '" . $ilance->db->escape_string($Buyerregisteremail) . "',
                '" . $ilance->db->escape_string($BuyerregFirstNam) . "',
				'" . $ilance->db->escape_string($BuyerregLastNam) . "',
				'" . $ilance->db->escape_string($BuyerregSt1) . "',
				'" . $ilance->db->escape_string($BuyerregCityNam) . "',
				'" . $ilance->db->escape_string($BuyerregState) . "',
                '" . $ilance->db->escape_string($BuyerregPostCode) . "',
                '" . $ilance->db->escape_string($BuyerregPhone) . "',
				'eBay',
				'" . $ilance->db->escape_string($BuyerregCoun) . "',
                '" . DATETIME24H . "',
                'active',
                '" . DATETIME24H . "',
                '0000-00-00',
				'2',
                '1',
                '1')
                ", 0, null, __FILE__, __LINE__);
				
                $buyer_id = $ilance->db->insert_id();
			    $txn = construct_transaction_id();
				$buyer_id = $ilance->db->insert_id();
				$subscriptionid=1;
				$paymethod='account';
				$roleid=3;
				$migratelogic='none';

				$ilance->db->query("INSERT INTO " . DB_PREFIX . "invoices
									(invoiceid, subscriptionid, user_id, description, amount, totalamount, status, invoicetype, 
									paymethod, ipaddress,referer, createdate, duedate,paiddate, custommessage, transactionid, isfvf,Site_Id)
									VALUES(
									NULL,
									'" . intval($subscriptionid) . "',
									'" . intval($buyer_id) . "',
									'Subscription Payment for Default Plan (10Y) ebay',
									'0',
									'0',
									'paid',
									'subscription',
									'account',
									'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
									'" . $ilance->db->escape_string($referer_Page) . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'Thank you for your Business!',
									'" . $txn . "',
									'0','1')
									");	
		
				$invoice_k_id = $ilance->db->insert_id();
				$subscription_renew_date = print_subscription_renewal_datetime(3650);
				$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_user
									(id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, 
									migrateto, migratelogic, recurring, invoiceid, roleid)
									VALUES(
									NULL,
									'" . intval($subscriptionid) . "',
									'" . intval($buyer_id) . "',
									'" . $ilance->db->escape_string($paymethod) . "',
									'" . DATETIME24H . "',
									'" . $subscription_renew_date . "',
									'1',
									'yes',
									'0',
									'1',
									'" . $ilance->db->escape_string($migratelogic) . "',
									'0',
									'" . intval($invoice_k_id) . "',
									'" . $roleid . "')
									", 0, null, __FILE__, __LINE__);
					
				$ilance->db->query("INSERT INTO " . DB_PREFIX . "email_preference VALUES(NULL,'1','1','1','1','1','1','1','1','1','" . $buyer_id ."')");
			
				return $buyer_id;
			}
			else
			{
				while($user_det = $ilance->db->fetch_array($check))
				{
					return $buyer_id = $user_det['user_id'];
				}
			}
			return false;
		}
		return false;
}


function calculate_ebay_fvf_value($pid,$bidamount,$buyer_id,$seller_id)
{
global $ilance;
$fvf = 0 ;
$house_acc = $ilance->db->query("SELECT house_account FROM " .DB_PREFIX. "users WHERE user_id = '".$seller_id."' AND house_account='1'");
$resproject1 = $ilance->db->fetch_array($house_acc);
if($ilance->db->num_rows($house_acc) > 0)
{
	$fvf = 0 ;
}
else
{
	$ebay_seller_fee="SELECT ebay_seller_percentage  FROM " . DB_PREFIX . "users  WHERE  user_id='".$seller_id."'";
	$ebay_seller_fee_res = $ilance->db->query($ebay_seller_fee, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($ebay_seller_fee_res)>0)
	{
		while($ebay_seller=$ilance->db->fetch_array($ebay_seller_fee_res))
		{
		$ebay_fvf_percentage= $ebay_seller['ebay_seller_percentage'];
		$fvf = ($bidamount * $ebay_fvf_percentage / 100);
		}
	}
	if($fvf <= 0)
	{
	$sql="SELECT fvf_id  FROM " . DB_PREFIX . "coins WHERE  coin_id='".$pid."'";
	$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res)>0)
	{
		while($line=$ilance->db->fetch_array($res))
		{
			$sql1="SELECT amountpercent  FROM " . DB_PREFIX . "finalvalue_groups g left join 	" . DB_PREFIX . "finalvalue f on g.groupid=f.groupid and 					f.finalvalue_from<='".$bidamount."' and (f.finalvalue_to>='".$bidamount."'  or f.finalvalue_to<0) and f.amountpercent>0 
			WHERE  g.groupid='".$line['fvf_id']."' and g.state='product'";
			$res1 = $ilance->db->query($sql1);
			if($ilance->db->num_rows($res1)>0)
			{
				while($line1=$ilance->db->fetch_array($res1))
				{
					$fvf_percentage= $line1['amountpercent'];
					$fvf = ($bidamount * $fvf_percentage / 100);
				}
			}
		}
	}
	}
}
return $fvf;
}

function nodeval($response,$tag)
{
	$temp=$response->getElementsByTagName($tag) ;
	if($temp->length>0)
		return $value=$temp->item(0)->nodeValue ;
	else
	{
	return '';
	}
	
}
 

function mysql_date($given_date)
{
  return  str_replace('T',' ',substr($given_date,0,19));
}
function ebay_date($given_date)
{
  list($d,$t)=explode(" ",$given_date);
  return $d.'T'.$t.'.000Z';
}
function resetobj($obj)
{
	if(count($obj)>0)
	{
	foreach($obj as $key=>$value)
	{
		unset($obj->$key);
	}
	}
}

}

?>