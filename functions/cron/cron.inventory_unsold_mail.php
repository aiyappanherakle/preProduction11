<?php 

// define('LOCATION', 'admin');
// require_once('../config.php');

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

error_reporting(E_ALL);
require_once(DIR_SERVER_ROOT.'Swift/lib/swift_required.php');
$ilance->inVentoryunsold = construct_object('api.inVentoryunsold');
$transport = Swift_MailTransport::newInstance();
$mailer = Swift_Mailer::newInstance($transport);
$message = Swift_Message::newInstance()


  ->setSubject('Inventory Unsold Details')


  ->setFrom(array($ilconfig['globalserversettings_developer_email'] => 'Report'))


  ->setTo(array($ilconfig['globalserversettings_testemail']))

  ->setBody('Dear Donna,
			Please Find Attached Report
			
			Thanks.');

  
$attachment = Swift_Attachment::newInstance($ilance->inVentoryunsold->inventory_list_unsold(), 'Inventory_Unsold'.date('Y-m-d h-i-s').'.csv','appication/comma-separated-values');
$message->attach($attachment);

$result = $mailer->send($message);
?>