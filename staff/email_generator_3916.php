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
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_attachment.php');
//print_r($_SESSION);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
  $ilance->subscription = construct_object('api.subscription');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

if(isset($ilance->GPC['txt']))
{

$file = HTTP_SERVER.'email_template/template_'.$_SESSION['ilancedata']['txt']['time'].'.txt';

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

header("Content-Type: application/force-download");
header( "Content-Disposition: attachment; filename=".basename($file));

header( "Content-Description: File Transfer");
@readfile($file);
exit();
}

if(isset($ilance->GPC['var']))
{

$file = HTTP_SERVER.'email_template/template_'.$_SESSION['ilancedata']['time'].'.html';

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

header("Content-Type: application/force-download");
header( "Content-Disposition: attachment; filename=".basename($file));

header( "Content-Description: File Transfer");
@readfile($file);
exit();
}
if(isset($ilance->GPC['you']) and $ilance->GPC['you'] == 'uio')
{

$head = '<div><font face="arial" style="font:x-small" color="#000000"><br/></font></div><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head></head><body><table width="598" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="598" style="border-collapse:collapse;"><!-- start PREHEADER TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="60%" style="border-collapse:collapse; padding-bottom:10px; font-family:verdana,arial; font-size:10px; text-align:left; line-height:13px; color:#999999;">Skip the e-mail! Just go straight to <a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; text-decoration:underline; font-size:10px; color:#999999;">GreatCollections.com</a>.<br /><br />Having problems viewing this email? <a href="http://www.greatcollections.com/emails/';
$head .= $_SESSION['ilancedata']['user']['date_link'];
$head .= '.html" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; text-decoration:underline; font-size:10px; color:#999999;">View it in your browser.</a></td><td width="40%" style="border-collapse:collapse; padding-bottom:10px; font-family:verdana,arial; font-size:10px; text-align:right; line-height:13px; color:#999999;">{VR_SOCIAL_SHARING}<br /><a href="{VR_F2AF_LINK}">Forward to a Friend</a></td></tr></table><!-- end PREHEADER TABLE --></td></tr><tr><td width="598" height="17" style="border-collapse:collapse;"><!-- start GLOBAL NAV TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background-color:#DEE7EC; background:url(\'http://www.greatcollections.com/images/gc/globalnavbg.gif\') center left repeat-x #DEE7EC;"><tr><td width="350" height="17" align="center" style="border:1px solid #B0C4CF; border-bottom:none; border-collapse:collapse; background-color:#DEE7EC; background:url(\'http://www.greatcollections.com/images/gc/globalnavbg.gif\') center left repeat-x #DEE7EC; color:#555555;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" valign="middle" style="background-color:#DEE7EC; background:url(\'http://www.greatcollections.com/images/gc/globalnavbg.gif\') center left repeat-x #DEE7EC;"><tr><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#336699; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">GreatCollections.com</a></td><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=about&referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#336699; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">About Us</a></td><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=sell&referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#336699; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">Consign</a></td><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=contact&referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#336699; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">Contact Us</a></td></tr></table></td><td width="250" bgcolor="#FFFFFF" style="border-collapse:collapse;"></td></tr></table><!-- end GLOBAL NAV TABLE --></td></tr></table><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="border:1px solid #B0C4CF; background:#FFF;"><tr><td width="600" height="90" valign="middle" style="border-collapse:collapse; background:#fafaf3"><!-- start HEADER TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="200" height="90" align="left" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none;"><img src="http://www.greatcollections.com/images/gc/logoBLUE_275px_x.gif" width="275" height="60" alt="GreatCollections Coin Auctions" title="GreatCollections Coin Auctions" border="0" style="display:block;" /></a></td><td width="400" height="90" style="text-align:center;"><span style="border-collapse:collapse; text-align:center; font-family:verdana,arial; font-size:15px; color:#2c5987;">';
$head.=	$_SESSION['ilancedata']['user']['auction_date'];
$head.=	'<br /><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#336699; font-family:verdana,arial; font-size:15px; text-decoration:none;">www.greatcollections.com</a></span></span><br />   <span style="border-collapse:collapse; text-align:center; font-family:verdana,arial; font-size:11px; color:#2c5987;">New Coins Listed Daily</span><br /></td></tr></table><!-- end HEADER TABLE --></td></tr><tr><td width="600" height="20" valign="middle" style="border-collapse:collapse;"><!-- start NAV TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background-color:#2C5987; background:url(\'http://www.greatcollections.com/images/gc/nav-bg.jpg\') center left repeat-x #2C5987;"><tr>      <td width="600" height="21" align="center" style="background-color:#2C5987; background:url(\'http://www.greatcollections.com/images/gc/nav-bg.jpg\') center left repeat-x #2C5987;">       <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" valign="middle" style="background-color:#2C5987; background:url(\'http://www.greatcollections.com/images/gc/nav-bg.jpg\') center left repeat-x #2C5987;"><tr><td align="center" style="border-collapse:collapse;"><a href="#" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">Browse Coins</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?mode=product&series=55&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">Silver Dollars</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/24hours.php?referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">24-Hour Deals</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?mode=product&q=&series=&sort=12&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">$1,000+ Certified Coins</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?mode=product&q=&series=&sort=11&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">$1-$100 Certifed Coins</a></td></tr></table></td></tr></table><!-- end NAV TABLE --></td></tr><tr><td width="598" style="border-collapse:collapse; padding:14px;">';
$featured='<td align=left width="380"><p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:12px; color:555555;" align="center"><strong>Browse Over ';
$featured.=$_SESSION['ilancedata']['user']['total_coins'];
$featured.=' Coin Auctions<br />No Hidden Reserves - Items Bid from $1 to $25,000+</strong><br /><Br /><strong>Great</strong>Collections offers:<br /><strong>Value:</strong>  The lowest fees, whether buying or selling.<br /><strong>Selection:</strong>  Over ';
$featured.=$_SESSION['ilancedata']['user']['total_coins1'];						
$featured.=' certified coins currently listed.<br /><strong>Service:</strong>  Fast, professional customer service.<br /><br /><strong>All first-time U.S. auction winners receive free shipping.</strong><br /><br /><A href="https://www.greatcollections.com/registration.php?referal_name=email01"><strong style="color:#336699;">Register to Bid and Be Part of the Action</strong></A><br />Its fast and free, and gives you instant access to bid, our auction archives, your personal watchlist and more.<br /><br />Ian Russell, Raeleen Endo and the <strong>Great</strong>Collections Team</td></tr></table></td></tr>';
$foot = '</td></tr><tr><td width="598" style="border-collapse:collapse;"><!-- start FOOTER TABLE --><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="border-top:1px dashed #929293; text-align:center; background:#E1E9F3;"><tr><td width="600" height="20" valign="bottom" align="center" style="padding:0 15px; border-collapse:collapse; background:#E1E9F3;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background:#E1E9F3;"><tr><td align="center" style="border-collapse:collapse;"><a href="https://www.greatcollections.com/main.php?cmd=cp&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">MyGC</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/watchlist.php?referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Watchlist</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=about&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">About Us</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/kb/" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Help</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=contact&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Contact Us</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=promise&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Trust</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=shows&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Coin Shows</a></td></tr></table></td></tr><tr><td width="600" height="20" valign="middle" align="center" style="padding:0 10px; border-collapse:collapse; background:#E1E9F3;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background:#E1E9F3;"><tr><td align="center" style="border-collapse:collapse;"><a href="https://www.greatcollections.com/main.php?cmd=through&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Bidding/Buying Coins Through GreatCollections</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.facebook.com/GreatCollections" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Facebook</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://twitter.com/greatcollection" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Follow Us On Twitter</a></td></tr></table></td></tr><tr><td width="600" height="70" valign="top" align="center" style="border-collapse:collapse;"><p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:10px; color:#555555;">This message was sent to <a href="mailto:{EMAIL_ADDRESS}" style="font-family:verdana,arial; font-size:10px; color:#336699; color:#336699; text-decoration:none;">{EMAIL_ADDRESS}</a>.<br />Please add <a href="mailto:info@greatcollections.com" style="font-family:verdana,arial; font-size:10px; color:#336699; color:#336699; text-decoration:none;">info@greatcollections.com</a> to your address book to ensure our emails reach your inbox!</p><p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:10px; color:#555555;">&#169; 2010 - '.CURRENTYEAR.' <a href="http://www.greatcollections.com/main.php?referal_name=email01" style="font-family:verdana,arial; font-size:11px; color:#555555; text-decoration:none; font-size:10px; color:#336699;">GreatCollections.com</a>, LLC<br />2030 Main Street, Suite 620, Irvine, CA 92614<br />Tel: 1.800.44.COINS (+1.949.679.4180)</p></td></tr></table><!-- end FOOTER TABLE --></td></tr></table></body></html>';

// $all = $head.''.$ilance->GPC['user'].''.$foot;
$_SESSION['ilancedata']['time'] = date('Y-m-d-h-i-s');	  
 $all = $head.''.$_SESSION['ilancedata']['user']['featured'].''.$featured.''.$_SESSION['ilancedata']['user']['item'].''.$foot;
$handle = $all;
$f=@fopen("/home/gc/public_html/email_template/template_".$_SESSION['ilancedata']['time'].".html","w");
fwrite($f,$handle);
fclose($f);

exit();

}

if(isset($ilance->GPC['text']) and $ilance->GPC['text'] == 'text_temp')
{

$head.='View this e-mail in your browser:'."\r\n";
$head.='http://www.greatcollections.com/emails/template.html'."\r\n"."\r\n";
$head.='------------------------------------------------------------------'."\r\n";
$head.=$_SESSION['ilancedata']['user']['auction_date']."\r\n";
$head.='Over ';
$head.=$_SESSION['ilancedata']['user']['total_coins'];
$head.=' Certified Coins Currently Listed'."\r\n";
$head.='No Hidden Reserves, Many Coins Bid from $1'."\r\n";
$head.='------------------------------------------------------------------'."\r\n"."\r\n";
$head.='Featured Coin Auction'."\r\n"."\r\n";


	  
$featured.='New Auctions Are Uploaded Daily - Visit Our Website Often '."\r\n"."\r\n";
$featured.='All first-time U.S. auction winners receive free shipping.'."\r\n"."\r\n";
$featured.='Register to Bid and Be Part of the Action'."\r\n";
$featured.='Its fast and free, and gives you instant access to bid, our auction archives, your personal watchlist and more.'."\r\n";
$featured.=' http://www.greatcollections.com/registration.php'."\r\n"."\r\n";
$featured.='Ian Russell, Raeleen Endo & the GreatCollections Team'."\r\n"."\r\n";
$featured.='------------------------------------------------------------------'."\r\n";
$featured.='Auction Highlights'."\r\n";
$featured.='------------------------------------------------------------------'."\r\n"."\r\n";
$featured.='http://www.greatcollections.com/main.php?referal_name='.$_SESSION['ilancedata']['txt']['referal']."\r\n"."\r\n";


$foot.='-----------------------------------------------------------------'."\r\n"."\r\n";
$foot.='Questions about GreatCollections or Coins?'."\r\n";
$foot.='Tel: 1.800.44.COINS (+1.949.679.4180)'."\r\n";
$foot.='E-mail: info@greatcollections.com'."\r\n";
$foot.='Website: www.greatcollections.com'."\r\n"."\r\n";
$foot.='-----------------------------------------------------------------'."\r\n"."\r\n";
$foot.='-----------------------------------------------------------------'."\r\n"."\r\n";
$foot.='GreatCollections.com, LLC'."\r\n";
$foot.='2030 Main Street, Suite 620, Irvine, CA 92614'."\r\n";
$foot.='-----------------------------------------------------------------'."\r\n"."\r\n";
$foot.='*** You are currently subscribed as: {EMAIL_ADDRESS}';

	  
	
	// $all = $head.''.$ilance->GPC['user'].''.$foot;
$_SESSION['ilancedata']['txt']['time'] = date('Y-m-d-h-i-s');	  
 $all = $head.''.$_SESSION['ilancedata']['txt']['featured'].''.$featured.''.$_SESSION['ilancedata']['txt']['item'].''.$foot;
$handle = $all;
$f=@fopen("/home/gc/public_html/email_template/template_".$_SESSION['ilancedata']['txt']['time'].".txt","w");
fwrite($f,$handle);
fclose($f);

exit();

}

if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view')
{

	$_SESSION['subs'] = $ilance->GPC['subs'];
	$_SESSION['cou'] = $ilance->GPC['cou'];
	$_SESSION['itemf'] = $ilance->GPC['itemf'];
	$_SESSION['items'] = $ilance->GPC['items'];
	$_SESSION['itemt'] = $ilance->GPC['itemt'];
    $_SESSION['feat'] = $ilance->GPC['featured'];
	$_SESSION['featr'] = $ilance->GPC['featured_ref'];
	/*-- Tamil for Bug 2526 * Starts -- */
	$_SESSION['ilancedata']['user']['auction_date'] = $auction_date = $ilance->GPC['auction_date'];
	$_SESSION['ilancedata']['user']['total_coins'] = $total_coins = $ilance->GPC['total_coins'];
	$_SESSION['ilancedata']['user']['total_coins1'] = $total_coins1 = $ilance->GPC['total_coins1'];
	$_SESSION['ilancedata']['user']['date_link'] = $date_link = $ilance->GPC['date_link'];
	/*-- Tamil for Bug 2526 * Ends --*/
	$_SESSION['ilancedata']['txt']['referal']=$ilance->GPC['featured_ref'];

unset($myfeat);
  $select_featured= $ilance->db->query("select *
				                          from " . DB_PREFIX . "projects 
					                       where project_id='".$ilance->GPC['featured']."' and
											project_state = 'product' and
											visible = '1'
										   ");
		if ($ilance->db->num_rows($select_featured) > 0)
		{
	
			$c = 0;
			while($row_pre_fea = $ilance->db->fetch_array($select_featured))
			{
			
			           $sql_coin = $ilance->db->query("
                        SELECT coin_detail_year,coin_detail_denom_short FROM
                        " . DB_PREFIX . "catalog_coin
                        WHERE Orderno='".$row_pre_fea['Orderno']."'
						
                        ");
				    $fetch_coin=$ilance->db->fetch_array($sql_coin);
					
					$alt=$fetch_coin['coin_detail_year'].' '.$fetch_coin['coin_detail_denom_short'];
			
			    	$filename = '/home/gc/public_html/'.$ilconfig['email_image_folder'].'/'.$row_pre_fea['project_id'].'-1.jpg';

                    if (file_exists($filename)) 
					
					{
					  
					   $uselistra = HTTP_SERVER.$ilconfig['email_image_folder'].'/'.$row_pre_fea['project_id'].'-1.jpg';
					   
					   list($width,$height) = getimagesize($uselistra);
					 
					}
					
					else
					
					{   
			
			            $sql_attya = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$row_pre_fea['project_id']."'
						AND attachtype='itemphoto' order by attachid desc
						
                        ");
						$fetch_newa=$ilance->db->fetch_array($sql_attya);
						
						if($ilance->db->num_rows($sql_attya) > 0)
						{
						
                
							
						$uselistra = HTTP_SERVER.$ilpage['attachment'] . '?cmd=thumb&subval=acc&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=150'; 
						
						list($width,$height) = getimagesize($uselistra);
							
						}
						if($ilance->db->num_rows($sql_attya) == 0)
					
						{
							
							 $uselistra =HTTP_SERVER.'images/gc/nophoto.gif';	
							 
							 list($width,$height) = getimagesize($uselistra);						
						}
					
					}

				$htma ='<img width="'.$width.'" height="'.$height.'"   border="0" style="display:block;" alt="'.$alt.'" title="'.$alt.'" src="'.$uselistra.'" >';

				
					if($row_pre_fea['filtered_auctiontype'] == 'regular')
				          $type = 'Current Bid';
				           else
				           $type = 'Buy Now';
						   
						  // $currencyid = fetch_auction('currencyid', intval($row_pre_fea['project_id']));
				/*if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeat.= '<a href="https://www.greatcollections.com/herakle/Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">';
						else
						$myfeat.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'">';*/
						
						 setlocale(LC_MONETARY, 'en_US');
						$currentprice= money_format('%.0n',$row_pre_fea['currentprice']);
						
						/*$myfeat= ' <td width="190" align="center" style="line-height:16px;"> '. $htma.'<br>

            <a href="https://www.greatcollections.com/herakle/Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'" style="color:rgb(0-0-255); font-family:verdana,arial; font-size:11px; text-decoration:none; line-height:16px;text-decoration:underline;">'.$row_pre_fea['project_title'].'<br />'.$type.' '.$currentprice.'
          </a>
        </td>';*/
		
	    $arr = fetch_coin_table('Grading_Service',$row_pre_fea['project_id']);
		
		if($arr)
		{
		  $new_arr = str_replace(' '.$arr,'<br>'.$arr,$row_pre_fea['project_title']);
		}
		else
		{
		  $new_arr = str_replace($arr,'<br>'.$arr,$row_pre_fea['project_title']);
		}
		               $myfeat= ' <table width="570" border="0" cellpadding="0" cellspacing="0">

  <tr>
    <td style="border-collapse:collapse;">
      <table width="570" border="0" cellpadding="0" cellspacing="0">
       <tr> <td width="190" align="center" style="line-height:16px;">

            <a href="http://www.greatcollections.com/merch.php?id='.$row_pre_fea['project_id'].'&referal_name='.$ilance->GPC['featured_ref'].'" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; line-height:16px;text-decoration:underline;">'.$htma.'<br>'.$new_arr.'<br />'.$type.' '.$currentprice.'
          </a>
        </td>';
		
		 $txt_feat =$row_pre_fea['project_title']."\r\n".$type.' '.$currentprice."\r\n"."\r\n";
				
		}
		$_SESSION['ilancedata']['txt']['featured']=$txt_feat;
		
		$_SESSION['ilancedata']['user']['featured']=$myfeat;
		}
		
		$item.='<tr>
  <td width="570" style="padding-top:10px; border-collapse:collapse;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="padding-top:10px; padding-bottom:20px;">';
	
		for($i=0;$i<count($ilance->GPC['sub_heading']);$i++)
		{
		$subheading=$ilance->GPC['sub_heading'][$i];
		 $item.='<tr><td>&nbsp;</td></tr><tr><TD width="190"><P style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; font-weight:bold;">'.$subheading.'</P></TD></tr><tr><td>&nbsp;</td></tr>';
		
		 $item.='<tr>';
		 
		 $txt_item.=$subheading."\r\n";
		 $txt_item.='____________________'."\r\n"."\r\n";
	   
		    $item_list[]=$ilance->GPC['item1'][$i];
			$item_list[]=$ilance->GPC['item2'][$i];
			$item_list[]=$ilance->GPC['item3'][$i];
	        $item_arr=array_filter($item_list);
			
		
			for($j=0;$j<count($item_arr);$j++)
			{
					
		  // $itm_filter=array_filter($itm);
	
	           $select_series= $ilance->db->query("select *
				   from 
					
					" . DB_PREFIX . "projects 
					where project_id='". $item_arr[$j]."'
					AND project_state = 'product' 
					AND visible = '1'
                   ");
				 
		   if ($ilance->db->num_rows($select_series) > 0)
		    {
	
		
			while($row_series = $ilance->db->fetch_array($select_series))
			{
			
			        $sql_alt = $ilance->db->query("
                        SELECT coin_detail_year,coin_detail_denom_short FROM
                        " . DB_PREFIX . "catalog_coin
                        WHERE Orderno='".$row_series['Orderno']."'
						
                        ");
				    $fetch_alt=$ilance->db->fetch_array($sql_alt);
					
					$alt_det=$fetch_alt['coin_detail_year'].' '.$fetch_alt['coin_detail_denom_short'];
			
			        $filename = '/home/gc/public_html/'.$ilconfig['email_image_folder'].'/'.$row_series['project_id'].'-1.jpg';

                    if (file_exists($filename)) 
					
					{
					  
					  $uselistra = HTTP_SERVER.$ilconfig['email_image_folder'].'/'.$row_series['project_id'].'-1.jpg';
					  
					  list($width,$height) = getimagesize($uselistra);
					 
					}
					
					else
					
					{   
			
			            $sql_attach = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$row_series['project_id']."'
						AND attachtype='itemphoto' order by attachid desc
						
                        ");
				    $fetch_series=$ilance->db->fetch_array($sql_attach);
					
					if($ilance->db->num_rows($sql_attach) > 0)
					{
					
                        
							
					 $uselistra = HTTP_SERVER.$ilpage['attachment'] . '?cmd=thumb&subval=acc&subcmd=itemphoto&id=' . $fetch_series['filehash'] .'&w=170&h=150'; 
					 
					 list($width,$height) = getimagesize($uselistra);
						
			     	}
					if($ilance->db->num_rows($sql_attach) == 0)
				
					{
					    
					    $uselistra =HTTP_SERVER.'images/gc/nophoto.gif';			
						
						list($width,$height) = getimagesize($uselistra);			
					}
					
					}


					$htm ='<img width="'.$width.'" height="'.$height.'" border="0" style="display:block;" alt="'.$alt_det.'" title="'.$alt_det.'" src="'.$uselistra.'">';
					
					if($row_series['filtered_auctiontype'] == 'regular')
				          $type = 'Current Bid';
				           else
				           $type = 'Buy Now';
				if ($ilconfig['globalauctionsettings_seourls'])	
				{
						$series['url']= '<a href="http://www.greatcollections.com/Coin/'.$row_series['project_id'].'/'.construct_seo_url_name($row_series['project_title']).'">';
				}		
						else
				{		
						$series['url']= '<a href="merch.php?id='.$row_series['project_id'].'">';
				}		
				
			
						$projectid=$row_series['project_id'];
						$projecttitle=$row_series['project_title'];
						 //$currencyid = fetch_auction('currencyid', intval($row_series['project_id']));
						 setlocale(LC_MONETARY, 'en_US');
						$currentprice= money_format('%.0n',$row_series['currentprice']);
						
		$arr = fetch_coin_table('Grading_Service',$row_series['project_id']);
		
		if($arr)
		{
		$new_arr = str_replace(' '.$arr,'<br />'.$arr,$row_series['project_title']);
		}
		else
		{
		 $new_arr = str_replace($arr,'<br />'.$arr,$row_series['project_title']);
		}
	
	                   
						/*$item.='<td width="190" align="center" style="line-height:16px;">'.$htm.'<br />
		  <a href="https://www.greatcollections.com/herakle/Coin/'.$row_series['project_id'].'/'.construct_seo_url_name($row_series['project_title']).'" style="color:rgb(0-0-255); font-family:verdana,arial; font-size:11px; text-decoration:none; line-height:16px;text-decoration:underline;">'.$projecttitle.'<br />'.$type.' '.$currentprice.'</span>
          </a>
        </td>';*/
		
		            $item.='<td width="190" align="center" style="line-height:16px;">
		  <a href="http://www.greatcollections.com/merch.php?id='.$row_series['project_id'].'&referal_name='.$ilance->GPC['featured_ref'].'" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; line-height:16px;text-decoration:underline;">'.$htm.'<br />'.$new_arr.'<br />'.$type.' '.$currentprice.'</span>
          </a>
        </td>';
		
		$txt_item.=$row_series['project_title']."\r\n".$type.' '.$currentprice."\r\n"."\r\n";

$c++;
}

}

}
	        unset($item_list);
			
}

$item.='</tr>';

$item.='</table></td></tr></table></td></tr>';

$_SESSION['ilancedata']['txt']['item']=$txt_item;


$_SESSION['ilancedata']['user']['item']=$item;

  $pprint_array = array('date_link','total_coins1','total_coins','auction_date','myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','latestviews','list','myfeat','subheading','series','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','heading','item');

         ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	    $ilance->template->fetch('main', 'email_generator_view.html',2);
      	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	    $ilance->template->parse_loop('main', array('series_list'));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();


	}	
	
if (isset($ilance->GPC['ck']) AND $ilance->GPC['ck']== 'op')
{
$css = 'check(\''.$_SESSION['feat'].'\',\''.$_SESSION['featr'].'\',\''.$_SESSION['cou'].'\',\''.$_SESSION['subs'].'\',\''.$_SESSION['itemf'].'\',\''.$_SESSION['items'].'\',\''.$_SESSION['itemt'].'\')';
 
 $onload = $css;

$couo = $_SESSION['cou'];
}

$couo = ($ilance->GPC['ck']== 'op') ? $_SESSION['cou'] : 1;

        $pprint_array = array('couo','myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','latestviews','list','myfeat','subheading','numbers','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

         ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	    $ilance->template->fetch('main', 'email_generator.html',2);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val','series_list');
		$ilance->template->parse_loop('main', 'info_feat');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();

	
}		
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>