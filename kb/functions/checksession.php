<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base1.0.8 Build 85
|| # -------------------------------------------------------------------- # ||
|| # Customer License # KapIxNXTSUYf3LjCGHiWk1XevwZ-ISZStLboZ-ErQdU-pATvJ3
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

if (isset($ilance->GPC['catid']))
{
	$tid = intval($ilance->GPC['catid']);
}
else 
{
	$tid = intval($ilance->GPC['id']);
}

if (isset($ilance->GPC['typekb']))
{
    $typekb = $ilance->GPC['typekb'];
}
if ($ilance->lancekb->is_user_logged_in() == 0 AND $ilance->lancekb->fetch_kbaccess_level($tid, $typekb) == 'Y')
{
	$var = '';
	if (!empty($redirectURL))
	{
		$var = "?redirect=".$redirectURL;
	}
	refresh($ilpage['login'] . $var, HTTPS_SERVER . $ilpage['login'] . $var);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Tue, Jan 11th, 2011
|| ####################################################################
\*======================================================================*/
?>