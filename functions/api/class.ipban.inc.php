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
* IP address banning class
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ipban
{
        /**
        * Constructor determines if the visitor's IP address is within the blacklist, if so, it bans that IP/visitor.
        *
        */
        function ipban()
        {
                global $ilance, $ilcrumbs, $navcrumb, $phrase, $ilconfig, $ilpage, $show;
                
                if (!empty($ilconfig['globalfilters_blockips']))
                {
                        $ip_blacklist = $ilconfig['globalfilters_blockips'];
                        $ips = explode(',', $ip_blacklist);
                        
                        $ip_blacklist = array();
                        foreach ($ips AS $ipaddress)
                        {
                                $ip_blacklist[] = trim($ipaddress);
                        }
                        
                        if (in_array($_SERVER['REMOTE_ADDR'], $ip_blacklist))
                        {
                                $area_title = $phrase['_you_have_been_banned_from_the_marketplace'];
                                $page_title = $ilconfig['template_metatitle'] . ' | ' . SITE_NAME;
                                
                                $navcrumb = array("$ilpage[main]" => $phrase['_you_have_been_banned_from_the_marketplace']);
                                print_notice($phrase['_you_have_been_banned_from_the_marketplace'], $phrase['_you_have_been_banned_from_the_marketplace'] . '<br /><br />', $ilpage['main'] . '?cmd=contact&amp;subcmd=banned', $phrase['_contact_customer_support']);
                                exit();
                        }
                }
                
                ($apihook = $ilance->api('ipban_constructor_start')) ? eval($apihook) : false;
                
                if (defined('SUPRESS_MAINTENANCE_MODE') AND SUPRESS_MAINTENANCE_MODE)
                {
                        return;
                }
                
                global $phrase, $ilconfig, $maintenancemessage, $ilpage, $ilance, $phrase;
                
                $maintenancemessage = stripslashes($ilconfig['maintenance_message']);
                $excludeips = $ilconfig['maintenance_excludeips'];
                $ips = mb_split(', ', $excludeips);
                
                $excludeips = array();
                foreach ($ips AS $ipaddress)
                {
                        $excludeips[] = $ipaddress;
                }
                
                $excludeurls = $ilconfig['maintenance_excludeurls'];
                $urls = mb_split(', ', $excludeurls);
                
                $excludeurls = array();
                foreach ($urls AS $pagename)
                {
                        $excludeurls[] = $pagename;
                }
                
                if (defined('LOCATION') AND (LOCATION == 'admin' OR LOCATION == 'login'))
                {
                        // we are in the admin so do not run maintenance mode
                        // this will allow the admin to login and create a session to
                        // be able to visit the client side without seeing maintenance mode
                }
                else
                {
                        if ($ilconfig['maintenance_mode'] AND (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0'))
                        {
                                // maintenance mode is enabled and there is no admin active so do maintennce mode routine
                                if (!in_array($_SERVER['REMOTE_ADDR'], $excludeips))
                                {
                                        if (!in_array($_SERVER['PHP_SELF'], $excludeurls))
                                        {
                                                $area_title = $phrase['_maintenance_mode_temporarily_unavailable'];
                                                $page_title = $ilconfig['template_metatitle'] . ' | ' . SITE_NAME;
                                                
                                                $navcrumb = array("$ilpage[main]" => $phrase['_maintenance_mode_temporarily_unavailable']);			
                                                print_notice($phrase['_maintenance_mode'], $maintenancemessage, $ilpage['main'], $phrase['_main_menu']);
                                                exit();
                                        }
                                }
                        }
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>