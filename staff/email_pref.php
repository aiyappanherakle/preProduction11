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



$email = $ilance->db->query("select * from " . DB_PREFIX . "email_preference group by user_id order by user_id");


    $list_email = '<table border="1"><tr><td>UserId</td><td>Username</td><td>Related</td><td>Outbid</td><td>Wantlist</td><td>Recommend</td><td>Gccollection</td><td>ItemTracked</td><td>Gcoffer</td><td>Bidconfirm</td><td>Dailydeal</td></tr>';
	
	 
           while($email_list = $ilance->db->fetch_array($email))

               {
			  
			   
			               $userid=$email_list['user_id'];
						   
						    $username=fetch_user('username',$email_list['user_id']);
							
						    $related=$email_list['related'];
							
						     $outbid=$email_list['outbid'];
							 
						      $wantlist=$email_list['wantlist'];
							  
						        $recommend=$email_list['recommend'];
								
						          $gccollection=$email_list['gccollection'];
								  
						         $itemtracked=$email_list['itemtracked'];
								 
							    $gcoffer=$email_list['gcoffer'];
							
							   $bidconfirm=$email_list['bidconfirm'];
							   
							  $dailydeal=$email_list['dailydeal'];
							
						 
						    
							
						$list_email.= '<tr>

									<td>'.$userid.'</td>

									<td>'.$username.'</td>
                                     
									 <td>'.$related.'</td>

									<td>'.$outbid.'</td>

									<td>'.$wantlist.'</td>

									<td>'.$recommend.'</td>

									<td>'.$gccollection.'</td>
									
									<td>'.$itemtracked.'</td>
									
									<td>'.$gcoffer.'</td>
									
									<td>'.$bidconfirm.'</td>
									
									<td>'.$dailydeal.'</td>
									
									
									</tr>';	
							
							
							
			   }

$list_email.='</table>';



  define('FPDF_FONTPATH','../font/');

					

					require('pdftable_1.9/lib/pdftable.inc.php');

					

					$p = new PDFTable();

					

					$p->AddPage();

					

					$p->setfont('times','',8);

					

					$p->htmltable($list_email);

					

					$p->output('Email_pref_list.pdf','D');  













?>