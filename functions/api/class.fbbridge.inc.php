<?php

class fbbridge
{
	var $config = array();	
	var $modgroup = 'fbbridge';
	
	var $conn;
	var $session;
	var $uid;
	var $me;
	var $encodedSession;
	var $fbmlButton = "";
	
	function fbbridge()
	{
		global $ilance;	
	
		$query = $ilance->db->query("
				SELECT configtable, version
				FROM " . DB_PREFIX . "modules_group
				WHERE modulegroup = '" . $this->modgroup . "'
				LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) > 0)
		{
				$table = $ilance->db->fetch_array($query);
				if (!empty($table['configtable']))
				{
						$sql = $ilance->db->query("
								SELECT name, value
								FROM ".DB_PREFIX . $table['configtable'],
						0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
								while ($res = $ilance->db->fetch_array($sql))
								{
										$this->config[$res['name']] = $res['value'];
								}
								unset($res);
								$this->config['version'] = $table['version'];
						}
				}
		}
		
		require_once(DIR_FUNCTIONS . 'facebook.php');
		
		// Create our Application instance (replace this with your appId and secret).
		$this->conn = new Facebook(
								 array(  
									   'appId'  => $this->config['appId'],  
								   	   'secret' => $this->config['secret'],
									   'cookie' => true,)
								 );
		
		$this->session = $this->conn->getSession();
		$this->me = null;
		
		// Session based API call.
		if ($this->session) 
		{  
			try 
			{    				
				$this->uid = $this->conn->getUser();
				$this->me = $this->conn->api('/me');  
			} 
			catch (FacebookApiException $e) 
			{   
				error_log($e);  
			}
		}
		
		if ($this->me) 
		{  
			$this->logoutUrl = $this->conn->getLogoutUrl();
			$this->fbmlButton = "<fb:login-button autologoutlink=\"true\">Log Out</fb:login-button>";
		} 
		else 
		{  
			$this->loginUrl = $this->conn->getLoginUrl();
			$this->fbmlButton = "<fb:login-button perms=\"email,publish_stream,status_update,user_birthday, user_location,user_work_history\">Log In</fb:login-button>";
		}
		//print_r($this->me);
		$this->encodedSession = json_encode($this->session);
	}
	
	
	function UnpackFacebookCookie() {

		if (!isset($_COOKIE['fbs_' .  $this->config['appId']])) {
	
			return NULL;
		}
	
		$parameters = array();
	
		parse_str(trim($_COOKIE['fbs_' . $this->config['appId']], '\\"'),
				  $parameters);
	
		ksort($parameters);
	
		$payload = '';
	
		foreach ($parameters as $key => $value) {
			if ($key != 'sig') {
				$payload .= $key . '=' . $value;
			}
		}
	
		if (md5($payload . $this->config['secret']) != $parameters['sig']) {
	
			return NULL;
		}
	
		return $parameters;
	}
	
	function generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) 
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if ( $special_chars )
			$chars .= '!@#$%^&*()';
		if ( $extra_special_chars )
			$chars .= '-_ []{}<>~`+=,.;:/?|';
	
		$password = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$password .= substr($chars, rand(0, strlen($chars) - 1), 1);
		}
	
		// random_password filter was previously in random_password function which was deprecated
		return $password;
	}
	
	
	// login
	function login_via_facebookid($id)
	{
		global $ilance, $ilconfig;
		// default subscription params
		$userinfo['roleid'] = -1;
		$userinfo['subscriptionid'] = $userinfo['cost'] = 0;
		$userinfo['active'] = 'no';
		
		$sql = $ilance->db->query("
			SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
			LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			WHERE facebookid = '" . $ilance->db->escape_string($id) . "'
			GROUP BY facebookid
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
			// update last seen for this member
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
			");
			
			if ($userinfo['status'] == 'active')
			{
				// ip restriction
				if ($userinfo['iprestrict'] AND !empty($userinfo['ipaddress']))
				{
					if (IPADDRESS != $userinfo['ipaddress'])
					{
						refresh(HTTPS_SERVER . $ilpage['login'] . '?error=iprestrict');	
						exit();	
					}
				}
				// create user session
				$_SESSION['ilancedata'] = array(
						'user' => array(
						'isadmin' => $userinfo['isadmin'],
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
				
				// user has chosen the marketplace to remember them
				set_cookie('userid', $ilance->crypt->three_layer_encrypt($userinfo['user_id'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
				set_cookie('password', $ilance->crypt->three_layer_encrypt($userinfo['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
				set_cookie('username', $ilance->crypt->three_layer_encrypt($userinfo['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), true);
					
				// remember users last visit and last hit activity regardless of remember me preference
				set_cookie('lastvisit', DATETIME24H, true);
				set_cookie('lastactivity', DATETIME24H, true);
				set_cookie('radiuszip', handle_input_keywords(format_zipcode($userinfo['zip_code'])), true);
			}
			
			return true;
		}
		
		return false;
	}
	
	function print_fbbutton()
	{
		
		global $ilance,$ilconfig, $phrase;
		
		$newphrase = array(
						    
						    '_encodedSession' => $ilance->fb->encodedSession,
						    '_fb_button' => $ilance->fb->fbmlButton,
						    '_sitename' => addslashes($ilconfig['globalserversettings_sitename']), 
							'_fb_message' => 'I just joined ' . $ilconfig['globalserversettings_sitename'] . ' the online auction site. You should try it too.', 
							'_caption' => addslashes($ilconfig['template_metatitle']) , 
							'_description' => addslashes($ilconfig['template_metadescription']), 
							'_fb_url' => HTTPS_SERVER,   
							'_url_text' => $ilconfig['globalserversettings_sitename'] , 
							'_prompt' => 'Join ' . $ilconfig['globalserversettings_sitename']
							
							);
		
		$phrase = array_merge($phrase, $newphrase);
					
					
					
					
		
		$html = $ilance->fb->fbmlButton . '
				<div id="fb-root"></div>
				<script type="text/javascript">
					FB.init({ appId: \''. $ilance->fb->config['appId'] .'\', status: true, cookie: true, xfbml:  true });
				
					FB.Event.subscribe(\'auth.login\',    function(response) {
															jQuery.ajax({
																			async: false,
																			data: ({ operation: "login" }),
																			dataType: "json",
																			global: false,
																			success: function(reply) {
																				if (!reply.success) {
																					alert(reply.message);
																				}else{
																					
																					if(reply.message == \'loggedin\')
																					{
																						window.location.href = \'main.php?cmd=cp\';
																						//window.location.reload();
																					}
																					else {
																						alert(reply.message);
																						FB.ui(
																						{
																							method: \'stream.publish\',
																							message: \''.$phrase['_fb_message'].'\',
																							attachment: {
																								name: \''.$phrase['_sitename'].'\',
																								caption: \''.$phrase['_caption'].'\',
																								description: \'('.$phrase['_description'].')\',
																								href: \''.$phrase['_fb_url'].'\'
																							},
																							action_links: [
																								{ text: \''.$phrase['_url_text'].'\', href: \''.$phrase['_fb_url'].'\'}
																							],
																							user_prompt_message: \''.$phrase['_prompt'].'\'
																						},
																						function(response) {
																							window.location.reload();
																						});
																					}
																					
																				}
																				
																			},
																			type: "POST",
																			url: "'.$phrase['_fb_url'].'ajax.php?cmd=fbbridge&subcmd=login"
																		});
														});
					FB.Event.subscribe(\'auth.logout\',   function(response) {
																jQuery.ajax({
																	async: false,
																	data: ({ operation: "login" }),
																	dataType: "json",
																	global: false,
																	type: "POST",
																	url: "'.$phrase['_fb_url'].'ajax.php?cmd=fbbridge&subcmd=logout",
																	success: function(reply) {
																		if (!reply.success) 
																		{
																			alert(reply.message);
																		}
																	}
																});
																window.location.reload();
														});
				</script>';
				
				return $html;
	}
	
	
	function logout()
	{
		global $ilance;
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
		// destroy entire member session
		session_unset();
		$ilance->sessions->session_destroy(session_id());
		session_destroy();	
		
	}
	/**
	* Consruct Settings
	* constructs the settings form for the admincp
	*/
	function construct_settings()
	{
		global $ilance, $phrase;
			
		$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "fbbridge_configuration", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
				$html = '';
				while ($res = $ilance->db->fetch_array($sql))
				{
				
					$html .= '<tr>';
					$html .= '<td width="27%" valign="top" nowrap>'.stripslashes($res['description']).'</td>';
					$html .= '<td width="34%" align="left" valign="top">';
					if($res['inputtype'] == "yesno")
					{	
						$html .= '<input type="radio" name="'.$res['name'].'" id="'.$res['name'].'" value="1" ';
						if ($res['value'])
						{
								$html .= 'checked="checked">';
						}
						else
						{
								$html .= '>';
						}
						$html .= '<label for="'.$res['name'].'">'.$phrase['_yes'].'</label>';

						$html .= '<input name="'.$res['name'].'" id="'.$res['name'].'2" type="radio" value="0" ';
						if ($res['value'] == 0)
						{
								$html .= 'checked="checked">';
						}
						else
						{
								$html .= '>';
						}
						
						$html .= '<label for="'.$res['name'].'2">'.$phrase['_no'].'</label>';
					}
					
					if($res['inputtype'] == 'textarea')
					{
						$html .= '<textarea name="'.$res['name'].'" id="'.$res['name'].'" style="width:250px; height:100px;">'.$res['value'].'</textarea>';
					}
					
					if($res['inputtype'] == 'text')
					{
						$html .='<input style="padding:2px; height:15px; width:150px; font-family: verdana;" id="'.$res['name'].'" type="text" name="'.$res['name'].'"  value="'.$res['value'].'" />';
					}
					$html .= '</td>';
					$html .= '</tr>';
					
				}
				$html .= '<tr>';
				$html .= '<td width="27%" height="15">'.stripslashes($res['description']).'</td>';
				$html .= '</tr>';
		}
		return $html;
	}
	
	
}
?>