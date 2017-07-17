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
	'search',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'modal',
	'yahoo-jar', 
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
$ilance->accounting = construct_object('api.accounting');
 $ilance->bid = construct_object('api.bid');
 $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]); 
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
 
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
     
 //global $ilance, $phrase, $ilconfig, $ilpage;
$ilance->accounting = construct_object('api.accounting');
$ilance->accounting_fees = construct_object('api.accounting_fees');
  
  // tamil * 29.6.12 * ajax response * begin \\
  
	if(isset($ilance->GPC['get_user_id']))
		{
		
			$sql_user_id =$ilance->db->query("SELECT user_id 	
						  FROM " . DB_PREFIX . "users  
						  WHERE  user_id = '".$ilance->GPC['get_user_id']."'
						  
						  OR username LIKE'%".$ilance->GPC['get_user_id']."%'
						  OR email LIKE'%".$ilance->GPC['get_user_id']."%'
						  OR first_name LIKE'%".$ilance->GPC['get_user_id']."%'
						  OR last_name  LIKE'%".$ilance->GPC['get_user_id']."%' 						  
						  LIMIT 1
							 ");
			$res_user_id = $ilance->db->fetch_array($sql_user_id);
			
			
			echo "
						SELECT p.project_id,p.project_title ,p.user_id as seller_id,p.winner_user_id ,b.bidamount as hammer_price,d.taxamount,d.totalamount,DATE_FORMAT(b.date_awarded,'%b %d %Y %h:%i %p') as date_awarded,d.status
						FROM " . DB_PREFIX . "projects p,
						" . DB_PREFIX . "project_bids b, 
						" . DB_PREFIX . "invoice_projects c, 
						" . DB_PREFIX . "invoices d
						WHERE  	p.winner_user_id = '".$return_user_id."' 
						AND		b.bidstatus = 'awarded'
						AND     p.project_id = b.project_id
						AND    	p.haswinner = '1'	
						AND 	c.buyer_id = p.winner_user_id
						AND     b.project_id = c.project_id
						AND     d.projectid = p.project_id
						AND     d.isfvf = '0'
						AND     d.isbuyerfee = '0'
						AND     d.status='unpaid'
						ORDER BY p.date_end DESC
						";
			
			$return_user_id= $res_user_id['user_id'];	
			
						
			$return_regular_awarded='<table border="1" cellpadding="{table_cellpadding}" cellspacing="{table_cellspacing}" align="center">
										<caption><b>Awarded by regular method</b></caption>
										<tr>
										<td><strong>Project_id</strong></td>
										<td><b>Project_title</b></td>
										<td><b>Seller_id</b></td>
										<td><b>Winner_user_id</b></td>
										<td><b>Hammer_price</b></td>
										<td><b>Taxamount</b></td>
										<td><b>Totalamount</b></td>
										<td><b>Date_awarded</b></td>
										<td><b>Status</b></td>	
										<td><b>SELECT</b></td>
										
										</tr>
									';
			
			
			$sql_regular_awarded= $ilance->db->query("
						SELECT p.project_id,p.project_title ,p.user_id as seller_id,p.winner_user_id ,b.bidamount as hammer_price,d.taxamount,d.totalamount,DATE_FORMAT(b.date_awarded,'%b %d %Y %h:%i %p') as date_awarded,d.status
						FROM " . DB_PREFIX . "projects p,
						" . DB_PREFIX . "project_bids b, 
						" . DB_PREFIX . "invoice_projects c, 
						" . DB_PREFIX . "invoices d
						WHERE  	p.winner_user_id = '".$return_user_id."' 
						AND		b.bidstatus = 'awarded'
						AND     p.project_id = b.project_id
						AND    	p.haswinner = '1'	
						AND 	c.buyer_id = p.winner_user_id
						AND     b.project_id = c.project_id
						AND     d.projectid = p.project_id
						AND     d.isfvf = '0'
						AND     d.isbuyerfee = '0'
						AND     d.status='unpaid'
						ORDER BY p.date_end DESC
						"); 
			
			while($res_regular_awarded=$ilance->db->fetch_array($sql_regular_awarded))
			{
				$return_regular_awarded.='<tr>
											<td>'.$res_regular_awarded['project_id'].'</td>
											<td>'.$res_regular_awarded['project_title'].'</td>
											<td>'.$res_regular_awarded['seller_id'].'</td>
											<td>'.$res_regular_awarded['winner_user_id'].'</td>
											<td>'.$res_regular_awarded['hammer_price'].'</td>
											<td>'.$res_regular_awarded['taxamount'].'</td>
											<td>'.$res_regular_awarded['totalamount'].'</td>
											<td>'.$res_regular_awarded['date_awarded'].'</td>
											<td>'.$res_regular_awarded['status'].'</td>
											<td><input type="checkbox" name="checkbox[]" value='.$res_regular_awarded['project_id']. '></td>
										</tr>';
			}
			
			$return_regular_awarded.='</table>';
			
			
			
							
							
			$return_fixed_awarded='<table border="1"  cellpadding="{table_cellpadding}" cellspacing="{table_cellspacing}" align="center">
										<caption><b>Awarded by fixed method</b></caption>
										<tr>
										<td>Project_id</td>
										<td>Project_title</td>
										<td>Seller_id</td>
										<td>Buyer_id</td>
										<td>Buynow_price</td>
										<td>Taxamount</td>
										<td>Totalamount</td>
										<td>Date_of_purchase</td>
										<td>Status</td>	
										<td>SELECT</td>
										</tr>
									';	
			$sql_fixed_awarded=$ilance->db->query("SELECT  p.project_id,p.project_title,p.user_id as seller_id,b.buyer_id,p.buynow_price,c.taxamount,c.totalamount,DATE_FORMAT(b.orderdate,'%b %d %Y %h:%i %p') as date_of_purchase,c.status
													FROM " . DB_PREFIX . "projects p,
														 " . DB_PREFIX . "buynow_orders b,
														 " . DB_PREFIX . "invoices c
														
													WHERE  	b.buyer_id = '".$return_user_id."' 
													AND     p.project_id = b.project_id	
													AND     c.projectid = p.project_id
													AND 	c.status = 'unpaid'
													AND     c.isfvf = '0'
													AND     c.isbuyerfee = '0'
													GROUP BY b.orderdate											
													ORDER BY b.orderid DESC	
													");	
													
			while($res_fixed_awarded=$ilance->db->fetch_array($sql_fixed_awarded))
			{
				$return_fixed_awarded.='<tr>
											<td>'.$res_fixed_awarded['project_id'].'</td>
											<td>'.$res_fixed_awarded['project_title'].'</td>
											<td>'.$res_fixed_awarded['seller_id'].'</td>
											<td>'.$res_fixed_awarded['buyer_id'].'</td>
											<td>'.$res_fixed_awarded['buynow_price'].'</td>
											<td>'.$res_fixed_awarded['taxamount'].'</td>
											<td>'.$res_fixed_awarded['totalamount'].'</td>
											<td>'.$res_fixed_awarded['date_of_purchase'].'</td>
											<td>'.$res_fixed_awarded['status'].'</td>
											<td><input type="checkbox" name="checkbox[]" value='.$res_fixed_awarded['project_id']. '></td>
										</tr>';
			}
			
			$return_fixed_awarded.='</table>';										
							
			$return_html=$return_user_id.'|'.$return_regular_awarded.'|'.$return_fixed_awarded;
			
			echo $return_html;
			exit;
		}
  

  
  
if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'change_buyer_confirm' and isset($ilance->GPC['selecte']))
{
if($ilance->GPC['inhouse_user_id']<=1)
{
echo "no to user found";
exit;
}
error_reporting(E_ALL);
$selected=$ilance->GPC['selecte'];
$change_to_user_id=$ilance->GPC['inhouse_user_id'];
if(count($selected)>0)
{
foreach($selected as $row)
{
list($method,$id)=explode("_",$row);
	if($method=='regular')
	{
	//regualr move, bid_id
	$query="SELECT *  FROM " . DB_PREFIX . "project_bids WHERE bid_id = '".$id."'";
	$result=$ilance->db->query($query);
	if($ilance->db->num_rows($result))
	{
	while($line=$ilance->db->fetch_array($result))
	{
			//$line['project_user_id'];
			//$line['bid_id'];

			$query1="SELECT *  FROM " . DB_PREFIX . "invoices WHERE projectid = '".$line['project_id']."' and user_id='".$line['user_id']."'";
			$removed_item_total=0;
			$result1=$ilance->db->query($query1);
			if($ilance->db->num_rows($result1)>0)
			{
			while($line1=$ilance->db->fetch_array($result1))
			{
			if($line1['isbuyerfee']==0)
			$moved_invoice_id=$line1['invoiceid'];
			
			$removed_item_total+=$line1['totalamount'];
			}
			
			//change invoice
			echo $query3="update " . DB_PREFIX . "invoices set user_id='".$change_to_user_id."' WHERE projectid = '".$line['project_id']."' and user_id='".$line['user_id']."'";
			echo ';<br>';
			//change combine invoice if any
	$query2="SELECT *  FROM " . DB_PREFIX . "invoices where combine_project like '%".$moved_invoice_id."%' and user_id='".$line['user_id']."'";
				$result2=$ilance->db->query($query2);
				if($ilance->db->num_rows($result2))
				{
				while($line2=$ilance->db->fetch_array($result2))
				{
				if($line2['status']=='paid')
				{
				echo 'carefull invoice is paid '.$line2['invoiceid'];
				echo '<br>';
				}
				
				$old_list=explode(",",$line2['combine_project']);
				if(($key = array_search( $moved_invoice_id,$old_list)) !== false) {
					unset($old_list[$key]);
				}
				$new_list=implode(",",$old_list);
					echo $query10="update " . DB_PREFIX . "invoices set combine_project='".$new_list."', totalamount=totalamount-".$removed_item_total.", amount=totalamount where invoiceid=".$line2['invoiceid'];
					echo ';<br>';
					
				}
				}
				
			//change bid
			echo $query3="update " . DB_PREFIX . "project_bids set user_id='".$change_to_user_id."' WHERE bid_id = '".$id."' and user_id='".$line['user_id']."'";
			echo ';<br>';
			//change proxy
			echo $query4="update " . DB_PREFIX . "proxybid set user_id='".$change_to_user_id."' WHERE project_id = '".$line['project_id']."' and user_id='".$line['user_id']."'";
			echo ';<br>';
			//change winner id in project
			echo $query5="update " . DB_PREFIX . "projects set winner_user_id='".$change_to_user_id."' WHERE project_id = '".$line['project_id']."' and winner_user_id='".$line['user_id']."'";
			echo ';<br>';
			//insert resell coins
			echo $query6="INSERT INTO " . DB_PREFIX . "resell_coins
				          (id,coin_id, from_user, to_user, dead_beat_date)
					  VALUES(
					  NULL,
					  '" . $line['project_id'] . "',
					  '" . $line['user_id'] . "',
					  '" . $change_to_user_id . "',
					  '" . DATETIME24H . "')
					  ";
					  echo ';<br>';
			}else
			{
			echo "prob with ".$line['project_id'];
			}

	}
	}
	}else if($type=='buynow')
	{
	//buy now move
	}
}	
}
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'change_buyer')
{
$given_projects=$ilance->GPC['project_id'];
$projectlist=explode(",",$ilance->GPC['project_id']);
$show['change_buyer_confirm']=false;
foreach($projectlist as $project_id)
{
$query="SELECT s.username as sellername,b.username as buyername,p.*,bid.*  FROM " . DB_PREFIX . "projects p  
left join " . DB_PREFIX . "users s on  p.user_id=s.user_id
left join " . DB_PREFIX . "users b on  p.winner_user_id=b.user_id
left join " . DB_PREFIX . "project_bids bid on bid.project_id=p.project_id and bidstatus='awarded' and date_awarded>'0000-00-00'
WHERE p.project_id = '".$project_id."'
";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result))
{
$show['change_buyer_confirm']=true;
while($line=$ilance->db->fetch_array($result))
{
	if($line['filtered_auctiontype']=='regular')
	{
		$line['checkbox_value']='regular_'.$line['bid_id'];
	}else
	{
	
		$line['checkbox_value']='buynow_'.$order_id;
	}
$res[]=$line;

}
$change_buyers=$res;
}
}
 
}

if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'sold_to_pending')
{
	
		
	$proj_id=$ilance->GPC['checkbox'];
	$buyer_id=$ilance->GPC['user_id'];
	if((count($proj_id) < 1)  || (empty($buyer_id)))
	{
		print_action_failed('You have not selected any projects ', 'auction_details_change.php');
		exit;
	}
	
	
	for($i=0;$i<count($proj_id);$i++)
	{
		
		$sql_regorfix=$ilance->db->query("SELECT  filtered_auctiontype
			      FROM " . DB_PREFIX . "projects 
			      WHERE  project_id = '".$proj_id[$i]."'
		             ");
		$res_regorfix=$ilance->db->fetch_array($sql_regorfix);
		
		if($res_regorfix['filtered_auctiontype']=='fixed')
		{
			
			
			//select parent invoice,combined projects //
			$sql_invoice=$ilance->db->query("SELECT  invoiceid,combine_project
											FROM " . DB_PREFIX . "invoices 
											WHERE user_id = '".$buyer_id."'
											AND projectid='0'
											AND status='unpaid'
											AND combine_project LIKE '".$proj_id[$i]."'
											");
			$res_invoice=$ilance->db->fetch_array($sql_invoice)	;
			
			
			$combine_invoices=explode(",",$res_invoice['combine_project']);

			
			//select child invoice//
			$sql_child_invoice=$ilance->db->query("SELECT invoiceid,amount
													FROM " . DB_PREFIX . "invoices 
													WHERE user_id = '".$buyer_id."'
													AND   projectid='".$proj_id[$i]."'
													AND	  isfvf='0'
													AND   isbuyerfee='0'
													AND   combine_project=''
													AND   status='unpaid'													
													");
													
			$res_child_invoice=$ilance->db->fetch_array($sql_child_invoice)	;	
			
			$child_invoice_id=$res_child_invoice['invoiceid'];
			
			if(empty($combine_invoices[0]))
			{
				// delete invoice , in case if there is only one invoice i.e no parent invoice generated //
				$sql_single_invoice=$ilance->db->query("SELECT  invoiceid
											FROM " . DB_PREFIX . "invoices 
											WHERE user_id = '".$buyer_id."'
											AND projectid='".$proj_id[$i]."'
											AND status='unpaid'
											");
				if($ilance->db->num_rows($sql_single_invoice) > 0)
				{
					$res_single_invoice=$ilance->db->fetch_array($sql_single_invoice);
					$sql_single_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
																WHERE user_id='".$buyer_id."'
																AND invoiceid='".$res_single_invoice['invoiceid']."'
																");
																
					
				}
			}
			
			else
			{
				if(count($combine_invoices)==1 && !empty($combine_invoices[0]) && $combine_invoices[0]==$res_invoice['combine_project'])
				{
					//parent invoice delete ,in case if there is only one child  for that parent//
					$sql_parent_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
																WHERE user_id='".$buyer_id."'
																AND combine_project='".$res_invoice['combine_project']."'
																");
																
					
					
					
				}
				if(count($combine_invoices) > 1)
				{	
					//remove child invoice from combined projects of parent invoice i.e update parent invoice//  
					
					
					$combine_invoices=array_diff($combine_invoices,array($child_invoice_id) );
					
					$combine_invoices=implode(",",$combine_invoices);

					$sql_invoice_update=$ilance->db->query("UPDATE  FROM " . DB_PREFIX . "invoices 
															SET  combine_project = '".$combine_invoices."'
															WHERE invoiceid = '".$res_invoice['invoiceid']."'
														");		
					
					
				}
				
				//child invoice delete//									
				$sql_child_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
															WHERE invoiceid='".$child_invoice_id."'
															");
			}
			
			
			//fvf invoice delete//							
			$sql_fvf_invoice_id=$ilance->db->query("SELECT fvfinvoiceid FROM  " . DB_PREFIX . "buynow_orders 
													
														WHERE project_id='".$proj_id[$i]."'
														AND buyer_id='".$buyer_id."'
														AND  status='unpaid'
														GROUP BY project_id
														");		
														
			$res_fvf_invoice_id=$ilance->db->fetch_array($sql_fvf_invoice_id);	
			
			
			$sql_fvf_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
														WHERE invoiceid='".$res_fvf_invoice_id['fvfinvoiceid']."'
														AND  isfvf='1'
														");
														
														
			//buynow orders delete//											
			$sql_buynow_orders_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "buynow_orders 
														WHERE project_id='".$proj_id[$i]."'
														AND buyer_id='".$buyer_id."'			
														");	
			//invoice_projects delete//
			$sql_invoice_projects_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoice_projects 
														WHERE project_id='".$proj_id[$i]."'
														AND buyer_id='".$buyer_id."'
														AND status='unpaid'
														");
														
			// update for  projects  //	
			$sql_projects_update=$ilance->db->query("UPDATE " . DB_PREFIX . "projects 
														SET  buynow_qty=(buynow_qty+1) ,
														buynow_purchases =(buynow_purchases-1),
														buyer_fee = '0.00',														
														buyerfeeinvoiceid = '0',
														isfvfpaid = '0',
														fvfinvoiceid = '0',
														haswinner = '0',
														hasbuynowwinner = '0'
														
														WHERE project_id = '".$proj_id[$i]."'
														
													");
													
			// watchlist delete //
			$sql_watchlist_del=$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist
													WHERE user_id = '".$buyer_id."'
													AND watching_project_id = '".$proj_id[$i]."'
													");
													
			print_action_success('The coin is moved to pending ', 'auction_details_change.php');						
		}
		
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		if($res_regorfix['filtered_auctiontype']=='regular')
		{
			
			//select parent invoice id,combined projects //
			$sql_invoice=$ilance->db->query("SELECT  invoiceid,combine_project
											FROM " . DB_PREFIX . "invoices 
											WHERE user_id = '".$buyer_id."'
											AND projectid='0'
											AND status='unpaid'											
											");
											
			$res_invoice=$ilance->db->fetch_array($sql_invoice)	;			
			
			$combine_invoices=explode(",",$res_invoice['combine_project']);
			
			
			//select child invoice id//
			$sql_child_invoice=$ilance->db->query("SELECT invoiceid,amount
													FROM " . DB_PREFIX . "invoices 
													WHERE user_id = '".$buyer_id."'
													AND   projectid='".$proj_id[$i]."'
													AND	  isfvf='0'
													AND   isbuyerfee='0'
													AND   combine_project=''
													AND   status='unpaid'													
													");
			$res_child_invoice=$ilance->db->fetch_array($sql_child_invoice)	;										
			
			$child_invoice_id=$res_child_invoice['invoiceid'];
			
			if(empty($combine_invoices[0]))
			{
				
				// delete invoice , in case if there is only one invoice i.e no parent invoice generated //
				$sql_single_invoice=$ilance->db->query("SELECT  invoiceid
											FROM " . DB_PREFIX . "invoices 
											WHERE user_id = '".$buyer_id."'
											AND projectid='".$proj_id[$i]."'
											AND status='unpaid'
											");
				if($ilance->db->num_rows($sql_single_invoice) > 0)
				{
					$res_single_invoice=$ilance->db->fetch_array($sql_single_invoice);
					
					
					$sql_single_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
																WHERE user_id='".$buyer_id."'
																AND invoiceid='".$res_single_invoice['invoiceid']."'
																");
																
					
				}
			}
			
			else
			{
				
				if(count($combine_invoices)==1 &&  !empty($combine_invoices[0]) && $combine_invoices[0]==$res_invoice['combine_project'])
				{
										
					//parent invoice delete, in case if there is only one child  for that parent//
					$sql_parent_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
																WHERE user_id='".$buyer_id."'
																AND combine_project='".$res_invoice['combine_project']."'
																");
																
					
					
					
				}
				if(count($combine_invoices) > 1)
				{	
					
					//remove child invoice from combined projects of parent invoice i.e update parent invoice//  
					
					$combine_invoices=array_diff($combine_invoices,array($child_invoice_id) );
					
					
					$combine_invoices=implode(",",$combine_invoices);
					
		
					$sql_invoice_update=$ilance->db->query("UPDATE " . DB_PREFIX . "invoices 
															SET  combine_project = '".$combine_invoices."'
															WHERE invoiceid = '".$res_invoice['invoiceid']."'
														");		
					
					
				}
				
				//child invoice delete//									
				$sql_child_invoice_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoices 
															WHERE invoiceid='".$child_invoice_id."'
															");
			}			
		
			
			//fvf and buyerfee invoice delete//
							
			 $sql_fvf_bf=$ilance->db->query("SELECT fvfinvoiceid,buyerfeeinvoiceid FROM " . DB_PREFIX . "projects
												WHERE project_id = '".$proj_id[$i]."'
												AND winner_user_id = '".$buyer_id."' 
												");
												
			$res_fvf_bf=$ilance->db->fetch_array($sql_fvf_bf);								
				
						
			$sql_fvf_bf_del=$ilance->db->query("DELETE FROM " . DB_PREFIX . "invoices
											WHERE invoiceid = '".$res_fvf_bf['fvfinvoiceid']."'
											OR invoiceid = '".$res_fvf_bf['buyerfeeinvoiceid']."'											
											");									
								
			//bids delete //
			$sql_project_bids_del=$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids
															WHERE project_id='".$proj_id[$i]."'		
															");
															
															
			//proxy bids delete//
			$sql_proxybid_del=$ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid
														WHERE project_id='".$proj_id[$i]."'
														");
			
			//invoice_projects delete//
			$sql_invoice_projects_del=$ilance->db->query("DELETE FROM  " . DB_PREFIX . "invoice_projects 
														WHERE project_id='".$proj_id[$i]."'
														AND buyer_id='".$buyer_id."'
														AND status='unpaid'
														");
			
			//update for projects //
			$sql_projects_update=$ilance->db->query("UPDATE " . DB_PREFIX . "projects 
													SET haswinner = '0' ,
														winner_user_id = '0',
														buyer_fee = '0.00',														
														buyerfeeinvoiceid = '0',
														isfvfpaid = '0',
														fvfinvoiceid = '0',
														fvf='0.00',
														currentprice=startprice
														
													WHERE project_id = '".$proj_id[$i]."'
													AND winner_user_id = '".$buyer_id."'
													");
													
			// watchlist delete //

			$sql_watchlist_del=$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist
													WHERE user_id = '".$buyer_id."'
													AND watching_project_id = '".$proj_id[$i]."'
													");			

			echo"Completed";
			print_action_success('The coin is moved to pending ', 'auction_details_change.php');
		}
	}
	
	
}

// tamil * 26:06:12 * sold to pending * end \\
  
  
  
  
 if(isset($ilance->GPC['get_user_detail']))
 {
	   $item7 = $ilance->db->query("SELECT user_id,email	
			      FROM " . DB_PREFIX . "users  
			      WHERE  user_id = '".$ilance->GPC['get_user_detail']."'
			      OR username LIKE'%".$ilance->GPC['get_user_detail']."%'
			      OR email LIKE'%".$ilance->GPC['get_user_detail']."%'
			      OR first_name LIKE'%".$ilance->GPC['get_user_detail']."%'
			      OR last_name  LIKE'%".$ilance->GPC['get_user_detail']."%' LIMIT 1
		             ");
         
	    $de_beat = $ilance->db->fetch_array($item7);
            echo $de_beat['email'].'|'.$de_beat['user_id'];
            exit;
  }
      
      if(isset($ilance->GPC['get_fromuser_detail']))
      {
	    $item6 = $ilance->db->query("SELECT user_id 	
			      FROM " . DB_PREFIX . "users  
			      WHERE  user_id = '".$ilance->GPC['get_fromuser_detail']."'
			      OR username LIKE'%".$ilance->GPC['get_fromuser_detail']."%'
			      OR email LIKE'%".$ilance->GPC['get_fromuser_detail']."%'
			      OR first_name LIKE'%".$ilance->GPC['get_fromuser_detail']."%'
			      OR last_name  LIKE'%".$ilance->GPC['get_fromuser_detail']."%' LIMIT 1
		             ");
	    $de_bet = $ilance->db->fetch_array($item6);
	    $field=$de_bet['user_id'];
	    
	    
	   $table='<table border="1" style="padding:10px"><tr style="height: 25px;"><td><B>ProjectID</td><td><B>User_id</td>
		                                         <td><B>Amount</td><td><B>Tax-Amount</td><td><B>Total-Amount</td><td><B>Auction Type</td></tr>';
 	    
	    $item5 = $ilance->db->query("SELECT projectid,user_id,amount,taxamount,totalamount
			      FROM " . DB_PREFIX . "invoices  
			      WHERE  user_id = '".$field."'
			      AND status = 'unpaid' 
			      AND combine_project = ''
			      AND projectid > 0
			      GROUP BY projectid
		             ");
	    
	   while($de_be = $ilance->db->fetch_array($item5))
           {
	      $table.= '<td style="width: 20px;">'. $de_be['projectid'].'</td>
	                <td style="width:20px;">'. $de_be['user_id'].'</td> 
	                <td style="width:20px;">'. $de_be['amount'].'</td>
			<td style="width:20px;">'. $de_be['taxamount'].'</td> 
	                <td style="width:20px;">'. $de_be['totalamount'].'</td>
			<td style="width:20px;">'.Regular.'</td>
			</tr>';
           }
	   
	   
	   $buynow = $ilance->db->query("SELECT  project_id, orderid
			            FROM " . DB_PREFIX . "buynow_orders
			            WHERE  buyer_id = '".$field."'
		                   ");
	  
	   while($fet = $ilance->db->fetch_array($buynow))
           {
	      
	    $from_user = $ilance->db->query("SELECT projectid,user_id,amount,taxamount,totalamount
					  FROM " . DB_PREFIX . "invoices  
					  WHERE buynowid = '".$fet['orderid']."'
					  ");
		
	    $de_beet = $ilance->db->fetch_array($from_user);
			   
			  $table.= '<td style="width: 20px;">'. $de_beet['projectid'].'</td>
				    <td style="width:20px;">'. $de_beet['user_id'].'</td> 
				    <td style="width:20px;">'. $de_beet['amount'].'</td>
				    <td style="width:20px;">'. $de_beet['taxamount'].'</td> 
				    <td style="width:20px;">'. $de_beet['totalamount'].'</td>
				    <td style="width:20px;">'.Fixed.'</td>
				</tr>';
	     }
	   $table.= '</table>';
	             
	   echo $table;
           exit;
      }
      

  
if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'dead_beat')
{
         $from_user=$ilance->GPC['from_user'];
         $to_user=$ilance->GPC['to_user'];

      //if projects are in bidding process
     
     $item = $ilance->db->query("SELECT  id,project_id,filtered_auctiontype
			        FROM " . DB_PREFIX . "projects 
			        WHERE  winner_user_id = '".$from_user."'
		                ");
      if($ilance->db->num_rows($item)>0)
      {
        while($ded_bet = $ilance->db->fetch_array($item))
        {    
	     $unpaid = $ilance->db->query("SELECT  projectid,amount,status
			      FROM " . DB_PREFIX . "invoices 
			      WHERE  user_id = '".$from_user."'
			      AND projectid = '".$ded_bet['project_id']."'
			      AND status = 'unpaid'
		              ");
	    if($ilance->db->num_rows($unpaid)>0)
	    {
	          while($de_be = $ilance->db->fetch_array($unpaid))
                  {
			echo $query100= "UPDATE " . DB_PREFIX . "projects SET  winner_user_id = '".$to_user."'
				  WHERE project_id = '".$de_be['projectid']."'
				  AND winner_user_id = '".$from_user."'";
echo ';<br>';
			echo $query101="UPDATE " . DB_PREFIX . "proxybid SET  user_id = '".$to_user."'
				          WHERE project_id = '".$de_be['projectid']."'
				          AND user_id = '".$from_user."'";
echo ';<br>';
			echo $query102="INSERT INTO " . DB_PREFIX . "resell_coins
				          (id,coin_id, from_user, to_user, dead_beat_date)
					  VALUES(
					  NULL,
					  '" . $de_be['projectid'] . "',
					  '" . $from_user . "',
					  '" . $to_user . "',
					  '" . DATETIME24H . "')
					  ";
echo ';<br>';	    
			echo $query103="UPDATE " . DB_PREFIX . "project_bids SET  user_id = '".$to_user."'
			 WHERE project_id = '".$de_be['projectid']."'
			 AND user_id = '".$from_user."'
			 AND  bidstatus = 'awarded'";
echo ';<br>';		 
					 
			$ilance->tax = construct_object('api.tax');
			if($ilance->tax->is_taxable($to_user,'escrow')=='1')
			{
		        $istaxable='1';
			}
		        else
			{
		        $istaxable='0';
			}
					 
			$unpa= $ilance->db->query("SELECT status
					  FROM " . DB_PREFIX . "invoices 
					  WHERE projectid = '".$de_be['projectid']."'
					  AND user_id = '".$from_user."'
					  ");
			while($un_pay = $ilance->db->fetch_array($unpa))
			{
			      if($un_pay['status']=='unpaid')
			      {
			      $amount= $de_be['amount'];
			      $ilance->tax = construct_object('api.tax');
                              
			      
			                           
			      // fetch tax amount to charge for this invoice type
			      if($ilance->tax->is_taxable($to_user, 'escrow')=='1')
			      $taxamount = $ilance->tax->fetch_amount($to_user, $amount, 'escrow', 0);
			      else
			      $taxamount = '0.00';
                               
			      // fetch total amount to hold within the "totalamount" field
			      $totalamount = ($amount + $taxamount);
                        
			      // fetch tax bit to display when outputing tax infos
			      $taxinfo = $ilance->tax->fetch_amount($to_user, $amount, 'escrow', 1);
	             
				  echo $query104="UPDATE " . DB_PREFIX . "invoices SET  user_id = '".$to_user."',
									  istaxable = '".$istaxable."',
					  taxamount = '".$taxamount."',
					  totalamount = '".$totalamount."',
					  taxinfo = '".$taxinfo."'
							  WHERE projectid = '".$de_be['projectid']."'
					  AND status = 'unpaid'
							  AND user_id = '".$from_user."'";
					echo ';<br>';               
		              }
		              else 
		              {
			      echo $query105= "UPDATE " . DB_PREFIX . "invoices SET  user_id = '".$to_user."'
				                      WHERE projectid = '".$de_be['projectid']."'
						      AND status = 'paid'
				                      AND user_id = '".$from_user."'";
					echo ';<br>';
		              }
	                }
	          }
	    }
      }
 }
    
        $from_user=$ilance->GPC['from_user'];
        $to_user=$ilance->GPC['to_user'];
     //if projects are in buynow process
     
      $buynow = $ilance->db->query("SELECT  project_id, orderid
			            FROM " . DB_PREFIX . "buynow_orders
			            WHERE  buyer_id = '".$from_user."'
		                   ");

if($ilance->db->num_rows($buynow)>0)
 {
      while($ded_bet1 = $ilance->db->fetch_array($buynow))
      {
		  
	    $unpaid1 = $ilance->db->query("SELECT  projectid,amount,status
			      FROM " . DB_PREFIX . "invoices 
			      WHERE  user_id = '".$from_user."'
			      AND projectid = '".$ded_bet1['project_id']."'
			      AND buynowid = '".$ded_bet1['orderid']."'
		              ");
	    if($ilance->db->num_rows($unpaid1)>0)
	    {
		   while($de_be1 = $ilance->db->fetch_array($unpaid1))
                  {
			echo $query106="UPDATE " . DB_PREFIX . "buynow_orders SET  buyer_id = '".$to_user."'
				          WHERE project_id = '".$de_be1['projectid']."'
				          AND buyer_id = '".$from_user."'";
			echo ';<br>';
			echo $query107="INSERT INTO " . DB_PREFIX . "resell_coins
				          (id,coin_id, from_user, to_user, dead_beat_date)
					  VALUES(
					  NULL,
					  '" . $de_be1['projectid'] . "',
					  '" . $from_user . "',
					  '" . $to_user . "',
					  '" . DATETIME24H . "')
					  ";
					  echo ';<br>';
			$ilance->tax = construct_object('api.tax');
			if($ilance->tax->is_taxable($to_user, 'escrow')=='1')
			{
		        $istaxable='1';
			}
		        else
			{
		        $istaxable='0';
			}
					 
			$unpa1= $ilance->db->query("SELECT status
					  FROM " . DB_PREFIX . "invoices 
					  WHERE projectid = '".$de_be1['projectid']."'
					  AND buynowid = '".$ded_bet1['orderid']."'
					  ");
			while($un_pay1 = $ilance->db->fetch_array($unpa1))
			{
			      if($un_pay1['status']=='cancelled')
			      {
			      $amount= $de_be1['amount'];
			      $ilance->tax = construct_object('api.tax');
                              
			      // fetch tax amount to charge for this invoice type
			      if($ilance->tax->is_taxable($to_user, 'escrow')=='1')
			      $taxamount = $ilance->tax->fetch_amount($to_user, $amount, 'escrow', 0);
			      else
			      $taxamount = '0.00';
                               
			      // fetch total amount to hold within the "totalamount" field
			      $totalamount = ($amount + $taxamount);
                        
			      // fetch tax bit to display when outputing tax infos
			      $taxinfo = $ilance->tax->fetch_amount($to_user, $amount, 'escrow', 1);
	             
	                      echo $query108="UPDATE " . DB_PREFIX . "invoices SET  user_id = '".$to_user."',
		                                      istaxable = '".$istaxable."',
						      taxamount = '".$taxamount."',
						      totalamount = '".$totalamount."',
						      taxinfo = '".$taxinfo."'
				                      WHERE projectid = '".$de_be1['projectid']."'
						      AND status = 'unpaid'
				                      AND user_id = '".$from_user."'";
					        echo ';<br>';       
		              }
		              else
		              {
			      echo $query109="UPDATE " . DB_PREFIX . "invoices SET  user_id = '".$to_user."',
				                      WHERE projectid = '".$de_be1['projectid']."'
									AND status = 'paid'
				                      AND user_id = '".$from_user."'";
									  echo ';<br>';
		              }
			}      
		  }
	    }
      } 
  }
   
    
   print_action_success('UserID Successfully Updated', 'auction_details_change.php');
}


// cancel the bidding items
 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'cancel_sale')
    {
	$show['no_cancel_list']=false;
	if(is_numeric($ilance->GPC['email']))
	{
		$user_id=fetch_user('user_id',$ilance->GPC['email']);
		$user_email=fetch_user('email',$ilance->GPC['email']);
		
	}
	else
	{
		$user_id=fetch_user('user_id',0,0,$ilance->GPC['email']);
		$user_email=$ilance->GPC['email'];
	}
	if($user_id>0)
	{
	$sql="SELECT i.projectid,p.project_title,i.invoiceid,i.p2b_user_id,i.amount,i.buynowid,date_end,date_format(date_end,'%d-%b-%y') as statement_date FROM " . DB_PREFIX . "invoices i 
	left join " . DB_PREFIX . "projects p on p.project_id=i.projectid
	WHERE  i.user_id='".$user_id."' and i.projectid>0 and i.status!='paid' and i.isbuyerfee=0";
	$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res)>0)
	{
		while($line=$ilance->db->fetch_array($res))
		{
		$sql1="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  combine_project like '%".$line['invoiceid']."%'";
		$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res1)>0)
		{
			while($line1=$ilance->db->fetch_array($res1))
			{
				$result['invoiceid']=$line['invoiceid'];
			}
		}
			if($line['buynowid']>0)
			{
			//item is buynow
			//find statement date
				$sql2="SELECT date_format(actual_end_date,'%d-%b-%y') as statement_date   FROM " . DB_PREFIX . "coin_relist WHERE  coin_id='".$line['projectid']."' and actual_end_date>='".$line['date_end']."'";
				$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($res2)>0)
				{
					while($line2=$ilance->db->fetch_array($res2))
					{
						$result['statementdate']= $line2['statement_date'];
						$result['checkbox_value']="buynow_".$line['buynowid'];
					}
				}
			}else
			{
			//auction item
			$result['statementdate']=$line['statement_date'];
				$sql3="SELECT *  FROM " . DB_PREFIX . "project_bids WHERE  project_id='".$line['projectid']."' and user_id='".$line['user_id']."' and bidstatus='awarded'";
				$res3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($res3)>0)
				{
					while($line3=$ilance->db->fetch_array($res3))
					{
						$result['checkbox_value']="bid_".$line3['bid_id'];
					}
				}
			
			}
		
		$result['seller_email']=fetch_user('email',$line['p2b_user_id']);
		$result['seller_id']=$line['p2b_user_id'];
		$result['projectid']=$line['projectid'];
		$result['project_title']=$line['project_title'];
		$result['amount']=$line['amount'];
		
		$cancel_list[]=$result;
		}
		
	}else
	{
	$show['no_cancel_list']=true;
	}
	
	}else
	{
	echo "check the user detail entered";
	exit;	
	}
	
	}		
				
	if (isset($ilance->GPC['subcmd']) && $ilance->GPC['subcmd'] == 'delete_buynow')
     {
	  $orderid=$ilance->GPC['id'];
	  $invoiceid=$ilance->GPC['invoiceid'];
	  $projectid=$ilance->GPC['projectid'];
	  $delete_buynow_qty=1;
	  $query="select qty from ".DB_PREFIX."buynow_orders where orderid=".$orderid;
	  $result=$ilance->db->query($query);
	  if($ilance->db->num_rows($result)>0)
	  {
		  while($line=$ilance->db->fetch_array($result))
		  {
		  $delete_buynow_qty=$line['qty'];
		  }
	  }
	  
	  $buy = $ilance->db->query("DELETE FROM ".DB_PREFIX."buynow_orders WHERE orderid = '".$orderid."'");
	  $inv = $ilance->db->query("DELETE FROM ".DB_PREFIX."invoices WHERE invoiceid = '".$invoiceid."'");
	  $inv1 = $ilance->db->query("DELETE FROM ".DB_PREFIX."invoices WHERE buynowid = '".$orderid."'");
	     $sql_project = $ilance->db->query("
											UPDATE  " . DB_PREFIX . "projects
											SET  buynow_qty = buynow_qty + ".$delete_buynow_qty.",
											     buynow_purchases = buynow_purchases - ".$delete_buynow_qty."
												 WHERE  project_id = '".$projectid."'
											");
											//coin table will update
											
									  $coin = $ilance->db->query("
											UPDATE  " . DB_PREFIX . "coins
											SET  sold_qty = sold_qty - ".$delete_buynow_qty."						
											 WHERE  coin_id = '".$projectid."'
											");		
	   	print_action_success('Buynow Item Deleted Successfully', 'auction_details_change.php');
	   
	 }

    if (isset($ilance->GPC['subbuynow']) AND $ilance->GPC['subbuynow'] == 'buynow_cancel')
     {
	 
	
		 if($ilance->GPC['user_id'])
		 {
		  $user_id = $ilance->GPC['user_id'];
		 }
		 if($ilance->GPC['username'])
		 {
		  $user_id = fetch_user('user_id',0,$ilance->GPC['username']);
		 }
	  
	       $itm = $ilance->db->query("SELECT orderid,project_id,buyer_id,owner_id,invoiceid,qty,amount
													FROM " . DB_PREFIX . "buynow_orders 
													WHERE  project_id = '".$ilance->GPC['proj_id']."'
													       and buyer_id = '".$user_id ."'  
														 ");
						 if($ilance->db->num_rows($itm))
						  {
						    while($row_list = $ilance->db->fetch_array($itm))
							{
							  $res['orderid'] = $row_list['orderid'];
							  $res['project_id'] = $row_list['project_id'];
							  $res['buyer_id'] = $row_list['buyer_id'];
							  $res['owner_id'] = $row_list['owner_id'];
							  $res['invoiceid'] = $row_list['invoiceid'];
							  $res['qty'] = $row_list['qty'];
							  $res['amount'] = $row_list['amount'];
							  $res['delete'] = '<a href="auction_details_change.php?subcmd=delete_buynow&id='.$row_list['orderid'].'&invoiceid='.$row_list['invoiceid'].'&projectid='.$row_list['project_id'].'">delete</a>';
							  $total_list[] = $res;
							}
						  }		
						  
						  else
						  {
						  $htm = 'No Result Found';
						  }						 
	   
	   
	 }
	 
if (isset($ilance->GPC['cancelcmd']) && $ilance->GPC['cancelcmd'] == 'auction_sale')
  {  
        if(isset($ilance->GPC['buyer_id'])>'0')
		 {
		  $userid = $ilance->GPC['buyer_id'];
		 }
		 else
		 {
		  $userid = fetch_user('user_id',0,$ilance->GPC['username']);
		 }
  
  //$userid = $ilance->GPC['buyer_id'];
  
$sql_projects = $ilance->db->query("
                       SELECT * FROM " . DB_PREFIX . "projects WHERE project_id = '".$ilance->GPC['proj_id']."' and status != 'open' and haswinner != '1' and filtered_auctiontype = 'regular'");
		if ($ilance->db->num_rows($sql_projects) > 0)
		{		
		  while($res = $ilance->db->fetch_array($sql_projects))
		  {
		  
		  if($ilance->GPC['current_price'] != '')
		  {
		  	$res['currentprice'] = $ilance->GPC['current_price'];
		  }
		  else
		  {
		  	$res['currentprice'] = $res['currentprice'];
		  }
		     $ilance->db->query("UPDATE " . DB_PREFIX . "projects
									SET haswinner = '1',
									bids = '1',
									winner_user_id = '".$userid."'
									WHERE project_id = '" . $res['project_id'] . "'
							", 0, null, __FILE__, __LINE__);
							
				// insert the next minimum bid for the bidder
              $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "project_bids
                        (bid_id, user_id, project_id, project_user_id, bidamount, qty, date_added, date_awarded, bidstatus, bidstate, state)
                        VALUES(
                        NULL,
                        '".$userid."',
                        '" . $res['project_id'] . "',
                        '" . $res['user_id'] . "',
                        '" . sprintf("%01.2f", $res['currentprice']) . "',
                        '1',
                        '" . $res['date_end'] . "',
						'" . $res['date_end'] . "',
                        'awarded',
                        'expired',
                        'product')
                ", 0, null, __FILE__, __LINE__);						
						
						 $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "proxybid
                        (user_id, project_id, maxamount,date_added)
                        VALUES(                       
                        '".$userid."',
                        '" . $res['project_id'] . "',                      
                        '" . sprintf("%01.2f", $res['currentprice']) . "',
						'".DATETIME24H."'
                        )
                ", 0, null, __FILE__, __LINE__);	
						
						$transactionid = construct_transaction_id();							
						  $ilance->db->query("INSERT INTO " . DB_PREFIX . "invoices
                                              (invoiceid, projectid, user_id,p2b_user_id, description, amount, totalamount, status, invoicetype, createdate, duedate, custommessage, transactionid)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $res['project_id'] . "',
                                                                                '".$userid."',
																				'".$res['user_id']."',
                                                                                '" . $ilance->db->escape_string($phrase['_escrow_transaction_fee_securing_funds_for_auction']) . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res['project_id'])) . ' #' . $res['project_id'] . "',
                                                                                '" . $ilance->db->escape_string($res['currentprice']) . "',																				 
                                                                                '" . $ilance->db->escape_string($res['currentprice']) . "',
                                                                                'unpaid',
                                                                                'escrow',
                                                                                '" . $res['date_end'] . "',
                                                                                '" . DATEINVOICEDUE . "',
                                                                                '" . $ilance->db->escape_string($phrase['_may_include_applicable_taxes']) . "',
                                                                                '" . $transactionid . "')
                                                                        ", 0, null, __FILE__, __LINE__);
																		
																	if($ilconfig['staffsettings_feeinnumber'] != 0)
																	{
																		$buyerfee_calnum = $ilconfig['staffsettings_feeinnumber'];
																	}
																	else
																	{
																		$buyerfee_calnum = 0;
																	}
																	if($ilconfig['staffsettings_feeinpercentage'] != 0)
																	{
																		$buyerfee_calper = ($res['currentprice'] * ($ilconfig['staffsettings_feeinpercentage'] / 100));
																	}
																	else
																	{
																		$buyerfee_calper = 0;
																	}
																	if($buyerfee_calnum <= $buyerfee_calper )
																	{																													
																		$buyerfee1 = $buyerfee_calper;																													
																	}
																	else
																	{																													
																		$buyerfee1 = $buyerfee_calnum;																													
																	}	
																												
																			$transactionidnew =  construct_transaction_id();
																			$ilance->db->query("INSERT INTO ".DB_PREFIX."invoices																			(projectid,user_id,p2b_user_id,description,amount,paid,totalamount,status,invoicetype,createdate,duedate,custommessage,isbuyerfee,transactionid)
																												VALUES(
																												'".intval($res['project_id'])."',
																												'".$userid."',
																												'".$res['user_id']."',
																												'buyer fees',
																												'".$buyerfee1."',
																												'".$buyerfee1."',
																												'".$buyerfee1."',
																												'paid',
																												'debit',
																												'".$res['date_end']."',
																												'".DATEINVOICEDUE."',
																												'buyer fees for buyer',
																												'1',
																												'".$transactionidnew."'																												
																												)");
																												 $buyerfee_id = $ilance->db->insert_id();
																												 
																													$ilance->db->query("
                                                                                                                        UPDATE " . DB_PREFIX . "projects
                                                                                                                        SET buyer_fee = '" . $buyerfee1 . "',
                                                                                                                        isbuyerfee = '1',
                                                                                                                        buyerfeeinvoiceid = '" . $buyerfee_id . "'
                                                                                                                        WHERE project_id = '" . $res['project_id'] . "'
                                                                                                                ", 0, null, __FILE__, __LINE__);	
																												
																												
																												
										  $coin = $ilance->db->query("
											UPDATE  " . DB_PREFIX . "coins
											SET  sold_qty = sold_qty + 1						
											 WHERE  coin_id = '".$res['project_id']."'
											");																			
																												
																																																							 
			$sql_bids = $ilance->db->query(" SELECT *
											FROM " . DB_PREFIX . "project_bids
											WHERE project_id = '" . $res['project_id'] . "'
									", 0, null, __FILE__, __LINE__);
									
			$res_bids = $ilance->db->fetch_array($sql_bids);		    
			
			$ilance->accounting_fees->construct_final_value_fee_new($res_bids['bid_id'], $res['user_id'], $res['project_id'], 'charge', 'product');
			
			print_action_success('This Item Sold Successfully', 'auction_details_change.php');
						
		 
		  }
		}
				
		   else
				{
				print_action_failed('We are sorry . Please check the Item Number and Buyerid and Selleing Price', 'auction_details_change.php');
				}			
	}
				
				
	 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'check')  
    {
		$checkbox=$ilance->GPC['documents'];		
		$document=implode(',',$checkbox);
		$coinid=array_filter(explode(",",$document));		
		 $total_coins = count($coinid);	   
			   if($total_coins > 0)
			   {
				   $count= 0; 
				   for($i=0;$i<$total_coins;$i++)
				   {
										
						$con_insert1 = $ilance->db->query("INSERT INTO " . DB_PREFIX . "coins  SELECT * FROM " . DB_PREFIX . "coins_retruned where coin_id='".$coinid[$i]."'");
						$con_delete = $ilance->db->query("delete FROM " . DB_PREFIX . "coins_retruned where coin_id='".$coinid[$i]."'");
						
						$con_delete1 = $ilance->db->query("delete FROM " . DB_PREFIX . "coin_return where coin_id='".$coinid[$i]."'");
						
						$con_update = $ilance->db->query("UPDATE ".DB_PREFIX."coins SET End_Date = '0000-00-00 00:00:00',project_id = '0' where coin_id='".$coinid[$i]."'");
						$count++;
				   }
			   		if($count==$total_coins)
					{
					print_action_success('Your Coin Details Returned successfully', 'auction_details_change.php');
					exit();	
					}
					else
					{
					 print_action_failed('Your Coin Details havent Returned successfully', 'auction_details_change.php');
					 exit();
					}
			   }
			   else
				{
				    print_action_failed("Please Select the Checkbox for Relist.", 'auction_details_change.php');
				   	exit();
				}
	}
				 
			$sql=$ilance->db->query("select * from ".DB_PREFIX."shippers order by basefee asc");
			if($ilance->db->num_rows($sql))
			{
			$html='';
			
			$html.='<select name="shipper" id="shipper">
			<option value="-1"  >Select Shipper</option>';
				while($line=$ilance->db->fetch_array($sql))
				{ 
				
				
				$html.='<option value="'.$line['shipperid'].'">'.$line['title'].'</option>';
				}
			} 
			
			
			
     //list of return
       $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
       $scriptpageprevnext = 'auction_details_change.php?';
				 
      if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
          $ilance->GPC['page'] = 1;
      else
          $ilance->GPC['page'] = intval($ilance->GPC['page']);
	  
        $con_listing = $ilance->db->query("SELECT charges,shipping_fees,user_id,coin_id,consign_id,return_date FROM " . DB_PREFIX . "coin_return
					  LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']."
					  ");
	 $con_listing1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coin_return");
         $number = (int)$ilance->db->num_rows($con_listing1);
        if($ilance->db->num_rows($con_listing) > 0)
        {
	    $row_con_list = 0;
	    while($row_list = $ilance->db->fetch_array($con_listing))
	    {	
														
	      $row_list['chargeamt'] = $ilance->currency->format($row_list['charges']); 
	      $row_list['shipamt'] = $ilance->currency->format($row_list['shipping_fees']); 
	      $row_list['sel'] =  fetch_user('username',$row_list['user_id']);
	      $row_list['returnback']='<input type="checkbox" name="documents[]" value="'.$row_list['coin_id'].'">';
	      $return_sale_list[] = $row_list;
	      $row_con_list++;
											
	    }
        }
        else
        {				
	    $show['no'] = 'return_list';
        }
	$return_pagnation = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);							
					


$pprint_array = array('given_projects','return_pagnation','number','remote_addr','rid','login_include_admin','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','htm','ilanceversion');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'auction_details_change.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('cancel_list','total_list','return_sale_list','change_buyers'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}