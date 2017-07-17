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
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{	
$show['search_list'] = false;
$filter_userid = 'no';
$filtervalue = isset($ilance->GPC['filtervalue']) ? $ilance->GPC['filtervalue']: '';

	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search_by_user') 
	{
		if(isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) 
		{
			$where = "WHERE ".$ilance->GPC['filterby']."='".$ilance->GPC['filtervalue']."' ";
			$sql = $ilance->db->query("
                                SELECT user_id,username
                                FROM " . DB_PREFIX . "users
                                $where
                        	", 0, null, __FILE__, __LINE__);

			if($ilance->db->num_rows($sql) > 0)
			{
				while ($user = $ilance->db->fetch_array($sql))
				{
					// echo '<pre>';
					// print_r($user);
					// exit;
					$username = $user['username'];
					$filter_userid = $user['user_id'];
					// echo "
					// 	SELECT createdate, duedate, paiddate, totalamount, paid, description, status, invoiceid, transactionid, paymethod, invoicetype
					// 	FROM " . DB_PREFIX . "invoices
					// 	WHERE user_id = '" . $user['user_id'] . "'
					// 		AND status = 'scheduled'
					// ";exit;

					// echo '<pre>';
					// print_r($ilance->GPC);
					// exit;
					//AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0


					$sql = $ilance->db->query("select i.invoiceid,i.user_id,u.first_name,u.last_name,u.email,u.username,i.status,i.amount,i.totalamount,i.paymethod,i.paiddate,i.paid, i.combine_project ,s.shipped_items,ns.non_shipped_items, LENGTH(i.combine_project) - LENGTH(REPLACE(i.combine_project, ',', ''))+1 as invoice_item_count 
						from " . DB_PREFIX . "invoices i 
						left join " . DB_PREFIX . "users u on u.user_id=i.user_id 
						left join (select count(ship_id) as shipped_items,final_invoice_id from " . DB_PREFIX . "shippnig_details where track_no!='' group by final_invoice_id) s on s.final_invoice_id=i.invoiceid 
						left join (select count(ship_id) as non_shipped_items,final_invoice_id from " . DB_PREFIX . "shippnig_details where track_no='' group by final_invoice_id) ns on ns.final_invoice_id=i.invoiceid 
						where i.combine_project!='' AND u.user_id = '".$user['user_id']."' AND i.status!='paid'AND i.paid=0 AND s.shipped_items is NULL
						group by i.invoiceid order by i.invoiceid desc", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0) {
				$show['search_list'] = true;
				while ($res = $ilance->db->fetch_array($sql)) {
					// echo '<pre>';
					// print_r($res);
					// exit;
					if ($res['status'] == 'paid') {
						$res['status'] = ucfirst(strtolower($res['status']));
						//new changes
						$status_paid = $res['status'];
					}
					if ($res['status'] != 'Paid') {
						if ($res['paid'] == 0) {
							$res['status'] = 'Unpaid';
						} else {
							$res['status'] = '<a href=' . $ilpage['buyers'] . '?cmd=buyer&amp;subcmd=update&amp;invoiceid=' . intval($res['invoiceid']) . '&amp;user_id=' . intval($res['user_id']) . '>Partially Paid</a>';
						}

						$status_paid = 'Unpaid';
					}
					// error_reporting(E_ALL);

					$res['user_name'] = '<a href="' . 'users.php?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"">' . $res['username'] . '</a>';
					//new change apr25 new variable added in line
					$res['details'] = '<a href="buyers.php?subcmd=_detail_invoice&user_id=' . $res['user_id'] . '&paidstatus=' . $status_paid . '&amp;id=' . $res['invoiceid'] . '"">Items</a>';
					$res['print'] = '<a href="print_invoice2.php?id=' . $res['invoiceid'] . '"">Print</a>';
					$res['paymethod'] = ucfirst(strtolower($res['paymethod']));
					if ($res['invoice_item_count'] <= $res['shipped_items']) {
						$res['ship_status'] = 'Y';
					} elseif ($res['shipped_items'] > 0 and $res['shipped_items'] < $res['invoice_item_count']) {
						$res['ship_status'] = 'Partial';
					} else if ($res['shipped_items'] == 0) {
						$res['ship_status'] = 'N';
					}

					$invoicelist[] = $res;

				}
				$show['invoicelist'] = true;

			} else {
				$show['search_list'] = true;
				$show['invoicelist_empty'] = true;
			}





				}
			}	
			else
			{

			}
			
		}
		else {
			print_action_failed('Please Enter user details to search invoices', 'change_checkedout_invoices.php');
			exit();
		}
		
	}
	


		


	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'change_user') 
	{

		if ((isset($ilance->GPC['change_to_userid']) AND empty($ilance->GPC['change_to_userid']))  OR (isset($ilance->GPC['user_id']) AND empty($ilance->GPC['user_id'])))
		{
			print_action_failed('Something went wrong. Please try again', 'change_checkedout_invoices.php');
			exit();
		}




		if (isset($ilance->GPC['checked_invoice_id']) AND is_array($ilance->GPC['checked_invoice_id'])  AND count($ilance->GPC['checked_invoice_id'])>0 ) 
		{

			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'Print')
		{
			//echo '<pre>';print_r($ilance->GPC);exit;
			$invoice_ids = $ilance->GPC['checked_invoice_id'];
			$user_id = $ilance->GPC['user_id'];
			$change_to_userid = $ilance->GPC['change_to_userid'];

			//header('Content-type: application/csv; charset="' . $ilconfig['template_charset'] . '"');
			//header("Content-Disposition: \"inline; filename=invoice-(" . date('Y') . "-" . date('m') . "-" . date('d') . ").csv");
			$coinids = array();
			$bkup_csv = '';
			foreach($invoice_ids as $invoice_id)
			{
				echo $invoice_id.'<br/>';
				
				$sql1 = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices WHERE invoiceid = ".$invoice_id." AND user_id=".$user_id, 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql1) > 0) 
				{
					$bkup_csv .= $ilance->db->fields_backup(DB_PREFIX . "invoices");

					while ($res1 = $ilance->db->fetch_array($sql1, DB_ASSOC)) 
					{
						$bkup_csv .= '"'.implode('","', $res1).'" ';
						$bkup_csv .= "\n";
						
						$invoices = $res1['combine_project'];
						
						
						$sql2 = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices WHERE invoiceid IN (".$invoices.") AND user_id=".$user_id, 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0) 
						{
							
							while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC)) 
							{

								$coinids[] = $res2['projectid'];
								if(fetch_auction('filtered_auctiontype', $res2['projectid']) == 'regular')
								{
									$sqlr = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices WHERE projectid = '".$res2['projectid']."' AND user_id =".$user_id, 0, null, __FILE__, __LINE__);
									while ($resr = $ilance->db->fetch_array($sql2, DB_ASSOC)) 
									{
										$bkup_csv .= '"'.implode('","', $resr).'" \n';
									}

									
								}
								else
								{
									$bkup_csv .= '"'.implode('","', $res2).'" \n';
								}
							}
							
							if(isset($coinids) AND is_array($coinids) AND count($coinids)>0)
							{
								$coinidss = implode(',',$coinids);

								$bkup_csv .= "\n\n";
								$bkup_csv .= $ilance->db->fields_backup(DB_PREFIX . "coins");
								$sql4 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "coins WHERE coin_id IN (".$coinidss.") ", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql4) > 0) 
								{
									

									while ($res4 = $ilance->db->fetch_array($sql4,DB_ASSOC)) 
									{
										$bkup_csv .= '"'.implode('","', $res4).'" ';
										$bkup_csv .= "\n";
									}
								}

								$bkup_csv .= "\n\n";
								$bkup_csv .= $ilance->db->fields_backup(DB_PREFIX . "projects");
								$sql3 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE project_id IN (".$coinidss.") ", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql3) > 0) 
								{
									
									while ($res3 = $ilance->db->fetch_array($sql3,DB_ASSOC)) 
									{
										$bkup_csv .= '"'.implode('","', $res3).'" ';
										$bkup_csv .= "\n";

										if($res3['filtered_auctiontype']=='regular')
											$coin[] = $res3['project_id'];
										else	
											$project[] = $res3['project_id'];
									}


									if(isset($coin) AND is_array($coin) AND count($coin)>0)
									{
										$coins = implode(',',$coin);
										if($coins != '')
										{
											//proxybid
											$bkup_csv .= "\n\n";
											$bkup_csv .= $ilance->db->sql_backup(DB_PREFIX . "proxybid", "project_id IN (".$coins.") AND user_id =".$user_id);

											//project_bids
											$bkup_csv .= "\n\n";
											$bkup_csv .= $ilance->db->sql_backup(DB_PREFIX . "project_bids", "user_id = '".$user_id."' AND project_id IN (".$coins.") ");
											
											//escrow
											$bkup_csv .= "\n\n";
											$bkup_csv .= $ilance->db->sql_backup(DB_PREFIX . "projects_escrow", "project_id IN (".$coins.") AND user_id = ".$user_id);
										}
										
										
									}

									if(isset($project) AND is_array($project) AND count($project)>0)
									{
										$projects = implode(',',$project);
										$bkup_csv .= "\n\n";
										if($projects != '')
											$bkup_csv .= $ilance->db->sql_backup(DB_PREFIX . "buynow_orders", "project_id IN (".$projects.") AND buyer_id = ".$user_id);
									}
								}
							}

					
						}
					}
				}
			}
			echo $bkup_csv;
			exit;
			//header("Content-Disposition: \"inline; filename=invoice-(" . date('Y') . "-" . date('m') . "-" . date('d') . ").csv");
			
		}
			

			$changed_invoiceid = '';
			$invoice_ids = $ilance->GPC['checked_invoice_id'];
			$user_id = $ilance->GPC['user_id'];
			$change_to_userid = $ilance->GPC['change_to_userid'];
			foreach($invoice_ids as $invoice_id)
			{
				$changed_invoiceid .=  '#'.$invoice_id.'<br/>';

				$sql1 = $ilance->db->query("SELECT combine_project  FROM " . DB_PREFIX . "invoices WHERE invoiceid = ".$invoice_id." AND user_id=".$user_id, 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql1) > 0) 
				{
					while ($res1 = $ilance->db->fetch_array($sql1)) 
					{
						
						//echo "UPDATE " . DB_PREFIX . "invoices SET user_id='".$change_to_userid."' WHERE invoiceid = ".$invoice_id." AND user_id=".$user_id;

						$ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET user_id='".$change_to_userid."' WHERE invoiceid = ".$invoice_id." AND user_id=".$user_id);

						//echo '<br/>'."SELECT *  FROM " . DB_PREFIX . "invoices WHERE invoiceid IN (".$res1['combine_project'].") AND user_id=".$user_id;exit;
						$sql2 = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "invoices WHERE invoiceid IN (".$res1['combine_project'].") AND user_id=".$user_id, 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0) 
						{
							while ($res2 = $ilance->db->fetch_array($sql2)) 
							{
								
								//echo "UPDATE " . DB_PREFIX . "invoices SET user_id='".$change_to_userid."' WHERE invoiceid = ".$res2['invoiceid']." AND user_id=".$user_id;
								$ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET user_id='".$change_to_userid."' WHERE invoiceid = (".$res2['invoiceid'].") AND user_id=".$user_id);

								
								if(fetch_auction('filtered_auctiontype', $res2['projectid']) == 'regular')
								{

									//echo "UPDATE " . DB_PREFIX . "invoices SET user_id='".$change_to_userid."'  WHERE projectid = '".$res2['projectid']."' AND user_id =".$user_id;
									$ilance->db->query("UPDATE " . DB_PREFIX . "invoices SET user_id='".$change_to_userid."'  WHERE projectid = '".$res2['projectid']."' AND user_id =".$user_id);
									
									//echo "UPDATE " . DB_PREFIX . "projects SET winner_user_id='".$change_to_userid."' WHERE project_id = '".$res2['projectid']."' AND winner_user_id =".$user_id;
									$ilance->db->query("UPDATE " . DB_PREFIX . "projects SET winner_user_id='".$change_to_userid."' WHERE project_id = '".$res2['projectid']."' AND winner_user_id =".$user_id);

									$chk_sql = $ilance->db->query("SELECT *  FROM " . DB_PREFIX . "proxybid WHERE project_id = '".$res2['projectid']."' AND user_id =".$change_to_userid, 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($chk_sql) > 0) 
									{
										
										$ilance->db->query(" DELETE * FROM " . DB_PREFIX . "proxybid
                                                      WHERE project_id = '".$res2['projectid']."' AND user_id = ".$change_to_userid);

										
									}

									
									$ilance->db->query("UPDATE " . DB_PREFIX . "proxybid SET user_id='".$change_to_userid."' WHERE project_id = '".$res2['projectid']."' AND user_id = ".$user_id);
									
									$ilance->db->query("UPDATE " . DB_PREFIX . "project_bids SET user_id='".$change_to_userid."' WHERE user_id = '".$user_id."' AND project_id = '".$res2['projectid']."' ");
									
									//echo "UPDATE " . DB_PREFIX . "projects_escrow SET user_id='".$change_to_userid."' WHERE project_id = '".$res2['projectid']."' AND user_id = ".$user_id;
									$ilance->db->query("UPDATE " . DB_PREFIX . "projects_escrow SET user_id='".$change_to_userid."' WHERE project_id = '".$res2['projectid']."' AND user_id = ".$user_id);
									
								}
								else
								{
									$ilance->db->query("UPDATE " . DB_PREFIX . "buynow_orders SET buyer_id='".$change_to_userid."' WHERE project_id = '".$res2['projectid']."' AND buyer_id = ".$user_id." AND invoiceid=".$res2['invoiceid']);			
								}
							}
						}
					}
				}
			}
			print_action_success('Changed Successfully. <br/><br/>Changed Invoices: <br/>'.$changed_invoiceid, 'change_checkedout_invoices.php');
			exit();
		}
		else
		{
			print_action_failed('Please Select any one invoice', 'change_checkedout_invoices.php');
			exit();
		}
	}					   
						   
							
							
							       
										
										
										
	$pprint_array = array('filter_userid','username','filtervalue','invoices_count','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'change_checkedout_invoices.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('invoicelist','pending_list_search'));
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