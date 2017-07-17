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
* Template class to perform the majority of custom template operations in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class template
{
        /*
	* The ILance registry object
	*
	* @var	    $ilance
	*/
        var $registry = null;
        
        /**
        * This will store the current template into the registry
        *
        * @var array
        */
        var $templateregistry = array();
        
        /**
        * This will store the variable modifier pipe action
        *
        * @var str
        */
        var $modifierpipe = '|';
        
        /**
        * This will store the opening template variable
        *
        * @var str
        */
        var $start = '{';
        
        /**
        * This will store the closing template variable
        *
        * @var str
        */
	var $end = '}';
        
        /**
        * This will store the opening template variable for language phrases
        *
        * @var str
        */
	var $phrasestart = '{_';
        
        /**
        * This will store the closing template variable for language phrases
        *
        * @var str
        */
	var $phraseend = '}';
        
        /**
        * This will store the tags used to prevent a template from parsing phrase variables
        * within the html templates (admincp templates area, <text areas>'s etc.)
        *
        * @usage <nophraseparse>......</nophraseparse>
        * @var str
        */
        var $nophraseparse = 'nophraseparse';
        
        /**
        * This will store all current {var_names} used in a template registry
        *
        * @var array
        */
	var $var_names = array();
        var $regexp = null;
        
        /**
        * This array will store all permitted functions allowed to pass through
        * the template's <if condition=""> conditionals
        *
        * @var array
        */
        var $safe_functions = array();
        
        /**
        * This array will store all template bits for the templates
        *
        * @var array
        */
        var $templatebits = array();
        var $headerfooter = true;
        
        /*
        * Constructor
        *
        * @param       $registry	    ILance registry object
        */
        function template(&$registry)
        {
		$this->registry =& $registry;
        }
        
	/*
	* Loads a template popup into the class (does not use template skinning)
	*
        * @param       string       node
        * @param       string       filename
	*/
	function load_popup($node, $filename)
	{
                global $ilance, $myapi, $ilconfig, $v3left_nav;
                
		$this->templateregistry["$node"] = file_get_contents(DIR_TEMPLATES . $filename);
        }
        
        /*
	* Loads an AdminCP template popup into the class (does not use template skinning)
	*
        * @param       string       node
        * @param       string       filename
	*/
	function load_admincp_popup($node, $filename)
	{
		$this->templateregistry["$node"] = file_get_contents(DIR_TEMPLATES_ADMIN . $filename);
        }
	/*
	* Function alias to fetch
	*
        * @param       string       node, admin template, client template, use file path only
        * @param       string       filename
        * @param       boolean      is admin cp template
        * @param       integer      use file path only
        * @param       string       custom argument
	*/
        function load_file($node = '', $filename = '', $admin = 0, $filepathonly = '', $custom = '')
        {
		$this->fetch($node, $filename, $admin, $filepathonly, $custom);
	}
	
	/*
	* Function to fetch and load a template (client or admin) into a specific node
	*
        * @param       string       node, admin template, client template, use file path only
        * @param       string       filename
        * @param       boolean      is admin cp template
        * @param       integer      use file path only
        * @param       string       custom argument
	*/
	// arsath added new admin==2 on 18/09/2010
        function fetch($node = '', $filename = '', $admin = 0, $filepathonly = '', $custom = '')
        {
                global $ilance, $myapi, $ilconfig, $v3left_nav;
                
                if(strpos($_SERVER['SCRIPT_FILENAME'],'admincp')) 
                        $admin=1;
                elseif(strpos($_SERVER['SCRIPT_FILENAME'],'staff')) 
                        $admin=2;
                elseif(strpos($_SERVER['SCRIPT_FILENAME'],'asst1')) 
                        $admin=4;
                elseif(strpos($_SERVER['SCRIPT_FILENAME'],'asst')) 
                        $admin=3;
                


            if ($admin == 1)
            {
                                
                    $template  = file_get_contents(DIR_TEMPLATES_ADMIN . 'TEMPLATE_header.html');
                    $template .= file_get_contents(DIR_TEMPLATES_ADMIN . $filename);
                    $template .= file_get_contents(DIR_TEMPLATES_ADMIN . 'TEMPLATE_footer.html');
                    
                    $this->templateregistry["$node"] = "$template";
                    unset($template);
            }
                else if ($admin == 2)
                {       
                            $template  = file_get_contents(DIR_TEMPLATES_ADMINSTUFF . 'TEMPLATE_header.html');
                            $template .= file_get_contents(DIR_TEMPLATES_ADMINSTUFF . $filename);
                            $template .= file_get_contents(DIR_TEMPLATES_ADMINSTUFF . 'TEMPLATE_footer.html');

                            $this->templateregistry["$node"] = "$template";
                            unset($template);
                }                               
                else if ($admin == 3)                                   
                {
                            //bug1736 starts 
                            // added for asst folder by tamil on 22/08/2012                                                                     
                            $template  = file_get_contents(DIR_TEMPLATES_ADMIN_ASST . 'TEMPLATE_header.html');                                                  
                            $template .= is_file(DIR_TEMPLATES_ADMIN_ASST . $filename)?file_get_contents(DIR_TEMPLATES_ADMIN_ASST . $filename):file_get_contents(DIR_TEMPLATES_ADMINSTUFF . $filename);                                                 
                            $template .= file_get_contents(DIR_TEMPLATES_ADMIN_ASST . 'TEMPLATE_footer.html');
                            $this->templateregistry["$node"] = "$template";
                            unset($template);
                }                               
            else if ($admin == 4)                                       
                {
                        $template  = file_get_contents(DIR_TEMPLATES_ADMIN_ASST1 . 'TEMPLATE_header.html');                                                     
                $template .= is_file(DIR_TEMPLATES_ADMIN_ASST1 . $filename)?file_get_contents(DIR_TEMPLATES_ADMIN_ASST1 . $filename):file_get_contents(DIR_TEMPLATES_ADMINSTUFF . $filename);                                   
                        $template .= file_get_contents(DIR_TEMPLATES_ADMIN_ASST1 . 'TEMPLATE_footer.html');
                        $this->templateregistry["$node"] = "$template";
                        unset($template);
                }                               
            else
            {
                    $shell = 'TEMPLATE_SHELL';
                    $this->templateregistry["$shell"] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_SHELL.html');
                    
                    // #### LOAD COMMON TEMPLATES ##########################
                    
                    $this->templateregistry['TEMPLATE_headerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_headerbit.html');
                    $this->templateregistry['TEMPLATE_topnav'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_topnav.html');
                    $this->templateregistry['TEMPLATE_breadcrumbbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
                    $this->templateregistry['TEMPLATE_infobar'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
                    $this->templateregistry['TEMPLATE_footerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_footerbit.html');                        
                    $this->templateregistry['TEMPLATE_pluginheaderbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
                    $this->templateregistry['TEMPLATE_pluginfooterbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
                       
                    // #### MERGE COMMON TEMPLATES #########################
                    
                    $this->templateregistry['template'] = file_get_contents(DIR_TEMPLATES . $filename);
                    
                    $this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
                    $this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
                    $this->templateregistry["$shell"] = str_replace($this->start . 'navbar' . $this->end, $this->templateregistry['TEMPLATE_topnav'], $this->templateregistry["$shell"]);
                    $this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
                    $this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
                    $this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
                    
                    $this->templateregistry["$node"] = $this->templateregistry["$shell"];
            }
                
            $this->handle_template_hooks($node);
        }
	
        /*
	* Function for handling {apihook[xxxx]} custom html locations within the templates
	*
	* @param       string       node
	* @param       string       custom template data
	*
	* @return      string       Returns modified template with parsed template hook
	*/
	function handle_template_hooks($node = '', $customtemplate = '')
	{
                if (defined('LOCATION') AND LOCATION == 'admin' AND defined('AREA') AND AREA == 'language')
                {
                        // we do not want to parse the template apihooks in the template manager
                        return;
                }
                                                
                if (!is_object($this->registry))
                {
                        global $ilance;
                        $this->registry =& $ilance;
                }
                              
                if (!empty($customtemplate))
                {
                        $contents = $customtemplate;        
                }
                else
                {
                        $contents = $this->templateregistry["$node"];
                }
                
                if (!empty($contents))
                {
                        $pattern = '/' . $this->start . 'apihook' . '\[([\w\d_]+)\]' . $this->end . '/';
                        if (preg_match_all($pattern, $contents, $m) !== false)
                        {
                                $replaceable = array();
                                foreach ($m[1] AS $key)
                                {
                                        if (!empty($key))
                                        {
                                                // handles the replacement of the template hook
                                                $replaceable[$this->start . 'apihook' . '[' . $key . ']' . $this->end] = $this->registry->api($key);
                                        }
                                }
                                $contents = str_replace(array_keys($replaceable), array_values($replaceable), $contents);
                        }
                        
                        if (!empty($customtemplate))
                        {
                                return $contents;       
                        }
                        else
                        {
                                $this->templateregistry["$node"] = $contents;
                        }
                }
        }
        
        /*
	* Function for parsing {hash[key]} style tags for links throughout the templates by Dexter Tad-y
	*
	* @param       string       node
	* @param       array        hash names ( array('ilpage' => $ilpage) )
	* @param       integer      parse globals
	* @param       string       custom template data (optional)
	*/
	function parse_hash($node = '', $hashes, $parseglobals = 0, $data = '')
	{
                if (empty($data) OR $data == '')
                {
                        $contents = $this->templateregistry["$node"];
                }
                else 
                {
                        $contents = $data;	
                }
                
                foreach ($hashes as $hname => $hash)
                {
                        $pattern = '/' . $this->start . $hname . '\[([\w\d_]+)\]' . $this->end . '/';
                        if (preg_match_all($pattern, $contents, $m) !== false)
                        {
                                $replaceable = array();
                                foreach ($m[1] as $key)
                                {
                                        if (isset($hash["$key"]))
                                        {
                                                $replaceable[$this->start . $hname . '['.$key.']' . $this->end] = $hash["$key"];
                                        }
                                }
                                $contents = str_replace(array_keys($replaceable), array_values($replaceable), $contents);
                        }        
                }
                $this->templateregistry["$node"] = $contents;
                
                return $this->templateregistry["$node"];
	}
        
	/*
	* Compiles and produces header template for external addons or plugins
	*
        * @param       string       node
	*/
	function construct_header($node)
	{
                global $ilance, $myapi, $ilconfig, $login_include, $show;
                
                // #### LOAD COMMON TEMPLATES ##################################
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');                        
                $this->templateregistry['TEMPLATE_headerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_headerbit.html');
                $this->templateregistry['TEMPLATE_topnav'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_topnav.html');
                $this->templateregistry['TEMPLATE_breadcrumbbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
                $this->templateregistry['TEMPLATE_infobar'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
                   
                // #### MERGE COMMON TEMPLATES #################################
                
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'navbar' . $this->end, $this->templateregistry['TEMPLATE_topnav'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'login_include' . $this->end, $login_include, $this->templateregistry['TEMPLATE_pluginheaderbit']);
                
                $this->templateregistry["$node"] = $this->templateregistry['TEMPLATE_pluginheaderbit'];
                
                // #### PARSE TEMPLATE API HOOKS ###############################
                
                $this->handle_template_hooks($node);
	}
	
	/*
	* Compiles and produces footer template for external addons or plugins
	*
        * @param       string       node
	*/
	function construct_footer($node)
	{
                global $ilance, $myapi, $ilconfig, $show;
                
                // #### LOAD COMMON TEMPLATES ##################################
                
                $this->templateregistry['TEMPLATE_pluginfooterbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
                $this->templateregistry['TEMPLATE_footerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_footerbit.html');
                
                // #### MERGE COMMON TEMPLATES #################################
                
                $this->templateregistry['TEMPLATE_pluginfooterbit'] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry['TEMPLATE_pluginfooterbit']);
                $this->templateregistry["$node"] = $this->templateregistry['TEMPLATE_pluginfooterbit'];
                
                // #### PARSE TEMPLATE API HOOKS ###############################
                
                $this->handle_template_hooks($node);
	}
	
	/*
	* Function to load custom template created by an admin into the the class from the filesystem.
	*
        * @param       string       node
        * @param       string       filename
        * @param       integer      styleid
        * @param       integer      clientcp template?
	*/
	function fetch_parsed_template($node = '', $filename = '', $styleid = 0)
        {
                global $ilance, $myapi, $v3left_nav, $ilconfig;
                
                // #### LOAD COMMON TEMPLATES ##########################
                        
                $this->templateregistry['TEMPLATE_headerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_headerbit.html');
                $this->templateregistry['TEMPLATE_topnav'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_topnav.html');
                $this->templateregistry['TEMPLATE_breadcrumbbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit.html');
                $this->templateregistry['TEMPLATE_infobar'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_infobar.html');
                $this->templateregistry['TEMPLATE_footerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_footerbit.html');
                $this->templateregistry['TEMPLATE_pluginheaderbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit.html');
                $this->templateregistry['TEMPLATE_pluginfooterbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit.html');
                   
                // #### FETCH REQUESTED TEMPLATE ###############################
                             
                $template = file_get_contents(DIR_TEMPLATES . $filename . '.html');                             
                              
                $shell = 'TEMPLATE_SHELL';
		
                $this->templateregistry["$shell"] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_SHELL.html');
                $this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $template, $this->templateregistry["$shell"]);
                $this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
                $this->templateregistry["$shell"] = str_replace($this->start . 'navbar' . $this->end, $this->templateregistry['TEMPLATE_topnav'], $this->templateregistry["$shell"]);
                $this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
                $this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
                $this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
                $this->templateregistry["$node"] = $this->templateregistry["$shell"];
                
                $this->handle_template_hooks($node);
        }
        
        /*
	* Function to load template from the file system.
	*
        * @param       string           filename
        * @param       integer          use template filename commenting
	*/
	function fetch_template($filename = '', $htmlcomments = false)
        {
                global $ilance, $myapi, $ilconfig, $phrase, $v3left_nav, $cid, $ilpage, $metadescription, $metakeywords, $buildversion, $categorypopup,$motd_list, $log_red;
                
                // fetch template from file system
                $template = file_get_contents(DIR_TEMPLATES . $filename);
                $template = addslashes($template);
                
                if ($htmlcomments)
                {
                        $template = "<!-- BEGIN TEMPLATE: $filename -->\n$template\n<!-- END TEMPLATE: $filename -->";
                }
                
                // STEP 1 ##############################################
                // let's search entire template for <nophraseparse></nophraseparse> tags
                // so this function can rip those blocks out if required (before we do all phrases in template in next step)
                preg_match_all("'\<$this->nophraseparse.*\>(.*)\</$this->nophraseparse\>'isU", $template, $findregxp);
                if (!empty($findregxp[0]) AND $findregxp[0] > 0)
                {
                        for ($i = 0; $i < count($findregxp[0]); $i++)
                        {
                                $template = str_replace($findregxp[0][$i], "~~$this->nophraseparse~~$i~~$this->nophraseparse~~", $template);
                        }        
                }
                      
                // STEP 2 ##############################################
                // let's convert entire template {_phrases} into their intended output
                $phrasepattern = '/' . $this->phrasestart . '([\w\d_]+)' . $this->phraseend . '/';
                if (preg_match_all($phrasepattern, $template, $phrasematches) == true)
                {
                        $phrasematches = array_values(array_unique($this->remove_duplicate_template_variables($phrasematches[1])));
                        $replaceable = array();
                        
                        foreach ($phrasematches AS $phrasekey)
                        {
                                // parse phrase variable {_some_phrase} to Some phrase
                                $replaceable[$this->phrasestart . $phrasekey . $this->phraseend] = isset($phrase["_$phrasekey"]) ? $phrase["_$phrasekey"] : ucfirst(str_replace('_', ' ', $phrasekey));
                        }
                        
                        $template = str_replace(array_keys($replaceable), array_values($replaceable), $template);
                        unset($replaceable);
                }
                unset($phrasematches);
                
                // security token we'll be comparing our form post's to
                $token = (!empty($_COOKIE[COOKIE_PREFIX . 'token']) ? $_COOKIE[COOKIE_PREFIX . 'token'] : '');
                $sid = (!empty($_COOKIE['s']) ? $_COOKIE['s'] : session_id());
                $keywords = (!empty($keywords) ? $keywords : '');
		$last10 = (LICENSEKEY != '') ? mb_substr(LICENSEKEY, 0, 10) : '';
                $topnav_tl = $topnav_tr = $topnav_firstrow = '';
				$ilance->template_nav = construct_object('api.template_nav');
             //   $motd_list =$ilance->template_nav->construct_motd_list();
                
				//BUG 1989 * START
				
				 if(isset($ilance->GPC['searchid']))
				{
					$search_id_formatted = html_entity_decode($ilance->GPC['searchid']);							  
					$search_id_formatted = str_replace('&amp;quot;', "", $search_id_formatted);							  

				}
				
				//BUG 1989 * END
				
                // build our template bits
				
					//'pop_up_search_title' WAS ADDED TO THIS ARRAY FOR BUG 1989
                $this->templatebits = array(
                        'cid' => $cid,
                        'template_relativeimagepath' => $ilconfig['template_relativeimagepath'],
                        'template_imagesfolder' => $ilconfig['template_imagesfolder'],
                        'template_requesturi' => (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : ''),
			'template_metatitle' => (!empty($metatitle) ? $metatitle : $ilconfig['template_metatitle']),
                        'template_metadescription' => (!empty($metadescription) ? $metadescription : $ilconfig['template_metadescription']),
                        'template_metakeywords' => (!empty($metakeywords) ? $metakeywords : $ilconfig['template_metakeywords']),
			'topnav_tl' => $topnav_tl,
			'topnav_tr' => $topnav_tr,
			'topnav_firstrow' => $topnav_firstrow,
                        'site_name' => SITE_NAME,
                        'site_title' => COMPANY_NAME,
                        'site_email' => SITE_EMAIL,
                        'site_phone' => SITE_PHONE,
                        'site_address' => SITE_ADDRESS,
                        'https_server' => HTTPS_SERVER,
                        'http_server' => HTTP_SERVER,
                        'https_server_admin' => HTTPS_SERVER_ADMIN,
                        'http_server_admin' => HTTP_SERVER_ADMIN,
			'q' => (isset($ilance->GPC['q']) ? handle_input_keywords($ilance->GPC['q']) : ''),
			'pop_up_search_title' => (isset($ilance->GPC['searchid']) ? '' : (isset($ilance->GPC['q'])?handle_input_keywords($ilance->GPC['q']):'')),
			'keywords' => $keywords,
                        's' => $sid,
                        'token' => $token,
                        'last10' => $last10,
			'buildversion' => $buildversion,
			'categorypopup' => isset($categorypopup) ? $categorypopup : '',
			'red' => $log_red,
			'motd_list' => isset($motd_list) ? $motd_list : '',
			'year' => date('Y')
                );
                
                ($apihook = $ilance->api('fetch_template_templatebits')) ? eval($apihook) : false;
                
                foreach ($this->templatebits AS $name => $value)
                {
                        // find all occurrences of {template_variables}
                        if (is_int(mb_strpos($template, $this->start . $name . $this->end)) == true)
                        {
                                $template = str_replace($this->start . $name . $this->end, $value, $template);
                                unset($name);
                        }
                }
                unset($this->templatebits);
                
                // STEP 3 ##############################################
                // let's piece back together the template tags used to filter out parsing of phrases
                if (!empty($findregxp[0]) AND $findregxp[0] > 0)
                {
                        for ($i = 0; $i < count($findregxp[0]); $i++)
                        {
                                $template = str_replace("~~$this->nophraseparse~~$i~~$this->nophraseparse~~", $findregxp[0][$i], $template);
                        }
                }
                unset($findregxp);
                
                return $template;
        }
	
	/*
	* Function to set template variable identifiers such as "{" and "}"
	*
        * @param       string           starting tag
        * @param       string           ending tag
	*/
	function set_identifiers($start, $end)
	{
		$this->start = $start;
		$this->end = $end;
	}
	
	/*
	* Function to include another file. eg. A header/footer.
	*
        * @param       string           node
        * @param       string           filename
	*/
	function include_file($node, $filename)
	{
		if (file_exists(DIR_TEMPLATES . $filename))
		{
			$include = file_get_contents(DIR_TEMPLATES . $filename);
		}
		else
                {
                        $include = 'Requested template: "' . $filename . '" does not exist.';
                }
                
                $tag = mb_substr($this->templateregistry["$node"], mb_strpos(mb_strtolower($this->templateregistry["$node"]), '<include filename="' . $filename . '">'), mb_strlen('<include filename="' . $filename . '">'));
                
                $this->templateregistry["$node"] = str_replace($tag, $include, $this->templateregistry["$node"]);
	}
	/*
	* Function for parsing a <loop name="xxx">yyy</loop name="xxx"> HTML template tag
	*
        * @param       string           node
        * @param       mixed            loop identifier variable (can be single variable or array variable)
	*/
	function parse_loop($node, $array_name)
	{
		// #### handle single variable we're requesting ################
		if (isset($array_name) AND !is_array($array_name))
		{
			global ${$array_name};
			
			$loop_code = '';
			
			$start_pos = strpos(strtolower($this->templateregistry["$node"]), '<loop name="' . $array_name . '">') + strlen('<loop name="' . $array_name . '">');
			$end_pos = strpos(strtolower($this->templateregistry["$node"]), '</loop name="' . $array_name . '">');
			
			$loop_code = substr($this->templateregistry["$node"], $start_pos, $end_pos - $start_pos);
			
			$start_tag = substr($this->templateregistry["$node"], strpos(strtolower($this->templateregistry["$node"]), '<loop name="' . $array_name . '">'), strlen('<loop name="' . $array_name . '">'));
			$end_tag = substr($this->templateregistry["$node"], strpos(strtolower($this->templateregistry["$node"]), '</loop name="' . $array_name . '">'), strlen('</loop name="' . $array_name . '">'));
			
			if (!empty($loop_code))
			{
				$new_code = '';
				
				$num = count(${$array_name});
				for ($i = 0; $i < $num; $i++)
				{
					$temp_code = $loop_code;
					if ((!empty(${$array_name}[$i]) AND is_array(${$array_name}[$i]) OR !empty(${$array_name}[$i]) AND is_object(${$array_name}[$i])))
					{
						foreach (${$array_name}[$i] AS $key => $value)
						{
							// we expect only strings here
							if (isset($value) AND !is_array($value))
							{
								$temp_code = str_replace($this->start . $key . $this->end, ${$array_name}[$i][$key], $temp_code);
							}
						}
						
						$new_code .= $temp_code;
					}
				}
				
				$this->templateregistry["$node"] = str_replace($start_tag . $loop_code . $end_tag, $new_code, $this->templateregistry["$node"]);
			}	
		}
		
		// #### handle multiple array of variables we're requesting ####
		else if (isset($array_name) AND is_array($array_name))
		{
			$temparray = $array_name;
			unset($array_name);
			
			foreach ($temparray AS $array_name)
			{
				global ${$array_name};
				
				$loop_code = '';
				
				$start_pos = strpos(strtolower($this->templateregistry["$node"]), '<loop name="' . $array_name . '">') + strlen('<loop name="' . $array_name . '">');
				$end_pos = strpos(strtolower($this->templateregistry["$node"]), '</loop name="' . $array_name . '">');
				
				$loop_code = substr($this->templateregistry["$node"], $start_pos, $end_pos - $start_pos);
				
				$start_tag = substr($this->templateregistry["$node"], strpos(mb_strtolower($this->templateregistry["$node"]), '<loop name="' . $array_name . '">'), strlen('<loop name="' . $array_name . '">'));
				$end_tag = substr($this->templateregistry["$node"], strpos(mb_strtolower($this->templateregistry["$node"]), '</loop name="' . $array_name . '">'), strlen('</loop name="' . $array_name . '">'));
				
				if (!empty($loop_code))
				{
					$new_code = '';
					
					$num = count(${$array_name});
					for ($i = 0; $i < $num; $i++)
					{
						$temp_code = $loop_code;
						if ((!empty(${$array_name}[$i]) AND is_array(${$array_name}[$i]) OR !empty(${$array_name}[$i]) AND is_object(${$array_name}[$i])))
						{
							foreach (${$array_name}[$i] AS $key => $value)
							{
								// we expect only strings here
								if (isset($value) AND !is_array($value))
								{
									$temp_code = str_replace($this->start . $key . $this->end, ${$array_name}[$i][$key], $temp_code);
								}
							}
							
							$new_code .= $temp_code;
						}
					}
					
					$this->templateregistry["$node"] = str_replace($start_tag . $loop_code . $end_tag, $new_code, $this->templateregistry["$node"]);
				}
			}
		}
	}
        /*
        * Function to display error message based on unaccepted functions used within a if condition in a template
        *
        * @param       string           function name being used
        */
        function unsafe_precedence($fn = '')
        {
                echo '<strong>Fatal:</strong> callback if condition function <strong>' . $fn . '()</strong> is not in the safe functions list. Please remove this if condition expression from the template ASAP.';
                return 'false';
        }
    
        /*
        * Function to handle the regular expressions used within the if condition parser
        *
        * @param       string           template content
        */
        function pr_callback($string = '')
        {		
                global $ilance, $nothing_to_parse, $else_error;
		
                $else_error = $nothing_to_parse = 0;
		
                $safe_functions = array(
			'in_array',
			'is_array',
			'is_numeric',
			'function_exists',
			'isset',
			'empty',
			'defined',
			'array',
			'extension_loaded',
			'can_display_financials',
                        'check_access',
			'is_awarded',
                        'is_bid_placed',
                        'is_subscription_permissions_ready',
                        'is_profile_cat_prepared',
                        'has_winning_bidder',
                        'has_highest_bidder',
			'can_display_element'
		);
                
                // used to allow developers to add more functions to the list above
                ($apihook = $ilance->api('template_pr_callback_start')) ? eval($apihook) : false;
                
                $this->safe_functions = $safe_functions;
    
                $string = substr($string, strpos($string, 'condition'));
                preg_match("/condition=([\"'])((?:(?!\\1).)*)\\1/is", $string, $condition);
    
                // find yes code start and end, and start of no-code we need tag ending position - so dont take into account anything between '"' and "'"
                $quotepos = $quotepos2 = $pos = 0;
                
                while (true)
                {
                        $endpos = strpos($string, '>', $pos);
                        
                        if ($quotepos !== false)
                        {
                                $quotepos = strpos($string, '"', $pos);
                        }
                        
                        if ($quotepos2 !== false)
                        {
                                $quotepos2 = strpos($string, "'", $pos);
                        }
                        
                        if (($quotepos < $endpos AND $quotepos !== false) OR ($quotepos2 < $endpos AND $quotepos2 !== false))
                        {
                                if (($quotepos < $quotepos2 OR $quotepos2 === false) AND $quotepos !== false)
                                {
                                        // we have " - quotes here	
                                        $quotepos = strpos($string, '"', $quotepos + 1);
                                        
                                        if ($quotepos !== false)
                                        $pos = $quotepos + 1;
                                        
                                        // back to top of the loop and search for endpos again
                                        continue;
                                }
                                
                                if (($quotepos2 < $quotepos OR $quotepos === false) AND $quotepos2 !== false)
                                {
                                        // we have ' - quotes here
                                        $quotepos2 = strpos($string, "'", $quotepos2 + 1);
                                        
                                        if ($quotepos2 !== false)
                                        $pos = $quotepos2 + 1;
                                        
                                        // back to top of the loop and search for endpos again
                                        continue;
                                }
                        }
                        
                        if (($quotepos === false OR $quotepos > $endpos) AND ($quotepos2 === false OR $quotepos2 > $endpos))
                        {
                                $pos = $endpos;
                                break;	
                        }
                        
                        if ($endpos === false)
                        {
                                $pos = $endpos;
                        }
                }
            
                // from end of the if tag ( '>' char +1)
                $string = substr($string, $pos + 1);
                
                // from end of the if tag ( '>' char +1)
                // now we have inner content only
                $string = substr($string, 0, strrpos($string, '<'));
                $iels = strpos($string, '<else />');
                
                $no_start = $yes_end = false;
                $pos = -1;
                $level = 0;
                
                while ($iels !== false)
                {
                        $is = strpos($string, '<if ', $pos + 1);
                        $ie = strpos($string, '</if>', $pos + 1);
                        //$ie = strpos($string, '</if', $pos + 1);
                        $iels = strpos($string, '<else />', $pos + 1);	
                        
                        // we have found our else
                        if (($is > $iels OR $is === false) AND ($ie > $iels OR $ie === false) AND $level == 0)
                        {
                                if ($iels !== false)
                                {
                                        $yes_end = strpos($string, '<else />', max($pos,0));
                                        $no_start = $yes_end + strlen('<else />');
                                }
                                break;
                        }
                        
                        if (($is < $ie AND $is !== false) OR ($is !== false AND $ie === false))
                        {
                                $level++;
                                $pos = $is;	
                        }
                        
                        if (($is > $ie AND $ie !== false) OR ($is === false AND $ie !== false))
                        {
                                $level--;
                                $pos = $ie;	
                        }
                }
                    
                if ($yes_end === false)
                {
                        $no_start = false;
                        
                        // find end of this </if tag
                        $yes_end = strlen($string);	
                }
            
                $yes_code = substr($string, 0, $yes_end);
                    
                // find no-code
                $no_code = '';
                if ($no_start !== false)
                {
                        $no_end = strlen($string);
                        $no_code = substr($string, $no_start, ($no_end - $no_start));
                }
                
                // if condition has else code
                $possible_elsif = isset($yes_no_code[1]) ? $yes_no_code[1] : '';
                $condition = isset($condition[2]) ? $condition[2] : '';
		
//                $condition = preg_replace("/(([a-z_][a-z_0-9]*)\\(.*?\\))/ie","in_array(strtolower('\\2'), \$safe_functions) ? '\\1' : \$this->unsafe_precedence('\\2')", $condition);
//                $condition = preg_replace("/\\$([a-z][a-z_0-9]*)/is", "\$GLOBALS['\\1']", $condition);
                $condition = preg_replace_callback("/(([a-z_][a-z_0-9]*)\\(.*?\\))/i",'self::cb',$condition);
                $condition = preg_replace("/\\$([a-z][a-z_0-9]*)/is", "\$GLOBALS['\\1']", $condition);
                             
                //DEBUG("$condition", 'TEMPLATE_CONDITIONAL');
                
                if (eval("return ($condition);"))
                {
			return $yes_code;
                }
                else
                {
			return $no_code;
                }
        }
        function cb($match)
        {
                //var_dump($match);
                $safe_functions = array(
                        'in_array',
                        'is_array',
                        'is_numeric',
                        'function_exists',
                        'isset',
                        'empty',
                        'defined',
                        'array',
                        'extension_loaded',
                        'can_display_financials',
                        'check_access',
                        'is_awarded',
                        'is_bid_placed',
                        'is_subscription_permissions_ready',
                        'is_profile_cat_prepared',
                        'has_winning_bidder',
                        'has_highest_bidder',
                        'can_display_element'
                );
                return in_array(strtolower($match[2]), $safe_functions) ? $match[1] : $this->unsafe_precedence($match[2]);
        }
        /*
        * Functions for returning <if condition=""> errors
        *
        * @param       void
        */
        function report_if_error($html = '', $if_pos = 0, $ending = false)
        {
                $start = $if_pos;
                $end = strpos($html, '>', $if_pos);
                
                if ($ending)
                {
                        // nothing to do
                }
                else
                {
                        // get if condition
                        $start = $if_pos;
                        $end = strpos($html, '>', $if_pos);
                        
                        if ($end === false)
                        {
                                $start = strpos($html, '"', $if_pos);
                                $end = strpos($html, '"', $start + 1);
                                $start2 = strpos($html, "'", $if_pos);
                                $end2 = strpos($html, "'", $start + 1);
    
                                // choose quote type that if condition is enclosed in
                                if (($start2 < $start AND $start2 !== false) OR $start === false)
                                {
                                        $start = $start2;
                                        $end = $end2;
                                }
                        }
                }
                
                $if_cond = '';
                if ($start !== false AND $end !== false)
                {
                        $if_cond = substr($html, $start, $end - $start + 1);
                }
                else
                {
                        $if_cond = 'unknown';
                }
                
                $style = "<style>.code {margin: 0px 0px 0px 0px;width: 100%;font-family: monospace;font-size: 13px;color:#000000;background-color:#fff; cursor: crosshair;}</style>";
                if ($ending)
                {
                        // if tag without ending
                        //echo $style . '<strong>Fatal:</strong> no ending &lt;/if&gt; found for: ' . htmlentities(stripslashes($if_cond)) . '<br><br>HTML code: <pre class="code">'.htmlentities(mb_substr(stripslashes($html), $if_pos, 400)).'</pre>';
                        echo $style . '<strong>Fatal:</strong> no ending &lt;/if&gt; found for: ' . ilance_htmlentities(stripslashes($if_cond)) . '<br><br>HTML code: <pre class="code">'.ilance_htmlentities(substr(stripslashes($html), $if_pos, 400)).'</pre>';
                }
                else
                {       // if tag without ending
                        //echo $style . '<strong>Fatal:</strong> no starting &lt;if condition&gt; tag for ending ' . htmlentities(stripslashes($if_cond)) . ' tag!<br><br>HTML code: <pre class="code">'.htmlentities(mb_substr(stripslashes($html), $if_pos, 400)).'</pre>';
                        echo $style . '<strong>Fatal:</strong> no starting &lt;if condition&gt; tag for ending ' . ilance_htmlentities(stripslashes($if_cond)) . ' tag!<br><br>HTML code: <pre class="code">'.ilance_htmlentities(substr(stripslashes($html), $if_pos, 400)).'</pre>';
                }
        }
        /*
        * Functions for parsing <if condition="">xxx<else />yyy</if> template conditionals
        *
        * @param       string       node
        * @param       string       template data (optional)
        * @param       boolean      apply slashes to template string/data (default false)
        */
        function parse_if_blocks($node = '', $content = '', $addslashes = false)
        {
                global $nothing_to_parse, $else_error;
                
                $template_str = (empty($content)) ? $this->templateregistry["$node"] : $content;
                // simple support for </if name= & </if condition= closing tags
                $pos = $opening_tags = $level = 0;
                $start = $start2 = -1;
                while (true)
                {
                        $pos = strpos($template_str, '</if ');
                        $end = strpos($template_str, '>', $pos);
                        
                        if ($end === false)
                        {
                                echo '<strong>Warning:</strong> &lt;/if&gt; tag not closed within template!';
                                break;
                        }                        
                        if ($pos === false)
                        {
                                break;	
                        }
                        
                        $template_str = substr($template_str, 0, $pos) . '</if>' . substr($template_str, $end + 1);        	
                }
		
                while (true)
                {
                        $start2 = strpos($template_str, '<if ', $start + 1);
                        
                        if ($start2 !== false)
                        {
                                $end = strpos($template_str, '</if>', $start + 1);
                        }
                        else
                        {
                                break;
                        }
			
                        $start = $start2;
			
                        if ($end === false)
                        {
                                echo $this->report_if_error($template_str, $start, true);
                        }
                        if ($start > $end)
                        {
                                echo $this->report_if_error($template_str, $end);
                        }
        
                        // start processing if conditional block!
                        $end = $start - 1;	
                        while (true)
                        {
                                $is = strpos($template_str, '<if ', $end + 1);
                                $ie = strpos($template_str, '</if>', $end + 1);
                                
                                if (($is < $ie AND $is !== false) OR ($is !== false AND $ie === false))
                                {
                                        $level++;
                                        $end = $is;	
                                }
                                
                                if (($is > $ie AND $ie !== false) OR ($is === false AND $ie !== false))
                                {
                                        $level--;
                                        $end = $ie;	
                                }
                                
                                if ($ie === false AND $is === false AND $level != 0)
                                {
                                        $end = false;
                                        break;	
                                }
                                
                                if ($level == 0 AND ($ie < $is OR $is === false))
                                {
                                        $end = $ie;
                                        break;
                                }
                        }
                        
                        if ($end === false)
                        {
                                $this->report_if_error($template_str, $start, true);
                        }
                        
                        if ($start < $end)
                        {
                                $a = substr($template_str, 0, $start);
                                $b = substr($template_str, $end + 5);
                                $c = $this->pr_callback(stripslashes(substr($template_str, $start, $end - $start + 5)));
                                
                                $template_str = ($addslashes) ? $a . addslashes($c) . $b : $a . $c . $b;
                                $start = -1;
                        }
                }
                
                if (empty($content))
                {
			$this->templateregistry["$node"] = $template_str;
                }
                else
                {
			return $template_str;
                }
	}
        /*
	* Function is used only by the register_template_variables() method, for going through arrays and extracting the values.
	*
        * @param       string           node
        * @param       array            array of variable names
	*/
	function traverse_array($node = '', $array)
	{
		while (list(,$value) = each($array))
		{
			if (is_array($value))
			{ 
				$this->traverse_array($node, $value);
			}
			else 
			{
				$this->var_names["$node"][] = $value;
			}
		}
	}
        
	/*
	* Function to register template variables and assigns them to $this->var_names
	*
        * @param       string           node
        * @param       array            variable names
	*/
	function register_template_variables($node = '', $vars)
	{
		if (!empty($vars) AND is_array($vars))
		{
                        $this->traverse_array($node, $vars);
		}
		else if (!empty($vars))
		{
			if (is_long(mb_strpos($vars, ',')) == true)
			{
				$vars = explode(',', $vars);
				for (reset($vars); $current = current($vars); next($vars))
                                {
                                        $this->var_names["$node"][] = $current;
                                }
			}
			else
			{
                                $this->var_names["$node"][] = $vars;
			}
		}
	}
        
        /*
        * Function to remove duplicate values in an array
        *
        * @param       array           array of values
        */
        function remove_duplicate_template_variables($array)
        {
                $newarray = array();
                if (is_array($array))
                {
                        foreach($array as $key => $val)
                        {
                                if (is_array($val))
                                {
                                        $val2 = $this->remove_duplicate_template_variables($val);
                                }
                                else
                                {
                                        $val2 = $val;
                                        $newarray = array_unique($array);
                                        break;
                                }
                                if (!empty($val2))
                                {
                                        $newarray["$key"] = $val2;
                                }
                        }
                }
                
                return $newarray;
        }        
		function get_seo_items()
		{
		 global $ilance;
		 $page_name=$ilance->escape_string(addslashes(substr($_SERVER['REQUEST_URI'],1,strlen($_SERVER['REQUEST_URI']))));
		 $query=$ilance->db->query("select url_title,url_description,url_keyword from ".DB_PREFIX."seo where page='".$page_name."' or seo_url='".HTTP_SERVER.$page_name."' or seo_url='".HTTPS_SERVER.$page_name."' limit 1", 0,null, __FILE__, __LINE__);
		 
		 if($ilance->db->num_rows($query))
		 {
		 while($line=$ilance->db->fetch_array($query))
		 {
		 
		 return $line;
		 }
		 }
		 
		}
        /*
        * Function to parse template variables within a template
        *
        * @param       node           template node
        */                
        function parse_template_variables($node = '')
	{
                global $ilance, $myapi, $phrase, $area_title, $page_title, $templatevars, $templatebits, $breadcrumbtrail, $breadcrumbfinal, $navcrumb, $iltemplate, $headinclude, $breadcrumb, $onload, $official_time, $v3left_nav, $v3left_storenav, $ilconfig, $ilpage, $newpmbpopupjs, $show, $cid, $metadescription, $metakeywords, $keywords, $buildversion, $categorypopup,$motd_list, $log_red;
                
                ($apihook = $ilance->api('parse_template_variables_start')) ? eval($apihook) : false;
                
                $ilance->template_nav = construct_object('api.template_nav');
                        
                // #### PARSE PHRASE VARIABLES #################################
                if (!empty($phrase))
                {
                        // STEP 1 ##############################################
                        // let's search template for <nophraseparse></nophraseparse> tags
                        // so this function can rip those blocks out if required (before we do all phrases in template in next step)                       
                        preg_match_all("'\<$this->nophraseparse\>(.*)\</$this->nophraseparse\>'isU", $this->templateregistry["$node"], $findregxp);
                        if (!empty($findregxp[0]) AND $findregxp[0] > 0)
                        {
                                for ($i = 0; $i < count($findregxp[0]); $i++)
                                {
                                        $this->templateregistry["$node"] = str_replace($findregxp[0]["$i"], "~~$this->nophraseparse~~$i~~$this->nophraseparse~~", $this->templateregistry["$node"]);
                                }        
                        }
                              
                        // STEP 2 ##############################################
                        // let's convert template {_phrases} into their intended output                        
                        $phrasepattern = '/' . $this->phrasestart . '([\w\d_]+)' . $this->phraseend . '/';
                        if (preg_match_all($phrasepattern, $this->templateregistry["$node"], $phrasematches))
                        {
                                $phrasematches = array_values(array_unique($this->remove_duplicate_template_variables($phrasematches[1])));
                                $replaceable = array();
                                
                                foreach ($phrasematches AS $phrasekey)
                                {
                                        //$replaceable[$this->phrasestart . $phrasekey . $this->phraseend] = isset($phrase["_$phrasekey"]) ? $phrase["_$phrasekey"] : ucfirst(str_replace('_', ' ', $phrasekey));
					$replaceable[$this->phrasestart . $phrasekey . $this->phraseend] = isset($phrase["_$phrasekey"]) ? stripcslashes($phrase["_$phrasekey"]) : $this->phrasestart . $phrasekey . $this->phraseend;
                                }
                                
                                $this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
                                unset($replaceable);
                        }
                        unset($phrasematches);
                        
                        // STEP 3 ##############################################
                        // let's parse our left nav and other {template_vars}
                        
                        // #### LEFT NAV LOGIC #################################
                        if (!empty($v3left_nav))
                        {
                                // load defined left nav (categories and legend perhaps)
                                $leftnav = $v3left_nav;
                        }
                        else
                        {
                                $leftnav = '';
                                if (empty($show['leftnav']) OR isset($show['leftnav']) AND $show['leftnav'])
                                {
                                        // load xml client cp left nav (member links)
                                        $leftnav = $ilance->template_nav->construct_nav($_SESSION['ilancedata']['user']['slng'], 'client');
                                }
                        }
                        
                        // #### NEW PMB JAVASCRIPT POPUP #######################
                        if (defined('LOCATION') AND LOCATION != 'messages' AND LOCATION != 'login' AND LOCATION != 'registration' AND !empty($_SESSION['ilancedata']['user']['userid']))
                        {
                              
                        }
                        $topnav = $ilance->template_nav->construct_nav($_SESSION['ilancedata']['user']['slng'], 'client_topnav');
//                       $motd_list =$ilance->template_nav->construct_motd_list();
                        
                        // security token we'll be comparing our form post's to
                        $token = (!empty($_COOKIE[COOKIE_PREFIX . 'token']) ? $_COOKIE[COOKIE_PREFIX . 'token'] : '');
                        $sid = (!empty($_COOKIE['s']) ? $_COOKIE['s'] : session_id());
                        $keywords = (!empty($keywords) ? $keywords : '');
			$last10 = (LICENSEKEY != '') ? mb_substr(LICENSEKEY, 0, 10) : '';
                        $topnav_tl = $topnav_tr = $topnav_firstrow = '';
						$seo_items=$this->get_seo_items();
						
						if(count($seo_items)>0)
						{
							$page_title=$seo_items['url_title'];
							$metadescription=$seo_items['url_description'];
							$metakeywords=$seo_items['url_keyword'];
						}
/*
 'template_ilversion' => $ilconfig['current_version'],
                                'template_languagepulldown' => $ilance->language->print_language_pulldown($_SESSION['ilancedata']['user']['languageid'], 1),
                                'template_stylepulldown' => $ilance->styles->print_styles_pulldown($_SESSION['ilancedata']['user']['styleid'], 1),
*/
						
						//BUG 1989 * START
				
						 if(isset($ilance->GPC['searchid']))
						{
							$search_id_formatted = html_entity_decode($ilance->GPC['searchid']);							  
							$search_id_formatted = str_replace('&amp;quot;', "", $search_id_formatted);							  

						}
						
						//BUG 1989 * END
						
						//'pop_up_search_title' WAS ADDED TO THIS ARRAY FOR BUG 1989
						
                        $this->templatebits = array(
                                'cid' => $cid,
                                'template_charset' => $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset'],
                                'template_languagecode' => $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['languageiso'],
                                'template_textdirection' => $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['textdirection'],
                                'template_relativeimagepath' => $ilconfig['template_relativeimagepath'],
                                'template_htmldoctype' => (isset($ilconfig['template_htmldoctype']) ? $ilconfig['template_htmldoctype'] : ''),
                                'template_htmlextra' => (isset($ilconfig['template_htmlextra']) ? $ilconfig['template_htmlextra'] : ''),
                                'template_requesturi' => (isset($_SERVER['PHP_SELF']) ? strip_tags(ilance_htmlentities($_SERVER['PHP_SELF'])) : ''),
				'template_metatitle' => (!empty($metatitle) ? $metatitle : $ilconfig['template_metatitle']),
                                'template_metadescription' => (!empty($metadescription) ? $metadescription : $ilconfig['template_metadescription']),
                                'template_metakeywords' => (!empty($metakeywords) ? $metakeywords : $ilconfig['template_metakeywords']),
				'topnav_tl' => $topnav_tl,
				'topnav_tr' => $topnav_tr,
				'topnav_firstrow' => $topnav_firstrow,
                                'official_time' => $ilconfig['official_time'],
                                'distanceformula' => $ilconfig['globalserver_distanceformula'],            
                                'headinclude' => (isset($headinclude) ? $headinclude : ''),
                                'onload' => (isset($onload) ? $onload : ''),
                                'area_title' => (isset($area_title) ? $area_title : ''),
                                'page_title' => (isset($page_title) ? $page_title : ''),
                                'company_name' => COMPANY_NAME,
                                'site_name' => SITE_NAME,
                                'site_email' => SITE_EMAIL,
                                'site_phone' => SITE_PHONE,
                                'site_address' => SITE_ADDRESS,
                                'https_server' => HTTPS_SERVER,
                                'http_server' => HTTP_SERVER,
                                'https_server_admin' => HTTPS_SERVER_ADMIN,
                                'http_server_admin' => HTTP_SERVER_ADMIN,
                                'v3left_nav' => isset($leftnav) ? $leftnav : '',
                                'v3left_storenav' => (isset($v3left_storenav) ? $v3left_storenav : ''),
                                'members_online' => members_online(),
                                'new_pmb_popup_js' => (isset($newpmbpopupjs) ? $newpmbpopupjs : ''),
                                'rand()' => rand(1, 999999),
                                'topnav_menu_links' => isset($topnav) ? $topnav : '',
				'q' => (isset($ilance->GPC['q']) ? handle_input_keywords($ilance->GPC['q']) : ''),
				'pop_up_search_title' => (isset($ilance->GPC['searchid']) ? fetch_pop_up_search_title(intval($search_id_formatted)) : handle_input_keywords(isset($ilance->GPC['q'])?$ilance->GPC['q']:'')),
				'keywords' => $keywords,
                                's' => $sid,
                                'token' => $token,
                                'pageurl' => PAGEURL,
                                'pageurl_urlencoded' => urlencode(PAGEURL),
                                'currencysymbol' => $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'],
                                'last10' => $last10,
				'buildversion' => $buildversion,
				'categorypopup' => isset($categorypopup) ? $categorypopup : '',
				'red' => $log_red,
				'motd_list' => isset($motd_list) ? $motd_list : '',
				'year' => date('Y'),
                        );
                        unset($doctypeinfo);
                        
                        ($apihook = $ilance->api('parse_template_variables_templatebits')) ? eval($apihook) : false;
                        
                        // merge our new template bits into existing template variable array
                        $iltemplate = array_merge($templatevars, $this->templatebits);
                        foreach ($iltemplate AS $name => $value)
                        {
                                // find all occurrences of {template_variables}
                                if (is_int(mb_strpos($this->templateregistry["$node"], $this->start . $name . $this->end)) == true)
                                {
                                        $this->templateregistry["$node"] = str_replace($this->start . $name . $this->end, $value, $this->templateregistry["$node"]);
                                        unset($name);
                                }
                        }
                        unset($iltemplate, $templatevars, $this->templatebits);
                        
                        // STEP 4 ##############################################
                        // let's piece back together the template tags used to filter out parsing of phrases
                        if (!empty($findregxp[0]) AND $findregxp[0] > 0)
                        {
                                // finally replace all <nophraseparse>xx</nophraseparse> with just xx
                                for ($i = 0; $i < count($findregxp[0]); $i++)
                                {
                                        $findregxp[0]["$i"] = str_replace("<$this->nophraseparse>", '', $findregxp[0]["$i"]);
                                        $findregxp[0]["$i"] = str_replace("</$this->nophraseparse>", '', $findregxp[0]["$i"]);
                                }
                                
                                for ($i = 0; $i < count($findregxp[0]); $i++)
                                {
					$this->templateregistry["$node"] = str_replace("~~$this->nophraseparse~~$i~~$this->nophraseparse~~", $findregxp[0]["$i"], $this->templateregistry["$node"]);
                                }
                                
                        }
                        unset($findregxp);
                        
                        ($apihook = $ilance->api('parse_template_variables_end')) ? eval($apihook) : false;
                }
	}
        
        /*
	* Function for reading and parsing the template's special tags/variables.
        * Now checks for <include filename=""> tags and executes include_file()
	*
        * @param       string
	*/
	function parse_template($node = '')
	{
		$nodes = explode(',', $node);
		for (reset($nodes); $node = trim(current($nodes)); next($nodes))
		{
                        // do we have any included templates to call?
			while (is_long($pos = mb_strpos(mb_strtolower($this->templateregistry["$node"]), '<include filename="')))
			{
				$pos += 19;
				$endpos = mb_strpos($this->templateregistry["$node"], '">', $pos);
				$filename = mb_substr($this->templateregistry["$node"], $pos, $endpos - $pos);
                                
				$this->include_file($node, $filename);
			}
                        
                        $this->parse_session_globals($node);
                        $this->parse_api_globals($node);
                        $this->parse_template_variables($node);
			
			if (isset($this->var_names["$node"]) OR !empty($this->var_names["$node"]))
			{
                                $nodecount = count($this->var_names["$node"]);
				for ($i = 0; $i < $nodecount; $i++)
				{
					$temp_var = $this->var_names["$node"]["$i"];
                                        
                                        // handle special template variable tags
                                        if (is_int(mb_strpos($this->templateregistry["$node"], $this->start . $temp_var . $this->end)) == true)
					{
						global ${$temp_var};
						if (!is_array(${$temp_var}))
						{
							$this->templateregistry["$node"] = str_replace($this->start . $temp_var . $this->end, ${$temp_var}, $this->templateregistry["$node"]);
							unset($temp_var, $this->var_names["$node"][$i]);
						}
					}
                                        else
                                        {
                                                unset($temp_var, $this->var_names["$node"][$i]);
                                        }
				}
			}
		}
	}
        
        /*
        * Function to parse template collapsables
        *
        * @param       node            template node
        */
        function parse_template_collapsables($node = '')
        {
                global $ilcollapse, $ilconfig;
                /*
                * Usage:
                * <a href="javascript:void(0)" onclick="return toggle('expert_{user_id}');"><img id="collapseimg_expert_{user_id}" src="{template_relativeimagepath}{template_imagesfolder}expand{collapse[collapseimg_expert_{user_id}]}.gif" border="0" alt=""></a>
                * <tbody id="collapseobj_expert_{user_id}" style="{collapse[collapseobj_expert_{user_id}]}">
                */
		//print_r($ilcollapse);
                if (!empty($ilcollapse))
                {
                        foreach ($ilcollapse AS $key => $value)
                        {
                                $replaceable = array();
                                $replaceable[$this->start . 'collapse[' . $key . ']' . $this->end] = $value;
                                $this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
                        }
                }
    
                // find all occurrences of {collapse[XXXXX]}
                $cname = 'collapse';
                $pattern = '/' . $this->start . $cname . '\[([\w\d_]+)\]' . $this->end . '/';
                if (preg_match_all($pattern, $this->templateregistry[$node], $m) !== false)
                {
                        $replaceable = array();
                        foreach ($m[1] AS $key)
                        {
                                $replaceable[$this->start . $cname . '[' . $key . ']' . $this->end] = '';
                        }                        
                        $this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
                }
        }
        
        /*
	* Function for printing the compiled templates to the web browser.
	*
	* @param       string
	*/
	function print_parsed_template($node = '')
	{
                global $ilconfig, $ilance, $ilcollapse, $navcrumb, $phrase;
                
                // handle template collapsable javascript buttons
                $this->parse_template_collapsables($node);
                
                // construct breadcrumb trail
                $navcrumb = $this->construct_breadcrumb($navcrumb);
                
                // prevents breadcrumbs from preparsing during common template update
                if (defined('LOCATION') AND LOCATION != 'admin')
                {
                        //echo $navcrumb['breadcrumbfinal'];
                        $navcrumb['breadcrumbfinal'] = str_replace('$', '\$', $navcrumb['breadcrumbfinal']);
                        $navcrumb['breadcrumbtrail'] = str_replace('$', '\$', $navcrumb['breadcrumbtrail']);
                        
			// find all occurrences of {breadcrumbtrail} and {breadcrumbfinal}
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbtrail}/si", "$navcrumb[breadcrumbtrail]", $this->templateregistry["$node"]);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbfinal}/si", "$navcrumb[breadcrumbfinal]", $this->templateregistry["$node"]);
                }
                
		// #### white space HTML stripper ##############################
		if ($ilconfig['globalfilters_whitespacestripper'])
		{
			$pattern = '/(?:(?<=\>)|(?<=\/\>))(\s+)(?=\<\/?)/';
			$this->templateregistry["$node"] = preg_replace("$pattern", "", $this->templateregistry["$node"]);
			echo $this->templateregistry["$node"];
		}
		else
		{
			echo $this->templateregistry["$node"];
		}
                if (defined('DB_EXPLAIN') AND DB_EXPLAIN)
                {
			echo $ilance->db->explain;
                }
                
                if (defined('DEBUG_FOOTER') AND DEBUG_FOOTER AND isset($node) AND ($node == 'footer' OR $node == 'popupfooter' OR $node == 'main'))
                {
                        $ta = $ta2 = '';
                        foreach ($GLOBALS['DEBUG']['FUNCTION'] AS $key => $value)
                        {
                                $ta .= "$key : $value\n";
                        }
			foreach ($GLOBALS['DEBUG']['CLASS'] AS $key => $value)
                        {
                                $ta2 .= "$key : $value\n";
                        }
                        
                        echo "<div align=\"center\" style=\"padding-top:5px; padding-bottom:20px\"><textarea style=\"width:98%; height:150px; border:1px inset; background-color:#000; color:#fff\">FUNCTIONS:\n\n$ta\nCLASSES:\n\n$ta2</textarea></div>";
                        //print_r($GLOBALS['DEBUG']);
                }
	}
        
	/*
	* Parses and then immediately prints the file.
	*
	* @param       string
	*/
	function pprint($node = '', $variablearray = '')
	{
                // break up variables from the array and assign values individually to $this->var_names
                $this->register_template_variables($node, $variablearray);
                
                // parse template elements like {_phrases} and sessions globals like {user[username]}
                $this->parse_template($node);
                
                // handles collapsable menu items, breadcrumb and template javascript encryption
                // which ultimately prints the compiled template to the web browser
                $this->print_parsed_template($node);
	}
	/*
	* Function for parsing $_SESSION['ilancedata'] tags throughout the templates
	*
	* @notes       $_SESSION['ilancedata']['user']['XXXX'] = {user[XXXX]}
	* @usage       {user[username]} would be ILance
	* @param       string
	*/
	function parse_session_globals($node = '')
	{
                if (!empty($_SESSION['ilancedata']) AND is_array($_SESSION['ilancedata']))
                {
                        foreach ($_SESSION['ilancedata'] AS $name => $value)
                        {
                                $pattern = '/' . $this->start . $name . '\[([\w\d_]+)\]' . $this->end . '/';
                                if (preg_match_all($pattern, $this->templateregistry[$node], $matches) !== false)
                                {
                                        $matches = array_values(array_unique($this->remove_duplicate_template_variables($matches[1])));
                                        
                                        $replaceable = array();
                                        foreach ($matches AS $key)
                                        {
                                                if (isset($key) AND $key != '')
                                                {
                                                        if (defined('LOCATION') AND defined('AREA') AND LOCATION == 'admin' AND AREA == 'language')
                                                        {
                                                                continue;    
                                                        }
                                                        else if (defined('LOCATION') AND LOCATION == 'admin' AND $key == 'username')
                                                        {
                                                                continue;
                                                        }
                                                        else
                                                        {
                                                                $replaceable[$this->start . $name . "[$key]" . $this->end] = (isset($value["$key"]) ? $value["$key"] : '');
                                                        }
                                                }
                                        }
                                        
                                        $this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
                                        unset($replaceable, $matches);
                                }
                        }
                }
	}
        
        /*
	* Function for parsing $myapi['xxx'] tags throughout the templates
	*
	* @notes       $myapi['xxx'] = {myapi[XXXX]}
	* @usage       {myapi[username]} would be ILance
	* @param       string
	*/
	function parse_api_globals($node = '')
	{
                global $myapi;
                
                return '';
        
                if (!empty($myapi) AND is_array($myapi))
                {
                        foreach ($myapi AS $name => $value)
                        {
                                $pattern = '/' . $this->start . 'myapi' . '\[([\w\d_]+)\]' . $this->end . '/';
                                if (preg_match_all($pattern, $this->templateregistry["$node"], $matches) !== false)
                                {
                                        $matches = array_values(array_unique($this->remove_duplicate_template_variables($matches[1])));
                                        $replaceable = array();
                                        foreach ($matches AS $key)
                                        {
						$replaceable[$this->start . 'myapi' . "[$key]" . $this->end] = $value["$key"];
                                        }
                                        $this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry[$node]);
                                        unset($replaceable);
                                        unset($matches);
                                }
                        }
                }
	}
	
	/*
	* Function for processing an xml nav menu within ILance
	*
	* @notes       $myapi['xxx'] = {myapi[XXXX]}
	* @usage       {myapi[username]} would be ILance
	* 
	* @param       array
	* @param       array
	* @param       string       xml nav type to process (ADMIN/CLIENT/CLIENT_TOPNAV)
	*/
        function process_cpnav_xml($a, $e, $type = 'CLIENT')
	{
                $lang_code = $current_nav_group = $version = '';
                $navgroupdata = $navoptions = array();
                $counter = count($a);
                
                for ($i = 0; $i < $counter; $i++)
                {
                        // #### CLIENT LEFT NAV AND ADMIN TOP NAV ##############
                        if ($type == 'CLIENT' OR $type == 'ADMIN')
                        {
                                if ($a[$i]['tag'] == $type . 'NAVGROUPS')
                                {
                                        if ($a[$i]['type'] == 'complete' OR $a[$i]['type'] == 'open')
                                        {
                                                $lang_code = $a[$i]['attributes']['LANGUAGECODE'];
                                        }
                                }
                                else if ($a[$i]['tag'] == $type . 'NAVGROUP')
                                {
                                        if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                        {
                                                if ($type == 'CLIENT')
                                                {
                                                        $current_nav_group = mb_strtolower(str_replace(' ', '_', trim($a[$i]['attributes']['PHRASE'])));
                                                        
                                                        $navgroupdata[] = array(
                                                                $current_nav_group,                        // 0
                                                                trim($a[$i]['attributes']['PHRASE']),      // 1
                                                                trim($a[$i]['attributes']['LINK']),        // 2
                                                                trim($a[$i]['attributes']['SEOLINK']),     // 3
                                                                trim($a[$i]['attributes']['CONFIG']),      // 4
                                                                trim($a[$i]['attributes']['PERMISSION1']), // 5
                                                                trim($a[$i]['attributes']['PERMISSION2']), // 6
                                                                trim($a[$i]['attributes']['HEADER']),      // 7
                                                                trim($a[$i]['attributes']['SORT']),        // 8
                                                                trim($a[$i]['attributes']['ROLES']),       // 9
                                                                trim($a[$i]['attributes']['LOCATIONS']),   // 10
								trim($a[$i]['attributes']['SUBSCRIPTIONPERMISSION']) // 11
                                                        );
                                                }
                                                else if ($type == 'ADMIN')
                                                {
                                                        $current_nav_group = mb_strtolower(str_replace(' ', '_', trim($a[$i]['attributes']['PHRASE'])));
                                                        
                                                        $navgroupdata[] = array(
                                                                $current_nav_group,                        // 0
                                                                trim($a[$i]['attributes']['PHRASE']),      // 1
                                                                trim($a[$i]['attributes']['LINK']),        // 2
                                                                trim($a[$i]['attributes']['SEOLINK']),     // 3
                                                                trim($a[$i]['attributes']['CONFIG']),      // 4
                                                                trim($a[$i]['attributes']['PERMISSION1']), // 5
                                                                trim($a[$i]['attributes']['PERMISSION2']), // 6
                                                                trim($a[$i]['attributes']['SORT']),        // 7
                                                        );
                                                }
                                                
                                        }
                                }
                                else if ($a[$i]['tag'] == 'OPTION')
                                {
                                        if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                        {
                                                if ($type == 'CLIENT')
                                                {
                                                        $navoptions[] = array(
                                                                $current_nav_group,                        // 0
                                                                trim($a[$i]['attributes']['LINK']),        // 1
                                                                trim($a[$i]['attributes']['SEOLINK']),     // 2
                                                                trim($a[$i]['attributes']['CONFIG']),      // 3
                                                                trim($a[$i]['attributes']['PERMISSION1']), // 4
                                                                trim($a[$i]['attributes']['PERMISSION2']), // 5
                                                                trim($a[$i]['attributes']['SORT']),        // 6
                                                                trim($a[$i]['attributes']['PHRASE']),      // 7
                                                                trim($a[$i]['attributes']['ROLES']),       // 8
                                                                trim($a[$i]['attributes']['LOCATIONS']),   // 9
								trim($a[$i]['attributes']['SUBSCRIPTIONPERMISSION']) // 10
                                                        );
                                                }
                                                else if ($type == 'ADMIN')
                                                {
                                                        $navoptions[] = array(
                                                                $current_nav_group,                        // 0
                                                                trim($a[$i]['attributes']['PHRASE']),      // 1
                                                                trim($a[$i]['attributes']['LINK']),        // 2
                                                                trim($a[$i]['attributes']['SEOLINK']),     // 3
                                                                trim($a[$i]['attributes']['CONFIG']),      // 4
                                                                trim($a[$i]['attributes']['PERMISSION1']), // 5
                                                                trim($a[$i]['attributes']['PERMISSION2']), // 6
                                                                trim($a[$i]['attributes']['SORT']),        // 7
                                                        );
                                                }
                                        }
                                }        
                        }
                        else if ($type == 'CLIENT_TOPNAV')
                        {
                                if ($a[$i]['tag'] == 'VERSION')
                                {
                                        if ($a[$i]['type'] == 'complete' OR $a[$i]['type'] == 'open')
                                        {
                                                $version = $a[$i]['attributes']['VERSION'];
                                        }
                                }
                                else if ($a[$i]['tag'] == 'OPTION')
                                {
                                        if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                        {
                                                $navoptions[] = array(
                                                        trim($a[$i]['attributes']['PHRASE']),           // link phrase
                                                        trim($a[$i]['attributes']['LINK']),             // link url
                                                        trim($a[$i]['attributes']['SEOLINK']),          // seo link url
                                                        trim($a[$i]['attributes']['GUESTS']),           // true/false
                                                        trim($a[$i]['attributes']['MEMBERS']),          // true/false
                                                        trim($a[$i]['attributes']['ADMINS']),           // true/false
                                                        trim($a[$i]['attributes']['SHOW']),             // can be comma separated
                                                        trim($a[$i]['attributes']['PERMISSIONS']),      // can be comma separated
                                                        trim($a[$i]['attributes']['LOCATIONS']),        // can be comma separated
                                                        trim($a[$i]['attributes']['LINKEXTRA']),        // extra <a href xxx > control
                                                );
                                        }
                                }        
                        }
                }
                
                $result = array(
                        'lang_code' => $lang_code,
                        'navarray' => $navgroupdata,
                        'navoptions' => $navoptions,
                        'version' => $version,
                );
                
                return $result;
	}
	
        /*
	* Function to print the left side nav that holds service, product, experts and portfolio category logic
	*
	* @param	string	        nav type (service, product, serviceprovider, portfolio)
	* @param        integer         category id
	* @param        boolean         show sub-cats under main cats? (default true)
	* @param        boolean         show both (service and product) categories under one another?
	* @param        boolean         show category count?
	*/
        function print_left_nav($navtype = 'service', $cid = 0, $dosubcats = 1, $displayboth = 0, $showcount = 1, $showfilters = false,$adv=0,$sub_titl='')
        {
                global $ilconfig, $ilpage, $ilance, $phrase, $show, $categoryfinderhtml, $block, $blockcolor;
                
                ($apihook = $ilance->api('print_left_nav_start')) ? eval($apihook) : false;
                
                $ilance->subscription = construct_object('api.subscription');
		
                $html = $categorytitle = $block = '';

                if (isset($displayboth) AND $displayboth)
				
                {
                        if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                        {
                                
                                if ($cid > 0)
                                {
                                        $categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $cid);
                                }
                                
                                $nav = $ilance->categories_parser->print_subcategory_columns(1, 'product', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1);
                                
                                $html2 = $this->fetch_template('leftnav_product.html');
                                $html2 = $this->parse_hash('leftnav_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html2);
                                $html2 = $this->parse_if_blocks('leftnav_product.html', $html2, $addslashes = true);
                                
                                $html2 = stripslashes($html2);
                                $html2 = addslashes($html2);
                                
                                eval('$html .= "' . $html2 . '";');
                                
                                $html = stripslashes($html);
                        }
                }
                else 
                {
				
                        $blockcolor = 'yellow';
                        
                        $title = $phrase['_categories'];
                        
                        $categorytitle = '';
                        if ($cid > 0)
                        {
                                $categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $navtype, $cid);
                        }
                        
                        $html = $this->fetch_template('leftnav_categories.html');
                        $html = $this->parse_hash('leftnav_categories.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->parse_if_blocks('leftnav_categories.html', $html, $addslashes = true);
                        
                        $html = stripslashes($html);
                        $html = addslashes($html);
	
						//nataraj for bug 3081
						if(isset($sub_titl) && $sub_titl == 'young_collection')
						{
							$searchfilters = ($showfilters) ? $this->print_youngcollections_nav($navtype, $cid,$adv) : '';
						}
						if(isset($sub_titl) && $sub_titl == 'collections')
						{
							$searchfilters = ($showfilters) ? $this->print_collections_nav($navtype, $cid,$adv) : '';
						}
						else if(isset($sub_titl) && $sub_titl == 'die_variety')
						{
							$searchfilters = ($showfilters) ? $this->print_dievariety_nav($navtype, $cid,$adv) : '';
						}
						else
						{
							$searchfilters = ($showfilters) ? $this->print_search_nav($navtype, $cid,$adv) : '';
						}
						
						//nataraj for bug 3681
						if(isset($sub_titl) && $sub_titl == 'paterson')
						{
							$searchfilters = ($showfilters) ? $this->print_paterson_nav($navtype, $cid,$adv) : '';
						}

                                                //bug #6622
                                                if(isset($sub_titl) && $sub_titl == 'won')
                                                {
                                                        $searchfilters = ($showfilters) ? $this->print_wonitems_nav($navtype, $cid,$adv) : '';
                                                }
						
                        
                        eval('$html = "' . $html . '";');
                        
                        $html = stripslashes($html);
                }
                
                ($apihook = $ilance->api('print_left_nav_end')) ? eval($apihook) : false;
                
                return $html;
        }
    
        /*
	* Function to print the left side search options nav that holds service, product and experts category logic
	*
	* @param	string	        nav type (service, product, serviceprovider, portfolio)
	* @param        integer         category id
	*/
        function print_search_nav($navtype = '', $cid = 0,$adv=0)
        {
		global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
                
                $html = '';
		if ($navtype == 'product')
		{
			$html = $this->fetch_template('leftnav_searchoptions_product.html');
                        $html = $this->parse_hash('leftnav_searchoptions_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->handle_template_hooks('leftnav_searchoptions_product.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_product.html', $html, $addslashes = true);
		}
		if ($navtype == 'service')
		{
			$html = $this->fetch_template('leftnav_searchoptions_service.html');
                        $html = $this->parse_hash('leftnav_searchoptions_service.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->handle_template_hooks('leftnav_searchoptions_service.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_service.html', $html, $addslashes = true);
		}
		if ($navtype == 'serviceprovider')
		{
			$html = $this->fetch_template('leftnav_searchoptions_experts.html');
                        $html = $this->parse_hash('leftnav_searchoptions_experts.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->handle_template_hooks('leftnav_searchoptions_experts.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_experts.html', $html, $addslashes = true);
		}
                
                $html = stripslashes($html);
                
		return $html;
        }
        
	//for bug 3081
	function print_youngcollections_nav($navtype = '', $cid = 0,$adv=0)
        {
			global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
					
					$html = '';
					
			if ($navtype == 'product')
			{
				$html = $this->fetch_template('leftnav_youngcollections_product.html');
							$html = $this->parse_hash('leftnav_youngcollections_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
							$html = $this->handle_template_hooks('leftnav_youngcollections_product.html', $html);
				$html = $this->parse_if_blocks('leftnav_youngcollections_product.html', $html, $addslashes = true);
			}
                
                $html = stripslashes($html);
                
		return $html;
        }

        //for bug 6622
        function print_wonitems_nav($navtype = '', $cid = 0,$adv=0)
        {
                global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
                                
                                $html = '';
                                
                if ($navtype == 'product')
                {
                        $html = $this->fetch_template('leftnav_wonitems_product.html');
                                                $html = $this->parse_hash('leftnav_wonitems_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                                                $html = $this->handle_template_hooks('leftnav_wonitems_product.html', $html);
                        $html = $this->parse_if_blocks('leftnav_wonitems_product.html', $html, $addslashes = true);
                }
                
                $html = stripslashes($html);
                
                return $html;
        }
		
		function print_collections_nav($navtype = '', $cid = 0,$adv=0)
        {
			global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
					
					$html = '';
					
			if ($navtype == 'product')
			{
				$html = $this->fetch_template('leftnav_collections_product.html');
							$html = $this->parse_hash('leftnav_collections_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
							$html = $this->handle_template_hooks('leftnav_collections_product.html', $html);
				$html = $this->parse_if_blocks('leftnav_collections_product.html', $html, $addslashes = true);
			}
                
                $html = stripslashes($html);
                
		return $html;
        }
		
		//die  variety
		function print_dievariety_nav($navtype = '', $cid = 0,$adv=0)
        {
			global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
					
					$html = '';
					
			if ($navtype == 'product')
			{
				$html = $this->fetch_template('leftnav_die_variety_product.html');
							$html = $this->parse_hash('leftnav_die_variety_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
							$html = $this->handle_template_hooks('leftnav_die_variety_product.html', $html);
				$html = $this->parse_if_blocks('leftnav_die_variety_product.html', $html, $addslashes = true);
			}
                
                $html = stripslashes($html);
                
		return $html;
        }
		
		//for bug 3081
		function print_paterson_nav($navtype = '', $cid = 0,$adv=0)
        {
			global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
					
					$html = '';
					
			if ($navtype == 'product')
			{
				$html = $this->fetch_template('leftnav_paterson_product.html');
							$html = $this->parse_hash('leftnav_paterson_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
							$html = $this->handle_template_hooks('leftnav_paterson_product.html', $html);
				$html = $this->parse_if_blocks('leftnav_paterson_product.html', $html, $addslashes = true);
			}
                
                $html = stripslashes($html);
                
		return $html;
        }
		
 //amutha works on search page on june 02
		
		 function print_left_nav1($navtype = 'service', $cid = 0, $dosubcats = 1, $displayboth = 0, $showcount = 1, $showfilters = false)
         {
                global $ilconfig, $ilpage, $ilance, $phrase, $show, $categoryfinderhtml, $block, $blockcolor;
                
                ($apihook = $ilance->api('print_left_nav_start')) ? eval($apihook) : false;
                
                $ilance->subscription = construct_object('api.subscription');
		
                $html = $categorytitle = $block = '';
		
                if (isset($displayboth) AND $displayboth)
				
                {
                        if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
                        {
                                $title = $phrase['_services'];
                                
                                if ($cid > 0)
                                {
                                        $categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
                                }
				
                                 $nav = $ilance->categories_parser->print_subcategory_columns(1, 'service', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1);
                                $html1 = $this->fetch_template('leftnav_service.html');
                                $html1 = $this->parse_hash('leftnav_service.html', array('ilpage' => $ilpage), $parseglobals = 0, $html1);
                                $html1 = $this->parse_if_blocks('leftnav_service.html', $html1, $addslashes = true);
                                
                                $html1 = stripslashes($html1);
                                $html1 = addslashes($html1);
                                
                                eval('$html .= "' . $html1 . '";');
                                
                                $html = stripslashes($html);
                        }
			
                        if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                        {
                                $title = $phrase['_items'];
                                
                                if ($cid > 0)
                                {
                                        $categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], 'product', $cid);
                                }
                                
                                $nav = $ilance->categories_parser->print_subcategory_columns(1, 'product', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1);
                                
                                $html2 = $this->fetch_template('leftnav_product.html');
                                $html2 = $this->parse_hash('leftnav_product.html', array('ilpage' => $ilpage), $parseglobals = 0, $html2);
                                $html2 = $this->parse_if_blocks('leftnav_product.html', $html2, $addslashes = true);
                                
                                $html2 = stripslashes($html2);
                                $html2 = addslashes($html2);
                                
                                eval('$html .= "' . $html2 . '";');
                                
                                $html = stripslashes($html);
                        }
                }
                else 
                {
				
                        $blockcolor = 'yellow';
                        if ($navtype == 'service' OR $navtype == 'serviceprovider' OR $navtype == 'portfolio' OR $navtype == 'wantads' OR $navtype == 'stores' OR $navtype == 'storesmain')
                        {
                                $nav = $ilance->categories_parser->print_subcategory_columns(1, $navtype, $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1);
				if ($navtype == 'portfolio' OR $navtype == 'wantads' OR $navtype == 'serviceprovider')
				{
					$blockcolor = 'gray';
                                        $block = '3';	
				}
                                else if ($navtype != 'stores' AND $navtype != 'storesmain')
                                {
                                        $blockcolor = 'blue';
                                        $block = '2';
                                }
                        }
                        else
                        {
                              
							    // Murugan Changes On Jan 30 For Page Not Loading at search page
								//suku
								//$nav = $ilance->categories_parser->print_subcategory_columns(1, $navtype, $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1);
								
                        }
                        //($apihook = $ilance->api('print_left_nav_else_condition')) ? eval($apihook) : false;
                        
                        $title = $phrase['_categories'];
                        
                        $categorytitle = '';
                        if ($cid > 0)
                        {
                                $categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $navtype, $cid);
                        }
                        
                        $html = $this->fetch_template('leftnav_categories.html');
                        $html = $this->parse_hash('leftnav_categories.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->parse_if_blocks('leftnav_categories.html', $html, $addslashes = true);
                        
                        $html = stripslashes($html);
                        $html = addslashes($html);
			
			$searchfilters = ($showfilters) ? $this->print_search_nav1($navtype, $cid) : '';
                        
                        eval('$html = "' . $html . '";');
                        
                        $html = stripslashes($html);
                }
                
                ($apihook = $ilance->api('print_left_nav_end')) ? eval($apihook) : false;
                
              return $html;
        }
        
		
        function print_search_nav1($navtype = '', $cid = 0)
        {
		global $ilance, $myapi, $ilpage, $ilconfig, $phrase, $show;
                
                $html = '';
		if ($navtype == 'product')
		{
			$html = $this->fetch_template('leftnav_category_new.html');
                        $html = $this->parse_hash('leftnav_category_new', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->handle_template_hooks('leftnav_category_new', $html);
			$html = $this->parse_if_blocks('leftnav_category_new', $html, $addslashes = true);
		}
		if ($navtype == 'service')
		{
			$html = $this->fetch_template('leftnav_searchoptions_service.html');
                        $html = $this->parse_hash('leftnav_searchoptions_service.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->handle_template_hooks('leftnav_searchoptions_service.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_service.html', $html, $addslashes = true);
		}
		if ($navtype == 'serviceprovider')
		{
			$html = $this->fetch_template('leftnav_searchoptions_experts.html');
                        $html = $this->parse_hash('leftnav_searchoptions_experts.html', array('ilpage' => $ilpage), $parseglobals = 0, $html);
                        $html = $this->handle_template_hooks('leftnav_searchoptions_experts.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_experts.html', $html, $addslashes = true);
		}
                
                $html = stripslashes($html);
                
		return $html;
        }
		
		
		//amutha works finished on search page on june 02
        /*
	* Function to construct the breadcrumb trail for the client cp template (just under the top nav)
	*
	* @param	string	        
	*/
//sekar works on july 28 for top nav link
          function construct_breadcrumb($navcrumb)
        {
		global $navcrumb, $ilcrumbs, $page_title, $area_title, $phrase, $ilpage;
                
		$elements = array('breadcrumbtrail' => '', 'breadcrumbfinal' => '');
	 $current = sizeof($navcrumb);
                
		$count = 0;
		if (isset($navcrumb) AND is_array($navcrumb))
		{
			foreach ($navcrumb AS $navurl => $navtitle)
			{
				$type = iif(++$count == $current, 'breadcrumbfinal', 'breadcrumbtrail');
				$dotrail = iif($type == 'breadcrumbtrail', true, false);
				if (empty($navtitle))
				{
					continue;
				}    
				if($navurl !='')
				{
				   if($navurl == 'project_title')
				   {
				   
				   $new = '&nbsp; &gt;';
				      eval('$elements["$type"] .= "' . $this->fetch_template('breadcrumb.html', 0) . '";');
				   }
				   else
				   {
					 
					  eval('$elements["$type"] .= "' . $this->fetch_template('breadcrumb_trail.html', 0) . '";');
					
				   }
				}
				else
				{
				   $new ='&nbsp; &gt;';
				 eval('$elements["$type"] .= "' . $this->fetch_template('breadcrumb.html', 0) . '";');
				}
				
				/*if ($dotrail == 1)
				{
					eval('$elements["$type"] .= "' . $this->fetch_template('breadcrumb_trail.html', 0) . '";');
				}
				else
				{
					eval('$elements["$type"] .= "' . $this->fetch_template('breadcrumb.html', 0) . '";');
				}*/
			}
		}
                
		return $elements;
        }
		
//sekar works finished  on july 28 for top nav link		
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
