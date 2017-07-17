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
* ILance class to perform the majority of the main common ILance functions.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class ilance
{
        /**
	* $_GET, $_POST and $_COOKIE array
	*
	* @var	    $GPC
	*/
	var $GPC = array();
        
        /**
	* Will store {apihook[xxx]}'s that are being loaded to prevent double loading
	*
	* @var	    $apihooks
	*/
        var $apihooks = array();
        
        /**
	* Will store $ilconfig as $this->config['xxx'] (will use more in future)
	*
	* @var	    $config
	*/
	var $config = array();
        
        /**
	* Will store all plugins currently installed into an array for future processing
	*
	* @var	    $pluginsxml
	*/
        var $plugins = array();
    
        /**
	* Constructor
	*/
	function ilance()
	{
		// set main ilance product details
		$this->config['ilversion'] = ILANCEVERSION;
		$this->config['licensekey'] = LICENSEKEY;
                
                $this->plugins = $this->fetch_installed_plugins();
		
		// determine magic quotes status
		if (get_magic_quotes_gpc())
		{
			$this->magicquotes = 1;
			$this->strip_slashes_array($_REQUEST);
			$this->strip_slashes_array($_POST);
			$this->strip_slashes_array($_GET);
			$this->strip_slashes_array($_COOKIE);
		}
		@ini_set('magic_quotes_runtime', 0);
			
		$arrays = array_merge($_GET, $_POST);
                //$arrays = array_merge($_COOKIE, $arrays);
                
		$this->parse_incoming($arrays);
	
		// if we've got register globals on, then kill them too
		if (@ini_get('register_globals') OR !@ini_get('gpc_order'))
		{
			$this->unset_globals($_POST);
			$this->unset_globals($_GET);
			$this->unset_globals($_FILES);
		}
                
                if (mb_strtolower($_SERVER['REQUEST_METHOD']) == 'post')
                {
                        $acceptedreferrers = ""; // add to db as textarea
                        
                        if (!empty($_ENV['HTTP_HOST']) OR !empty($_SERVER['HTTP_HOST']))
                        {
                                $httphost = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
                        }
                        else if (!empty($_SERVER['SERVER_NAME']) OR !empty($_ENV['SERVER_NAME']))
                        {
                                $httphost = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME'];
                        }
                
                        if (!empty($httphost) AND !empty($_SERVER['HTTP_REFERER']))
                        {
                                $httphost = preg_replace('#:80$#', '', trim($httphost));
                                $parts = @parse_url($_SERVER['HTTP_REFERER']);
                                $port = !empty($parts['port']) ? intval($parts['port']) : '80';
                                $host = $parts['host'] . ((!empty($port) AND $port != '80') ? ":$port" : '');
                
                                $allowdomains = preg_split('#\s+#', $acceptedreferrers, -1, PREG_SPLIT_NO_EMPTY);
                                $allowdomains[] = preg_replace('#^www\.#i', '', $httphost);
                                $allowdomains[] = '.paypal.com';
                
                                $passcheck = false;
                                foreach ($allowdomains AS $allowhost)
                                {
                                        if (preg_match('#' . preg_quote($allowhost, '#') . '$#siU', $host))
                                        {
                                                $passcheck = true;
                                                break;
                                        }
                                }
                                unset($allowdomains);
                
                                if ($passcheck == false)
                                {
                                        $message = 'POST request could not find your domain in the whitelist. Please contact support for further information.';
                                        $template = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
        <title>POST request error</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style type="text/css">
        <!--	
        body { background-color: white; color: black; }
        #container { width: 400px; }
        #message   { width: 400px; color: black; background-color: #FFFFCC; }
        #bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
        .bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
        a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
        a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
        -->
        </style>
</head>
<body>
<table cellpadding="3" cellspacing="5" id="container">
<tr>
        <td id="bodytitle" width="100%">POST Request Error</td>
</tr>
<tr>
        <td class="bodytext" colspan="2">' . $message . '</td>
</tr>
<tr>
        <td colspan="2"><hr /></td>
</tr>
<tr>
        <td class="bodytext" colspan="2">
                Please try the following:
                <ul>
                        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
                </ul>
        </td>
</tr>
<tr>
        <td class="bodytext" colspan="2">The technical staff have been notified of the error.  We apologise for any inconvenience.</td>
</tr>
</table>
</body>
</html>';
                                        // tell the search engines that our service is temporarily unavailable to prevent indexing db errors
                                        header('HTTP/1.1 503 Service Temporarily Unavailable');
                                        header('Status: 503 Service Temporarily Unavailable');
                                        header('Retry-After: 3600');
                                        
                                        die($template);
                                }
                        }
                }
	}
	/**
	* Function to parse any incoming input and tranform it into our reusable $ilance->GPC array used in the software.
	*
	* @param       array         array
	* 
        * @return      nothing
	*/
        function parse_incoming($array)
	{
		if (!is_array($array))
		{
			return;
		}
                
		foreach ($array AS $key => $val)
		{
			$this->GPC["$key"] = $val;
		}
	}
	/**
	* Function wrapper for the xx_escape_string function for escaping valid sql input
	*
	* @param       string        string to escape
	* 
        * @return      string        Returns xx_escape_string value
	*/
        function escape_string($text = '')
	{
		global $ilance;
		return $ilance->db->escape_string($text);
	}
	/**
	* Function to strip any slashes within a regular or recursive array
	*
	* @param       array         array
	* 
        * @return      nothing
	*/
        function strip_slashes_array(&$array)
	{
		foreach ($array AS $key => $val)
		{
			if (is_array($array[$key]))
			{
				$this->strip_slashes_array($array[$key]);
			}
			else
			{
				$array[$key] = stripslashes($array[$key]);
			}
		}
	}
	/**
	* Function to unset $_GLOBAL's from being set by users via URL manipulation
	*
	* @param       array         array value to clean
	* 
        * @return      nothing
	*/
        function unset_globals($array)
	{
		if (!is_array($array))
		{
			return;
		}
                
		foreach (array_keys($array) AS $key)
		{
			unset($GLOBALS["$key"]);
		}
	}
	
	/**
	* Function to clean $_GLOBAL, $_POST and $_COOKIE input
	*
	* @param       string        g, p or c values 
	* @param       array         array or value to clean
	* @param       string        variable clean type selector (ie: TYPE_INT, TYPE_NUM, etc)
	* 
        * @return      nothing
	*/
        function clean_gpc($gpc, $variable, $type = '')
	{
		$boolmethods = array('1', 'yes', 'y', 'true');
                
                if (empty($type))
                { // handling input in main scripts (abuse.php, etc)
                        foreach ($variable as $fieldname => $type)
                        {
                                switch ($type)
                                {
                                        case 'TYPE_INT':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = intval($_GET["$fieldname"]);	
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = intval($_POST["$fieldname"]);	
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = intval($_COOKIE["$fieldname"]);	
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_NUM':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_GET["$fieldname"]) + 0;	
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_POST["$fieldname"]) + 0;
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_COOKIE["$fieldname"]) + 0;
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_STR':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = trim(strval($_GET["$fieldname"]));
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = trim(strval($_POST["$fieldname"]));
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = trim(strval($_COOKIE["$fieldname"]));
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_NOTRIM':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_GET["$fieldname"]);
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_POST["$fieldname"]);
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_COOKIE["$fieldname"]);
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_NOHTML':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_GET["$fieldname"])));
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_POST["$fieldname"])));
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_COOKIE["$fieldname"])));
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_BOOL':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = in_array(mb_strtolower($_GET["$fieldname"]), $boolmethods) ? 1 : 0; 
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = in_array(mb_strtolower($_POST["$fieldname"]), $boolmethods) ? 1 : 0; 
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = in_array(mb_strtolower($_COOKIE["$fieldname"]), $boolmethods) ? 1 : 0; 
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_ARRAY':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = (is_array($_GET["$fieldname"])) ? $fieldname : array();
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = (is_array($_POST["$fieldname"])) ? $fieldname : array();
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = (is_array($_COOKIE["$fieldname"])) ? $fieldname : array();
                                                }
                                                break;
                                        }
                                }
                        }
                }
                else
                { // handling input in datamanger scripts (class.datamanager_xxx.inc.php, etc)
                        switch ($type)
                        {
                                case 'TYPE_INT':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = intval($_GET["$variable"]);	
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = intval($_POST["$variable"]);	
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = intval($_COOKIE["$variable"]);	
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = intval($variable);
                                        }
                                        break;
                                }                        
                                case 'TYPE_NUM':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = strval($_GET["$variable"]) + 0;	
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = strval($_POST["$variable"]) + 0;
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = strval($_COOKIE["$variable"]) + 0;
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = strval($variable) + 0;
                                        }
                                        break;
                                }                        
                                case 'TYPE_STR':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = trim(strval($_GET["$variable"]));
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = trim(strval($_POST["$variable"]));
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = trim(strval($_COOKIE["$variable"]));
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = trim(strval($variable));
                                        }
                                        break;
                                }                        
                                case 'TYPE_NOTRIM':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = strval($_GET["$variable"]);
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = strval($_POST["$variable"]);
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = strval($_COOKIE["$variable"]);
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = strval($variable);
                                        }
                                        break;
                                }                        
                                case 'TYPE_NOHTML':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_GET["$variable"])));
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_POST["$variable"])));
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_COOKIE["$variable"])));
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($variable)));
                                        }
                                        break;
                                }                        
                                case 'TYPE_BOOL':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($_GET["$variable"]), $boolmethods) ? 1 : 0; 
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($_POST["$variable"]), $boolmethods) ? 1 : 0; 
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($_COOKIE["$variable"]), $boolmethods) ? 1 : 0; 
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($variable), $boolmethods) ? 1 : 0; 
                                        }
                                        break;
                                }                        
                                case 'TYPE_ARRAY':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = (is_array($_GET["$variable"])) ? $variable : array();
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = (is_array($_POST["$variable"])) ? $variable : array();
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = (is_array($_COOKIE["$variable"])) ? $variable : array();
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = (is_array($variable)) ? $variable : array();
                                        }
                                        break;
                                }
                        }
                        if ($gpc == 's')
                        {
                                return $this->GPC["$variable"];
                        }        
                }
	}	
	/**
	* Function to connect to the ilance.com web site to fetch the latest version of any specific add-on product supported by ILance.
	*
	* @param       string        version checkup url (ie: http://www.ilance.com/lancebb/versioncheck)
	* 
        * @return      string        Returns formatted HTML or PHP code to be parsed inline as called.
	*/
        function latest_addon_version($versioncheckurl = '')
	{
                global $myapi, $ilconfig, $phrase;
                
                $version = '-';
		return $version;
	
                if (defined('LOCATION') AND LOCATION == 'admin' AND !empty($versioncheckurl) AND defined('VERSIONCHECK') AND VERSIONCHECK)
                {
                        // may cause slight delay for 1 or 2 seconds to grab latest version
                        $fp = @fopen($versioncheckurl, 'r');
                        $version = trim(@fread($fp, 16));
                        @fclose($fp);                    
                        if (mb_strlen($version) > 5)
                        {
                                $version = $phrase['_unknown'];
                        }
                }
                if (empty($versioncheckurl))
                {
                        $versioncheckurl = '#';
                }
                
                return '<a href="' . $versioncheckurl . '" target="_blank">' . $version . '</a>';
        }
        
        /**
	* Function to search and locate any inline php or html code to be parsed within an official ILance api hook.
	*
	* @param       string        api location hook name (ie: init_configuration_end)
	* 
        * @return      string        Returns formatted HTML or PHP code to be parsed inline as called.
	*/
        function api($location = '')
        {
                global $ilance, $ilconfig, $phrase;
                
                if ((defined('DISABLE_PLUGINAPI') AND DISABLE_PLUGINAPI) OR empty($location))
                {
                        return;
                }
                
                //$function->timer = new timer;
                //$function->timer->start();
                
                if (!in_array($location, $this->apihooks))
                {
                        $this->apihooks["$location"] = true;
                }
                
                $plugincode = '';
                $foundinlinecode = 0;
                $foundinlinehtml = 0;
                foreach ($this->plugins as $plugs)
                {
                        // each array denotes a new plugin_*.xml file loaded from the xml folder
                        if (!empty($plugs) AND is_array($plugs))
                        {
                                // perhaps this plugin_*.xml file contains multiple <plug> calls in the same plugin file
                                // this will return at least 1 plug array [attached to a specific inline api hook] (if not we'll skip)
                                foreach ($plugs as $plugin)
                                {
                                        if (is_array($plugin))
                                        {
                                                foreach ($plugin AS $plugkey => $plugvalue)
                                                {
                                                        if (($plugkey == 'key' OR $plugkey == 'addon' OR $plugkey == 'title' OR $plugkey == 'api' OR $plugkey == 'php' OR $plugkey == 'html') AND !is_array($plugvalue))
                                                        {
                                                                // plugin_*.xml file contains a single <plug> tags
                                                                //echo "<li />$plugkey => $plugvalue";
                                                                if ($location == $plugin['api'])
                                                                {
                                                                        if (empty($plugin['html']) AND !empty($plugin['php']))
                                                                        {
                                                                                //echo "<li />$plugin[php]";
                                                                                $plugincode .= stripslashes($plugin['php']);
                                                                                $foundinlinecode++;
                                                                                break;
                                                                        }
                                                                        else if  (!empty($plugin['html']) AND empty($plugin['php']))
                                                                        {
                                                                                //echo "<li />$plugin[html]";
                                                                                $plugincode .= stripslashes($plugin['html']);
                                                                                $foundinlinehtml++;
                                                                                break;
                                                                        }
                                                                }
                                                        }
                                                        else
                                                        {
                                                                // plugin_*.xml file contains multiple <plug> tags
                                                                foreach ($plugvalue AS $pluginkey => $pluginkeyvalue)
                                                                {
                                                                        if ($location == $plugvalue['api'])
                                                                        {
                                                                                if (empty($plugvalue['html']) AND !empty($plugvalue['php']))
                                                                                {
                                                                                        //echo "<li />$plugvalue[php]";
                                                                                        $plugincode .= stripslashes($plugvalue['php']);
                                                                                        $foundinlinecode++;
                                                                                        break;
                                                                                }
                                                                                else if  (!empty($plugvalue['html']) AND empty($plugvalue['php']))
                                                                                {
                                                                                        
                                                                                        //echo "<li />$plugvalue[html]";
                                                                                        $plugincode .= stripslashes($plugvalue['html']);
                                                                                        $foundinlinehtml++;
                                                                                        break;
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                }
                                        }        
                                }        
                        }
                }
                
                //$function->timer->stop();
                //DEBUG("$location, found $foundinlinecode code plugins and $foundinlinehtml html plugins attached to this hook in " . $function->timer->get() . " seconds", 'HOOK');
                return $plugincode;
        }
    
        /**
	* Function to fetch all installed plugin_*.xml files and build the plugin array with any installed add-on products
	*
        * @return      none
	*/
	function fetch_installed_plugins()
	{
                global $ilance, $myapi, $phrase;
                
                if ((defined('DISABLE_PLUGINAPI') AND DISABLE_PLUGINAPI))
                {
                        return;
                }
                
                $foundplugins = 0;
                $function->timer = new timer;
                $function->timer->start();
                
                $ilance->xml = construct_object('api.xml');
                
                $xml = array();
                
                $handle = opendir(DIR_XML);
                while (($file = readdir($handle)) !== false)
                {
                        if (!preg_match('#^plugin_(.*).xml$#i', $file, $matches))
                        {
                                continue;
                        }
                        $xml[] = $ilance->xml->construct_xml_array('UTF-8', 1, $file);
                        $foundplugins++;
                }
                ksort($xml);
                
                $function->timer->stop();
                DEBUG("fetch_installed_plugins(), found $foundplugins plugins in " . $function->timer->get() . " seconds", 'FUNCTION');
                
                return $xml;
	}
	
        /**
        * Function to fetch the language locale settings to setup our environment
        *
        * @param       itneger       language id 
        * 
        * @return      integer       Returns an array with locale settings (locale, decimal and thousands)
        */
        function fetch_language_locale($languageid = 1)
        {
                global $ilance;
                $res['locale'] = 'en_US';
                /*
                $sql = $ilance->db->query("
                        SELECT locale
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($languageid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                }
                */
                return $res;
        }
        
	/**
	* Initializes the $ilconfig array as well as the payment modules configuration construction
	*
	* @return      none
	*/
	function init_configuration()
	{
                global $phrase;
                
                /**
                * print a stop message on servers running php < 4.3.0
                */
                if (PHP_VERSION < '5.2.0')
                {
                        die('<strong>Fatal error:</strong> installed php version <strong>' . PHP_VERSION . '</strong> is currently not supported. Minimum expected version is <strong>5.2.0</strong> or higher');
                }
                
                $ilconfig = array();
                global $ilance, $ilconfig, $ilpage, $ilcrumbs;
                
                $config = $ilance->db->query("
                        SELECT name, value
                        FROM " . DB_PREFIX . "configuration
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($config) > 0)
                {
                        while ($res = $ilance->db->fetch_array($config))
                        {
                                $ilconfig[$res['name']] = $res['value'];
                        }
                        unset($res);
                        
                        // important global constants
						define('COMPANY_NAME', stripslashes($ilconfig['globalserversettings_companyname']));
                        define('SITE_NAME', stripslashes($ilconfig['globalserversettings_sitename']));
                        define('SITE_ADDRESS', stripslashes($ilconfig['globalserversettings_siteaddress']));
                        define('SITE_EMAIL', stripslashes($ilconfig['globalserversettings_siteemail']));
						define('OWNER_EMAIL', stripslashes($ilconfig['globalserversettings_adminemail']));
						define('DONA_EMAIL', stripslashes($ilconfig['globalserversettings_accountsemail']));
                        define('SITE_PHONE', stripslashes($ilconfig['globalserversettings_sitephone']));
                        define('ADMINCP_TEST_MODE', preg_match('#ilance.com$#', $_SERVER['HTTP_HOST']));
                        define('COOKIE_PREFIX', (empty($ilconfig['globalsecurity_cookiename']) ? 'ilance_' : $ilconfig['globalsecurity_cookiename']));
                        define('CSRF_TOKEN_SECURITY', false);
                        
                        // important script titles
                        // the breadcrumb list can be found in functions.php within function fetch_breadcrumb_titles()
						//arsath added the ilpage for staff settings,users
						//
						//'attachment' => 'https://images.greatcollections.com/index' . $ilconfig['globalsecurity_extensionmime'],
                        $ilpage = array(
                                'invoicepayment' => 'invoicepayment' . $ilconfig['globalsecurity_extensionmime'],
                                'login' => 'login' . $ilconfig['globalsecurity_extensionmime'],
                                'payment' => 'payment' . $ilconfig['globalsecurity_extensionmime'],
                                'attachment' => 'attachment' . $ilconfig['globalsecurity_extensionmime'],
                                'buying' => 'buying' . $ilconfig['globalsecurity_extensionmime'],
                                'rfp' => 'rfp' . $ilconfig['globalsecurity_extensionmime'],
                                'pmb' => 'pmb' . $ilconfig['globalsecurity_extensionmime'],
                                'feedback' => 'feedback' . $ilconfig['globalsecurity_extensionmime'],
                                'members' => 'members' . $ilconfig['globalsecurity_extensionmime'],
                                'portfolio' => 'portfolio' . $ilconfig['globalsecurity_extensionmime'],
                                'merch' => 'merch' . $ilconfig['globalsecurity_extensionmime'],
                                'main' => 'main' . $ilconfig['globalsecurity_extensionmime'],
                                'watchlist' => 'watchlist' . $ilconfig['globalsecurity_extensionmime'],
                                'upload' => 'upload' . $ilconfig['globalsecurity_extensionmime'],
                                'preferences' => 'preferences' . $ilconfig['globalsecurity_extensionmime'],
                                'subscription' => 'subscription' . $ilconfig['globalsecurity_extensionmime'],
                                'accounting' => 'accounting' . $ilconfig['globalsecurity_extensionmime'],
                                'messages' => 'messages' . $ilconfig['globalsecurity_extensionmime'],
                                'notify' => 'notify' . $ilconfig['globalsecurity_extensionmime'],
                                'abuse' => 'abuse' . $ilconfig['globalsecurity_extensionmime'],
                                'search' => 'search' . $ilconfig['globalsecurity_extensionmime'],
                                'upload' => 'upload' . $ilconfig['globalsecurity_extensionmime'],
                                'rss' => 'rss' . $ilconfig['globalsecurity_extensionmime'],
                                'registration' => 'registration' . $ilconfig['globalsecurity_extensionmime'],
                                'selling' => 'selling' . $ilconfig['globalsecurity_extensionmime'],
							'sell' => 'sell' . $ilconfig['globalsecurity_extensionmime'],
                                'index' => 'index' . $ilconfig['globalsecurity_extensionmime'],
                                'workspace' => 'workspace' . $ilconfig['globalsecurity_extensionmime'],
                                'mediashare' => 'mediashare' . $ilconfig['globalsecurity_extensionmime'],
                                'campaign' => 'campaign' . $ilconfig['globalsecurity_extensionmime'],
                                'ajax' => 'ajax' . $ilconfig['globalsecurity_extensionmime'],
                                'nonprofits' => 'nonprofits' . $ilconfig['globalsecurity_extensionmime'],
								'escrow' => 'escrow' . $ilconfig['globalsecurity_extensionmime'],
								'buyer_invoice' => 'buyer_invoice' . $ilconfig['globalsecurity_extensionmime'],
                                // admin control panel
                                'components' => 'components' . $ilconfig['globalsecurity_extensionmime'],
                                'connections' => 'connections' . $ilconfig['globalsecurity_extensionmime'],
                                'distribution' => 'distribution' . $ilconfig['globalsecurity_extensionmime'],
                                'language' => 'language' . $ilconfig['globalsecurity_extensionmime'],
                                'settings' => 'settings' . $ilconfig['globalsecurity_extensionmime'],
                                'subscribers' => 'subscribers' . $ilconfig['globalsecurity_extensionmime'],
                                'dashboard' => 'dashboard' . $ilconfig['globalsecurity_extensionmime'],
                                'compare' => 'compare' . $ilconfig['globalsecurity_extensionmime'],
                                'template' => 'template' . $ilconfig['globalsecurity_extensionmime'],
								'staffsettings' => 'settings' . $ilconfig['globalsecurity_extensionmime'],
								'users' => 'users' . $ilconfig['globalsecurity_extensionmime'],
								'shipping' => 'shipping' . $ilconfig['globalsecurity_extensionmime'],
								'consignment' => 'consignments' . $ilconfig['globalsecurity_extensionmime'],
								'catalog' => 'catalog' . $ilconfig['globalsecurity_extensionmime'],
								'listedarea' => 'listings' . $ilconfig['globalsecurity_extensionmime'],
								'buyers' => 'buyers' . $ilconfig['globalsecurity_extensionmime'],
								'mydata' => 'mycollection' . $ilconfig['globalsecurity_extensionmime'],
								'pending' => 'pendings'. $ilconfig['globalsecurity_extensionmime'],
								'track' => 'track'. $ilconfig['globalsecurity_extensionmime'],
								'deals' => 'deals'. $ilconfig['globalsecurity_extensionmime'],
								'dailydeal' => 'dailydeal'. $ilconfig['globalsecurity_extensionmime'],
								'image' => 'image'. $ilconfig['globalsecurity_extensionmime'],
								
								'denomination' => 'denomination'. $ilconfig['globalsecurity_extensionmime'],
                        );
                    
                        ($apihook = $this->api('init_configuration_start')) ? eval($apihook) : false;
                }
                unset($config);
                
                // initialize our payment module configuration
                $config = $ilance->db->query("
                        SELECT name, value
                        FROM " . DB_PREFIX . "payment_configuration
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($config) > 0)
                {
                        while ($res = $ilance->db->fetch_array($config))
                        {
                                $ilconfig[$res['name']] = $res['value'];
                        }
                        unset($res);
                }
                unset($config);
                
                $paygroups = $ilance->db->query("
                        SELECT groupname
                        FROM " . DB_PREFIX . "payment_groups
                        WHERE moduletype = 'gateway'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($paygroups) > 0)
                {
                            while ($res = $ilance->db->fetch_array($paygroups))
                            {
                                    if ($res['groupname'] == $ilconfig['use_internal_gateway'])
                                    {
                                            $v3pay['selectedmodule'] = $res['groupname'];
                                            break;
                                    }
                                    else
                                    {
                                            $v3pay['selectedmodule'] = 'none';
                                    }
                            }
                            unset($res);
                            
                            if ($v3pay['selectedmodule'] != 'none')
                            {
                                        $sql = $ilance->db->query("
                                                SELECT name, value
                                                FROM " . DB_PREFIX . "payment_configuration
                                                WHERE configgroup = '" . $ilance->db->escape_string($v3pay['selectedmodule']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql) > 0)
                                        {
                                                while ($res = $ilance->db->fetch_array($sql))
                                                {
                                                        $ilconfig[$res['name']] = $res['value'];
                                                }
                                                unset($res);
                                        }
                                        unset($sql);
                            }
                            unset($v3pay);
                }
                unset($paygroups);
       /* if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
		{
			$ilconfig['template_relativeimagepath'] = SUB_FOLDER_ROOT;
		}
		else
		{
			$ilconfig['template_relativeimagepath'] = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
		}*/
		$ilconfig['template_relativeimagepath']=HTTPS_SERVER;
                
                return $ilconfig;
        }
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>