<?php
//error_reporting(E_ALL);
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
 $ilance->bid = construct_object('api.bid');
 $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
 $ilance->bid_proxy = construct_object('api.bid_proxy');
// #### require shipping backend #######################
require_once(DIR_CORE . 'functions_shipping.php');
// #### setup default breadcrumb ###############################################
//$navcrumb = array("$ilpage[denomination]" => $ilcrumbs["$ilpage[denomination]"]);
// #### decrypt our encrypted url ##############################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
// #### HANDLE SELLER TOOLS FROM LISTING PAGE ##################################
/*echo '<pre>';
 print_r ($_GET);
 
 echo '<pre>';
print_r($_SESSION);*/
 $ilance->auction = construct_object('api.auction');
	//sekar works on back to search on july 26
	
	$_SESSION['ilancedata']['user']['denomin']=PAGEURL;
     $_SESSION['ilancedata']['user']['search']='';
	 //sekar finished works on back to search on july 26
// #### ITEM CATEGORY LISTINGS #################################################
 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list all denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
	$ilance->categories_parser=construct_object('api.categories_parser');
    
	
    $count = $ilance->db->query("SELECT count(*) as cnt,date(date_end) as date
								FROM   " . DB_PREFIX . "projects
								WHERE STATUS =  'open'
								GROUP BY DATE(date_end)
								having cnt > 10
		                      ");
	$l=0;		
		  
	while($row=$ilance->db->fetch_array($count))
	{
		//if($ilance->GPC['date_end']!='')
		  $tab_name_date = $ilance->GPC['date_end'];
		
		 $count1 = $ilance->db->query("SELECT count(*) as cnt
									FROM   " . DB_PREFIX . "projects
									WHERE STATUS =  'open'
									AND DATE(date_end)='".$tab_name_date."'
								  ");
		
			$num=$ilance->db->fetch_array($count1);				  
		  
		  $tab_name=date('l, F j, Y',strtotime($row['date']));
		  
		  
		  
		  if($ilance->GPC['date_end']!='')
		{
			
		  $text="Bid Now through ".date('l, F j, Y',strtotime($tab_name_date))." (".$num['cnt']." Coin Auctions)";
		  
		  $categoryresults=$ilance->categories_parser->html_denomination_new($tab_name_date);
			  
		  $search_category_pulldown=$ilance->categories_parser->demonomination_dropdwn('denominationid',0,true,$tab_name_date);
		  
		  $date_end="AND date(date_end)='".$tab_name_date."'";
		  
		 }
		 else
		 {
		
		 $text = "Browse All Active Coin Auctions";
		 
		 $categoryresults=$ilance->categories_parser->html_denomination();
		 
		 $search_category_pulldown=$ilance->categories_parser->demonomination_dropwdown('denominationid',0,true);
		 
		 $date_end='';
		 
		 } 
		  
		 $tab_name_href = $row['date'];
		if($l==0)
		if((isset($ilance->GPC['date_end']) and $ilance->GPC['date_end'] != '') || isset($ilance->GPC['deal']))
		$tab.=' <li title="" class="" ><a href="'.HTTP_SERVER.'Denominations">All Coin Auctions</a></li>';
		else
		$tab.=' <li title="" class="on" ><a href="'.HTTP_SERVER.'Denominations">All Coin Auctions</a></li>';
		if(isset($ilance->GPC['date_end']))
		if($tab_name_href == $ilance->GPC['date_end'])
		$tab.='<li title="" class="on" ><a href="'.HTTP_SERVER.'Denominations/'.$tab_name_href.'">'.$tab_name.'</a></li>';
		else
		$tab.='<li title="" class="" ><a href="'.HTTP_SERVER.'Denominations/'.$tab_name_href.'">'.$tab_name.'</a></li>';
		else
		if($l==0 AND !($ilance->GPC['deal']))
		$tab.='<li title="" class="" ><a href="'.HTTP_SERVER.'Denominations/'.$tab_name_href.'">'.$tab_name.'</a></li>';
		else
		$tab.='<li title="" class="" ><a href="'.HTTP_SERVER.'Denominations/'.$tab_name_href.'">'.$tab_name.'</a></li>';
		
	
        //Featured	
		
	 $ilance->auction = construct_object('api.auction');
	 $featuredproductauctions = $ilance->auction->fetch_featured_auctions_denominations('product', 10, 2,$tab_name_date,false);
	
	   $l++;
	   
		}						  
		
	//24-Hour Deals Tab 		
	  $sql_gcdeal = $ilance->db->query("SELECT * FROM 
	                                    " . DB_PREFIX . "projects p, 
					                    " . DB_PREFIX . "dailydeal d 
                                        WHERE d.live_date = '".DATETODAY."' 
										AND p.project_id = d.project_id
				                        AND   p.status='open' 
				                        group by d.project_id
									  ");
				
	    if ($ilance->db->num_rows($sql_gcdeal) > 0)
	    {	
		
		    if($ilance->GPC['deal'])
	        {
		  
				 $tab.='<li title="" class="on" ><a href="'.HTTP_SERVER.'Denominations/DailyDeal">24-Hour Deals</a></li>';
				 $show['daily_deal']=true;
				 $rowstotal = $ilance->db->num_rows($sql_gcdeal); 
				 
				  while ($res_gcdeals = $ilance->db->fetch_array($sql_gcdeal))
				  {
					$sql_atty = $ilance->db->query("SELECT * FROM
                                                    " . DB_PREFIX . "attachment
                                                    WHERE visible='1'
                                                      AND project_id = '".$res_gcdeals['project_id']."'
                                                      AND attachtype='itemphoto'
                                                  ");
                	  $fetch_new=$ilance->db->fetch_array($sql_atty);
                               
					   if($ilance->db->num_rows($sql_atty) == 1)
					   {
							$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $fetch_new['filehash'] .'&w=170&h=105';
							$htm ='<a href="'. $ilpage['merch'] .'?id='.$res_gcdeals['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
					   }
					   if($ilance->db->num_rows($sql_atty) == 0)
			   
					   {
						   $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						   $htm ='<img src="' . $uselistr . '">';
					   }
					   
					    if ($c == 0)
	                    {
								$res_gc_deal['separator_begin'] = '<tr>';
								$td = 0;
						}
						else 
						{
								$res_gc_deal['separator_begin'] = '';
						}
                                        
	                     if ($c == '4')
	                     {
	                            $res_gc_deal['separator_end'] = '</tr>';
	                     }
	                     else 
	                     {
	                             $res_gc_deal['separator_end'] = '';
	                     }
									
									  
					
					$res_gc_deal['thumbnail'] ='<a href="'. $ilpage['merch'] .'?id='.$res_gcdeals['project_id'].'">'. $htm . '</a>';
					$res_gc_deal['item_title'] = '<a href="'.HTTP_SERVER.'Coin/'.$res_gcdeals['project_id'].'/'.construct_seo_url_name($res_gcdeals['project_title']).'">'.$res_gcdeals['project_title'].'</a>';
					$res_gc_deal['coin_amount'] = fetch_coin('Buy_it_now',$res_gcdeals['cid'],$res_gcdeals['project_id']);
					$res_gc_deal['current_bid'] = $res_gcdeals['buynow_price'];
					$res_gc_deal['project_id'] = $res_gcdeals['project_id'];
					$res_gc_deal['image'] = '<a href="'.HTTP_SERVER.'Coin/'.$res_gcdeals['project_id'].'/'.construct_seo_url_name($res_gcdeals['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_now_but.jpg" /></a>';
					$res_gcdealing[] = $res_gc_deal;
				
					$c++;
			   }//while
	  
	        }	//if	
	  
			else
			  
			{
			   $tab.='<li title="" class="" ><a href="'.HTTP_SERVER.'Denominations/DailyDeal">24-Hour Deals</a></li>';
			}
	
	    }//24-Hour Deals End
	$check = $ilconfig['globalauctionsettings_seourls'];
    //new change
	
	//$search_category_pulldown=$ilance->categories_parser->demonomination_dropwdown('denominationid',0,true);
	
	/*Tamil for bug 2637* Starts*/
	if(isset($ilance->GPC['date_end']))
	$show['date_end_search']=true;
	else
	$show['date_end_search']=false;
	/*Tamil for bug 2637* Ends*/
	  $pprint_array = array('check','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','tab','table_featured');
        
	$ilance->template->fetch('main', 'merch_denomination_listings.html'); 
	
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'res_gcdealing');
	$ilance->template->parse_loop('main', 'featuredproductauctions');
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// #### COIN SERIES LISTINGS #################################################
//karthik changes for search on apr 27
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'go')
{
//add buy suku
$ilance->GPC['q']=$ilance->db->escape_string($ilance->GPC['q']);
$show['go_denomination'] = true;
$series_details=$ilance->categories_parser->fetch_coin_series(0,$ilance->GPC['series']);
	$text=$series_details['coin_series_name'];
 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = $ilpage['denomination'] . '?denomination='.$ilance->GPC['denomination'].'&cmd=go&q='. $ilance->GPC['q'] .'&series='.$ilance->GPC['series'].'&sort='.$ilance->GPC['sort'].'';
 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
if (!empty($ilance->GPC['q']) AND !empty($ilance->GPC['series']))
{
     $condition ="AND(c.project_title LIKE '%" . $ilance->GPC['q'] . "%'OR c.project_id LIKE '%" . $ilance->GPC['q'] . "%' OR c.description LIKE '%" . $ilance->GPC['q'] . "%')AND     cc.coin_series_unique_no='".$ilance->GPC['series']."'";
	 
}
else if (!empty($ilance->GPC['q']) AND empty($ilance->GPC['series']))
{
   $condition ="AND(c.project_title LIKE '%" . $ilance->GPC['q'] . "%'OR c.project_id LIKE '%" . $ilance->GPC['q'] . "%' OR c.description LIKE '%" . $ilance->GPC['q'] . "%')AND    cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'";
   
}
else if (!empty($ilance->GPC['series']) )
{
$condition="AND(cc.coin_series_unique_no='".$ilance->GPC['series']."')";
}
else
{
$condition="AND(cc.coin_series_denomination_no='".$ilance->GPC['denomination']."')";
}
if ($ilance->GPC['sort']=='31') 
{
$orderby ="ORDER BY c.Orderno ASC";
}
else if ($ilance->GPC['sort']=='32') 
{
$orderby ="ORDER BY c.Orderno DESC";
}
else if ($ilance->GPC['sort']=='21') 
{
$orderby ="ORDER BY c.bids ASC";
}
else if ($ilance->GPC['sort']=='22') 
{
$orderby ="ORDER BY c.bids DESC";
}
else if ($ilance->GPC['sort']=='01') 
{
$orderby ="ORDER BY c.date_starts ASC";
}
else if ($ilance->GPC['sort']=='02') 
{
$orderby ="ORDER BY c.date_starts DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$orderby ="ORDER BY c.currentprice ASC";
}
else if ($ilance->GPC['sort']=='12') 
{
$orderby ="ORDER BY c.currentprice DESC";
}

if (isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
{
   $condition .= " AND(DATE(c.date_end) = '".$ilance->GPC['date_end']."') ";
}

  $select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow				   
                                          from 
					                      " . DB_PREFIX . "catalog_coin cc, 
					                      " . DB_PREFIX . "catalog_second_level cs,
					                      " . DB_PREFIX . "projects c				
					                      where 
										  c.status = 'open'
										  $condition
										  AND c.visible = '1'
										   AND c.cid=cc.PCGS
										   AND c.cid !='0'
										 group by c.project_id
										  $orderby  LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                                           ");
				  
				
				   
                       $select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
																	   from 
																		" . DB_PREFIX . "catalog_coin cc, 
																		" . DB_PREFIX . "catalog_second_level cs,
																		" . DB_PREFIX . "projects c				
																		where 
																	   c.status = 'open'
																	    $condition
																	   AND c.visible = '1'
																	   AND c.cid=cc.PCGS
																	   AND c.cid !='0'
																	   group by c.project_id
                                                                   ");
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
					 
					  $total_num2=$ilance->db->num_rows($select_featurednew);
					  
					    if($total_num2)
				   {
						   $total_go=true;
				   }
				   else
				   {
				         $total_go=false;
				   }
				   
				   if($ilance->db->num_rows( $select_featurednew) > 0)
				   {
	                 while($det=$ilance->db->fetch_array($select_featurednew))
				     {
				               $projectid=$det['project_id'].'<br>';
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }  $sql_attya = $ilance->db->query("
																				SELECT * FROM
														
														
														
																				" . DB_PREFIX . "attachment
														
														
														
																				WHERE visible='1' 
														
														
														
																				AND project_id = '".$det['project_id']."'
														
														
														
																				AND attachtype='itemphoto' order by attachid desc
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
					if($ilance->db->num_rows($sql_attya) > 0)
					{
						$uselistra = HTTPS_SERVER. 'image.php?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
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
						$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
									 	$listpage[]=$listpageg;	
									 }
									 
					 
						
							} 
							else
							{
							
							
							
							$listpage[]='';	
							
							}
							
							 $listing =  '<td><a href="'.$ilpage['denomination'] . '?denomination='.$ilance->GPC['denomination'].'&cmd=go&q='. $ilance->GPC['q'] .'&sort=12" title="Sort by Price" style="text-decoration:none">Price</a></td>								
                                            <td><a href="#" title="Sort by Type" style="text-decoration:none">Type</a></td>	
											<td><a href="#" title="Sort by Time Left" style="text-decoration:none">Time Left</a></td> ';
							
							
						$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
							
						$search_category_pulldown=$ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'],'series',0,true);
						
						$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
					
						$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
									
						$pprint_array = array('prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','listing','total_go');
	                   $ilance->template->fetch('main', 'merch_series_listings.html'); 
	                   $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	                    $ilance->template->parse_loop('main', 'listpage');
	 //sekar listings on categoris finished 
						$ilance->template->parse_if_blocks('main');
				
					    $ilance->template->pprint('main', $pprint_array);
				
					    exit();
				
}
//karthik end
else if(isset($ilance->GPC['denomination']) AND $ilance->GPC['denomination'] >0 AND (empty($ilance->GPC['ended'])))
{
$show['search_denomination'] = true;
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list series for selected denominations
	$show['widescreen'] = false;
	$area_title = $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
	$ilance->categories_parser=construct_object('api.categories_parser');
	$denomination_details=$ilance->categories_parser->fetch_denominations($ilance->GPC['denomination']);
	$text=$denomination_details['denomination_long'];
	$categoryresults=$ilance->categories_parser->html_coin_series_new($ilance->GPC['denomination'],$ilance->GPC['date_end']);
	
	if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
		$navcrumb = array(HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).'/'.$ilance->GPC['date_end'] => $text);	
	else
		$navcrumb = array(HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']) => $text);	

	
//new change
	//$search_category_pulldown=$ilance->categories_parser->series_dropwdown($ilance->GPC['denomination'],'series_id',0,true);
	
	$search_category_pulldown=$ilance->categories_parser->series_dropwdwn_new($ilance->GPC['denomination'],'series',0,true,$ilance->GPC['date_end']);
	
	//copy the page from here copy the page from here copy the page from here copy the page from here copy the page from here
	//sekar listings on categoris
	
	$ilance->GPC['denomination'];
	
	$list_link = '$ilpage[denomination]?denomination='.$denomination.'&amp;mode=' . $ilance->GPC['mode'];
	//sekar working for search in merch on june 01
	
	if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'])
				{
				     $featured=" AND c.buynow='1' ";
				}
				else if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'])
				{
				
				     $featured=" AND c.buynow = '0' ";
				}
				else
				{
				    $featured='';
				}
		
		
	if(isset($ilance->GPC['bidrange']))
	{
	if($ilance->GPC['bidrange']=='1')
	{
	$bidrng="AND c.bids < 10";
	}
	else if($ilance->GPC['bidrange']=='2')
	{
	$bidrng="AND c.bids < 20 AND c.bids > 10";
	}
	else if($ilance->GPC['bidrange']=='3')
	{
	$bidrng="AND c.bids > 20";
	}
	}
	else
	{
	$bidrng=" ";
	}	
			
	/*Tamil For Bug 2545 * Starts*/
	
	/* if(isset($ilance->GPC['completed']) OR $ilance->GPC['completed']=='1')
	{
	   $comp="c.status = 'expired'";
	}
	else
	{
	$comp="c.status = 'open'";
	} */
        
		
	$comp="c.status = 'open'";
	$show['denom1_no_result']=true;
	/*Tamil For Bug 2545 * Ends*/
	
	
	//sekar working on price on june02
	
	
		//for price
  $sqlquery['pricerange'] = $clear_price = '';
                        if ($ilance->GPC['mode'] == 'product')
                        {
					
                                if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
                                {
								
								
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
					$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					
                                        $sqlquery['pricerange'] .= "AND (c.currentprice >= " . intval($ilance->GPC['fromprice']) . " ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_min_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong></span> &ndash; ');
                                        handle_search_verbose_save($phrase['_min_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= "AND (c.currentprice >= 0 ";
                                }
                                
                                if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
                                {
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
                                        $removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
					
                                        $sqlquery['pricerange'] .= "AND c.currentprice <= " . intval($ilance->GPC['toprice']) . ") ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_max_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= ")";
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $phrase['_unlimited'] . '</strong>, ');
                                }
                        }
                $con=$sqlquery['pricerange'];
				
	
	
	
	
  //counter for page 
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 //$scriptpageprevnext = $ilpage['denomination'] . '?denomination='.$ilance->GPC['denomination'].'&list=gallery';
				 //$scriptpageprevnext = HTTP_SERVER.'Denimonation/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).'/'.$date_end_onlinks.'/?list=gallery';
				 //$scriptpageprevnext = $ilpage['denomination'] . '?denomination='.$ilance->GPC['denomination'];
				 //$scriptpageprevnext = HTTP_SERVER.'Denimonation/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).'/'.$date_end_onlinks.'/?';
				 
				 
				 if(isset($ilance->GPC['date_end']))
				 $date_end_onlinks='&date_end='.$ilance->GPC['date_end'];
				 else
				 $date_end_onlinks='';
				 if($ilance->GPC['list'] == 'list' || empty($ilance->GPC['list']))
				 {
					$page_layout='';
				 }
				 else
				 {
					$page_layout='/list=gallery';
				 }
				 $scriptpageprevnext = 'denomination1.php?denomination='.$ilance->GPC['denomination'].$date_end_onlinks.$page_layout;
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
	    
	
	           
	
	
        $scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
		
			if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
				$daturl_add = '/'.$ilance->GPC['date_end'];
			else
				$daturl_add = '';


			switch($ilance->GPC['action'])
				  {
				  case 'price':
				  
                 
					if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
						{
						  $listing =  '<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none; padding-left:45px;">Price<img alt="" src="images/gc/default/expand_collapsed.gif"></a></td> 
						  <td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=02&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left</a></td> ';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none; padding-left:45px;">Price<img alt="desc" src="images/gc/default/expand.gif"></a></td> 
						  <td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=02&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left</a></td> ';
						 }  
						 
						break;
						
						 case 'type':
				  
                 
					if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
						{
						 $listing =  '<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none; padding-left:45px;">Price</a></td> 
						  <td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Type" style="text-decoration:none">Type<img alt="desc" src="images/gc/default/expand_collapsed.gif"></a></td>
							<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=02&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left</a></td> ';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none; padding-left:45px;">Price</a></td> 
						  <td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=21&action=type" title="Sort by Type" style="text-decoration:none">Type<img alt="desc" src="images/gc/default/expand.gif"></a></td>
							<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=02&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left</a></td> ';
						 }  
						 
						break;
						
						 case 'time':
				  
                 
					if($ilance->GPC['sort']!='02' && $ilance->GPC['sort']!='')
						{
						 $listing =  '<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none; padding-left:45px;">Price</a></td> 
						  <td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=02&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left<img alt="desc" src="images/default/expand_collapsed.gif"></a></td> ';
						 }
						 
						 else
						 {
						   $listing =  '<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none; padding-left:45px;">Price</a></td> 
						  <td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							<td><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=01&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left<img alt="desc" src="images/default/expand.gif"></a></td> ';
						 }  
						 
						break;
						
				
				default:		
						  $listing =  '<td width="12%"><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td>								
                                           <td width="12%"><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
											<td width="12%"><a href="'.HTTP_SERVER .Denomination.'/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($denomination_details['denomination_long']).$daturl_add.'?q='. $ilance->GPC['q'] .'&sort=02&action=time" title="Sort by Time Left" style="text-decoration:none">Time Left</a></td> ';
											
											
					}

if (!empty($ilance->GPC['q']) AND !empty($ilance->GPC['series']))
{
    $con ="AND(c.project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%'OR c.project_id LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%' OR c.description LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%') AND  c.coin_series_unique_no='".$ilance->GPC['series']."' ";
}
else if (!empty($ilance->GPC['q']) AND empty($ilance->GPC['series']))
{
   $con ="AND(c.project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%'OR c.project_id LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%' OR c.description LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%') ";
   
}
else if (!empty($ilance->GPC['series']) )
{
	$con="AND(c.coin_series_unique_no='".$ilance->GPC['series']."')";
}
/*else if (isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
{
   $con =" AND(DATE(c.date_end)='".$ilance->GPC['date_end']."') ";
}*/
else
{}
		
if ($ilance->GPC['sort']=='21') 
{
$orderby ="ORDER BY c.bids ASC";
}
else if ($ilance->GPC['sort']=='22') 
{
$orderby ="ORDER BY c.bids DESC";
}
else if ($ilance->GPC['sort']=='01') 
{
$orderby ="ORDER BY c.date_starts ASC";
}
else if ($ilance->GPC['sort']=='02') 
{
$orderby ="ORDER BY c.date_starts DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$orderby ="ORDER BY c.currentprice ASC";
}
else if ($ilance->GPC['sort']=='12') 
{
$orderby ="ORDER BY c.currentprice DESC";
}
else
{
$orderby ="ORDER BY c.date_end ASC";
}

		
		//5086
		if(!empty($ilance->GPC['denom_all']) and ($ilance->GPC['denom_all'] == '1')){
			$denom_checkbox= ' checked="checked" ';
			$denom_drop_is_sel='style="display:none;"';								
		}
		else{
			$denom_checkbox= '';
			$denom_drop_is_sel='style="display:none;"';
		}
		if(!empty($ilance->GPC['denomination']) || !empty($ilance->GPC['series'])){
			
			$denom_checkbox= '';
			$denom_drop_is_sel='';
			$checkbox_denom_value_all='(Select All)';
		}
		else
		{
			$checkbox_denom_value_all='(Edit)';


			$denom_checkbox= ' checked="checked" ';
		}
		$denom_drop_sql=$ilance->db->query("SELECT denomination_unique_no,denomination_long FROM " . DB_PREFIX . "catalog_toplevel order by denomination_unique_no asc", 0, null, __FILE__, __LINE__);							
		$product_denom_selection='<select name="denomination[]" id="denom_dropdown"  multiple>';

		$gpc_denom_arr=isset($ilance->GPC['denomination'])?$ilance->GPC['denomination']:null;
		
		if(!empty($ilance->GPC['series']))
		{
			$series_details=$ilance->categories_parser->fetch_coin_series(0,$ilance->GPC['series']);
		}

		if(!empty($gpc_denom_arr))
		{
										
			while($denom_drop_res=$ilance->db->fetch_array($denom_drop_sql))
			{

				if(in_array($denom_drop_res['denomination_unique_no'],$gpc_denom_arr) || ($denom_drop_res['denomination_unique_no']==$gpc_denom_arr))
				{
					$product_denom_selection.= '<option value="'.$denom_drop_res['denomination_unique_no'].'" selected="selected">'.$denom_drop_res['denomination_long'].'</option>';

				}
				
				else

				{
					$product_denom_selection.= '<option value="'.$denom_drop_res['denomination_unique_no'].'">'.$denom_drop_res['denomination_long'].'</option>';
				}
				
				
			}
		}
		else{

			while($denom_drop_res=$ilance->db->fetch_array($denom_drop_sql))
			{
				
				if(!empty($ilance->GPC['series'])){
					
					if($series_details['coin_series_denomination_no'] == $denom_drop_res['denomination_unique_no']){
					
						$product_denom_selection.= '<option value="'.$denom_drop_res['denomination_unique_no'].'" selected="selected">'.$denom_drop_res['denomination_long'].'</option>';
					}
					else
					{
						$product_denom_selection.= '<option value="'.$denom_drop_res['denomination_unique_no'].'">'.$denom_drop_res['denomination_long'].'</option>';
					}
					
				}
				else{
				
					$product_denom_selection.= '<option value="'.$denom_drop_res['denomination_unique_no'].'">'.$denom_drop_res['denomination_long'].'</option>';
				}
				
			}
		}


		$product_denom_selection.='</select>'; 

		//echo $product_denom_selection;exit;

		$ilance->GPC['listing_type']=isset($ilance->GPC['listing_type'])?$ilance->GPC['listing_type']:null;
		$checkbox_lt_1= ($ilance->GPC['listing_type'] == '1') ? ' checked="checked" ' :'';
		$checkbox_lt_2= ($ilance->GPC['listing_type'] == '2') ? ' checked="checked" ' :'';
		$checkbox_lt_3= ($ilance->GPC['listing_type'] == '3') ? ' checked="checked" ' :'';
		$checkbox_lt_4= ($ilance->GPC['listing_type'] == '4') ? ' checked="checked" ' :'';

        if (isset($ilance->GPC['sold']) AND $ilance->GPC['sold'])
        {
        	$sqlquery['projectdetails'] .= "AND ((p.haswinner = '1' AND p.winner_user_id > 0) OR p.hasbuynowwinner = '1') ";
                $completed_url_var='&amp;sold=1&amp;ended=1';
            $sold_ended_hidden = '<input type="hidden" name="ended" value="1" /> <input type="hidden" name="sold" value="1" />';
            $show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
        }

        $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '01';
        $sort_value = $ilance->GPC['sort'];
		if(isset($ilance->GPC['action'])){
			$action_field_hidden='<input type="hidden" name="action" value="'.$ilance->GPC['action'].'">';
		}
		else
		{
			$action_field_hidden='';
		}

		if(!empty($ilance->GPC['grading_service']) || !empty($ilance->GPC['fromyear']) || !empty($ilance->GPC['toyear']) || !empty($ilance->GPC['grade_range_1']) || !empty($ilance->GPC['grade_range_2']) || !empty($ilance->GPC['frombid']) || !empty($ilance->GPC['tobid']) || !empty($ilance->GPC['listing_type']) || !empty($ilance->GPC['denom_all']) || !empty($ilance->GPC['denomination']) )
        {
        	//denomination
			if(!empty($ilance->GPC['denom_all']) || !empty($ilance->GPC['denomination']))
			{
				if(!empty($ilance->GPC['denom_all']) && empty($ilance->GPC['denomination'])){
				
					$sqlquery['denomination']= '';
				}
				if(empty($ilance->GPC['denom_all']) && !empty($ilance->GPC['denomination'])){
				
					$gpc_denomination_arr=$ilance->GPC['denomination'];
					for($i=0;$i<count($gpc_denomination_arr);$i++)
					{
						$gpc_denomination_arr_1[]="'".$gpc_denomination_arr[$i]."'";
					}
					
					$gpc_denomination_arr_1=implode(",",$gpc_denomination_arr_1);
					
					$sqlquery['denomination'] = " AND (p.coin_series_denomination_no IN(".$gpc_denomination_arr_1.")) ";
				}
			}
			else
			{
				$sqlquery['denomination']='';
			}

			//Tamil for 3208 starts
			if(!empty($ilance->GPC['grading_service']) )
			{
				$gpc_sql_grading_service_arr=$ilance->GPC['grading_service'];
				if($gpc_sql_grading_service_arr[0] == 'ALL'){
					$sqlquery['grading_service'] = '';
				}
				elseif(in_array('Raw/Other',$gpc_sql_grading_service_arr) && count($gpc_sql_grading_service_arr) ==1 ){
				
					$sqlquery['grading_service'] = " AND (p.Grading_Service ='') ";
				}
				elseif(in_array('CAC',$gpc_sql_grading_service_arr) && count($gpc_sql_grading_service_arr) ==1 ){
					
					$sqlquery['grading_service'] = " AND (p.Cac =1) ";
				}
				else{
					
					for($i=0;$i<count($gpc_sql_grading_service_arr);$i++)
					{
						$gpc_sql_grading_service_arr_1[]="'".$gpc_sql_grading_service_arr[$i]."'";
					}
					
					
				
					if(in_array('CAC',$gpc_sql_grading_service_arr)){
					
						$sqlquery_grading_service_cac = " OR p.Cac=1 ";
					}
					else{
						$sqlquery_grading_service_cac = " AND p.Cac=0 ";
					}
					
					if(in_array('Raw/Other',$gpc_sql_grading_service_arr)){
						
						$sqlquery_grading_service_raw = " OR p.Grading_Service='' ";
						
						$gpc_sql_grading_service_arr_1=array_diff($gpc_sql_grading_service_arr_1,array("'Raw/Other'"));
					}
					else{												
						
						$sqlquery_grading_service_raw ='';								
					}
					
					$gpc_sql_grading_service_arr_1=implode(",",$gpc_sql_grading_service_arr_1);
					
					$sqlquery['grading_service'] = " AND (p.Grading_Service IN(".$gpc_sql_grading_service_arr_1.") ".$sqlquery_grading_service_cac.$sqlquery_grading_service_raw." )";
					
				}
				
			}
			else
			{
				$sqlquery['grading_service'] ='';
			}

			//year range
			if(!empty($ilance->GPC['fromyear']) || !empty($ilance->GPC['toyear']))
			{
			
				$temp=preg_split('#(?<=\d)[/+|\s|_|-]?(?=[a-z])#i', $ilance->GPC['fromyear']);
				$ilance->GPC['fromyear1']=$temp[0];
				$ilance->GPC['mintage']=$temp[1];
				
				$temp=preg_split('#(?<=\d)[/+|\s|_|-]?(?=[a-z])#i', $ilance->GPC['toyear']);
				$ilance->GPC['toyear1']=$temp[0];
				//$ilance->GPC['mintage']=$temp[1];
			

				if(!empty($ilance->GPC['fromyear']) && !empty($ilance->GPC['toyear'])){
				
					$sqlquery['year_range'] = " AND (p.coin_detail_year BETWEEN ". intval($ilance->GPC['fromyear1'])." AND ". intval($ilance->GPC['toyear1'])." ) ";
				}
				if(!empty($ilance->GPC['fromyear']) && empty($ilance->GPC['toyear'])){
				
					$sqlquery['year_range'] = " AND (p.coin_detail_year BETWEEN ". intval($ilance->GPC['fromyear1']) ." AND ".date("Y")." ) ";
				}
				if(empty($ilance->GPC['fromyear']) && !empty($ilance->GPC['toyear'])){
					
					$sqlquery['year_range'] = " AND (p.coin_detail_year BETWEEN ''  AND ". intval($ilance->GPC['toyear1']) .") ";
				}
				
				if(!empty($ilance->GPC['mintage']))
				{
				$sqlquery['year_range'].= " AND (p.mintmark = '".$ilance->GPC['mintage']."' ) ";
				}
				
			}

			//grade range	
			if(!empty($ilance->GPC['grade_range_1']) && !empty($ilance->GPC['grade_range_2']) )
			{
				$gr_1 = ($ilance->GPC['grade_range_1'] =='1') ? 0 : $ilance->GPC['grade_range_1'];									
				$gr_2 = ($ilance->GPC['grade_range_2'] =='1') ? 0 : $ilance->GPC['grade_range_2'];										
				$gr_1 = ($gr_1 < $gr_2) ? $gr_1 : $gr_2;										
				$gr_2 = ($gr_2 > $gr_1) ? $gr_2 : $gr_1 ;										
			
				$sqlquery['grade_range'] = " AND (p.Grade BETWEEN ".$gr_1." AND ".$gr_2.") ";
			}
			else{
				
				$sqlquery['grade_range'] = '';
			}

			//bid range
			$bid_only = '';
            if(isset($ilance->GPC['listing_type']) AND $ilance->GPC['listing_type']!=1)
                $bid_only = "AND p.filtered_auctiontype='regular'";

            if(isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid']=='')
                $ilance->GPC['tobid']=500;

            if(!empty($ilance->GPC['frombid']) || (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid']>=0) )
            {

                $ilance->GPC['frombid']=intval($ilance->GPC['frombid']);
                $ilance->GPC['tobid']=intval($ilance->GPC['tobid']);
                if(!empty($ilance->GPC['frombid']) && !empty($ilance->GPC['tobid'])){
                    
                    $ilance->GPC['frombid'] = ($ilance->GPC['frombid'] < $ilance->GPC['tobid']) ? $ilance->GPC['frombid'] : $ilance->GPC['tobid'];      
                    $ilance->GPC['tobid'] = ($ilance->GPC['tobid'] < $ilance->GPC['frombid']) ? $ilance->GPC['frombid'] : $ilance->GPC['tobid'] ;
                    $sqlquery['bid_range'] = " AND (p.bids>=".$ilance->GPC['frombid']." AND p.bids<=".$ilance->GPC['tobid']." ".$bid_only." ) ";                        
                    
                }
                
                if(!empty($ilance->GPC['frombid']) && (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid']>=0) ){
                    
                    $ilance->GPC['tobid'] = ($ilance->GPC['tobid'] < $ilance->GPC['frombid']) ? $ilance->GPC['frombid'] : $ilance->GPC['tobid'] ;
                    $sqlquery['bid_range'] = " AND (p.bids >= ".$ilance->GPC['frombid']."  AND p.bids <= ".$ilance->GPC['tobid']." ".$bid_only." )";
                }
                if(empty($ilance->GPC['frombid']) && (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid']>=0 ) ){
                    
                    $sqlquery['bid_range'] = " AND (p.bids >= 0 AND p.bids <= ".$ilance->GPC['tobid']." ".$bid_only." )";
                }

            }

            if(!empty($ilance->GPC['listing_type']))
            {						
				switch($ilance->GPC['listing_type']){
					case '1':
					{
						$sqlquery['listing_type']=" AND (p.filtered_auctiontype='regular') ";
						break;
					}
					case '2':
					{
						$sqlquery['listing_type']=" AND (p.filtered_auctiontype='fixed') ";
						$sqlquery['bid_range'] ='';
						break;
					}
					case '3':
					{
						$sqlquery['join_coins']= " JOIN " . DB_PREFIX . "coins c ON p.project_id = c.coin_id AND c.nocoin > 1 ";												
						break;
					}
					case '4':
					{
						//$sqlquery['timestamp'] = "AND (UNIX_TIMESTAMP(p.date_end) < UNIX_TIMESTAMP('" . DATETIME24H . "'))";
						$sqlquery['timestamp'] = " ";
						$sqlquery['projectstatus'] = "AND (p.status != 'open')";
						break;
					}
				}
			}


        }	

        //Tamil bug 2389 starts
		$grading_comp_arr=array('ALL','PCGS', 'NGC', 'ANACS', 'Raw/Other', 'CAC');
		
		$grading_service_dropdown='<select name="grading_service[]" multiple>';	
		
		
		$gpc_grading_arr=!empty($ilance->GPC['grading_service'])?$ilance->GPC['grading_service']:'';							
		if(!empty($gpc_grading_arr)){
			
			foreach($grading_comp_arr as $default_grading_company)
			{
				if(in_array($default_grading_company,$ilance->GPC['grading_service']))
				{
					$grading_service_dropdown.='<option value="'.$default_grading_company.'" selected="selected">'.$default_grading_company.'</option>';
				}
				else
				{
					$grading_service_dropdown.='<option value="'.$default_grading_company.'" >'.$default_grading_company.'</option>';
				}
			}
		}
		else
		{
			foreach($grading_comp_arr as $result2_val){
				$grading_service_dropdown.='<option value="'.$result2_val.'" >'.$result2_val.'</option>';
			}
		}
		$grading_service_dropdown.='</select>';

        $grade_range_dropdown_1='<select name="grade_range_1">';
		$grade_range_dropdown_2='<select name="grade_range_2">';							
		$grade_range_sql= $ilance->db->query("select *
						from " . DB_PREFIX . "coin_proof 
						");
		
		while($grade_range_res=$ilance->db->fetch_array($grade_range_sql))
		{
			if(isset($ilance->GPC['grade_range_1']) and $ilance->GPC['grade_range_1']==$grade_range_res['value']){
				$grade_range_dropdown_1.='<option value="'.$grade_range_res['value'].'" selected>'.$grade_range_res['value'].'</option>';
			}
			else{
				if($grade_range_res['value']==1)
				$grade_range_dropdown_1.='<option value="'.$grade_range_res['value'].'" selected>'.$grade_range_res['value'].'</option>';
				else
				$grade_range_dropdown_1.='<option value="'.$grade_range_res['value'].'">'.$grade_range_res['value'].'</option>';
			}									
				
			if(isset($ilance->GPC['grade_range_2'])){
				
				if($ilance->GPC['grade_range_2']==$grade_range_res['value'])
				$grade_range_dropdown_2.='<option value="'.$grade_range_res['value'].'" selected>'.$grade_range_res['value'].'</option>';
				else
				$grade_range_dropdown_2.='<option value="'.$grade_range_res['value'].'">'.$grade_range_res['value'].'</option>';
			}
			else{
			
				if($grade_range_res['value']=='70'){
					$grade_range_dropdown_2.='<option value="'.$grade_range_res['value'].'" selected>'.$grade_range_res['value'].'</option>';
				}
				else
				$grade_range_dropdown_2.='<option value="'.$grade_range_res['value'].'">'.$grade_range_res['value'].'</option>';
			}
				
		}

		$fromyear= !empty($ilance->GPC['fromyear']) ? $ilance->GPC['fromyear'] : '';
		$toyear= !empty($ilance->GPC['toyear']) ? $ilance->GPC['toyear'] : date('Y');
		
		$frombid= !empty($ilance->GPC['frombid'] ) ? $ilance->GPC['frombid'] :'0';
		$tobid= (isset($ilance->GPC['tobid']) AND $ilance->GPC['tobid']>=0) ? $ilance->GPC['tobid'] :'500';

		if(isset($ilance->GPC['series'])){
			$series_hidden_fld='<input type="hidden" name="series" value="'.$ilance->GPC['series'].'">';
		}
		else{
			$series_hidden_fld='';
		}

//5086
	 
					$select_featurednew_query="select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.highlite,c.bold,c.status
					from " . DB_PREFIX . "projects c				
					where 
					$comp
					AND date(date_end)='".$ilance->GPC['date_end']."'
					$featured
					$bidrng
					AND c.visible = '1'
					$con
					AND c.coin_series_denomination_no='".$ilance->GPC['denomination']."' 
					group by c.project_id
					$orderby LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
					"; 
				   

				   
				   
				    $select_featurednew= $ilance->db->query($select_featurednew_query);
				   
				
				   
					$select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.highlite,c.bold 
					from  " . DB_PREFIX . "projects c				
					where 
					$comp
					AND date(date_end)='".$ilance->GPC['date_end']."'
					$featured
					$bidrng
					AND c.visible = '1'
					$con
					AND c.coin_series_denomination_no='".$ilance->GPC['denomination']."'
					group by c.project_id
					$orderby ");

				
				   
				   $total_num1=$ilance->db->num_rows($select_featurednew);
				   if($total_num1)
				   {
				   $total_num=true;
				   }
				   else
				   {
				
				   $total_num=false;
				   }
				   
				      if($ilance->db->num_rows( $select_featurednew) > 0)
				{
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
					  if($ilance->GPC['list']=='gallery')
				 {
				   $show['title']=false;
				  }
				  else
				  {
				   $show['title']=true;
				   }   
				 $count_gal=1;  
			  $gal_view.= '<table><tr >';
	              while($det=$ilance->db->fetch_array($select_featurednew))
				   {
				             $projectid=$det['project_id'];
					//karthik may03
					  if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
								  
                                    $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($projectid, $_SESSION['ilancedata']['user']['userid']);
								  	
									if ($pbit > 0)
                                    {
																		
											 $highbidderidtest = $ilance->bid->fetch_highest_bidder($projectid);
																										// murugan on feb 25
										if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
										$proxybit = '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
										else
										$proxybit ='<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
';                                          $show['proxy']=true;
																		
									}
									else
									{
										$proxybit = '';	
									}
									 unset($pbit);
					//karthik on jun04				
					 $sql_buynow = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "buynow_orders
                        WHERE project_id = '" .$det['project_id']  . "'
                        AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
				
				 if ($ilance->db->num_rows($sql_buynow) > 0)
                {
				
                       	$buy_now = '<div class="smaller green" style="padding-top:4px">You own this item</div>';
						
                }
				else
				{
				   $buy_now = '' ;
				}
			                                                    //unset($pbit);
                            }
																				//karthik end may03
					
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					           
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
										 
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
									
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }
								
								
								$cat=$ilance->db->query("select bids from " . DB_PREFIX . "projects where buynow='0' AND project_id= '".$projectid."'");
								 if($ilance->db->num_rows($cat) > 0)
									{
										$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($det['currentprice']). '</strong>';
									}
									else
									{
										$listpageg['currentprice']='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($det['currentprice'],$currencyid). '</strong>';
									}
					            //$listpageg['currentprice']='<strong>' .$ilance->currency->format($det['currentprice'], $currencyid). '</strong>';
							    
								
		       $type=$ilance->db->query("select bids from " . DB_PREFIX . "projects where $buynow='0' AND project_id= '".$projectid."'");
		       $fet=$ilance->db->fetch_array($type);
			   if($ilance->db->num_rows($type)>0)
			   {
			   $listpageg['bids']='<span class="blue">'.$fet['bids'].' '.'Bids</span>';
			   }
			   else
			   {
			   $listpageg['bids']='<span class="blue">Buy<br>Now</span>';
			   }
		                
						
						   //sekar works on oct 14  for bug num 943 daily deal
						   
						   
														if($det['status'] == 'open')
														{
															   
																   $sql_display = $ilance->db->query("
												
																						SELECT offer_amt FROM
																	
																						" . DB_PREFIX . "dailydeal
																	
																						WHERE project_id = '".$det['project_id']."' 
																						
																						AND live_date = '".DATETODAY."'
												
																										");
																  if($ilance->db->num_rows($sql_display)>0)
																   {
																   $ret = $ilance->db->fetch_array($sql_display);
																   
																   $rrr = ($ret['offer_amt']+$respjt['buynow_price']);
																
																 $display = '<div style="color:#FF0000; font-weight:bold;">24-Hour Deal Was '.$ilance->currency->format($rrr).'.   Save '.$ilance->currency->format($ret['offer_amt']).'</div>';
																
															   }
																
														}			        		
							
					   $sql_attya = $ilance->db->query("
                        SELECT  attachtype,user_id,project_id,category_id,coin_id,filename,filehash FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$det['project_id']."'
						AND (attachtype = 'itemphoto') order by attachid desc
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
				
					if($ilance->db->num_rows($sql_attya) > 0)
					{
					
					 $count_attachtype= $ilance->db->query("
                        SELECT  count(attachtype) as imag FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$det['project_id']."'
						AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') order by attachid desc
						
                        ");
						
						$display_count=$ilance->db->fetch_array($count_attachtype);
					
                          $pictures = 0;
						  
						  if ($fetch_newa['attachtype'] == 'slideshow')
                        {
                                $pictures++;
                        }
					
					
						$uselistra = HTTPS_SERVER. 'image.php?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash'];
						// murugan changes on jun 17 
						$uselistgal = HTTPS_SERVER. 'image.php?cmd=thumb&subcmd=resultsgallery&id=' . $fetch_newa['filehash'];  
						if($ilance->GPC['list']=='list' || empty($ilance->GPC['list']))
						{
											if ($ilconfig['globalauctionsettings_seourls'])
											$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
						<div class="gallery-thumbs-entry">
						  <div class="gallery-thumbs-main-entry">
							<div class="gallery-thumbs-wide-wrapper">
							  <div class="gallery-thumbs-wide-inner-wrapper" align="center">
											<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);" src="'.$uselistra.'" ></a> 
											<div class="gallery-thumbs-corner-text"><span>' . ($display_count['imag']) . ' photos</span></div>
							  </div>
							</div>
						  </div>
						</div>
					  </div>';
											else
											
											$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
						<div class="gallery-thumbs-entry">
						  <div class="gallery-thumbs-main-entry">
							<div class="gallery-thumbs-wide-wrapper">
							  <div class="gallery-thumbs-wide-inner-wrapper"><a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a> 
							  </div>
							</div>
						  </div>
						</div>
					  </div>';
			}
			else
				{
					  if ($ilconfig['globalauctionsettings_seourls'])
								$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
			<div >
			  <div >
				<div >
				  <div>
								<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);" src="'.$uselistgal.'" ></a> 
				  </div>
				</div>
			  </div>
			</div>
		  </div>';
								else
								$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
			<div class="gallery-thumbs-entry">
			  <div class="gallery-thumbs-main-entry">
				<div class="gallery-thumbs-wide-wrapper">
				  <div class="gallery-thumbs-wide-inner-wrapper"><a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;">></a> 
				  </div>
				</div>
			  </div>
			</div>
		  </div>';
					}			
						
					}
					if($ilance->db->num_rows($sql_attya) == 0)
				
					{
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
						
					   //$listpageg['yy']=$htm; 
					     
						 
						 $listpageg['class']=($det['highlite'] == '1') ? 'featured_highlight' : '';
						 $listpageg['class1']=($det['bold'] =='1')?'bold_highlight' : '';
						 
					   $listpage[]=	$listpageg;	
				 switch($ilance->GPC['list'])
				  {
				
				  case 'gallery':
				  
				  if($count_gal%3==0)
				  {
				$gal_view.= '<td class="alt1"><table width="100%"><tr><td  width="33.33%">
				                          
				                         <div align="center" style="padding-bottom: 30px;">'.$listpageg['imgval'].'</div>
										 <div id="{class1}">'.$listpageg['project_title'].'</div>
										  <div align="left" style="padding: 2px;"><span style="float: right; padding-top: 2px;"></span>&nbsp;</div>
										 <div class="smaller blue">'.$listpageg['bids'].'<span style="float: right; font-size:12px; color:#000000; font:10pt arial,helvetica,verdana,sans-serif;">'.$listpageg['currentprice'].'</span></div>									
										 <div align="left" style="padding: 7px;"><span style="float: right; padding-top: 5px;"></span>&nbsp;</div>
                                         <div style="float:right;"><span style="float: right;">'.$listpageg['date_end'].'</span></div>
										  <div align="left" style="padding: 4px;"><span style="float: right; padding-top: 4px;"></span>&nbsp;</div>
										<tr>
	
										</tr></table></td></tr><tr>';
										}
										else
										{
										$gal_view.= '<td class="alt1 " width="33.33%"><table width="100%"><tr >
										  <div align="center" style="padding-bottom: 30px;">'.$listpageg['imgval'].'</div>
										 <div id="{class1}">'.$listpageg['project_title'].'</div>
										  <div align="left" style="padding: 2px;"><span style="float: right; padding-top: 2px;"></span>&nbsp;</div>
										 <div class="smaller blue">'.$listpageg['bids'].'<span  style="float: right; font-size:12px; color:#000000; font:10pt arial,helvetica,verdana,sans-serif;">'.$listpageg['currentprice'].'</span></div>									
										 <div align="left" style="padding: 7px;"><span style="float: right; padding-top: 5px;"></span>&nbsp;</div>
                                         <div style="float:right;"><span style="float: right;">'.$listpageg['date_end'].'</span></div>
										 <div align="left" style="padding: 4px;"><span style="float: right; padding-top: 4px;"></span>&nbsp;</div>
										</tr></table></td><td class="alt1_left"></td>';
				
			 							}
			 $count_gal++;
						 $list='	<span class="smaller" >
			<a title="List view" href="Denomination/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'">
			<img border="0" alt="List View" src="'.HTTPS_SERVER.'images/gc/icons/list.gif" style="margin-left: 380px;">
			</a>
			<img border="0" alt="Gallery view" src="'.HTTPS_SERVER.'images/gc/icons/gallery_current.gif">';
										
										
					break;
					
					default:
					 
					     $show['che_box'] = true;
						$che_box='<input id=product_'.$det['project_id'].' type="checkbox" name="project_id[]" style="margin-left: 20px;" value="'.$det['project_id'].'">'; 
					     $gal_view.= '	<tr class="alt1" id="' .$listpageg['class'].'" style="height: 100px;">
										 <td  width="12%">'.$listpageg['imgval'].'</td>
										 <td width="40%" id="'.$listpageg['class1'].'">'.$listpageg['project_title'].'<br>'.$proxybit.$buy_now .'</td>
										 <td  width="12%">'.$listpageg['currentprice'].'</td>										
										  <td  width="12%" class="smaller blue">'.$listpageg['bids'].'</td>
                                         <td  width="12%">'.$listpageg['date_end'].'</td>
										 <td width="12%">'.$che_box.'</td>
										<tr><td>'.$display.'</td></tr>
										</tr>
										';
										
								 $list='	<span class="smaller" >
			
			<img border="0" alt="List View" src="'.HTTPS_SERVER.'images/gc/icons/list_current.gif" style="margin-left: 380px;">
			
			<a title="Gallery view" href="Denomination/'.$ilance->GPC['denomination'].'/'
			.construct_seo_url_name($text).'/'.$ilance->GPC['date_end'].'?mode=product&amp;sort=01&amp;page=1&amp;list=gallery&amp;pp=12"><img border="0" alt="Gallery view" src="'.HTTPS_SERVER.'images/gc/icons/gallery.gif"></a>';
		
					}			
			   
             }
			 
			//	$gal_view.= '</tr ></table>';
                   }
				   
			
				   //karthik changes on apr 29
				   $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
				   	$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
					
					
					//amutha changes	
					$cid>0;
					$denomination=$ilance->GPC['denomination'];
					//given mode as product
					$leftnav_options = print_options1('product',$denomination);
					$search_bidrange_pulldown_product = print_bid_range_pulldown1($ilance->GPC['bidrange'], 'bidrange', 'productbidrange', 'links',$denomination);
					
					$denomination=$ilance->GPC['denomination'];
					$leftnav_buyingformats = print_buying_formats1($denomination);
					
				/*	$clear_listtype = ($show['allbuyingformats'])
				? ''
				: '<a href="' . $clear_listtype_url . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
				
				$clear_bidrange = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';*/
			//sekar working on searc same page on may28
			
                                $v3left_nav = $ilance->template->print_left_nav1('product', $cid, $dosubcats = 1, $displayboth = 0, $ilconfig['globalfilters_enablecategorycount'], true);
								
								
								
								                        ($apihook = $ilance->api('search_results_providers_end')) ? eval($apihook) : false;
                        
                        $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
                        $sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', $expertsmode = true);
                        
                        $hiddenfields = print_hidden_fields(false, array('searchid','cid','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','q','sort','page'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
                        $hiddenfields_leftnav = print_hidden_fields(false, array('searchid','feedback','country','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','page','radius','radiuscountry','radiuszip'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
		
                       //amutha changes	end
					  ///// featured sekar
					   
					 
				
					   
					   //featured
					   $count_fea=1;
					   
					   
					   $featured_select1= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
				   from   " . DB_PREFIX . "projects c				
					
					where 
				   $comp
				   AND date(date_end)='".$ilance->GPC['date_end']."'
				   $featured
				   $bidrng
				   
				   AND c.visible = '1'
				   $con
				   AND c.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND featured = '1'
				   AND c.cid !='0'
                   group by c.project_id
				   ORDER BY c.date_end ASC LIMIT 4
                   ");
				   
				   
			
				   $total_num1=$ilance->db->num_rows($featured_select1);
				   if($total_num1)
				   {
				   $total_num=true;
				   }
				      if($ilance->db->num_rows( $featured_select1) > 0)
				{
				$c=0;
				
				//$sep = '<div id="seperator" style="height:165px;"></div>';
				     $number = (int)$ilance->db->num_rows( $featured_select2);
				   
				   $show['featured']=1;
			  $table_featured='<tr>';
			  
	              while($det=$ilance->db->fetch_array($featured_select1))
				   {
				   if($c < 1)
			
				
				             $projectid=$det['project_id'];
					//karthik may03
					  if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
								  
                                    $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($det['project_id'], $_SESSION['ilancedata']['user']['userid']);
								  	
									if ($pbit > 0)
                                    {
																		
											 $highbidderidtest = $ilance->bid->fetch_highest_bidder($det['project_id']);
																										// murugan on feb 25
										if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
										$listpageg['proxybit'] = '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
										else
										$listpageg['proxybit'] ='<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
';                                          $show['proxy']=true;
																		
																		}
                                                                                        unset($pbit);
                                                                                }
																				//karthik end may03
					
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					           
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								
								// Murugan changes on mar 22
								
								
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
										 
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
									
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }
								
								
								/*$cat=$ilance->db->query("select bids from " . DB_PREFIX . "projects where buynow='0' AND project_id= '".$projectid."'");
								 if($ilance->db->num_rows($cat) > 0)
									{
										$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($det['currentprice']). '</strong>';
									}
									else
									{
										$listpageg['currentprice']='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($det['currentprice'],$currencyid). '</strong>';
									}
					            //$listpageg['currentprice']='<strong>' .$ilance->currency->format($det['currentprice'], $currencyid). '</strong>';
							    
								
		       $type=$ilance->db->query("select bids from " . DB_PREFIX . "projects where $buynow='0' AND project_id= '".$projectid."'");
		       $fet=$ilance->db->fetch_array($type);
			   if($ilance->db->num_rows($type)>0)
			   {
			   $listpageg['bids']='<span class="blue">'.$fet['bids'].' '.'Bids</span>';
			   }
			   else
			   {
			   $listpageg['bids']='<span class="blue">Buy<br>Now</span>';
			   }*/
		                        		
								
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
					    $count_attachtype= $ilance->db->query("
                        SELECT  count(attachtype) as imag FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$det['project_id']."'
						AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') order by attachid desc
						
                        ");
						
						$display_count=$ilance->db->fetch_array($count_attachtype);
						$uselistra = 'image.php?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper" align="center">
						<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
						
						<div class="gallery-thumbs-corner-text"><span>' . ($display_count['imag']) . ' photos</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper" align="center"><a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px; "></a> 
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
						$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
						
					   //$listpageg['yy']=$htm; 
					   
					   //featured 
					
					   
					   $table_featured.='<td><table> <tr><td>'.$listpageg['imgval'].'</td></tr><tr><td>'.$listpageg['project_title'].'</td></tr><tr><td>'.$listpageg['currentprice'].'</td></tr><tr><td>'.$listpageg['date_end'].'</td></tr></table></td><td class="alt1_left"></td>';
					   
					 
					  
					  
					    
						
					   $count_fea++;
					  //featured div   
					   
			 
				
			   
             }
                   }
					   
			//for bug #5384
            $show['show_end_date'] = $show_end_date_text = $end_date_removed_url = 0;
            $show_end_date_text = '';
            if(isset($ilance->GPC['date_end']) and $ilance->GPC['date_end']!='')
            {
                $show['show_end_date'] =  1;
                $show_end_date_text = 'Only coins ending on '. date("F d,Y",strtotime($ilance->GPC['date_end']));
                $datend_hidn = '<input type="hidden" name="date_end" value="'.$ilance->GPC['date_end'].'" />';
                $reqst_url = explode('&', $_SERVER['QUERY_STRING']);

                $emptykey = count($reqst_url)-1;
                foreach($reqst_url as $key=>$reqsturl)
                {
                    $requrl = explode('=', $reqsturl);
                    if($requrl[0] == 'date_end')
                        unset($reqst_url[$key]);

                    if($requrl[0] == 'denomination')
                    {
                    	unset($reqst_url[$key]);
                    	$reqst_url[] = 'denomination[]='.$requrl[1];
                    }
                }
                unset($reqst_url[$emptykey]);
           
                $end_date_removed_url = implode('&',$reqst_url).'&dEnom_search=1&mode=product&sort=01&sef=1';

            }	
					  
			//$profes = print_pagnation($number1, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);		   
					   
					   
					   
					   ///// featured sekar end
			
						
	$pprint_array = array('end_date_removed_url','show_end_date_text','frombid','tobid','checkbox_denom_value_all','product_denom_selection','series_hidden_fld','action_field_hidden','toyear','grading_service_dropdown','grade_range_dropdown_2','grade_range_dropdown_1','fromyear','sort_value','sold_ended_hidden','datend_hidn','prof','profes','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','listing','clear_bidrange','clear_listtype','leftnav_buyingformats','search_bidrange_pulldown_product','v3left_nav','prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','hiddenfields','clear_budgetrange','clear_currencies','leftnav_currencies','clear_local','clear_feedback','leftnav_feedbackrange','leftnav_ratingrange','clear_rating','clear_award','leftnav_awardrange','sort','country','clear_price','clear_options','leftnav_options','leftnav_options','showallurl','clear_region','leftnav_regions','full_country_pulldown','didyoumean','search_radius_country_pulldown_experts','search_country_pulldown_experts','favtext','favoritesearchurl','profilebidfilters','fewer_keywords','fromprice','toprice','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','search_country_pulldown2','hiddenfields','search_results_table','sortpulldown2','keywords','two_column_category_vendors','keywords','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','input_style','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','input_style','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','table_featured','list','gal_view','proxybit','listing');
       //sekar finished on june 01
	$ilance->template->fetch('main', 'merch_series_listings.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', 'listpage');
	 $ilance->template->parse_loop('main', 'featured');
	 //sekar listings on categoris finished 
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// finish copy here finish copy here finish copy here finish copy here finish copy here 
//Karthik on jun03
///prices realized listing
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'CoinPrices')
{
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list all denominations
	$show['widescreen'] = true;
	$area_title = 'Prices Realized';
	$page_title = SITE_NAME . ' - ' . 'Prices Realized';
	$topnavlink = array('main_categories');
		$ilance->categories_parser=construct_object('api.categories_parser');
	$text="List of all Prices Realized";
	$categoryresults=$ilance->categories_parser->html_denomination_price();
	$text1='Prices Realized/Coin Prices';
	//$categoryresults=$ilance->categories_parser->html_coin_class($ilance->GPC['denomination']);
	$navcrumb = array("$ilpage[denomination]" => $text1);
	$check = $ilconfig['globalauctionsettings_seourls'];
    //new change
	
	//$search_category_pulldown=$ilance->categories_parser->demonomination_dropwdown('denominationid',0,true);
	$search_category_pulldown=$ilance->categories_parser->demonomination_dropwdown_price_new('denominationid',0,true);
	  $pprint_array = array('check','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
	$ilance->template->fetch('main', 'merch_coin_realized.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
	
}
//karthik on jun03
else if(isset($ilance->GPC['CoinPrices']) AND $ilance->GPC['CoinPrices'])
{
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list series for selected denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
	$ilance->categories_parser=construct_object('api.categories_parser');
	$denomination_details=$ilance->categories_parser->fetch_denominations($ilance->GPC['denomination']);
	$text='Prices Realized/Coin Prices';
	$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
	$navcrumb = array(HTTP_SERVER .CoinPrices => $text );
	 $navcrumb[""] = $denomination_details['denomination_long'];
//new change
	//$search_category_pulldown=$ilance->categories_parser->series_dropwdown($ilance->GPC['denomination'],'series_id',0,true);
	
	$search_category_pulldown=$ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'],'series',0,true);
	
	
	//sekar listings on categoris
	
	$ilance->GPC['denomination'];
	
	
  //counter for page 
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = 'CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'];
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
	    
	
        $scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
		
				switch($ilance->GPC['action'])
				  {
				  case 'price':
				  
                 
					if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
						{
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							 ';
						 }  
						 
						break;
						
						 case 'type':
				  
                 
					if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
						{
						 $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Type" style="text-decoration:none">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
							';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=21&action=type" title="Sort by Type" style="text-decoration:none">Type<img alt="desc" src="images/default/expand.gif"></a></td>
							';
						 }  
						 
						break;
						
				
				default:		
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td>								
                                           <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
											';
									
					}
		
		if ($ilance->GPC['sort']=='21') 
{
$orderby ="ORDER BY c.bids ASC";
}
else if ($ilance->GPC['sort']=='22') 
{
$orderby ="ORDER BY c.bids DESC";
}
else if ($ilance->GPC['sort']=='01') 
{
$orderby ="ORDER BY c.date_starts ASC";
}
else if ($ilance->GPC['sort']=='02') 
{
$orderby ="ORDER BY c.date_starts DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$orderby ="ORDER BY c.currentprice ASC";
}
else if ($ilance->GPC['sort']=='12') 
{
$orderby ="ORDER BY c.currentprice DESC";
}
else
{
$orderby ="ORDER BY c.date_end ASC";
}
	
				   $select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
				   from  " . DB_PREFIX . "projects c 
				   where 
				   c.status = 'expired'
				   AND c.visible = '1'
				   AND c.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid !='0'
                   group by c.project_id
				   $orderby LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                   ");
				   
				   
				    $select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
				   from  " . DB_PREFIX . "projects c
				   where 
				   c.status = 'expired'
				   AND c.visible = '1'
				   AND c.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid !='0'
                   group by c.project_id
				   $orderby ");
				   
				
				   
				   $total_num1=$ilance->db->num_rows($select_featurednew12);
				   if($total_num1)
				   {
				   $total_num=true;
				   }
				      if($ilance->db->num_rows( $select_featurednew12) > 0)
				{
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
				   
			  
	              while($det=$ilance->db->fetch_array($select_featurednew))
				   {
				             $projectid=$det['project_id'];
					//karthik may03
					  if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
								  
                                    $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($det['project_id'], $_SESSION['ilancedata']['user']['userid']);
								  	
									if ($pbit > 0)
                                    {
																		
											 $highbidderidtest = $ilance->bid->fetch_highest_bidder($det['project_id']);
																										// murugan on feb 25
										if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
										$listpageg['proxybit'] = '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
										else
										$listpageg['proxybit'] ='<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
';                                          $show['proxy']=true;
																		
																		}
                                                                                        unset($pbit);
                                                                                }
																				//karthik end may03
					
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					           
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .'Sold'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .'Sold'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
										 
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
									
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }
								
								
								$cat=$ilance->db->query("select bids from " . DB_PREFIX . "projects where buynow='0' AND project_id= '".$projectid."'");
								 if($ilance->db->num_rows($cat) > 0)
									{
										$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($det['currentprice']). '</strong>';
									}
									else
									{
										$listpageg['currentprice']='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($det['currentprice'],$currencyid). '</strong>';
									}
					            //$listpageg['currentprice']='<strong>' .$ilance->currency->format($det['currentprice'], $currencyid). '</strong>';
							    
								
		       $type=$ilance->db->query("select bids from " . DB_PREFIX . "projects where $buynow='0' AND project_id= '".$projectid."'");
		       $fet=$ilance->db->fetch_array($type);
			   if($ilance->db->num_rows($type)>0)
			   {
			   $listpageg['bids']='<span class="blue">'.$fet['bids'].' '.'Bids</span>';
			   }
			   else
			   {
			   $listpageg['bids']='<span class="blue">Buy<br>Now</span>';
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
						$uselistra = 'image.php?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
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
						$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
						
					   //$listpageg['yy']=$htm; 
					     
					   $listpage[]=	$listpageg;	
			   
				
			   
             }
                   }
				   
			
				   //karthik changes on apr 29
				   $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
				   	$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);	
	$pprint_array = array('prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','listing');
       
	$ilance->template->fetch('main', 'merch_coin_realized_listing.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', 'listpage');
	 //sekar listings on categoris finished 
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// #### COIN SERIES LISTINGS #################################################
//karthik changes on jun03
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'price_go')
{
$show['widescreen'] = true;
$area_title = $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . 'Prices Realized';
	$topnavlink = array('main_categories');
	$ilance->categories_parser=construct_object('api.categories_parser');
	$denomination_details=$ilance->categories_parser->fetch_denominations($ilance->GPC['denomination']);
	$text1='Prices Realized/Coin Prices';
	$text='Prices Realized - '.$denomination_details['denomination_long'].'';
	$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
	$navcrumb = array(HTTP_SERVER .CoinPrices => $text1 );	
    $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
    $navcrumb[""] = $denomination_details['denomination_long'];
    
				 				 $scriptpageprevnext = 'CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'];
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
         $subquery=' where id='. $ilance->GPC['denomination'].'';
		$result=$ilance->db->query("select * from " . DB_PREFIX . "catalog_toplevel ".$subquery." order by denomination_sort");
        $row=$ilance->db->fetch_array($result);
		
				 
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
//karthik on may27 for sort
switch($ilance->GPC['action'])
				  {
				  case 'price':
				  
                 
					if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
						{
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Price" style="text-decoration:none">Type</a></td>
							 ';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Price" style="text-decoration:none">Type</a></td>
						';
						 }  
						 
						break;
						
						 case 'type':
				  
                 
					if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
						{
						 $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Price" style="text-decoration:none">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
							 ';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=11&action=type" title="Sort by Price" style="text-decoration:none">Type<img alt="desc" src="images/default/expand.gif"></a></td>
							 ';
						 }  
						 
						break;
						
					
				default:		
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td>								
                                           <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($row['denomination_long']).'?cmd=price_go&q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Price" style="text-decoration:none">Type</a></td>
											';
					}
					
if (!empty($ilance->GPC['q']) AND !empty($ilance->GPC['series']))
{
     $condition ="AND(c.project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%'OR c.project_id LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%' OR c.description LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%')AND     cc.coin_series_unique_no='".$ilance->GPC['series']."'";
	 
}
else if (!empty($ilance->GPC['q']) AND empty($ilance->GPC['series']))
{
   $condition ="AND(c.project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%'OR c.project_id LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%' OR c.description LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%')AND    cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'";
   
}
else if (!empty($ilance->GPC['series']) )
{
$condition="AND(cc.coin_series_unique_no='".$ilance->GPC['series']."')";
}
else
{
$condition="AND(cc.coin_series_denomination_no='".$ilance->GPC['denomination']."')";
}
if ($ilance->GPC['sort']=='31') 
{
$orderby ="ORDER BY c.Orderno ASC";
}
else if ($ilance->GPC['sort']=='32') 
{
$orderby ="ORDER BY c.Orderno DESC";
}
//karthik on may27 for sort order
else if ($ilance->GPC['sort']=='21') 
{
$orderby ="ORDER BY c.bids ASC";
}
else if ($ilance->GPC['sort']=='22') 
{
$orderby ="ORDER BY c.bids DESC";
}
else if ($ilance->GPC['sort']=='01') 
{
$orderby ="ORDER BY c.date_starts ASC";
}
else if ($ilance->GPC['sort']=='02') 
{
$orderby ="ORDER BY c.date_starts DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$orderby ="ORDER BY c.currentprice ASC";
}
else if ($ilance->GPC['sort']=='12') 
{
$orderby ="ORDER BY c.currentprice DESC";
}
else
{
$orderby ="ORDER BY c.date_end ASC";
}
  $select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow				   
                                          from 
					                      " . DB_PREFIX . "catalog_coin cc, 
					                      " . DB_PREFIX . "catalog_second_level cs,
					                      " . DB_PREFIX . "projects c				
					                      where 
										  c.status = 'expired'
										  $condition
										  AND c.visible = '1'
										   AND c.cid=cc.PCGS
										   AND c.cid !='0'
										 group by c.project_id
										  $orderby  LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                                           ");
				   $total_num1=$ilance->db->num_rows($select_featurednew);
				   if($total_num1)
				   {
						   $total_num=true;
				   }
				   if($ilance->db->num_rows( $select_featurednew) > 0)
				   {
                       $select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow
																	   from 
																		" . DB_PREFIX . "catalog_coin cc, 
																		" . DB_PREFIX . "catalog_second_level cs,
																		" . DB_PREFIX . "projects c				
																		where 
																	   c.status = 'expired'
																	    $condition
																	   AND c.visible = '1'
																	   AND c.cid=cc.PCGS
																	   AND c.cid !='0'
																	   group by c.project_id
                                                                   ");
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
	                 while($det=$ilance->db->fetch_array($select_featurednew))
				     {
				               $projectid=$det['project_id'].'<br>';
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					            $listpageg['date_end']='<strong>' . $ilance->auction->auction_timeleft($det['project_id'], 'center') . '</strong>';
								$buynow=$det['buynow'];
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
                              if($respjt['haswinner']=='1')
										{
										  $sol='Sold';
										 }
										 else
										 {
										  $sol='Unsold';
										 }  
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .$sol.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .$sol.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .$sol.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }  $sql_attya = $ilance->db->query("
																				SELECT * FROM
														
														
														
																				" . DB_PREFIX . "attachment
														
														
														
																				WHERE visible='1' 
														
														
														
																				AND project_id = '".$det['project_id']."'
														
														
														
																				AND attachtype='itemphoto' order by attachid desc
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
					if($ilance->db->num_rows($sql_attya) > 0)
					{
						$uselistra = HTTPS_SERVER. 'image.php?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
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
						$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
									 	$listpage[]=$listpageg;	
									 }
									 
					 
						
							} 
							else
							{
							
							$total_num=false;
							
							$listpage[]='';	
							
							}
							
						$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
							
						$search_category_pulldown=$ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'],'series',0,true);
						
						$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
					
						$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
									
						$pprint_array = array('prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','listing');
	                   $ilance->template->fetch('main', 'merch_coin_realized_listing.html'); 
	                   $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	                    $ilance->template->parse_loop('main', 'listpage');
	 //sekar listings on categoris finished 
						$ilance->template->parse_if_blocks('main');
				
					    $ilance->template->pprint('main', $pprint_array);
				
					    exit();
				
}
//karthik end
//karthik on jun03
else if(isset($ilance->GPC['ended']) AND $ilance->GPC['ended'])
{
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list series for selected denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . 'Prices Realized';
	$topnavlink = array('main_categories');
	$ilance->categories_parser=construct_object('api.categories_parser');
	$denomination_details=$ilance->categories_parser->fetch_denominations($ilance->GPC['denomination']);
	$text1='Prices Realized/Coin Prices';
	$text='Prices Realized - '.$denomination_details['denomination_long'].'';
	$categoryresults=$ilance->categories_parser->html_coin_series($ilance->GPC['denomination']);
	$navcrumb = array(HTTP_SERVER .CoinPrices => $text1 );	
//new change
	//$search_category_pulldown=$ilance->categories_parser->series_dropwdown($ilance->GPC['denomination'],'series_id',0,true);
	
	$search_category_pulldown=$ilance->categories_parser->series_dropwdown_new($ilance->GPC['denomination'],'series',0,true);
	
	
	//sekar listings on categoris
	
	$ilance->GPC['denomination'];
	
	
  //counter for page 
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 $scriptpageprevnext = 'CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'];
				 
				 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
	    
	
	
	 $navcrumb[""] = $denomination_details['denomination_long'];
	
        $scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
		
		//karthik on may27 for sort order
		
		switch($ilance->GPC['action'])
				  {
				  case 'price':
				  
                 
					if($ilance->GPC['sort']!='12' && $ilance->GPC['sort']!='')
						{
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price<img alt="" src="images/default/expand_collapsed.gif"></a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							 ';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none">Price<img alt="desc" src="images/default/expand.gif"></a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
							 ';
						 }  
						 
						break;
						
						 case 'type':
				  
                 
					if($ilance->GPC['sort']!='22' && $ilance->GPC['sort']!='')
						{
						 $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=22&action=type" title="Sort by Type" style="text-decoration:none">Type<img alt="desc" src="images/default/expand_collapsed.gif"></a></td>
							';
						 }
						 
						 else
						 {
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=11&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td> 
						  <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=21&action=type" title="Sort by Type" style="text-decoration:none">Type<img alt="desc" src="images/default/expand.gif"></a></td>
							 ';
						 }  
						 
						break;
						
					
				default:		
						  $listing =  '<td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=price" title="Sort by Price" style="text-decoration:none">Price</a></td>								
                                           <td><a href="CoinPrices/'.$ilance->GPC['denomination'].'/'.construct_seo_url_name($text).'?denomination='.$ilance->GPC['denomination'].'&q='. $ilance->GPC['q'] .'&sort=12&action=type" title="Sort by Type" style="text-decoration:none">Type</a></td>
											 ';
											
					}
		
		if ($ilance->GPC['sort']=='21') 
{
$orderby ="ORDER BY c.bids ASC";
}
else if ($ilance->GPC['sort']=='22') 
{
$orderby ="ORDER BY c.bids DESC";
}
else if ($ilance->GPC['sort']=='01') 
{
$orderby ="ORDER BY c.date_starts ASC";
}
else if ($ilance->GPC['sort']=='02') 
{
$orderby ="ORDER BY c.date_starts DESC";
}
else if ($ilance->GPC['sort']=='11') 
{
$orderby ="ORDER BY c.currentprice ASC";
}
else if ($ilance->GPC['sort']=='12') 
{
$orderby ="ORDER BY c.currentprice DESC";
}
else
{
$orderby ="ORDER BY c.haswinner desc,c.date_end desc";
}
		
		
	
				   
				    $select_featurednew= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.description
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
			
					" . DB_PREFIX . "projects c				
					
					where 
				   c.status = 'expired'
				   AND c.visible = '1'
				   AND cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid=cc.PCGS
				   AND c.cid !='0'
                   group by c.project_id
				   $orderby LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                   ");
				   
				   
				    $select_featurednew12= $ilance->db->query("select c.date_end,c.project_title,c.currentprice,c.project_id,c.buynow,c.description
				   from 
					" . DB_PREFIX . "catalog_coin cc, 
			
					" . DB_PREFIX . "projects c				
					
					where 
				   c.status = 'expired'
				   AND c.visible = '1'
				   AND cc.coin_series_denomination_no='".$ilance->GPC['denomination']."'
				   AND c.cid=cc.PCGS
				   AND c.cid !='0'
                   group by c.project_id
				   $orderby ");
				   
				//karthik end on may27
				   
				   $total_num1=$ilance->db->num_rows($select_featurednew12);
				   if($total_num1)
				   {
				   $total_num=true;
				   }
				      if($ilance->db->num_rows( $select_featurednew12) > 0)
				{
				     $number = (int)$ilance->db->num_rows( $select_featurednew12);
				   
			  
	              while($det=$ilance->db->fetch_array($select_featurednew))
				   {
				             $projectid=$det['project_id'];
					//karthik may03
					  if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
								  
                                    $pbit = $ilance->bid_proxy->fetch_user_proxy_bid($det['project_id'], $_SESSION['ilancedata']['user']['userid']);
								  	
									if ($pbit > 0)
                                    {
																		
											 $highbidderidtest = $ilance->bid->fetch_highest_bidder($det['project_id']);
																										// murugan on feb 25
										if($highbidderidtest == $_SESSION['ilancedata']['user']['userid'])
										$listpageg['proxybit'] = '<div class="smaller green" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>';
										else
										$listpageg['proxybit'] ='<div class="smaller red" style="padding-top:4px">' . $phrase['_your_maximum_bid'] . ': ' . $ilance->currency->format($pbit, $det['currencyid']) . ' <em> ' . $phrase['_invisible'] . '</em></div>
';                                          $show['proxy']=true;
																		
									}
									//karthik on jun16
									else
									{
									  $listpageg['proxybit'] ='';
									}
                                                                                      
                                                                                }
																				//karthik end may03
					
                               if ($ilconfig['globalauctionsettings_seourls'])	
								$listpageg['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $listpageg['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
					            $listpage['description']=$det['description'];
					            $listpageg['date_end']='<strong>' . date("F d,Y",strtotime($det['date_end'])) . '</strong>';
								$buynow=$det['buynow'];
								
								// Murugan changes on mar 22
								$selectpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "projects WHERE project_id = '".$projectid."'");
								$respjt = $ilance->db->fetch_array($selectpjt);
								  if($respjt['haswinner']=='1')
										{
										  $sol='Sold';
										 }
										 else
										 {
										  $sol='Unsold';
										 }  
							if ($respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'fixed' OR $respjt['buynow_price'] > 0 AND $respjt['filtered_auctiontype'] == 'regular')
                                 {
								 	if ($respjt['filtered_auctiontype'] == 'regular')
                                    {
									  
										if ($respjt['bids'] > 0)
										{
											$listpageg['currentprice']='<strong>' .$sol.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										}
										else
										{
											$listpageg['currentprice'] ='<strong>' .$sol.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
											$listpageg['currentprice'] .='' .'<br>Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '';
										}
										 $listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
										 
									}
									else if ($respjt['filtered_auctiontype'] == 'fixed')
                                     {
									 	$listpageg['currentprice'] ='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($respjt['buynow_price']). '</strong>';
										$listpageg['bids']='<span class="blue">Buy<br>Now</span>';
									 }
									
								 }
								  else
									 {
									 	$listpageg['currentprice']='<strong>' .$sol.'<br>'.$ilance->currency->format($respjt['currentprice']). '</strong>';
										$listpageg['bids'] = ($respjt['bids'] > 0)
											? '<span class="blue">' . $respjt['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
											: '<span class="blue"> Bid </span>';
									 }
								
								
								/*$cat=$ilance->db->query("select bids from " . DB_PREFIX . "projects where buynow='0' AND project_id= '".$projectid."'");
								 if($ilance->db->num_rows($cat) > 0)
									{
										$listpageg['currentprice']='<strong>' .'Bid'.'<br>'.$ilance->currency->format($det['currentprice']). '</strong>';
									}
									else
									{
										$listpageg['currentprice']='<strong>' .'Buy Now'.'<br>'.$ilance->currency->format($det['currentprice'],$currencyid). '</strong>';
									}
					            //$listpageg['currentprice']='<strong>' .$ilance->currency->format($det['currentprice'], $currencyid). '</strong>';
							    
								
		       $type=$ilance->db->query("select bids from " . DB_PREFIX . "projects where $buynow='0' AND project_id= '".$projectid."'");
		       $fet=$ilance->db->fetch_array($type);
			   if($ilance->db->num_rows($type)>0)
			   {
			   $listpageg['bids']='<span class="blue">'.$fet['bids'].' '.'Bids</span>';
			   }
			   else
			   {
			   $listpageg['bids']='<span class="blue">Buy<br>Now</span>';
			   }*/
		                        		
								
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
						$uselistra = HTTPS_SERVER.'image.php?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$listpageg['imgval'] ='<div class="gallery-thumbs-cell">
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
						$listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $listpageg['imgval'] ='<a href="'.HTTPS_SERVER.'merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
						
					   //$listpageg['yy']=$htm; 
					     
					   $listpage[]=	$listpageg;	
			   
				
			   
             }
                   }
				   //karthik changes on apr 29
				   $ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
						$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'product');
				   	$prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);	
	$pprint_array = array('prof','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','sortpulldown','listing');
       
	$ilance->template->fetch('main', 'merch_coin_realized_listing.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', 'listpage');
	 //sekar listings on categoris finished 
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// #### COINS CATEGORIES LISTINGS #################################################
else if(isset($ilance->GPC['series']) AND $ilance->GPC['series'] >0 AND !isset($ilance->GPC['cid']))
{
echo '1';
$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
$seolistings = print_seo_url($ilconfig['listingsidentifier']);
$seocategories = print_seo_url($ilconfig['categoryidentifier']);
//list series for selected denominations
	$show['widescreen'] = true;
	$area_title = $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$page_title = SITE_NAME . ' - ' . $phrase['_buy'] . ' - ' . $phrase['_viewing_all_categories'];
	$topnavlink = array('main_categories');
	
	$ilance->categories_parser=construct_object('api.categories_parser');
	$series_details=$ilance->categories_parser->fetch_coin_series(0,$ilance->GPC['series']);
	$text=$series_details['coin_series_name'];
	$categoryresults=$ilance->categories_parser->html_coin_class($ilance->GPC['series']);
	$search_category_pulldown=$ilance->categories_parser->coin_class_dropwdown($ilance->GPC['series'],0,'cid',0,true);
	$pprint_array = array('seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categoryresults','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'merch_coin_listings.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// #### PRODUCT AUCTION CATEGORY LISTINGS VIA CATEGORY ID ######################
else if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND empty($ilance->GPC['cmd']))
{
        // update category view count
        add_category_viewcount(intval($ilance->GPC['cid']));
        
        $ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
        
        if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', intval($ilance->GPC['cid'])))
        {
                $urlbit = print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = false);
                header('Location: ' . $ilpage['search'] . '?mode=product' . $urlbit);
                exit();
        }
        
        $urlbit = print_hidden_fields($string = true, $excluded = array('cid'), $questionmarkfirst = false);
        header('Location: ' . $ilpage['denomination'] . '?cmd=listings&cid=' . intval($ilance->GPC['cid']) . $urlbit);
        exit();
}
 
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
