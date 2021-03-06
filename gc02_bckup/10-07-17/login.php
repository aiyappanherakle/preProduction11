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
	'preferences',
	'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'md5',
	'flashfix',
	'jquery'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'main'
);

// #### setup script location ##################################################
define('LOCATION', 'login');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[login]" => $ilcrumbs["$ilpage[login]"]);

// #### MEMBER LOGIN PROCESS ###################################################
$redirect = isset($ilance->GPC['redirect']) ? strip_tags($ilance->GPC['redirect']) : '';

$watchlist_id = isset($ilance->GPC['watchlist_id'])?intval($ilance->GPC['watchlist_id']):'';

($apihook = $ilance->api('login_start')) ? eval($apihook) : false;

// #### LOGIN AUTHENTICATION PROCESS ###########################################
if (isset($ilance->GPC['login_process']) AND $ilance->GPC['login_process'] > 0) {

	//echo "<pre>";print_r($ilance->GPC);echo "</pre>";exit;

	$area_title = $phrase['_submitting_login_information'].' . .';
	$page_title = SITE_NAME . ' - ' . $phrase['_submitting_login_information'];

	$result['error'] = $result['landing'] = $landing = '';	
	$badusername = $badpassword = false;
	$userinfo = array();
	
	if (isset($ilance->GPC['username']) AND !empty($ilance->GPC['username'])) {

		// default subscription params
		$userinfo['roleid'] = -1;
		$userinfo['subscriptionid'] = $userinfo['cost'] = 0;
		$userinfo['active'] = 'no';
		
		$sql = $ilance->db->query("SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode	FROM " . DB_PREFIX . "users AS u 
			LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id 
			LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid 
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id 
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid 
			WHERE username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "' OR email = '".$ilance->db->escape_string($ilance->GPC['username'])."' 
			GROUP BY username 
			LIMIT 1
		");

		if ($ilance->db->num_rows($sql) > 0) {

			$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);

			if ($userinfo['password'] != iif($ilance->GPC['password'] AND !$ilance->GPC['md5pass'], md5(md5($ilance->GPC['password']) . $userinfo['salt']), '') AND $userinfo['password'] != md5($ilance->GPC['md5pass'] . $userinfo['salt']) AND $userinfo['password'] != iif($ilance->GPC['md5pass_utf'], md5($ilance->GPC['md5pass_utf'] . $userinfo['salt']), '')) {

				$badpassword = true;
			}
		}else {
			($apihook = $ilance->api('login_process_start_external_authentication')) ? eval($apihook) : false;
			$badusername = true;
		}
		
		($apihook = $ilance->api('login_process_start')) ? eval($apihook) : false;
		
		if ($badusername == false AND $badpassword == false) {

			// update last seen for this member
			$ilance->db->query("UPDATE " . DB_PREFIX . "users 
				SET 
				lastseen = '" . DATETIME24H . "' 
				WHERE user_id = '" . $userinfo['user_id'] . "'
			");
			
			if ($userinfo['status'] == 'active') {
				// ip restriction
				if ($userinfo['iprestrict'] AND !empty($userinfo['ipaddress'])) {
					if (IPADDRESS != $userinfo['ipaddress']) {

						if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ajax_login_process') {
							$result['error'] = $phrase['_your_ip_address_does_not_match_for_this_account']."<span class=blue><a href=".HTTPS_SERVER."login.php?cmd=_ip-reset target=_blank>".$phrase['_click_here_to_reset_your_ip_address_preference']."</a></span>";
							echo json_encode($result);
							exit();
						}else {
							refresh(HTTPS_SERVER . $ilpage['login'] . '?error=iprestrict');
							exit();
						}
					}
				}
				
 				// create user session
				$_SESSION['ilancedata'] = array(
					'user' => array(
						'isstaff' => $userinfo['isstaff'],
						'isadmin' => $userinfo['isadmin'],
						'access_bb' => intval($userinfo['access_bb']),
						'enable_batch_bid' => intval($userinfo['enable_batch_bid']),
						'is_auto_lower_min_bid' => intval($userinfo['is_auto_lower_min_bid']),
						'auto_min_bid_lower_prec' => intval($userinfo['auto_min_bid_lower_prec']),
						'status' => $userinfo['status'],
						'userid' => $userinfo['user_id'],
						'username' => $userinfo['username'],
						'password' => $userinfo['password'],
						'salt' => $userinfo['salt'],
						'email' => $userinfo['email'],
						'firstname' => stripslashes($userinfo['first_name']),
						'lastname' => stripslashes($userinfo['last_name']),
						'fullname' => $userinfo['first_name'] . ' ' . $userinfo['last_name'],
						'address' => ucwords(stripslashes($userinfo['address'])),
						'address2' => ucwords(stripslashes($userinfo['address2'])),
						'fulladdress' => ucwords(stripslashes($userinfo['address'])) . ' ' . ucwords(stripslashes($userinfo['address2'])),
						'city' => ucwords(stripslashes($userinfo['city'])),
						'state' => ucwords(stripslashes($userinfo['state'])),
						'postalzip' => mb_strtoupper(trim($userinfo['zip_code'])),
						'countryid' => intval($userinfo['country']),
						'country' => print_country_name($userinfo['country']),
						'countryshort' => print_country_name($userinfo['country'], mb_substr($userinfo['languagecode'], 0, 3), true),
						'lastseen' => $userinfo['lastseen'],
						'ipaddress' => $userinfo['ipaddress'],
						'iprestrict' => $userinfo['iprestrict'],
						'auctiondelists' => intval($userinfo['auctiondelists']),
						'bidretracts' => intval($userinfo['bidretracts']),
						'ridcode' => $userinfo['rid'],
						'dob' => $userinfo['dob'],
						'serviceawards' => intval($userinfo['serviceawards']),
						'productawards' => intval($userinfo['productawards']),
						'servicesold' => intval($userinfo['servicesold']),
						'productsold' => intval($userinfo['productsold']),
						'rating' => $userinfo['rating'],
						'languageid' => intval($userinfo['languageid']),
						'slng' => mb_substr($userinfo['languagecode'], 0, 3),
						'styleid' => intval($userinfo['styleid']),
						'timezoneid' => intval($userinfo['timezoneid']),
						'timezonedst' => $userinfo['timezone_dst'],
						'distance' => $userinfo['project_distance'],
						'emailnotify' => intval($userinfo['emailnotify']),
						'companyname' => stripslashes($userinfo['companyname']),
						'roleid' => intval($userinfo['roleid']),
						'subscriptionid' => intval($userinfo['subscriptionid']),
						'cost' => $userinfo['cost'],
						'active' => $userinfo['active'],
						'currencyid' => intval($userinfo['currencyid']),
						'currencyname' => stripslashes($userinfo['currency_name']),
						'currencysymbol' => isset($userinfo['currencyid']) ? $ilance->currency->currencies[$userinfo['currencyid']]['symbol_left'] : '$',
						'currencyabbrev' => mb_strtoupper($userinfo['currency_abbrev']),
						'searchoptions'  => isset($userinfo['searchoptions']) ? $userinfo['searchoptions'] : '',
						'token' => TOKEN,
						'siteid' => SITE_ID,
						
					)
				);

				// update last seen for this member
				$ilance->db->query("UPDATE " . DB_PREFIX . "users 
					SET 
					token_code = '' 
					WHERE user_id = '" . $userinfo['user_id'] . "'
				");	

				($apihook = $ilance->api('login_globalize_session')) ? eval($apihook) : false;

				if (isset($ilance->GPC['remember']) AND $ilance->GPC['remember']) {

					// user has chosen the marketplace to remember them
					set_cookie('userid', $ilance->crypt->three_layer_encrypt($userinfo['user_id'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
					set_cookie('password', $ilance->crypt->three_layer_encrypt($userinfo['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
					set_cookie('username', $ilance->crypt->three_layer_encrypt($userinfo['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
				}
				
				// remember users last visit and last hit activity regardless of remember me preference
				set_cookie('lastvisit', DATETIME24H, true);
				set_cookie('lastactivity', DATETIME24H, true);
				set_cookie('radiuszip', handle_input_keywords(format_zipcode($userinfo['zip_code'])), true);
                                
                if(!empty($redirect)) {
                	if(strpos($redirect,'Coin')) {
                		if(isset($ilance->GPC['watchlist_id']) AND $ilance->GPC['watchlist_id'] > 0) {
                			$ilance->watchlist = construct_object('api.watchlist');
                			$comment=$phrase['_added_from_listing_page'];
                			$success = $ilance->watchlist->insert_item(intval($userinfo['user_id']), intval($ilance->GPC['watchlist_id']), 'auction', $comment, 0, 0, 0, 0);
                		}

                		$newredirect = parse_url($redirect);
                		$newurl = ($newredirect['scheme'] == 'https') ? 'https://'.DOMAINNAME.''.$newredirect['path'].'' : 'http://'.DOMAINNAME.''.$newredirect['path'].'';
                		
                		$result['landing'] = $newurl;
                		$landing = $newurl;

                	}else if(strpos($redirect,'main.php')) {
                		$result['landing'] = HTTPS_SERVER.'main.php';
                		$landing = HTTPS_SERVER.'main.php';
                	}else {
                		$result['landing'] = $redirect;
                		$landing = $redirect;
                	}
                }else if (!empty($userinfo['startpage']) AND $ilance->GPC['login_process'] == '1') {
                	$result['landing'] = HTTPS_SERVER.'main.php';
                	$landing = HTTPS_SERVER.'main.php';
				}else {
					$result['landing'] = $ilpage['main'] . '?cmd=cp';
					$landing = $ilpage['main'] . '?cmd=cp';
				}

				if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ajax_login_process') {
					echo json_encode($result);
					exit();
				}else {
					refresh($landing);
					exit();
				}

			}else {

				($apihook = $ilance->api('login_status_banned')) ? eval($apihook) : false;

				if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ajax_login_process') {

					if($userinfo['status'] == 'suspended') {
						$result['error'] = $phrase['_your_account_is_suspended_login_disabled'];

					}else if($userinfo['status'] == 'cancelled') {
						$result['error'] = $phrase['_your_account_is_cancelled'];

					}else if($userinfo['status'] == 'unverified') {
						$result['error'] = $phrase['_you_have_not_activated_your_account_via_email'];

					}else if($userinfo['status'] == 'banned') {
						$result['error'] = $phrase['_you_have_been_banned_from_the_marketplace'] . "<br />" . $phrase['_if_you_would_like_to_dispute_this_ban_contact_our_staff']."<span class=blue> <a href=".HTTPS_SERVER."main.php?cmd=contact&amp;subcmd=banned target=_blank>".$phrase['_contact_customer_support']."</a></span>";

					}else if($userinfo['status'] == 'moderated') {
						$result['error'] = $phrase['_your_account_is_in_moderation_mode_login_temporarily_disabled_until_further_notice'];

					}else {
						$result['error'] = "<span class=blue> <a href=".HTTPS_SERVER."main-contact target=_blank>".$phrase['_contact_customer_support']."</a></span>";

					}

					echo json_encode($result);
					exit();

				}else {

					if ($userinfo['status'] == 'banned') {
						print_notice($phrase['_you_have_been_banned_from_the_marketplace'], $phrase['_you_have_been_banned_from_the_marketplace'] . '.<br />' . $phrase['_if_you_would_like_to_dispute_this_ban_contact_our_staff'], $ilpage['main'] . '?cmd=contact&amp;subcmd=banned', $phrase['_contact_customer_support']);
						exit();

					}else {
						refresh(HTTPS_SERVER . $ilpage['login'] . '?error=' . $userinfo['status']);
						exit();

					}
				}
			}

		}else{

			// #### INCORRECT USERNAME / PASSWORD ENTERED BY USER

			$ilance->GPC['username'] = isset($ilance->GPC['username']) ? $ilance->GPC['username'] : '';
			$ilance->GPC['password'] = isset($ilance->GPC['password']) ? $ilance->GPC['password'] : '';

			if (isset($ilance->GPC['hpwd']) AND $ilance->GPC['hpwd'] != '') {
				$ilance->GPC['password'] = $ilance->GPC['hpwd'];
			}

			$ilance->db->query("INSERT INTO " . DB_PREFIX . "failed_logins
	            (id, attempted_username, attempted_password, referrer_page, ip_address, datetime_failed)
	            VALUES(
	            NULL,
	            '" . $ilance->db->escape_string($ilance->GPC['username']) . "',
	            '" . $ilance->db->escape_string($ilance->GPC['password']) . "',
	            '" . $ilance->db->escape_string(REFERRER) . "',
	            '" . $ilance->db->escape_string(IPADDRESS) . "',
	            '" . DATETIME24H . "')
	        ");

	        if ($ilconfig['globalsecurity_emailonfailedlogins']) {

	            // count number of login attempts
	            $sql = $ilance->db->query("SELECT COUNT(*) AS num_attempts FROM " . DB_PREFIX . "failed_logins WHERE attempted_username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'");

	            $res = $ilance->db->fetch_array($sql);

	            if ($res['num_attempts'] >= $ilconfig['globalsecurity_numfailedloginattempts']) {
	                // to be added: check if this user is actually a user, if so
	                // send them an email also informing them of a suspicious hack attempt

	                ($apihook = $ilance->api('login_failed_attempts_exceeded')) ? eval($apihook) : false;
	            }

	            $ilance->email = construct_dm_object('email', $ilance);
	            $ilance->email->mail = $ilconfig['globalserversettings_staff_ron'];
	            $ilance->email->slng = fetch_site_slng();
	            $ilance->email->dohtml = true;
	            $ilance->email->get('failed_login_attempt_admin');      
	            $ilance->email->set(array(
	                '{{remote_addr}}' =>IPADDRESS,
	                '{{num_attempts}}' => $res['num_attempts'],
	                '{{date_time}}' => DATETIME24H,
	                '{{referrer}}' => REFERRER,
	                '{{username}}' => $ilance->GPC['username'],
	                '{{password}}' => $ilance->GPC['password'],
	                '{{reverse_dns}}' => '<a href="https://gwhois.org/'.IPADDRESS.'+dns">https://gwhois.org/'.IPADDRESS.'+dns</a>'
	            ));

	            $ilance->email->send();

				$landing = $ilance->GPC['password'] = '';
				if (!empty($redirect)) {

					$landing = '&redirect=' . urlencode($redirect);
				}

				if ($ilance->GPC['login_process'] == '2') {
					refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?error=1' . $landing);
					exit();		
				}else {
					$result['error'] = 'Your login has failed.  Please <span class="blue"> <a href='.HTTPS_SERVER.'main-contact target="_blank">contact us</a></span> if you need assistance.';
					echo json_encode($result);
					exit();
				}

			}else {

				$landing = '';
				if (!empty($redirect)) {

					$landing = '&redirect=' . urlencode($redirect);
				}

				if ($ilance->GPC['login_process'] == '2') {
					refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?error=1' . $landing);
					exit();		
				}else {
					$result['error'] = 'Your login has failed.  Please <span class="blue"> <a href='.HTTPS_SERVER.'main-contact target="_blank">contact us</a></span> if you need assistance.';
					echo json_encode($result);
					exit();
				}
			}
		}
	}else {

		$landing = '';
		if (!empty($redirect)) {

			$landing = '&redirect=' . urlencode($redirect);
		}

		if ($ilance->GPC['login_process'] == '2') {
			refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?error=1' . $landing);
			exit();		
		}else {
			$result['error'] = 'Your login has failed.  Please <span class="blue"> <a href='.HTTPS_SERVER.'main-contact target="_blank">contact us</a></span> if you need assistance.';
			echo json_encode($result);
			exit();
		}
	}
}

// #### MEMBER LOGOUT REQUEST ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_logout')
{
	$area_title = $phrase['_logging_out_of_marketplace'];
	$page_title = $phrase['_logging_out_of_marketplace'];

	($apihook = $ilance->api('logout_process_start')) ? eval($apihook) : false;

	// keep last visit and last activity cookie
	set_cookie('lastvisit', DATETIME24H, true);
	set_cookie('lastactivity', DATETIME24H, true);
	
	// expire member specific cookies so the marketplace doesn't re-login user in automatically
        // leave username cookie alone so the marketplace can greet the member by username (login, breadcrumb, etc)
	set_cookie('userid', '', false);
	set_cookie('password', '', false);
	
	// expire the securitytoken cookie
	set_cookie('token', '', false);
	
	// expire any checkboxes selected in this session
	set_cookie('inlineproduct', '', false);
	set_cookie('inlineservice', '', false);
	set_cookie('inlineprovider', '', false);
        
        set_cookie('collapse', '', false);
        set_cookie('hideacpnag', '', false);
	
	($apihook = $ilance->api('logout_process_end')) ? eval($apihook) : false;

	// destroy entire member session
	session_unset();
	$ilance->sessions->session_destroy(session_id());
	session_destroy();	
	
        // refresh page to set new sessions to empty values
		//june02
	//refresh(HTTPS_SERVER . $ilpage['login']);
	refresh(HTTPS_SERVER . $ilpage['main']);
	exit();
}

// #### RENEW PASSWORD #########################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_pw-renew')
{
	$area_title = $phrase['_request_account_password'];
	$page_title = SITE_NAME . ' - ' . $phrase['_request_account_password'];

	// javascript header includes
	$headinclude .= '
<script type="text/javascript" language="Javascript">
<!--
function validate_input(f)
{
        haveerrors = 0;
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
	
	$pprint_array = array('userid','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'login_password_renewal.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### USER REQUESTING PASSWORD ###############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-pw-request' AND isset($ilance->GPC['email']) AND !empty($ilance->GPC['email']))
{
        $area_title = $phrase['_change_account_password_verification'];
	$page_title = SITE_NAME . ' - ' . $phrase['_change_account_password_verification'];
                
	$sql = $ilance->db->query("
		SELECT username, secretquestion, secretanswer
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
                
		$email = $ilance->GPC['email'];
		$username = stripslashes($res['username']);
			
		if ($res['secretquestion'] != '' AND $res['secretanswer'] != '')
		{
			$headinclude .= '
<script type="text/javascript">
<!--
function validate_secret_answer(f)
{
        haveerrors = 0;
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
			$secret_question = stripslashes($res['secretquestion']);
					
			$pprint_array = array('email','username','secret_question','userid','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'login_password_change.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		
		// #### secret question and answer blank!! #####################
		else
		{
			print_notice($phrase['_could_not_find_your_secret_question_and_answer'], $phrase['_we_are_sorry_but_after_recent_site_security_upgrades'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', $phrase['_retry']);
			exit();
		}
	}
	else
	{
		$area_title = $phrase['_request_account_password_denied'];
                $page_title = SITE_NAME . ' - ' . $phrase['_request_account_password_denied'];
                
                print_notice($phrase['_request_account_password_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', $phrase['_retry']);
                exit();
	}
}
// #### USER CHANGING PASSWORD #################################################
/*else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-change' AND isset($ilance->GPC['secretanswer']) AND isset($ilance->GPC['email']) AND isset($ilance->GPC['username']))
{
	$secretanswer = strip_tags($ilance->GPC['secretanswer']);
	$secretanswermd5 = md5($secretanswer);
	$email = strip_tags($ilance->GPC['email']);
	$username = strip_tags($ilance->GPC['username']);
        
	$sql = $ilance->db->query("
		SELECT user_id, secretanswer
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($email) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);                
		$userid = $res['user_id'];
		$secretanswerdb = stripslashes($res['secretanswer']);
	}
	else
	{
		$area_title = $phrase['_request_account_password_denied'];
                $page_title = SITE_NAME . ' - ' . $phrase['_request_account_password_denied'];
                
                print_notice($phrase['_request_account_password_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', $phrase['_retry']);
                exit();
	}
	
	if ($secretanswermd5 == $secretanswerdb)
	{
                $salt = construct_password_salt(5);
		$newpassword = construct_password(8);
		$newpasswordmd5 = md5(md5($newpassword) . $salt);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET password = '" . $ilance->db->escape_string($newpasswordmd5) . "',
			salt = '" . $ilance->db->escape_string($salt) . "' 
			WHERE user_id = '" . intval($userid) . "'
		");
		
		$ilance->email = construct_dm_object('email', $ilance);
                $ilance->email->mail = $email;
                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
		$ilance->email->get('password_renewed');		
		$ilance->email->set(array(
			'{{username}}' => $username,
			'{{password}}' => $newpassword,
		));
		$ilance->email->send();
		
		$area_title = $phrase['_account_password_renewal_success'];
		$page_title = SITE_NAME . ' - ' . $phrase['_account_password_renewal_success'];
		
		print_notice($phrase['_your_account_password_was_changed'], $phrase['_you_have_successfully_renewed_the_password_for_your_online_account'], HTTPS_SERVER . $ilpage['login'], $phrase['_login_to_your_account']);
		exit();
	}
	else
	{
		$sql = $ilance->db->query("
			SELECT email
			FROM " . DB_PREFIX . "users
			WHERE username = '" . $ilance->db->escape_string($username) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->mail = $res['email'];
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('password_recovery_denied');		
			$ilance->email->set(array(
				'{{username}}' => $username,
				'{{ipaddress}}' =>IPADDRESS,
				'{{agent}}' => USERAGENT
			));
			$ilance->email->send();
			
			$area_title = $phrase['_request_account_password_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_request_account_password_denied'];
			
			print_notice($phrase['_request_account_password_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal'], HTTPS_SERVER . $ilpage['login'], $phrase['_sign_in']);
			exit();
		}
		else
		{
			$area_title = $phrase['_request_account_password_denied'];
			$page_title = SITE_NAME . ' - ' . $phrase['_request_account_password_denied'];
			
			print_notice($phrase['_request_account_password_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal'], HTTPS_SERVER . $ilpage['login'], $phrase['_sign_in']);
			exit();
		}
	}
}
*/
// murugan Changes On Feb 02 for Forgot Password
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-change' AND isset($ilance->GPC['email']))
{
	$email = strip_tags($ilance->GPC['email']);

	$sql = $ilance->db->query("SELECT user_id, username FROM " . DB_PREFIX . "users	WHERE email = '" . $ilance->db->escape_string($email) . "'");

	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);                
		$userid = $res['user_id'];
		$username = $res['username'];
		$salt = construct_password_salt(5);
		//$newpassword = construct_password(8);
		$newpassword = mt_rand(100000, 999999);
		$newpasswordmd5 = md5(md5($newpassword) . $salt);
		$code = token(40);

		$ilance->db->query("UPDATE " . DB_PREFIX . "users 
			SET password = '" . $ilance->db->escape_string($newpasswordmd5) . "', 
			salt = '" . $ilance->db->escape_string($salt) . "', 
			token_code = '" . $ilance->db->escape_string($code) . "' 
			WHERE user_id = '" . intval($userid) . "'
		");

		$ilance->email = construct_dm_object('email', $ilance);
		$ilance->email->mail = $email;
		$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
		$ilance->email->get('password_renewed');		
		$ilance->email->set(array(
			'{{username}}' => $username,
			'{{password}}' => $newpassword,
			'{{pwd_reset_link}}' => '<a href="'.HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$code.'&userid='.$userid.'">'.HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$code.'&userid='.$userid.'</a>'
		));
		$ilance->email->send();

		//admin email cc june29 and j30
		$ilance->email = construct_dm_object('email', $ilance);
		$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
		$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
		$ilance->email->get('password_renewed');		
		$ilance->email->set(array(
			'{{username}}' => $username,
			'{{password}}' => $newpassword,
			'{{pwd_reset_link}}' => '<a href="'.HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$code.'&userid='.$userid.'">'.HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$code.'&userid='.$userid.'</a>'
		));
		$ilance->email->send();

		$area_title = $phrase['_account_password_renewal_success'];
		$page_title = SITE_NAME . ' - ' . $phrase['_account_password_renewal_success'];

		print_notice($phrase['_your_account_password_was_changed'], $phrase['_you_have_successfully_renewed_the_password_for_your_online_account'], HTTPS_SERVER . $ilpage['login'], $phrase['_login_to_your_account']);
		exit();
	}
	else
	{
		$area_title = $phrase['_request_account_password_denied'];
		$page_title = SITE_NAME . ' - ' . $phrase['_request_account_password_denied'];

		print_notice($phrase['_request_account_password_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', $phrase['_retry']);
		exit();
	}
}
// ############## Reset new password ########################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_reset-newpassword' AND isset($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0 AND isset($ilance->GPC['code']) AND $ilance->GPC['code'] != '')
{
	$area_title = $phrase['_request_account_password'];
	$page_title = SITE_NAME . ' - ' . $phrase['_request_account_password'];

	$userid = $ilance->GPC['userid'];
	$code = $ilance->GPC['code'];

	$sql = $ilance->db->query("SELECT user_id FROM " . DB_PREFIX . "users WHERE user_id = '" . intval($userid) . "'");

	if ($ilance->db->num_rows($sql) == 0)
	{
		print_notice('Reset Password - No User Found', 'We do not have any record of this user having an account at GreatCollections. To Register, click on the Register link at the top right. If you are convinced you have an account with us, please telephone us at 1-800-442-6467.', HTTPS_SERVER . $ilpage['registration']);
        exit();
	}

	$sql = $ilance->db->query("SELECT token_code FROM " . DB_PREFIX . "users WHERE user_id = '" . intval($userid) . "'");

	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);

		if ($res['token_code'] == '') 
		{
			print_notice('Reset Password - Your password reset code was already used.', '<a href="'.HTTPS_SERVER . $ilpage['login'].'">Please click here to login.</a>', HTTPS_SERVER . $ilpage['login']);
        	exit();
		}
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

    function resetPwd(f)
    {
        haveerrors = 0;

        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
    }

    </script>';
	
	$pprint_array = array('userid','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','userid','code');
	
	$ilance->template->fetch('main', 'reset_newpassword.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// ############### Update New Password ####################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_update-newpassword' AND isset($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0 AND isset($ilance->GPC['code']) AND $ilance->GPC['code'] != '')
{
	if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] == '')
    {
        refresh(HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$ilance->GPC['code'].'&userid='.$ilance->GPC['userid'].'&error_password=1');
        exit();
    }

    if (isset($ilance->GPC['password2']) AND $ilance->GPC['password2'] == '')
    {
        refresh(HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$ilance->GPC['code'].'&userid='.$ilance->GPC['userid'].'&error_password2=1');
        exit();
    }

    if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] != '' AND strlen($ilance->GPC['password']) < 6)
    {
        refresh(HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$ilance->GPC['code'].'&userid='.$ilance->GPC['userid'].'&error_password_length=1');
        exit();
    }

    if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] != '' AND isset($ilance->GPC['password2']) AND $ilance->GPC['password2'] != '')
    {
        if ($ilance->GPC['password'] != $ilance->GPC['password2'])
        {
            refresh(HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$ilance->GPC['code'].'&userid='.$ilance->GPC['userid'].'&error_pwd_miss_match=1');
            exit();
        }
    }

    if (strstr($ilance->GPC['password'], " "))
    {
        refresh(HTTPS_SERVER.'login.php?cmd=_reset-newpassword&code='.$ilance->GPC['code'].'&userid='.$ilance->GPC['userid'].'&error_password_space=1');
        exit();
    }

	//echo "<pre>";var_dump($ilance->GPC);echo "</pre>";exit;
	$salt = construct_password_salt(5);
	$newpasswordmd5 = md5(md5($ilance->GPC['password']) . $salt);

	$ilance->db->query("UPDATE " . DB_PREFIX . "users 
		SET password = '" . $ilance->db->escape_string($newpasswordmd5) . "', 
		salt = '" . $ilance->db->escape_string($salt) . "', 
		token_code = '' 
		WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
	");

	$area_title = $phrase['_account_password_renewal_success'];
	$page_title = SITE_NAME . ' - ' . $phrase['_account_password_renewal_success'];

	print_notice($phrase['_your_account_password_was_changed'], $phrase['_you_have_successfully_renewed_the_password_for_your_online_account'], HTTPS_SERVER . $ilpage['login'], $phrase['_login_to_your_account']);
	exit();
}
// #### CHANGE IP ADDRESS PREFERENCE ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_ip-reset')
{
	$area_title = $phrase['_change_ip_preference'];
	$page_title = SITE_NAME . ' - ' . $phrase['_change_ip_preference'];

	// javascript header includes
	$headinclude .= '
<script type="text/javascript">
<!--
function validate_input(f)
{
        haveerrors = 0;
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
	
	$pprint_array = array('userid','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
	$ilance->template->fetch('main', 'login_ipaddress_renewal.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### USER REQUESTING IP PREFERENCE CHANGE ###################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-ip-change' AND isset($ilance->GPC['email']) AND !empty($ilance->GPC['email']))
{
        $area_title = $phrase['_change_ip_preference'];
	$page_title = SITE_NAME . ' - ' . $phrase['_change_ip_preference'];
                
	$sql = $ilance->db->query("
		SELECT username, secretquestion
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql);
                
		$email = $ilance->GPC['email'];
		$secret_question = stripslashes($res['secretquestion']);
		$username = stripslashes($res['username']);
		
		$headinclude .= '
<script type="text/javascript">
<!--
function validate_secret_answer(f)
{
        haveerrors = 0;
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
		
		$pprint_array = array('email','username','secret_question','userid','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'login_ipaddress_change.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = $phrase['_change_ip_preference_denied'];
                $page_title = SITE_NAME . ' - ' . $phrase['_change_ip_preference_denied'];
                
                print_notice($phrase['_change_ip_preference_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_ip-reset', $phrase['_retry']);
                exit();
	}
}

// #### USER CHANGING IP PREFERENCE ############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ipaddress-change' AND isset($ilance->GPC['secretanswer']) AND isset($ilance->GPC['email']) AND isset($ilance->GPC['username']))
{
	$secretanswer = strip_tags($ilance->GPC['secretanswer']);
	$secretanswermd5 = md5($secretanswer);
	$email = strip_tags($ilance->GPC['email']);
	$username = strip_tags($ilance->GPC['username']);
        
	$sql = $ilance->db->query("
		SELECT user_id, secretanswer
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($email) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql);
                
		$userid = $res['user_id'];
		$secretanswerdb = stripslashes($res['secretanswer']);
	}
	else
	{
		$area_title = $phrase['_change_ip_preference_denied'];
                $page_title = SITE_NAME . ' - ' . $phrase['_change_ip_preference_denied'];
                
                print_notice($phrase['_change_ip_preference_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_ip-reset', $phrase['_retry']);
                exit();
	}
	
	if ($secretanswermd5 == $secretanswerdb)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET iprestrict = '0'
			WHERE user_id = '" . intval($userid) . "'
		");
		
		$area_title = $phrase['_ip_address_preference_changed'];
		$page_title = SITE_NAME . ' - ' . $phrase['_ip_address_preference_changed'];
		
		print_notice($phrase['_ip_address_preference_changed'], $phrase['_you_successfully_reset_the_ip_address_preference_for_your_account'], HTTPS_SERVER . $ilpage['login'], $phrase['_login_to_your_account']);
		exit();
	}
	else
	{
		$area_title = $phrase['_change_ip_preference_denied'];
                $page_title = SITE_NAME . ' - ' . $phrase['_change_ip_preference_denied'];
                
                print_notice($phrase['_change_ip_preference_denied'], $phrase['_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes'], HTTPS_SERVER . $ilpage['login'] . '?cmd=_ip-reset', $phrase['_retry']);
                exit();
	}
}

// #### LOGIN AREA MENU ########################################################
else
{
	if (empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off') {
		//refresh();
		$redirect_url='';
		if($redirect!='')
		{
			$redirect_url="?redirect=".urlencode($ilance->GPC['redirect']);
		}
		header('HTTP/1.1 301 Moved Permanently');
	    header('Location: '.HTTPS_SERVER.$ilpage['login'].$redirect_url);
	    exit();
	}
	$area_title = $phrase['_login_area_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_login_area_menu'];
		
	$onload .= (empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? 'document.login.username.focus();' : 'document.login.password.focus();';
		
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		refresh($ilpage['main']);
		exit();
	}
	else
	{
		$rid = (!empty($_COOKIE[COOKIE_PREFIX . 'rid']))
			? trim($_COOKIE[COOKIE_PREFIX . 'rid'])
			: '';
		
		$user_cookie = (!empty($_COOKIE[COOKIE_PREFIX . 'username']))
			? $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])
			: '';
		
		$lastvisit = (!empty($_COOKIE[COOKIE_PREFIX . 'lastvisit']))
			? print_date($_COOKIE[COOKIE_PREFIX . 'lastvisit'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0)
			: $phrase['_never'];
		
		$pprint_array = array('lastvisit','remember_checked','input_style','redirect','referer','rid','login','user_cookie','enter_username','enter_password','buyer_login','seller_login','clientip','rem_cookies','how_t','in_y','place_bids','register_as_provider','register_as_buyer','retreive_password','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','watchlist_id');
		
		$ilance->template->fetch('main', 'login.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>