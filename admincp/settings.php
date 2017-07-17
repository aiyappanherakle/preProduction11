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
	'countries',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['settings'] => $ilcrumbs[$ilpage['settings']]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['settings']);

if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

// #### CUSTOM REGISTRATION QUESTIONS MANAGEMENT ###############################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'registration')
{
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=registration', $_SESSION['ilancedata']['user']['slng']);
	
	$area_title = $phrase['_registration_question_management'];
	$page_title = SITE_NAME . ' - ' . $phrase['_registration_question_management'];
	
	$configuration_registrationdisplay = $ilance->admincp->construct_admin_input('registrationdisplay', $ilpage['settings'] . '?cmd=registration');
	$configuration_registrationupsell = $ilance->admincp->construct_admin_input('registrationupsell', $ilpage['settings'] . '?cmd=registration');
	
	$ilance->GPC['id'] = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	
	// #### remove registration question ###########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-register-question')
	{
		$ilance->admincp->remove_registration_question(intval($ilance->GPC['id']));
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=registration');
		exit();
	}
	
	// #### update registration question ###########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-register-question' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
		$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : 0;
		$profile = isset($ilance->GPC['public']) ? intval($ilance->GPC['public']) : 0;
		$guests = isset($ilance->GPC['guests']) ? intval($ilance->GPC['guests']) : 0;
		$displayvalues = isset($ilance->GPC['multiplechoice']) ? $ilance->GPC['multiplechoice'] : '';
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
		$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : 0;
		$formdefault = isset($ilance->GPC['formdefault']) ? $ilance->GPC['formdefault'] : '';
		
		// handle multilanguage question and description
		$query1 = $query2 = '';
		
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] as $slng => $value)
			{
				$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] as $slng => $value)
			{
				$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "register_questions
			SET pageid = '" . intval($ilance->GPC['pageid']) . "',
			$query1
			$query2
			inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			formname = '" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
			formdefault = '" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
			sort = '" . intval($ilance->GPC['sort']) . "',
			visible = '" . $visible . "',
			required = '" . $required . "',
			profile = '" . $profile . "',
			multiplechoice = '" . $displayvalues . "',
			cansearch = '" . $cansearch . "',
			guests = '" . $guests . "'
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### add new registration question ##########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-register-question' AND isset($ilance->GPC['pageid']) AND $ilance->GPC['pageid'] > 0 AND !empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']) AND isset($ilance->GPC['formname']) AND isset($ilance->GPC['inputtype']))
	{
		$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : 0;
		$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : 0;
		$profile = isset($ilance->GPC['public']) ? intval($ilance->GPC['public']) : 0;
		$guests = isset($ilance->GPC['guests']) ? intval($ilance->GPC['guests']) : 0;
		$displayvalues = isset($ilance->GPC['multiplechoice']) ? $ilance->GPC['multiplechoice'] : '';
		$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : 0;
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
		$formdefault = isset($ilance->GPC['formdefault']) ? $ilance->GPC['formdefault'] : '';
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "register_questions
			(questionid, pageid, formname, formdefault, inputtype, multiplechoice, sort, required, profile, cansearch, guests)
			VALUES(
			NULL,
			'" . intval($ilance->GPC['pageid']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
			'" . $formdefault . "',
			'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			'" . $displayvalues . "',
			'" . $sort . "',
			'" . $required . "',
			'" . $profile . "',
			'" . $cansearch . "',
			'" . $guests . "')
		");
		$insid = $ilance->db->insert_id();
		
		$query1 = $query2 = '';
		
		if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
		{
			// questions
			foreach ($ilance->GPC['question'] as $slng => $value)
			{
				$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
			
			// descriptions
			foreach ($ilance->GPC['description'] as $slng => $value)
			{
				$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
			}
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "register_questions
			SET
			$query1
			$query2
			visible = '" . $visible . "'
			WHERE questionid = '" . $insid . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### edit registration question edit handler ################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-register-question' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sqlregq = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "register_questions
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		$resregq = $ilance->db->fetch_array($sqlregq);
		
		$register_page_pulldown = '
		<select name="pageid" style="font-family: verdana">
		    <option value="1"'; ($resregq['pageid'] == 1) ? $register_page_pulldown .= ' selected="selected"' : '';
		    $register_page_pulldown .= '>' . $phrase['_page_1_member_details'] . '</option>
		    <option value="2"'; ($resregq['pageid'] == 2) ? $register_page_pulldown .= ' selected="selected"' : '';
		    $register_page_pulldown .= '>' . $phrase['_page_2_personal_details'] . '</option>
		    <option value="3"'; ($resregq['pageid'] == 3) ? $register_page_pulldown .= ' selected="selected"' : '';
		    $register_page_pulldown .= '>' . $phrase['_page_3_subscription_details'] . '</option>
		</select>';
		
		$regquestion_subcmd = 'update-register-question';
		$regquestion_id_hidden = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
		$regsort = $resregq['sort'];
    
		$regchecked_visible = ($resregq['visible'] > 0) ? 'checked="checked"' : '';
		$regchecked_required = ($resregq['required'] > 0) ? 'checked="checked"' : '';
		$regchecked_public = ($resregq['profile'] > 0) ? 'checked="checked"' : '';
		$regchecked_guests = ($resregq['guests'] > 0) ? 'checked="checked"' : '';
		
		$regformname = $resregq['formname'];
		$regformdefault = $resregq['formdefault'];
    
		$regsubmit_profile_question = '<input type="submit" value=" ' . $phrase['_save'] . ' " class="buttons" style="font-size:15px" /> &nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=registration">' . $phrase['_cancel'] . '</a></span>';
	}
	else
	{
		$register_page_pulldown = '
		<select name="pageid" style="font-family: verdana">
		    <option value="1">' . $phrase['_page_1_member_details'] . '</option>
		    <option value="2">' . $phrase['_page_2_personal_details'] . '</option>
		    <option value="3">' . $phrase['_page_3_subscription_details'] . '</option>
		</select>';
    
		$regquestion_subcmd = 'insert-register-question';
		$regquestion_id_hidden = $regquestion = $regquestion_description = $regsort = '';
		$regchecked_visible = $regchecked_required = $regchecked_public = $regchecked_guests = $regformdefault = '';
		$regformname = construct_form_name(14);
		$regsubmit_profile_question = '<input type="submit" value=" ' . $phrase['_save'] . ' " class="buttons" style="font-size:15px" />';
	}
	
	$regprofile_inputtype_pulldown = '<select name="inputtype" style="font-family: verdana">';

	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-register-question')
	{
		$regprofile_inputtype_pulldown .= '<option value="yesno"'; if ($resregq['inputtype'] == "yesno") { $regprofile_inputtype_pulldown .= ' selected="selected"'; } $regprofile_inputtype_pulldown .= '>' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="int"'; if ($resregq['inputtype'] == "int") { $regprofile_inputtype_pulldown .= ' selected="selected"'; } $regprofile_inputtype_pulldown .= '>' . $phrase['_integer_field_numbers_only'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="textarea"'; if ($resregq['inputtype'] == "textarea") { $regprofile_inputtype_pulldown .= ' selected="selected"'; } $regprofile_inputtype_pulldown .= '>' . $phrase['_textarea_field_multiline'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="text"'; if ($resregq['inputtype'] == "text") { $regprofile_inputtype_pulldown .= ' selected="selected"'; } $regprofile_inputtype_pulldown .= '>' . $phrase['_input_text_field_singleline'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="multiplechoice"'; if ($resregq['inputtype'] == "multiplechoice") { $regprofile_inputtype_pulldown .= ' selected="selected"'; } $regprofile_inputtype_pulldown .= '>' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="pulldown"'; if ($resregq['inputtype'] == "pulldown") { $regprofile_inputtype_pulldown .= ' selected="selected"'; } $regprofile_inputtype_pulldown .= '>' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
		$multiplechoice = $resregq['multiplechoice'];
		$checked_question_cansearch = ($resregq['cansearch']) ? 'checked="checked"' : '';
	}
	else
	{
		$regprofile_inputtype_pulldown .= '<option value="yesno">' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="int">' . $phrase['_integer_field_numbers_only'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="textarea">' . $phrase['_textarea_field_multiline'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="text">' . $phrase['_input_text_field_singleline'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="multiplechoice">' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$regprofile_inputtype_pulldown .= '<option value="pulldown">' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
		$multiplechoice = $checked_question_cansearch = '';
	}
	
	$regprofile_inputtype_pulldown .= '</select>';
	
	$row_count = 0;
	$registerlanguages = array();
	$languages = $ilance->db->query("
		SELECT languagecode, title
		FROM " . DB_PREFIX . "language
	");
	while ($language = $ilance->db->fetch_array($languages))
	{
		$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
		$language['language'] = stripslashes($language['title']);
		$language['question'] = '';	
		$language['description'] = '';
			    
		// fetch english question and description values
		$sql = $ilance->db->query("
			SELECT question_$language[slng] AS question, description_$language[slng] AS description
			FROM " . DB_PREFIX . "register_questions
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql))
			{
				$language['question'] = stripslashes($res['question']);	
				$language['description'] = stripslashes($res['description']);
				$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			}
		}
		$row_count++;
		$registerlanguages[] = $language;
	}
	    
	$show['no_register_questions'] = true;
	
	$regquestions = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "register_questions
		ORDER BY sort ASC
	");
	if ($ilance->db->num_rows($regquestions) > 0)
	{
		$row_count2 = 0;
		$show['no_register_questions'] = false;
		while ($rows = $ilance->db->fetch_array($regquestions))
		{
			$rows['question'] = stripslashes($rows['question_' . $_SESSION['ilancedata']['user']['slng']]);
			$rows['question_description'] = stripslashes($rows['description_' . $_SESSION['ilancedata']['user']['slng']]);
			$rows['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=registration&amp;subcmd=_edit-register-question&amp;id=' . $rows['questionid'] . '#registrationquestion"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$rows['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=registration&amp;subcmd=_remove-register-question&amp;id=' . $rows['questionid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$rows['question_active'] = ($rows['visible']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$rows['isrequired'] = ($rows['required']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$rows['inputtype'] = mb_strtolower($rows['inputtype']);
			$rows['sortinput'] = '<input type="text" name="sort[' . $rows['questionid'] . ']" value="' . $rows['sort'] . '" class="input" size="3" style="text-align:center" />';
			$rows['visibleprofile'] = ($rows['profile']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$rows['guestsprofile'] = ($rows['guests']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$register_questions[] = $rows;
			$row_count2++;
		}
	}
	
	$pprint_array = array('checked_question_cansearch','multiplechoice','regprofile_inputtype_pulldown','regsubmit_profile_question','regformdefault','regformname','regchecked_guests','regchecked_public','regchecked_required','regchecked_visible','regsort','regquestion_id_hidden','regquestion_subcmd','register_page_pulldown','configuration_registrationdisplay','configuration_registrationupsell','buildversion','ilanceversion','login_include_admin','prevnext','reportrange','titlesinput','roletypepulldown','roleusertypepulldown','role_pulldown','migrate_billing_pulldown','migrate_plan_pulldown','commission_group_pulldown','permission_group_pulldown','currency','new_resource_item','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_registration_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'registration.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','register_questions','registerlanguages'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();		
}

// #### FEEDBACK CRITERIA MANAGEMENT ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'feedback')
{
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=feedback', $_SESSION['ilancedata']['user']['slng']);
	
	$area_title = $phrase['_feedback_manager'];
	$page_title = SITE_NAME . ' - ' . $phrase['_feedback_manager'];
	
	$sqlfields = $sqlfieldsinput = '';
	
	// fetch installed languages
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "language
	");
	while ($res = $ilance->db->fetch_array($sql))
	{
		$slng = mb_strtolower(mb_substr($res['languagecode'], 0, 3));
		$sqlfields .= "title_$slng, ";
		$res['slng'] = $slng;
		$res['code'] = $res['languagecode'];
		$languages[] = $res;
	}
	
	// #### ADD FEEDBACK RATING CRITERIA ###########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add')
	{
		if (empty($ilance->GPC['sort']))
		{
			$ilance->GPC['sort'] = 100;
		}
		foreach ($ilance->GPC['title'] as $shortlang => $input)
		{
			$sqlfieldsinput .= "'" . $ilance->db->escape_string($input) . "',";
		}
		
		// create new feedback criteria option
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "feedback_criteria
			(id, $sqlfields sort)
			VALUES(
			NULL,
			$sqlfieldsinput
			'" . intval($ilance->GPC['sort']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=feedback');
		exit();
	}
	
	// #### REMOVE FEEDBACK RATING CRITERIA ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removecriteria' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "feedback_criteria
			WHERE id = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=feedback');
		exit();
	}
	
	// #### UPDATE FEEDBACK RATING CRITERIA ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update' AND !empty($ilance->GPC['sort']) AND is_array($ilance->GPC['sort']))
	{
		//foreach ($ilance->GPC['title'] as $criteriaid => $title)
		foreach ($ilance->GPC['title'] AS $shortlanguage => $array)
		{
			foreach ($array AS $criteriaid => $title)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "feedback_criteria
					SET title_$shortlanguage = '" . $ilance->db->escape_string($title) . "'
					WHERE id = '" . intval($criteriaid) . "'
				");
			}
		}

		foreach ($ilance->GPC['sort'] as $criteriaid => $sort)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "feedback_criteria
				SET sort = '" . intval($sort) . "'
				WHERE id = '" . intval($criteriaid) . "'
			");
		}
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=feedback');
		exit();
	}
	
	// #### DELETE FEEDBACK ########################################
	else if (isset($ilance->GPC['remove']) AND !empty($ilance->GPC['remove']))
	{
		foreach ($ilance->GPC['id'] AS $key => $value)
		{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "feedback
					WHERE id = '" . intval($value) . "'
					LIMIT 1
				");
		}
	}
	
	// #### UPDATE FEEDBACK  #######################################
	else if (isset($ilance->GPC['update']) AND !empty($ilance->GPC['update']))
	{
		foreach ($ilance->GPC['id'] AS $key => $value)
		{
				$index = 'comm_'.$value;
				$com = $ilance->GPC[$index];
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "feedback
					SET comments = '" . $ilance->db->escape_string($com) . "'
					WHERE id = '" . intval($value) . "'
					LIMIT 1
				");
		}
	}
	
	$area_title = $phrase['_feedback_criteria_manager'];
	$page_title = SITE_NAME . ' - ' . $phrase['_feedback_criteria_manager'];
	
	($apihook = $ilance->api('admincp_feedback_settings')) ? eval($apihook) : false;
	
	// #### is admin searching feedback?
	$queryextra = '';
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
		// searching via auction listing id?
		if (isset($ilance->GPC['project_id']) AND !empty($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
		{
			$queryextra .= " AND project_id = '" . intval($ilance->GPC['project_id']) . "'";	
		}
		
		if (isset($ilance->GPC['rangepast']) AND !empty($ilance->GPC['rangepast']))
		{
			$startdate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
			$enddate = print_datetime_from_timestamp(time());
			
			$queryextra .= " AND (date_added <= '" . $enddate . "' AND date_added >= '" . $startdate . "')";
		}
	}

	// #### latest feedback recorded ###############################
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	
	$limit = ' ORDER BY date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	
	$show['nolatestfeedback'] = true;
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "feedback
		WHERE for_user_id > 0
			AND from_user_id > 0
			AND project_id > 0
		$queryextra
		ORDER BY date_added DESC
	");
	$numberrows = $ilance->db->num_rows($sql);
	
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "feedback
		WHERE for_user_id > 0
			AND from_user_id > 0
			AND project_id > 0
		$queryextra
		$limit
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		$show['nolatestfeedback'] = false;
		while ($row = $ilance->db->fetch_array($sql))
		{
			$row['cb'] = '<input type="checkbox" name="id[]" value="' . $row['id'] . '" />';
			$row['userby'] = fetch_user('username', $row['from_user_id']);
			$row['userfor'] = fetch_user('username', $row['for_user_id']);
			$type = fetch_auction('project_state', $row['project_id']);
			
			if ($type == 'service')
			{
				$row['auction'] = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $row['project_id'] . '" target="_blank">' . fetch_auction('project_title', $row['project_id']) . '</a>';
			}
			else
			{
				$row['auction'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '" target="_blank">' . fetch_auction('project_title', $row['project_id']) . '</a>';
			}
				
			if ($row['response'] == 'negative')
			{
				$row['response'] = '<span style="color:red">' . ucwords($row['response']). '</span>';
			}
			else if (($row['response'] == 'neutral'))
			{
				$row['response'] = '<span style="color:black">' . ucwords($row['response']). '</span>';
			}
			else
			{
				$row['response'] = ucwords($row['response']);
			}
			
			$id = $row['id'];
			$row['comments'] = '<input type="text" name="comm_' . $id . '" id="comm_' . $id . '" value="' . handle_input_keywords($row['comments']) . '" style="font-family: verdana; width:300px">';
			
			// since comments will be shown in bubble, addslashes where possible
			//$row['comments'] = addslashes($row['comments']);
			$row['date'] = print_date($row['date_added']);
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$latestfeedback[] = $row;
			$row_count++;
		}
	}
	
	$urlquery = print_hidden_fields($string = true, $excluded = array('cmd','page'), $questionmarkfirst = false);
	
	$prevnext = print_pagnation($numberrows, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['settings'] . '?cmd=feedback' . $urlquery);
	
	// #### feedback criteria ######################################
	$sql = $ilance->db->query("
		SELECT id, $sqlfields sort
		FROM " . DB_PREFIX . "feedback_criteria
		ORDER BY sort ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sql))
		{
			$titles = $titlesinput = '';
			foreach ($languages AS $shortlang)
			{
				$titles .= '<div align="right"><span class="gray" style="float:left; padding-right:7px; padding-top:4px">' . ucfirst($shortlang['code']) . ': </span><input type="text" name="title[' . $shortlang['slng'] . '][' . $row['id'] . ']" value="' . stripslashes($row["title_$shortlang[slng]"]) . '" class="input" style="width:85%" /></div><div style="padding-top:5px"></div>';
				$titlesinput .= '<div align="right"><span class="gray" style="float:left; padding-right:7px; padding-top:4px">' . ucfirst($shortlang['code']) . ': </span><input type="text" name="title[' . $shortlang['slng'] . ']" style="width:85%; font-family: verdana" /></div>';
			}
			
			$row['title'] = $titles;
			$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=feedback&amp;subcmd=removecriteria&amp;id=' . $row['id'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['sortaction'] = '<input type="text" name="sort[' . $row['id'] . ']" value="' . $row['sort'] . '" class="input" size="3" style="text-align: center" />';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			
			// fetch total number of ratings for this specific criteria
			$sql2 = $ilance->db->query("
				SELECT COUNT(*) AS count
				FROM " . DB_PREFIX . "feedback_ratings
				WHERE criteria_id = '" . $row['id'] . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2);
				$row['ratings'] = $res2['count'];
			}
			else
			{
				$row['ratings'] = 0;
			}
			
			$feedback[] = $row;
			$row_count++;
		}
		$show['no_feedback_rows'] = false;
	}
	else
	{
		$show['no_feedback_rows'] = true;
	}
	
	// #### reporting range pulldown ###############################
	$reportrange = '<select name="rangepast" style="font-family: verdana"><option value="">' . $phrase['_any_day'] . '</option><option value="-1 day"';
	if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>' . $phrase['_the_past_day'] . '</option><option value="-1 week"';
	if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
	{
		$reportrange .= ' selected="selected"'; 
	}
	$reportrange .= '>' . $phrase['_the_past_week'] . '</option><option value="-1 month"';
	if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>' . $phrase['_the_past_month'] . '</option><option value="-1 year"'; 
	if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
	{
		$reportrange .= ' selected="selected"';
	}
	$reportrange .= '>' . $phrase['_the_past_year'] . '</option></select>';
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','prevnext','reportrange','titlesinput','roletypepulldown','roleusertypepulldown','role_pulldown','migrate_billing_pulldown','migrate_plan_pulldown','commission_group_pulldown','permission_group_pulldown','currency','new_resource_item','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_feedback_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'feedback.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','feedback','latestfeedback'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'subscriptions')
{
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=subscriptions', $_SESSION['ilancedata']['user']['slng']);
    
	// #### MIGRATE SUBSCRIPTION PLAN USERS TO ANOTHER PLAN ################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_migrate-subscription-users' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$area_title = $phrase['_migrating_customers'];
		$page_title = SITE_NAME . ' - ' . $phrase['_migrating_customers'];
    
		$subscriptionid = intval($ilance->GPC['id']);
		$subscription_group_name = $ilance->db->fetch_field(DB_PREFIX . 'subscription', 'subscriptionid=' . intval($ilance->GPC['id']), 'title');
		$subscription_duration = $ilance->db->fetch_field(DB_PREFIX . 'subscription', 'subscriptionid=' . intval($ilance->GPC['id']), 'length');
		$subscription_units = $ilance->db->fetch_field(DB_PREFIX . 'subscription', 'subscriptionid=' . intval($ilance->GPC['id']), 'units');
		
		$migrate_plan_pulldown = $ilance->admincp->print_migrate_to_pulldown($ilance->GPC['id']);
		
		$count = '0';				    
		$sqla = $ilance->db->query("
			SELECT COUNT(*) AS usersactive
			FROM " . DB_PREFIX . "subscription_user
			WHERE subscriptionid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sqla) > 0)
		{
			$resactive = $ilance->db->fetch_array($sqla);
			$count = $resactive['usersactive'];
		}
    
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','subscription_duration','subscription_units','count','subscription_group_name','subscriptionid','migrate_plan_pulldown','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_subscriptions_migrateplan_end')) ? eval($apihook) : false;
    
		$ilance->template->fetch('main', 'subscriptions_migrateplan.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
    
	// #### MIGRATE SUBSCRIPTION PLAN USERS HANDLER ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_migrate-subscription-users' AND isset($ilance->GPC['migratefromid']) AND $ilance->GPC['migratefromid'] > 0 AND isset($ilance->GPC['migratetoid']) AND $ilance->GPC['migratetoid'] > 0)
	{
		$area_title = $phrase['_migrating_customers'];
		$page_title = SITE_NAME . ' - ' . $phrase['_migrating_customers'];
    
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "subscription_user
			SET subscriptionid = '" . intval($ilance->GPC['migratetoid']) . "'
			WHERE subscriptionid = '" . intval($ilance->GPC['migratefromid']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
    
	// #### ADD NEW SUBSCRIPTION GROUP #####################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-subscription-group' AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['description']) AND !empty($ilance->GPC['description']))
	{
		$area_title = $phrase['_composing_new_subscription_permission_group'];
		$page_title = SITE_NAME . ' - ' . $phrase['_composing_new_subscription_permission_group'];
    
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "subscription_group
			WHERE title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			print_action_failed($phrase['_this_subscription_permission_group_already_exists_and_cannot_be_recreated'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		else
		{
			if (empty($ilance->GPC['description']) OR empty($ilance->GPC['title']))
			{
				print_action_failed($phrase['_you_can_only_create_a_new_subscription_permission_group_by_filling_out'], $ilpage['settings'] . '?cmd=subscriptions');
				exit();
			}
			
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "subscription_group
				(subscriptiongroupid, title, description, canremove)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
				'1')
			");
			
			print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
	}
	
	// #### ADD NEW SUBSCRIPTION PLAN ######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-subscription-plan' AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['description']) AND !empty($ilance->GPC['description']) AND isset($ilance->GPC['cost']) AND !empty($ilance->GPC['cost']))
	{
		$area_title = $phrase['_composing_new_subscription_plan'];
		$page_title = SITE_NAME . ' - ' . $phrase['_composing_new_subscription_plan'];
    
		$ilance->GPC['icon'] = isset($ilance->GPC['icon']) ? $ilance->GPC['icon'] : 'default.gif';
		$migratetoid = 0;
		$migratelogic = 'none';
		if (isset($ilance->GPC['migratetoid']))
		{
			if ($ilance->GPC['migratetoid'] != 'none')
			{
				$migratetoid = intval($ilance->GPC['migratetoid']);
			}
			if ($ilance->GPC['migratelogic'] != 'none')
			{
				$migratelogic = $ilance->GPC['migratelogic'];
			}
		}
		
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "subscription
			WHERE title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			print_action_failed($phrase['_this_subscription_plan_already_exists_and_cannot_be_recreated'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		else
		{
			if (empty($ilance->GPC['description']) OR empty($ilance->GPC['title']) OR empty($ilance->GPC['cost']))
			{
				print_action_failed($phrase['_you_can_only_create_a_new_subscription_plan_by_filling_out'], $ilpage['settings'] . '?cmd=subscriptions');
				exit();
			}
				
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "subscription
				(subscriptionid, title, description, cost, length, units, subscriptiongroupid, roleid, active, visible, icon, migrateto, migratelogic)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['cost']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['duration']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['units']) . "',
				'" . intval($ilance->GPC['subscriptiongroupid']) . "',
				'" . intval($ilance->GPC['roleid']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['active']) . "',
				'" . intval($ilance->GPC['visible']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['icon']) . "',
				'" . $migratetoid . "',
				'" . $ilance->db->escape_string($migratelogic) . "')
			");
			
			print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
	}
	
	// #### ADD SUBSCRIPTION ROLE ##########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-role' AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['purpose']) AND !empty($ilance->GPC['purpose']))
	{
		$area_title = $phrase['_composing_new_subscription_role'];
		$page_title = SITE_NAME . ' - ' . $phrase['_composing_new_subscription_role'];

		if (empty($ilance->GPC['purpose']) OR empty($ilance->GPC['title']))
		{
			print_action_failed($phrase['_you_can_only_create_a_new_subscription_role_by_filling_out_all'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}

		// create new subscription commission group
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "subscription_roles
			(roleid, purpose, title, custom, active)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['purpose']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['custom']) . "',
			'" . intval($ilance->GPC['visible']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	
	// #### REMOVE SUBSCRIPTION ROLE #######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-role' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$area_title = $phrase['_removing_subscription_roles'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_subscription_roles'];
    
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "subscription_roles
			WHERE roleid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	
	// #### REMOVE SUBSCRIPTION PLAN #######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-subscription-plan' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$area_title = $phrase['_removing_subscription_plans'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_subscription_plans'];
    
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "subscription
			WHERE subscriptionid = '" . intval($ilance->GPC['id']) . "'
			    AND canremove = '1'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	
	// #### REMOVE SUBSCRIPTION GROUP ######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-subscription-group' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$area_title = $phrase['_removing_subscription_groups'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_subscription_groups'];
    
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "subscription_group
			WHERE subscriptiongroupid = '" . intval($ilance->GPC['id']) . "'
			    AND canremove = '1'
		");
		
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "subscription_permissions
			WHERE subscriptiongroupid = '" . intval($ilance->GPC['id']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	
	// #### ADD NEW SUBSCRIPTION PERMISSIONS ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-permissions' AND isset($ilance->GPC['accesstext']) AND !empty($ilance->GPC['accesstext']) AND isset($ilance->GPC['accessname']) AND !empty($ilance->GPC['accessname']) AND isset($ilance->GPC['accesstype']) AND !empty($ilance->GPC['accesstype']) AND isset($ilance->GPC['value']) AND !empty($ilance->GPC['value']))
	{
		$area_title = $phrase['_composing_subscription_permissions'];
		$page_title = SITE_NAME . ' - ' . $phrase['_composing_subscription_permissions'];
		
		if (add_subscription_permissions($ilance->GPC['accesstext'], $ilance->GPC['accessdescription'], $ilance->GPC['accessname'], $ilance->GPC['accesstype'], $ilance->GPC['value'], 1))
		{
			print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();    
		}
		else
		{
			print_action_failed($phrase['_this_permission_access_name_has_already_been_defined_and_cannot_be_recreated'], $ilpage['settings'] . '?cmd=subscriptions');
			exit();    
		}
	}
	
	// #### UPDATE SUBSCRIPTION PERMISSIONS ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_change-permissions')
	{
		// was submit button to update permissions called?
		if (isset($ilance->GPC['updatepermissions']))
		{
			$area_title = $phrase['_updating_subscription_permissions'];
			$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_permissions'];
	
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "subscription_permissions
				WHERE subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				// we have permissions setup for this group already > just update
				foreach ($ilance->GPC AS $k => $v)
				{
					if ($k != 'cmd' OR $k != 'subcmd' OR $k != 'subscriptiongroupid' OR $k != 'updatepermissions')
					{
						$vis = 0;
						if (isset($ilance->GPC['accessvisible'][$k]) AND $ilance->GPC['accessvisible'][$k] == 'on')
						{
							$vis = 1;
						}
						
						if (isset($v) AND !is_array($v))
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "subscription_permissions
								SET value = '" . $ilance->db->escape_string($v) . "',
								visible = '" . intval($vis) . "'
								WHERE accessname = '" . $ilance->db->escape_string($k) . "'
								    AND subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
								LIMIT 1
							");
						}
					}
				}
				
				$notice = $phrase['_access_permissions_have_been_updated_for_subscription_group_id'] . " <strong>" . intval($ilance->GPC['subscriptiongroupid']) . "</strong>.";
			}
			
			// #### CREATE NEW SUBSCRIPTION PERMISSIONS ####
			else
			{
				// create an entirely new set of subscription permissions for this new group
				// which we'll be using an existing permissions group as a base start to go on
				
				// select the default site language
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
					WHERE languageid = '" . $ilance->db->escape_string($ilconfig['globalserverlanguage_defaultlanguage']) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$extraquery  = '';
					while ($languages = $ilance->db->fetch_array($sql))
					{
						// build the sql query statement for the default language
						$extraquery  .= 'accesstext_' . mb_strtolower(mb_substr($languages['languagecode'], 0, 3)) . ', accessdescription_' . mb_strtolower(mb_substr($languages['languagecode'], 0, 3)) . ', ';
						
						// fetch permissions (original and new custom ones added by the admin
						$sql3 = $ilance->db->query("
							SELECT id, subscriptiongroupid, accessname, accesstext_eng AS accesstext, accessdescription_eng AS accessdescription, accesstype, value, original, iscustom
							FROM " . DB_PREFIX . "subscription_permissions
							WHERE original = '1' OR iscustom = '1'
							GROUP BY accessname
							ORDER BY id ASC
						");
						if ($ilance->db->num_rows($sql3) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql3))
							{
								// create new permissions
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "subscription_permissions
									(id, subscriptiongroupid, accessname, " . $extraquery . " accesstype, value, canremove, original, iscustom)
									VALUES(
									NULL,
									'" . intval($ilance->GPC['subscriptiongroupid']) . "',
									'" . $ilance->db->escape_string($res['accessname']) . "',
									'" . $ilance->db->escape_string($res['accesstext']) . "',
									'" . $ilance->db->escape_string($res['accessdescription']) . "',
									'" . $ilance->db->escape_string($res['accesstype']) . "',
									'" . $ilance->db->escape_string($res['value']) . "',
									'1',
									'" . $res['original'] . "',
									'" . $res['iscustom'] . "'
									)
								");
							}
				
							// finally update new permissions with any pre-configured settings the admin may have enabled/disabled for this subscription group
							foreach ($_POST AS $k => $v)
							{
								if ($k != 'cmd' AND $k != 'subscriptiongroupid' AND $k != 'updatepermissions')
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "subscription_permissions
										SET value = '" . $ilance->db->escape_string($v) . "'
										WHERE accessname = '" . $ilance->db->escape_string($k) . "'
										    AND subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
										LIMIT 1
									");
								}
							}
						}
								    
						// update accesstext and accessdescription for all other languages
						$sqlx = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "language
							WHERE languageid != '" . $ilance->db->escape_string($ilconfig['globalserverlanguage_defaultlanguage']) . "'
						");
						if ($ilance->db->num_rows($sqlx) > 0)
						{
							while ($langs = $ilance->db->fetch_array($sqlx))
							{
								if ($ilance->db->field_exists("accesstext_" . mb_strtolower(mb_substr($langs['languagecode'], 0, 3)), DB_PREFIX . "subscription_permissions") != 1)
								{
									$ilance->db->query("
										ALTER TABLE " . DB_PREFIX . "subscription_permissions
										ADD accesstext_" . mb_strtolower(mb_substr($langs['languagecode'], 0, 3)) . " VARCHAR(150) NOT NULL
										AFTER `accessname`
									");
								}
											    
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "subscription_permissions
									SET accesstext_" . mb_strtolower(mb_substr($langs['languagecode'], 0, 3)) . " = accesstext_" . mb_strtolower(mb_substr($languages['languagecode'], 0, 3))."
								");
											    
								if ($ilance->db->field_exists("accessdescription_" . mb_strtolower(mb_substr($langs['languagecode'], 0, 3)), DB_PREFIX . "subscription_permissions") != 1)
								{
									$ilance->db->query("
										ALTER TABLE " . DB_PREFIX . "subscription_permissions
										ADD accessdescription_" . mb_strtolower(mb_substr($langs['languagecode'], 0, 3)) . " VARCHAR(150) NOT NULL
										AFTER `accessname`
									");
								}
											    
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "subscription_permissions
									SET accessdescription_" . mb_strtolower(mb_substr($langs['languagecode'], 0, 3)) . " = accessdescription_" . mb_strtolower(mb_substr($languages['languagecode'], 0, 3)) . "
								");
							}
						}
					}
				}
			}
			
			print_action_success($phrase['_access_permissions_have_been_updated_for_subscription_group_id'] . " <strong>" . intval($ilance->GPC['subscriptiongroupid']) . "</strong>.", $ilpage['settings'] . '?cmd=subscriptions');
			exit();
		}
		
		// delete subscription permission
		else if (isset($ilance->GPC['deletepermissions']))
		{
			$notice = '';
			foreach ($ilance->GPC['accessname'] AS $varname)
			{
				if (!empty($varname))
				{
					$sql = $ilance->db->query("
						SELECT canremove
						FROM " . DB_PREFIX . "subscription_permissions
						WHERE accessname = '" . $ilance->db->escape_string($varname) . "'
						LIMIT 1
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						if ($res['canremove'])
						{
							$ilance->db->query("
								DELETE FROM " . DB_PREFIX . "subscription_permissions
								WHERE accessname = '" . $ilance->db->escape_string($varname) . "'
							");
							$notice .= $phrase['_access_permission_removed'] . " <strong>{$varname}</strong>";
						}
						else
						{
							$notice .= "<strong>{$varname}</strong> " . $phrase['_is_a_framework_dependent_permission_resource_and_cannot_be_removed'];
						}
					}
				}		
			}
					
			if (empty($notice))
			{
				$success = false;
				print_action_failed($phrase['_warning_no_access_permissions_were_deleted_to_delete_a_permission_you_must_select_it_first_by_using_the_checkbox_option_beside_each_item_you_wish_to_remove'], $ilpage['settings'] . '?cmd=subscriptions');
				exit();
			}
			else
			{
				print_action_success($notice, $ilpage['settings'] . '?cmd=subscriptions');
				exit();
			}	
		}
	}
		
	// #### EDIT SUBSCRIPTION PLAN #########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-subscription-plan' AND $ilance->GPC['id'] > 0)
	{
		$area_title = $phrase['_updating_subscription_plan'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_plan'];
			
		$id = intval($ilance->GPC['id']);
		$subscriptionid = $id;
		
		$migrate_plan_pulldown = $ilance->admincp->print_migrate_to_pulldown($ilance->GPC['id']);
		$migrate_billing_pulldown = $ilance->admincp->print_migrate_billing_pulldown($ilance->GPC['id']);
		
		$sqlsubscriptions = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "subscription
			WHERE subscriptionid = '" . $id . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sqlsubscriptions) > 0)
		{
			$res = $ilance->db->fetch_array($sqlsubscriptions);
			
			$subscriptiongroupid = $res['subscriptiongroupid'];
			$title = stripslashes($res['title']);
			$description = stripslashes($res['description']);
			$cost = $res['cost'];
			$icon = $res['icon'];
			$roleid	= $res['roleid'];
					
			$duration_pulldown = '<select name="duration" style="font-family: verdana"><option value="1"'; if ($res['length'] == "1") {
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>1</option>';
			$duration_pulldown .= '<option value="2"'; if ($res['length'] == "2") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>2</option>';
			$duration_pulldown .= '<option value="3"'; if ($res['length'] == "3") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>3</option>';
			$duration_pulldown .= '<option value="4"'; if ($res['length'] == "4") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>4</option>';
			$duration_pulldown .= '<option value="5"'; if ($res['length'] == "5") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>5</option>';
			$duration_pulldown .= '<option value="6"'; if ($res['length'] == "6") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>6</option>';
			$duration_pulldown .= '<option value="7"'; if ($res['length'] == "7") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>7</option>';
			$duration_pulldown .= '<option value="8"'; if ($res['length'] == "8") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>8</option>';
			$duration_pulldown .= '<option value="9"'; if ($res['length'] == "9") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>9</option>';
			$duration_pulldown .= '<option value="10"'; if ($res['length'] == "10") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>10</option>';
			$duration_pulldown .= '<option value="11"'; if ($res['length'] == "11") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>11</option>';
			$duration_pulldown .= '<option value="12"'; if ($res['length'] == "12") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>12</option>';
			$duration_pulldown .= '<option value="13"'; if ($res['length'] == "13") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>13</option>';
			$duration_pulldown .= '<option value="14"'; if ($res['length'] == "14") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>14</option>';
			$duration_pulldown .= '<option value="15"'; if ($res['length'] == "15") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>15</option>';
			$duration_pulldown .= '<option value="16"'; if ($res['length'] == "16") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>16</option>';
			$duration_pulldown .= '<option value="17"'; if ($res['length'] == "17") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>17</option>';
			$duration_pulldown .= '<option value="18"'; if ($res['length'] == "18") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>18</option>';
			$duration_pulldown .= '<option value="19"'; if ($res['length'] == "19") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>19</option>';
			$duration_pulldown .= '<option value="20"'; if ($res['length'] == "20") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>20</option>';
			$duration_pulldown .= '<option value="21"'; if ($res['length'] == "21") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>21</option>';
			$duration_pulldown .= '<option value="22"'; if ($res['length'] == "22") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>22</option>';
			$duration_pulldown .= '<option value="23"'; if ($res['length'] == "23") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>23</option>';
			$duration_pulldown .= '<option value="24"'; if ($res['length'] == "24") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>24</option>';
			$duration_pulldown .= '<option value="25"'; if ($res['length'] == "25") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>25</option>';
			$duration_pulldown .= '<option value="26"'; if ($res['length'] == "26") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>26</option>';
			$duration_pulldown .= '<option value="27"'; if ($res['length'] == "27") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>27</option>';
			$duration_pulldown .= '<option value="28"'; if ($res['length'] == "28") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>28</option>';
			$duration_pulldown .= '<option value="29"'; if ($res['length'] == "29") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>29</option>';
			$duration_pulldown .= '<option value="30"'; if ($res['length'] == "30") { 
			$duration_pulldown .= ' selected="selected" '; }
			$duration_pulldown .= '>30</option>';
			$duration_pulldown .= '</select>';
								  
			$units_pulldown = '<select name="units" style="font-family: verdana"><option value="D"'; if ($res['units'] == "D") { 
			$units_pulldown .= ' selected="selected"'; }
			$units_pulldown .= '>'.$phrase['_days'].'</option>';
			$units_pulldown .= '<option value="M"'; if ($res['units'] == "M") { 
			$units_pulldown .= ' selected="selected"'; }
			$units_pulldown .= '>'.$phrase['_months'].'</option>'; 
			$units_pulldown .= '<option value="Y"'; if ($res['units'] == "Y") { 
			$units_pulldown .= ' selected="selected"'; }
			$units_pulldown .= '>'.$phrase['_years'].'</option>';
			$units_pulldown .= '</select>';
					
			$sqla = $ilance->db->query("
				SELECT *, COUNT(*) AS usercount
				FROM " . DB_PREFIX . "subscription_user
				WHERE subscriptionid = '" . $id . "'
				GROUP BY user_id
			");
			if ($ilance->db->num_rows($sqla) > 0)
			{
				$resactive = $ilance->db->fetch_array($sqla);
				$usercount = intval($resactive['usercount']);
			}
			else
			{
				$usercount = 0;
			}
			$selectoption = '';
			
			$active_pulldown = '<select name="active" '.$selectoption.' onchange="return alert(\'Warning: changing the state of this plan will affect ' . $usercount . ' customers within this plan.  When disabled, this plan is no longer available for usage until it becomes re-activated.\')"><option value="yes"'; if ($res['active'] == "yes") {
			$active_pulldown .= ' selected="selected"'; }
			$active_pulldown .= '>' . $phrase['_yes'] . '</option><option value="no"'; if ($res['active'] == "no") {
			$active_pulldown .= ' selected="selected"'; }
			$active_pulldown .= '>' . $phrase['_no'] . '</option></select>';
			
			$visible_pulldown = '<select name="visible" '.$selectoption.' onchange="return alert(\'Warning: changing the visiblity state of this plan will affect ' . $usercount . ' customers within this plan in terms of them seeing their selected subscription plan from the upgrade menu.  When visible, this plan can be seen from the registration menu and the subscription menus.\')"><option value="1"'; if ($res['visible'] == 1) {
			$visible_pulldown .= ' selected="selected"'; }
			$visible_pulldown .= '>' . $phrase['_yes'] . '</option><option value="0"'; if ($res['visible'] != 1) {
			$visible_pulldown .= ' selected="selected"'; }
			$visible_pulldown .= '>' . $phrase['_no'] . '</option>';
			$visible_pulldown .= '</select>';

			$permission_group_pulldown = '<select name="subscriptiongroupid" style="font-family: verdana"><optgroup label="' . $phrase['_subscription_permissions_resource'] . '">';
			
			$sql_permgroups = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "subscription_group
			");
			while ($res_phrasegroups = $ilance->db->fetch_array($sql_permgroups))
			{
				$permission_group_pulldown .= '<option value="' . $res_phrasegroups['subscriptiongroupid'] . '"';
				if ($subscriptiongroupid == $res_phrasegroups['subscriptiongroupid'])
				{
					$permission_group_pulldown .= ' selected="selected"';
				}						    
				$permission_group_pulldown .= '>' . stripslashes($res_phrasegroups['title']) . '</option>';
			}
			$permission_group_pulldown .= '</optgroup></select>';
			
			$currency = print_left_currency_symbol();
		}
		
		$roleselected = isset($roleid) ? intval($roleid) : '';
		$role_pulldown = print_role_pulldown($roleselected, 1, 1, 1);
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','migrate_billing_pulldown','migrate_plan_pulldown','role_pulldown','subscriptionid','commission_group_pulldown','permission_group_pulldown','currency','units_pulldown','duration_pulldown','cost','title','description','active_pulldown','visible_pulldown','icon','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_subscriptions_editplan_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'subscriptions_editplan.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
		
	// #### EDIT SUBSCRIPTION PERMISSION GROUP INFO ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-subscription-group' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$id = intval($ilance->GPC['id']);
		$subscriptiongroupid = $id;
		
		$show['deletebutton'] = 0;
		
		$area_title = $phrase['_updating_subscription_group'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_group'];
		
		// fetch subscription group info
		$sqlsubscriptions = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "subscription_group
			WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
		");
		if ($ilance->db->num_rows($sqlsubscriptions) > 0)
		{
			// permission group title and description
			$res = $ilance->db->fetch_array($sqlsubscriptions);
			$title = stripslashes($res['title']);
			$description = stripslashes($res['description']);
	
			// fetch subscription plans using this permissions group
			$sqlplans = $ilance->db->query("
				SELECT title
				FROM " . DB_PREFIX . "subscription
				WHERE subscriptiongroupid = '" . $subscriptiongroupid . "'
			");
			if ($ilance->db->num_rows($sqlplans) > 0)
			{
				$plans_in_group = '';
				while ($resplans = $ilance->db->fetch_array($sqlplans))
				{
					$plans_in_group .= stripslashes($resplans['title']) . ', ';
				}					
				$plans_in_group = mb_substr($plans_in_group, 0, -2);
			}
			else
			{
				// no plans currently utilizing this permissions group
				$noplans = 1;
				$plans_in_group = $phrase['_no_subscription_plans_currently_utilizing_this_permissions_group'];
			}
		}
			    
		$sqlitems = $ilance->db->query("
			SELECT id, subscriptiongroupid, accessname, accesstext_" . $_SESSION['ilancedata']['user']['slng'] . " AS accesstext, accessdescription_" . $_SESSION['ilancedata']['user']['slng'] . " AS accessdescription, accesstype, value, original, visible
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
			GROUP BY accessname
			ORDER BY id ASC
		");
		if ($ilance->db->num_rows($sqlitems) > 0)
		{
			// permissions group exist! we are just updating this group/permissions
			$row_count2 = 0;
			while ($resitems = $ilance->db->fetch_array($sqlitems))
			{
				if ($resitems['value'] == 'yes' OR $resitems['value'] == 'no')
				{ 
					if ($resitems['value'] == 'yes')
					{
						$userinput = '<label for="yes_' . $resitems['id'] . '">' . $phrase['_yes'] . ' <input type="radio" name="' . $resitems['accessname'] . '" value="yes" id="yes_' . $resitems['id'] . '" checked="checked" /></label> <label for="no_' . $resitems['id'] . '">' . $phrase['_no'] . ' <input type="radio" name="' . $resitems['accessname'] . '" value="no" id="no_' . $resitems['id'] . '" /></label>';
					}
					else
					{
						$userinput = '<label for="yes_' . $resitems['id'] . '">' . $phrase['_yes'] . ' <input type="radio" name="' . $resitems['accessname'] . '" id="yes_' . $resitems['id'] . '" value="yes" /></label> <label for="no_' . $resitems['id'] . '">' . $phrase['_no'] . ' <input type="radio" name="' . $resitems['accessname'] . '" value="no" id="no_' . $resitems['id'] . '" checked="checked" /></label>';
					}
				}
				else
				{
					$userinput = '<input type="text" name="' . $resitems['accessname'] . '" value="' . $resitems['value'] . '" style="width:75px; text-align: center" />';
				}
				
				$resitems['userinput'] = $userinput;
				$resitems['action'] = '<input type="checkbox" name="accessname[]" value="' . $resitems['accessname'] . '" />';
				$resitems['accesstype'] = $resitems['accesstype'];
				$resitems['accessname'] = stripslashes($resitems['accessname']);
				
				$resitems['accesstext'] = '<span id="edit_input_' . $resitems['id'] . '">' . stripslashes($resitems['accesstext']) . '</span>';
				$resitems['accessdescription'] = '' . stripslashes($resitems['accessdescription']) . '';
				
				if ($resitems['original'])
				{
					$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
				}
				else
				{
					$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				}

				if ($resitems['visible'] == 1) 
				{
					$checked = '" checked="checked"';
					$vis = 1;
				}
				else 
				{
					$checked = '';
					$vis = 0;
				}

				$resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" '. $checked . '/>';
				
				$row_count2++;
				$resitems['class2'] = ($row_count2 % 2) ? 'alt1' : 'alt2';
				$access_permission_items[] = $resitems;
			}				
			$show['deletebutton'] = 1;
		}
		else
		{
			// this is a entirely new permissions instance.  We must collect all original permissions
			// and any "new" custom permissions the admin/staff may have created in his/her venture
			// for only 1 given permissions group :-).  collect all original and/or custom created ones
			// and group by the unique "accessname" while ordering the sort as ascending
			
			$sqlitems = $ilance->db->query("
				SELECT id, subscriptiongroupid, accessname, accesstext_eng AS accesstext, accessdescription_eng AS accessdescription, accesstype, value, original
				FROM " . DB_PREFIX . "subscription_permissions
				WHERE original = '1' OR iscustom = '1'
				GROUP BY accessname
				ORDER BY id ASC
			");
			if ($ilance->db->num_rows($sqlitems) > 0)
			{
				$row_count2 = $row_count  = 0;
				while ($resitems = $ilance->db->fetch_array($sqlitems))
				{
					if ($resitems['value'] == 'yes' OR $resitems['value'] == 'no')
					{ 
						if ($resitems['value'] == 'yes')
						{
							$userinput = '<label for="yes_' . $resitems['id'] . '">' . $phrase['_yes'] . ' <input type="radio" name="'.$resitems['accessname'].'" value="yes" id="yes_'.$resitems['id'].'" checked="checked" /></label> <label for="no_'.$resitems['id'].'">' . $phrase['_no'] . ' <input type="radio" name="'.$resitems['accessname'].'" value="no" id="no_'.$resitems['id'].'" /></label>';
						}
						else
						{
							$userinput = '<label for="yes_' . $resitems['id'] . '">' . $phrase['_yes'] . ' <input type="radio" name="'.$resitems['accessname'].'" id="yes_'.$resitems['id'].'" value="yes" /></label> <label for="no_'.$resitems['id'].'">'.$phrase['_no'] . ' <input type="radio" name="'.$resitems['accessname'].'" value="no" id="no_'.$resitems['id'].'" checked="checked" /></label>';
						}
					}
					else
					{
						$userinput = '<input type="text" name="' . $resitems['accessname'] . '" value="' . $resitems['value'] . '" style="width:75px; text-align: center" />';
					}
							    
					$resitems['userinput'] = $userinput;
					$resitems['action'] = '<input type="checkbox" name="accessname[]" value="' . $resitems['accessname'] . '" />';
					$resitems['accesstype'] = $resitems['accesstype'];
					$resitems['accessname'] = stripslashes($resitems['accessname']);
					
					$resitems['accesstext'] = '<span id="edit_input_' . $resitems['id'] . '">' . stripslashes($resitems['accesstext']) . '</span>';
					$resitems['accessdescription'] = '' . stripslashes($resitems['accessdescription']) . '';
					
					$resitems['class2'] = ($row_count2 % 2) ? 'alt1' : 'alt2';
					$resitems['visible_perm'] = '<input type="checkbox" name="accessvisible[' . stripslashes($resitems['accessname']) . ']" value="1" checked="checked" />';
					
					$row_count2++;
					
					if ($resitems['original'])
					{
						$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="This subscription permission is original and cannot be removed (framework dependent)" />';
					}
					else
					{
						$resitems['iscustom'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="This subscription permission is custom and can be removed (added after install by an admin)" />';
					}
					
					$access_permission_items[] = $resitems;
				}
			}
		}
			    
		$new_resource_item  = "<tr>\n";
		$new_resource_item .= "  <td><div><span class=\"gray\">" . $phrase['_title'] . " (example: Attach Limit)</span></div><div><input type=\"text\" name=\"accesstext\" style=\"width:98%\"></div><div style=\"padding-top:6px\"><span class=\"gray\">" . $phrase['_description'] . " (example: Defines the attachment limit)</span></div><div><input type=\"text\" name=\"accessdescription\" style=\"width:98%\"></div></td>\n";
		$new_resource_item .= "  <td><div align=\"center\" class=\"smaller\"><select name=\"accesstype\" style=\"font-family: verdana\"><option value=\"yesno\">".$phrase['_yes']." / ".$phrase['_no']."</option><option value=\"int\">Integer</option></select></div></td>\n";
		$new_resource_item .= "  <td><div align=\"center\" class=\"smaller\"><input type=\"text\" name=\"accessname\" style=\"width:75px\"></div></td>\n";
		$new_resource_item .= "  <td align=\"center\"><input type=\"text\" name=\"value\" style=\"width:55px\"></td>\n";
		$new_resource_item .= "  <td align=\"center\"><input type=\"submit\" class=\"buttons\" name=\"newaccess\" value=\"".$phrase['_create']."\" style=\"font-size:15px\"></td>\n";
		$new_resource_item .= "</tr>\n";
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','new_resource_item','subscriptiongroupid','plans_in_group','subscriptionid','commission_group_pulldown','permission_group_pulldown','currency','units_pulldown','duration_pulldown','cost','title','description','active_pulldown','visible_pulldown','icon','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_subscriptions_editperm_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'subscriptions_editperm.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','access_permission_items'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
		
	// #### EDIT SUBSCRIPTION ROLE #################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-role' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$area_title = $phrase['_updating_subscription_role'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_role'];
		
		$id = intval($ilance->GPC['id']);
		$sqlcommissions = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "subscription_roles
			WHERE roleid = '" . $id . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sqlcommissions) > 0)
		{
			$res = $ilance->db->fetch_array($sqlcommissions);
			$title = stripslashes($res['title']);
			$purpose = stripslashes($res['purpose']);
			$custom = $res['custom'];
			$roletypepulldown = $ilance->admincp->print_roletype_pulldown($res['roletype']);
			$roleusertypepulldown = $ilance->admincp->print_roleusertype_pulldown($res['roleusertype']);
			$rolevisible = '<select name="visible"><option value="1">' . $phrase['_yes'] . '</option><option value="0" selected="selected">' . $phrase['_no'] . '</option>';
			if ($res['active'] == 1)
			{
				$rolevisible = '<select name="visible"><option value="1" selected="selected">' . $phrase['_yes'] . '</option><option value="0">' . $phrase['_no'] . '</option>';
			}
		}
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','roletypepulldown','roleusertypepulldown','id','title','description','purpose','custom','rolevisible','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_subscriptions_editrole_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'subscriptions_editrole.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
		
	// #### UPDATE SUBSCRIPTION ROLE ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-role' AND isset($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0 AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['purpose']) AND !empty($ilance->GPC['purpose']))
	{
		$area_title = $phrase['_updating_subscription_role'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_role'];
		
		$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : '1';
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "subscription_roles
			SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			purpose = '" . $ilance->db->escape_string($ilance->GPC['purpose']) . "',
			custom = '" . $ilance->db->escape_string($ilance->GPC['custom']) . "',
			roletype = '" . $ilance->db->escape_string($ilance->GPC['roletype']) . "',
			roleusertype = '" . $ilance->db->escape_string($ilance->GPC['roleusertype']) . "',
			active = '" . $visible . "'
			WHERE roleid = '" . intval($ilance->GPC['roleid']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
		
	// #### UPDATE SUBSCRIPTION PLAN ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-subscription-plan' AND isset($ilance->GPC['subscriptionid']) AND $ilance->GPC['subscriptionid'] > 0 AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['description']) AND !empty($ilance->GPC['description']))
	{
		$area_title = $phrase['_updating_subscription_plan'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_plan'];
		
		$active = isset($ilance->GPC['active']) ? $ilance->GPC['active'] : '1';
		$visible = isset($ilance->GPC['visible']) ? (int)$ilance->GPC['visible'] : '1';
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "subscription
			SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			cost = '" . $ilance->db->escape_string($ilance->GPC['cost']) . "',
			length = '" . intval($ilance->GPC['duration']) . "',
			units = '" . $ilance->db->escape_string(mb_strtoupper($ilance->GPC['units'])) . "',
			subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "',
			roleid = '" . intval($ilance->GPC['roleid']) . "',
			active = '" . $active . "',
			visible = '" . $visible . "',
			icon = '" . $ilance->db->escape_string($ilance->GPC['icon']) . "',
			migrateto = '" . intval($ilance->GPC['migratetoid']) . "',
			migratelogic = '" . $ilance->db->escape_string($ilance->GPC['migratelogic']) . "'
			WHERE subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
		
	// #### UPDATE SUBSCRIPTION GROUP TITLE ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-subscription-group' AND isset($ilance->GPC['subscriptiongroupid']) AND $ilance->GPC['subscriptiongroupid'] > 0 AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['description']) AND !empty($ilance->GPC['description']))
	{
		$area_title = $phrase['_updating_subscription_group'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_subscription_group'];
		
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "subscription_group
			SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
			WHERE subscriptiongroupid = '" . intval($ilance->GPC['subscriptiongroupid']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=subscriptions');
		exit();
	}
	else
	{
		$area_title = $phrase['_subscription_plan_management_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_subscription_plan_management_menu'];
		
		($apihook = $ilance->api('admincp_subscription_settings')) ? eval($apihook) : false;
		
		$resplans = $ilance->db->query("
			SELECT
			s.subscriptionid, s.title, s.description, s.cost, s.length, s.units, s.subscriptiongroupid, s.canremove, s.visible, s.icon, s.active, s.roleid, g.subscriptiongroupid
			FROM " . DB_PREFIX . "subscription as s,
			" . DB_PREFIX . "subscription_group as g
			WHERE s.subscriptiongroupid = g.subscriptiongroupid
		");
		if ($ilance->db->num_rows($resplans) > 0)
		{
			$row_count = 0;
			while ($row = $ilance->db->fetch_array($resplans))
			{
				$sqla = $ilance->db->query("
					SELECT COUNT(*) AS usersactive
					FROM " . DB_PREFIX . "subscription_user
					WHERE subscriptionid = '" . $row['subscriptionid'] . "'
				");
				if ($ilance->db->num_rows($sqla) > 0)
				{
					$resactive = $ilance->db->fetch_array($sqla);
					$row['active'] = $resactive['usersactive'];
				}
				else
				{
					$row['active'] = '0';
				}
					    
				$sqle = $ilance->db->query("
					SELECT COUNT(*) AS usersexpired
					FROM " . DB_PREFIX . "subscription_user
					WHERE subscriptionid = '" . $row['subscriptionid'] . "'
					    AND active = 'no'
				");
				if ($ilance->db->num_rows($sqle) > 0)
				{
					$resexpired = $ilance->db->fetch_array($sqle);
					$row['expired'] = $resexpired['usersexpired'];
				}
				else
				{
					$row['expired'] = '0';
				}
					    
				$row['subscriptionid'] = $row['subscriptionid'];
				$row['subscriptiongroupid'] = $row['subscriptiongroupid'];
				$row['subscriptiongroupname'] = $ilance->db->fetch_field(DB_PREFIX . "subscription_group", "subscriptiongroupid=" . $row['subscriptiongroupid'], "title");
				$row['title'] = stripslashes($row['title']);
				$row['description'] = stripslashes($row['description']);
				if ($row['cost'] > 0)
				{
					$row['cost'] = $ilance->currency->format($row['cost']);
				}
				else
				{
					$row['cost'] = $phrase['_free'];
				}
				
				$row['units'] = print_unit($row['units']);
				if ($row['active'] > 0)
				{
					$row['move'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_migrate-subscription-users&amp;id=' . $row['subscriptionid'] . '" title="Migrate '.$row['active'].' users to a different subscription plan">'.$phrase['_migrate'].'</a>';
				}
				else
				{
					$row['move'] = '<span style="color:#888888" title="' . $phrase['_cannot_migrate_users__no_users_exist_within_plan'] . '">'.$phrase['_migrate'].'</span>';
				}
				$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-subscription-plan&amp;id=' . $row['subscriptionid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
					    
				if ($row['canremove'] == 1 AND $row['active'] == 0)
				{
					$row['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_remove-subscription-plan&amp;id=' . $row['subscriptionid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				}
				else
				{
					$row['remove'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" alt="' . $phrase['_users_exist_please_migrate_all_users_before_removing_an_existing_subscription_plan'] . '" />';
				}
					    
				if ($row['visible'])
				{
					$row['isvisible'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="' . $phrase['_yes'] . '" />';
				}
				else
				{
					$row['isvisible'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="' . $phrase['_no'] . '" />';
				}      
				$row['action'] = '<input type="radio" name="subscriptionid" id="subscriptionid" value="' . $row['subscriptionid'] . '" />';
				$row['access'] = $row['subscriptiongroupname'];
				
				// using role?
				$sqlr = $ilance->db->query("
					SELECT title
					FROM " . DB_PREFIX . "subscription_roles
					WHERE roleid = '" . $row['roleid'] . "'
				");
				if ($ilance->db->num_rows($sqlr) > 0)
				{
					$resrole = $ilance->db->fetch_array($sqlr);
					$row['usingrole'] = stripslashes($resrole['title']);
				}
				else
				{
					$row['usingrole'] = '<span style="color:red">' . $phrase['_no_role_assigned'] . '</span>';
				}
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$subscription_rows[] = $row;
				$row_count++;
			}
			$show['no_subscription_rows'] = false;
		}
		else
		{
			$show['no_subscription_rows'] = true;
		}
		    
		$resgroups = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "subscription_group");
		if ($ilance->db->num_rows($resgroups) > 0)
		{
			while ($row = $ilance->db->fetch_array($resgroups))
			{
				$sqlplans = $ilance->db->query("
					SELECT title
					FROM " . DB_PREFIX . "subscription
					WHERE subscriptiongroupid = '" . $row['subscriptiongroupid'] . "'
				");
				if ($ilance->db->num_rows($sqlplans) > 0)
				{
					while ($resplans = $ilance->db->fetch_array($sqlplans))
					{
						if (isset($row['plans_in_group']))
						{
							$row['plans_in_group'] .= $resplans['title'] . ", ";
						}
						else
						{
							$row['plans_in_group'] = $resplans['title'] . ", ";
						}
					}
					
					$row['plans_in_group'] = mb_substr($row['plans_in_group'], 0, -2);
					$noplans = 0;
				}
				else
				{
					$noplans = 1;
					$row['plans_in_group'] = '-';
				}
					    
				$row['subscriptiongroupid'] = $row['subscriptiongroupid'];
				$row['subscriptiongroupname'] = $ilance->db->fetch_field(DB_PREFIX . "subscription_group", "subscriptiongroupid=" . $row['subscriptiongroupid'], "title");
				$row['gtitle'] = stripslashes($row['title']);
				$row['gdescription'] = stripslashes($row['description']);
					    
				$sqlsetup = $ilance->db->query("
					SELECT * FROM " . DB_PREFIX . "subscription_permissions
					WHERE subscriptiongroupid = '" . $row['subscriptiongroupid'] . "'
				");
				if ($ilance->db->num_rows($sqlsetup) > 0)
				{
					$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-subscription-group&amp;id='.$row['subscriptiongroupid'].'"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
				}
				else
				{
					$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-subscription-group&amp;id='.$row['subscriptiongroupid'].'" onclick="return confirm_js(\'' . $phrase['_remember_you_must_click_permissions_on_the_next_page_and_then_scroll'] . '\');"><strong>' . $phrase['_set_up'] . '</strong></a>';
				}
					    
				if ($noplans)
				{
					if ($row['canremove'])
					{
						$row['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_remove-subscription-group&amp;id=' . $row['subscriptiongroupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action_continue'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
					}
					else
					{
						$row['remove'] = '-';
					}							    
					$row['move'] = '-';
				}
				else
				{
					$row['remove'] = '-';
					$row['move'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_migrate-subscription-group&amp;id='.$row['subscriptiongroupid'].'">' . $phrase['_migrate'] . '</a>';
				}
				$row['class'] = ($row_count % 2) ? 'alt1' : 'alt2';
				$subscription_group_rows[] = $row;
				$row_count++;
			}
			
			$show['no_subscription_group_rows'] = false;
		}
		else
		{
			$show['no_subscription_group_rows'] = true;
		}
		    
		if (isset($new_resource_item))
		{
			$new_resource_item .= "<tr>\n";
		}
		else
		{
			$new_resource_item = "<tr>\n";
		}
		
		$new_resource_item .= "<td class=\"" . $row['class'] . "\"><input type=\"text\" name=\"accesstext\" class=\"textfield\"></td>\n";
		$new_resource_item .= "<td class=\"" . $row['class'] . "\"><div align=\"center\" class=\"smaller\"><select name='accesstype'><option value='yesno'>yesno</option><option value='int'>int</option></select></div></td>\n";
		$new_resource_item .= "<td class=\"" . $row['class'] . "\"><div align=\"center\" class=\"smaller\"><input type=\"text\" name=\"accessname\" class=\"input\" size=\"3\" /></div></td>\n";
		$new_resource_item .= "<td class=\"alt1\" align=\"center\"><input type=\"text\" name=\"value\" class=\"input\" size=\"3\" \></td>\n";
		$new_resource_item .= "<td class=\"alt1\" align=\"center\"><input type='submit' name='newaccess' value='" . $phrase['_save'] . "'></td>\n";
		$new_resource_item .= "</tr>\n";
		
		$currency = print_left_currency_symbol();
		
		$sql_permgroups = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "subscription_group");
		$permission_group_pulldown = '<select name="subscriptiongroupid" style="font-family: verdana">';
		$permission_group_pulldown .= '<optgroup label="' . $phrase['_subscription_permission_resource'] . '">';
		while ($res_phrasegroups = $ilance->db->fetch_array($sql_permgroups))
		{
			$permission_group_pulldown .= '<option value="' . $res_phrasegroups['subscriptiongroupid'] . '"';
			$permission_group_pulldown .= '>' . stripslashes($res_phrasegroups['title']) . '</option>';
		}
		$permission_group_pulldown .= '</optgroup></select>';                
		    
		$migrate_plan_pulldown = $ilance->admincp->print_migrate_to_pulldown('');
		$migrate_billing_pulldown = $ilance->admincp->print_migrate_billing_pulldown('');
		
		// subscription roles logic
		$sqlroles = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "subscription_roles");
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			$row_count = 0;
			while ($resroles = $ilance->db->fetch_array($sqlroles))
			{
				$sqlplans = $ilance->db->query("
					SELECT title
					FROM " . DB_PREFIX . "subscription
					WHERE roleid = '" . $resroles['roleid'] . "'
				");
				if ($ilance->db->num_rows($sqlplans) > 0)
				{
					while ($resplans = $ilance->db->fetch_array($sqlplans))
					{
						if (isset($resroles['plans_in_role']))
						{
							$resroles['plans_in_role'] .= stripslashes($resplans['title']) . ", ";
						}
						else
						{
							$resroles['plans_in_role'] = stripslashes($resplans['title']) . ", ";
						}
					}
					$resroles['plans_in_role'] = mb_substr($resroles['plans_in_role'], 0, -2);
				}
				else
				{
					$noplans = 1;
					$resroles['plans_in_role'] = '<span class="gray"><em>' . $phrase['_please_assign_a_plan'] . '</em></span>';
				}
					    
				$resroles['rtitle'] = stripslashes($resroles['title']);
				$resroles['rpurpose'] = '<div class="smaller"><em><strong>Purpose</strong></em>: ' . stripslashes($resroles['purpose']) . '</div>';
				$resroles['active'] = ($resroles['active']) ? $phrase['_yes'] : $phrase['_no'];
				$resroles['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_edit-role&amp;id=' . $resroles['roleid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$resroles['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=subscriptions&amp;subcmd=_remove-role&amp;id=' . $resroles['roleid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action_continue'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$resroles['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$subscription_roles[] = $resroles;
				$row_count++;
			}		
		}
		else
		{
		    $show['no_subscription_roles'] = true;
		}
		    
		$roleselected = isset($roleid) ? intval($roleid) : '';
		$role_pulldown = print_role_pulldown($roleselected, 1, 1, 1);
		$roleusertypepulldown = $ilance->admincp->print_roleusertype_pulldown();
		$roletypepulldown = $ilance->admincp->print_roletype_pulldown();
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','roletypepulldown','roleusertypepulldown','role_pulldown','migrate_billing_pulldown','migrate_plan_pulldown','commission_group_pulldown','permission_group_pulldown','currency','new_resource_item','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_subscriptions_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'subscriptions.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','subscription_rows','subscription_group_rows','commission_rows','subscription_roles','subscription_report_rows'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

// #### MARKETPLACE HANDLER ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'marketplace')
{
	$area_title = $phrase['_marketplace_configuration'];
	$page_title = SITE_NAME . ' - ' . $phrase['_marketplace_configuration'];

	($apihook = $ilance->api('admincp_marketplace_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=marketplace', $_SESSION['ilancedata']['user']['slng']);
	
	$question_id_hidden = '';
	
	// #### ASSIGN BUDGET GROUP TO ALL CATEGORIES ##################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-budget' AND isset($ilance->GPC['title']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			WHERE cattype = 'service'
		");
		
		print_action_success($phrase['_all_categories_have_been_assigned_to_the_selected_bid_increment_group'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### ASSIGN SERVICE INSERTION GROUP TO ALL CATEGORIES #######
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-service-insertion' AND isset($ilance->GPC['title']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			WHERE cattype = 'service'
		");
		
		print_action_success($phrase['_all_categories_have_been_assigned_to_the_selected_bid_increment_group'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### ASSIGN PRODUCT INSERTION GROUP TO ALL CATEGORIES #######
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-product-insertion' AND isset($ilance->GPC['title']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			WHERE cattype = 'product'
		");
		
		print_action_success($phrase['_all_categories_have_been_assigned_to_the_selected_bid_increment_group'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### ASSIGN SERVICE FVF GROUP TO ALL CATEGORIES #############
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-service-finalvalue' AND isset($ilance->GPC['title']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			WHERE cattype = 'service'
		");
		
		print_action_success($phrase['_all_categories_have_been_assigned_to_the_selected_bid_increment_group'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### ASSIGN PRODUCT FVF GROUP TO ALL CATEGORIES #############
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-product-finalvalue' AND isset($ilance->GPC['title']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
			WHERE cattype = 'product'
		");
		
		print_action_success($phrase['_all_categories_have_been_assigned_to_the_selected_bid_increment_group'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### INSERT NEW SHIPPING PARTNER ############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-shipper')
	{
		$ilance->GPC['title'] = isset($ilance->GPC['title']) ? $ilance->GPC['title'] : '';
		$ilance->GPC['shipcode'] = isset($ilance->GPC['shipcode']) ? $ilance->GPC['shipcode'] : '';
		$ilance->GPC['domestic'] = isset($ilance->GPC['domestic']) ? intval($ilance->GPC['domestic']) : 0;
		$ilance->GPC['international'] = isset($ilance->GPC['international']) ? intval($ilance->GPC['international']) : 0;
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "shippers
			(shipperid, title, shipcode, domestic, international)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['shipcode']) . "',
			'" . $ilance->GPC['domestic'] . "',
			'" . $ilance->GPC['international'] . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE SHIPPING PARTNER ################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-shipper' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "shippers
			WHERE shipperid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### UPDATE SHIPPING PARTNERS ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippers')
	{
		foreach ($ilance->GPC['title'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET title = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET domestic = '0', international = '0'
		");
		
		foreach ($ilance->GPC['domestic'] AS $shipperid => $value)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET domestic = '" . intval($value) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['international'] AS $shipperid => $value)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET international = '" . intval($value) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}
		
		foreach ($ilance->GPC['shipcode'] AS $shipperid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "shippers
				SET shipcode = '" . $ilance->db->escape_string($title) . "'
				WHERE shipperid = '" . intval($shipperid) . "'
			");
		}

		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### UPDATE FINAL VALUE FEE SORTING #########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fv-sort')
	{
		if (!empty($ilance->GPC['sort']))
		{
			foreach ($ilance->GPC['sort'] AS $tierid => $sortvalue)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "finalvalue
					SET sort = '" . intval($sortvalue) . "'
					WHERE tierid = '" . intval($tierid) . "'
					LIMIT 1
				");
			}
			
			refresh($ilpage['settings'] . '?cmd=marketplace');
			exit();
		}
	}
	
	// #### UPDATE INSERTION FEE SORTING ###########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-sort')
	{
		if (!empty($ilance->GPC['sort']))
		{
			foreach ($ilance->GPC['sort'] AS $insertionid => $sortvalue)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "insertion_fees
					SET sort = '" . intval($sortvalue) . "'
					WHERE insertionid = '" . intval($insertionid) . "'
					LIMIT 1
				");
			}
			
			refresh($ilpage['settings'] . '?cmd=marketplace');
			exit();
		}
	}
	
	// #### UPDATE BUDGET SORTING ##################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-budget-sort')
	{
		if (!empty($ilance->GPC['sort']))
		{
			foreach ($ilance->GPC['sort'] AS $budgetid => $sortvalue)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "budget
					SET sort = '" . intval($sortvalue) . "'
					WHERE budgetid = '".intval($budgetid)."'
					LIMIT 1
				");
			}
			
			refresh($ilpage['settings'] . '?cmd=marketplace');
			exit();
		}
	}
		
	// #### ADD NEW BUDGET RANGE ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-budget-range' AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['budgetfrom']) AND isset($ilance->GPC['budgetto']) AND isset($ilance->GPC['groupid']))
	{
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
		$groupname = $ilance->db->fetch_field(DB_PREFIX . "budget_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
		$insgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['insertiongroupid']) . "'", "groupname");
		
		$ilance->GPC['fieldname'] = str_replace(' ', '_', $ilance->GPC['title']);
		$ilance->GPC['fieldname'] = mb_strtolower($ilance->GPC['fieldname']);
		$ilance->GPC['fieldname'] = $ilance->GPC['fieldname'] . '_' . rand(1, 99999);
		
		$ilance->admincp->insert_budget_range($ilance->GPC['title'], $ilance->GPC['fieldname'], $ilance->GPC['budgetfrom'], $ilance->GPC['budgetto'], $groupname, $insgroupname, $sort);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### ADD INSERTION FEE ######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-insertion' AND isset($ilance->GPC['insertion_from']) AND isset($ilance->GPC['insertion_to']) AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
	{
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
		$groupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
		
		$ilance->admincp->insert_insertion_fee($ilance->GPC['insertion_from'], $ilance->GPC['insertion_to'], $ilance->GPC['amount'], $groupname, $sort, $ilance->GPC['state']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### ADD FINAL VALUE FEE ####################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-fv' AND isset($ilance->GPC['finalvalue_from']) AND isset($ilance->GPC['finalvalue_to']))
	{
		$groupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
		
		$ilance->admincp->insert_fv_fee($ilance->GPC['finalvalue_from'], $ilance->GPC['finalvalue_to'], $ilance->GPC['amountfixed'], $ilance->GPC['amountpercent'], intval($ilance->GPC['sort']), $groupname, $ilance->GPC['state']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### REMOVE INSERTION FEE ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-insertion-fee' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->admincp->remove_insertion_fee($ilance->GPC['id']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE INSERTION GROUP #################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-insertion-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		// remove group, remove fees tied to group, update categories group to '0' to disable fees
		$ilance->admincp->remove_insertion_group($ilance->GPC['groupid']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE FINAL VALUE FEE #################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-fv-fee' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->admincp->remove_fv_fee($ilance->GPC['id']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE FINAL VALUE GROUP ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-fv-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		// remove group, remove fees tied to group, update categories group to '0' to disable fees
		$ilance->admincp->remove_fv_group($ilance->GPC['groupid']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}        

	// #### ADD PROFILE QUESTION ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-profile-question' AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['question']))
	{
		if ($ilance->GPC['verifycost'] != '0.00')
		{
			$ilance->GPC['verifycost'] = $ilance->GPC['verifycost'];
		}
		else
		{
		    $ilance->GPC['verifycost'] = '0.00';
		}
		if (!empty($ilance->GPC['sort']))
		{
			$ilance->GPC['sort'] = intval($ilance->GPC['sort']);
		}
		else
		{
			$ilance->GPC['sort'] = '10';
		}
		if (isset($ilance->GPC['profile_question_active']) AND $ilance->GPC['profile_question_active'])
		{
			$ilance->GPC['visible'] = '1';
		}
		else
		{
			$ilance->GPC['visible'] = '0';	    
		}
		if (isset($ilance->GPC['canverify']) AND $ilance->GPC['canverify'])
		{
			$ilance->GPC['canverify'] = '1';
		}
		else
		{
			$ilance->GPC['canverify'] = '0';
		}		
		if (isset($ilance->GPC['required']) AND $ilance->GPC['required'])
		{
			$ilance->GPC['required'] = '1';
		}
		else
		{
			$ilance->GPC['required'] = '0';	
		}
		if (isset($ilance->GPC['isfilter']) AND $ilance->GPC['isfilter'])
		{
			$ilance->GPC['isfilter'] = '1';
		}
		else
		{
			$ilance->GPC['isfilter'] = '0';	
		}
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "profile_questions
			(questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, verifycost, isfilter, filtertype, filtercategory)
			VALUES(
			NULL,
			'" . intval($ilance->GPC['groupid']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['question']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			'" . intval($ilance->GPC['sort']) . "',
			'" . intval($ilance->GPC['visible']) . "',
			'" . intval($ilance->GPC['required']) . "',
			'" . intval($ilance->GPC['canverify']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['verifycost']) . "',
			'" . intval($ilance->GPC['isfilter']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['filtertype']) . "',
			'" . intval($ilance->GPC['filtercategory']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
		
	// #### UPDATE PROFILE QUESTION HANDLER ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-profile-question' AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['question']) AND isset($ilance->GPC['description']))
	{
		if (isset($ilance->GPC['profile_question_active']) AND $ilance->GPC['profile_question_active'])
		{
			$ilance->GPC['visible'] = '1';
		}
		else
		{
			$ilance->GPC['visible'] = '0';    
		}
		if (isset($ilance->GPC['canverify']) AND $ilance->GPC['canverify'])
		{
			$ilance->GPC['canverify'] = '1';
		}
		else
		{
			$ilance->GPC['canverify'] = '0';
		}		
		if (isset($ilance->GPC['verifycost']) AND $ilance->GPC['verifycost'] > 0)
		{
			$ilance->GPC['verifycost'] = sprintf("%01.2f", $ilance->GPC['verifycost'], 'credit');
		}
		else
		{
			$ilance->GPC['verifycost'] = '0.00';
		}		
		if (isset($ilance->GPC['required']) AND $ilance->GPC['required'])
		{
			$ilance->GPC['required'] = '1';
		}
		else
		{
			$ilance->GPC['required'] = '0';
		}			
		if (!isset($ilance->GPC['multiplechoice']))
		{
			$ilance->GPC['multiplechoice'] = '';   
		}
		if (isset($ilance->GPC['isfilter']) AND $ilance->GPC['isfilter'])
		{
			$ilance->GPC['isfilter'] = '1';
		}
		else
		{
			$ilance->GPC['isfilter'] = '0';
		}
		if (!isset($ilance->GPC['filtertype']))
		{
			$ilance->GPC['filtertype'] = '';   
		}
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "profile_questions
			SET groupid = '" . intval($ilance->GPC['groupid']) . "',
			question = '" . $ilance->db->escape_string($ilance->GPC['question']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
			multiplechoice = '" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
			sort = '" . intval($ilance->GPC['sort']) . "',
			visible = '" . intval($ilance->GPC['visible']) . "',
			required = '" . intval($ilance->GPC['required']) . "',
			canverify = '" . intval($ilance->GPC['canverify']) . "',
			verifycost = '" . $ilance->db->escape_string($ilance->GPC['verifycost']) . "',
			isfilter = '" . intval($ilance->GPC['isfilter']) . "',
			filtertype = '" . $ilance->db->escape_string($ilance->GPC['filtertype']) . "',
			filtercategory = '" . $ilance->db->escape_string($ilance->GPC['filtercategory']) . "'
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
		
	// #### EDIT PROFILE QUESTIONS #################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question' AND $ilance->GPC['groupid'] > 0 AND $ilance->GPC['id'] > 0)
	{
		$id = intval($ilance->GPC['id']);
		$groupid = intval($ilance->GPC['groupid']);
		$question_id_hidden = '<input type="hidden" name="id" value="' . $id . '" />';
		
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "profile_questions
			WHERE groupid = '" . $groupid . "'
			    AND questionid = '" . $id . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			
			$question = stripslashes($res['question']);
			$question_description = stripslashes($res['description']);
			$inputtype = $res['inputtype'];
			$sort = $res['sort'];
			$visible = $res['visible'];
			$required = $res['required'];
			$canverify = $res['canverify'];
			$multiplechoiceprofile = $res['multiplechoice'];
			
			$isfilter = $res['isfilter'];
			$filtertype = $res['filtertype'];
			$filtercategory = intval($res['filtercategory']);
		}
		if ($sort == '0')
		{
			$sort = '10';
		}
		else
		{
			$sort = $res['sort'];
		}
		    
		$checked_profile_question_active = '';
		if ($visible)
		{
			$checked_profile_question_active = 'checked="checked"';
		}
		    
		$checked_profile_question_canverify = '';
		$verifycost = "0.00";	
		if ($canverify)
		{
			$checked_profile_question_canverify = 'checked="checked"';
			$verifycost = number_format($res['verifycost'], 2);
		}
		    
		$checked_profile_question_required = '';	
		if ($required)
		{
			$checked_profile_question_required = 'checked="checked"';
		}
		
		$checked_profile_question_isfilter = '';
		if ($isfilter)
		{
			$checked_profile_question_isfilter = 'checked="checked"';
		}
		
		$filter_type_pulldown = $ilance->admincp->print_profile_filtertype_pulldown($filtertype);
		
		$ilance->categories_pulldown = construct_object('api.categories_pulldown');
		$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
		$filter_category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($filtercategory, $cattype = 'service', $type = 'level', $fieldname = 'filtercategory', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
		
		$submit_profile_question = '<input type="submit" name="update" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" /> &nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">' . $phrase['_cancel'] . '</a></span>';
	}	
		
	// #### UPDATE PROFILE QUESTIONS SORTING #######################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-questions-sort' AND $ilance->GPC['groupid'] > 0)
	{
		foreach ($ilance->GPC['sort'] AS $key => $value)
		{
			if (!empty($key) AND !empty($value))
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "profile_questions
					SET sort = '" . $ilance->db->escape_string($value) . "'
					WHERE questionid = '" . $ilance->db->escape_string($key) . "'
					    AND groupid = '" . intval($ilance->GPC['groupid']) . "'
					LIMIT 1
				");    
			}
		}
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### UPDATE QUESTIONS SORT FOR A PROFILE GROUP OF QUESTIONS
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-register-questions-sort')
	{
		foreach ($ilance->GPC['sort'] AS $key => $value)
		{
			if (!empty($key) AND !empty($value))
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "register_questions
					SET sort = '" . intval($value) . "'
					WHERE questionid = '" . intval($key) . "'
					LIMIT 1
				");
			}
		}
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### UPDATE PROFILE GROUP ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-profile-group' AND isset($ilance->GPC['name']) AND isset($ilance->GPC['description']) AND $ilance->GPC['groupid'] > 0)
	{
		$visible = (isset($ilance->GPC['profile_group_active']) AND $ilance->GPC['profile_group_active']) ? '1' : '0';
				    
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "profile_groups
			SET name = '" . $ilance->db->escape_string($ilance->GPC['name']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			visible = '" . intval($visible) . "',
			cid = '" . intval($ilance->GPC['cid']) . "'
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### REMOVE PROFILE GROUP (REMOVES QUESTIONS ALSO) ##########
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-profile-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$notice = '';
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "profile_groups
			WHERE groupid = '".intval($ilance->GPC['groupid'])."'
		");
		
		$notice = $phrase['_the_action_requested_was_completed_successfully'];
	    
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "profile_questions
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql))
			{
				// remove answers to questions from within this group
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "profile_answers
					WHERE questionid = '" . $res['questionid'] . "'
					LIMIT 1
				");
			}
		}
		
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "profile_questions
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		");			
		
		print_action_success($notice, $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE PROFILE QUESTIONS ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-profile-question' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "profile_questions
			WHERE groupid = '".intval($ilance->GPC['groupid'])."'
			    AND questionid = '".intval($ilance->GPC['id'])."'
			LIMIT 1
		");
		
		// remove all profile answers for this profile question we're deleting
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "profile_answers
			WHERE questionid = '" . intval($ilance->GPC['id']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
		
	// #### ADD NEW PROFILE GROUP ##################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-profile-group' AND isset($ilance->GPC['name']) AND isset($ilance->GPC['description']))
	{
		$visible = '0';
		if (isset($ilance->GPC['profile_group_active']) AND $ilance->GPC['profile_group_active'])
		{
			$visible = '1';
		}
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "profile_groups
			(groupid, name, description, visible, cid)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['name']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			'" . $visible . "',
			'" . intval($ilance->GPC['cid']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
		
	// #### UPDATE INSERTION GROUP HANDLER #########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
	{
		$oldname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '".intval($ilance->GPC['groupid'])."'", "groupname");
		
		// update group table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "insertion_groups
			SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		
		// update fees table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "insertion_fees
			SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
			WHERE groupname = '" . $ilance->db->escape_string($oldname) . "'
			    AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
		");
		
		// update categories table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
			WHERE insertiongroup = '" . $ilance->db->escape_string($oldname) . "'
			    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// does admin create final value group?
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-fv-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['description']))
	{
		$ilance->admincp->insert_fv_group($ilance->GPC['groupname'], $ilance->GPC['state'], $ilance->GPC['description']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// does admin update final value group?
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fv-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
	{
		$oldname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '".intval($ilance->GPC['groupid'])."'", "groupname");
		    
		// update group table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "finalvalue_groups
			SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		
		// update fees table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "finalvalue
			SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
			WHERE groupname = '" . $ilance->db->escape_string($oldname) . "'
			    AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
		");
		
		// update categories table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
			WHERE finalvaluegroup = '" . $ilance->db->escape_string($oldname) . "'
			    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// does admin request updating of a final value group?
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-fv-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['state']))
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "finalvalue_groups
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
		
		$subcmdfv = '_update-fv-group';
		$hiddenfvgroupid = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
		$groupnamefv = stripslashes($res['groupname']);
		$groupnamefv2 = $groupnamefv;
		$descriptionfv = stripslashes($res['description']);
		$descriptionfv2 = $descriptionfv;
		$submitfv = '<input type="submit" name="submit" value="' . $phrase['_save'] . '" class="buttons" style="font-size:15px" />&nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">' . $phrase['_cancel'] . '</a></span>';
	}
	else 
	{
		$hiddenfvgroupid = $groupnamefv = $descriptionfv = '';
		$subcmdfv = '_create-fv-group';
		$submitfv = '<input type="submit" name="save" value="' . $phrase['_save'] . '" class="buttons" style="font-size:15px" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" />';
		$groupnamefv2 = $groupnamefv;
		$descriptionfv2 = $descriptionfv;
	}
	
	// does admin create insertion group?
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-insertion-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['description']))
	{
		$ilance->admincp->insert_insertion_group($ilance->GPC['groupname'], $ilance->GPC['state'], $ilance->GPC['description']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### CALLED WHEN ADMIN CLICKS EDIT INSERTION GROUP PENCIL ICON ######
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-insertion-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['state']))
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "insertion_groups
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
		
		$subcmdinsertion = '_update-insertion-group';
		$hiddengroupid = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
		$groupname = stripslashes($res['groupname']);
		$groupname2 = $groupname;
		$description = stripslashes($res['description']);
		$description2 = $description;
		$insertiongroupdescription = stripslashes($res['description']);
		$insertiongroupdescription2 = $insertiongroupdescription;
		$submitinsertion = '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" /> &nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">' . $phrase['_cancel'] . '</a></span>';
	}
	else 
	{
		$hiddengroupid = $groupname = $description = $insertiongroupdescription = '';
		$subcmdinsertion = '_create-insertion-group';
		$submitinsertion = '<input type="submit" style="font-size:15px" value="' . $phrase['_save'] . '" class="buttons" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" />';
		$groupname2 = $groupname;
		$description2 = $description;
		$insertiongroupdescription2 = $insertiongroupdescription;
	}
	
	// #### UPDATE INSERTION GROUP HANDLER #################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
	{
		$oldname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
		
		// update group table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "insertion_groups
			SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		
		// update fees table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "insertion_fees
			SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
			WHERE groupname = '" . $ilance->db->escape_string($oldname) . "'
			    AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
		");
		
		// update categories table
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
			WHERE insertiongroup = '" . $ilance->db->escape_string($oldname) . "'
			    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// admin requestion edit/update mode?
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$name = $description = $checked_profile_group_active = '';
		$groupid = intval($ilance->GPC['groupid']);
		$hiddengroupid = '<input type="hidden" name="groupid" value="' . $groupid . '">';
		$submit = '<input type="submit" name="update" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')">&nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">' . $phrase['_cancel'] . '</a></span>';
		$subcmd = '_update-profile-group';
					
		$sqlupdate = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "profile_groups
			WHERE groupid = '" . $groupid . "'
		");
		if ($ilance->db->num_rows($sqlupdate) > 0)
		{
			$resupdate = $ilance->db->fetch_array($sqlupdate);
			$name = stripslashes($resupdate['name']);
			$description = stripslashes($resupdate['description']);
			$ilance->categories_pulldown = construct_object('api.categories_pulldown');
			$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
			$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($resupdate['cid'], $cattype = 'service', $type = 'level', $fieldname = 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
			$checked_profile_group_active = '';
			if ($resupdate['visible'])
			{
				$checked_profile_group_active = 'checked="checked"';
			}
		}
	}
	else
	{
		$name = $description = '';
		$subcmd = '_create-profile-group';
		$submit = '<input type="submit" name="save" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" />';
		$checked_profile_group_active = 'checked';
	}
		
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question')
	{
		$question_subcmd = '_update-profile-question';
	}
	else
	{
		$question_subcmd = '_add-profile-question';
		$submit_profile_question = '<input type="submit" name="update" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" />&nbsp;';
		
		$filter_type_pulldown = $ilance->admincp->print_profile_filtertype_pulldown('');
		
		$ilance->categories_pulldown = construct_object('api.categories_pulldown');
		$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
		$filter_category_pulldown = $ilance->categories_pulldown->print_cat_pulldown(0, $cattype = 'service', $type = 'level', $fieldname = 'filtercategory', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
	}
	    
	// requesting normal mode or edit/update mode?
	$profile_inputtype_pulldown = '<select name="inputtype" style="font-family: verdana">';
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question')
	{
		$profile_inputtype_pulldown .= '<option value="yesno"'; if ($res['inputtype'] == "yesno") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="int"'; if ($res['inputtype'] == "int") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>' . $phrase['_integer_field_numbers_only'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="textarea"'; if ($res['inputtype'] == "textarea") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>' . $phrase['_textarea_field_multiline'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="text"'; if ($res['inputtype'] == "text") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>' . $phrase['_input_text_field_singleline'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="multiplechoice"'; if ($res['inputtype'] == "multiplechoice") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="pulldown"'; if ($res['inputtype'] == "pulldown") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
	}
	else
	{
		$profile_inputtype_pulldown .= '<option value="yesno">' . $phrase['_radio_selection_box_yes_or_no_type_question'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="int">' . $phrase['_integer_field_numbers_only'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="textarea">' . $phrase['_textarea_field_multiline'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="text">' . $phrase['_input_text_field_singleline'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="multiplechoice">' . $phrase['_multiple_choice_enter_values_below'] . '</option>';
		$profile_inputtype_pulldown .= '<option value="pulldown">' . $phrase['_pulldown_menu_enter_values_below'] . '</option>';
	}
	$profile_inputtype_pulldown .= '</select>';
		
	// #### UPDATE FINAL VALUE FEE HANDLER #############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fv-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['tierid']) AND $ilance->GPC['tierid'] > 0)
	{
		$ilance->admincp->update_fv_fee($ilance->GPC['finalvalue_from'], $ilance->GPC['finalvalue_to'], $ilance->GPC['amountfixed'], $ilance->GPC['amountpercent'], $ilance->GPC['groupid'], $ilance->GPC['tierid'], $ilance->GPC['sort']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	    
	// #### UPDATE INSERTION FEE HANDLER ###################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['insertionid']) AND $ilance->GPC['insertionid'] > 0)
	{
		$ilance->admincp->update_insertion_fee($ilance->GPC['insertion_from'], $ilance->GPC['insertion_to'], $ilance->GPC['amount'], $ilance->GPC['groupid'], $ilance->GPC['insertionid'], $ilance->GPC['sort']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	    
	// #### CALLED WHEN ADMIN CLICKS PENCIL ICON TO EDIT INSERTION FEE RANGE
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-insertion-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['state']))
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "insertion_fees
			WHERE insertionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
    
		// product
		$insamount = $res['amount'];
		$insfrom = $res['insertion_from'];
		$insto = $res['insertion_to'];
		$inssort = $res['sort'];
		$insform = '_update-insertion-fee';
		$inshidden = '<input type="hidden" name="insertionid" value="' . $res['insertionid'] . '" />';
		$inssubmit = '<input type="submit" style="font-size:15px" value="' . $phrase['_save'] . '" class="buttons" /> &nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">' . $phrase['_cancel'] . '</a></span>';
		
		// service
		$insamount2 = $insamount;
		$insfrom2 = $insfrom;
		$insto2 = $insto;
		$insform2 = $insform;
		$inshidden2 = $inshidden;
		$inssubmit2 = $inssubmit;
		$inssort2 = $inssort;
	}
	else 
	{
		// product
		$insamount = '0.00';
		$insfrom = '0.00';
		$insto = '0.00';
		$insform = 'insert-insertion';
		$inssort = '0';
		$inshidden = '';
		$inssubmit = '<input type="submit" value="' . $phrase['_save'] . '" class="buttons" style="font-size:15px" />';
		
		$insamount2 = $insamount;
		$insfrom2 = $insfrom;
		$insto2 = $insto;
		$insform2 = $insform;
		$inshidden2 = $inshidden;
		$inssubmit2 = $inssubmit;
		$inssort2 = $inssort;
	}
	    
	// #### UPDATE BUDGET FEE RANGE HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-budget-range' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['budgetfrom']) AND isset($ilance->GPC['budgetto']) AND isset($ilance->GPC['budgetid']))
	{
		$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
		
		$ilance->admincp->update_budget_range($ilance->GPC['budgetid'], $ilance->GPC['title'], $ilance->GPC['budgetfrom'], $ilance->GPC['budgetto'], $ilance->GPC['groupid'], $ilance->GPC['insertiongroupid'], $sort);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### INSERT BUDGET FEE GROUP HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-budget-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
	{
		$ilance->admincp->insert_budget_group($ilance->GPC['groupname'], $ilance->GPC['description']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE BUDGET FEE GROUP HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-budget-group' AND isset($ilance->GPC['groupid']))
	{
		$ilance->admincp->remove_budget_group($ilance->GPC['groupid']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### REMOVE BUDGET FEE RANGE HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-budget' AND isset($ilance->GPC['id']))
	{
		$ilance->admincp->remove_budget_range($ilance->GPC['id']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### UPDATE BUDGET FEE GROUP HANDLER ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-budget-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
	{
		$ilance->admincp->update_budget_group($ilance->GPC['groupid'], $ilance->GPC['groupname'], $ilance->GPC['description']);
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
	
	// #### CALLED WHEN ADMIN CLICKS EDIT BUDGET FEE GROUP PENCIL ICON #####
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-budget-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "budget_groups
			WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
	    
		$budgetgroupname = $res['groupname'];
		$budgetgroupdescription = $res['description'];
		$submitbudget = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
		$subcmdbudgetgroup = '_update-budget-group';
		$hiddenbudgetgroupid2 = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
	}
	else 
	{
		$budgetgroupname = $budgetgroupdescription = $hiddenbudgetgroupid2 = '';
		$submitbudget = '<input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" />';
		$subcmdbudgetgroup = 'insert-budget-group';
	}
	
	// #### CALLED WHEN ADMIN CLICKS EDIT BUDGET FEE RANGE PENCIL ICON #####
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-budget' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "budget
			WHERE budgetid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
		
		$ilance->GPC['insertiongroupid'] = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupname = '" . $ilance->db->escape_string($res['insertiongroup']) . "'", "groupid");
		
		$title = $res['title'];
		$budgetfieldname = $res['fieldname'];
		$budgetfrom = $res['budgetfrom'];
		if ($res['budgetto'] == '-1.00')
		{
			$res['budgetto'] = '-1';
		}
		$budgetto = $res['budgetto'];
		$budgetsort = $res['sort'];
		$budgetsubmit = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
		$hiddenbudgetgroupid = '<input type="hidden" name="budgetid" value="' . $res['budgetid'] . '" />';
		$budgetsubcmd = '_update-budget-range';
		$budgethidden = '<input type="hidden" name="budgetid" value="' . $res['budgetid'] . '" />';
	}
	else 
	{
		$title = $budgetfieldname = $budgetfrom = $budgetto = $budgethidden = $hiddenbudgetgroupid = '';
		$budgetsort = 0;
		$budgetsubmit = '<input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" />';
		$budgetsubcmd = 'insert-budget-range';
	}
	
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-fv-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['state']))
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "finalvalue
			WHERE tierid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql);
    
		// product
		$fvamountfixed = $res['amountfixed'];
		$fvamountpercent = $res['amountpercent'];
		$fvfrom = $res['finalvalue_from'];
		$fvto = $res['finalvalue_to'];
		$fvsort = $res['sort'];
		$fvsubcmd = '_update-fv-fee';
		$fvhidden = '<input type="hidden" name="tierid" value="' . $res['tierid'] . '" />';
		$fvsubmit = '<input type="submit" name="submit" value=" ' . $phrase['_save'] . ' " class="buttons" style="font-size:15px" />&nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">' . $phrase['_cancel'] . '</a></span>';
		
		// service
		$fvamountfixed2 = $fvamountfixed;
		$fvamountpercent2 = $fvamountpercent;
		$fvfrom2 = $fvfrom;
		$fvto2 = $fvto;
		$fvsort2 = $fvsort;
		$fvsubcmd2 = $fvsubcmd;
		$fvhidden2 = $fvhidden;
		$fvsubmit2 = $fvsubmit;
	}
	else 
	{
		// product
		$fvamountfixed = '0';
		$fvamountpercent = '0.0';
		$fvfrom = $fvto = $fvhidden = '';
		$fvsort = '10';
		$fvsubcmd = 'insert-fv';
		$fvsubmit = '<input type="submit" value=" ' . $phrase['_save'] . ' " class="buttons" style="font-size:15px" />';
		
		// service
		$fvamountfixed2 = $fvamountfixed;
		$fvamountpercent2 = $fvamountpercent;
		$fvfrom2 = $fvfrom;
		$fvto2 = $fvto;
		$fvsort2 = $fvsort;
		$fvsubcmd2 = $fvsubcmd;
		$fvhidden2 = $fvhidden;
		$fvsubmit2 = $fvsubmit;
	}
    
	// #### SETTINGS MENU ##################################################
	
	$ilance->categories_pulldown = construct_object('api.categories_pulldown');
	
	$sqlprofilegroupz = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_groups
	");
	if ($ilance->db->num_rows($sqlprofilegroupz) > 0)
	{
		$row_count = 0;
		while ($rowz = $ilance->db->fetch_array($sqlprofilegroupz))
		{
			// question count
			$qcount = $ilance->db->query("
				SELECT COUNT(*) AS questions 
				FROM " . DB_PREFIX . "profile_questions
				WHERE groupid = '" . $rowz['groupid'] . "'
			");
			if ($ilance->db->num_rows($qcount) > 0)
			{
				$rescount = $ilance->db->fetch_array($qcount);
				$rowz['questions'] = $rescount['questions'];
			}
			else
			{
				$rowz['questions'] = '0';
			}
				
			if ($rowz['canremove'] == 0)
			{
				$rowz['category'] = $phrase['_all_categories'];
				$rowz['remove_group'] = '-';
				$rowz['edit'] = '-';
			}
			else
			{
				$rowz['category'] = ($rowz['cid'] <= 0) ? $phrase['_all_categories'] : $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $rowz['cid']);
				$rowz['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-profile-group&amp;groupid=' . $rowz['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rowz['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-profile-group&amp;groupid=' . $rowz['groupid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			}
			
			$rowz['active'] = ($rowz['visible']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			
			$rowz['groupname'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '".$rowz['groupid']."'", "name"));
			$rowz['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$profile_groups[] = $rowz;
			$row_count++;
		}
	}
	else
	{
		$show['no_profile_groups'] = true;
	}
	    
	$sqlprofilegroups = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_groups
	");
	if ($ilance->db->num_rows($sqlprofilegroups) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sqlprofilegroups))
		{
			$qcount = $ilance->db->query("
				SELECT COUNT(*) AS questions
				FROM " . DB_PREFIX . "profile_questions
				WHERE groupid = '" . $row['groupid'] . "'
			");
			if ($ilance->db->num_rows($qcount) > 0)
			{
				$rescount = $ilance->db->fetch_array($qcount);
				$row['questions'] = $rescount['questions'];
			}
			else
			{
				$row['questions'] = '0';
			}
		
			if ($row['visible'])
			{
				$row['active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
			}
			else
			{
				$row['active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			}
					
			//$row['category'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $row['cid']);
			$row['category'] = ($row['cid'] <= 0) ? $phrase['_all_categories'] : $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $row['cid']);
					
			$sqlquestions = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions 
				WHERE groupid = '" . $row['groupid'] . "'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlquestions) > 0)
			{
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlquestions))
				{
					$rows['question_description'] = stripslashes($rows['description']);
					$rows['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-profile-question&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['questionid'] . '#profilequestion"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
					$rows['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-profile-question&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['questionid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
					
					if ($rows['visible'])
					{
						$rows['question_active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
					}
					else
					{
						$rows['question_active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
					}
								
					if ($rows['required'])
					{
						$rows['isrequired'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
					}
					else
					{
						$rows['isrequired'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
					}
								
					if ($rows['canverify'])
					{
						$rows['canverify'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
						$rows['cost'] = $ilance->currency->format($rows['verifycost']);
					}
					else
					{
						$rows['canverify'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
						$rows['cost'] = '-';
					}
					
					if ($rows['isfilter'])
					{
						$rows['isfilter'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';	
					}
					else
					{
						$rows['isfilter'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
					}
								
					$rows['inputtype'] = mb_strtolower($rows['inputtype']);
					$rows['sortinput'] = '<input type="text" name="sort[' . $rows['questionid'] . ']" value="' . $rows['sort'] . '" style="text-align:center" class="input" size="3" />';
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$GLOBALS['profile_questions' . $row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS['no_profile_questions' . $row['groupid']][] = 1;	
			}
					
			$row['groupname'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid=" . $row['groupid'], "name"));
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$profile_question_groups[] = $row;
			$row_count++;
		}
	}
		
	// fetch product insertion fee groups
	$sqlinsertionproductgroups = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "insertion_groups
		WHERE state = 'product'
	");
	if ($ilance->db->num_rows($sqlinsertionproductgroups) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sqlinsertionproductgroups))
		{
			// fetch insertion fees in this group
			$sqlproductfees = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "insertion_fees
				WHERE groupname = '" . $row['groupname'] . "'
					AND state = 'product'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlproductfees) > 0)
			{		
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlproductfees))
				{
					$rows['from'] = $ilance->currency->format($rows['insertion_from']);
					if ($rows['insertion_to'] != '-1')
					{
						$rows['to'] = $ilance->currency->format($rows['insertion_to']);
					}
					else 
					{
						$rows['to'] = $phrase['_or_more'];
					}                                
					$rows['amount'] = $ilance->currency->format($rows['amount']);
					$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '&amp;state=product#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
					$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$GLOBALS["productinsertionfees".$row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS["no_productinsertionfees".$row['groupid']][] = 1;	
			}
					
			$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
			$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
			$row['groupcount'] = $ilance->admincp->fetch_insertion_catcount('product', $row['groupname']);
			$row['groupnameplain'] = $row['groupname'];
			$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup">' . $row['groupname'] . '</a>';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$productinsertion_groups[] = $row;
			$productinsertion1_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_productinsertion1_groups'] = true;
	}
		
	// #### SERVICE INSERTION GROUPS #######################################
	// fetch service insertion fee groups
	$sqlinsertionservicegroups = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "insertion_groups
		WHERE state = 'service'
	");
	if ($ilance->db->num_rows($sqlinsertionservicegroups) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sqlinsertionservicegroups))
		{
			// fetch insertion fees in this group
			$sqlservicefees = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "insertion_fees
				WHERE groupname = '" . $row['groupname'] . "'
					AND state = 'service'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlservicefees) > 0)
			{
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlservicefees))
				{
					$rows['from'] = $ilance->currency->format($rows['insertion_from']);
					if ($rows['insertion_to'] != '-1')
					{
						$rows['to'] = $ilance->currency->format($rows['insertion_to']);
					}
					else 
					{
						$rows['to'] = $phrase['_or_more'];
					}
						
					$rows['amount'] = $ilance->currency->format($rows['amount']);
					$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '&amp;state=service#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
					$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$GLOBALS["serviceinsertionfees".$row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS["no_serviceinsertionfees".$row['groupid']][] = 1;	
			}                                
			$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
			$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
			$row['groupcount'] = $ilance->admincp->fetch_insertion_catcount('service', $row['groupname']);
			$row['budgetgroupcount'] = $ilance->admincp->fetch_insertion_budget_catcount($row['groupname']);
			$row['groupnameplain'] = $row['groupname'];
			$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup">' . $row['groupname'] . '</a>';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$serviceinsertion_groups[] = $row;
			$serviceinsertion1_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_serviceinsertion1_groups'] = true;
	}
	    
	// #### PRODUCT FINAL VALUE GROUPS #####################################
	// fetch product final value fee groups
	$sqlfinalproductgroups = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "finalvalue_groups
		WHERE state = 'product'
	");
	if ($ilance->db->num_rows($sqlfinalproductgroups) > 0)
	{
		$row_count = 0;
		$tier = 0;
		while ($row = $ilance->db->fetch_array($sqlfinalproductgroups))
		{
			// fetch final value fees in this group
			$sqlproductfees = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "finalvalue
				WHERE groupname = '" . $row['groupname'] . "'
				    AND state = 'product'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlproductfees) > 0)
			{
			    $row_count2 = 0;
			    while ($rows = $ilance->db->fetch_array($sqlproductfees))
			    {
				$tier++;
				$rows['from'] = $ilance->currency->format($rows['finalvalue_from']);
				if ($rows['finalvalue_to'] != '-1')
				{
					$rows['to'] = $ilance->currency->format($rows['finalvalue_to']);
				}
				else 
				{
					$rows['to'] = $phrase['_or_more'];
				}
				if ($rows['amountfixed'] > 0)
				{
					$rows['amountfixed'] = $ilance->currency->format($rows['amountfixed']);
				}
				else
				{
					$rows['amountfixed'] = '-';
				}
				
				if ($rows['amountpercent'] > 0)
				{
					$rows['amountpercent'] = $rows['amountpercent'] . '%';
				}
				else
				{
					$rows['amountpercent'] = '-';
				}
				
				$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'&amp;state=product#productfvf"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$rows['tier'] = $tier;
				
				$GLOBALS['productfvfees' . $row['groupid']][] = $rows;
				$row_count2++;
			    }
			}
			else
			{
				$GLOBALS['no_productfvfees' . $row['groupid']][] = 1;	
			}
			
			$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$row['groupcount'] = $ilance->admincp->fetch_fv_catcount('product', $row['groupname']);
			$row['groupnameplain'] = $row['groupname'];
			$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup">' . $row['groupname'] . '</a>';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$productfv_groups[] = $row;
			$productfv1_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_productfv_groups'] = true;
	}
		
	// #### SERVICE FINAL VALUE GROUPS #####################################
	// fetch service final value fee groups
	$sqlfinalservicegroups = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "finalvalue_groups
		WHERE state = 'service'
	");
	if ($ilance->db->num_rows($sqlfinalservicegroups) > 0)
	{
		$row_count = 0;
		$tier = 0;
		while ($row = $ilance->db->fetch_array($sqlfinalservicegroups))
		{
			// fetch final value fees in this group
			$sqlservicefees = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "finalvalue
				WHERE groupname = '" . $row['groupname'] . "'
					AND state = 'service'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlservicefees) > 0)
			{
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlservicefees))
				{
					$tier++;
					$rows['from'] = $ilance->currency->format($rows['finalvalue_from']);
					if ($rows['finalvalue_to'] != '-1')
					{
						$rows['to'] = $ilance->currency->format($rows['finalvalue_to']);
					}
					else 
					{
						$rows['to'] = $phrase['_or_more'];
					}
						
					if ($rows['amountfixed'] > 0)
					{
						$rows['amountfixed'] = $ilance->currency->format($rows['amountfixed']);
					}
					else
					{
						$rows['amountfixed'] = '-';        
					}
					
					if ($rows['amountpercent'] > 0)
					{
						$rows['amountpercent'] = $rows['amountpercent'] . '%';
					}
					else
					{
						$rows['amountpercent'] = '-';        
					}
					
					$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'&amp;state=service#servicefvf"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
					$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$rows['tier'] = $tier;
					$GLOBALS['servicefvfees' . $row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS["no_servicefvfees".$row['groupid']][] = 1;	
			}
			
			$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$row['groupcount'] = $ilance->admincp->fetch_fv_catcount('service', $row['groupname']);
			$row['groupnameplain'] = $row['groupname'];
			$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup">' . $row['groupname'] . '</a>';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$servicefv_groups[] = $row;
			$servicefv1_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_servicefv_groups'] = true;
		$no_servicefv1_groups = 1;
	}
		
	#####################################################
	## SERVICE BUDGET GROUPS ############################
	#####################################################
	// fetch service insertion fee groups
	$sqlbudgetgroups = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "budget_groups");
	if ($ilance->db->num_rows($sqlbudgetgroups) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sqlbudgetgroups))
		{
			// fetch budget values in this group
			$sqlfees = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "budget
				WHERE budgetgroup = '" . $row['groupname' ]. "'
				ORDER BY sort ASC
			");
			if ($ilance->db->num_rows($sqlfees) > 0)
			{
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlfees))
				{
					$rows['from'] = $ilance->currency->format($rows['budgetfrom']);
					if ($rows['budgetto'] != '-1')
					{
						$rows['to'] = $ilance->currency->format($rows['budgetto']);
					}
					else 
					{
						$rows['to'] = $phrase['_or_more'];
					}
						    
					$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-budget&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['budgetid'].'#editbudgetgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
					$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-budget&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['budgetid'].'" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$rows['insgroup'] = (isset($rows['insertiongroup']) AND !empty($rows['insertiongroup'])) ? $rows['insertiongroup'] : '-';
					
					$GLOBALS['budgetfees' . $row['groupid']][] = $rows;
					
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS["no_budgetfees" . $row['groupid']][] = 1;
			}
			
			$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-budget-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-budget-group&amp;groupid=' . $row['groupid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$row['groupcount'] = $ilance->admincp->fetch_budget_catcount($row['groupname']);
			$row['groupnameplain'] = $row['groupname'];
			$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-budget-group&amp;groupid=' . $row['groupid'] . '#editgroup">' . $row['groupname'] . '</a>';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$budget_groups[] = $row;
			$budget1_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$no_budget_groups = 1;
		$show['no_budget1_groups'] = true;
	}
	
	// create insertion group pulldown for budget range updating
	$igroup = isset($ilance->GPC['insertiongroupid']) ? intval($ilance->GPC['insertiongroupid']) : '-1';
	$insertiongrouppulldown = $ilance->admincp->print_insertion_group_pulldown($igroup, 1, 'service', 'insertiongroupid');
	    
	// profile groups
	$sqlgroupquestions = $ilance->db->query("
		SELECT groupid, name, description, visible, canremove, cid
		FROM " . DB_PREFIX . "profile_groups
	");
	if ($ilance->db->num_rows($sqlgroupquestions) > 0)
	{
		$profile_group_pulldown = '<select name="groupid" style="font-family: Verdana">';
		while ($res = $ilance->db->fetch_array($sqlgroupquestions))
		{
			if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question')
			{
				$profile_group_pulldown .= '<option value="' . $res['groupid'] . '"';
				if ($res['groupid'] == $groupid)
				{
					$profile_group_pulldown .= ' selected="selected"';
				}
			}
			else
			{
				$profile_group_pulldown .= '<option value="' . $res['groupid'] . '"';
			}                            
			$profile_group_pulldown .= '>' . stripslashes($res['name']) . '</option>';
		}
		$profile_group_pulldown .= '</select>';
	}
	else
	{
		$profile_group_pulldown = $phrase['_no_results_found'];
	}
	    
	// profile questions category pulldown
	if (empty($category_pulldown))
	{
		$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
		$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown(0, $cattype = 'service', $type = 'level', $fieldname = 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
	}
    
	// #### SHIPPING PARTNERS ######################################
	$show['no_shippers_rows'] = true;
	$sql = $ilance->db->query("
		SELECT shipperid, title, shipcode, domestic, international, carrier
		FROM " . DB_PREFIX . "shippers
		ORDER BY shipperid ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['no_shippers_rows'] = false;
		
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$row['class'] = ($row['international']) ? 'alt1' : 'alt1';				
			$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=remove-shipper&amp;id=' . $row['shipperid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['title'] = '<input type="text" name="title[' . $row['shipperid'] . ']" value="' . stripslashes($row['title']) . '" class="input" style="width:420px" />';
			$row['shipcode'] = '<input type="text" name="shipcode[' . $row['shipperid'] . ']" value="' . stripslashes($row['shipcode']) . '" class="input" style="width:60px; text-align:center" />';
			$row['domestic'] = '<input type="checkbox" name="domestic[' . $row['shipperid'] . ']" value="1" ' . ($row['domestic'] ? 'checked="checked"' : '') . ' />';
			$row['international'] = '<input type="checkbox" name="international[' . $row['shipperid'] . ']" value="1" ' . ($row['international'] ? 'checked="checked"' : '') . ' />';
			
			$row['carrier'] = '<input type="text" name="carrier[' . $row['shipperid'] . ']" value="' . stripslashes($row['carrier']) . '" class="input" style="width:60px" />';
			
			$shippers[] = $row;
			$row_count++;
		}
	}
	
	// #### insertion group pulldowns ##############################
	$igroup = isset($ilance->GPC['groupid']) ? intval($ilance->GPC['groupid']) : '';
	$insertiongroupservicepulldown = $ilance->admincp->print_insertion_group_pulldown($igroup, 1, 'service');
	$insertiongroupproductpulldown = $ilance->admincp->print_insertion_group_pulldown($igroup, 1, 'product');
	
	// #### final value group pulldowns ############################
	$finalvaluegroupservicepulldown = $ilance->admincp->print_fv_group_pulldown($igroup, 1, 'service');
	$finalvaluegroupproductpulldown = $ilance->admincp->print_fv_group_pulldown($igroup, 1, 'product');
	
	// #### budget group pulldowns #################################
	$budgetgrouppulldown = $ilance->admincp->print_budget_group_pulldown($igroup, 1);
	
	// #### tabs ###################################################
	$configuration_servicerating = $ilance->admincp->construct_admin_input('servicerating', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_servicebid = $ilance->admincp->construct_admin_input('servicebid', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_serviceupsell = $ilance->admincp->construct_admin_input('serviceupsell', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_productupsell = $ilance->admincp->construct_admin_input('productupsell', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_productaward = $ilance->admincp->construct_admin_input('productaward', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_portfoliodisplay = $ilance->admincp->construct_admin_input('portfoliodisplay', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_portfolioupsell = $ilance->admincp->construct_admin_input('portfolioupsell', $ilpage['settings'] . '?cmd=marketplace');
	$configuration_shippingsettings = $ilance->admincp->construct_admin_input('shippingsettings', $ilpage['settings'] . '?cmd=marketplace');
	
	$pprint_array = array('configuration_shippingsettings','buildversion','ilanceversion','login_include_admin','budgetfieldname','filter_type_pulldown','checked_profile_question_isfilter','filtercategory','filtertype','filter_category_pulldown','multiplechoiceprofile','configuration_servicebid','checked_question_cansearch','multiplechoice','insertiongrouppulldown','hiddenbudgetgroupid','hiddenbudgetgroupid2','subcmdbudgetgroup','submitbudget','budgetgroupdescription','budgetgroupname','budgetsubcmd','budgethidden','subcmdbudget','subcmdbudgetgroup','budgetsort','inssort','inssort2','insertiongroupdescription','insertiongroupdescription2','fvsort','fvsort2','hiddenbudgetgroupid','budgetsubmit','title','fieldname','budgetfrom','budgetto','budgetgrouppulldown','descriptionfv','descriptionfv2','fvsubcmd','fvhidden','fvfrom','fvto','fvamountfixed','fvamountpercent','finalvaluegroupproductpulldown','finalvaluegroupservicepulldown','fvsubmit','subcmdfv','hiddenfvgroupid','groupnamefv','groupnamefv2','submitfv','groupname2','description','description2','inssubmit2','inshidden2','insform2','insto2','insamount2','insfrom2','subcmdinsertion','submitinsertion','insertiongroupservicepulldown','insertiongroupproductpulldown','groupname','inssubmit','inshidden','insfrom','insto','insamount','insform','insertionid','taxsubmit','tax_subcmd','tax_id_hidden','invoicetypetax','state','city','amount','taxlabel','countryname','regformname','regformdefault','regprofile_inputtype_pulldown','regsubmit_profile_question','regchecked_guests','regchecked_public','regchecked_required','regchecked_visible','regsort','regquestion_description','regquestion','regquestion_id_hidden','regquestion_subcmd','register_page_pulldown','configuration_serviceaward','category_pulldown','checked_profile_question_required','subcatname','catname','service_subcategories','product_categories','configuration_moderationsystem','configuration_invoicesystem','configuration_escrowsystem','configuration_attachmentlimits','configuration_attachmentmoderation','configuration_attachmentsettings','configuration_referalsystem','configuration_registrationdisplay','configuration_registrationupsell','configuration_portfoliodisplay','configuration_portfolioupsell','configuration_productaward','configuration_productupsell','configuration_serviceupsell','configuration_servicerating','checked_spell_check_enabled_true','checked_spell_check_enabled_false','service_fee_highlite','service_fee_highlite_color','checked_service_fee_highlite_active_true','checked_service_fee_highlite_active_false','service_spell_check_pulldown','service_quality','service_delivery','service_professionalism','service_responsiveness','service_price','product_quality','product_delivery','product_professionalism','product_responsiveness','product_price','verifycost','question_id_hidden','question_subcmd','question_description','submit_profile_question','checked_profile_question_canverify','canverify','checked_profile_question_active','question','sort','hiddengroupid','checked_profile_group_active','profile_inputtype_pulldown','profile_group_pulldown','subcmd','id','submit','description','name','checked_profile_group_active','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_marketplace_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'marketplace.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','register_questions','increments','profile_groups','verify_questions','registerlanguages','profile_question_groups'));
	if (!isset($profile_question_groups))
	{
		$profile_question_groups = array();
	}		
	@reset($profile_question_groups);
	while ($i = @each($profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'profile_questions' . $i['value']['groupid']);
	}
	
	// #### product insertion fees / groups ########################
	$ilance->template->parse_loop('main', array('productinsertion1_groups','productinsertion_groups'));
	if (!isset($productinsertion_groups))
	{
		$productinsertion_groups = array();
	}
	@reset($productinsertion_groups);
	while ($i = @each($productinsertion_groups))
	{
		$ilance->template->parse_loop('main', 'productinsertionfees' . $i['value']['groupid']);
	}
	
	// #### service insertion fees / groups ########################
	$ilance->template->parse_loop('main', array('serviceinsertion1_groups','serviceinsertion_groups'));
	if (!isset($serviceinsertion_groups))
	{
		$serviceinsertion_groups = array();
	}
	@reset($serviceinsertion_groups);
	while ($i = @each($serviceinsertion_groups))
	{
		$ilance->template->parse_loop('main', 'serviceinsertionfees' . $i['value']['groupid']);
	}
	
	// #### product final value fees / groups ######################
	$ilance->template->parse_loop('main', array('productfv1_groups','productfv_groups'));
	if (!isset($productfv_groups))
	{
		$productfv_groups = array();
	}
	@reset($productfv_groups);
	while ($i = @each($productfv_groups))
	{
		$ilance->template->parse_loop('main', 'productfvfees' . $i['value']['groupid']);
	}
	
	// #### service final value fees / groups ######################
	$ilance->template->parse_loop('main', array('servicefv1_groups','servicefv_groups'));
	if (!isset($servicefv_groups))
	{
	    $servicefv_groups = array();
	}
	@reset($servicefv_groups);
	while ($i = @each($servicefv_groups))
	{
		$ilance->template->parse_loop('main', 'servicefvfees' . $i['value']['groupid']);
	}
	
	// #### service buget groups ###################################
	$ilance->template->parse_loop('main', array('budget_groups','budget1_groups'));
	if (!isset($budget_groups))
	{
		$budget_groups = array();
	}
	@reset($budget_groups);
	while ($i = @each($budget_groups))
	{
		$ilance->template->parse_loop('main', 'budgetfees' . $i['value']['groupid']);
	}
	
	// #### shippers ###############################################
	$ilance->template->parse_loop('main', array('shippers','paytypes'));
	
	// #### payment types ##########################################
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### PAYMENT MODULES ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'paymodules')
{
	$area_title = $phrase['_payment_modules_management'];
	$page_title = SITE_NAME.' - ' . $phrase['_payment_modules_management'];
		
	($apihook = $ilance->api('can_moderator_access_settings_paymodules')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=paymodules', $_SESSION['ilancedata']['user']['slng']);
	
	// #### INSERT NEW PAYMENT METHOD ##############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-paytype')
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "payment_methods
			(id, title)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=paymodules');
		exit();
	}
	
	// #### REMOVE PAYMENT TYPE ####################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-paytype' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "payment_methods
			WHERE id = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=paymodules');
		exit();
	}
	
	// #### UPDATE PAYMENT TYPES ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-paytypes')
	{
		foreach ($ilance->GPC['title'] AS $id => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "payment_methods
				SET title = '" . $ilance->db->escape_string($title) . "'
				WHERE id = '" . intval($id) . "'
			");
		}

		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=paymodules');
		exit();
	}
	
	// #### CREATE NEW TAX ZONE ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'create-taxzone')
	{
		$ilance->GPC['invoicetypes'] = (!empty($ilance->GPC['taxtype'])) ? serialize($ilance->GPC['taxtype']) : '';
		
		if (empty($ilance->GPC['country']))
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_country_name_please_retry'], $ilance->GPC['return']);
			exit();
		}
		
		$countryid = intval($ilance->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "'", "locationid"));
		if ($countryid == 0)
		{
			print_action_failed($phrase['_there_is_no_country_with_this_name_in_the_system_please_retry'], $ilance->GPC['return']);
			exit();
		}
		
		if (empty($ilance->GPC['taxlabel']))
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_title_name_please_retry'], $ilance->GPC['return']);
			exit();
		}
		
		if (empty($ilance->GPC['state']))
		{
			$ilance->GPC['state'] = '';
		}
		
		if (empty($ilance->GPC['city']))
		{
			$ilance->GPC['city'] = '';
		}
		if (empty($ilance->GPC['amount']))
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_amount_please_retry'], $ilance->GPC['return']);
			exit();
		}

		$entirecountry = ((isset($ilance->GPC['entirecountry']) AND $ilance->GPC['entirecountry'] == 'true') ? 1 : 0);
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "taxes
			(taxid, taxlabel, state, countryname, countryid, city, amount, invoicetypes, entirecountry)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['taxlabel']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['state']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['country']) . "',
			'" . intval($countryid) . "',
			'" . $ilance->db->escape_string($ilance->GPC['city']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['amount']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['invoicetypes']) . "',
			'" . intval($entirecountry) . "')
		");
		
		print_action_success($phrase['_new_tax_zone_was_successfully_added_to_the_tax_zone_system'], $ilance->GPC['return']);
		exit();
	}
		
	// #### REMOVE TAX ZONE ########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-taxzone')
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "taxes
			WHERE taxid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		
		print_action_success($phrase['_the_selected_tax_zone_was_successfully_removed'], $ilpage['settings'] . '?cmd=paymodules');
		exit();
	}
	
	// #### UPDATE TAX ZONE ########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-taxzone')
	{
		$entirecountry = ((isset($ilance->GPC['entirecountry']) AND $ilance->GPC['entirecountry'] == 'true') ? 1 : 0);
		
		if ($entirecountry AND empty($ilance->GPC['state']))
		{
			$ilance->GPC['state'] = '';
		}
		if ($entirecountry AND empty($ilance->GPC['city']))
		{
			$ilance->GPC['city'] = '';
		}
		
		if (!empty($ilance->GPC['taxtype']))
		{
			$ilance->GPC['invoicetypes'] = serialize($ilance->GPC['taxtype']);
		}
		else
		{
			$ilance->GPC['invoicetypes'] = '';
		}
    
		if (empty($ilance->GPC['country']))
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_country_name_please_retry'], $ilance->GPC['return']);
			exit();
		}
    
		$countryid = (int)$ilance->db->fetch_field(DB_PREFIX . "locations", "location_" . $_SESSION['ilancedata']['user']['slng'] . " = '".$ilance->db->escape_string($ilance->GPC['country'])."'", "locationid");
		if ($countryid == 0)
		{
			print_action_failed($phrase['_there_is_no_country_with_this_name_in_the_system_please_retry'], $ilance->GPC['return']);
			exit();
		}
		if (empty($ilance->GPC['taxlabel']))
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_title_name_please_retry'], $ilance->GPC['return']);
			exit();
		}
		if (empty($ilance->GPC['state']) AND $entirecountry == 0)
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_state_please_retry'], $ilance->GPC['return']);
			exit();
		}
		if (empty($ilance->GPC['amount']))
		{
			print_action_failed($phrase['_you_must_enter_a_tax_zone_amount_please_retry'], $ilance->GPC['return']);
			exit();
		}
    
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "taxes
			SET taxlabel = '" . $ilance->db->escape_string($ilance->GPC['taxlabel']) . "',
			state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
			countryname = '" . $ilance->db->escape_string($ilance->GPC['country']) . "',
			countryid = '" . intval($countryid) . "',
			city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
			amount = '" . $ilance->db->escape_string($ilance->GPC['amount']) . "',
			invoicetypes = '" . $ilance->GPC['invoicetypes'] . "',
			entirecountry = '" . $entirecountry . "'
			WHERE taxid = '" . intval($ilance->GPC['taxid']) . "'
			LIMIT 1
		");
    
		print_action_success($phrase['_tax_zone_was_successfully_updated_in_the_tax_zone_system'], $ilance->GPC['return']);
		exit();
	}
	
	//suku
	//#### UPLOAD TAX FILE ################################################
	//TAX FILES FIRST LINE SHOULD IN THE BELOW ORDER AND SPELLING
	//z2t_ID	ZipCode	SalesTaxRate	RateState	ReportingCodeState	RateCounty	ReportingCodeCounty	RateCity	ReportingCodeCity	RateSpecialDistrict	//ReportingCodeSpecialDistrict	City	PostOffice	State	County	ShippingTaxable	PrimaryRecord
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'upload_taxfile')
	{
		if ($_FILES["taxfilename"]["error"] > 0)
		  {
		  print_action_success("Problem with file upload", $ilpage['settings'] . '?cmd=paymodules');
		  exit;
		  }
		else
		  {
		 /* echo "Upload: " . $_FILES["taxfilename"]["name"] . "<br />";
		  echo "Type: " . $_FILES["taxfilename"]["type"] . "<br />";
		  echo "Size: " . ($_FILES["taxfilename"]["size"] / 1024) . " Kb<br />";
		  echo "Stored in: " . $_FILES["taxfilename"]["tmp_name"];
		  exit;*/
		  $handle = fopen($_FILES["taxfilename"]["tmp_name"], "r");
		  $line_no=1;
		  while($data = fgetcsv($handle, 1000, ","))
		  {
		  if($line_no==0)
		  {
		  if($data[0]!='z2t_ID' and $data[1]!='ZipCode' and $data[2]=='SalesTaxRate' and $data[3]!='RateState' and $data[7]!='RateCity')
		  {
		  print_action_success("Invalid File format uploaded ", $ilpage['settings'] . '?cmd=paymodules');
		  exit;
		  }
		  }
		  if($line_no>1)
		  {
		  $dump[$line_no]=$data;
		  }
		  $line_no++;
		  }
			if(is_array($dump) and count($dump)>0)
			{
			foreach($dump as $row)
			{
				
				$select_res=$ilance->db->query("select * from ".DB_PREFIX."taxes  where zipcode='".$row[1]."'");
				if($ilance->db->num_rows($select_res)==0)
				{
				$ilance->db->query("insert into ".DB_PREFIX."taxes (taxlabel, state, countryname, countryid, city, amount, invoicetypes, zipcode) values (
									'Tax for Zip ".$row['1']."',
									'California',
									'USA',
									'500',
									'".$row[7]."',
									'".$row[2]."',
									'a:2:{s:6:\"buynow\";s:1:\"1\";s:6:\"escrow\";s:1:\"1\";}',
									'".$row[1]."')");
				}else
				{
				$update_sucessful=$ilance->db->query("update ".DB_PREFIX."taxes set amount=".$row[2]." where zipcode='".$row[1]."'");
				}
			}
			 print_action_success("Tax file sucessfully updated", $ilpage['settings'] . '?cmd=paymodules');
		  		exit;
			}
		  
		  }
	}

	
	// #### PAYMENT MODULES ################################################
	
	$paymodules_authnet = $ilance->admincp->construct_paymodules_input('authnet', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_bluepay = $ilance->admincp->construct_paymodules_input('bluepay', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_plugnpay = $ilance->admincp->construct_paymodules_input('plug_n_pay', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_psigate = $ilance->admincp->construct_paymodules_input('psigate', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_eway = $ilance->admincp->construct_paymodules_input('eway', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_defaultgateway = $ilance->admincp->construct_paymodules_input('defaultgateway', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_paypal = $ilance->admincp->construct_paymodules_input('paypal', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_stormpay = $ilance->admincp->construct_paymodules_input('stormpay', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_cashu = $ilance->admincp->construct_paymodules_input('cashu', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_moneybookers = $ilance->admincp->construct_paymodules_input('moneybookers', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_check = $ilance->admincp->construct_paymodules_input('check', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_bank = $ilance->admincp->construct_paymodules_input('bank', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_keys = $ilance->admincp->construct_paymodules_input('keys', $ilpage['settings'] . '?cmd=paymodules');
	$paymodules_ach = $ilance->admincp->construct_paymodules_input('ach', $ilpage['settings'] . '?cmd=paymodules');	

	($apihook = $ilance->api('admincp_template_payment_modules_end')) ? eval($apihook) : false;
	
	// #### TAX MODULE #####################################################
	$taxlabel = $countryname = $state = $city = $amount = $entirecountry_cb = '';

	// #### UPDATE TAX ZONE ################################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-taxzone')
	{
		$updtax = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "taxes
			WHERE taxid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($updtax) > 0)
		{
			$taxres = $ilance->db->fetch_array($updtax);
			$taxlabel = $taxres['taxlabel'];
			$countryname = $taxres['countryname'];
			$state = $taxres['state'];
			$city = $taxres['city'];
			$amount = $taxres['amount'];
			$entirecountry = $taxres['entirecountry'];
			
			if ($taxres['entirecountry'])
			{
				$entirecountry_cb = 'checked="checked"';
				
				$headinclude .= "
<script language=\"Javascript\">
function disable_select() 
{
document.ilform.state.disabled = true;
document.ilform.city.disabled = true;
}
</script>";
				$onload .= "return disable_select();";
			}
			
			if (!empty($taxres['invoicetypes']))
			{
				$checked1 = $checked2 = $checked3 = $checked4 = $checked5 = $checked6 = $checked7 = $checked8 = $checked9 = $checked10 = $checked11 = '';
				$invoicetypetax = unserialize($taxres['invoicetypes']);
				
				foreach ($invoicetypetax AS $invoicetype => $value)
				{
					switch ($invoicetype)
					{
						case 'storesubscription':
						{
							$checked1 .= 'checked="checked"';
							//$checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= '';
							break;
						}							
						case 'subscription':
						{
							//$checked1 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= '';
							$checked2 .= 'checked="checked"';
							break;
						}							
						case 'commission':
						{
							//$checked1 .= $checked2 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= '';
							$checked3 .= 'checked="checked"';
							break;
						}							
						case 'credential':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked5 .= $checked6 .= $checked7 .= '';
							$checked4 .= 'checked="checked"';
							break;
						}							
						case 'portfolio':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked6 .= $checked7 .= '';
							$checked5 .= 'checked="checked"';
							break;
						}							
						case 'enhancements':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked7 .= '';
							$checked6 .= 'checked="checked"';
							
							break;
						}
						case 'lanceads':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= '';
							$checked7 .= 'checked="checked"';
							break;
						}
						case 'insertionfee':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= '';
							$checked8 .= 'checked="checked"';
							break;
						}
						case 'finalvaluefee':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= $checked8 .= '';
							$checked9 .= 'checked="checked"';
							break;
						}
						case 'buynow':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= $checked8 .= $checked9 .= '';
							$checked10 .= 'checked="checked"';
							break;
						}
						case 'escrow':
						{
							//$checked1 .= $checked2 .= $checked3 .= $checked4 .= $checked5 .= $checked6 .= $checked7 .= $checked8 .= $checked9 .= $checked10 .= '';
							$checked11 .= 'checked="checked"';
							break;
						}
					}
				}
			}
			else
			{
				$invoicetypetax = '';
			}
		}
    
		$tax_subcmd = 'update-taxzone';
		$tax_id_hidden = '<input type="hidden" name="taxid" value="' . intval($ilance->GPC['id']) . '" />';
		$taxsubmit = '<input type="submit" value="' . $phrase['_update'] . '" class="buttons" style="font-size:15px" />';
		$invoicetypetax = '';
		
		// tax types currently supported
		$invoicetypetax .= '<label for="subscription"><input type="checkbox" name="taxtype[subscription]" id="subscription" value="1" ' . $checked2 . ' />' . $phrase['_member_subscription_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="commission"><input type="checkbox" name="taxtype[commission]" id="commission" value="1" ' . $checked3 . ' />' . $phrase['_escrow'] . ' ' . $phrase['_commission_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="credential"><input type="checkbox" name="taxtype[credential]" id="credential" value="1" ' . $checked4 . ' />' . $phrase['_credential_verification_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="portfolio"><input type="checkbox" name="taxtype[portfolio]" id="portfolio" value="1" ' . $checked5 . ' />' . $phrase['_featured_portfolio_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="enhancements"><input type="checkbox" name="taxtype[enhancements]" id="enhancements" value="1" ' . $checked6 . ' />' . $phrase['_auction_enhancement_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="insertionfee"><input type="checkbox" name="taxtype[insertionfee]" id="insertionfee" value="1" ' . $checked8 . ' />Insertion Fees</label><br />';
		$invoicetypetax .= '<label for="finalvaluefee"><input type="checkbox" name="taxtype[finalvaluefee]" id="finalvaluefee" value="1" ' . $checked9 . ' />Final Value Fees</label><br />';
		$invoicetypetax .= '<label for="buynow"><input type="checkbox" name="taxtype[buynow]" id="buynow" value="1" ' . $checked10 . ' />Buynow Purchase</label><br />';
		$invoicetypetax .= '<label for="escrow"><input type="checkbox" name="taxtype[escrow]" id="escrow" value="1" ' . $checked11 . ' />Auction Award</label><br />';
		
		($apihook = $ilance->api('admincp_update_taxzone_end')) ? eval($apihook) : false;
	}
	else
	{
		$tax_subcmd = 'create-taxzone';
		$taxsubmit = '<input type="submit" value="' . $phrase['_create'] . '" class="buttons" style="font-size:15px" />';
		$tax_id_hidden = $invoicetypetax = '';			
		
		// tax types currently supported
		$invoicetypetax .= '<label for="subscription"><input type="checkbox" name="taxtype[subscription]" id="subscription" value="1" />' . $phrase['_member_subscription_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="commission"><input type="checkbox" name="taxtype[commission]" id="commission" value="1" />' . $phrase['_escrow'] . ' ' . $phrase['_commission_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="credential"><input type="checkbox" name="taxtype[credential]" id="credential" value="1" />' . $phrase['_credential_verification_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="portfolio"><input type="checkbox" name="taxtype[portfolio]" id="portfolio" value="1" />' . $phrase['_featured_portfolio_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="enhancements"><input type="checkbox" name="taxtype[enhancements]" id="enhancements" value="1" />' . $phrase['_auction_enhancement_fees'] . '</label><br />';
		$invoicetypetax .= '<label for="insertionfee"><input type="checkbox" name="taxtype[insertionfee]" id="insertionfee" value="1" />Insertion Fees</label><br />';
		$invoicetypetax .= '<label for="finalvaluefee"><input type="checkbox" name="taxtype[finalvaluefee]" id="finalvaluefee" value="1" />Final Value Fees</label><br />';
		$invoicetypetax .= '<label for="buynow"><input type="checkbox" name="taxtype[buynow]" id="buynow" value="1"  />Buynow Purchase</label><br />';
		$invoicetypetax .= '<label for="escrow"><input type="checkbox" name="taxtype[escrow]" id="escrow" value="1" />Auction Award</label><br />';
		
		$dynamic_js_bodyend = '';
		
		($apihook = $ilance->api('admincp_create_taxzone_start')) ? eval($apihook) : false;
	}
	    
	// display taxes row
	$show['no_taxes'] = true;
	$sqltax = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "taxes
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqltax) > 0)
	{
		$taxcount = 0;
		while ($tax = $ilance->db->fetch_array($sqltax))
		{
			$tax['class'] = ($taxcount % 2) ? 'alt2' : 'alt1';
			$tax['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=_remove-taxzone&amp;id=' . $tax['taxid'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$tax['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=_update-taxzone&amp;id=' . $tax['taxid'] . '#editzone"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$tax['entire'] = ($tax['entirecountry']) ? $phrase['_yes'] : $phrase['_no'];
				
			if (!empty($tax['invoicetypes']))
			{
				$invoicetypetaxx = unserialize($tax['invoicetypes']);
				$typex = '';
				foreach ($invoicetypetaxx AS $invoicetypex => $value)
				{
					$typex .= ucfirst($invoicetypex) . ', ';
				}
				$typex = mb_substr($typex, 0, -2);
				$tax['types'] = $typex;
			}
			else
			{
				$tax['types'] = $phrase['_no_invoice_types_defined'];
			}
			
			if (empty($tax['state']))
			{
				$tax['state'] = '-';
			}
			
			if (empty($tax['city']))
			{
				$tax['city'] = '-';
			}
			
			$taxes[] = $tax;
			$taxcount = $taxcount+1;
		}
		
		$show['no_taxes'] = false;
	}

	// construct countries / states pulldown
	//$country_js_pulldown = print_js_countries_pulldown('country', 'state', 'city', $dojs = false, '', '', '', 'ilform');
	//$state_js_pulldown = print_js_states_pulldown('state', 'city', $dojs = false, '', '', '', 'ilform');
	
	// construct countries / states pulldown
	$jscountry = isset($countryname) ? $countryname : $ilconfig['registrationdisplay_defaultcountry'];
	$jsstate = isset($state) ? $state : $ilconfig['registrationdisplay_defaultstate'];
	$jscity = isset($city) ? $city : $ilconfig['registrationdisplay_defaultcity'];
	
	$countryid = fetch_country_id($jscountry, $_SESSION['ilancedata']['user']['slng']);
	$country_js_pulldown = construct_country_pulldown($countryid, $jscountry, 'country', false, 'state');
	$state_js_pulldown = construct_state_pulldown($countryid, $jsstate, 'state');
	
	// #### PAYMENT TYPES ##########################################
	$show['no_paytypes_rows'] = true;
	
	$sql = $ilance->db->query("
		SELECT id, title
		FROM " . DB_PREFIX . "payment_methods
		ORDER BY sort ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sql))
		{
			$row['title'] = '<div><input type="text" name="title[' . $row['id'] . ']" value="' . stripslashes($row['title']) . '" class="input" size="75%" /></div><div style="padding-top:6px"><div class="gray">' . $phrase[$row['title']] . '</div></div>';
			$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=paymodules&amp;subcmd=remove-paytype&amp;id=' . $row['id'] . '" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';				
			$paytypes[] = $row;
			$row_count++;
		}
		
		$show['no_paytypes_rows'] = false;
	}
	else
	{
		$show['no_paytypes_rows'] = true;
	}
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','paymodules_ach','paymodules_bluepay','entirecountry_cb','country_js_pulldown','state_js_pulldown','dynamic_js_bodyend','paymodules_moneybookers','paymodules_stormpay','paymodules_cashu','paymodules_psigate','paymodules_eway','paymodules_nochex','taxsubmit','tax_subcmd','tax_id_hidden','invoicetypetax','state','city','amount','taxlabel','countryname','configuration_invoicesystem','configuration_escrowsystem','paymodules_plugnpay','paymodules_keys','paymodules_bank','paymodules_check','paymodules_defaultipn','paymodules_paypal','paymodules_defaultgateway','paymodules_authnet','tctfee','tcwfee','pptfee','ppwfee','antfee','anactive','max_cc_verify_attempts','checked_enable_outside_fees_true','checked_enable_outside_fees_false','checked_enable_internal_fees_true','checked_enable_internal_fees_false','checked_wt_active_true','checked_wt_active_false','checked_multi_bankaccount_support','checked_disable_cc_on_processor_decline','checked_multi_creditcard_support','checked_refund_on_max_cc_attempts','checked_creditcard_authentication','checked_authnet_enabled_true','checked_authnet_enabled_false','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_paymodules_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'paymodules.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','taxes','paytypes'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### EMAIL TEMPLATES ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'emailtemplates')
{        
	$area_title = 'AdminCP - ' . $phrase['_email_templates_management_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_email_templates_management_menu'];
	
	($apihook = $ilance->api('admincp_emailtemplates_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=emailtemplates', $_SESSION['ilancedata']['user']['slng']);
	
	// #### DOWNLOAD XML EMAIL PACKAGE #############################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_download-xml-emails')
	{
		$area_title = $phrase['_exporting_email_language_pack_via_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_exporting_email_language_pack_via_xml'];
    
		if (isset($ilance->GPC['id']))
		{
			$languageid = intval($ilance->GPC['id']);
		}
		else if (isset($ilance->GPC['languageid']))
		{
			$languageid = intval($ilance->GPC['languageid']);
		}
		else
		{
			$languageid = 1;
		}
		
		$characterset = '';
		if (!empty($ilance->GPC['characterset']))
		{
			$characterset = $ilance->GPC['characterset'];
		}
		
		$phrasefilter = '';
		if (!empty($ilance->GPC['phrasefilter']))
		{
			$phrasefilter = $ilance->GPC['phrasefilter'];
		}
    
		$query = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . intval($languageid) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($query) > 0)
		{
			$langconfig = $ilance->db->fetch_array($query);
			header("Content-type: text/xml; charset=" . stripslashes($langconfig['charset']));
			
			$xml_output = "<?xml version=\"1.0\" encoding=\"" . stripslashes($langconfig['charset']) . "\"?>\n";
			$xml_output .= "<language ilversion=\"" . $ilance->config['ilversion'] . "\">\n\n";
			$xml_output .= "\t<settings>\n";
			$xml_output .= "\t\t<author><![CDATA[" . stripslashes(SITE_NAME) . "]]></author>\n";
			$xml_output .= "\t\t<languagecode><![CDATA[" . stripslashes($langconfig['languagecode']) . "]]></languagecode>\n";
			$xml_output .= "\t\t<charset><![CDATA[" . stripslashes($langconfig['charset']) . "]]></charset>\n";
			$xml_output .= "\t</settings>\n";
			
			$query2 = $ilance->db->query("
				SELECT name, varname, type, subject_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS subject, message_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS message, product, cansend, departmentid
				FROM " . DB_PREFIX . "email
				ORDER BY id ASC
			");
			if ($ilance->db->num_rows($query2) > 0)
			{
				while ($phraseres = $ilance->db->fetch_array($query2))
				{
					$themessage = stripslashes($phraseres['message']);
					$thesubject = stripslashes($phraseres['subject']);
					$thename = stripslashes($phraseres['name']);
					
					if (isset($ilance->GPC['decodenumericentities']) AND $ilance->GPC['decodenumericentities'])
					{
						$themessage = html_entity_decode($themessage, ENT_NOQUOTES, 'UTF-8');
						$thesubject = html_entity_decode($thesubject, ENT_NOQUOTES, 'UTF-8');
						$thename = html_entity_decode($thename, ENT_NOQUOTES, 'UTF-8');
					}
					
					if (isset($ilance->GPC['decodeentities']) AND $ilance->GPC['decodeentities'])
					{
						$themessage = $ilance->admincp->decode_entities($themessage);
						$thesubject = $ilance->admincp->decode_entities($thesubject);
						$thename = $ilance->admincp->decode_entities($thename);
					}
					
					//$xml_output .= "\t<email varname=\"" . trim($phraseres['varname']) . "\" name=\"" . $thename . "\" subject=\"" . $thesubject . "\" type=\"" . trim($phraseres['type']) . "\" product=\"" . trim($phraseres['product']) . "\" cansend=\"" . intval($phraseres['cansend']) . "\" departmentid=\"" . intval($phraseres['departmentid']) . "\">\n\t\t\t<![CDATA[" . $themessage . "]]>\n\t\t</email>\n\n";
					$xml_output .= "
\t<email>
\t\t<varname>" . trim($phraseres['varname']) . "</varname>
\t\t<name><![CDATA[" . $thename . "]]></name>
\t\t<subject><![CDATA[" . $thesubject . "]]></subject>
\t\t<type><![CDATA[" . trim($phraseres['type']) . "]]></type>
\t\t<product><![CDATA[" . trim($phraseres['product']) . "]]></product>
\t\t<cansend>" . intval($phraseres['cansend']) . "</cansend>
\t\t<departmentid>" . intval($phraseres['departmentid']) . "</departmentid>
\t\t<message><![CDATA[" . $themessage . "]]></message>
\t</email>\n";

				}
			}
			
			$xml_output .= "</language>";
			
			$ilance->common->download_file($xml_output, 'emails-' . VERSIONSTRING . '-' . mb_strtolower($langconfig['languagecode']) . '.xml', 'text/plain');
		}
	}

		// #### UPLOAD XML EMAIL PACKAGE ###############################
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_upload-xml-emails')
		{
			$area_title = $phrase['_importing_email_pack_via_xml'];
			$page_title = SITE_NAME . ' - ' . $phrase['_importing_email_pack_via_xml'];
	    
			$notice = '';
			while (list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;  
			foreach ($_FILES AS $key => $value)
			{
				$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
				foreach ($value AS $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$GLOBALS["$key2"] = $value2;
				}
			}
			
			$xml = file_get_contents($xml_file);
                        
                        $xml_encoding = 'UTF-8';
                        $xml_encoding = mb_detect_encoding($xml);
                        
                        if ($xml_encoding == 'ASCII') 
                        {
                                $xml_encoding = '';
                        }
			
			$parser = xml_parser_create($xml_encoding);
			$data = array();
			xml_parse_into_struct($parser, $xml, $data);
			$error_code = xml_get_error_code($parser);
			xml_parser_free($parser);
			
			if ($error_code == 0)
			{
				$ilance->xml = construct_object('api.xml');
				$result = $ilance->xml->process_email_xml($data, $xml_encoding);
				
				$noversioncheck = isset($ilance->GPC['noversioncheck']) ? intval($ilance->GPC['noversioncheck']) : 0;
				if ($result['ilversion'] != $ilance->config['ilversion'] AND $noversioncheck == 0)
				{
					print_action_failed($phrase['_the_version_of_the_this_email_package_is_different_than'] . ' <strong>' . $ilance->config['ilversion'] . '</strong>.  ' . $phrase['_the_operation_has_aborted_due_to_a_version_conflict'], $ilance->GPC['return']);
					exit();
				}
                                
                                // check if language exists before importing xml file
                                $query = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "language
                                        WHERE languagecode = '" . $ilance->db->escape_string($result['langcode']) . "'
                                ");
                                if ($ilance->db->num_rows($query) == 0)
                                {
                                        print_action_failed($phrase['_were_sorry_email_pack_uploading_requires_the_actual_language_to_already_exist'], $ilance->GPC['return']);
                                        exit();
                                }
                                
                                $query2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "language
                                        WHERE languagecode = '" . $ilance->db->escape_string($result['langcode']) . "'
                                ");
                                if ($ilance->db->num_rows($query2) > 0)
                                {
                                        $AllLanguages = $AllSubjects = array();                                        
                                        
                                        while ($row = $ilance->db->fetch_array($query2))
                                        {
                                                $AllSubjects[] = 'subject_' . mb_substr($row['languagecode'], 0, 3);
                                                $AllMessages[] = 'message_' . mb_substr($row['languagecode'], 0, 3);
                                        }
                                        
                                        $phrasearray = $result['emailarray'];
                                        
                                        $lfn1 = 'subject_' . mb_substr($result['langcode'], 0, 3);
                                        $lfn2 = 'message_' . mb_substr($result['langcode'], 0, 3);
                                        $lfn3 = 'type';
                                        $lfn4 = 'varname';
                                        
                                        $newid = 0;                                                
                                        for ($i = 0; $i < count($phrasearray); $i++)
                                        {
                                                $product = isset($phrasearray[$i][5]) ? $phrasearray[$i][5] : 'ilance';
                                                if ($ilance->db->num_rows($ilance->db->query("SELECT * FROM " . DB_PREFIX . "email WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "' LIMIT 1")) == 0)
                                                {
                                                        // checks email subject for email text before new insert email
                                                        if ($phrasearray[$i][4] != '')
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "email
                                                                        (`varname`)
                                                                        VALUES ('" . $ilance->db->escape_string($phrasearray[$i][4]) . "')
                                                                ");
                                                        }
                                                        else
                                                        {
                                                                $notice .= "Error: Email template name '<strong>".$phrasearray[$i][0]."</strong>' could not be added due to a blank phrase existing within the xml file (near CDATA[])";
                                                        }
                                                                                    
                                                        // if new email subject is not blank .. update proper field content
                                                        if ($phrasearray[$i][1] != '') 
                                                        {
	                                                                $ilance->db->query("
	                                                                        UPDATE " . DB_PREFIX . "email 
	                                                                        SET `subject_original` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
	                                                                        `message_original` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
	                                                                        `name` = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
	                                                                        `" . $lfn1 . "` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
	                                                                        `" . $lfn2 . "` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
	                                                                        `" . $lfn3 . "` = '" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
	                                                                        `" . $lfn4 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "',
	                                                                        `product` = '" . trim($ilance->db->escape_string($product)) . "',
	                                                                        `cansend` = '" . intval($phrasearray[$i][6]) . "',
	                                                                        `departmentid` = '" . intval($phrasearray[$i][7]) . "'
	                                                                        WHERE `varname` = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
	                                                                        LIMIT 1
	                                                                ");
                                                        }
                                                        else
                                                        {
                                                                $notice .= "Error: email: <strong>".$phrasearray[$i][0]."</strong> could not be added due to a blank email template existing within the xml file (near CDATA)";
                                                        }
                                                }
                                                else
                                                {
                                                        // 'name' exists .. update
                                                        if ($phrasearray[$i][1] != '') 
                                                        {
                                                                $extraquery = '';
                                                                if (isset($ilance->GPC['overwrite']) AND $ilance->GPC['overwrite'])
                                                                {
                                                                        $extraquery .= "`" . $lfn1 . "` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',";
                                                                        $extraquery .= "`" . $lfn2 . "` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
                                                                
		                                                                $ilance->db->query("
		                                                                        UPDATE " . DB_PREFIX . "email 
		                                                                        SET `subject_original` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
		                                                                        `message_original` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
		                                                                        $extraquery
		                                                                        `" . $lfn3 . "` = '" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
		                                                                        `" . $lfn4 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "',
		                                                                        `product` = '" . trim($ilance->db->escape_string($product)) . "',
		                                                                        `cansend` = '" . intval($phrasearray[$i][6]) . "',
		                                                                        `departmentid` = '" . intval($phrasearray[$i][7]) . "'
		                                                                        WHERE `varname` = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
		                                                                        LIMIT 1
		                                                                ");
                                                                }
                                                                else 
                                                                {
		                                                                
				                                                                $extraquery = '';
		                                                                        $extraquery .= "`" . $lfn1 . "` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',";
		                                                                        $extraquery .= "`" . $lfn2 . "` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
		                                                                
				                                                                $ilance->db->query("
				                                                                        UPDATE " . DB_PREFIX . "email 
				                                                                        SET
				                                                                        $extraquery
				                                                                        `" . $lfn3 . "` = '" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
				                                                                        `" . $lfn4 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "',
				                                                                        `product` = '" . trim($ilance->db->escape_string($product)) . "',
				                                                                        `cansend` = '" . intval($phrasearray[$i][6]) . "',
				                                                                        `departmentid` = '" . intval($phrasearray[$i][7]) . "'
				                                                                        WHERE `varname` = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
				                                                                        LIMIT 1
				                                                                ");
                                                                }
                                                        }
                                                        else
                                                        {
                                                                $notice .= "Error: template: <strong>" . $phrasearray[$i][0] . "</strong> could not be added due to a blank template existing within the xml file (near CDATA[])";
                                                        }
                                                }
                                        }
                                        
                                        print_action_success($phrase['_email_language_pack_importation_success'], $ilance->GPC['return']);
                                        exit();							
                                }
                                else 
                                {
                                        print_action_failed($phrase['_were_sorry_this_language_does_not_exist'], $ilance->GPC['return']);
                                        exit();							
                                }
			}
			else
			{
				$error_string = xml_error_string($error_code);
				
				print_action_failed($phrase['_were_sorry_there_was_an_error_with_the_formatting'] . ' <strong>' . $error_string . '</strong>.', $ilance->GPC['return']);
				exit();
			}
		}
	
	// #### UPDATE EMAIL TEMPLATE PREVIEW ##########################
	else if (!empty($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-email-template' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$id = intval($ilance->GPC['id']);
		
		$area_title = $phrase['_updating_email_template'] . ' #' . $id;
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_email_template'] . ' #' . $id;
		
		$sqllang = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
		");
		while ($langres = $ilance->db->fetch_array($sqllang))
		{
			$langres['langshort'] = mb_strtolower(mb_substr($langres['languagecode'], 0, 3));
			$langres['language'] = $langres['title'];
		
			$sql = $ilance->db->query("
				SELECT id, name, varname, message_" . $langres['langshort'] . " AS message, subject_" . $langres['langshort'] . " AS subject, product, cansend, departmentid
				FROM " . DB_PREFIX . "email
				WHERE id = '" . intval($id) . "'
			");
			$res = $ilance->db->fetch_array($sql);
			
			$langres['name'] = stripslashes($res['name']);
			$langres['name'] = handle_input_keywords($langres['name']);
			
			$langres['subject'] = stripslashes($res['subject']);
			//$langres['subject'] = handle_input_keywords($langres['subject']);
			
			$langres['message'] = stripslashes($res['message']);
			$langres['varname'] = stripslashes($res['varname']);
			
			if (preg_match_all("!\{\{[a-z0-9_]+\}\}!", $langres['message'], $matches))
			{
				$langres['emailobjects'] = implode("\n", array_unique($matches[0]));
			}
			
			$langres['page'] = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
			$langres['nextid'] = ($id + 1);
			$langres['previd'] = ($id - 1);
			
			$products_pulldown = $ilance->admincp->products_pulldown($res['product']);
			$department_pulldown = $ilance->admincp->email_departments_pulldown($res['departmentid']);
			
			$email_languages[] = $langres;
		}
		
		$show['update_template'] = true;
		$show['list_template'] = false;
	}
	
	// #### UPDATE EMAIL TEMPLATE HANDLER ##########################
	else if (!empty($ilance->GPC['do']) AND $ilance->GPC['do'] == '_update-email-template' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['subject']) AND !empty($ilance->GPC['message']) AND !empty($ilance->GPC['langshort']) AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
	{
		$area_title = $phrase['_updating_email_template'] . ' #' . intval($ilance->GPC['id']);
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_email_template'] . ' #' . intval($ilance->GPC['id']);
		
		//$subject = ilance_htmlentities($ilance->GPC['subject']);
		//$message = ilance_htmlentities($ilance->GPC['message']);
		$subject = $ilance->GPC['subject'];
		$message = $ilance->GPC['message'];
		$name = ilance_htmlentities($ilance->GPC['name']);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "email
			SET name = '" . $ilance->db->escape_string($name) . "',
			message_" . $ilance->db->escape_string($ilance->GPC['langshort']) . " = '" . $ilance->db->escape_string($message) . "',
			subject_" . $ilance->db->escape_string($ilance->GPC['langshort']) . " = '" . $ilance->db->escape_string($subject) . "',
			varname = '" . $ilance->db->escape_string($ilance->GPC['varname']) . "',
			product = '" . $ilance->db->escape_string($ilance->GPC['product']) . "',
			departmentid = '" . intval($ilance->GPC['departmentid']) . "'
			WHERE id = '" . intval($ilance->GPC['id']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();
	}
	
	// #### INSERT NEW EMAIL TEMPLATE ##############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_insert-email-template' AND isset($ilance->GPC['subject']) AND isset($ilance->GPC['message']) AND isset($ilance->GPC['name']) AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
	{
		$area_title = $phrase['_adding_new_email_template'];
		$page_title = SITE_NAME . ' - ' . $phrase['_adding_new_email_template'];
    
		$ids = $val = '';
		
		$subject = $ilance->GPC['subject'];
		$message = $ilance->GPC['message'];
		//$subject = ilance_htmlentities($ilance->GPC['subject']);
		//$message = ilance_htmlentities($ilance->GPC['message']);
		$name = ilance_htmlentities($ilance->GPC['name']);
		
		$sql = $ilance->db->query("
			SELECT languagecode
			FROM " . DB_PREFIX . "language
		");
		while ($langres = $ilance->db->fetch_array($sql))
		{
			$ids .= " message_" . mb_substr($langres['languagecode'], 0, 3) . ", subject_" . mb_substr($langres['languagecode'], 0, 3) . ", ";
			$val .= "'" . $ilance->db->escape_string($message) . "', '" . $ilance->db->escape_string($subject) . "', ";
		}
    
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "email
			(id, varname, name, subject_original, message_original, " . $ids . " type, product, cansend, departmentid)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['varname']) . "',
			'" . $ilance->db->escape_string($name) . "',
			'" . $ilance->db->escape_string($subject) . "',
			'" . $ilance->db->escape_string($message) . "',
			$val
			'" . $ilance->db->escape_string($ilance->GPC['type']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['product']) . "',
			'1',
			'" . intval($ilance->GPC['departmentid']) . "')
		");
		
		$id = $ilance->db->insert_id();
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=_update-email-template&amp;id=' . $id);
		exit();
	}
	// #### REMOVE EMAIL DEPARTMENT HANDLER ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removedepartment' AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
	{
		$area_title = $phrase['_removing_email_department'] . ' #' . intval($ilance->GPC['departmentid']);
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_email_department'] . ' #' . intval($ilance->GPC['departmentid']);
		
		$sql = $ilance->db->query("
			SELECT canremove
			FROM " . DB_PREFIX .  "email_departments
			WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
		");
		$res = $ilance->db->fetch_array($sql);
		if ($res['canremove'] == '1')
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "email_departments
				WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
			");
			
			// select the default non-removable department
			$sql2 = $ilance->db->query("
				SELECT departmentid
				FROM " . DB_PREFIX .  "email_departments
				WHERE canremove = '0'
			");
			$res2 = $ilance->db->fetch_array($sql2);
			
			// migrate all email templates in this department to the default non-removable department
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "email
				SET departmentid = '" . $res2['departmentid'] . "'
				WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
			");
			
			print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=emailtemplates');
			exit();	
		}
		else
		{
			print_action_failed($phrase['_email_department_could_not_be_removed_this_is_your_default_email'], $ilpage['settings'] . '?cmd=emailtemplates');
			exit();	
		}
	}
	// #### ADD EMAIL DEPARTMENT HANDLER ###########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'adddepartment' AND isset($ilance->GPC['title']) AND $ilance->GPC['title'] != '' AND isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '')
	{
		$area_title = $phrase['_adding_new_email_department'];
		$page_title = SITE_NAME . ' - ' . $phrase['_adding_new_email_department'];
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "email_departments
			(departmentid, title, email, canremove)
			VALUES (
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['email']) . "',
			'1')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();	
	}
	// #### UPDATE EMAIL DEPARTMENT HANDLER ########################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'doupdatedepartment' AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0 AND isset($ilance->GPC['title']) AND $ilance->GPC['title'] != '' AND isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '')
	{
		$area_title = $phrase['_updating_email_department'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_email_department'];
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "email_departments
			SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
			WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilance->GPC['return']);
		exit();	
	}
	else
	{
		$show['update_template'] = false;
		$show['list_template'] = true;
    
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		
		$rowlimit = '10';
		$counter = ($ilance->GPC['page'] - 1) * $rowlimit;
		$orderlimit = ' ORDER BY id ASC LIMIT ' . (($ilance->GPC['page'] - 1) * $rowlimit) . ',' . $rowlimit;
    
		// are we searching for a particular email template?
		$extrasql = '';
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
		{
			$extrasql = "WHERE id > 0 ";
			
			if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
			{
				$extrasql .= "AND id = '" . intval($ilance->GPC['id']) . "'";
			}
			if (isset($ilance->GPC['varname']) AND !empty($ilance->GPC['varname']))
			{
				$extrasql .= "AND varname = '" . $ilance->db->escape_string($ilance->GPC['varname']) . "'";
			}
			if (isset($ilance->GPC['keywords']) AND !empty($ilance->GPC['keywords']))
			{
				$extrasql .= "AND subject_" . fetch_site_slng() . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keywords']) . "%' OR message_" . fetch_site_slng() . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keywords']) . "%'";
			}
			if (isset($ilance->GPC['product']) AND !empty($ilance->GPC['product']))
			{
				$extrasql .= "AND product = '" . $ilance->db->escape_string($ilance->GPC['product']) . "'";
			}
		}
    
		$sql = $ilance->db->query("
			SELECT id, varname, name, message_" . fetch_site_slng() . " AS body, subject_" . fetch_site_slng() . " AS subject, product, cansend, departmentid
			FROM " . DB_PREFIX . "email
			$extrasql
			$orderlimit
		");
		
		$sql2 = $ilance->db->query("
			SELECT id, varname, name, message_" . fetch_site_slng() . " AS body, subject_" . fetch_site_slng() . " AS subject, product, cansend, departmentid
			FROM " . DB_PREFIX . "email
			$extrasql
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$number = $ilance->db->num_rows($sql2);
			$row_count = 0;
			
			while ($res = $ilance->db->fetch_array($sql))
			{
				if ($res['product'] == 'ilance')
				{
					$res['product'] = 'ILance';
				}
				else
				{
					$res['product'] = ucfirst($res['product']);
				}
				$res['action'] = '<a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=_update-email-template&amp;id=' . $res['id'] . '&amp;page=' . intval($ilance->GPC['page']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$res['department'] = $ilance->admincp->fetch_email_department_title($res['departmentid']);
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$email_templates[] = $res;
				$row_count++;
			}
			
			// settings.php?cmd=emailtemplates&subcmd=search&id=&varname=&keywords=This+email+is+to+inform+you+that&product=ilance
			$subcmd = (isset($ilance->GPC['subcmd']) AND !empty($ilance->GPC['subcmd'])) ? $ilance->GPC['subcmd'] : '';
			$id = (isset($ilance->GPC['id']) AND !empty($ilance->GPC['id'])) ? $ilance->GPC['id'] : '0';
			$varname = (isset($ilance->GPC['varname']) AND !empty($ilance->GPC['varname'])) ? $ilance->GPC['varname'] : '';
			$keywords = (isset($ilance->GPC['keywords']) AND !empty($ilance->GPC['keywords'])) ? $ilance->GPC['keywords'] : '';
			
			$extra = '&amp;subcmd=' . $subcmd . '&amp;id=' . $id . '&amp;varname=' . $varname . '&amp;keywords=' . $keywords . '';
			$prevnext = print_pagnation($number, $rowlimit, $ilance->GPC['page'], $counter, $ilpage['settings'] . '?cmd=emailtemplates' . $extra);
		}
		
		$products_pulldown = $ilance->admincp->products_pulldown();
		$department_pulldown = $ilance->admincp->email_departments_pulldown();
		
		// #### EMAIL DEPARTMENT MANAGEMENT ####################
		$title = $email = $hiddeninput = '';
		$emailsubcmd = 'adddepartment';
		$submitname = $phrase['_add'] . ' ' . $phrase['_department'];
		
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updatedepartment' AND isset($ilance->GPC['departmentid']) AND $ilance->GPC['departmentid'] > 0)
		{
			$emailsubcmd = 'doupdatedepartment';
			$submitname = $phrase['_update'];
			$hiddeninput = '<input type="hidden" name="departmentid" value="' . intval($ilance->GPC['departmentid']) . '" />';
			
			$sql = $ilance->db->query("
				SELECT departmentid, title, email
				FROM " . DB_PREFIX . "email_departments
				WHERE departmentid = '" . intval($ilance->GPC['departmentid']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				$title = $res['title'];
				$email = $res['email'];
			}
		}
		
		$sql = $ilance->db->query("
			SELECT departmentid, title, email, canremove
			FROM " . DB_PREFIX . "email_departments
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql))
			{
				$res['templatecount'] = $ilance->admincp->fetch_email_department_count($res['departmentid']);
				if ($res['canremove'])
				{
					 $res['action'] = '<a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=updatedepartment&amp;departmentid=' . $res['departmentid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a> &nbsp; <a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=removedepartment&amp;departmentid=' . $res['departmentid'] . '" style="color:#990000" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
					 $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				}
				else
				{
					$res['action'] = '<a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=updatedepartment&amp;departmentid=' . $res['departmentid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
					$res['class'] = 'featured_highlight';
				}
				
				$email_departments[] = $res;
				$row_count++;
			}
		}
	}
		
	$language_pulldown = $ilance->language->print_language_pulldown();
	$keywords = isset($ilance->GPC['keywords']) ? $ilance->GPC['keywords'] : '';
	$varname = isset($ilance->GPC['varname']) ? $ilance->GPC['varname'] : '';
	
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','hiddeninput','department_pulldown','action','submitname','emailsubcmd','title','email','varname','keywords','products_pulldown','langshort','language_pulldown','page','name','subject','body','emailobjects','id','prevnext','login_include','headinclude','area_title','page_title');
	
	($apihook = $ilance->api('admincp_emailtemplates_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'emailtemplates.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','email_languages','email_templates','email_departments'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### MAINTENANCE MODE #######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'maintenance')
{
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=maintenance', $_SESSION['ilancedata']['user']['slng']);
		
	// #### UPDATE MAINTENANCE MODE ########################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-maintenance-mode')
	{
		$area_title = $phrase['_updating_maintenance_mode_settings'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_maintenance_mode_settings'];
		
		$maintenance_mode = $ilance->GPC['maintenancemode'];
		$maintenance_message = $ilance->GPC['message'];
		$maintenance_excludeips = $ilance->GPC['excludeips'];
		$maintenance_excludeurls = $ilance->GPC['excludeurls'];
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "configuration
			SET value = '" . $maintenance_mode . "'
			WHERE name = 'maintenance_mode'
		");
		
		$notify = intval($ilance->GPC['notify']);
		if ($notify)
		{
			$sqlu = $ilance->db->query("
				SELECT email
				FROM " . DB_PREFIX . "users
				WHERE status = 'active'
			");
			if ($ilance->db->num_rows($sqlu) > 0)
			{
				while ($rows = $ilance->db->fetch_array($sqlu))
				{
					$subject = SITE_NAME." - Maintenance Mode";
					$message = $maintenance_message;
					send_email($rows['email'], $subject, $message, SITE_EMAIL);
				}
			}
		}
		print_action_success($phrase['_maintenance_configuration_settings_have_been_saved'], $ilpage['settings'] . '?cmd=maintenance');
		exit();
	}
	else
	{
		$area_title = $phrase['_maintenance_mode_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_maintenance_mode_menu'];
		
		($apihook = $ilance->api('admincp_maintenance_settings')) ? eval($apihook) : false;
    
		$excludeips_list = $ilconfig['maintenance_excludeips'];
		$excludeips = $ilconfig['maintenance_excludeips'];
		$ips = mb_split(', ', $excludeips);
		
		$excludeips = array();            
		foreach ($ips as $ipaddress)
		{
			$excludeips[] = $ipaddress;
		}
		
		$excludeurls_list = $ilconfig['maintenance_excludeurls'];
		$excludeurls = $ilconfig['maintenance_excludeurls'];
		$urls = mb_split(', ', $excludeurls);
		
		$excludeurls = array();            
		foreach ($urls as $pagename)
		{
			$excludeurls[] = $pagename;
		}			
		$message = stripslashes($ilconfig['maintenance_message']);
		
		$configuration_input = $ilance->admincp->construct_admin_input('maintenance', $ilpage['settings'] . '?cmd=maintenance');
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','configuration_input','excludeurls_list','excludeips_list','message','maintenance_mode_pulldown','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_maintenance_end')) ? eval($apihook) : false;
    
		$ilance->template->fetch('main', 'maintenance.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','template_files'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

// #### SCHEDULED TASKS ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'automation')
{
	$area_title = $phrase['_scheduled_tasks_and_automation_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_scheduled_tasks_and_automation_menu'];
	
	($apihook = $ilance->api('admincp_automation_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=automation', $_SESSION['ilancedata']['user']['slng']);
	
	// #### PRUNE TASKS ####################################################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'prune')
	{
		$ilance->GPC['cronid'] = intval($ilance->GPC['cronid']);
		$ilance->GPC['varname'] = $ilance->admincp->fetch_task_varname($ilance->GPC['cronid']);
		$ilance->GPC['days'] = intval($ilance->GPC['days']);
		$ilance->GPC['cutoff'] = TIMESTAMPNOW - (86400 * $ilance->GPC['days']);
		$conds = '';
		if (!empty($ilance->GPC['varname']))
		{
			$conds = " AND varname = '" . $ilance->db->escape_string($ilance->GPC['varname']) . "'";
		}
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "cronlog
			WHERE dateline < " . $ilance->GPC['cutoff'] . " " . $conds);
		$count = number_format($ilance->db->num_rows($sql));
		
		$ilance->db->query("
			DELETE
			FROM " . DB_PREFIX . "cronlog
			WHERE dateline < " . $ilance->GPC['cutoff'] . " " . $conds);
		
		print_action_success($phrase['_scheduled_task_logs_pruned'] . ': ' . $count, $ilance->GPC['return']);
		exit();    
	}
	
	// #### UPDATE SCHEDULE TASK ###########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-crontab')
	{
		$ilance->GPC['title'] = str_replace(' ', '_', $ilance->GPC['title']);
		if (!empty($ilance->GPC['minute']))
		{
			foreach ($ilance->GPC['minute'] as $key => $value)
			{
				if ($value != '-1')
				{
					$newminute[$key] = $value;
				}
			}
		}
		$ilance->GPC['minute'] = serialize($newminute);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "cron
			SET weekday = '".intval($ilance->GPC['weekday'])."',
			day = '".intval($ilance->GPC['day'])."',
			hour = '".intval($ilance->GPC['hour'])."',
			minute = '".$ilance->db->escape_string($ilance->GPC['minute'])."',
			filename = '".$ilance->db->escape_string($ilance->GPC['filename'])."',
			loglevel = '".intval($ilance->GPC['loglevel'])."',
			active = '1',
			varname = '".$ilance->db->escape_string($ilance->GPC['title'])."',
			product = '".$ilance->db->escape_string($ilance->GPC['product'])."'
			WHERE cronid = '".intval($ilance->GPC['cronid'])."'
			LIMIT 1
		");
		
		print_action_success($phrase['_existing_scheduled_task_event_was_successfully_updated'], $ilance->GPC['return']);
		exit();
	}
	
	// #### REMOVE SCHEDULED TASK ##########################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove' AND isset($ilance->GPC['cronid']))
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "cron
			WHERE cronid = '".intval($ilance->GPC['cronid'])."'
			LIMIT 1
		");
		print_action_success($phrase['_scheduled_task_has_been_removed_from_the_cron_system'], $ilpage['settings'] . '?cmd=automation');
		exit();
	}
	
	// #### ADD NEW TASK ###################################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-new-task' AND isset($ilance->GPC['title']) AND isset($ilance->GPC['filename']) AND $ilance->GPC['filename'] != '.php')
	{
		$ilance->GPC['title'] = str_replace(' ', '_', $ilance->GPC['title']);
		if (!empty($ilance->GPC['minute']))
		{
			foreach ($ilance->GPC['minute'] as $key => $value)
			{
				if ($value != '-1')
				{
					$newminute[$key] = $value;
				}
			}
		}
		$ilance->GPC['minute'] = serialize($newminute);
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "cron
			(cronid, nextrun, weekday, day, hour, minute, filename, loglevel, active, varname)
			VALUES
			(NULL,
			'" . TIMESTAMPNOW . "',
			'" . intval($ilance->GPC['weekday']) . "',
			'" . intval($ilance->GPC['day']) . "',
			'" . intval($ilance->GPC['hour']) . "',
			'" . addslashes($ilance->GPC['minute']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['filename']) . "',
			'" . intval($ilance->GPC['loglevel']) . "',
			'1',
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "')
		");
		
		print_action_success($phrase['_the_action_requested_was_completed_successfully'], $ilpage['settings'] . '?cmd=automation');
		exit();
	}
	
	// #### EDIT TASK ######################################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit' AND isset($ilance->GPC['cronid']))
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . intval($ilance->GPC['cronid']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql))
			{
				$minutes = stripslashes($res['minute']);
				$minutes = unserialize($minutes);
				
				#### MINUTES 1 #################################
				$res['minute1'] = '<select name="minute[0]" tabindex="1" class="input">';
				if (!isset($minutes[1]))
				{
					$res['minute1'] .= '<option value="-1" selected="selected">*</option>';
				}
				else
				{
					$res['minute1'] .= '<option value="-1">*</option>';
				}
				for ($m=0; $m<=59; $m++)
				{
					if (isset($minutes[0]) AND $minutes[0] == $m)
					{
						$res['minute1'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
					}
					else
					{
						$res['minute1'] .= '<option value="' . $m . '">' . $m . '</option>';
					}
				}					
				$res['minute1'] .= '</select>';
				
				#### MINUTES 2 #################################
				$res['minute2'] = '<select name="minute[1]" tabindex="1" class="input">';
				if (!isset($minutes[1]))
				{
					$res['minute2'] .= '<option value="-1" selected="selected">-</option>';
				}
				else
				{
					$res['minute2'] .= '<option value="-1">-</option>';
				}
				for ($m=0; $m<=59; $m++)
				{
					if (isset($minutes[1]) AND $minutes[1] == $m)
					{
						$res['minute2'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
					}
					else
					{
						$res['minute2'] .= '<option value="' . $m . '">' . $m . '</option>';
					}
				}					
				$res['minute2'] .= '</select>';
				
				#### MINUTES 3 #################################
				$res['minute3'] = '<select name="minute[2]" tabindex="1" class="input">';
				if (!isset($minutes[2]))
				{
					$res['minute3'] .= '<option value="-1" selected="selected">-</option>';
				}
				else
				{
				    $res['minute3'] .= '<option value="-1">-</option>';
				}
				for ($m=0; $m<=59; $m++)
				{
					if (isset($minutes[2]) AND $minutes[2] == $m)
					{
						$res['minute3'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
					}
					else
					{
						$res['minute3'] .= '<option value="' . $m . '">' . $m . '</option>';
					}
				}					
				$res['minute3'] .= '</select>';
				
				#### MINUTES 4 #################################
				$res['minute4'] = '<select name="minute[3]" tabindex="1" class="input">';
				if (!isset($minutes[3]))
				{
					$res['minute4'] .= '<option value="-1" selected="selected">-</option>';
				}
				else
				{
				    $res['minute4'] .= '<option value="-1">-</option>';
				}
				for ($m=0; $m<=59; $m++)
				{
					if (isset($minutes[3]) AND $minutes[3] == $m)
					{
						$res['minute4'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
					}
					else
					{
						$res['minute4'] .= '<option value="' . $m . '">' . $m . '</option>';
					}
				}					
				$res['minute4'] .= '</select>'; 
				
				#### HOURS #####################################
				$res['hours'] = '<select name="hour" id="sel_hour" tabindex="1" class="input">';
				if ($res['hour'] == '-1')
				{
					$res['hours'] .= '<option value="-1" selected="selected">*</option>';
				}
				else
				{
					$res['hours'] .= '<option value="-1">*</option>';
				}
				for ($h=0; $h<=23; $h++)
				{
					if (isset($res['hour']) AND $res['hour'] == $h)
					{
						$res['hours'] .= '<option value="' . $h . '" selected="selected">' . $h . '</option>';
					}
					else
					{
						$res['hours'] .= '<option value="' . $h . '">' . $h . '</option>';
					}
				}
				$res['hours'] .= '</select>';
				
				#### DAYS OF THE WEEK ##########################
				$res['dow'] = '<select name="weekday" id="sel_weekday" tabindex="1" class="input">';
				if ($res['weekday'] == '-1')
				{
					$res['dow'] .= '<option value="-1" selected="selected">*</option>';
				}
				else
				{
					$res['dow'] .= '<option value="-1">*</option>';
				}
				$days = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
				for ($dow=0; $dow<=6; $dow++)
				{
					$day = $days[$dow];
					$weekday = $phrase['_'.$day];
					if (isset($res['weekday']) AND $res['weekday'] == $dow)
					{
						$res['dow'] .= '<option value="' . $dow . '" selected="selected">'.$weekday.'</option>';
					}
					else
					{
						$res['dow'] .= '<option value="' . $dow . '">'.$weekday.'</option>';
					}
				}
				$res['dow'] .= '</select>';
				
				#### DAY OF THE MONTH ##########################
				$res['dom'] = '<select name="day" id="sel_day" tabindex="1" class="input">';
				if ($res['weekday'] == '-1')
				{
					if ($res['day'] == '-1')
					{
						$res['dom'] .= '<option value="-1" selected="selected">*</option>';
					}
					else
					{
						$res['dom'] .= '<option value="-1">*</option>';
					}
				}
				for ($dom=1; $dom<=31; $dom++)
				{
					if (isset($res['day']) AND $res['day'] == $dom)
					{
						$res['dom'] .= '<option value="' . $dom . '" selected="selected">' . $dom . '</option>';
					}
					else
					{
						$res['dom'] .= '<option value="' . $dom . '">' . $dom . '</option>';
					}
				}
				$res['dom'] .= '</select>';
				
				$savelog_1 = '';
				$savelog_0 = 'checked="checked"';
				if ($res['loglevel'] == 1)
				{
					$savelog_1 = 'checked="checked"';
					$savelog_0 = '';
				}
				
				$res['products_pulldown'] = $ilance->admincp->products_pulldown($res['product']);
				
				$tasks[] = $res;
			}
			
			$cronid = isset($ilance->GPC['cronid']) ? intval($ilance->GPC['cronid']) : 0;
			
			$pprint_array = array('buildversion','ilanceversion','login_include_admin','cronid','savelog_1','savelog_0','automationsettings','crontab','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			($apihook = $ilance->api('admincp_automation_edit_end')) ? eval($apihook) : false;
			
			$ilance->template->fetch('main', 'automation_edit.html', 1);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('v3nav','subnav_settings','tasks'));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	
	// #### SCHEDULED TASKS ################################################
	else
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "cron
			ORDER BY nextrun ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$count = 0;
			while ($res = $ilance->db->fetch_array($sql))
			{
				$nextrun = $ilance->datetime->fetch_datetime_from_timestamp($res['nextrun']);
				$res['nextrun'] = print_date($nextrun, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 1);
				$timerule = $ilance->admincp->fetch_cron_schedule($res);
				$res['minute'] = $timerule['minute'];
				$res['hour'] = $timerule['hour'];
				$res['day'] = $timerule['day'];
				$res['month'] = $timerule['month'];
				$res['day_of_week'] = $timerule['weekday'];
				$res['job'] = $res['filename'];
				
				if ($res['product'] == 'ilance' OR empty($res['product']))
				{
					$res['product'] = 'ILance';
				}
				else
				{
					$res['product'] = ucfirst($res['product']);
				}
				
				if ($show['ADMINCP_TEST_MODE'])
				{
					$res['action'] = '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil_gray.gif" border="0" alt="" /> &nbsp; <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" alt="" /></div>';
				}
				else
				{
					$res['action'] = '<div><a href="' . $ilpage['settings'] . '?cmd=automation&amp;subcmd=edit&amp;cronid=' . $res['cronid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a> &nbsp; <a href="' . $ilpage['settings'] . '?cmd=automation&amp;subcmd=remove&amp;cronid=' . $res['cronid'] . '" style="color:#990000" onclick="return confirm_js(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a></div>';
				}
				
				$res['class'] = ($count % 2) ? 'alt2' : 'alt1';
				$count++;
				$tasks[] = $res;
			}
		}
    
		$selected = isset($ilance->GPC['cronid']) ? intval($ilance->GPC['cronid']) : '';
		$tasks_pulldown = $ilance->admincp->print_scheduled_tasks_pulldown($selected);
		$products_pulldown = $ilance->admincp->products_pulldown();	
		
		// #### VIEWING TASK LOG ###########################################
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'view')
		{
			// filters
			$ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
			$ilance->GPC['cronid'] = intval($ilance->GPC['cronid']);
			$ilance->GPC['where'] = '';
			if ($ilance->GPC['cronid'] > 0)
			{
				$ilance->GPC['where'] = "AND varname = '".$ilance->admincp->fetch_task_varname($ilance->GPC['cronid'])."'";
			}
			
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
			{
				$ilance->GPC['page'] = 1;
			}
			else
			{
				$ilance->GPC['page'] = intval($ilance->GPC['page']);
			}
			
			$ilance->GPC['limit'] = ' ORDER BY ' . $ilance->db->escape_string($ilance->GPC['orderby']) . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilance->GPC['pp']).','.$ilance->GPC['pp'];
			
			$crontmp = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "cronlog WHERE cronlogid > 0 ".$ilance->GPC['where']);
			$ilance->GPC['totalcount'] = $ilance->db->num_rows($crontmp);
			$ilance->GPC['counter'] = ($ilance->GPC['page']-1)*$ilance->GPC['pp'];
			
			$cron = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "cronlog WHERE cronlogid > 0 ".$ilance->GPC['where']." ".$ilance->GPC['limit']);
			if ($ilance->db->num_rows($cron) > 0)
			{
				$count = 0;
				while ($res = $ilance->db->fetch_array($cron))
				{
					$res['varname'] = $ilance->admincp->scheduled_task_phrase($res['varname']);
					$res['class'] = ($count % 2) ? 'alt2' : 'alt1';
					$res['dateline'] = print_date($ilance->datetime->fetch_datetime_from_timestamp($res['dateline']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$cronlog[] = $res;
					$count++;
				}
			}
			
			$prevnext = print_pagnation($ilance->GPC['totalcount'], $ilance->GPC['pp'], $ilance->GPC['page'], $ilance->GPC['counter'], $ilpage['settings'] . '?cmd=automation&amp;do=view&amp;cronid='.$ilance->GPC['cronid'].'&amp;orderby='.$ilance->GPC['orderby']);
		}
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','products_pulldown','prevnext','tasks_pulldown','automationsettings','crontab','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_automation_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'automation.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','tasks','cronlog'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}	
}
    
// #### UPDATE GLOBAL SETTINGS HANLDER #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'globalupdate')
{
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-config-settings')
	{
		$ilance->admincp = construct_object('api.admincp');
		
		require_once(DIR_CORE . 'functions_attachment.php');

		foreach ($ilance->GPC['config'] AS $varname => $value)
		{
	 		$coin_list=$value;
			$numbers1 = explode(',', $value);
			$numbers2 = array_filter($numbers1);
			$numbers  =array_map('trim', $numbers2);
			$error    = 0;
			$ipaddress  = array();
			$inValidNumbers = array();
			foreach($numbers as $number) 
			{
	 			if(filter_var($number, FILTER_VALIDATE_IP))
				{
				  $ipaddress[] = $number;
				} else
				{
				  	$error++;                 
					array_push($inValidNumbers,$number);
				}
			}
			if($error != 0) 
			{
	 		    print_action_failed("Please Check seperate the ipaddress by comma OR You can Enter valid ipaddress .", $_SERVER['PHP_SELF']);exit();
			} 
	    }
	    $result = array_unique($ipaddress);
	    $final_ipaddress  = array();	 
		foreach($result as $ss_array)
		{
			$final_ipaddress[] = $ss_array;
		}
		$live_coin       = implode(",",$final_ipaddress);		
		foreach ($ilance->GPC['config'] AS $varname => $value)
		{
			if (isset($varname) AND $varname == 'attachment_dbstorage')
			{
				if (isset($value) AND $value == 0 AND $ilconfig['attachment_dbstorage'])
				{
					move_attachments_to_filepath();
				}
				else if (isset($value) AND $value == 1 AND $ilconfig['attachment_dbstorage'] == 0)
				{
					move_attachments_to_database();
				}
			}
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . $ilance->db->escape_string($value) . "',
				sort = '" . intval($ilance->GPC['sort'][$varname]) . "'
				WHERE name = '" . $ilance->db->escape_string($varname) . "'
			");
					
			$sql = $ilance->db->query("
				SELECT value, inputname
				FROM " . DB_PREFIX . "configuration
				WHERE name = '" . $ilance->db->escape_string($varname) . "'
					AND inputtype = 'pulldown'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				
				if ($res['inputname'] == 'timezones')
				{
					$writepulldown = $ilance->datetime->construct_timezone_pulldown('admin', $varname);
				}
				else if ($res['inputname'] == 'currencyrates')
				{
					$writepulldown = $ilance->currency->pulldown('admin', $varname);
				}
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET inputcode = '" . $ilance->db->escape_string($writepulldown) . "'
					WHERE name = '" . $ilance->db->escape_string($varname) . "'
				");
			}
			else
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . $ilance->db->escape_string($live_coin) . "'
					WHERE name = '" . $ilance->db->escape_string($varname) . "'
				");
			}
		}
	}
	
	print_action_success($phrase['_configuration_settings_have_been_saved_to_the_database'], $ilance->GPC['return']);
	exit();
}

// #### PAYMENT MODULES UPDATE HANDLER #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'paymodulesupdate')
{
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-config-settings')
	{
		foreach ($ilance->GPC['config'] AS $key => $value)
		{
			// are we updating the payment pulldown menu?
			if ($key == 'use_internal_gateway')
			{
				$sql = $ilance->db->query("
					SELECT id, value, inputname
					FROM " . DB_PREFIX . "payment_configuration
					WHERE name = '" . $ilance->db->escape_string($key) . "'
						AND inputtype = 'pulldown'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					if ($res['inputname'] == 'defaultgateway')
					{
						$writepulldown = $ilance->admincp->default_gateway_pulldown($value, $key);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "payment_configuration
							SET inputcode = '" . $ilance->db->escape_string($writepulldown) . "',
							value = '" . $ilance->db->escape_string($value) . "'
							WHERE name = '" . $ilance->db->escape_string($key) . "'
								AND inputtype = 'pulldown'
						");
					}
				}
			}
			else
			{
				if (isset($key) AND $key > 0)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "payment_configuration
						SET value = '" . $ilance->db->escape_string($value) . "',
						sort = '" . intval($ilance->GPC['sort'][$key]) . "'
						WHERE id = '" . intval($key) . "'
					");
				}
			}

		}
	}
	
	print_action_success($phrase['_payment_configuration_settings_saved'], $ilance->GPC['return']);
	exit();
}
else
{
	$area_title = $phrase['_global_configuration_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_global_configuration_menu'];
	
	$ilance->distance = construct_object('api.distance');
	
	($apihook = $ilance->api('admincp_global_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=global', $_SESSION['ilancedata']['user']['slng']);
	
	//arsath added staff settings on 01-oct-2010
	$global_securitysettings = $ilance->admincp->construct_admin_input('globalsecurity', $ilpage['settings'] . '?cmd=global');
	$global_filtersettings = $ilance->admincp->construct_admin_input('globalfilters', $ilpage['settings'] . '?cmd=global');
	$global_filterresults = $ilance->admincp->construct_admin_input('globalfilterresults', $ilpage['settings'] . '?cmd=global');
	$global_serverdistanceapi = $ilance->admincp->construct_admin_input('globalserverdistanceapi', $ilpage['settings'] . '?cmd=global');
	$global_serverlocale = $ilance->admincp->construct_admin_input('globalserverlocale', $ilpage['settings'] . '?cmd=global');
	$global_serversettings = $ilance->admincp->construct_admin_input('globalserversettings', $ilpage['settings'] . '?cmd=global');
	$global_search = $ilance->admincp->construct_admin_input('search', $ilpage['settings'] . '?cmd=global');
	$global_metatags = $ilance->admincp->construct_admin_input('metatags', $ilpage['settings'] . '?cmd=global');
	$global_seo = $ilance->admincp->construct_admin_input('globalseo', $ilpage['settings'] . '?cmd=global');
	$global_staff = $ilance->admincp->construct_admin_input('staffsettings', $ilpage['settings'] . '?cmd=global');
	
	// #### distance api installed countries list ##################
	$installedcountries = $ilance->distance->fetch_installed_countries();
	
	$pprint_array = array('global_staff','buildversion','ilanceversion','login_include_admin','global_seo','main3','main2','global_metatags','global_search','global_templatesettings','global_connectionsettings','global_auctionoptions','global_categoryoptions','dbadmin','global_serverossettings','global_serverliveupdate','global_serverpaths','global_serversettings','global_serverlocale','global_serverdistanceapi','global_securitysettings','global_templatesettings','global_filtersettings','global_filterresults','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','lanceads_header','lanceads_footer');
	
	($apihook = $ilance->api('admincp_global_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'global.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('clientnav_language_tabs','installedcountries'));
	if (!isset($clientnav_language_tabs))
	{
		$clientnav_language_tabs = array();
	}
	@reset($clientnav_language_tabs);
	while ($x = @each($clientnav_language_tabs))
	{
		$ilance->template->parse_loop('main', 'clientnav_languageid' . $x['value']['languageid']);
	}
	
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>