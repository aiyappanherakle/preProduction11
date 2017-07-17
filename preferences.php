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
        'portfolio',
        'preferences',
        'selling',
        'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
    'inline_edit',
	'jquery',
    'modal',
    'yahoo-jar',
	'flashfix'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'preferences'
);

// #### setup script location ##################################################
define('LOCATION', 'preferences');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[preferences]" => $ilcrumbs["$ilpage[preferences]"]);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	if (!empty($ilance->GPC['crypted']))
	{
		$uncrypted = decrypt_url($ilance->GPC['crypted']);
	}
        
        $ilance->subscription = construct_object('api.subscription');

	// #### REMOVE ATTACHMENTS #############################################
	if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_attachment-action' AND isset($ilance->GPC['attachid']))
        {
		if (isset($ilance->GPC['attachcmd']) AND $ilance->GPC['attachcmd'] == 'delete')
                {
			$area_title = $phrase['_attachment_removal_process'];
			$page_title = SITE_NAME . ' - ' . $phrase['_attachment_removal_process'];
			
                        $ilance->attachment = construct_object('api.attachment');
                        
			$attachids = $ilance->GPC['attachid'];
			if (isset($attachids) AND is_array($attachids))
			{
                                foreach ($attachids as $value)
                                {
					$ilance->attachment->remove_attachment($value, $_SESSION['ilancedata']['user']['userid']);
				}
			}
			print_notice($phrase['_attachments_successfully_removed'], $phrase['_you_have_successfully_removed_specified_attachments_from_your_account'], $ilpage['preferences'] . '?cmd=attachments', $phrase['_return_to_the_previous_menu']);
		}
	}
	
	/*// #### RENEW PASSWORD #################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-renewal')
        {
		$area_title = $phrase['_password_renewal_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_password_renewal_menu'];

		$headinclude .= '
<script type="text/javascript">
<!--
function validateSAForm(f)
{
        haveerrors = 0;
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>
';		
		$sql = $ilance->db->query("
                        SELECT secretquestion, secretanswer
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND secretquestion != '' AND secretanswer != ''
                        LIMIT 1
                ");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			$secret_question = stripslashes($res['secretquestion']);
			
			$ilance->template->fetch('main', 'preferences_password_renewal.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('secret_question','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
		}
		else 
		{
			// skip right to the password change template (no secret password to answer found)..
			$area_title = $phrase['_create_new_password'];
			$page_title = SITE_NAME . ' - ' . $phrase['_create_new_password'];

			$headinclude .= '
<script type="text/javascript">
<!--
function validateNewPWForm(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) 
        ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true)
        : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1)
        ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true)
        : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>
';			
			$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
		}
			
	}*/
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-change')
        {
		$area_title = $phrase['_renewing_password'];
		$page_title = SITE_NAME . ' - ' . $phrase['_renewing_password'];

		$sql_answer = $ilance->db->query("
                        SELECT password
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ");
		$sql_answer_result = $ilance->db->fetch_array($sql_answer);
                
		$password = $sql_answer_result['password'];
		$md5_password = md5($ilance->GPC['password']);
		
		/*if ($md5_password == $password)
                {*/
			$area_title = $phrase['_create_new_password'];
			$page_title = SITE_NAME . ' - ' . $phrase['_create_new_password'];

			$headinclude .= '

<script type="text/javascript">
function verifynotify(field1, field2, result_id, match_html, nomatch_html)
{
        this.field1 = field1;
        this.field2 = field2;
        this.result_id = result_id;
        this.match_html = match_html;
        this.nomatch_html = nomatch_html;
        this.check = function() 
        {
                if (!this.result_id) 
                {	 
                        return false; 
                }
                if (!document.getElementById)
                { 
                        return false; 
                }
                r = fetch_js_object(this.result_id);
                if (!r)
                { 
                        return false; 
                }

                if (this.field1.value != "" && this.field1.value == this.field2.value) 
                {
                    r.innerHTML = this.match_html;
                } 
                else 
                {
                    r.innerHTML = this.nomatch_html;
                }
        }
}

function validateNewPWForm(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
		 (f.password1.value.length < 1) ? showImage("password1error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password1error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}

</script>
';	

$js = '
<script type="text/javascript">
<!--
verify = new verifynotify();
verify.field1 = fetch_js_object(\'password1\');
verify.field2 = fetch_js_object(\'password2\');
verify.result_id = "password_result";
verify.match_html = "<span style=\"color:blue\"><img src=\"' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif\" border=\"0\" alt=\"\" /></span>";
verify.nomatch_html = "";
verify.check();
//-->
</script>
';            
	
  
			$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','js'));
			exit();
	/*}*/
		/*else
                {
			$area_title = $phrase['_renew_password_bad_secret_answer'];
			$page_title = SITE_NAME . ' - ' . $phrase['_renew_password_bad_secret_answer'];
                        
			print_notice($phrase['_bad_secret_answer'], $phrase['_you_have_supplied_the_wrong_answer_for_your_secret_question'], 'javascript:history.back()', $phrase['_back']);
		}*/
	}
        
	// #### RENEW PASSWORD HANDLER #########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-create' AND !empty($ilance->GPC['password']) AND !empty($ilance->GPC['password2']))
        {
		$area_title = $phrase['_renewing_password'];
		$page_title = SITE_NAME . ' - ' . $phrase['_renewing_password'];

		$md5_secret_answer = md5($ilance->GPC['password']);
		if ($md5_secret_answer == $secret_answer)
                {
			$area_title = $phrase['_create_new_password'];
			$page_title = SITE_NAME . ' - ' . $phrase['_create_new_password'];

			$headinclude .= '

<script type="text/javascript">
function verifynotify(field1, field2, result_id, match_html, nomatch_html)
{
        this.field1 = field1;
        this.field2 = field2;
        this.result_id = result_id;
        this.match_html = match_html;
        this.nomatch_html = nomatch_html;
        this.check = function() 
        {
                if (!this.result_id) 
                {	 
                        return false; 
                }
                if (!document.getElementById)
                { 
                        return false; 
                }
                r = fetch_js_object(this.result_id);
                if (!r)
                { 
                        return false; 
                }

                if (this.field1.value != "" && this.field1.value == this.field2.value) 
                {
                    r.innerHTML = this.match_html;
                } 
                else 
                {
                    r.innerHTML = this.nomatch_html;
                }
        }
}

function validateNewPWForm(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
		 (f.password1.value.length < 1) ? showImage("password1error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password1error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}

</script>
';	

$js = '
<script type="text/javascript">
<!--
verify = new verifynotify();
verify.field1 = fetch_js_object(\'password1\');
verify.field2 = fetch_js_object(\'password2\');
verify.result_id = "password_result";
verify.match_html = "<span style=\"color:blue\"><img src=\"' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif\" border=\"0\" alt=\"\" /></span>";
verify.nomatch_html = "";
verify.check();
//-->
</script>
';            
			
			$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','js'));
			exit();
		}
	}
	
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-password-create' AND !empty($ilance->GPC['password']) AND !empty($ilance->GPC['password1']) AND !empty($ilance->GPC['password2']))
        {
		$area_title = $phrase['_renewing_password'];
		$page_title = SITE_NAME . ' - ' . $phrase['_renewing_password'];
		$password1 = $ilance->GPC['password1'];
		$password2 = $ilance->GPC['password2'];
		$sql_password = $ilance->db->query("
                        SELECT password,salt
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ");
		$sql_password_result = $ilance->db->fetch_array($sql_password);
                
		$password =$sql_password_result['password'];
		$md5_password =md5(md5($ilance->GPC['password']).$sql_password_result['salt']);
		
		if ($md5_password != $password)
           {
		     $show['error_password']=true;
			}
		else
		{	
		   if (isset($password1) AND isset($password2) AND $password1 == $password2)
            {
				$area_title = $phrase['_creating_new_password'];
				$page_title = SITE_NAME . ' - ' . $phrase['_creating_new_password'];
	
				$salt = construct_password_salt($length = 5);
				$newpassword = md5(md5($password1) . $salt);
				
				$ilance->db->query("
									UPDATE " . DB_PREFIX . "users
									SET password = '".$ilance->db->escape_string($newpassword)."',
									salt = '".$ilance->db->escape_string($salt)."'
									WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
									LIMIT 1
							");
				
				$_SESSION['ilancedata']['user']['password'] = "";
				$_SESSION['ilancedata']['user']['password'] = $newpassword;
							
				print_notice($phrase['_password_sucessfully_changed'], $phrase['_you_have_successfully_changed_your_profile_password_information'], 'Preferences', $phrase['_preferences']);
				exit();
		    }
		
		else
                {
			 $show['error_password2']=true;
		}
		}
		$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
	}

	// #### TIME ZONE MANAGEMENT ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'timezone')
        {
		$area_title = $phrase['_time_zone_change_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_time_zone_change_menu'];

		$user_timezone = $_SESSION['ilancedata']['user']['timezoneid'];
		$user_timezone_dst = $_SESSION['ilancedata']['user']['timezonedst'];

		$user_time = $ilance->datetime->timezone_convert($ilconfig['globalserverlocale_officialtimezone'], DATETIME24HNODST, $user_timezone);
		$user_time = $ilance->datetime->fetch_timestamp_from_datetime($user_time);
		$user_time = gmstrftime($ilconfig['globalserverlocale_globaltimeformat'], $user_time);
                
		$timezone_pulldown  = $ilance->datetime->construct_user_timezone_pulldown();
		$timezone_dst_checkbox = '<input type="checkbox" name="user_timezone_dst" value="1" ';
		if (!empty($user_timezone_dst) AND $user_timezone_dst == 1)
                {
			$timezone_dst_checkbox .= 'checked="checked"';
		}
		$timezone_dst_checkbox .= ' />';
		
		$pprint_array = array('timezone_dst_checkbox','user_timezone','user_time','official_time','timezone','timezone_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'preferences_timezone.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### TIME ZONE HANDLER ##############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-timezone-change' AND !empty($ilance->GPC['usertimezone']))
        {
		$area_title = $phrase['_updating_time_zone_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_time_zone_preference'];

		if (!empty($ilance->GPC['usertimezone']))
                {
			$user_timezone = $ilance->GPC['usertimezone'];
			$user_timezone_dst = 0;
			if (isset($ilance->GPC['user_timezone_dst']) AND $ilance->GPC['user_timezone_dst'] == 1)
                        {
				$user_timezone_dst = $ilance->GPC['user_timezone_dst'];
			}

                        $_SESSION['ilancedata']['user']['timezoneid'] = $user_timezone;                        
                        $_SESSION['ilancedata']['user']['timezonedst'] = $user_timezone_dst;
			
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET timezoneid = '" . intval($ilance->GPC['usertimezone']) . "',
                                timezone_dst = '" . intval($user_timezone_dst) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
                        
			print_notice($phrase['_time_zone_sucessfully_changed'], $phrase['_you_have_successfully_changed_your_profile_time_zone_information'], $ilpage['preferences'] . '?cmd=timezone', $phrase['_return_to_the_previous_menu']);
                        exit();
		}
	}
	
	// #### NEWSLETTER #####################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'notifications')
        {
		$area_title = $phrase['_newsletter_preferences_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_newsletter_preferences_menu'];
                
                // #### define top header nav ##################################################
                $topnavlink = array(
                        'mycp',
                        'newsletters'
                );
                
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'newsletteropt_in') == 'no')
                {
			$area_title = $phrase['_access_denied_to_newsletter_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_newsletter_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . ' <a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>' . $phrase['_click_here'] . '</strong></a>', $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('newsletteropt_in'));
			exit();	
		}
		
		$headinclude .= "
<script type=\"text/javascript\">
<!--
function move_from_merge_to(divfrom, divto)
{
	// fetch the blocks
        var fromdiv = fetch_js_object(divfrom).innerHTML;
	var todiv = fetch_js_object(divto).innerHTML;
	
	// merge the blocks
	mergediv = fromdiv + todiv;
	fetch_js_object(divto).innerHTML = mergediv;
	fetch_js_object(divfrom).innerHTML = '';
	
	// reset the categories
	document.frames['category_iframe'].location.reload(true);
}
//-->
</script>
";
		$sql = $ilance->db->query("
			SELECT  notifyproducts, notifyproductscats, lastemailproductcats
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			// #### last email date ################################
			$lastitemsentdate = $res['lastemailproductcats'];
			
			if ($lastitemsentdate == '0000-00-00')
			{
				$lastitemsentdate = $phrase['_never'];
			}
			else
			{
				$lastitemsentdate = print_date($lastitemsentdate, '%b. %d, %Y', 0, 0);
			}
			

			// #### defaults for email opt-in confirmation #########			
			$notifyproducts_cb = ($res['notifyproducts']) ? '<input type="checkbox" name="notifyproducts" value="1" checked="checked" />' : '<input type="checkbox" name="notifyproducts" value="1" />';
			
			// #### existing category selections ###################
			$existingproduct ='';
			if (!empty($res['notifyproductscats']))
			{
				$temp = explode(',', $res['notifyproductscats']);
				$selectdcats = array();
				foreach ($temp AS $cid)
				{
					if ($cid > 0)
					{
						$selectdcats[] = $cid;
						$existingproduct .= '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories2_' . $cid . '" name="subcategories2[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive(intval($cid), 'product', $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\'subcategories2_' . $cid . '\').disabled=true;toggle_hide(\'hiderow_' . $cid . '\')" style="text-decoration:underline">' . $phrase['_remove'] . '</a>)</span></div>';
					}
				}
			}

		}
		//suku
		// murugan changes on feb18 for catelog order
		//$product_category_selection.='<select name="subcategories2[]"  multiple="multiple" size="12">';
		$product_category_selection.='<div style="overflow-y: auto; width:320px; height: 205px; border: 1px solid #E0DEDC;">';
		$topsql = $ilance->db->query("SELECT denomination_unique_no FROM " .DB_PREFIX. "catalog_toplevel ORDER BY denomination_sort");
		while($restop = $ilance->db->fetch_array($topsql))
		{
			$drop_sql=$ilance->db->query("SELECT coin_series_unique_no,coin_series_name FROM " .DB_PREFIX. "catalog_second_level WHERE coin_series_denomination_no = '".$restop['denomination_unique_no']."' order by coin_series_sort");
			
				while($drop_line=$ilance->db->fetch_array($drop_sql))
				{
					$product_category_selection.= '<div style="white-space: nowrap;">';

					if(isset($ilance->GPC['series']) and $ilance->GPC['series']>0 and $drop_line['coin_series_unique_no']==$ilance->GPC['series'])
						$product_category_selection.= '<input type="checkbox" name="subcategories2[]" value="'.$drop_line['coin_series_unique_no'].'" checked="checked" /> '.$drop_line['coin_series_name'];
					elseif(isset($selectdcats) AND is_array($selectdcats) AND count($selectdcats)>0 AND in_array($drop_line['coin_series_unique_no'],$selectdcats))
						$product_category_selection.= '<input type="checkbox" name="subcategories2[]" value="'.$drop_line['coin_series_unique_no'].'" checked="checked" /> '.$drop_line['coin_series_name'];
					else
						$product_category_selection.= '<input type="checkbox" name="subcategories2[]" value="'.$drop_line['coin_series_unique_no'].'" /> '.$drop_line['coin_series_name'];

					$product_category_selection.= '</div>';
				}
		}
$product_category_selection.='</div>';


		
		$pprint_array = array('product_category_selection','lastitemsentdate','existingproduct','product_newsletter','notifyproducts_cb','dynamic_newsletter_unselect','dynamic_newsletter_select','dynamic_newsletter_unselect2','dynamic_newsletter_select2','newsletter_category_select','newsletter_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('end_newsletter_preferences')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'preferences_newsletter.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### NEWSLETTER HANDLER #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-notifications-change')
        {
		$area_title = $phrase['_updating_newsletter_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_newsletter_preference'];
		
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'newsletteropt_in') == 'no')
                {
			$area_title = $phrase['_access_denied_to_newsletter_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_newsletter_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('newsletteropt_in'));
			exit();	
		}
		
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'product')
		{
			if (!empty($ilance->GPC['subcategories2']))
			{
				$subcats2 = '';
				for ($i = 0; $i < sizeof($ilance->GPC['subcategories2']); $i++)
				{
					if ($ilance->GPC['subcategories2'][$i] > 0)
					{
						$subcats2 .= intval($ilance->GPC['subcategories2'][$i]) . ',';
					}
				}
				//$subcats2 = ',' . $subcats2;
			}
			
			$notifyproducts = '0';
			if (isset($ilance->GPC['notifyproducts']) AND $ilance->GPC['notifyproducts'])
			{
				$notifyproducts = '1';
			}
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET notifyproductscats = '" . $ilance->db->escape_string($subcats2) . "',
				notifyproducts = '" . intval($notifyproducts) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
		}
		
		
		//nov29
       if ($ilconfig['globalauctionsettings_seourls'])
		{
		  $return_url = HTTP_SERVER .'Preferences/Notifications';
		}
		else
		{
		  $return_url = HTTP_SERVER . $ilpage['preferences'] . '?cmd=notifications';
		}
		
		($apihook = $ilance->api('newsletter_preferences_submit')) ? eval($apihook) : false;
		
		print_notice($phrase['_vendor_newsletter_options_changed'], $phrase['_you_have_successfully_changed_options_for_the_daily_vendor_newsletter_list'], $return_url, $phrase['_return_to_the_previous_menu']);
		exit();
	}

	// #### DISTANCE CALCULATION ###########################################
	else if (isset($ilance->GPC['cmd']) && $ilance->GPC['cmd'] == 'distance')
        {
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'distance') == 'no')
                {
			$area_title = $phrase['_access_denied_to_distance_calculation_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_distance_calculation_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('distance'));
			exit();	
		}
		
		$area_title = $phrase['_distance_calculation_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_distance_calculation_menu'];

		$distance_pulldown  = '<select name="distance" style="font-family: verdana">';
		$distance_pulldown .= '<option value="yes"'; if ($_SESSION['ilancedata']['user']['distance'] == '1') {
		$distance_pulldown .= ' selected="selected"'; }
		$distance_pulldown .= '>' . $phrase['_yes'] . '</option>';
		$distance_pulldown .= '<option value="no"'; if ($_SESSION['ilancedata']['user']['distance'] == '0') {
		$distance_pulldown .= ' selected="selected"'; }
		$distance_pulldown .= '>' . $phrase['_no'] . '</option>';
		$distance_pulldown .= '</select>';
		
		$ilance->template->fetch('main', 'preferences_distance.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('distance_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
		exit();
	}
	
	// #### DISTANCE CALCULATION HANDLER ###################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-distance-change' AND isset($ilance->GPC['distance']) AND $ilance->GPC['distance'] != '')
        {
		$area_title = $phrase['_updating_distance_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_distance_preference'];

		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'distance') == 'yes')
                {
			$area_title = $phrase['_access_denied_to_distance_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_distance_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('distance'));
			exit();	
		}
		
		$distance_calculation_enabled = $ilance->GPC['distance'];
		$_SESSION['ilancedata']['user']['distance'] = $distance_calculation_enabled;
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET project_distance = '" . intval($ilance->GPC['distance']) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		print_notice($phrase['_distance_calculation_options_changed'], $phrase['_you_have_successfully_changed_options_for_the_distance_calculation_profile_settings'], $ilpage['preferences'] . '?cmd=distance', $phrase['_return_to_the_previous_menu']);
		exit();
	}

	// #### EMAIL ##########################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'email')
        {
		$area_title = $phrase['_email_preference_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_email_preference_menu'];
                
                $topnavlink = array(
                        'mycp',
                        'preferencesemail'
                );
				
		//#############  EMAIL PREFERENCE	############################
		################################################################
		######  	EMAIL PREFERENCE						############
		######  Herakle Murugan Coding Nov 02 Starts Here 	############
		################################################################
				////id, related, outbid, wantlist, recommend, gccollection, itemtracked, gcoffer, user_id
		$email_pre = $ilance->db->query("SELECT * FROM  " . DB_PREFIX . "email_preference
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								");
		$email_prefer = $ilance->db->fetch_array($email_pre);
		
		$related = '<input type="checkbox" name="related_items" value="1" ';
		if($email_prefer['related'] == '1')
		{
			$related .= 'checked="checked"';
		}
		$related .= ' />Consignment Related, Items received';
		
		$outbid = '<input type="checkbox" name="outbid" value="1"';
		if($email_prefer['outbid'] == '1')
		{
			$outbid .= 'checked="checked"';
		}
		$outbid .= ' />Outbid Notices';
	
		$wantlist = '<input type="checkbox" name="wantlist" value="1"';
		if($email_prefer['wantlist'] == '1')
		{
			$wantlist .= 'checked="checked"';
		}
		$wantlist .= ' />Saved Search Reminders';
		 
		$recommend = '<input type="checkbox" name="recommeded" value="1"';
		if($email_prefer['recommend'] == '1')
		{
			$recommend .= 'checked="checked"';
		}
		$recommend .= ' />Recommendations';
		
		$gccollection = '<input type="checkbox" name="gccollection" value="1"';
		if($email_prefer['gccollection'] == '1')
		{
			$gccollection .= 'checked="checked"';
		}
		$gccollection .= ' />My GreatCollections';
	
		$itemtracked = '<input type="checkbox" name="itemtracked" value="1"';
		if($email_prefer['itemtracked'] == '1')
		{
			$itemtracked .= 'checked="checked"';
		}
		$itemtracked .= ' />Watchlist Reminders';
		 
		$gcoffer = '<input type="checkbox" name="gcoffer" value="1"';
		if($email_prefer['gcoffer'] == '1')
		{
			$gcoffer .= 'checked="checked"';
		}
		$gcoffer .= ' />Special Offers from GreatCollections';
		
		$bidconfirm = '<input type="checkbox" name="bidconfirm" value="1"';
		if($email_prefer['bidconfirm'] == '1')
		{
			$bidconfirm .= 'checked="checked"';
		}
		$bidconfirm .= ' />Bid Confirmation Notices';
		
		$dailydeal = '<input type="checkbox" name="dailydeal" value="1"';
		if($email_prefer['dailydeal'] == '1')
		{
			$dailydeal .= 'checked="checked"';
		}
		$dailydeal .= ' />24-Hour Deal Notification';
		
		//#############  EMAIL PREFERENCE	############################
		################################################################
		######  	EMAIL PREFERENCE						############
		######  Herakle Murugan Coding Nov 02 End Here	 	############
		################################################################

		$emailnotify = fetch_user('emailnotify', $_SESSION['ilancedata']['user']['userid']);
		
		$email_pulldown  = '<select name="notify" style="font-family: verdana"><option value="1"';
                if ($emailnotify == '1')
		{ 
			$email_pulldown .= ' selected="selected"';
		}
		$email_pulldown .= '>' . $phrase['_yes'] . '</option><option value="0"';
                if ($emailnotify == '0')
		{ 
			$email_pulldown .= ' selected="selected"';
		}
		$email_pulldown .= '>' . $phrase['_no'] . '</option></select>';
		
		$session_email = $_SESSION['ilancedata']['user']['email'];
		
		$pprint_array = array('dailydeal','bidconfirm','gcoffer','itemtracked','gccollection','recommend','wantlist','outbid','related','session_email','email_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

		$ilance->template->fetch('main', 'preferences_email.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### EMAIL HANDLER ##################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-email-change' AND isset($ilance->GPC['email']) AND !empty($ilance->GPC['email']))
        {
		$area_title = $phrase['_updating_email_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_email_preference'];

		$user_email = $ilance->GPC['email'];
                $actual_email = $_SESSION['ilancedata']['user']['email'];
                $notify = isset($ilance->GPC['notify']) ? intval($ilance->GPC['notify']) : 0;
               
                if ($user_email != $actual_email)
                {
                        // the user is actually changing existing email address
                        // final email checks (check mx record, check list of banned free emails, etc)
                        if ($ilance->common->is_email_banned(trim($user_email)))
                        {
                                // email appears to be banned
                                print_notice($phrase['_email_is_banned'], $phrase['_it_appears_this_email_address_is_banned_from_the_marketplace_please_try_another_email_address'], $ilpage['preferences'].'?cmd=email', $phrase['_return_to_the_previous_menu']);
                                exit();
                        }
                        else
                        {
                                // email is good check if it's duplicate
								$sql = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "users
								WHERE username = '".$ilance->db->escape_string($user_email)."' OR email = '".$ilance->db->escape_string($user_email)."'
								");
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        // error - email appears to already exist!
                                        print_notice($phrase['_duplicate_email_found'], $phrase['_it_appears_this_email_address_is_being_used_by_another_member_on_this_marketplace_please_try_another_email_address'], $ilpage['preferences'].'?cmd=email', $phrase['_return_to_the_previous_menu']);
                                        exit();
                                }
                                else
                                {
									
									
							// update existing session with new email preference
							$_SESSION['ilancedata']['user']['email'] = $user_email;

                          
                            $old_email_address = fetch_user('email',$_SESSION['ilancedata']['user']['userid']);
        
							// update email for user in db
							$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET email = '" . $ilance->db->escape_string($user_email) . "'
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							LIMIT 1
							");
                            //**************vijay work for email change start ************//
							$username =fetch_user('username',$_SESSION['ilancedata']['user']['userid']);
							$user_id = fetch_user('user_id',$_SESSION['ilancedata']['user']['userid']);
							$firstname = fetch_user('first_name',$_SESSION['ilancedata']['user']['userid']);
							$lastname = fetch_user('last_name',$_SESSION['ilancedata']['user']['userid']);
							$phone = fetch_user('phone',$_SESSION['ilancedata']['user']['userid']);
							$new_email_address = fetch_user('email',$_SESSION['ilancedata']['user']['userid']);
							


/* 									//developer email

									$ilance->email = construct_dm_object('email', $ilance);
									$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
									$ilance->email->logtype = 'Update Master Email Address';
									$ilance->email->slng = fetch_site_slng(1);
									$ilance->email->get('master_email_address_reset');
									$ilance->email->set(array(

									'{{username}}' => $username,		                        
									'{{user_id}}' => $user_id,
									'{{first_name}}' => $firstname,
									'{{last_name}}' => $lastname,
									'{{phone}}' => $phone,
									'{{old_emailaddress}}' => $old_email_address,
									'{{new_emailaddress}}' => $new_email_address,

									));

									$ilance->email->send();
 */
									//user email

									$ilance->email->mail = $new_email_address;
									$ilance->email->logtype = 'Update Master Email Address';
									$ilance->email->slng = fetch_site_slng(1);
									$ilance->email->get('master_email_address_reset');
									$ilance->email->set(array(

									'{{username}}' => $username,		                        
									'{{user_id}}' => $user_id,
									'{{first_name}}' => $firstname,
									'{{last_name}}' => $lastname,
									'{{phone}}' => $phone,
									'{{old_emailaddress}}' => $old_email_address,
									'{{new_emailaddress}}' => $new_email_address,

									));

									$ilance->email->send();


									$ilance->email->mail = $ilconfig['globalserversettings_siteemail'];
									$ilance->email->logtype = 'Update Master Email Address';
									$ilance->email->slng = fetch_site_slng(1);
									$ilance->email->get('master_email_address_reset');
									$ilance->email->set(array(

									'{{username}}' => $username,		                        
									'{{user_id}}' => $user_id,
									'{{first_name}}' => $firstname,
									'{{last_name}}' => $lastname,
									'{{phone}}' => $phone,
									'{{old_emailaddress}}' => $old_email_address,
									'{{new_emailaddress}}' => $new_email_address,

									));

									$ilance->email->send();

									//**************vijay work for email change finished ************//

                                }
                        }
                }
                
                // update global email preference for user
                $ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET emailnotify = '" . $notify . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		//#############  EMAIL PREFERENCE	############################
		################################################################
		######  	EMAIL PREFERENCE						############
		######  Herakle Murugan Coding Nov 02 Starts Here 	############
		################################################################
			$email_pre = $ilance->db->query("SELECT * FROM  " . DB_PREFIX . "email_preference
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								");
			if($ilance->db->num_rows($email_pre) > 0)
			{
			
			
			  $ilance->db->query("
			UPDATE " . DB_PREFIX . "email_preference
			SET related = '" . $ilance->GPC['related_items']. "',
			outbid = '" . $ilance->GPC['outbid']. "',
			wantlist = '" . $ilance->GPC['wantlist']. "',
			recommend = '" . $ilance->GPC['recommeded']. "',
			gccollection = '" . $ilance->GPC['gccollection']. "',
			itemtracked = '" . $ilance->GPC['itemtracked']. "',
			gcoffer = '" . $ilance->GPC['gcoffer']. "',
			bidconfirm = '" . $ilance->GPC['bidconfirm']. "',
			dailydeal = '" . $ilance->GPC['dailydeal']. "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
			}
			
			else
			{
			  $ilance->db->query("INSERT INTO " . DB_PREFIX . "email_preference
			   VALUES(
			   NULL,
			  '" . $ilance->GPC['related_items']. "',
			  '" . $ilance->GPC['outbid']. "',
			  '" . $ilance->GPC['wantlist']. "',
			  '" . $ilance->GPC['recommeded']. "',
			  '" . $ilance->GPC['gccollection']. "',
			  '" . $ilance->GPC['itemtracked']. "',
			  '" . $ilance->GPC['gcoffer']. "',
			  '" . $ilance->GPC['bidconfirm']. "',
			  '" . $ilance->GPC['dailydeal']. "',
			  '" . $_SESSION['ilancedata']['user']['userid'] . "')");
			}
		//#############  EMAIL PREFERENCE	############################
		################################################################
		######  	EMAIL PREFERENCE						############
		######  Herakle Murugan Coding Nov 02 End Here	 	############
		################################################################				
		
                print_notice($phrase['_email_preferences_changed'], $phrase['_you_have_successfully_changed_email_options_for_your_profile'], 'Preferences', $phrase['_return_to_the_previous_menu']);
                exit();
	}
	
	// #### START PAGE #####################################################
	else if (isset($ilance->GPC['cmd']) && $ilance->GPC['cmd'] == 'login')
        {
		$area_title = $phrase['_login_preference_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_login_preference_menu'];

		$sql_prefs = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ");
		$res_prefs = $ilance->db->fetch_array($sql_prefs);
		
                // this should be handled better..
		$startpage_pulldown  = '<select name="startpage" style="font-family: verdana">';
		$startpage_pulldown .= '<option value="main"'; if ($res_prefs['startpage'] == "main") { 
		$startpage_pulldown .= ' selected="selected"'; }
		$startpage_pulldown .= '>' . $phrase['_main_menu'] . '</option>';
		$startpage_pulldown .= '<option value="accounting"'; if ($res_prefs['startpage'] == "accounting") { 
		$startpage_pulldown .= ' selected="selected"'; }
		$startpage_pulldown .= '>' . $phrase['_accounting'] . '</option>';
		$startpage_pulldown .= '<option value="buying"'; if ($res_prefs['startpage'] == "buying") { 
		$startpage_pulldown .= ' selected="selected"'; }
		$startpage_pulldown .= '>' . $phrase['_buying_activity'] . '</option>';
		$startpage_pulldown .= '<option value="selling"'; if ($res_prefs['startpage'] == "selling") { 
		$startpage_pulldown .= ' selected="selected"'; }
		$startpage_pulldown .= '>' . $phrase['_selling_activity'] . '</option>';
		$startpage_pulldown .= '<option value="watchlist"'; if ($res_prefs['startpage'] == "watchlist") { 
		$startpage_pulldown .= ' selected="selected"'; }
		$startpage_pulldown .= '>' . $phrase['_watchlist'] . '</option>';
                $startpage_pulldown .= '</select>';
		
		$ilance->template->fetch('main', 'preferences_startpage.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('session_email','startpage_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
		exit();
	}
	
	// #### START PAGE HANDLER #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-startpage-change')
        {
		$area_title = $phrase['_updating_start_page_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_start_page_preference'];
                
		$ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET startpage = '" . $ilance->db->escape_string(mb_strtolower($ilance->GPC['startpage'])) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ");
		//nov-29		
		if ($ilconfig['globalauctionsettings_seourls'])
		{
		  $return_url = 'Preferences/Login';
		}
		else
		{
		  $return_url = $ilpage['preferences'] . '?cmd=login';
		}
                
		print_notice($phrase['_login_preferences_changed'], $phrase['_you_have_successfully_changed_start_page_login_preferences'], $return_url, $phrase['_return_to_the_previous_menu']);
		exit();
	}
	
	// ### IP RESTRICTION ##################################################
	else if (isset($ilance->GPC['cmd']) && $ilance->GPC['cmd'] == 'ip-restrict')
        {
		$area_title = $phrase['_ip_address_restriction_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_ip_address_restriction_menu'];

		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'iprestrict') == 'yes')
                {
			$sql_prefs = $ilance->db->query("
                                SELECT iprestrict, ipaddress
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
			$res_prefs = $ilance->db->fetch_array($sql_prefs);
			
			$iprestrict_pulldown  = '<select name="iprestrict" style="font-family: verdana">';
			$iprestrict_pulldown .= '<option value="yes"'; if ($res_prefs['iprestrict'] == '1') { 
			$iprestrict_pulldown .= ' selected="selected"'; }
			$iprestrict_pulldown .= '>' . $phrase['_yes'] . '</option>';
			$iprestrict_pulldown .= '<option value="no"'; if ($res_prefs['iprestrict'] == '0') { 
			$iprestrict_pulldown .= ' selected="selected"'; }
			$iprestrict_pulldown .= '>' . $phrase['_no'] . '</option>';
			$iprestrict_pulldown .= '</select>';
			
			$restrict_ipaddress = $res_prefs['ipaddress'];			
			if (empty($restrict_ipaddress))
                        {
				$restrict_ipaddress = getenv("REMOTE_ADDR");
			}
			
			$ilance->template->fetch('main', 'preferences_iprestrict.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('restrict_ipaddress','session_email','iprestrict_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
		}
		else
                {
			$area_title = $phrase['_access_denied_to_ip_restriction_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_ip_restriction_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('iprestrict'));
			exit();
		}
	}
	
	// ### IP RESTRICTION HANDLER ##########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-iprestrict-change')
        {
		$area_title = $phrase['_updating_ip_restriction_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_ip_restriction_preference'];

		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'iprestrict') == 'yes')
                {
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET iprestrict = '" . intval($ilance->GPC['iprestrict']) . "',
                                ipaddress = '" . $ilance->db->escape_string($ilance->GPC['ipaddress']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
                        
			print_notice($phrase['_login_preferences_changed'], $phrase['_you_have_successfully_changed_ip_address_restriction_login_preferences_for_your_profile'], $ilpage['preferences'] . '?cmd=ip-restrict', $phrase['_return_to_the_previous_menu']);
                        exit();
		}
		else
                {
			$area_title = $phrase['_access_denied_to_ip_restriction_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_ip_restriction_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource'] . " <a href='" . $ilpage['subscription'] . "'><strong>" . $phrase['_click_here'] . "</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('iprestrict'));
			exit();
		}
	}
	
	// #### LANGUAGE #######################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'language')
        {
		$area_title = $phrase['_language_preference_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_language_preference_menu'];

                $language_pulldown = '<select name="languageid" style="font-family: verdana">';

		$sql_langs = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                ");
		while ($res_langs = $ilance->db->fetch_array($sql_langs))
                {
			$sql_prefs = $ilance->db->query("
                                SELECT languageid
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ");
			$res_prefs = $ilance->db->fetch_array($sql_prefs);
			
			$language_pulldown .= '<option value="' . $res_langs['languageid'] . '"'; if ($res_langs['languageid'] == $res_prefs['languageid']) { 
			$language_pulldown .= ' selected="selected"'; }
			$language_pulldown .= '>' . stripslashes($res_langs['title']) . '</option>';
   		}
                $language_pulldown .= '</select>';
    	
		$ilance->template->fetch('main', 'preferences_language.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('language_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
		exit();
	}
	
	// #### LANGUAGE HANDLER ###############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-language-change')
        {
		$area_title = $phrase['_updating_language_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_language_preference'];

		$ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET languageid = '" . intval($ilance->GPC['languageid']) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ");
		
		$langdata = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($ilance->GPC['languageid']) . "'
                ");
		if ($ilance->db->num_rows($langdata) > 0)
                {
			$langinfo = $ilance->db->fetch_array($langdata);
			$_SESSION['ilancedata']['user']['languageid'] = $langinfo['languageid'];
			$_SESSION['ilancedata']['user']['languagecode'] = $langinfo['languagecode'];
			$_SESSION['ilancedata']['user']['slng'] = mb_substr($_SESSION['ilancedata']['user']['languagecode'] ? $_SESSION['ilancedata']['user']['languagecode'] : 'english', 0, 3);
		}
                
		print_notice($phrase['_language_preferences_changed'], $phrase['_you_have_successfully_changed_the_language_preference_for_your_profile'], $ilpage['preferences'] . '?cmd=language', $phrase['_return_to_the_previous_menu']);
                exit();
	}
	
	// #### CURRENCY #######################################################
	else if (isset($ilance->GPC['cmd']) && $ilance->GPC['cmd'] == 'currency')
        {
		$area_title = $phrase['_currency_preference_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_currency_preference_menu'];

		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
                {
                        $currency_pulldown = '<select name="currencyid" style="font-family: verdana">';
                        
			$sql_cur = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "currency
                        ");
			while ($res_cur = $ilance->db->fetch_array($sql_cur))
                        {
				$sql_prefs = $ilance->db->query("
                                        SELECT currencyid
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                ");
				$res_prefs = $ilance->db->fetch_array($sql_prefs);
				
				$currency_pulldown .= '<option value="' . $res_cur['currency_id'] . '"';
                                if ($res_cur['currency_id'] == $res_prefs['currencyid'])
                                { 
                                        $currency_pulldown .= ' selected="selected"';
                                }
				$currency_pulldown .= '>' . $res_cur['currency_name'] . ' (' . $res_cur['currency_abbrev'] . ')</option>';
			}
			$currency_pulldown .= '</select>';
			
			$ilance->template->fetch('main', 'preferences_currency.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main',   array('currency_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
		}
		else
                {
			$area_title = $phrase['_access_denied_to_currency_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_currency_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('enablecurrencyconversion'));
			exit();
		}
	}
	
	// #### CURRENCY HANDLER ###############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-currency-change')
        {
		$area_title = $phrase['_updating_currency_preference'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_currency_preference'];

		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == "yes")
                {
			$ilance->db->query("UPDATE " . DB_PREFIX . "users SET currencyid = '" . intval($ilance->GPC['currencyid']) . "' WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
			print_notice($phrase['_currency_preferences_changed'], $phrase['_you_have_successfully_changed_the_currency_preference_for_your_profile'], $ilpage['preferences'] . '?cmd=currency', $phrase['_return_to_the_previous_menu']);
                        exit();
		}
		else
                {
			$area_title = $phrase['_access_denied_to_currency_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_currency_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('enablecurrencyconversion'));
			exit();
		}
	}
	
	// #### ATTACHMENTS ####################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'attachments')
        {
		$show['widescreen'] = true;
		if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes')
                {
			$area_title = $phrase['_attachments_menu'];
			$page_title = SITE_NAME . ' - ' . $phrase['_attachments_menu'];
                        
                        $topnavlink = array(
                                'attachments'
                        );

                        $array = array("cmd" => "_attachment-action");
			$inputcrypted = encrypt_url($array);
			
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
                        {
                                $ilance->GPC['page'] = 1;
                        }
                        else
                        {
                                $ilance->GPC['page'] = intval($ilance->GPC['page']);
                        }
                        
			$limit = ' ORDER BY date DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
			
			$cntexe = $ilance->db->query("SELECT COUNT(*) AS number FROM " . DB_PREFIX . "attachment WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND visible = '1'");
			
			$cntarr = $ilance->db->fetch_array($cntexe);
			$number = $cntarr['number'];
			
			$SQL = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref, invoiceid
                                FROM " . DB_PREFIX . "attachment
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND visible = '1'
                                $limit
                        ";
			
			$sql_file_sum = $ilance->db->query("
                                SELECT SUM(filesize) AS attach_usage_total
                                FROM " . DB_PREFIX . "attachment
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
                        if ($ilance->db->num_rows($sql_file_sum) > 0)
                        {
                                $res_file_sum = $ilance->db->fetch_array($sql_file_sum);
                                $attach_usage_total = print_filesize($res_file_sum['attach_usage_total']);
			}
			else
			{
                                $attach_usage_total = 0;
                        }
            
			$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$row_count = 0;
                        
			$ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
                        
			$res = $ilance->db->query($SQL);
			if ($ilance->db->num_rows($res) > 0)
                        {
                                require_once(DIR_CORE . 'functions_attachment.php');
                                
				while ($row = $ilance->db->fetch_array($res))
                                {
					$row['attach_id'] = $row['attachid'];
					$row['attach_filename'] = $row['filename'];
					$row['attach_cat'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $row['category_id']);
                                        $row['attach_size'] = print_filesize($row['filesize']);
					$row['attach_type'] = fetch_attachment_type($row['attachtype'], $row['project_id']);
					$row['attach_views'] = $row['counter'];
					$row['attach_date'] = print_date($row['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$row['attach_action'] = '<input type="checkbox" name="attachid[]" value="' . $row['attachid'] . '" />';
                                        if ($row['invoiceid'] == '0')
                                        {
                                                $row['invoiceid'] = '-';
                                        }
                                        else
                                        {
                                                $row['invoiceid'] = '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['invoiceid'] . '">#' . $row['invoiceid'] . '</a></span>';
                                        }
					$row['class'] = ($row_count % 2) ? 'alt1' : 'alt2';
					$attachment_rows[] = $row;
					$row_count++;
				}
                                
				$show['no_attachment_rows'] = false;
			}
			else
                        {
				$show['no_attachment_rows'] = true;
			}
			
			$scriptpage = $ilpage['preferences'] . '?cmd=attachments';
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);
			
			if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
                        {
                                $ilance->GPC['page2'] = 1;
                        }
                        else
                        {
                                $ilance->GPC['page2'] = intval($ilance->GPC['page2']);
                        }
                        
			$limit2 = ' ORDER BY date_added DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
			
			$SQL3 = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND visible = '0'
                                $limit
                        ";
			
			$SQL4 = "
                                SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
                                FROM " . DB_PREFIX . "attachment
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND visible = '0'
                        ";
			
			$numberrows2 = $ilance->db->query($SQL4);
			$number2 = $ilance->db->num_rows($numberrows2);
			
			$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$row_count2 = 0;
			
			$result2 = $ilance->db->query($SQL3);
			if ($ilance->db->num_rows($result2) > 0)
                        {
                                require_once(DIR_CORE . 'functions_attachment.php');
                                
				while ($row2 = $ilance->db->fetch_array($result2))
                                {
					$row2['attach_id'] = $row2['attachid'];
					$row2['attach_filename'] = $row2['filename'];
					$row2['attach_type'] = fetch_attachment_type($row2['attachtype'], $row2['project_id']);
					$row2['status'] = $phrase['_review_in_progress'];
					$row2['actions'] = '<input type="checkbox" name="attachid[]" value="'.$row2['attachid'].'" />';
					$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$attachment_pending_rows[] = $row2;
					$row_count2++;
				}
                                
				$show['no_attachment_pending_rows'] = false;
			}
			else
                        {
				$show['no_attachment_pending_rows'] = true;
			}
			
			$attach_user_max = print_filesize($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'attachlimit'));
			$scriptpage2 = $ilpage['preferences'] . '?cmd=attachments';
                        
			$prevnext2 = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $scriptpage2, 'page2');
			
			$ilance->template->fetch('main', 'preferences_attachments.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('attachment_rows','attachment_pending_rows'));
                        $ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('inputcrypted','attach_user_max','attach_usage_total','prevnext','prevnext2','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
			exit();
		}
		else
                {
			$area_title = $phrase['_access_denied_to_attachment_resources'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied_to_attachment_resources'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('attachments'));
			exit();
		}
	}
	
	// #### PROFILE MANAGEMENT #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'profile')
	{
		$area_title = $phrase['_profile_update_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_profile_update_menu'];
                
                $topnavlink = array(
                        'mycp',
                        'preferencesprofile'
                );
		
		$ilance->registration_questions = construct_object('api.registration_questions');

                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'updateprofile') == 'no')
                {
                        $area_title = $phrase['_access_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
                        
			print_notice($phrase['_access_denied'], $phrase['_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource']." <a href='".$ilpage['subscription']."'><strong>".$phrase['_click_here']."</strong></a>", $ilpage['subscription'], ucwords($phrase['_click_here']), fetch_permission_name('updateprofile'));
			exit();        
                }
                
		$sql_user = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ");
                if ($ilance->db->num_rows($sql_user) > 0)
                {
                        $res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
			
                        $user_countryid = $res_user['country'];
                        $first_name = stripslashes($res_user['first_name']);
                        $last_name = stripslashes($res_user['last_name']);
                        $phone = $res_user['phone'];
                        $address = stripslashes($res_user['address']);
                        $address2 = stripslashes($res_user['address2']);
                        $city = stripslashes($res_user['city']);
                        $zipcode = $res_user['zip_code'];
                        $user_state = $res_user['state'];
                        
                        // current role (via session)
                        $roleid = intval($_SESSION['ilancedata']['user']['roleid']);
                        $regnumber = $vatnumber = $dnbnumber = $companyname = '';
                        
                        $sqlreg = $ilance->db->query("
                                SELECT regnumber, vatnumber, companyname, dnbnumber
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
                        if ($ilance->db->num_rows($sqlreg) > 0)
                        {
                                $resreg = $ilance->db->fetch_array($sqlreg, DB_ASSOC);
                                $regnumber = $resreg['regnumber'];
                                $vatnumber = $resreg['vatnumber'];
                                $dnbnumber = $resreg['dnbnumber'];
                                $companyname = stripslashes($resreg['companyname']);
                        }
                        
                        $sql_loc = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "locations
                                WHERE locationid = '" . intval($user_countryid) . "'
                        ");
                        if ($ilance->db->num_rows($sql_loc) > 0)
                        {
                                $res_loc = $ilance->db->fetch_array($sql_loc, DB_ASSOC);
                                $_SESSION['ilancedata']['user']['countryid'] = $res_loc['locationid'];
                        }
                        
                        if ($ilconfig['registrationdisplay_dob'])
                        {
                                $dateofbirth = $_SESSION['ilancedata']['user']['dob'];
                                $dobsplit = explode('-', $dateofbirth);
                                $year = $dobsplit[0];
                                $dobmonth = $dobsplit[1];
                                $dobday = $dobsplit[2];
                                
                                $month  = '<option value="01" '; if ($dobmonth == '01') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_january'] . '</option>';
                                $month .= '<option value="02" '; if ($dobmonth == '02') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_february'] . '</option>';
                                $month .= '<option value="03" '; if ($dobmonth == '03') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_march'] . '</option>';
                                $month .= '<option value="04" '; if ($dobmonth == '04') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_april'] . '</option>';
                                $month .= '<option value="05" '; if ($dobmonth == '05') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_may'] . '</option>';
                                $month .= '<option value="06" '; if ($dobmonth == '06') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_june'] . '</option>';
                                $month .= '<option value="07" '; if ($dobmonth == '07') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_july'] . '</option>';
                                $month .= '<option value="08" '; if ($dobmonth == '08') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_august'] . '</option>';
                                $month .= '<option value="09" '; if ($dobmonth == '09') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_september'] . '</option>';
                                $month .= '<option value="10" '; if ($dobmonth == '10') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_october'] . '</option>';
                                $month .= '<option value="11" '; if ($dobmonth == '11') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_november'] . '</option>';
                                $month .= '<option value="12" '; if ($dobmonth == '12') { $month .= 'selected="selected"'; } $month .= '>' . $phrase['_december'] . '</option>';
                                
                                $days = 1;
                                $day = '';
                                while ($days <= 31)
                                {
                                        if ($days < 10)
                                        {
                                                $day .= '<option value="0' . $days . '" '; if ($dobday == $days) { $day .= 'selected="selected"'; } $day .= '>' . $days . '</option>';
                                        }
                                        else
                                        {
                                                $day .= '<option value="' . $days . '" '; if ($dobday == $days) { $day .= 'selected="selected"'; } $day .= '>' . $days . '</option>';
                                        }
                                        $days++;
                                }
                        }
        
                        // construct countries / states pulldown
                        $jscity = $city;
                        $formid = 'forms[' . SEARCHBOXHEADER . ']';
			$countryid = fetch_country_id($_SESSION['ilancedata']['user']['country'], $_SESSION['ilancedata']['user']['slng']);
			$country_js_pulldown = construct_country_pulldown($countryid, $res_loc['location_' . $_SESSION['ilancedata']['user']['slng']], 'country', false, 'state');
			$state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $user_state, 'state') . '</div>';
                
                        // custom registration questions
                        $customquestions = $ilance->registration_questions->construct_register_questions(0, 'updateprofile', $_SESSION['ilancedata']['user']['userid']);
                        
                        // redirection?
                        $redirect = '';
                        if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
                        {
                                $redirect = handle_input_keywords($ilance->GPC['returnurl']);
                        }
                        else
                        {
                                if (isset($ilance->GPC['redirect']) AND !empty($ilance->GPC['redirect']))
                                {
                                        $redirect = handle_input_keywords($ilance->GPC['redirect']);    
                                }
                        }
			
			// #### gender #########################################
			if ($ilconfig['genderactive'])
			{
				if ($res_user['gender'] == '')
				{
					$cb_gender_undecided = 'checked="checked"';
					$cb_gender_male = '';
					$cb_gender_female = '';
				}
				else
				{
					if ($res_user['gender'] == 'male')
					{
						$cb_gender_undecided = '';
						$cb_gender_male = 'checked="checked"';
						$cb_gender_female = '';
					}
					else if ($res_user['gender'] == 'female')
					{
						$cb_gender_undecided = '';
						$cb_gender_male = '';
						$cb_gender_female = 'checked="checked"';
					}
				}
			}
                        
                        $pprint_array = array('cb_gender_undecided','cb_gender_male','cb_gender_female','dnbnumber','companyname','customquestions','redirect','dynamic_js_bodyend','regnumber','vatnumber','month','day','year','zipcode','first_name','last_name','phone','address','address2','city','dynamic_js_bodyend','state_js_pulldown','country_js_pulldown','language_pulldown','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                        
                        ($apihook = $ilance->api('start_edit_personal_profile')) ? eval($apihook) : false;
                        
                        $ilance->template->fetch('main', 'preferences_profile.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);                       
                        exit();        
                }
	}
	
        // #### PROFILE UPDATE HANDLER #########################################
        else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-profile-change')
        {
                $area_title = $phrase['_updating_profile_information'];
                $page_title = SITE_NAME . ' - ' . $phrase['_updating_profile_information'];
                
                ($apihook = $ilance->api('preferences_profile_change_start')) ? eval($apihook) : false;

		$sql_loc = $ilance->db->query("
                        SELECT locationid
                        FROM " . DB_PREFIX . "locations
                        WHERE location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "'
                        LIMIT 1
                ");
		$res_loc = $ilance->db->fetch_array($sql_loc, DB_ASSOC);
                
		$extraquery = '';
		
		// #### date of birth ##########################################
		if ($ilconfig['registrationdisplay_dob'] AND isset($ilance->GPC['year']) AND isset($ilance->GPC['month']) AND isset($ilance->GPC['day']))
		{
			$year = intval($ilance->GPC['year']);
			$month = intval($ilance->GPC['month']);
			$day = intval($ilance->GPC['day']);
                        
			$_SESSION['ilancedata']['user']['dob'] = $year . '-' . $month . '-' . $day;
			$extraquery .= "dob = '" . $year . "-" . $month . "-" . $day . "',";
		}
		
		// #### gender #################################################
		if ($ilconfig['genderactive'] AND isset($ilance->GPC['gender']) AND !empty($ilance->GPC['gender']))
		{
			$extraquery .= "gender = '" . $ilance->db->escape_string($ilance->GPC['gender']) . "',";
		}
		
		$ilance->GPC['zipcode'] = handle_input_keywords(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zipcode']))));
                
                // #### update user current session ############################
                $_SESSION['ilancedata']['user']['address'] = ucwords(stripslashes($ilance->GPC['address']));
                $_SESSION['ilancedata']['user']['address2'] = ucwords(stripslashes($ilance->GPC['address2']));
                $_SESSION['ilancedata']['user']['fulladdress'] = ucwords(stripslashes($ilance->GPC['address'])) . ' ' . ucwords(stripslashes($ilance->GPC['address2']));
                $_SESSION['ilancedata']['user']['postalzip'] = format_zipcode($ilance->GPC['zipcode']);
                $_SESSION['ilancedata']['user']['city'] = ucwords($ilance->GPC['city']);
                $_SESSION['ilancedata']['user']['state'] = ucwords($ilance->GPC['state']);
                $_SESSION['ilancedata']['user']['country'] = print_country_name($res_loc['locationid']);
                $_SESSION['ilancedata']['user']['countryid'] = $res_loc['locationid'];
		$_SESSION['ilancedata']['user']['countryshort'] = print_country_name($res_loc['locationid'], $_SESSION['ilancedata']['user']['slng'], true);
		
		set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['zipcode'])), true);
                
                ($apihook = $ilance->api('start_update_personal_profile')) ? eval($apihook) : false;    
        //murugan changes sep 28
		$old_addr = fetch_user('address',$_SESSION['ilancedata']['user']['userid']);
		$old_addr2 = fetch_user('address2',$_SESSION['ilancedata']['user']['userid']);
		$old_city = fetch_user('city',$_SESSION['ilancedata']['user']['userid']);
		$old_zip = fetch_user('zip_code',$_SESSION['ilancedata']['user']['userid']);
		$existing_addr = $old_addr.','.$old_addr2.','.$old_city.','.$old_zip;
		$ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                	SET first_name = '" . $ilance->db->escape_string($ilance->GPC['first_name']) . "',
                	last_name = '" . $ilance->db->escape_string($ilance->GPC['last_name']) . "',
                	phone = '" . $ilance->db->escape_string($ilance->GPC['phone']) . "',
                	address = '" . $ilance->db->escape_string($ilance->GPC['address']) . "',
                	address2 = '" . $ilance->db->escape_string($ilance->GPC['address2']) . "',
                	country = '" . $res_loc['locationid'] . "',
                	city = '" . $ilance->db->escape_string(ucwords($ilance->GPC['city'])) . "',
                	state = '" . $ilance->db->escape_string(ucwords($ilance->GPC['state'])) . "',
                        $extraquery
                        zip_code = '" . $ilance->db->escape_string($ilance->GPC['zipcode']) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ");
		// email to admin
			$username_new = fetch_user('username',$_SESSION['ilancedata']['user']['userid']);
			$new_addr = fetch_user('address',$_SESSION['ilancedata']['user']['userid']);
			$new_addr2 = fetch_user('address2',$_SESSION['ilancedata']['user']['userid']);
			$new_city = fetch_user('city',$_SESSION['ilancedata']['user']['userid']);
			$new_zip = fetch_user('zip_code',$_SESSION['ilancedata']['user']['userid']);
			$new_address = $new_addr.','.$new_addr2.','.$new_city.','.$new_zip;
			
		$ilance->email = construct_dm_object('email', $ilance);

				$ilance->email->mail = SITE_EMAIL;
				

				$ilance->email->slng = fetch_site_slng();

				$ilance->email->get('user_address_change');		

				$ilance->email->set(array(
				
				    '{{username}}' => $username_new,

					'{{existing_addr}}' =>$existing_addr,

					'{{new_addr}}' => $new_address,					

				));

				$ilance->email->send();

		// business registration numbers
                if (isset($ilance->GPC['companyname']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET companyname = '" . $ilance->db->escape_string($ilance->GPC['companyname']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
		}
                
		if (isset($ilance->GPC['regnumber']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET regnumber = '" . $ilance->db->escape_string($ilance->GPC['regnumber']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
		}

		if (isset($ilance->GPC['vatnumber']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET vatnumber = '" . $ilance->db->escape_string($ilance->GPC['vatnumber']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
		}
                
                // dnb number
                if (isset($ilance->GPC['dnbnumber']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET dnbnumber = '" . $ilance->db->escape_string($ilance->GPC['dnbnumber']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
		}
	
		// are we changing roles?
		if (!empty($ilance->GPC['roleid']))
		{
			// member is changing roles ..
			$_SESSION['ilancedata']['user']['roleid'] = intval($ilance->GPC['roleid']);
                        
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "subscription_user
                                SET roleid = '" . intval($ilance->GPC['roleid']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ");
		}
		
                // process custom registration questions
                if (!empty($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        $ilance->registration = construct_object('api.registration');
                        $ilance->registration->process_custom_register_questions($ilance->GPC['custom'], intval($_SESSION['ilancedata']['user']['userid']));
                }
                
		if (isset($ilance->GPC['redirect']) AND !empty($ilance->GPC['redirect']))
		{
			refresh($ilance->GPC['redirect']);
			exit();
		}
		//nov29
		if ($ilconfig['globalauctionsettings_seourls'])
		{
		  $return_url = 'Preferences/Profile';
		}
		else
		{
		  $return_url = $ilpage['preferences'] . '?cmd=profile';
		}
                
		print_notice($phrase['_profile_information_updated'], $phrase['_you_have_successfully_updated_information_for_your_personal_profile'], $return_url, $phrase['_return_to_the_previous_menu']);
		exit();
	}
        
        // #### MY FAVORITE SEARCHES ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'favorites')
	{
                $show['widescreen'] = true;
                
                $topnavlink = array(
                        'saved_searches'
                );
                
                // #### RECENTLY REVIEWED AUCTION BIT ##########################
                if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'recentlyreviewed')
                {
                        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'clearproductlist')
                        {
                                set_cookie('productauctions', '', false);
                                
                                if (isset($ilance->GPC['returnurl']))
                                {
                                        refresh(handle_input_keywords($ilance->GPC['returnurl']));
                                }
                                else
                                {
                                        refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
                                }
                                exit();
                        }
                        else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'clearservicelist')
                        {
                                set_cookie('serviceauctions', '', false);
                                
                                if (isset($ilance->GPC['returnurl']))
                                {
                                        refresh(handle_input_keywords($ilance->GPC['returnurl']));
                                }
                                else
                                {
                                        refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
                                }
                                exit();
                        }
                }
                
                // #### DELETE SAVED SEARCHES ##################################
                if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletesearches')
                {
                        if (!empty($ilance->GPC['searchid']) AND is_array($ilance->GPC['searchid']))
                        {
                                foreach ($ilance->GPC['searchid'] as $searchid)
                                {
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "search_favorites
                                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                        AND searchid = '" . intval($searchid) . "'
                                        ");
                                }
                        }
                        
                        refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
                        exit();     
                }
                
                // #### SAVE NEW FAVORITE SEARCH ###############################
                else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'save' AND !empty($ilance->GPC['fav']))
                { 
                	
				
				//sekar works on add new saved search on sep 27
                       if(isset($ilance->GPC['check']) AND $ilance->GPC['check'] == 'do')
						{
                        
						$unc[] = array('q' => $ilance->GPC['fav']);
						$unc[] = array('mode' => 'product');
						$unc[] = array('sort' => '01');
						$unc[] = array('budget' => '');
						$unc[] = array('bidrange' => '');
						$unc[] = array('radiuszip' => '');
						
						$ilance->GPC['verbose'] = '<div>Keywords: <strong><strong>'.$ilance->GPC['fav'].'</strong></strong></div>Max Price: <strong>Unlimited</strong>';
						}elseif(isset($ilance->GPC['ajax']) and $ilance->GPC['ajax']==1)
						{
							$unc[] = array('q' => '');
							$unc[] = array('mode' => 'product');
							$unc[] = array('sort' => '01');
							$unc[] = array('budget' => '');
							$unc[] = array('bidrange' => '');
							if(isset($ilance->GPC['pcgs']))
							$unc[] = array('pcgs' => intval($ilance->GPC['pcgs']));
							if(isset($ilance->GPC['series']))
							$unc[] = array('series' => intval($ilance->GPC['series']));
							if(isset($ilance->GPC['denomination']))
							$unc[] = array('denomination' => intval($ilance->GPC['denomination']));
							if(isset($ilance->GPC['ungraded']))
							$unc[] = array('ungraded' => intval($ilance->GPC['ungraded']));
							if(isset($ilance->GPC['frombid']))
							$unc[] = array('frombid' => intval($ilance->GPC['frombid']));
							if(isset($ilance->GPC['tobid']))
							$unc[] = array('tobid' => intval($ilance->GPC['tobid']));
							if(isset($ilance->GPC['grade_range_1']))
							$unc[] = array('grade_range_1' => intval($ilance->GPC['grade_range_1']));
							if(isset($ilance->GPC['grade_range_2']))
							$unc[] = array('grade_range_2' => intval($ilance->GPC['grade_range_2']));
							if(isset($ilance->GPC['grading_service']))
							$unc[] = array('grading_service' => $ilance->GPC['grading_service']);
								
						}
						else{
						$unc = urldecode($ilance->GPC['fav']);
                        $unc = unserialize($unc);			
						$unc[0]['q']=$ilance->GPC['keyword'];
						}
						// echo $ilance->GPC['fav'];
						// echo "<pre>";
						// print_r( $unc);
						// exit;
                        //sekar finished works on add new saved search on sep 27 
                        if (!empty($unc) AND is_array($unc))
                        {
                                $url = '';
                                foreach ($unc AS $value)
                                {
                                        if (is_array($value))
                                        {
                                                foreach ($value AS $search => $option)
                                                {
                                                        if ($search == 'sid')
                                                        {
														
														
                                                                if (is_array($option))
                                                                {
                                                                        foreach ($option AS $searchkey => $searchsel)
                                                                        {
                                                                                if (!empty($searchsel))
                                                                                {
                                                                                        $url .= '&amp;sid[' . $searchkey . ']=' . $searchsel;
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if (!empty($search) AND !empty($option))
                                                                {		
																		if ($search == 'grading_service')
																		{
																			foreach($option as $grading_service)
																				$url.='&amp;' . $search . '[]='.$grading_service;
																		}
                                                                        else if ($search == 'q')
                                                                        {
                                                                            $url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
                                                                            $unc['keywords'] = $option;
                                                                        }
                                                                        else if ($search == 'mode')
                                                                        {
                                                                            $url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
                                                                            $unc['cattype'] = $option;
                                                                        }
                                                                        else if ($search == 'denomination')
																		{
																			if(is_array($option))
																			{
																				foreach($option as $each_denomination)
																				{
																					$url.='&amp;' . $search . '[]='.$each_denomination;
																				}	
																			}else
																			{
																				$url.='&amp;' . $search . '='.$option;
																			}
																			
																		}else
																		{
																			$url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
																		}
                                                                }elseif (!empty($search) AND empty($option)){
                                                                		$url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
                                                                }
                                                        }
                                                }        
                                        }
                                }
                                
                                $ilance->GPC['verbose'] = '<div>Keywords: <strong><strong>'.$unc['keywords'].'</strong></strong></div>Max Price: <strong>Unlimited</strong>';
                                if (empty($ilance->GPC['title']))
                                {
                                        $unc['keywords'] = $phrase['_custom_search'];
                                }
                                else
                                {
                                        $unc['keywords'] = $ilance->GPC['title'];
                                }
                                
                                if (empty($unc['cattype']))
                                {
                                        $unc['keywords'] = 'service';
                                }

								$sqlo="
                                        INSERT INTO " . DB_PREFIX . "search_favorites
                                        (searchid, user_id, searchoptions, searchoptionstext, title,series_name,denomination_name, cattype, subscribed, added)
                                        VALUES
                                        (NULL,
                                        '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                        '" . $ilance->db->escape_string($url) . "',
                                        '" . $ilance->db->escape_string($ilance->GPC['verbose']). "',
                                        '" . $ilance->db->escape_string($unc['keywords']) . "',
										 '" . $ilance->db->escape_string($ilance->GPC['series_save_search']). "',
										 '" . $ilance->db->escape_string($ilance->GPC['save_search_denomination']). "',
                                        '" . $ilance->db->escape_string($unc['cattype']) . "',
                                        '1',
                                        '" . DATETIME24H . "')
                                ";
                                $ilance->db->query($sqlo);
                        }
                       	if(isset($ilance->GPC['ajax']) and $ilance->GPC['ajax']==1) 
                       	{
                       		echo "1";
                       		exit;
                       	}
                        refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
                        exit;
                
                }
				else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update_search' AND $_SESSION['ilancedata']['user']['searchid']  > 0 AND !empty($ilance->GPC['fav']))
                {
							
						
                        if(isset($ilance->GPC['check']) AND $ilance->GPC['check'] == 'do')
						{
						
                       
						$unc[] = array('q' => $ilance->GPC['fav']);
						$unc[] = array('mode' => 'product');
						$unc[] = array('sort' => '01');
						$unc[] = array('budget' => '');
						$unc[] = array('bidrange' => '');
						$unc[] = array('radiuszip' => '');
						
						$ilance->GPC['verbose'] = '<div>Keywords: <strong><strong>'.$ilance->GPC['fav'].'</strong></strong></div>Max Price: <strong>Unlimited</strong>';
						
						}
						else{
						
						
							$unc = urldecode($ilance->GPC['fav']);
							$unc = unserialize($unc);								
							
							
							
							
							if(!empty($ilance->GPC['grading_service']) || !empty($ilance->GPC['fromyear']) || !empty($ilance->GPC['toyear']) || !empty($ilance->GPC['grade_range_1']) || !empty($ilance->GPC['grade_range_2']) || !empty($ilance->GPC['frombid']) || !empty($ilance->GPC['tobid']) || !empty($ilance->GPC['listing_type']) || !empty($ilance->GPC['denom_all']) || !empty($ilance->GPC['denomination']) || !empty($ilance->GPC['q'])){
									
									
								
									
									$unc[0]['q']=$ilance->GPC['q'];
									
									//denomination
									if(!empty($ilance->GPC['denom_all']) || !empty($ilance->GPC['denomination'])){
									
										if(!empty($ilance->GPC['denom_all']) && empty($ilance->GPC['denomination'])){
										
											$unc[]['denomination']=$ilance->GPC['denomination'];
										}
										if(empty($ilance->GPC['denom_all']) && !empty($ilance->GPC['denomination'])){
										
											
											$unc[]['denomination']=$ilance->GPC['denomination'];
										}
										
									}
									else
									{
										$unc[]['denomination']='';
									}
									
									//grading company
									
									if(!empty($ilance->GPC['grading_service']) )
									{
										
										$unc[]['grading_service']=$ilance->GPC['grading_service'];
										
									}
									else
									{
										$unc[]['grading_service']='';
									}
									
									
									//year range
									if(!empty($ilance->GPC['fromyear']) || !empty($ilance->GPC['toyear'])){
									
										$temp=preg_split('#(?<=\d)[/+|\s|_|-]?(?=[a-z])#i', $ilance->GPC['fromyear']);
										$ilance->GPC['fromyear1']=$temp[0];
										$ilance->GPC['mintage']=$temp[1];
										
										$temp=preg_split('#(?<=\d)[/+|\s|_|-]?(?=[a-z])#i', $ilance->GPC['toyear']);
										$ilance->GPC['toyear1']=$temp[0];
										//$ilance->GPC['mintage']=$temp[1];
									
									
									
										if(!empty($ilance->GPC['fromyear']) && !empty($ilance->GPC['toyear'])){
										
											$unc[]['fromyear']=intval($ilance->GPC['fromyear1']);
											$unc[]['toyear']=intval($ilance->GPC['toyear1']);
										}
										if(!empty($ilance->GPC['fromyear']) && empty($ilance->GPC['toyear'])){
										
											$unc[]['fromyear']=intval($ilance->GPC['fromyear1']);
											$unc[]['toyear']=date("Y");
										}
										if(empty($ilance->GPC['fromyear']) && !empty($ilance->GPC['toyear'])){
											
											$unc[]['fromyear']='';
											$unc[]['toyear']=intval($ilance->GPC['toyear1']);
											
										}
										
										if(!empty($ilance->GPC['mintage']))
										{
											$unc[]['mintage']= $ilance->GPC['mintage'];
										}
										
									}
									
									
									//grade range
									
									
										
									if(!empty($ilance->GPC['grade_range_1']) && !empty($ilance->GPC['grade_range_2']) ){
									
										$gr_1 = ($ilance->GPC['grade_range_1'] =='1') ? 0 : $ilance->GPC['grade_range_1'];									
										$gr_2 = ($ilance->GPC['grade_range_2'] =='1') ? 0 : $ilance->GPC['grade_range_2'];										
										$gr_1 = ($gr_1 < $gr_2) ? $gr_1 : $gr_2;										
										$gr_2 = ($gr_2 > $gr_1) ? $gr_2 : $gr_1 ;										
									
										$unc[]['grade_range_1'] =$gr_1;
										$unc[]['grade_range_2'] =$gr_2;
									}
									
									
									
									//bid range
									
									if(!empty($ilance->GPC['frombid']) || !empty($ilance->GPC['tobid'])){
									
										$ilance->GPC['frombid']=intval($ilance->GPC['frombid']);
										$ilance->GPC['tobid']=intval($ilance->GPC['tobid']);
										if(!empty($ilance->GPC['frombid']) && !empty($ilance->GPC['tobid'])){
											
											$ilance->GPC['frombid'] = ($ilance->GPC['frombid'] < $ilance->GPC['tobid']) ? $ilance->GPC['frombid'] : $ilance->GPC['tobid'];		
											$ilance->GPC['tobid'] = ($ilance->GPC['tobid'] < $ilance->GPC['frombid']) ? $ilance->GPC['frombid'] : $ilance->GPC['tobid'] ;
											
											$unc[]['frombid'] =$ilance->GPC['frombid'];
											$unc[]['tobid'] =$ilance->GPC['tobid'];
											
											
										}
										
										if(!empty($ilance->GPC['frombid']) && empty($ilance->GPC['tobid'])){
											
											$unc[]['frombid'] =$ilance->GPC['frombid'];
											$unc[]['tobid'] ='';
											
										}
										if(empty($ilance->GPC['frombid']) && !empty($ilance->GPC['tobid'])){
											
											$unc[]['frombid'] ='';
											$unc[]['tobid'] =$ilance->GPC['tobid'];
										}
										
									}
									
									//listing type
									
									if(!empty($ilance->GPC['listing_type'])){
									
										$unc[]['listing_type'] =$ilance->GPC['listing_type'];
									}
									
									//price range
									
									if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
									{
										$unc[]['fromprice']=$ilance->GPC['fromprice'];
									}									
									
									if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
									{
										$unc[]['toprice']=$ilance->GPC['toprice'];
									}
									
									
								}
								else{
									$unc[0]['q']=$ilance->GPC['q'];
								}
							
							
							
						}
						
						
							// foreach($unc as $arr1){
								// if(is_array($arr1)){
									// foreach($arr1 as $arr2 => $value){
										
										// if(is_array($value1)){
											// foreach($value1 as $value2 => $value3 )
										// }
									// }
								// }
							// }
                       //sekar finished works on add new saved search on sep 27 
                        if (!empty($unc) AND is_array($unc))
                        {
                                $url = '';
                                foreach ($unc AS $value)
                                {
                                        if (is_array($value))
                                        {
                                                foreach ($value AS $search => $option)
                                                {
                                                        if ($search == 'sid')
                                                        {
                                                                if (is_array($option))
                                                                {
                                                                        foreach ($option AS $searchkey => $searchsel)
                                                                        {
                                                                                if (!empty($searchsel))
                                                                                {
                                                                                        $url .= '&amp;sid[' . $searchkey . ']=' . $searchsel;
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if (!empty($search) AND !empty($option))
                                                                {
                                                                        
																		if ($search == 'grading_service')
																		{
																		
																		foreach($option as $grading_service)
																				$url.='&amp;' . $search . '[]='.$grading_service;
																				
																				
																		}
																		else if ($search == 'q')
                                                                        {
                                                                                $url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
                                                                                $unc['keywords'] = $option;
                                                                        }
                                                                        else if ($search == 'mode')
                                                                        {
                                                                                $url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
                                                                                $unc['cattype'] = $option;
                                                                        }
                                                                        else
                                                                        {
																
																			if ($search == 'denomination')
																			{
																			
																			foreach($option as $each_denomination)
																					$url.='&amp;' . $search . '[]='.$each_denomination;
																					
																					//$unc['keywords'] = $option;
																			}else
																			{
																			$url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
																			}
																		
                                                                        }
                                                                }
                                                        }
                                                }        
                                        }
                                }
                                $ilance->GPC['verbose'] = '<div>Keywords: <strong><strong>'.$unc['keywords'].'</strong></strong></div>Max Price: <strong>Unlimited</strong>';
                                if (empty($ilance->GPC['title']))
                                {
                                        $unc['keywords'] = $phrase['_custom_search'];
                                }
                                else
                                {
                                        $unc['keywords'] = $ilance->GPC['title'];
                                }
                                
                                if (empty($unc['cattype']))
                                {
                                        $unc['keywords'] = 'service';
                                }

								
								
						  
								 $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "search_favorites
                                                SET searchoptions = '" . $ilance->db->escape_string($url) . "',
												searchoptionstext = '" . $ilance->db->escape_string($ilance->GPC['verbose']). "',
												title =  '" . $ilance->db->escape_string($unc['keywords']) . "',
												series_name =  '" . $ilance->db->escape_string($ilance->GPC['series_save_search']). "',
												denomination_name =  '" . $ilance->db->escape_string($ilance->GPC['save_search_denomination']). "'
                                                WHERE searchid = '" . $_SESSION['ilancedata']['user']['searchid'] . "'                                  
                                        ");
                                                        
                        
                    
                        }
                        
                        refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
                        exit;
                }
				
                $area_title = $phrase['_my_favorite_searches'];
		$page_title = SITE_NAME . ' - ' . $phrase['_my_favorite_searches'];
                
                // inline auction ajax controls
                $headinclude .= "
<script type=\"text/javascript\">
<!--
var searchid = 0;
var value = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function reset_image()
{
        imgtag.src = favoriteicon;
}
function fetch_response()
{
        if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
        {
                response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
                phpstatus = xmldata.fetch_data(response);
                
                searchiconsrc = fetch_js_object('inline_favorite_' + xmldata.searchid).src;
                status = searchiconsrc.match(/\/unchecked.gif/gi);
                if (status == '/unchecked.gif')
                {
                       status = 'unchecked';
                }
                else
                {
                       status = 'checked';
                }                                
                if (status == 'unchecked')
                {
                        if (phpstatus == 'on' || phpstatus == 'off')
                        {
                                favoriteiconsrc = fetch_js_object('inline_favorite_' + xmldata.searchid).src;
                                imgtag = fetch_js_object('inline_favorite_' + xmldata.searchid);
                                
                                favoriteicon2 = favoriteiconsrc.replace(/unchecked.gif/gi, 'working.gif');
                                imgtag.src = favoriteicon2;
                                
                                favoriteicon = favoriteiconsrc.replace(/unchecked.gif/gi, 'checked.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert(phpstatus);
                        }
                }
                else if (status == 'checked')
                {
                        if (phpstatus == 'on' || phpstatus == 'off')
                        {
                                favoriteiconsrc = fetch_js_object('inline_favorite_' + xmldata.searchid).src;
                                imgtag = fetch_js_object('inline_favorite_' + xmldata.searchid);
                                
                                favoriteicon2 = favoriteiconsrc.replace(/checked.gif/gi, 'working.gif');
                                imgtag.src = favoriteicon2;
        
                                favoriteicon = favoriteiconsrc.replace(/checked.gif/gi, 'unchecked.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert(phpstatus); 
                        }
                }
                xmldata.handler.abort();
        }
}
function update_favorite(searchid)
{                        
        xmldata = new AJAX_Handler(true);
        
        searchid = urlencode(searchid);
        xmldata.searchid = searchid;
        
        searchiconsrc = fetch_js_object('inline_favorite_' + searchid).src;
        status = searchiconsrc.match(/\/unchecked.gif/gi);
        if (status == '/unchecked.gif')
        {
               value = 'on';
        }
        else
        {
               value = 'off';
        }
        xmldata.onreadystatechange(fetch_response);
        xmldata.send('ajax.php', 'do=searchfavorites&value=' + value + '&searchid=' + searchid + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}

var urlBase = 'ajax.php?do=inlineedit&action=favsearchtitle&id=';

//-->
</script>
";                
                $show['no_favorites'] = false;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "search_favorites
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ORDER BY searchid DESC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $row_count = 0;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                //$searchoptions = fetch_searchoption_url($res['searchoptions']);
                                $searchoptions = stripslashes($res['searchoptions']);
                                // remove first &amp; from beginning of string
                                $searchoptions = mb_substr($searchoptions, 5);
                                
                                $res['searchoptionstext'] = stripslashes($res['searchoptionstext']);
                                $res['action'] = '<input type="checkbox" name="searchid[]" value="' . $res['searchid'] . '" />';
                                if ($res['subscribed'])
                                {
                                        $res['ajax_subscribed'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="inline_favorite_'.$res['searchid'].'" onclick="update_favorite('.$res['searchid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />';
                                }
                                else
                                {
                                        $res['ajax_subscribed'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="'.$phrase['_click_to_enable_disable'].'" border="0" id="inline_favorite_'.$res['searchid'].'" onclick="update_favorite('.$res['searchid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />';
                                }
                                
                                if ($res['cattype'] == 'service')
                                {
                                        $res['cattype'] = $phrase['_service'];
                                }
                                else
                                {
                                        $res['cattype'] = ucfirst($res['cattype']);
                                }
                                $date1split = explode(' ', $res['added']);
                                $date2split = explode('-', $date1split[0]);
                                $totaldays = 3600;
                                $elapsed = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
                                $days = ($totaldays - $elapsed);
                                if ($days < 0)
                                {
                                        // somehow the cron job did not expire the save search subscription for this member
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "search_favorites
                                                SET subscribed = '0'
                                                WHERE searchid = '" . $res['searchid'] . "'
                                                LIMIT 1
                                        ");
                                        $res['daysleft'] = '<span id="daysleft_' . $res['searchid'] . '">' . $phrase['_ended'] . '</span>';
                                        
                                        if ($res['lastsent'] == '0000-00-00 00:00:00')
                                        {
                                                $res['lastsent'] = $phrase['_never'];
                                        }
                                        else
                                        {
                                                $res['lastsent'] = print_date($res['lastsent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        }
                                }
                                else
                                {
                                        if ($res['subscribed'])
                                        {
                                                $res['daysleft'] = '<span id="daysleft_' . $res['searchid'] . '">' . $days . ' ' . $phrase['_days_left'] . '</span>';
                                                if ($res['lastsent'] == '0000-00-00 00:00:00')
                                                {
                                                        $res['lastsent'] = $phrase['_never'];
                                                }
                                                else
                                                {
                                                        $res['lastsent'] = print_date($res['lastsent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                }
                                        }
                                        else
                                        {
                                                $res['daysleft'] = '<span id="daysleft_' . $res['searchid'] . '">-</span>';
                                                if ($res['lastsent'] == '0000-00-00 00:00:00')
                                                {
                                                        $res['lastsent'] = $phrase['_never'];        
                                                }
                                                else
                                                {
                                                        $res['lastsent'] = print_date($res['lastsent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                }
                                        }
                                }
                                
								/* jai start radiuszip remove */
								$new_array=explode("&",$searchoptions);
                                $last_val=end($new_array);
                                 if(strstr($last_val,"radiuszip") != "")
                                 {
                                    array_pop($new_array);

                                  }
                                $new_val = implode("&",$new_array);
								
								/* jai end radiuszip remove */
								$stored_keyword_cunks=explode('&amp;',$res['searchoptions']);
								foreach($stored_keyword_cunks as $allpieces)
								{
								$keyword_pieces=explode("=",$allpieces);
								foreach($keyword_pieces as $key=>$term)
								{
								$chunks[$key]=$term;
								}
								}
                                $res['title'] = str_replace('"', "&#34;", $res['title']);
                                $res['title'] = str_replace("'", "&#39;", $res['title']);
                                $res['title'] = str_replace("<", "&#60;", $res['title']);
                                $res['title'] = str_replace(">", "&#61;", $res['title']);
                               /* $res['title'] = '<div id="favorite_searchid_' . $res['searchid'] . '" onmouseover="return toggle_show(\'' . $res['searchid'] . '_edit\')" onmouseout="return toggle_hide(\'' . $res['searchid'] . '_edit\')"><strong><span id="phrase' . $res['searchid'] . 'inline" title="' . $phrase['_doubleclick_to_edit'] . '"><span ondblclick="do_inline_edit(' . $res['searchid'] . ', this);">' . $res['title'] . '</span></span></strong><span style="display:none" id="' . $res['searchid'] . '_edit" class="smaller litegray">&nbsp;&nbsp;&nbsp;(' . $phrase['_doubleclick_to_edit'] . ')</span></div>';*/
							   $res['title'] = '<div id="favorite_searchid_' . $res['searchid'] . '"  style="font-weight:bold;">' . $res['title'] . '</div>';
							   
							   
							   
                                $res['edit'] = '<div class="smaller gray" style="padding-top:3px">' . $phrase['_added'] . ' ' .  print_date($res['added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 1, 0) . '<a href="' . HTTP_SERVER . $ilpage['search'] . '?' . $new_val . '&amp;searchid=' . $res['searchid'] . '&edit=1"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif'.'" ><a></div>';
                                $res['goto'] = '<a href="' . HTTP_SERVER . $ilpage['search'] . '?' . $new_val . '&amp;searchid=' . $res['searchid'] . '">' . $phrase['_go_to_search_results'] . '</a>';
                                $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                $row_count++;
                                $favorites[] = $res;
                        }
                }
                else
                {
                        $show['no_favorites'] = true;
                }
                
                $returnurl = '';
                if (!empty($ilance->GPC['returnurl']))
                {
                        $returnurl = HTTP_SERVER . $ilance->GPC['returnurl'];
                }
				
						//sekar works on popup for add new saved searches on sep 27
				
				$popup_new = '
				<span class="smaller gray">[ <span class="blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow();" onmouseover="Tip(\'<div><strong>' . $phrase['_save_this_search'] . '</strong></div><div>' . $phrase['_saved_searches_can_be_used_when_you_are_viewing_search_results_from_the_marketplace'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $phrase['_add_new_favorite_search'] . '</a></span> ]</span>';
                $headinclude.='<script>
				function saved_search_modal(searchid,title,keyword)
				{
				alert(keyword);
				}
				</script>';
		$pprint_array = array('returnurl','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','popup_new');
		
                $ilance->template->fetch('main', 'preferences_favorites.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_loop('main', 'favorites');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();                
        }
	
        // #### MY FAVORITE SEARCHES ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'edit_saved_search')
	{

	$title = $ilance->GPC['title'];	
	$key = $ilance->GPC['keyword'];
	$title_array = explode(" ", $key);	
	$title_splitadd = implode("+", $title_array);


    $keyword = '<div>Keywords: <strong><strong>'.$ilance->GPC['keyword'].'</strong></strong></div>Max Price: <strong>Unlimited</strong>';
	
	
  $url.= '&amp;'.'q='.$title_splitadd.'&amp;'.'mode=product'.'&amp;'.'sort=01'.'&amp;'.'searchid='.$ilance->GPC['searchid'];
	 
	
	 $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "search_favorites
                                                SET title ='".$ilance->GPC['title']."' 
												, searchoptions = '" . $ilance->db->escape_string($url) . "'	
                                                ,searchoptionstext ='".$keyword."'												
                                                WHERE searchid = '" . $ilance->GPC['searchid'] . "'                                  
                                        ");
                                                  
	
	                  refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
                        exit;
	
    }	
	
	
		else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'promotions')
        {
		$area_title = 'My Promotions';
		$page_title = SITE_NAME . ' - ' . 'My Promotions';

		
		$show['no_promocode'] = false;
                $row_count = 0;
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "promo_inventory
						WHERE (userID = '".$_SESSION['ilancedata']['user']['userid']."' OR userID = '')
                        ORDER BY promoID DESC
                ");
				
				
				if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['promocode'] = true;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
								                                                    
								if($res['offerType']=='dollar')
								{								  
								   $res['offerType'] = '$';
								}
								if($res['offerType']=='percentage')
								{
								   $res['offerType'] = '%';
								}
								if($res['validDate'] == '0000-00-00')
								{
								   $res['validDate'] = 'No Limit';
								}
								if($res['validDate'] != '0000-00-00')
								{
								$dobsplit = explode('-', $res['validDate']);
                                $year = $dobsplit[0];
                                $month = $dobsplit[1];
                                $day = $dobsplit[2];
								$res['validDate'] = $month . '-' . $day . '-' .$year;
								}
								
								if($res['itemID'] == '0')
								{
								   $res['itemID'] = '--';
								}
								if($res['categoryID'] == '0')
								{
								   $res['categoryID'] = '--';
								}                         	
							
                                $promocode[] = $res;
                                $row_count++;
                        }        
                }
				else
				{
				
				$show['no_promocode'] = false;
				}
			
		$pprint_array = array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'preferences_promotions.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('promocode'));
        $ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = $phrase['_preferences_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_preferences_menu'];
		
		$pprint_array = array('distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

		$ilance->template->fetch('main', 'preferences.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['preferences'] . print_hidden_fields(true, array(), true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
