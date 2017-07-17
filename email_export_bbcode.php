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
		'accounting',
		'subscription',
		'buying',
		'selling',
		'rfp'
	);
	
	// #### load required javascript ###############################################
	// $jsinclude = array(
		// 'functions',
		// 'ajax',
		// 'inline',
		// 'cron',
		// 'autocomplete',
		// 'flashfix',
		// 'jquery'
	// );
	
	// #### define top header nav ##################################################
	$topnavlink = array(
			'forum_entry'
	);
	// #### setup script location ##################################################
	define('LOCATION','invoicepayment');
	// #### require backend ########################################################
	require_once('./functions/config.php');
	
	// #### require shipping backend ###############################################
	require_once(DIR_CORE . 'functions_shipping.php');
	$ilance->subscription = construct_object('api.subscription');
	// #### setup default breadcrumb ###############################################
	$show['widescreen'] = false;
	$show['block_para'] = true;
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
	$navcrumb[""] = "Forum codes";
	// #### build our encrypted array for decoding purposes
	$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode('post_forum.php' . print_hidden_fields(true, array(), true)));
		exit();
	}
	
   /* vijay  start 18 nov for bbcode  on off */
   
   if ($_SESSION['ilancedata']['user']['access_bb'] == '0')
	 {
	 refresh(HTTPS_SERVER . $ilpage['main'] . print_hidden_fields(true, array(), true));
		exit();
	 }
	/*
	$permitted_user=array(101,154,163);
            
        if(!in_array($_SESSION['ilancedata']['user']['userid'],$permitted_user))
	{
		refresh(HTTPS_SERVER . $ilpage['main'] . print_hidden_fields(true, array(), true));
		exit();
	}
      vijay  start  18 nov for bb on off end   */

		
		$sql=$ilance->db->query("SELECT p.project_id,p.project_title,p.description,p.coin_series_denomination_no,c.Grading_Service,c.Grade,ct.coin_detail_year,p.Orderno ,p.coin_series_unique_no,p.startprice,p.date_end,p.mintmark,a.filehash,ct.coin_detail_suffix
								FROM ". DB_PREFIX ."projects p	
								LEFT JOIN " . DB_PREFIX . "coins AS c ON c.coin_id=p.project_id 								
								LEFT JOIN " . DB_PREFIX . "catalog_coin AS ct ON p.Orderno = ct.Orderno
								LEFT JOIN " . DB_PREFIX . "attachment AS a  ON p.project_id = a.project_id
								WHERE p.status='open'
								AND p.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
								AND p.visible = '1'
								AND p.hotlists = '1'
								AND a.attachtype = 'itemphoto'								
								");	
			
        $ilance->GPC['item_title']      =1;
        $ilance->GPC['item_image']      =1;
  		
			
		if($ilance->db->num_rows($sql) >0)
		{		
            $bb_code=" ";
			$html='<div style="padding:10px;color:blue;text-align:center;">';
			$text='';
			while($row=$ilance->db->fetch_array($sql))
			{
			
			
				if(isset($ilance->GPC['item_title']) AND !empty($ilance->GPC['item_title']))
				{
					
					// $bb_code.='[L='.$row['coin_detail_year'].'-'.$row['mintmark'].'&nbsp;'.$row['Grading_Service'].'&nbsp;'.$row['Grade'].'&nbsp;'.$row['coin_detail_suffix'].']'.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'[/L]
					// ';
					
					$bb_code.='[L='.$row['project_title'].' at GreatCollections Coin Auctions'.']'.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'[/L]
					';
					
					$html.='<div><a href='.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'>'.$row['project_title'].'</a></div>';
					
					$text.=$row['project_title']."\r\n";					
				}
				if(isset($ilance->GPC['item_id']) AND !empty($ilance->GPC['item_id']))
				{
					
					$bb_code .='[b]Item ID :[/b][L='.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).']'.$row['project_id'].'[/L]
					';
					
					$html.='<table cellspacing="4" align="center"><tr><td style="float:right">ITEM ID :</td><td>';
					$html.='<a href='.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'>'.$row['project_id'].'</a>';
					$html.='</td></tr>';
					
					
					$text.='ITEM ID : '.$row['project_id'].'
					';
				}
				/* vijay  start 29  may 2014 for bbcode   */
				/*
				if(isset($ilance->GPC['item_start_price']) AND !empty($ilance->GPC['item_start_price']))
				{
					
					$bb_code.='[b]Starting Price : [/b][b]US$'.$row['startprice'].'[/b]
					';
					
					$html.='<tr><td style="float:right">STARTING PRICE :</td><td>';
					$html.='<b>US$'.$row['startprice'].'</b>';
					$html.='</td></tr>';
					
					
					$text.='STARTING PRICE : US$'.$row['startprice'].'
					';
					
				}
				if(isset($ilance->GPC['item_end_date']) AND !empty($ilance->GPC['item_end_date']))
				{
					$date=print_date($row['date_end']);
										
					$bb_code.='[b]Auction Ends On : [/b][b]'.$date.'[/b]
					';
					
					$html.='<tr><td style="float:right">AUCTION ENDS ON :</td><td>';
					$html.='<b>'.$date.'</b>';
					$html.='</td></tr></table>';
					
					
					$text.='AUCTION ENDS ON : '.$date.'
					';
					
				}	
				  vijay  work end */
				if(isset($ilance->GPC['item_image']) AND !empty($ilance->GPC['item_image']))
				{
					/* vijay  start 29  may 2014 for bbcode   */
				/*
					if(isset($ilance->GPC['item_image_size']) AND $ilance->GPC['item_image_size']['0'] == '1')
					{							
						$bb_code.='[img]'.HTTP_SERVER .$ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $row['filehash'].'[/img]
						
						';

						$html.='<div><a href='.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'><img src='.HTTP_SERVER .$ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $row['filehash'].'></a></div>';
					}
					if(isset($ilance->GPC['item_image_size']) AND $ilance->GPC['item_image_size']['0']== '2')
					{		
						$bb_code.='[img]'.HTTP_SERVER.$ilpage['image'] . '?cmd=thumb&subcmd=itemphoto&id=' . $row['filehash'] .'&w=170&h=140[/img]
						
						';

						$html.='<div><a href='.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'><img src='.HTTP_SERVER .$ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $row['filehash'] .'&w=170&h=140></a></div>';		
					}
					if(isset($ilance->GPC['item_image_size']) AND $ilance->GPC['item_image_size']['0'] == '3')
					{	
					 vijay  work end */
					
						$bb_code.='[img]'.HTTP_SERVER .$ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $row['filehash'] .'&w=268[/img]
						
						';

						$html.='<div><a href='.HTTP_SERVER .'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']).'><img src='.HTTP_SERVER .$ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $row['filehash'] .'&w=268></a></div>';	

						
					//}
					
				}
				/* vijay  start 29  may 2014 for bbcode   */
				/*
				if(isset($ilance->GPC['item_description']) AND !empty($ilance->GPC['item_description']) AND !empty($row['description']))
				{
					
					$bb_code.='[b]'.$row['description'].'[/b][/size][/color]';
					
					$html.='<div><b>'.$row['description'].'</b></div>';	
					
					$text.=$row['description'];
					
				}
				
				*/
				
				// $sql_denom_series=$ilance->db->query("SELECT  t.denomination_long,s.coin_series_name 
										// FROM ". DB_PREFIX ."catalog_toplevel t,
										// ". DB_PREFIX ."catalog_second_level s
										// WHERE t.denomination_unique_no = '".$row['coin_series_denomination_no']."'
										// AND s.coin_series_unique_no = '".$row['coin_series_unique_no']."'
										// ");	
				// while($row_denom_series=$ilance->db->fetch_array($sql_denom_series))
				// {
					// if(isset($ilance->GPC['item_denomination']))
					// {
						
						// $bb_code.='[b]DEnomination : [/b][font=courier]'.$row_denom_series['denomination_long'].'[/font]';
						
					// }
					// if(isset($ilance->GPC['item_series']))
					// {
						
						// $bb_code.='[b]Series :[/b][font=courier]'.$row_denom_series['coin_series_name'].'[/font]';
						
					// }
				// }	

				
			}
			$bb_code.='';
			$html.='</div>';
			
			$show_bb_code['show_bb_code']="show";
			
			$pprint_array = array('show_bb_code','bb_code','html','text','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server');

			
			
			$ilance->template->fetch('main', 'post_forum.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			print_notice("Failed","Either coin with given item id doesn't exist or it doesn't belongs to you","post_forum.php","Get code for other coin");
			exit;
		}
		
	

?>