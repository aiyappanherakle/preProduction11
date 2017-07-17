<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©20002010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
	$phrase['groups'] = array(
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
	);
// #### load required javascript ###############################################
	$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
        'modal',
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'merch');
// #### require backend ########################################################
require_once('./functions/config.php');

$page_title = SITE_NAME . ' - ' . 'Consign to GreatCollections Coin Auctions';
$navcrumb = array("consignnow.php" => "Consign to GreatCollections Coin Auctions" );


$pprint_array = array('html','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
$ilance->template->fetch('main', 'main_consign-now.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
$ilance->template->parse_loop('main', 'res_loop');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();



?>