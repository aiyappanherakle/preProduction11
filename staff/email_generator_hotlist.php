<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright �2000�2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration',
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
	'flashfix',
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once './../functions/config.php';

require_once DIR_CORE . 'functions_attachment.php';
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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1') {

	if (isset($ilance->GPC['txt'])) {

		$file = HTTP_SERVER . 'email_template/template_' . $_SESSION['ilancedata']['txt']['time'] . '.txt';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=" . basename($file));

		header("Content-Description: File Transfer");
		@readfile($file);
		exit();
	}
//vijay work for  CCE Text starts 
	if (isset($ilance->GPC['cce_txt'])) {

		$file = HTTP_SERVER . 'email_template/template_cce_text_' . $_SESSION['ilancedata']['cce_txt']['time'] . '.txt';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=" . basename($file));

		header("Content-Description: File Transfer");
		@readfile($file);
		exit();
	}
//vijay work for  CCE Text ends
	if (isset($ilance->GPC['var'])) {

		$file = HTTP_SERVER . 'email_template/template_' . $_SESSION['ilancedata']['time'] . '.html';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=" . basename($file));

		header("Content-Description: File Transfer");
		@readfile($file);
		exit();
	}
	if (isset($ilance->GPC['you']) and $ilance->GPC['you'] == 'uio') {

		$head = '<div><font face="arial" style="font:x-small" color="#000000"><br/></font></div><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head></head><body><table width="598" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="598" style="border-collapse:collapse;"><!-- start PREHEADER TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="60%" style="border-collapse:collapse; padding-bottom:10px; font-family:verdana,arial; font-size:10px; text-align:left; line-height:13px; color:#999999;">Skip the e-mail! Just go straight to <a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; text-decoration:underline; font-size:10px; color:#999999;">GreatCollections.com</a>.<br /><br />Having problems viewing this email? <a href="http://www.greatcollections.com/emails/';
		$head .= $_SESSION['ilancedata']['user']['date_link'];
		$head .= '.html" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; text-decoration:underline; font-size:10px; color:#999999;">View it in your browser.</a></td><td width="40%" style="border-collapse:collapse; padding-bottom:10px; font-family:verdana,arial; font-size:10px; text-align:right; line-height:13px; color:#999999;">{VR_SOCIAL_SHARING}<br /><a href="{VR_F2AF_LINK}">Forward to a Friend</a></td></tr></table><!-- end PREHEADER TABLE --></td></tr><tr><td width="598" height="17" style="border-collapse:collapse;"><!-- start GLOBAL NAV TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background-color:#DEE7EC; background:url(\'http://www.greatcollections.com/images/gc/globalnavbg.png\') center left repeat-x #DEE7EC;"><tr><td width="350" height="17" align="center" style="border:1px solid #B0C4CF; border-bottom:none; border-collapse:collapse; background-color:#DEE7EC; background:url(\'http://www.greatcollections.com/images/gc/globalnavbg.png\') center left repeat-x #DEE7EC; color:#555555;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" valign="middle" style="background-color:#DEE7EC; background:url(\'http://www.greatcollections.com/images/gc/globalnavbg.png\') center left repeat-x #DEE7EC;"><tr><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#000000; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">GreatCollections.com</a></td><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=about&referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#000000; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">About Us</a></td><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=sell&referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#000000; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">Consign</a></td><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=contact&referal_name=email01" style="margin:0; padding:0 3px 0 7px; color:#000000; font-family:verdana,arial; font-size:10px; font-weight:bold; text-decoration:none;">Contact Us</a></td></tr></table></td><td width="250" bgcolor="#FFFFFF" style="border-collapse:collapse;"></td></tr></table><!-- end GLOBAL NAV TABLE --></td></tr></table><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="border:1px solid #B0C4CF; background:#FFF;"><tr><td width="600" height="90" valign="middle" style="border-collapse:collapse; background:#2f2f2f"><!-- start HEADER TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center"><tr><td width="200" height="90" align="left" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none;"><img src="http://www.greatcollections.com/images/gc/logowhite_275px_x.png" width="275" height="60" alt="GreatCollections Coin Auctions" title="GreatCollections Coin Auctions" border="0" style="display:block;" /></a></td><td width="400" height="90" style="text-align:center;"><span style="border-collapse:collapse; text-align:center; font-family:verdana,arial; font-size:15px; color:#2c5987;">';
		$head .= $_SESSION['ilancedata']['user']['auction_date'];
		$head .= '<br /><a href="http://www.greatcollections.com/main.php?referal_name=email01" style="color:#fff; font-family:verdana,arial; font-size:15px; text-decoration:none;">www.greatcollections.com</a></span></span><br />   <span style="border-collapse:collapse; text-align:center; font-family:verdana,arial; font-size:18px;font-weight:bold; color:#fff;text-transform:uppercase;">Hot List Coins</span><br /></td></tr></table><!-- end HEADER TABLE --></td></tr><tr><td width="600" height="20" valign="middle" style="border-collapse:collapse;"><!-- start NAV TABLE --><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background-color:#2C5987; background:url(\'http://www.greatcollections.com/images/gc/nav-bg.jpg\') center left repeat-x #2C5987;"><tr>      <td width="600" height="21" align="center" style="background-color:#2C5987; background:url(\'http://www.greatcollections.com/images/gc/nav-bg.jpg\') center left repeat-x #2C5987;">       <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" valign="middle" style="background-color:#2C5987; background:url(\'http://www.greatcollections.com/images/gc/nav-bg.jpg\') center left repeat-x #2C5987;"><tr><td align="center" style="border-collapse:collapse;"><a href="http://www.greatcollections.com/Denominations?referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">Browse Coins</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?mode=product&series=55&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">Silver Dollars</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/24hours.php?referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">24-Hour Deals</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?mode=product&q=&series=&sort=12&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">$1,000+ Certified Coins</a></td><td align="center" style="border-left:1px solid #fff; border-collapse:collapse;"><a href="http://www.greatcollections.com/search.php?mode=product&q=&series=&sort=11&referal_name=email01" style="padding:0 6px; font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; text-transform:none;">$1-$100 Certifed Coins</a></td></tr></table></td></tr></table><!-- end NAV TABLE --></td></tr>';

		$foot = '<tr><td width="598" style="border-collapse:collapse;"><!-- start FOOTER TABLE --><table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="border-top:1px dashed #929293; text-align:center; background:#2f2f2f;"><tr><td width="600" height="20" valign="bottom" align="center" style="padding:0 15px; border-collapse:collapse; background:#2f2f2f;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background:#2f2f2f;"><tr><td align="center" style="border-collapse:collapse;"><a href="https://www.greatcollections.com/main.php?cmd=cp&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">MyGC</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/watchlist.php?referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Watchlist</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=about&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">About Us</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/kb/" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Help</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=contact&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Contact Us</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=promise&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Trust</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.greatcollections.com/main.php?cmd=shows&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Coin Shows</a></td></tr></table></td></tr><tr><td width="600" height="20" valign="middle" align="center" style="padding:0 10px; border-collapse:collapse; background:#2f2f2f;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="background:#2f2f2f;"><tr><td align="center" style="border-collapse:collapse;"><a href="https://www.greatcollections.com/main.php?cmd=through&referal_name=email01" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Bidding/Buying Coins Through GreatCollections</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://www.facebook.com/GreatCollections" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Facebook</a></td><td align="center" style="border-left:1px solid #B0C4CF; border-collapse:collapse;"><a href="http://twitter.com/greatcollection" style="padding:0 4px; border-left:none; font-family:verdana,arial; font-size:10px; color:#336699; text-decoration:none; font-weight:bold;">Follow Us On Twitter</a></td></tr></table></td></tr><tr><td width="600" height="70" valign="top" align="center" style="border-collapse:collapse;"><p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:10px; color:#fff;">This message was sent to <a href="mailto:{EMAIL_ADDRESS}" style="font-family:verdana,arial; font-size:10px; color:#ff610a; text-decoration:none;">{EMAIL_ADDRESS}</a>.<br />Please add <a href="mailto:info@greatcollections.com" style="font-family:verdana,arial; font-size:10px; color:#ff610a; text-decoration:none;">info@greatcollections.com</a> to your address book to ensure our emails reach your inbox!</p><p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:10px; color:#fff;">&#169; 2010 - ' . CURRENTYEAR . ' <a href="http://www.greatcollections.com/main.php?referal_name=email01" style="font-family:verdana,arial; font-size:11px; color:#fff; text-decoration:none; font-size:10px; color:#ff610a;">GreatCollections.com</a>, LLC<br />17500 Red Hill Avenue, Suite 160, Irvine, CA 92614<br />Tel: 1.800.44.COINS (+1.949.679.4180)</p></td></tr></table><!-- end FOOTER TABLE --></td></tr></table></body></html>';

// $all = $head.''.$ilance->GPC['user'].''.$foot;
		$_SESSION['ilancedata']['time'] = date('Y-m-d-h-i-s');
		$all = $head . '' . $_SESSION['ilancedata']['user']['featured'] . '' . $featured . '' . $_SESSION['ilancedata']['user']['item'] . '' . $foot;
		$handle = $all;
		$f = @fopen("/home/gc/public_html/email_template/template_" . $_SESSION['ilancedata']['time'] . ".html", "w");
		fwrite($f, $handle);
		fclose($f);

		exit();

	}

	if (isset($ilance->GPC['text']) and $ilance->GPC['text'] == 'text_temp') {

		$head .= 'View this e-mail in your browser:' . "\r\n";
		$head .= 'http://www.greatcollections.com/emails/template.html' . "\r\n" . "\r\n";
		$head .= '------------------------------------------------------------------' . "\r\n";
		$head .= 'The HotList at GreatCollections' . "\r\n";
		$head .= 'Bidding Ends Sunday for These Highlights' . "\r\n";
		$head .= '------------------------------------------------------------------' . "\r\n" . "\r\n";


		$foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$foot .= 'Questions about GreatCollections or Coins?' . "\r\n";
		$foot .= 'Tel: 1.800.44.COINS (+1.949.679.4180)' . "\r\n";
		$foot .= 'E-mail: info@greatcollections.com' . "\r\n";
		$foot .= 'Website: www.greatcollections.com' . "\r\n" . "\r\n";
		$foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$foot .= 'GreatCollections.com, LLC' . "\r\n";
		$foot .= '17500 Red Hill Avenue, Suite 160, Irvine, CA 92614' . "\r\n";
		$foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$foot .= '*** You are currently subscribed as: {EMAIL_ADDRESS}';

		// $all = $head.''.$ilance->GPC['user'].''.$foot;
		$_SESSION['ilancedata']['txt']['time'] = date('Y-m-d-h-i-s');
		$all = $head . '' . $_SESSION['ilancedata']['txt']['item'] . '' . $foot;
		$handle = $all;
		$f = @fopen("/home/gc/public_html/email_template/template_" . $_SESSION['ilancedata']['txt']['time'] . ".txt", "w");
		fwrite($f, $handle);
		fclose($f);

		exit();

	}

	//vijay work for  CCE Text starts 

	if (isset($ilance->GPC['ccetext']) and $ilance->GPC['ccetext'] == 'cce_text') {

		
		$cce_head .= 'GreatCollections Auction Highlights' . "\r\n";
		$cce_head .= 'Bidding Ends Sunday ('.$_SESSION['ilancedata']['cce_txt']['coming_auction_day'].')' . "\r\n";
		$cce_head .= "No Hidden Reserves, 10% Buyer's Fee (min $5)" . "\r\n" . "\r\n";
	

		$cce_foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$cce_foot .= 'Questions about GreatCollections or Coins?' . "\r\n";
		$cce_foot .= 'Tel: 1.800.44.COINS (+1.949.679.4180)' . "\r\n";
		$cce_foot .= 'E-mail: info@greatcollections.com' . "\r\n";
		$cce_foot .= 'Website: www.greatcollections.com' . "\r\n" . "\r\n";
		$cce_foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$cce_foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$cce_foot .= 'GreatCollections.com, LLC' . "\r\n";
		$cce_foot .= '17500 Red Hill Avenue, Suite 160, Irvine, CA 92614' . "\r\n";
		$cce_foot .= '-----------------------------------------------------------------' . "\r\n" . "\r\n";
		$cce_foot .= '*** You are currently subscribed as: {EMAIL_ADDRESS}';

		$_SESSION['ilancedata']['cce_txt']['time'] = date('Y-m-d-h-i-s');
		$cce_all = $cce_head . '' . $_SESSION['ilancedata']['cce_txt']['item'] . '' . $cce_foot;
		$cce_handle = $cce_all;
		$cce_f = @fopen("/home/gc/public_html/email_template/template_cce_text_" . $_SESSION['ilancedata']['cce_txt']['time'] . ".txt", "w");
		fwrite($cce_f, $cce_handle);
		fclose($cce_f);

		exit();

	}




	// vijay work for cce Text ends

	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view') {

		$_SESSION['subs'] = $ilance->GPC['subs'];
		$_SESSION['cou'] = $ilance->GPC['cou'];
		$_SESSION['itemf'] = $ilance->GPC['itemf'];
		$_SESSION['items'] = $ilance->GPC['items'];
		$_SESSION['itemt'] = $ilance->GPC['itemt'];
		$_SESSION['feat'] = $ilance->GPC['featured'];
		$_SESSION['featr'] = $ilance->GPC['featured_ref'];
		/*-- Tamil for Bug 2526 * Starts -- */
		$_SESSION['ilancedata']['user']['auction_date'] = $auction_date = $ilance->GPC['auction_date'];
		$CurrentAuction = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE status ='open' ");
		$item_CurrentAuction = $ilance->db->num_rows($CurrentAuction);

		$_SESSION['ilancedata']['user']['date_link'] = $date_link = $ilance->GPC['date_link'];
		/*-- Tamil for Bug 2526 * Ends --*/
		$_SESSION['ilancedata']['txt']['referal'] = $ilance->GPC['featured_ref'];

		//vijay work for cce auction date 
		$sql_cce_act_date = $ilance->db->query("

		SELECT  DATE_FORMAT(date_end,'%d/%l') as coming_auction_day FROM " . DB_PREFIX . "projects WHERE status = 'open' 
		AND hotlists = 1 group by date(date_end) asc limit 1
		");
		$cce_act_date = $ilance->db->fetch_array($sql_cce_act_date);

		$cce_act_date['coming_auction_day'];

		$_SESSION['ilancedata']['cce_txt']['coming_auction_day'] = $cce_act_date['coming_auction_day'];

		//ends

		unset($myfeat);
		$item .= '<tr>
  <td width="570" style="padding-top:10px; border-collapse:collapse;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="padding-top:10px; padding-bottom:20px;">';

		for ($i = 0; $i < count($ilance->GPC['item1']); $i++) {

			
			$subheading = $ilance->GPC['sub_heading'][$i];
			$item .= '<tr><td>&nbsp;</td></tr><tr><TD width="190"><P style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; font-weight:bold;">' . $subheading . '</P></TD></tr><tr><td>&nbsp;</td></tr>';

			$item .= '<tr>';

			
			$item_list[] = $ilance->GPC['item1'][$i];
			$item_list[] = $ilance->GPC['item2'][$i];
			$item_list[] = $ilance->GPC['item3'][$i];
			$item_arr = array_filter($item_list);

			for ($j = 0; $j < count($item_arr); $j++) {

				// $itm_filter=array_filter($itm);

				$select_series = $ilance->db->query("select *
				   	from	" . DB_PREFIX . "projects p
					left join " . DB_PREFIX . "coin_proof cp on cp.value= p.Grade
					where p.project_id='" . $item_arr[$j] . "'
					AND p.project_state = 'product'
					AND p.visible = '1'
					and p.status ='open'
                   ");

				if ($ilance->db->num_rows($select_series) > 0) {

					while ($row_series = $ilance->db->fetch_array($select_series)) {

						$sql_alt = $ilance->db->query("
                        SELECT coin_detail_year,coin_detail_denom_short,coin_detail_suffix FROM
                        " . DB_PREFIX . "catalog_coin
                        WHERE Orderno='" . $row_series['Orderno'] . "'

                        ");
						$fetch_alt = $ilance->db->fetch_array($sql_alt);

						$forcent = $fetch_alt['coin_detail_denom_short'];

						$alt_det = $fetch_alt['coin_detail_year'] . ' ' . $fetch_alt['coin_detail_denom_short'];

						$filename = '/home/gc/public_html/' . $ilconfig['email_image_folder_v2'] . '/' . $row_series['project_id'] . '-1.jpg';

						if (file_exists($filename)) {
							$sql_attach1 = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1'
						AND project_id = '" . $row_series['project_id'] . "'
						AND attachtype='itemphoto' order by attachid desc

                        ");
							$fetch_newa = $ilance->db->fetch_array($sql_attach1);

							if ($ilance->db->num_rows($sql_attach1) > 0) {
								$uselistra = HTTP_SERVER . $ilpage['attachment'] . '?cmd=thumb&subval=acc_v1&subcmd=itemphoto&id=' . $fetch_newa['filehash'] . '&w=170&h=190';
							}
							$uselistra = HTTP_SERVER . $ilconfig['email_image_folder_v2'] . '/' . $row_series['project_id'] . '-1.jpg';

							list($width, $height) = getimagesize($uselistra);

						} else {

							$sql_attach = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1'
						AND project_id = '" . $row_series['project_id'] . "'
						AND attachtype='itemphoto' order by attachid desc

                        ");
							$fetch_series = $ilance->db->fetch_array($sql_attach);

							if ($ilance->db->num_rows($sql_attach) > 0) {

								$uselistra = HTTP_SERVER . $ilpage['attachment'] . '?cmd=thumb&subval=acc_v1&subcmd=itemphoto&id=' . $fetch_series['filehash'] . '&w=170&h=190';

								list($width, $height) = getimagesize($uselistra);

							}
							if ($ilance->db->num_rows($sql_attach) == 0) {

								$uselistra = HTTP_SERVER . 'images/gc/nophoto.gif';

								//list($width,$height) = getimagesize($uselistra);
							}

						}

						$htm = '<img width="' . $width . '" height="' . $height . '" border="0" style="display:block;" alt="' . $alt_det . '" title="' . $alt_det . '" src="' . $uselistra . '">';

						if ($row_series['filtered_auctiontype'] == 'regular') {
							$type = 'Current Bid';
						} else {
							$type = 'Buy Now';
						}

						if ($ilconfig['globalauctionsettings_seourls']) {
							$series['url'] = '<a href="http://www.greatcollections.com/Coin/' . $row_series['project_id'] . '/' . construct_seo_url_name($row_series['project_title']) . '">';
						} else {
							$series['url'] = '<a href="merch.php?id=' . $row_series['project_id'] . '">';
						}

						$projectid = $row_series['project_id'];
						$projecttitle = $row_series['project_title'];
						//$currencyid = fetch_auction('currencyid', intval($row_series['project_id']));
						setlocale(LC_MONETARY, 'en_US');
						$currentprice = money_format('%.0n', $row_series['currentprice']);

						$arr = fetch_coin_table('Grading_Service', $row_series['project_id']);

						if ($arr) {
							$new_arr = str_replace(' ' . $arr, '<br />' . $arr, $row_series['project_title']);
						} else {
							$new_arr = str_replace($arr, '<br />' . $arr, $row_series['project_title']);
						}

						if (($row_series['bids']) == 1) {
							$hadbid = '(' . $row_series['bids'] . ' Bid)';
						} elseif (($row_series['bids']) > 1) {
							$hadbid = '(' . $row_series['bids'] . ' Bids)';
						} else {
							$hadbid = " ";
						}

						$item .= '<td width="190" align="center" style="line-height:16px;">
		  <a href="http://www.greatcollections.com/merch.php?id=' . $row_series['project_id'] . '&referal_name=' . $ilance->GPC['featured_ref'] . '" style="color:#336699; font-family:verdana,arial; font-size:11px; text-decoration:none; line-height:16px;text-decoration:underline;">' . $htm . '<br />' . $new_arr . '<br />' . $type . ' ' . $currentprice . ' ' . $hadbid . '
		   </span>
          </a>
        </td>';

						$txt_item .= $row_series['project_title'] . "\r\n" . $type . ' ' . $currentprice . ' ' . $hadbid . "\r\n" . "\r\n";

						if($fetch_alt['coin_detail_year']!='' && (is_numeric($fetch_alt['coin_detail_year'])))
						{							
						$cce_txt_item .= $fetch_alt['coin_detail_year'] . ' ' . $forcent.' '.$row_series['Grading_Service'] .' '.$row_series['proof'] . '-'.$row_series['Grade'].' '.$fetch_alt['coin_detail_suffix'].' - '. $type . ' ' . $currentprice . ' ' . $hadbid . "\r\n";
					    }
						$c++;
					}

				}

			}
			unset($item_list);

		
		}

		$item .= '</tr>';

		$item .= '</table></td></tr></table></td></tr>';

		$_SESSION['ilancedata']['txt']['item'] = $txt_item;

		$_SESSION['ilancedata']['cce_txt']['item'] = $cce_txt_item;

		$_SESSION['ilancedata']['user']['item'] = $item;

		$pprint_array = array('date_link', 'item_CurrentAuction', 'total_coins1', 'total_coins', 'auction_date', 'myfeature', 'mydaily', 'myfeat', 'service_cat', 'product_cat', 'buyingreminders', 'sellingreminders', 'scheduledcount', 'itemsworth', 'expertsrevenue', 'expertsearch', 'jobcount', 'expertcount', 'itemcount', 'feedbackactivity', 'messagesactivity', 'recentlyviewedflash', 'tagcloud', 'main_servicecats_img', 'main_productcats_img', 'main_servicecats', 'main_productcats', 'lanceads_folder', 'two_column_category_buyers', 'two_column_service_categories', 'two_column_product_categories', 'remote_addr', 'rid', 'default_exchange_rate', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'latestviews', 'list', 'myfeat', 'subheading', 'series', 'numbers', 'number', 'buildversion', 'ilanceversion', 'login_include_admin', 'currentmotd', 'currentmotd_preview', 'wysiwyg_area', 'admincpnews', 'totalusers', 'cbaddons', 'page', 'catid', 'module', 'input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'heading', 'item');

		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'email_generator_hotlist_view.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('series_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();

	}

	if (isset($ilance->GPC['ck']) AND $ilance->GPC['ck'] == 'op') {
		$css = 'check(\'' . $_SESSION['feat'] . '\',\'' . $_SESSION['featr'] . '\',\'' . $_SESSION['cou'] . '\',\'' . $_SESSION['subs'] . '\',\'' . $_SESSION['itemf'] . '\',\'' . $_SESSION['items'] . '\',\'' . $_SESSION['itemt'] . '\')';

		$onload = $css;

		$couo = $_SESSION['cou'];
	}
	$sql1 = "SELECT project_id  FROM " . DB_PREFIX . "projects WHERE status = 'open' AND hotlists = 1 and date(date_end)=(SELECT  date(date_end) as coming_auction_day FROM " . DB_PREFIX . "projects WHERE status = 'open' AND hotlists = 1 group by date(date_end) asc limit 1) order by date_end ASC";
$row_count=1;
$hotlist='';
$html='';
	$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result1) > 0) {
		while ($line1 = $ilance->db->fetch_array($result1)) {
			$i=$row_count%3;
			$row["hotlist_item".$i]=$line1['project_id'];
			if($i==0 and $row_count>0)
			{	
			$rows[$row_count]=$row;
			$html.='<tr>
      		  <td><input type="text" name="item1[]" id="item1-0" value="'.$row["hotlist_item1"].'" /></td>
		      <td><input type="text" name="item2[]" id="item2-0" value="'.$row["hotlist_item2"].'" /></td>
		      <td><input type="text" name="item3[]" id="item3-0" value="'.$row["hotlist_item0"].'" /></td>
		 </tr> ';
			}
			$row_count++;
			
		}
	}
	$hotlist=$html;
	$couo = ($ilance->GPC['ck'] == 'op') ? $_SESSION['cou'] : 1;

	$pprint_array = array('hotlist','couo', 'myfeature', 'mydaily', 'myfeat', 'service_cat', 'product_cat', 'buyingreminders', 'sellingreminders', 'scheduledcount', 'itemsworth', 'expertsrevenue', 'expertsearch', 'jobcount', 'expertcount', 'itemcount', 'feedbackactivity', 'messagesactivity', 'recentlyviewedflash', 'tagcloud', 'main_servicecats_img', 'main_productcats_img', 'main_servicecats', 'main_productcats', 'lanceads_folder', 'two_column_category_buyers', 'two_column_service_categories', 'two_column_product_categories', 'remote_addr', 'rid', 'default_exchange_rate', 'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'latestviews', 'list', 'myfeat', 'subheading', 'numbers', 'number', 'buildversion', 'ilanceversion', 'login_include_admin', 'currentmotd', 'currentmotd_preview', 'wysiwyg_area', 'admincpnews', 'totalusers', 'cbaddons', 'page', 'catid', 'module', 'input_style', 'remote_addr', 'rid', 'login_include', 'headinclude', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer');

	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'email_generator_hotlist.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

} else {
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>