<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1943
|| # -------------------------------------------------------------------- # ||
|| # Customer License # DPIzbreDT-vNiTuW=AuzteStDb2sio-n--kbWLg-MYBvswO-yG
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
        'registration',
        'preferences',
        'subscription',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
	'flashfix',
	'jquery'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'registration'
);

// #### setup script location ##################################################
define('LOCATION', 'registration');

// #### require backend ########################################################
require_once('./functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[registration]" => $ilcrumbs["$ilpage[registration]"]);
$_SESSION['ilancedata']['user']['roleid']='3';
$area_title = $phrase['_user_registration'];
$page_title = SITE_NAME . ' - ' . $phrase['_user_registration'];

($apihook = $ilance->api('registration_start')) ? eval($apihook) : false;

// #### REDIRECTION HANDLER ####################################################
if (isset($ilance->GPC['redirect']) AND !empty($ilance->GPC['redirect']))
{
    $ilance->GPC['redirect'] = strip_tags($ilance->GPC['redirect']);
}

// #### Sucess message ##################################
if (isset($ilance->GPC['msg']) AND $ilance->GPC['msg'] == 'successful')
{
    print_notice($phrase['_registration_complete'], $phrase['_thank_you_your_registration_is_now_complete'].''.$scriptgoogle.''.$scriptgoogle1, $ilpage['main'] . '?cmd=cp', $phrase['_my_cp']);
    exit;
}
// #### new member email verification process ##################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'activate')
{
    if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'resend')
    {
        // member requests that ilance resend their email link code verification
        if (!empty($ilance->GPC['email']))
        {
            // resend email activation code to member
            $ilance->registration = construct_object('api.registration');
            
            if ($ilance->registration->send_email_activation($ilance->GPC['email']))
            {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?error=checkemail');
                exit();
            }
            else
            {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
                exit();
            }
        }
        else
        {
            refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
            exit();
        }
    }
    else
    {
        // member appears to be validating his/her registration
        if (!empty($ilance->GPC['u']))
        {
            $ilance->GPC['u'] = $ilance->crypt->three_layer_decrypt($ilance->GPC['u'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
            
            $sql = $ilance->db->query("
                    SELECT *
                    FROM " . DB_PREFIX . "users 
                    WHERE user_id = '" . intval($ilance->GPC['u']) . "'
                            AND status = 'unverified'
                    LIMIT 1
            ");
            if ($ilance->db->num_rows($sql) > 0)
            {
                $user = $ilance->db->fetch_array($sql, DB_ASSOC);
                
                // does admin manually verify new members before they can login?
                $status = ($ilconfig['registrationdisplay_moderation']) ? 'moderated' : 'active';
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET status = '" . $status . "'
                        WHERE user_id = '" . intval($ilance->GPC['u']) . "'
                ");

                $ilance->email = construct_dm_object('email', $ilance);
                    
                // if we are active, send new email to user
                if ($status == 'active')
                {
                    // if an account credit bonus was active we should dispatch that email to new user now
                    // and update his account balance with new credit accordingly
                    $registerbonus = '0.00';
                    if ($ilconfig['registrationupsell_bonusactive'])
                    {
                        // lets construct a little payment bonus for new member, we will:
                        // - create a transaction and send email to user and admin
                        // - return the bonus amount so we can update the users account
                        $registerbonus = construct_account_bonus($user['user_id'], $status);
                        if ($registerbonus > 0)
                        {
                            // update register bonus credit to online account data
                            $ilance->db->query("
                                    UPDATE " . DB_PREFIX . "users
                                    SET total_balance = total_balance + $registerbonus,
                                    available_balance = available_balance + $registerbonus
                                    WHERE user_id = '" . $user['user_id'] . "'
                            ");
                        }
                    }
	
            		$categories = '';			
            		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
            		{
            			$getcats = $ilance->db->query("
            				SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
            				FROM " . DB_PREFIX . "categories
            				WHERE parentid = '0'
            					AND cattype = 'product'
            					AND visible = '1'
            				ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
            				LIMIT 10
            			", 0, null, __FILE__, __LINE__);
            			if ($ilance->db->num_rows($getcats) > 0)
            			{
            				while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
            				{
            					$categories .= "$res[title]\n";
            				}
            			}
            		}
	
            		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
            		{
            			$getcats = $ilance->db->query("
            				SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
            				FROM " . DB_PREFIX . "categories
            				WHERE parentid = '0'
            					AND cattype = 'service'
            					AND visible = '1'
            				ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
            				LIMIT 10
            			", 0, null, __FILE__, __LINE__);
            			if ($ilance->db->num_rows($getcats) > 0)
            			{
            				while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
            				{
            					$categories .= "$res[title]\n";
            				}
            			}
            		}
                        
                    // admin activates new members after their email link code verification
                    // so in this case, let's dispatch a new welcome email to the member
                    $ilance->email->mail = $user['email'];
                    $ilance->email->slng = fetch_user_slng($user['user_id']);
                    $ilance->email->get('register_welcome_email');		
                    $ilance->email->set(array(
                    '{{username}}' => $user['username'],
                    '{{user_id}}' => $user['user_id'],
                    '{{first_name}}' => $user['first_name'],
                    '{{last_name}}' => $user['last_name'],
                    '{{phone}}' => $user['phone'],
                    '{{categories}}' => $categories
                    ));
                    $ilance->email->send();
                }
    
                // regardless of the moderation status for new users, the admin will
                // receive this new user registration email so they can activate the new user
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                $ilance->email->get('register_welcome_email_admin');		
                $ilance->email->set(array(
                    '{{username}}' => $user['username'],
                    '{{user_id}}' => $user['user_id'],
                    '{{first_name}}' => $user['first_name'],
                    '{{last_name}}' => $user['last_name'],
                    '{{phone}}' => $user['phone'],
                    '{{emailaddress}}' => $user['email'],
                ));
                $ilance->email->send();
                    
                if ($status == 'active')
                {
                    // display welcome to marketplace template for active members
                    print_notice($phrase['_registration_complete'], $phrase['_thank_you_for_registering_we_are_glad_you_have_chosen'], HTTPS_SERVER . $ilpage['login'], $phrase['_please_log_in']);
                    exit();
                }
                else
                {
                    // display thanks for verifying email, admin will moderate you shortly ..
                    // at this point the user still has not been sent the welcome to the marketplace
                    // nor has he received any "account bonus" credit for signing up
                    // these emails will be dispatched from the admin control panel
                    print_notice($phrase['_registration_complete'], $phrase['_thanks_for_verifying_your_email_sddress_credentials_all_new_accounts_are_currently_under_moderated_review_you_will_receive_an_email_very_shortly'], HTTP_SERVER . $ilpage['search'], $phrase['_search']);
                    exit();
                }
            }
            else
            {
                refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
                exit();
            }
        }
        else
        {
            refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
            exit();
        }
    }
}

// are we returning to registration from a previous invitation or registration attempt?
if (!empty($_COOKIE[COOKIE_PREFIX . 'invitedid']) AND $_COOKIE[COOKIE_PREFIX . 'invitedid'] > 0)
{
    $_SESSION['ilancedata']['user']['invited'] = 1;
    $_SESSION['ilancedata']['user']['invitedid'] = intval($_COOKIE[COOKIE_PREFIX . 'invitedid']);
}
else
{
    // are we being externally invited?
    if (isset($ilance->GPC['invited']) AND $ilance->GPC['invited'] AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
    {
        // member has clicked link from within auction page
        $_SESSION['ilancedata']['user']['invited'] = 1;
        $_SESSION['ilancedata']['user']['invitedid'] = intval($ilance->GPC['id']);
        
        set_cookie('invitedid', $_SESSION['ilancedata']['user']['invitedid'], true);
    }
}

// #### BEGIN REGISTRATION #####################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'register')
{
    //var_dump($ilance->GPC);exit();

    if (!isset($ilance->GPC['step']))
    {
        $ilance->GPC['step'] = 1;
        
        // disable some cookies so we don't auto-login
        set_cookie('userid', '', false);
        set_cookie('password', '', false);			
    }
        
    // ########### STEP 1 ##################################################
    if ($ilance->GPC['step'] == '1')
    {
        $navcrumb = array();
        $navcrumb["$ilpage[registration]"] = $phrase['_registration'];
        $navcrumb[""] = $phrase['_account'];
        $countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
        $country_js_pulldown = construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state');
        $state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state') . '</div>';                
        // if conditions
        
        $show['error'] = $show['error_firstname'] = $show['error_lastname'] = $show['error_email'] = $show['error_email_exist'] = $show['error_email_space'] = false;
        
        $first_name = $last_name = $password = $password2 = $email = $email2 = $secretquestion = $secretanswer = '';

        if (isset($ilance->GPC['first_name']) AND $ilance->GPC['first_name'] == '')
        {
            $show['error_firstname'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['last_name']) AND $ilance->GPC['last_name'] == '')
        {
            $show['error_lastname'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '')
        {
            if((strstr($ilance->GPC['email']," ")))
            {
                $show['error_email_space'] = true;
                $show['error'] = true;
            }
            elseif (!filter_var($ilance->GPC['email'], FILTER_VALIDATE_EMAIL))
            {
                $show['error_email'] = true;
                $show['error'] = true;
            }

            //check email id is valid
            $sqlusercheck = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE email = '".$ilance->db->escape_string($ilance->GPC['email'])."'");

            if ($ilance->db->num_rows($sqlusercheck) > 0)
            {
                $show['error_email_exist'] = true;
                $show['error'] = true;
                $email = stripslashes(strip_tags(trim($ilance->GPC['email'])));
            }
        }
        else
        {
            if (!filter_var($ilance->GPC['email'], FILTER_VALIDATE_EMAIL))
            {
                $show['error_email'] = true;
                $show['error'] = true;
            }
        }

        if ($show['error'])
        {
            // terms of agreements
            $area_title = "Get Registered with GreatCollections";
            $page_title = SITE_NAME . ' - ' ."Get Registered with GreatCollections";

            $first_name = $ilance->GPC['first_name'];
            $last_name = $ilance->GPC['last_name'];
            $email = $ilance->GPC['email'];

            $pprint_array = array('first_name','last_name','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','email');

            $ilance->template->fetch('main', 'registration.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');
            $ilance->template->pprint('main', $pprint_array);
            exit();
        }
        else
        {
            // set agreed if user decides to come back later

            if (isset($ilance->GPC['agreement']) AND $ilance->GPC['agreement'] == 1)
            {
                $_SESSION['ilancedata']['user']['agreeterms'] = 1;
            }
        
            if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']) AND $ilconfig['registrationdisplay_dob'])
            {
                $_SESSION['ilancedata']['user']['dob'] = intval($ilance->GPC['year']) . '-' . intval($ilance->GPC['month']) . '-' . intval($ilance->GPC['day']);

                // does admin require members to be over 18?
                if ($ilconfig['registrationdisplay_dobunder18'] == 0)
                {
                    // are we under the age of 18?
                    if ($ilance->GPC['year'] > (gmdate('Y') - 18) OR ($ilance->GPC['year'] == (gmdate('Y') - 18) AND $ilance->GPC['month'] < gmdate('m')) OR ($ilance->GPC['year'] == (gmdate('Y') - 18) AND $ilance->GPC['month'] == gmdate('m') AND $ilance->GPC['day'] < gmdate('d')))
                    {
                        print_notice($phrase['_you_must_be_over_18'], $phrase['_were_sorry_you_must_be_over_the_age_of_18_to_register_on_this_marketplace'], $ilpage['main'], $phrase['_main_menu']);
                        exit();
                    }
                }
            }
            else
            {
                $_SESSION['ilancedata']['user']['dob'] = '0000-00-00';
            }
        
            if ($ilconfig['registrationdisplay_dob'] != 1)
            {
                $_SESSION['ilancedata']['user']['dob'] = '0000-00-00';
            }

            // specific javascript includes
            $headinclude .= '
            <script type="text/javascript">

            verify = new verifynotify();
            verify.field1 = fetch_js_object(\'password\');
            verify.field2 = fetch_js_object(\'password2\');
            verify.result_id = "password_result";
            verify.match_html = "<span style=\"color:blue\"><img src=\"' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif\" border=\"0\" alt=\"\" /></span>";
            verify.nomatch_html = "";
            verify.check();

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

            function register1(f)
            {
                haveerrors = 0;

                (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.first_name.value.length < 1) ? showImage("firstnameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("firstnameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.last_name.value.length < 1) ? showImage("lastnameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("lastnameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.company.value.length < 1) ? showImage("companyerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("companyerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.address.value.length < 1) ? showImage("addresserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("addresserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.address2.value.length < 1) ? showImage("address2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("address2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.city.value.length < 1) ? showImage("cityerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("cityerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.state.value.length < 1) ? showImage("stateerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("stateerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.zipcode.value.length < 1) ? showImage("ziperror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("ziperror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.country.value.length < 1) ? showImage("countryerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("countryerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.telephone.value.length < 1) ? showImage("telephoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("telephoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                return (!haveerrors);
            }

            </script>';

            //$roleselected = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '';
            //$rolepulldown = print_role_pulldown($roleselected, 0, 0);

            $email = $ilance->GPC['email'];
            $_SESSION['ilancedata']['user']['email'] = $ilance->GPC['email'];
            $email = $_SESSION['ilancedata']['user']['email'];
            
            $first_name = $ilance->GPC['first_name'];
            $_SESSION['ilancedata']['user']['first_name'] = ucwords($ilance->GPC['first_name']);
            $first_name = $_SESSION['ilancedata']['user']['first_name'];
            
            $last_name = $ilance->GPC['last_name'];
            $_SESSION['ilancedata']['user']['last_name'] = ucwords($ilance->GPC['last_name']);
            $last_name = $_SESSION['ilancedata']['user']['last_name'];

            $ilance->db->query("INSERT INTO " . DB_PREFIX . "users_unregistered(first_name,last_name,email,date_attempt,user_id)VALUES(
                '" . $ilance->db->escape_string($ilance->GPC['first_name']). "',
                '" . $ilance->db->escape_string($ilance->GPC['last_name']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['email']) . "',
                '" . DATETODAY . "',
                NULL)
            ");

            $pprint_array = array('rolepulldown','customquestions','password','password2','secretquestion','secretanswer','js','captcha','username','email','email2','js','captcha','first_name','last_name','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','zipcode','companyname','address','address2','city','state','country','telephone','email','country_js_pulldown','state_js_pulldown');

            $ilance->template->fetch('main', 'register1.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');
            $ilance->template->pprint('main', $pprint_array);
            exit();
        }
    }        
    // ########### STEP 2 ##################################################
    else if ($ilance->GPC['step'] == '2')
    {		
        // #### construct breadcrumb trail #############################
        $navcrumb = array();
        $navcrumb["$ilpage[registration]"] = $phrase['_registration'];
        $navcrumb[""] = $phrase['_contact'];
        $countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
        $country_js_pulldown = construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state');
        $state_js_pulldown = '<div id="stateid" style="height:20px">' . construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state') . '</div>';
        // #### template if conditions #################################
        $show['error'] = $show['username'] = $show['error_username'] = $show['error_username_exist'] = $show['error_username_sapce'] = $show['error_firstname'] = $show['error_lastname'] = $show['error_password'] = $show['error_password2'] = $show['error_password_length'] = $show['error_pwd_miss_match'] = $show['error_company'] = $show['error_address'] = $show['error_address2'] = $show['error_city'] = $show['error_state'] = $show['error_zip'] = $show['error_country'] = $show['error_telephone'] = false;
        $email = $username = $first_name = $last_name = $password = $password2 = $companyname = $address = $address2 = $city = $zipcode = $telephone =  $state = $country = '';

        // #### All input field validation #######################################

        if (empty($_SESSION['ilancedata']['user']['email']))
        {
            $area_title = $phrase['_your_session_has_expired_please_login'];
            $page_title = SITE_NAME . ' - '.$phrase['_your_session_has_expired_please_login'];

            $navcrumb = array("$ilpage[main]" => $phrase['_your_session_has_expired_please_login']);
            print_notice($phrase['_your_session_has_expired_please_login'], $phrase['_either_your_session_has_expired_or_you_are_a_guest_attempting_to_access_a_member_resource'], $ilpage['registration'], $phrase['_register_to_login_here']);
            exit();
        }

        if (isset($ilance->GPC['username']) AND $ilance->GPC['username'] == '')
        {
            $show['error_username'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['username']) AND $ilance->GPC['username'] != '')
        {
            $show['username'] = true;

            if (strstr($ilance->GPC['username'], " "))
            {
                $show['error_username_sapce'] = true;
                $show['error'] = true;
            }
            else
            {
                $sqlusercheck = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE 
                    username = '".$ilance->db->escape_string($ilance->GPC['username'])."' 
                    OR email = '".$ilance->db->escape_string($ilance->GPC['username'])."'
                ");

                if ($ilance->db->num_rows($sqlusercheck) > 0)
                {
                    $show['error_username_exist'] = true;
                    $show['error'] = true;
                    $username = stripslashes(strip_tags(trim($ilance->GPC['username'])));
                }
            }
        }

        if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] == '')
        {
            $show['error_password'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['password2']) AND $ilance->GPC['password2'] == '')
        {
            $show['error_password2'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] != '' AND strlen($ilance->GPC['password']) < 4)
        {
            $show['error_password_length'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] != '' AND isset($ilance->GPC['password2']) AND $ilance->GPC['password2'] != '')
        {
            if ($ilance->GPC['password'] != $ilance->GPC['password2'])
            {
                $password = trim($ilance->GPC['password']);
                $password2 = trim($ilance->GPC['password2']);
                $show['error_pwd_miss_match'] = true;
                $show['error'] = true;
            }
        }

        if (isset($ilance->GPC['first_name']) AND $ilance->GPC['first_name'] == '')
        {
            $show['error_firstname'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['last_name']) AND $ilance->GPC['last_name'] == '')
        {
            $show['error_lastname'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['address']) AND $ilance->GPC['address'] == '')
        {
            $show['error_address'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['city']) AND $ilance->GPC['city'] == '')
        {
            $show['error_city'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['state']) AND $ilance->GPC['state'] == '')
        {
            $show['error_state'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['zipcode']) AND $ilance->GPC['zipcode'] == '')
        {
            $show['error_zip'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['country']) AND $ilance->GPC['country'] == '')
        {
            $show['error_country'] = true;
            $show['error'] = true;
        }

        if (isset($ilance->GPC['telephone']) AND $ilance->GPC['telephone'] == '')
        {
            $show['error_telephone'] = true;
            $show['error'] = true;
        }
              
        // #### final checkups for step 1 ##############################
        if ($show['error'])
        {
            // ########### ERRORS: BACK TO STEP 1 ##############################

            $navcrumb = array();
            $navcrumb["$ilpage[registration]?cmd=register"] = $phrase['_registration'];
            $navcrumb["$ilpage[registration]"] = $phrase['_account'];
	    
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

            function register1(f)
            {
                haveerrors = 0;
                (f.username.value.length < 1) ? showImage("usernameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("usernameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.secretquestion.value.length < 1) ? showImage("secretquestionerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretquestionerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                (f.email2.value.search("@") == -1 || f.email2.value.search("[.*]") == -1) ? showImage("email2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("email2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
                ' . ($ilconfig['registrationdisplay_turingimage'] ? '(f.turing.value.length < 1) ? showImage("turingerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("turingerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);' : '') . '
                return (!haveerrors);
            }

            </script>';

            $js = '
            <script type="text/javascript">

            verify = new verifynotify();
            verify.field1 = fetch_js_object(\'password\');
            verify.field2 = fetch_js_object(\'password2\');
            verify.result_id = "password_result";
            verify.match_html = "<span style=\"color:blue\"><img src=\"' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif\" border=\"0\" alt=\"\" /></span>";
            verify.nomatch_html = "";
            verify.check();

            </script>';

            $email = $ilance->GPC['email'];
            $username = $ilance->GPC['username'];
            $password = trim($ilance->GPC['password']);
            $password2 = trim($ilance->GPC['password2']);
            $first_name = $ilance->GPC['first_name'];
            $last_name = $ilance->GPC['last_name'];
            $companyname = $ilance->GPC['companyname'];
            $address = $ilance->GPC['address'];
            $address2 = $ilance->GPC['address2'];
            $city = $ilance->GPC['city'];
            $zipcode = $ilance->GPC['zipcode'];
            $country = trim($ilance->GPC['country']);
            $countryid = fetch_country_id(trim($ilance->GPC['country']), $_SESSION['ilancedata']['user']['slng']);
            $state = $ilance->GPC['state'];
            $telephone = $ilance->GPC['telephone'];

            $pprint_array = array('rolepulldown','customquestions','password','password2','secretquestion','secretanswer','js','captcha','username','email','email2','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','zipcode','first_name','last_name','companyname','address','address2','city','state','country','telephone','email','username','country_js_pulldown','state_js_pulldown');

            $ilance->template->fetch('main', 'register1.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');
            $ilance->template->pprint('main', $pprint_array);
            exit();
        }
        else
        {

            $email = $ilance->GPC['email'];
            $_SESSION['ilancedata']['user']['email'] = $ilance->GPC['email'];
            $email = $_SESSION['ilancedata']['user']['email'];

            $username = $ilance->GPC['username'];
            $_SESSION['ilancedata']['user']['username'] = $ilance->GPC['username'];
            $username = $_SESSION['ilancedata']['user']['username'];

            $_SESSION['ilancedata']['user']['salt'] = construct_password_salt($length = 5);
            $_SESSION['ilancedata']['user']['password_md5'] = md5(md5($ilance->GPC['password']) . $_SESSION['ilancedata']['user']['salt']);
            $password = trim($ilance->GPC['password']);
            $password2 = trim($ilance->GPC['password2']);

            $first_name = $ilance->GPC['first_name'];
            $_SESSION['ilancedata']['user']['first_name'] = ucwords($ilance->GPC['first_name']);
            $first_name = $_SESSION['ilancedata']['user']['first_name'];

            $last_name = $ilance->GPC['last_name'];
            $_SESSION['ilancedata']['user']['last_name'] = ucwords($ilance->GPC['last_name']);
            $last_name = $_SESSION['ilancedata']['user']['last_name'];

            $companyname = $ilance->GPC['companyname'];

            $address = $ilance->GPC['address'];
            $_SESSION['ilancedata']['user']['address'] = ucwords($ilance->GPC['address']);
            $address = $_SESSION['ilancedata']['user']['address'];

            $address2 = $ilance->GPC['address2'];
            $_SESSION['ilancedata']['user']['address2'] = ucwords($ilance->GPC['address2']);
            $address2 = $_SESSION['ilancedata']['user']['address2'];

            $city = $ilance->GPC['city'];
            $_SESSION['ilancedata']['user']['city'] = ucwords($ilance->GPC['city']);
            $city = $_SESSION['ilancedata']['user']['city'];

            $zipcode = $ilance->GPC['zipcode'];
            $_SESSION['ilancedata']['user']['zipcode'] = trim($ilance->GPC['zipcode']);
            $zipcode = $_SESSION['ilancedata']['user']['zipcode'];

            $country = trim($ilance->GPC['country']);
            $_SESSION['ilancedata']['user']['country'] = trim($ilance->GPC['country']);
            $_SESSION['ilancedata']['user']['countryid'] = fetch_country_id($_SESSION['ilancedata']['user']['country'], $_SESSION['ilancedata']['user']['slng']);
            $country = $_SESSION['ilancedata']['user']['country'];

            $state = $ilance->GPC['state'];
            $_SESSION['ilancedata']['user']['state'] = $ilance->GPC['state'];
            $state = $_SESSION['ilancedata']['user']['state'];

            $telephone = $ilance->GPC['telephone'];
            $_SESSION['ilancedata']['user']['phone'] = $ilance->GPC['telephone'];
            $telephone = $_SESSION['ilancedata']['user']['phone'];

            $pprint_array = array('companyname_checkbox','companyname','service_newsletter','product_newsletter','customquestions','address','address2','city','zipcode','onsubmit','onload','currency_pulldown','language_pulldown','timezone_dst_checkbox','timezone_pulldown','onsubmit','dynamic_js_bodyend','state_js_pulldown','country_js_pulldown','js','first_name','last_name','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

            ($apihook = $ilance->api('register2_end')) ? eval($apihook) : false;

            $ilance->template->fetch('main', 'register2.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');
            $ilance->template->pprint('main', $pprint_array);
            exit();
        }
    }
    // ########### STEP 4 ##################################################
    else if ($ilance->GPC['step'] == '4')
    {
        if (empty($_SESSION['ilancedata']['user']['username']) OR empty($_SESSION['ilancedata']['user']['email']))
        {
            $area_title = $phrase['_your_session_has_expired_please_login'];
            $page_title = SITE_NAME . ' - '.$phrase['_your_session_has_expired_please_login'];

            $navcrumb = array("$ilpage[main]" => $phrase['_your_session_has_expired_please_login']);
            print_notice($phrase['_your_session_has_expired_please_login'], $phrase['_either_your_session_has_expired_or_you_are_a_guest_attempting_to_access_a_member_resource'], $ilpage['registration'], $phrase['_register_to_login_here']);
            exit();
        }

        $show['error_agreecheck'] = false;

        if (!isset($ilance->GPC['agreecheck']))
        {
            $show['error_agreecheck'] = true;
        }

        if (isset($ilance->GPC['agreecheck']) AND $ilance->GPC['agreecheck'] != 1)
        {
            $show['error_agreecheck'] = true;
        }

        // final checkups
        if ($show['error_agreecheck'])
        {
            // specific javascript includes
            $headinclude .= '
            <script type="text/javascript">
            <!--
            function register2(f)
            {
                haveerrors = 0;
                (!f.agreecheck.checked) ? showImage("agreecheckerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("agreecheckerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);

                return (!haveerrors);
            }
            //-->
            </script>';

            ($apihook = $ilance->api('register2_end')) ? eval($apihook) : false;

            $pprint_array = array('first_name','last_name','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','email');

            $ilance->template->fetch('main', 'register2.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');
            $ilance->template->pprint('main', $pprint_array);
            exit();
        }
        else 
        {
            // build subscription plan session
            if ($ilance->GPC['subscriptionid'] > 0)
            {
                $_SESSION['ilancedata']['subscription']['subscriptionid'] = intval($ilance->GPC['subscriptionid']);
                $_SESSION['ilancedata']['subscription']['subscriptionpaymethod'] = mb_strtolower(trim($ilance->GPC['paymethod']));
            }
            else
            {
                $_SESSION['ilancedata']['subscription']['subscriptionid'] = '1';
                $_SESSION['ilancedata']['subscription']['subscriptionpaymethod'] = 'account';
            }       
    
            // support promotional code feature
            if (!empty($ilance->GPC['promocode']))
            {
                $_SESSION['ilancedata']['subscription']['promocode'] = handle_input_keywords(trim($ilance->GPC['promocode']));
            }
            else
            {
                $_SESSION['ilancedata']['subscription']['promocode'] = '';
            }
    
            $navcrumb = array();
            $navcrumb["$ilpage[registration]"] = $phrase['_registration'];
            $navcrumb[""] = $phrase['_message'];
            
            // find out if we had any questions to answer
            if (empty($_SESSION['ilancedata']['questions']))
            {
                $_SESSION['ilancedata']['questions'] = array();    
            }       
            
            // notes: you may send 3 custom arguments:
            // return_userid        : returns only the new user ID
            // return_userstatus    : returns the new users status (login status, active, unverified, etc)
            // return_userarray     : returns the full user array of the created member
            $dowhat = 'return_userarray';
    
            $ilance->registration = construct_object('api.registration');

            //echo "<pre>";print_r($_SESSION['ilancedata']['user']);echo "</pre>";
            //echo "<pre>";print_r($_SESSION['ilancedata']['preferences']);echo "</pre>";
            //echo "<pre>";print_r($_SESSION['ilancedata']['subscription']);echo "</pre>";exit();
            
            $final = $ilance->registration->build_user_datastore($_SESSION['ilancedata']['user'], $_SESSION['ilancedata']['preferences'], $_SESSION['ilancedata']['subscription'], $_SESSION['ilancedata']['questions'], $dowhat);

            if (!empty($final))
            {			 
                // set new user cookies
                set_cookie('userid', $ilance->crypt->three_layer_encrypt($final['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
                set_cookie('username', $ilance->crypt->three_layer_encrypt($final['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
                set_cookie('password', $ilance->crypt->three_layer_encrypt($final['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
                set_cookie('lastvisit', DATETIME24H, true);
                set_cookie('lastactivity', DATETIME24H, true);
                      
                switch ($final['status'])
                {
                    case 'active':
                    {
                        // make sure we have a valid password session
                        if (!empty($_SESSION['ilancedata']['user']['password_md5']))
                        {
                                $_SESSION['ilancedata']['user']['password'] = $_SESSION['ilancedata']['user']['password_md5'];
                                session_unregister($_SESSION['ilancedata']['user']['password_md5']);
                        }
                        
						//new change apr28,apr29
						$scriptgoogle = '';
										
						//new may10
						$scriptgoogle1 = '';			
						
                        // display final registration information
						
                        refresh(HTTPS_SERVER . $ilpage['registration'] . '?msg=successful');
                        break;
                    }                            
                    case 'unverified':
                    {
                        // display email link code information
                        print_notice($phrase['_registration_not_completed'], $phrase['_thank_you_for_registering_an_email_has_been_dispatched_to_you'], $ilpage['login'], $phrase['_sign_in']);
                        break;
                    }
                    case 'moderated':
                    {
                        // display email link code information
                        print_notice($phrase['_registration_complete'], $phrase['_thank_you_your_registration_is_now_complete_and_is_pending_verification'], $ilpage['main'], $phrase['_main_menu']);
                        break;
                    }
                }
				
                exit();					
            }
            else
            {
                print_notice($phrase['_registration_error_occured'], $phrase['_were_sorry_we_only_allow_forms_to_be_securely_processed_via_our_web_site'], $ilpage['registration'] . '?cmd=register&step=1', $phrase['_register_to_login_here']);
                exit();
            }
        }
    }
}
else
{
    //$_SESSION['ilancedata']['user']['referal_name'] = $ilance->GPC['referal_name'];
    $area_title = "Get Registered with GreatCollections";
    $page_title = SITE_NAME . ' - ' ."Get Registered with GreatCollections";

    $navcrumb = array();
    $navcrumb["$ilpage[registration]"] = $phrase['_registration'];
    //$navcrumb[""] = $phrase['_terms'];

    $pprint_array = array('first_name','last_name','input_style','remote_addr','rid','default_exchange_rate','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','email','username');

    $ilance->template->fetch('main', 'registration.html');
    $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
    $ilance->template->parse_if_blocks('main');
    $ilance->template->pprint('main', $pprint_array);

    exit();		
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Dec 16th, 2010
|| ####################################################################
\*======================================================================*/
?>