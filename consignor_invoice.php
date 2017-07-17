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
        'portfolio',
        'preferences',
        'selling',
        'search'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
        'inline_edit',
	'flashfix',
	'jquery'
);
// #### define top header nav ##################################################
$topnavlink = array(
        'preferences'
);
// #### setup script location ##################################################
define('LOCATION', 'preferences');
// #### require backend ########################################################
require_once('./functions/config.php');
 $show['widescreen'] = true;
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[preferences]" => $ilcrumbs["$ilpage[preferences]"]);
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
 	//karthik may04
	 $sqlcat_user_detail = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "consignments
								WHERE user_id = '". $_SESSION['ilancedata']['user']['userid']."'
								
								");
								        if($ilance->db->num_rows($sqlcat_user_detail) > 0)
										{
                                               while($res_cong = $ilance->db->fetch_array($sqlcat_user_detail))
												{
												     $consgn_id= $res_cong['consignid'];
												}
									
										}
 		$area_title = 'Conginor Statement';
		$page_title = SITE_NAME . ' - ' . 'Statement';
		$user_id = $_SESSION['ilancedata']['user']['userid'];
			
		$date_down = '<select name="date_down" id="date_down" >
								              <option value="" selected="selected">Select</option>';
										$con_date = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "consign_statement 										
										WHERE seller_id = '".$_SESSION['ilancedata']['user']['userid']."'
										AND end_date != '0000-00-00'										
										GROUP BY date(end_date)
										");
										$datecount = 0;							            
							            if($ilance->db->num_rows($con_date) > 0)
										{
										      
											    while($res_date = $ilance->db->fetch_array($con_date))
												{
												
												  $date_coin = explode('-',$res_date['end_date']);
										          $date_day = explode(' ',$date_coin[2]);
									              $month_name = $date_day[0].'-'.$date_coin[1].'-'.$date_coin[0]; 
												  $month_namev = $date_coin[0].'-'.$date_coin[1].'-'.$date_day[0];
											
																							
												$con_date_co = $ilance->db->query("
												SELECT COUNT(*) AS endcount
												FROM " . DB_PREFIX . "consign_statement 
												WHERE seller_id = '".$_SESSION['ilancedata']['user']['userid']."' 
												AND date(end_date) = '".$month_namev."'
												
										
												");
												$res_date_co = $ilance->db->fetch_array($con_date_co);
												$item_count = $res_date_co['endcount'];
												  
												 $date_down.='<option value="'.$month_namev.'">'.$month_name.' <b>('.$item_count.' items)</b></option>';												
												 $datecount++;
												}
										}
										
										$date_down.='</select>';
		
		
		$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';
						$day = date('d')-1;
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';
				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
				
				$year = date("Y");
				for ($k = $year;$k > $year-5;$k--) 
				{
   				$s = ($k == $year)?' selected':'';
   					$yearlist .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
				}
					/*$year = date('Y') - 5;
					for($k=$year; $k <=date("Y"); )
					{
					$year++;
					$yearlist .= "<option value='$k' selected>$k</option>";
					}*/
				
				$yearlist .='</select>';
				// Date Month Year End
		
		if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
		{
		  
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
		  /* $select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd 				
					WHERE co.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(co.End_Date) = '".$ilance->GPC['date_down']."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no
					AND	cc.coin_series_denomination_no=cd.denomination_unique_no
					GROUP BY co.coin_id
					ORDER BY cd.denomination_sort,
					cs.coin_series_sort,
					cc.coin_detail_year
					
					");	*/
			$select = $ilance->db->query("
					SELECT * FROM ". DB_PREFIX ."consign_statement
					WHERE seller_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(end_date) = '".$ilance->GPC['date_down']."'
			");
		
					
		}
		else if (isset($ilance->GPC['date']))
		{
		$show['no_statement'] = false;
		$dateexp = explode('-',$ilance->GPC['date']);
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
			/*$select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd 				
					WHERE co.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(co.End_Date) = '".$ilance->GPC['date']."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no
					AND	cc.coin_series_denomination_no=cd.denomination_unique_no
					GROUP BY co.coin_id
					ORDER BY cd.denomination_sort,
					cs.coin_series_sort,
					cc.coin_detail_year
					
					");	*/
			$select = $ilance->db->query("
					SELECT * FROM ". DB_PREFIX ."consign_statement
					WHERE seller_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(end_date) = '".$ilance->GPC['date']."'
			");
		}
		else
		{
		
		$show['no_statement'] = false;
		
		$date1 = date('m-d-Y');
		$date = DATETODAY;
		$settledate = date(date('m') + 1 . '-' . date('d') . '-' . date('Y'));
		$/*select = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."coins co,
					" . DB_PREFIX . "catalog_coin cc, 
					" . DB_PREFIX . "catalog_second_level cs, 
					" . DB_PREFIX . "catalog_toplevel cd 			
					WHERE co.user_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(co.End_Date) = '".$date."'
					AND co.pcgs=cc.PCGS 
					AND	cc.coin_series_unique_no=cs.coin_series_unique_no
					AND	cc.coin_series_denomination_no=cd.denomination_unique_no
					GROUP BY co.coin_id
					ORDER BY cd.denomination_sort,
					cs.coin_series_sort,
					cc.coin_detail_year
					");*/
			$select = $ilance->db->query("
					SELECT * FROM ". DB_PREFIX ."consign_statement
					WHERE seller_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(end_date) = '".$date."'
			");
		
		}
		
		
		//new 
		$select2 = $ilance->db->query("
					SELECT * FROM ". DB_PREFIX ."consign_statement
					WHERE seller_id = '".$_SESSION['ilancedata']['user']['userid']."'
					AND date(end_date) = '".$date."'
			");
	    while ($res2 = $ilance->db->fetch_array($select2))
                  {
				  $arr[] = $res2['item_id'];
				  }		
				  
				  
				$new_id = array_unique($arr);
				
				//$new_count = array_count_values($arr);
				
				echo '<pre>';
				
				print_r($new_id);
				
				
				
				echo '</pre>';  
			
		$listcount = $ilance->db->num_rows($select);
		
		if ($ilance->db->num_rows($select) > 0)
         {
				$show['statement'] = true;									
				$row_count = 0;				
				
				for($i=0;$i<count($new_id);$i++)
				{
				
				 
				
				
				    $select3 = $ilance->db->query("
					SELECT * FROM ". DB_PREFIX ."consign_statement
					WHERE item_id = '".$new_id[$i]."' AND date(end_date) = '".$date."'
					group by item_id
					
			       ");
				   
				    $select4 = $ilance->db->query("
					SELECT count(item_id) as cou,item_id FROM ". DB_PREFIX ."consign_statement
					WHERE item_id = '".$new_id[$i]."' AND date(end_date) = '".$date."'
					
			       ");
				   $res4 = $ilance->db->fetch_array($select4);
				   
				  // echo $res4['item_id'].'-----'.$res4['cou'].'<br>';
                   while ($res = $ilance->db->fetch_array($select3))
                  {
				  
				  
				  
				   	if($res['item_id'] == '0')
					{
						$res['bids'] = '-';
						$res['fvf'] = '-';
						$res['bidamount'] = '-';
						$res['binamount'] = '-';
						$res['seller_fee'] = '-';
						$res['listing_fee'] = '-';
						$res['net_consignor'] = '-';
						$res['qty'] = '';
						
					}
					else
					{
						
						
						
						$result = $ilance->db->fetch_array($selectbid, DB_ASSOC);
						$result1 = $ilance->db->fetch_array($selectbin, DB_ASSOC);
						$resultpjt = $ilance->db->fetch_array($selectpjt, DB_ASSOC);
						$resultinvoice = $ilance->db->fetch_array($selectinvoice, DB_ASSOC);
						// // miscellaneous Calculatation Murugan on jun 4 
						$misselect = $ilance->db->query("SELECT sum(amount) as amount FROM ". DB_PREFIX ."invoices
				  						WHERE user_id ='".$_SESSION['ilancedata']['user']['userid']."'
				  						AND projectid = '".$res['project_id']."'
										AND invoicetype = 'credit'
				  						AND ismis = 1 ");						
						
						
						if($ilance->db->num_rows($misselect) > 0) 
						{
							$resmis = $ilance->db->fetch_array($misselect);							
							$miscell[] = $resmis['amount'];
							$misamt = $ilance->currency->format($resmis['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$miscell[] = 0;
							$misamt = $ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']);
						}
						
						$misselectdebit = $ilance->db->query("SELECT sum(amount) as amount FROM ". DB_PREFIX ."invoices
				  						WHERE user_id ='".$_SESSION['ilancedata']['user']['userid']."'
				  						AND projectid = '".$res['project_id']."'
										AND invoicetype = 'debit'
				  						AND ismis = 1 ");
						
						if($ilance->db->num_rows($misselectdebit) > 0) 
						{
							$resmisdb = $ilance->db->fetch_array($misselectdebit);							
							$miscelldb[] = $resmisdb['amount'];
							$misamtdb = $ilance->currency->format($resmisdb['amount'],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$miscelldb[] = 0;
							$misamtdb = $ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']);
						}
						
						// murugan changes on jun 2 
						
						$selectcoin = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coins			
														WHERE coin_id = '".$res['item_id']."'														
														");
														
						$resultcoin  = $ilance->db->fetch_array($selectcoin, DB_ASSOC);
						
						if($resultcoin['Certification_No'] != '')
						{
						$show['certify'] = true;
						}
						if($resultcoin['Alternate_inventory_No'] != '')
						{
						$show['inventory'] = true;
						}
						$res['Certification_No'] = $resultcoin['Certification_No'];
						$res['Alternate_inventory_No'] = $resultcoin['Alternate_inventory_No'];
						
						
						
						
						
						
						// Total Amount (insertionfee , bold,highlight,featured)
						$listfeetotal = $res['insertion_fee'] + $res['featured_fee'] + $res['highlite_fee'] + $res['bold_fee'];						
						$totins[] = $res['insertion_fee'] + $res['featured_fee'] + $res['highlite_fee'] + $res['bold_fee'];
						$res['bids'] = $res['bids'];
						$bidtot[] = $res['bids'];
						
						$totfvf[] = $res['seller_fee'];
						if($res['auction_price'] != '0')
						{						
							//if($res['bidamount'] != '')
							//$test5[] = $result['bidamount'];
							//$res['bidamount'] = $ilance->currency->format($result['bidamount'],$ilconfig['globalserverlocale_defaultcurrency']);
							$test5[] = $res['auction_price'];
							$res['bidamount'] = $ilance->currency->format($res['auction_price'],$ilconfig['globalserverlocale_defaultcurrency']);
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
						if($res['buynow_price'] != '0')
						{	
						
						
						
						$test4[] = $res['buynow_price'];
						
										
							
							$res['binamount']  = $ilance->currency->format($res['buynow_price'],$ilconfig['globalserverlocale_defaultcurrency']);
							
						}
						else
						{
						  	$res['binamount']  = '0.00';
						}
						if($res['sold_price'] != '0')
						{
							$res['finalprice'] = $res['sold_price'];
							if($res4['cou'] > 1)
							{	
							
							
							$alldata = fetch_cons_state($res['item_id'],$date);	
							$exp = explode('|',$alldata);				
							$res['qty'] = '<b>('.$exp[0].')</b>';
							}
							else
							{
							$res['qty'] = '';
							}
							
							
							$res['fvf'] = $ilance->currency->format($exp[1],$ilconfig['globalserverlocale_defaultcurrency']);
						}
						else
						{
							$res['finalprice'] = 0;							
							$res['fvf'] = $ilance->currency->format(0,$ilconfig['globalserverlocale_defaultcurrency']);
						}
						// Total Final price
						$totfinal[] = $res['finalprice'];
						$seller_fee = ($res4['cou'] > 1) ? $exp[2] : $res['seller_fee'];
						$res['seller_fee'] = $ilance->currency->format($seller_fee,$ilconfig['globalserverlocale_defaultcurrency']);
						$res['listing_fee'] = $ilance->currency->format($listfeetotal,$ilconfig['globalserverlocale_defaultcurrency']);
						
						/*if($result['bidamount'] != '')
						{							
							 $res['net_consignor1'] = $res['sold_price'] - ( $res['seller_fee'] + $listfeetotal);
							 if($res['net_consignor1'] > 0)
							 $res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
							 
						}
						
						if($result1['binamount'] != '')
						{
							
							$res['net_consignor1'] = $result1['binamount'] - ( $res['seller_fee'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
						}
						
						if($result['bidamount'] == '' AND $result1['binamount'] == '' )
						{
							$res['net_consignor1'] =  ($res['seller_fee'] + $listfeetotal);
							if($res['net_consignor1'] > 0)
							$res['net_consignor'] = '- US$'.$res['net_consignor1']. '.00';
							else
							$res['net_consignor'] = 'US$0.00';
						}*/
							$res['net_consignor1'] = ($res4['cou'] > 1) ? $exp[4] : $res['sold_price'] - ($seller_fee + $listfeetotal);
							 if($res['net_consignor1'] > 0)
							 $res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
							 else
							 $res['net_consignor'] = '- US$'.abs($res['net_consignor1']). '';
						$test[] = $res['net_consignor1'];
						//$res['net_consignor'] = $ilance->currency->format($res['net_consignor1'],$ilconfig['globalserverlocale_defaultcurrency']);
					}
				  		$res['Site_Id'] = 0;
					if($res['Site_Id'] == '0')
					{
					  $res['Site_Id'] ='GC';
					  $res['Title'] = '<a href="'.$ilpage['merch'].'?id='.$res['item_id'].'">'.$res['item_title'].'</a>';
					  $res['stateid'] = '<a href="'.$ilpage['merch'].'?id='.$res['item_id'].'">'.$res['item_id'].'</a>';
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
				  
				}  
				  // Advance Calculateion
				  
				 	
				  $advanceselect = $ilance->db->query("SELECT sum(amount) as amount FROM " . DB_PREFIX . "user_advance WHERE statusnow = 'paid' AND user_id ='".$_SESSION['ilancedata']['user']['userid']."' ");
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
				  
				  $miscal = array_sum($miscell) - array_sum($miscelldb);
				  $tot_mis = $ilance->currency->format(array_sum($miscal),$ilconfig['globalserverlocale_defaultcurrency']);
				  // murugan FEB 23
				  $miscellan = array_sum($miscell);			
				  //$lastamountvalue = array_sum($test) - $advanceres['amount'];
				 $lastamountvalue = $newnettotal - $advanceres['amount'] + $miscal ;
				  //$lastamount = $ilance->currency->format($lastamountvalue,$ilconfig['globalserverlocale_defaultcurrency']);
				  $lastamount = 'US$'.$lastamountvalue;
				  $statecount = '('.$listcount.' items) will settle on '.$settledate .' ('.$lastamount.')';
				 
		 }
		 else
		 {
		 	$show['no_statement'] = true;
		 }
		 //karthik may 04
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