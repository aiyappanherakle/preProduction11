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
        'wantads',
        'lancekb',
        'lanceads',
        'stores',
        'warnings',
        'lancealert'
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
$navcrumb = array($ilpage['components'] => $ilcrumbs[$ilpage['components']]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['components']);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'components' AND isset($ilance->GPC['module']) AND isset($ilance->GPC['subcmd']) AND !isset($ilance->GPC['external']))
        {
		global $ilance;
		
		$ilmodule = '';
		$ilmodule = mb_strtolower(trim($ilance->GPC['module']));
		
		include(DIR_ADMIN . DIR_ADMIN_ADDONS_NAME . '/' . $ilmodule . '.mod.inc' . $ilconfig['globalsecurity_extensionmime']);
		exit();	
	}
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'components' AND isset($ilance->GPC['module']) AND isset($ilance->GPC['subcmd']) AND !isset($ilance->GPC['external']))
        {
		global $ilance;
		
		$ilmodule = '';
		$ilmodule = mb_strtolower(trim($ilance->GPC['module']));
		
		include(DIR_ADMIN . DIR_ADMIN_ADDONS_NAME . '/' . $ilmodule . '.mod.inc' . $ilconfig['globalsecurity_extensionmime']);
		exit();
	}
	
	// #### ADDON INSTALL MANAGER ##########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'install' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'mod')
        {
		while (list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;
		foreach ($_FILES as $key => $value)
		{
			$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
			foreach ($value as $ext => $value2)
			{
				$key2 = $key . '_' . $ext;
				$GLOBALS["$key2"] = $value2;
			}
		}
		
                $ignoreversion = (isset($ilance->GPC['ignoreversion']) AND $ilance->GPC['ignoreversion']) ? intval($ilance->GPC['ignoreversion']) : 0;
                $movephrases = (isset($ilance->GPC['movephrases']) AND $ilance->GPC['movephrases']) ? 1 : 0;
                $updatephrases = (isset($ilance->GPC['updatephrases']) AND $ilance->GPC['updatephrases']) ? 1 : 0;
		
		$xml = file_get_contents($xml_file);
                
                $ilance->admincp_products = construct_object('api.admincp_products');
		$ilance->admincp_products->install($xml, $ignoreversion, $movephrases, $updatephrases, $showmissingfiles = 1);
	}
	
	// #### ADDON UPGRADE MANAGER ##########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'upgrade' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'mod')
        {
		while (list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;
		foreach ($_FILES as $key => $value)
		{
			$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
			foreach ($value as $ext => $value2)
			{
				$key2 = $key . '_' . $ext;
				$GLOBALS["$key2"] = $value2;
			}
		}
		
		$xml = file_get_contents($xml_file);
                
                $movephrases = (isset($ilance->GPC['movephrases']) AND $ilance->GPC['movephrases']) ? 1 : 0;
                $updatephrases = (isset($ilance->GPC['updatephrases']) AND $ilance->GPC['updatephrases']) ? 1 : 0;
                $updateemails = (isset($ilance->GPC['updateemails']) AND $ilance->GPC['updateemails']) ? 1 : 0;
                
                $ilance->admincp_products = construct_object('api.admincp_products');
		$ilance->admincp_products->upgrade($xml, $movephrases, $updatephrases, $updateemails);
	}
	
	// #### UNINSTALL ADDON MANAGEMENT #####################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'uninstall')
        {
		$area_title = $phrase['_product_addon_uninstallation'];
		$page_title = SITE_NAME . ' - ' . $phrase['_product_addon_uninstallation'];
                
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'].'?cmd=install', $_SESSION['ilancedata']['user']['slng']);
		
		if (isset($ilance->GPC['modulegroup']) AND !empty($ilance->GPC['modulegroup']))
		{
                        $ilance->admincp_products = construct_object('api.admincp_products');
                        
                        $extra = '';
                        if (isset($ilance->GPC['showfiles']) AND $ilance->GPC['showfiles'])
                        {
                                $files = $ilance->admincp_products->print_file_dependencies($ilance->GPC['modulegroup']);
                                $extra = '<br /><br />Please remove <span style="color:blue">found</span> files below:<br /><br />' . $files;
                        }
                        
			if ($ilance->admincp_products->uninstall($ilance->GPC['modulegroup']))
			{
				print_action_success($phrase['_addon_product_was_uninstalled_from_your_control_panel'] . $extra, $ilpage['components']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_addon_product_could_not_be_uninstalled_using_the_uninstall_manager'], $ilpage['components']);
				exit();
			}
		}
	}
	
	// #### ADDON INSTALL MANAGEMENT #######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'install')
        {
		$area_title = $phrase['_addon_installation_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_addon_installation_menu'];
		
                $ilance->admincp_products = construct_object('api.admincp_products');
                
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'].'?cmd=install', $_SESSION['ilancedata']['user']['slng']);
                
		($apihook = $ilance->api('admincp_install_components_management')) ? eval($apihook) : false;
                
		$modulespulldown = $ilance->admincp_products->modules_pulldown();
                
                $pprint_array = array('buildversion','ilanceversion','login_include_admin','modulespulldown','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                ($apihook = $ilance->api('admincp_components_install_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'components_install.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
        {
		$area_title = $phrase['_product_addons_and_plugins'];
		$page_title = SITE_NAME . ' - ' . $phrase['_product_addons_and_plugins'];
		
                $ilance->admincp_products = construct_object('api.admincp_products');
                
		($apihook = $ilance->api('admincp_components_management')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'], $_SESSION['ilancedata']['user']['slng']);
		
		// fetch all modules
		$sqlmodgroup = $ilance->db->query("
                        SELECT modulegroup, modulename, version, versioncheckurl, developer
                        FROM " . DB_PREFIX . "modules_group
                ");
		while ($modgroupres = $ilance->db->fetch_array($sqlmodgroup))
		{
			$modulegroup[] = $modgroupres;
		}
		unset($modgroupres);
		    
		if (isset($ilance->GPC['external']) AND $ilance->GPC['external'])
		{
			$module = '';
			if (isset($ilance->GPC['module']))
			{
				$module = trim($ilance->GPC['module']);
			}
			
			$subcmd = '';
			if (isset($ilance->GPC['subcmd']))
			{
				$subcmd = $ilance->GPC['subcmd'];
			}
				
			$where = "WHERE modulegroup = 'unknown'";
			if (!empty($module))
			{
				$where = "WHERE modulegroup = '".$ilance->db->escape_string($module)."'";
			}
				
			$sqlmodgroups = $ilance->db->query("
                                SELECT id, modulegroup, modulename, folder, configtable, installcode, uninstallcode, version, versioncheckurl, url, developer
                                FROM " . DB_PREFIX . "modules_group
                                $where
                        ");
			while ($modgroupsres = $ilance->db->fetch_array($sqlmodgroups))
			{
				// construct tabs for this addon
				$sql = $ilance->db->query("
                                        SELECT id, parentkey, tab, modulegroup, template
					FROM " . DB_PREFIX . "modules
					WHERE modulegroup = '" . $modgroupsres['modulegroup'] . "'
					    AND sort = '-1'
					    AND subcmd = '" . $ilance->db->escape_string($subcmd) . "'
					ORDER BY sort ASC");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql))
					{
						$res['moduletab'] = '';
						$res['moduletab'] .= '<div class="tab-pane" style="width:100%" id="' . $modgroupsres['modulegroup'] . '"><div class="tab-page" id="' . $modgroupsres['modulegroup'] . '"><h2 class="tab">' . stripslashes($res['tab']) . '</h2>' . parse_php_in_html($res['template']) . '</div></div>';
						
						$GLOBALS['moduletabs' . $modgroupsres['id']][] = $res;
					}
				}
				$modulegroups[] = $modgroupsres;
			}
			unset($modgroupsres);
		}
		else
		{
			$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['components'], $ilpage['components'], $_SESSION['ilancedata']['user']['slng']);
			
			$module = '';
			if (isset($ilance->GPC['module']) AND $ilance->GPC['module'] != '')
			{
				$module = trim($ilance->GPC['module']);
			}
			
			$where = "WHERE modulegroup = 'unknown'";
			if (!empty($module))
			{
				$where = "WHERE modulegroup = '" . $ilance->db->escape_string($module) . "'";
			}
	
			// load requested module group
			$sqlmodgroups = $ilance->db->query("
                                SELECT id, modulegroup, modulename, folder, configtable, installcode, uninstallcode, version, versioncheckurl, url, developer
                                FROM " . DB_PREFIX . "modules_group
                                $where
                        ");
			while ($modgroupsres = $ilance->db->fetch_array($sqlmodgroups))
			{
				// HERE WE LOAD AND ALL MODULES TO PAGE TABS
									    
				$sql = $ilance->db->query("
                                        SELECT id, modulegroup, tab, template
                                        FROM " . DB_PREFIX . "modules
					WHERE modulegroup = '" . $modgroupsres['modulegroup'] . "'
					    AND sort != '-1'
					ORDER BY sort ASC
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
                                        $extra = $ilance->admincp_products->print_file_dependencies($modgroupsres['modulegroup']);
                                        
                                        // before we include the core products installed, let's find out if we have the necessary file sturucture
                                        if (!file_exists(DIR_API . 'class.' . $modgroupsres['modulegroup'] . '.inc.php'))
                                        {
                                                print_action_failed($phrase['_the_main_class_for_this_product_could_not_be_found'] . ' <strong>./functions/api/class.' . $modgroupsres['modulegroup'] . '.inc.php</strong>.  ' . $phrase['_please_remember_after_you_install_the_product_using_the_admincp_interface_you_must'] . '<br /><br />' . $extra, 'components.php');
                                        }
                                        if (!file_exists(DIR_XML . 'plugin_' . $modgroupsres['modulegroup'] . '.xml'))
                                        {
                                                print_action_failed($phrase['_the_main_xml_framework_for_this_product_could_not_be_found'].' <strong>./functions/xml/plugin_' . $modgroupsres['modulegroup'] . '.xml</strong>.  ' . $phrase['_please_remember_after_you_install_the_product_using_the_admincp_interface_you_must'] . '<br /><br />' . $extra, 'components.php');
                                        }
					while ($res = $ilance->db->fetch_array($sql))
					{
						$res['moduletab'] = '<!-- begin pane --><div class="tab-page" id="' . stripslashes($modgroupsres['modulegroup']) . '-' . $modgroupsres['id'] . '"><h2 class="tab">' . stripslashes($res['tab']) . '</h2>' . parse_php_in_html($res['template']) . '</div><!-- /begin pane -->';
						$GLOBALS['moduletabs' . $modgroupsres['id']][] = $res;
					}
				}
				$modulegroups[] = $modgroupsres;
			}
			unset($modgroupsres);
		}
    
		$cbaddons = '';
                $row_count = 0;
	    
		if (isset($modulegroup) AND is_array($modulegroup))
		{
			foreach ($modulegroup AS $key => $addon)
			{
				$class = ($row_count % 2) ? 'alt1' : 'alt1';
				if (isset($ilance->GPC['module']) AND $ilance->GPC['module'] == $addon['modulegroup'])
				{
					$cbaddons .= '
					<tr class="' . $class . ' featured_highlight">
						<td><span class="smaller gray">[' . $phrase['_in_use'] . ']</span> <strong><span class="blue"><a href="' . $ilpage['components'] . '?module=' . $addon['modulegroup'] . '"><strong>' . $addon['modulename'] . '</strong></a></span></td>
						<td><span class="gray">' . $addon['version'] . '</span></td>
						<td><span class="gray">' . $ilance->latest_addon_version($addon['versioncheckurl']) . '</span></td>
						<td><span class="gray">-</span></td>
						<td><span class="gray">-</span></td>
						<td><span class="gray">-</span></td>
                                                <td><span class="gray">' . stripslashes($addon['developer']) . '</span></td>
					</tr>';
				}
				else
				{
					$cbaddons .= '
					<tr class="' . $class . '">
						<td><span class="blue"><a href="' . $ilpage['components'] . '?module=' . $addon['modulegroup'] . '">' . $addon['modulename'] . '</a></span></td>
						<td><span class="gray">' . $addon['version'] . '</span></td>
						<td><span class="gray">' . $ilance->latest_addon_version($addon['versioncheckurl']) . '</span></td>
						<td><span class="gray">-</span></td>
						<td><span class="gray">-</span></td>
						<td><span class="gray">-</span></td>
                                                <td><span class="gray">' . stripslashes($addon['developer']) . '</span></td>
					</tr>';
				}
				$row_count++;
			}
		}
                
                if ($row_count == 0)
                {
                        $cbaddons .= '<tr><td colspan="7" align="center">' . $phrase['_no_products_or_plugins_found'] . '</td></tr>';               
                }
                
                $pprint_array = array('buildversion','ilanceversion','login_include_admin','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
                ($apihook = $ilance->api('admincp_components_end')) ? eval($apihook) : false;
                
		$ilance->template->fetch('main', 'components.html', 1);
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','modulegroups'));
		
		if (!isset($modulegroups))
		{
			$modulegroups = array();
		}
		@reset($modulegroups);
		while ($i = @each($modulegroups))
		{
			$ilance->template->parse_loop('main', 'moduletabs' . $i['value']['id']);
		}
		
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
        }
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>