<?php
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
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$user_down = '<select name="user_down" id="user_down" >
								              <option value="" selected="selected">Select</option>';
										$con_date = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "consignments	
										GROUP BY user_id
										ORDER BY user_id
										");
										$datecount = 0;							            
							            if($ilance->db->num_rows($con_date) > 0)
										{
										      
											    while($res_date = $ilance->db->fetch_array($con_date))
												{
												
												 										  
												 $user_down.='<option value="'.$res_date['user_id'].'">'.fetch_user('username',$res_date['user_id']).'</option>';												
												 $datecount++;
												}
										}
										
		$user_down.='</select>';
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'statement')
		{
			$_SESSION['ilancedata']['user']['userstatement'] =  $ilance->GPC['user_down'];
			//$user_statement = $_SESSION['ilancedata']['user']['userstatement'];
			
		}
 		$area_title = 'Conginer Statement';
		$page_title = SITE_NAME . ' - ' . 'Statement';
		$user_id = $_SESSION['ilancedata']['user']['userstatement'];
			
		$date_down = '<select name="date_down" id="date_down" >
								              <option value="" selected="selected">Select</option>';
										$con_date = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "coins 										
										WHERE user_id = '".$_SESSION['ilancedata']['user']['userstatement']."'
										AND End_Date != '0000-00-00'										
										GROUP BY date(End_Date)
										");
										$datecount = 0;							            
							            if($ilance->db->num_rows($con_date) > 0)
										{
										      
											    while($res_date = $ilance->db->fetch_array($con_date))
												{
												
												  $date_coin = explode('-',$res_date['End_Date']);
										          $date_day = explode(' ',$date_coin[2]);
									              $month_name = $date_day[0].'-'.$date_coin[1].'-'.$date_coin[0]; 
												  $month_namev = $date_coin[0].'-'.$date_coin[1].'-'.$date_day[0];
											
																							
												$con_date_co = $ilance->db->query("
												SELECT COUNT(*) AS endcount
												FROM " . DB_PREFIX . "coins 
												WHERE user_id = '".$_SESSION['ilancedata']['user']['userstatement']."' 
												AND date(End_Date) = '".$month_namev."'
												
										
												");
												$res_date_co = $ilance->db->fetch_array($con_date_co);
												$item_count = $res_date_co['endcount'];
												  
												 $date_down.='<option value="'.$month_namev.'">'.$month_name.' <b>('.$item_count.' items)</b></option>';												
												 $datecount++;
												}
										}
										
		$date_down.='</select>';
		
		
		
		if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
		{
		  
		  $userstatementid = $ilance->GPC['userstatementid'];
		  
		  $dateexp = explode('-',$ilance->GPC['date_down']);
		  $ilance->GPC['year'] = $dateexp[0];
		  $ilance->GPC['month'] = $dateexp[1];
		  $ilance->GPC['day'] = $dateexp[2];
		   if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
            {
                       		$validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
							$validdate1 = intval($ilance->GPC['month']) . '-' . $ilance->GPC['day'] . '-' . $ilance->GPC['year'];
							
							$settledate = intval($ilance->GPC['month']) + 1 . '-' . $ilance->GPC['day'] . '-' . $ilance->GPC['year'];
							$date1 =  $validdate1;
							$date =  date('Y-m-d',strtotime( $validdate));
			}
			
			$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
						$day = $ilance->GPC['day'];
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				$month = $ilance->GPC['month'];
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
				
				$year = $ilance->GPC['year'];
				for ($k = $year;$k > $year-5;$k--) 
				{
   				$s = ($k == $year)?' selected':'';
   					$yearlist .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
				}
				
		  $show['no_statement'] = false;
		   $select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs			
					WHERE co.user_id = '".$userstatementid."'
					AND date(co.End_Date) = '".$ilance->GPC['date_down']."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no					
					GROUP BY co.coin_id
					ORDER BY cs.coin_series_sort,
					cc.coin_detail_year
					
					");	
		
					
		}
		else
		{
		
		$show['no_statement'] = false;
		
		$date1 = date('m-d-Y');
		$date = DATETODAY;
		$settledate = date(date('m') + 1 . '-' . date('d') . '-' . date('Y'));
		$select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs		
					WHERE co.user_id = '".$_SESSION['ilancedata']['user']['userstatement']."'
					AND date(co.End_Date) = '".$date."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no
					GROUP BY co.coin_id
					ORDER BY cs.coin_series_sort,
					cc.coin_detail_year
					");
		
		}
		$listcount = $ilance->db->num_rows($select);
		
		if ($ilance->db->num_rows($select) > 0)
         {
				$show['statement'] = true;							
				$row_count = 0;				
				
                 while ($res = $ilance->db->fetch_array($select))
                  {
				   	if($res['project_id'] == '0')
					{
						$res['bids'] = '-';
						$res['fvf'] = '-';
						$res['bidamount'] = '-';
						$res['binamount'] = '-';
						$res['seller_fee'] = '-';
						$res['listing_fee'] = '-';
						$res['net_consignor'] = '-';
						
					}
					else
					{
						$selectbid = $ilance->db->query("SELECT MIN(bidamount) AS bidamount, MAX(bidamount) AS final,count(*) AS count FROM ".DB_PREFIX."project_bids			
														WHERE project_id = '".$res['project_id']."'
														");
						$selectbin = $ilance->db->query("SELECT SUM(amount) AS binamount, SUM(qty) AS qty FROM ".DB_PREFIX."buynow_orders			
														WHERE project_id = '".$res['project_id']."'
														");
						$selectpjt = $ilance->db->query("SELECT insertionfee, fvf, featured, highlite, bold FROM ".DB_PREFIX."projects			
														WHERE project_id = '".$res['project_id']."'
														");
														
						$selectinvoice = $ilance->db->query("SELECT SUM(amount) AS newfvf FROM ".DB_PREFIX."invoices			
						WHERE projectid = '".$res['project_id']."'
						AND isfvf = '1'
						");
						// murugan changes on jun 24 								
						$enhancementfee = $ilance->db->query("SELECT SUM(amount) AS newenhance FROM ".DB_PREFIX."invoices			
						WHERE projectid = '".$res['project_id']."'
						AND isenhancementfee = '1'
						");
						$result = $ilance->db->fetch_array($selectbid, DB_ASSOC);
						$result1 = $ilance->db->fetch_array($selectbin, DB_ASSOC);
						$resultpjt = $ilance->db->fetch_array($selectpjt, DB_ASSOC);
						
						$resultinvoice = $ilance->db->fetch_array($selectinvoice, DB_ASSOC);
						
						// murugan june 24 
						$resenhancementfee = $ilance->db->fetch_array($enhancementfee, DB_ASSOC);
						
						 // miscellaneous Calculatation Murugan on jun 4 
						$misselect = $ilance->db->query("SELECT amount,invoicetype FROM ". DB_PREFIX ."invoices
				  						WHERE user_id ='".$userstatementid."'
				  						AND projectid = '".$res['project_id']."'
				  						AND ismis = 1 ");
							
						if ($ilance->db->num_rows($misselect) > 0)
						{
							$resmis = $ilance->db->fetch_array($misselect, DB_ASSOC);
							//murugan july 7
							if($resmis['invoicetype'] == 'debit')
							{
								$misdebit[] = $resmis['amount'];
							}
							if($resmis['invoicetype'] == 'credit')
							{
								$miscredit[] = $resmis['amount'];
							}							
							$miscell[] = $resmis['amount'];
							//$misamt = $ilance->currency->format($resmis['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$miscell[] = 0;
							$miscredit[] = 0;
							$misdebit[] = 0;
							//$misamt = $ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']);
						}
						
						
						// Featured fee Amount 
						if($resultpjt['featured'] !=0)
						{
							$featured = $ilconfig['productupsell_featuredfee'];
						}
						else
						{
						 	$featured = '0.00';
						}
						// highlite fee amount
						if($resultpjt['highlite'] !=0)
						{
							$highlite = $ilconfig['productupsell_highlightfee'];
						}
						else
						{
						 	$highlite = '0.00';
						}
						
						// bold fee amount
						if($resultpjt['bold'] !=0)
						{
							$bold = $ilconfig['productupsell_boldfee'];
						}
						else
						{
						 	$bold = '0.00';
						}
						// Total Amount (insertionfee , bold,highlight,featured)
						//$listfeetotal = $resultpjt['insertionfee'] + $featured + $highlite + $bold;
						// july 12
						$listfeetotal = $resultpjt['insertionfee'] + $resenhancementfee['newenhance'];
						
						//$totfvf[] = $resultpjt['fvf'];
						$totfvf[] = $resultinvoice['newfvf'];
						//$totins[] = $resultpjt['insertionfee'] + $featured + $highlite + $bold;
						// july 12
						$totins[] = $resultpjt['insertionfee'] + $resenhancementfee['newenhance'];
						$res['bids'] = $result['count'];
						$bidtot[] = $result['count'];
						
						
						if($res['Minimum_bid'] != '')
						{						
							//if($res['bidamount'] != '')
							//$test5[] = $result['bidamount'];
							//$res['bidamount'] = $ilance->currency->format($result['bidamount'],$ilconfig['globalserverlocale_defaultcurrency']);
							$test5[] = $res['Minimum_bid'];
							$res['bidamount'] = $ilance->currency->format($res['Minimum_bid'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
						  	$res['bidamount'] = '0.00';
						}
						
						/*if($result1['binamount'] != '')
						{						
							$test4[] = $result1['binamount'];
							$res['binamount']  = $ilance->currency->format($result1['binamount'],$ilconfig['globalserverlocale_defaultcurrency']);
							
						}*/
						if($res['Buy_it_now'] != '')
						{						
							$test4[] = $res['Buy_it_now'];
							$res['binamount']  = $ilance->currency->format($res['Buy_it_now'],$ilconfig['globalserverlocale_defaultcurrency']);
							
						}
						else
						{
						  	$res['binamount']  = '0.00';
						}
						if($result['final'] != '')
						{
							$res['finalprice'] = $result['final'];
							$res['qty'] = '';
							$res['fvf'] = $ilance->currency->format($result['final'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$res['finalprice'] = $result1['binamount'];
							if($result1['qty'] > 1)
							$res['qty'] = '<b>('.$result1['qty'].')</b>';
							else
							$res['qty'] = '';
							$res['fvf'] = $ilance->currency->format($result1['binamount'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						// Total Final price
						$totfinal[] = $res['finalprice'];
						
						//$res['seller_fee'] = $ilance->currency->format($resultpjt['fvf'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res['seller_fee'] = $ilance->currency->format($resultinvoice['newfvf'],$ilconfig['globalserverlocale_defaultcurrency']);
						$res['listing_fee'] = $ilance->currency->format($listfeetotal,$ilconfig['globalserverlocale_defaultcurrency']);
						/*if($result['bidamount'] != '')
						{
							
							 $res['net_consignor1'] = $result['final'] - ($res['seller_fee'] + $resultpjt['fvf'] + $listfeetotal);
							 $res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 
						}
						if($result1['binamount'] != '')
						{
							
							$res['net_consignor1'] = $result1['binamount'] - ($res['seller_fee'] + $resultpjt['fvf'] + $listfeetotal);
							$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						if($result['bidamount'] == '' AND $result1['binamount'] == '' )
						{
							$res['net_consignor1'] =  ($res['seller_fee'] + $resultpjt['fvf'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = '<span class="red">US$ -'.$res['net_consignor1']. '</span>';
							else
							$res['net_consignor'] = 'US$0.00';
						}
*/
					if($result['bidamount'] != '')
						{							
							 $res['net_consignor1'] = $result['final'] - ( $resultinvoice['newfvf'] + $listfeetotal);
							 if($res['net_consignor1'] > 0)
							 $res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
							 
						}
						if($result1['binamount'] != '')
						{
							
							$res['net_consignor1'] = $result1['binamount'] - ( $resultinvoice['newfvf'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
						}
						if($result['bidamount'] == '' AND $result1['binamount'] == '' )
						{
							$res['net_consignor1'] =  ($resultinvoice['newfvf'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = '- US$'.$res['net_consignor1']. '.00';
							else
							$res['net_consignor'] = 'US$0.00';
						}
						$test[] = $res['net_consignor1'];
						//$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
						//$res['net_consignor'] = 'US$'.$res['net_consignor1'];
					}
				  		
					if($res['Site_Id'] == '0')
					{
					  $res['Site_Id'] ='GC';
					  $res['Title'] = $res['Title'];
					  $res['stateid'] = $res['coin_id'];
					}
					else
					{
					$sitesel = $ilance->db->query("
					SELECT site_name FROM ".DB_PREFIX."affiliate_listing				
					WHERE id = '".$res['Site_Id']."'					
					");
						$siteres = $ilance->db->fetch_array($sitesel, DB_ASSOC);
					 $res['Site_Id'] =$siteres['site_name'];
					 $res['Title'] = $res['Title'];
					$res['stateid'] = $res['coin_id'];
					}										
					$statement[] = $res;
					$row_count++;
				  }
				  // Advance Calculateion
				  
				 	
				  $advanceselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "user_advance WHERE statusnow = 'paid' AND user_id ='".$_SESSION['ilancedata']['user']['userstatement']."'");
				  $advanceres = $ilance->db->fetch_array($advanceselect);
				  // Feb 23 for consignor statement changes 
				  $sum_inset = array_sum($totins);
				  $sum_finalvaluefe = array_sum($totfvf);
				  $sum_totfinalval = array_sum($totfinal);
				  $newnettotal = $sum_totfinalval - $sum_finalvaluefe - $sum_inset;				  
				  //$totnet_consignor = $ilance->currency->format(array_sum($test),$ilconfig['globalserverlocale_defaultcurrency']);
				  
				  
				  $totnet_consignor = 'US$'.$newnettotal;
				  //$totseller_fee = $ilance->currency->format(array_sum($test1),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totseller_fee = $ilance->currency->format(array_sum($totfvf),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totlisting_fee = $ilance->currency->format(array_sum($totins),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totfvf = $ilance->currency->format(array_sum($totfinal),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totbinamount = $ilance->currency->format(array_sum($test4),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totbidamount = $ilance->currency->format(array_sum($test5),$ilconfig['globalserverlocale_defaultcurrency']);
				  $totbids = array_sum($bidtot);				
				  $total_advance = $ilance->currency->format($advanceres['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
				  
				 // murugan changes on july 12
				 
				  $mis_totdebit = array_sum($misdebit);
				  $mis_totcredit = array_sum($miscredit);
				  	$miscellan = array_sum($miscell);
					$mis_total =  $mis_totdebit - $mis_totcredit;
					if($mis_total > 0)
					{
					$tot_mis = $ilance->currency->format($mis_total,$ilconfig['globalserverlocale_defaultcurrency']);
					}
					else
					{
					$tot_mis = 'US$'.number_format(abs($mis_total), 2, '.', '');
					}
				 
				  // murugan FEB 23
				  				
				  //$lastamountvalue = array_sum($test) - $advanceres['amount'];
				  
					// murugan changes on july 12
					
				  //$lastamountvalue = $newnettotal - $advanceres['amount'];
				  
				  $lastamountvalue = $newnettotal - $advanceres['amount'] - $mis_totcredit + $mis_totdebit;
				  //$lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
				   if($lastamountvalue > 0)
							 $lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							  $lastamount = '- US$'.abs($lastamountvalue). '';
				  //$lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
				 // $lastamount = 'US$'.$lastamountvalue;
				  $statecount = '('.$listcount.' Items), will settle on '.$settledate .' ('.$lastamount.')';
				 
		 }
		 else
		 {
		 	$show['no_statement'] = true;
		 }
		 $print = '<span style="cursor:pointer; margin-left: 450px;" class="blue" onClick="window.open(\'consigner_pdf.php?date='.$ilance->GPC['date_down'].'&user_id='.$_SESSION['ilancedata']['user']['userid'].'\',\'mywindow\',\'width=400,height=200,scrollbars=yes\')">Print</span>';
										
		 
		$pprint_array = array('tot_mis','user_id','date_down','lastamount','total_advance','statecount','date1','date','totbids','totbidamount','totbinamount','totfvf','totlisting_fee','totseller_fee','totnet_consignor','daylist','monthlist','yearlist','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','print');
		$ilance->template->fetch('main', 'consigner_statement.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('statement'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
    }
	else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['preferences'] . print_hidden_fields(true, array(), true)));
	exit();
}
?>		