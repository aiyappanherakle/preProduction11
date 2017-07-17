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

if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'image_update')

{

$sql_new=$ilance->db->query("SELECT * FROM ".DB_PREFIX."attachment where  project_id != cast(SUBSTRING(filename,1,LOCATE('-',filename)-1) as unsigned) OR coin_id != cast(SUBSTRING(filename,1,LOCATE('-',filename)-1) as unsigned)");

if($ilance->db->num_rows($sql_new)>0)
{
			
		while($res=$ilance->db->fetch_array($sql_new))
		
		{
		
		         $coin_id=explode("-",$res['filename']);

				 $coin_id1=$coin_id[0];
				
				$ilance->db->query("update ".DB_PREFIX."attachment set filename='".$res['filename']."', project_id = '".$coin_id1."' , coin_id = '".$coin_id1."' where filename='".$res['filename']."'");
				
		}

print_action_success('Image errors have been fixed successfully', $ilpage['staffsettings'] . '');

exit;

}

else

{

print_action_failed('No image error', $ilpage['staffsettings'] . '');

exit;

}

}

else

{

$matchin_project = $ilance->db->query("SELECT project_id,filename FROM " . DB_PREFIX . "attachment where project_id != cast(SUBSTRING(filename,1,LOCATE('-',filename)-1) as unsigned)");


$notmatching.= '<table border="1"><tr><td>Project ID Not Matching Filename</td></tr><tr><td>Projectid</td><td>Filename</td></tr>';

if($ilance->db->num_rows($matchin_project)>0)
{
      while($totallist=$ilance->db->fetch_array($matchin_project))
        {
		
		   	         $notmatching.= '<tr>

									<td>'.$totallist['project_id'].'</td>

									<td>'.$totallist['filename'].'</td></tr>';
		


		}
		
		
}		


$notmatching.='</table>';

$matchin_project1 = $ilance->db->query("SELECT coin_id,filename FROM " . DB_PREFIX . "attachment where coin_id != cast(SUBSTRING(filename,1,LOCATE('-',filename)-1) as unsigned)");


$notmatching.= '<table border="1"><tr><td>Coin ID Not Matching Filename</td></tr><tr><td>CoinID</td><td>Filename</td></tr>';

if($ilance->db->num_rows($matchin_project1)>0)
{
      while($totallist1=$ilance->db->fetch_array($matchin_project1))
        {
		
		   	         $notmatching.= '<tr>

									<td>'.$totallist1['coin_id'].'</td>

									<td>'.$totallist1['filename'].'</td></tr>';
		


		}
		
		
}		


$notmatching.='</table>';

$matchin_project2 = $ilance->db->query("SELECT project_id,coin_id FROM " . DB_PREFIX . "attachment where coin_id != project_id");


$notmatching.= '<table border="1"><tr><td>Coin ID Not Matching Project ID</td></tr><tr><td>CoinID</td><td>ProjectID</td></tr>';

if($ilance->db->num_rows($matchin_project2)>0)
{
      while($totallist2=$ilance->db->fetch_array($matchin_project2))
        {
		
		   	         $notmatching.= '<tr>

									<td>'.$totallist2['coin_id'].'</td>

									<td>'.$totallist2['project_id'].'</td></tr>';
		


		}
		
		
}		


$notmatching.='</table>';

  define('FPDF_FONTPATH','../font/');

					

					require('pdftable_1.9/lib/pdftable.inc.php');

					

					$p = new PDFTable();

					

					$p->AddPage();

					

					$p->setfont('times','',10);

					

					$p->htmltable($notmatching);

					

					$p->output('image_not_matching_projectid.pdf','D');
						  
}						  
						  
?>