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
        'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'jquery',
	'functions','modal','ajax'
);

// #### setup script location ##################################################
define('LOCATION', 'selling');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once DIR_CORE . 'functions_search.php';

// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');
 $show['widescreen'] = true;
// #### setup default breadcrumb ###############################################
$area_title = $phrase['_access_denied'];
$page_title = SITE_NAME . ' - ' . ucfirst($ilance->GPC['cmd']);
$navcrumb = array("$ilpage[selling]" => $ilcrumbs["$ilpage[selling]"]);

if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode('sell.php' . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

if (!empty($ilance->GPC['crypted']))
{
	$uncrypted = decrypt_url($ilance->GPC['crypted']);
}

($apihook = $ilance->api('selling_top')) ? eval($apihook) : false;
	
// #### CREATING OR UPDATING PRODUCT AUCTION ###########################
 if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
 {
///currently selling
 	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);		
	$sql_limit = 'LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

	$date_filter_values['0'] = '2011-01-01';
	$date_filter_values['1'] = DATEYESTERDAY;
	$date_filter_values['2'] = SEVENDAYSAGO;
	$date_filter_values['3'] = THIRTYDAYSAGO;
	$date_filter_values['4'] = SIXTYDAYSAGO;
	$date_filter_values['5'] = NINETYDAYSAGO;
	$date_filter_values['6'] = ONEEIGHTYDAYSAGO;
	$date_filter_values['7'] = THREESIXTYDAYSAGO;
	$date_filter_values['8'] = SEVENTWENTYDAYSAGO;
	$date_filter_values['9'] = THOUSANDEIGHTYDAYSAGO;

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'current')
	{
		$table_header_images['ASC']="images/default/expand_collapsed.gif";
		$table_header_images['DESC']="images/default/expand.gif";

		

	$area_title = 'Current Selling';
	$scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	$ilance->GPC['action']=isset($ilance->GPC['action'])?$ilance->GPC['action']:0;
	$ilance->GPC['q']=isset($ilance->GPC['q'])?$ilance->GPC['q']:'';
	$ilance->GPC['sort']=isset($ilance->GPC['sort'])?$ilance->GPC['sort']:'61';	

	$order_list['11']="ORDER BY p.project_id ASC";
	$order_list['12']="ORDER BY p.project_id DESC";
	$order_list['21']="ORDER BY p.project_title ASC";
	$order_list['22']="ORDER BY p.project_title DESC";
	$order_list['31']="ORDER BY p.bids ASC";
	$order_list['32']="ORDER BY p.bids DESC";
	$order_list['41']="ORDER BY price ASC";
	$order_list['42']="ORDER BY price DESC";
	$order_list['51']="ORDER BY p.currentprice ASC";
	$order_list['52']="ORDER BY p.currentprice DESC";
	$order_list['61']="ORDER BY p.date_end ASC";
	$order_list['62']="ORDER BY p.date_end DESC";
	
	$columns[0]=array("column"=>"project_id","ASC"=>"11","DESC"=>"12","name"=>"Item id","url"=>"action=itemid","title"=>"Sort by itemid");
	$columns[1]=array("column"=>"project_title","ASC"=>"21","DESC"=>"22","name"=>"Item Title","url"=>"action=itemtitle","title"=>"Sort by itemtitle");
	$columns[2]=array("column"=>"bids","ASC"=>"31","DESC"=>"32","name"=>"Bids","url"=>"action=bids","title"=>"Sort by bids");
	$columns[3]=array("column"=>"price","ASC"=>"41","DESC"=>"42","name"=>"Min Bid/Buynow","url"=>"action=minbid/buynow","title"=>"Sort by minbid/buynow");
	$columns[4]=array("column"=>"currentprice","ASC"=>"51","DESC"=>"52","name"=>"Current Bids","url"=>"action=currentbids","title"=>"Sort by Current Bids");
	$columns[5]=array("column"=>"date_end","ASC"=>"61","DESC"=>"62","name"=>"Time left","url"=>"action=timeleft","title"=>"Sort by Time Left");
	$listing1='';
	$listing2='';
	foreach($columns as $key=>$column)
	{
		$current_order="ASC";
		$opposite_order="DESC";
		if(strstr($order_list[$ilance->GPC['sort']],$column['column']))
		{
			$current_order=strstr($order_list[$ilance->GPC['sort']], 'DESC')?"DESC":"ASC";
			$opposite_order=strstr($order_list[$ilance->GPC['sort']], 'DESC')?"ASC":"DESC";
		}
		$opposite_order=$column[$opposite_order];
		if($key!=5)
		{
			$listing1.='<td><a href="'.$ilpage['sell'] . '?cmd=current&sort='.$opposite_order.'&'.$column['url'].'" title="'.$column['title'].'" style="text-decoration:underline">'.$column['name'].'<img alt="" src="'.$table_header_images[$current_order].'"></a></td> ';
		}else
		{
			$listing2.='<td><a href="'.$ilpage['sell'] . '?cmd=current&sort='.$opposite_order.'&'.$column['url'].'" title="'.$column['title'].'" style="text-decoration:underline">'.$column['name'].'<img alt="" src="'.$table_header_images[$current_order].'"></a></td> ';
		}
		
	}

	//bug1782 tamil for sort ends
	$SQL="  SELECT p.*,GREATEST(p.buynow_price,p.startprice) as price,a.filehash,
			UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, 
			UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime,
			count(w.watching_project_id) as watchlist_count
			FROM " . DB_PREFIX . "projects p
			left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto' and a.visible='1'
			left join " . DB_PREFIX . "watchlist w on p.project_id = w.watching_project_id
			WHERE  	p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
			AND     p.visible ='1'
			AND  	p.project_state = 'product'
			AND    	p.status = 'open'
			group by p.project_id ".$order_list[$ilance->GPC['sort']]." ";
    $scriptpage = HTTP_SERVER .'Sell/Current'. print_hidden_fields(true, array('do','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	$sql_gcsell1 = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($sql_gcsell1);	

	$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);	
	
	$sql_gcsell = $ilance->db->query($SQL.$sql_limit, 0, null, __FILE__, __LINE__);           
				
			if ($ilance->db->num_rows($sql_gcsell) > 0)
			{
				while ($res_gcsell = $ilance->db->fetch_array($sql_gcsell))
				{
						if(strlen($res_gcsell['filehash'])>1)
						$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gcsell['filehash'] .'&w=170&h=105';
						else
					    $uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
					   
					
					if ($ilconfig['globalauctionsettings_seourls'])
						{
						$item_path=HTTP_SERVER.'Coin/'.$res_gcsell['project_id'].'/'.construct_seo_url_name($res_gcsell['project_title']).'';
						$htm ='<a href="'.$item_path.'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
						}	                    
						else
					    $htm ='<a href="merch.php?id='.$res_gcsell['project_id'].'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
						
						
					   if($res_gcsell['bids'] > 0)
					   {
					    $mess = '<font color="green">Item Has '.$res_gcsell['bids'].' Bids </font>';
					   }
					   
					   if($res_gcsell['bids'] == 0 AND $res_gcsell['filtered_auctiontype'] == 'regular' AND $res_gcsell['buynow'] == 0)
					   {
					    $mess = 'Listed for<br>Auction';
					   }
					   
					   if($res_gcsell['filtered_auctiontype'] == 'regular' AND $res_gcsell['buynow'] == 1)
					   {
					    $mess = '<font color="blue"> Listed for <br>Auction &<br>Buy Now</font>';
					   }
					   
					   if($res_gcsell['filtered_auctiontype'] == 'fixed' AND $res_gcsell['buynow_price'] > 0 AND $res_gcsell['buynow'] == 1)
					   {
					    $mess = '<font color="blue"> Listed for<br>Buy Now </font>';
					   }
					    $res_gc_sell['timelef']=auction_time_left_new($res_gcsell,false);
						$views_trackers  = $res_gcsell['views'].' / '.($res_gcsell['users_tracked']+$res_gcsell['watchlist_count']);
					   
					  // End here murugan
					   
					$res_gc_sell['item_path']=$item_path;
					$res_gc_sell['thumbnail'] = $htm;
					$res_gc_sell['item_id'] = $res_gcsell['project_id'];
					$res_gc_sell['item_title'] = $res_gcsell['project_title'];
					$res_gc_sell['minbid_min'] = $res_gcsell['startprice'];
					$res_gc_sell['minbid_buynow'] = $res_gcsell['buynow_price'];
					$res_gc_sell['bids'] = $res_gcsell['bids'];
					//$res_gc_sell['timelef'] = $res_gcsell['date_end'];
					$res_gc_sell['status'] = $res_gcsell['status'];
					$res_gc_sell['description'] = $mess;
					$res_gc_sell['current_bid'] = $res_gcsell['currentprice'];
					$res_gc_sell['views_trackers']=$views_trackers;
					$res_gcselling[] = $res_gc_sell;
					}
				}
				else
				{				
				$res_gcselling['mm'] = 'Nofound';
				}
				
				$pprint_array = array('prevnext','listing2','listing1','daylist','monthlist','yearlist','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'sell_current.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
 }
				
				//##########################
				//// items sold
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'sold')
	{
		
		$area_title = 'Sold Items';
		$scriptpage = HTTP_SERVER .'Sell/Sold'. print_hidden_fields(true, array('do','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
		$endDate = DATETODAY;
		$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:2;
		
 		
 		$gcsold1 = " SELECT p.project_id FROM " . DB_PREFIX . "projects p
 				WHERE  	p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
				AND     p.visible ='1'
				AND  	p.project_state = 'product'
				AND    	p.status = 'expired'	
				AND    	p.haswinner = '1'
				AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchsell']]."') 
				ORDER BY p.id ASC  ";  

 		$gcsold = " SELECT p.project_id,p.project_title,p.currentprice,p.startprice,
 				p.bids,p.date_end,i.status,i.duedate,i.paiddate,i.createdate,i.invoiceid,
 				a.filehash,a.filename,b.bidamount FROM " . DB_PREFIX . "projects p
 				left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto'
 				left join " . DB_PREFIX . "project_bids b on b.project_id=p.project_id and b.bidstatus = 'awarded'
 				left join " . DB_PREFIX . "invoices i on i.projectid=p.project_id and i.p2b_user_id='".$_SESSION['ilancedata']['user']['userid']."' and i.isbuyerfee=0
                WHERE  	p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
				AND     p.visible ='1'
				AND  	p.project_state = 'product'
				AND    	p.status = 'expired'	
				AND    	p.haswinner = '1'
				AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchsell']]."') 
				ORDER BY p.id ASC  ";  

   		$sql_gcsold1 = $ilance->db->query($gcsold1, 0, null, __FILE__, __LINE__);
		$number = $ilance->db->num_rows($sql_gcsold1);	


		$sql_gcsold = $ilance->db->query($gcsold.$sql_limit, 0, null, __FILE__, __LINE__);

		
		$drop_value=form_days_drop_down("searchsell",$ilance->GPC['searchsell']);
		

		$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);	
								
		if ($ilance->db->num_rows($sql_gcsold) > 0)
		{
		while ($res_gcsold = $ilance->db->fetch_array($sql_gcsold))
			{
				if($res_gcsold['filehash'] != NULL)
				$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gcsold['filehash'] .'&w=170&h=105';
				else
				$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';

				if ($ilconfig['globalauctionsettings_seourls'])
				{
					$item_path=HTTP_SERVER.'Coin/'.$res_gcsold['project_id'].'/'.construct_seo_url_name($res_gcsold['project_title']).'';
				}else
				{
					$item_path='merch.php?id='.$res_gcsold['project_id'];
					
				}
				$htm ='<a href="'.$item_path.'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>'; 
			if($res_gcsold['invoiceid']>0)
			{
				$explo = explode(' ',$res_gcsold['createdate']);
				$res_gc_sold['invoice'] = '<a href="consignor_statement.php?date='.$explo[0].'">'.$res_gcsold['invoiceid'].'</a>';
				if($res_gcsold['status']=="paid")
				{
					$res_gc_sold['status'] = 'Paid on'.'<br>'.date('m/d/Y',strtotime($res_gcsold['paiddate']));
				}else
				{
					$res_gc_sold['status'] = 'To Be Paid on '.'<br>'.date('m/d/Y',strtotime($res_gcsold['duedate']));
				}
			}else{
				$res_gc_sold['invoice'] = 'Offline Payment';
				$res_gc_sold['status'] = 'Ended';
			}
				

			$res_gc_sold['current_bid'] = (isset($res_gcsold['bidamount']))?$res_gcsold['bidamount']:$res_gcsold['currentprice'];
			$res_gc_sold['item_path']=$item_path;
			$res_gc_sold['thumbnail'] = $htm;
			$res_gc_sold['item_id'] = $res_gcsold['project_id'];
			$res_gc_sold['item_title'] = $res_gcsold['project_title'];
			$res_gc_sold['minbid_buynow'] = $res_gcsold['startprice'];
			$res_gc_sold['bids'] = $res_gcsold['bids'];
			$res_gc_sold['date_end'] = date('m-d-Y',strtotime($res_gcsold['date_end']));
			$res_gc_sold['description'] = 'Item Sold';
			$res_gc_itemsold[] = $res_gc_sold;
			}
		}
		else
		{				
		$res_gc_itemsold['mm'] = 'Nofound';
		}
	$pprint_array = array('prevnext','drop_value','daylist','monthlist','yearlist','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'sell_sold.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
				
}
				//// items sold end
				//work by karthik
				//##########################
				//// buy now sold
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buynowsold')
	{
	$area_title = 'Buynow Item Sold';
	$scriptpage = HTTP_SERVER .'Sell/Buynowsold'. print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);      
	$endDate = DATETODAY;
	$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:2;
		$buynw= " SELECT p.project_id,p.project_title,p.date_end,p.status,a.filehash,i.invoiceid,i.duedate,i.paiddate,i.createdate,i.amount,p.bids
		,o.orderdate,o.qty FROM " . DB_PREFIX . "buynow_orders o
				left join " . DB_PREFIX . "projects p on p.project_id=o.project_id
				left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto'
				left join " . DB_PREFIX . "invoices i on i.projectid=o.project_id and i.p2b_user_id=o.owner_id and o.orderid=i.buynowid
                WHERE  p.project_id>0 and	o.owner_id = '".$_SESSION['ilancedata']['user']['userid']."'   
				AND   (date(o.orderdate) <= '".$endDate."' 
				AND date(o.orderdate) >= '".$date_filter_values[$ilance->GPC['searchsell']]."') 
				order by o.orderid asc ";  
	   		
	   		$sql_buynw1 = $ilance->db->query($buynw, 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($sql_buynw1);	
	
			$sql_buynw = $ilance->db->query($buynw.$sql_limit, 0, null, __FILE__, __LINE__);


			
			$drop_value=form_days_drop_down("searchsell",$ilance->GPC['searchsell']);
			
			$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);		
				if ($ilance->db->num_rows($sql_buynw) > 0)
				{
				while ($res_gcsold = $ilance->db->fetch_array($sql_buynw))
					{

					$uselistr = (strlen($res_gcsold['filehash'])>0)?HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gcsold['filehash'] .'&w=170&h=105':$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
					$url=$ilconfig['globalauctionsettings_seourls']?'Coin/'.$res_gcsold['project_id'].'/'.construct_seo_url_name($res_gcsold['project_title']):'merch.php?id='.$res_gcsold['project_id'];
					$htm ='<a href="'.$url.'"><img  src="'.$uselistr.'" style="padding: 10px; border-width:0px;"></a>';
					  
					if($res_gcsold['invoiceid']> 0)
				   	{
				   		$explo = explode(' ',$res_gcsold['date_end']);
				    	$item_path=HTTP_SERVER.'Coin/'.$res_gcsold['project_id'].'/'.construct_seo_url_name($res_gcsold['project_title']).'';
						$res_gc_sold['invoice'] = '<a href="consignor_statement.php?date='.$explo[0].'">'.$res_gcsold['invoiceid'].'</a>';
						if($res_gcsold['status'] == 'paid')
						{
							$invdate = date('m/d',strtotime($res_gcsold['paiddate']));
							$res_gc_sold['status'] = 'Paid on'.$invdate;
						}
						else
						{
						  $invdate = date('m/d',strtotime($res_gcsold['duedate']));
						  $res_gc_sold['status'] = 'To Be Paid on '.$invdate;
						}
				   	}
				   	else
				   	{
				    	$res_gc_sold['invoice'] = 'Offline Payment';
						$res_gc_sold['status'] = 'Ended';
				   	}
					//$res_gcsold1=$ilance->db->fetch_array($sql_gcsold); 
					if($res_gcsold['status']=='closed'||$res_gcsold['status']=='expired')
					{
					$res_gc_sold['status'] ='Ended';	
					}
					else
					{
					$res_gc_sold['status'] ='Open';	
					}
					$res_gc_sold['item_path'] = $url;
					$res_gc_sold['thumbnail'] = $htm;
					$res_gc_sold['item_id'] = $res_gcsold['project_id'];
					//$res_gc_sold['item_title'] = '<a href="' . $ilpage['selling'] . '?cmd=product-management&amp;state=product&amp;id='.$res_gcsold['project_id'].'">'.$res_gcsold['project_title'] .'</a>';
					$res_gc_sold['item_title'] = $res_gcsold['project_title'];
					$res_gc_sold['minbid_buynow'] = $res_gcsold['amount'];
					$res_gc_sold['bids'] = $res_gcsold['bids'];
					$res_gc_sold['qty'] = $res_gcsold['qty'];
					$res_gc_sold['date_end'] = date('m-d-Y',strtotime($res_gcsold['date_end']));
					$res_gc_sold['orderdate'] = date('m-d-Y',strtotime($res_gcsold['orderdate']));		
					$res_gc_sold['description'] = 'Item Sold';
					//$res_gc_sold['current_bid'] = $resbid['bidamount'];
					$res_gc_itemsold[] = $res_gc_sold;
					
					}
				}
				
				else
				{				
				$res_gc_itemsold['mm'] = 'Nofound';
				}
				$pprint_array = array('prevnext','drop_value','daylist','monthlist','yearlist','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'sell_buynowsold.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
				
}
				//// buy now sold end
				
//buy now unsold

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buynowunsold')
{
		$area_title = 'Buynow Item Unsold';
		$scriptpage = HTTP_SERVER .'Sell/Buynowunsold'. print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false); 
		$endDate = DATETODAY;
		$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:2;
		$drop_value=form_days_drop_down("searchsell",$ilance->GPC['searchsell']);
		
		$buynww="
                SELECT p.project_id,p.project_title,p.buynow_price,p.buynow_qty,a.filehash,p.date_end
                FROM " . DB_PREFIX . "projects p 
                left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id
                WHERE p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
				AND p.visible ='1'
				AND p.project_state = 'product'
				AND p.filtered_auctiontype = 'fixed'
				AND p.status = 'expired'	
				AND p.buynow_qty !='0'	
				AND (date(p.date_end) <= '".DATETODAY."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchsell']]."')	
				GROUP BY p.project_id
				ORDER BY p.project_id ASC ";
                $sql_buynww1 = $ilance->db->query($buynww, 0, null, __FILE__, __LINE__);
				$number = $ilance->db->num_rows($sql_buynww1);	
				$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
				$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);	
		
				$sql_gcsold = $ilance->db->query($buynww.$sql_limit, 0, null, __FILE__, __LINE__);
				if($number>0)
				{
					while ($res_gcsold = $ilance->db->fetch_array($sql_gcsold))
					{

						$uselistr = (strlen($res_gcsold['filehash'])>0)?HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gcsold['filehash'] .'&w=170&h=105':$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						$url=$ilconfig['globalauctionsettings_seourls']?'Coin/'.$res_gcsold['project_id'].'/'.construct_seo_url_name($res_gcsold['project_title']):$ilpage['merch'] .'?id='.$res_gcsell['project_id'];
						$htm ='<img src="' . $uselistr . '">';
						$res_gc_sold['item_path'] = $url;
						$res_gc_sold['thumbnail'] = $htm;
						$res_gc_sold['item_id'] = $res_gcsold['project_id'];

						$res_gc_sold['item_title'] = $res_gcsold['project_title'];
						$res_gc_sold['minbid_buynow'] = $res_gcsold['buynow_price'];
						//$res_gc_sold['bids'] = $res_gcsold['bids'];
						$res_gc_sold['qty'] = $res_gcsold['buynow_qty'];

						$res_gc_sold['date_end'] =  date('m-d-Y',strtotime($res_gcsold['date_end']));

						$res_gc_sold['status'] = 'Ended';
						//$res_gc_sold['current_bid'] = $resbid['bidamount'];
						$res_gc_itemsold[] = $res_gc_sold;
					}
				}
				else
				{				
				$res_gc_itemsold['mm'] = 'Nofound';
				}
					

				
	$pprint_array = array('prevnext','drop_value','daylist','monthlist','yearlist','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'sell_buynowunsold.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
				
}				
				///#####################################
				//item unsold
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'unsold')
	{
	$area_title = 'Item Unsold';
	$scriptpage = HTTP_SERVER .'Sell/Unsold'. print_hidden_fields(true, array('do','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	$endDate = DATETODAY;
	$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:2;
	$sql_gcunsold1 = " SELECT p.*,a.filehash FROM " . DB_PREFIX . "projects  p 
    			left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
                WHERE  (c.site_id=0 or (c.site_id=1 and c.sold_qty=0)) and
				p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
				AND     p.visible ='1'
				AND     p.haswinner = '0'
				AND 	p.hasbuynowwinner != '1'
				AND  	p.project_state = 'product'
				AND		p.filtered_auctiontype = 'regular'
				AND    	p.status = 'expired'	
				AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchsell']]."')
				ORDER BY p.id ASC ";
				
  	$gcunsold = " SELECT p.*,a.filehash FROM " . DB_PREFIX . "projects  p 
    			left join " . DB_PREFIX . "attachment a on p.project_id=a.project_id and a.attachtype='itemphoto'
    			left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
                WHERE  (c.site_id=0 or (c.site_id=1 and c.sold_qty=0)) and
				p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'   
				AND     p.visible ='1'
				AND     p.haswinner = '0'
				AND 	p.hasbuynowwinner != '1'
				AND  	p.project_state = 'product'
				AND		p.filtered_auctiontype = 'regular'
				AND    	p.status = 'expired'	
				AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchsell']]."')	
				ORDER BY p.id ASC ";
					
				$sql_gcunsold1 = $ilance->db->query($gcunsold, 0, null, __FILE__, __LINE__);
				$number = $ilance->db->num_rows($sql_gcunsold1);	

				$sql_gcunsold = $ilance->db->query($gcunsold.$sql_limit, 0, null, __FILE__, __LINE__);	

		$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:0;
		$drop_value=form_days_drop_down("searchsell",$ilance->GPC['searchsell']);
		

		$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);								
								
				if ($ilance->db->num_rows($sql_gcunsold) > 0)
				{
				//status
				while ($res_gcunsold = $ilance->db->fetch_array($sql_gcunsold))
					{
					
					if ($ilconfig['globalauctionsettings_seourls'])
						$url='Coin/'.$res_gcunsold['project_id'].'/'.construct_seo_url_name($res_gcunsold['project_title']);
					else
						$url='merch.php?id='.$res_gcunsold['project_id'];

					if(strlen($res_gcunsold['filehash'])>0)
						$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gcunsold['filehash'] .'&w=170&h=105';
					else
						$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
					
					$htm ='<img src="' . $uselistr . '">';

					$res_gc_unsold['item_path'] = $url;
					$res_gc_unsold['thumbnail'] = $htm;
					$res_gc_unsold['item_id'] = $res_gcunsold['project_id'];
					$res_gc_unsold['item_title'] = $res_gcunsold['project_title'];
					$res_gc_unsold['minbid_min'] = $res_gcunsold['startprice'];
					$res_gc_unsold['minbid_buynow'] = $res_gcunsold['buynow_price'];
					$res_gc_unsold['bids'] = $res_gcunsold['bids'];
					$res_gc_unsold['description'] = 'Item Unsold';
					$res_gc_unsold['timelef'] = date('m-d-Y',strtotime($res_gcunsold['date_end']));
					$res_gc_unsold['status'] = 'Ended';
					$res_gcsolding[] = $res_gc_unsold;
					}
				}
				else
				{				
				$res_gcsolding['mm'] = 'Nofound';
				}
			$pprint_array = array('prevnext','drop_value','daylist','monthlist','yearlist','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'sell_unsold.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
	}
	
				//item unsold end
				//#####################################
				//item pennding
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'pending')
{
	$ilance->GPC['pages'] = (!isset($ilance->GPC['pages']) OR isset($ilance->GPC['pages']) AND $ilance->GPC['pages'] <= 0) ? 1 : intval($ilance->GPC['pages']);				
	$area_title = 'Item Pending';
	$scriptpage = HTTP_SERVER .'Sell/Pending'. print_hidden_fields(true, array('do','cmd','pages','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
	$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:2;
	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'pendingsearch')
	{
	    $endDate = DATETODAY;
		
		
			$gccoinpend = " SELECT p.* FROM " . DB_PREFIX . "projects   p left join " . DB_PREFIX . "coins c  on c.coin_id=p.project_id
                WHERE  	p.user_id = '".$_SESSION['ilancedata']['user']['userid']."' 				
				AND     p.visible ='1'
				AND     p.haswinner = '0'
				AND		(p.hasbuynowwinner ='0' OR (p.hasbuynowwinner ='1' AND p.buynow_qty != '0'))
				AND  	p.project_state = 'product'
				AND    	p.status = 'expired'				
				AND   (date(p.date_end) <= '".$endDate."' AND date(p.date_end) >= '".$date_filter_values[$ilance->GPC['searchsell']]."')						
				group by p.id ";
			
			$sql_gccoinpend1 = $ilance->db->query($gccoinpend, 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($sql_gccoinpend1);	

			$sql_gccoinpend = $ilance->db->query($gccoinpend.$sql_limit, 0, null, __FILE__, __LINE__);	
			
		}
		else
		{ 
		$gcpend = " SELECT pj.project_id,pj.project_title,pj.currentprice,pj.buynow_qty,pj.buynow_price,pj.status,pj.date_end,pj.startprice,pj.bids,a.filehash FROM " . DB_PREFIX . "projects pj
				left join " . DB_PREFIX . "coins c  on pj.project_id=c.coin_id
				left join " . DB_PREFIX . "catalog_second_level cs on cs.coin_series_unique_no=pj.coin_series_unique_no
				left join " . DB_PREFIX . "catalog_toplevel cd on cd.denomination_unique_no=pj.coin_series_denomination_no 
				left join " . DB_PREFIX . "attachment a on a.project_id=pj.project_id AND a.attachtype='itemphoto'
                WHERE (c.site_id=0 or (c.site_id=1 and c.sold_qty=0)) 
				AND     pj.user_id = '".$_SESSION['ilancedata']['user']['userid']."'				
				AND     pj.visible ='1'
				AND     pj.haswinner = '0'
				AND		(pj.hasbuynowwinner ='0' OR (pj.hasbuynowwinner ='1' AND pj.buynow_qty != '0'))
				AND  	pj.project_state = 'product'
				AND     pj.status = 'expired'
				AND c.project_id != 0
				ORDER BY cd.denomination_sort, cs.coin_series_sort, pj.coin_detail_year ";

			$sql_gcpend1 = $ilance->db->query($gcpend, 0, null, __FILE__, __LINE__);
			$number1 = $ilance->db->num_rows($sql_gcpend1);	

			$sql_limit = 'LIMIT ' . (($ilance->GPC['pages'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
			$sql_gcpend = $ilance->db->query($gcpend.$sql_limit, 0, null, __FILE__, __LINE__);
		
		}	
		$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:0;
		$drop_value=form_days_drop_down("searchsell",$ilance->GPC['searchsell']);
		
		
		$counter1 = (intval($ilance->GPC['pages']) - 1) * fetch_perpage();
		$prevnext1 = print_pagnation($number1, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['pages']), $counter1, $scriptpage, 'pages');	
		
				if ($ilance->db->num_rows($sql_gcpend) > 0)
				{
				
				
				//status
				while ($res_gcpendding = $ilance->db->fetch_array($sql_gcpend))
					{
					 
						if ($ilconfig['globalauctionsettings_seourls'])
							$item_path=HTTP_SERVER.'Coin/'.$res_gcpendding['project_id'].'/'.construct_seo_url_name($res_gcpendding['project_title']).'';
						else
							$item_path=$ilpage['merch'] .'?id='.$res_gcsell['project_id'];
						if(strlen($res_gcpendding['filehash'])>0)
							$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gcpendding['filehash'] .'&w=170&h=105';
						else
							$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';

						$res_gc_pend['minbid'] = $res_gcpendding['startprice'];
						$res_gc_pend['buynow'] = $res_gcpendding['buynow_price'];
						$res_gc_pend['item_path'] = $item_path;
						
						$res_gc_pend['thumbnail'] = '<img src="' . $uselistr . '">';
						$res_gc_pend['item_id'] = $res_gcpendding['project_id'];
						$res_gc_pend['item_title'] = $res_gcpendding['project_title'];					
						$res_gc_pend['bids'] = $res_gcpendding['bids'];
						$res_gc_pend['timelef'] = date('m-d-Y',strtotime($res_gcpendding['date_end']));
						$res_gc_pend['buynow_qty'] = $res_gcpendding['buynow_qty'];
						$resgcsold['status'] = $res_gcpendding['status'];
						$resgcsold['status'] = 'Pending';
						$res_gc_pend['status'] = $resgcsold['status'];
						
						$res_gc_pend['current_bid'] = $res_gcpendding['currentprice'];
						$res_gc_itempending[] = $res_gc_pend;
					}
				
				
				
				}
				else
				{				
				$res_gc_itempending['mm'] = 'Nofound';
				}				
				//coin table			
					
				$gccoinpend = " SELECT co.coin_id,co.Title,co.Minimum_bid,DATE_FORMAT(co.End_Date, '%m-%d-%Y') as End_Date,co.status,a.filehash FROM  
				" . DB_PREFIX . "coins co
				left join " . DB_PREFIX . "attachment a on co.coin_id=a.project_id AND a.attachtype='itemphoto'
				left join " . DB_PREFIX . "catalog_coin cc on co.pcgs=cc.PCGS 
				left join " . DB_PREFIX . "catalog_second_level cs on cc.coin_series_unique_no=cs.coin_series_unique_no
				left join " . DB_PREFIX . "catalog_toplevel cd on cc.coin_series_denomination_no=cd.denomination_unique_no
				WHERE  	 co.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
				AND		co.project_id='0' AND 	co.status = '0'	 
				ORDER BY  cc.Orderno ,(CASE WHEN (co.pcgs = '6000120' OR co.pcgs = '6000127' OR co.pcgs = '6000128' OR co.pcgs = '6000129') THEN co.title END) ASC,co.grade DESC
				";

			$sql_gccoinpend1 = $ilance->db->query($gccoinpend, 0, null, __FILE__, __LINE__);
			$number = $ilance->db->num_rows($sql_gccoinpend1);	

			$sql_limit1 = 'LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
			$sql_gccoinpend = $ilance->db->query($gccoinpend.$sql_limit1, 0, null, __FILE__, __LINE__);
			$scriptpage1 = HTTP_SERVER .'Sell/Pending'. print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);
			$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage1);	
				
				 if ($ilance->db->num_rows($sql_gccoinpend) > 0)
				{				
				while ($res_gccoin_pendding = $ilance->db->fetch_array($sql_gccoinpend))
					{
					
					if(strlen($res_gccoin_pendding['filehash']))
						$uselistr = HTTPS_SERVER . 'image.php?cmd=thumb&subcmd=itemphoto&id=' . $res_gccoin_pendding['filehash'] .'&w=170&h=105';
					else
						$uselistr = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';

					$htm ='<img class="img_liboxpend" src="'.$uselistr.'" style="padding: 10px; border-width:0px; cursor:pointer;">';
					$res_gc_coin_pend['thumbnail'] = $htm;
					$res_gc_coin_pend['item_id'] = $res_gccoin_pendding['coin_id'];
					$res_gc_coin_pend['item_title'] = $res_gccoin_pendding['Title'];
					$res_gc_coin_pend['minbid_buynow'] = $res_gccoin_pendding['Minimum_bid'];
					//$res_gc_coin_pend['bids'] = $res_gccoin_pendding['bids'];
					$res_gc_coin_pend['timelef'] = $res_gccoin_pendding['End_Date'];
					
					$resgcsold['status'] = $res_gccoin_pendding['status'];
					$resgcsold['status'] = 'Pending';
					$res_gc_coin_pend['status'] = $resgcsold['status'];
					
					$res_gc_coin_pending[] = $res_gc_coin_pend;
					}
				}
				else
				{				
				$res_gc_coin_pending['mm'] = 'Nofound';
				}
				
			$pprint_array = array('prevnext1','prevnext','drop_value','daylist','monthlist','yearlist','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	$ilance->template->fetch('main', 'sell_pending.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning','res_gc_coin_pending'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
	}
					//item pennding end

// work for item pending download starts

if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'item_pending')
	{
	 
	  header("Location:item_pending_pdf.php");
	}
				
// work for item pending download end
					
					////returning starting
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'returned')
{
	$area_title = 'Items Returned';
	
	$scriptpage = HTTP_SERVER .'Sell/Returned'. print_hidden_fields(true, array('do','cmd','page','budget','searchid','list'), true, '', '', $htmlentities = true, $urldecode = false);   
	$endDate = DATETODAY;
	$ilance->GPC['searchsell']=isset($ilance->GPC['searchsell'])?$ilance->GPC['searchsell']:2;

   	$gcsell = " SELECT ct.coin_id,ct.Title,ct.Minimum_bid,ct.Buy_it_now,cr.return_date
			 FROM  " . DB_PREFIX . "coins_retruned ct,
			 " . DB_PREFIX . "coin_return cr
			 WHERE cr.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
			 AND cr.coin_id = ct.coin_id
			 AND   (date(cr.return_date) <= '".$endDate."' AND date(cr.return_date) >= '".$date_filter_values[$ilance->GPC['searchsell']]."')
			 ORDER BY cr.return_date ASC ";
	$sql_gcsell1 = $ilance->db->query($gcsell, 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($sql_gcsell1);	

	$sql_gcsell = $ilance->db->query($gcsell.$sql_limit, 0, null, __FILE__, __LINE__);
	
	

	$drop_value=form_days_drop_down("searchsell",$ilance->GPC['searchsell']);
		
	$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);	
									
				if ($ilance->db->num_rows($sql_gcsell) > 0)
				{
				while ($res_gcsell = $ilance->db->fetch_array($sql_gcsell))
					{	
					$res_gc_sell['coin_id'] = $res_gcsell['coin_id'];
					$res_gc_sell['Title'] = $res_gcsell['Title'];
					if(!empty($res_gcsell['Minimum_bid']))
					$res_gc_sell['Minimum_bid'] = $res_gcsell['Minimum_bid'];
					else
					$res_gc_sell['Minimum_bid'] = '-';
					if(!empty($res_gcsell['Buy_it_now']))
					$res_gc_sell['Buy_it_now'] = $res_gcsell['Buy_it_now'];
					else
					$res_gc_sell['Buy_it_now'] = '-';
					$res_gc_sell['return_date'] = $res_gcsell['return_date'];													
					$res_gc_returning[] = $res_gc_sell;
					}
				}
				else
				{				
				$res_gc_returning['return'] = 'Nofound';
				}
	/*if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'returnedsearch')
	{		
				if ($ilance->db->num_rows($sql_gcreturn) > 0)
				{
				$res_gc_returning['return'] = 'Nofound';
				} 
			else
			{
			$res_gc_returning['return'] = 'Nofound';
			}
			
	
	}
	else{
	$res_gc_returning['return'] = 'Nofound';
	}*/
		
	$pprint_array = array('prevnext','drop_value','bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'sell_returned.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
 }
 }
	else
		{				////returning ending
				//##############################
	$pprint_array = array('bidamount','project_title','buyingreminders','sellingreminders','scheduledcount','itemsworth','expertsrevenue','expertsearch','jobcount','expertcount','itemcount','feedbackactivity','messagesactivity','recentlyviewedflash','tagcloud','main_servicecats_img','main_productcats_img','main_servicecats','main_productcats','lanceads_folder','two_column_category_buyers','two_column_service_categories','two_column_product_categories','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'sell_current.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('res_gc_itempending','res_gc_itemsold','res_gcselling','res_gcsolding','res_gc_returning'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
 }

 function form_days_drop_down($select_name="searchsell",$selected_val=0)
 {
 	$days_drop_down_list["0"]="-------All-------";
 	$days_drop_down_list["1"]="Last 24 Hours";
 	$days_drop_down_list["2"]="Last 7 days";
 	$days_drop_down_list["3"]="Last 30 days";
 	$days_drop_down_list["4"]="Last 60 days";
 	$days_drop_down_list["5"]="Last 90 days";
 	$days_drop_down_list["6"]="Last 180 days";
 	$days_drop_down_list["7"]="Last 360 days";
 	$days_drop_down_list["8"]="Last 720 days";
 	$days_drop_down_list["9"]="Last 1080 days";
 	$drop_value='<select name="'.$select_name.'">';
 	foreach ($days_drop_down_list as $day_option_id=>$day_option_value)
 	{
 		if(($selected_val==$day_option_id))
 			$drop_value.='<option value="'.$day_option_id.'"  selected="selected">'.$day_option_value.'</option>';
 		else
 			$drop_value.='<option value="'.$day_option_id.'">'.$day_option_value.'</option>';
 	}
 	$drop_value.='</select>';
 	return $drop_value;
 }
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
