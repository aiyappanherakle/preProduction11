<?php
//define('LOCATION', 'admin');

//require_once('../config.php');

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

//$ilance->dailysales = construct_object('api.dailysales');
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

						$ilance->email->logtype = 'daily_report';

                                                $ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
						$ilance->email->slng = fetch_site_slng();

						$ilance->email->get('daily_reports_acc_receivables');

						$ilance->email->set(array(
								'{{report_date}}' =>$DATEYESTERDAY,
								'{{html}}' => $html,
						));

						$ilance->email->send();
						
						$ilance->email->logtype = 'daily_report';

                                                $ilance->email->mail = $ilconfig['globalserversettings_accountsemail']; 
						$ilance->email->slng = fetch_site_slng();

						$ilance->email->get('daily_reports_acc_receivables');

						$ilance->email->set(array(
								'{{report_date}}' =>$DATEYESTERDAY,
								'{{html}}' => $html,
						));

						$ilance->email->send();
$ilance->dailysales->log();


?>