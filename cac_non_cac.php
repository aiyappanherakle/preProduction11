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
        'search',
        'stores',
		'accounting',
        'wantads',
        'subscription',
        'preferences',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'search',
	'tabfx',
	'jquery',
	'modal',
	'yahoo-jar',
	'flashfix'
);

// #### define top header nav ##################
$topnavlink = array(
        'main_listings'
);

// #### setup script location ##################################################
define('LOCATION', 'search');

// #### require backend ########################################################
require_once('./functions/config.php');
//error_reporting(E_ALL);

// #### setup default breadcrumb ###############################################
$navcrumb = array("cac_non_cac.php" => $phrase['_cac_and_non_cac_bread_crumb']);
$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';

$page_title = $phrase['_cac_non_cac_page_title'];

$coc_non_cac = '';

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;
$count=0;
	$query="SELECT p.project_id as cac_id, q.project_id as noncac_id, p.project_title as cac_title, q.project_title as noncac_title, p.date_end as cac_enddate, q.date_end as noncac_enddate, p.currentprice as cac_price, q.currentprice as noncac_price,p.buyer_fee as cac_buyfee,q.buyer_fee as noncac_buyfee, pa.filehash AS cac_image, qa.filehash AS noncac_image
		FROM " . DB_PREFIX . "projects p
		LEFT JOIN " . DB_PREFIX . "projects q ON p.pcgs = q.pcgs AND p.grade = q.grade AND p.grade = q.grade 
		AND p.grading_service = q.grading_service AND p.project_id != q.project_id
		AND ABS( DATEDIFF( p.date_end, q.date_end ) ) <=90
        Left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
        Left join " . DB_PREFIX . "coins cn on cn.coin_id=q.project_id AND c.nocoin=cn.nocoin
        LEFT JOIN " . DB_PREFIX . "attachment pa ON pa.project_id = p.project_id
		AND pa.attachtype =  'itemphoto'
		LEFT JOIN " . DB_PREFIX . "attachment qa ON qa.project_id = q.project_id AND qa.attachtype =  'itemphoto'
		WHERE p.pcgs !=  ''
		AND p.grade >0
		AND YEAR(p.date_end)>2014 AND YEAR(q.date_end)>2014
		AND pa.attachtype !=  ''
		AND qa.attachtype !=  ''
		AND p.grading_service !=  ''
		AND p.cac =1
		AND q.cac =0
        AND c.plus=cn.plus AND c.star=cn.star
        AND p.haswinner=1 AND q.haswinner=1
		GROUP BY p.pcgs,p.coin_series_denomination_no
        order by rand()
		LIMIT 20";

	$query="SELECT p.project_id as cac_id, p.project_title as cac_title, p.date_end as cac_enddate, p.currentprice as cac_price, p.buyer_fee as cac_buyfee, pa.filehash AS cac_image, p.pcgs, p.grade, p.grading_service, c.plus, c.star, c.nocoin
		FROM " . DB_PREFIX . "projects p
        Left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
        LEFT JOIN " . DB_PREFIX . "attachment pa ON pa.project_id = p.project_id
		AND pa.attachtype =  'itemphoto'
		WHERE p.pcgs !=  ''
		AND p.grade >0
		AND YEAR(p.date_end)>2014 
		AND pa.attachtype !=  ''
		AND p.grading_service !=  ''
		AND p.cac =1
        AND p.haswinner=1 
        
		GROUP BY p.pcgs,p.coin_series_denomination_no
        order by rand() "; //AND p.project_id = 27708
		//echo $query;exit;
		$sql = $ilance->db->query($query, 0, null, __FILE__, __LINE__);
		 
		//echo $ilance->db->num_rows($sql);exit;
		$i = 0;
		$cac_noncac_list = array();
		
		if ($ilance->db->num_rows($sql) > 0)
        {
			
			while($ressq = $ilance->db->fetch_array($sql))
			{
				if($i==20) { break; }
				
				//echo '<pre>';print_r($ressq);exit;
				$query1="SELECT q.project_id as noncac_id, q.project_title as noncac_title, q.date_end as noncac_enddate, q.currentprice as noncac_price, q.buyer_fee as noncac_buyfee, qa.filehash AS noncac_image
					FROM " . DB_PREFIX . "projects q
			        Left join " . DB_PREFIX . "coins cn on cn.coin_id=q.project_id AND cn.nocoin='".$ressq['nocoin']."'
					LEFT JOIN " . DB_PREFIX . "attachment qa ON qa.project_id = q.project_id AND qa.attachtype =  'itemphoto'
					WHERE q.pcgs =  '".$ressq['pcgs']."'
					AND q.grade = '".$ressq['grade']."'
					AND YEAR(q.date_end)>2014
					AND qa.attachtype !=  ''
					AND q.grading_service =  '".$ressq['grading_service']."'
					AND q.cac =0
			        AND cn.plus='".$ressq['plus']."' 
			        AND cn.star='".$ressq['star']."'
			        AND q.haswinner=1
			        AND q.project_id != '".$ressq['cac_id']."'
			        AND ABS( DATEDIFF( '".$ressq['cac_enddate']."', q.date_end ) ) <=90
					GROUP BY q.pcgs,q.coin_series_denomination_no
			        order by rand()
					LIMIT 1"; 
				
				$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);

				if ($ilance->db->num_rows($sql1) > 0)
		        {
					while($ressqq = $ilance->db->fetch_array($sql1))
					{
						$i++;
						//echo '<pre>';print_r($ressqq);





						$cac_noncac_list[$count]['cac_id'] = $ressq['cac_id'];
						$cac_noncac_list[$count]['noncac_id'] = $ressqq['noncac_id'];
						//cac title
						if ($ilconfig['globalauctionsettings_seourls'])	
						$cac_noncac_list[$count]['cac_title'] ='<span class="blue"><a href="Coin/'.$ressq['cac_id'].'/'.construct_seo_url_name($ressq['cac_title']).'">'.handle_input_keywords($ressq['cac_title']).'</a></span>';
						else
						$cac_noncac_list[$count]['cac_title']='<span class="blue"><a href="merch.php?id='.$ressq['cac_id'].'" style="color:blue;">'.handle_input_keywords($ressq['cac_title']).'</a></span>';
						//non cac title
						if ($ilconfig['globalauctionsettings_seourls'])	
						$cac_noncac_list[$count]['noncac_title'] ='<span class="blue"><a href="Coin/'.$ressqq['noncac_id'].'/'.construct_seo_url_name($ressqq['noncac_title']).'">'.handle_input_keywords($ressqq['noncac_title']).'</a></span>';
						else
						$cac_noncac_list[$count]['noncac_title']='<span class="blue"><a href="merch.php?id='.$ressqq['noncac_id'].'" style="color:blue;">'.handle_input_keywords($ressqq['noncac_title']).'</a></span>';
						
						//$cac_noncac_list[$count]['cac_title'] = handle_input_keywords($ressq['cac_title']);
						//$cac_noncac_list[$count]['noncac_title'] = handle_input_keywords($ressq['noncac_title']);
						$cac_noncac_list[$count]['cac_enddate'] = date("F d, Y",strtotime($ressq['cac_enddate']));
						$cac_noncac_list[$count]['noncac_enddate'] = date("F d, Y",strtotime($ressqq['noncac_enddate']));
		                                 $cac_price=$ressq['cac_price']+$ressq['cac_buyfee'];
						$cac_noncac_list[$count]['cac_price'] = $ilance->currency->format($cac_price);
		                                $non_cac_price=$ressqq['noncac_price']+$ressqq['noncac_buyfee'];
						$cac_noncac_list[$count]['noncac_price'] = $ilance->currency->format($non_cac_price);
						//echo $cac_noncac_list[$count]['cac_price'].'>'.$cac_noncac_list[$count]['noncac_price'].'<br>';
						 $greenchkcac = 'images/gc/Greenchkcac.png';
		                                 if ($cac_price > $non_cac_price )
						 {
						 
						 $cac_noncac_list[$count]['cac_image_chk']='<div style="position:absolute;margin-top:-70px;margin-left:150px;"><img src="'.$greenchkcac.'"></div>';
		                                 $cac_noncac_list[$count]['non_cac_image_chk']='';
						 }
						else 
						{
						 $cac_noncac_list[$count]['cac_image_chk']='';
		                                 $cac_noncac_list[$count]['non_cac_image_chk']='<div style="position:absolute;margin-top:-70px;margin-left:150px;"><img src="'.$greenchkcac.'"></div>';
						}
						
						//cac image
						if(!is_null($ressq['cac_image']) and strlen($ressq['cac_image'])>0)
							{
								$uselistra = $ilpage['image'] . '?cmd=thumb&subcmd=itemphoto&id=' . $ressq['cac_image'] .'&w=163&h=201'; 
								if ($ilconfig['globalauctionsettings_seourls'])
								$cac_noncac_list[$count]['cac_image'] ='<a href="Coin/'.$ressq['cac_id'].'/'.construct_seo_url_name($ressq['cac_title']).'"> <img src="'.$uselistra.'" style="padding-top: 3px;" alt="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
								else
								$cac_noncac_list[$count]['cac_image'] ='<a href="merch.php?id='.$ressq['cac_id'].'"><img src="'.$uselistra.'" style="padding-top: 3px;" alt="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
								
								
							}else
							{
							    $uselistra =  $ilconfig['template_imagesfolder'] . 'nophoto.gif';
								if ($ilconfig['globalauctionsettings_seourls'])
								$cac_noncac_list[$count]['cac_image'] ='<a href="Coin/'.$ressq['cac_id'].'/'.construct_seo_url_name($ressq['cac_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 3px; width: 163px; height: 201px;" alt="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
								else
							   $cac_noncac_list[$count]['cac_image'] ='<a href="merch.php?id='.$ressq['cac_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 3px; width: 163px; height: 201px;" alt="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressq['cac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
							}
				
						//non cac image
						
						if(!is_null($ressqq['noncac_image']) and strlen($ressqq['noncac_image'])>0)
							{
								$uselistra = $ilpage['image'] . '?cmd=thumb&subcmd=itemphoto&id=' . $ressqq['noncac_image'] .'&w=163&h=201'; 
								if ($ilconfig['globalauctionsettings_seourls'])
								$cac_noncac_list[$count]['noncac_image'] ='<a href="Coin/'.$ressqq['noncac_id'].'/'.construct_seo_url_name($ressqq['noncac_title']).'"> <img src="'.$uselistra.'" style="padding-top: 3px;" alt="'.$ressqq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressqq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
								else
								$cac_noncac_list[$count]['noncac_image'] ='<a href="merch.php?id='.$ressqq['noncac_id'].'"><img src="'.$uselistra.'" style="padding-top: 3px;" alt="'.$ressqq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressqq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
								
								
							}else
							{
							    $uselistra =  $ilconfig['template_imagesfolder'] . 'nophoto.gif';
								if ($ilconfig['globalauctionsettings_seourls'])
								$cac_noncac_list[$count]['noncac_image'] ='<a href="Coin/'.$ressqq['noncac_id'].'/'.construct_seo_url_name($ressqq['noncac_title']).'"><img src="images/gc/nophoto.gif" style="padding-top: 3px; width: 163px; height: 201px;" alt="'.$ressqq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressqq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
								else
							   $cac_noncac_list[$count]['noncac_image'] ='<a href="merch.php?id='.$ressq['noncac_id'].'"><img src="images/gc/nophoto.gif" style="padding-top: 3px; width: 163px; height: 201px;" alt="'.$ressq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'" title="'.$ressq['noncac_title'].' '.$phrase['_at_gc_image_tag'].'"></a>';
							}
						
						
						$count++;





						
						
						//exit;
					}
				}	
				//echo $query1;exit;
				
			}
			
				
			
		}
//echo $i.'<br/>';
//echo 'exit ';//exit;

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'cac_non_cac.html');
$ilance->template->parse_if_blocks('main');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('cac_noncac_list'));

$pprint_array = array('colsperrow','region_pulldown_product','locatedin_pulldown_product','availableto_pulldown_product','search_bidrange_pulldown_product','search_radius_country_pulldown_product','search_country_pulldown_product','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_displayfeatured','cb_hidelisted','cb_proxybit','cb_hideverbose','productavailable','productselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','product_category_selection','search_productauctions_img','search_productauctions_collapse','pfp_category_left','rfp_category_left','input_style','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');

($apihook = $ilance->api('search_start_template')) ? eval($apihook) : false;
 
$ilance->template->pprint('main', $pprint_array);
exit();


		
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>