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
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	/* vijay  for bug 3313 * start 20.12.13 */	
	$show_coin_search=0;
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='coins_search')
	{
		$show_coin_search=1;
		$projec_id=(isset($ilance->GPC['project_id'])) ? $ilance->GPC['project_id'] : '';
		$sql="
	SELECT p.*,c.coin_id ,c.pcgs,c.Certification_No,e.coin_id as ebay_coin_id,e.listedon,el.amount,el.invoice_status,el.coin_id as ebay_row_coin_id,el.buyer_id,c.consignid,c.Site_Id,c.Alternate_inventory_No,c.Title,c.Minimum_bid,c.user_id,c.relist_count,c.End_Date,c.Buy_it_now FROM " . DB_PREFIX . "coins AS c
	LEFT JOIN  " . DB_PREFIX . "projects AS p ON p.project_id = c.coin_id 
	LEFT JOIN  " . DB_PREFIX . "ebay_listing AS e ON e.coin_id = c.coin_id
	LEFT JOIN  " . DB_PREFIX . "ebay_listing_rows AS el ON el.coin_id = c.coin_id
	WHERE c.coin_id = '".$projec_id."'";
		$coins_search_Sold= $ilance->db->query($sql);
		$total_n1=$ilance->db->num_rows($coins_search_Sold);
		$total_n=false;
		if($total_n1>0)
		$total_n=true;
		
		if($ilance->db->num_rows($coins_search_Sold) > 0)
		{
			while($sld=$ilance->db->fetch_array($coins_search_Sold))
			{
				$projectid=$sld['coin_id'];
				if($sld['project_id']==$sld['coin_id'])//if there is a record in projects table
				{
					if($sld['filtered_auctiontype'] == 'regular')
					{
						$listpagesold['pcgs']=$sld['pcgs'];
						if($sld['Certification_No'] =='')
						{
							$listpagesold['Certification_No']= '-';
						}
						else
						{
							$listpagesold['Certification_No']=$sld['Certification_No'];
						}
						$listpagesold['consignid'] = '<span class="blue"><a href="consignments.php?cmd=coin_list&user_id='.$sld['user_id'].'&consignid='.$sld['consignid'].'">'.$sld['consignid'].'</a></span>'; 
						if($sld['Alternate_inventory_No'] =='')
						{
							$listpagesold['Alternate_inventory_No']= '-';
						}
						else
						{
							$listpagesold['Alternate_inventory_No']=$sld['Alternate_inventory_No'];
						}			
						$listpagesold['pro_project_id'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_id'].'</a></span>';
						$listpagesold['seller_name1']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('username', $sld['user_id']) . '</a></span>';	
						$listpagesold['seller_email'] ='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('email', $sld['user_id']) . '</a></span>';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_title'].'</a></span>';
						else
						$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['pro_project_id'].'" style="color:blue;">'.$sld['project_title'].'</a></span>';
						$listpagesold['Create_Date']='<strong>' . date("F d,Y",strtotime($sld['date_added'])) . '</strong>';
						$listpagesold['insertionfee']='<strong>'.$ilance->currency->format($sld['insertionfee']).'</strong>';
						$sldcoinpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coins WHERE coin_id = '".$projectid."'");
						if($ilance->db->num_rows($sldcoinpjt) > 0)
						{	
							while($soldcoinpjt = $ilance->db->fetch_array($sldcoinpjt))
							{
								$listpagesold['relist_count']=$soldcoinpjt['relist_count'];
							}	
						}
						$hammer_price = $ilance->currency->format($sld['currentprice']);
						if($sld['winner_user_id'] !='0')
						{	
							$listpagesold['buyer_name']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['winner_user_id'] . '">' . fetch_user('username', $sld['winner_user_id']) . '</a></span>';
							$listpagesold['buyer_email']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['winner_user_id'] . '">' . fetch_user('email', $sld['winner_user_id']) . '</a></span>';
							fetch_user('',$sld['winner_user_id']);
							$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['startprice']). '/$0.00</strong>';
							$listpagesold['buyer_fee']='<strong>'.$ilance->currency->format($sld['buyer_fee']). '</strong>';
							$listpagesold['paiddate']='<strong>' . date("F d,Y",strtotime($sld['date_end'])) . '</strong>';
							$sol='Sold';
							$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
							
						}
						if($sld['winner_user_id'] =='0')
						{
							$listpagesold['buyer_name']='<strong>No Winner</strong>';
							$listpagesold['buyer_email']='<strong>No Winner</strong>';
							$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['startprice']). '/$0.00</strong>';
							$listpagesold['buyer_fee']='-';
							$listpagesold['paiddate']='<strong>No Winner</strong>';
						} 
						$listpagesold['date_end']='<strong>' . date("F d,Y",strtotime($sld['date_end'])) . '</strong>';
						$sldreturnpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coin_return WHERE coin_id = '".$projectid."'");
						if($ilance->db->num_rows($sldreturnpjt) > 0)
						{	
							while($soldreturnpjt = $ilance->db->fetch_array($sldreturnpjt))
							{
								$listpagesold['return']='<strong>yes</strong>';
								$listpagesold['returndate']='<strong>' .$soldreturnpjt['return_date']. '</strong>';
							}	
						}
						else
						{
							$listpagesold['return']='<strong>Not Returned</strong>';
							$listpagesold['returndate']='<strong>Not Returned</strong>';
						}
						$sldinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE p2b_user_id = '".$sld['user_id']."'
				AND projectid = '".$projectid."'
				AND isif = '0'
				AND isfvf = '0'
				AND user_id = '".$sld['winner_user_id']."'
				AND description LIKE '%Payment Received%'
				");
						if($ilance->db->num_rows($sldinvprojt) > 0)
						{
							while($soldinvprojt = $ilance->db->fetch_array($sldinvprojt))
							{
							  $sql3="SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['winner_user_id']."'
				AND isif = '0'
				AND isfvf = '0'
				AND projectid = '0'
				AND combine_project LIKE '%".$soldinvprojt['invoiceid']."%'/*".__LINE__."*/";
								$sldinvpjt = $ilance->db->query($sql3);
								if($ilance->db->num_rows($sldinvpjt) > 0)
								{	
									while($soldinvpjt = $ilance->db->fetch_array($sldinvpjt))
									{ 
										$listpagesold['invoiceid']=$soldinvpjt['invoiceid'];
										$listpagesold['invoiceid']='<span class="blue"><a href="buyers.php?subcmd=_detail_invoice&user_id='.$soldinvpjt['user_id'].'&paidstatus='.$soldinvpjt['status'].'&amp;id='.$soldinvpjt['invoiceid'].'"">'.$soldinvpjt['invoiceid'].'</a></span>';
										$listpagesold['hammerprice']=$soldinvprojt['amount'];
										$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
										$shippeddate=!empty($soldinvprojt['shipment_date']) ? $soldinvprojt['shipment_date']:'--';
										$track_no=!empty($soldinvprojt['track_no']) ? $soldinvprojt['track_no']:'--';
										$shipped=!empty($soldinvprojt['email']) ? $soldinvprojt['email']:'--';
										$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
										$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
										$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
									}	
								}
								else 
								{
									$listpagesold['invoiceid']='<span class="blue">'.$soldinvprojt['invoiceid'].'</span>';
									$listpagesold['hammerprice']=$soldinvprojt['amount'];
									$listpagesold['paid_status']='<strong>'.$soldinvprojt['status'].'</strong>';
									$shippeddate=!empty($soldinvprojt['shipment_date']) ? $soldinvprojt['shipment_date']:'--';
									$track_no=!empty($soldinvprojt['track_no']) ? $soldinvprojt['track_no']:'--';
									$shipped=!empty($soldinvprojt['email']) ? $soldinvprojt['email']:'--';
									$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
									$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
									$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
									
								}
							}
						}
						else
						{
							$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
				AND projectid = '".$projectid."'
				AND isif = '1'
				AND isfvf = '0'
				AND projectid = '0'
				AND description LIKE '%Insertion Fee%'
				");
							if($ilance->db->num_rows($sellinvprojt) > 0)
							{	
								while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
								{
									$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
									$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
									$listpagesold['hammerprice']='<strong>UnSold</strong>';
									$listpagesold['paid_status']='<strong>'.$sellinvpjt['status'].'</strong>';
									$shippeddate=!empty($sellinvpjt['shipment_date']) ? $sellinvpjt['shipment_date']:'--';
									$track_no=!empty($sellinvpjt['track_no']) ? $sellinvpjt['track_no']:'--';
									$shipped=!empty($sellinvpjt['email']) ? $sellinvpjt['email']:'--';
									$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
									$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
									$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
								}	
							}
							else 
							{
								$listpagesold['invoiceid']='-';
								$listpagesold['hammerprice']='<strong>UnSold</strong>';
								$listpagesold['paid_status']='<strong>-</strong>';
								$shippeddate=!empty($sellinvpjt['shipment_date']) ? $sellinvpjt['shipment_date']:'--';
								$track_no=!empty($sellinvpjt['track_no']) ? $sellinvpjt['track_no']:'--';
								$shipped=!empty($sellinvpjt['email']) ? $sellinvpjt['email']:'--';
								$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
								$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
								$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
							}
						}
						if ($sld['filtered_auctiontype'] == 'regular')
						{
							$sol='Sold';
							if ($sld['bids'] > 0)
							{
								$listpagesold['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($sld['currentprice']+$sld['buyer_fee']). '</strong>';
							}
							else
							{
								$listpagesold['currentprice'] ='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($sld['currentprice']+$sld['buyer_fee']). '</strong>';
							}
							$listpagesold['bids'] = ($sld['bids'] > 0)
							? '<span class="blue">' . $sld['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
							: '<span class="blue"> Bid </span>';
						}
						else
						{
							$listpagesold['currentprice']='<strong>' .$sol.'&nbsp;'.$ilance->currency->format($sld['currentprice']+$sld['buyer_fee']). '</strong>'.$hammer;
							$listpagesold['bids'] = ($sld['bids'] > 0)
							? '<span class="blue">' . $sld['bids'] . '&nbsp;' . $phrase['_bids_lower'] . '</span>'
							: '<span class="blue"> Bid </span>';
						}
						$listpagesld[]=	$listpagesold;
					}
					if($sld['filtered_auctiontype'] == 'fixed')
					{
						$listpagesold['pcgs']=$sld['pcgs'];
						if($sld['Certification_No'] =='')
						{
							$listpagesold['Certification_No']= '-';
						}
						else 
						{
							$listpagesold['Certification_No']=$sld['Certification_No'];
						}
						$listpagesold['consignid'] = '<span class="blue"><a href="consignments.php?cmd=coin_list&user_id='.$sld['user_id'].'&consignid='.$sld['consignid'].'">'.$sld['consignid'].'</a></span>'; 
						if($sld['Alternate_inventory_No'] =='')
						{
							$listpagesold['Alternate_inventory_No']= '-';
						}
						else
						{
							$listpagesold['Alternate_inventory_No']=$sld['Alternate_inventory_No'];
						}
						$listpagesold['currentprice'] .='<strong>' .'<br>Buy Now'.'&nbsp;'.$ilance->currency->format($sld['buynow_price']). '<strong>';
						$listpagesold['bids']='<span class="blue">Buy<br>Now</span>';
						$sldbuyordrpjt = $ilance->db->query("
						

						
						SELECT b.buyer_id,b.orderdate,b.amount,s.track_no,s.email,s.shipment_date FROM " .DB_PREFIX . "buynow_orders as b 
						left join " . DB_PREFIX . "shippnig_details s on s.item_id='".$projectid."' and s.track_no !=''
						and s.buyer_id = b.buyer_id
						WHERE b.project_id = '".$projectid."'
						group by b.orderid,b.orderdate
						order by b.orderdate desc
						");
						if($ilance->db->num_rows($sldbuyordrpjt) > 0)
						{	
							while($soldbyordrpjt = $ilance->db->fetch_array($sldbuyordrpjt))
							{	
								$listpagesold['pro_project_id'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_id'].'</a></span>';
								$listpagesold['seller_name1']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('username', $sld['user_id']) . '</a></span>';	
								$listpagesold['seller_email'] ='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('email', $sld['user_id']) . '</a></span>';
								if ($ilconfig['globalauctionsettings_seourls'])	
								$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_title'].'</a></span>';
								else
								$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['project_id'].'" style="color:blue;">'.$sld['project_title'].'</a></span>';
								$listpagesold['Create_Date']='<strong>' . date("F d,Y",strtotime($sld['date_added'])) . '</strong>';
								$listpagesold['insertionfee']='<strong>'.$ilance->currency->format($sld['insertionfee']).'</strong>';
								$sldcoinpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coins WHERE coin_id = '".$projectid."'");
								if($ilance->db->num_rows($sldcoinpjt) > 0)
								{	
									while($soldcoinpjt = $ilance->db->fetch_array($sldcoinpjt))
									{
										$listpagesold['relist_count']=$soldcoinpjt['relist_count'];
									}	
								}
								$sldinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE p2b_user_id = '".$sld['user_id']."'
				AND projectid = '".$projectid."'
				AND isif = '0'
				AND isfvf = '0'
				AND user_id = '".$soldbyordrpjt['buyer_id']."'
				AND description LIKE '%Buy Now Payment Received%'
				");
								if($ilance->db->num_rows($sldinvprojt) > 0)
								{
									while($soldinvprojt = $ilance->db->fetch_array($sldinvprojt))
									{
									  $sql3="SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$soldbyordrpjt['buyer_id']."'
				AND isif = '0'
				AND isfvf = '0'
				AND projectid = '0'
				AND combine_project LIKE '%".$soldinvprojt['invoiceid']."%'/*".__LINE__."*/";
										$sldinvpjt = $ilance->db->query($sql3);
										if($ilance->db->num_rows($sldinvpjt) > 0)
										{	
											while($soldinvpjt = $ilance->db->fetch_array($sldinvpjt))
											{
												$listpagesold['invoiceid']='<span class="blue"><a href="buyers.php?subcmd=_detail_invoice&user_id='.$soldinvpjt['user_id'].'&paidstatus='.$soldinvpjt['status'].'&amp;id='.$soldinvpjt['invoiceid'].'"">'.$soldinvpjt['invoiceid'].'</a></span>';
												$listpagesold['hammerprice']=$soldbyordrpjt['amount'];
												$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
												$shippeddate=!empty($soldbyordrpjt['shipment_date']) ? $soldbyordrpjt['shipment_date']:'--';
												$track_no=!empty($soldbyordrpjt['track_no']) ? $soldbyordrpjt['track_no']:'--';
												$shipped=!empty($soldbyordrpjt['email']) ? $soldbyordrpjt['email']:'--';
												$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
												$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
												$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
														
											}	
										}
										else
										{
											$listpagesold['invoiceid']='<span class="blue">'.$soldinvprojt['invoiceid'].'</span>';					
											$listpagesold['hammerprice']=$soldbyordrpjt['amount'];
											$listpagesold['paid_status']='<strong>'.$soldinvprojt['status'].'</strong>';
											$shippeddate=!empty($soldinvprojt['shipment_date']) ? $soldinvprojt['shipment_date']:'--';
											$track_no=!empty($soldinvprojt['track_no']) ? $soldinvprojt['track_no']:'--';
											$shipped=!empty($soldinvprojt['email']) ? $soldinvprojt['email']:'--';
											$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
											$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
											$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
									
										}
									}
								}
								else
								{
									$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
				AND projectid = '".$projectid."'
				AND isif = '1'
				AND isfvf = '0'	
				AND description LIKE '%Insertion Fee%'
				");
									if($ilance->db->num_rows($sellinvprojt) > 0)
									{	
										while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
										{
											$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
											$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
											$listpagesold['hammerprice']='<strong>UnSold</strong>';
											$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
											$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
											$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
											$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
											$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
											$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
											$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
										}	
									}
									else 
									{
										$listpagesold['invoiceid']='-';
										$listpagesold['hammerprice']='<strong>UnSold</strong>';

									}
								}
								$hammer_price = $ilance->currency->format($sld['currentprice']);
								$listpagesold['buyer_fee']='-';
								$listpagesold['startprice']='<strong>$0.00/'.$ilance->currency->format($sld['currentprice']).'</strong>';
								$listpagesold['date_end']='<strong>' . date("F d,Y",strtotime($sld['date_end'])) . '</strong>';
								$listpagesold['buyer_name']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $soldbyordrpjt['buyer_id'] . '">' . fetch_user('username', $soldbyordrpjt['buyer_id']) . '</a></span>';	
								$listpagesold['buyer_email'] ='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $soldbyordrpjt['buyer_id'] . '">' . fetch_user('email', $soldbyordrpjt['buyer_id']) . '</a></span>';
								$listpagesold['paiddate']='<strong>' . date("F d,Y",strtotime($soldbyordrpjt['orderdate'])).'</strong>';
								$listpagesold['shipped']='<strong>' . date("F d,Y",strtotime($soldbyordrpjt['orderdate'])).'</strong>';
								$sol='Sold';
								$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
								$sldreturnpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coin_return WHERE coin_id = '".$projectid."'");
								if($ilance->db->num_rows($sldreturnpjt) > 0)
								{	
									while($soldreturnpjt = $ilance->db->fetch_array($sldreturnpjt))
									{
										$listpagesold['return']='<strong>yes</strong>';
										$listpagesold['returndate']='<strong>' .$soldreturnpjt['return_date']. '</strong>';
									}	
								}
								else
								{
									$listpagesold['return']='<strong>Not Returned</strong>';
									$listpagesold['returndate']='<strong>Not Returned</strong>';
								}
								$listpagesld[]=	$listpagesold;
							}
						}
						else
						{
						
								$listpagesold['pro_project_id'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_id'].'</a></span>';
								$listpagesold['seller_name1']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('username', $sld['user_id']) . '</a></span>';	
								$listpagesold['seller_email'] ='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('email', $sld['user_id']) . '</a></span>';
								if ($ilconfig['globalauctionsettings_seourls'])	
								$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['project_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['project_title'].'</a></span>';
								else
								$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['project_id'].'" style="color:blue;">'.$sld['project_title'].'</a></span>';
								$listpagesold['Create_Date']='<strong>' . date("F d,Y",strtotime($sld['date_added'])) . '</strong>';
								$listpagesold['insertionfee']='<strong>'.$ilance->currency->format($sld['insertionfee']).'</strong>';
								
								$hammer_price = $ilance->currency->format($sld['currentprice']);
								$listpagesold['buyer_fee']='-';
								$listpagesold['startprice']='<strong>$0.00/'.$ilance->currency->format($sld['currentprice']).'</strong>';
								$listpagesold['date_end']='<strong>' . date("F d,Y",strtotime($sld['date_end'])) . '</strong>';
								
								
								$listpagesold['paiddate']='<strong>' . date("F d,Y",strtotime($soldbyordrpjt['orderdate'])).'</strong>';
								$listpagesold['shipped']='<strong>' . date("F d,Y",strtotime($soldbyordrpjt['orderdate'])).'</strong>';
								$sol='Sold';
								$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
								
								$listpagesold['buyer_name']='<strong>No Winner</strong>';
								$listpagesold['buyer_email']='<strong>No Winner</strong>';
								
								$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
								$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
								$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
								$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
								$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
								$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
								$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
								
								$sldcoinpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coins WHERE coin_id = '".$projectid."'");
								if($ilance->db->num_rows($sldcoinpjt) > 0)
								{	
									while($soldcoinpjt = $ilance->db->fetch_array($sldcoinpjt))
									{
										$listpagesold['relist_count']=$soldcoinpjt['relist_count'];
									}	
								}
								
								$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
								AND projectid = '".$projectid."'
								AND isif = '1'
								AND isfvf = '0'	
								AND description LIKE '%Insertion Fee%'
								");
									if($ilance->db->num_rows($sellinvprojt) > 0)
									{	
										while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
										{
											$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
											$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
											$listpagesold['hammerprice']='<strong>UnSold</strong>';
											
										}	
									}
									else 
									{
										$listpagesold['invoiceid']='-';
										$listpagesold['hammerprice']='<strong>UnSold</strong>';

									}
								$sldreturnpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coin_return WHERE coin_id = '".$projectid."'");
								if($ilance->db->num_rows($sldreturnpjt) > 0)
								{	
									while($soldreturnpjt = $ilance->db->fetch_array($sldreturnpjt))
									{
										$listpagesold['return']='<strong>yes</strong>';
										$listpagesold['returndate']='<strong>' .$soldreturnpjt['return_date']. '</strong>';
									}	
								}
								else
								{
									$listpagesold['return']='<strong>Not Returned</strong>';
									$listpagesold['returndate']='<strong>Not Returned</strong>';
								}
						$listpagesld[]=	$listpagesold;
						}
						
					}
				}
				else
				{
					if($sld['Minimum_bid'] > '0')
					{
						$listpagesold['bids'] ='<span class="blue"> Bid </span>';
						$listpagesold['pcgs']=$sld['pcgs'];
						if($sld['Certification_No'] =='')
						{
							$listpagesold['Certification_No']= '-';
						}
						else
						{
							$listpagesold['Certification_No']=$sld['Certification_No'];
						}
						$listpagesold['consignid'] = '<span class="blue"><a href="consignments.php?cmd=coin_list&user_id='.$sld['user_id'].'&consignid='.$sld['consignid'].'">'.$sld['consignid'].'</a></span>'; 
						if($sld['Alternate_inventory_No'] =='')
						{
							$listpagesold['Alternate_inventory_No']= '-';
						}
						else
						{
							$listpagesold['Alternate_inventory_No']=$sld['Alternate_inventory_No'];
						}			
						$listpagesold['pro_project_id'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['coin_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['coin_id'].'</a></span>';
						$listpagesold['seller_name1']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('username', $sld['user_id']) . '</a></span>';	
						$listpagesold['seller_email'] ='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('email', $sld['user_id']) . '</a></span>';
						//$listpagesold['invoiceid']=$sld['ivcid'];
						if ($ilconfig['globalauctionsettings_seourls'])	
						$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['coin_id'].'/'.construct_seo_url_name($sld['Title']).'">'.$sld['Title'].'</a></span>';
						else
						$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['coin_id'].'" style="color:blue;">'.$sld['Title'].'</a></span>';
						$listpagesold['Create_Date']='<strong>' . date("F d,Y",strtotime($sld['listedon'])) . '</strong>';
						$listpagesold['insertionfee']='<strong>'.$ilance->currency->format($sld['listing_fee']).'</strong>';
						$listpagesold['currentprice']='-';
						$listpagesold['relist_count']=$sld['relist_count'];
						if($sld['Site_Id'] =='0')
						{
							$listpagesold['buyer_name']='<strong>No Winner</strong>';
							$listpagesold['buyer_email']='<strong>No Winner</strong>';
							$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['Minimum_bid']). '/$0.00</strong>';
							$listpagesold['buyer_fee']='-';
							$listpagesold['paiddate']='<strong>No Winner</strong>';
							$hammer_price = $ilance->currency->format($sld['Minimum_bid']);
							$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
				AND projectid = '".$projectid."'
				AND isif = '0'
				AND isfvf = '0'
				AND user_id = '".$sld['buyer_id']."'
				");
							if($ilance->db->num_rows($sellinvprojt) > 0)
							{	
								while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
								{
									$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
									$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
									$listpagesold['hammerprice']='<strong>UnSold</strong>';
								}	
							}
							else 
							{
								$listpagesold['invoiceid']='-';
								$listpagesold['hammerprice']='<strong>UnSold</strong>';
							}
						}
						else
						{			
							$listpagesold['buyer_name']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['buyer_id'] . '">' . fetch_user('username', $sld['buyer_id']) . '</a></span>';
							$listpagesold['buyer_email']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['buyer_id'] . '">' . fetch_user('email', $sld['buyer_id']) . '</a></span>';
							fetch_user('',$sld['buyer_id']);
							$sldinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE p2b_user_id = '".$sld['user_id']."'
						AND projectid = '".$projectid."'
						AND isif = '0'
						AND isfvf = '0'
						AND user_id = '".$sld['buyer_id']."'
						
						");
							if($ilance->db->num_rows($sldinvprojt) > 0)
							{
								while($soldinvprojt = $ilance->db->fetch_array($sldinvprojt))
								{
								  $sql3="SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['buyer_id']."'
						AND isif = '0'
						AND isfvf = '0'
						AND projectid = '0'
						AND combine_project LIKE '%".$soldinvprojt['invoiceid']."%'/*".__LINE__."*/";
									$sldinvpjt = $ilance->db->query($sql3);
									if($ilance->db->num_rows($sldinvpjt) > 0)
									{	
										while($soldinvpjt = $ilance->db->fetch_array($sldinvpjt))
										{
											$hammer_price = $ilance->currency->format($sld['amount']);
											$listpagesold['invoiceid']=$soldinvpjt['invoiceid'];
											$listpagesold['invoiceid']='<span class="blue"><a href="buyers.php?subcmd=_detail_invoice&user_id='.$soldinvpjt['user_id'].'&paidstatus='.$soldinvpjt['status'].'&amp;id='.$soldinvpjt['invoiceid'].'"">'.$soldinvpjt['invoiceid'].'</a></span>';
											$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
											$listpagesold['hammerprice']=$hammer_price;
											$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['Minimum_bid']). '/$0.00</strong>';
											$listpagesold['buyer_fee']='-';
											$listpagesold['paiddate']='<strong>' . date("F d,Y",strtotime($soldinvpjt['createdate'])) . '</strong>';
											$listpagesold['currentprice']='<strong>' .$soldinvpjt['status'].'&nbsp;'.$ilance->currency->format($soldinvpjt['paid']). '</strong>';
										}	
									}
									else 
									{
										$listpagesold['invoiceid']='<span class="blue">'.$soldinvprojt['invoiceid'].'</span>';
										$listpagesold['hammerprice']=$soldinvprojt['paid'];
									}
								}
							}
							else
							{
								$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
						AND projectid = '".$projectid."'
						AND isif = '1'
						AND isfvf = '0'
						AND projectid = '0'
						AND description LIKE '%Insertion Fee%'
						");
								if($ilance->db->num_rows($sellinvprojt) > 0)
								{	
									while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
									{
										$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
										$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
										$listpagesold['hammerprice']='<strong>UnSold</strong>';
									}	
								}
								else 
								{
									$listpagesold['invoiceid']='-';
									$listpagesold['hammerprice']='<strong>UnSold</strong>';
								}
							}
						}		
						$listpagesold['date_end']='<strong>' . date("F d,Y",strtotime($sld['End_Date'])) . '</strong>';
						$sldreturnpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coin_return WHERE coin_id = '".$projectid."'");
						if($ilance->db->num_rows($sldreturnpjt) > 0)
						{	
							while($soldreturnpjt = $ilance->db->fetch_array($sldreturnpjt))
							{
								$listpagesold['return']='<strong>yes</strong>';
								$listpagesold['returndate']='<strong>' .$soldreturnpjt['return_date']. '</strong>';
							}	
						}
						else
						{
							$listpagesold['return']='<strong>Not Returned</strong>';
							$listpagesold['returndate']='<strong>Not Returned</strong>';
						}
						$listpagesld[]=	$listpagesold;
					}
					if($sld['Buy_it_now'] > '0')
					{
						$listpagesold['bids'] ='<span class="blue"> Buy Now </span>';
						$listpagesold['pcgs']=$sld['pcgs'];
						if($sld['Certification_No'] =='')
						{
							$listpagesold['Certification_No']= '-';
						}
						else
						{
							$listpagesold['Certification_No']=$sld['Certification_No'];
						}
						$listpagesold['consignid'] = '<span class="blue"><a href="consignments.php?cmd=coin_list&user_id='.$sld['user_id'].'&consignid='.$sld['consignid'].'">'.$sld['consignid'].'</a></span>'; 
						if($sld['Alternate_inventory_No'] =='')
						{
							$listpagesold['Alternate_inventory_No']= '-';
						}
						else
						{
							$listpagesold['Alternate_inventory_No']=$sld['Alternate_inventory_No'];
						}			
						if($sld['Site_Id'] =='0')
						{
							//$listpagesold['invoiceid']=$sld['ivcid'];
							if ($ilconfig['globalauctionsettings_seourls'])	
							$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['coin_id'].'/'.construct_seo_url_name($sld['Title']).'">'.$sld['Title'].'</a></span>';
							else
							$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['coin_id'].'" style="color:blue;">'.$sld['Title'].'</a></span>';
							$listpagesold['Create_Date']='<strong>' . date("F d,Y",strtotime($sld['Create_Date'])) . '</strong>';
							$listpagesold['insertionfee']='<strong>'.$ilance->currency->format($sld['listing_fee']).'</strong>';
							$listpagesold['currentprice']='-';
							$listpagesold['relist_count']=$sld['relist_count'];
							if (!empty($sld['buyer_id']))
							{
							$listpagesold['buyer_name']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['buyer_id'] . '">' . fetch_user('username',  $sld['buyer_id']) . '</a></span>';
							$listpagesold['buyer_email']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $buyer_email . '">' . fetch_user('username',  $sld['buyer_id']) . '</a></span>';
							}
							else
							{
							$listpagesold['buyer_name']='<strong>No Winner</strong>';
							$listpagesold['buyer_email']='<strong>No Winner</strong>';
							}
							$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['Minimum_bid']). '/$0.00</strong>';
							$listpagesold['buyer_fee']='-';
							$listpagesold['paiddate']='<strong>No Winner</strong>';
							$hammer_price = $ilance->currency->format($sld['Minimum_bid']);
							$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
				AND projectid = '".$projectid."'				
				AND description LIKE '%Insertion Fee%'
				");
							if($ilance->db->num_rows($sellinvprojt) > 0)
							{	
								while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
								{
									$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
									$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
									$listpagesold['hammerprice']='<strong>UnSold</strong>';
									$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
									$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
									$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
									$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
									$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
									$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
									$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
								}	
							}
							else 
							{
								$listpagesold['invoiceid']='-';
								$listpagesold['hammerprice']='<strong>UnSold</strong>';
								$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
								$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
								$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
								$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
								$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
								$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
								$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
							}
						}
						else
						{	
							if (!empty($sld['buyer_id']))
							{
							$listpagesold['buyer_name']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['buyer_id'] . '">' . fetch_user('username',  $sld['buyer_id']) . '</a></span>';
							$listpagesold['buyer_email']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $buyer_email . '">' . fetch_user('username',  $sld['buyer_id']) . '</a></span>';
							}
							else
							{
							$listpagesold['buyer_name']='<strong>No Winner</strong>';
							$listpagesold['buyer_email']='<strong>No Winner</strong>';
							}
							
							
							
							
							$sldinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE p2b_user_id = '".$sld['user_id']."'
						AND projectid = '".$projectid."'
						AND isif = '0'
						AND isfvf = '0'
						AND user_id = '".$sld['buyer_id']."'
						");
							if($ilance->db->num_rows($sldinvprojt) > 0)
							{
								while($soldinvprojt = $ilance->db->fetch_array($sldinvprojt))
								{
								  $sql3="SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['buyer_id']."'
						AND isif = '0'
						AND isfvf = '0'
						AND projectid = '0'
						AND combine_project LIKE '%".$soldinvprojt['invoiceid']."%'/*".__LINE__."*/";
									$sldinvpjt = $ilance->db->query($sql3);
									if($ilance->db->num_rows($sldinvpjt) > 0)
									{	
										while($soldinvpjt = $ilance->db->fetch_array($sldinvpjt))
										{
											$hammer_price = $ilance->currency->format($sld['amount']);
											$listpagesold['invoiceid']=$soldinvpjt['invoiceid'];
											$listpagesold['invoiceid']='<span class="blue"><a href="buyers.php?subcmd=_detail_invoice&user_id='.$soldinvpjt['user_id'].'&paidstatus='.$soldinvpjt['status'].'&amp;id='.$soldinvpjt['invoiceid'].'"">'.$soldinvpjt['invoiceid'].'</a></span>';
											$hammer = '<br><font color="#999999">('.$hammer_price.'&nbsp;hammer)</font>';
											$listpagesold['hammerprice']=$hammer_price;
											$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['Minimum_bid']). '/$0.00</strong>';
											$listpagesold['buyer_fee']='-';
											$listpagesold['paiddate']='<strong>' . date("F d,Y",strtotime($soldinvpjt['createdate'])) . '</strong>';
											$listpagesold['currentprice']='<strong>' .$soldinvpjt['status'].'&nbsp;'.$ilance->currency->format($soldinvpjt['paid']). '</strong>';
											$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
											$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
											$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
											$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
											$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
											$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
											$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
										}	
									}
									else 
									{
										$listpagesold['invoiceid']='<span class="blue">'.$soldinvprojt['invoiceid'].'</span>';
										$listpagesold['hammerprice']=$soldinvprojt['paid'];
										$listpagesold['paid_status']='<strong>'.$soldinvprojt['status'].'</strong>';
									$shippeddate=!empty($soldinvprojt['shipment_date']) ? $soldinvprojt['shipment_date']:'--';
									$track_no=!empty($soldinvprojt['track_no']) ? $soldinvprojt['track_no']:'--';
									$shipped=!empty($soldinvprojt['email']) ? $soldinvprojt['email']:'--';
									$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
									$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
									$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
									}
								}
							}
							else
							{
								$sellinvprojt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "invoices WHERE user_id = '".$sld['user_id']."'
						AND projectid = '".$projectid."'
						AND isif = '1'
						AND isfvf = '0'
						AND projectid = '0'
						AND description LIKE '%Insertion Fee%'
						");
								if($ilance->db->num_rows($sellinvprojt) > 0)
								{	
									while($sellinvpjt = $ilance->db->fetch_array($sellinvprojt))
									{
										$listpagesold['invoiceid']=$sellinvpjt['invoiceid'];
										$listpagesold['invoiceid']='<span class="blue">Ins Fee:'.$sellinvpjt['invoiceid'].'</span>';
										$listpagesold['hammerprice']='<strong>UnSold</strong>';
										$listpagesold['paid_status']='<strong>'.$soldinvpjt['status'].'</strong>';
										$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
										$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
										$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
										$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
										$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
										$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
									}	
								}
								else 
								{
									$listpagesold['invoiceid']='-';
									$listpagesold['hammerprice']='<strong>UnSold</strong>';
									$listpagesold['paid_status']='-';
									$shippeddate=!empty($soldinvpjt['shipment_date']) ? $soldinvpjt['shipment_date']:'--';
									$track_no=!empty($soldinvpjt['track_no']) ? $soldinvpjt['track_no']:'--';
									$shipped=!empty($soldinvpjt['email']) ? $soldinvpjt['email']:'--';
									$listpagesold['Shipped']='<strong>' .$shipped .'</strong>';
									$listpagesold['Shipped_Date']='<strong>' . date("F d,Y",strtotime($shippeddate)) .'</strong>';
									$listpagesold['track_no']='<strong>' .$track_no.'</strong>';
								}
							}
						}		
						$listpagesold['pro_project_id'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['coin_id'].'/'.construct_seo_url_name($sld['project_title']).'">'.$sld['coin_id'].'</a></span>';
						if ($ilconfig['globalauctionsettings_seourls'])	
						$listpagesold['project_title'] ='<span class="blue"><a href="'.HTTP_SERVER.'Coin/'.$sld['coin_id'].'/'.construct_seo_url_name($sld['Title']).'">'.$sld['Title'].'</a></span>';
						else
						$listpagesold['project_title']='<span class="blue"><a href="'.HTTP_SERVER.'merch.php?id='.$sld['coin_id'].'" style="color:blue;">'.$sld['Title'].'</a></span>';
						$listpagesold['Create_Date']='<strong>' . date("F d,Y",strtotime($sld['Create_Date'])) . '</strong>';
						$listpagesold['insertionfee']='<strong>'.$ilance->currency->format($sld['listing_fee']).'</strong>';
						$listpagesold['currentprice']='-';
						$listpagesold['relist_count']=$sld['relist_count'];
						$listpagesold['startprice']='<strong>' .$ilance->currency->format($sld['Minimum_bid']). '/$0.00</strong>';
						$listpagesold['buyer_fee']='-';
						$listpagesold['paiddate']='<strong>No Winner</strong>';
						$hammer_price = $ilance->currency->format($sld['Minimum_bid']);
						$listpagesold['seller_name1']='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('username', $sld['user_id']) . '</a></span>';	
						$listpagesold['seller_email'] ='<span class="blue"><a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $sld['user_id'] . '">' . fetch_user('email', $sld['user_id']) . '</a></span>';
						$listpagesold['date_end']='<strong>' . date("F d,Y",strtotime($sld['End_Date'])) . '</strong>';
						
						$sldreturnpjt = $ilance->db->query("SELECT * FROM " .DB_PREFIX. "coin_return WHERE coin_id = '".$projectid."'");
						if($ilance->db->num_rows($sldreturnpjt) > 0)
						{	
							while($soldreturnpjt = $ilance->db->fetch_array($sldreturnpjt))
							{
								$listpagesold['return']='<strong>yes</strong>';
								$listpagesold['returndate']='<strong>' .$soldreturnpjt['return_date']. '</strong>';
							}	
						}
						else
						{
							$listpagesold['return']='<strong>Not Returned</strong>';
							$listpagesold['returndate']='<strong>Not Returned</strong>';
						}
						$listpagesld[]=	$listpagesold;
					}
				}
			}
			//exit;
		}
	}
	$pprint_array = array('filtervalue','projec_id','number','item_id','amount_val','datesold','seller_id','seller_name','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','coin_id1','consign_id1','$seller_name1','$title1','$pcgs_no1','dt1','amount1','prof2');
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'coins_search.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('listpagesld','listpage'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	/* vijay  for bug 3313 * ends 3.12.13 */
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>