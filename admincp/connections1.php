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
        'tabfx',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['connections'] => $ilcrumbs[$ilpage['connections']]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['connections']);

$area_title = 'Viewing Connection Activity';
$page_title = SITE_NAME . ' - Connection Activity';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	// #### KICK SESSION #######################################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_kick-session' AND !empty($ilance->GPC['sid']))
	{
                foreach ($ilance->GPC['sid'] AS $session)
                {
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "sessions
                                WHERE sesskey = '".$ilance->db->escape_string($session)."'
                                LIMIT 1
                        ");
                }
                
                print_action_success($phrase['_the_requested_sessions_have_been_kicked'], $ilpage['connections']);
                exit();
	}
	
	// #### CONNECTION ACTIVITY ################################################
	else
	{
		($apihook = $ilance->api('admincp_connection_management')) ? eval($apihook) : false;
		
		function my_bcmod($x, $y)
		{
			$take = 5;
			$mod = '';
			do
			{
			    $a = (int)$mod . mb_substr($x, 0, $take);
			    $x = mb_substr( $x, $take );
			    $mod = $a % $y;
			}
			while (mb_strlen($x));
			return (int)$mod;
		}
			
		function sec2hours($num_secs)
		{
			$htmlx = '';
			$hours = intval(intval($num_secs) / 3600);
			if ($hours >= 1)
			{
			    $htmlx = $hours;
			    $htmlx .= 'h, ';
			}
			
			$minutes = bcmod(($num_secs / 60),60);
			if ($minutes < 10)
			{
			    $htmlx .= '0';
			}
			$htmlx .= $minutes;
			$htmlx .= 'm, ';
	    
			$seconds = bcmod($num_secs,60);
			if ($seconds < 10)
			{
			    $htmlx .= '0';
			}			
			$htmlx .= $seconds . 's';
                        
			return $htmlx;
		}
			
		$show['nomembers'] = $show['noguests'] = $show['noadmins'] = $show['nocrawlers'] = false;
                $row_count = 0;
                
		$sqlguest = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "sessions
                        WHERE userid = '0' AND isrobot = '0'
                        GROUP BY token
                        ORDER BY lastclick ASC
		");
		if ($ilance->db->num_rows($sqlguest) > 0)
		{
			while ($row = $ilance->db->fetch_array($sqlguest, DB_ASSOC))
			{
				$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['sesskey'] . '" />';
				$row['username'] = $phrase['_guest'];
				$row['location_title'] = stripslashes($row['title']);
                                $row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>URL Spy</strong></div><div>' . (!empty($row['url']) ? addslashes(print_string_wrap(urldecode($row['url']), $limit = 35)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
				$row['ip_address'] = $row['ipaddress'];
				$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_browser'] . '</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>' . $phrase['_browser_agent'] . '</strong></div><div>' . stripslashes($row['agent']) . '</div><div style=padding-top:4px><strong>' . $phrase['_session_id'] . '</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>' . $phrase['_token_hash'] . '</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
				$row['lastclick'] = sec2hours(TIMESTAMPNOW - $row['lastclick']);
				$row['duration'] = sec2hours(TIMESTAMPNOW - $row['firstclick']);
				$row['expiresin'] = sec2hours($row['expiry'] - TIMESTAMPNOW);
				$row['class']  = ($row_count % 2) ? 'alt1' : 'alt1';
				$guest_connection_results[] = $row;
				$row_count++;
			}
			unset($row);
		}
		else
		{
			$show['noguests'] = true;
		}
                $guestsonline = $row_count;
		unset($sqlguest);
		
                $row_count = 0;
		$sqlmember = $ilance->db->query("
                        SELECT sess.*
                        FROM " . DB_PREFIX . "users AS user,
                        " . DB_PREFIX . "sessions AS sess
                        WHERE sess.userid = user.user_id
                                AND sess.isuser = '1'
                                AND sess.userid > 0
                        GROUP BY sess.token
                        ORDER BY sess.lastclick ASC
		");
		if ($ilance->db->num_rows($sqlmember) > 0)
		{
			while ($row = $ilance->db->fetch_array($sqlmember, DB_ASSOC))
			{
				$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['sesskey'] . '" />';
				$row['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['userid'] . '">' . fetch_user('username', $row['userid']) . '</a>';
				//$row['location'] = urldecode($row['url']);
				$row['location_title'] = stripslashes($row['title']);
                                $row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>URL Spy</strong></div><div>' . (!empty($row['url']) ? addslashes(print_string_wrap(urldecode($row['url']), $limit = 35)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
				$row['ip_address'] = $row['ipaddress'];
				$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_browser'] . '</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>' . $phrase['_browser_agent'] . '</strong></div><div>' . stripslashes($row['agent']) . '</div><div style=padding-top:4px><strong>' . $phrase['_session_id'] . '</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>' . $phrase['_token_hash'] . '</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
				$row['lastclick'] = sec2hours(TIMESTAMPNOW - $row['lastclick']);
				$row['duration'] = sec2hours(TIMESTAMPNOW - $row['firstclick']);
				$row['expiresin'] = sec2hours($row['expiry'] - TIMESTAMPNOW);
				$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
				$member_connection_results[] = $row;
				$row_count++;
			}
			unset($row);
		}
		else
		{
			$show['nomembers'] = true;
		}
		$membersonline = $row_count;
                
                $row_count = 0;
		$sqladmin = $ilance->db->query("
			SELECT sess.*
                        FROM " . DB_PREFIX . "users AS user,
                        " . DB_PREFIX . "sessions AS sess
                        WHERE sess.userid = user.user_id
                                AND sess.userid > 0
                                AND sess.isadmin = '1'
                        GROUP BY sess.token
                        ORDER BY sess.lastclick ASC
		");
		if ($ilance->db->num_rows($sqladmin) > 0)
		{
			while ($row = $ilance->db->fetch_array($sqladmin, DB_ASSOC))
			{
				$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['sesskey'] . '" />';
				$row['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['userid'] . '">' . fetch_user('username', $row['userid']) . '</a>';
				//$row['location'] = urldecode($row['url']);
				$row['location_title'] = stripslashes($row['title']);
                                $row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>URL Spy</strong></div><div>' . (!empty($row['url']) ? addslashes(print_string_wrap(urldecode($row['url']), $limit = 35)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
				$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_browser'] . '</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>' . $phrase['_browser_agent'] . '</strong></div><div>' . stripslashes($row['agent']) . '</div><div style=padding-top:4px><strong>' . $phrase['_session_id'] . '</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>' . $phrase['_token_hash'] . '</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
				$row['ip_address'] = $row['ipaddress'];
				$row['lastclick'] = sec2hours(TIMESTAMPNOW - $row['lastclick']);
				$row['duration'] = sec2hours(TIMESTAMPNOW - $row['firstclick']);
				$row['expiresin'] = sec2hours($row['expiry'] - TIMESTAMPNOW);
				$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
				$admin_connection_results[] = $row;
				$row_count++;
			}
			unset($row);
		}
		else
		{
			$show['noadmins'] = true;
		}
                $staffonline = $row_count;
		unset($sqlmember);
		
                $row_count = 0;
		$sqlcrawlers = $ilance->db->query("
			SELECT *
                        FROM " . DB_PREFIX . "sessions
                        WHERE userid = '0'
                                AND isrobot = '1'
                        GROUP BY token
                        ORDER BY lastclick ASC
		");
		if ($ilance->db->num_rows($sqlcrawlers) > 0)
		{
			while ($row = $ilance->db->fetch_array($sqlcrawlers, DB_ASSOC))
			{
				$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['sesskey'] . '" />';
				$row['username'] = fetch_search_crawler_title($row['agent']);
				//$row['location'] = !empty($row['url']) ? urldecode($row['url']) : HTTP_SERVER;
				$row['location_title'] = !empty($row['title']) ? stripslashes($row['title']) : $phrase['_unknown'];
                                $row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>URL Spy</strong></div><div>' . (!empty($row['url']) ? addslashes(print_string_wrap(urldecode($row['url']), $limit = 35)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
				$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>' . $phrase['_browser'] . '</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>' . $phrase['_browser_agent'] . '</strong></div><div>' . stripslashes($row['agent']) . '</div><div style=padding-top:4px><strong>' . $phrase['_session_id'] . '</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>' . $phrase['_token_hash'] . '</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
				$row['ip_address'] = $row['ipaddress'];
				$row['lastclick'] = sec2hours(TIMESTAMPNOW - $row['lastclick']);
				$row['duration'] = sec2hours(TIMESTAMPNOW - $row['firstclick']);
				$row['expiresin'] = sec2hours($row['expiry'] - TIMESTAMPNOW);
				$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';                                
				$crawler_connection_results[] = $row;
				$row_count++;
			}
			unset($row);
		}
		else
		{
			$show['nocrawlers'] = true;
		}
                $robotsonline = $row_count;
		unset($sqlcrawlers);
                
                $pprint_array = array('buildversion','ilanceversion','login_include_admin','guestsonline','membersonline','staffonline','robotsonline','global_connectionsettings','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                ($apihook = $ilance->api('admincp_connections_end')) ? eval($apihook) : false;
			
		$ilance->template->fetch('main', 'connections.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','guest_connection_results','member_connection_results','admin_connection_results','crawler_connection_results'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>