<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
ini_set('memory_limit', '5024M');
set_time_limit(0);

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consignorlist')
	{
		if($ilance->GPC['filterby']=='username' OR $ilance->GPC['filterby']=='email')
		{
		    $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
								WHERE  username = '".$ilance->GPC['filtervalue']."'
								 OR    email    = '".$ilance->GPC['filtervalue']."'
                        ");
			$res = $ilance->db->fetch_array($sql);
			$userid = $res['user_id'];			
		}		 
		else
		{
			$userid = $ilance->GPC['filtervalue'];
		}
		
	   /* if($ilance->GPC["excsold"]=="old")
		{
			$condition="AND c.End_Date between '".NINETYDAYSAGO."' AND '".DATETODAY."'";
			$cond = '';	
			$conds = '';	
		}
		else
		{
			$condition='';
			$cond = "AND haswinner != 1 AND hasbuynowwinner != 1";
			$conds = "AND p.haswinner != 1 AND p.hasbuynowwinner != 1";
		}
		 		
		if($ilance->GPC["excsold"]=="sold")
		{
			$consinorslist = $ilance->db->query("SELECT c.coin_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.pcgs,c.cost FROM
											  " . DB_PREFIX . "catalog_coin cc,
											" .DB_PREFIX. "coins c
											WHERE c.user_id = '".$userid."' 
											AND c.sold_qty =0
											AND  c.pcgs=cc.PCGS
												GROUP BY c.coin_id
												ORDER BY  cc.Orderno ,c.grade DESC
											 ");
		}
		if($ilance->GPC["excsold"]=="old")
		{
			$consinorslist = $ilance->db->query("SELECT c.coin_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.pcgs,c.cost FROM
											 " . DB_PREFIX . "catalog_coin cc,
											" .DB_PREFIX. "coins c 
											WHERE c.user_id = '".$userid."'											
											AND c.End_Date between '".NINETYDAYSAGO."' AND '".DATETODAY."'
											AND  c.pcgs=cc.PCGS
												GROUP BY c.coin_id
												ORDER BY  cc.Orderno ,c.grade DESC
											 ");
		} */
						
			$consinorslist = $ilance->db->query("SELECT c.coin_id, c.Title, c.Certification_No,c.Buy_it_now,c.Minimum_bid,c.pcgs,c.cost  FROM 
											  " . DB_PREFIX . "catalog_coin cc,
											  " . DB_PREFIX . "coins c
											  WHERE c.user_id = '".$userid."'										   
												AND date(c.End_Date) >='2012-12-01' 
												AND  c.pcgs=cc.PCGS
												GROUP BY c.coin_id
												ORDER BY  cc.Orderno ,c.grade DESC												
											");
		
										
			$return_list =  $ilance->db->query("SELECT c.coin_id, c.Title, c.Certification_No,c.pcgs,c.cost FROM 
											 " . DB_PREFIX . "catalog_coin cc, 
											 " . DB_PREFIX . "coins_retruned c
											 WHERE c.user_id = '".$userid."' 
											  AND date(c.End_Date) >='2012-12-01'
												AND  c.pcgs=cc.PCGS
												GROUP BY c.coin_id
												ORDER BY  cc.Orderno ,c.grade DESC
												
											");		
									
		//cs.coin_series_sort,cc.coin_detail_year asc,cd.denomination_sort,
		$listing_items = '<table border="1" cellpadding="1"><tr><td>Coin_id</td><td>Sold</td><td>Title</td><td>PCGS</td><td>Certificate No</td><td>Min Bid</td><td>Buy Now</td><td>Bids</td><td>Cost</td><td>Total</td></tr>';
		
		$entire_total=0;
		
		if($ilance->db->num_rows($consinorslist)>0)
		{
			while($listing = $ilance->db->fetch_array($consinorslist))
			{

				$projectid = $listing['coin_id'];
				$projecttitle = $listing['Title'];
				$certnum = $listing['Certification_No'];

				$selprj = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = '".$listing['coin_id']."'");

				if($ilance->db->num_rows($selprj) > 0 )
				{

					$pjt_listing = $ilance->db->fetch_array($selprj);

					if($pjt_listing['haswinner'] == '1')
					{
						$sold = 'X';
					}
					elseif($pjt_listing['hasbuynowwinner'] == '1')
					{
						$sold = 'X';
					}
					else	
					{

						$sold = ''; 

					}		 

					if($pjt_listing['filtered_auctiontype'] == 'fixed')
					{
						$buynow_price = $ilance->currency->format($pjt_listing['buynow_price']); 
						$bp = $pjt_listing['buynow_price'];
					}
					else
					{
						$buynow_price = '-';
						$bp = '-';
					}	
					
					if($pjt_listing['filtered_auctiontype'] == 'regular')
					{
						$no_bids = $pjt_listing['bids'];
						$current_price = $ilance->currency->format($pjt_listing['currentprice']); 
						$cp = $pjt_listing['currentprice'];
					}
					else
					{
						$no_bids = '-';
						$current_price = '-';
						$cp = '-';
					}
					
				}
				else
				{

					if(!empty($listing['Buy_it_now']))
					{

						$buynow_price = $listing['Buy_it_now'];
						$bp = $listing['Buy_it_now'];
					}
					else
					{
						$buynow_price = '-';
						$bp = '-';
					}
					
					if(!empty($listing['Minimum_bid']))
					{

						$current_price = $listing['Minimum_bid'];
						$cp = $listing['Minimum_bid'];

					}
					else
					{
						$current_price = '-';
						$cp = '-';
					}
					
					$sold='Pending';						  

					$no_bids = '-';

				}

				$cost_column=$listing['cost'];
				
				$total =  $ilance->currency->format($cp+$bp);

				$entire_total+=$cp+$bp;
				
				$listing_items.= '<tr><td>'.$projectid.'</td><td>'.$sold.'</td><td>'.$projecttitle.'</td><td>'.$listing['pcgs'].'</td><td>'.$certnum.'</td><td>'.$current_price.'</td><td>'.$buynow_price.'</td><td>'.$no_bids.'</td><td>'.$cost_column.'</td><td>'.$total.'</td></tr>';

			}

		}
							 
		if(empty($ilance->GPC["excsold"]))
		{	
			if($ilance->db->num_rows($return_list)>0)
			{
				while($listing = $ilance->db->fetch_array($return_list))
				{
									
					$projectid = $listing['coin_id'];
					$projecttitle = $listing['Title'];
					$certnum = $listing['Certification_No'];

					$sold='R';

					$buynow_price = '-';
					$bp = '-';

					$no_bids = '-';

					$current_price = '-';
					$cp = '-';
					
					$cost_column=$listing['cost'];
					
					$total =  $ilance->currency->format($cp+$bp);
					
					$entire_total+=$cp+$bp;
					
					$listing_items.= '<tr><td>'.$projectid.'</td><td>'.$sold.'</td><td>'.$projecttitle.'</td><td>'.$listing['pcgs'].'</td><td>'.$certnum.'</td><td>'.$current_price.'</td><td>'.$buynow_price.'</td><td>'.$no_bids.'</td><td>'.$cost_column.'</td><td>'.$total.'</td></tr>';
     
				}
				
			} 							
			else
			{
				$listing_items.='<tr><td>No Result Found</td></tr>'; 
			}	
		}
		
		
		$listing_items.='<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
		$listing_items.='<td>Total Sum ';
		$listing_items.=$ilance->currency->format($entire_total).'</td>';
		$listing_items.='</tr>';
		
		$listing_items.='</table>';

		$listing_items.='</table>';

		define('FPDF_FONTPATH','../font/');

		require('pdftable_1.9/lib/pdftable.inc.php');

		$p = new PDFTable();

		$p->AddPage();

		$p->setfont('times','',10);

		$p->htmltable($listing_items);

		$p->output('consignor_list_with_cost_'.date('Y-m-d h-i-s').'.pdf','D');  
					  
							 
	}

	else
	{
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','reportorderby','reportfromrange','reportrange','radiopast','radioexact','reportcolumns','reportaction','reportshow','customprevnext','reportoutput','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	
		$ilance->template->fetch('main', 'consignorlist_with_cost_report.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
		
?>