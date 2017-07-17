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
$navcrumb = array("$ilpage[denomination]" => $ilcrumbs["$ilpage[denomination]"]);
// #### decrypt our encrypted url ##############################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
// #### HANDLE SELLER TOOLS FROM LISTING PAGE ##################################

$page_title = SITE_NAME . ' - ' . 'Hall of Fame';
$area_title = 'Record Coin Prices and Information at GreatCollections Coin Auctions';
$metadescription='View our recent record coin auction prices and more information about rare coins at GreatCollections. Free coin appraisals and more.';

$navcrumb[""] = 'Hall of Fame';
 $ilance->auction = construct_object('api.auction');
 
 
 	   
				  $select_featurednew= $ilance->db->query("select date_end,project_title,currentprice,project_id,buynow,highlite,bold,description,filtered_auctiontype,buyer_fee
				   from 										
					" . DB_PREFIX . "projects 				
					where featured = '1'
				   AND (status = 'expired' or status = 'closed')
				   AND (haswinner = 1 OR hasbuynowwinner = 1)
                   group by project_id
				   order by currentprice desc
	                limit 5
				     ");
					 
					 
		 if($ilance->db->num_rows( $select_featurednew) > 0)
		 {
		        while($det=$ilance->db->fetch_array($select_featurednew))
				 {
				 
				                               if ($ilconfig['globalauctionsettings_seourls'])	
								$fame_list['project_title'] ='<span class="blue"><a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $fame_list['project_title']='<span class="blue"><a href="merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
				 
				    //$fame_list['project_title']=$det['project_title'];
					$fame_list['currentprice']=$ilance->currency->format($det['currentprice']+$det['buyer_fee']);
					
					if($det['filtered_auctiontype'] == 'regular')
					{
					$fame_list['hammer']='<font color="#999999">('.$ilance->currency->format($det['currentprice']).'&nbsp;hammer)</font>';
					}
					else
					{
					$fame_list['hammer']= '';
					}
					
					$fame_list['date_end']='<strong>' . date("F d, Y",strtotime($det['date_end'])) . '</strong>';
					$fame_list['description']=$det['description'];
					/* if ($det['filtered_auctiontype'] == 'fixed')
                      {
					     
					     $fame_list['bids']='<span class="blue">Buy<br>Now</span>';
					  }
					  else
					  {
					   $fame_list['bids']='<span class="blue">Bid</span>'; 
					  }*/
					
					$sql_attya = $ilance->db->query("SELECT * FROM
														" . DB_PREFIX . "attachment
														WHERE visible='1' 
														AND project_id = '".$det['project_id']."'
														AND attachtype='itemphoto' 
						
                        ");
				    $fetch_newa=$ilance->db->fetch_array($sql_attya);
					if($ilance->db->num_rows($sql_attya) > 0)
					{
					 $count_attachtype= $ilance->db->query("
                        SELECT  count(attachtype) as imag FROM
                        " . DB_PREFIX . "attachment
                        WHERE visible='1' 
						AND project_id = '".$det['project_id']."'
						AND (attachtype = 'itemphoto' OR attachtype = 'slideshow') 
						
                        ");
						
						$display_count=$ilance->db->fetch_array($count_attachtype);
					
						$uselistra = $ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $fetch_newa['filehash']; 
						if ($ilconfig['globalauctionsettings_seourls'])
						$fame_list['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper">
						<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"> <img  class="gallery-thumbs-image-cluster" style="border-color: rgb(255, 255, 255);"  src="'.$uselistra.'" ></a> 
				<div class="gallery-thumbs-corner-text" style = "margin-left: 5px"><span>' . ($display_count['imag']) . ' photos</span></div>
							  </div>
          </div>
        </div>
      </div>
    </div>
  </div>';
						else
						$fame_list['imgval'] ='<div class="gallery-thumbs-cell">
    <div class="gallery-thumbs-entry">
      <div class="gallery-thumbs-main-entry">
        <div class="gallery-thumbs-wide-wrapper">
          <div class="gallery-thumbs-wide-inner-wrapper"><a href="merch.php?id='.$det['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;"></a> 
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
						$fame_list['imgval'] ='<a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
						else
					    $fame_list['imgval']='<a href="merch.php?id='.$det['project_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 6px;"></a>';
					}
					
			
			
			$famelist[]=$fame_list;
			
				 
			}
		 
		}
		
		
		
 			  $select_featurednew= $ilance->db->query("select date_end,project_title,currentprice,project_id,buynow,highlite,bold,description,filtered_auctiontype,buyer_fee
				   from 
										
					" . DB_PREFIX . "projects 				
					where 
				    (status = 'expired' or status = 'closed')
					AND (haswinner = 1 OR hasbuynowwinner = 1)
                   group by project_id
				   order by currentprice desc
	                limit 20
				     ");
					 
					 
		 if($ilance->db->num_rows( $select_featurednew) > 0)
		 {
		        while($det=$ilance->db->fetch_array($select_featurednew))
				 {
				 
				                               if ($ilconfig['globalauctionsettings_seourls'])	
								$fame_list['project_title'] ='<span class="blue"><a href="Coin/'.$det['project_id'].'/'.construct_seo_url_name($det['project_title']).'">'.$det['project_title'].'</a></span>';
								else
					   			 $fame_list['project_title']='<span class="blue"><a href="merch.php?id='.$det['project_id'].'" style="color:blue;">'.$det['project_title'].'</a></span>';
				 
				    //$fame_list['project_title']=$det['project_title'];
					
					$fame_list['currentprice']=$ilance->currency->format($det['currentprice']+$det['buyer_fee']);
					if($det['filtered_auctiontype'] == 'regular')
					{
					$fame_list['hammer']='<font color="#999999">('.$ilance->currency->format($det['currentprice']).'&nbsp;hammer)</font>';
					}
					else
					{
					$fame_list['hammer']= '';
					}
					
					$fame_list['date_end']='<strong>' . date("F d, Y",strtotime($det['date_end'])) . '</strong>';
					$fame_list['description']=$det['description'];
				
					
			
			
			$famelist_sec[]=$fame_list;
			
				 
			}
		 
		}
  $pprint_array = array('page_title','navcrumb','login_include');
       
   $ilance->template->fetch('main', 'fame.html'); 
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main','famelist');
	 $ilance->template->parse_loop('main','famelist_sec');
	 //sekar listings on categoris finished 
        $ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();