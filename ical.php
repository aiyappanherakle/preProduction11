<?php

require 'functions/iCalcreator/iCalcreator.class.php';


//takes a date and converts it to a ISO8601 compliant date object




$evID = "A certain ID";
$evDesc = $_POST['description'];
$decodedDesc = base64_decode($evDesc);
$title = $_POST['title'];
$evTime = $_POST['date'];
$evTimeEnd = $_POST['dateEnd'];
$url = $_POST['url'];



$v = new vcalendar(); // initiate new CALENDAR

$v->setProperty( 'x-wr-calname', 'Great Collections');
$v->setProperty( "X-WR-TIMEZONE", "America/Los_Angeles" );



$e = new vevent(); // initiate a new EVENT
$e->setProperty( 'dtstart', $evTime); // set start time
$e->setProperty( 'dtend', $evTimeEnd);	// set end time
$e->setProperty( 'summary', "Ending: "."$title"); // subject of the event, without HTML
$e->setProperty( 'description', $decodedDesc); // describe the event, without HTML
//$e->setProperty( 'url', $url); // url to the event

$v->addComponent( $e ); // add our event to the calender



$v->returnCalendar(); // generate and redirect output to user browser


/**
 * Created by IntelliJ IDEA.
 * User: theloniousquimby
 * Date: 10/7/16
 * Time: 1:07 PM
 */

?>
