<?php
//define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);
$ilance->dailysales = construct_object('api.dailysales');
$DATEYESTERDAY=DATEYESTERDAY;
//$DATEYESTERDAY='2012-12-25';
$html=$ilance->dailysales->gethtml($DATEYESTERDAY);


					$ilance->email = construct_dm_object('email', $ilance);
						$ilance->email->logtype = 'daily_report';

						//$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
						$ilance->email->mail = $ilconfig['globalserversettings_testemail'];
						$ilance->email->slng = fetch_site_slng();

						$ilance->email->get('daily_reports_acc_receivables');

						$ilance->email->set(array(
								'{{report_date}}' =>$DATEYESTERDAY,
								'{{html}}' => $html,
						));

						$ilance->email->send();

						$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
						$ilance->email->slng = fetch_site_slng();

						$ilance->email->get('daily_reports_acc_receivables');

						$ilance->email->set(array(
								'{{report_date}}' =>$DATEYESTERDAY,
								'{{html}}' => $html,
						));

						$ilance->email->send();


$ilance->dailysales->log();


?>