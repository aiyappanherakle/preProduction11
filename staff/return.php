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
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['Submit_return']) AND $ilance->GPC['Submit_return'] == 'Return sale' )
	{
	
		//$coin_id=$ilance->GPC['coin_list'];
	    if(strlen($ilance->GPC['coin_list'])==0)
		{
			print_action_failed("We're sorry. The textarea is empty.", $_SERVER['PHP_SELF']);exit();
		}
$coin_id=$ilance->GPC['coin_list'];	
 
    $numbers1 = explode(',', $ilance->GPC['coin_list']);
     $numbers2 = array_filter($numbers1);
     $numbers =array_map('trim', $numbers2);

  
     
    $error = 0;
     $inValidNumbers = array();
 
     foreach($numbers as $number) {
         if(!preg_match("/^[0-9]*$/", $number)) {
              $error++;                 
              array_push($inValidNumbers,$number);
 
        }
    }
 
    if($error != 0) {
       print_action_failed("Please Check seperate the Item IDs by comma.", $_SERVER['PHP_SELF']);exit();
       
    }
 	    

    //          $items = array();
    //          $itemss = array();

    //          foreach($numbers as $cins_array)
		  //    {
		  //      	if ($cins_array > 0) 
			 //    {
			 //        $sql1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE  ((filtered_auctiontype = 'regular' AND winner_user_id  = '0' AND bids = '0') OR (buynow = '1' AND filtered_auctiontype = 'fixed' AND buynow_qty > '0')) AND project_id = ".$cins_array, 0, null, __FILE__, __LINE__);
			 //        $project_id = $ilance->db->fetch_array($sql1);
				// 	if ($ilance->db->num_rows($sql1) > 0) 
				// 	{ 						 
				// 		$items[] = $cins_array;
				// 	}
				// 	else
				// 	{
				// 		$itemss[] = $cins_array;
				// 	}
			 //    }

		  //    }
		  //     $result = array_unique($items);
		  //     $live_coin = implode(",",$result);
		  //     $coin_id = $live_coin;
		  //     $result1 = array_unique($itemss);
		  //     $sold_coin = implode(",",$result1);
		   
		  //    $tot_coins=count($itemss);
		
		 	//  if($tot_coins > 0)
			 // {			 
				// print_action_failed("Return coins: ".$coin_id." <br><br>The coin enter is either the sold or with bids-Kindly Check cancel following list: ".$sold_coin." ", $_SERVER['PHP_SELF']);exit();
			 // }	
            
              

		 
		$sql_username = $ilance->db->query("SELECT u.user_id,u.first_name,u.last_name,c.consignid
		
										FROM " . DB_PREFIX . "coins c,
										
										" . DB_PREFIX . "users u
										
										WHERE c.coin_id in(".$coin_id.")
										
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
		
			$form_action='return.php';
			
			$pprint_array = array('newnumber','html','coin_id','consignid','seller_name','user_id','seller_id','buyer_name','buyer_id','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');
						
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'return_coin_new.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();			
		}
		else
		{
			print_action_failed('<b>1.Either the coin or the consignor do not exist in GC<br><br>2.If you are trying to return multiple coins please note that,<br>
			<pre>	You can return only the coins of the same consignor at one time.<br>
			<b>	The item IDs should be seperated by comma.</pre></b>','return.php');
			exit();
		}		

	}
	else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insert_return_user')
	{
		
		
		$con  = $ilance->db->query("SELECT *
									FROM " . DB_PREFIX . "shippers WHERE shipperid='".$ilance->GPC['return_opt']."'
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
			
			$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins_retruned SELECT * FROM " . DB_PREFIX . "coins 
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

		//PRINT INVOICE
		
		if(isset($ilance->GPC['print_confirm']) AND $ilance->GPC['print_confirm'] == '1')
		{
			
			
			$form_action='return.php';
			$show['print_return_confirmation']= 'print_return_confirmation';
			$display_result="The coin details had been returned successfully to the consignor";
			$coin_id=$ilance->GPC['coin_id'];
			$pprint_array = array('coin_id','display_result','show','table','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');
						
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'return_coin_new.html', 2);
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
		
		$coin_id_list=$ilance->GPC['coin_id'];
		
		$sql_return = $ilance->db->query("SELECT c.*,u.first_name,u.last_name,u.email,u.address,u.address2,u.city,u.state,u.zip_code,u.phone 
											FROM ".DB_PREFIX."coin_return c,
											".DB_PREFIX."users u
											WHERE 	c.coin_id in(".$coin_id_list.")
											AND  c.user_id = u.user_id
											ORDER BY c.return_id DESC LIMIT 1
												");
		$res_return = $ilance->db->fetch_array($sql_return);
		
		$prt_addr2='';
		if(!empty($res_return['address2']))
		{
		$prt_addr2=	$res_return['address2']."<br/>";			
		}
		
		//$seller_name=$res_return['first_name'].$res_return['last_name'];
		
		$seller_name=$res_return['first_name']." ".$res_return['last_name']."<br/>
						Address: ".$res_return['address']."<br/>".
						$prt_addr2.
						$res_return['city']." ".$res_return['state']." ".$res_return['zip_code']."<br/>
						E-mail: ".$res_return['email']."<br/>
						Telephone: ".$res_return['phone']."<br/><br/>";		
		
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
						<td size="10">Consignor: '.$seller_name.'</td>
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
												
		$sql_multiple_return = 	$ilance->db->query("SELECT c.*,r.consignid as multiple_conisgn_id,r.Title,r.Minimum_bid as Minimum_bid,r.Buy_it_now as Buy_it_now FROM ".DB_PREFIX."coin_return  c
									left join " . DB_PREFIX . "coins_retruned r on c.coin_id = r.coin_id 
									WHERE c.coin_id in(".$coin_id_list.")
									ORDER BY coin_id ASC
								");
								
		$count = $totalminbid = 0;								
		while($res_multiple_return = $ilance->db->fetch_array($sql_multiple_return))
		{
			//echo "UPDATE ilance_coin_return SET consign_id = ".$res_multiple_return['multiple_conisgn_id']." WHERE coin_id =". $res_multiple_return['coin_id']."";		
			
			$ilance->db->query("UPDATE " . DB_PREFIX . "coin_return SET consign_id = ".$res_multiple_return['multiple_conisgn_id']." WHERE coin_id = ".$res_multiple_return['coin_id']."");
			
			$table.='<tr>
				<td nowrap size="10">'.$res_multiple_return['coin_id'].'</td>
				<td size="10">'.$res_multiple_return['Title'].'</td>
				<td align="center" nowrap size="10">'.$res_multiple_return['multiple_conisgn_id'].'</td>
				<td align="center" nowrap size="10">'. date("m-d-Y", strtotime($res_multiple_return['return_date'])).'</td>
				<td nowrap size="10">'.$return_via.'</td>
				<td size="10">'.$res_multiple_return['notes_client'].'</td>
				</tr>';
			
			$totalminbid+=$res_multiple_return['Minimum_bid'];
			
			if($res_multiple_return['Buy_it_now'] > 0)
			{
			
			$totalBuy_it_now+=$res_multiple_return['Buy_it_now'];
			
			}
			

			$count++;	
		}	
		
		$table.='</table> <br/><br/>
					<table width="100%">
					<tr>
						
						<td size="11" >Total Item Count : <b>'.$count.'</b></td>
					</tr>
					</table>
					<table width="100%">
					<tr>
						
						<td size="11" >Total Min Bid : <b>'.$ilance->currency->format($totalminbid).'</b></td></b>
						<td size="11" >Total Buy Now : <b>'.$ilance->currency->format($totalBuy_it_now).'</b></td>
					</tr> 
					</table> 
				</div>';
				
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
		$form_action='return.php';
		$pprint_array = array('show','newnumber','html','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action');
					
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'return_coin_new.html', 2);
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
