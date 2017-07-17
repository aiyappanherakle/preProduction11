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
	'cron',
	'accounting',
	'buying',
	'selling',
	'rfp',
	'subscription',
	'feedback',
	'search'
);

// disable time limit for running scripts
@ignore_user_abort(1);
@set_time_limit(0);

// #### setup script location ##################################################
define('LOCATION','cron');
define('SKIP_SESSION', true);

// #### require backend ########################################################
require_once('functions/config.php');

($apihook = $ilance->api('cron_start')) ? eval($apihook) : false;

// load cron engine
include(DIR_CRON . 'automation.php');
if (isset($ilance->GPC['type']) AND $ilance->GPC['type'] == 'image')
{
	header('Location: ' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer.gif');
}

($apihook = $ilance->api('cron_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>