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
	'administration','accounting'
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
		$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
		

    
 
		// listing resuls - issue coin list
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
		{
			
			$show['showsearch'] = true;
			
			$coin_type = $ilance->GPC['filterby'];
			

			$select_default_current_selling_coin = $ilance->db->query("SELECT *
													FROM " . DB_PREFIX . "issues_coin
													WHERE cointype = '".$coin_type."'
													ORDER BY releasedate DESC");
													
			$number = $ilance->db->num_rows($select_default_current_selling_coin);

			if ($ilance->db->num_rows($select_default_current_selling_coin) > 0)
			{
				
                               
					while ($result_default_current_selling_coin = $ilance->db->fetch_array($select_default_current_selling_coin, DB_ASSOC))
					{
					
						$result_default_current_selling_coin['id'];
						$result_default_current_selling_coin['title'];
						$result_default_current_selling_coin['description'];
						$result_default_current_selling_coin['releasedate'];
						$result_default_current_selling_coin['excepteddate'];
						$result_default_current_selling_coin['edit'] = '<a href="' . 'issues.php'. '?subcmd=_update-coin&amp;id=' . $result_default_current_selling_coin['id'] . '">
										<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
						$result_default_current_selling_coin['remove'] = '<a href="' . 'issues.php' . '?subcmd=deletecoin&amp;id=' . $result_default_current_selling_coin['id'] . '" 
											onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">
											<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
						$searchissues_coin[] = $result_default_current_selling_coin;
					}

					
			}
		}		

        // #### CREATE NEW issues ############################################
        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-new-issues')
        {
			
				$show['show_update'] = false;
				$show['showsearch'] = false;
				
				
			$issues_title = $ilance->GPC['title'];
			$issues_description = $ilance->GPC['description'];
			$issues_releasedate = $ilance->GPC['releasedate'];
			$issues_excepteddate = $ilance->GPC['excepteddate'];
			$issues_link = $ilance->GPC['link'];
			$issues_catissues = $ilance->GPC['catissues'];
			$issues_formCoin = $ilance->GPC['formCoin'];

			
			$issue_coin_insert = $ilance->db->query("INSERT INTO " . DB_PREFIX . "issues_coin
										(id, title, description, releasedate, excepteddate, link, categorieslink, cointype)
										VALUES (
										NULL,
										'" . $issues_title . "',
										'" . $issues_description . "',
										'" . $issues_releasedate . "',
										'" . $issues_excepteddate . "',
										'" . $issues_link . "',
										'" . $issues_catissues . "',
										'" . $issues_formCoin . "')");
								
		print_action_success('Your issues Coin Successfully Added', 'issues.php' . '?cmd=create');
								exit();
			
        }
		

 

			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-coin' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
			{

						$show['show_update'] = true;
                        $show['showsearch'] = false;
					
				$update_issues_coin = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "issues_coin
                                WHERE id = '" . intval($ilance->GPC['id']) . "'
                                LIMIT 1
                        ");	
				if ($ilance->db->num_rows($update_issues_coin) > 0)
				{
					while ($res_update_issues_coin = $ilance->db->fetch_array($update_issues_coin, DB_ASSOC))
					{
						$cointype1 = 'Current Selling Coins';
						$cointype2 = 'Upcoming Coins';

						$cointype_html='<select name="cointype" >';
				
							if($res_update_issues_coin['cointype'] == 1)
							{
								$cointype_html.='<option value="'.$res_update_issues_coin['cointype'].'" selected="selected">'.$cointype1.'</options>';
								$cointype_html.='<option value="0">'.$cointype2.'</options>';
							}
							else
							{
								$cointype_html.='<option value="1" >'.$cointype1.'</options>';
								$cointype_html.='<option value="'.$res_update_issues_coin['cointype'].'" selected="selected">'.$cointype2.'</options>';
								
							}
						
					   $cointype_html.='</select>';

			   
						$res_update_issues_coin['title'];
						$res_update_issues_coin['description'];
						$res_update_issues_coin['releasedate'];
						$res_update_issues_coin['excepteddate'];
						$res_update_issues_coin['link'];
						$res_update_issues_coin['categorieslink'];
						$res_update_issues_coin['cointype'];	

						$profile_coin[] = $res_update_issues_coin;
					}
				}
			}

			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-issues-coin' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
			{
				
				$ilance->db->query("
                        UPDATE " . DB_PREFIX . "issues_coin
                        SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
                        description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                        releasedate = '" . $ilance->db->escape_string($ilance->GPC['releasedate']) . "',
                        excepteddate = '" . $ilance->db->escape_string($ilance->GPC['excepteddate']) . "',
						link = '" . $ilance->db->escape_string($ilance->GPC['link']) . "',
                        categorieslink = '" . $ilance->db->escape_string($ilance->GPC['categorieslink']) . "',
                        cointype = '" . $ilance->db->escape_string($ilance->GPC['cointype']) . "'
                        WHERE id = '" . intval($ilance->GPC['id']) . "'
						");				
				
					$notice = 'the coins profile has been updated with new changes';
					print_action_success($notice, 'issues.php' . '?cmd=create');
					exit();			
				
			}  



        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletecoin' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
			$ilance->db->query("DELETE 
								FROM " . DB_PREFIX . "issues_coin 
								WHERE id = '" . intval($ilance->GPC['id']) . "' ");		
								
				print_action_success('the selected coins were removed from the coins issues.' , 'issues.php' . '?cmd=create');
				exit();
			
        }
			
			
			
			
			
			
                $pprint_array = array('number','cointype_html');
        	
				$ilance->template->fetch('main', 'issues.html', 2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('searchissues_coin','profile_coin'));
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