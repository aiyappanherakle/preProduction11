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
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['dashboard']);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
        // #### BACKUP MARKETPLACE CONFIGURATION ###############################
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'news' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'dismiss' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "admincp_news
                        SET visible = '0'
                        WHERE newsid = '" . intval($ilance->GPC['id']) . "'
                ");
                
                refresh(HTTPS_SERVER_ADMIN . $ilpage['dashboard']);
                exit();
        }
                
                
        // #### BACKUP MARKETPLACE CONFIGURATION ###############################
        else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'backup')
        {
                $area_title = $phrase['_exporting_marketplace_configuration_to_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_exporting_marketplace_configuration_to_xml'];
                
                $xml_output = "<?xml version=\"1.0\"?>" . LINEBREAK;
                $xml_output .= "<!-- This configuration build was generated on " . DATETIME24H . " //-->" . LINEBREAK;
                $xml_output .= "<config ilversion=\"" . $ilance->config['ilversion'] . "\">" . LINEBREAK;
                
                $query2 = $ilance->db->query("
                        SELECT parentgroupname, groupname, description, help, sort
                        FROM " . DB_PREFIX . "configuration_groups
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($query2) > 0)
                {
                        while ($groupres = $ilance->db->fetch_array($query2))
                        {
                                $xml_output .= "\t<configgroup parentgroupname=\"" . stripslashes($groupres['parentgroupname']) . "\" groupname=\"" . stripslashes($groupres['groupname']) . "\" description=\"" . stripslashes(ilance_htmlentities($groupres['description'])) . "\" help=\"" . stripslashes(ilance_htmlentities($groupres['help'])) . "\" sort=\"" . stripslashes($groupres['sort']) . "\">" . LINEBREAK;
                                
                                $query3 = $ilance->db->query("
                                        SELECT name, description, value, configgroup, inputtype, inputcode, inputname, help, sort, visible
                                        FROM " . DB_PREFIX . "configuration
                                        WHERE configgroup = '" . $groupres['groupname'] . "'
                                        ORDER BY sort ASC
                                ");
                                if ($ilance->db->num_rows($query3) > 0)
                                {
                                        while ($res = $ilance->db->fetch_array($query3))
                                        {
                                                $xml_output .= "\t\t<setting name=\"" . stripslashes($res['name']) . "\" description=\"" . stripslashes(ilance_htmlentities($res['description'])) . "\" value=\"" . ilance_htmlentities($res['value']) . "\" configgroup=\"" . $res['configgroup'] . "\" inputtype=\"" . $res['inputtype'] . "\" inputcode=\"" . ilance_htmlentities($res['inputcode']) . "\" inputname=\"" . $res['inputname'] . "\" help=\"" . stripslashes(ilance_htmlentities($res['help'])) . "\" sort=\"" . $res['sort'] . "\" visible=\"" . $res['visible'] . "\"></setting>" . LINEBREAK;
                                        }
                                }
                                $xml_output .= "\t</configgroup>" . LINEBREAK;
                        }
                }
                $xml_output .= "</config>";
                
                $ilance->common->download_file($xml_output, 'ilance_' . VERSIONSTRING . '_config.xml', 'text/plain');
                exit();
        }
        
        // #### RESTORE MARKETPLACE CONFIGURATION ##############################
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'restore')
        {
                $area_title = $phrase['_restoring_marketplace_configuration_via_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_restoring_marketplace_configuration_via_xml'];
		
		while (list($key, $value) = each($_FILES))
		{
			$GLOBALS[$key] = $value;
			foreach ($_FILES as $key => $value)
			{
				$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
				foreach ($value as $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$GLOBALS[$key2] = $value2;
				}
			}
		}
		
		$xml = file_get_contents($xml_file);
		$xml_encoding = '';
                
		if (MULTIBYTE)
		{
			$xml_encoding = mb_detect_encoding($xml);
		}
		
		if ($xml_encoding == 'ASCII')
		{
			$xml_encoding = '';
		}
	
		$data = array();
		$parser = xml_parser_create($xml_encoding);
		xml_parse_into_struct($parser, $xml, $data);
		$error_code = xml_get_error_code($parser);
		xml_parser_free($parser);
		if ($error_code == 0)
		{
			// process our xml language package
			
			$ilance->xml = construct_object('api.xml');
			
			$result = $ilance->xml->process_config_xml($data, $xml_encoding);
                        print_r($result);
                        exit;
			if ($result['ilversion'] != $ilance->config['ilversion'])
			{
				print_action_failed($phrase['_the_version_of_the_this_configuration_xml_package_is_different_than_the_currently_installed_version_of_ilance'] . ' <strong><em>' . $ilance->config['ilversion'] . '</em></strong>.  ' . $phrase['_the_operation_has_aborted_due_to_a_version_conflict'], $ilance->GPC['return']);
				exit();
			}
		}
		else
		{
			print_action_failed($phrase['_were_sorry_there_was_an_error_with_the_formatting_of_the_configuration_file'] . ' [' . xml_error_string($error_code) . '].', $ilance->GPC['return']);
			exit();
		}        
        }
        
	$dashboard = array();
        
	// mysql version
	$version = $ilance->db->query_fetch("SELECT VERSION() AS version");
	$dashboard['mysqlversion'] = $version['version'];
	
	// mysql max_packet_allowed
	if ($variables = $ilance->db->query_fetch("SHOW VARIABLES LIKE 'max_allowed_packet'"))
	{
		$dashboard['mysqlpacketsize'] = print_filesize($variables['Value']);
	}
	else
	{
		$dashboard['mysqlpacketsize'] = 'n/a';
	}
	
	// web server
	if (preg_match('#(Apache)/([0-9\.]+)\s#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs))
	{
		$dashboard['webserver'] = "$wsregs[1] v$wsregs[2]";
	}
	else if (preg_match('#Microsoft-IIS/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs))
	{
		$dashboard['webserver'] = "IIS v$wsregs[1]";
	}
	else if (preg_match('#Zeus/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs))
	{
		$dashboard['webserver'] = "Zeus v$wsregs[1]";
	}
	else if (mb_strtoupper($_SERVER['SERVER_SOFTWARE']) == 'APACHE')
	{
		$dashboard['webserver'] = 'Apache';
	}
	else
	{
		$dashboard['webserver'] = $phrase['_unknown'];
	}
    
	// server type
	$info = iif(ini_get('safe_mode') == 1 OR mb_strtolower(ini_get('safe_mode')) == 'on', "<br />Safe Mode Enabled");
	$info .= iif(ini_get('file_uploads') == 0 OR mb_strtolower(ini_get('file_uploads')) == 'off', "<br />File Uploads Disabled");
    
	if (PHP_OS == 'WINNT')
	{
		$dashboard['servertype'] = 'Windows NT/XP';
	}
	else
	{
		$dashboard['servertype'] = PHP_OS . $info;
	}
	
	// php settings
	$dashboard['phpmaxpost'] = ini_get('post_max_size');
	$dashboard['phpversion'] = PHP_VERSION;
	$dashboard['phpmaxupload'] = ini_get('upload_max_filesize');
	
        $memorylimit = ini_get('memory_limit');
        if (mb_strpos($memorylimit, 'M'))
        {
                $memorylimit = (intval($memorylimit) * 1024 * 1024);
        } 
        $dashboard['phpmemorylimit'] = ($memorylimit AND $memorylimit != '-1') ? print_filesize($memorylimit, 2, true) : 'None';
    	
	// members moderation count
	$members = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "users AS users
		WHERE users.status = 'moderated'
	");
	$dashboard['members'] = (int)$members['count'];
	    
	// auction moderation count
	$auctions = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "projects AS projects
		WHERE projects.visible = '0'
	");
	$dashboard['auctions'] = (int)$auctions['count'];
	    
	// attachment moderation count
	$attach = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "attachment AS attachment
		WHERE attachment.visible = '0'
	");
	$dashboard['attach'] = (int)$attach['count'];
	    
	// verifications moderation count
	$verifies = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "profile_answers AS profile_answers
		WHERE profile_answers.isverified = '0'
			AND profile_answers.invoiceid > 0
	");
	$dashboard['verifies'] = (int)$verifies['count'];
	
	// referral payouts pending count
	$referral = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "referral_data AS referral_data
		WHERE referral_data.paidout = '0'
			AND referral_data.invoiceid = '0'
			AND referral_data.postauction = 'yes'
			AND referral_data.awardauction = 'yes'
			AND referral_data.paysubscription = 'yes'
		");
	$dashboard['referral'] = (int)$referral['count'];
    
	// withdraws pending count
	$withdraws = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "invoices AS invoices
		WHERE invoices.invoiceid > 0
			AND invoices.iswithdraw = '1'
			AND invoices.status = 'scheduled'
	");
	$dashboard['withdraws'] = (int)$withdraws['count'];
	
	// unpaid invoices count
	$unpaid = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "invoices AS invoices
		WHERE (invoices.status = 'unpaid')
			AND invoices.invoiceid > 0
			AND invoices.iswithdraw = '0'
			AND invoices.isdeposit = '0'
			AND invoicetype != 'escrow'
                        AND invoicetype != 'p2b'
	");
	$dashboard['unpaid'] = (int)$unpaid['count'];
        
        // unpaid p2b invoices count
	$unpaid = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "invoices AS invoices
		WHERE (invoices.status = 'unpaid')
			AND invoices.invoiceid > 0
			AND invoices.iswithdraw = '0'
			AND invoices.isdeposit = '0'
			AND invoicetype != 'escrow'
                        AND invoicetype = 'p2b'
	");
	$dashboard['unpaidp2b'] = (int)$unpaid['count'];
	
	// unpaid scheduled transactions count
	$scheduled = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "invoices AS invoices
		WHERE (invoices.status = 'scheduled')
			AND invoices.invoiceid > 0
			AND invoices.iswithdraw = '0'
			AND invoices.isdeposit = '0'
			AND invoicetype != 'escrow'
                        AND invoicetype != 'p2b'
	");
	$dashboard['scheduled'] = (int)$scheduled['count'];
	
	// php information
	if (extension_loaded('gd'))
	{
		$dashboard['gd'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'checked.gif" alt="' . $phrase['_enabled'] . '" border="0" />';
	}
	else 
	{
		$dashboard['gd'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'unchecked.gif" alt="' . $phrase['_disabled'] . '" border="0" />';
	}
	
	if (extension_loaded('openssl'))
	{
		$dashboard['openssl'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'checked.gif" alt="' . $phrase['_enabled'] . '" border="0" />';
	}
	else 
	{
		$dashboard['openssl'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'unchecked.gif" alt="' . $phrase['_disabled'] . '" border="0" />';
	}
	
	if (extension_loaded('ftp'))
	{
		$dashboard['ftp'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'checked.gif" alt="' . $phrase['_enabled'] . '" border="0" />';
	}
	else 
	{
		$dashboard['ftp'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'unchecked.gif" alt="' . $phrase['_disabled'] . '" border="0" />';
	}
	
	if (MULTIBYTE)
	{
		$dashboard['mbstring'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'checked.gif" alt="' . $phrase['_enabled'] . '" border="0" />';
	}
	else 
	{
		$dashboard['mbstring'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'unchecked.gif" alt="' . $phrase['_disabled'] . '" border="0" />';
	}
	
	if (ini_get('safe_mode'))
	{
		$dashboard['safemode'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'checked.gif" alt="' . $phrase['_enabled'] . '" border="0" />';
	}
	else 
	{
		$dashboard['safemode'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'unchecked.gif" alt="' . $phrase['_disabled'] . '" border="0" />';
	}
	
	if (ini_get('register_globals'))
	{
		$dashboard['registerglobals'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'checked.gif" alt="' . $phrase['_enabled'] . '" border="0" />';
	}
	else 
	{
		$dashboard['registerglobals'] = '<img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'].'unchecked.gif" alt="' . $phrase['_disabled'] . '" border="0" />';
	}
       
	// 24 hour information preview
	$members24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "users 
		WHERE date_added LIKE ('%" . DATETODAY . "%')
	");
	$dashboard['newmembers'] = intval($members24h['count']);
	    
	$referrals24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "referral_data
		WHERE date LIKE ('%" . DATETODAY . "%')
	");
	$dashboard['newreferrals'] = intval($referrals24h['count']);
	    
	$service24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
		WHERE date_added LIKE ('%" . DATETODAY . "%')
			AND project_state = 'service'
	");
	$dashboard['newserviceauctions'] = intval($service24h['count']);
	    
	$servicebids24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "project_bids
		WHERE date_added LIKE ('%" . DATETODAY . "%')
			AND state = 'service'
	");
	$dashboard['newservicebids'] = intval($servicebids24h['count']);
	    
	$serviceexpired24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
		WHERE date_end LIKE ('%" . DATETODAY . "%')
			AND project_state = 'service'
	");
	$dashboard['expiredserviceauctions'] = intval($serviceexpired24h['count']);
	    
	$servicedelisted24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
		WHERE close_date LIKE ('%" . DATETODAY . "%')
			AND status = 'delisted'
			AND project_state = 'service'
	");
	$dashboard['delistedserviceauctions'] = intval($servicedelisted24h['count']);
	    
	$product24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
		WHERE date_added LIKE ('%" . DATETODAY . "%')
			AND project_state = 'product'
	");
	$dashboard['newproductauctions'] = intval($product24h['count']);
	    
	$productbids24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "project_bids
		WHERE date_added LIKE ('%" . DATETODAY . "%')
			AND state = 'product'
	");
	$dashboard['newproductbids'] = intval($productbids24h['count']);
	
	$productexpired24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
		WHERE date_end LIKE ('%" . DATETODAY . "%')
			AND project_state = 'product'
	");
	$dashboard['expiredproductauctions'] = intval($productexpired24h['count']);
	    
	$productdelisted24h = $ilance->db->query_fetch("
		SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
		WHERE close_date LIKE ('%" . DATETODAY . "%')
			AND status = 'delisted'
			AND project_state = 'product'
	");
	$dashboard['delistedproductauctions'] = intval($productdelisted24h['count']);
        
        ($apihook = $ilance->api('admincp_dashboard_mid')) ? eval($apihook) : false;
        
	$dashboard[] = $dashboard;
	
	$totalusers = print_flash_stats('totalusers', 'stats');
        
        $admincpnews = $ilance->admincp->fetch_admincp_news();
        
        $ilance->bbcode = construct_object('api.bbcode');
        
        // MOTD WYSIWYG editor
        //$currentmotd = $ilance->db->fetch_field(DB_PREFIX . "motd", "date = '" . DATETODAY . "'", "content");
		$sql=$ilance->db->query("select * from ".DB_PREFIX."motd where visible=1");
		$currentmotd_preview='';
		if($ilance->db->num_rows($sql)>0)
		{
		while($line=$ilance->db->fetch_array($sql))
		{
			$currentmotd_preview='<tr><td>'.$ilance->bbcode->bbcode_to_html($line['content']).'<td><td>
			<span class="smaller">[ <span class="blue"><a href="'.$ilpage[dashboard].'?cmd=motd&amp;subcmd=edit&amp;id='.$line['motdid'].'">edit</a></span> ]</span>
			</td><td>
			<span class="smaller">[ <span class="blue"><a href="'.$ilpage[dashboard].'?cmd=motd&amp;subcmd=remove&amp;id='.$line['motdid'].'">delete</a></span> ]</span>
			</td></tr>';
		}
		}else
		{
		$currentmotd_preview = $phrase['_none'];
		}
		
        /*$currentmotd_preview = $ilance->bbcode->bbcode_to_html($currentmotd);
        
        if ($currentmotd_preview == '')
        {
                $currentmotd_preview = $phrase['_none'];
        }*/
        
        $ilance->GPC['description'] = !empty($ilance->GPC['description']) ? $ilance->GPC['description'] : '';
        $wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
        
        
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'motd')
        {
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert')
                {
                        // admin sending bulk email as plain text
                        
                        if (!empty($ilance->GPC['description']))
                        {
                                $message = $ilance->GPC['description'];
                                $message = $ilance->bbcode->prepare_special_codes('PHP', $message);
                                $message = $ilance->bbcode->prepare_special_codes('HTML', $message);
                                $message = $ilance->bbcode->prepare_special_codes('CODE', $message);
                                $message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
                                //$message = $ilance->bbcode->strip_bb_tags($message);
                                $message = html_entity_decode($message);
                                
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "motd
                                        (motdid, content, date, visible)
                                        VALUES (
                                        NULL,
                                        '" . $ilance->db->escape_string($message) . "',
                                        '" . DATETODAY . "',
                                        '1')
                                ");
                                
                                print_action_success($phrase['_you_have_successfully_composed_a_new_message_of_the_day'], $ilance->GPC['return']);
                                exit();
                        }
                }
                
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update')
                {
                        // admin sending bulk email as plain text
                        
                        if (!empty($ilance->GPC['description']))
                        {
                                $message = $ilance->GPC['description'];
                                $message = $ilance->bbcode->prepare_special_codes('PHP', $message);
                                $message = $ilance->bbcode->prepare_special_codes('HTML', $message);
                                $message = $ilance->bbcode->prepare_special_codes('CODE', $message);
                                $message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
                                //$message = $ilance->bbcode->strip_bb_tags($message);
                                $message = html_entity_decode($message);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "motd
                                        SET content = '" . $ilance->db->escape_string($message) . "'
                                        WHERE motdid = '" . $ilance->GPC['id'] . "'
                                        LIMIT 1
                                ");
                                
                                print_action_success($phrase['_you_have_successfully_updated_the_current_message_of_the_day'], $ilance->GPC['return']);
                                exit();
                        }
                }
                
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove')
                {
                        // admin sending bulk email as plain text
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "motd
                                WHERE motdid = '" . $ilance->GPC['id'] . "'
                        ");
                        
                        print_action_success($phrase['_the_current_message_of_the_day_was_removed'], $ilpage['dashboard']);
                        exit();
                }
                
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit')
                {
                        $currentmotd = $ilance->db->fetch_field(DB_PREFIX . "motd", "motdid = '" . $ilance->GPC['id'] . "'", "content");
                        if (empty($currentmotd))
                        {
                                $currentmotd = $phrase['_there_is_no_motd_posted_for_today'];        
                        }
                        else
                        {
                                $wysiwyg_area = print_wysiwyg_editor('description', $currentmotd, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
                        }
                }
        }
		if(isset($ilance->GPC['id'])) $motdid=$ilance->GPC['id'];
	
	$pprint_array = array('motdid','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'dashboard.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','dashboard'));
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