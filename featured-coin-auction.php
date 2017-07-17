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
$navcrumb = array("$ilpage[main]" => $ilcrumbs["$ilpage[main]"]);

$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');



// #### SEO related ############################################################
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);

        // #### define top header nav ##########################################
        $topnavlink = array(
                'main'
        );
        
        $show['widescreen'] = false;
		$show['hide'] = 'home';
        
       $area_title = 'Featured Items';
       $page_title = SITE_NAME . ' - ' . 'Featured Coin Auctions, Certified by PCGS, NGC and ANACS at GreatCollections';
        
        
	 
		$ilance->categories_parser = construct_object('api.categories_parser');
		$denominationslist=$ilance->categories_parser->leftnav_denomination();
		$product_cat=$denominationslist;
		$watchlist_check ='';
		if ($_SESSION['ilancedata']['user']['userid'] >0)
		{
		$watchlist_check="and  w.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'";
		}

		 $select_featurednew= $ilance->db->query("select p.cid,p.project_id,p.project_title,p.filtered_auctiontype,p.currentprice,ca.project_id as img_project_id, ca.filename as filehash,w.watchlistid
							from " . DB_PREFIX . "projects p 
							left join " . DB_PREFIX . "users u ON p.user_id = u.user_id and u.status = 'active'			      
							left join " . DB_PREFIX . "watchlist w on  p.project_id = w.watching_project_id ".$watchlist_check ."
							left join  " . DB_PREFIX . "attachment ca on ca.project_id=p.project_id and ca.attachtype='itemphoto' and ca.visible = '1'
							WHERE p.featured = '1'
							AND p.project_state = 'product'
							AND p.status='open'
							AND p.visible = '1'		
							group by p.project_id
							having count(p.project_id) >0
							order by p.date_end asc
                   			");
				   
        
		if ($ilance->db->num_rows($select_featurednew) > 0)
		{   $c = 0;
		    $myfeature = '';
			
			$myfeature.='<table  width="100%"><tr>';
			while($row_pre_fea = $ilance->db->fetch_array($select_featurednew))
			{
			    if($c==0)
				{
				$myfeature.='';
				}
				else if($c%4==0)
				{
				$myfeature.='</tr><tr class="alt1"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr>';
				}
				
				

					if (!empty($row_pre_fea['filehash'])) {
						$uselistra =  HTTPS_SERVER .'image/140/170/' . $row_pre_fea['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])	
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$uselistra.'" style="padding-top: 6px;" title="'.$row_pre_fea['project_title'].'" alt="'.$row_pre_fea['project_title'].'"></a>';
						else
						$htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a>';						
					}
					else
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$htma ='<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;" title="'.$row_pre_fea['project_title'].'" alt="'.$row_pre_fea['project_title'].'"></a>';
						else
					    $htma ='<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
				
				
					//vijay work for add watchlist star  starts

				if (!empty($_SESSION['ilancedata']['user']['userid']))
                {

					$watch_user_id=$_SESSION['ilancedata']['user']['userid'];

					if($row_pre_fea['watchlistid'] > 0)
					{
					$watch_list_check = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_on.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="inline_watch_'.$row_pre_fea['project_id'].'" onclick="update_watch('.$row_pre_fea['project_id'].','.$watch_user_id.');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
					<span>
				        <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
				        <strong>Click to Remove from your Watchlist</strong> 
				     </span>
					  </a>';
					}                                            
					else
					{

					$watch_list_check = '<a href="javascript:void(0)" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_off.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="inline_watch_'.$row_pre_fea['project_id'].'" onclick="update_watch('.$row_pre_fea['project_id'].','.$watch_user_id.');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />
					 <span>
				        <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
				        <strong>Click to Add to your Watchlist</strong> 
				     </span>
					</a>';
					}
			    }
	   			 else
				{
					$watch_list_check = '<a href="javascript:void(0)" onclick="login_watch('.$row_pre_fea['project_id'].');" class="tooltip" style="text-decoration:none"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_off.gif" alt=""   border="0" />
		    <span>
		        <img class="callout" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'callout.gif"   />
		        <strong>Click to Add to your Watchlist</strong> 
		     </span>
					</a>';
					
				}


											
                                            
                                            
// inline auction ajax controls
                $headinclude .= "
<script type=\"text/javascript\">
<!--
var watching_project_id = 0;
var value = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function reset_image()
{
        imgtag.src = favoriteicon;
}
function fetch_response()
{
        if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
        {
                response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
                phpstatus = xmldata.fetch_data(response);

                favoriteiconsrc = fetch_js_object('inline_watch_' + xmldata.watching_project_id).src;
                status = favoriteiconsrc.match(/\/star_off.gif/gi);
                if (status == '/star_off.gif')
                {
                       status = 'off';
                }
                else
                {
                       status = 'on';
                }                                
                if (status == 'off')
                {
                        if (phpstatus == 'addedwatchlist' || phpstatus == 'removed_watchlist')
                        {
                                favoriteiconsrc = fetch_js_object('inline_watch_' + xmldata.watching_project_id).src;
                                imgtag = fetch_js_object('inline_watch_' + xmldata.watching_project_id);
                                
                                favoriteicon2 = favoriteiconsrc.replace(/star_off.gif/gi, 'working.gif');
                                imgtag.src = favoriteicon2;
                                
                                favoriteicon = favoriteiconsrc.replace(/star_off.gif/gi, 'star_on.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert(phpstatus);
                        }
                }
                else if (status == 'on')
                {
                        if (phpstatus == 'addedwatchlist' || phpstatus == 'removed_watchlist')
                        {
                                favoriteiconsrc = fetch_js_object('inline_watch_' + xmldata.watching_project_id).src;
                                imgtag = fetch_js_object('inline_watch_' + xmldata.watching_project_id);
                                
                                favoriteicon2 = favoriteiconsrc.replace(/star_on.gif/gi, 'working.gif');
                                imgtag.src = favoriteicon2;
        
                                favoriteicon = favoriteiconsrc.replace(/star_on.gif/gi, 'star_off.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert(phpstatus); 
                        }
                }
                xmldata.handler.abort();
        }
}
function update_watch(watching_project_id,uid)
{                        
        xmldata = new AJAX_Handler(true);
        
        watching_project_id = urlencode(watching_project_id);
        xmldata.watching_project_id = watching_project_id;

        uid = urlencode(uid);
        xmldata.uid = uid;
        
        searchiconsrc = fetch_js_object('inline_watch_' + watching_project_id).src;
        status = searchiconsrc.match(/\/star_off.gif/gi);
        if (status == '/star_off.gif')
        {
               value = 'on';
        }
        else
        {
               value = 'off';
        }
        xmldata.onreadystatechange(fetch_response);

        xmldata.send('ajax.php', 'do=add_watchlist_star&value=' + value + '&watching_project_id=' + watching_project_id + '&s=' + ILSESSION + '&token=' + ILTOKEN+'&uid='+uid);                        
}


//-->
</script>
";        

                                        //vijay work end.			
			
			$myfeature.='<td style="width:219px;">';
				 $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, 0);
				 
                        $myfeature.= '<div id="fetit">';	
									
						$myfeature.=$ilconfig['globalauctionsettings_seourls']?
						'<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">':
						'<a href="merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeature.=$row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center" style="float:left;padding-left:45px;">'.$htma.'</div><div style="float:right; padding:5px 5px 0px 0px; "> '.$watch_list_check.'</div></div>
						
					    <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">current<br>
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
						$myfeature.= '<a href="Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
						else
						$myfeature.= '<a href="merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
						
						 $sql_idly = $ilance->db->query("
                        SELECT * FROM
                        " . DB_PREFIX . "dailydeal
                        WHERE project_id = '".$row_pre_fea['project_id']."'
						
						
                        ");
				   
					if($ilance->db->num_rows($sql_idly) > 0)
					{
					  
					   //new change may10
					  $row_idly = $ilance->db->fetch_array($sql_idly);
					  
					  $daily = ($row_idly['live_date'] == DATETODAY) ? 'Ends' : '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
						$myfeature.= '</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>';
					$myfeature.='</td>';
					
			$c++;
			}
			
			$myfeature.='</table>';
		}
		else
		{
		  $myfeature = '<div style="margin-left: 350px; margin-top: 150px;">NO RESULTS FOUND</div>';
		}
		
		
		
        $pprint_array = array('myfeature','mydaily','myfeat','product_cat','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_productcats','lanceads_folder','two_column_category_buyers','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','latestviews','list');

        ($apihook = $ilance->api('main_start')) ? eval($apihook) : false;  
	
	    $ilance->template->fetch('main', 'featured-coin-auction.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'res_gcdealing');
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
