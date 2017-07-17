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
        'search',
        'feedback',
        'portfolio',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'main'
);

// #### setup script location ##################################################
define('LOCATION','generic_coin');

// #### require backend ######################################################## 
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[generics]" => $ilcrumbs["$ilpage[generics]"]);

$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');

   $no='<center>no result found</center>';

$column=$row*4;
$count_gal=1;

$new_coin="SELECT p.date_starts,p.cid,p.status,p.project_id,p.project_title,p.filtered_auctiontype,p.currentprice,
                    c.coin_id,c.new_release,c.Create_Date,
					a.filehash,
					dd.live_date,
					UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('".DATETIME24H."') AS mytime,
                    UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('".DATETIME24H."') AS starttime
					FROM  
					" . DB_PREFIX . "coins c, 
					" . DB_PREFIX . "projects p 
					left join " . DB_PREFIX . "dailydeal dd on dd.project_id = p.project_id
					left join " . DB_PREFIX . "attachment a on a.project_id = p.project_id and a.visible='1' and a.attachtype='itemphoto'
					WHERE c.new_release = '1'
					AND p.visible = '1'
					AND p.status = 'open'
					AND c.coin_id = p.project_id
					GROUP BY c.coin_id
					ORDER BY c.Create_Date DESC
					LIMIT 0,50";
							  
		$select_featured= $ilance->db->query($new_coin);					  

		if ($ilance->db->num_rows($select_featured) > 0)
		{   
		
		    $detail = '<table><tr>';
			$c = 0;
			while($row_pre_fea = $ilance->db->fetch_array($select_featured))
			{			   
					
					if(!is_null($row_pre_fea['filehash']) and strlen($row_pre_fea['filehash'])>0)
					{
						$uselistra = HTTPS_SERVER.$ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $row_pre_fea['filehash'] .'&w=170&h=140'; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"> <img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' at GreatCollections" title="'.$row_pre_fea['project_title'].' at GreatCollections"></a>';
						else
						$htma ='<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' at GreatCollections" title="'.$row_pre_fea['project_title'].' at GreatCollections"></a>';
						
						
					}else
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$htma ='<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' at GreatCollections" title="'.$row_pre_fea['project_title'].' at GreatCollections"></a>';
						else
					    $htma ='<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].' at GreatCollections" title="'.$row_pre_fea['project_title'].' at GreatCollections"></a>';
					}
				
				
				
				//$yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], '', 'right', $timeintext = 0, $showlivebids = 0, 0);
				$yutq =auction_time_left_new($row_pre_fea,false); 
				 //###########sekar on sep23
				$detail.= '<td><div style="width:25px;"></div></td>
				<td><div id="abox01">
						
						<div id="fetit">';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$detail.= '<h4 style="font-weight:bold; font-size: 12px; color: #303030; text-decoration: none; "><a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">';
						else
						$detail.= '<h4 style="font-weight:bold; font-size: 12px; color: #303030; text-decoration: none; "><a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$detail.= $row_pre_fea['project_title'].'</a></h4></div>
						<div>&nbsp;</div>
						<div id="textim">
						<div align="center">'.$htma.'</div>
						</div>
						
					    <div style="height: 50px;padding-top: 6px;">	
						<div id="fetit" style="float: left; width: 99px;">
						Currently:
						<br>
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span>
						</div>
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
				$detail.='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
				else
				$detail.='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
				
		 
					if(!is_null($row_pre_fea['live_date']))
					{
					 
					  $daily = ($row_pre_fea['live_date'] == DATETODAY) ? 'Ends' : '24-Hour Deals Starts';
					  //$daily = '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
				$detail.='
						</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>';
						
				 if($count_gal%4==0)
				 {
				 if($count_gal==$column)
				  $detail.= $sep.'</tr><tr>';
				 else
                  $detail.= $sep.'</tr><tr><td colspan="10"><hr></td></tr><tr>';
			       }
				 else
				 {
				  $detail.= $sep;	
				 }
				$count_gal++;
			}
		}
		

else

{

$detail.= "No New Release Coins Found.</br>
 All New Release Coins Are Sold Out";

}

$detail.= '</td></tr></table>';



            $pprint_array = array('detail','mydaily','service_cat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','current','title');


        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
    
        $ilance->template->fetch('main', 'new_coin_issues.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val');
		$ilance->template->parse_loop('main', 'info_feat');
        $ilance->template->parse_loop('main', 'featuredserviceauctions');
        $ilance->template->parse_loop('main', 'featuredproductauctions');
        $ilance->template->parse_loop('main', 'latestserviceauctions');
        $ilance->template->parse_loop('main', 'latestproductauctions');
        $ilance->template->parse_loop('main', 'productsendingsoon');
        $ilance->template->parse_loop('main', 'servicesendingsoon');
        $ilance->template->parse_loop('main', 'recentreviewedproductauctions');
        $ilance->template->parse_loop('main', 'recentreviewedserviceauctions');
		
        
        ($apihook = $ilance->api('main_end')) ? eval($apihook) : false;
        
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
?>
