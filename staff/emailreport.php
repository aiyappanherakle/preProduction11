<?php

/*==========================================================================*\

|| ######################################################################## ||

|| # ILance Marketplace Software 3.2.0 Build 1352							||

|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||

|| # -------------------------------------------------------------------- # ||

|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||

|| # -------------------------------------------------------------------- # ||

|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||

|| # This file may not be redistributed in whole or significant part.     # ||

|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||

|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||

|| # -------------------------------------------------------------------- # ||

|| ######################################################################## ||

\*==========================================================================*/



// #### load required phrase groups ############################################

$phrase['groups'] = array(

	'administration'

);



// #### load required javascript ###############################################

$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);



// #### setup script location ##################################################

define('LOCATION', 'admin');



// #### require backend ########################################################

require_once('./../functions/config.php');



// #### setup default breadcrumb ###############################################

$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);









$emaillistsel = $ilance->db->query("SELECT *
FROM " . DB_PREFIX . "projects
WHERE date( date_end ) = '2011-07-10'
AND bids !=0 ORDER BY date_end ASC
");

	$listing_items = '<table border="1"><tr><td>winner userid</td><td>username</td><td>email</td><td>itemid</td><td>itemtitle</td><td>finalprice</td><td>endtime</td></tr>';

while($emaillist = $ilance->db->fetch_array($emaillistsel))
 {     
	  $projectid=$emaillist['project_id'];
	  $userid=$emaillist['winner_user_id'];
	  $currentprice=$emaillist['currentprice'];
	  $dateend=$emaillist['date_end'];
	  $projecttitle=$emaillist['project_title'];
	  
	  
	  $username=fetch_user('username',$emaillist['winner_user_id']);
	  $email=fetch_user('email',$emaillist['winner_user_id']);
	  
	  
$listing_items.= '<tr>

									<td>'.$userid.'</td>

									<td>'.$username.'</td>

									<td>'.$email.'</td>

									<td>'.$projectid.'</td>

									<td>'.$projecttitle.'</td>

									<td>'.$currentprice.'</td>

									<td>'.$dateend.'</td></tr>';

}

$listing_items.='</table>';


 define('FPDF_FONTPATH','../font/');

					

					require('pdftable_1.9/lib/pdftable.inc.php');

					

					$p = new PDFTable();

					

					$p->AddPage();

					

					$p->setfont('times','',10);

					

					$p->htmltable($listing_items);

					

					$p->output('email list.pdf','D');  


?>