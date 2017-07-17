<?php 

//define('LOCATION', 'admin');
//require_once('../config.php');

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

error_reporting(E_ALL);
require_once(DIR_SERVER_ROOT.'Swift/lib/swift_required.php');
$ilance->arExcelnew = construct_object('api.arExcelnew');
//Create transport
$transport = Swift_MailTransport::newInstance();

// Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

// Create the message
$message = Swift_Message::newInstance()

  // Give the message a subject
  ->setSubject('AR Report')

  // Set the From address with an associative array
  ->setFrom(array($ilconfig['globalserversettings_developer_email'] => 'Report'))

  // Set the To addresses with an associative array
  ->setTo(array($ilconfig['globalserversettings_testemail']))

  // Give it a body
   ->setBody('Dear Donna,
			Please Find Attached Report
			
			Thanks.');

  // And optionally an alternative body
 // ->addPart($ilance->arExcelnew->ar_excel_new(), 'text/html')
  

  // Optionally add any attachments
  //->attach(Swift_Attachment::fromPath(DIR_SERVER_ROOT.'/greatcollections_logo.jpg'))
  ;
  
  // Send the message
  
$attachment = Swift_Attachment::newInstance($ilance->arExcelnew->ar_excel_new(), 'AR_Report_'.date('Y-m-d h-i-s').'.csv','appication/comma-separated-values');
$message->attach($attachment);

$result = $mailer->send($message);
?>