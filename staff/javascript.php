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
$phrase['groups'] = array();

// disable time limit for running scripts
@ignore_user_abort(1);
@set_time_limit(0);

// #### setup script location ##################################################
define('LOCATION', 'javascript');
define('SKIP_SESSION', true);

// #### require backend ########################################################
require_once('./../functions/config.php');

$html = "function ilance_require(jspath)
{
	document.write('<script language=\"javascript\" type=\"text/javascript\" src=\"' + jspath + '\" charset=\"utf-8\"><\/script>');
}\n";

$js = array();

($apihook = $ilance->api('javascript_start')) ? eval($apihook) : false;

// #### determine what javascript's we want to load ############################
if (isset($ilance->GPC['do']) AND !empty($ilance->GPC['do']))
{
	$js = explode(',', $ilance->GPC['do']);
	if (isset($js) AND is_array($js) AND count($js) > 0)
	{
		foreach ($js AS $jsfile)
		{
			if (!empty($jsfile))
			{
				$html .= ($jsfile == 'functions') ? "ilance_require(ILBASE + \"functions/javascript/$jsfile.js\");\n" : "ilance_require(ILBASE + \"functions/javascript/functions_$jsfile.js\");\n";
			}
		}
	}
}

($apihook = $ilance->api('javascript_end')) ? eval($apihook) : false;

// #### print our client javascript ############################################
if (!empty($html))
{
	echo $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>