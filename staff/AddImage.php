<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
    'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
//vijay work for multiple image upload 


global $ilance;

	if(isset($ilance->GPC['Submit_ebay']) AND $ilance->GPC['Submit_ebay'] == 'Submit' )
	{
		if(!empty($ilance->GPC['coin_list_Next']))
		{
			$coin_id=$ilance->GPC['coin_list_Next'];
		}
		else
		{
			print_action_failed('Please give valid coinid details','AddImage.php','Retry');
			exit();		
		}

			$coin_id = rtrim($coin_id,',');
			$place_item_arr=explode(',',$coin_id);
			$place_item_sigarr = array_unique($place_item_arr);
			
			if(count($place_item_sigarr) > 0)
			{ 
				$flag=true;
				foreach($place_item_sigarr as $id)
				{
					if(!is_numeric($id))
					{
					$flag=false;
					break;
					}
				}
				if($flag == true)
				{
				$notlisted	=	array();
					foreach($place_item_sigarr as $id)
					{
									$con_listifng_gc = $ilance->db->query("
									SELECT  *
									FROM " . DB_PREFIX . "coins 
									WHERE coin_listed = 'c'
									AND coin_id = '".$id."'", 0, null, __FILE__, __LINE__);
									if($ilance->db->num_rows($con_listifng_gc) > 0)
									{
																	
										while($row_lisst_gc = $ilance->db->fetch_array($con_listifng_gc))
										{
																		
											$query1 = $ilance->db->query("SELECT attachid,project_id,filehash,filename,attachtype FROM " . DB_PREFIX . "attachment  WHERE project_id = '".$row_lisst_gc['coin_id']."'", 0, null, __FILE__, __LINE__);
											if ($ilance->db->num_rows($query1) > 0)
											{    

											//$ebayimageflag =false;
											while ($res = $ilance->db->fetch_array($query1, DB_ASSOC))
											{
											
											
											$project_id= $res['project_id'];
											$filehash= $res['filehash'];
											$filename = $res['filename'];
											$DIR_AUCTION_ATTACHMENT = '/home/gc/public_html/uploads/attachments/auctions/';
											$file_path = $DIR_AUCTION_ATTACHMENT.floor($project_id/100).'00/'.  $project_id . '/' . $filehash . '.attach';

											// // $imageuplchk = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "ebay_images WHERE coin_id = '".$coin_id."' group by attach_id", 0, null, __FILE__, __LINE__);

											// // if ($ilance->db->num_rows($imageuplchk) == 0)
											// // {

											
											$full_url=post_image_ebay($file_path,$filename);
											
											$sql="INSERT INTO " . DB_PREFIX . "ebay_images (coin_id, attach_id, filename, ebay_url, ebay_id,attachtype,upload_date) VALUES ('".$project_id."', '".$res['attachid']."', '".$filename."', '".$full_url."','1','".$res['attachtype']."',NOW())";

											$ilance->db->query($sql);
											$set_ebay_coin[]	= $file_path;

											
											//}
																				
											}
											
											// {
											// //echo '<strong>Ebay Image is ALready Exists</strong></br><br>';
											// }

											}
											else
											{
											$noimages[]=$row_lisst_gc['coin_id'];
											}
										
										}
										

											// if($ebayimageflag == false)
									}

									else
									{
									$notlisted[]=$id;
									}
								
					}
				}
				
				if(!empty($noimages))
				{
					$html	=	'<h3> No Image,for the following Coins List & Remaining Coin are Sucessfully Uploaded to EBay Live.</h3>';
					$html	.=	'<br/>Coins Id:';
					$html	.=	implode(',',$noimages);
					$html	.=	'<br/>';
					
					print_action_success($html,'AddImage.php','The Given Coins are not having images','Retry');
					exit();			
				}

				if(!empty($notlisted))
				{
					$html	=	'<h3>This Coins are Not Listed in GC Coins List & Remaining Coin are Sucessfully Uploaded to EBay Live.</h3>';
					$html	.=	'<br/>Coins Id:';
					$html	.=	implode(',',$notlisted);
					$html	.=	'<br/>';
					
					print_action_success($html,'AddImage.php','The Given Coins not listed in live','Retry');
					exit();			
				}
				if($flag == true)
				{
				print_action_success('Your Coin images are Uploaded Ebay.','AddImage.php' ,'Success');
				exit();
				}
				else
				{
				print_action_failed('Please give valid coinid details.','AddImage.php' ,'Retry');
				exit();
				}
			}
	}
//ilance_ebay_images
$pprint_array = array('show','newnumber','html','coin_id','consignid','seller_name','user_id','seller_id','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');
			
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'addimage.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}

else
{
refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
exit();
}
function post_image_ebay($file_path,$filename)
{	
		
	$devID = '0ad39d96-573f-47e7-9345-1cffd412549e';   // these prod keys are different from sandbox keys
    $appID = 'herakle26-0679-4e6f-9155-39794874fde';
    $certID = 'd32b96ca-b01e-407a-8575-7b0e44d76dc1';
    //the token representing the eBay user to assign the call with
    $userToken = 'AgAAAA**AQAAAA**aAAAAA**wNuAUw**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wJkoKjCpmKpA+dj6x9nY+seQ**1OwBAA**AAMAAA**4bmRLHRSN7fp+zQHHvTGptnBWwI1Stnwv2mqCFLMSKSwuoiTxr0KrpUPXIY5nPomb/5VcltYsN8jPQg5+CX9kiJNt0aRLXLpn0F2wZbI95FQtMBchNXNpHCp31OfzZJMNA1U3NNcyNwXJsHRdHPlWnUnUkaA+JlulxhWxe2sCrS8f0yJpLpE+FWFFFXUYKb3lkvoIGwbuuAH7gnlwrgAFHJoGmNjI/YFPkVFwbBNW5zp9ytza+tDs6gBWndel8C1aG3glY735k+obFfyXFV67ZMPyS/NjUPJ4Hr/ioTnE5liyD0g6473NBH7zNJKfcgzjcteQNlJa6Q5QUE0IhHqVw3KT7I94HptJjTOQEp7Z8A5w3u0mZJUCfk3yL4DwlTh/r65d+hnmYoJez/YH1nxpK3AOcNyRw4fr/o0Zze/eNHGAzjW6HA/wxw8lhO1pRm2jQXXH9tMerhwMwI9bI/ipscRNSyP5gxkgEYlFAxNi+6xYBSNKNMXAI9Re/KpLoB5XKvXm/hfCnwcIYNfhvDesYckq3fhbA7SO8lTuaOCT1QxwhZ6yQDXDYjHVpwKTQ1JwYAVSSZSh6JLULLvi7n6/CB91SyzeOEMV/kC9bQw/NgV+2dRTJ0jXtlj6GmsH+K81DMhRRX/7LuDglp7+LwB6df481oq1TI6ZKgnMnCnfwsF8iIQgrxZzlvCIj+TxchS7a/yjdtIi7Jhz7s+YIV1tUtJ/3EAp9FWEjQgcH2ylc166d4qTS2XyWNl/Lh/8dIY';                 


   
    $siteID  = 0;                            // siteID needed in request - US=0, UK=3, DE=77...
    $verb    = 'UploadSiteHostedPictures';   // the call being made:
    $version = 517;                          // eBay API version
    
	$file=$file_path;

    $picNameIn = $filename;
    $handle = fopen($file,'r');         // do a binary read of image
    $multiPartImageData = fread($handle,filesize($file));
    fclose($handle);

    ///Build the request XML request which is first part of multi-part POST
    $xmlReq = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $xmlReq .= '<' . $verb . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
    $xmlReq .= "<Version>$version</Version>\n";
    $xmlReq .= "<PictureName>$picNameIn</PictureName>\n";    
    $xmlReq .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>\n";
    $xmlReq .= '</' . $verb . 'Request>';

    $boundary = "MIME_boundary";
    $CRLF = "\r\n";
    
    // The complete POST consists of an XML request plus the binary image separated by boundaries
    $firstPart   = '';
    $firstPart  .= "--" . $boundary . $CRLF;
    $firstPart  .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
    $firstPart  .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
    $firstPart  .= $xmlReq;
    $firstPart  .= $CRLF;
    
    $secondPart = "--" . $boundary . $CRLF;
    $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
    $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
    $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
    $secondPart .= $multiPartImageData;
    $secondPart .= $CRLF;
    $secondPart .= "--" . $boundary . "--" . $CRLF;
    
    $fullPost = $firstPart . $secondPart;
    
    // Create a new eBay session (defined below) 
    $session = new eBaySession1($userToken, $devID, $appID, $certID, false, $version, $siteID, $verb, $boundary);

    $respXmlStr = $session->sendHttpRequest($fullPost);   // send multi-part request and get string XML response
    
    if(stristr($respXmlStr, 'HTTP 404') || $respXmlStr == '')
        die('<P>Error sending request');
        
    $respXmlObj = simplexml_load_string($respXmlStr);    
	$ack        = $respXmlObj->Ack;
    $picNameOut = $respXmlObj->SiteHostedPictureDetails->PictureName;
    $picURL     = $respXmlObj->SiteHostedPictureDetails->FullURL;
	$PictureSetMember=$respXmlObj->SiteHostedPictureDetails->PictureSetMember;
	
	/*
	//$hosted_image['PictureSetMember']=$PictureSetMember;
	print_r($respXmlObj);
	print "<P>Picture Upload Outcome : $ack </P>\n";
			print "<P>picNameOut = $picNameOut </P>\n";
			print "<P>picURL = $picURL</P>\n";
			print "<IMG SRC=\"$picURL\">";*/
			return $picURL;

	//return $hosted_image;

}
	
	class eBaySession1
{
	private $requestToken;
	private $devID;
	private $appID;
	private $certID;
	private $serverUrl;
	private $compatLevel;
	private $siteID;
	private $verb;
    private $boundary;

	public function __construct($userRequestToken, $developerID, $applicationID, $certificateID, $useTestServer,
								$compatabilityLevel, $siteToUseID, $callName, $boundary)
	{
	    $this->requestToken = $userRequestToken;
	    $this->devID = $developerID;
            $this->appID = $applicationID;
	    $this->certID = $certificateID;
	    $this->compatLevel = $compatabilityLevel;
	    $this->siteID = $siteToUseID;
	    $this->verb = $callName;
            $this->boundary = $boundary;
	    if(!$useTestServer)
		$this->serverUrl = 'https://api.ebay.com/ws/api.dll';
	    else
	        $this->serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
	}
	
	/**	sendHttpRequest
		Sends a HTTP request to the server for this session
		Input:	$requestBody
		Output:	The HTTP Response as a String
	*/
	public function sendHttpRequest($requestBody)
	{        
        $headers = array (
            'Content-Type: multipart/form-data; boundary=' . $this->boundary,
            'Content-Length: ' . strlen($requestBody),
	    'X-EBAY-API-COMPATIBILITY-LEVEL: 517' . $this->compatLevel,  // API version
		
			
	    'X-EBAY-API-DEV-NAME: ' . $this->devID,     //set the keys
	    'X-EBAY-API-APP-NAME: ' . $this->appID,
	    'X-EBAY-API-CERT-NAME: ' . $this->certID,

            'X-EBAY-API-CALL-NAME: ' . $this->verb,		// call to make	
	    'X-EBAY-API-SITEID: ' . $this->siteID,      // US = 0, DE = 77...
        );
	//initialize a CURL session - need CURL library enabled
	$connection = curl_init();
	curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
        curl_setopt($connection, CURLOPT_TIMEOUT, 30 );
	curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($connection, CURLOPT_FAILONERROR, 0 );
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1 );
        //curl_setopt($connection, CURLOPT_HEADER, 1 );           // Uncomment these for debugging
        //curl_setopt($connection, CURLOPT_VERBOSE, true);        // Display communication with serve
        curl_setopt($connection, CURLOPT_USERAGENT, 'ebatns;xmlstyle;1.0' );
        curl_setopt($connection, CURLOPT_HTTP_VERSION, 1 );       // HTTP version must be 1.0
	$response = curl_exec($connection);
        
        if ( !$response ) {
            print "curl error " . curl_errno($connection ) . "\n";
        }
	curl_close($connection);
	return $response;
    } // function sendHttpRequest
}  // class eBaySession

?>