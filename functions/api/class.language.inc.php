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
* Language class to perform the majority of language functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class language
{
        /**
        * array holding language cache
        */
        var $cache = array();
        
        /**
        * Constructor
        */
        function language()
        {
                global $ilance, $ilconfig;
                /*
                // we'll create a cache so we don't have to call the language table again
                $sql = $ilance->db->query("
                        SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $this->cache[$res['languageid']] = $res;
                        }
                        unset($res);
                }*/
				$res['languageid']=1;
				$res['title']='English (US)';
				$res['languagecode']='english';
				$res['charset']='UTF-8';
				$res['locale']='en_US';
				$res['author']='iLance';
				$res['textdirection']='ltr';
				$res['languageiso']='en';
				$res['canselect']='1';
				$res['installdate']='2010-09-17 14:43:21';
				$res['replacements']='ó|o, ę|e, ż|z, ł|l, ę|e, ć|c, ń|n, ą|a, ś|s, Ż|Z, Ś|S';
				
				 $this->cache[$res['languageid']] = $res;
				
        }
        
        /**
        * Function to return the language phrases cache array from the datastore.
        * This function is called just after session_start() within global.php
        *
        * @return      array       $phrase array
        */
        function init_phrases()
        {
                global $ilance, $myapi, $ilconfig, $phrase;
        
                
                // list of default phrase groups to load
                $default['phrasegroups'] = array(
                        'main',
                        'livesync',
                        'cron',
                        'ipn',
                        'javascript'
                );
        
                // list of phrase variables for searching
                $phrasesearch  = array('{{site_name}}', '{{max_payment_days}}');
                $phrasereplace = array(SITE_NAME, $ilconfig['invoicesystem_maximumpaymentdays']);
                
                $charsearch = array("'", '"');
                $charreplace = array('\x27', '\x22');
                
                // list of phrase groups we're parsing
                $phrasegroups = (!empty($phrase['groups'])) ? $phrase['groups'] : array();
                
                // merge default and requested phrase groups
                $phrase['groups'] = array_merge($phrasegroups, $default['phrasegroups']);
                $phrase['groups'] = array_unique($phrase['groups']);
                
                // #### DATABASE LANGUAGE CACHE ################################
                $queryextra = '';
                foreach ($phrase['groups'] AS $phrasegroup)
                {
                        $queryextra .= " groups.groupname = '" . $ilance->db->escape_string($phrasegroup) . "' OR";
                }
                $querystr = mb_substr($queryextra, 0, -3);
    
                // seconds to live for cache files
                $cachefile['expire'] = 38400;
                if ($ilconfig['externaljsphrases'])
                {
                        $cachefile['language'] = DIR_TMP . 'phrases_' . mb_strtolower($_SESSION['ilancedata']['user']['slng']) . '.js';
						$cachefile_php['language'] = DIR_TMP . 'phrases_' . mb_strtolower($_SESSION['ilancedata']['user']['slng']) . '.php';
                }
				if(is_file($cachefile_php['language']) and time()-filemtime($cachefile_php['language'])<$cachefile['expire'])
				{
				include($cachefile_php['language']);
				return $phrase;
				}
                
                $jshtml = "var phrase = \n{\n";
                    //($querystr) AND
                $query = $ilance->db->query("
                        SELECT phrase.varname, phrase.text_eng AS text, groups.groupname AS phrasegroupname
                        FROM " . DB_PREFIX . "language_phrases AS phrase,
                        " . DB_PREFIX . "language_phrasegroups AS groups,
                        " . DB_PREFIX . "language AS lang
                        WHERE 
                             groups.groupname = phrase.phrasegroup
                            AND lang.languageid = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
				$fp = fopen($cachefile_php['language'], 'wb');
				fputs($fp, "<?php \n");
                        while ($cache = $ilance->db->fetch_array($query, DB_ASSOC))
                        {
                                if ($cache['phrasegroupname'] == 'javascript')
                                {
                                        if (defined('LOCATION') AND (LOCATION != 'attachment' OR LOCATION != 'lancealert'))
                                        {
                                                $jsphrase = $cache['text'];
                                                $jsphrase = str_replace($charsearch, $charreplace, $jsphrase);
                                                $jsphrase = str_replace($phrasesearch, $phrasereplace, $jsphrase);
                                                $jsphrase = html_entity_decode($jsphrase);
                                                $jsphrase = nl2br($jsphrase);
                                                $jshtml .= "\t" . "'" . trim($cache['varname']) . "' : '" . $jsphrase . "',\n";
												
                                        }	
                                }
                                else 
                                {
                                        $phrase[$cache['varname']] = str_replace($phrasesearch, $phrasereplace, stripslashes(un_htmlspecialchars($cache['text'])));
										$text=addslashes(str_replace($phrasesearch, $phrasereplace, un_htmlspecialchars($cache['text'])));
										$t="\$phrase['".$cache['varname']."'] = \"".$text."\";\n";
										fputs($fp, $t);
										
                                }
                        }
						
                        $jshtml = substr($jshtml, 0, -2) . "\n";
						fputs($fp, "\$phrase['JAVASCRIPT_PHRASES'] = \"".$jshtml."\";\n");
						fputs($fp, "?>");
						fclose($fp);   
                        unset($cache);
                }
                
                $jshtml .= "};\n";
				
                if ($ilconfig['externaljsphrases'])
                {
                        if (!file_exists($cachefile['language']))
                        {
                                $fp = fopen($cachefile['language'], 'wb');
                                fputs($fp, $jshtml);
                                fclose($fp);
                                unset($jshtml);
                        }
                }
                else
                {                        
                        $phrase['JAVASCRIPT_PHRASES'] = $jshtml;
                        unset($jshtml);
                }
                
                return $phrase;
        }
    
        /**
        * Function to construct a phrase using replacement phrases
        *
        * @param       string      phrase string containing [x]'s
        * @param       mixed       array or string containing our replacements
        *
        * @return      array       $phrase array
        */
        function construct_phrase($var, $replacements)
        {
                global $phrase;
                
                $result = $result2 = '';
                if (is_array($replacements))
                {
                        $k = 0;
                        $max = count($replacements);
                        for ($i = 0; $i < mb_strlen($var); $i++)
                        {
                                if (mb_substr($var, $i, 3) == '[x]')
                                {
                                        $result .= '' . $replacements[$k++] . '';
                                        if ($k > $max)
                                        {
                                                return $phrase['_incorrect_number_of_replacements_provided_to_construct_phrase_function'];
                                        }
                                        
                                        $i+=2;
                                }
                                else
                                {
                                        $result .= mb_substr($var, $i, 1);
                                }
                        }
                }
                else
                {
                        for ($i = 0; $i < mb_strlen($var); $i++)
                        {
                                if (mb_substr($var, $i, 3) == '[x]')
                                {
                                        $result .= '' . $replacements . '';
                                        $i+=2;
                                }
                                else
                                {
                                        $result .= mb_substr($var, $i, 1);
                                }
                        }
                }
                
                return $result;
        }
        
        /**
        * Function to construct a language pulldown menu
        *
        * @return      string       HTML formatted language pulldown menu
        */
        function construct_language_pulldown($fieldname = 'languageid')
        {
                global $ilance, $myapi, $ilconfig;
                
                $html = '<select name="' . $fieldname . '" style="font-family: verdana">';
                
                $sql = $ilance->db->query("
                        SELECT languageid, title
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                                {
                                        $sql2 = $ilance->db->query("
                                                SELECT languageid
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $res2 = $ilance->db->fetch_array($sql2);
                                        
                                        $html .= '<option value="'.$res['languageid'].'"';
                                        if ($res['languageid'] == $res2['languageid'])
                                        {
                                                $html .= ' selected="selected"';
                                        }
                                        $html .= '>' . stripslashes($res['title']) . '</option>';
                                }
                                else
                                {
                                        // register form
                                        $html .= '<option value="' . $res['languageid'] . '"';
                                        if ($res['languageid'] == $ilconfig['globalserverlanguage_defaultlanguage'])
                                        {
                                                $html .= ' selected="selected"';
                                        }
                                        $html .= '>' . stripslashes($res['title']) . '</option>';
                                }
                        }
                }
                
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a language code like english or german, etc
        *
        * @param       integer      (optional) language id
        * @return      string       HTML formatted language pulldown menu
        */
        function print_language_code($languageid = '')
        {
                global $ilance, $myapi, $ilconfig, $ilance;
                
                $langid = !empty($languageid) ? intval($languageid) : $ilconfig['globalserverlanguage_defaultlanguage'];
                /*
                $sql = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($langid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        return $res['languagecode'];
                }
                */
                return 'english';
        }
        
        /**
        * Function to print a short version of the language code like eng or ger, etc
        *
        * @return      string       HTML formatted default language pulldown menu
        */
        function print_short_language_code()
        {
                global $ilance, $myapi, $ilconfig;
                /*
                if (!empty($ilconfig['globalserverlanguage_defaultlanguage']) AND $ilconfig['globalserverlanguage_defaultlanguage'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT languagecode
                                FROM " . DB_PREFIX . "language
                                WHERE languageid = '" . $ilconfig['globalserverlanguage_defaultlanguage'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                
                                return mb_substr($res['languagecode'], 0, 3);
                        }
                }
                */
                return 'eng';
        }
    
        /**
        * Function to count the number of phrases within a particular phrase group
        *
        * @param       integer      phrase group
        * 
        * @return      integer      Returns the number of phrases in the phrasegroup
        */
        function count_phrases_in_phrasegroup($phrasegroup = '')
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "'
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql);
                
                return (int)$res['count'];
        }
        
        /**
        * Function to count the number of un-phrased phrases within a particular phrase group
        *
        * @param       integer      phrase group
        * @param       string       short language code
        * 
        * @return      integer      Returns the number of un-phrased phrases in the phrasegroup
        */
        function count_unphrased_in_phrasegroup($phrasegroup = '', $slng)
        {
                global $ilance, $myapi;
                
                if (isset($slng) AND $slng != 'eng')
                {
                        $sql = $ilance->db->query("
                                SELECT COUNT(*) AS count
                                FROM " . DB_PREFIX . "language_phrases
                                WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "'
                                    AND text_$slng = text_eng
                        ", 0, null, __FILE__, __LINE__);
                        $res = $ilance->db->fetch_array($sql);
                        
                        return ', ' . (int)$res['count'] . ' untranslated';
                }
                
                return '';
        }
        
        /**
        * Function to print the phrase group pulldown menu
        *
        * @param       integer      (optional) phrase group
        * @param       bool         enable auto-submit?
        * @param       string       short language code
        * 
        * @return      integer      HTML formatted phrase group pulldown menu
        */
        function print_phrase_groups_pulldown($selected = '', $autosubmit = '', $slng)
        {
                global $ilance, $myapi, $phrase;
                
                $html = '';
                $sql = $ilance->db->query("
                        SELECT groupname, description
                        FROM " . DB_PREFIX . "language_phrasegroups
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        if (isset($autosubmit) AND $autosubmit)
                        {
                                $html = '<select name="phrasegroup" id="phrasegroup" onchange="urlswitch(this, \'dostyle\')" style="font-family: verdana"><optgroup label="' . $phrase['_choose_phrase_group'] . '">';
                        }
                        else
                        {
                                $html = '<select name="phrasegroup" id="phrasegroup" style="font-family: verdana"><optgroup label="' . $phrase['_choose_phrase_group'] . '">';
                        }
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if (isset($selected) AND $res['groupname'] == $selected)
                                {
                                        $html .= '<option value="' . $res['groupname'] . '" selected="selected">' . stripslashes($res['description']) . ' (' . $this->count_phrases_in_phrasegroup($res['groupname']) . ' phrases' . $this->count_unphrased_in_phrasegroup($res['groupname'], $slng) . ')</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['groupname'] . '">' . stripslashes($res['description']) . ' (' . $this->count_phrases_in_phrasegroup($res['groupname']) . ' phrases' . $this->count_unphrased_in_phrasegroup($res['groupname'], $slng) . ')</option>';
                                }
                        }
                        $html .= '</optgroup></select>';
                }
                
                return $html;
        }
        
        /**
        * Function to print the site's default language id
        *
        * @return      integer       Returns default language id
        */
        function fetch_default_languageid()
        {
                global $ilconfig;
                
                if (!empty($ilconfig['globalserverlanguage_defaultlanguage']) AND $ilconfig['globalserverlanguage_defaultlanguage'] > 0)
                {
                        return intval($ilconfig['globalserverlanguage_defaultlanguage']);
                }
        }
        
        /**
        * Function to return site language selection pulldown menu on footer pages
        *
        * @param       string 	     selected language
        * @param       bool          enable auto-submit once new value is selected?
        * @param       string        fieldname of pulldown menu
        * @param       string        (optional) optgroup title
        */
        function print_language_pulldown($selected = '', $autosubmit = '', $selectname = '', $optgrouptitle = '')
        {
                global $ilance, $myapi, $phrase;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT languageid, languagecode, title, canselect
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if (isset($autosubmit) AND $autosubmit)
                {
                        $html = '<select name="language" id="language" onchange="urlswitch(this, \'dolanguage\')" style="font-family: verdana">';
                        $html .= '<optgroup label="' . $phrase['_choose_language'] . '">';
                        
                        $languagecount = 0;
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                if ($res['canselect'] OR !empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
                                {
                                        $html .= (isset($selected) AND $selected == $res['languageid']) ? '<option value="' . $res['languagecode'] . '" selected="selected">' . stripslashes($res['title']) . '</option>' : '<option value="' . $res['languagecode'] . '">' . stripslashes($res['title']) . '</option>';
                                        $languagecount++;
                                }
                        }
                        unset($res);
                        $html .= '</optgroup></select>';
                }
                else
                {
                        // custom select name title
                        $html = (isset($selectname) AND !empty($selectname)) ? '<select style="font-family: verdana" name="' . $selectname . '" id="' . $selectname . '">' : '<select style="font-family: verdana" name="languageid" id="languageid">';
                        $html .= (isset($optgrouptitle) AND !empty($optgrouptitle)) ? '<optgroup label="' . $optgrouptitle . '">' : '<optgroup label="' . $phrase['_choose_language'] . '">';
                        
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= (isset($selected) AND $selected == $res['languageid']) ? '<option value="' . $res['languageid'] . '" selected="selected">' . stripslashes($res['title']) . '</option>' : '<option value="' . $res['languageid'] . '">' . stripslashes($res['title']) . '</option>';
                        }
                        $html .= '</optgroup></select>';
                }
                
                // if we're viewing this pulldown menu from the footer page, and have only 1 language, hide the pulldown menu
                if (isset($autosubmit) AND $autosubmit AND $languagecount == 1 AND defined('LOCATION') AND (LOCATION != 'admin' OR LOCATION != 'registration'))
                {
                        $html = '';
                }
                
                return $html;
        }
        
        /**
        * Function to fetch the seo replacement characters for the seo urls based on the currently selected viewing language
        *
        * @param       integer       language id
        * 
        * @return      integer       Returns the phrase group id number
        */
        function fetch_seo_replacements($languageid = 0)
        {
                return $this->cache[$languageid]['replacements'];
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
