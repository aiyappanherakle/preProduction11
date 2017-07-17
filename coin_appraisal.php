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
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
    'modal',
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'merch');


// #### require backend ########################################################
require_once('./functions/config.php');

require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');
				$area_title = 'Selling Coins';
				
                $page_title = SITE_NAME . ' - Coin Appraisal Form by GreatCollections Coin Auctions';
                
                // construct breadcrumb trail
                $navcrumb = array();
                $navcrumb["main-sell"] = $area_title;
				$navcrumb[""] = ' Request an Estimate';
				
//Captcha validation 

if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'validate_captcha')

{

 $turing = mb_strtoupper(trim($ilance->GPC['value']));
 
 if ($turing != $_SESSION['ilancedata']['user']['captcha'])
 {
		echo 'error';
		exit;
 }

}

//Image Download				
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'app_img')	
{
if(!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$sql = $ilance->db->query("SELECT * FROM ".DB_PREFIX."newsell_attachment 
                           WHERE filehash = '".$ilance->db->escape_string($ilance->GPC['img_id'])."'
                           LIMIT 1
                         ");
						 
		                $row_img = $ilance->db->fetch_array($sql);				 
						 
						  $attachment['filename'] = $row_img['filename'];
						
						  $attachment['filehash'] = $row_img['filehash'].'.attach';

                          $attachment['filedata'] = file_get_contents(DIR_SERVER_ROOT.'newsell/'.$attachment['filehash']);
   
                        
                        header('Cache-control: max-age=31536000');
                        header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                        header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                        header("Content-disposition: attachment; filename=\"" . $attachment['filename'] . "\"");
                        header('Content-transfer-encoding: binary');
                       if (!empty($row_img['filetype']))
					  {
							header('Content-type: ' . $row_img['filetype']);
					  }
					  else
					  {
							header('Content-type: unknown/unknown');        
					  }
	
		
					echo $attachment['filedata'];
					exit();
}
else
{
refresh(HTTPS_SERVER.$ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}						
             
}	

//Inventory File/Spreadsheet Download		
 if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'app_pdf')	
{
if(!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
         $sql = $ilance->db->query("SELECT * FROM ".DB_PREFIX."newsell_inventory 
                                   WHERE filehash = '".$ilance->db->escape_string($ilance->GPC['pdf_id'])."'
                                   LIMIT 1
                                  ");    
						 
						 $row_pdf = $ilance->db->fetch_array($sql);				 
						 
					    $attachment['filename'] = $row_pdf['filename'];
						
						$attachment['filehash'] = $row_pdf['filehash'].'.attach';

                        $attachment['filedata'] = file_get_contents(DIR_SERVER_ROOT.'newsell_inventroy/'.$attachment['filehash']);
   
                        
                        header('Cache-control: max-age=31536000');
                        header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                        header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                        header("Content-disposition: attachment; filename=\"" . $attachment['filename'] . "\"");
                        header('Content-transfer-encoding: binary');
                       if (!empty($row_pdf['filetype']))
					  {
							header('Content-type: ' . $row_pdf['filetype']);
					  }
					  else
					  {
							header('Content-type: unknown/unknown');        
					  }
	
		
					echo $attachment['filedata'];
					exit();
							
}
else
{
refresh(HTTPS_SERVER.$ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}								
}		

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'newsell_coin')
{		

if(isset($ilance->GPC['captcha'])  AND $ilance->GPC['captcha'] != ''  AND isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '' AND isset($ilance->GPC['name']) AND $ilance->GPC['name'] != '')

{
	$ip_restrict = $ilance->db->query("SELECT count(*) as cnt  FROM " . DB_PREFIX . "newsell_coins
	                                     WHERE ipaddress='".IPADDRESS."'
									     AND date(date_added) = '".DATETODAY."'  
								");
								
	   $ip_count = $ilance->db->fetch_array($ip_restrict);	
		
		if($ip_count['cnt'] < 10 )
	   {					
									
		 $target_path = DIR_SERVER_ROOT.'newsell/';
		 $inventory_path = DIR_SERVER_ROOT.'newsell_inventroy/';
		 $items = $_FILES['item3'];
		 $pur = $ilance->GPC['purchaser'];
	  
		 $i=0;
	   
		 for($i=0;$i<count($pur);$i++){
		 $purt[] = $pur[$i];
		 }
		 $pursd = implode(',',$purt);
		 $commen=htmlspecialchars($ilance->GPC['comments']);
		 $purchaser=htmlspecialchars($pursd);
		 $tele=htmlspecialchars($ilance->GPC['tel']);

		 $firstins = $ilance->db->query("INSERT INTO " . DB_PREFIX . "newsell_coins
										(name,email,email1,comments,tel,purchaser,ipaddress,date_added)
										VALUES(
										'".$ilance->db->escape_string($ilance->GPC['name'])."',
										'".$ilance->db->escape_string($ilance->GPC['email'])."',
										'".$ilance->db->escape_string($ilance->GPC['email1'])."',
										'".$ilance->db->escape_string($commen)."',
										'".$ilance->db->escape_string($tele)."',
										'".$ilance->db->escape_string($purchaser)."',
										'".IPADDRESS."',
										'" . DATETIME24H . "'
									   )
									   ", 0, null, __FILE__, __LINE__);
					
		 $newattachid = $ilance->db->insert_id();
	  
	  
		 //########### PDF/Excel Sheets File Upload #############//
	  
		  $hash1=md5(microtime());
		  $inventory = $_FILES['inventory'];
		  $name1 = str_replace(" ","_",$inventory['name']); 
		  $type1 = $inventory['type'];
		  $size1 = $inventory['size'];
		  $tmp1 = $inventory['tmp_name'];
		  $fileExt = explode(".", $name1);
		  
	  if($inventory['size'] > 0)
	  {
		  if (sizeof($fileExt)>1)
				$tmpFileType=$fileExt[sizeof($fileExt)-1];
	  
		  if($tmpFileType != 'exe')
		  {
			 $name_attach= $hash1.'.attach';
			 if(move_uploaded_file($tmp1, $inventory_path.$name_attach)) 
			 {
				  $size1 = filesize($inventory_path.$name_attach);
				  $filedata1 = addslashes(fread(fopen($inventory_path.$name_attach, 'rb'), filesize($inventory_path.$name_attach)));
			 }
	 
			$ilance->db->query("INSERT INTO " . DB_PREFIX . "newsell_inventory
							   (newsell_id,filename,filetype,filesize,filehash)
							   VALUES(
								'".$newattachid."',
								'".$name1."',
								'".$type1."',
								'".$size1."',
								'".$hash1."'
							   )
						", 0, null, __FILE__, __LINE__);
		  }
		 else
		 {
		   print_notice('Requesting Failed', 'File Type not support');
		   exit(); 
		 }
	   }
		
		
		//########## IMAGE UPLOAD into ilance_newsell_attachment ############//
		
		  $hash=md5(microtime());
		  $items = $_FILES['items'];
		  $name = str_replace(" ","_",$items['name']); 
		  $type = $items['type'];
		  $size = $items['size'];
		  $tmp = $items['tmp_name'];
		  
		  $fileExt1 = explode(".", $name);
		if($items['size'] > 0)
		{   
			if (sizeof($fileExt1)>1)
			 $tmpFileType1=$fileExt1[sizeof($fileExt1)-1];
			 $imgtypes = array('jpeg','gif','png','jpg','bmp','tiff','tif');
		
		  if($tmpFileType1 != 'exe')
		  {
			  //rename file 
			
			  $name_attch = $hash.'.attach';
			 if(move_uploaded_file($tmp, $target_path.$name_attch)) 
			 {
				 $size = filesize($target_path.$name_attch);
				 $filedata = addslashes(fread(fopen($target_path.$name_attch, 'rb'), filesize($target_path.$name_attch)));
			 }
			
		  
			 if($name != '')
			 {
			   $ilance->db->query("INSERT INTO " . DB_PREFIX . "newsell_attachment
									(newsell_id,filename,filetype,filesize,filehash)
									VALUES(
									'".$newattachid."',
									'".$name."',
									'".$type."',
									'".$size."',
									'".$hash."'
								   )
							", 0, null, __FILE__, __LINE__);
			 }
		
		  }
		else
		{
			 print_notice('Requesting Failed', 'File Type not support');
			 exit(); 
		}

	   
	  }  
	  
	   $select = $ilance->db->query("SELECT name,email,email1,comments,tel,purchaser
									 FROM " . DB_PREFIX . "newsell_coins
									 WHERE 	id ='".$newattachid."' 
								  ");
		if($ilance->db->num_rows($select) > 0)						  
		{   							  
		 while($row = $ilance->db->fetch_array($select))
		 {
			$username = $row['name'];
			$useremail = $row['email'];
			$useremail1 = $row['email1'];
			$comments = $row['comments'];
			$phone_no = $row['tel'];
			$purchasers = $row['purchaser'];
		 }
		} 
			$ipaddress=$ilance->db->escape_string($_SERVER['REMOTE_ADDR']);

			$txt .= '<div style= "margin-top:0px;"><table style="border: 1px solid #000000; border-collapse: collapse;" cellpadding=10>';
		
 
		
			$txt .= '<tr><td style="background-color: #5B5F68; border: 1px solid #000000;color:#ffffff;"><b>Username: </b></td><td  style="border: 1px solid #000000; ">'.$username.' requested the following coin estimate from GreatCollections.</td></tr><tr><td style="background-color: #5B5F68; border: 1px solid #000000;color:#ffffff;"><b>E mail id: </b></td><td  style="border: 1px solid #000000; ">'.$useremail. '</td></tr><tr><td width ="175px" style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>Telephone number: </b></td><td  style="border: 1px solid #000000; ">'.$phone_no.'</td></tr><tr><td width ="175px" style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>Mostly Purchased at: </b></td><td  style="border: 1px solid #000000; ">'.$purchasers.'</td></tr><tr><td style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff; height: auto;" valign="top"><b>Information: </b></td><td  style="border: 1px solid #000000; " colspan="3" valign="top">'.$comments. '</td></tr>';
			
			$select_pdf = $ilance->db->query("SELECT filehash FROM " . DB_PREFIX . "newsell_inventory
			WHERE newsell_id='".$newattachid."'
			");

			if($ilance->db->num_rows($select_pdf) > 0)						  
			{   							
			$res_pdf = $ilance->db->fetch_array($select_pdf);
			$invfiledata = $res_pdf['filehash'];
			$pdffiles = 'Added Inventory files click here to download:  <a href="'.HTTP_SERVER.'coin_appraisal.php?cmd=app_pdf&pdf_id='.$invfiledata.'">'.HTTP_SERVER.'coin_appraisal.php?cmd=app_pdf&pdf_id='.$invfiledata.'</a>';

			$txt .= '<tr><td width ="175px" style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>Attachments1: </b></td><td  style="border: 1px solid #000000; ">'.$pdffiles.'</td></tr>';	


			}  


			$select_img = $ilance->db->query("SELECT filehash FROM " . DB_PREFIX . "newsell_attachment 
			WHERE newsell_id='".$newattachid."'
			");

			if($ilance->db->num_rows($select_img) > 0)						  
			{   							
			$res_img = $ilance->db->fetch_array($select_img);
			$attfildata = $res_img['filehash'];
			$images_down= 'Added images click here to download:  <a href="'.HTTP_SERVER.'coin_appraisal.php?cmd=app_img&img_id='.$attfildata.'">'.HTTP_SERVER.'coin_appraisal.php?cmd=app_img&img_id='.$attfildata.'</a>';

			$txt .= '<tr><td width ="175px" style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>Attachments2: </b></td><td  style="border: 1px solid #000000; ">'.$images_down.'</td></tr>';


			} 


			$txt .= '<tr><td style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>IP Address: </b></td><td  style="border: 1px solid #000000; ">'.$ipaddress.'</td></tr></table></div>';
			$txt .=' 
			------------------------------------------------------------------

			This email was generated on: '.$ilconfig['official_time'].'
			Email ID: 235';

			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
			$ianemail=$ilconfig['globalserversettings_adminemail'];
			
			$headers .= 'From: '.$ianemail.''. "\r\n" ;

			send_email_enquiry(SITE_EMAIL,'Request an Auction Estimate Page', $txt,OWNER_EMAIL,SITE_NAME,true);

			print_notice('Requesting Complete','Thank you for requesting an Auction Estimate. A representative will be in touch in the next 24-48 hours to discuss your coins',$ilpage['main'],'Home');
			exit(); 
		   
	   }
	   else
	   {
		 print_notice('Requesting Not Completed','You have reached our online limit for coin estimate requests. Please telephone our office at 1-800-442-6467 to discuss your coins.',$ilpage['main'],'Home');
		  exit(); 
	   
	   }	  
   
}
else
{
print_notice('Requesting Failed','Invalid Request.Try Again',$ilpage['main'],'Home');
exit(); 
}
       
  
}
        $captcha = '<img src="'.HTTPS_SERVER.'attachment.php?do=captcha" alt="'.'{_please_enter_the_security_code_shown_on_the_image_to_continue_registration}'.'" border="0" />';
		
		$pprint_array = array('area_title','page_title','site_name','https_server','http_server','login_include','captcha');
		
		($apihook = $ilance->api('invoicepayment_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'newsell_coin.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();