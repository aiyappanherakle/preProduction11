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

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{


	/* vijay  for bug 5829 * start 20.12.13 */	
	$show_coin_search=0;
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='update_coin_details')
	{
		$show_coin_search=1;
		$projec_id=(isset($ilance->GPC['project_id'])) ? $ilance->GPC['project_id'] : '';
		
		$sql_check="SELECT * FROM " . DB_PREFIX . "projects WHERE ((filtered_auctiontype = 'regular' AND winner_user_id  > '1' AND bids > '0') OR (buynow = '1' AND filtered_auctiontype = 'fixed' AND buynow_qty = '0')) AND  project_id = '".$projec_id."'";
		$update_coin_details_check= $ilance->db->query($sql_check);
				if($ilance->db->num_rows($update_coin_details_check) > 0)
				{
		 print_action_failed('Please give valid Coin id details', 'edit_coins_details.php');
					 exit();
				}	 

		$sql="
				SELECT p.project_id,p.project_title,p.Description,c.coin_id,c.Title,c.description FROM " . DB_PREFIX . "coins AS c
				LEFT JOIN  " . DB_PREFIX . "projects AS p ON p.project_id= c.coin_id 
				WHERE c.coin_id = '".$projec_id."'
				GROUP BY coin_id";

		$update_coin_details= $ilance->db->query($sql);
				
		if($ilance->db->num_rows($update_coin_details) > 0)
		{
			while($update_coin=$ilance->db->fetch_array($update_coin_details))
			{
			$ifcoins = true;
			if ($update_coin['project_id'] > 0) {
            $edit_reslt['project_id'] =  $update_coin['project_id'];
            $edit_reslt['project_id1'] = '<input type="hidden" name="project_id"  id="project_id"  value="'.$update_coin[project_id].'" size="8"/>';
            $edit_reslt['project_title'] = '<input type="text" name="project_title"  id="project_title"  value="'.$update_coin[project_title].'" cols="82" rows="8" size="60"/>';
            $edit_reslt['Description'] = '<textarea id="Description" name="Description" cols="82" rows="10">'.$update_coin[Description].'</textarea>';
            }
            else
            {
            $edit_reslt['project_id'] = $update_coin['coin_id'];
            $edit_reslt['project_id1'] = '<input type="hidden" name="project_id"  id="project_id"  value="'.$update_coin[coin_id].'" size="8"/>';
            $edit_reslt['project_title'] = '<input type="text" name="project_title"  id="project_title"  value="'.$update_coin[Title].'" size="60"/>';
            $edit_reslt['Description'] = '<textarea id="Description" name="Description" cols="82" rows="10">'.$update_coin[Description].'</textarea>';
            

            }
            $edit_coin[]=$edit_reslt;
            
			}

			
		}
		else
		{
			$ifcoins = false;
		}

			$pprint_array = array('projec_id','ifcoins','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
			$ilance->template->fetch('main', 'edit_coins_details.html', 2);
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
					

				$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
					SET project_title='".$ilance->db->escape_string($project_title)."',Description ='".$ilance->db->escape_string($Description)."'	
					WHERE  project_id = '".$ilance->GPC['project_id']."'");

			  	$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
					SET Title='".$ilance->db->escape_string($project_title)."',description='".$ilance->db->escape_string($Description)."'
					WHERE  coin_id = '".$ilance->GPC['project_id']."'");

			}
			else
			{	

				$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
					SET Title='".$project_title."',description='".$Description."'
					WHERE  coin_id = '".$ilance->GPC['project_id']."'");
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
	$ilance->template->fetch('main', 'edit_coins_details.html', 2);
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
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>