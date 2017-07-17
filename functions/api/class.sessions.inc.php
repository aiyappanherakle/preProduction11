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

/**
* defines session host
*/
define('SESSIONHOST', mb_substr(IPADDRESS, 0, 15));

/**
* defines if we're in the admincp
*/
if (defined('LOCATION') AND LOCATION == 'admin')
{
        define('IN_ADMIN_CP', true);
}
else
{
        define('IN_ADMIN_CP', false);    
}

/**
* Session class to perform the majority of session functionality in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class sessions
{
        /**
	* The ILance session registry object
	*
	* @var	    $registry
	*/
        var $registry = null;
        
        /**
        * Timeout in minutes for guests and visitors
        *
        * @var	    $guesttimeout
        */
        var $guesttimeout = 30;
        
        /**
        * Timeout in minutes for logged in members
        *
        * @var	    $membertimeout
        */
        var $membertimeout = 90;
        
        /**
        * Timeout in minutes for logged in administrators
        *
        * @var	    $admintimeout
        */
        var $admintimeout = 90;
        
        /**
        * Timeout in minutes for crawlers and search bots
        *
        * @var	    $crawlertimeout
        */
        var $crawlertimeout = 5;
        
        /**
        * Constructor
        *
        * @param       $registry	ILance registry object
        */
        function __construct($registry)
        {
                $this->registry =& $registry;
                
                /**
                * initialize our session handling datastore
                */
                session_set_save_handler(
                        array(&$this, 'session_open'),
                        array(&$this, 'session_close'),
                        array(&$this, 'session_read'),
                        array(&$this, 'session_write'),
                        array(&$this, 'session_destroy'),
                        array(&$this, 'session_gc')
                );
        }
        
        /**
        * Encrypt and compress the serailized session data
        *
        * @param       array        session data
        * @return      string       Encrypted session data
        */        
        function encrypt($data = '')
        {
                if ($this->sessionencrypt)
                {
                        global $ilconfig;			
                        return $this->registry->crypt->three_layer_encrypt($data, $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                }
                
                return $data;
        }
    
        /**
        * Decrypt and return the encrypted or serialized session data
        *
        * @param       string       encrypted session data
        * @return      array        Session data
        */ 
        function decrypt($data = '')
        {
                if ($this->sessionencrypt)
                {
                        global $ilconfig;
                        return $this->registry->crypt->three_layer_decrypt($data, $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                }
                
                return $data;
        }
        
        /**
        * Fetch session first click if applicable
        *
        * @param       string       session key
        * 
        * @return      string       Returns first click timestamp
        */
        function session_firstclick($sessionkey = '')
        {
                $sql = $this->registry->db->query("
                        SELECT firstclick
                        FROM " . DB_PREFIX . "sessions
                        WHERE sesskey = '" . $this->registry->db->escape_string($sessionkey) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($this->registry->db->num_rows($sql) > 0)
		{
			$res = $this->registry->db->fetch_array($sql, DB_ASSOC);
			return $res['firstclick'];
		}
                
                return TIMESTAMPNOW;
        }
	
	/**
        * Session open handler
        *
        * @return      bool         true if session data could be opened
        */
        function session_open($savepath = '', $sessioname = '')
        {
                return true;
        }
    
        /**
        * Session close handler
        *
        * @return      bool         true if session data could be closed
        */
        function session_close()
        {
                $this->session_gc();                
                return true;
        }

        /**
        * Session read handler is called once the script is loaded
        *
        * @param       string       session key
        * 
        * @return      string       value from the session table
        */
        function session_read($sessionkey)
        {
                $result = $this->registry->db->query("
                        SELECT value
                        FROM " . DB_PREFIX . "sessions
                        WHERE sesskey = '" . $this->registry->db->escape_string($sessionkey) . "'
                                AND expiry > " . TIMESTAMPNOW . "
                ", 0, null, __FILE__, __LINE__);
                if (list($value) = $this->registry->db->fetch_row($result))
                {
                        return $value;
                }
                
                return '';
        }
        
        /**
        * Session write handler is called once the script is finished executing
        *
        * @param       string       session key
        * @param       string       session data we would like to update
        */
        function session_write($sessionkey = '', $sessiondata = '')
        {
                global $ilconfig, $area_title, $show, $phrase;

                $session = array();
                
                $skipsession = array('lancealert', 'lancealert_checkauth', 'cron');

                if (defined('SKIP_SESSION') AND SKIP_SESSION OR defined('LOCATION') AND in_array(LOCATION, $skipsession))
                {
                        return true;
                }
                
                // if we've never been here before, we'll create a "last visit" cookie to remember the user
                if (empty($_COOKIE[COOKIE_PREFIX . 'lastvisit']))
                {
                        set_cookie('lastvisit', DATETIME24H, true);
                }
                
                // we will continue to update our last activity cookie on each page hit
                set_cookie('lastactivity', DATETIME24H, true);
                
                $session['ilancedata'] = unserialize(str_replace('ilancedata|', '', $sessiondata));
                
		$scriptname = !empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
                $querystring = print_hidden_fields(true, array(), true);
		$firstclick = $this->session_firstclick($sessionkey);
                
                $session['ilancedata']['user']['url'] = $scriptname . $querystring;
                $session['ilancedata']['user']['area_title'] = !empty($area_title) ? $area_title : $phrase['_unknown'];
                
                // #### SEARCH BOT TRACKER #####################################
                if (isset($show['searchengine']) AND $show['searchengine'])
                {
                        $this->registry->db->query("
                                REPLACE " . DB_PREFIX . "sessions
                                (sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
                                VALUES(
                                '" . $this->registry->db->escape_string($sessionkey) . "',
                                '" . (TIMESTAMPNOW + ($this->crawlertimeout * 60)) . "',
                                '" . $this->registry->db->escape_string($sessiondata) . "',
                                '0',
                                '0',
                                '0',
                                '1',
                                '0',
                                '" . intval($session['ilancedata']['user']['languageid']) . "',
                                '" . intval($session['ilancedata']['user']['styleid']) . "',
                                '" . $this->registry->db->escape_string(USERAGENT) . "',
                                '" . TIMESTAMPNOW . "',
                                '" . $this->registry->db->escape_string(IPADDRESS) . "',
                                '" . $this->registry->db->escape_string($session['ilancedata']['user']['url']) . "',
                                '" . $this->registry->db->escape_string($session['ilancedata']['user']['area_title']) . "',
                                '" . $this->registry->db->escape_string($firstclick) . "',
                                '" . $this->registry->db->escape_string($this->registry->common->fetch_browser_name()) . "',
                                '" . $this->registry->db->escape_string(TOKEN) . "',
				'" . $this->registry->db->escape_string(SITE_ID) . "')
                        ", 0, null, __FILE__, __LINE__);
                }
                
                // #### USER & STAFF TRACKER ###################################
                else if (!empty($session['ilancedata']['user']['userid']))
                {
                        $expiry  = ((IN_ADMIN_CP AND $session['ilancedata']['user']['isadmin']) ? "'" . (TIMESTAMPNOW + ($this->admintimeout * 60)) . "'," : "'" . (TIMESTAMPNOW + ($this->membertimeout * 60)) . "',");
                        $isuser  = ((IN_ADMIN_CP AND $session['ilancedata']['user']['isadmin']) ? "'0'," : "'1',");
                        $isadmin = ((IN_ADMIN_CP AND $session['ilancedata']['user']['isadmin']) ? "'1'," : "'0',");
                        
                        $this->registry->db->query("
                                REPLACE " . DB_PREFIX . "sessions
                                (sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
                                VALUES(
                                '" . $this->registry->db->escape_string($sessionkey) . "',
                                $expiry
                                '" . $this->registry->db->escape_string($sessiondata) . "',
                                '" . $session['ilancedata']['user']['userid'] . "',
                                $isuser
                                $isadmin
                                '0',
                                '0',
                                '" . intval($session['ilancedata']['user']['languageid']) . "',
                                '" . intval($session['ilancedata']['user']['styleid']) . "',
                                '" . $this->registry->db->escape_string(USERAGENT) . "',
                                '" . TIMESTAMPNOW . "',
                                '" . $this->registry->db->escape_string(IPADDRESS) . "',
                                '" . $this->registry->db->escape_string($session['ilancedata']['user']['url']) . "',
                                '" . $this->registry->db->escape_string($session['ilancedata']['user']['area_title']) . "',
                                '" . $this->registry->db->escape_string($firstclick) . "',
                                '" . $this->registry->db->escape_string($this->registry->common->fetch_browser_name()) . "',
                                '" . $this->registry->db->escape_string(TOKEN) . "',
				'" . $this->registry->db->escape_string(SITE_ID) . "')
                        ", 0, null, __FILE__, __LINE__);
                        
                        unset($expiry, $isadmin);
                }
                
                // #### GUEST TRACKER ##########################################
                else
                {
                        $this->registry->db->query("
                                REPLACE " . DB_PREFIX . "sessions
                                (sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
                                VALUES(
                                '" . $this->registry->db->escape_string($sessionkey) . "',
                                '" . (TIMESTAMPNOW + ($this->guesttimeout * 60)) . "',
                                '" . $this->registry->db->escape_string($sessiondata) . "',
                                '0',
                                '0',
                                '0',
                                '0',
                                '0',
                                '" . intval($session['ilancedata']['user']['languageid']) . "',
                                '" . intval($session['ilancedata']['user']['styleid']) . "',
                                '" . $this->registry->db->escape_string(USERAGENT) . "',
                                '" . TIMESTAMPNOW . "',
                                '" . $this->registry->db->escape_string(IPADDRESS) . "',
                                '" . $this->registry->db->escape_string($session['ilancedata']['user']['url']) . "',
                                '" . $this->registry->db->escape_string($session['ilancedata']['user']['area_title']) . "',
                                '" . $this->registry->db->escape_string($firstclick) . "',
                                '" . $this->registry->db->escape_string($this->registry->common->fetch_browser_name()) . "',
                                '" . $this->registry->db->escape_string(TOKEN) . "',
				'" . $this->registry->db->escape_string(SITE_ID) . "')
                        ", 0, null, __FILE__, __LINE__);
                }
                unset($scriptname, $querystring);             
        
                return true;
        }
    
        /**
        * Session destroy handler
        *
        * @param       string       session key
        * @return      void
        */
        function session_destroy($sessionkey = '')
        {
                $this->registry->db->query("
                        DELETE
                        FROM " . DB_PREFIX . "sessions
                        WHERE sesskey = '" . $this->registry->db->escape_string($sessionkey) . "'
                ", 0, null, __FILE__, __LINE__);
                
                return true;
        }
    
        /**
        * Session garbage collection handler
        *
        * @return      void
        */
        function session_gc($maxlifetime = '')
        {
                $this->registry->db->query("
                        DELETE
                        FROM " . DB_PREFIX . "sessions
                        WHERE expiry < " . TIMESTAMPNOW
                , 0, null, __FILE__, __LINE__);
                
                return true;
        }
        
        function init_remembered_session()
        {
                global $show, $ilconfig;
                
                $session = array();
                $noremember = array('registration','attachment','login','admin','cron','ipn','ajax','lancealert');
                
                if (empty($_SESSION['ilancedata']['user']['userid']) AND !empty($_COOKIE[COOKIE_PREFIX . 'password']) AND !empty($_COOKIE[COOKIE_PREFIX . 'username']) AND !empty($_COOKIE[COOKIE_PREFIX . 'userid']) AND defined('LOCATION') AND !in_array(LOCATION, $noremember))
                {
                        $sql = $this->registry->db->query("
                                SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
                                FROM " . DB_PREFIX . "users AS u
                                LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
                                LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
                                LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
                                LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
                                WHERE username = '" . $this->registry->db->escape_string($this->registry->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "'
                                        AND password = '" . $this->registry->db->escape_string($this->registry->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "'
                                        AND u.user_id = '" . intval($this->registry->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "'
                                        AND status = 'active'
                                GROUP BY username
                                LIMIT 1      
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->registry->db->num_rows($sql) > 0)
                        {
                                $userinfo = $this->registry->db->fetch_array($sql, DB_ASSOC);
                                
                                // globalize remember me session based on cookie username, password and userid
                                $session['ilancedata']['user'] = array(
                                        'isadmin'        => $userinfo['isadmin'],
                                        'status'         => $userinfo['status'],
                                        'userid'         => $userinfo['user_id'],
                                        'username'       => $userinfo['username'],
                                        'password'       => $userinfo['password'],
                                        'salt'           => $userinfo['salt'],
                                        'email'          => $userinfo['email'],
                                        'firstname'      => stripslashes($userinfo['first_name']),
                                        'lastname'       => stripslashes($userinfo['last_name']),
                                        'fullname'       => $userinfo['first_name'] . ' ' . $userinfo['last_name'],
                                        'address'        => ucwords(stripslashes($userinfo['address'])),
                                        'address2'       => ucwords(stripslashes($userinfo['address2'])),
                                        'fulladdress'    => ucwords(stripslashes($userinfo['address'])) . ' ' . ucwords(stripslashes($userinfo['address2'])),
                                        'city'           => ucwords(stripslashes($userinfo['city'])),
                                        'state'          => ucwords(stripslashes($userinfo['state'])),
                                        'postalzip'      => mb_strtoupper(trim($userinfo['zip_code'])),
                                        'countryid'      => intval($userinfo['country']),
                                        'country'        => print_country_name($userinfo['country']),
                                        'countryshort'   => print_country_name($userinfo['country'], mb_substr($userinfo['languagecode'], 0, 3), true),
                                        'lastseen'       => $userinfo['lastseen'],
                                        'ipaddress'      => $userinfo['ipaddress'],
                                        'iprestrict'     => $userinfo['iprestrict'],
                                        'auctiondelists' => intval($userinfo['auctiondelists']),
                                        'bidretracts'    => intval($userinfo['bidretracts']),
                                        'dob'            => $userinfo['dob'],
                                        'ridcode'        => $userinfo['rid'],
                                        'browseragent'   => USERAGENT,
                                        'serviceawards'  => intval($userinfo['serviceawards']),
                                        'productawards'  => intval($userinfo['productawards']),
					'servicesold'    => intval($userinfo['servicesold']),
                                        'productsold'    => intval($userinfo['productsold']),
                                        'rating'         => $userinfo['rating'],
                                        'languageid'     => intval($userinfo['languageid']),
                                        'languagecode'   => $userinfo['languagecode'],
                                        'slng'           => mb_substr($userinfo['languagecode'], 0, 3),
                                        'styleid'        => intval($userinfo['styleid']),
                                        'timezoneid'     => intval($userinfo['timezoneid']),
                                        'timezonedst'    => $userinfo['timezone_dst'],
                                        'distance'       => $userinfo['project_distance'],
                                        'emailnotify'    => intval($userinfo['emailnotify']),
                                        'companyname'    => stripslashes($userinfo['companyname']),
                                        'roleid'         => $userinfo['roleid'],
                                        'subscriptionid' => $userinfo['subscriptionid'],
                                        'cost'           => $userinfo['cost'],
                                        'active'         => $userinfo['active'],
                                        'currencyid'     => intval($userinfo['currencyid']),
                                        'currencyname'   => stripslashes($userinfo['currency_name']),
                                        'currencysymbol' => $this->registry->currency->currencies[$userinfo['currencyid']]['symbol_left'],
                                        'currencyabbrev' => mb_strtoupper($userinfo['currency_abbrev']),
                                        'searchoptions'  => isset($userinfo['searchoptions']) ? $userinfo['searchoptions'] : '',
                                        'captcha'        => !empty($_SESSION['ilancedata']['user']['captcha']) ? $_SESSION['ilancedata']['user']['captcha'] : '',
                                        'token'          => TOKEN,
					'siteid'         => SITE_ID,
                                );
                                
                                // update last seen for this member
                                $this->registry->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET lastseen = '" . DATETIME24H . "'
                                        WHERE user_id = '" . $userinfo['user_id'] . "'
                                ");
				
				set_cookie('radiuszip', handle_input_keywords(format_zipcode($userinfo['zip_code'])), true);
                                
                                ($apihook = $this->registry->api('remember_me_session')) ? eval($apihook) : false;
                        }
                }                
                
                if (!empty($session['ilancedata']['user']) AND is_array($session['ilancedata']['user']))
                {
                        // set the valid session with the full session array of user logged in
                        // this prevents the user from having to relogin when being rememberd via cookies
                        foreach ($session AS $key => $value)
                        {
                                $_SESSION["$key"] = $value;
                        }
                }
        }
        
        /**
        * Function to handle a user language or style switch within the marketplace.  Additionally, will update their account within the db if the user is active and logged in.  This is called from global.php.
        * Additionally, this function is responsible for setting the user's initial languageid and styleid for the active session.
        *
        * @return      void
        */
        function handle_language_style_changes()
        {
                global $myapi, $ilconfig;
                
                // #### WEB USER REQUESTING LANGUAGE CHANGE ####################
                if (isset($this->registry->GPC['language']) AND !empty($this->registry->GPC['language']))
                {
                        $ilconfig['langcode'] = urldecode(mb_strtolower(trim($this->registry->GPC['language'])));
                        
                        $langdata = $this->registry->db->query("
                                SELECT languageid, languagecode
                                FROM " . DB_PREFIX . "language
                                WHERE languagecode = '" . $this->registry->db->escape_string($ilconfig['langcode']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->registry->db->num_rows($langdata) > 0)
                        {
                                $langinfo = $this->registry->db->fetch_array($langdata);
                            
                                // are we a registered member and are we logged in?
                                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                                {
                                        // we are logged in so let's officially update users languageid preference
                                        $this->registry->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET languageid = '" . $langinfo['languageid'] . "'
                                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                // set requested language sessions
                                $_SESSION['ilancedata']['user']['languageid'] = intval($langinfo['languageid']);
                                $_SESSION['ilancedata']['user']['languagecode'] = $langinfo['languagecode'];
                                $_SESSION['ilancedata']['user']['slng'] = mb_substr($_SESSION['ilancedata']['user']['languagecode'], 0, 3);
                                
                                unset($langinfo);
                        }
                }
                
                // #### WEB USER REQUESTING STYLE CHANGE #######################
                if (isset($this->registry->GPC['styleid']) AND $this->registry->GPC['styleid'] > 0 AND defined('LOCATION') AND LOCATION != 'admin')
                {
                        $styledata = $this->registry->db->query("
                                SELECT styleid
                                FROM " . DB_PREFIX . "styles
                                WHERE styleid = '" . intval($this->registry->GPC['styleid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($this->registry->db->num_rows($styledata) > 0)
                        {
                                // are we a registered member and are we logged in?
                                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                                {
                                        // we are logged in so let's officially update users styleid preference
                                        // to ensure we remember it always
                                        $this->registry->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET styleid = '" . intval($this->registry->GPC['styleid']) . "'
                                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                // set requested style session
                                $_SESSION['ilancedata']['user']['styleid'] = intval($this->registry->GPC['styleid']);
                        }
                }
                
                if (empty($_SESSION['ilancedata']['user']['languageid']) OR empty($_SESSION['ilancedata']['user']['styleid']))
                {
                        $_SESSION['ilancedata']['user']['languageid'] = $ilconfig['globalserverlanguage_defaultlanguage'];
                        $_SESSION['ilancedata']['user']['languagecode'] = $this->registry->language->print_language_code($ilconfig['globalserverlanguage_defaultlanguage']);
                        $_SESSION['ilancedata']['user']['slng'] = $this->registry->language->print_short_language_code();
                        $_SESSION['ilancedata']['user']['styleid'] = $ilconfig['defaultstyle'];
                }
                
                // #### WEB USER CURRENCY DEFAULT ##############################
                if (empty($_SESSION['ilancedata']['user']['currencyid']))
                {
                        $_SESSION['ilancedata']['user']['currencyid'] = $ilconfig['globalserverlocale_defaultcurrency'];
                }
        }
        
        /**
        * Ensure session data is written out before classes are destroyed
        * (see http://bugs.php.net/bug.php?id=33772 for details)
        *
        * @return      void
        */
        function __destruct()
        {
                @session_write_close();    
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>