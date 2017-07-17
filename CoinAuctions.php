<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1575
|| # -------------------------------------------------------------------- # ||
|| # Customer License # =ryotOqStzEoc1gDhm2kyaoC2VZLPe-ZTcK=-2d-y-SXgzbKia
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
        'wantads',
        'search',
        'feedback',
        'lancebb',
        'buying',
        'selling',
        'accounting',
        'rfp'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix',
	'jquery'
);

// #### setup script location ##################################################
define('LOCATION', 'main');

// #### require backend ########################################################
require_once('./functions/config.php');
// #### setup default breadcrumb ###############################################
//$navcrumb = array("$ilpage[main]" => $ilcrumbs["$ilpage[main]"]);

 $area_title = 'CoinAuctions';
       $page_title = SITE_NAME . ' - ' . 'Coin Auctions and Direct Sales of Rare Coins';

$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');

// #### SEO related ############################################################
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);

$e=explode('/',SCRIPT_URI);
$end= end($e);
$a= substr($end,1);
if(strstr($a,'?'))
{
$b=explode('?',$a);
$a=$b[0];
}

//For Denomination D1,D2....

if(($end[0]=='d')||($end[0]=='D'))

{
$denomination_details=$ilance->categories_parser->fetch_denominations($a);
$text=$denomination_details['denomination_long'];
$condition="AND(cc.coin_series_denomination_no='".$a."')";	

}

//For CoinSeries S1,S2...

else

{

$denomination_details=$ilance->categories_parser->fetch_coin_series(0,$a);
$text=$denomination_details['coin_series_name'];
$condition="AND(cc.coin_series_unique_no='".$a."')";	

}

//For Coins based on Denomination/Series..
	 
	    $select_featured= $ilance->db->query("select c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice,cd.denomination_short,cd.denomination_long,cc.coin_detail_year,cc.coin_series_unique_no
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd,
					" . DB_PREFIX . "projects c
					where 
					c.featured = '0' and 
					c.project_state = 'product'
					AND c.status = 'open'
					$condition
					AND c.visible = '1' and
					c.cid=cc.PCGS and
					cc.coin_series_unique_no=cs.coin_series_unique_no and
					cc.coin_series_denomination_no=cd.denomination_unique_no
					order by RAND() DESC limit 3
        
                   ");
		if ($ilance->db->num_rows($select_featured) > 0)
		{ 
		
		//$denomination_details=$ilance->categories_parser->fetch_denominations($a);
       // $text=$denomination_details['denomination_long'];
		
		// #### setup default breadcrumb ###############################################
       $navcrumb[""] = $text;

  
		    $myfeat = '';
			$c = 0;
			while($row_pre_fea = $ilance->db->fetch_array($select_featured))
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
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=140'; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"> <img src="'.$uselistra.'" style="padding-top: 6px;"></a>';
						else
						$htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a>';
						
						
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
				//karthik changes on apr 28
				if($c > 1)
				$sep = '';
				else
				$sep = '<div id="seperator"></div>';
				 $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, 0);
				$myfeat.= '<div id="abox01">
						<div>&nbsp;</div>
						<div id="fetit">';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeat.= '<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">';
						else
						$myfeat.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeat.= $row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					    <div style="height: 40px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>
						
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
				
				if($row_pre_fea['filtered_auctiontype'] == 'fixed')
				{
				   $image = 'buy_now_but.jpg';
				}
				else
				{
				   $image = 'bid_now_butt.jpg';
				}
				if ($ilconfig['globalauctionsettings_seourls'])		
				$myfeat.='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
				else
				$myfeat.='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
				
				
				     $sql_idly = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "dailydeal
                        WHERE project_id = '".$row_pre_fea['project_id']."'
						
						
                        ");
				   
					if($ilance->db->num_rows($sql_idly) > 0)
					{
					  
					  $daily = '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
			
			 $select_series= $ilance->db->query("select c.cid,c.project_id,c.project_title,cd.denomination_long,cc.coin_detail_year,cc.coin_series_unique_no,cc.coin_series_denomination_no,cc.PCGS,cd.denomination_unique_no
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd,
					" . DB_PREFIX . "projects c
					where 
					c.project_id = ".$row_pre_fea['project_id']." AND
					c.Orderno = cc.Orderno AND
					cc.coin_series_denomination_no = cd.denomination_unique_no
                   ");
			
			if ($ilance->db->num_rows($select_series) > 0)
		   {   
		
		     $fetch_series=$ilance->db->fetch_array($select_series);
		   
		   } 
				
				
				$myfeat.='
						</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						<div>&nbsp;</div>
						<div id="fetit">Browse All "<a href="search.php?mode=product&q=&series='.$fetch_series['coin_series_unique_no'].'&sort=01">'.$text.'</a>"</div>
						</div>
                        '.$sep.'';
			$c++;
			}
		}
		
		
		
// If there is no specified Denomination/Series, by default it will display three most bidded items across the whole site.
		
		
		else
		{
		  $text = 'Current Coin Auctions';
		  // #### setup default breadcrumb ###############################################
$navcrumb[""] = $text;

		  $select_featured= $ilance->db->query("select c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice,cd.denomination_short,cd.denomination_long,cc.coin_detail_year,cc.coin_series_unique_no
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd,
					" . DB_PREFIX . "projects c
					where 
					c.featured = '0' and 
					c.project_state = 'product'
					AND c.status = 'open'
					AND c.visible = '1' and
					c.cid=cc.PCGS and
					cc.coin_series_unique_no=cs.coin_series_unique_no and
					cc.coin_series_denomination_no=cd.denomination_unique_no
					order by RAND() DESC limit 3
        
                   ");
		if ($ilance->db->num_rows($select_featured) > 0)
		{   
		    $myfeat = '';
			$c = 0;
			while($row_pre_fea = $ilance->db->fetch_array($select_featured))
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
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=140'; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"> <img src="'.$uselistra.'" style="padding-top: 6px;"></a>';
						else
						$htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a>';
						
						
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
				//karthik changes on apr 28
				if($c > 1)
				$sep = '';
				else
				$sep = '<div id="seperator"></div>';
				 $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, 0);
				$myfeat.= '<div id="abox01">
						<div>&nbsp;</div>
						<div id="fetit">';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeat.= '<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">';
						else
						$myfeat.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeat.= $row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					    <div style="height: 40px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>
						
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
				
				if($row_pre_fea['filtered_auctiontype'] == 'fixed')
				{
				   $image = 'buy_now_but.jpg';
				}
				else
				{
				   $image = 'bid_now_butt.jpg';
				}
				if ($ilconfig['globalauctionsettings_seourls'])		
				$myfeat.='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
				else
				$myfeat.='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
				
				
				     $sql_idly = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "dailydeal
                        WHERE project_id = '".$row_pre_fea['project_id']."'
						
						
                        ");
				   
					if($ilance->db->num_rows($sql_idly) > 0)
					{
					  
					  $daily = '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
			
			 $select_series= $ilance->db->query("select c.cid,c.project_id,c.project_title,cd.denomination_long,cc.coin_detail_year,cc.coin_series_unique_no,cc.coin_series_denomination_no,cc.PCGS,cd.denomination_unique_no
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd,
					" . DB_PREFIX . "projects c
					where 
					c.project_id = ".$row_pre_fea['project_id']." AND
					c.Orderno = cc.Orderno AND
					cc.coin_series_denomination_no = cd.denomination_unique_no
                   ");
			
			if ($ilance->db->num_rows($select_series) > 0)
		   {   
		
		     $fetch_series=$ilance->db->fetch_array($select_series);
		   
		   } 
				$myfeat.='
						</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						<div>&nbsp;</div>
						<div id="fetit">Browse All "<a href="search.php?mode=product&q=&series='.$fetch_series['coin_series_unique_no'].'&sort=01">'.$fetch_series['denomination_long'].'</a>"</div>
						</div>
                        '.$sep.'';
			$c++;
			}
		}
		
		}
		
		
		//For  Featured Auctions 
		
		 $select_featurednew= $ilance->db->query("select c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice,cd.denomination_short,cc.coin_detail_year
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd,
					" . DB_PREFIX . "projects c
					where 
					c.featured = '1' and 
					c.project_state = 'product'
					AND c.status = 'open'
					AND c.visible = '1' and
					c.cid=cc.PCGS and
					cc.coin_series_unique_no=cs.coin_series_unique_no and
					cc.coin_series_denomination_no=cd.denomination_unique_no
					group by c.project_id order by RAND() ASC limit 4
        
                   ");
				   
				 
        
		if ($ilance->db->num_rows($select_featurednew) > 0)
		{   
		    $myfeature = '';
			$c = 0;
			while($row_pre_fea = $ilance->db->fetch_array($select_featurednew))
			{
			
			            $sql_attya = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$row_pre_fea['project_id']."'
						AND attachtype='itemphoto'
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
				
					if($ilance->db->num_rows($sql_attya) == 1)
					{
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $fetch_newa['filehash'] .'&w=170&h=140'; 
						if ($ilconfig['globalauctionsettings_seourls'])	
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a>';
						else
						$htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a>';						
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
				
				if($c > 2)
				$sep = '';
				else
				$sep = '<div id="seperator"></div>';
				 $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, 0);
				$myfeature.= '<div id="abox01">
						
						<div id="fetit">';
						$myfeature.=$ilconfig['globalauctionsettings_seourls']?
						'<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">':
						'<a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeature.=$row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					    <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
						
						if($row_pre_fea['filtered_auctiontype'] == 'fixed')
						{
				  			 $image = 'buy_now_but.jpg';
						}
						else
						{
				   			$image = 'bid_now_butt.jpg';
						}
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeature.= '<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
						else
						$myfeature.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
						
						 $sql_idly = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "dailydeal
                        WHERE project_id = '".$row_pre_fea['project_id']."'
						
						
                        ");
				   
					if($ilance->db->num_rows($sql_idly) > 0)
					{
					  
					  $daily = '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
						$myfeature.= '</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>
                        '.$sep.'';
			$c++;
			}
		}
		else
		{
		  $myfeature = '<div style="margin-top: 150px;" align="center">NO RESULTS FOUND</div>';
		}
		
	
        $pprint_array = array('myfeature','mydaily','myfeat','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','text');

        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
	
	    $ilance->template->fetch('main', 'CoinAuctions.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val');
		$ilance->template->parse_loop('main', 'info_feat');
        ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Fri, Sep 17th, 2010
|| ####################################################################
\*======================================================================*/
?>