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
	<title>Last MySQL errors for GreatCollections</title>
	<meta http-equiv="refresh" content="1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<base href="http://www.greatcollections.com/" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="Cache-Control" content="no-cache" />
</head>
<body>
<pre><?php 
$output = null;
$output = shell_exec('tail -20 /var/lib/mysql/vps.greatcollections.com.err');	//  | grep "Out of memory"
echo htmlentities($output);
?>
</pre>
</body>
</html>
<?
exit;

$output = preg_replace("#^.+?\ntop - #s",'top - ', $output);	// filter out first whole "top -" results
if (preg_match("# ([0-9]+)k total#",$output,$res))
{
	$total = intval($res[0]);
	if (preg_match("# ([0-9]+)k used#",$output,$res))
	{
		$used = intval($res[0]);
		if (preg_match("# ([0-9]+)k cached#",$output,$res))
		{
			$cached = intval($res[0]);
			// $output = 'Total: '.($total).'k, Used: '.($used).'k, Cached: '.($cached).'k'."\n".$output;
			$output = str_replace('k cached',"k cached\n\nActual used memory w/o cache: ".($used-$cached).'k ('.round(($used-$cached)/$total*100.0,1).'%)', $output);
		}
	}
}
echo htmlentities($output);
/*
top - 17:02:12 up 3 days, 14:35,  4 users,  load average: 5.82, 5.18, 4.86
Tasks: 207 total,   5 running, 198 sleeping,   4 stopped,   0 zombie
Cpu(s):  4.9%us,  0.2%sy,  0.0%ni, 94.8%id,  0.1%wa,  0.0%hi,  0.0%si,  0.0%st
Mem:  67108864k total, 56967024k used, 10141840k free,        0k buffers
Swap:        0k total,        0k used,        0k free, 54464152k cached
*/
?>
</pre>
</body>
</html>

