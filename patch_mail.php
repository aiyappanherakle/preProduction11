<?php 
require_once('./functions/config.php');
error_reporting(E_ALL);
require_once 'Swift/lib/swift_required.php';
$ilance->arExcelnew = construct_object('api.arExcelnew');
//Create transport
$transport = Swift_MailTransport::newInstance();

// Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

// Create the message
$message = Swift_Message::newInstance()

  // Give the message a subject
  ->setSubject('Your subject')

  // Set the From address with an associative array
  ->setFrom(array($ilconfig['globalserversettings_developer_email'] => 'John Doe'))

  // Set the To addresses with an associative array
  ->setTo(array($ilconfig['globalserversettings_developer_email']))

  // Give it a body
  ->setBody('Here is the message itself')

  // And optionally an alternative body
 // ->addPart($ilance->arExcelnew->ar_excel_new(), 'text/html')
  

  // Optionally add any attachments
  //->attach(Swift_Attachment::fromPath(DIR_SERVER_ROOT.'/greatcollections_logo.jpg'))
  ;
  
  // Send the message
  
$attachment = Swift_Attachment::newInstance($ilance->arExcelnew->ar_excel_new(), 'my-file.csv','appication/comma-separated-values');
$message->attach($attachment);
$result = $mailer->send($message);
?>