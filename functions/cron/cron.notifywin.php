<?php














if (!isset($GLOBALS['ilance']->db))
{
    die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}


//require_once('../config.php');

($apihook = $ilance->api('cron_new_notify_start')) ? eval($apihook) : false;
require_once(DIR_CORE . 'functions_shipping.php');


/* vijay  for bug 6367 - Notify Auction Win report  * start 1.2.16 */	

$flag=0;




$notifysql="SELECT user_id,country,username,issalestaxreseller,state
FROM " . DB_PREFIX . "users AS u
WHERE u.user_id > '1'and u.notifyauction = '1'";

$sql_uid= $ilance->db->query($notifysql);



	if($ilance->db->num_rows($sql_uid) > 0)
	{
		
		$messagebody .= "*********************************"."\n";
		$messagebody .= "Notify Win Auction Details"."\n";
		$messagebody .= "*********************************"."\n";	
		
		
		while($sql_uid_coin = $ilance->db->fetch_array($sql_uid))











		{
			$giv_user_id=$sql_uid_coin['user_id'];
						

			$giv_countryid=$sql_uid_coin['country'];
			
			$state=$sql_uid_coin['state'];
						
			$ilance->tax = construct_object('api.tax');		
				
	 							
			$sql_regardlist = $ilance->db->query("			
				SELECT u.user_id, u.first_name, u.last_name,u.username,u.email,u.phone,i.invoiceid,i.projectid,i.istaxable,
				i.amount,i.taxinfo,i.invoicetype
				FROM " . DB_PREFIX . "invoices i
				JOIN " . DB_PREFIX . "users u ON u.user_id = i.user_id
				WHERE u.user_id = '" . $giv_user_id."'
				AND i.status = 'unpaid'
				AND NOT i.combine_project
				AND i.isfvf !=1
				AND i.isif !=1
				AND i.Site_Id !=1
				AND i.isbuyerfee !=1
				AND i.isenhancementfee !=1
				", 0, null, __FILE__, __LINE__);
					
					
			$invcount = $ilance->db->num_rows($sql_regardlist);
			
			if($invcount > 0)
			{	
						
				$totqty=$totalamountlistcal=$buyerfeecal=0;
				while($res_regardlist = $ilance->db->fetch_array($sql_regardlist))
				{  
					$username = $res_regardlist['username'];
					$email =$res_regardlist['email'];
					
																									
					$buy = $ilance->db->query("SELECT qty FROM " . DB_PREFIX . "buynow_orders
												WHERE invoiceid = '".$res_regardlist['invoiceid']."'
												AND buyer_id = '".$giv_user_id."'");
					if($ilance->db->num_rows($buy)>0)
					{
						$resbuy = $ilance->db->fetch_array($buy);
						$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
						WHERE coin_id = '".$res_regardlist['projectid']."'");
						$temp=$ilance->db->fetch_array($bids);						

						$coin_no_in_set=empty($temp['nocoin'])?1:intval($temp['nocoin']);
						$res_regardlist['qty'] = $resbuy['qty'];
						$totqty+= $res_regardlist['qty']*$coin_no_in_set;
						//echo 'if == '.$totqty.'<br/>';
					}
					else
					{
						//check 	nocoin  in ilance_coins for each coins
						$bids = $ilance->db->query("SELECT nocoin FROM " . DB_PREFIX . "coins
						WHERE coin_id = '".$res_regardlist['projectid']."'");
						$temp=$ilance->db->fetch_array($bids);		

						$res_regardlist['qty'] = 1;

						$totqty+= empty($temp['nocoin'])?1:intval($temp['nocoin']);
						//echo 'else == '.$totqty.'<br/>';
					}
					
						
						$show['listing'] = 0;
						$project_id = 0;
						
						if ($res_regardlist['projectid'] > 0)
						{
						$show['listing'] = 1;
						$project_id = $res_regardlist['projectid'];
						$projects[] = $res_regardlist['projectid'];
						}
						// tax check 
						$taxdetails = $res_regardlist['istaxable'];
						
						$buyfee_inv = $ilance->db->query("SELECT * FROM ".DB_PREFIX."invoices 
						WHERE projectid = '".$res_regardlist['projectid']."'
						AND user_id = '".$giv_user_id."'
						AND isbuyerfee = '1'");
						if($ilance->db->num_rows($buyfee_inv) > 0)
						{
							$res_buyfee = $ilance->db->fetch_array($buyfee_inv);
							$buyerfee1 = $res_buyfee['amount'];
							$totalamountlist1 = $res_regardlist['amount'] + $res_buyfee['amount'] ;
							$buyerfeecal+= $buyerfee1;
							$amountcal+= $res_regardlist['amount'];
							$totalamountlistcal+= $totalamountlist1;
							
						}
						else
						{
							
							$buyerfee1 = 0;
							$totalamountlist1 = $res_regardlist['amount'];
							$amountcal+= $res_regardlist['amount'];
							$totalamountlistcal+= $totalamountlist1;
							
						}				
							
							$taxinfolist = $res_regardlist['taxinfo'];
							$invoicetype = $res_regardlist['invoicetype'];
							
							$totalqty=$totqty;
							
							$totalamtlistcal=$totalamountlistcal;
							
							$regardlist[] = $res_regardlist;
																				
				
					}
			
					$itemcount= $totalqty;
					
					$totalamount_pending_in=$totalamtlistcal;
					
															
					$shippment_nethod_pulldown = print_shippment_nethod_pulldown($projects,$selected=0,'shipper_id',$totalqty,$giv_user_id,$giv_countryid,$totalamount_pending_in);

					$shipper_drop_down = $shippment_nethod_pulldown['html'];
					
																
					$amounttotal =$totalamtlistcal+$shipper_drop_down;
					
					$issalestaxreseller=$sql_uid_coin['issalestaxreseller'];	
					
					if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] >= $amounttotal 
					AND $sales_tax_reseller!='1')
					 {		 	
							
						$state=$sql_uid_coin['state'];		
						$taxinfo = 'Sales Tax ('.$ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0).'%, '.$state.')';
						

						 
						$taxinfonew = $ilance->tax->fetch_taxdetails($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, $amounttotal, 'buynow', 0);
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						




						$show['taxes'] = 1;
					}
					else if ($ilance->tax->is_taxable($giv_user_id, $invoicetype) AND $ilconfig['staffsettings_max_tax_limit'] <= $amounttotal)
					{	
						


						
						$taxinfo = 'Sales Tax Not Applicable ('.$state.'over $1,500)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						
						$taxinfonew = 0.00;
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);
						
					}
					else 
					{	
														
						$taxinfo = 'Sales Tax Not Applicable (Out of State)';
						$taxamount1 = $ilance->tax->fetch_amount($giv_user_id, 0, 'buynow', 0);
						$taxamount =  $ilance->currency->format($taxamount1,$ilconfig['globalserverlocale_defaultcurrency']);				
						$taxinfonew = 0.00;
					}
								
					$buyerfe = $buyerfeecal;
					$buyerfee = $ilance->currency->format($buyerfe,$ilconfig['globalserverlocale_defaultcurrency']);
					$taxamount1=empty($taxamount1)?"0":$taxamount1;

					if ($taxamount1 > 0)
					{	

					$totalamount = $ilance->currency->format(($amounttotal + $taxamount1), $ilconfig['globalserverlocale_defaultcurrency']);
					$totalamountnew = $amounttotal + $taxamount1;

					}
					else
					{

					$totalamount = $ilance->currency->format($amounttotal, $ilconfig['globalserverlocale_defaultcurrency']);
					$totalamountnew = $amounttotal;

					}
					

					$pending_in_amt=$totalamountnew;
					




					

					$pending_inv_amt=$ilance->currency->format($pending_in_amt,$ilconfig['globalserverlocale_defaultcurrency']);	
					
					
					$messagebody .= "Username: ". $username. "\n";
					$messagebody .= "Email: ". $email. "\n";
					$messagebody .= "Total No of Item Count :" . $itemcount. "\n";
					$messagebody .= "Total Amounts :" . $totalamount . "\n";
					//$messagebody .= "Total Amounts with shipping :" . $pending_inv_amt . "\n";
					$messagebody .= "*********************************"."\n";

					
					$flag++;
										
					$user_id=$giv_user_id;
					
			
			}
		
		}
		
			if($flag > 0)
			{
			
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->logtype = 'Notify Win Auction Details';
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
			$ilance->email->get('notifywin_auction');
			$ilance->email->set(array(
			'{{message}}' => $messagebody,
			));
			$ilance->email->send();	
			
			
			 // email admin
			$ilance->email->logtype = 'Notify Win Auction Details';
			$ilance->email->mail = $ilconfig['globalserversettings_siteemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('notifywin_auction');
			$ilance->email->set(array(
			'{{message}}' => $messagebody,
			));
			$ilance->email->send();
			
			
			 // email admin
			$ilance->email->logtype = 'Notify Win Auction Details';
			$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];   
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('notifywin_auction');
			$ilance->email->set(array(
			'{{message}}' => $messagebody,
			));
			$ilance->email->send();
							

			}	
			
			unset($messagebody);
		
	}
	exit();


/* vijay  ends  */




function print_shippment_nethod_pulldown($projects,$selected,$name,$totqty=0,$giv_user_id,$giv_countryid,$totalamount_pending_in)
	{
	global $ilance,$ilconfig;
	$first_shipment=false;
	$only_buynow=true;
	$html='';
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."projects where project_id in (".implode(",",$projects).") and filtered_auctiontype='regular'");
	if($ilance->db->num_rows($sql)>0)
	$only_buynow=false;
	//echo '<!--'.$totqty.'-->';
	if($ilconfig['staffsettings_free_first_shipping'] ==1 AND $giv_countryid==500 and !$only_buynow)
	{
	
	
	$sql=$ilance->db->query("select * from ".DB_PREFIX."invoice_projects where buyer_id='".$giv_user_id."'AND status IN('paid')");
	if($ilance->db->num_rows($sql)<1 AND $ilance->db->num_rows($sql) ==0)
	{
	$first_shipment=true;
	}
	}
		
	//shipping for INTERNATIONAL CLIENTS 
	
	
	if($giv_countryid!=500)
	{			
		///invoice  over $10,000	
		if( $totalamount_pending_in >= '5000.00')
		{
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='23' and visible=1");
			if($ilance->db->num_rows($sql))
			{
			
				while($line=$ilance->db->fetch_array($sql))
				{
				
					if($totqty>$line['maxitem_count'])
					{
					$international_extra_morethen_n_coins=$line['addedfee_above_maxitem_count'];
					}
					$html.=$line['basefee']+ ($line['addedfee'] *$totqty) +$international_extra_morethen_n_coins;

				}
			 
			}
		}
		else if( $totalamount_pending_in >= '1000.00')
		{
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where shipperid='22' and visible=1");
			if($ilance->db->num_rows($sql))
			{
				
				while($line=$ilance->db->fetch_array($sql))
				{

				$selected='22';
					if($line['shipperid']==$selected)
					{
					if($totqty>$line['maxitem_count'])
					{
					$international_extra_morethen_n_coins=$line['addedfee_above_maxitem_count'];
					}
					$html.=$line['basefee']+ ($line['addedfee'] *$totqty) +$international_extra_morethen_n_coins;

					}
					
				}

			}
		}

		else
		{	
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='0' and visible=1 order by carrier desc");
				if($ilance->db->num_rows($sql))
				{

					while($line=$ilance->db->fetch_array($sql))
					{
						
						
						$selected='21';
						if($line['shipperid']==$selected)
						{
						if($totqty>$line['maxitem_count'])
						{
							$international_extra_morethen_n_coins=$line['addedfee_above_maxitem_count'];
						}
						$html.=$line['basefee']+ ($line['addedfee'] *$totqty) +$international_extra_morethen_n_coins;
						}

					}
				}
			}
			
	}
	//end of shipping for INTERNATIONAL CLIENTS 
	
	//shipping method for US Clients
	
	else
	{
       //invoice  over $10,000	
		if( $totalamount_pending_in > '10000.00')
		{
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid='25' and visible=1");
			if($ilance->db->num_rows($sql))
			{
				
				while($line=$ilance->db->fetch_array($sql))
				{
					 $html.=$line['basefee']+ ($line['addedfee'] * $totqty);
				 }
				
			}
		}  
		//invoice  over $2,000,
		else if( $totalamount_pending_in > '1000.00')
		{
			
		   //may2 new change add order by basefee asc
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where  shipperid in('25','27') and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
				while($line=$ilance->db->fetch_array($sql))
				{
									   
					$selected='27';
					
					if($line['shipperid']==$selected)
					{						
					 $html.=$line['basefee']+ ($line['addedfee'] *$totqty);
					}	
											
						









				}
				
			}
		}  

		else
		{	
		//new change apr19  order by carrier to basefee asc
		$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic='1' and visible=1 order by basefee asc");
			if($ilance->db->num_rows($sql))
			{

				while($line=$ilance->db->fetch_array($sql))






				{
				
				//	Shipping is free for your first auction purchase (U.S. only)
			   
					$selected='26';
				
					if($line['shipperid']==$selected){
						















						$html.=$line['basefee']+ ($line['addedfee'] *$totqty);
						  
					}
					
				}
				
			}
		}
		
		if($first_shipment==true)
		{
		$html=$free_announce='0.00';	
		}
	}

	
	$shipping_add['html']=$html;
	
	
	return $shipping_add;
	}
($apihook = $ilance->api('cron_new_notify_end')) ? eval($apihook) : false;
        
log_cron_action('The notify email was successfully emailed to ' . SITE_EMAIL, $nextitem);
?>
