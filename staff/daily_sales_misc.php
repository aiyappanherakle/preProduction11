<?php
//define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);
$ilance->dailysales = construct_object('api.dailysalesmisc');
$DATEYESTERDAY=DATEYESTERDAY;
//$DATEYESTERDAY='2012-12-25';
echo $html=$ilance->dailysales->gethtml($DATEYESTERDAY);

?>