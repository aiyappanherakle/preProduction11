<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base 1.0.8 Build 85
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

$phrase['groups'] = array(
    'lancekb',
    'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix',
	'jquery'
);

define('LOCATION', 'kb');
if(is_file('./functions/config.php'))
{
require_once('./functions/config.php');	
}else{
require_once('../functions/config.php');
}

if (!isset($show['lancekb']) OR $show['lancekb'] == 0)
{
    $navcrumb = array("index.php" => "Knowledge Base");
    print_notice($phrase['_disabled'], $phrase['_were_sorry_this_feature_is_currently_disabled'], $ilpage['main'], $phrase['_main_menu']);
    exit();
}

$navcrumb = array("$ilpage[index]" => $phrase['_knowledge_base']);

if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("UPDATE ".DB_PREFIX."kbposts 
	SET numprints = numprints+1 
	WHERE postsid = '".intval($ilance->GPC['id'])."'
	LIMIT 1");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en">
<head>
<title>Viewing Article ID (<?php echo (int)$ilance->GPC['id']; ?>)</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
<!--
td, p, li, div
{
	font: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
}
.smaller
{
	font-size: 11px;
}
.tableborder
{
	border: 1px solid #808080;
}
.tablehead
{
	background-color: #EEEEEE;
}
.page
{
	background-color: #FFFFFF;
	color: #000000;
}
-->
</style>
</head>
<body class="page" onload="javascript:window.print()">
<table class="tableborder" cellpadding="6" cellspacing="1" border="0" width="100%">
<tr>
	<td class="page">
		<div><?php echo $ilance->lancekb->fetch_article(intval($ilance->GPC['id'])); ?></div>
	</td>
</tr>
</table>

<br />

<table class="tableborder" cellpadding="6" cellspacing="1" border="0" width="100%">
<?php 
echo $ilance->lancekb->fetch_article_comments("SELECT * FROM ".DB_PREFIX."kbcomments 
WHERE approved = '1' 
AND postsid = '".intval($ilance->GPC['id'])."' 
ORDER BY insdate", intval($ilance->GPC['id']));
?>
</table>


</body>
</html>
