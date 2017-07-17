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
        'buying',
        'selling',
        'watchlist'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'notify');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[notify]" => $ilcrumbs["$ilpage[notify]"]);

//Captcha validation 
 if(isset($ilance->GPC['captchaa']) AND $ilance->GPC['captchaa'] == 'hi')
 {
	$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTPS_SERVER . 'main-contact' : HTTPS_SERVER . $ilpage['main'] . '?cmd=contact';
	print_notice($phrase['_invalid_message'], "The web browser you&#039;re using does not have Javascript enabled.  You must have Javascript support in your web browser in order to use this site", $url, $phrase['_contact_us']);
	exit();
 }

if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'validate_captcha')

{
 $turing = $ilance->GPC['value'];
  if ($turing != $_SESSION['ilancedata']['user']['captcha'])
 {
		echo 'error';
		exit;
 }
}

if (isset($ilance->GPC['crypted']))
{
	$uncrypted = decrypt_url($ilance->GPC['crypted']);
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'success')
{
		print_notice($phrase['_your_message_was_sent'], $phrase['_your_message_was_sent_and_delivered_to_customer_support'], $ilpage['main'], welcome);
        exit();
}

// #### CONTACT US FORM HANDLER ################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_submit-contactus')
{

	$ip_restrict = $ilance->db->query("SELECT count(*) as cnt  FROM " . DB_PREFIX . "enqiry_email
	WHERE from_ipaddress='".IPADDRESS."'
	AND date(create_date) = '".DATETODAY."'  
	");
	$ip_count = $ilance->db->fetch_array($ip_restrict);	
	if($ip_count['cnt'] < 5 )
	{

		if(isset($ilance->GPC['captcha'])  AND $ilance->GPC['captcha'] != ''  AND isset($ilance->GPC['ufehx']) AND $ilance->GPC['ufehx'] != '' )
		{
				if (isset($ilance->GPC['subject']) AND $ilance->GPC['subject'] == 'other')
				{
				$subject = un_htmlspecialchars($ilance->GPC['subject_other']);	
				}
				else if (isset($ilance->GPC['subject']) AND $ilance->GPC['subject'] == '1')
				{
				$subject = $phrase['_registration'];
				}
				else
				{
				$subject = 'Website Inquiry';	
				}

				$ipaddress=$ilance->db->escape_string($_SERVER['REMOTE_ADDR']);
		
				$txt .= '<div style= "margin-top:0px;"><table style="border: 1px solid #000000; border-collapse: collapse;" cellpadding=10>';

				if (!empty($ilance->GPC['message']))
				{
				$message = un_htmlspecialchars($ilance->GPC['message']);
				}
				else
				{
				$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTPS_SERVER . 'main-contact' : HTTPS_SERVER . $ilpage['main'] . '?cmd=contact';
				print_notice($phrase['_invalid_message'], "invalid message", $url, $phrase['_contact_us']);
				exit();
				}
				
				
				if (!empty($ilance->GPC['ufehx']))
				{
					$email = un_htmlspecialchars($ilance->GPC['ufehx']);
				    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				    $url = ($ilconfig['globalauctionsettings_seourls']) ? HTTPS_SERVER . 'main-contact' : HTTPS_SERVER . $ilpage['main'] . '?cmd=contact';
				print_notice($phrase['_invalid_email_address'], $phrase['_were_sorry_in_order_to_send_a_contact_us_message_you_must_specify_your_email_address_please_try_again'], $url, $phrase['_contact_us']);
				exit();
				    }
 				}
				else
				{
				$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTPS_SERVER . 'main-contact' : HTTPS_SERVER . $ilpage['main'] . '?cmd=contact';
				print_notice($phrase['_invalid_email_address'], $phrase['_were_sorry_in_order_to_send_a_contact_us_message_you_must_specify_your_email_address_please_try_again'], $url, $phrase['_contact_us']);
				exit();
				}
				if (!empty($ilance->GPC['name']))
				{
				$name = un_htmlspecialchars($ilance->GPC['name']);
				}
				else
				{
				$name = 'Guest';
				}


				$txt .= '<tr><td style="background-color: #5B5F68; border: 1px solid #000000;color:#ffffff;"><b>Username: </b></td><td  style="border: 1px solid #000000; ">'.$name. '</td></tr><tr><td style="background-color: #5B5F68; border: 1px solid #000000;color:#ffffff;"><b>E mail id: </b></td><td  style="border: 1px solid #000000; ">'.$email. '</td></tr><tr><td width ="175px" style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>Subject: </b></td><td  style="border: 1px solid #000000; ">'.$subject.'</td></tr><tr><td style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff;"><b>IP Address: </b></td><td  style="border: 1px solid #000000; ">'.$ipaddress.'</td></tr><tr><td style="background-color: #5B5F68; border: 1px solid #000000; color:#ffffff; height: auto;" valign="top"><b>Message: </b></td><td  style="border: 1px solid #000000; " colspan="3" valign="top">'.$message. '</td></tr></table></div>';	
				
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
				$ianemail=$ilconfig['globalserversettings_adminemail'];
				$headers .= 'From: '.$ianemail.''. "\r\n" ;
				
				send_email_enquiry(SITE_EMAIL, $subject, $txt, OWNER_EMAIL,SITE_NAME,true);
				

	            refresh(HTTPS_SERVER . 'notify.php?cmd=success');
				exit();
				
		}
		else
		{
		print_notice('Requesting Failed','Invalid Request.Try Again',$ilpage['main'],$phrase['_main_menu']);
		exit(); 
		}

    }
	else
	{
	 print_notice('Requesting Not Completed','You have reached our online limit for your message was sent to GreatCollections!. Please telephone our office at 1-800-442-6467 to discuss your Message.', $ilpage['main'], welcome);
	  exit(); 

	}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode($ilpage['notify'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>