<?php 
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
	'flashfix',
	'jquery',
	'jquery_custom_ui',
    'modal',
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(E_ALL);

// echo 'empty userid='.empty($_SESSION['ilancedata']['user']['userid']).', userid='.$_SESSION['ilancedata']['user']['userid'].', isadmin='.$_SESSION['ilancedata']['user']['isadmin'].'<BR>'; exit;
if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] <= 0 OR $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
	// echo urlencode(SCRIPT_URI); exit;
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html dir="ltr" lang="en" >
<head>
	<title>phpinfo() display for GreatCollections</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<base href="http://www.greatcollections.com/" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="Cache-Control" content="no-cache" />
</head>
<body>
<?php
phpinfo();
?>
</body>
</html>

