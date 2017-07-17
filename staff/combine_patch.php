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
	'accounting'
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
error_reporting(E_ALL);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  combine_project!=''";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
	$child_invoices=explode(",",$line['combine_project']);
	foreach($child_invoices as $child)
	{
	$sql1="SELECT *  FROM " . DB_PREFIX . "combine_invoices WHERE  childid='".$child."'";
	$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res1)==0)
	{
			$rsql="insert into ilance_combine_invoices (parentid,childid) values ('".$line['invoiceid']."','".$child."')";
			$ilance->db->query($rsql);
	}else
	{
	echo $child."<br>";
	}
		
	}
	}
} 
echo "halts";
}else
{
echo "login";
}
	
?>