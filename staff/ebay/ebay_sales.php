<?php 
require_once('../../functions/config.php');

require_once(DIR_CORE.'../../ebay/get-common/keys.php');
require_once(DIR_CORE.'../../ebay/get-common/eBaySession.php');

define('SHOWNOTIFICATIONS',true);
 error_reporting(E_ALL);

 
$query1="SELECT coin_id,ebay_id,date(end_date) as end_date , DATE_ADD(end_date,INTERVAL 30 DAY) as  end_date_plus_thirty_days FROM " . DB_PREFIX . "ebay_listing  where coin_id=230527";
 

$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($sql1))
{
	while($line1 = $ilance->db->fetch_array($sql1))
	{
	/*
	<ModTimeFrom>'.$line1['listedon'].'T18:28:52.799Z</ModTimeFrom>
	<ModTimeTo>'.$line1['end_date_plus_thirty_days'].'T18:28:52.799Z</ModTimeTo>
	*/
		$siteID = 0;
		$ebay_coin_id = $line1['ebay_id'];
		$coin_id=$line1['coin_id'];
		$verb = 'GetItemTransactions';
		$requestXmlBody  = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXmlBody .= '<'.$verb.'Request xmlns="urn:ebay:apis:eBLBaseComponents"> 
							<ItemID>'.$ebay_coin_id.'</ItemID>';
		$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
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
			e ('<P><B>eBay returned the following error(s):</B>');
			//display each error
			//Get error code, ShortMesaage and LongMessage
			$code     = $errors->item(0)->getElementsByTagName('ErrorCode');
			$shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
			$longMsg  = $errors->item(0)->getElementsByTagName('LongMessage');
			$classification  = $errors->item(0)->getElementsByTagName('ErrorClassification');
			
			
			//Display code and shortmessage
			e ('<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue)));
			e ('<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $classification->item(0)->nodeValue)));
			//if there is a long message (ie ErrorLevel=1), display it
			if(count($longMsg) > 0)
				e ('<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue)));
				
			
				
		} else { //no errors
            
			
			//get results nodes
            $responses = $responseDoc->getElementsByTagName($verb."Response");
			$xml = $responseDoc->saveXML($responseDoc);
			$responseDoc->saveXML($responseDoc);
			// echo $xml;exit;
			foreach ($responses as $response)
			{
				$Coin 				= $response->getElementsByTagName('CurrentPrice');
				$CoinAmount 		= $Coin->item(0)->nodeValue;
				$sold_quantity_list	= $response->getElementsByTagName("QuantitySold");
				$sold_quantity		= $sold_quantity_list->item(0)->nodeValue;
				$ListingStatus_array= $response->getElementsByTagName("ListingStatus");
				$ListingStatus		= $ListingStatus_array->item(0)->nodeValue;
				$EndTime_array= $response->getElementsByTagName("EndTime");
				$EndTime		    = $EndTime_array->item(0)->nodeValue;
				$str_data	='';
				if($EndTime!=""){
					$str_data		=	str_replace('T',' ',substr($EndTime,0,19));
				}
				$QuantitySoldArray 		= $response->getElementsByTagName("QuantitySold");
				$QuantitySold   		= $QuantitySoldArray->item(0)->nodeValue;
				if($QuantitySold > 0)
				{
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
							$transaction_id_list	= $Buyerreg->getElementsByTagName('TransactionID');
							$transaction_id			= $transaction_id_list->item(0)->nodeValue;
							$ExternalTransactionID_list	= $Buyerreg->getElementsByTagName('ExternalTransactionID');
							$ExternalTransactionID			= $ExternalTransactionID_list->item(0)->nodeValue;
							if($transaction_id==0)
								$transaction_id=$ExternalTransactionID;
							$PaymentAmount			= $Buyerreg->getElementsByTagName("PaymentAmount");
							$PaymentAmt				= $PaymentAmount->item(0)->nodeValue;
							$Payment_list	    	= $Buyerreg->getElementsByTagName("PaymentStatus");
							$Payment_details		= $Payment_list->item(0)->nodeValue;
							$PaymentMethodUsed		= $Buyerreg->getElementsByTagName("PaymentMethodUsed");
							$PaymentMethodUsd		= $PaymentMethodUsed->item(0)->nodeValue;
							$PaymentAmount			= $Buyerreg->getElementsByTagName("PaymentAmount");
							$PaymentAmt				= $PaymentAmount->item(0)->nodeValue;
							$QuantityPurchased_list	= $Buyerreg->getElementsByTagName('QuantityPurchased');
							$QuantityPurchased		= $QuantityPurchased_list->item(0)->nodeValue;
							
							if($Payment_details=='Succeeded')
							{	
							$Payment_detail='paid';
							}
							else
							{
							$Payment_detail='unpaid';
							}
							/*
							//echo 'transaction id '.$transaction_id.'<br>';
							if(transaction_accounted($transaction_id) and $transaction_id>0)
							{
							//check if transaction id is already accounted
							
							}
							*/
								$buyer_id=getbuyer_registered($Buyerreg);
								//echo 'buyer_id id '.$buyer_id.'<br>';
								if(isset($buyer_id) && $buyer_id > 0)
								{
									$sql="SELECT e.*,e.end_date as ebay_end_date,c.user_id,c.Title FROM " . DB_PREFIX . "ebay_listing e 
									left join ".DB_PREFIX."coins c on c.coin_id=e.coin_id WHERE ebay_id = '".$ebay_coin_id."'";
									 
									$coins_det = $ilance->db->query($sql);
									
									if($ilance->db->num_rows($coins_det) > 0 )
									{ 
								
										while($coins = $ilance->db->fetch_array($coins_det))
										{
										
											 $project_id 	= $coins['coin_id'];
											$seller_id 		= $coins['user_id'];
											$description 	= 'Sold on Ebay sales ebay item id '.$ebay_coin_id;
											$amount 		= ($CoinAmount*$QuantityPurchased);
											$status 		= $Payment_detail;
											$invoicetype	= 'escrow';
											$ipaddress      = IPADDRESS;
											//$createdate     = DATETIME24H;
											$createdate     = $coins['ebay_end_date'];
											$duedate        = DATETIME24H;
											$paiddate       = '';
											$custommessage  = '';
											$paid= $PaymentAmt;
											$archive = $ispurchaseorder = $isdeposit = $iswithdraw = 0;
											$transactionid = construct_transaction_id();
											
											if($Payment_details=='Succeeded')
											{
											
											$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "invoices
											(invoiceid, subscriptionid, projectid, buynowid, user_id, p2b_user_id, storeid, orderid, description, amount, paid,totalamount, 
											status,invoicetype, paymethod, ipaddress, referer, createdate, duedate, paiddate,custommessage, transactionid, archive, 
											ispurchaseorder, isdeposit, iswithdraw)
											VALUES(
											NULL,
											0,
											'" . intval($project_id) . "',
											0,
											'" . intval($buyer_id) . "',
											'" . intval($seller_id) . "',
											0,
											0,
											'" . $ilance->db->escape_string($description) . "',
											'" . $ilance->db->escape_string($paid) . "',
											'" . $ilance->db->escape_string($paid) . "',
											'" . $ilance->db->escape_string($PaymentAmt) . "',
											'" . $ilance->db->escape_string($status) . "',
											'" . $ilance->db->escape_string($invoicetype) . "',
											'" . $ilance->db->escape_string($PaymentMethodUsd) . "',
											'" . $ilance->db->escape_string($ipaddress) . "',
											'" . $ilance->db->escape_string($referer_Page) . "',
											'" . $ilance->db->escape_string($createdate) . "',
											'" . $ilance->db->escape_string($duedate) . "',
											'" . $ilance->db->escape_string($paiddate) . "',
											'" . $ilance->db->escape_string($custommessage) . "',
											'" . $ilance->db->escape_string($transactionid) . "',
											'" . $ilance->db->escape_string($archive) . "',
											'" . intval($ispurchaseorder) . "',
											'" . intval($isdeposit) . "',
											'" . intval($iswithdraw) . "')
											", 0, null, __FILE__, __LINE__); 
											 $invoiceid = $ilance->db->insert_id();
									
									//combine project invoice
									$transactionid = construct_transaction_id();
											$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "invoices
											(invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,scheduled_date,transactionid, combine_project)
											VALUES(
											NULL,
											'" . intval($buyer_id). "',                        
											'". $ilance->db->escape_string($description)."',
											'0',
											'" . $ilance->db->escape_string($paid) . "',
											'" . $ilance->db->escape_string($PaymentAmt) . "',
											'1',
											'ebay_listing_tax',
											'including Tax',
											'" . $ilance->db->escape_string($status) . "',
											'escrow',
											'".$ilance->db->escape_string($PaymentMethodUsd)."',
											'" . $ilance->db->escape_string($ipaddress) . "',
											'" . $ilance->db->escape_string($referer_Page) . "',
											'" . $ilance->db->escape_string($createdate) . "',
											'" . $ilance->db->escape_string($createdate) . "',  
											'" . $ilance->db->escape_string($createdate) . "',  
											'" . $ilance->db->escape_string($transactionid) . "',
											'".$invoiceid."'
										   )
									", 0, null, __FILE__, __LINE__);
					
										
									
											}
											else
											{
								
											$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "invoices
											(invoiceid, subscriptionid, projectid, buynowid, user_id, p2b_user_id, storeid, orderid, description, amount, paid,totalamount, status,invoicetype, paymethod, ipaddress, referer, createdate, duedate, paiddate,custommessage, transactionid, archive, ispurchaseorder, isdeposit, iswithdraw)
											VALUES(
											NULL,
											0,
											'" . intval($project_id) . "',
											0,
											'" . intval($buyer_id) . "',
											'" . intval($seller_id) . "',
											0,
											0,
											'" . $ilance->db->escape_string($description) . "',
											'" . $ilance->db->escape_string($amount) . "',
											'" . $ilance->db->escape_string(0) . "',
											'" . $ilance->db->escape_string($amount) . "',
											'" . $ilance->db->escape_string($status) . "',
											'" . $ilance->db->escape_string($invoicetype) . "',
											'" . $ilance->db->escape_string($paymethod) . "',
											'" . $ilance->db->escape_string($ipaddress) . "',
											'" . $ilance->db->escape_string($referer_Page) . "',
											'" . $ilance->db->escape_string($createdate) . "',
											'" . $ilance->db->escape_string($duedate) . "',
											'" . $ilance->db->escape_string($paiddate) . "',
											'" . $ilance->db->escape_string($custommessage) . "',
											'" . $ilance->db->escape_string($transactionid) . "',
											'" . $ilance->db->escape_string($archive) . "',
											'" . intval($ispurchaseorder) . "',
											'" . intval($isdeposit) . "',
											'" . intval($iswithdraw) . "')
											", 0, null, __FILE__, __LINE__); 
											$invoiceid = $ilance->db->insert_id();
											
											//combine project invoice
									$transactionid = construct_transaction_id();
											$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "invoices
											(invoiceid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, referer, createdate, duedate,transactionid, combine_project)
											VALUES(
											NULL,
											'" . intval($buyer_id). "',                        
											'". $ilance->db->escape_string($description)."',
											'" . $ilance->db->escape_string($amount+$ShippingCost). "',
											'" . $ilance->db->escape_string($paid) . "',
											'" . $ilance->db->escape_string($PaymentAmt) . "',
											'1',
											'ebay_listing_tax',
											'including Tax',
											'" . $ilance->db->escape_string($status) . "',
											'escrow',
											'".$ilance->db->escape_string($PaymentMethodUsd)."',
											'" . $ilance->db->escape_string($ipaddress) . "',
											'" . $ilance->db->escape_string($referer_Page) . "',
											'" . $ilance->db->escape_string($createdate) . "',
											'" . $ilance->db->escape_string($createdate) . "',   
											'" . $ilance->db->escape_string($transactionid) . "',
											'".$invoiceid."'
										   )
									", 0, null, __FILE__, __LINE__);
											
											}
						
																
							$fvf = calculate_ebay_fvf_value($project_id,$CoinAmount,$buyer_id,$seller_id);

								

							if (!empty($buyer_id) AND $buyer_id > 0 )
							{
									$fee = 0;
							}
	                        if ($fvf > 0)
	                        {
                                
	                            
                                                    														
													$txn = construct_transaction_id();

												
													$ilance->db->query("
													INSERT INTO " . DB_PREFIX . "invoices
													(invoiceid, projectid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate,paiddate, custommessage, transactionid, isfvf)
													VALUES(
													NULL,
													'" . intval($project_id) . "',
													'" . intval($seller_id) . "',
													'" . 'Final value fee for auction' . ' - ' . fetch_auction('project_title', intval($project_id)) . ' #' . intval($project_id) . "',
													'" .  sprintf("%01.2f", $fvf) . "',
													'" .  sprintf("%01.2f", $fvf) . "',
													'paid',
													'debit',
													'account',
													'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
													'" . DATETIME24H . "',
													'" . DATETIME24H . "',
													'" . DATETIME24H . "',
													'" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
													'" . $txn . "',
													'1')
													");
                                                   
                                
                               
	                        }
							

							//end seller fee
							$buyer_fee=0;
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "ebay_listing_rows 
							(coin_id, ebay_id,ebay_Transaction_id,seller_id, buyer_id, type, quantity, enddate, invoice_id, ebay_buyer_fee, ebay_seller_fee, amount) 								 VALUES (
							'".$ilance->db->escape_string($project_id)."', 
							'".$ilance->db->escape_string($ebay_coin_id)."', 
							'".$ilance->db->escape_string($transaction_id)."', 
							'".intval($seller_id)."', 
							'".intval($buyer_id)."', 
							'".$coins['item_type']."', 
							'".$ilance->db->escape_string($QuantityPurchased)."', 
							'".$coins['end_date']."', 
							'".$ilance->db->escape_string($invoiceid)."', 
							'".$buyer_fee."', 
							'".sprintf("%01.2f", $fvf)."', 
							'".$amount."')");
							
											
																	
												
										}
									}									
								}
								else
								{
								   // return false;
								   echo "buyer id cannot be generated";
								}
							 
						}
						 /*saravanan 3 mar 2014 */
						$quantity_puchased_list	= $response->getElementsByTagName("QuantityPurchased");
						$quantity_puchased		= $quantity_puchased_list->item(0)->nodeValue;
						
						$quantity_list			= $response->getElementsByTagName("Quantity");
						$quantity				= $quantity_list->item(0)->nodeValue;
						
						
						$ListingStatusArray	= $response->getElementsByTagName("ListingStatus");
						$ListingStatus		= $ListingStatusArray->item(0)->nodeValue;
						if($ListingStatus == 'Completed'){
							if($quantity_puchased == $quantity and $quantity > 0){
								$query2="update " . DB_PREFIX . "ebay_listing set status='sold' where ebay_id='".$ebay_coin_id."'";
								$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
							}else{
								$query2="update " . DB_PREFIX . "ebay_listing set status='expired' where ebay_id='".$ebay_coin_id."'";
								$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
							}
								
						}
						
					
					}
				}else
				{
					#update as expired is status is completed
					if($ListingStatus == 'Completed')
					{
						$query2="update " . DB_PREFIX . "ebay_listing set status='expired' where ebay_id='".$ebay_coin_id."'";
						$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
					}
				echo 'Item <a href="http://www.ebay.in/itm/'.$ebay_coin_id.'">'.$ebay_coin_id.'</a> is not sold'.'<br>';
				}			
				$query12="SELECT sum(quantity) as total_quantity FROM " . DB_PREFIX . "ebay_listing_rows  where ebay_id='".$ebay_coin_id."'";
				$sql12 = $ilance->db->query($query12, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($sql12))
				{
					while($line12 = $ilance->db->fetch_array($sql12))
					{
						$total_quantity=$line12['total_quantity'];
	 					$query4="update " . DB_PREFIX . "ebay_listing set status='sold' where ebay_id='".$ebay_coin_id."' and quantity='".$total_quantity."'";
	 					$sql4 = $ilance->db->query($query4, 0, null, __FILE__, __LINE__);
	 					$query5="update " . DB_PREFIX . "ebay_listing set sld_quantity='".$total_quantity."' where ebay_id='".$ebay_coin_id."'";
	 					$sql5 = $ilance->db->query($query5, 0, null, __FILE__, __LINE__);
		/* author: kumaraverl.m start  12.03.14*/
		
		$update_coin_tbl="SELECT quantity  FROM " . DB_PREFIX . "ebay_listing  where coin_id='".$coin_id."' and  ebay_id='".$ebay_coin_id."'";
		$sql_update_coin_tbl = $ilance->db->query($update_coin_tbl);
		$res_update_coin_tbl = $ilance->db->fetch_array($sql_update_coin_tbl);
		
		$before_upt_Quantity = $res_update_coin_tbl['quantity'];
			
		$after_upt_Quantity = $before_upt_Quantity - $total_quantity;       
				
	 	$query6="update " . DB_PREFIX . "coins set Quantity = '".$after_upt_Quantity."' 
													where coin_id='".$coin_id."'";
	 	$sql6 = $ilance->db->query($query6, 0, null, __FILE__, __LINE__);

			/* author: kumaraverl.m end 12.03.14 */			
			
			 /*saravanan 30th apr 2014*/
			
			$checkin = $ilance->db->query("SELECT End_Date,user_id,relist_count FROM " . DB_PREFIX . "coins WHERE coin_id = '".$coin_id."'");
					if($ilance->db->num_rows($checkin) > 0)
					{
						$update_coin_tbl	 =	"SELECT item_type  FROM " . DB_PREFIX . "ebay_listing  where coin_id='".$coin_id."' and  ebay_id='".$ebay_coin_id."'";
						$sql_update_coin_tbl = $ilance->db->query($update_coin_tbl);
						$rst_coin_type 		 = $ilance->db->fetch_array($sql_update_coin_tbl);
						$item_type 			 = $rst_coin_type['item_type'];
						
						$relistend = $ilance->db->fetch_array($checkin);
						 $dataexplode = explode('-', $relistend['End_Date']);
						$date_coin = $dataexplode['0'] .'-'.$dataexplode['1'].'-'.$dataexplode['2'];
						$ilance->db->query("update " . DB_PREFIX . "coin_relist set  
											enddate ='".$date_coin."',
											startbydate ='".DATETIME24H."'
											where user_id='".$relistend['user_id']."' and coin_id='".$coin_id."' and date(actual_end_date)=date('".$relistend['End_Date']."')");
						if($ilance->db->affected_rows() == 0)
						{
							$ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_relist
															(id, coin_id, enddate, startbydate, user_id, actual_end_date, filtered_auctiontype)
															VALUES (
															NULL,
															'".$coin_id."',
															'".$str_data."',
															'".DATETIME24H."',
															'".$relistend['user_id']."',
															'".$relistend['End_Date']."',
															'".$item_type."'
															)");
								$relist_count	=	$relistend['relist_count']+1;
							$ilance->db->query("update " . DB_PREFIX . "coins set relist_count=".$relist_count." WHERE coin_id = '".$coin_id."'" );
						//	$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE project_id = '" . intval($row_value['coin_id']) . "'");
						}
					}
						 /*saravanan 30th apr 2014*/
					}
				}
				
			}
			
			
			$xml = $responseDoc->saveXML($responseDoc);
           
		} // if $errors->length > 0
	
	
	}
	
}

		function transaction_not_accounted($transaction_id)
		{
			global $ilance;
			$query2="SELECT * FROM  ".DB_PREFIX."ebay_listing_rows WHERE  ebay_Transaction_id = '".$transaction_id."'";
			$sql2 = $ilance->db->query($query2, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($sql2))
			{
				return false;
			}
			return true;
		}
	
		function getbuyer_registered($Buyerreg)
		{
			global $ilance;
			$Buyer 					= $Buyerreg->getElementsByTagName('Buyer');
			$Buyerregemail 			= $Buyerreg->getElementsByTagName("Email");
			$Buyerregisteremail   	= $Buyerregemail->item(0)->nodeValue;
			$ebayBuyerregisteremail1=$Buyerreg->getElementsByTagName("StaticAlias");
			$ebayBuyerregisteremail	= $ebayBuyerregisteremail1->item(0)->nodeValue;
			$Buyerregisteremail=$Buyerregisteremail=='Invalid Request'?$ebayBuyerregisteremail:$Buyerregisteremail;
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
			$BuyerregStateOrProvince = $Buyerreg->getElementsByTagName("StateOrProvince");
			$BuyerregState  		= $BuyerregStateOrProvince->item(0)->nodeValue;
			$BuyerregCountry 		= $Buyerreg->getElementsByTagName("Country");
			$BuyerregCoun   		= fetch_country_id($BuyerregCountry->item(0)->nodeValue);
//			$BuyerregCoun   		= $BuyerregCountry->item(0)->nodeValue;
			$BuyerregPostalCode 	= $Buyerreg->getElementsByTagName("PostalCode");
			$BuyerregPostCode  	 	= $BuyerregPostalCode->item(0)->nodeValue;
			$BuyerregPhonenumber    = $Buyerreg->getElementsByTagName("Phone");
			$BuyerregPhone  	 	= $BuyerregPhonenumber->item(0)->nodeValue;
			
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
					   												(invoiceid, subscriptionid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress,referer, createdate, duedate,paiddate, custommessage, transactionid, isfvf)
					   												VALUES(
					   												NULL,
					   												'" . intval($subscriptionid) . "',
					   												'" . intval($buyer_id) . "',
					   												'Subscription Payment for Default Plan (10Y) ebay',
					   												'" .  sprintf("%01.2f", $fvf) . "',
					   												'" .  sprintf("%01.2f", $fvf) . "',
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
					   												'1')
					   												");	
									
					   					$invoice_k_id = $ilance->db->insert_id();
										$subscription_renew_date = print_subscription_renewal_datetime(3650);
					   					    $ilance->db->query("
					                                           INSERT INTO " . DB_PREFIX . "subscription_user
					                                           (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, migrateto, migratelogic, recurring, invoiceid, roleid)
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
		$house_acc = $ilance->db->query("SELECT house_account FROM " .DB_PREFIX. "users WHERE user_id = '".$seller_id."' AND house_account='1'");
		$resproject1 = $ilance->db->fetch_array($house_acc);
		if($ilance->db->num_rows($house_acc) > 0)
		{
			$fvf = 0 ;
		}
		else
		{
			//
			
			
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
				
					$sql1="SELECT amountpercent  FROM " . DB_PREFIX . "finalvalue_groups g
					left join 	" . DB_PREFIX . "finalvalue f on g.groupid=f.groupid and f.finalvalue_from<='".$bidamount."' and (f.finalvalue_to>='".$bidamount."'  or f.finalvalue_to<0) and f.amountpercent>0 
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
		
		
	
//log_cron_action('The auction tasks were successfully executed' , '');
function e($string)
{
if(SHOWNOTIFICATIONS==true)
echo $string;
}
?>