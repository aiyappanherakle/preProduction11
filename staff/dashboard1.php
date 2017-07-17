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
	'flashfix',
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once './../functions/config.php';

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
$ilpage['staff_dashboard'] = 'dashboard1.php';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1') {
	$sql = "SELECT user_id FROM " . DB_PREFIX . "user_activity a
	 WHERE a.is_bot = false and a.user_id!=1 and a.user_id!=0
	group by a.user_id
	";
	$numberrows = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	$ilance->GPC['page'] = isset($ilance->GPC['page']) ? $ilance->GPC['page'] : 1;
	$number = $ilance->db->num_rows($numberrows);
	$counter = (intval($ilance->GPC['page']) - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
	$scriptpage = HTTP_SERVER . $ilpage['sell'] . print_hidden_fields(true, array('do', 'cmd', 'action', 'q', 'sort'), true, '', '', $htmlentities = true, $urldecode = false);
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], intval($ilance->GPC['page']), $counter, $scriptpage);
	$limit = ' LIMIT ' . $counter . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];

	$sql1 = "SELECT a.user_id,count(*) as count_per_user,u.username FROM " . DB_PREFIX . "user_activity a
	left join " . DB_PREFIX . "users u on a.user_id=u.user_id
	 WHERE a.is_bot = false and a.user_id!=1 and a.user_id!=0
	group by a.user_id order by count_per_user desc
	" . $limit;
	$result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result1) > 0) {
		while ($line1 = $ilance->db->fetch_array($result1)) {
			$temp['username'] = $line1['username'];
			$temp['count_per_user'] = $line1['count_per_user'];
			$temp['user_id'] = $line1['user_id'];
			$user_list[] = $temp;
			$rowcount++;
		}
	}
	$pprint_array = array(
		'prevnext',
		'login_include',
		'headinclude',
		'area_title',
		'page_title',
		'site_name',
		'https_server',
		'http_server',
		'lanceads_header',
		'lanceads_footer');

	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'dashboard1.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('user_list', 'feecal', 'apreport'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
} else {
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>