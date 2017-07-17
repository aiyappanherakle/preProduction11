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
* Styles class to perform the majority of skinning and template functions in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class styles
{
        /**
        * This will store the compiled css stylesheet
        */
        var $computed_style = null;
        
        /**
        * CSS cache so we don't have to continously call the db in other scripts
        */
        var $css_cache = null;
    
        /*
        * Constructor
        */
        function styles()
        {
                global $ilance, $phrase, $myapi, $templatevars, $breadcrumbtrail, $breadcrumbfinal, $navcrumb, $iltemplate, $headinclude, $breadcrumb, $onload, $official_time, $v3left_nav, $v3left_storenav, $ilconfig, $ilpage, $resstyles, $jsinclude;
                
                if (empty($_SESSION['ilancedata']['user']['styleid']))
                {
                        $_SESSION['ilancedata']['user']['styleid'] = $ilconfig['defaultstyle'];
                }
                
                $stylevalid = $this->is_styleid_valid($_SESSION['ilancedata']['user']['styleid']);
                if (!$stylevalid)
                {
                        $_SESSION['ilancedata']['user']['styleid'] = $ilconfig['defaultstyle'];
                }
                
                $this->computed_style = '';
                
            
                        $sql = $ilance->db->query("
                                SELECT name, content, type
                                FROM " . DB_PREFIX . "templates
                                WHERE styleid = '2'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $templates = array();                                
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        switch ($res['type'])
                                        {
                                                case 'variable':
                                                {
                                                        $templatevars[$res['name']] = stripslashes($res['content']);
                                                        break;
                                                }                                            
                                                case 'cssclient':
                                                {
                                                        $templatecss['cssclient'][$res['name']] = unserialize($res['content']);
                                                        if ($templatecss['cssclient'][$res['name']]['EXTRA'] != '')
                                                        {
                                                                $templatecss['cssclient'][$res['name']]['EXTRA'] = base64_decode($templatecss['cssclient'][$res['name']]['EXTRA']);
                                                        }
                                                        break;
                                                }                                            
                                                case 'cssadmin':
                                                {
                                                        $templatecss['cssadmin'][$res['name']] = unserialize($res['content']);
                                                        if ($templatecss['cssadmin'][$res['name']]['EXTRA'] != '')
                                                        {
                                                                $templatecss['cssadmin'][$res['name']]['EXTRA'] = base64_decode($templatecss['cssadmin'][$res['name']]['EXTRA']);
                                                        }
                                                        break;
                                                }                                            
                                                case 'csswysiwyg':
                                                {
                                                        $templatecss['csswysiwyg'][$res['name']] = unserialize($res['content']);
                                                        if ($templatecss['csswysiwyg'][$res['name']]['EXTRA'] != '')
                                                        {
                                                                $templatecss['csswysiwyg'][$res['name']]['EXTRA'] = base64_decode($templatecss['csswysiwyg'][$res['name']]['EXTRA']);
                                                        }
                                                        break;
                                                }                                            
                                                case 'csstabs':
                                                {
                                                        $templatecss['csstabs'][$res['name']] = unserialize($res['content']);
                                                        if ($templatecss['csstabs'][$res['name']]['EXTRA'] != '')
                                                        {
                                                                $templatecss['csstabs'][$res['name']]['EXTRA'] = base64_decode($templatecss['csstabs'][$res['name']]['EXTRA']);
                                                        }
                                                        break;
                                                }                                            
                                                case 'csscommon':
                                                {
                                                        $templatecss['csscommon'][$res['name']] = unserialize($res['content']);
                                                        if ($templatecss['csscommon'][$res['name']]['EXTRA'] != '')
                                                        {
                                                                $templatecss['csscommon'][$res['name']]['EXTRA'] = base64_decode($templatecss['csscommon'][$res['name']]['EXTRA']);
                                                        }
                                                        break;
                                                }
                                        }                                        
                                }
                                unset($res);
                        }
                 
                // #### MIGRATE TEMPLATE VARS INTO $ilconfig ###################
                // if we do this, we can easily access $ilconfig['template_xxx'] vars easily when we need to
                if (is_array($templatevars))
                {
                        $ilconfig = array_merge($ilconfig, $templatevars);
                }
            
                // #### REPLACE IMPORTANT CSS STYLE VARIABLES
                // recursively scan all array elements and replace important values like:
                // {template_relativeimagepath} => HTTPS_SERVER, {http_server} => HTTP_SERVER, {https_server} => HTTPS_SERVER, etc                
                // additionally, process all template vars so our template system can make use of them like {template_xxx}                
                $tdata = $this->handle_css_style_replacements($templatecss, $templatevars);
                
                // cache css formatted data for quick access from other scripts
                $this->css_cache = $tdata['inline'];
                
                $css_style['filename'] = '';
                
                if (defined('LOCATION') AND LOCATION == 'admin')
                {
                        // #### MAIN ADMINCP CSS ###############################
                        $this->computed_style .= $tdata['inline']['cssadmin'] . LINEBREAK;
                        
                        $css_style['filename'] = 'css_style_' . intval($_SESSION['ilancedata']['user']['styleid']) . '_admincp.css';
                        $css_style['filepath'] = DIR_TMP . $css_style['filename'];
                        $css_style['url'] = $ilconfig['template_relativeimagepath'] . DIR_TMP_NAME . '/' . $css_style['filename'];
                }
                else
                {
                        // #### MAIN CLIENTCP CSS ##############################
                        $this->computed_style .= $tdata['inline']['cssclient'] . LINEBREAK;
                        
                        $css_style['filename'] = 'css_trial_style_' . intval($_SESSION['ilancedata']['user']['styleid']) . '_client.css';
                        $css_style['filepath'] = DIR_TMP . $css_style['filename'];
                        $css_style['url'] = $ilconfig['template_relativeimagepath'] . DIR_TMP_NAME . '/' . $css_style['filename'];
                }
                
                // #### CSS TABS ###############################################
                $this->computed_style .= $tdata['inline']['csstabs'] . LINEBREAK;
                    
                // #### CSS CUSTOM #############################################
                $this->computed_style .= $tdata['inline']['csscommon'] . LINEBREAK;
                
                ($apihook = $ilance->api('style_compute')) ? eval($apihook) : false;
                
                if ($ilconfig['externalcss'])
                {   
                        // check if we need to rewrite CSS file
                        $write_css = false;
                        if (file_exists($css_style['filepath']))
                        {
                                $lastmod = filemtime($css_style['filepath']);
                                if (($lastmod + $ilconfig['externalcsstimeout'] * 60) < mktime())
                                {
                                        // css is outdated .. but does the admin want to overwrite the existing css cache?
                                        $write_css = $ilconfig['refreshcsscache'];
                                }
                        }
                        else 
                        {
                                // the css file does not exist! we need to generate something!
                                $write_css = true;
                        }
                        
                        if ($write_css)
                        {
                                $css_style['unique_name'] = '';
                                do 
                                {
                                        $css_style['unique_name'] = rand(0, 100000);
                                }
                                while (file_exists(DIR_TMP . $css_style['unique_name']));
                                {                                    
                                        $f = fopen(DIR_TMP . $css_style['unique_name'], 'w');
                                        if ($f === false)
                                        {
                                                @unlink(DIR_TMP . $css_style['unique_name']);
                                        }
                                        else 
                                        {
                                                fwrite($f, $this->computed_style);
                                                fclose($f);
                                                @unlink(DIR_TMP . $css_style['filename']);
                                                @rename(DIR_TMP . $css_style['unique_name'], DIR_TMP . $css_style['filename']);
                                                @unlink(DIR_TMP . $css_style['unique_name']);
                                        }
                                }
                        }
                }
                
                $cachefile['expire'] = '86400';
                
                $headincludebit = '';
                $headinclude .= "\n<!-- START client script -->\n";
                $headinclude .= ($ilconfig['externaljsphrases'])
                        ? "<script language=\"javascript\" type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\" src=\"" . $ilconfig['template_relativeimagepath'] . DIR_TMP_NAME . "/phrases_" . mb_strtolower($_SESSION['ilancedata']['user']['slng']) . ".js\"></script>\n"
                        : "<script language=\"javascript\" type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\">\n<!--\n" . $phrase['JAVASCRIPT_PHRASES'] . "//-->\n</script>\n";
                        
                $headinclude .= "<script language=\"javascript\" type=\"text/javascript\">\n<!--\nvar ILSESSION = \"" . session_id() . "\";";                
                $headinclude .= (defined('LOCATION') AND LOCATION == 'admin')
                        ? "var ILADMIN = \"1\";"
                        : "var ILADMIN = \"0\";";
                        
                $headinclude .= "var ILBASE = \"" . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . "\";";
                $headinclude .= "var IMAGEBASE = \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "\";";
                $headinclude .= "var ILNAME = \"" . $ilconfig['globalsecurity_cookiename'] . "\";";
                $headinclude .= "var ILTOKEN = \"" . TOKEN . "\";";
                $headinclude .= "var PAGEURL = \"" . ilance_htmlentities(un_htmlspecialchars(PAGEURL)) . "\";";
                $headinclude .= "var ESCROW = \"" . $ilconfig['escrowsystem_enabled'] . "\";";
                $headinclude .= "var DISTANCE = \"" . $ilconfig['globalserver_enabledistanceradius'] . "\";";
                $headinclude .= "var LUB = \"" . $ilconfig['enable_uniquebidding'] . "\";";
                
                ($apihook = $ilance->api('headinclude_javascript_end')) ? eval($apihook) : false;
                
                $headinclude .= "//-->\n</script>\n";
                
                $jsurl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
                /*
                // #### HANDLE JAVASCRIPT LOGIC ################################
                if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
		{
			$jsurl = SUB_FOLDER_ROOT;
		}
		else
		{
                        $jsurl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
                }
                */
                $headinclude .= (!empty($jsinclude) AND is_array($jsinclude) AND count($jsinclude) > 0)
                        ? "<script language=\"javascript\" type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\" src=\"" . $jsurl . "javascript.php?do="
                        : '';
                        
                unset($jsurl);
                
                // #### CORE JS ################################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('functions', $jsinclude)) ? "functions," : '';
                
                // #### AJAX JS ################################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('ajax', $jsinclude)) ? "ajax," : '';
                
                // #### EDIT INLINE AJAX #######################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('inline', $jsinclude)) ? "inline," : '';
                
                // #### AJAX CRON JOB EXECUTION ################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('cron', $jsinclude)) ? "cron," : '';
                
				 // #### JQUERY TOOLS ###########################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('jquery', $jsinclude)) ? "jquery," : '';
				
				// #### CUSTOM UI FOR DATEPICKER #####################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('jquery_custom_ui', $jsinclude)) ? "jquery_custom_ui," : '';
               
                // #### HEADER AUTOCOMPLETE SEARCH BAR #########################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('autocomplete', $jsinclude)) ? "autocomplete," : '';

                // #### SEARCH JS FOR DISABLING FORM ELEMENTS ##################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('search', $jsinclude)) ? "search," : '';

                // #### WEB FX TABS ############################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('tabfx', $jsinclude)) ? "tabfx," : '';
                
                // #### MD5 ####################################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('md5', $jsinclude)) ? "md5," : '';
                
                // #### DROP DOWN MENU #########################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('menu', $jsinclude)) ? "menu," : '';
                
                // #### AJAX INLINE EDIT #######################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('inline_edit', $jsinclude)) ? "inline_edit," : '';
                
                
                // #### JQUERY MODAL ###########################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('modal', $jsinclude)) ? "jquery_blockui,jquery_modal," : '';
                
                // #### JQUERY IPHONE CHECKBOXES ###############################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('iphone', $jsinclude)) ? "jquery_iphone," : '';
                
                // #### YAHOO TOOLS ############################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('yahoo-jar', $jsinclude)) ? "yahoo-jar," : '';
                
                // #### PROTOTYPE TOOLS ########################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('prototype', $jsinclude)) ? "prototype," : '';
                
                // #### PROTOTYPE EFFECTS ######################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('effects', $jsinclude)) ? "prototype_effects," : '';
                
                // #### PROTOTYPE LIVE COUNTER #################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('counter', $jsinclude)) ? "prototype_counter," : '';
                
                // #### PROTOTYPE BASIC #################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('basic', $jsinclude)) ? "prototype_basic," : '';
                
                // #### FIX FOR FLASH APPLETS ##################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('flashfix', $jsinclude)) ? "flashfix," : '';
                
                // #### POPUP COLOR PICKER #####################################
                $headincludebit .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('colorpicker', $jsinclude)) ? "colorpicker," : '';
                
                ($apihook = $ilance->api('headinclude_javascript_end_include')) ? eval($apihook) : false;
                
                if (!empty($jsinclude) AND is_array($jsinclude) AND count($jsinclude) > 0 AND !empty($headincludebit))
                {
                        $headincludebit = substr($headincludebit, 0, -1);
                        $headinclude .= $headincludebit;
                        $headinclude .= "\"></script>\n";
                }
                
                // #### DROP DOWN MENU #########################################
                $headinclude .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('menu', $jsinclude))
                        ? "<script language=\"javascript\" type=\"text/javascript\">\n<!--\nvar d = new v3lib()\n//-->\n</script>\n"
                        : '';
                
                // #### IPHONE FINAL ###########################################
                $headinclude .= (!empty($jsinclude) AND is_array($jsinclude) AND in_array('iphone', $jsinclude))
                        ? "<script type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\">\n$(document).ready(function()\n{\n$('.on_off :checkbox').iphoneStyle();\n});\n</script>"
                        : '';
                
                // #### END client script ######################################
                $headinclude .= (!empty($jsinclude) AND is_array($jsinclude) AND count($jsinclude) > 0) ? "<!-- END client script -->\n" : '';
                
                ($apihook = $ilance->api('headinclude_javascript_end_final')) ? eval($apihook) : false;
                
                // #### HANDLE INLINE CSS STYLESHEET ###########################
                $headinclude .= ($ilconfig['externalcss'] == 0 OR !file_exists($css_style['filepath'])) ? "\n<!-- START client style -->\n<style id=\"html\" type=\"text/css\">\n" . $this->computed_style . "</style>\n<!-- END client style -->\n" : '';
                
                // #### HANDLE EXTERNAL CSS STYLESHEET #########################
                $headinclude .= ($ilconfig['externalcss'] AND file_exists($css_style['filepath'])) ? "<!-- START client style -->\n<link rel=\"stylesheet\" href=\"" . $css_style['url'] . "\" type=\"text/css\" media=\"screen\" id=\"html\" />\n<!-- END client style -->\n" : '';
                
                unset($headincludebit);
        }
        
        /*
        * Function to fetch all css from the style datastore and to recursively replace all css array values with
        * intended template variable replacements.  This function will additionally provide results in array format
        * broken down based on the css array format including the css array inline data.
        *
        * @param       array       array holding all css elements and properties
        * @param       array       array holding all css template variables to replace placeholder values {https_server}, etc
        * 
        * @return      array       Returns two arrays one for css data array the other for inline css array for printing
        */
        function handle_css_style_replacements($templatecss = array(), $templatevars = array())
        {
                global $ilance;
                
                $csstypes = array_keys($templatecss);
                $extra = $extra2 = '';
                
                foreach ($csstypes AS $key) 
                {
                        $css_write_order = array_keys($templatecss["$key"]);
                        $css["$key"] = $templatecss["$key"];
                        
                        foreach ($css_write_order AS $itemname)
                        {
                                unset($thisitem);
                                
                                if (isset($links) AND is_array($links))
                                {
                                        unset($links);
                                }
                                
                                if (is_array($css["$key"]["$itemname"]))
                                {                          
                                        foreach ($css["$key"]["$itemname"] AS $cssidentifier => $value)
                                        {
                                                if (preg_match('#^\.(\w+)#si', $itemname, $match))
                                                {
                                                        $itemshortname = $match[1];
                                                }
                                                else
                                                {
                                                        $itemshortname = $itemname;
                                                }
                                                switch ($cssidentifier)
                                                {
                                                        case 'LINK_NORMAL':
                                                        {
                                                                if ($getlinks = $this->handle_css_link($itemname, $cssidentifier, $value))
                                                                {
                                                                        $links['normal'] = $getlinks;
                                                                }
                                                        }
                                                        break;
                                                        case 'LINK_VISITED':
                                                        {
                                                                if ($getlinks = $this->handle_css_link($itemname, $cssidentifier, $value))
                                                                {
                                                                        $links['visited'] = $getlinks;
                                                                }
                                                        }
                                                        break;
                                                        case 'LINK_HOVER':
                                                        {
                                                                if ($getlinks = $this->handle_css_link($itemname, $cssidentifier, $value))
                                                                {
                                                                        $links['hover'] = $getlinks;
                                                                }
                                                        }
                                                        break;
                                                        case 'EXTRA':
                                                        case 'EXTRA2':
                                                        {
                                                                if (!empty($value))
                                                                {
                                                                        $value = $this->handle_replacements($value, $templatevars);
                                                                        $value = "\t" . str_replace("\r\n", "\r\n\t", $value);
                                                                        $thisitem[] = "$value\r\n";
                                                                }
                                                        }
                                                        break;
                                                        case 'font':
                                                        {
                                                                if ($getfont = $this->handle_css_font($value))
                                                                {
                                                                        $thisitem[] = $getfont;
                                                                }
                                                        }
                                                        break;
                                                        default:
                                                        {
                                                                if ($value != '')
                                                                {
                                                                        $value = trim($value);
                                                                        $value = $this->handle_replacements($value, $templatevars);
                                                                        
                                                                        switch ($cssidentifier)
                                                                        {
                                                                                case 'background':
                                                                                {
                                                                                        $csscolors["{$itemshortname}_bgcolor"] = $this->fetch_css_color_value($value);
                                                                                }
                                                                                break;
                                                                                case 'color':
                                                                                {
                                                                                        $csscolors["{$itemshortname}_fgcolor"] = $this->fetch_css_color_value($value);
                                                                                }
                                                                                break;
                                                                        }
                                                                        
                                                                        $thisitem[] = "\t$cssidentifier: $value;\r\n";
                                                                }
                                                        }                
                                                }
                                        }
                                }
                                
                                // add the item to the css if it's not blank
                                if (isset($thisitem) AND sizeof($thisitem) > 0)
                                {
                                        if (!isset($links) OR !is_array($links))
                                        {
                                                $links = array('normal' => '', 'visited' => '', 'hover' => '');
                                        }
                                        
                                        $cssarray["$key"][] = "$itemname\r\n{\r\n" . implode('', $thisitem) . "}\r\n" . (!empty($links['normal']) ? $links['normal'] : '') . (!empty($links['visited']) ? $links['visited'] : '') . (!empty($links['hover']) ? $links['hover'] : '');
                                }
								
								//suku
                                if(isset($cssarray) and is_array($cssarray["$key"]))
                                $cssinline["$key"] = trim(implode('', $cssarray["$key"]) . $extra . "\r\n" . $extra2);
                        }
                }
                
                return array(
                        'array' => $css,
                        'inline' => $cssinline
                );
        }
        
        /*
        * Function to actually convert the template variables based on any array values being passed.
        *
        * @param       string      text of array value
        * @param       array       template variables array
        * 
        * @return      string      Returns properly formatted and replaced text
        */
        function handle_replacements($text = '', $templatevars = array())
        {
                if ($text != '' AND is_array($templatevars))
                {
                        foreach ($templatevars AS $varname => $content)
                        {
                                if ($varname != '' AND $content != '')
                                {
                                        $text = str_replace("{" . $varname . "}", $content, $text);
                                }
                        }
                        
                        // handle all urls as relative to avoid ssl issues with internal & external css
                        /*if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
                        {
                                $text = str_replace("{template_relativeimagepath}", SUB_FOLDER_ROOT, $text);
                        }
                        else
                        {
                                $text = str_replace("{template_relativeimagepath}", ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER), $text);
                        }*/
						$text = str_replace("{template_relativeimagepath}", HTTPS_SERVER, $text);
                }
                
                return $text;
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function fetch_css_color_value($csscolor)
        {
                if (preg_match('/^(rgb\([0-9,\s]+\)|(#?\w+))(\s|$)/siU', $csscolor, $match))
                {
                        return $match[1];
                }
                else
                {
                        return $csscolor;
                }
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function handle_css_font($font)
        {
                // possible values for CSS 'font-weight' attribute
                $css_font_weight = array('normal', 'bold', 'bolder', 'lighter');
        
                // possible values for CSS 'font-style' attribute
                $css_font_style = array('normal', 'italic', 'oblique');
        
                // possible values for CSS 'font-variant' attribute
                $css_font_variant = array('normal', 'small-caps');
        
                foreach ($font AS $key => $value)
                {
                        $font["$key"] = trim($value);
                }
        
                $out = '';
        
                if (!empty($font['size']) AND !empty($font['family']))
                {
        
                        foreach ($font as $value)
                        {
                                $out .= "$value ";
                        }
                        $out = trim($out);
                        if (!empty($out))
                        {
                                $out = "\tfont: $out;\r\n";
                        }
        
                }
                else
                {
        
                        if (!empty($font['size']))
                        {
                                $out .= "\tfont-size: $font[size];\r\n";
                        }
                        if (!empty($font['family']))
                        {
                                $out .= "\tfont-family: $font[family];\r\n";
                        }
                        if (!empty($font['style']))
                        {
                                $stylebits = explode(' ', $font['style']);
                                foreach ($stylebits as $bit)
                                {
                                        $bit = mb_strtolower($bit);
                                        if (in_array($bit, $css_font_weight) OR preg_match('/[1-9]{1}00/', $bit))
                                        {
                                                $out .= "\tfont-weight: $bit;\r\n";
                                        }
                                        if (in_array($bit, $css_font_style))
                                        {
                                                $out .= "\tfont-style: $bit;\r\n";
                                        }
                                        if (in_array($bit, $css_font_variant))
                                        {
                                                $out .= "\tfont-variant: $bit;\r\n";
                                        }
                                        if (preg_match('/(pt|\.|%)/siU', $bit))
                                        {
                                                $out .= "\tline-height: $bit;\r\n";
                                        }
                                }
                        }
        
                }
        
                if (trim($out) == '')
                {
                        return false;
                }
                else
                {
                        return $out;
                }
        
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function handle_css_link($item, $what, $array)
        {
                $out = '';
                
                foreach ($array as $attribute => $value)
                {
                        $value = trim($value);
                        if (!empty($value))
                        {
                                $out .= "\t$attribute: $value;\r\n";
                        }
                }
        
                if (!empty($out))
                {
                        $item_bits = '';
                        $items = explode(',', $item);
                        foreach ($items as $one_item)
                        {
                                $one_item = trim($one_item);
                                if (!empty($one_item))
                                {
                                        if ($what == 'LINK_NORMAL')
                                        {
                                                $item_bits .= ", $one_item a:link, {$one_item}_alink";
                                        }
                                        else if ($what == 'LINK_VISITED')
                                        {
                                                $item_bits .= ", $one_item a:visited, {$one_item}_avisited";
                                        }
                                        else
                                        {
                                                $item_bits .= ", $one_item a:hover, $one_item a:active, {$one_item}_ahover";
                                        }
                                }
                        }
                        
                        $item_bits = str_replace('body a:', 'a:', mb_substr($item_bits, 2));
                        switch ($what)
                        {
                                case 'LINK_NORMAL':
                                        return "$item_bits\r\n{\r\n$out}\r\n";
                                case 'LINK_VISITED':
                                        return "$item_bits\r\n{\r\n$out}\r\n";
                                default:
                                        return "$item_bits\r\n{\r\n$out}\r\n";
                        }
                }
                else
                {
                        return false;
                }
        }
        
        /*
        * Function to determine if the selected style id is valid within the datastore
        *
        * @param       integer     style id
        * 
        * @return      bool        Returns true or false
        */
        function is_styleid_valid($styleid = 0)
        {
                global $ilance;
				 return true;
                /*
                $sql = $ilance->db->query("
                        SELECT visible
                        FROM " . DB_PREFIX . "styles
                        WHERE styleid = '" . intval($styleid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        return true;
                }
                return false;*/
        }
        /*
        * Function to determine the version of the browser being used
        *
        * @param       string      browser user agent
        * @param       string      search string
        * 
        * @return      integer     Returns the browser version
        */
        function browser_version($browser_user_agent, $search_string)
        {
                $string_length = 8;
                $browser_number = '';
                $start_pos = mb_strpos($browser_user_agent, $search_string);
                $start_pos += mb_strlen($search_string)+1;
        
                for ($i = $string_length; $i > 0; $i--)
                {
                        if (is_numeric(mb_substr($browser_user_agent, $start_pos, $i)))
                        {
                                $browser_number = mb_substr($browser_user_agent, $start_pos, $i);
                                break;
                        }
                }
                return $browser_number;
        }
    
        /*
        * Function to determine the web browser
        *
        * @param       string      what to determine? (browser, number or full)?
        * 
        * @return      array       Returns the details requested
        */
        function browser_detection($which_test)
        {
                $browser_name = '';
                $browser_number = '';
                $ag = USERAGENT;
                $browser_user_agent = mb_strtolower($ag);
        
                $a_browser_types[] = array('opera', true, 'op' );
                $a_browser_types[] = array('msie', true, 'ie' );
                $a_browser_types[] = array('konqueror', true, 'konq' );
                $a_browser_types[] = array('safari', true, 'saf' );
                $a_browser_types[] = array('gecko', true, 'moz' );
                $a_browser_types[] = array('mozilla/4', false, 'ns4' );
        
                for ($i = 0; $i < count($a_browser_types); $i++)
                {
                        $s_browser = $a_browser_types[$i][0];
                        $b_dom = $a_browser_types[$i][1];
                        $browser_name = $a_browser_types[$i][2];
            
                        if (mb_stristr($browser_user_agent, $s_browser))
                        {
                                if ($browser_name == 'moz')
                                {
                                        $s_browser = 'rv';
                                }
                                $browser_number = $this->browser_version($browser_user_agent, $s_browser);
                                break;
                        }
                }
                if ($which_test == 'browser')
                {
                        return $browser_name;
                }
                else if ($which_test == 'number')
                {
                        return $browser_number;
                }
                else if ($which_test == 'full')
                {
                        $a_browser_info = array( $browser_name, $browser_number );
                        return $a_browser_info;
                }
        }
           
        /*
        * Function to return site style selection / theme pulldown menu on footer pages
        *
        * @return      string       HTML formatted style pulldown menu
        */
        function print_styles_pulldown($selected = '', $autosubmit = '')
        {
                global $ilance, $phrase, $myapi;
                
                if (isset($autosubmit) AND $autosubmit)
                {
                        $html = '<select name="styleid" id="styleid" onchange="urlswitch(this, \'dostyle\')" style="font-family: verdana">';
                }
                else
                {
                        $html = '<select name="styleid" id="styleid" style="font-family: verdana">';
                }
                
                $html .= '<optgroup label="' . $phrase['_choose_style'] . '">';
                
                $sql = $ilance->db->query("
                        SELECT styleid, name
                        FROM " . DB_PREFIX . "styles
                        WHERE visible = '1'
                        ORDER BY styleid ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $stylecount = 0;
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($selected) AND $res['styleid'] == $selected)
                                {
                                        $html .= '<option value="' . $res['styleid'] . '" selected="selected">' . stripslashes($res['name']) . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['styleid'] . '">' . stripslashes($res['name']) . '</option>';
                                }
                                $stylecount++;
                        }
                }
                $html .= '</optgroup></select>';
                
                // if we're viewing this pulldown menu from the footer page, and have only 1 language, hide the pulldown menu
                if (isset($autosubmit) AND $autosubmit AND $stylecount == 1 AND defined('LOCATION') AND LOCATION != 'admin')
                {
                        $html = '';
                }
                
                return $html;
        }
        
        /*
        * Function to fetch the site's default style title
        *
        * @return      string       Default style name
        */
        function fetch_default_style_title()
        {
                global $ilance, $myapi, $ilconfig;
                
                if (!empty($ilconfig['defaultstyle']) AND $ilconfig['defaultstyle'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT name
                                FROM " . DB_PREFIX . "styles
                                WHERE styleid = '" . intval($ilconfig['defaultstyle']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res['name']);
                        }
                }
        }
        
        /*
        * Function to fetch a css element from the selected style from the datastore
        *
        * @param       string       css element (eg: alt1)
        * @param       string       css property (eg: background, color, font-style, font-size, font-family, etc)
        * @param       string       css type (cssclient, cssadmin, csswysiwyg, csstabs, csscommon)
        *
        * @return      string       Returns property of selected element
        */
        function fetch_css_element($element = '', $property = '', $csstype = '')
        {
                global $ilance, $myapi, $ilconfig;
                
                $value = '';
                
                $sql = $ilance->db->query("
                        SELECT name, content
                        FROM " . DB_PREFIX . "templates
                        WHERE styleid = '" . $_SESSION['ilancedata']['user']['styleid'] . "'
                            AND type = '" . $ilance->db->escape_string($csstype) . "'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if ($res['name'] == '.' . $element OR $res['name'] == '#' . $element)
                                {
                                        $content = $res['content'];
                                        $content = unserialize($content);
                                        
                                        switch ($property)
                                        {
                                                case 'background':
                                                {
                                                        $value = $content['background'];
                                                        break;
                                                }
                                                case 'color':
                                                {
                                                        $value = $content['color'];
                                                        break;
                                                }
                                                case 'font-style':
                                                {
                                                        $value = $content['font']['style'];
                                                        break;
                                                }
                                                case 'font-size':
                                                {
                                                        $value = $content['font']['size'];
                                                        break;
                                                }
                                                case 'font-family':
                                                {
                                                        $value = $content['font']['family'];
                                                        break;
                                                }
                                                case 'EXTRA':
                                                {
                                                        $value = $content['EXTRA'];
                                                        break;
                                                }
                                                case 'LINK_NORMAL_background':
                                                {
                                                        $value = $content['LINK_NORMAL']['background'];
                                                        break;
                                                }
                                                case 'LINK_NORMAL_color':
                                                {
                                                        $value = $content['LINK_NORMAL']['color'];
                                                        break;
                                                }
                                                case 'LINK_NORMAL_textdecoration':
                                                {
                                                        $value = $content['LINK_NORMAL']['text-decoration'];
                                                        break;
                                                }
                                                case 'LINK_VISITED_background':
                                                {
                                                        $value = $content['LINK_VISITED']['background'];
                                                        break;
                                                }
                                                case 'LINK_VISITED_color':
                                                {
                                                        $value = $content['LINK_VISITED']['color'];
                                                        break;
                                                }
                                                case 'LINK_VISITED_textdecoration':
                                                {
                                                        $value = $content['LINK_VISITED']['text-decoration'];
                                                        break;
                                                }
                                                case 'LINK_HOVER_background':
                                                {
                                                        $value = $content['LINK_HOVER']['background'];
                                                        break;
                                                }
                                                case 'LINK_HOVER_color':
                                                {
                                                        $value = $content['LINK_HOVER']['color'];
                                                        break;
                                                }
                                                case 'LINK_HOVER_textdecoration':
                                                {
                                                        $value = $content['LINK_HOVER']['text-decoration'];
                                                        break;
                                                }
                                        }
                                }
                        }
                        
                        return $value;
                }
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function print_css_elements_pulldown($name = '')
        {
                global $ilance, $ilconfig, $phrase;
                
                $html = '<select name="csselement" style="font-family: verdana">';
                               
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "templates
                        WHERE type != 'variable'
                        GROUP BY name, type
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if ($res['type'] == 'csscommon')
                                {
                                        $css['csscommon'][] = $res['name'];
                                }
                                else if ($res['type'] == 'cssclient')
                                {
                                        $css['cssclient'][] = $res['name'];
                                }
                                else if ($res['type'] == 'cssadmin')
                                {
                                        $css['cssadmin'][] = $res['name'];
                                }
                                else if ($res['type'] == 'csswysiwyg')
                                {
                                        $css['csswysiwyg'][] = $res['name'];
                                }
                                else if ($res['type'] == 'csstabs')
                                {
                                        $css['csstabs'][] = $res['name'];
                                }
                        }
                        $html .= '<option value="">' . $phrase['_show_all_css_elements'] . '</option>';
                        $html .= '<optgroup label="' . $phrase['_css_client'] . '">';
                        foreach ($css['cssclient'] AS $key => $value)
                        {
                                $selected = '';
                                if (isset($name) AND $name == $value)
                                {
                                        $selected = 'selected="selected"';
                                }
                                $html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</optgroup>';
                        $html .= '<optgroup label="' . $phrase['_css_admin'] . '">';
                        foreach ($css['cssadmin'] AS $key => $value)
                        {
                                $selected = '';
                                if (isset($name) AND $name == $value)
                                {
                                        $selected = 'selected="selected"';
                                }
                                $html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</optgroup>';
                        $html .= '<optgroup label="' . $phrase['_css_wysiwyg'] . '">';
                        foreach ($css['csswysiwyg'] AS $key => $value)
                        {
                                $selected = '';
                                if (isset($name) AND $name == $value)
                                {
                                        $selected = 'selected="selected"';
                                }
                                $html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</optgroup>';
                        $html .= '<optgroup label="' . $phrase['_css_tab_pane'] . '">';
                        foreach ($css['csstabs'] AS $key => $value)
                        {
                                $selected = '';
                                if (isset($name) AND $name == $value)
                                {
                                        $selected = 'selected="selected"';
                                }
                                $html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</optgroup>';
                        $html .= '<optgroup label="' . $phrase['_css_common'] . '">';
                        foreach ($css['csscommon'] AS $key => $value)
                        {
                                $selected = '';
                                if (isset($name) AND $name == $value)
                                {
                                        $selected = 'selected="selected"';
                                }
                                $html .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                        }
                        $html .= '</optgroup>';
                }
                
                $html .= '</select>';
                
                return $html;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
