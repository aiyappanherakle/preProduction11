<?php	
//include('./functions/config.php');
require_once('./../functions/config.php');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
error_reporting(E_ALL);
global $ilance, $myapi, $ilpage, $phrase, $ilconfig;

$ilance->lowering_coin_amount = construct_object('api.lowering_coin_amount');

$cronlog = $ilance->lowering_coin_amount->lowering_coins();

}	
else
{
refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
exit();
}

?>