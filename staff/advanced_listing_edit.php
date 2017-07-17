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
'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
    'search',
    'tabfx',
    'jquery',
	'jquery_custom_ui',
    'modal',
    'yahoo-jar',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{

	if((isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='add_single_coin') && (isset($ilance->GPC['subcmd']) and $ilance->GPC['subcmd']=='update_single_coin')) 
	{
		
		$pcg_exp = explode('.',$_POST['pcgs']);
		for($i=0; $i<count($ilance->GPC['other_information']); $i++)
		{
			$oth_in[] = $ilance->GPC['other_information'][$i];
		}
		$other_info_val = implode(',',$oth_in);

		($ilance->GPC['genuine_details_o']=='1')?$genuine_details='1':$genuine_details='0';
		($ilance->GPC['details_o']=='1')?$details='1':$details='0';

		$con_insert_cointable = $ilance->db->query("
			UPDATE  " . DB_PREFIX . "coins
			SET pcgs = '" . $pcg_exp['0'] . "',
			Title = '" . $ilance->db->escape_string($_POST['title']) . "',
			Grading_Service	= '" . $ilance->db->escape_string($_POST['grade_service']) . "',
			Grade = '" . $ilance->db->escape_string($_POST['Grade']) . "',
			Condition_Attribute	=	'" . $ilance->db->escape_string($_POST['Condition_Attribute']) . "',
			Cac = '" . $_POST['Cac'] . "',
			Star ='" . $_POST['Star'] . "',
			Plus ='" . $_POST['Plus'] . "',
			Coin_Series	=	'" . $ilance->db->escape_string($_POST['coin_series']) . "',
			Category	=	'" . $pcg_exp['0'] . "',
			Other_information		=	'" . $other_info_val . "',
			Sets 	 = '".$ilance->GPC['Sets']."',
            nocoin   = '".$ilance->GPC['nocoin']."',
			Variety = '".$ilance->GPC['Variety']."',
			genuine_details = '".$genuine_details."',
			details = '".$details."',
			QA = '".$ilance->GPC['QA']."'

			WHERE coin_id 	  =  '" . $_POST['coin_id'] . "'
		");
				 
		$coin_id_last = $_POST['coin_id'];

		$prj_query = $ilance->db->query("
			SELECT project_id FROM " . DB_PREFIX . "projects WHERE project_id = '" . $_POST['coin_id'] . "' ");
		if($ilance->db->num_rows($prj_query) > 0)
		{
			$sql2="SELECT Orderno,coin_series_denomination_no,coin_series_unique_no,coin_detail_year,coin_detail_mintmark FROM " . DB_PREFIX . "catalog_coin WHERE PCGS = '" . $pcg_exp['0'] . "'";
			$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($res2)>0)
			{
				while($line2=$ilance->db->fetch_array($res2))
				{
					$order_nos = $line2['Orderno']; 
					$denom_series = $line2['coin_series_denomination_no'];
					$denom_uniqueno = $line2['coin_series_unique_no'];
					$detail_year = $line2['coin_detail_year'];
					$coin_detail_res=$line2;
				}
			}

			$ilance->db->query("
				UPDATE  " . DB_PREFIX . "projects
				SET project_title = '" . $ilance->db->escape_string($_POST['title']) . "',
				Orderno	= '" . $order_nos . "',
				pcgs = '" . $pcg_exp['0'] . "',
				Grade = '" . $ilance->db->escape_string($_POST['Grade']) . "',
				Grading_Service	= '" . $ilance->db->escape_string($_POST['grade_service']) . "',
				Cac = '" . $_POST['Cac'] . "',
				coin_series_denomination_no ='" . $denom_series . "',
				coin_series_unique_no ='" . $denom_uniqueno . "',
				coin_detail_year	=	'" . $detail_year . "',
				cid	=	'" . $pcg_exp['0'] . "',
				Variety = '".$ilance->GPC['Variety']."',
				QA = '".$ilance->GPC['QA']."'
				WHERE project_id 	  =  '" . $_POST['coin_id'] . "'
			");

		print_action_success('updated successfully saved', 'advanced_listing_edit.php');
		exit();

		}
	}


	/* vijay  for bug 5829 * start 20.12.13 */	
	$show_coin_search=0;
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='update_coin_details')
	{
		$show_coin_search=1;
		$projec_id=(isset($ilance->GPC['project_id'])) ? $ilance->GPC['project_id'] : '';
		
		$sql_check="SELECT * FROM " . DB_PREFIX . "projects WHERE ((filtered_auctiontype = 'regular' AND winner_user_id  > '1' AND bids > '0') OR (buynow = '1' AND filtered_auctiontype = 'fixed' AND buynow_qty = '0')) AND  project_id = '".$projec_id."'";
		$update_coin_details_check= $ilance->db->query($sql_check);
		
		if($ilance->db->num_rows($update_coin_details_check) > 0)
		{
		 	print_action_failed('Please give valid Coin id details', 'advanced_listing_edit.php');
			exit();
		}	 

		$con_insert = $ilance->db->query(" SELECT * FROM " . DB_PREFIX . "coins 
				WHERE coin_id= '".$ilance->GPC['project_id']."'", 0, null, __FILE__, __LINE__);

		if($ilance->db->num_rows($con_insert) > 0)
		{
			$rescat = $ilance->db->fetch_array($con_insert);
			$coin_id = $rescat['coin_id'];
			$user_id = $rescat['user_id'];
			$consignid = $rescat['consignid'];
			$pcgs =  $rescat['pcgs'];
			$cat_id =  $rescat['Category'];
			$Coin_Series =  $rescat['Coin_Series']; 
			$Title =  $rescat['Title']; 

			$sqlcat_pcg = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "catalog_coin
								WHERE PCGS = '".$rescat['pcgs']."'                        
								", 0, null, __FILE__, __LINE__);
			$rescat_pcg = $ilance->db->fetch_array($sqlcat_pcg);

			//echo '<pre>';print_r($rescat_pcg);
			
			if(empty($rescat_pcg['coin_detail_mintmark']))
				$myvem = '';
			else
				$myverm = '-'.$rescat_pcg['coin_detail_mintmark'];

			if($rescat_pcg['coin_detail_major_variety'] == '')
				$myver = '';
			else
				$myver = ' '.$rescat_pcg['coin_detail_major_variety'];
			
			$coin_year = $rescat_pcg['coin_detail_year'];
			if(empty($rescat_pcg['coin_detail_suffix']))
			{
				$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
				$suff = '';
			}
			else
			{
				$title_eng  =  $rescat_pcg['coin_detail_year'].''.$myverm.' '.$rescat_pcg['coin_detail_coin_series'].''.$myver;
				$suff = $rescat_pcg['coin_detail_suffix'];
			}

			$pro_tes =  fetch_cat('coin_detail_proof',$rescat['pcgs']);
			if($pro_tes == 'y')
				$text_pro = 'Proof-'.$rescat['Grade'];
			else if($pro_tes == 's')
				$text_pro = 'Specimen-'.$rescat['Grade'];
			else
				$text_pro = $pro_pc.''.$rescat['Grade'];

			$upda_val = '<input type="hidden" name="suff" value="'.$suff.'" id="suff">
							<input type="hidden"  value="'.$title_eng.'" id="cat_tit_p">
							<input type="hidden"  value="" id="nbgrade"> <input type="hidden" value="'.$text_pro .'" id="grad_pro">';
			$editcat = fetch_cat_title('title_eng',$rescat['Category']);
			$Certification_No =  $rescat['Certification_No'];
			$Alternate_inventory_No =  $rescat['Alternate_inventory_No'];
			$Sets = $rescat['Sets'];
			$nocoin = $rescat['nocoin'];

			$grade_service_update = '<select name="grade_service" id="grade_service" onchange="grade(this.value);">
			   <option value="">Select</option>';
				 $sqlcat_gr = $ilance->db->query("
					SELECT * FROM " . DB_PREFIX . "grading_service ", 0, null, __FILE__, __LINE__);
				 if ($ilance->db->num_rows($sqlcat_gr) > 0)
				 {         
					while ($rescat_s = $ilance->db->fetch_array($sqlcat_gr))
					{	
						if($rescat_s['grading'] == $rescat['Grading_Service'])
							$grade_service_update.='<option value="'.$rescat_s['grading'].'" selected="selected">'.$rescat_s['grading'].'</option>';
						else
							$grade_service_update.='<option value="'.$rescat_s['grading'].'">'.$rescat_s['grading'].'</option>';
					}
						
				}
			
			$grade_service_update.='</select>';

			$pro_grade_edit  = '<select name="Grade" id="Grade" onchange="newgrade(this.value,document.getElementById(\'pcgs\').value);"><option value="">Select</option>';
				  
				$sqlcat_p_g = $ilance->db->query("
					SELECT * FROM " . DB_PREFIX . "coin_proof ORDER BY id DESC ", 0, null, __FILE__, __LINE__);
				 
				 if ($ilance->db->num_rows($sqlcat_p_g) > 0)
				 {         
					while ($rescat_p_g = $ilance->db->fetch_array($sqlcat_p_g))
					{
						if($rescat_p_g['value'] == $rescat['Grade'])
							$pro_grade_edit.='<option value="'.$rescat_p_g['value'].'" selected="selected">'.$rescat_p_g['value'].'</option>';
						else
							$pro_grade_edit.='<option value="'.$rescat_p_g['value'].'">'.$rescat_p_g['value'].'</option>';
					}
						
				}
				
			$pro_grade_edit.='</select>';

			//Condition Attribute update
			$con_update = '<select onchange="grade(this.value);" style="font-family: Verdana;" name="Condition_Attribute" id="Condition_Attribute"><option value="">Select</option>
		   ';
			$sqlcat_at = $ilance->db->query("
				SELECT * FROM " . DB_PREFIX . "coin_condition ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlcat_at) > 0)
			{         
				while ($rescat_a = $ilance->db->fetch_array($sqlcat_at))
				{
					if($rescat_a['coincondition'] == $rescat['Condition_Attribute'])
						$con_update.='<option value="'.$rescat_a['coincondition'].'" selected="selected">'.$rescat_a['coincondition'].'</option>';
					else
						$con_update.='<option value="'.$rescat_a['coincondition'].'">'.$rescat_a['coincondition'].'</option>';
				}
					
			}
		
		 $con_update.='</select>';

		$listarray = explode(',', $rescat['Other_information']);
					  
		$w=1;
		foreach($listarray as $text)
		{
			$data[$w]= $text;
			$w++;
		}

		//other inform
		$con_pedi_oth = '<select name="other_information[]" id="other_information" multiple="multiple" style="width: 200px; height: 100px;" onchange="grade(this.value);">
			   ';
				 $sqlcat_pedi_oth = $ilance->db->query("
				SELECT * FROM " . DB_PREFIX . "coin_other_details ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlcat_pedi_oth) > 0)
				{         
					
					while ($rescat_pedi_oth = $ilance->db->fetch_array($sqlcat_pedi_oth))
					{
						if(array_search($rescat_pedi_oth['other_details'],$data))
						{
							$selected_list = 'selected="selected"';
						}
						else
						{
							$selected_list = '';
						}
									
								$con_pedi_oth.='<option value="'.$rescat_pedi_oth['other_details'].'" '.$selected_list.'>'.$rescat_pedi_oth['other_details'].'</option>';
					}
						
				}
			
		$con_pedi_oth.='</select>';


			//cac and qa and star and plus selected
			if($rescat['genuine_details'] == '1')
				$seleted_genuine = '<input type="radio" name="genuine_details_o" id="genuine_details_o" value="1" onclick="grade(\'Genuine\')" checked="checked"/>Yes<input type="radio" name="genuine_details_o" id="genuine_details_o_n" value="0"  onclick="grade(\'0\')"/>No';
			else 
				$seleted_genuine = '<input type="radio" name="genuine_details_o" id="genuine_details_o" value="1" onclick="grade(\'Genuine\')" />Yes<input type="radio" name="genuine_details_o" id="genuine_details_o_n" value="0" checked="checked" onclick="grade(\'0\')"/>No';
			
			if($rescat['details'] == '1')
				$seleted_details = '<input type="radio" name="details_o" id="details_o" value="1" onclick="grade(\'Details\')" checked="checked"/>Yes<input type="radio" name="details_o" id="details_o_n" value="0"  onclick="grade(\'0\')"/>No';
			else 
				$seleted_details = '<input type="radio" name="details_o" id="details_o" value="1" onclick="grade(\'Details\')" />Yes<input type="radio" name="details_o" id="details_o_n" value="0" checked="checked" onclick="grade(\'0\')"/>No';

			if($rescat['Cac'] == '1')
				$seleted = '<input type="radio" name="Cac" id="Cac" value="1" onclick="grade(\'CAC\')" checked="checked"/>Yes<input type="radio" name="Cac" id="Cacn" value="0"  onclick="grade(\'0\')"/>No';
			else 
				$seleted = '<input type="radio" name="Cac" id="Cac" value="1" onclick="grade(\'CAC\')" />Yes<input type="radio" name="Cac" id="Cacn" value="0" checked="checked" onclick="grade(\'0\')"/>No';	
			
			if($rescat['QA'] == '1')
				$seleted_qa = '<input type="radio" name="QA" id="QA" value="1" onclick="grade(\'QA\')" checked="checked"/>Yes<input type="radio" name="QA" id="Qan" value="0"  onclick="grade(\'0\')"/>No';
			else 
				$seleted_qa = '<input type="radio" name="QA" id="QA" value="1" onclick="grade(\'QA\')" />Yes<input type="radio" name="QA" id="Qan" value="0" checked="checked" onclick="grade(\'0\')"/>No';	

			if($rescat['Star'] == '1')
				$seleted_star = '<input type="radio" name="Star" id="Star" value="1" checked="checked" onclick="grade(\'STAR\')"/>Yes<input type="radio" name="Star" id="Starn" value="0" onclick="grade(\'0\')"/>No';
			else
				$seleted_star = '<input type="radio" name="Star" id="Star" value="1"  onclick="grade(\'STAR\')"/>Yes<input type="radio" name="Star" id="Starn" value="0" checked="checked" onclick="grade(\'0\')"/>No';		
			
			if($rescat['Plus'] == '1')
				$seleted_plus = '<input type="radio" name="Plus" id="Plus" value="1" checked="checked" onclick="grade(\'PLUS\')"/>Yes<input type="radio" name="Plus" id="Plusn" value="0"  onclick="grade(\'0\')"/>No';
			else
				$seleted_plus = '<input type="radio" name="Plus" id="Plus" value="1"  onclick="grade(\'PLUS\')"/>Yes<input type="radio" name="Plus" id="Plusn" checked="checked" value="0"  onclick="grade(\'0\')"/>No';	

			if($rescat['Variety'] == '1')
				$seleted_Variety = '<input type="radio" name="Variety" id="Variety" value="1" checked="checked" onclick="grade(\'Variety\')"/>Yes<input type="radio" name="Variety" id="Variety" value="0"  onclick="grade(\'0\')"/>No';
			else
				$seleted_Variety = '<input type="radio" name="Variety" id="Variety" value="1"  onclick="grade(\'Variety\')"/>Yes<input type="radio" name="Variety" id="Variety" checked="checked" value="0"  onclick="grade(\'0\')"/>No';	


			// echo '<pre>';
			// print_r($rescat);
			// exit;
		}
		$sql=" SELECT p.project_id,p.project_title,p.Description,c.coin_id,c.Title,c.description FROM " . DB_PREFIX . "coins AS c
				LEFT JOIN  " . DB_PREFIX . "projects AS p ON p.project_id= c.coin_id 
				WHERE c.coin_id = '".$projec_id."'
				GROUP BY coin_id ";

		$update_coin_details= $ilance->db->query($sql);

		if($ilance->db->num_rows($update_coin_details) > 0)
		{
			while($update_coin=$ilance->db->fetch_array($update_coin_details))
			{
			$ifcoins = true;
			if ($update_coin['project_id'] > 0) {
            $edit_reslt['project_id'] =  $update_coin['project_id'];
            $edit_reslt['project_id1'] = '<input type="hidden" name="project_id"  id="project_id"  value="'.$update_coin[project_id].'" size="8"/>';
            $edit_reslt['project_title'] = '<input type="text" name="project_title"  id="project_title"  value="'.$update_coin[project_title].'" size="60"/>';
            $edit_reslt['Description'] = '<input type="text" name="Description"  id="Description"  value="'.$update_coin[Description].'" size="80"/>';
            }
            else
            {
            $edit_reslt['project_id'] = $update_coin['coin_id'];
            $edit_reslt['project_id1'] = '<input type="hidden" name="project_id"  id="project_id"  value="'.$update_coin[coin_id].'" size="8"/>';
            $edit_reslt['project_title'] = '<input type="text" name="project_title"  id="project_title"  value="'.$update_coin[Title].'" size="60"/>';
            $edit_reslt['Description'] = '<textarea id="Description" name="Description" cols="80" rows="1">'.$update_coin[description].'</textarea>';
            

            }
            $edit_coin[]=$edit_reslt;
            
			}

			
		}
		else
		{
			$ifcoins = false;
		}

			$pprint_array = array('nocoin','Sets','coin_year','cat_id','consignid','user_id','con_pedi_oth','con_update','Title','upda_val','Alternate_inventory_No','seleted_Variety','seleted_plus','seleted_qa','seleted_star','seleted','seleted_details','seleted_genuine','pro_grade_edit','grade_service_update','Certification_No','editcat','Coin_Series','pcgs','projec_id','ifcoins','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
			$ilance->template->fetch('main', 'advanced_listing_edit.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('edit_coin'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
	}

	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='update_coins_tit_des')
	{


		/*echo '<pre>';
		print_r($ilance->GPC);
		exit;*/
		if((isset($ilance->GPC['project_id']) and $ilance->GPC['project_id']!='') 
		and ((isset($ilance->GPC['project_title']) and $ilance->GPC['project_title']!='') 
		or (isset($ilance->GPC['Description']) and $ilance->GPC['Description']!='')))
		{
			$project_title=(isset($ilance->GPC['project_title'])) ? $ilance->GPC['project_title'] : '';
			$Description=(isset($ilance->GPC['Description'])) ? $ilance->GPC['Description'] : '';
			if ($ilance->GPC['project_id'] > 0) 
			{
					

				$ilance->db->query("UPDATE  " . DB_PREFIX . "projects
					SET project_title='".$ilance->db->escape_string($project_title)."',Description ='".$ilance->db->escape_string($Description)."'	
					WHERE  project_id = '".$ilance->GPC['project_id']."'");

			  	$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
					SET Title='".$ilance->db->escape_string($project_title)."',description='".$ilance->db->escape_string($Description)."'
					WHERE  coin_id = '".$ilance->GPC['project_id']."'");

			}
			else
			{	

				$ilance->db->query("UPDATE  " . DB_PREFIX . "coins
					SET Title='".$project_title."',description='".$Description."'
					WHERE  coin_id = '".$ilance->GPC['project_id']."'");
			}

			print_action_success('Coin Title & Descriptions updated Successfully.', $ilpage['listings']);
			exit();

		}
		else
		{
			 print_action_failed('sorry please fill title & descriptios and submit again', 'advanced_listing_edit.php');
			 exit();
	  	}

	}


	$pprint_array = array('projec_id','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'advanced_listing_edit.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('listpage'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	/* vijay  ends 3.12.13 */
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>