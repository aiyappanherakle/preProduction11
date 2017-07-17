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

/* TAMIL for Bug 2503 on 26Mar13 * Starts */

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

$area_title = 'Buying Coins';
$page_title = SITE_NAME . ' - WantList Form To GreatCollections Coin Auctions';

// construct breadcrumb trail
$navcrumb = array();
$navcrumb["main-sell"] = $area_title;
$navcrumb[""] = 'Send a WantList';

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


//Inventory File/Spreadsheet Download	
	
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'app_pdf')	
{
	if(!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
	{
			 $sql = $ilance->db->query("SELECT * FROM ".DB_PREFIX."wantlist_inventory 
									   WHERE filehash = '".$ilance->db->escape_string($ilance->GPC['pdf_id'])."'
									   LIMIT 1
									  ");    
							 
							$row_pdf = $ilance->db->fetch_array($sql);				 
							
							$attachment['filename'] = $row_pdf['filename'];
							
							$attachment['filehash'] = $row_pdf['filehash'].'.attach';

							$attachment['filedata'] = file_get_contents(DIR_SERVER_ROOT.'wantlist_inventroy/'.$attachment['filehash']);
	   
							
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
   	
	$target_path = DIR_SERVER_ROOT.'wantlist/';
	$inventory_path = DIR_SERVER_ROOT.'wantlist_inventory/';
	$items = $_FILES['item3'];
	//$pur = $ilance->GPC['purchaser'];

	$pcgs=(isset($ilance->GPC['pcgs']))?$ilance->GPC['pcgs']:0;
	$ngc=(isset($ilance->GPC['ngc']))?$ilance->GPC['ngc']:0;
	$anacs=(isset($ilance->GPC['anacs']))?$ilance->GPC['anacs']:0;
	$ogp_raw=(isset($ilance->GPC['ogp_raw']))?$ilance->GPC['ogp_raw']:0;

	$firstins = $ilance->db->query("INSERT INTO " . DB_PREFIX . "wantlist_new
							(name,email,phone,comments,pcgs,ngc,anacs,ogp_raw,ip_address,date_added)
							VALUES(
							'".$ilance->db->escape_string($ilance->GPC['name'])."',
							'".$ilance->db->escape_string($ilance->GPC['email'])."',
							'".$ilance->db->escape_string($ilance->GPC['tel'])."',
							'".$ilance->db->escape_string($ilance->GPC['comments'])."',
							'".$pcgs."',
							'".$ngc."',
							'".$anacs."',
							'".$ogp_raw."',
							'".IPADDRESS."',
							'" . DATETIME24H . "'
						   )
						   ", 0, null, __FILE__, __LINE__);
		
	$want_id = $ilance->db->insert_id();


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

				
			$ilance->db->query("INSERT INTO " . DB_PREFIX . "wantlist_inventory
						   (want_id,filename,filetype,filesize,filehash)
						   VALUES(
							'".$want_id."',
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


	

	$select = $ilance->db->query("SELECT *
						 FROM " . DB_PREFIX . "wantlist_new
						 WHERE 	id ='".$want_id."' 
					  ");
	if($ilance->db->num_rows($select) > 0)						  
	{   							  
		while($row = $ilance->db->fetch_array($select))
		{
			$username = $row['name'];
			$useremail = $row['email'];
			
			$comments = $row['comments'];
			$phone_no = $row['phone'];
			
		}
	} 

	$select_pdf = $ilance->db->query("SELECT filehash FROM " . DB_PREFIX . "wantlist_inventory
							   WHERE want_id='".$want_id."'
						");
						
	if($ilance->db->num_rows($select_pdf) > 0)						  
	{   							
		$res_pdf = $ilance->db->fetch_array($select_pdf);
		$invfiledata = $res_pdf['filehash'];
		$pdffiles = 'Added Inventory files click here to download:'.HTTP_SERVER.'coin_wantlist.php?cmd=app_pdf&pdf_id='.$invfiledata;
	}



	//Mail

	$message =$username.' requested the following coin estimate from GreatCollections.

	Email: '.$useremail.'

	Telephone number: '.$phone_no.'	

	Information:'.$comments.'

	'.$pdffiles.'
	
	------------------------------------------------------------------

	This email was generated on: '.$ilconfig['official_time'].'
	Email ID: 235';

	send_email($ilconfig['globalserversettings_developer_email'],'Request an Auction Estimate Page', $message,$useremail,$username);

	print_notice('Requesting Complete','Thank you for Sending us your WantList. A representative will be in touch in the next 24-48 hours to discuss your request',$ilpage['main'],'Home');
	exit(); 

}

$captcha = '<img src="'.HTTPS_SERVER.'attachment.php?do=captcha" alt="'.'{_please_enter_the_security_code_shown_on_the_image_to_continue_registration}'.'" border="0" />';

if(!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	$name=$_SESSION['ilancedata']['user']['userid']['fullname'];
	$email=$_SESSION['ilancedata']['user']['userid']['email'];
	$sql = $ilance->db->query("	SELECT *
	FROM " . DB_PREFIX . "users 
	WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'			
	LIMIT 1
	");
	if($ilance->db->num_rows($sql) > 0)
	{
		$res=$ilance->db->fetch_array($sql, DB_ASSOC);
	}
	$telephone=$res['phone'];
}
else
{
	$name='';
	$email='';
	$telephone='';
}

$pprint_array = array('name','email','telephone','area_title','page_title','site_name','https_server','http_server','login_include','captcha');

($apihook = $ilance->api('invoicepayment_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'coin_wantlist.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
		
/* TAMIL for Bug 2503 on 26Mar13 * Ends */
?>