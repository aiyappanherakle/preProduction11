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
	'administration',
	'shipping'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
        'tabfx',
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
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ajax_invoice_items' )
{
	
if(isset($ilance->GPC['invoice_id']) and $ilance->GPC['invoice_id']>0)
{
	
{

	$query=$ilance->db->query("select combine_project,user_id from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice_id']."'");	
	if($ilance->db->num_rows($query))
	{
	$html.='<table>';
		while($line=$ilance->db->fetch_array($query))
		{
			$invoices=explode(",",$line['combine_project']);
			if(count($invoices)==0)
			{
				$invoices=array($ilance->GPC['invoice_id']);
			}
			$buyer_id=fetch_user('username',$line['user_id']);
			$html.='<tr><td>Buyer</td><td>'.$buyer_id.'</td></tr>';	
		foreach($invoices as $invoice_id)
		{
		$query=$ilance->db->fetch_array($ilance->db->query("select projectid from ".DB_PREFIX."invoices where invoiceid='".$invoice_id."' limit 1"));	
		$items_list[]=$query;
		$html.='<tr><td>Item id </td><td>'.$query['projectid'].'</td></tr>';	
		}
		
		unset($items_list);
		}
		echo $html.='</table>';
		exit;
		
	}
	echo '';exit;
}
}exit;
}
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'shipping_by_invoice')
{

		
		
if(isset($ilance->GPC['tracking_no']) and strlen($ilance->GPC['tracking_no'])<1)
{
print_action_failed("Tracking number cannot be empty");
							exit();
								
	
}
	if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}

	$query=$ilance->db->query("select combine_project,user_id from ".DB_PREFIX."invoices where invoiceid='".$ilance->GPC['invoice_id']."'");	
	if($ilance->db->num_rows($query))
	{
	$html.='<table>';
		while($line=$ilance->db->fetch_array($query))
		{
			$invoices=explode(",",$line['combine_project']);
			if(count($invoices)==0)
			{
				$invoices=array($ilance->GPC['invoice_id']);
			}
		$first_name = fetch_user('first_name',$line['user_id']);
		$last_name = fetch_user('last_name',$line['user_id']);
		$buyer_email= fetch_user('email',$line['user_id']);
	
		foreach($invoices as $invoice_id)
		{
		$query=$ilance->db->fetch_array($ilance->db->query("select projectid from ".DB_PREFIX."invoices where invoiceid='".$invoice_id."' limit 1"));	
		$ilance->GPC['email']='NO';
		$seller_id=fetch_coin_table('user_id',$query['projectid']);
		$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "shippnig_details
                                (coin_id, cust_id,buyer_id, shipper_id, track_no, shipment_date, email,invoice_id,final_invoice_id)
                                VALUES (
								'" . $ilance->db->escape_string($query['projectid']) . "',
                                '" . $ilance->db->escape_string($seller_id) . "',
								'" . $ilance->db->escape_string($line['user_id']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['shippserviece']) . "',
								'" . $ilance->db->escape_string($ilance->GPC['tracking_no']) . "',
								'".$ilance->db->escape_string($validdate)."',									
								'" . $ilance->db->escape_string($ilance->GPC['shipping_email']) . "',
								'" . $ilance->db->escape_string($invoice_id) . "',
								'" . $ilance->db->escape_string($ilance->GPC['invoice_id']) . "'
                                )
                        ");
                        
		}
		unset($items_list);
		}
	}
		
if(isset($ilance->GPC['shipping_email']) and $ilance->GPC['shipping_email']=='YES')
		{
			// #### START EMAIL ############################################
                $sql_query=  $ilance->db->query("SELECT * FROM " . DB_PREFIX . "shippers where shipperid='".$ilance->GPC['shippserviece']."'");	
					while ($result = $ilance->db->fetch_array($sql_query, DB_ASSOC))
                        {				
						$shipping_service_name = $result['title'];
						$shipper_id = $result['shipper_id'];
						
						}
						
						$html.='https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1='.$ilance->GPC['tracking_no'];
																	
						if($shipper_id==35)
						{
						$html.='https://www.fedex.com/fedextrack/WTRK/index.html?action=track&trackingnumber='.$res['track_no'];
						}
						
                $existing = array(
									'{{first_name}}'=>$first_name,
									'{{last_name}}'=>$last_name,
									'{{shipping_service_name}}'=>$shipping_service_name,
									'{{tracking_no}}'=>$ilance->GPC['tracking_no'],  		
									'{{emailaddress}}' => $buyer_email,
									'{{invoiceid}}' => $ilance->GPC['invoice_id'],   
									
                );
        
                // #### email admin
                $ilance->email = construct_dm_object('email', $ilance);
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_user_slng(1);
			    $ilance->email->get('inform_shipping_tracking_no_to_admin');		
                $ilance->email->set($existing);
                $ilance->email->send();
                
                // #### email winning bidder
                $ilance->email->mail = $buyer_email;
				$ilance->email->slng = fetch_user_slng(1);
                $ilance->email->get('inform_shipping_tracking_no');		
                $ilance->email->set($existing);
                $ilance->email->send();
				
				$existingtest = array(
									'{{first_name}}'=>$first_name,
									'{{last_name}}'=>$last_name,
									'{{shipping_service_name}}'=>$shipping_service_name,
									'{{tracking_no}}'=>$ilance->GPC['tracking_no'],
									'{{tracking_link}}'=>$html,
									'{{emailaddress}}' => $buyer_email,
									'{{invoiceid}}' => $ilance->GPC['invoice_id'],   
									
                );
				
				// #### Test email developer
                $ilance->email->mail = $ilconfig['globalserversettings_developer_email'];
				$ilance->email->slng = fetch_user_slng();
                $ilance->email->get('inform_shipping_tracking_no_new');		
                $ilance->email->set($existingtest);
                $ilance->email->send();
				
		
				
         	
		}
	 
						
					    print_action_success("The New Shipping was Added Successfully", $ilpage['shipping'] . '?cmd=shipping');
                        exit();
}

     //if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'promocode')
        
	   			
				  
				$hiddenfieldsubcmd = 'add-shipping';				
				$hiddendo = $hiddenid = '';
				$ilance->auction = construct_object('api.auction');
        		$ilance->auction_post = construct_object('api.auction_post');
				
				$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];

         // Date Month Year Starts Here 
				$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';

						$day = date('d');
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
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				
				$shipper = '';					
				$shipper .='<select name="shippserviece" id="shippserviece"><option value="" selected = "selected">Select Shipper</option>';
				
				$sql_query=  $ilance->db->query("SELECT *
                								FROM " . DB_PREFIX . "shippers order by visible desc,sort asc");	
					while ($result = $ilance->db->fetch_array($sql_query, DB_ASSOC))
                        {				
						$shipper .= "<option value='".$result['shipperid']."'>".$result['title']."</option>";
						}
						
				$shipper .='</select>';
				
				 $emailcheck = '<input type="radio" name="email" value="YES" checked="checked"/>Yes
									    <input type="radio" name="email" value="NO" />No';
			$itemid = $ilance->GPC['coin_id'];
			$coinid = $ilance->GPC['coin_id'];
			$buyerid = $ilance->GPC['buyer_id'];
			$sellerid = $ilance->GPC['seller_id'];
				// Date Month Year End  Here 
		
		 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-shippingdetail' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
         {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "shippnig_details
                                WHERE ship_id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");
                        
                        print_action_success("Selected Shipping Details is deleted Successfully", $ilpage['shipping'] . '?cmd=shipping');
                        exit();
         }
		 
		 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'shipping' )
		 {
		   
			 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'uploadcsv')
			 {
			    
				$column_names = array('coin_id', 'cust_id','shipper_id','track_no','shipment_date','email');
				
				
				if((!empty($_FILES['upload'])) && ($_FILES['upload']['error'] == 0))
				{						
					if($_FILES['upload']['type'] == 'application/vnd.ms-excel' || 'application/octet-stream' )
					{						
						if($_FILES['upload']['size'] > 1000000)
						{
							print_action_failed("We're sorry.  File you are uploading is bigger then 1MB.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
							exit();
						}
						else
						{
							$handle = fopen($_FILES['upload']['tmp_name'],'r');
							$row_count = 0;	
																	
							while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
			
							 { 							
							 	$row_count++;
								if ($row_count==1) continue;
							 	if(count($data) != count($column_names))
								{
								print_action_failed("We're sorry. CSV file is not correct. Number of columns in
 database and number of columns in file are not the same. Please fix the problem and retry your action.", $_SERVER['PHP_SELF'].'?module=lanceactivity');
							exit();
								}
								else
								{							    							
								$temp_data['coin_id'] = $data[0];								
								$temp_data['cust_id'] = $data[1];
								$temp_data['shipper_id'] = $data[2];
								$temp_data['track_no'] = $data[3];
								$temp_data['shipment_date'] = $data[4];
								$temp_data['email'] = $data[5];	
												
										
										 $ilance->db->query("
                                					INSERT INTO " . DB_PREFIX . "shippnig_details
                               						 (coin_id, cust_id, shipper_id, track_no, shipment_date, email)
                               						 VALUES (
                                				'" . $ilance->db->escape_string($temp_data['coin_id']) . "',
                               					'" . $ilance->db->escape_string($temp_data['cust_id']) . "',
                               					 '" . $ilance->db->escape_string($temp_data['shipper_id']) . "',
												'" . $ilance->db->escape_string($temp_data['track_no']) . "',
												'".$ilance->db->escape_string($temp_data['shipment_date'])."',									
								 				 '" . $ilance->db->escape_string($temp_data['email']) . "'
                                					)
                       						 ");
                        
										
									}
																								 						
							 }							
						  
						}							
					}
					
					else
					{
						print_action_failed("We're sorry.  Upload Only CSV file.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
						exit();
					}	
					
					fclose($handle);
					print_action_success("CSV File Pack importation success.  Changes reflected within the CSV email template have been successfully imported to the database.", $_SERVER['PHP_SELF']);
								exit();									
				}			
				else 
				{
				   
					print_action_failed("We're sorry.  This CSV file does not exist.  Please fix the problem and retry your action.", $_SERVER['PHP_SELF']);
					exit();							
				}
			
		}	
				
			 if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-shipping')
         	{
				//ship_id, item_id, cust_id, shipper_id, tack_no, shipment_date, email
                     
					   //print_r($ilance->GPC);
						if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}
						 $itemarray = explode(',', $ilance->GPC['itemid']);
						 $count = count($itemarray);
						 
						for($i = 0; $i < $count ; $i++ )
						{
					    $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "shippnig_details
                                (coin_id, cust_id,buyer_id, shipper_id, track_no, shipment_date, email,invoice_id,final_invoice_id)
                                VALUES (
                                '" . $ilance->db->escape_string($itemarray[$i]) . "',
								'" . $ilance->db->escape_string($ilance->GPC['sellerid']) . "',
								'" . $ilance->db->escape_string($ilance->GPC['buyerid']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['shippserviece']) . "',
								'" . $ilance->db->escape_string($ilance->GPC['track_no']) . "',
								'".$ilance->db->escape_string($validdate)."',									
								'" . $ilance->db->escape_string($ilance->GPC['email']) . "',
								'" . $ilance->db->escape_string($invoice_id) . "',
								'" . $ilance->db->escape_string($ilance->GPC['invoice_id']) . "'
                                )
                        ");
                        }
                        print_action_success("The New Shipping was Added Successfully", $ilpage['shipping'] . '?cmd=shipping');
                        exit();
                }
			
			
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippingdetail' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND empty($ilance->GPC['do']))
                {
                        $hiddenfieldsubcmd = 'update-shippingdetail';
                        $hiddendo = '<input type="hidden" name="do" value="update" />';
                        $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
                        $showdate['show']= 'datedisp';
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "shippnig_details
                                WHERE ship_id = '" . intval($ilance->GPC['id']) . "'
							
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
						//ship_id, item_id, cust_id, shipper_id, track_no, shipment_date, email
                                $res = $ilance->db->fetch_array($sql);
								$itemid = $res['coin_id'];
								$sellerid = $res['cust_id'];
								$buyerid = $res['buyer_id'];									
								$shipper_id = $res['shipper_id'];
								$track_no = $res['track_no'];
								$shipment_date = $res['shipment_date'];
								$email = $res['email'];
								
								if($email == 'YES')
								{
							
								  $emailcheck = '<input type="radio" name="email" value="YES" checked="checked"/>Yes
									    <input type="radio" name="email" value="NO"/>No';
								}
								else if($email == 'NO')
								{
								 $emailcheck = '<input type="radio" name="email" value="YES"/>Yes
									    <input type="radio" name="email" value="NO" checked="checked"/>No';
								}						
								
								$dateofbirth = $res['shipment_date'];
                                $dobsplit = explode('-', $dateofbirth);
                                $year = $dobsplit[0];
                                $dobmonth = $dobsplit[1];
                                $dobday = $dobsplit[2];
								
								$daylist ='<select name="day" id="day"><option value="">DATE</option>';
																	
									for($i=1; $i<=31; $i++)
									if($dobday == $i)
									$daylist .= "<option value='$i' selected>$i</option>";
									else
									$daylist .= "<option value='$i'>$i</option>";
								
								$daylist .='</select>';
								
								$monthlist ='<select name="month" id="month"><option value="">MONTH</option>';									
									
									for($j=1; $j<=12; $j++)
									
									if($dobmonth == $j)
									$monthlist .= "<option value='$j' selected>$j</option>";
									else
									$monthlist .= "<option value='$j'>$j</option>";
								
								
								$monthlist .= '</select>';
								
								$yearlist = '<select name="year" id="year"><option value="">YEAR</option>';
								
									
									for($k=date("Y"); $k<=date("Y")+5; $k++)
									if($year == $k)
									$yearlist .= "<option value='$k' selected>$k</option>";
									else
									$yearlist .= "<option value='$k'>$k</option>";
								
								$yearlist .='</select>';
                                
							$shipper = '';					
							$shipper .='<select name="shippserviece" id="shippserviece"><option value="" selected = "selected">Select Shipper</option>';
				
							$sql_query=  $ilance->db->query("SELECT *
                								FROM " . DB_PREFIX . "shippers order by visible desc,sort asc");	
							while ($result = $ilance->db->fetch_array($sql_query, DB_ASSOC))
                      		  {		
							  		if($shipper_id == $result['shipperid'])
									{
										$shipper .= "<option value='".$result['shipperid']."' selected='selected'>".$result['title']."</option>";
									}
									else
									{
									$shipper .= "<option value='".$result['shipperid']."'>".$result['title']."</option>";
									}		
									
							}
							$shipper .='</select>';
				
						 $emailcheck = '<input type="radio" name="email" value="YES" checked="checked"/>Yes
									    <input type="radio" name="email" value="NO" />No';
								
                                
                        }
                }
				
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippingdetail' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
                     
					   //ship_id, item_id, cust_id, shipper_id, track_no, shipment_date, email
					    if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
                		{
                        $validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
						}
						else
						{
						  $validdate = '0000-00-00';
						}				
						$ilance->db->query("
                                UPDATE " . DB_PREFIX . "shippnig_details
                                SET coin_id = '" . $ilance->db->escape_string($ilance->GPC['coin_id']) . "',
                                cust_id = '" . $ilance->db->escape_string($ilance->GPC['sellerid']) . "',
								buyer_id = '" . $ilance->db->escape_string($ilance->GPC['buyerid']) . "',
								shipper_id = '" . $ilance->db->escape_string($ilance->GPC['shippserviece']) . "',
								track_no = '" . $ilance->db->escape_string($ilance->GPC['track_no']) . "',
								shipment_date = '".$ilance->db->escape_string($validdate)."',
								email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'								
                                WHERE ship_id = '" . intval($ilance->GPC['id']) . "'
                        ");
                        
                        print_action_success("The Selected Shipping Detais Updated Successfully", $ilpage['shipping'] . '?cmd=shipping');
                        exit();         
                }
				
		}

		
		 
			$show['shipping'] = false ;
				$rows_count = 0;
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
				{
				 
				 $searchby = (isset($ilance->GPC['searchby']) AND !empty($ilance->GPC['searchby'])) ? $ilance->GPC['searchby'] : 'coin_id';
                 $searchkey = (isset($ilance->GPC['searchkey']) AND !empty($ilance->GPC['searchkey'])) ? $ilance->GPC['searchkey'] : '';
				 
				 $where = "WHERE ship_id != '' ";
				  if (!empty($searchkey) AND !empty($searchby))
                        {
                                $where .= "AND " . $searchby . " = '" . $searchkey . "'";
                        }
					
					 $sql_ship = $ilance->db->query("
                               SELECT *
                        	   FROM " . DB_PREFIX . "shippnig_details
                               $where
							   ORDER BY ship_id ASC
                        ");
				 	 $number = (int)$ilance->db->num_rows($sql_ship);
					  while ($result = $ilance->db->fetch_array($sql_ship, DB_ASSOC))
                        {
                              
								$firstname = fetch_user('first_name',$result['buyer_id']);
								$lastname = fetch_user('last_name',$result['buyer_id']);
								//murugan changes on jan 19
								$result['buyer_id'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$result['buyer_id'].'"">'.fetch_user('username', $result['buyer_id']).'</a>';
								
								$firstname = fetch_user('first_name',$result['cust_id']);
								$lastname = fetch_user('last_name',$result['cust_id']);
								////murugan changes on jan 19
								$result['seller_id'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$result['cust_id'].'"">'.fetch_user('username', $result['cust_id']).'</a>';
								
								// this fetch_shipper function written by murugan on Function File
								$result['shipper_id'] = fetch_shipper('title',$result['shipper_id']);
								
								$result['edit'] = '<a href="' . $ilpage['shipping'] . '?cmd=shipping&amp;subcmd=update-shippingdetail&amp;id=' . $result['ship_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                        $result['remove'] = '<a href="' . $ilpage['shipping'] . '?cmd=shipping&amp;subcmd=remove-shippingdetail&amp;id=' . $result['ship_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
								
								 $result['action'] = '<input type="checkbox" name="ship_id[]" value="' . $result['ship_id'] . '" id="ship_id_' . $result['ship_id'] . '" />';
								 $result['print'] = '<a href = "listpdf.php?id='.$result['ship_id'] .'">print</a>';
                                $shippinglist[] = $result;
                                $rows_count++;
						}   
				  
				}
				else
				{
				
				  $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
							
				  $scriptpageprevnext = $ilpage['shipping'].'?';
							 
				  if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				  {
					 $ilance->GPC['page'] = 1;
	              }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
							 
				$sql_ship = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "shippnig_details
						WHERE track_no != ''
                        ORDER BY ship_id ASC LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," .$ilconfig['globalfilters_maxrowsdisplaysubscribers']."
                ");
				//$number = (int)$ilance->db->num_rows($sql_ship);
				if($ilance->db->num_rows($sql_ship)>0)
				{
				
					$sql_ship1 = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "shippnig_details
						WHERE track_no != ''
                        ORDER BY ship_id ASC
                ");
				$number = (int)$ilance->db->num_rows($sql_ship1);
				
				   $rows_count = 0;
                        $show['shipping'] = true;
                        while ($result = $ilance->db->fetch_array($sql_ship, DB_ASSOC))
                        {
                              
								$firstname = fetch_user('first_name',$result['buyer_id']);
								$lastname = fetch_user('last_name',$result['buyer_id']);
								//murugan changes on jan 19
								$result['buyer_id'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$result['buyer_id'].'"">'.fetch_user('username', $result['buyer_id']).'</a>';
								
								$firstname = fetch_user('first_name',$result['cust_id']);
								$lastname = fetch_user('last_name',$result['cust_id']);
								//murugan changes on jan 19
								$result['seller_id'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$result['cust_id'].'"">'.fetch_user('username', $result['cust_id']).'</a>';
								
								// this fetch_shipper function written by murugan on Function File
								$result['shipper_id'] = fetch_shipper('title',$result['shipper_id']);
								
								$result['edit'] = '<a href="' . $ilpage['shipping'] . '?cmd=shipping&amp;subcmd=update-shippingdetail&amp;id=' . $result['ship_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                        $result['remove'] = '<a href="' . $ilpage['shipping'] . '?cmd=shipping&amp;subcmd=remove-shippingdetail&amp;id=' . $result['ship_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
								
								 $result['action'] = '<input type="checkbox" name="ship_id[]" value="' . $result['ship_id'] . '" id="ship_id_' . $result['ship_id'] . '" />';
								 $result['print'] = '<a href = "listpdf.php?id='.$result['ship_id'] .'">print</a>';
                                $shippinglist[] = $result;
                                $rows_count++;
						}     
			  
                $searchprevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
				}     
				
				}
					$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
							
							 $scriptpageprevnext = $ilpage['shipping'];
							 
							 if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
							 {
								$ilance->GPC['page'] = 1;
							 }
							 else
							 {
								$ilance->GPC['page'] = intval($ilance->GPC['page']);
							 }
				 $sql_toship =   $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "shippnig_details
						WHERE track_no = ''
                        ORDER BY ship_id ASC LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," .$ilconfig['globalfilters_maxrowsdisplaysubscribers']."");
				if($ilance->db->num_rows($sql_toship)>0)
				{
				
				  $sql_toship1 =   $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "shippnig_details
						WHERE track_no = ''
                        ORDER BY ship_id ASC
                ");
				$numbership = (int)$ilance->db->num_rows($sql_toship1);
				while ($res_toship = $ilance->db->fetch_array($sql_toship, DB_ASSOC))
                        {
						//// murugan changes on jan 19
			 //'<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$res['user_id'].'"">'.fetch_user('username', $res['user_id']).'</a>';
							$res_toship['shipper_id'] = fetch_shipper('title',$res_toship['shipper_id']);
							$firstname = fetch_user('first_name',$res_toship['cust_id']);						
							$res_toship['cust_id'] ='<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$res_toship['cust_id'].'"">'.fetch_user('username', $res_toship['cust_id']).'</a>';
							$res_toship['buyer'] = '<a href="'.$ilpage['users'].'?subcmd=_update-customer&amp;id='.$res_toship['buyer_id'].'"">'.fetch_user('username', $res_toship['buyer_id']).'</a>'; 
							$res_toship['update'] = '<a href="' . $ilpage['shipping'] . '?cmd=shipping&amp;subcmd=updatetrack&amp;id=' . $res_toship['ship_id'] . '">Send</a>';
							$toship[] = $res_toship;
						}
				  
				}
				
				
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatetrack' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND empty($ilance->GPC['do']))
                {
						$hiddenfieldsubcmd = 'updatetrack';
                        $hiddendo = '<input type="hidden" name="do" value="update" />';
                        $hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
					$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "shippnig_details
                                WHERE ship_id = '" . intval($ilance->GPC['id']) . "'
							
                        ");
					if ($ilance->db->num_rows($sql) > 0)
                        {
						 $res = $ilance->db->fetch_array($sql);
						 $upitemid = $res['coin_id'];
						 $upship_id = $res['ship_id'];
						}		
					
				}
				
				if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatetrack' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
                {
				 $today = date("Y-m-d");
					$ilance->db->query("
                                UPDATE " . DB_PREFIX . "shippnig_details
                                SET track_no = '" . $ilance->db->escape_string($ilance->GPC['uptrack_no']) . "',
								shipment_date = '".$today."'														
                                WHERE ship_id = '" . intval($ilance->GPC['id']) . "'
                        ");
						 print_action_success("The Selected Shipping Detais Updated Successfully", $ilpage['shipping'] . '?cmd=shipping');
                        exit();  
				}
				
				$toshiprevnext = print_pagnation($numbership, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
							
   				
	
	$pprint_array = array('sellerid','buyerid','coinid','upitemid','toshiprevnext','toship','searchprevnext','emailcheck','shipper','hiddenfieldsubcmd','hiddenid','hiddendo','shippinglist','daylist','monthlist','yearlist','itemid','custid','track_no','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'shipping.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', array('shippinglist','toship'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
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