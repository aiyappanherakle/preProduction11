<?php

require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

echo'hoi';
	if(isset($_SESSION['ilancedata']['file_run_exist']))
	{		
		$_SESSION['ilancedata']['file_run_exist']=1;
		echo $_SESSION['ilancedata']['file_run_exist'];
		unset($_SESSION['ilancedata']['file_run_exist']);
	}

}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>