<?php
//define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);
$ilance->newdailysales = construct_object('api.newdailysales');
$DATEYESTERDAY=DATEYESTERDAY;
//$DATEYESTERDAY='2012-12-25';
echo $html=$ilance->newdailysales->gethtml($DATEYESTERDAY);

?>