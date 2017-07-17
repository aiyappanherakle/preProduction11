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
        'accounting',
        'preferences',
        'search',
        'feedback'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'abuse'
);

// #### setup script location ##################################################
define('LOCATION', 'abuse');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[abuse]" => $ilcrumbs["$ilpage[abuse]"]);

// #### login redirection ######################################################
if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh($ilpage['login'] . '?redirect=' . urlencode($ilpage['abuse'] . print_hidden_fields(true, array(), true)));
	exit();
}
	
// #### SUBMIT ABUSE ###################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'submit-abuse')
{
	// #### expected input variables #######################################
	$ilance->clean_gpc('p', array(
		'abusetype'       => 'TYPE_STR',
		'type'            => 'TYPE_STR',
		'abusemessage'    => 'TYPE_STR',
		'id'              => 'TYPE_INT',
	));

	$ilance->GPC['abusemessage'] = strip_vulgar_words($ilance->GPC['abusemessage']);
	$ilance->GPC['memberstart'] = print_date(fetch_user('date_added', $_SESSION['ilancedata']['user']['userid']), '%d-%b-%Y', 0, 0);
	$ilance->GPC['countryname'] = print_user_country($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['slng']);

	if (empty($ilance->GPC['abusemessage']))
	{
		$area_title = $phrase['_sending_abuse_notification'];
		$page_title = SITE_NAME . ' - ' . $phrase['_sending_abuse_notification'];
		
		print_notice($phrase['_please_enter_all_fields'], $phrase['_please_enter_a_message_to_continue_submiting_this_abuse_report_thank_you'], 'javascript:history.back(1);', $phrase['_back']);
		exit();
	}

	switch ($ilance->GPC['abusetype'])
	{
		case 'profile':
		{
			$abusetype = $phrase['_profile'];
			$abuseurl = HTTP_SERVER . $ilpage['members'] . '?id=' . intval($ilance->GPC['id']);
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$user = fetch_user('username', intval($ilance->GPC['id']));
				$user = construct_seo_url_name($user);
				$abuseurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $user;
			}
			break;
		}
		case 'listing':
		{
			$abusetype = $phrase['_listing'];
			$abuseurl = ($ilance->GPC['type'] == 'service')
				? HTTP_SERVER . $ilpage['rfp'] . '?id=' . ($ilance->GPC['id'])
				: HTTP_SERVER . $ilpage['merch'] . '?id=' . ($ilance->GPC['id']);
			break;
		}		
		case 'portfolio':
		{
			$abusetype = $phrase['_portfolio'];
			$abuseurl = HTTP_SERVER . $ilpage['portfolio'] . '?id=' . intval($ilance->GPC['id']);
			break;
		}
	}

	$area_title = $phrase['_abuse_notification_was_posted_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_abuse_notification_was_posted_menu'];
	
	($apihook = $ilance->api('abuse_submit_start')) ? eval($apihook) : false;
	
	// #### insert abuse report into database ######################
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "abuse_reports
		(abuseid, regarding, username, email, itemid, abusetype, type, status, dateadded)
		VALUES (
		NULL,
		'" . $ilance->db->escape_string($ilance->GPC['abusemessage']) . "',
		'" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['username']) . "',
		'" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['email']) . "',
		'" . intval($ilance->GPC['id']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['abusetype']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['type']) . "',
		'1',
		'" . DATETIME24H . "')
	");

	$ilance->email = construct_dm_object('email', $ilance);
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('submit_abuse');		
	$ilance->email->set(array(
		'{{abusetype}}' => $abusetype,
		'{{abuseurl}}' => $abuseurl,					  
		'{{reporter}}' => $_SESSION['ilancedata']['user']['username'],
		'{{reporteremail}}' => $_SESSION['ilancedata']['user']['email'],
		'{{abusemessage}}' => $ilance->GPC['abusemessage'],
		'{{memberstart}}' => $ilance->GPC['memberstart'],
		'{{countryname}}' => $ilance->GPC['countryname'],
	));
	$ilance->email->send();
	
	($apihook = $ilance->api('abuse_submit_end')) ? eval($apihook) : false;
	
	print_notice($phrase['_your_message_was_sent'], $phrase['_your_message_was_sent_and_delivered_to_administration'], $ilpage['main'], $phrase['_main_menu']);
	exit();
}
else
{
	$area_title = $phrase['_sending_abuse_notification'];
	$page_title = SITE_NAME . ' - ' . $phrase['_sending_abuse_notification'];
	
	$abusetypes = array('listing', 'bid', 'portfolio', 'profile', 'feedback', 'pmb', 'forum');
	
	$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
	$ilance->GPC['id'] = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	$id = $ilance->GPC['id'];
	$type = isset($ilance->GPC['type']) ? $ilance->GPC['type'] : '';
	
	if (!in_array($ilance->GPC['cmd'], $abusetypes))
	{
		print_notice($phrase['_invalid_abuse_report'], $phrase['_the_abuse_report_attempting_to_be_made_is_invalid_please_remember'], 'javascript:history.back(1);', $phrase['_back']);
		exit();
	}
	
	$abusetype_pulldown = print_abuse_type_pulldown($ilance->GPC['cmd'], $ilance->GPC['id']);
	
	// #### form elements ##########################################
	$form['start'] = '<form action="' . HTTP_SERVER . $ilpage['abuse'] . '" method="post" name="ilform" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="cmd" value="submit-abuse" />
<input type="hidden" name="id" value="' . $id . '" />
<input type="hidden" name="type" value="' . $type . '" />
<input type="hidden" name="token" value="' . TOKEN . '" />
<input type="hidden" name="s" value="' . session_id() . '" />';
	
	$form['end'] = '</form>';
	
	$pprint_array = array('formstart','formend','type','abusetype_pulldown','requesturi','url','cmd','id','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('abuse_start')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'abuse.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage, 'form' => $form));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>