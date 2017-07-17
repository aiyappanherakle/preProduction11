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
* Auction class to perform the majority of functions dealing with anything to do with auctions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class auction
{
        /**
        * Function to print auction icons based on the selected filters of the associated auction id.
        *
        * @param       integer      auction id
        * @param       integer      auction owner id
        *
        * @return      string       HTML representation of icons for the associated auction.
        */
        function auction_icons($auctionid = 0, $ownerid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase, $ilpage, $show;
                $html = $query_fields = '';
                ($apihook = $ilance->api('auction_icons_start')) ? eval($apihook) : false;
                $sql = $ilance->db->query("
                        SELECT filter_escrow, filter_gateway, filter_offline, featured, reserve, project_details, project_state, filtered_auctiontype, bid_details, filter_budget, paymethodoptions, donation, charityid, $query_fields cid, description_videourl
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($auctionid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        // is bid placed?
                        /*
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                        {
                                $sql_bidplaced = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                            AND project_id = '" . intval($auctionid) . "'
                                            AND bidstate != 'retracted'
                                            AND bidstatus != 'declined'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_bidplaced) > 0)
                                {
                                        $html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="' . $phrase['_you_have_placed_a_bid_on_this_auction'] . '" />&nbsp;';
                                }
                        }*/
                        // is added to watchlist?
                        /*
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                        {
                                $sql_watchlist = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                            AND watching_project_id = '".intval($auctionid)."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_watchlist) > 0)
                                {
                                        $html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/watchlist.gif" border="0" alt="' . $phrase['_item_added_to_your_watchlist'] . '" id="" />&nbsp;';
                                }
                        }*/
                        // contains auction attachments?
                        /*
                        $sql6 = $ilance->db->query("
                                SELECT a.filename
                                FROM " . DB_PREFIX . "attachment a
                                LEFT JOIN " . DB_PREFIX . "projects p ON a.project_id = p.project_id
                                WHERE p.project_state = 'service'
                                        AND a.project_id = '" . intval($auctionid) . "'
                                        AND a.user_id = '" . intval($ownerid) . "'
                                        AND a.visible = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql6) > 0)
                        {
                                $res_attach = $ilance->db->fetch_array($sql6);
                                $html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="' . $phrase['_attachment'] . ': ' . stripslashes($res_attach['filename']) . '" id="" />&nbsp;';
                        }*/
                        // nonprofit supported listing
                        $html .= ($res['donation'] == '1' AND $res['charityid'] > 0 AND $res['project_state'] == 'product')
                                ? '<span title="' . $phrase['_nonprofit'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits.gif" border="0" alt="' . $phrase['_nonprofit'] . '" id="" /></span>&nbsp;'
                                : '';
                        // contains video?
                        $html .= (!empty($res['description_videourl'])
                                ? '<span title="' . $phrase['_video'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/video.gif" border="0" alt="' . $phrase['_video'] . '" id="" /></span>&nbsp;'
                                : '');
                        // non-disclosed budget (service)
                        $html .= ($res['filter_budget'] == '0' AND $res['project_state'] == 'service')
                                ? '<span title="' . $phrase['_non_disclosed_budget'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nondisclosed.gif" border="0" alt="' . $phrase['_non_disclosed_budget'] . '" id="" /></span>&nbsp;'
                                : '';
                        // is featured?
                        if ($res['featured'])
                        {
                                //$html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/dollarsign.gif" alt="'.$phrase['_featured'].'" border="0" />&nbsp;';
                        }
                        // has a reserve price?
                        $html .= ($res['reserve'])
                                ? '<span title="' . $phrase['_reserve_price'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'reserve.gif" alt="' . $phrase['_reserve_price'] . '" border="0" id="" /></span>&nbsp;'
                                : '';
                        // is realtime?
                        $html .= ($res['project_details'] == 'realtime')
                                ? '<span title="' . $phrase['_realtime_auction'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'realtime.gif" alt="' . $phrase['_realtime_auction'] . '" border="0" id="" /></span>&nbsp;'
                                : '';
                        // is by invite only?
                        $html .= ($res['project_details'] == 'invite_only')
                                ? '<span title="' . $phrase['_invite_only'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invite.gif" alt="' . $phrase['_invite_only'] . '" border="0" id="" /></span>&nbsp;'
                                : '';
                        // is proxybid enabled by default for product auctions?
                        if ($ilconfig['productbid_enableproxybid'] AND $res['project_state'] == 'product' AND $res['project_details'] != 'unique')
                        {
                                //$html .= ($ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid'])) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'proxy.gif" alt="'.$phrase['_proxy_bid_enabled'].'" border="0" />&nbsp;' : '';
                        }
                        // is buynowable?
                        if ($res['filtered_auctiontype'] == 'fixed')
                        {
                                //$html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="'.$phrase['_purchase_now'].'" border="0" />&nbsp;';				
                        }
                        // is unique bid event
                        $html .= ($res['project_details'] == 'unique')
                                ? '<span title="' . $phrase['_lowest_unique_bid_event'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/rate-icon.gif" alt="' . $phrase['_lowest_unique_bid_event'] . '" border="0" id="" /></span>&nbsp;'
                                : '';
                        // #### BID PRIVACY FILTERS ########################################
                        // sealed bidding
                        $html .= ($res['bid_details'] == 'sealed')
                                ? '<span title="' . $phrase['_sealed_bidding'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/sealed.gif" border="0" alt="' . $phrase['_sealed_bidding'] . '" id="" /></span>'
                                : '';
                        // blind bidding
                        $html .= ($res['bid_details'] == 'blind')
                                ? '<span title="' . $phrase['_blind_bidding'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blind.gif" border="0" alt="' . $phrase['_blind_bidding'] . '" id="" /></span>'
                                : '';
                        // full privacy
                        $html .= ($res['bid_details'] == 'full')
                                ? '<span title="' . $phrase['_sealed_bidding'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/sealed.gif" border="0" alt="' . $phrase['_sealed_bidding'] . '" id="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blind.gif" border="0" alt="' . $phrase['_blind_bidding'] . '" id="" /></span>'
                                : '';
                        // offers secure escrow?
                        if ($res['filter_escrow'] == '1')
                        {
                                $html .= '<span title="' . $phrase['_escrow_secured'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" alt="' . $phrase['_escrow_secured'] . '" border="0" id="" /></span>&nbsp;';
                        }
                        if ($res['filter_gateway'] == '1' AND !empty($res['paymethodoptions']) AND is_serialized($res['paymethodoptions']))
                        {
                                $paymethodoptions = unserialize($res['paymethodoptions']);
                                foreach ($paymethodoptions AS $gateway => $value)
                                {
                                        if (!empty($gateway))
                                        {
                                                $html .= '<span title="' . $phrase['_pay_me_directly_through'] . ' ' . ((isset($phrase["_$gateway"]) AND !empty($phrase["_$gateway"])) ? $phrase["_$gateway"] : SITE_NAME) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/' . $gateway . '_small.gif" alt="' . $phrase['_pay_me_directly_through'] . ' ' . ((isset($phrase["_$gateway"]) AND !empty($phrase["_$gateway"])) ? $phrase["_$gateway"] : SITE_NAME) . '" border="0" id="" /></span>&nbsp;';
                                        }
                                }
                        }
                        ($apihook = $ilance->api('auction_icons_end')) ? eval($apihook) : false;
                }
                return $html;
        }
        /**
        * Function to fetch the type (project state) of an auction.
        *
        * @param       integer      auction id
        *
        * @return      string       auction type (project state)
        */
        function fetch_auction_type($projectid = 0)
        {
                global $ilance, $myapi;
                $value = '';
                $sql = $ilance->db->query("
                        SELECT project_state
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $value = $res['project_state'];
                }
                return $value;
        }
        /**
        * Function to calculate the exact time left based on a few date and time parameters.
        *
        * @param       integer       date starts
        * @param       integer       start time
        * @param       integer       time now
        *
        * @return      string        phrased value of the time left (eg: 1h, 3m)
        */
        function calculate_time_left($datestarts, $starttime, $mytime)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                // auction has not yet started
                if ($datestarts > DATETIME24H)
                {
                        $dif = $starttime;
                        $ndays = floor($dif / 86400);
                        $dif -= $ndays * 86400;
                        $nhours = floor($dif / 3600);
                        $dif -= $nhours * 3600;
                        $nminutes = floor($dif / 60);
                        $dif -= $nminutes * 60;
                        $nseconds = $dif;
                        $sign = '+';
                        if ($starttime < 0)
                        {
                                $starttime = - $starttime;
                                $sign = '-';
                        }
                        if ($sign != '-')
                        {
                                if ($ndays != '0')
                                {
                                        $tl = $ndays . $phrase['_d_shortform'] . ', ';
                                        $tl .= $nhours . $phrase['_h_shortform'] . '+';
                                }
                                else if ($nhours != '0')
                                {
                                        $tl = $nhours . $phrase['_h_shortform'] . ', ';
                                        $tl .= $nminutes . $phrase['_m_shortform'] . '+';
                                }
                                else
                                {
                                        $tl = $nminutes . $phrase['_m_shortform'] . ', ';
                                        $tl .= $nseconds . $phrase['_s_shortform'] . '+';
                                }
                        }
                        $timeleft = '<span class="gray">' . $phrase['_starts'] . ':</span> <span class="black">' . $tl . '</span>';
                }
                // auction already started
                else
                {
                        $dif = $mytime;
                        $ndays = floor($dif / 86400);
                        $dif -= $ndays * 86400;
                        $nhours = floor($dif / 3600);
                        $dif -= $nhours * 3600;
                        $nminutes = floor($dif / 60);
                        $dif -= $nminutes * 60;
                        $nseconds = $dif;
                        $sign = '+';
                        if ($mytime < 0)
                        {
                                $mytime = - $mytime;
                                $sign = '-';
                        }
                        if ($sign == '-')
                        {
                                $tl = '<span class="gray">' . $phrase['_ended'] . '</span>';
                                $expiredauction = 1;
                        }
                        else
                        {
                                $expiredauction = 0;
                                if ($ndays != '0')
                                {
                                        $tl = $ndays . $phrase['_d_shortform'] . ', ';
                                        $tl .= $nhours . $phrase['_h_shortform'] . '+';
                                }
                                else if ($nhours != '0')
                                {
                                        $tl = $nhours . $phrase['_h_shortform'] . ', ';
                                        $tl .= $nminutes . $phrase['_m_shortform'] . '+';
                                }
                                else
                                {
                                        $tl = $nminutes . $phrase['_m_shortform'] . ', ';
                                        $tl .= $nseconds . $phrase['_s_shortform'] . '+';
                                }
                        }
                        $timeleft = $tl;
                }
                return $timeleft;
        }
        /**
        * Function to calculate the exact time left based on a few date and time parameters and prints text or flash countdown applets.
        *
        * @param       integer       project id
        * @param       string        class background color (default alt1)
        * @param       string        vert. alignment (default center)
        * @param       boolean       show the time left in text? (true or false) (default false)
        * @param       boolean       show live bids placed in applet? (true or false) (default false)
        * @param       boolean       force no flash applet (true or false) (default false)
        * @param       boolean       show the full timeleft string? (default false) ie: 2d, 1h, 3m, 5+ vs. 2d, 1h+
        *
        * @return      string        HTML representation of the countdown text or countdown flash applet
        */
        function auction_timeleft($projectid = 0, $class = 'alt1', $valign = 'center', $timeintext = 0, $showlivebids = 0, $forcenoflash = 0, $showfullformat = false)
        {
                global $ilance, $myapi, $ilconfig, $ilconfig, $phrase;
                // fetch default search options for this user so we can output the time left in text or flash
                if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
                {
                        $searchoptions = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                }
                else
                {
                        $searchoptions['showtimeas'] = 'static';
                }
                // background color
                $bgcolor = $ilance->styles->fetch_css_element($class, $property = 'background', $csstype = 'csscommon');
                if (empty($bgcolor))
                {
                        $use['bgcolor'] = '#FFFFFF';
                }
                $use['bgcolor'] = $bgcolor;
                // flash text alignment
                $use['valign'] = (isset($valign)) ? $valign : 'center';
                $sql = $ilance->db->query("
                        SELECT date_starts, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $result = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($result['date_starts'] > DATETIME24H)
                        {
                                // auction is scheduled to start later
                                if ($ilconfig['globalauctionsettings_showflashcountdown'] AND defined('LOCATION') AND LOCATION != 'admin' AND $forcenoflash != 1 AND !empty($_SESSION['ilancedata']['user']['slng'])
                                        // opera 9.10 currently not supporting flash via FlashObject.. :-(
                                        AND !$ilance->common->is_webbrowser('opera')
                                        // and this user has chosen view time left as flash from his/her search options
                                        AND $searchoptions['showtimeas'] == 'flash')
                                {
                                        $uniqueid = rand(1, 9999);
                                        $result['timeleft'] = '
<div id="applet' . intval($projectid) . '-' . $uniqueid . '"></div>
<script type="text/javascript">
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/countdown.swf", "applet' . intval($projectid) . '-' . $uniqueid . '", "110", "19", "8,0,0,0", "' . $use['bgcolor'] . '");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("flashvars", "languageConfig=' . DIR_FUNCT_NAME . '/' . DIR_XML_NAME . '/livebid_' . $_SESSION['ilancedata']['user']['slng'] . '.xml&prId=' . intval($projectid) . '&valign=' . $use['valign'] . '&timeintext=' . $timeintext . '&showlivebids=' . $showlivebids . '&sId=' . session_id() . '&rand=' . rand(100000, 999999) . '");
fo.addParam("menu", "false");
fo.write("applet' . intval($projectid) . '-' . $uniqueid . '");
</script>';
                                }
                                else 
                                {
                                        $dif = $result['starttime'];
                                        $ndays = floor($dif / 86400);
                                        $dif -= $ndays * 86400;
                                        $nhours = floor($dif / 3600);
                                        $dif -= $nhours * 3600;
                                        $nminutes = floor($dif / 60);
                                        $dif -= $nminutes * 60;
                                        $nseconds = $dif;                                        
                                        $sign = '+';
                                        if ($result['starttime'] < 0)
                                        {
                                                $result['starttime'] = - $result['starttime'];
                                                $sign = '-';
                                        }		    
                                        if ($sign != '-')
                                        {
                                                if ($ndays != '0')
                                                {
                                                        $timeleft = $ndays . $phrase['_d_shortform'] . ', ' . $nhours . $phrase['_h_shortform'];
                                                }
                                                else if ($nhours != '0')
                                                {
                                                        $timeleft = $nhours . $phrase['_h_shortform'] . ', ' . $nminutes . $phrase['_m_shortform'];
                                                }
                                                else if ($nminutes != '0')
                                                {
                                                        $timeleft = $nminutes . $phrase['_m_shortform'] . ', ' . $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft = $nseconds . $phrase['_s_shortform'];        
                                                }
                                        }
                                        $result['timeleft'] = $phrase['_starts'] . ': ' . $timeleft;	
                                }
                        }
                        else
                        {
                                // auction already started
                                $dif = $result['mytime'];
                                $ndays = floor($dif / 86400);
                                $dif -= $ndays * 86400;
                                $nhours = floor($dif / 3600);
                                $dif -= $nhours * 3600;
                                $nminutes = floor($dif / 60);
                                $dif -= $nminutes * 60;
                                $nseconds = $dif;
                                $sign = '+';
                                if ($result['mytime'] < 0)
                                {
                                        $result['mytime'] = - $result['mytime'];
                                        $sign = '-';
                                }
                                if ($sign == '-')
                                {
                                        // expired
                                        $timeleft = $phrase['_ended'];
                                        $expiredauction = 1;
                                }
                                else
                                {
                                        if ($ndays != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $ndays    . $phrase['_d_shortform'] . ', ';	
                                                        $timeleft .= $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft = $ndays . $phrase['_d_shortform'] . ', ' . $nhours . $phrase['_h_shortform'];
                                                }
                                        }
                                        else if ($nhours != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];        
                                                }
                                                else
                                                {
                                                        $timeleft = $nhours . $phrase['_h_shortform'] . ', ' . $nminutes . $phrase['_m_shortform'];
                                                }
                                        }
                                        else
                                        {
                                                if ($nminutes != '0')
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nminutes . $phrase['_m_shortform'] . ', ' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                                else
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                        }
                                }
                                if ($ilconfig['globalauctionsettings_showflashcountdown'] AND $forcenoflash != 1 AND !empty($_SESSION['ilancedata']['user']['slng'])
                                        // opera 9.10 currently not supporting flash via FlashObject.. :-(
                                        AND !$ilance->common->is_webbrowser('opera')
                                        // and this user has chosen view time left as flash from his/her search options
                                        AND $searchoptions['showtimeas'] == 'flash')
                                {
                                        if (defined('LOCATION') AND LOCATION == 'admin')
                                        {
                                                $result['timeleft'] = $timeleft;
                                        }
                                        else
                                        {
                                                $uniqueid = rand(1, 9999);
                                                $result['timeleft'] = '
<div id="applet' . intval($projectid) . '-' . $uniqueid . '"></div>
<script type="text/javascript">
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/countdown.swf", "applet' . intval($projectid) . '-' . $uniqueid . '", "110", "19", "8,0,0,0", "'.$use['bgcolor'] . '");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("flashvars", "languageConfig=' . DIR_FUNCT_NAME . '/' . DIR_XML_NAME . '/livebid_' . $_SESSION['ilancedata']['user']['slng'] . '.xml&prId=' . intval($projectid) . '&valign=' . $use['valign'] . '&timeintext=' . $timeintext . '&showlivebids=' . $showlivebids . '&sId=' . session_id() . '&rand=' . rand(100000, 999999) . '");
fo.addParam("menu", "false");
fo.write("applet' . intval($projectid) . '-' . $uniqueid . '");
</script>';
                                        }
                                }
                                else
                                {
                                        $result['timeleft'] = $timeleft;
                                }
                        }
                        return $result['timeleft'];
                }
        }
        /**
        * Function to create the private message board icons within various sections of the client control panel.
        *
        * @param       integer       from user id
        * @param       integer       to user id
        * @param       integer       project id
        * @param       boolean       force admin mode (true or false)
        * @param       boolean       show icon vs text? (default false)
        *
        * @return      string        HTML representation of private message board icon
        */
        function construct_pmb_icon($fromid = 0, $toid = 0, $projectid = 0, $adminmode = 0, $showicon = false)
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $ilconfig;
                require_once(DIR_CORE . 'functions_pmb.php');
                $ilance->subscription = construct_object('api.subscription');
                $pmb = '-';
                $rand = rand(1, 99999);
                if ($ilance->subscription->check_access($fromid, 'pmb') == 'no')
                {
                        return '<span title="' . $phrase['_upgrade_or_renew_your_subscription_to_view_or_post_private_messages'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="' . $phrase['_upgrade_or_renew_your_subscription_to_view_or_post_private_messages'] . '" /></span>';
                }
                if ($ilance->subscription->check_access($toid, 'pmb') == 'no')
                {
                        return '<span title="' . $phrase['_the_recipient_cannot_view_or_post_private_messages_at_this_time'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="' . $phrase['_the_recipient_cannot_view_or_post_private_messages_at_this_time'] . '" /></span>';
                }
                $sql = $ilance->db->query("
                        SELECT user_id, status
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $sql2 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "pmb_alerts
                                WHERE to_id = '" . $res['user_id'] . "'
                                    AND project_id = '" . intval($projectid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                $crypted = array(
                                        'project_id' => intval($projectid),
                                        'event_id' => $res2['event_id'],
                                        'from_id' => $fromid,
                                        'to_id' => $toid,
                                        'isadmin' => $adminmode
                                );
                                $posts = fetch_pmb_posts(intval($projectid), $res2['event_id']);
                                $postphrase = '_post';
                                if ($posts == 1)
                                {
                                        $postphrase = '_post';
                                }
                                else if ($posts <> 1)
                                {
                                        $postphrase = '_posts';
                                }
                                $unread = fetch_unread_pmb_posts(intval($projectid), $res2['event_id'], $_SESSION['ilancedata']['user']['userid']);
                                //$pmb = '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'email_post.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" /></a></span>';
                                if ($unread > 0)
                                {
                                        $pmb = '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
                                }
                                else
                                {
                                        $pmb = ($posts > 0)
                                                ? '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>'
                                                : '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
                                }
                        }
                        else
                        {
                                // check to see if the person posting a new message is the auction owner
                                if ($fromid == $res['user_id'])
                                {
                                        $crypted = array(
                                                'project_id' => intval($projectid),
                                                'from_id' => $fromid,
                                                'to_id' => $toid,
                                                'isadmin' => $adminmode
                                        );
                                        $eventid = fetch_pmb_eventid(intval($projectid), $fromid, $toid);
                                        $posts = fetch_pmb_posts(intval($projectid), $eventid);
                                        $postphrase = '_post';
                                        if ($posts == 1)
                                        {
                                                $postphrase = '_post';
                                        }
                                        else if ($posts <> 1)
                                        {
                                                $postphrase = '_posts';
                                        }
                                        $unread = fetch_unread_pmb_posts(intval($projectid), $eventid, $_SESSION['ilancedata']['user']['userid']);
                                        //$pmb = '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'email_post.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" /></a></span>';
                                        if ($unread > 0)
                                        {
                                                $pmb = '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
                                        }
                                        else
                                        {
                                                $pmb = ($posts > 0)
                                                        ? '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>'
                                                        : '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
                                        }
                                }
                                else
                                {
                                        switch ($res['status'])
                                        {
                                                case 'wait_approval':
                                                case 'approval_accepted':
                                                case 'expired':
                                                case 'closed':
                                                case 'delisted':
                                                case 'finished':
                                                case 'archived':
                                                case 'open':
                                                {
                                                        $crypted = array(
                                                                'project_id' => intval($projectid),
                                                                'from_id' => $fromid,
                                                                'to_id' => $toid,
                                                                'isadmin' => $adminmode
                                                        );
                                                        $posts = $unread = 0;
                                                        $postphrase = '_posts';
                                                        if ($unread > 0)
                                                        {
                                                                $pmb = '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
                                                        }
                                                        else
                                                        {
                                                                $pmb = ($posts > 0)
                                                                        ? '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>'
                                                                        : '<span title="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '"><a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif" border="0" alt="' . $posts . ' ' . $phrase["$postphrase"] . ', ' . $unread . ' ' . $phrase['_unread'] . '" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
                                                        }
                                                        break;
                                                }
                                                default:
                                                {
                                                        $pmb = '-';
                                                        break;
                                                }
                                        }
                                }
                        }
                }
                return $pmb;
        }
        /**
        * Function to create the invoice icon to be clicked so providers can generate new transaction to their buyers.
        *
        * @param       integer       seller id
        * @param       integer       buyer id
        * @param       integer       project id
        *
        * @return      string        HTML representation of the clickable invoice icon
        */
        function construct_invoice_icon($sellerid = 0, $buyerid = 0, $projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $ilpage, $iltemplate, $ilconfig, $phrase;
                $html = '-';
                $sql = $ilance->db->query("
                        SELECT status
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if (($res['status'] == 'open' OR $res['status'] == 'expired' OR $res['status'] == 'wait_approval' OR $res['status'] == 'approval_accepted'))
                        {
                                // did provider already send invoice to buyer?
                                $any_invoice_sent = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "invoices
                                        WHERE user_id = '" . intval($buyerid) . "'
                                            AND p2b_user_id = '" . intval($sellerid) . "'
                                            AND projectid = '" . intval($projectid) . "'
                                            AND invoicetype = 'p2b'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($any_invoice_sent) == 0)
                                {
                                        $crypted = array(
                                                'cmd' => '_generate-invoice',
                                                'buyer_id' => intval($buyerid),
                                                'seller_id' => intval($sellerid),
                                                'project_id' => intval($projectid)
                                        );
                                        $html = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="' . $phrase['_generate_new_invoice_to'] . ' ' . fetch_user('username', intval($buyerid)) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice.gif" border="0" alt="' . $phrase['_generate_new_invoice_to'] . ' ' . fetch_user('username', intval($buyerid)) . '" /></a>';
                                }
                                else if ($ilance->db->num_rows($any_invoice_sent) > 0)
                                {
                                        $invpaid = $ilance->db->fetch_array($any_invoice_sent);
                                        if ($invpaid['status'] == 'paid')
                                        {
                                                $crypted = array(
                                                        'cmd' => '_generate-invoice',
                                                        'buyer_id' => intval($buyerid),
                                                        'seller_id' => intval($sellerid),
                                                        'project_id' => intval($projectid)
                                                );
                                                $html = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="' . $phrase['_invoice'] . ' #' . $invpaid['invoiceid'] . ' ' . $phrase['_paid_by'] . ' ' . fetch_user('username', $buyerid) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_checkmark.gif" border="0" alt="' . $phrase['_invoice'] . ' #' . $invpaid['invoiceid'] . ' ' . $phrase['_paid_by'] . ' ' . fetch_user('username', $buyerid) . '" /></a>';
                                        }
                                        else
                                        {
                                                $crypted = array(
                                                        'cmd' => '_generate-invoice',
                                                        'buyer_id' => intval($buyerid),
                                                        'seller_id' => intval($sellerid),
                                                        'project_id' => intval($projectid)
                                                );
                                                $html = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="' . $phrase['_waiting_on_payment_for_invoice'] . ' #' . $invpaid['invoiceid'] . ' ' . $phrase['_from'] . ' ' . fetch_user('username', $buyerid) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_gray.gif" border="0" alt="' . $phrase['_waiting_on_payment_for_invoice'] . ' #' . $invpaid['invoiceid'] . ' ' . $phrase['_from'] . ' ' . fetch_user('username', $buyerid) . '" /></a>';
                                        }
                                }
                        }
                        else
                        {
                                $html = '-';
                        }
                }
                return $html;
        }
        /**
        * Function to fetch a clickable link to the auction winner for a particular auction id.
        *
        * @param       integer       project id
        *
        * @return      integer       clickable link with the username
        */
        function fetch_auction_winner($projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $ilpage;
                $winner = '-';
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND bidstatus = 'awarded'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $userid = $res['user_id'];
                        $winner = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $userid . '">' . fetch_user('username', intval($userid)) . '</a>';
                }
                return $winner;
        }
        /**
        * Function to fetch the current reserve price of a particular auction id.
        *
        * @param       integer       auction id
        *
        * @return      integer       reserve price amount
        */
        function fetch_reserve_price($auctionid = 0)
        {
                global $ilance, $myapi;
                $sql = $ilance->db->query("
                        SELECT reserve, reserve_price
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($auctionid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['reserve'] AND $res['reserve_price'] > 0)
                        {
                                return $res['reserve_price'];
                        }
                }
                return '0';
        }
        /**
        * Function to fetch the current reserve price bit of a particular auction id.
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the reserve price details
        */
        function fetch_reserve_price_bit($projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                $sql = $ilance->db->query("
                        SELECT reserve, reserve_price
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                            AND reserve > 0
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $html = '<div>' . $phrase['_reserve_price'] . ' <input type="text" name="reserve_price" size="10" value="' . $res['reserve_price'] . '"></div>';
                }
                else
                {
                        $html = '<div>' . $phrase['_this_auction_is_not_using_the_reserve_price_feature'] . '</div>';
                }
                return $html;
        }
        /**
        * Function to fetch the current purchase now price of a particular auction id.
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the purchase now details
        */
        function fetch_purchase_now($projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                $sql = $ilance->db->query("
                        SELECT buynow, buynow_price, buynow_qty
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                            AND buynow > 0
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $html = '<div>' . $phrase['_purchase_now_price'] . ' <input type="text" name="buynow_price" size="10" value="' . $res['buynow_price'] . '"></div>';
                        $html .= '<div>' . $phrase['_qty'] . ': <input type="text" name="buynow_qty" size="2" value="' . $res['buynow_qty'] . '"></div>';
                }
                else
                {
                        $html = '<div>' . $phrase['_this_auction_is_not_selling_any_items_using_the_purchase_now_feature'] . '</div>';
                }
                return $html;
        }
        /**
        * Function to fetch the current transfer of ownership details
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the auction transfer of ownership details
        */
        function fetch_transfer_ownership($projectid = 0)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                $sql = $ilance->db->query("
                        SELECT transfertype, transfer_to_userid, transfer_from_userid, transfer_to_email, transfer_status, transfer_code
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                            AND transfer_to_userid > 0
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $html .= $phrase['_this_auction_has_been_transfered_from_the_original_buyer_to_another_member'] . '</div>';
                        $html .= '<div>' . $phrase['_transferred_from'] . ' <a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['transfer_from_userid'] . '">' . fetch_user('username', $res['transfer_from_userid']) . '</a></div>';
                        $html .= '<div>' . $phrase['_transferred_to'] . ' <a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['transfer_to_userid'] . '">' . fetch_user('username', $res['transfer_to_userid']) . '</a></div>';
                        $html .= '<div>' . $phrase['_transfer_status'] . ' ' . ucfirst($res['transfer_status']) . '</div>';
                }
                else
                {
                        $html = '<div>' . $phrase['_this_auction_has_not_been_transfered_to_any_other_member'] . '</div>';
                }
                return $html;
        }
        /**
        * Function to create the mediashare (workspace) icon.
        *
        * @param       integer       buyer id
        * @param       integer       seller id
        * @param       integer       project id
        * @param       boolean       force if icon is active or disabled (active by default)
        *
        * @return      string        HTML representation of the clickable mediashare icon
        */
        function construct_mediashare_icon($buyerid = 0, $sellerid = 0, $projectid = 0, $active = true)
        {
                global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $iltemplate, $ilconfig;
                $html = '-';
                $viewinguserid = $_SESSION['ilancedata']['user']['userid'];
                if ($active)
                {
                        $crypted = array(
                                'project_id' => intval($projectid),
                                'buyer_id' => $buyerid,
                                'seller_id' => $sellerid
                        );
                        $shared = $private = 0;
                        $sql = $ilance->db->query("
                                SELECT tblfolder_ref, user_id
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = 'ws'
                                        AND project_id = '" . intval($projectid) . "'
                                        AND visible = '1'
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $sql2 = $ilance->db->query("
                                                SELECT folder_type
                                                FROM " . DB_PREFIX . "attachment_folder
                                                WHERE project_id = '" . intval($projectid) . "'
                                                        AND id = '" . $res['tblfolder_ref'] . "'
                                        ");
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                if ($res2['folder_type'] == '1' AND $res['user_id'] == $viewinguserid)
                                                {
                                                        $private++;        
                                                }
                                                else if ($res2['folder_type'] == '2')
                                                {
                                                        $shared++;        
                                                }
                                        }
                                }
                        }
                        if ($shared > 0 OR $private > 0)
                        {
                                $html = '<span title="' . $shared . ' ' . $phrase['_shared_lower'] . ', ' . $private . ' ' . $phrase['_private_lower'] . '"><a href="' . HTTPS_WS . $ilpage['workspace'] . '?crypted=' . encrypt_url($crypted) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share.gif" border="0" alt="' . $shared . ' ' . $phrase['_shared_lower'] . ', ' . $private . ' ' . $phrase['_private_lower'] . '" id="" /></a></span>';
                        }
                        else
                        {
                                $html = '<span title="' . $shared . ' ' . $phrase['_shared_lower'] . ', ' . $private . ' ' . $phrase['_private_lower'] . '"><a href="' . HTTPS_WS . $ilpage['workspace'] . '?crypted=' . encrypt_url($crypted) . '" onmouseover="rollovericon(\'' . md5($buyerid . ':' . $sellerid . ':' . $projectid) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share.gif\')" onmouseout="rollovericon(\'' . md5($buyerid . ':' . $sellerid . ':' . $projectid) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="' . $shared . ' ' . $phrase['_shared_lower'] . ', ' . $private . ' ' . $phrase['_private_lower'] . '" id="" name="' . md5($buyerid . ':' . $sellerid . ':' . $projectid) . '" /></a></span>';
                        }
                }
                return $html;
        }
        /**
        * Function to print the budget pulldown details
        *
        * @param       integer       category id
        * @param       integer       selected id (optional)
        * @param       string        field name to use
        * @param       bool          are we enabling javascript (true or false)
        * @param       bool          will we show the "please select" option as well?
        * @param       bool          will we show insertion fees within the pulldown menu that is generated?
        *
        * @return      string        HTML representation of the budget pulldown values
        */
        function construct_budget_pulldown($cid, $selected = '', $fieldname = 'filtered_budgetid', $dojs = 1, $showselect = 0, $showinsertionfees = 0)
        {
                global $ilance, $myapi, $phrase, $show;
                $html = '';
                if ($dojs)
                {
                        $html .= '<select style="font-family: verdana" name="' . $fieldname . '" onchange="javascript: document.ilform.showbudget.checked=true;">';
                }
                else 
                {
                        $html .= '<select style="font-family: verdana" name="' . $fieldname . '">';    		
                }
                if ($showselect)
                {
                        $html .= '<option value="">' . $phrase['_any_budget_range'] . '</option>';		
                }
                $query = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
                        $rquery = $ilance->db->fetch_array($query);
                        $budgetgroup = $rquery['budgetgroup'];
                }
                else 
                {
                        $budgetgroup = 'default';	
                }
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "budget
                        WHERE budgetgroup = '" . $ilance->db->escape_string($budgetgroup) . "'
                        ORDER BY budgetfrom ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['budgetgroups'] = true;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if (isset($selected) AND $selected == $res['budgetid'])
                                {
                                        $show['selectedbudgetlogic'] = $this->calculate_insertion_fee_in_budget_group($res['insertiongroup']);
                                        if ($res['budgetto'] == '-1')
                                        {
                                                $html .= '<option style="font-family: value="' . $res['budgetid'] . '" selected="selected">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . ')' . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - *' . $phrase['_insertion_fee'] . ': ' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';    
                                        }
                                        else
                                        {
                                                $html .= '<option value="' . $res['budgetid'] . '" selected="selected">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')'  . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - *' . $phrase['_insertion_fee'] . ': ' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
                                        }
                                }
                                else 
                                {
                                        if ($res['budgetto'] == '-1')
                                        {
                                                $html .= '<option value="' . $res['budgetid'] . '">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . ')' . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - ' . $phrase['_insertion_fee'] . ': *' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
                                        }
                                        else
                                        {
                                                $html .= '<option value="' . $res['budgetid'] . '">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')' . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - *' . $phrase['_insertion_fee'] . ': ' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
                                        }
                                }
                        }	
                }
                else
                {
                        $show['budgetgroups'] = false;
                        $html .= '<option value="0">--</option>';
                }
                $html .= '</select>';
                return $html;
        }
        /**
        * Function to print the budget overview details
        *
        * @param       integer       category id
        * @param       integer       selected id (optional)
        * @param       boolean       don't show range title (default false)
        * @param       boolean       don't show brackets (default false)
        * @param       boolean       force function to use raw budget id vs. the budget in place for the actual category.  This is required for the search system if subcategories are also being called
        *
        * @return      string        HTML representation of the budget values
        */
        function construct_budget_overview($cid = 0, $selected = 0, $notext = false, $nobrackets = false, $forcenocategory = false)
        {
                global $ilance, $myapi, $phrase, $sqlquery;
                $html = '';
                if ($selected == 0 OR $cid == 0)
                {
                        $html = $phrase['_non_disclosed'];
                        return $html;
                }
                if ($forcenocategory AND $selected > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT budgetid, budgetto, budgetfrom, title
                                FROM " . DB_PREFIX . "budget
                                WHERE budgetid = '" . intval($selected) . "'
                                ORDER BY budgetfrom ASC
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        if ($res['budgetto'] == '-1')
                                        {
                                                if ($nobrackets AND $notext)
                                                {
                                                        $html = $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'];
                                                }
                                                else
                                                {
                                                        $html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . ')';
                                                }
                                        }
                                        else
                                        {
                                                if ($nobrackets AND $notext)
                                                {
                                                        $html = $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_to'] . ' ' . $ilance->currency->format($res['budgetto']);
                                                }
                                                else
                                                {
                                                        $html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')';
                                                }
                                        }
                                }
                        }        
                }
                else
                {
                        $query = $ilance->db->query("
                                SELECT budgetgroup
                                FROM " . DB_PREFIX . "categories
                                WHERE cid = '" . intval($cid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($query) > 0)
                        {
                                $rquery = $ilance->db->fetch_array($query, DB_ASSOC);
                                $sql = $ilance->db->query("
                                        SELECT budgetid, budgetto, budgetfrom, title
                                        FROM " . DB_PREFIX . "budget
                                        WHERE budgetgroup = '" . $ilance->db->escape_string($rquery['budgetgroup']) . "'
                                        ORDER BY budgetfrom ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        while ($res = $ilance->db->fetch_array($sql))
                                        {
                                                if (isset($selected) AND $selected == $res['budgetid'])
                                                {
                                                        if ($res['budgetto'] == '-1')
                                                        {
                                                                if ($nobrackets AND $notext)
                                                                {
                                                                        $html = $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'];
                                                                }
                                                                else
                                                                {
                                                                        $html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . ')';
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if ($nobrackets AND $notext)
                                                                {
                                                                        $html = $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_to'] . ' ' . $ilance->currency->format($res['budgetto']);
                                                                }
                                                                else
                                                                {
                                                                        $html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')';
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }        
                }
                if (empty($html))
                {
                        $html = $phrase['_non_disclosed'];
                }
                return $html;
        }
        /**
        * Function to print the bid amount types pulldown menu.
        *
        * @param       integer       selected bid amount type value
        * @param       bool          disable the bid amount type pulldown menu (true or false)
        * @param       bool          enable javascript (true or false)
        * @param       integer       selected category id (optional)
        * @param       string        category type (service / product)
        *
        * @return      string        HTML representation of the bid amount types pulldown values
        */
        function construct_bidamounttype_pulldown($selected = '', $disable = 0, $dojs = 1, $cid = '', $cattype = '')
        {
                global $ilance, $myapi, $ilconfig, $phrase, $show;
                $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = $sel7 = $sel8 = $sel9 = $dis1 = $dis2 = '';
                if (isset($selected) AND !empty($selected))
                {
                        if ($selected == 'entire')
                        {
                                $sel1 = 'selected="selected"';		    
                        }
                        if ($selected == 'hourly')
                        {
                                $sel2 = 'selected="selected"';
                        }
                        if ($selected == 'daily')
                        {
                                $sel3 = 'selected="selected"';
                        }
                        if ($selected == 'weekly')
                        {
                                $sel4 = 'selected="selected"';
                        }
                        if ($selected == 'monthly')
                        {
                                $sel6 = 'selected="selected"';
                        }
                        if ($selected == 'lot')
                        {
                                $sel7 = 'selected="selected"';
                        }
                        if ($selected == 'weight')
                        {
                                $sel8 = 'selected="selected"';
                        }
                        if ($selected == 'item')
                        {
                                $sel9 = 'selected="selected"';
                        }
                }
                else
                {
                        // no type was supplied to function - default "For entire project"
                        $selected = 'entire';
                }
                if (isset($disable) AND $disable AND !empty($selected))
                {
                        $dis1 = 'disabled="disabled"';
                        $dis2 = '<input type="hidden" name="filtered_bidtype" value="' . $selected . '" />';
                }
                $html = '<select name="filtered_bidtype" style="font-family: verdana" id="bidamounttype" ';
                if (isset($dojs) AND $dojs == '1')
                {
                        $html .= 'onchange="javascript: document.ilform.filter_bidtype[0].checked=true;" ' . $dis1 . '>';
                }
                else if (isset($dojs) AND $dojs == '2')
                {
                        $html .= 'onchange="javascript:
                        if (document.ilform.filtered_bidtype.value == \'entire\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_days'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'hourly\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_hours'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'daily\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_days'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'weekly\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_weeks'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'monthly\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_months'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'lot\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_lot'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'weight\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_weight'] . '\'
                        }
                        else if (document.ilform.filtered_bidtype.value == \'item\')
                        {
                            fetch_js_object(\'measure\').innerHTML = \'' . $phrase['_items'] . '\'
                        }
                        " ' . $dis1 . '>';
                }
                else if (isset($dojs) AND $dojs == 0)
                {
                        $html .= '>';
                }
                if ($cattype == 'service')
                {
                        $html .= '<optgroup label="' . $phrase['_reverse_service_auction'] . '">';
                        if (isset($cid) AND $cid > 0)
                        {
                                $data = $ilance->categories->bidamounttypes($cid);
                                if (!empty($data))
                                {
                                        $show['bidamounttypes'] = true;
                                        $data = unserialize($data);
                                        if (is_array($data))
                                        {
                                                foreach ($data as $key => $value)
                                                {
                                                        if (!empty($value) AND $value == 'entire')
                                                        {
                                                                $html .= '<option value="entire" ' . $sel1 . '>' . $phrase['_for_entire_project'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'hourly')
                                                        {
                                                                $html .= '<option value="hourly" ' . $sel2 . '>' . $phrase['_per_hour'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'daily')
                                                        {
                                                                $html .= '<option value="daily" ' . $sel3 . '>' . $phrase['_per_day'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'weekly')
                                                        {
                                                                $html .= '<option value="weekly" ' . $sel4 . '>' . $phrase['_weekly'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'monthly')
                                                        {
                                                                $html .= '<option value="monthly" ' . $sel6 . '>' . $phrase['_monthly'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'lot')
                                                        {
                                                                $checked7 = 'checked="checked"';
                                                                $html .= '<option value="lot" ' . $sel7 . '>' . $phrase['_per_lot'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'weight')
                                                        {
                                                                $checked8 = 'checked="checked"';
                                                                $html .= '<option value="weight" ' . $sel8 . '>' . $phrase['_per_weight'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'item')
                                                        {
                                                                $checked9 = 'checked="checked"';
                                                                $html .= '<option value="item" ' . $sel9 . '>' . $phrase['_per_item'] . '</option>';
                                                        }
                                                }
                                        }
                                }
                                else
                                {
                                        // no bidamount types for this category
                                        // let's set the default type to "For entire project"
                                        $html .= '<option value="entire" ' . $sel1 . '>' . $phrase['_for_entire_project'] . '</option>';
                                        $show['bidamounttypes'] = false;
                                }
                        }
                        $html .= '</optgroup>';    
                }
                else if ($cattype == 'product')
                {
                        // product reverse auction elements
                        if (isset($cid) AND $cid > 0)
                        {
                                $data = $ilance->categories->bidamounttypes($cid);
                                if (!empty($data))
                                {
                                        $show['bidamounttypes'] = true;
                                        $htmlx = '';
                                        $data = unserialize($data);
                                        if (is_array($data))
                                        {
                                                $doproduct = 0;
                                                foreach ($data as $key => $value)
                                                {
                                                        if (!empty($value) AND $value == 'lot')
                                                        {
                                                                $doproduct = 1;
                                                                $htmlx .= '<option value="lot" ' . $sel7 . '>' . $phrase['_per_lot'] . '</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'weight')
                                                        {
                                                                $doproduct = 1;
                                                                $htmlx .= '<option value="weight" '.$sel8.'>'.$phrase['_per_weight'].'</option>';
                                                        }
                                                        if (!empty($value) AND $value == 'item')
                                                        {
                                                                $doproduct = 1;
                                                                $htmlx .= '<option value="item" '.$sel9.'>'.$phrase['_per_item'].'</option>';
                                                        }
                                                }
                                        }
                                        if ($doproduct > 0)
                                        {
                                                $html .= '<optgroup label="'.$phrase['_reverse_product_auction'].'">';
                                                $html .= $htmlx;
                                                $html .= '</optgroup>';
                                        }
                                }
                                else
                                {
                                        $show['bidamounttypes'] = false;
                                }
                        }    
                }
                $html .= '</select>';
                $html .= $dis2;
                return $html;
        }
        /**
        * Function to print the bid amount types overview.
        *
        * @param       string        selected bid amount type (optional)
        *
        * @return      string        HTML representation of the bid amount types
        */
        function construct_bidamounttype($selected = '')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                if (isset($selected) AND !empty($selected))
                {
                        if ($selected == 'entire')
                        {
                                $html = $phrase['_for_entire_project'];		    
                        }
                        if ($selected == 'hourly')
                        {
                                $html = $phrase['_per_hour'];
                        }
                        if ($selected == 'daily')
                        {
                                $html = $phrase['_per_day'];
                        }
                        if ($selected == 'weekly')
                        {
                                $html = $phrase['_weekly'];
                        }
                        if ($selected == 'monthly')
                        {
                                $html = $phrase['_monthly'];
                        }
                        if ($selected == 'lot')
                        {
                                $html = $phrase['_per_lot'];
                        }
                        if ($selected == 'weight')
                        {
                                $html = $phrase['_per_weight'];
                        }
                        if ($selected == 'item')
                        {
                                $html = $phrase['_per_item'];
                        }
                }
                else
                {
                        $html = $phrase['_for_entire_project'];
                }
                return $html;
        }
        /**
        * Function to print the phrased measure values of the selected bid amount types.
        *
        * @param       string        selected bid amount type (optional)
        *
        * @return      string        HTML representation of the bid amount types
        */
        function construct_measure($selected = '')
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                $html = '';
                if (isset($selected) AND !empty($selected))
                {
                        if ($selected == 'entire')
                        {
                                $html = $phrase['_days'];		    
                        }
                        if ($selected == 'hourly')
                        {
                                $html = $phrase['_hours'];
                        }
                        if ($selected == 'daily')
                        {
                                $html = $phrase['_days'];
                        }
                        if ($selected == 'weekly')
                        {
                                $html = $phrase['_weeks'];
                        }
                        if ($selected == 'monthly')
                        {
                                $html = $phrase['_months'];
                        }
                        if ($selected == 'lot')
                        {
                                $html = $phrase['_lot'];
                        }
                        if ($selected == 'weight')
                        {
                                $html = $phrase['_weight'];
                        }
                        if ($selected == 'item')
                        {
                                $html = $phrase['_item'];
                        }
                }
                else
                {
                        $html = $phrase['_days'];
                }
                return $html;
        }
        /**
        * Function to fetch the request for proposal budget amount for a particular project
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the duration pulldown menu
        */
        function fetch_rfp_budget($projectid = 0, $showicon = false)
        {
                global $ilance, $myapi, $phrase, $ilconfig;
                $html = '';
                $sql = $ilance->db->query("
                        SELECT budgetgroup, filter_budget, filtered_budgetid
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['filter_budget'] > 0 AND $res['filtered_budgetid'] > 0)
                        {
                                // buyer is filtering budget via specific range
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "budget
                                        WHERE budgetid = '" . $res['filtered_budgetid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                        if ($res2['budgetto'] == '-1')
                                        {
                                                $html = stripslashes($res2['title']) . ' (' . $ilance->currency->format($res2['budgetfrom']) . ' ' . $phrase['_or_more'] . ')';
                                        }
                                        else
                                        {
                                                $html = stripslashes($res2['title']) . ' (' . $ilance->currency->format($res2['budgetfrom']) . ' - ' . $ilance->currency->format($res2['budgetto']) . ')';
                                        }
                                }
                        }
                        else
                        {
                                $html = $phrase['_non_disclosed'];
                                if ($showicon)
                                {
                                        $html .= ' <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nondisclosed.gif" border="0" alt="' . $phrase['_non_disclosed_budget'] . '" />';
                                }
                        }
                }
                return $html;
        }
        /**
        * Function to fetch the insertion fee amount associated within a particular budget group
        *
        * @param       string        insertion group name
        *
        * @return      string        Insertion fee amount
        */
        function calculate_insertion_fee_in_budget_group($groupname = '')
        {
                global $ilance, $myapi;
                $fee = 0.00;
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "insertion_fees
                        WHERE groupname = '" . $ilance->db->escape_string($groupname) . "'
                            AND state = 'service'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($rows = $ilance->db->fetch_array($sql))
                        {
                                $fee += $rows['amount'];
                        }
                }
                return $fee;
        }
        /**
        * Function to return template array data for a the buyer or seller currently being viewed.  This function is
        * usually called from the actual listing or profile page of the marketplace letting users see other related
        * listings from the same seller (product listings) or buyer (service auction listings)
        *
        * @param       integer       user id
        * @param       string        auction type (service or product)
        * @param       integer       auction result limit (default 5)
        * @param       array         array of project id's not to include in sellers listings
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
        function fetch_users_other_listings($userid = 0, $auctiontype = '', $limit = 5, $excludelist = array(), $forcenoflash = false)
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $otherlistings = array();
                $show['otherproductlistings'] = $show['otherservicelistings'] = false;
                $query_fields = $exclude = $excluded = '';
                // build exclusion query bit to prevent the same listings as the one being viewed to show up
                if (isset($excludelist) AND !empty($excludelist) AND is_array($excludelist) AND count($excludelist) > 0)
                {
                        foreach ($excludelist AS $projectid)
                        {
                                if (!empty($projectid) AND $projectid > 0)
                                {
                                        $excluded .= " AND project_id != '" . intval($projectid) . "'";
                                }
                        }
                }
                ($apihook = $ilance->api('fetch_users_other_listings_start')) ? eval($apihook) : false;
                if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        $rowcount = 0;
                        $query = $ilance->db->query("
                                SELECT user_id, project_id, cid, project_title, project_details, views, $query_fields bids, currentprice
                                FROM " . DB_PREFIX . "projects
                                WHERE visible = '1'
                                    $excluded
                                    AND user_id = '" . intval($userid) . "'
                                    AND status = 'open'
                                    AND project_state = 'product'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))" : "") . "
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($query) > 0)
                        {
                                $show['otherproductlistings'] = true;
                                while ($respcook = $ilance->db->fetch_array($query))
                                {
                                        $respcook['class'] = ($rowcount % 2) ? 'alt2' : 'alt1';
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $respcook['project_id'], stripslashes($respcook['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $respcook['photo'] = print_item_photo($url, 'thumb', $respcook['project_id']);
                                        }
                                        else
                                        {
                                                $respcook['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $respcook['project_id'], 'thumb', $respcook['project_id']);
                                        }
                                        $respcook['project_title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $respcook['project_id'], stripslashes($respcook['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?id=' . $respcook['project_id'] . '">' . stripslashes($respcook['project_title']) . '</a>';
                                        $respcook['timeleft'] = $this->auction_timeleft($respcook['project_id'], $respcook['class'], 'center', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        $respcook['icons'] = $this->auction_icons($respcook['project_id'], $respcook['user_id']);
                                        if ($respcook['project_details'] == 'unique')
                                        {
                                                $respcook['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($respcook['project_id']);
                                        }
                                        $otherlistings[] = $respcook;                                                
                                        $rowcount++;
                                }
                        }
                        else
                        {
                                $show['otherproductlistings'] = false;
                        }
                }
                else if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                        $rowcount2 = $show['otherservicelistingcount'] = 0;
                        $query2 = $ilance->db->query("
                                SELECT project_id, cid, project_title, views, bids, $query_fields user_id, filtered_budgetid
                                FROM " . DB_PREFIX . "projects
                                WHERE visible = '1'
                                    $excluded    
                                    AND user_id = '" . intval($userid) . "'
                                    AND status = 'open'
                                    AND project_state = 'service'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))" : "") . "
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);    
                        if ($ilance->db->num_rows($query2) > 0)
                        {
                                $show['otherservicelistings'] = true;
                                while ($resscook = $ilance->db->fetch_array($query2))
                                {
                                        $resscook['class'] = ($rowcount2 % 2) ? 'alt2' : 'alt1';
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $resscook['project_title'] = construct_seo_url('serviceauction', 0, $resscook['project_id'], stripslashes($resscook['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                        }
                                        else
                                        {
                                                $resscook['project_title'] = '<a href="'.$ilpage['rfp'].'?id='.$resscook['project_id'].'">'.stripslashes($resscook['project_title']).'</a>';
                                        }
                                        $resscook['timeleft'] = $this->auction_timeleft($resscook['project_id'], $resscook['class'], 'center', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        $resscook['icons'] = $this->auction_icons($resscook['project_id'], $resscook['user_id']);
                                        $resscook['budget'] = $this->construct_budget_overview($resscook['cid'], $resscook['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true);
                                        $otherlistings[] = $resscook;
                                        $rowcount2++;
                                }
                                $show['otherservicelistingcount'] = $rowcount2;
                        }
                        else
                        {
                                $show['otherservicelistings'] = false;
                        }
                }
                $ilance->timer->stop();
                DEBUG("fetch_users_other_listings(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $otherlistings;
        }
        /**
        * Function to return template array data for recently viewed auctions stored in the users cookie.
        * This function additionally provides {photo} variable to let the designer show the related photo for the item being shown
        * within the HTML template.
        *
        * Function now takes into consideration if a member is not active (don't display the listing).  Additionally, this function
        * will no longer show "ended" listings so only active open listings will appear in the recently viewed blocks.
        *
        * @param       string        auction type
        * @param       integer       auction result limit (default 5)
        * @param       integer       auction rows limit (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
        function fetch_recently_viewed_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $recentreviewedauctions = array();
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
                if ($cid > 0)
                {
                        $childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
                        $subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
                        $cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                }
                else
                {
                        $cidcondition = "AND p.cid > 0";        
                }
                if (!empty($keywords))
                {
                        $kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
                }
                ($apihook = $ilance->api('fetch_recently_viewed_auctions_start')) ? eval($apihook) : false;
                if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        if (!empty($_COOKIE[COOKIE_PREFIX . 'productauctions']))
                        {
                                $productsarr = explode('|', $_COOKIE[COOKIE_PREFIX . 'productauctions']);
                                if (count($productsarr) == 1)
                                {
                                        $pcookiesql = " AND p.project_id = '" . intval($productsarr[0]) . "' ";        
                                }
                                else if (count($productsarr) > 1)
                                {
                                        $pcookiesql = "AND (";
                                        for ($i = 0; $i < count($productsarr); $i++)
                                        {
                                                $pcookiesql .= "p.project_id = '" . intval($productsarr[$i]) . "' OR ";
                                        }
                                        $pcookiesql = substr($pcookiesql, 0, -4);
                                        $pcookiesql .= ")";
                                }
                                $rowcount = 0;
                                $query = $ilance->db->query("
                                        SELECT p.user_id, p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.buynow_price, p.date_added, p.highlite, p.retailprice, p.project_details, p.views, $query_fields p.bids, p.currentprice, p.currencyid, p.buynow
                                        FROM " . DB_PREFIX . "projects AS p
                                        LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                        WHERE p.visible = '1'
                                            $pcookiesql
                                            $cidcondition
                                            $kwcondition
                                            AND p.status = 'open'
                                            AND u.status = 'active'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                        LIMIT $columns
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($query) > 0)
                                {
                                        $rowstotal = $ilance->db->num_rows($query);                                
                                        $width = number_format(100 / $columns, 1);
                                        $separator = '';
                                        if ($rowstotal != $columns && $rowstotal != 0)
                                        {
                                                $neededtds = $columns - $rowstotal;
                                                for ($i = 0; $i < $neededtds; $i++)
                                                {
                                                        //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                        $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                                }
                                        }
                                        $show['recentlyviewedproducts'] = true;
                                        $resrows = 0;
                                        while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
                                        {
                                                if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                                {
                                                        $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                        if (strcmp($temp_title, $res['project_title']) != '0')
                                                        {
                                                                $res['project_title'] = $temp_title . '...';
                                                        }
                                                        $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                        if (strcmp($temp_desc, $res['description']) != '0')
                                                        {
                                                                $res['description'] = $temp_desc . '...';
                                                        }
                                                }
						$res['width'] = $width;
                                                if ($ilconfig['globalauctionsettings_seourls'])
                                                {
                                                        $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                        $res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);
                                                }
                                                else
                                                {
                                                        $res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
                                                }
                                               // $res['merchant'] = print_username($res['user_id'], 'href');
                                                $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>';
                                                $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                                $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                                $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                if ($res['project_details'] == 'unique')
                                                {
                                                        $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                        $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                }
                                                else
                                                {
                                                        if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                        {
                                                                $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                                $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                                $res['buynowtxt'] = $phrase['_buy'] . ':';
                                                                $res['price'] = $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                                        }
                                                        else
                                                        {
                                                            //karthik on may 26 for categories
												       $show['hid'] = '';
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                        $res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>&nbsp;<span class="smaller gray">('.$res['bids'].'&nbsp;'.$phrase['_bids_lower'].')</span>';
                                                        }
                                                }
                                                $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                                $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                                $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                                //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                                $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                                $resrows++;
                                                $res['separator'] = '';
                                                if ($resrows < $rowstotal)
                                                {
                                                        $res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
                                                }
                                                $recentreviewedauctions[] = $res;
                                        }
                                }
                                else
                                {
                                        $show['recentlyviewedproducts'] = false;
                                }
                        }    
                }
                if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                        if (!empty($_COOKIE[COOKIE_PREFIX . 'serviceauctions']))
                        {
                                $servicesarr = explode('|', $_COOKIE[COOKIE_PREFIX . 'serviceauctions']);
                                if (count($servicesarr) == 1)
                                {
                                        $scookiesql = " AND p.project_id = '" . intval($servicesarr[0]) . "' ";        
                                }
                                else if (count($servicesarr) > 1)
                                {
                                        $scookiesql = "AND (";
                                        for ($i = 0; $i < count($servicesarr); $i++)
                                        {
                                                $scookiesql .= "p.project_id = '" . intval($servicesarr[$i]) . "' OR ";
                                        }
                                        $scookiesql = substr($scookiesql, 0, -4);
                                        $scookiesql .= ")";
                                }
                                $rowcount2 = 0;
                                $query2 = $ilance->db->query("
                                        SELECT p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.highlite, p.views, p.bids, $query_fields p.user_id, p.bid_details, p.currencyid, p.filtered_budgetid, u.status
                                        FROM " . DB_PREFIX . "projects AS p
                                        LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                        WHERE p.visible = '1'
                                            $scookiesql
                                            $cidcondition
                                            $kwcondition
                                            AND p.status = 'open'
                                            AND u.status = 'active'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                        LIMIT $columns
                                ", 0, null, __FILE__, __LINE__);    
                                if ($ilance->db->num_rows($query2) > 0)
                                {
                                        $rowstotal = $ilance->db->num_rows($query2);
                                        $width = number_format(100 / $columns, 1);
                                        //echo $width;
                                        $separator = '';
                                        if ($rowstotal != $columns && $rowstotal != 0)
                                        {
                                                $neededtds = $columns - $rowstotal;
                                                for ($i = 0; $i < $neededtds; $i++)
                                                {
                                                        //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                        $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                                }
                                        }
                                        $show['recentlyviewedservices'] = true;
                                        $resrows = 0;
                                        while ($res = $ilance->db->fetch_array($query2, DB_ASSOC))
                                        {
                                                if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        	{
	                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                        if (strcmp($temp_title, $res['project_title']) != '0')
                                                        {
                                                                $res['project_title'] = $temp_title . '...';
                                                        }
                                                        $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                        if (strcmp($temp_desc, $res['description']) != '0')
                                                        {
                                                                $res['description'] = $temp_desc . '...';
                                                        }
                                        	}
						$res['width'] = $width;
                                                $res['buyer'] = print_username($res['user_id'], 'plain', 0, '', '');
                                                $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>';                                               
                                                $res['project_title'] = $res['title'];
                                                $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                                $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                                $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']));
                                                $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';
                                                $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                                //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                                $res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], true);
                                                $res['average'] = $ilance->currency->format($res['average'], $res['currencyid']);
                                                $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                                $res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true);
                                                $resrows++;
                                                $res['separator'] = '';
                                                if ($resrows < $rowstotal)
                                                {
                                                        $res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
                                                }
                                                $recentreviewedauctions[] = $res;
                                        }
                                }
                                else
                                {
                                        $show['recentlyviewedservices'] = false;
                                }
                        }
                }
                $ilance->timer->stop();
                DEBUG("fetch_recently_viewed_auctions(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $recentreviewedauctions;
        }
        /**
        * Function to return template array data for recently viewed auctions stored in the users cookie.
        *
        * Function now takes into consideration if a member is not active (don't display the listing).
        *
        * @param       string        auction type
        * @param       integer       auction result columns (default 5)
        * @param       integer       auction result rows (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
        function fetch_ending_soon_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                if ($ilconfig['showendingsoonlistings'] == false)
                {
                        $show['endingsoonservices'] = $show['endingsoonproducts'] = false;
                        return;
                }
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
                if ($cid > 0)
                {
                        $childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
                        $subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
                        $cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                }
                else
                {
                        $cidcondition = "AND p.cid > 0";        
                }
                if (!empty($keywords))
                {
                        $kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
                }
                ($apihook = $ilance->api('fetch_ending_soon_auctions_start')) ? eval($apihook) : false;
                require_once(DIR_CORE . 'functions_search.php');
                /**
                -1 = any date, 1 = 1 hour, 2 = 2 hours, 3 = 3 hours, 4 = 4 hours, 5 = 5 hours, 6 = 12 hours, 7 = 24 hours, 8 = 2 days, 9 = 3 days, 10 = 4 days, 11 = 5 days, 12 = 6 days, 13 = 7 days, 14 = 2 weeks, 15 = 1 month
                */
                $endingsoon = array();
                $timeid = $ilconfig['globalauctionsettings_endsoondays'];
                if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                	$limit = $columns * $rows;
                        $query = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.buynow_price, p.date_added, p.highlite, p.project_details, p.views, $query_fields p.bids, p.currentprice, p.retailprice, p.currencyid, p.buynow
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.visible = '1'
                                    AND p.status = 'open'
                                    AND p.project_state = 'product'
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . fetch_startend_sql($timeid, 'DATE_ADD', 'p.date_end', '<=') . "
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($query) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($query);                                
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['endingsoonproducts'] = true;
                                $resrows = 0;
                                while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
                                        $res['width'] = $width;
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);
                                        }
                                        else
                                        {
                                                $res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
                                        }
                                       // $res['merchant'] = print_username($res['user_id'], 'href');
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['merch'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                        $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                        if ($res['project_details'] == 'unique')
                                        {
                                                $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                        }
                                        else
                                        {
                                                if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                {
                                                        $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                        $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                        $res['buynowtxt'] = $phrase['_buy'] . ':';
                                                        $res['price'] = $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                                }
                                                else
                                                {
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                        $res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>';
                                                }
                                        }
                                        $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1'); 
                                        $resrows++;
                                        if ($resrows == 1)
	                                {
	                                        $res['separator_begin'] = '<tr>';
	                                        $td = 0;
	                                }
	                                else 
	                                {
	                                        $res['separator_begin'] = '';
	                                }
	                                if ($resrows == $rowstotal)
	                                {
	                                        $res['separator_end'] = '</tr>';
	                                }
	                                else 
	                                {
	                                        $res['separator_end'] = '';
	                                }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                }
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $endingsoon[] = $res;
                                }
                        }
                        else
                        {
                                $show['endingsoonproducts'] = false;
                        }
                }
                else
                {
                        $show['endingsoonproducts'] = false;
                }
                if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                	$limit = $columns * $rows;
                        $query = $ilance->db->query("
                                SELECT p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.highlite, p.views, p.bids, $query_fields p.user_id, p.bid_details, p.currencyid, p.filtered_budgetid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.visible = '1'
                                    AND p.status = 'open'
                                    AND p.project_state = 'service'
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . fetch_startend_sql($timeid, 'DATE_ADD', 'p.date_end', '<=') . "
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                LIMIT $columns
                        ", 0, null, __FILE__, __LINE__);    
                        if ($ilance->db->num_rows($query) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($query);
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['endingsoonservices'] = true;
                                $rowcount = $resrows = 0;
                                while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
                                        $res['width'] = $width;
                                        $res['buyer'] = print_username($res['user_id'], 'plain', 0, '', '');
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['rfp'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], true);
                                        $res['average'] = $ilance->currency->format($res['average'], $res['currencyid']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($rowcount % 2) ? 'alt1' : 'alt1');
                                        $res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true);
                                        $resrows++;                                        
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                             $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
                                                if ($td == $columns - 1 OR $td == $rowstotal - 1)
                                                {
                                                        $res['separator_end'] = '</tr>';
                                                }
                                                else if ($td == $columns)
                                                {
                                                        $res['separator_end'] = '';
                                                        $res['separator_begin'] = '<tr>';
                                                        $td = 0;
                                                }
                                                else 
                                                {
                                                        $res['separator_end'] = '';
                                                }
                                                $td++;
                                                if ($res['separator_end'] == '</tr>')
                                                {
                                                        $res['separator'] .= $separator;
                                                }
                                                else
                                                { 
                                                        $res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
                                                }
	                                }
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $endingsoon[] = $res;
                                }
                        }
                        else
                        {
                                $show['endingsoonservices'] = false;
                        }
                }
                else
                {
                        $show['endingsoonservices'] = false;        
                }                
                $ilance->timer->stop();
                DEBUG("fetch_ending_soon_auctions(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $endingsoon;
        }
        /**
        * Function to return template array data for featured auctions in picture grid mode.
        * This function also take lowest unique bidding into account when displaying number of bids
        *
        * Function now takes into consideration if a member is not active (don't display the listing).
        * 
        * @param       string        auction type
        * @param       integer       number of columns to display (default 4)
        * @param       integer       number of rows to display (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        * @param       array         excluded project ids (to prevent search results showing both featured and regular listings simultaneously)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
        function fetch_featured_auctions($auctiontype = '', $columns = 4, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false, $excludelist = array())
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                if ($ilconfig['showfeaturedlistings'] == false)
                {
                        $show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
                        return;
                }
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
                if ($cid > 0)
                {
                        $childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
                        $subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
                        $cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                }
                else
                {
                        $cidcondition = "AND p.cid > 0";        
                }
                if (!empty($keywords))
                {
                        $kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
                }
                // build exclusion query bit to prevent the same listings as the one being viewed to show up
                $excluded = '';
                if (isset($excludelist) AND !empty($excludelist) AND is_array($excludelist) AND count($excludelist) > 0)
                {
                        if (count($excludelist) == 1)
                        {
                                $excluded .= "AND p.project_id != '" . intval($excludelist[0]) . "'";         
                        }
                        else if (count($excludelist) > 1)
                        {
                                $excluded .= "AND (";
                                foreach ($excludelist AS $projectid)
                                {
                                        if (!empty($projectid) AND $projectid > 0)
                                        {
                                                $excluded .= "p.project_id != '" . intval($projectid) . "' OR ";
                                        }
                                }
                                $excluded = substr($excluded, 0, -4);
                                $excluded .= ")";
                        }
                }
                ($apihook = $ilance->api('fetch_featured_auctions_start')) ? eval($apihook) : false;
                $featuredauctions = array();
                if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                	$limit = $columns * $rows;
                        $sqlproductauctions = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.project_title, p.description, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, p.retailprice, p.buynow, p.buynow_qty, p.buynow_price, p.currentprice, $query_fields p.highlite, p.currencyid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'product'
                                    AND p.status = 'open'
                                    AND p.featured = '1'
                                    AND p.visible = '1'
                                    $excluded
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlproductauctions) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sqlproductauctions);
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['featuredproductauctions'] = true;
                                $resrows = 0;
                                while ($res = $ilance->db->fetch_array($sqlproductauctions, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					$res['width'] = $width;
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);
                                        }
                                        else
                                        {
                                                $res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
                                        }
                                       // $res['merchant'] = print_username($res['user_id'], 'href');
                                        //$res['seller'] = $res['merchant'];
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['merch'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                        $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                        if ($res['project_details'] == 'unique')
                                        {
                                                $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                        }
                                        else
                                        {
                                                if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                {
                                                        $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                        $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                        $res['buynowtxt'] = $phrase['_buy'] . ':';
														//new herakle bid comment reason buynow item 
														$show['hid'] = 'hidbid';
                                                        $res['price'] =  $res['bid'] = '';		
                                                       // $res['price'] = $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                                }
                                                else
                                                {
												//karthik on may 26 for categories
												       $show['hid'] = '';
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                        $res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>&nbsp;<span class="smaller gray">('.$res['bids'].'&nbsp;'.$phrase['_bids_lower'].')</span>';
												}
                                        }
                                        $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                        $resrows++;
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                	}
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $featuredauctions[] = $res;                                        
                                }
                        }
                        else
                        {
                                $show['featuredproductauctions'] = false;
                        }
                        if ($show['featuredproductauctions'] AND !empty($_SESSION['ilancedata']['user']['searchoptions']))
                        {
                                $temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                                if (isset($temp['displayfeatured']) AND $temp['displayfeatured'] == 'false')
                                {
                                        $show['featuredproductauctions'] = false;
                                }
                        }
                }
                if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                		$limit = $columns * $rows;
                        $sqlserviceauctions = $ilance->db->query("
                                SELECT p.user_id, p.project_title, p.description, p.project_id, p.cid, p.bids, p.additional_info, $query_fields p.highlite, p.bid_details, p.currencyid, p.filtered_budgetid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'service'
                                    AND p.status = 'open'
                                    AND p.featured = '1'
                                    AND p.visible = '1'
                                    $excluded
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlserviceauctions) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sqlserviceauctions);                                
                                $width = number_format(100 / $columns, 1);                                
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['featuredserviceauctions'] = true;                                
                                $resrows = 0;
                                while ($res = $ilance->db->fetch_array($sqlserviceauctions, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
	                                        $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					$res['width'] = $width;
                                        $res['buyer'] = print_username($res['user_id'], 'plain', 0, '', '');
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], true);
                                        $res['average'] = $ilance->currency->format($res['average'], $res['currencyid']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                        $res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true);
                                        $resrows++;                                        
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = '0';
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                }
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $featuredauctions[] = $res;
                                }
                        }
                        else
                        {
                                $show['featuredserviceauctions'] = false;
                        }
                        if ($show['featuredserviceauctions'] AND !empty($_SESSION['ilancedata']['user']['searchoptions']))
                        {
                                $temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                                if (isset($temp['displayfeatured']) AND $temp['displayfeatured'] == 'false')
                                {
                                        $show['featuredserviceauctions'] = false;
                                }
                        }
                }                
                $ilance->timer->stop();
                DEBUG("fetch_featured_auctions(\$auctiontype = $auctiontype, \$columns = $columns, \$rows = $rows) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $featuredauctions;
        }
		// Murugan changes on Feb 17 For Featured Auction List
		function fetch_featured_auctions_new($auctiontype = '', $columns = 4, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false, $excludelist = array(),$series)
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;           
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                if ($ilconfig['showfeaturedlistings'] == false)
                {
                        $show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
                        return;
                }
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
						// Murugan changes on Feb 17 for Featured Auction List
                        //$childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
						if( $series > 0)
						{
						 $childrenids = $ilance->categories->fetch_children_pcgs($series);
						 if(!empty($childrenids))						 					
                         $subcategorylist .= implode(',',$childrenids);
                        $cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
						}
						 else
                		{
                        	$cidcondition = "AND p.cid > 0";        
               			 }
 //karthik on may28 for featured search
                if (!empty($keywords))
                {
				//venkat featured search
                        //$kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
						/*if(!$keywords[1]))
						{*/
					/*	$kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords[0]) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords[0]) . "%'OR p.project_id LIKE '%" . $ilance->db->escape_string($keywords[0]) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($keywords[0]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keywords[0]]) . "%')";
						}
						else
						{*/
						//venkat
						if(is_array($keywords))
						{
						$key="";
						foreach($keywords As $values)
						{
						$key.=$values;
						}
					$kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($key) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($key) . "%'OR p.project_id LIKE '%" . $ilance->db->escape_string($key) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($key) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($key) . "%')";
						}
						else
						{
						$kwcondition = "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%'OR p.project_id LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
						}
						/*}*/
                }
                // build exclusion query bit to prevent the same listings as the one being viewed to show up
                $excluded = '';
                if (isset($excludelist) AND !empty($excludelist) AND is_array($excludelist) AND count($excludelist) > 0)
                {
                        if (count($excludelist) == 1)
                        {//may31
                                $excluded .= "AND p.project_id = '" . intval($excludelist[0]) . "'";         
                        }
                        else if (count($excludelist) > 1)
                        {
                                $excluded .= "AND (";
                                foreach ($excludelist AS $projectid)
                                {
                                        if (!empty($projectid) AND $projectid > 0)
                                        {
                                                $excluded .= "p.project_id != '" . intval($projectid) . "' OR ";
                                        }
                                }
                                $excluded = substr($excluded, 0, -4);
                                $excluded .= ")";
                        }
                }
                ($apihook = $ilance->api('fetch_featured_auctions_start')) ? eval($apihook) : false;
                $featuredauctions = array();
				//karthik starton Apr 30
				  $sqlquery['pricerange'] = $clear_price = '';
                        if ($show['mode_product'])
                        {
                                if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
                                {
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
					$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
                                        $sqlquery['pricerange'] .= "AND (p.currentprice >= " . intval($ilance->GPC['fromprice']) . " ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_min_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong></span> &ndash; ');
                                        handle_search_verbose_save($phrase['_min_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['fromprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= "AND (p.currentprice >= 0 ";
                                }
                                if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
                                {
					$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
                                        $removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
					$clear_price = '<a href="' . $removeurl . '" rel="nofollow">' . $phrase['_clear'] . '</a>';
                                        $sqlquery['pricerange'] .= "AND p.currentprice <= " . intval($ilance->GPC['toprice']) . ") ";
                                        handle_search_verbose('<span class="black"><!--' . $phrase['_max_price'] . ': --><strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="' . $phrase['_remove_this_item_specific_from_your_search'] . '" /></a>-->, ');
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $ilance->common->xss_clean($ilance->currency->format($ilance->GPC['toprice'])) . '</strong>, ');
                                }
                                else
                                {
                                        $sqlquery['pricerange'] .= ")";
                                        handle_search_verbose_save($phrase['_max_price'] . ': <strong>' . $phrase['_unlimited'] . '</strong>, ');
                                }
                        }
                $con=$sqlquery['pricerange'];
				//karthik end on apr 30
				//karthik may03
			/* $url=parse_url($php_self);
			 $url1=explode('&',$url['query']);
			 $url2 = count($url1)-1;
			 echo  $url1[$url2];
			*/
				if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'])
				{
				     $featured=" AND p.buynow='1' ";
				}
				else if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'])
				{
				     $featured=" AND p.buynow = '0' ";
				}
				else
				{
				    $featured='';
				}
				//end may 03
                if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                	$limit = $columns * $rows;					
                        $sqlproductauctions = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.project_title, p.description, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, p.retailprice, p.buynow, p.buynow_qty, p.buynow_price, p.currentprice, $query_fields p.highlite, p.currencyid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'product'
                                    AND p.status = 'open'
                                    AND p.featured = '1'
                                    AND p.visible = '1'
									$featured
									 $con
                                    $excluded
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlproductauctions) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sqlproductauctions);
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['featuredproductauctions'] = true;
                                $resrows = 0;
                                while ($res = $ilance->db->fetch_array($sqlproductauctions, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					$res['width'] = $width;
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);
                                        }
                                        else
                                        {
                                                $res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
                                        }
                                       // $res['merchant'] = print_username($res['user_id'], 'href');
                                        //$res['seller'] = $res['merchant'];
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['merch'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                        $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                        if ($res['project_details'] == 'unique')
                                        {
                                                $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                        }
                                        else
                                        {
                                                if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                {
                                                        $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                        $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                        $res['buynowtxt'] = $phrase['_buy'] . ':';
														 //karthik may03
                                                        $res['price'] =  $res['bid'] = '';	
														$res['newbids'] = '';
														//karthik may03 end
                                                       // $res['price'] = $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                                }
                                                else
                                                {
												//karthik may03
												        $res['newbids'] = '<div class="smaller gray">('.$res['bids'].' bids)</div>';
														//karthik may03 end
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                        $res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>';
                                                }
                                        }
                                        $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                        $resrows++;
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                	}
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $featuredauctions[] = $res;                                        
                                }
                        }
                        else
                        {
                                $show['featuredproductauctions'] = false;
                        }
                        if ($show['featuredproductauctions'] AND !empty($_SESSION['ilancedata']['user']['searchoptions']))
                        {
                                $temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                                if (isset($temp['displayfeatured']) AND $temp['displayfeatured'] == 'false')
                                {
                                        $show['featuredproductauctions'] = false;
                                }
                        }
                }
                if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                		$limit = $columns * $rows;
                        $sqlserviceauctions = $ilance->db->query("
                                SELECT p.user_id, p.project_title, p.description, p.project_id, p.cid, p.bids, p.additional_info, $query_fields p.highlite, p.bid_details, p.currencyid, p.filtered_budgetid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'service'
                                    AND p.status = 'open'
                                    AND p.featured = '1'
                                    AND p.visible = '1'
									$featured
									 $con
									 $excluded
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlserviceauctions) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sqlserviceauctions);                                
                                $width = number_format(100 / $columns, 1);                                
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['featuredserviceauctions'] = true;                                
                                $resrows = 0;
                                while ($res = $ilance->db->fetch_array($sqlserviceauctions, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
	                                        $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					$res['width'] = $width;
                                        $res['buyer'] = print_username($res['user_id'], 'plain', 0, '', '');
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], true);
                                        $res['average'] = $ilance->currency->format($res['average'], $res['currencyid']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                        $res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true);
                                        $resrows++;                                        
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = '0';
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                }
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $featuredauctions[] = $res;
                                }
                        }
                        else
                        {
                                $show['featuredserviceauctions'] = false;
                        }
                        if ($show['featuredserviceauctions'] AND !empty($_SESSION['ilancedata']['user']['searchoptions']))
                        {
                                $temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                                if (isset($temp['displayfeatured']) AND $temp['displayfeatured'] == 'false')
                                {
                                        $show['featuredserviceauctions'] = false;
                                }
                        }
                }                
                $ilance->timer->stop();
                DEBUG("fetch_featured_auctions(\$auctiontype = $auctiontype, \$columns = $columns, \$rows = $rows) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $featuredauctions;
        }
			//New Changes on 12 Jan 09 for Denomination Featured Auction List
		function fetch_featured_auctions_denominations($auctiontype = '', $columns = 5, $rows = 1,$date_end = '', $forcenoflash = false)
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage ,$php_self;           
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                if ($ilconfig['showfeaturedlistings'] == false)
                {
                        $show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
                        return;
                }
				if($date_end=='')
				{
				  $date_con='';
				}
				else
				{
				  $date_con="AND date(date_end)='".$date_end."'";
				}
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
                $limit = $columns * $rows;	
               $sql="SELECT UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('". DATETIME24H . "') AS mytime,a.filename,a.filehash,p.user_id, p.project_id, p.project_title, p.description, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, p.retailprice, p.buynow, p.buynow_qty, p.buynow_price, p.currentprice,p.highlite,p.currencyid
                ,( select count(attachid) from  " . DB_PREFIX . "attachment  where project_id=p.project_id) as pictures_count
                                                          FROM " . DB_PREFIX . "projects p
                                                          left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto'
                                                          WHERE p.status =  'open'
                                                          $date_con
                                                          AND  p.featured = '1'
                                                         ORDER BY RAND()
                                                         LIMIT 10
                                                       ";
                $sqlproductauctions = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlproductauctions) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sqlproductauctions);
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['featuredproductauctions'] = true;
                                $resrows = 0;
                                while ($res = $ilance->db->fetch_array($sqlproductauctions, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					                    $res['width'] = $width;
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $html = '<a href="' . $url . '"><img src="' .HTTPS_SERVER. 'image/72/96/' . $res['filename'] . '" border="0" alt="" style="border-color:#ffffff" class="gallery-thumbs-image-cluster" /></a>';
                                               // $res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);

                                        }
                                        else
                                        {
                                                $html = '<a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '"><img src="' .HTTPS_SERVER. 'image/72/96/' . $res['filename'] . '" border="0" alt="" style="border-color:#ffffff" class="gallery-thumbs-image-cluster" /></a>';
                                               // $res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
                                        }

                                         $html1 = '
                                                    <div class="gallery-thumbs-cell">           
                                                    <div class="gallery-thumbs-entry">
                                                            <div class="gallery-thumbs-main-entry">
                                                                    <div class="gallery-thumbs-wide-wrapper">
                                                                            <div class="gallery-thumbs-wide-inner-wrapper">';
                                                                            $html1 .= $html;
                                                                            $html1 .= '<div class="gallery-thumbs-corner-text"><span>' . ($res['pictures_count']) . ' photos</span></div>
                                                                            </div>
                                                                    </div>
                                                            </div>
                                                    </div>
                                                    </div>
                                                    ';
                                        $res['photo']=$html1;            
                                       // $res['merchant'] = print_username($res['user_id'], 'href');
                                        //$res['seller'] = $res['merchant'];
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['merch'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                        $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                        if ($res['project_details'] == 'unique')
                                        {
                                                $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                        }
                                        else
                                        {
                                                if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                {
                                                        $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                        $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                        $res['buynowtxt'] = $phrase['_buy'] . ':';
                                                       //karthik may03
                                                       $show['hid'] = 'hidbid';
                                                        $res['price'] =  $res['bid'] = '';	
														//karthik may03 end
                                                }
                                                else
                                                {
												//karthik may03
												        $show['hid'] = '';
														//karthik may03 end
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                        $res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>';
                                                }
                                        }
                                        $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        //$res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                        //suku
                                        //$res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        $res['timeleft'] = auction_time_left_new($res,false);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
										if($res['bids']>0)
										{
										$res['bids']= '<span class="blue">'.$res['bids'].' '.'Bids</span>';
										}
										else
										{
										$res['bids']='';
										}
                                        $resrows++;
                                        if ($resrows == 6)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                	}
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $featuredauctions[] = $res;                                        
                                }
                        }
                        else
                        {
                                $show['featuredproductauctions'] = false;
                        }
                $ilance->timer->stop();
                return $featuredauctions;
        }
		//Denomination Featured Auction List End
        /**
        * Function to return template array data for featured auctions in picture grid mode.
        * This function also take lowest unique bidding into account when displaying number of bids
        *
        * Function now takes into consideration if a member is not active (don't display the listing).
        *
        * @param       string        auction type
        * @param       integer       auction limit (default 5)
        * @param       integer       auction rows limit (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
        function fetch_latest_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;
                $ilance->timer->start();
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $ilance->bbcode = construct_object('api.bbcode');
                if ($ilconfig['showlatestlistings'] == false)
                {
                        $show['latestproductauctions'] = $show['latestserviceauctions'] = false;
                        return;
                }
                $latestauctions = array();
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
                if ($cid > 0)
                {
                        $childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
                        $subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
                        $cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
                }
                else
                {
                        $cidcondition = "AND p.cid > 0";        
                }
                ($apihook = $ilance->api('fetch_latest_auctions_start')) ? eval($apihook) : false;
                if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                	$limit = ($columns * $rows);
                        $sql = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.project_title, p.description, p.highlite, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, $query_fields p.retailprice, p.buynow_price, p.currentprice, p.currencyid, p.buynow
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'product'
                                    AND p.status = 'open'
                                    AND p.visible = '1'
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY p.id DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sql);
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['latestproductauctions'] = true;                                
                                $resrows = $rowcount = 0;
                                $latestauctions = array();
                                while ($res = $ilance->db->fetch_assoc($sql))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);                                                
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);                                                                                                                
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					$res['width'] = $width;
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);
                                        }
                                        else
                                        {
                                                $res['photo'] = print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
                                        }
                                       // $res['merchant'] = print_username($res['user_id'], 'href');
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['merch'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                        $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                        if ($res['project_details'] == 'unique')
                                        {
                                                $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                        }
                                        else
                                        {
                                                if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                {
                                                        $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                        $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                        $res['buynowtxt'] = $phrase['_buy'] . ':';
														//new herakle bid comment reason buynow item 
														$show['hid'] = 'hidbid';
                                                        $res['price'] =  $res['bid'] = '';
                                                       // $res['price'] = $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                                }
                                                else
                                                {
                                                   //karthik on may 26 for categories
												       $show['hid'] = '';
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                                        $res['price'] = '<strong>' . $phrase['_bid'] . ': ' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>&nbsp;<span class="smaller gray">('.$res['bids'].'&nbsp;'.$phrase['_bids_lower'].')</span>';
                                                                }
                                        }
                                        $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                        $resrows++;                                        
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                }
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $latestauctions[] = $res;
                                }
                        }
                        else
                        {
                                $show['latestproductauctions'] = false;
                        }
                }
                else if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                	$limit = $columns * $rows;
                        $sql = $ilance->db->query("
                                SELECT p.user_id, p.project_title, p.description, p.additional_info, p.highlite, p.project_id, p.cid, $query_fields p.bids, p.bid_details, p.currencyid, p.filtered_budgetid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'service'
                                    AND p.status = 'open'
                                    AND p.visible = '1'
                                    $cidcondition
                                    $kwcondition
                                    AND u.status = 'active'
                                    " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY p.id DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sql);                                
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                //$separator .= '<td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td><td width="33.3%" valign="top"></td>';
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['latestserviceauctions'] = true;
                                $resrows = 0;
                                $latestauctions = array();
                                while ($res = $ilance->db->fetch_assoc($sql))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					$res['width'] = $width;
                                        $res['buyer'] = print_username($res['user_id'], 'plain', 0, '', '');
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>';
                                        if ($ilconfig['globalfilters_vulgarpostfilter'])
                                        {
                                        	$res['description'] = strip_vulgar_words(short_string($ilance->bbcode->strip_bb_tags($res['description']), 45));
                                        }
                                        else 
                                        {
                                        	$res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        }
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']));
                                        $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';
                                        $res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', 'background', 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
                                        //$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], true);
                                        $res['average'] = $ilance->currency->format($res['average'], $res['currencyid']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
                                        $res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true);
                                        $resrows++;                                        
                                        if ($resrows == 1)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                }
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $latestauctions[] = $res;
                                }
                        }
                        else
                        {
                                $show['latestserviceauctions'] = false;
                        }
                }
                ($apihook = $ilance->api('fetch_latest_auctions_end')) ? eval($apihook) : false;
                $ilance->timer->stop();
                DEBUG("fetch_latest_auctions(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $latestauctions;
        }
        function expire_featured_status_listings()
        {
                global $ilance, $ilconfig, $phrase;
                $sql = $ilance->db->query("
                        SELECT project_id, featured_date
                        FROM " . DB_PREFIX . "projects
                        WHERE featured = '1'
                                AND featured_date != '0000-00-00 00:00:00'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))	
                        {
                                $date1split = explode(' ', $res['featured_date']);
                                $date2split = explode('-', $date1split[0]);
                                $totaldays = $ilconfig['productupsell_featuredlength'];
                                $elapsed = $ilance->datetime->fetch_days_between($date2split[1], $date2split[2], $date2split[0], gmdate('m'), gmdate('d'), gmdate('Y'));
                                $days = ($totaldays - $elapsed);
                                if ($days < 0)
                                {
                                        // update this portfolio to non-featured
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET featured = '0',
                                                featured_date = '0000-00-00 00:00:00'
                                                WHERE project_id = '" . $res['project_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
        }
        // function run from cron.dailyrfp.php
        function category_listing_count_fixer()
        {
                global $ilance, $ilconfig, $phrase;
                $cronlog = '';
                $sql = $ilance->db->query("
                        SELECT cid, auctioncount
                        FROM " . DB_PREFIX . "categories
                        WHERE auctioncount > 0
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))	
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT COUNT(*) AS auctioncount
                                        FROM " . DB_PREFIX . "projects
                                        WHERE cid = '" . $res['cid'] . "'
                                                AND status = 'open'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $res2 = $ilance->db->fetch_array($sql2);
                                        if ($res['auctioncount'] != $res2['auctioncount'])
                                        {
                                                $loop = ($res['auctioncount'] - $res2['auctioncount']);
                                                if ($loop < 0)
                                                {
                                                        $loop = 0;
                                                }
                                                for ($i = 1; $i <= $loop; $i++)
                                                {
                                                        build_category_count($res['cid'], 'subtract', "category_listing_count_fixer(): subtracting increment count category id $res[cid]");
                                                }
                                        }
                                }
                        }
                }
                return $cronlog;
        }
        function relist_auction($id)
        {
        	global $ilance , $ilconfig;
        	$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($id) . "'
                ");
        	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        	$ilance->auction_rfp = construct_object('api.auction_rfp');
        	$rfpid = $ilance->auction_rfp->construct_new_auctionid_bulk();
        	$sql_shipp = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects_shipping
                        WHERE project_id = '" . intval($id) . "'
                ");
		$res_shipp = $ilance->db->fetch_array($sql_shipp, DB_ASSOC);
        	// #### SHIPPING INFORMATION ###################################
                $shipping1 = array(
                        'ship_method' => (isset($ilance->GPC['ship_method'])) ? $ilance->GPC['ship_method'] : 'flatrate',
                        'ship_packagetype' => (isset($ilance->GPC['ship_packagetype'])) ? $ilance->GPC['ship_packagetype'] : '',
                        'ship_length' => (isset($ilance->GPC['ship_length'])) ? $ilance->GPC['ship_length'] : '12',
                        'ship_width' => (isset($ilance->GPC['ship_width'])) ? $ilance->GPC['ship_width'] : '12',
                        'ship_height' => (isset($ilance->GPC['ship_height'])) ? $ilance->GPC['ship_height'] : '12',
                        'ship_weightlbs' => (isset($ilance->GPC['ship_weightlbs'])) ? $ilance->GPC['ship_weightlbs'] : '1',
                        'ship_weightoz' => (isset($ilance->GPC['ship_weightoz'])) ? $ilance->GPC['ship_weightoz'] : '0',
                        'ship_handlingtime' => (isset($ilance->GPC['ship_handlingtime'])) ? $ilance->GPC['ship_handlingtime'] : '3',
                        'ship_handlingfee' => (isset($ilance->GPC['ship_handlingfee'])) ? $ilance->currency->string_to_number($ilance->GPC['ship_handlingfee']) : '0.00'
                );
                $sql_shipp_dest = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects_shipping_destinations
                        WHERE project_id = '" . intval($id) . "'
                ");
                $res_shipp_dest = $ilance->db->fetch_array($sql_shipp_dest, DB_ASSOC);
                for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
                {
                        $shipping2['ship_options_' . $i] = (isset($res_shipp_dest['ship_options_' . $i])) ? $res_shipp_dest['ship_options_' . $i] : '';
                        $shipping2['ship_service_' . $i] = (isset($res_shipp_dest['ship_service_' . $i])) ? intval($res_shipp_dest['ship_service_' . $i]) : '';
                        $shipping2['ship_fee_' . $i] = (isset($res_shipp_dest['ship_fee_' . $i])) ? $ilance->currency->string_to_number($res_shipp_dest['ship_fee_' . $i]) : '0.00';
                        $shipping2['freeshipping_' . $i] = (isset($res_shipp_dest['freeshipping_' . $i])) ? intval($res_shipp_dest['freeshipping_' . $i]) : '0';
                        $shipping2['ship_options_custom_region_' . $i] = (isset($res_shipp_dest['ship_options_custom_region_' . $i])) ? $res_shipp_dest['ship_options_custom_region_' . $i] : array();
                }
                $res['shipping'] = array_merge($shipping1, $shipping2);
                unset($shipping1, $shipping2);
                $this->rewrite_photos($id, $rfpid);
        	$enhancements = array();
        	$promo = array('bold', 'featured', 'highlite', 'autorelist');
        	foreach($promo AS $key)
        	{
        		if (isset($res[$key]) AND $res[$key] == '1')
        		{
        			if (!isset($ilance->GPC[$promo][$id]))
        			{
        				$enhancements[$key] = $res[$key];
        			}
        		}
        	}
                $duration = strtotime($res['date_end']) - strtotime($res['date_starts']);
                if ($duration / 60 > 0 AND $duration / 60 <= 30)
                {
                        $duration_unit = 'M';
                        $duration = $duration / 60;
                }
                else if ($duration / 3600 > 0 AND $duration / 3600 <= 30)
                {
                        $duration_unit = 'H';
                        $duration = $duration / 3600;
                }
                else if ($duration / 86400 > 0 AND $duration / 86400 <= 30)
                {
                        $duration_unit = 'D';
                        $duration = $duration / 86400;
                }
        	$ilance->auction_rfp->insert_product_auction(
			$_SESSION['ilancedata']['user']['userid'],
			$res['project_type'],
			'open',
			$res['project_state'],
			$res['cid'],
			$rfpid,
			$res['project_title'],
			$res['description'],
			$res['description_videourl'],
			$res['additional_info'],
			$res['keywords'],
			$custom = array(),
			$profileanswer = array(),
			$res['filtered_auctiontype'],
			$res['startprice'],
			$res['project_details'] = 'public',
			$res['bid_details'],
			$res['filter_rating'],
			$res['filter_country'],
			$res['filter_state'],
			$res['filter_city'],
			$res['filter_zip'],
			$res['filtered_rating'],
			$res['filtered_country'],
			$res['filtered_state'],
			$res['filtered_city'],
			$res['filtered_zip'],
			$res['city'],
			$res['state'],
			$res['zipcode'],
			$res['country'],
			$res['shipping'],
			$res['buynow'],
			$res['buynow_price'],
			$res['buynow_qty'],
			$enhancements,
			$res['reserve'],
			$res['reserve_price'],
			$res['filter_underage'],
			$res['filter_escrow'],
			$res['filter_gateway'],
			$res['filter_offline'],
			$res['filter_publicboard'],
			$invitelist = '',
			$invitemessage = '',
			$year = '',
			$month = '',
			$day = '',
			$hour = '',
			$min = '',
			$sec = '',
			$duration,
			$duration_unit,
			unserialize($res['paymethod']),
			unserialize($res['paymethodoptions']),
			unserialize($res['paymethodoptionsemail']),
			$res['retailprice'],
			$res['uniquebidcount'],
			$res['draft'] = '0',
			$res['returnaccepted'],
			$res['returnwithin'],
			$res['returngivenas'],
			$res['returnshippaidby'],
			$res['returnpolicy'],
			$res['donation'],
			$charityid = '',
			$res['donationpercentage'],
			$skipemailprocess = 1,
			$apihookcustom = '',
			$isbulkupload = false,
			$sample = '',
			$res['currencyid'],
			$noexit = '1'
		);
        }
        function fetch_featured_auctions_pricerealized($auctiontype = '', $columns = 5, $rows = 1,$date_end = '', $forcenoflash = false)
        {
		
                global $ilance, $ilconfig, $show, $phrase, $ilpage ,$php_self;           
                $ilance->timer->start();
                $ilance->bbcode = construct_object('api.bbcode');
                $ilance->bid = construct_object('api.bid');
                $ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
                $ilance->encrypt = construct_object('api.encrypt');
                if ($ilconfig['showfeaturedlistings'] == false)
                {
                        $show['featuredserviceauctions'] = $show['featuredproductauctions'] = 0;
                        return;
                }
				if($date_end=='')
				{
				  $date_con='';
				}
				else
				{
				  $date_con="AND date(date_end)='".$date_end."'";
				}
                $query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
                $limit = $columns * $rows;	

                $start_dateBy = date('Y-m-d',strtotime('-14 Sunday'));
                $end_dateBy = date('Y-m-d',strtotime("-1 Sunday"));

                $sqlproductauctions = $ilance->db->query("SELECT a.filehash,a.filename,p.user_id, p.project_id, p.project_title, p.description, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, p.retailprice, p.buynow, p.buynow_qty, p.buynow_price, p.currentprice,p.highlite,p.currencyid,p.buyer_fee,
                                                            (select count(attachid) as pictures_count from " . DB_PREFIX . "attachment where project_id=p.project_id limit 1) as pictures_count
														  FROM " . DB_PREFIX . "projects p
                                                          left join " . DB_PREFIX . "attachment a on a.project_id=p.project_id and a.attachtype='itemphoto'
														  WHERE p.status =  'expired'
														  AND p.featured = '1'
                                                          AND p.bids > 0
                                                          AND p.haswinner != 0
                                                          AND p.winner_user_id > 0
                                                          AND (DATE(p.date_end) >= '".$start_dateBy."' AND DATE(p.date_end) <= '".$end_dateBy."')
													      ORDER BY RAND()
                                                          LIMIT 10
                                                       ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlproductauctions) > 0)
                        {
                                $rowstotal = $ilance->db->num_rows($sqlproductauctions);
                                $width = number_format(100 / $columns, 1);
                                $separator = '';
                                if ($rowstotal != $columns && $rowstotal != 0)
                                {
                                        $neededtds = $columns - $rowstotal;
                                        for ($i = 0; $i < $neededtds; $i++)
                                        {
                                                $separator .= '<td width="' . $width . '%" valign="top"></td>';
                                        }
                                }
                                $show['featuredpricerealized'] = true;
                                $resrows = 0;
								$td = 0;
                                while ($res = $ilance->db->fetch_array($sqlproductauctions, DB_ASSOC))
                                {
                                        if ($ilconfig['globalfilters_maxcharacterstitle'] != '0')
                                        {
                                                $temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_title, $res['project_title']) != '0')
                                                {
                                                        $res['project_title'] = $temp_title . '...';
                                                }
                                                $temp_desc = cutstring($res['description'], $ilconfig['globalfilters_maxcharacterstitle']);
                                                if (strcmp($temp_desc, $res['description']) != '0')
                                                {
                                                        $res['description'] = $temp_desc . '...';
                                                }
                                        }
					  $res['width'] = $width;
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);       
                                        }
                                        else
                                        {
                                                $url=$ilpage['merch'] . '?id=' . $res['project_id'];
                                                
                                        }
                                        //$res['photo'] = print_item_photo($url, 'thumb', $res['project_id']);
                                        $html1 = '<div class="gallery-thumbs-cell"><div class="gallery-thumbs-entry"><div class="gallery-thumbs-main-entry"><div class="gallery-thumbs-wide-wrapper"><div class="gallery-thumbs-wide-inner-wrapper">';
                                        $html1 .= '<a href="' . $url . '"><img src="' .HTTPS_SERVER. 'image/72/96/' . $res['filename'] . '" border="0" alt="" style="border-color:#ffffff" class="gallery-thumbs-image-cluster" /></a>';
                                        $html1 .= '<div class="gallery-thumbs-corner-text"><span>' . $res['pictures_count'] . ' photos</span></div></div></div></div></div></div>';
                                        $res['photo']=$html1;
                                       // $res['merchant'] = print_username($res['user_id'], 'href');
                                        //$res['seller'] = $res['merchant'];
                                        $res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="'.$ilpage['merch'].'?id='.$res['project_id'].'">'.stripslashes($res['project_title']).'</a>';
                                        $res['description'] = short_string($ilance->bbcode->strip_bb_tags($res['description']), 45);
                                        $res['additional_info'] = short_string($ilance->bbcode->strip_bb_tags($res['additional_info']), 45);
                                        $res['photoheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                        $res['photowidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                        if ($res['project_details'] == 'unique')
                                        {
                                                $res['bids'] = $ilance->bid_lowest_unique->fetch_bid_count($res['project_id']);
                                                $res['price'] = '<span class="black">' . $phrase['_retail_price'] . ':</span> <strong>' . $ilance->currency->format($res['retailprice'], $res['currencyid']) . '</strong>';
                                                $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
                                        }
                                        else
                                        {
                                                if ($res['buynow_price'] > 0 AND $res['buynow'])
                                                {
                                                        $res['buynow'] = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
                                                        $res['buynowimg'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" alt="' . $phrase['_buy_now_price'] . '" border="0" />';
                                                        $res['buynowtxt'] = $phrase['_buy'] . ':';
                                                       //karthik may03
                                                       $show['hid'] = 'hidbid';
                                                        $res['price'] =  $res['bid'] = '';	
														//karthik may03 end
                                                }
                                                else
                                                {
												//karthik may03
												        $show['hid'] = '';
														//karthik may03 end
                                                        $res['buynowimg'] = $res['buynowtxt'] = $res['buynow'] = '';
														$tot_fee=$res['currentprice']+$res['buyer_fee'];

                                                        $soldprice = $ilance->currency->format($tot_fee, $res['currencyid']);
                                                        $hamerprice = $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                                        $encramnt = $ilance->encrypt->Encrypt_Amount('Sold: '.$soldprice.' ||('.$hamerprice.' hammer)');

                                                        if($ilconfig['settings_sold_coins_price_to_image'])
                                                        {
                                                            $res['price'] = '<img style="margin: -5px 0 0 0px;" width="180" height="33" src="images.php?q='.$encramnt.'" />';
                                                        }
                                                        else
                                                        {
                                                            $res['price'] = '<strong>  Sold: ' . $soldprice . '</strong><br/><span style="color: #999999;">('.$hamerprice.' hammer)</span>';
                                                        }
                                                        // $res['price'] = '<img src="images.php?q='.$encramnt.'" />';

                                                        //$res['price'] = '<strong>  Sold: ' . $ilance->currency->format($tot_fee, $res['currencyid']) . '</strong>';
                                                }
                                        }
                                        $res['listed'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                       // $res['categoryname'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']));
                                       // $res['category'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcat', $res['cid'], $auctionid = 0, $res['categoryname'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0) : '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . $res['categoryname'] . '</a>';                                        
                                        
										//$res['timeleft'] = $this->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, $forcenoflash);
										//suku
                                        $res['timeleft'] = $this->auction_time_left_internal($res,true);;
										//$res['icons'] = $this->auction_icons($res['project_id'], $res['user_id']);
                                        $res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
										if($res['bids']>0)
										{
										$res['bids']= '<span class="black">'.$res['bids'].' '.'Bids</span>';
										}
										else
										{
										$res['bids']='';
										}
                                        $resrows++;
                                        if ($resrows == 6)
                                        {
                                                $res['separator_begin'] = '<tr>';
                                                $td = 0;
                                        }
                                        else 
                                        {
                                                $res['separator_begin'] = '';
                                        }
                                        if ($resrows == $rowstotal)
                                        {
                                                $res['separator_end'] = '</tr>';
                                        }
                                        else 
                                        {
                                                $res['separator_end'] = '';
                                        }
                                        if ($rows != 1)
                                        {
	                                        if ($td == $columns - 1 OR $td == $rowstotal - 1)
	                                        {
	                                        	$res['separator_end'] = '</tr>';
	                                        }
	                                        else if ($td == $columns)
	                                        {
	                                        	$res['separator_end'] = '';
	                                        	$res['separator_begin'] = '<tr>';
	                                        	$td = 0;
	                                        }
	                                        else 
	                                        {
	                                        	$res['separator_end'] = '';
	                                        }
	                                        $td++;
	                                        if ($res['separator_end'] == '</tr>')
	                                        {
	                                        	$res['separator'] = $separator;
	                                        }
	                                        else
	                                        { 
	                                        	$res['separator'] = '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>';
	                                        }
	                                	}
                                        else
                                        {	
	                                        $res['separator'] = ($resrows < $rowstotal) ? '<td width="1" style="width:1px"></td><td width="1" style="background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer_gray.gif); background-repeat: repeat-y"></td>' : '';	                                        
	                                        if ($resrows == $rowstotal)
	                                        {
	                                                $res['separator'] .= $separator;
	                                        }
                                        }
                                        $featuredauctions[] = $res;                                        
                                }
                        }
                        else
                        {
                                $show['featuredproductauctions'] = false;
                        }
                $ilance->timer->stop();
                return $featuredauctions;
        
        }
        function rewrite_photos($old_id, $new_id)
        {
        	global $ilance, $ilconfig;
        	$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "attachment
                        WHERE project_id = '" . intval($old_id) . "'
                ", 0, null, __FILE__, __LINE__);
        	while ($res_photo = $ilance->db->fetch_array($sql, DB_ASSOC))
        	{
        		$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, project_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress)
                                VALUES(
                                NULL,
                                '" . $ilance->db->escape_string($res_photo['attachtype']) . "',
                                '" . $res_photo['user_id'] . "',
                                '" . intval($new_id) . "',
                                '" . intval($res_photo['category_id']) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($res_photo['filename']) . "',
                                '" . addslashes($res_photo['filedata']) . "',
                                '" . $ilance->db->escape_string($res_photo['filetype']) . "',
                                '" . $ilconfig['attachment_moderationdisabled'] . "',
                                '0',
                                '" . $ilance->db->escape_string($res_photo['filesize']) . "',
                                '" . $ilance->db->escape_string($res_photo['filehash']) . "',
                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "')
                        ", 0, null, __FILE__, __LINE__);
        	}
        }        

		function auction_time_left_internal($result,$showfullformat)
{
global $ilance, $myapi, $ilconfig, $ilconfig, $phrase;
								$result['mytime']=isset($result['mytime'])?$result['mytime']:0;
                                $dif =$result['mytime'];
                                $ndays = floor($dif / 86400);
                                $dif -= $ndays * 86400;
                                $nhours = floor($dif / 3600);
                                $dif -= $nhours * 3600;
                                $nminutes = floor($dif / 60);
                                $dif -= $nminutes * 60;
                                $nseconds = $dif;
                                $sign = '+';
                                if ($result['mytime'] < 0)
                                {
                                        $result['mytime'] = - $result['mytime'];
                                        $sign = '-';
                                }
                                if ($sign == '-')
                                {
                                        // expired
                                        $timeleft = $phrase['_ended'];
                                        $expiredauction = 1;
                                }
                                else
                                {
                                        if ($ndays != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $ndays    . $phrase['_d_shortform'] . ', ';	
                                                        $timeleft .= $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft = $ndays . $phrase['_d_shortform'] . ', ' . $nhours . $phrase['_h_shortform'];
                                                }
                                        }
                                        else if ($nhours != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];        
                                                }
                                                else
                                                {
                                                        $timeleft = $nhours . $phrase['_h_shortform'] . ', ' . $nminutes . $phrase['_m_shortform'];
                                                }
                                        }
                                        else
                                        {
                                                if ($nminutes != '0')
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nminutes . $phrase['_m_shortform'] . ', ' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                                else
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                        }
                                }
                          return $timeleft;
}

		function auction_time_left_internal_email($result,$showfullformat)
{
global $ilance, $myapi, $ilconfig, $ilconfig, $phrase;
								$result['mytime']=isset($result['mytime'])?$result['mytime']:0;
                                $dif =$result['mytime'];
                                $ndays = floor($dif / 86400);
                                $dif -= $ndays * 86400;
                                $nhours = floor($dif / 3600);
                                $dif -= $nhours * 3600;
                                $nminutes = floor($dif / 60);
                                $dif -= $nminutes * 60;
                                $nseconds = $dif;
                                $sign = '+';
                                if ($result['mytime'] < 0)
                                {
                                        $result['mytime'] = - $result['mytime'];
                                        $sign = '-';
                                }
                                if ($sign == '-')
                                {
                                        // expired
                                        $timeleft = $phrase['_ended'];
                                        $expiredauction = 1;
                                }
                                else
                                {
                                        if ($ndays != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $ndays    . $phrase['_d_shortform'] . ', ';	
                                                        $timeleft .= $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft = $ndays . $phrase['_d_shortform'] . ', ' . $nhours . $phrase['_h_shortform'];
                                                }
                                        }
                                        else if ($nhours != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];        
                                                }
                                                else
                                                {
                                                        $timeleft = $nhours . $phrase['_h_shortform'] . ', ' . $nminutes . $phrase['_m_shortform'];
                                                }
                                        }
                                        else
                                        {
                                                if ($nminutes != '0')
                                                {
                                                        $timeleft =  $nminutes . $phrase['_m_shortform'] . ', ' . $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft =  $nseconds . $phrase['_s_shortform'];
                                                }
                                        }
                                }
                          return $timeleft;
}



		}



//chadru works on bug id 1256
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
