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
define('AREA', 'language');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[language]" => $ilcrumbs["$ilpage[language]"]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['language']);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{  
    //sen on mar26 for seo in admin 
	 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'seo_create')
     {

   if (!empty($ilance->GPC['url']) AND !empty($ilance->GPC['page']) AND !empty($ilance->GPC['title']) AND !empty($ilance->GPC['description']) AND !empty($ilance->GPC['keyword']))
              {                
        
  $sql = $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "seo
                (seo_url,page,url_title,url_description,url_keyword)
                VALUES
                (
				
                '" . $ilance->db->escape_string($ilance->GPC['url']). "',
                '" . $ilance->db->escape_string($ilance->GPC['page']) . "',
                '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['keyword']) . "'
				)
        ", 0, null, __FILE__, __LINE__);
		  	print_action_success('Your SEO Successfully Inserted.', $ilpage['language']);
	       exit();
	
        }
   }
	
     if (isset($ilance->GPC['delete-seo']) AND $ilance->GPC['delete-seo'] == 'delete-value' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
     {

	   $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "seo
                        WHERE id = '".$ilance->GPC['id']."'
                ");
	  	print_action_success('You have successfully deleted', $ilpage['language']);
		exit();
	} 
	
	 if (isset($ilance->GPC['edit-seo']) AND $ilance->GPC['edit-seo'] == 'edit-value' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
     {
	      $show['edit_seo']=true;
	      $edit_seo = $ilance->db->query("SELECT *
											  FROM " . DB_PREFIX . "seo where id ='".$ilance->GPC['id']."'
											"); 
		 $res_seo=$ilance->db->fetch_array($edit_seo);
		 $id = $ilance->GPC['id'];
		 $seo_url = $res_seo['seo_url'];	
		  $seo_page = $res_seo['page'];									
		 $seo_title = $res_seo['url_title'];	
		 $seo_description = $res_seo['url_description'];
		 $seo_key = $res_seo['url_keyword'];
	
	} 
	
	 if (isset($ilance->GPC['seo_update']) AND $ilance->GPC['seo_update'] == 'seo_updateval')
     {
              $ilance->db->query("
                                UPDATE " . DB_PREFIX . "seo
                                SET seo_url = '".$ilance->GPC['url']."',
								 page = '".$ilance->GPC['page']."',
                                 url_title = '".$ilance->GPC['title']."',
								 url_description = '".$ilance->GPC['description']."',
								 url_keyword = '".$ilance->GPC['keyword']."'
								 where id = '".$ilance->GPC['id']."'
								
                        ");
	print_action_success('your value  Changed Succecfully', $ilpage['language']);
	exit();
	}
	
	
	//sen on mar26 end
    
	// #### POPUP LANGUGAGE PHRASE REFERENCE FEATURE ###########################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'phrase-reference')
	{
		$area_title = $phrase['_viewing_quick_reference_language_phrases'];
		$page_title = SITE_NAME . ' - ' . $phrase['_viewing_quick_reference_language_phrases'];
	
                if (isset($ilance->GPC['phrasegroup']))
		{
			$phraselist = $ilance->admincp->phraselist_pulldown($ilance->GPC['phrasegroup'], $_SESSION['ilancedata']['user']['languageid']);
		}
		else
		{
			$phraselist = $ilance->admincp->phraselist_pulldown('accounting', $_SESSION['ilancedata']['user']['languageid']);
		}
                
		
		$language_pulldown = $ilance->language->print_language_pulldown(intval($ilance->GPC['languageid']), false);
		$phrasegroup_pulldown = $ilance->admincp->phrasegroup_pulldown();
		
		if (isset($ilance->GPC['languageid']) AND $ilance->GPC['languageid'] > 0)
		{
			$language = $ilance->admincp->fetch_language_name(intval($ilance->GPC['languageid']));
		}
		else
		{
			$language = $ilance->admincp->fetch_language_name($_SESSION['ilancedata']['user']['languageid']);
		}
		
		if (isset($ilance->GPC['phrasegroup']))
		{
			$phrasegroup = $ilance->admincp->api_phrasegroupname($ilance->GPC['phrasegroup']);
		}
		else
		{
			$phrasegroup = $ilance->admincp->api_phrasegroupname('accounting');
		}
		
                $pprint_array = array('buildversion','ilanceversion','login_include_admin','language','phraselist','phrasegroup','language_pulldown','phrasegroup_pulldown','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
                
                ($apihook = $ilance->api('admincp_phrase_reference_end')) ? eval($apihook) : false;
                
		$ilance->template->load_admincp_popup('main', 'phrase_reference.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'phrase_search_results');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### DOWNLOAD XML LANGUAGE PACKAGE ######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_download-xml-language')
	{
		$area_title = $phrase['_exporting_language_phrases_to_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_exporting_language_phrases_to_xml'];
		
                $characterset = $phrasefilter = '';    
                $languageid = $_SESSION['ilancedata']['user']['languageid'];
                
		if (isset($ilance->GPC['id']))
		{
			$languageid = intval($ilance->GPC['id']);
		}
		else if (isset($ilance->GPC['languageid']))
		{
			$languageid = intval($ilance->GPC['languageid']);
		}
		
		if (!empty($ilance->GPC['characterset']))
		{
			$characterset = $ilance->GPC['characterset'];
		}
		if (!empty($ilance->GPC['phrasefilter']))
		{
			$phrasefilter = $ilance->GPC['phrasefilter'];
		}
                
		$query = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . $languageid . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($query) > 0)
		{
			$langconfig = $ilance->db->fetch_array($query);
			header("Content-type: text/xml; charset=" . stripslashes($langconfig['charset']));
                        
                        $replacements = $langconfig['replacements'];
                        if (isset($ilance->GPC['decodenumericentities']) AND $ilance->GPC['decodenumericentities'])
                        {
                                $replacements = html_entity_decode($langconfig['replacements'], ENT_NOQUOTES, 'UTF-8');
                        }
                        
                        // language header configuration settings for this particular language
			$xml_output = "<?xml version=\"1.0\" encoding=\"" . stripslashes($langconfig['charset']) . "\"?>" . LINEBREAK;
			$xml_output .= "<language ilversion=\"" . $ilance->config['ilversion'] . "\">" . LINEBREAK;
			$xml_output .= "\t<settings>" . LINEBREAK;
			$xml_output .= "\t\t<title>" . stripslashes($langconfig['title']) . "</title>" . LINEBREAK;
			$xml_output .= "\t\t<author>" . stripslashes(SITE_NAME) . "</author>" . LINEBREAK;
			$xml_output .= "\t\t<languagecode><![CDATA[" . stripslashes($langconfig['languagecode']) . "]]></languagecode>" . LINEBREAK;
			$xml_output .= "\t\t<charset><![CDATA[" . stripslashes($langconfig['charset']) . "]]></charset>" . LINEBREAK;
                        $xml_output .= "\t\t<locale><![CDATA[" . stripslashes($langconfig['locale']) . "]]></locale>" . LINEBREAK;
                        $xml_output .= "\t\t<languageiso><![CDATA[" . stripslashes($langconfig['languageiso']) . "]]></languageiso>" . LINEBREAK;
                        $xml_output .= "\t\t<textdirection><![CDATA[" . stripslashes($langconfig['textdirection']) . "]]></textdirection>" . LINEBREAK;
                        $xml_output .= "\t\t<canselect><![CDATA[" . intval($langconfig['canselect']) . "]]></canselect>" . LINEBREAK;
                        $xml_output .= "\t\t<replacements><![CDATA[" . stripslashes($replacements) . "]]></replacements>" . LINEBREAK;
			$xml_output .= "\t</settings>" . LINEBREAK . LINEBREAK;
			
			$query2 = $ilance->db->query("
                                SELECT groupname, description, product
                                FROM " . DB_PREFIX . "language_phrasegroups
                        ");
			if ($ilance->db->num_rows($query2) > 0)
			{
				while ($groupres = $ilance->db->fetch_array($query2))
				{
					$xml_output .= "\t<phrasegroup name=\"" . stripslashes($groupres['groupname']) . "\" description=\"" . stripslashes($groupres['description']) . "\" product=\"" . stripslashes($groupres['product']) . "\">" . LINEBREAK;
                                        
					if (isset($ilance->GPC['untranslated']) AND $ilance->GPC['untranslated'])
					{
						// export only untranslated phrases
						$query3 = $ilance->db->query("
                                                        SELECT varname, text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS text
                                                        FROM " . DB_PREFIX . "language_phrases
                                                        WHERE phrasegroup = '" . $groupres['groupname'] . "'
                                                                AND text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " = text_eng
                                                        ORDER BY phraseid ASC
                                                ");
					}
					else
					{
						// export entire language phrases
						$query3 = $ilance->db->query("
                                                        SELECT varname, text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS text
                                                        FROM " . DB_PREFIX . "language_phrases
                                                        WHERE phrasegroup = '" . $groupres['groupname'] . "'
                                                        ORDER BY phraseid ASC
                                                ");
					}
                                        
					if ($ilance->db->num_rows($query3) > 0)
					{
						$shortlang = mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3));
                                                
						while ($phraseres = $ilance->db->fetch_array($query3))
						{
                                                        $thephrase = stripslashes($phraseres['text']);
                                                        
                                                        if (isset($ilance->GPC['decodenumericentities']) AND $ilance->GPC['decodenumericentities'])
                                                        {
                                                                $thephrase = html_entity_decode($thephrase, ENT_NOQUOTES, 'UTF-8');
                                                        }
                                                        
                                                        if (isset($ilance->GPC['decodeentities']) AND $ilance->GPC['decodeentities'])
                                                        {
                                                                $thephrase = $ilance->admincp->decode_entities($thephrase);        
                                                        }
                                                        
                                                        $xml_output .= "\t\t<phrase varname=\"" . stripslashes(trim($phraseres['varname'])) . "\">" . LINEBREAK . "\t\t\t<![CDATA[" . $thephrase . "]]>" . LINEBREAK . "\t\t</phrase>" . LINEBREAK;
						}
					}
                                        
					$xml_output .= "\t</phrasegroup>" . LINEBREAK;
				}
			}
                        
			$xml_output .= "</language>";
                        
			$ilance->common->download_file($xml_output, 'phrases-' . VERSIONSTRING . '-' . $langconfig['languagecode'] . '.xml', 'text/plain');
		}
	}
	
	// #### UPLOAD XML LANGUAGE PACKAGE ########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_upload-xml-language')
	{
		$area_title = $phrase['_importing_language_pack_via_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_importing_language_pack_via_xml'];
		
		while (list($key, $value) = each($_FILES))
		{
			$GLOBALS["$key"] = $value;
			foreach ($_FILES AS $key => $value)
			{
				$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
				foreach ($value AS $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$GLOBALS["$key2"] = $value2;
				}
			}
		}

                $data = array();
		$xml = file_get_contents($xml_file);
                
		$xml_encoding = 'UTF-8';
                $xml_encoding = mb_detect_encoding($xml);
                
                if ($xml_encoding == 'ASCII') 
                {
                        $xml_encoding = '';
                }
	
		$parser = xml_parser_create($xml_encoding);
		xml_parse_into_struct($parser, $xml, $data);
		$error_code = xml_get_error_code($parser);
		xml_parser_free($parser);
		if ($error_code == 0)
		{
			// process our xml language package
			
			$ilance->xml = construct_object('api.xml');
			
			$result = $ilance->xml->process_lang_xml($data, $xml_encoding);
                        
			$noversioncheck = isset($ilance->GPC['noversioncheck']) ? intval($ilance->GPC['noversioncheck']) : 0;
                        
			if ($result['illang_version'] != $ilance->config['ilversion'] AND $noversioncheck == 0)
			{
				print_action_failed($phrase['_the_version_of_the_this_language_xml_package_is_different_than_the_currently_installed_version'] . ' <strong><em>' . $ilance->config['ilversion'] . '</em></strong>.  ' . $phrase['_the_operation_has_aborted_due_to_a_language_version_conflict'] . '<br /><br />' . $phrase['_tip_you_can_click_the_checkbox_on_the_previous_page_to_ignore_language_version_conflicts_which_will_ultimately_bypass_this_version_checker'], $ilance->GPC['return']);
				exit();
			}
                        
                        $query = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "language
                                WHERE languagecode = '" . $result['lang_code'] . "'
                                LIMIT 1
                        ");
                        if ($ilance->db->num_rows($query) == 0)
                        {
                                print_action_failed($phrase['_were_sorry_the_language_package_being_uploaded_requires'], $ilance->GPC['return']);
                                exit();
                        }
                        
                        if (!empty($result['replacements']))
                        {
                                //$result['replacements'] = ilance_htmlentities($result['replacements']);
                        }
                        
                        // update language table with defaults in the xml file
                        // since there may have been new settings or character encoding strings changed
                        // with this specific import
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "language
                                SET title = '" . $ilance->db->escape_string($result['title']) . "',
                                charset = '" . $ilance->db->escape_string($result['charset']) . "',
                                locale = '" . $ilance->db->escape_string($result['locale']) . "',
                                author = '" . $ilance->db->escape_string($result['author']) . "',
                                languageiso = '" . $ilance->db->escape_string($result['languageiso']) . "',
                                textdirection = '" . $ilance->db->escape_string($result['textdirection']) . "',
                                canselect = '" . intval($result['canselect']) . "',
                                replacements = '" . $ilance->db->escape_string($result['replacements']) . "'
                                WHERE languagecode = '" . $ilance->db->escape_string($result['lang_code']) . "'
                                LIMIT 1
                        ");
                        
                        $AllLanguages = array();
                        
                        $query = $ilance->db->query("
                                SELECT languagecode
                                FROM " . DB_PREFIX . "language
                        ");
                        if ($ilance->db->num_rows($query) > 0)
                        {
                                while ($row = $ilance->db->fetch_array($query))
                                {
                                        $AllLanguages[] = 'text_' . mb_substr($row['languagecode'], 0, 3);
                                }
            
                                $lfn = 'text_' . mb_substr($result['lang_code'], 0, 3);
                                $phrasearray = $result['phrasearray'];
                                $phrasecount = count($phrasearray);
                                for ($i = 0; $i < $phrasecount; $i++)
                                {
                                        // does varname exist in the db?
                                        $varexist = $ilance->db->query("
                                                SELECT phrasegroup
                                                FROM " . DB_PREFIX . "language_phrases
                                                WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                LIMIT 1
                                        ");
                                        if ($ilance->db->num_rows($varexist) == 0)
                                        {
                                                // varname DOES NOT exist for this language within the db
                                                // this must be a new phrase! let's add it into the database
                                                // so the admin has the new phrase and the ability to update this phrase
                                                // in their own language .. lets also add this new phrase to the original_text field
                                                // for future reverts
                                                if (!empty($phrasearray[$i][0]) AND !empty($phrasearray[$i][1]))
                                                {
                                                        //$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
                                                        
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "language_phrases
                                                                (phrasegroup, varname, text_original)
                                                                VALUES(
                                                                '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
                                                                '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
                                                                '" . $ilance->db->escape_string($phrasearray[$i][2]) . "')
                                                        ");
                                                }
                                                else
                                                {
                                                        $notice .= "Notice: varname - <strong>" . $phrasearray[$i][1] . "</strong> for phrasegroup <strong>" . $phrasearray[$i][0] . "</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
                                                }
                    
                                                // since varname does not exist, update ALL languages with this one phrase
                                                // so we have it to translate later!
                                                foreach ($AllLanguages AS $value)
                                                {
                                                        if (!empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]))
                                                        {
                                                                //$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
                                                                
                                                                // update the phrase for all installed languages
                                                                // note: if the author of the package being uploaded is by ILance or ilance, set ismaster = '1'
                                                                $ismastersql = '';
                                                                if ($result['author'] == 'ilance' OR $result['author'] == 'ILance' OR $result['author'] == 'iLance')
                                                                {
                                                                        $ismastersql = "ismaster = '1',";
                                                                }
                                                                
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                                        SET " . $ilance->db->escape_string($lfn) . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
                                                                        $ismastersql
                                                                        isupdated = '0'
                                                                        WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                        LIMIT 1
                                                                ");
                                                        }
                                                        else
                                                        {
                                                                $notice .= "Notice: varname - <strong>" . $phrasearray[$i][1] . "</strong> for phrase group <strong>" . $phrasearray[$i][0] . "</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                // varname exists within the DB
                                                // update phrase.. but also make sure to change the phrase group
                                                // to the value of what is in the xml package being uploaded as team ilance
                                                // may have moved phrases around in this release or any future releases
                                                if (!empty($phrasearray[$i][0]) AND !empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]))
                                                {
                                                        //$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
                                                        
                                                        // ilance software original phrase text is based on English US
                                                        // if this language pack being uploaded is english, be sure to set the
                                                        // 'text_original' to the phrase text in this uploaded xml file
                                                        // if not, skip this field from being updated
                                                        $updateoriginaltext = $ismastersql = '';
                                                        
                                                        if ($result['lang_code'] == 'english' AND ($result['author'] == 'ilance' OR $result['author'] == 'ILance' OR $result['author'] == 'iLance'))
                                                        {
                                                                // this is the official English (US) xml language package produced by
                                                                // ILance so let's be sure to update this phrases original_text
                                                                // with that of the xml phrase currently being processed
                                                                $updateoriginaltext = "text_original = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
                                                                $ismastersql = "ismaster = '1',";
                                                        }
                                                        
                                                        if (isset($ilance->GPC['notextreplace']) AND $ilance->GPC['notextreplace'])
                                                        {
                                                                // update only new phrase group id
                                                                // also update the text_original so admins can use revert to original
                                                                // in the future for this specific phrase

                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                                        SET phrasegroup = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
                                                                        $updateoriginaltext
                                                                        $ismastersql
                                                                        isupdated = '0'
                                                                        WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                        LIMIT 1
                                                                ");    
                                                        }
                                                        else
                                                        {
                                                                // update phrase, phrase group id
                                                                // also update the text_original so admins can use revert to original
                                                                // in the future for this specific phrase
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                                        SET " . $ilance->db->escape_string($lfn) . " = '" . addslashes($phrasearray[$i][2]) . "',
                                                                        $updateoriginaltext
                                                                        $ismastersql
                                                                        phrasegroup = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
                                                                        isupdated = '0'
                                                                        WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                        LIMIT 1
                                                                ");
                                                        }
                                                }
                                                else
                                                {
                                                        $notice .= "Notice: varname - <strong>" . $phrasearray[$i][1] . "</strong> for phrase group <strong>" . $phrasearray[$i][0] . "</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
                                                }
                                        }
                                }
                                
                                print_action_success($phrase['_language_import_successful'], $ilance->GPC['return']);
                                exit();
                        }
		}
		else
		{
			print_action_failed($phrase['_were_sorry_there_was_an_error_with_the_formatting_of_the_language_file'], $ilance->GPC['return']);
			exit();
		}
	}
    
	// #### REMOVE PHRASE GROUP ############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removegroup' AND isset($ilance->GPC['phrasegroup']) AND !empty($ilance->GPC['phrasegroup']))
	{
		$area_title = $phrase['_removing_phrase_group'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_phrase_group'];
	
		$ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "language_phrasegroups
                        WHERE groupname = '" . $ilance->db->escape_string($ilance->GPC['phrasegroup']) . "'
                ");
                
		$ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "language_phrases
                        WHERE phrasegroup = '" . $ilance->db->escape_string($ilance->GPC['phrasegroup']) . "'");
                
		print_action_success($phrase['_you_have_successfully_removed_a_phrase_group_from_your_language'], $ilance->GPC['return']);
		exit();
	}
    
	// #### REMOVE LANGUAGE ################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removelanguage')
	{
		$area_title = $phrase['_removing_languages'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_languages'];
	
		($apihook = $ilance->api('admincp_removelanguage_start')) ? eval($apihook) : false;
	
		$success = true;
	
		if (isset($ilance->GPC['languageid']) AND $ilance->GPC['languageid'] > 1)
		{
			$sql_lang = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
				WHERE languageid = '" . intval($ilance->GPC['languageid']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_lang) > 0)
			{
				$res_lang = $ilance->db->fetch_array($sql_lang);
		
				// remove language
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "language
					WHERE languageid = '" . intval($ilance->GPC['languageid']) . "'
					LIMIT 1
				");
		
				// remove phrases for language
                                if ($ilance->db->field_exists("text_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "language_phrases") == 1)
                                {
                                        $ilance->db->query("
                                                ALTER TABLE " . DB_PREFIX . "language_phrases
                                                DROP text_" . mb_substr($res_lang['languagecode'], 0, 3) . "
                                        ");
                                }
		
				// remove email subjects
				if ($ilance->db->field_exists("subject_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "email
						DROP subject_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
				
				// remove email templates
				if ($ilance->db->field_exists("message_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "email
						DROP message_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					"); 
				}
		
				// categories
				if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "categories") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "categories
						DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
                                if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "categories") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "categories
						DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
			
				// countries
				if ($ilance->db->field_exists("location_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "locations") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "locations
						DROP location_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
		
				// subscription permissions
				if ($ilance->db->field_exists("accesstext_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription_permissions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "subscription_permissions
						DROP accesstext_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
			
				if ($ilance->db->field_exists("accessdescription_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription_permissions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "subscription_permissions
						DROP accessdescription_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
		
				// custom product questions
				if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "product_questions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "product_questions
						DROP question_" . mb_substr($res_lang['languagecode'], 0, 3)."
					");
				}
			
				if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "product_questions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "product_questions
						DROP description_" . mb_substr($res_lang['languagecode'], 0, 3)."
					");
				}
				
				// custom service questions
				if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "project_questions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "project_questions
						DROP question_" . mb_substr($res_lang['languagecode'], 0, 3)."
					");
				}
				
				if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "project_questions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "project_questions
						DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
			
				// custom registration questions
				if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "register_questions") == 1)
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "register_questions
						DROP question_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
		
				if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "register_questions") == 1)
				{    			
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "register_questions
						DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
				
				// skills
				if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "skills") == 1)
				{    			
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "skills
						DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
				
				if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "skills") == 1)
				{    			
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "skills
						DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
					");
				}
                                
                                if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "feedback_criteria") == 1)
                                {    			
                                        $ilance->db->query("
                                                ALTER TABLE " . DB_PREFIX . "feedback_criteria
                                                DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
                                        ");
                                }
                                
                                if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "bid_fields") == 1)
                                {    			
                                        $ilance->db->query("
                                                ALTER TABLE " . DB_PREFIX . "bid_fields
                                                DROP question_" . mb_substr($res_lang['languagecode'], 0, 3) . "
                                        ");
                                }
                                
                                if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "bid_fields") == 1)
                                {    			
                                        $ilance->db->query("
                                                ALTER TABLE " . DB_PREFIX . "bid_fields
                                                DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
                                        ");
                                }
				    
                                // we should also check to see which users are using this language, and set their languageid field to the new default..
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET languageid = '" . intval($ilance->GPC['baselanguage']) . "'
                                        WHERE languageid = '" . $res_lang['languageid'] . "'
                                ");
				
				($apihook = $ilance->api('admincp_removelanguage_end')) ? eval($apihook) : false;
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . intval($ilance->GPC['baselanguage']) . "'
					WHERE name = 'globalserverlanguage_defaultlanguage'
				");
		
				// if we are viewing the page in the language we are attempting to remove
				// let's ensure we switch back to the default language so no db phrase errors occur
				if ($_SESSION['ilancedata']['user']['languageid'] == $ilance->GPC['languageid'])
				{
					$_SESSION['ilancedata']['user']['languageid'] = intval($ilconfig['globalserverlanguage_defaultlanguage']);
					$_SESSION['ilancedata']['user']['languagecode'] = $ilance->language->print_language_code($ilconfig['globalserverlanguage_defaultlanguage']);
					$_SESSION['ilancedata']['user']['slng'] = $ilance->language->print_short_language_code();
				}
			}
			else
			{
				$success = false;
			}
	    
			if ($success == true)
			{
				print_action_success($phrase['_the_selected_language_was_successfully_removed_from_the_marketplace_datastore'], $ilance->GPC['return']);
				exit();
			}
			else
			{
				print_action_failed($phrase['_there_was_an_error_deleting_the_selected_language_please_select_all_required_form_fields_and_retry_your_action'], $ilance->GPC['return']);
				exit();
			}
		}
		else
		{
			print_action_failed($phrase['_there_was_an_error_deleting_the_selected_language_you_cannot_remove_language_id_1'], $ilance->GPC['return']);
			exit();
		}
	}
    
	// #### REMOVE SINGLE OR MULTIPLE PHRASES ##################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'deletephrases')
	{
		$area_title = $phrase['_removing_phrases'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_phrases'];
	
		$success = true;
		$notice = '';
		if (isset($ilance->GPC['removevarnames']))
		{
			foreach ($ilance->GPC['phrasesid'] as $varname)
			{
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "language_phrases WHERE phraseid = '" . intval($varname) . "' LIMIT 1");
				
				$notice .= "Template Variable ID#<strong>{$varname}</strong> was deleted from all language locale(s) available.";
			}
				    
			if ($notice == "")
			{
				$success = false;
				print_action_failed($phrase['_warning_phrases_could_not_be_deleted_to_delete_a_phrase_you_must'], $ilance->GPC['return']);
				exit();
			}
			else
			{
				$admurl = $ilance->GPC['return'];
				print_action_success($notice, $admurl);
				exit();
			}
		}
		else
		{
			$admurl = $ilance->GPC['return'];
			print_action_failed($phrase['_warning_your_template_phrases_could_not_be_deleted_to_delete_a_phrase_you'], $admurl);
			exit();
		}
	}
	    
	// #### ADD NEW LANGUAGE ###################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'addlanguage')
	{
		$area_title = $phrase['_adding_a_new_language'];
		$page_title = SITE_NAME . ' - ' . $phrase['_adding_a_new_language'];
		
		($apihook = $ilance->api('admincp_addlanguage_start')) ? eval($apihook) : false;
		
		$create = true;
		
		if (empty($ilance->GPC['lng']))
		{
			$error = $phrase['_please_enter_a_language_name'];
			$create = false;
		}
		if (empty($ilance->GPC['baselanguage']))
		{
			$error .= $phrase['_please_select_a_base_language'];
			$create = false;
		}
                if (empty($ilance->GPC['author']))
                {
                        $ilance->GPC['author'] = $_SESSION['ilancedata']['user']['username'];        
                }
		
		// does or will this language conflict with another language already installed?
		$conflicts = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
			WHERE (title LIKE '%" . $ilance->db->escape_string($ilance->GPC['lng']) . "%' OR languagecode LIKE '%" . $ilance->db->escape_string($ilance->GPC['lng']) . "%')
			LIMIT 1
		");
		if ($ilance->db->num_rows($conflicts) > 0)
		{
			$error = $phrase['_this_language_appears_to_be_similar_to_a_language_already_installed_operation_aborted'];
			$create = false;
		}
		if ($create == true)
		{
			$title = ucfirst(mb_strtolower(trim($ilance->GPC['lng'])));
                        
                        //$ilance->GPC['replacements'] = ilance_htmlentities($ilance->GPC['replacements']);
                        
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "language
				(languageid, title, languagecode, charset, author, locale, textdirection, languageiso, installdate, replacements)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($title) . "',
				'" . $ilance->db->escape_string(mb_strtolower(trim($ilance->GPC['lng']))) . "',
				'" . $ilance->db->escape_string(mb_strtoupper($ilance->GPC['charset'])) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['author']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['locale']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['textdirection']) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['languageiso']) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($ilance->GPC['replacements']) . "')
			");
			$newlangid = $ilance->db->insert_id();
	    
			$sql_blang = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language 
				WHERE languageid = '" . intval($ilance->GPC['baselanguage']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_blang) > 0)
			{
				$res_blang = $ilance->db->fetch_array($sql_blang, DB_ASSOC);
		
				// add language to service categories
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "categories
					ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(200) NOT NULL
					AFTER `parentid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
			  
                                $ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "categories
					ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(200) NOT NULL
					AFTER `parentid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
			
				// add language to email subjects
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					ADD subject_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(200) NOT NULL
					AFTER `message_original`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "email
					SET subject_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = subject_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
			
				// add language to email messages
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					ADD message_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " text NOT NULL
					AFTER `message_original`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "email
					SET message_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = message_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
			
				// add language to country locations
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "locations
					ADD location_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(150) NOT NULL
					AFTER `locationid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "locations
					SET location_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = location_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
						
				// add language to subscription group permission titles
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription_permissions
					ADD accesstext_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(150) NOT NULL
					AFTER `accessname`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "subscription_permissions
					SET accesstext_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = accesstext_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				    
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription_permissions
					ADD accessdescription_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " mediumtext NOT NULL
					AFTER `accessname`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "subscription_permissions
					SET accessdescription_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = accessdescription_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
			
				// create the new language from the base language
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "language_phrases
					ADD text_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " mediumtext NOT NULL
					AFTER `text_original`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "language_phrases
					SET text_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = text_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
			
				// add language to custom service questions
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "project_questions
					ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `cid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_questions
					SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				    
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "project_questions
					ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `cid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_questions
					SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
						
				// add language to custom product questions
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "product_questions
					ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `cid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "product_questions
					SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				    
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "product_questions
					ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `cid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "product_questions
					SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
						
				// add language to custom registration questions
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "register_questions
					ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `pageid`
				");
		    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "register_questions
					SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "register_questions
					ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `pageid`
				");
		    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "register_questions
					SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				
				// skills
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "skills
					ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(100) NOT NULL
					AFTER `parentid`
				");
        
                                $ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "skills
					ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `parentid`
				");
		    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "skills
					SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "skills
					SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
                                
                                // feedback
                                $ilance->db->query("
                                        ALTER TABLE " . DB_PREFIX . "feedback_criteria
                                        ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(100) NOT NULL
                                        AFTER `id`
                                ");
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "feedback_criteria
                                        SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
                                ");
                                
                                // bid fields
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "bid_fields
					ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `fieldid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "bid_fields
					SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
				    
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "bid_fields
					ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " VARCHAR(255) NOT NULL
					AFTER `fieldid`
				");
				    
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "bid_fields
					SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
				");
                                
				($apihook = $ilance->api('admincp_addlanguage_end')) ? eval($apihook) : false;
				
				// add language to stores
				// $ilance->db->query("ALTER TABLE " . DB_PREFIX . "stores_category ADD category_name_".strtolower(substr($ilance->GPC['lng'], 0, 3))." VARCHAR(255) NOT NULL AFTER `categoryid`");
				// $ilance->db->query("UPDATE " . DB_PREFIX . "stores_category SET category_name_".strtolower(substr($ilance->GPC['lng'], 0, 3))." = category_name_".strtolower(substr($res_blang['languagecode'], 0, 3)));
				
				// add language to profile questions
				// $ilance->db->query("ALTER TABLE " . DB_PREFIX . "profile_questions ADD question_".strtolower(substr($ilance->GPC['lng'], 0, 3))." VARCHAR(255) NOT NULL AFTER `groupid`");
				// $ilance->db->query("UPDATE " . DB_PREFIX . "profile_questions SET question_".strtolower(substr($ilance->GPC['lng'], 0, 3))." = question_".strtolower(substr($res_blang['languagecode'], 0, 3)));
				// $ilance->db->query("ALTER TABLE " . DB_PREFIX . "profile_questions ADD description_".strtolower(substr($ilance->GPC['lng'], 0, 3))." VARCHAR(255) NOT NULL AFTER `groupid`");
				// $ilance->db->query("UPDATE " . DB_PREFIX . "profile_questions SET description_".strtolower(substr($ilance->GPC['lng'], 0, 3))." = description_".strtolower(substr($res_blang['languagecode'], 0, 3)));
			
				// are we setting new language as default?
				if (isset($ilance->GPC['defaultlanguage']) AND $ilance->GPC['defaultlanguage'])
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "configuration 
						SET value = '" . $newlangid . "' 
						WHERE name = 'globalserverlanguage_defaultlanguage' 
					");
					$default_language_id = $newlangid;
				}
			}
                        
			print_action_success($phrase['_new_language_was_successfully_created'], $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed($error, $ilance->GPC['return']);
			exit();
		}
	}
    
	// #### CREATE NEW TEMPLATE VARIABLE ###################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'createphrase')
	{
		$area_title = $phrase['_creating_new_phrases'];
		$page_title = SITE_NAME . ' - ' . $phrase['_creating_new_phrases'];
			
		$error = $notice = '';
		
		$languageid = intval($ilance->GPC['languageid']);
		$phrasegroup = $ilance->GPC['phrasegroup'];
		$newvariable = $ilance->GPC['varname'];
		$newtext = $ilance->GPC['text'];
	
		$sql_languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
		if ($ilance->db->num_rows($sql_languages) > 0)
		{
			while ($row = $ilance->db->fetch_array($sql_languages))
			{
				$sql_checkvar = $ilance->db->query("
                                        SELECT phraseid, phrasegroup, varname, text_" . mb_substr($row['languagecode'], 0, 3) . " AS text
                                        FROM " . DB_PREFIX . "language_phrases
                                        WHERE varname = '" . $ilance->db->escape_string($newvariable) . "'
                                ");
				if ($ilance->db->num_rows($sql_checkvar) > 0)
				{
					## CHECK FOR NEW LANGUAGE..
					$sql_checkvar2 = $ilance->db->query("
                                                SELECT phraseid, phrasegroup, varname, text_" . mb_substr($row['languagecode'], 0, 3) . " AS text
                                                FROM " . DB_PREFIX . "language_phrases
                                                WHERE text_" . mb_substr($row['languagecode'], 0, 3) . " = ''
                                        ");
					if ($ilance->db->num_rows($sql_checkvar2) > 0)
					{
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                        SET text_" . mb_substr($row['languagecode'], 0, 3) . " = '" . $ilance->db->escape_string($newtext) . "'
                                                        WHERE varname = '" . $ilance->db->escape_string($newvariable) . "'
                                                ");
                                                
                                                $notice .= "New language template variable <strong>" . stripslashes($ilance->GPC['varname']) . "</strong> was successfully updated for the ".ucfirst($row['languagecode'])." language locale also existing in the template engine.";
					}
					else
					{
                                                $notice .= "The new template variable <strong>" . stripslashes($ilance->GPC['varname']) . "</strong> already exists.  Your new template variable could not be created.  To learn more, please use the Search Phrases or Content option below to find out where this phrase is being used.";
					}
				}
				else
				{
					// no rows exist > insert new phrase and variable for this language
					$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "language_phrases
                                                (phraseid, phrasegroup, varname, text_original, text_".mb_substr($row['languagecode'], 0, 3).")
                                                VALUES(
                                                NULL,
                                                '" . $phrasegroup . "',
                                                '" . $ilance->db->escape_string($newvariable) . "',
                                                '" . $ilance->db->escape_string($newtext) . "',
                                                '" . $ilance->db->escape_string($newtext) . "')
                                        ");
					$notice .= "New language template variable <strong>" . stripslashes($ilance->GPC['varname']) . "</strong> was successfully created.  In order to use this new template within your HTML templates, you must use it like the following: <strong>{" . stripslashes($ilance->GPC['varname']) . "}</strong> keeping the braces in tact.";
				}
			}
                        
			print_action_success($notice, $ilance->GPC['return']);
			exit();
		}
		else
		{
			$error .= $phrase['_were_sorry_there_is_currently_no_available_languages_to_add_phrases_to_please_add_a_new_language_before_you_create_new_phrases'];
			print_action_failed($error, $ilance->GPC['return']);
			exit();
		}
	}
    
	// #### CREATE NEW PHRASE GROUP ############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'addphrasegroup')
	{
		$area_title = $phrase['_adding_new_phrase_group'];
		$page_title = SITE_NAME . ' - ' . $phrase['_adding_new_phrase_group'];
                
		$create = true;
		if (empty($ilance->GPC['groupname']))
		{
			$create = false;
		}	
		if (empty($ilance->GPC['description']))
		{
			$create = false;
		}
		if ($create == true)
		{
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "language_phrasegroups
                                (groupname, description, product)
                                VALUES(
                                '" . mb_strtolower($ilance->db->escape_string($ilance->GPC['groupname'])) . "',
                                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                                'ilance')
                        ");
                        
			print_action_success($phrase['_new_language_phrase_group_was_successfully_created'], $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_there_was_an_error_adding_your_new_phrase_group_please_fill_in_all'], $ilance->GPC['return']);
			exit();
		}
	}
    
	// #### REBUILD LANGUAGE CACHE FILES #######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rebuildlanguage')
	{
		$area_title = $phrase['_rebuilding_language_cache'];
		$page_title = SITE_NAME . ' - ' . $phrase['_rebuilding_language_cache'];
		
		$ilance->admincp->rebuild_language_cache();
		
		print_action_success($phrase['_language_cache_files_have_been_rebuilt_any_recent_phrase_changes_should'], $ilance->GPC['return']);
		exit();
	}
    
	// #### UPDATE PHRASE VARIABLE OR CONTENT HANDLER ######################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'update')
	{
		$area_title = $phrase['_updating_existing_phrases'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_existing_phrases'];
		
		$success = true;
		$notice = "";
		if (isset($ilance->GPC['removevarnames']))
		{
			foreach ($ilance->GPC['phrasesid'] AS $varname)
			{
				$ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "language_phrases
                                        WHERE phraseid = '" . intval($varname) . "'
                                        LIMIT 1
                                ");
                                
				$notice .= "Template Variable <strong>{$varname}</strong> was deleted from all languages available.";
			}
				    
			if (empty($notice))
			{
				$success = false;
				print_action_failed("Warning: your template phrase <strong>{$varname}</strong> could not be deleted.  To delete a phrase you must select it first by using the checkbox option beside each phrase available from the search result listings.", $ilance->GPC['return']);
				exit();
			}
			else
			{
				print_action_success($notice, $ilance->GPC['return']);
				exit();
			}
		}
		else
		{
			$languageid = isset($ilance->GPC['languageid']) ? intval($ilance->GPC['languageid']) : 0;
			$phrasegroup = isset($ilance->GPC['phrasegroup']) ? $ilance->GPC['phrasegroup'] : 'main';
			$page = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
			$phrgroupname = isset($ilance->GPC['phrgroupname']) ? $ilance->GPC['phrgroupname'] : '';
			$phrvartplname = isset($ilance->GPC['phrvartplname']) ? $ilance->GPC['phrvartplname'] : '';
			$phrvarid = isset($ilance->GPC['phrvarid']) ? $ilance->GPC['phrvarid'] : '';
			$phrvarname = isset($ilance->GPC['phrvarname']) ? $ilance->GPC['phrvarname'] : '';
                        
                        //print_r($ilance->GPC); exit;
                        
			$sql_lang = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "language
                                WHERE languageid = '" . $languageid . "'
                                LIMIT 1
                        ");
			if ($ilance->db->num_rows($sql_lang) > 0)
			{
				$res_lang = $ilance->db->fetch_array($sql_lang);
                                
				if (isset($phrgroupname) AND is_array($phrgroupname))
				{
					foreach ($phrgroupname AS $phraseid => $groupname)
					{
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                        SET phrasegroup = '" . $ilance->db->escape_string($groupname) . "',
                                                        ismoved = '1'
                                                        WHERE phraseid = '" . intval($phraseid) . "'
                                                                AND phrasegroup != '" . $ilance->db->escape_string($groupname) . "'
                                                ");
					}
				}
		
				if (isset($phrvartplname) AND is_array($phrvartplname))
				{
					foreach ($phrvartplname AS $phraseid => $varname)
					{
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                        SET varname = '" . $ilance->db->escape_string($varname) . "',
                                                        isupdated = '1'
                                                        WHERE phraseid = '" . intval($phraseid) . "'
                                                                AND varname != '" . $ilance->db->escape_string($varname) . "'
                                                ");
					}
				}
		
				if (isset($phrvarname) AND is_array($phrvarname))
				{
					foreach ($phrvarname AS $phraseid => $phrasetext)
					{
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                        SET text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($phrasetext) . "',
                                                        isupdated = '1'
                                                        WHERE phraseid = '" . intval($phraseid) . "'
                                                                AND text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " NOT LIKE BINARY '" . $ilance->db->escape_string($phrasetext) . "'
                                                ");
					}
				}
			}
			else
			{
				$error = "Your requested actions to change, move or modify an existing variable or phrase could not be performed.";
				$success = false;
			}
		}
	
		if ($success == true)
		{
			print_action_success($phrase['_you_have_sucessfully_changed_moved_or_modified_language_phrases'], $ilance->GPC['return']);
			exit();
	
		}
		else
		{
			print_action_failed($error, $ilance->GPC['return']);
			exit();
		}
	}
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
	{
		$area_title = $phrase['_phrase_search_listings'];
		$page_title = SITE_NAME . ' - ' . $phrase['_phrase_search_listings'];
		
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
                {
                        $ilance->GPC['page'] = 1;
                }
                else
                {
                        $ilance->GPC['page'] = intval($ilance->GPC['page']);
                }
                
		$languageid  = isset($ilance->GPC['languageid']) ? intval($ilance->GPC['languageid']) : $ilance->language->fetch_default_languageid();
		$phrasegroup = isset($ilance->GPC['phrasegroup']) ? $ilance->GPC['phrasegroup'] : 'main';
                
		$request_uri = SCRIPT_URI;
                
		isset($keyword) ? $ilance->GPC['keyword'] : '';
                
		$sql_lang = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . $languageid . "'
                        LIMIT 1
                ");
		$res_lang = $ilance->db->fetch_array($sql_lang);
                
		$rowlimit = isset($ilance->GPC['limit']) ? $ilance->GPC['limit'] : 5;
		$counter = ($ilance->GPC['page'] - 1) * $rowlimit;
		$orderlimit = ' ORDER BY phraseid ASC LIMIT ' . ((intval($ilance->GPC['page']) - 1) * $rowlimit) . ',' . $rowlimit;
    
		$sql = "
                        SELECT phraseid, phrasegroup, varname, text_" . mb_strtolower(mb_substr($res_lang['languagecode'] ,0, 3)) . " AS text, isupdated, ismoved
                        FROM " . DB_PREFIX . "language_phrases
                ";
		    
		// view listings - no advanced search keywords entered
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'view')
		{
			if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'])
			{
				// perform search across all phrase groups
				$sql .= " WHERE phrasegroup != '' ";
			}
			else
			{
				// search a specific phrase group
				$sql .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
		}
		else
		{
			// exact matching queries within all phrasegroups
			if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND $ilance->GPC['keyword'] != "" AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'])
			{
				$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') ";
			}
	
			// matching queries within a phrasegroup
			else if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']))
			{
				$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
	
			// search a phrasegroup
			else if (empty($ilance->GPC['allgroups']) AND isset($ilance->GPC['keyword']) AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
			{
				$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
	
			// search all known phrasegroups
			else if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
			{
				$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') ";
			}
	
			// listing all phrases in all phrasegroups
			else if (empty($ilance->GPC['keyword']) AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND empty($ilance->GPC['exactmatch']))
			{
				$sql .= " WHERE phrasegroup != '' ";
			}
	
			// listing all phrases in a phrasegroup
			else if (empty($ilance->GPC['keyword']) AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
			{
				$sql .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
	
			// listing keywords in a phrasegroup
			else if (isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
			{
				$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
				
			if (isset($ilance->GPC['untranslated']) AND $ilance->GPC['untranslated'])
			{
				$sql .= " AND text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = text_eng";
			}
		}
                
		$sql .= $orderlimit;
		$sql = $ilance->db->query($sql);
		
		$sql2 = "
                        SELECT phraseid, phrasegroup, varname, text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " AS text
                        FROM " . DB_PREFIX . "language_phrases
                ";
                
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'view')
		{
			if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'])
			{
				$sql2 .= " WHERE phrasegroup != '' ";
			}
			else
			{
				$sql2 .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
		}
		else
		{
			// exact matching queries within all phrasegroups -->
			if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] == 1)
			{
				$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') ";
			}
	
			// matching queries within a phrasegroup
			else if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']))
			{
				$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
			
			// search a phrasegroup
			else if (empty($ilance->GPC['allgroups']) AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
			{
				$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
	
			// search all known phrasegroups
			else if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
			{
				$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') ";
			}
	
			// listing all phrases in all phrasegroups
			else if (empty($ilance->GPC['keyword']) AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND empty($ilance->GPC['exactmatch']))
			{
				$sql2 .= " WHERE phrasegroup != '' ";
			}
	
			// listing all phrases in a phrasegroup
			else if (empty($ilance->GPC['keyword']) AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
			{
				$sql2 .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
	
			// listing keywords in a phrasegroup
			else if (isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
			{
				$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
			}
		}
                
		$sql2 = $ilance->db->query($sql2);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$number = $ilance->db->num_rows($sql2);
			$row_count = 0;
			while ($row = $ilance->db->fetch_array($sql))
			{
				$row['groupname'] = $ilance->db->fetch_field(DB_PREFIX . "language_phrasegroups", "groupname = '" . $row['phrasegroup'] . "'", "description");
				$row['tempvariable'] = str_replace("_", "_ ", $row['varname']);
				$row['langcode'] = ucfirst($res_lang['languagecode']);
				
				$sqlbaselanguage = $ilance->db->query("
                                        SELECT baselanguageid
                                        FROM " . DB_PREFIX . "language_phrases
                                        WHERE varname = '" . $ilance->db->escape_string($row['varname']) . "'
                                ");
				if ($ilance->db->num_rows($sqlbaselanguage) > 0)
				{
					$resbaselanguage = $ilance->db->fetch_array($sqlbaselanguage);
				}
					    
				$sqllang = $ilance->db->query("
                                        SELECT languagecode
                                        FROM " . DB_PREFIX . "language
                                        WHERE languageid = '" . $resbaselanguage['baselanguageid'] . "'
                                ");
				$reslang = $ilance->db->fetch_array($sqllang);
				$reslngshort = $reslang['languagecode'];
	    
				$sqlbaselanguagetext = $ilance->db->query("
                                        SELECT text_original, text_" . mb_strtolower(mb_substr($reslngshort, 0, 3)) . " AS text
                                        FROM " . DB_PREFIX . "language_phrases
                                        WHERE varname = '" . $ilance->db->escape_string($row['varname']) . "'
                                ");
				if ($ilance->db->num_rows($sqlbaselanguagetext) > 0)
				{	
					$resbaselanguage = $ilance->db->fetch_array($sqlbaselanguagetext);
					
					($apihook = $ilance->api('admincp_language_loop')) ? eval($apihook) : false;
					
					// original text
					$info = '<div>' . $phrase['_original_phrase_based_from'] . ' <strong>' . ucfirst($reslang['languagecode']) . '</strong></div>';
					$info .= '<div class="gray">' . stripslashes($resbaselanguage['text_original']) . '</div><br />';
					
					// actual text (applied with some highlighting)
					$info .= '<div><strong>' . $phrase['_actual_phrase'] . '</strong> ' . $phrase['_in'] . ' <strong>' . $row['langcode'] . '</strong></div>';
		
					if (isset($ilance->GPC['keyword']))
					{
						$first_q = str_replace($ilance->GPC['keyword'], "<b><span class='errormessage'>".$ilance->GPC['keyword']."</span></b>", stripslashes($row['text']));
						$ucf_q = str_replace(ucfirst($ilance->GPC['keyword']), "<b><span class='errormessage'>".ucfirst($ilance->GPC['keyword'])."</span></b>", $first_q);
						$uc_q = str_replace(mb_strtoupper($ilance->GPC['keyword']), "<b><span class='errormessage'>".mb_strtoupper($ilance->GPC['keyword'])."</span></b>", $ucf_q);
					}
					else
					{
						$uc_q = stripslashes($row['text']);
					}
				}
				else
				{
					$info .= $phrase['_no_phrase_content_available'];
				}
                                
                                $uc_q = '<div class="gray">' . $uc_q . '</div>';
	    
				$row['phraseinfo'] = $info . $uc_q;
				$row['phrasetext'] = stripslashes($row['text']);
				$row['varname'] = $row['varname'];
                                
                                $phrasegroupname_pulldown = '<select name="phrgroupname[' . $row['phraseid'] . ']" style="font-family: verdana">';
	    
				$sql_phrasegroups = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "language_phrasegroups
                                ");
				while ($res_phrasegroups = $ilance->db->fetch_array($sql_phrasegroups))
				{
					$phrasegroupname_pulldown .= '<option value="' . $res_phrasegroups['groupname'] . '"';
					if ($res_phrasegroups['groupname'] == $row['phrasegroup'])
					{
						$phrasegroupname_pulldown .= ' selected="selected"';
					}
					$phrasegroupname_pulldown .= '>' . $res_phrasegroups['description'] . '</option>';
				}
				$phrasegroupname_pulldown .= '</select>';
				$row['phrasegroupname_pulldown'] = $phrasegroupname_pulldown;
	    
				/*
				require_once(DIR_API . 'class.template_files.inc.php');
				$start = new template_files;
				$base = array(DIR_TEMPLATES);
				$start->loop($base);
				$string_to_find = "{".$row['varname']."}";
				
				foreach ($start->newarr as $value)
				{
					if ($value == DIR_TEMPLATES) { }
					elseif ($value == DIR_TEMPLATES_ADMIN) { }
					else
					{
						$filecontents = file_get_contents(DIR_TEMPLATES.$value, "r");
						$found = preg_match("/".$string_to_find."/i", "$filecontents", $matches);
					}
					
					# case sensitive search use this instead
					# $found = preg_match("/$string_to_find/"); <- remove the 'i'
					if ($found)
					{
						//$val2 = mb_substr($val, 0, -2);
						$val = "$value - ";
					}
				}
				*/
				if (isset($val))
				{
					$row['templates_scan'] = $val;
				}
				else
				{
					$row['templates_scan'] = '';
				}
					    
				$row['isupd'] = '<input type="checkbox" name="isupdated" value="" '; if ($row['isupdated'] == '1') { $row['isupd'] .= 'checked="checked"'; } $row['isupd'] .= ' disabled="disabled" />';
				$row['ismov'] = '<input type="checkbox" name="ismoved" value="" '; if ($row['ismoved'] == '1') { $row['ismov'] .= 'checked="checked"'; } $row['ismov'] .= ' disabled="disabled" />';
                                
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                
				$phrase_search_results[] = $row;
				$row_count++;
			}
                        
                        $show['no_phrase_search_results'] = false;
		}
		else
		{
                        $show['no_phrase_search_results'] = true;
		}
	    
		$ilance->GPC['phrasegroup'] = isset($ilance->GPC['phrasegroup']) ? $ilance->GPC['phrasegroup'] : 'main';
		$ilance->GPC['languageid'] = isset($ilance->GPC['languageid']) ? $ilance->GPC['languageid'] : $ilance->language->fetch_default_languageid();
		
		// phrase groups pulldown menu
		$row['phrasegroup_pulldown'] = $ilance->language->print_phrase_groups_pulldown($ilance->GPC['phrasegroup'], false, $_SESSION['ilancedata']['user']['slng']);
    
		// phrase groups pulldown menu
		$phrasegroup_pulldown = $ilance->language->print_phrase_groups_pulldown($ilance->GPC['phrasegroup'], false, $_SESSION['ilancedata']['user']['slng']);
		$language_pulldown = $ilance->language->print_language_pulldown(intval($ilance->GPC['languageid']), false);
		
		$limit_pulldown = '<select name="limit" style="font-family: verdana">';
		$limit_pulldown .= '<option value="5" ';  if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5") { $limit_pulldown .= 'selected'; } $limit_pulldown .= '>5 per page</option>';
		$limit_pulldown .= '<option value="10" '; if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10") { $limit_pulldown .= 'selected'; } $limit_pulldown .= '>10 per page </option>';
		$limit_pulldown .= '<option value="25" '; if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25") { $limit_pulldown .= 'selected'; } $limit_pulldown .= '>25 per page</option>';
		$limit_pulldown .= '<option value="50" '; if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50") { $limit_pulldown .=  'selected'; } $limit_pulldown .= '>50 per page</option>';
		$limit_pulldown .= '</select>';
		
		$phrasegroupname = $ilance->db->fetch_field(DB_PREFIX . "language_phrasegroups", "groupname = '" . $ilance->db->escape_string($ilance->GPC['phrasegroup']) . "'", "description");
		$phrasegroup = $ilance->GPC['phrasegroup'];
		
                $keyword = '';
		if (isset($ilance->GPC['keyword']))
		{
			$keyword = $ilance->GPC['keyword'];
		}
                
		$limit = isset($ilance->GPC['limit']) ? intval($ilance->GPC['limit']) : 5;
                
                $subcmd = '';
		if (isset($ilance->GPC['subcmd']))
		{
			$subcmd = $ilance->GPC['subcmd'];
		}
                
                $allgroups = '';
		if (isset($ilance->GPC['allgroups']))
		{
			$allgroups = $ilance->GPC['allgroups'];
		}
		
                $exactmatch = '';
		if (isset($ilance->GPC['exactmatch']))
		{
			$exactmatch = $ilance->GPC['exactmatch'];
		}
		
                $untranslated = '';
		if (isset($ilance->GPC['untranslated']))
		{
			$untranslated = $ilance->GPC['untranslated'];
		}
	    
		$scriptpage = $ilpage['language'] . '?cmd=' . $ilance->GPC['cmd'] . '&amp;subcmd=' . $subcmd . '&amp;languageid=' . intval($ilance->GPC['languageid']) . '&amp;phrasegroup=' . $ilance->GPC['phrasegroup'] . '&amp;allgroups=' . $allgroups . '&amp;keyword=' . $keyword . '&amp;limit=' . $limit . '&amp;exactmatch=' . $exactmatch . '&amp;untranslated=' . $untranslated;
                
		if (empty($counter))
		{
			$counter = 0;
		}
		if (empty($number))
		{
			$number = 0;
		}
		
		$prevnext = print_pagnation($number, $rowlimit, intval($ilance->GPC['page']), $counter, $scriptpage);
                
                $pprint_array = array('buildversion','ilanceversion','login_include_admin','limit','keyword','phrasegroup','limit_pulldown','language_pulldown','phrasegroupname','prevnext','ismov','isupd','languageid','request_uri','keyword','base_language_pulldown','limit_pulldown','phrasegroup_pulldown','language_pulldown','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','https_server','http_server');
    
                ($apihook = $ilance->api('admincp_language_phrase_results_end')) ? eval($apihook) : false;
    
		$ilance->template->fetch('main', 'language_phrase_results.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','phrase_search_results'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
    else
	{
		$area_title = $phrase['_language_administration_menu'];
		$page_title = SITE_NAME . ' - ' . $phrase['_language_administration_menu'];
		
		($apihook = $ilance->api('admincp_language_management')) ? eval($apihook) : false;
		
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['language'], $ilpage['language'], $_SESSION['ilancedata']['user']['slng']);
		
                $show['editlanguage'] = false;
                if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'edit-language')
                {
                        $show['editlanguage'] = true;
                        
                        // #### saving language details
                        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'save')
                        {
                                //$ilance->GPC['replacements'] = ilance_htmlentities($ilance->GPC['replacements']);
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "language
                                        SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
                                        charset = '" . $ilance->db->escape_string($ilance->GPC['charset']) . "',
                                        locale = '" . $ilance->db->escape_string($ilance->GPC['locale']) . "',
                                        author = '" . $ilance->db->escape_string($ilance->GPC['author']) . "',
                                        textdirection = '" . $ilance->db->escape_string($ilance->GPC['textdirection']) . "',
                                        languageiso = '" . $ilance->db->escape_string($ilance->GPC['languageiso']) . "',
                                        canselect = '" . intval($ilance->GPC['canselect']) . "',
                                        replacements = '" . $ilance->db->escape_string($ilance->GPC['replacements']) . "'
                                        WHERE languageid = '" . intval($ilance->GPC['id']) . "'
                                ");
                                
                                
                                $lcount = $ilance->db->query("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "language");
                                $rcount = $ilance->db->fetch_array($lcount);
                                $languagecount = $rcount['count'];
                                
                                // remember to set as default if admin wants this to be default
                                // to be safe, if there is only 1 language, this will not update the core configuration as the admin
                                // might be setting the default language to NONE if there is only 1 language which should be the primary language to use!
                                if ($ilconfig['globalserverlanguage_defaultlanguage'] != $ilance->GPC['id'] AND $languagecount > 1)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "configuration
                                                SET value = '" . intval($ilance->GPC['id']) . "'
                                                WHERE name = 'globalserverlanguage_defaultlanguage'
                                        ");
                                }
                                
                                print_action_success($phrase['_you_have_updated_the_language_and_new_settings_have_been_applied'], $ilpage['language']);
				exit();
                        }
                        
                        // #### updating language
                        else
                        {
                                $sql = $ilance->db->query("
                                        SELECT languageid, title, charset, author, locale, textdirection, languageiso, canselect, replacements
                                        FROM " . DB_PREFIX . "language
                                        WHERE languageid = '" . intval($ilance->GPC['id']) . "'
                                ");
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql);
                                        
                                        $title = $res['title'];
                                        $id = $res['languageid'];
                                        $charset = $res['charset'];
                                        $author = $res['author'];
                                        $locale = $res['locale'];
                                        $languageiso = $res['languageiso'];
                                        $replacements = $res['replacements'];
                                        
                                        if ($ilconfig['globalserverlanguage_defaultlanguage'] == $res['languageid'])
                                        {
                                                $defaultlanguage0 = '';
                                                $defaultlanguage1 = 'selected="selected"'; 
                                        }
                                        else
                                        {
                                                $defaultlanguage0 = 'selected="selected"';
                                                $defaultlanguage1 = '';
                                        }
                                        if ($res['textdirection'] == 'rtl')
                                        {
                                                $textdirection0 = '';
                                                $textdirection1 = 'selected="selected"'; 
                                        }
                                        else
                                        {
                                                $textdirection0 = 'selected="selected"';
                                                $textdirection1 = '';
                                        }
                                        if ($res['canselect'])
                                        {
                                                $canselect0 = '';
                                                $canselect1 = 'selected="selected"'; 
                                        }
                                        else
                                        {
                                                $canselect0 = 'selected="selected"';
                                                $canselect1 = '';
                                        }
                                }
                        }
                }
                
                // does admin request template to show all phrases in drop down?
		if (isset($ilance->GPC['showphrases']))
		{
			$phrases_selectlist = '<select name="phrasesid[]" multiple size="10" style="font-family: verdana">';
			$result = $ilance->db->query("
                                SELECT phraseid, varname
                                FROM " . DB_PREFIX . "language_phrases
                                ORDER BY phraseid ASC
                        ");
			while ($record = $ilance->db->fetch_array($result))
			{
				$phrases_selectlist .= '<option value="' . $record['phraseid'] . '">[' . $record['phraseid'] . '] ' . shorten($record['varname'], 70) . '</option>';
			}
			$phrases_selectlist .= '</select>';
		}
		
		// language pulldown menu
		$language_pulldown = $ilance->language->print_language_pulldown();
	
		// base language pulldown menu
                $phr = $phrase['_choose_base_language'];
		$base_language_pulldown = $ilance->language->print_language_pulldown(false, false, 'baselanguage', $phr);
			
		// phrase groups pulldown menu
		$phrasegroup_pulldown = $ilance->language->print_phrase_groups_pulldown(false, false, $_SESSION['ilancedata']['user']['slng']);
			
		$limit_pulldown = '<select name="limit" class="flat" style="font-family: verdana">';
		$limit_pulldown .= '<option value="5" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>5 ' . $phrase['_per_page'] . '</option>';
		$limit_pulldown .= '<option value="10" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>10 ' . $phrase['_per_page'] . '</option>';
		$limit_pulldown .= '<option value="25" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>25 ' . $phrase['_per_page'] . '</option>';
		$limit_pulldown .= '<option value="50" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50")
		{
			$limit_pulldown .=  'selected="selected"';
		}
		$limit_pulldown .= '>50 ' . $phrase['_per_page'] . '</option>';
		$limit_pulldown .= '</select>';
	
		if (isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != '')
		{
			$keyword = $ilance->GPC['keyword'];
		}
	    
		// mysql character set information
		$charsetvariable = $ilance->db->query("SHOW VARIABLES LIKE 'character_set%'");
		if ($ilance->db->num_rows($charsetvariable) > 0)
		{
			while ($resvar = $ilance->db->fetch_array($charsetvariable))
			{
				$mysqlcharset[] = $resvar;
			}
		}
		
		// mysql connection collation
		$collation = $ilance->db->query("SHOW VARIABLES LIKE 'collation%'");
		if ($ilance->db->num_rows($collation) > 0)
		{
			while ($rescollation = $ilance->db->fetch_array($collation))
			{
				$mysqlcollation[] = $rescollation;
			}
		}
                
                // language management results
                $languageresults = $ilance->db->query("
                        SELECT languageid, languagecode, title, charset, locale, author, textdirection, languageiso, canselect, installdate
                        FROM " . DB_PREFIX . "language
                ");
		if ($ilance->db->num_rows($languageresults) > 0)
		{
                        $rowcount = 0;
			while ($res = $ilance->db->fetch_array($languageresults))
			{
                                $res['actions'] = '<a href="' . $ilpage['language'] . '?cmd=edit-language&amp;id=' . $res['languageid'] . '#editlanguage"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
                                if ($res['textdirection'] == 'ltr')
                                {
                                        $res['textdirection'] = $phrase['_left_to_right'];
                                }
                                else
                                {
                                        $res['textdirection'] = $phrase['_right_to_left'];
                                }
                                if ($res['installdate'] != '0000-00-00 00:00:00')
                                {
                                        $res['installdate'] = print_date($res['installdate']);
                                }
                                else
                                {
                                        $res['installdate'] = '-';
                                }
                                
                                $res['class'] = ($rowcount % 2) ? 'alt2' : 'alt1';
				$installedlanguages[] = $res;
                                $rowcount++;
			}
		}
                
                $masterphrases = number_format($ilance->admincp->fetch_master_phrases_count());
                $customphrases = number_format($ilance->admincp->fetch_custom_phrases_count());
                $movedphrases = number_format($ilance->admincp->fetch_moved_phrases_count());
                $totalphrases = number_format($ilance->admincp->fetch_total_phrases_count());
                
                // language settings tab
                $global_languagesettings = $ilance->admincp->construct_admin_input('language', $ilpage['language']);
                
                // set default new language author to admin logged in
                $adminuser = $_SESSION['ilancedata']['user']['username'];
                
                $products_pulldown = $ilance->admincp->products_pulldown('');
                
				//seo settings in admincp
				
				 // language management results
				 	$counter = ($ilance->GPC['page'] - 1) * 25;
	               $scriptpageprevnext = 'language.php?';
					if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
					 {
					$ilance->GPC['page'] = 1;
					 }
					 else
						 {
							$ilance->GPC['page'] = intval($ilance->GPC['page']);
						 }
					
					
					 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'searching' and $ilance->GPC['filtervalue']!='')
     					{
                        $show['showsearch'] = true;
                         $filtervalue=  $ilance->GPC['filtervalue']; 
                        $filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'seo_url';
						$pagination_query="SELECT * FROM " . DB_PREFIX . "seo WHERE " .$filterby. " LIKE '%" .$filtervalue. "%'";
						$actual_query = "SELECT * FROM " . DB_PREFIX . "seo WHERE " .$filterby. " LIKE '%" .$filtervalue. "%' LIMIT " . (($ilance->GPC['page'] - 1) * 25). "," . '25'." ";
   						}else
						{
						$pagination_query="SELECT * FROM " . DB_PREFIX . "seo";
						$actual_query = "SELECT * FROM " . DB_PREFIX . "seo LIMIT " . (($ilance->GPC['page'] - 1) * 25). "," . '25'." ";
						}
								 
				 
				  $languageresults1 = $ilance->db->query($pagination_query);
				 
                $languageresults = $ilance->db->query($actual_query);
						
						$number = (int)$ilance->db->num_rows( $languageresults1);
               
		if ($ilance->db->num_rows($languageresults) > 0)
		{
                        $rowcount = 0;
			while ($res = $ilance->db->fetch_array($languageresults))
			{                    $res['seo_page'] =$res['page']; 
                                 $res['url_description']=substr($res['url_description'],0,50);
								 $res['edit'] = '<a href="' . $ilpage['language'] . '?edit-seo=edit-value&amp;id=' . $res['id'] . '#editlanguage"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
								 $res['delete'] = '<a href="' . $ilpage['language'] . '?delete-seo=delete-value&amp;id=' . $res['id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
                                $res['class'] = ($rowcount % 2) ? 'alt2' : 'alt1';
				$seo[] = $res;
                                $rowcount++;
			}
		}
             $prof = print_pagnation($number, 25, $ilance->GPC['page'], $counter, $scriptpageprevnext);	
		
	
		
				
				
                $pprint_array = array('get_filtervalue','id','seo_url','seo_page','seo_key','seo_title','seo_description','prof','products_pulldown','buildversion','ilanceversion','login_include_admin','replacements','canselect0','canselect1','languageiso','textdirection0','textdirection1','masterphrases','customphrases','movedphrases','totalphrases','adminuser','id','author','title','locale','charset','defaultlanguage0','defaultlanguage1','global_languagesettings','language_pulldown2','phrases_selectlist','keyword','base_language_pulldown','limit_pulldown','phrasegroup_pulldown','language_pulldown','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
                ($apihook = $ilance->api('admincp_language_end')) ? eval($apihook) : false;
        
		$ilance->template->fetch('main', 'language.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav','subnav_settings','mysqlcharset','mysqlcollation','installedlanguages','seo','seo1'));
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