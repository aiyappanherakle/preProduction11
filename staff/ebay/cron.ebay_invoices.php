<?php 
require_once('../../functions/config.php');

require_once(DIR_CORE.'../../ebay/get-common/keys.php');
require_once(DIR_CORE.'../../ebay/get-common/eBaySession.php');

define('SHOWNOTIFICATIONS',true);
 error_reporting(E_ALL);
$query1="SELECT *, DATE_ADD(e.listedon,INTERVAL 30 DAY) as  end_date_plus_thirty_days FROM ilance_ebay_listing_rows r left join ilance_ebay_listing e on e.ebay_id=r.ebay_id where r.invoice_status='paid' and r.ExternalTransactionID='' order by r.id desc limit 50"
 ;

//$query1="SELECT coin_id,ebay_id,date(enddate) as end_date , DATE_ADD(enddate,INTERVAL 30 DAY) as  end_date_plus_thirty_days, date(enddate) as listedon FROM " . DB_PREFIX . "ebay_listing_rows where ebay_transaction_id='' limit 1 ";
$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	 
		$siteID = 0;
		$ebay_coin_id = $line1['ebay_id'];
		$coin_id=$line1['coin_id'];
		$verb = 'GetItemTransactions';
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<'.$verb.'Request xmlns="urn:ebay:apis:eBLBaseComponents"> 
							<ModTimeFrom>'.$line1['listedon'].'T18:28:52.799Z</ModTimeFrom>
							<ModTimeTo>'.$line1['end_date_plus_thirty_days'].'T18:28:52.799Z</ModTimeTo>
							<ItemID>'.$ebay_coin_id.'</ItemID>
							<IncludeContainingOrder>True</IncludeContainingOrder>';
		$requestXmlBody .= "<RequesterCredentials>
<eBayAuthToken>$userToken</eBayAuthToken>
</RequesterCredentials>";
		$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
		$requestXmlBody .= '<ErrorLanguage>en_US</ErrorLanguage>';
		$requestXmlBody .= "<Version>$compatabilityLevel</Version>"; 
		$requestXmlBody .= '</'.$verb.'Request>';

		

				
		
        $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
		
		$responseXml = $session->sendHttpRequest($requestXmlBody);
		if(stristr($responseXml, 'HTTP 404') || $responseXml == '')
			die('<P>Error sending request');
			
			
		$responseDoc = new DomDocument();
		$responseDoc->loadXML($responseXml);
					
		//get any error nodes
		$errors = $responseDoc->getElementsByTagName('Errors');
		
		//if there are error nodes
		if($errors->length > 0)
		{
			echo '<P>
<B>eBay returned the following error(s):</B>';
			//display each error
			//Get error code, ShortMesaage and LongMessage
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
			$classification  = $errors->item(0)->getElementsByTagName('ErrorClassification');
			
			
			//Display code and shortmessage
			echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));
			echo '<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $classification->item(0)->nodeValue));
			//if there is a long message (ie ErrorLevel=1), display it
			if(count($longMsg) > 0)
				echo '<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
				
			
				
		} else { //no errors
            error_reporting(E_ALL);
			
			//get results nodes
            $responses = $responseDoc->getElementsByTagName($verb."Response");
			$xml = $responseDoc->saveXML($responseDoc);
		//	print_r($xml);
		//	echo '<br>';
			$responseDoc->saveXML($responseDoc);
			foreach ($responses as $response)
			{
				$ListingStatus_array= $response->getElementsByTagName("ListingStatus");
				$ListingStatus		= $ListingStatus_array->item(0)->nodeValue;
				$BuyerArray 		= $response->getElementsByTagName("Transaction");
				$BuyerAray   		= $BuyerArray->item(0)->nodeValue;
				if(count($BuyerAray)>0 and $ListingStatus=='Completed')
				{	
					$TransactionArray 	= $response->getElementsByTagName("TransactionArray");
					$TransactionArr   	= $TransactionArray->item(0)->nodeValue;
					$EbayPage 			= $response->getElementsByTagName("ViewItemURL");
					$referer_Page 		= $EbayPage->item(0)->nodeValue;
					$Shipping 			= $response->getElementsByTagName("ShippingServiceCost");
					$ShippingCost 		= $Shipping->item(0)->nodeValue;
					foreach ($BuyerArray as $Buyerreg)
					{
					$transaction_id					= nodeval($response,"TransactionID");
					$ExternalTransactionID			= nodeval($response,"ExternalTransactionID");
					$SalesTaxAmount					= nodeval($response,"SalesTaxAmount");
					$ItemID							= nodeval($response,"ItemID");
					echo '/*'.$ItemID.'*/<br>';
					if($ExternalTransactionID!='')
					{
						$query5= "update " . DB_PREFIX . "ebay_listing_rows set ExternalTransactionID='".$ExternalTransactionID."' and ebay_Transaction_id='".$transaction_id."' where ebay_id='".$ItemID."'";
						echo $query5.';<br>';
						/*$query2="SELECT * FROM " . DB_PREFIX . "invoices  where combine_project like '%".$line1['invoice_id']."%' and status='unpaid'";
						$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
						if($ilance->db->num_rows($sql2))
						{
							while($line2 = $ilance->db->fetch_array($sql2))
							{
							//change to scheduled
								$query3= "update " . DB_PREFIX . "invoices set status='scheduled', scheduled_date=createdate, taxamount='".$SalesTaxAmount."' where invoiceid='".$line2['invoiceid']."";
							//change child to complete
								$query4= "update " . DB_PREFIX . "invoices set status='completed' where invoiceid='".$line2['combine_project']."'";
							//change ebay row status to paid
								$query5= "update " . DB_PREFIX . "ebay_listing_rows set invoice_status='paid' where ebay_id='".$ItemID."'";
							//create invoice_projects
								$invoiceid 			= $line2['combine_project'];
								$project_id			= $line1['coin_id'];
								$coin_id			= $line1['coin_id'];
								$qty				= nodeval($response,"QuantityPurchased");
								$shipper_id			= 26;
								$buyer_id			= $line2['user_id'];
								$seller_id			= $line2['p2b_user_id'];
								$status 			= 'paid';
								$created_date		= $line2['createdate'];
								$final_invoice_id	= $line2['invoiceid'];

								
								$query6="INSERT INTO " . DB_PREFIX . "invoice_projects (invoice_id, project_id, coin_id, qty, shipper_id, buyer_id, 
									seller_id, status, created_date, promocode, shipping_cost, final_invoice_id, disount_val, inv_address) VALUES
									 ('$invoiceid', '$project_id', '$coin_id', '$qty', '$shipper_id', '$buyer_id', '$seller_id', '$status', 
									 	'$created_date', NULL, '0', '$final_invoice_id', NULL, '');";

echo $query3.';<br>'.$query4.';<br>'.$query5.';<br>'.$query6.';<br><br>';
			
							}
						}else
						{
							$query45="SELECT * FROM " . DB_PREFIX . "invoices  where invoiceid = '".$line1['invoice_id']."' and status='paid'";
							$sql45 = $ilance->db->query($query45, 0, null, __FILE__, __LINE__);
							if($ilance->db->num_rows($sql45))
							{
								while($line45 = $ilance->db->fetch_array($sql45))
								{
								$query5= "update " . DB_PREFIX . "ebay_listing_rows set invoice_status='paid' where ebay_id='".$ItemID."'";
								echo $query5.';<br>';
								}
							}
						}*/
						

					}else
					{

					}
					}
				}
			}
			
			$xml = $responseDoc->saveXML($responseDoc);
           
		} // if $errors->length > 0
	
	
	}
	
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
	
//log_cron_action('The auction tasks were successfully executed' , '');
function e($string)
{
if(SHOWNOTIFICATIONS==true)
echo $string;
}
?>