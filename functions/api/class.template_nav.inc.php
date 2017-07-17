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

if (!class_exists('template'))
{
	exit;
}

/**
* Template navigation class responsible for building and constructing the xml navigational menus in iLance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class template_nav extends template
{
        /*
        * Function to build and construct the xml navigational menus
        *
        * @param      string      short language identifier (default eng)
        * @param      string      xml nav type (client or admin)
        * @param      string      (custom)
        *
        * @return      
        */
        function construct_nav($slng = 'eng', $navtype = 'client', $customnav = '')
        {
                if (defined('LOCATION') AND LOCATION == 'admin')
                {
                        return;       
                }
                
                global $ilance, $myapi, $phrase, $iltemplate, $ilconfig, $ilpage, $show, $topnavlink;
		
		$ilance->subscription = construct_object('api.subscription');
            
                if (empty($customnav))
                {
                        if ($navtype == 'client')
                        {
				$xml_file = DIR_XML . 'client_leftnav.xml';
                        }
                        else if ($navtype == 'client_topnav')
                        {
                                $xml_file = DIR_XML . 'client_topnav.xml';
                        }
                }
                else 
                {
                        $xml_file = DIR_XML . $customnav . '.xml';	
                }
                
                $xml = file_get_contents($xml_file);
                
                ($apihook = $ilance->api('construct_nav_xml_start')) ? eval($apihook) : false;
                
                // #### process our template hooks #############################
                $xml = $this->handle_template_hooks($navtype, $xml);                
                
                $xml_encoding = '';
                
                if (function_exists('mb_detect_encoding'))
                {
                        $xml_encoding = mb_detect_encoding($xml);
                }
                
                if ($xml_encoding == 'ASCII')
                {
                        $xml_encoding = '';
                }
    
                $data = array();
                
                $parser = xml_parser_create($xml_encoding);
                xml_parse_into_struct($parser, $xml, $data);
                $error_code = xml_get_error_code($parser);                
                xml_parser_free($parser);
                
                if ($error_code == 0)
                {
                        // #### CLIENT LEFT NAV MENU ###########################
                        if ($navtype == 'client')
                        {
                                $result = $this->process_cpnav_xml($data, $xml_encoding, 'CLIENT');
                                $navarray = $result['navarray'];
                                
                                $css = $html = '';
                                $navcount = count($navarray);
				$navcount2 = count($result['navoptions']);
				$totalnavitems = ($navcount + $navcount2);
				
                                for ($i = 0; $i < $navcount; $i++)
                                {
                                        $rolepassed["$i"] = 1;
                                        $subscriptionpassed["$i"] = 1;
					
                                        // nav header
                                        if ($navarray[$i][7] == true)
                                        {
                                                // role permission checkup
                                                // we will default to all roles enabled unless a role is defined
                                                // in this case we'll use the rules of the defined role ids.
                                                if (!empty($navarray["$i"][9]))
                                                {
                                                        // roles are being defined for this nav header group
                                                        // do we have multiple roles to checkup?
                                                        if (strchr($navarray["$i"][9], ','))
                                                        {
                                                                // multiple roles detected
                                                                $roles = explode(',', $navarray["$i"][9]);
                                                                if (!empty($_SESSION['ilancedata']['user']['roleid']) AND in_array($_SESSION['ilancedata']['user']['roleid'], $roles))
                                                                {
                                                                        $rolepassed["$i"] = 1;
                                                                }
                                                                else
                                                                {
                                                                        $rolepassed["$i"] = 0;
									$totalnavitems--;
                                                                }
                                                        }
                                                        else
                                                        {
                                                                // single role detected
                                                                if (!empty($_SESSION['ilancedata']['user']['roleid']) AND $_SESSION['ilancedata']['user']['roleid'] == intval($navarray[$i][9]))
                                                                {
                                                                        $rolepassed["$i"] = 1;
                                                                }
                                                                else
                                                                {
                                                                        $rolepassed["$i"] = 0;
									$totalnavitems--;
                                                                }
                                                        }
                                                }
                                                
						// subscription permission checkup
						if (!empty($navarray["$i"][11]))
						{
							if (empty($_SESSION['ilancedata']['user']['userid']) OR empty($_SESSION['ilancedata']['user']['active']) OR !empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no' OR !empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $navarray["$i"][11]) == 'no')
							{
								$subscriptionpassed["$i"] = 0;
								$totalnavitems--;
							}
						}
						
                                                // check for permissions
                                                // config="" / permission1=""
                                                if (!empty($navarray["$i"][4]) AND !empty($navarray["$i"][5]))
                                                {
                                                        // check permissions
                                                        if (!empty($ilance->$navarray["$i"][4][$navarray["$i"][5]]) AND $ilance->$navarray["$i"][4][$navarray["$i"][5]])
                                                        {
                                                                // did the role pass?
                                                                if (!empty($subscriptionpassed["$i"]) AND $subscriptionpassed["$i"] AND !empty($rolepassed["$i"]) AND $rolepassed["$i"])
                                                                {
                                                                        $topclass = 'block-content-yellow';
                                                                        $style = 'padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                        $navid = $navarray["$i"][8];
                                                                        $link1 = $navarray["$i"][3];
                                                                        $link2 = $navarray["$i"][2];
                                                                        $title = $phrase["{$navarray[$i][1]}"];
                                                                        
                                                                        if (!empty($link1))
                                                                        {
                                                                                $link = $link1;
                                                                        }
                                                                        else if (!empty($link2))
                                                                        {
                                                                                $link = $link2;
                                                                        }
                                                                        else
                                                                        {
                                                                                $link = '';
                                                                        }
                                                                        
                                                                        $html1 = $this->fetch_template('leftnav_mycp_navgroup.html');
                                                                        $html1 = $this->parse_hash('leftnav_mycp_navgroup.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navgroup.html', $html1, $addslashes = true);
                                                                        
                                                                        $html1 = stripslashes($html1);
                                                                        $html1 = addslashes($html1);
                                                                        
                                                                        eval('$html .= "' . $html1 . '";');
                                                                
                                                                        $html = stripslashes($html);
                                                                        unset($html1);
                                                                }
                                                        }
                                                }
                                                else
                                                {
                                                        // did the role pass?
                                                        if (!empty($subscriptionpassed["$i"]) AND $subscriptionpassed["$i"] AND !empty($rolepassed["$i"]) AND $rolepassed["$i"])
                                                        {
                                                                $topclass = 'block-content-yellow';
                                                                $style = 'padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                $navid = $navarray["$i"][8];
                                                                $link1 = $navarray["$i"][3];
                                                                $link2 = $navarray["$i"][2];
                                                                $title = $phrase["{$navarray[$i][1]}"];
                                                                
                                                                if (!empty($link1))
                                                                {
                                                                        $link = $link1;
                                                                }
                                                                else if (!empty($link2))
                                                                {
                                                                        $link = $link2;
                                                                }
                                                                else
                                                                {
                                                                        $link = '';
                                                                }
                                                                
                                                                $html1 = $this->fetch_template('leftnav_mycp_navgroup.html');
                                                                $html1 = $this->parse_hash('leftnav_mycp_navgroup.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                $html1 = $this->parse_if_blocks('leftnav_mycp_navgroup.html', $html1, $addslashes = true);
                                                                
                                                                $html1 = stripslashes($html1);
                                                                $html1 = addslashes($html1);
                                                                
                                                                //$html1 = str_replace('$', '\$', $html1);
                                                                
                                                                eval('$html .= "' . $html1 . '";');
                                                        
                                                                $html = stripslashes($html);
                                                                unset($html1);
                                                        }
                                                }                                                
                                        }
        
                                        // leftnav_mycp_navwrapper_start.html
                                        $navid = $navarray["$i"][8];
                                        
                                        $html1 = $this->fetch_template('leftnav_mycp_navwrapper_start.html');
                                        $html1 = $this->parse_hash('leftnav_mycp_navwrapper_start.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navwrapper_start.html', $html1, $addslashes = true);
                                        
                                        $html1 = stripslashes($html1);
                                        $html1 = addslashes($html1);
                                        
                                        eval('$html .= "' . $html1 . '";');
                                
                                        $html = stripslashes($html);
                                        unset($html1);
                                        
                                        
                                        for ($x = 0; $x < $navcount2; $x++)
                                        {
                                                $rolepassed["$x"] = 1;
						$subscriptionpassed["$x"] = 1;
						
                                                if ($navarray["$i"][0] == $result['navoptions']["$x"][0])
                                                {
                                                        ($apihook = $ilance->api('construct_nav_roles_start')) ? eval($apihook) : false;  

                                                        // role permission checkup
                                                        // we will default to all roles enabled unless a role is defined
                                                        // in this case we'll use the rules of the defined role ids.
                                                        if (!empty($result['navoptions']["$x"][8]))
                                                        {
                                                                // roles are being defined for this nav header group
                                                                // do we have multiple roles to checkup?
                                                                if (strchr($result['navoptions']["$x"][8], ','))
                                                                {
                                                                        // multiple roles detected
                                                                        $roles = explode(',', $result['navoptions']["$x"][8]);
                                                                        if (!empty($_SESSION['ilancedata']['user']['roleid']) AND in_array($_SESSION['ilancedata']['user']['roleid'], $roles))
                                                                        {
                                                                                $rolepassed["$x"] = 1;
                                                                        }
                                                                        else
                                                                        {
                                                                                $rolepassed["$x"] = 0;
										$totalnavitems--;
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        // single role detected
                                                                        if (!empty($_SESSION['ilancedata']['user']['roleid']) AND $_SESSION['ilancedata']['user']['roleid'] == intval($result['navoptions']["$x"][8]))
                                                                        {
                                                                                $rolepassed["$x"] = 1;
                                                                        }
                                                                        else
                                                                        {
                                                                                $rolepassed["$x"] = 0;
										$totalnavitems--;
                                                                        }
                                                                }
                                                        }
							
							// subscription permission checkup
                                                        if (!empty($result['navoptions']["$x"][10]))
                                                        {
								if (empty($_SESSION['ilancedata']['user']['userid']) OR empty($_SESSION['ilancedata']['user']['active']) OR !empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no' OR !empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $result['navoptions']["$x"][10]) == 'no')
								{
									$subscriptionpassed["$x"] = 0;
									$totalnavitems--;
								}
                                                        }
                                                        
                                                        // check access permissions
                                                        if (empty($result['navoptions']["$x"][3]))
                                                        {
                                                                // not using object style config
                                                                // does permission 1 exist?
                                                                $opt1 = $result['navoptions']["$x"][4];
                                                                $opt2 = $result['navoptions']["$x"][5];
                                                                
                                                                if (empty($opt1) AND empty($opt2))
                                                                {
                                                                        // no permissions assigned
                                                                        // did the role pass?
                                                                        if (!empty($subscriptionpassed["$x"]) AND $subscriptionpassed["$x"] AND !empty($rolepassed["$x"]) AND $rolepassed["$x"])
                                                                        {
                                                                                $url = HTTP_SERVER . (empty($result['navoptions']["$x"][2]) ? $result['navoptions']["$x"][1] : $result['navoptions']["$x"][2]);
                                                                                $class = 'alt1 gray';
                                                                                $style = 'padding:6px; padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                                $title = $phrase[$result['navoptions']["$x"][7]];
                                                                                if (!empty($result['navoptions']["$x"][9]))
                                                                                {
                                                                                        if (strchr($result['navoptions']["$x"][9], ','))
                                                                                        {
                                                                                                $definestatements = explode(',', $result['navoptions']["$x"][9]);
                                                                                                if (!empty($topnavlink) AND in_array($topnavlink[0], $definestatements))
                                                                                                {
                                                                                                        $class = 'alt1 black';
                                                                                                        $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px';
                                                                                                }
                                                                                        }
                                                                                        else if (!empty($topnavlink) AND in_array($result['navoptions']["$x"][9], $topnavlink))
                                                                                        {
                                                                                                $class = 'alt1 black';
                                                                                                $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px; border-bottom:#ddd solid 1px; background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedoptionbg.png); background-position: top; color:#000;';
                                                                                        }
                                                                                }
										
                                                                                $html1 = $this->fetch_template('leftnav_mycp_navoption.html');
                                                                                $html1 = $this->parse_hash('leftnav_mycp_navoption.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                                $html1 = $this->parse_if_blocks('leftnav_mycp_navoption.html', $html1, $addslashes = true);
                                                                                
                                                                                $html1 = stripslashes($html1);
                                                                                $html1 = addslashes($html1);
                                                                                
                                                                                eval('$html .= "' . $html1 . '";');
                                                                        
                                                                                $html = stripslashes($html);
                                                                                unset($html1);
                                                                        }
                                                                }
                                                                
                                                                if (!empty($opt1) AND !empty($opt2))
                                                                {
                                                                        // permission 1 and 2 both active
                                                                        if (!empty($ilconfig["$opt1"]) AND $ilconfig["$opt1"] AND !empty($ilconfig["$opt2"]) AND $ilconfig["$opt2"])
                                                                        {
                                                                                // did the role pass?
                                                                                if (!empty($subscriptionpassed["$x"]) AND $subscriptionpassed["$x"] AND !empty($rolepassed["$x"]) AND $rolepassed["$x"])
                                                                                {
                                                                                        $url = HTTP_SERVER . (empty($result['navoptions']["$x"][2]) ? $result['navoptions']["$x"][1] : $result['navoptions']["$x"][2]);
                                                                                        $class = 'alt1 gray';
                                                                                        $style = 'padding:6px; padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                                        $title = $phrase[$result['navoptions']["$x"][7]];
                                                                                        if (!empty($result['navoptions']["$x"][9]))
                                                                                        {
                                                                                                if (strchr($result['navoptions']["$x"][9], ','))
                                                                                                {
                                                                                                        $definestatements = explode(',', $result['navoptions']["$x"][9]);
                                                                                                        if (!empty($topnavlink) AND in_array($topnavlink[0], $definestatements))
                                                                                                        {
                                                                                                                $class = 'alt1 black';
                                                                                                                $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px';
                                                                                                        }
                                                                                                }
                                                                                                else if (!empty($topnavlink) AND in_array($result['navoptions']["$x"][9], $topnavlink))
                                                                                                {
                                                                                                        $class = 'alt1 black';
                                                                                                        $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px; border-bottom:#ddd solid 1px; background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedoptionbg.png); background-position: top; color:#000;';
                                                                                                }
                                                                                        }
											
                                                                                        $html1 = $this->fetch_template('leftnav_mycp_navoption.html');
                                                                                        $html1 = $this->parse_hash('leftnav_mycp_navoption.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navoption.html', $html1, $addslashes = true);
                                                                                        
                                                                                        $html1 = stripslashes($html1);
                                                                                        $html1 = addslashes($html1);
                                                                                        
                                                                                        eval('$html .= "' . $html1 . '";');
                                                                                
                                                                                        $html = stripslashes($html);
                                                                                        unset($html1);
                                                                                }
                                                                        }	
                                                                }
                                                                
                                                                if (!empty($opt1) AND empty($opt2))
                                                                {
                                                                        // permission 1 active, permission 2 is not
                                                                        if (!empty($ilconfig["$opt1"]) AND $ilconfig["$opt1"])
                                                                        {
                                                                                // did the role pass?
                                                                                if (!empty($subscriptionpassed["$x"]) AND $subscriptionpassed["$x"] AND !empty($rolepassed["$x"]) AND $rolepassed["$x"])
                                                                                {
                                                                                        $url = HTTP_SERVER . (empty($result['navoptions']["$x"][2]) ? $result['navoptions']["$x"][1] : $result['navoptions']["$x"][2]);
                                                                                        $class = 'alt1 gray';
                                                                                        $style = 'padding:6px; padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                                        $title = $phrase[$result['navoptions']["$x"][7]];
                                                                                        if (!empty($result['navoptions']["$x"][9]))
                                                                                        {
                                                                                                if (strchr($result['navoptions']["$x"][9], ','))
                                                                                                {
                                                                                                        $definestatements = explode(',', $result['navoptions']["$x"][9]);
                                                                                                        if (!empty($topnavlink) AND in_array($topnavlink[0], $definestatements))
                                                                                                        {
                                                                                                                $class = 'alt1 black';
                                                                                                                $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px';
                                                                                                        }
                                                                                                }
                                                                                                else if (!empty($topnavlink) AND in_array($result['navoptions']["$x"][9], $topnavlink))
                                                                                                {
                                                                                                        $class = 'alt1 black';
                                                                                                        $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px; border-bottom:#ddd solid 1px; background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedoptionbg.png); background-position: top; color:#000;';
                                                                                                }
                                                                                        }
                                                                                        
                                                                                        $html1 = $this->fetch_template('leftnav_mycp_navoption.html');
                                                                                        $html1 = $this->parse_hash('leftnav_mycp_navoption.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navoption.html', $html1, $addslashes = true);
                                                                                        
                                                                                        $html1 = stripslashes($html1);
                                                                                        $html1 = addslashes($html1);
                                                                                        
                                                                                        eval('$html .= "' . $html1 . '";');
                                                                                
                                                                                        $html = stripslashes($html);
                                                                                        unset($html1);
                                                                                }
                                                                        }
                                                                }
                                                                
                                                                if (empty($opt1) AND !empty($opt2))
                                                                {
                                                                        // permission 1 is not active, permission 2 is
                                                                        if (!empty($ilconfig["$opt2"]) AND $ilconfig["$opt2"])
                                                                        {
                                                                                // did the role pass?
                                                                                if (!empty($subscriptionpassed["$x"]) AND $subscriptionpassed["$x"] AND !empty($rolepassed["$x"]) AND $rolepassed["$x"])
                                                                                {
                                                                                        $url = HTTP_SERVER . (empty($result['navoptions']["$x"][2]) ? $result['navoptions']["$x"][1] : $result['navoptions']["$x"][2]);
                                                                                        $class = 'alt1 gray';
                                                                                        $style = 'padding:6px; padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                                        $title = $phrase[$result['navoptions']["$x"][7]];
                                                                                        if (!empty($result['navoptions']["$x"][9]))
                                                                                        {
                                                                                                if (strchr($result['navoptions']["$x"][9], ','))
                                                                                                {
                                                                                                        $definestatements = explode(',', $result['navoptions']["$x"][9]);
                                                                                                        if (!empty($topnavlink) AND in_array($topnavlink[0], $definestatements))
                                                                                                        {
                                                                                                                $class = 'alt1 black';
                                                                                                                $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px';
                                                                                                        }
                                                                                                }
                                                                                                else if (!empty($topnavlink) AND in_array($result['navoptions']["$x"][9], $topnavlink))
                                                                                                {
                                                                                                        $class = 'alt1 black';
                                                                                                        $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px; border-bottom:#ddd solid 1px; background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedoptionbg.png); background-position: top; color:#000;';
                                                                                                }
                                                                                        }
                                                                                        
                                                                                        $html1 = $this->fetch_template('leftnav_mycp_navoption.html');
                                                                                        $html1 = $this->parse_hash('leftnav_mycp_navoption.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navoption.html', $html1, $addslashes = true);
                                                                                        
                                                                                        $html1 = stripslashes($html1);
                                                                                        $html1 = addslashes($html1);
                                                                                        
                                                                                        eval('$html .= "' . $html1 . '";');
                                                                                
                                                                                        $html = stripslashes($html);
                                                                                        unset($html1);
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        else 
                                                        {
                                                                // we are using object configuration referencing (lanceads, wantads, stores, etc)
                                                                // do we have both elements?
                                                                $opt1 = $result['navoptions']["$x"][3];
                                                                $opt2 = $result['navoptions']["$x"][4];
                                                                if (!empty($opt1) AND !empty($opt2))
                                                                {
                                                                        $continue = false;
                                                                        $pieces = explode('->', $opt1);
                                                                        $continue = $ilance->{$pieces[0]}->{$pieces[1]}["$opt2"];
                                                                        if ($continue)
                                                                        {
                                                                                // did the role pass?
                                                                                if (!empty($subscriptionpassed["$x"]) AND $subscriptionpassed["$x"] AND !empty($rolepassed["$x"]) AND $rolepassed["$x"])
                                                                                {
                                                                                        $url = HTTP_SERVER . (empty($result['navoptions']["$x"][2]) ? $result['navoptions']["$x"][1] : $result['navoptions']["$x"][2]);
                                                                                        $class = 'alt1 gray';
                                                                                        $style = 'padding:6px; padding-top:9px; padding-bottom:9px; border-right:#ddd solid 1px; border-left:#ddd solid 1px';
                                                                                        $title = $phrase[$result['navoptions']["$x"][7]];
                                                                                        if (!empty($result['navoptions']["$x"][9]))
                                                                                        {
                                                                                                if (strchr($result['navoptions']["$x"][9], ','))
                                                                                                {
                                                                                                        $definestatements = explode(',', $result['navoptions']["$x"][9]);
                                                                                                        if (!empty($topnavlink) AND in_array($topnavlink[0], $definestatements))
                                                                                                        {
                                                                                                                $class = 'alt1 black';
                                                                                                                $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px';
                                                                                                        }
                                                                                                }
                                                                                                else if (!empty($topnavlink) AND in_array($result['navoptions']["$x"][9], $topnavlink))
                                                                                                {
                                                                                                        $class = 'alt1 black';
                                                                                                        $style = 'font-weight: bold; padding:6px; padding-top:9px; padding-bottom:9px; border-right:#fff solid 1px; border-left:#ddd solid 1px; border-bottom:#ddd solid 1px; background-image: url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedoptionbg.png); background-position: top; color:#000;';
                                                                                                }
                                                                                        }
                                                                                        
                                                                                        $html1 = $this->fetch_template('leftnav_mycp_navoption.html');
                                                                                        $html1 = $this->parse_hash('leftnav_mycp_navoption.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navoption.html', $html1, $addslashes = true);
                                                                                        
                                                                                        $html1 = stripslashes($html1);
                                                                                        $html1 = addslashes($html1);
                                                                                        
                                                                                        eval('$html .= "' . $html1 . '";');
                                                                                
                                                                                        $html = stripslashes($html);
                                                                                        unset($html1);
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                        
                                        // leftnav_mycp_navwrapper_end.html
                                        $html1 = $this->fetch_template('leftnav_mycp_navwrapper_end.html');
                                        $html1 = $this->parse_hash('leftnav_mycp_navwrapper_end.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                        $html1 = $this->parse_if_blocks('leftnav_mycp_navwrapper_end.html', $html1, $addslashes = true);
                                        
                                        $html1 = stripslashes($html1);
                                        $html1 = addslashes($html1);
                                        
                                        eval('$html .= "' . $html1 . '";');
                                
                                        $html = stripslashes($html);
                                        unset($html1);
                                }

                                $navresult = $html;
                                
                                $html = '';
                                $html = $this->fetch_template('leftnav_mycp.html');
                                $html = $this->parse_hash('leftnav_mycp.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                                $html = $this->parse_if_blocks('leftnav_mycp.html', $html, $addslashes = true);
                                
                                eval('$html = "' . $html . '";');
                                
                                // fix for javascript issues like this toggle_tr(\'xxx\') this will make it toggle_tr('xxx') ...
                                $html = stripslashes($html);
                        }
                        
                        // #### CLIENT TOP NAV MENU ############################
                        else if ($navtype == 'client_topnav')
                        {
                                $result = $this->process_cpnav_xml($data, $xml_encoding, 'CLIENT_TOPNAV');
                                
                                $html = '<ul>';
                                
                                $navcount = count($result['navoptions']);
                                for ($x = 0; $x < $navcount; $x++)
                                {
                                        // #### $show['xxx'] ###################
                                        if (!empty($result['navoptions']["$x"][6]))
                                        {
                                                // do we have multiple $show statements to compare against?
                                                if (strchr($result['navoptions']["$x"][6], ','))
                                                {
                                                        // multiple $show statements detected
                                                        
                                                        $showstatements = explode(',', $result['navoptions']["$x"][6]);                                                        
                                                        foreach ($showstatements AS $statement)
                                                        {
                                                                if (isset($show["$statement"]) AND $show["$statement"])
                                                                {
                                                                        $showpassed["$x"] = true;        
                                                                }
                                                                else
                                                                {
                                                                        $showpassed["$x"] = false;
                                                                        break;
                                                                }
                                                        }
                                                }
                                                else
                                                {
                                                        // single show detected
                                                        if (isset($show[$result['navoptions']["$x"][6]]) AND $show[$result['navoptions']["$x"][6]])
                                                        {
                                                                $showpassed["$x"] = true;  
                                                        }
                                                        else
                                                        {
                                                                $showpassed["$x"] = false;
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                $showpassed["$x"] = true;
                                        }
                                        
                                        // #### $ilconfig['xxx'] ###############
                                        if (!empty($result['navoptions']["$x"][7]))
                                        {
                                                // do we have multiple $ilconfig statements to compare against?
                                                if (strchr($result['navoptions']["$x"][7], ','))
                                                {
                                                        // multiple $ilconfig statements detected
                                                        
                                                        $ilconfigstatements = explode(',', $result['navoptions']["$x"][7]);                                                        
                                                        foreach ($ilconfigstatements AS $statement)
                                                        {
                                                                if (isset($ilconfig["$statement"]) AND $ilconfig["$statement"])
                                                                {
                                                                        $permissionspassed["$x"] = true;        
                                                                }
                                                                else
                                                                {
                                                                        $permissionspassed["$x"] = false;
                                                                        break;
                                                                }
                                                        }
                                                }
                                                else
                                                {
                                                        // single permission detected
                                                        if (isset($ilconfig[$result['navoptions']["$x"][7]]) AND $ilconfig[$result['navoptions']["$x"][7]])
                                                        {
                                                                $permissionspassed["$x"] = true;  
                                                        }
                                                        else
                                                        {
                                                                $permissionspassed["$x"] = false;
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                $permissionspassed["$x"] = true;  
                                        }
                                        
                                        // #### GUESTS CAN VIEW? ###############
                                        if ($result['navoptions']["$x"][3] == 'true')
                                        {
                                                $guestscanview["$x"] = true;
                                        }
                                        else
                                        {
                                                $guestscanview["$x"] = false;
                                        }
                                        
                                        // #### MEMBERS CAN VIEW? ##############
                                        if ($result['navoptions']["$x"][4] == 'true')
                                        {
                                                $memberscanview["$x"] = true;
                                        }
                                        else
                                        {
                                                $memberscanview["$x"] = false;
                                        }
                                        
                                        // #### ADMINS CAN VIEW? ###############
                                        if ($result['navoptions']["$x"][5] == 'true')
                                        {
                                                $adminscanview["$x"] = true;
                                        }
                                        else
                                        {
                                                $adminscanview["$x"] = false;
                                        }
                                        
                                        // #### WHO ARE WE? ####################
                                        $isguest = $ismember = $isadmin = false;
                                        
                                        if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
                                        {
                                                $isguest = true;
                                        }
                                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
                                        {
                                                $ismember = true;
                                        }
                                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                                        {
                                                $isadmin = true;
                                        }
                                        
                                        // #### LOCATIONS ######################
                                        if (!empty($result['navoptions']["$x"][8]))
                                        {
                                                if (strchr($result['navoptions']["$x"][8], ','))
                                                {
                                                        $definestatements = explode(',', $result['navoptions']["$x"][8]);
                                                        if (!empty($topnavlink) AND in_array($topnavlink[0], $definestatements))
                                                        {
                                                                // do we pass global permissions?
                                                                if ($showpassed["$x"] AND $permissionspassed["$x"])
                                                                {
                                                                        if ($isguest)
                                                                        {
                                                                                // we are a guest viewing this link
                                                                                if ($guestscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = 'current';
											if ($x == 0)
											{
												$cls = 'current';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                                
                                                                        }
                                                                        else if ($ismember)
                                                                        {
                                                                                // we are a member viewing this link
                                                                                if ($memberscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = 'current';
											if ($x == 0)
											{
												$cls = 'current';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                        else if ($isadmin)
                                                                        {
                                                                                // we are an admin viewing this link
                                                                                if ($adminscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = 'current';
											if ($x == 0)
											{
												$cls = 'current';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }                                                                                                
                                                                }
                                                        }
                                                        else
                                                        {
                                                                // do we pass global permissions?
                                                                if ($showpassed["$x"] AND $permissionspassed["$x"])
                                                                {
                                                                        if ($isguest)
                                                                        {
                                                                                // we are a guest viewing this link
                                                                                if ($guestscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = '';
											if ($x == 0)
											{
												$cls = 'first';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                                
                                                                        }
                                                                        else if ($ismember)
                                                                        {
                                                                                // we are a member viewing this link
                                                                                if ($memberscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = '';
											if ($x == 0)
											{
												$cls = 'first';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                        else if ($isadmin)
                                                                        {
                                                                                // we are an admin viewing this link
                                                                                if ($adminscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = '';
											if ($x == 0)
											{
												$cls = 'first';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                }        
                                                        }
                                                }
                                                else
                                                {
                                                        // single LOCATION detected
                                                        if (!empty($topnavlink) AND in_array($result['navoptions']["$x"][8], $topnavlink))
                                                        {                                                                
                                                                // do we pass global permissions?
                                                                if ($showpassed["$x"] AND $permissionspassed["$x"])
                                                                {
                                                                        if ($isguest)
                                                                        {
                                                                                // we are a guest viewing this link
                                                                                if ($guestscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = 'current';
											if ($x == 0)
											{
												$cls = 'current';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                                
                                                                        }
                                                                        else if ($ismember)
                                                                        {
                                                                                // we are a member viewing this link
                                                                                if ($memberscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = 'current';
											if ($x == 0)
											{
												$cls = 'current';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                        else if ($isadmin)
                                                                        {
                                                                                // we are an admin viewing this link
                                                                                if ($adminscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = 'current';
											if ($x == 0)
											{
												$cls = 'current';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        else
                                                        {
                                                                // do we pass global permissions?
                                                                if ($showpassed["$x"] AND $permissionspassed["$x"])
                                                                {
                                                                        if ($isguest)
                                                                        {
                                                                                // we are a guest viewing this link
                                                                                if ($guestscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = '';
											if ($x == 0)
											{
												$cls = 'first';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                                
                                                                        }
                                                                        else if ($ismember)
                                                                        {
                                                                                // we are a member viewing this link
                                                                                if ($memberscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = '';
											if ($x == 0)
											{
												$cls = 'first';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                        else if ($isadmin)
                                                                        {
                                                                                // we are an admin viewing this link
                                                                                if ($adminscanview["$x"])
                                                                                {
                                                                                        $url = ($ilconfig['globalauctionsettings_seourls'] AND !empty($result['navoptions']["$x"][2])) ? $result['navoptions']["$x"][2] : $result['navoptions']["$x"][1];
                                                                                        $urlextra = isset($result['navoptions']["$x"][9]) ? $result['navoptions']["$x"][9] : '';
											$cls = '';
											if ($x == 0)
											{
												$cls = 'first';
											}
                                                                                        $html .= '<li class="' . $cls . '"><a href="' . HTTP_SERVER . $url . '" ' . $urlextra . '>' . $phrase[$result['navoptions']["$x"][0]] . '</a></li>';
                                                                                }
                                                                        }
                                                                }        
                                                        }
                                                }
                                        }
                                }
                                
                                $html .= '</ul>';
                        }
                }
                else
                {
                        die("<strong>Fatal:</strong> There is an error with the formatting of the top client navigation xml file [" . xml_error_string($error_code) . "].  Please fix the problem and retry your action.");
                }
                
                return $html;
	}
	
	
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>