<?php
/*vijay  Bug 3107     * Starts*/
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
	'feedback',
	'accounting',
	'javascript'
	);
// #### load required javascript ###############################################
	$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
	'tabfx',
	'flashfix',
	'jquery'
);
// #### define top header nav ##################################################
	$topnavlink = array(
	'certificationnumber_search'
);
// error_reporting(e_all);
// #### setup script location ##################################################
	define('LOCATION', 'certificationnumber_search');
// #### require backend ########################################################
	require_once('./functions/config.php');
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_search_prefs.php');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
	$ilance->bid_proxy = construct_object('api.bid_proxy');
// #### require shipping backend #######################
	require_once(DIR_CORE . 'functions_shipping.php');
	
	$page_title = SITE_NAME . ' - ' . 'Certification Number Search';
	
	$navcrumb = array();
	$navcrumb["main.php?cmd=cp"] = "My GC";
	$navcrumb["certificationnumber_search.php"] = "Coin Certification Number Search";
	$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
	
	$show['widescreen']=true;
	
	$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
	
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
        {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['certificationnumber_search'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
                exit();
        }
		
	$show_certificationnumber_search=0;
	
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='certificationnumber_search')
	{
		global $sold_count,$purchased_count;
		$show_certificationnumber_search=1;
	
		if(!empty($ilance->GPC['cert_no']))
		{
			
			$pcgs_number=$ilance->GPC['cert_no'];
		}
		else
		{
					
			$pcgs_number='';
		}
		

	$surfing_user_id=isset($_SESSION['ilancedata']['user']['userid']) and $_SESSION['ilancedata']['user']['userid']>0?$_SESSION['ilancedata']['user']['userid']:0;
	
	
	$certificationnumber_search= $ilance->db->query("
	SELECT c.coin_id,c.Certification_No,p.user_id, p.project_id, p.project_title, p.currentprice, p.buynow, p.description, p.winner_user_id, b.buyer_id, p.pcgs, p.date_end
                            FROM " . DB_PREFIX . "projects AS p
							 JOIN  " . DB_PREFIX . "coins AS c ON c.coin_id=p.project_id  AND c.Certification_No = '".$pcgs_number."' 
							LEFT JOIN  " . DB_PREFIX . "buynow_orders AS b ON p.project_id = b.project_id AND b.buyer_id ='".$_SESSION['ilancedata']['user']['userid']."'
							
  									WHERE p.project_state = 'product'
									
									AND p.winner_user_id ='".$_SESSION['ilancedata']['user']['userid']."' 
                                    AND p.visible = '1'
									
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ");
								
					   
	$total_num1=$ilance->db->num_rows($certificationnumber_search);
	
	if($total_num1>0)
	{
		$total_num=true;
		$purchased_count = '<b>('.$total_num1.')</b>';
	}
	else{
		$total_num=false;
	}
	if($ilance->db->num_rows( $certificationnumber_search) > 0)
	{
	 
		$number = (int)$ilance->db->num_rows( $certificationnumber_search);

		while($det=$ilance->db->fetch_array($certificationnumber_search))
		{
			$projectid=$det['project_id'];
			
			
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				
				$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($det['project_id'], $_SESSION['ilancedata']['user']['userid']);

				if ($pbit > 0)
				{										
					$highbidderidtest = $ilance->bid->fetch_highest_bidder($det['project_id']);
					
					
					if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
					$listpageg['proxybit'] = '<div class="smaller green" style="padding-top:4px"><span>You won this item</span><br>' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
					else
					$listpageg['proxybit'] ='<div class="smaller red" style="padding-top:4px"><span>You were outbid</span><br>' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
					';                                          
					$show['proxy']=true;
				
				}
				
				else
				{
					$listpageg['proxybit'] ='';
				}
				
			}
			
			

			if ($ilconfig['globalauctionsettings_seourls'])	
			$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
			else
			$listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';

			$listpageg['description']=$det['description'];

			$listpageg['date_end']='<strong>' . date("F d,Y",strtotime($det['date_end'])) . '</strong>';
			$buynow=$det['buynow'];

			
			$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
			$respjt = $ilance->db->fetch_array($selectpjt);
			$hammer_price = $ilance->currency->format($respjt['currentprice']);
			if($respjt['haswinner']=='1')
			{
				$sol='Sold';
				$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
			}
			else
			{
				$sol='Unsold';
				$hammer = '';
			}  
			if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
			{
				if ($respjt['filtered_auctiontype'] == 'regular')
				{

					if ($respjt['bids'] > 0)
					{
						$listpageg['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($respjt['currentprice']+$respjt['buyer_fee']). '</strong>';
					}
					else
					{
						$listpageg['currentprice'] ='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($respjt['currentprice']+$respjt['buyer_fee']). '</strong>';
						$listpageg['currentprice'] .='' .'<br>Buy Now'.'&nbsp;'.$ilance->currency->format($respjt['buynow_price']). '';
					}
					$listpageg['bids'] = ($respjt['bids'] > 0)
					? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
					: '<span class="blue"> Bid </span>';

				}
				else if ($respjt['filtered_auctiontype'] == 'fixed')
				{
					$listpageg['currentprice'] ='<strong>' .'Buy Now'.'&nbsp;'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
					$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
				}

			}
			else
			{
				$listpageg['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($respjt['currentprice']+$respjt['buyer_fee']). '</strong>'.$hammer;
				$listpageg['bids'] = ($respjt['bids'] > 0)
				? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
				: '<span class="blue"> Bid </span>';
			}
		

			$sql_attya = $ilance->db->query("
			SELECT * FROM
			" . DB_PREFIX . "attachment
			WHERE visible='1' 
			AND project_id = '".$det['project_id']."'
			AND attachtype='itemphoto' order by attachid desc

			");
			$fetch_newa=$ilance->db->fetch_array($sql_attya);

			if($ilance->db->num_rows($sql_attya) > 0)
			{

			$count_attachtype= $ilance->db->query("SELECT  count(attachtype) as imag FROM
												" . DB_PREFIX . "attachment
												WHERE visible='1' 
												AND project_id = '".$det['project_id']."'
												AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') order by attachid desc
												");
			$display_count=$ilance->db->fetch_array($count_attachtype);
			$uselistra = HTTPS_SERVER.$ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
			if ($ilconfig['globalauctionsettings_seourls'])
			$listpageg['imgvals'] ='<div class="gallery-thumbs-cell">
									<div class="gallery-thumbs-entry">
									<div class="gallery-thumbs-main-entry">
									<div class="gallery-thumbs-wide-wrapper">
									<div class="gallery-thumbs-wide-inner-wrapper">
									<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
									<div class="gallery-thumbs-corner-text"><span>' . ($display_count['imag']) . ' photos</span></div>
									</div>
									</div>
									</div>
									</div>
									</div>';
			else
			$listpageg['imgvals'] ='<div class="gallery-thumbs-cell">
									<div class="gallery-thumbs-entry">
									<div class="gallery-thumbs-main-entry">
									<div class="gallery-thumbs-wide-wrapper">
									<div class="gallery-thumbs-wide-inner-wrapper"><a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a> 
									</div>
									</div>
									</div>
									</div>
									</div>';


			}
			if($ilance->db->num_rows($sql_attya) == 0)
			{
				$uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
				if ($ilconfig['globalauctionsettings_seourls'])
				$listpageg['imgvals'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
				else
				$listpageg['imgvals'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
			}				
		
			$listpage[]=	$listpageg;
			
		}
	}
	else
	{
		$listpage='';
	}
	
		
	$count=$number;

	
	$cert_no=(isset($ilance->GPC['cert_no'])) ? $ilance->GPC['cert_no'] : '';
	
	$certificationnumber_Sold= $ilance->db->query("SELECT p.user_id, p.project_id,c.coin_id,c.Certification_No,p.project_title, p.currentprice, p.buynow, p.description, p.winner_user_id, b.buyer_id, p.pcgs, p.date_end
                            FROM " . DB_PREFIX . "projects AS p
							 JOIN  " . DB_PREFIX . "coins AS c ON c.coin_id=p.project_id  AND c.Certification_No = '".$pcgs_number."' 
							LEFT JOIN  " . DB_PREFIX . "buynow_orders AS b ON p.project_id = b.project_id
  									WHERE p.project_state = 'product'
									
									AND  p.user_id='".$_SESSION['ilancedata']['user']['userid']."'
                                    AND p.visible = '1'
									
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ");
		
	$total_n1=$ilance->db->num_rows($certificationnumber_Sold);
	
	if($total_n1>0)
	{
		$total_n=true;
		$sold_count='<b>('.$total_n1.')</b>';
	}
	else{
		$total_n=false;
	}
	if($ilance->db->num_rows($certificationnumber_Sold) > 0)
	{
	 
		$number_sld = (int)$ilance->db->num_rows($certificationnumber_Sold);

		while($sld=$ilance->db->fetch_array($certificationnumber_Sold))
		{
			$projectid=$sld['project_id'];
			
		
			

			if ($ilconfig['globalauctionsettings_seourls'])	
			$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_title'].'</a></span>';
			else
			$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['project_id'].'" style="color:blue;">'.$sld['project_title'].'</a></span>';
			$listpagesold['description']=$sld['description'];
			$listpagesold['date_end']='<strong>' . date("F d,Y",strtotime($sld['date_end'])) . '</strong>';
			$buynow=$sld['buynow'];

			
			$sldpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
			$soldpjt = $ilance->db->fetch_array($sldpjt);
			$hammer_price = $ilance->currency->format($soldpjt['currentprice']);
			if($soldpjt['haswinner']=='1')
			{
				$sol='Sold';
				$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
			}
			else
			{
				$sol='Unsold';
				$hammer = '';
			}  
			if ($soldpjt['buynow_price'] > 0 AND $soldpjt['filtered_auctiontype'] == 'fixed' OR $soldpjt['buynow_price'] > 0 AND $soldpjt['filtered_auctiontype'] == 'regular')
			{
				if ($soldpjt['filtered_auctiontype'] == 'regular')
				{

					if ($soldpjt['bids'] > 0)
					{
						$listpagesold['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($soldpjt['currentprice']+$soldpjt['buyer_fee']). '</strong>';
					}
					else
					{
						$listpagesold['currentprice'] ='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($soldpjt['currentprice']+$soldpjt['buyer_fee']). '</strong>';
						$listpagesold['currentprice'] .='' .'<br>Buy Now'.'&nbsp;'.$ilance->currency->format($soldpjt['buynow_price']). '';
					}
					$listpagesold['bids'] = ($soldpjt['bids'] > 0)
					? '<span class="blue">' . $soldpjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
					: '<span class="blue"> Bid </span>';

				}
				else if ($soldpjt['filtered_auctiontype'] == 'fixed')
				{
					$listpagesold['currentprice'] ='<strong>' .'Buy Now'.'&nbsp;'.$ilance->currency->format($soldpjt['buynow_price']). '</strong>';
					$listpagesold['bids']='<span class="blue">Buy<br>Now</span>';
				}

			}
			else
			{
				$listpagesold['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($soldpjt['currentprice']+$soldpjt['buyer_fee']). '</strong>'.$hammer;
				$listpagesold['bids'] = ($soldpjt['bids'] > 0)
				? '<span class="blue">' . $soldpjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
				: '<span class="blue"> Bid </span>';
			}

			$sql_attcsld = $ilance->db->query("
			SELECT * FROM
			" . DB_PREFIX . "attachment
			WHERE visible='1' 
			AND project_id = '".$sld['project_id']."'
			AND attachtype='itemphoto' order by attachid desc

			");
			$fetch_sql_attcsld=$ilance->db->fetch_array($sql_attcsld);

			if($ilance->db->num_rows($sql_attcsld) > 0)
			{

			$sldcount_attachtype= $ilance->db->query("SELECT  count(attachtype) as imag FROM
												" . DB_PREFIX . "attachment
												WHERE visible='1' 
												AND project_id = '".$sld['project_id']."'
												AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') order by attachid desc
												");
			$displays_count=$ilance->db->fetch_array($sldcount_attachtype);
			$slduselistra = HTTPS_SERVER.$ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_sql_attcsld['filehash']; 
			if ($ilconfig['globalauctionsettings_seourls'])
			$listpagesold['imgval'] ='<div class="gallery-thumbs-cell">
									<div class="gallery-thumbs-entry">
									<div class="gallery-thumbs-main-entry">
									<div class="gallery-thumbs-wide-wrapper">
									<div class="gallery-thumbs-wide-inner-wrapper">
									<a href="'.HTTPS_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$slduselistra.'" ></a> 
									<div class="gallery-thumbs-corner-text"><span>' . ($displays_count['imag']) . ' photos</span></div>
									</div>
									</div>
									</div>
									</div>
									</div>';
			else
			$listpagesold['imgval'] ='<div class="gallery-thumbs-cell">
									<div class="gallery-thumbs-entry">
									<div class="gallery-thumbs-main-entry">
									<div class="gallery-thumbs-wide-wrapper">
									<div class="gallery-thumbs-wide-inner-wrapper"><a href="'.HTTPS_SERVER.'merch.php?id='.$sld['project_id'].'"><img src="'.$slduselistra.'" style="padding-top: 6px;"></a> 
									</div>
									</div>
									</div>
									</div>
									</div>';


			}
			if($ilance->db->num_rows($sql_attcsld) == 0)
			{
				$slduselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
				if ($ilconfig['globalauctionsettings_seourls'])
				$listpagesold['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
				else
				$listpagesold['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$sld['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
			}				
			$listpagesld[]=	$listpagesold;
			
		}
	}
	
	$countsld=$number_sld;
		
	}

	
 
	$pprint_array = array('sold_count','purchased_count','listing','cert_no','count','countsld','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'certificationnumber_search.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
	$ilance->template->parse_loop('main',  array('listpagesld','listpage'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

/*vijay  Bug 3107 * Ends*/

?>