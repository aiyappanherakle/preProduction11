<?php
require_once('../functions/config.php');
error_reporting(E_ALL);
$time_slot=' DATE_SUB(DATE(NOW()), INTERVAL DAYOFWEEK(NOW())+0 DAY) ';
$time_slot1=' DATE_SUB(DATE(NOW()), INTERVAL DAYOFWEEK(NOW())-1 DAY) ';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
 header('Content-type: application/excel');                                  
  header('Content-Disposition: attachment; filename="test.xls"');

}

//to cont
?>

   
 <table>
  <tr><th>Column 1</th><th>Column 2</th></tr>
 <tr><td style="font-size:200%">Answer 1</td><td style="color:#f00">Answer 2</td></tr>
  </table>