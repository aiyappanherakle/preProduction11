<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||
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


	/* vijay  for bug 5829 * start 20.12.13 */	
	$show_coin_search=0;
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='bid_edit')
	{
		$show_coin_search=1;
		$projec_id=(isset($ilance->GPC['project_id'])) ? $ilance->GPC['project_id'] : '';
		 
		$sql="SELECT c.Minimum_bid,p.project_id,p.project_title,p.project_details,p.filtered_auctiontype,p.user_id as project_user_id ,p.buynow_qty,p.buynow_price,p.max_qty,p.bids,p.project_state,p.description,p.status,p.currencyid,p.date_end,p.startprice,p.currentprice
		FROM " . DB_PREFIX . "coins AS c
		LEFT JOIN  " . DB_PREFIX . "projects AS p ON p.project_id= c.coin_id 
		WHERE p.status = 'open'
		and p.filtered_auctiontype ='regular'
		and c.coin_id = '".$projec_id."'
		GROUP BY c.coin_id";

	
		$bid_edit= $ilance->db->query($sql);
				
		if($ilance->db->num_rows($bid_edit) > 0)
		{
			while($update_coin=$ilance->db->fetch_array($bid_edit))
			{
			$ifcoins = true;
				if ($update_coin['project_id'] > 0) {
				$edit_reslt['project_id'] =  $update_coin['project_id'];
				$edit_reslt['project_id1'] = '<input type="hidden" name="project_id"  id="project_id"  value="'.$update_coin[project_id].'" size="8"/>';
				$edit_reslt['project_title'] = $update_coin['project_title'];
				$edit_reslt['bids'] = $update_coin['bids'];
				$edit_reslt['date_end'] = date("D, M d, Y h:i:s A",strtotime($update_coin['date_end']));
				if ($update_coin['bids'] == 0)
				{
				
				$edit_reslt['Minimum_bid'] = '<input type="text" name="Minimum_bid"  id="Minimum_bid"  value="'.$update_coin['Minimum_bid'].'" size="7"/>';	
				$edit_reslt['currentprice'] = '<input type="text" name="currentprice"  id="currentprice"  value="'.$update_coin['currentprice'].'" size="7"/>';	
				$editcoins = true;
				}
				else 
				{
				$get_project_bids_html = get_project_bids($update_coin['project_id']);
				$print_project_bids_html = $get_project_bids_html['html'];
				
				$get_proxy_bids_html = get_proxy_bids($update_coin['project_id']);
				$print_proxy_bids_html = $get_proxy_bids_html['proxybids_html'];
				
				// echo '<pre>';
				// print_r($get_project_bids);
				// exit;
				
				$edit_reslt['Minimum_bid'] = $update_coin['Minimum_bid'];
				$edit_reslt['currentprice'] = $update_coin['currentprice'];
				$editcoins = false;
					
				}
				
								
				$edit_coin[]=$edit_reslt;
				
				}
           			
			}
			
		}
		else
		{
			$ifcoins = false;
		}

			$pprint_array = array('print_project_bids_html','print_proxy_bids_html','projec_id','ifcoins','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
			$ilance->template->fetch('main', 'bid_edit.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('edit_coin'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
	}

	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='update_coins_tit_des')
	{


		/*echo '<pre>';
		print_r($ilance->GPC);
		exit;*/
		if((isset($ilance->GPC['project_id']) and $ilance->GPC['project_id']!='') 
		and ((isset($ilance->GPC['project_title']) and $ilance->GPC['project_title']!='') 
		or (isset($ilance->GPC['Description']) and $ilance->GPC['Description']!='')))
		{
			$project_title=(isset($ilance->GPC['project_title'])) ? $ilance->GPC['project_title'] : '';
			$Description=(isset($ilance->GPC['Description'])) ? $ilance->GPC['Description'] : '';
			if ($ilance->GPC['project_id'] > 0) 
			{
					

				// $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
					// SET project_title='".$ilance->db->escape_string($project_title)."',Description ='".$ilance->db->escape_string($Description)."'	
					// WHERE  project_id = '".$ilance->GPC['project_id']."'");

			  	// $ilance->db->query("UPDATE  " . DB_PREFIX . "coins
					// SET Title='".$ilance->db->escape_string($project_title)."',description='".$ilance->db->escape_string($Description)."'
					// WHERE  coin_id = '".$ilance->GPC['project_id']."'");

			}
			else
			{	

				// $ilance->db->query("UPDATE  " . DB_PREFIX . "coins
					// SET Title='".$project_title."',description='".$Description."'
					// WHERE  coin_id = '".$ilance->GPC['project_id']."'");
			}

			print_action_success('Coin Title & Descriptions updated Successfully.', $ilpage['listings']);
			exit();

		}
		else
		{
			 print_action_failed('sorry please fill title & descriptios and submit again', 'edit_coins_details.php');
			 exit();
	  	}

	}


	$pprint_array = array('projec_id','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'bid_edit.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('listpage'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	/* vijay  ends 3.12.13 */
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function get_project_bids($projects)
{
	global $ilance,$ilconfig;
	
	$get_project_bids_sql=$ilance->db->query("select * from ".DB_PREFIX."project_bids where project_id = '".$projects."' ORDER by bidamount DESC, bid_id DESC");
	$get_num_project_bids=$ilance->db->num_rows($get_project_bids_sql);
	if($get_num_project_bids > 0)
	{		
		
			$project_bids_html='';

			$project_bids_html.='<div class="block-wrapper">
			<div class="block3">
			<div class="block3-top">
			<div class="block3-right">
			<div class="block3-left"></div>
			</div>
			</div>
			<div class="block3-header">Bid Details - Total No:('.$get_num_project_bids.')</div>
			<div class="block3-content" style="padding:0px">
			<table cellpadding="9px" cellspacing="9px" border="0" width="100%" align="center">
			<form method="post" action="bid_edit.php" id="search" accept-charset="UTF-8" style="margin: 0px; padding-bottom:12px;" onsubmit="return validate();">	
			<input type="hidden" name="cmd" value="update_coins_tit_des"/>

			<tr class="alt2">
			<td>Cust ID</td>
			<td>Bid</td>
			<td>Bid placed</td>
			<td>Edit</td>
			<td>delete</td>																				
			</tr>';
																				
																				
			while($get_project_bids=$ilance->db->fetch_array($get_project_bids_sql))
			{

				
			$project_bids_html.='<tr class="alt1">
			<td>'.$get_project_bids['user_id'].'</td>
			<td>'.$get_project_bids['bidamount'].'</td>
			<td>'.date("D, M d, Y h:i:s A",strtotime($get_project_bids['date_added'])).'</td>
			<td>'.$get_project_bids['bid_id'].'</td>
			<td>'.$get_project_bids['bid_id'].'</td>
			</tr>';
						
			}
			
			$project_bids_html.='</form>
			</table>	 
			</div>
			<div class="block3-footer">
			<div class="block3-right">
			<div class="block3-left"></div>
			</div>
			</div>
			</div>
			</div>';
					
	}
	$print_project_bids['html']=$project_bids_html;
	return $print_project_bids;
}


function get_proxy_bids($projects)
{
	
	global $ilance,$ilconfig;
	
	$get_proxybid_sql=$ilance->db->query("select * from ".DB_PREFIX."proxybid where project_id = '".$projects."' ORDER by maxamount DESC, id DESC");
	$get_num_proxybid=$ilance->db->num_rows($get_proxybid_sql);
	if($get_num_proxybid > 0)
	{		
		
			$proxybidshtml='';

			$proxybidshtml.='<div class="block-wrapper">
			<div class="block3">
			<div class="block3-top">
			<div class="block3-right">
			<div class="block3-left"></div>
			</div>
			</div>
			<div class="block3-header">Proxy Bid Details - Total No:('.$get_num_proxybid.')</div>
			<div class="block3-content" style="padding:0px">
			<table cellpadding="9px" cellspacing="9px" border="0" width="100%" align="center">
			<form method="post" action="bid_edit.php" id="search" accept-charset="UTF-8" style="margin: 0px; padding-bottom:12px;" onsubmit="return validate();">	
			<input type="hidden" name="cmd" value="update_coins_tit_des"/>

			<tr class="alt2">
			<td>Cust ID</td>
			<td>Bid</td>
			<td>Bid placed</td>
			<td>Edit</td>
			<td>delete</td>																				
			</tr>';
																				
																				
			while($get_proxybid=$ilance->db->fetch_array($get_proxybid_sql))
			{
				
			$proxybidshtml.='<tr class="alt1">
			<td>'.$get_proxybid['user_id'].'</td>
			<td>'.$get_proxybid['maxamount'].'</td>
			<td>'.date("D, M d, Y h:i:s A",strtotime($get_proxybid['date_added'])).'</td>
			<td>'.$get_proxybid['id'].'</td>
			<td>'.$get_proxybid['id'].'</td>
			</tr>';
						
			}
			
			$proxybidshtml.='</form>
			</table>	 
			</div>
			<div class="block3-footer">
			<div class="block3-right">
			<div class="block3-left"></div>
			</div>
			</div>
			</div>
			</div>';
					
	}
	$print_proxybids['proxybids_html']=$proxybidshtml;
	return $print_proxybids;
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>