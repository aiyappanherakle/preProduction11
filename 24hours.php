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
        'buying',
        'selling',
        'rfp',
        'search',
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
        'inline_edit',
	'jquery',
        'modal',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'dailydeal');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');

// #### setup default breadcrumb ###############################################
$area_title = $phrase['_dailydeal'];
$page_title = SITE_NAME . ' - ' . 'Rare Coins - 24-Hour Coin Deals';
$navcrumb = array("$ilpage[dailydeal]" => $ilcrumbs["$ilpage[dailydeal]"]);
// #### CREATING OR UPDATING PRODUCT AUCTION ###########################
$columns = '3';
$rows = '3';
$limit = $columns * $rows;
			 
$sql_gcdeal = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects p, 
					                            " . DB_PREFIX . "dailydeal d 
                                  WHERE d.live_date = '".DATETODAY."' 
								    AND p.project_id = d.project_id
				                    AND p.status='open' 
				                  GROUP BY d.project_id
							   ");
$c = 0;
if ($ilance->db->num_rows($sql_gcdeal) > 0)
{
	$rowstotal = $ilance->db->num_rows($sql_gcdeal); 
	while ($res_gcdeals = $ilance->db->fetch_array($sql_gcdeal))
	{
		$sql_atty = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "attachment
                                        WHERE visible='1'
                                         AND project_id = '".$res_gcdeals['project_id']."'
                                         AND attachtype='itemphoto'
                                     ");
        $fetch_new=$ilance->db->fetch_array($sql_atty);
		
		//SEO URL
		if($ilconfig['globalauctionsettings_seourls'])
		  $url = '<a href="'.HTTP_SERVER.'Coin/'.$res_gcdeals['project_id'].'/'.construct_seo_url_name($res_gcdeals['project_title']).'">';
		else
          $url = '<a href="'. $ilpage['merch'] .'?id='.$res_gcdeals['project_id'].'">';		
                               
		if($ilance->db->num_rows($sql_atty) == 1)
	    {
			$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $fetch_new['filehash'] .'&w=170&h=105';
			$htm =$url.'<img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
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
                                        
	                                if (($c+1)%4 == 0)
	                                {
	                                        $res_gc_deal['separator_end'] = '</tr><tr>';
	                                }
	                                else 
	                                {
	                                        $res_gc_deal['separator_end'] = '';
	                                }
									
									  
					
					$res_gc_deal['thumbnail'] = $url. $htm . '</a>';
					$res_gc_deal['item_title'] = $url.$res_gcdeals['project_title'].'</a>';
					$res_gc_deal['coin_amount'] = fetch_coin('Buy_it_now',$res_gcdeals['cid'],$res_gcdeals['project_id']);
					$res_gc_deal['current_bid'] = $res_gcdeals['buynow_price'];
					$res_gc_deal['project_id'] = $res_gcdeals['project_id'];
					$res_gc_deal['image'] = $url.'<img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_now_but.jpg" /></a>';
					  
					$res_gcdealing[] = $res_gc_deal;
				
					$c++;
					}
				}
				//kannan
				else
				{
		
			 $sql_gcdeal = $ilance->db->query("
                 SELECT *
                FROM " . DB_PREFIX . "projects p,  
					 " . DB_PREFIX . "dailydeal d 
                WHERE  d.live_date BETWEEN '".THREEDAYSAGO."' AND '".DATEYESTERDAY."' 
				AND p.project_id = d.project_id
				AND     p.status='open' 
				group by d.project_id               
                ");
				//d.live_date = '".THREEDAYSAGO."' 
				$c = 0;
				 
				if ($ilance->db->num_rows($sql_gcdeal) > 0)
				{
				$rowstotal = $ilance->db->num_rows($sql_gcdeal); 
				while ($res_gcdeals = $ilance->db->fetch_array($sql_gcdeal))
					{
					$sql_atty = $ilance->db->query("
                       SELECT * FROM
                       " . DB_PREFIX . "attachment
                       WHERE visible='1'
                                               AND project_id = '".$res_gcdeals['project_id']."'
                                               AND attachtype='itemphoto'
                                               
                       ");
                $fetch_new=$ilance->db->fetch_array($sql_atty);
                               
					   if($ilance->db->num_rows($sql_atty) == 1)
					   {
							   $uselistr = HTTPS_SERVER .  'image.php?cmd=thumb&subcmd=itemphoto&id=' . $fetch_new['filehash'] .'&w=170&h=105';
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
                                     echo  $c ;   
	                                if (($c+1)%4 == 0)
	                                {
	                                        $res_gc_deal['separator_end'] = '</tr><tr>';
	                                }
	                                else 
	                                {
	                                        $res_gc_deal['separator_end'] = '';
	                                }
									
									  
					
					$res_gc_deal['thumbnail'] = $htm ;
					$res_gc_deal['item_title'] = $res_gcdeals['project_title'];
					$res_gc_deal['coin_amount'] = fetch_coin('Buy_it_now',$res_gcdeals['cid'],$res_gcdeals['project_id']);
					$res_gc_deal['current_bid'] = $res_gcdeals['buynow_price'];
					$res_gc_deal['project_id'] = $res_gcdeals['project_id'];
					$res_gc_deal['image'] = '<img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'sold_butt.jpg" />';
					$res_gcdealing[] = $res_gc_deal;
				
					$c++;
					}
				}
				else
				{
				  	$res_gc_daily_deal['deal'] = 'no';
				}
			}
			
			
				//kannan
 		$pprint_array = array('php_self','producttabs','rfpvisible','countdelisted','prevnext','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', '24hours.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_sell','res_gc_daily_deal','res_gcdealing','res_gcsolding','res_gcdeal'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();