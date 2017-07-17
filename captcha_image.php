<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required phrase groups ############################################
$phrase['groups'] = array(
        'rfp',
        'search',
        'accounting',
        'buying',
        'selling',
        'subscription',
        'feedback'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'jquery',
        'modal',
	'flashfix'
);
// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);
// #### setup script location ##################################################
define('LOCATION', 'merch');


// #### require backend ########################################################
require_once('./functions/config.php');

// *** CAPTCHA image generation ***
// *** http://frikk.tk ***

session_start();

// *** Tell the browser what kind of file is come'n at 'em! ***
header("Content-Type: image/jpeg");

// *** Send a generated image to the browser ***
die(create_image());

// *** Function List ***
function create_image()
{
	// *** Generate a passcode using md5
	//	(it will be all lowercase hex letters and numbers ***
	$md5 = md5(rand(0,9999));
	$pass = substr($md5, 10, 5);

	// *** Set the session cookie so we know what the passcode is ***
	$_SESSION['ilancedata']['user']['captcha'] = $pass;

	// *** Create the image resource ***
	$image = ImageCreatetruecolor(100, 20);

	// *** We are making two colors, white and black ***
	$clr_white = ImageColorAllocate($image, 255, 255, 255);
	$clr_black = ImageColorAllocate($image, 0, 0, 0);

	// *** Make the background black ***
	imagefill($image, 0, 0, $clr_black);

	// *** Set the image height and width ***
	imagefontheight(15);
	imagefontwidth(15);

	// *** Add the passcode in white to the image ***
	imagestring($image, 5, 30, 3, $pass, $clr_white);

	// *** Throw in some lines to trick those cheeky bots! ***
	imageline($image, 5, 1, 50, 20, $clr_white);
	imageline($image, 60, 1, 96, 20, $clr_white);

	// *** Return the newly created image in jpeg format ***
	return imagejpeg($image);

	// *** Just in case... ***
	imagedestroy($image);
}
?>