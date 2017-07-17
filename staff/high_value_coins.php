<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # # This File Created By Herakle Team On  24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
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
ini_set('memory_limit', '50240M');
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
  global $ilance,$ilconfig;
			
				



$sql_live = "SELECT p.project_id as p_project_id,c.coin_id as c_coin_id,c.Title,c.coin_listed as c_coin_listed,c.End_Date as c_End_Date,p.buynow,p.buynow_qty as p_buynow_qty,c.sold_qty,c.project_id as c_project_id,c.pending as c_pending,c.status as c_status,p.haswinner as p_haswinner ,p.user_id,p.hasbuynowwinner,concat(u.first_name,' ',u.last_name) as 'FullName', c.Minimum_bid,c.Buy_it_now,p.filtered_auctiontype, p.status as p_status,p.project_title,p.startprice,p.buynow_price,p.date_starts,p.date_end, c.Create_Date,i.invoiceid,i.status as ists,iv.final_invoice_id as fin_invoice_id, ic.status as finsts ,ic.amount ,ic.paid

FROM " . DB_PREFIX . "coins c
LEFT JOIN " . DB_PREFIX . "projects as p ON p.project_id=c.coin_id
LEFT JOIN " . DB_PREFIX . "users u on u.user_id = c.user_id 
LEFT JOIN " . DB_PREFIX . "invoices as i on i.projectid=p.project_id and i.p2b_user_id = p.user_id and i.invoicetype='escrow' and i.subscriptionid=0 AND i.isfvf = 0 AND i.isif = 0 AND i.isenhancementfee = 0 AND i.isescrowfee = 0 AND i.isbuyerfee = 0 AND i.Site_Id = 0 AND i.combine_project ='' and i.status in ('unpaid','complete','scheduled','paid') 

LEFT JOIN " . DB_PREFIX . "invoice_projects as iv on iv.invoice_id=i.invoiceid and iv.coin_id = c.coin_id and iv.seller_id=c.user_id 

LEFT JOIN " . DB_PREFIX . "invoices as ic on ic.invoiceid=iv.final_invoice_id and ic.projectid = '0' AND ic.invoicetype='escrow' and ic.combine_project !=''

WHERE (c.minimum_bid >= 2500 or c.buy_it_now>=2500) and c.coin_id not in (SELECT coin_id FROM " . DB_PREFIX . "shippnig_details where track_no != '' group by coin_id) 
and c.Site_Id=0
and c.coin_id >250000
ORDER BY c.coin_id DESC";


						
			$listing_items ='<div style="border:1px solid black; padding : 10px">
				<table   border="0">				
					<tr>
						<td size="23" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
						<td size="12" align="right" width="100%" family="helvetica" style="bold"><b>High Value Coins</b>
						<br/><br/>'.date("D M Y G:i:s").'</td>
						
					</tr>
					
					<tr>
						<td valign="top" size="10" family="helvetica" >
							Certified Coin Auctions & Direct Sales<br>
							17500 Red Hill Avenue, Suite 160, Irvine, CA 92614<br>
							Tel: +1.800.44.COINS - Fax: +1.949.679.4178<br>
							E-mail: info@greatcollections.com
						</td>
						<td >&nbsp;</td>
						<td >&nbsp;</td>

					</tr>
					
					<tr>
						<td size="10"></td>
					</tr>
				</table>
				<br/><br/>
				<table width="100%" style="text-align:center; color: #FFFFFF;">
					<tr bgcolor="#CD9C9C">
						<td align="center"><p>Coin ID</p></td>
						<td align="center"><p>Title</p></td>
						<td align="center"><p>Consignor</p></td>
						<td align="center"><p>Date Entered</p></td> 
						<td align="center"><p>Date Sold</p></td>
						<td align="center"><p>Paid</p></td>
					</tr>';
				$listed_list = $ilance->db->query($sql_live);
				//$listed_list1 = $ilance->db->query($sql_live1);
				
				$listed_list_no=0;
				
				
								
				    while($listed_coins = $ilance->db->fetch_array($listed_list))
					{	
						
						$listing_items.='<tr>';		
						$listing_items.='<td align="center">'.$listed_coins['c_coin_id'].'</td>
						<td align="center">'.$listed_coins['Title'].'</td>
						<td align="center">'.$listed_coins['FullName'].'</td>';
						
						if($listed_coins['c_coin_listed'] == 'c' AND
						($listed_coins['c_End_Date'] == '0000-00-00' OR $listed_coins['c_pending']
						== '1') AND $listed_coins['c_project_id'] == '0' AND
						$listed_coins['c_status'] == '0')
						{

						$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['Create_Date'])).'</td>';
						$listing_items.='<td align="center"> </td><td align="center" size="10">Pending</td>';

						}
						elseif (($listed_coins['p_status'] =='expired' OR
						$listed_coins['p_status'] == 'closed') AND $listed_coins['p_haswinner'] ==
						'0' AND $listed_coins['hasbuynowwinner'] ==
						'0' AND $listed_coins['p_buynow_qty'] > '0'AND
						$listed_coins['p_project_id'] == $listed_coins['c_coin_id'] AND
						$listed_coins['c_project_id'] != 0)
						{

						$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['Create_Date'])).'</td>';
						$listing_items.='<td align="center"> </td><td align="center" size="10">Relist</td>';

						}
						elseif ($listed_coins['c_coin_listed'] == 'c' AND
						$listed_coins['c_End_Date'] != '0000-00-00' AND $listed_coins['c_pending']
						!='1' AND $listed_coins['c_project_id'] == '0' AND
						$listed_coins['c_status'] == '0')
						{

						$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['Create_Date'])).'</td>';
						$listing_items.='<td align="center"> </td><td align="center" size="10">Holding</td>';

						}
						elseif ($listed_coins['p_status'] == 'open')
						{

						$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['Create_Date'])).'</td>';
						$listing_items.='<td align="center"> </td><td align="center" size="10">Live</td>';

						}
						elseif(($listed_coins['filtered_auctiontype']='regular' and $listed_coins['psts'] ='expired') or ($listed_coins['filtered_auctiontype']=='fixed' and $listed_coins['psts'] =='closed'))
						{
							
							
							
							if($listed_coins['Create_Date'] == '0000-00-00 00:00:00')
							{
							$listing_items.='<td align="center">-</td>';												

							}
							else
							{
							$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['Create_Date'])).'</td>';
							}
							
							
							if(($listed_coins['p_haswinner'] ==1) or ($listed_coins['hasbuynowwinner'] ==1))
								{		
									
									
									if($listed_coins['c_End_Date'] == '0000-00-00' or $listed_coins['date_end'] == '0000-00-00 00:00:00')
									{
									$listing_items.='<td align="center">-</td>';												

									}
									else
									{
									$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['date_end'])).'</td>';
									}	
									
									
										if($listed_coins['ists'] == 'unpaid')
										{
											$listing_items.='<td align="center" size="10">unpaid</td>';
																						
										}										
										elseif($listed_coins['ists'] == 'paid')
										{					
											$listing_items.='<td align="center" size="10">paid</td>';						
										}
										elseif($listed_coins['ists'] == 'complete' or  $listed_coins['ists'] == 'scheduled' )
										{
											
											if((($listed_coins['fin_invoice_id'] > 0) and ($listed_coins['amount'] ==0))  and (($listed_coins['finsts']=='paid' or $listed_coins['ists'] == 'paid' )))
											{								
											$listing_items.='<td align="center" size="10">paid</td>';						
											}
											elseif ($listed_coins['paid'] > 0.00)
											{
											$listing_items.='<td align="center" size="10">patialpaid</td>';
											}
											elseif ($listed_coins['paid'] == 0.00)
											{
											$listing_items.='<td align="center" size="10">scheduled</td>';
											}
											else
											{}
										}
																					
										else
										{
											$listing_items.='<td align="center" size="10">No</td>';												
										}
											
												
										
										
										
										

								}
								else
								{
									if( $listed_coins['c_End_Date'] == '0000-00-00' or $listed_coins['date_end'] == '0000-00-00 00:00:00') 
									{
									$listing_items.='<td align="center">-</td>';												

									}
									else
									{
									$listing_items.='<td align="center">'.date("m-d-Y", strtotime($listed_coins['date_end'])).'</td>';
									}	
							    }
									
						
						}
						
						else
						{
							
						}		
	
						
						
						$listed_list_no++;
						
						$listing_items.='</tr>';
					}
			 

										
												
									
							
					
				$listing_items.='</table> <br/><br/>
				<table width="100%">
				<tr>
				<td size="11" >Total Item Count : <b>'.$listed_list_no.'</b></td>
				</tr>
				</table>
				</div>';
					
			define('FPDF_FONTPATH','../font/');
			
			require('pdftable_1.9/lib/pdftable.inc.php');
			
			$p = new PDFTable();
			
			$p->AddPage();
			
			$p->setfont('times','',10);
							
			$p->htmltable($listing_items);
			
			$p->output('high_value_coins_report_'.date('Y-m-d h-i-s').'.pdf','D');  
			
				
	}
	else
	{
		refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}



	
	/*======================================================================*\
	|| ####################################################################||
	|| # worked for bug id :#7036 - High Value Coins                       ||
	|| ####################################################################||
	\*======================================================================*/
	?>
