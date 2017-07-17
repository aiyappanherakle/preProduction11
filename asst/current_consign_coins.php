<?php
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
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '2' or $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

	  
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'allcheck_return_consign')
	{


	    if($ilance->GPC['val'] == '')
		{
			print_action_failed('sorry atleast select one checkbox or dropdown val', '5204.php');
			exit();
		}
		else 
		{

			$coin_id=$ilance->GPC['val'];	
			//print_r($coin_id);
			foreach($coin_id as $coins){
			   $coins_id[] = $coins;
			}

			$selected_coins = implode(', ', $coins_id);
			//echo $selected_coins;

			// check if cons have bids 
			$check_bids_coin = $ilance->db->query("SELECT project_id		
											FROM " . DB_PREFIX . "projects										
											WHERE project_id in(".$selected_coins.")
											AND bids != 0 
											AND status = 'open' ");	
			if($ilance->db->num_rows($check_bids_coin) ==1)
			{
				print_action_failed('please select without bids coins', '5204.php');
				exit();
			}
 			else
 			{

				$sql_username = $ilance->db->query("SELECT u.user_id,u.first_name,u.last_name,c.consignid		
												FROM " . DB_PREFIX . "coins c,										
												" . DB_PREFIX . "users u										
												WHERE c.coin_id in(".$selected_coins.")										
												AND c.user_id = u.user_id 										
												GROUP BY c.user_id
												");					
				
				if($ilance->db->num_rows($sql_username) ==1)
				{
					$res_username = $ilance->db->fetch_array($sql_username);
					$seller_name = $res_username['first_name'].$res_username['last_name'];
					$user_id = $res_username['user_id'];
					$consignid = $res_username['consignid'];
					
					$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers where domestic != '0' order by basefee asc");
				
					if($ilance->db->num_rows($sql)>0) 
					{
						$html='';

						$html.='<select name="return_opt" id="return_opt">
						<option value="-1"  >-Select-</option>';			
						while($line=$ilance->db->fetch_array($sql))
						{
							$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'</option>';
						}
						$html.='<option value="Deliver at Show">Deliver at Show</option>
						<option value="Pick up Office">Pick-up Office</option>
						<option value="Mechanical Error">Mechanical Error</option>
						';

					}		


					$show['return_second_page']= 'second_page';
					$form_action='current_consign_coins.php';
					$pprint_array = array('show','newnumber','html','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action','selected_coins',
						'seller_name','html','user_id','consignid');
								
					($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

					$ilance->template->fetch('main', 'current_consign_coins.html', 4);
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', $pprint_array);
				}
			 }
		}
	}

	else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insert_return_user')
	{
		
		$con  = $ilance->db->query("SELECT *
									FROM " . DB_PREFIX . "shippers 
									WHERE shipperid='".$ilance->GPC['return_opt']."'
									");
		$row = $ilance->db->fetch_array($con);

		if($ilance->db->num_rows($con) > 0)
		{
			$amount_buy =  $ilance->GPC['feesc'] + $row['basefee'];
			$shipper = $ilance->GPC['return_opt'] ;
			$return_opt = 0 ;
		}
		else
		{
			$amount_buy = 0 ;
			$shipper = 0 ;
			$return_opt = $ilance->GPC['return_opt'] ;
		}					
		
		$ilance->accounting = construct_object('api.accounting');

		//seller invoice create 
		
		$coin_id=explode(",",$ilance->GPC['coin_id']);	
		$mail_total_returned_coins = count($coin_id);
		$mail_coin_id_list = $ilance->GPC['coin_id'];

		
		for($i=0;$i<count($coin_id);$i++)
		{
			$invoiceid = $ilance->accounting->insert_transaction(0,
															0,
															0,
															$_POST['user_id'],
															0,
															0,
															0,
															'Your request  for return coin',
															sprintf("%01.2f", $amount_buy),
															sprintf("%01.2f", $amount_buy),
															'paid',
															'debit',
															'account',
															DATETIME24H,
															DATETIME24H,
															DATETIME24H,
															'Return coin',
															0,
															0,
															1,
															'',
															0,
															0
															);

			//invocie id last
			$conw  = $ilance->db->query("SELECT invoiceid
			FROM " . DB_PREFIX . "invoices ORDER BY invoiceid DESC");
			$roww = $ilance->db->fetch_array($conw);

			$invocie_id = $roww['invoiceid'];				    


			//insert cancel value 
			
			$con_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coin_return
											(coin_id, consign_id, user_id, shipper_id, shipping_fees, charges, return_date, invoiceid,notes,notes_client,return_opt)
											VALUES (
											'".$coin_id[$i]."',
											'".$ilance->GPC['consignid']."',
											'".$ilance->GPC['user_id']."',
											'".$shipper."',
											'".$row['basefee']."',
											'".$ilance->GPC['feesc']."',
											'". DATETODAY ."',
											'".$invocie_id."',
											'".$ilance->GPC['notes_gc']."',
											'".$ilance->GPC['notes_client']."',
											'".$return_opt."'
											)
											");
											
											
			$return_invoiceid = $ilance->db->insert_id();								
			

			$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned 
												SELECT * FROM " . DB_PREFIX . "coins 
												WHERE 	coin_id = '".$coin_id[$i]."' 
												AND user_id = '".$ilance->GPC['user_id']."'
												");			
			$con_delete1 = $ilance->db->query("DELETE FROM " . DB_PREFIX . "coins 
												WHERE coin_id = '".$coin_id[$i]."' 
												AND user_id = '".$ilance->GPC['user_id']."'
												");
			
			$con_delete2 = $ilance->db->query("DELETE FROM " . DB_PREFIX . "projects 
												WHERE project_id = '".$coin_id[$i]."' 
												AND user_id = '".$ilance->GPC['user_id']."'
												");	

		}



				// send email start
					$html='';
					//Title:Return Coins to NAME (XX Coins), Address, Address2, City, State, Zip

					$first_name = fetch_user('first_name',$ilance->GPC['user_id']);
					$last_name = fetch_user('last_name',$ilance->GPC['user_id']);
					$name = $first_name.' '.$last_name;
					$mail_total_returned_coins;
					$address = fetch_user('address',$ilance->GPC['user_id']);
					$address2 = fetch_user('address2',$ilance->GPC['user_id']);
					$city = fetch_user('city',$ilance->GPC['user_id']);
					$state = fetch_user('state',$ilance->GPC['user_id']);
					$zip_code = fetch_user('zip_code',$ilance->GPC['user_id']);
					
						//body needs to list
						// CoinID, Title, Min Bid or Buy Now Price

					$sql_multiple_return = 	$ilance->db->query("SELECT c.*,r.Title,r.Minimum_bid,r.Buy_it_now 
																FROM ".DB_PREFIX."coin_return  c
											left join " . DB_PREFIX . "coins_retruned r on c.coin_id = r.coin_id 
												WHERE c.coin_id in(".$mail_coin_id_list.")
												GROUP BY coin_id
												ORDER BY coin_id ASC
											");
				$mail_tot_value = 0;								
				while($res_multiple_return = $ilance->db->fetch_array($sql_multiple_return))
				{
					$res_multiple_return['coin_id'];	
					$res_multiple_return['Title'];
					$res_multiple_return['Minimum_bid'];	
					$res_multiple_return['Buy_it_now'];	
					$mail_tot_value_sigle_coin = $res_multiple_return['Minimum_bid'] + $res_multiple_return['Buy_it_now'];		
					$mail_tot_value = ($res_multiple_return['Minimum_bid'] + $res_multiple_return['Buy_it_now']) + $mail_tot_value ;	
					$sub[] = $table;

					$html .= "***********************************"."\n";
					$html .= "Listing Information"."\n";
					$html .= "***********************************"."\n";	
					$html.='CoinID: ';
					$html.=$res_multiple_return['coin_id']."\n";
					$html.='Title: ';
					$html.=$res_multiple_return['Title']."\n";
					$html.='Min Bid or Buy Now Price:';
					$html.=$mail_tot_value_sigle_coin."\n\n";

				
				}	

						//total at the bottom with value and total coin count.

				$mail_total_returned_coins;
				
                $existing = array('{{mail_tot_value}}' => $mail_tot_value,
											'{{name}}' => $name,
											'{{address}}' => $address,
											'{{address2}}' => $address2,
											'{{city}}' => $city,
											'{{state}}' => $state,
											'{{zip_code}}' => $zip_code,
											'{{mail_total_returned_coins}}' => $mail_total_returned_coins,
											'{{details}}'=>$html,);
        

				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->mail = $ilconfig['globalserversettings_developer_email'];
				$ilance->email->slng = fetch_site_slng(1);	
				$ilance->email->get('returned_coins_staff');		
				$ilance->email->set($existing);
				$ilance->email->send(); 

				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
				$ilance->email->slng = fetch_site_slng(1);	
				$ilance->email->get('returned_coins_staff');		
				$ilance->email->set($existing);
				$ilance->email->send(); 

				
				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->mail = $ilconfig['globalserversettings_staff_ron'];
				$ilance->email->slng = fetch_site_slng(1);	
				$ilance->email->get('returned_coins_staff');		
				$ilance->email->set($existing);
				$ilance->email->send(); 
				
				
				$ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->mail = $ilconfig['globalserversettings_staff_juliann'];
				$ilance->email->slng = fetch_site_slng(1);	
				$ilance->email->get('returned_coins_staff');		
				$ilance->email->set($existing);
				$ilance->email->send();
				

			// send email end


		//PRINT INVOICE
		
		if(isset($ilance->GPC['print_confirm']) AND $ilance->GPC['print_confirm'] == '1')
		{
			
			
			$form_action='current_consign_coins.php';
			$show['print_return_confirmation']= 'print_return_confirmation';
			$display_result="The coin details had been returned successfully to the consignor";

			$coin_id=$ilance->GPC['coin_id'];
			$consng_id = $ilance->GPC['user_id'];

			$pprint_array = array('coin_id','consng_id','display_result','show','table','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');
						
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'current_consign_coins.html', 4);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			
			
		}
		else
		{
			print_action_success('Your Coin Details Had Been Returned successfully', $ilpage['pendings']);
			exit();
		}		
	}

	else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'print_return_confirmation')
	{	
		// download pdf start
		$coin_id_list=$ilance->GPC['coin_id'];
		
		$sql_return = $ilance->db->query("SELECT c.*,u.first_name,u.last_name,u.email,u.address,u.address2,u.city,u.state,u.zip_code 
											FROM ".DB_PREFIX."coin_return c,
											".DB_PREFIX."users u
											WHERE 	c.coin_id in(".$coin_id_list.")
											AND  c.user_id = u.user_id
											ORDER BY c.return_id DESC LIMIT 1
												");
		$res_return = $ilance->db->fetch_array($sql_return);
		
		//$seller_name=$res_return['first_name'].$res_return['last_name'];
		
		$seller_name=$res_return['first_name'].$res_return['last_name']."<br/>
						E-mail: ".$res_return['email']."<br/><br/>".
						$res_return['address']."<br/>".
						$res_return['address2']."<br/><br/>".
						$res_return['city']." ".$res_return['state']." ".$res_return['zip_code'];		
		
		if($res_return['return_opt'] == '0')
		{
		
			$sql_shipper  = $ilance->db->query("SELECT title
										FROM " . DB_PREFIX . "shippers WHERE shipperid='".$res_return['shipper_id']."'
										");
			$res_shipper = $ilance->db->fetch_array($sql_shipper);
			
			$return_via = $res_shipper['title'];
		
		}
		else
		{
			$return_via = $res_return['return_opt'];		 
		}
		
		
		$table = '<div style="border:1px solid black; padding : 10px">
				<table   border="0">				
					<tr>
						<td size="23" family="helvetica" style="bold" nowrap><b>GreatCollections</b></td>
						<td size="12" align="right" width="100%" family="helvetica" style="bold"><b>RETURN TO CONSIGNOR</b></td>
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
						<td size="10">Username: '.$seller_name.'</td>
					</tr>
				</table>
				<br/><br/>
				<table width="100%" style="text-align:center; color: #FFFFFF;">
					<tr bgcolor="#CD9C9C">
						<td nowrap size="10"><p>ID</p></td>
						<td nowrap size="10" width="60%"><p>Item Title</p></td>
						<td nowrap size="10"><p>Consign ID</p></td>
						<td nowrap size="10"><p>Return Date</p></td> 
						<td nowrap size="10"><p>Return Via</p></td>
						<td nowrap size="10"><p>Notes</p></td>
					</tr>';
		// $sql_multiple_return = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coin_return
											// WHERE 	coin_id in(".$coin_id_list.")
												// ");
			
		$sql_multiple_return = 	$ilance->db->query("SELECT c.*,r.Title,r.consignid FROM ".DB_PREFIX."coin_return  c
									left join " . DB_PREFIX . "coins_retruned r on c.coin_id = r.coin_id 
									WHERE c.coin_id in(".$coin_id_list.")
									GROUP BY coin_id
									ORDER BY coin_id ASC
								");
								
		$count=0;								
		while($res_multiple_return = $ilance->db->fetch_array($sql_multiple_return))
		{
			$table.='<tr>
				<td nowrap size="10">'.$res_multiple_return['coin_id'].'</td>
				<td size="10">'.$res_multiple_return['Title'].'</td>
				<td align="center" nowrap size="10">'.$res_multiple_return['consignid'].'</td>
				<td align="center" nowrap size="10">'. date("m-d-Y", strtotime($res_multiple_return['return_date'])).'</td>
				<td nowrap size="10">'.$return_via.'</td>
				<td size="10">'.$res_multiple_return['notes_client'].'</td>
				</tr>';
				
			$count++;	
		}	
		
		$table.='</table> <br/><br/>
				<table width="100%">
				<tr>
					
					<td size="11" >Total Item Count : <b>'.$count.'</b></td>
				</tr>
				</table> </div>';
				
		define('FPDF_FONTPATH','../font/');
		require('pdftable_1.9/lib/pdftable.inc.php');
		$p = new PDFTable();
		$p->AddPage();
		$p->setfont('times','',10);
		$p->htmltable($table);
		$p->output('Return consignor_'. DATETIME24H .'.pdf','D');
	}

	else
	{

		$show['return_first_page']= 'first_page';
		$form_action='current_consign_coins.php';
		$pprint_array = array('show','newnumber','html','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action','get_filtervalue');
					
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'current_consign_coins.html', 4);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);

	} 

}

else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();

}

?>	
